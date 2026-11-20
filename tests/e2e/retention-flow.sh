#!/usr/bin/env bash
#
# @copyright Copyright (c) 2026 Sendent B.V.
# @license GNU AGPL version 3 or any later version
#
# End-to-end test of the Sendent retention flow on a real Nextcloud with
# files_retention:
#
#   1. Configure it the way the retention assistant does: create the Sendent
#      tags through Sendent's API (which saves their ids in the app config) and
#      the files_retention rules with the values in RetentionAssistant.vue.
#   2. Share folders tagged with the upload tag. Let the shares expire (the
#      real ExpireSharesJob) or remove them. Sendent must tag the folder
#      retention:expired / retention:removed and restart its countdown.
#   3. Run the real RetentionJob. It must keep folders whose period hasn't
#      passed yet and delete (to the trash bin) the ones whose period has.
#
# Time passing is simulated by moving timestamps back in the database. All
# other steps go through the HTTP API and the background jobs, unchanged.
#
# Run it on the Nextcloud host, as the user that runs Nextcloud (it calls occ
# and opens the database), against a THROWAWAY instance: it replaces the
# files_retention rules of Sendent's tags and turns off rate limiting.
#
#   NC_URL   base URL of the instance        default http://localhost:8080
#   NC_AUTH  admin user:password              default admin:admin
#   NC_ROOT  Nextcloud server directory       default ../../../.. from here
#
# Example, in the official Docker image with this app in custom_apps:
#   docker exec -u www-data -e NC_URL=http://localhost <container> \
#       bash custom_apps/sendent/tests/e2e/retention-flow.sh

set -uo pipefail

HERE=$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)
APP_DIR=$(cd "$HERE/../.." && pwd)
NC_ROOT=${NC_ROOT:-$(cd "$APP_DIR/../.." && pwd)}
export NC_ROOT
NC_URL=${NC_URL:-http://localhost:8080}
NC_URL=${NC_URL%/}
NC_AUTH=${NC_AUTH:-admin:admin}
NC_USER=${NC_AUTH%%:*}
ASSISTANT="$APP_DIR/src/components/general/RetentionAssistant.vue"

DAV="$NC_URL/remote.php/dav/files/$NC_USER"
SHARES="$NC_URL/ocs/v2.php/apps/files_sharing/api/v1/shares"
RETENTIONS="$NC_URL/ocs/v2.php/apps/files_retention/api/v1/retentions"
OCS=(-H 'OCS-APIRequest: true' -H 'Accept: application/json')
RUN="e2e-$(date +%s)" # name prefix, so reruns on the same instance don't collide

# ---------------------------------------------------------------- helpers

MAIN_PID=$$
trap 'exit 2' TERM
die() {
	echo "ERROR: $*" >&2
	kill -TERM "$MAIN_PID" # also stops the script when called inside $(...)
	exit 2
}

FAILURES=0
pass() { printf '  \033[32mPASS\033[0m %s\n' "$1"; }
fail() {
	printf '  \033[31mFAIL\033[0m %s\n' "$1"
	FAILURES=$((FAILURES + 1))
}
check() { # description expected actual
	if [ "$2" = "$3" ]; then pass "$1"; else fail "$1: expected '$2', got '$3'"; fi
}
section() { printf '\n== %s\n' "$1"; }

occ() { php "$NC_ROOT/occ" --no-warnings "$@"; }
sql() { php "$HERE/helper.php" sql "$@"; }
json() { php "$HERE/helper.php" json "$1"; }
now() { date +%s; }
days_ago() { echo $(($(now) - $1 * 86400)); }

# api METHOD URL [curl args...]: prints the body, stops the test unless 2xx
api() {
	local method=$1 url=$2 out code
	shift 2
	out=$(curl -sS -u "$NC_AUTH" -X "$method" -w '\n%{http_code}' "$@" "$url") || die "$method $url: curl failed"
	code=${out##*$'\n'}
	out=${out%$'\n'*}
	[[ $code == 2* ]] || die "$method $url returned $code: $out"
	printf '%s' "$out"
}

http_status() { # path → 207 if it exists, 404 if not
	curl -s -o /dev/null -w '%{http_code}' -u "$NC_AUTH" -X PROPFIND -H 'Depth: 0' "$DAV/$1"
}

file_id() {
	api PROPFIND "$DAV/$1" -H 'Depth: 0' --data '<?xml version="1.0"?><d:propfind xmlns:d="DAV:" xmlns:oc="http://owncloud.org/ns"><d:prop><oc:fileid/></d:prop></d:propfind>' |
		grep -o '<oc:fileid>[0-9]*' | cut -d'>' -f2
}

upload() { # path content
	printf '%s' "$2" | api PUT "$DAV/$1" --data-binary @- >/dev/null
}

in_trash() { # name → yes/no
	if api PROPFIND "$NC_URL/remote.php/dav/trashbin/$NC_USER/trash" -H 'Depth: 1' | grep -q "/trash/$1\.d[0-9]"; then
		echo yes
	else
		echo no
	fi
}

share() { # path [expireDate] → share id
	api POST "$SHARES" "${OCS[@]}" --data-urlencode "path=/$1" -d shareType=3 ${2:+-d "expireDate=$2"} | json ocs.data.id
}

run_jobs() { # job class
	local id ids
	ids=$(occ background-job:list --class="$1" --output=json | json '*.id')
	[ -n "$ids" ] || die "no background job of class $1"
	for id in $ids; do
		occ background-job:execute "$id" --force-execute >/dev/null || die "background job $id ($1) failed"
	done
}

set_mtime() { # file id, days ago
	local t
	t=$(days_ago "$2")
	sql "UPDATE *PREFIX*filecache SET mtime = ?, storage_mtime = ? WHERE fileid = ?" "$t" "$t" "$1"
}

mtime_age_days() { # file id → whole days since its mtime
	echo $((($(now) - $(sql "SELECT mtime FROM *PREFIX*filecache WHERE fileid = ?" "$1")) / 86400))
}

retention_tags() { # file id → expired, removed, expired+removed or none
	local ids tags=()
	ids=$(sql "SELECT systemtagid FROM *PREFIX*systemtag_object_mapping WHERE objecttype = 'files' AND objectid = ?" "$1")
	grep -qx "${TAG[tag:expired]}" <<<"$ids" && tags+=(expired)
	grep -qx "${TAG[tag:removed]}" <<<"$ids" && tags+=(removed)
	if [ ${#tags[@]} -eq 0 ]; then echo none; else (IFS=+; echo "${tags[*]}"); fi
}

assistant_rule() { # tag key → "timeamount timeunit timeafter" that the assistant uses
	local args
	args=$(grep -oE "createRetentionRule\('$1', *[0-9]+, *[0-9]+, *[0-9]+\)" "$ASSISTANT" | grep -oE '[0-9]+, *[0-9]+, *[0-9]+\)' | tr -d ' )' | tr ',' ' ')
	[ -n "$args" ] || die "createRetentionRule('$1', ...) not found in $ASSISTANT"
	echo "$args"
}

period_days() { # timeamount timeunit → upper bound of the period in days
	local per_unit=(1 7 31 366) # days, weeks, months, years
	echo $(($1 * ${per_unit[$2]}))
}

# ---------------------------------------------------------------- 1. configure

section "Nextcloud $(occ status --output=json | json versionstring), files_retention $(occ app:list --output=json | json enabled.files_retention)"

section "1. Configure like the retention assistant"

# Creating shares is limited to 20 per 10 minutes per user and a run creates
# up to 13. Turned off, no new attempts are counted; attempts counted before
# still are for up to 10 minutes.
occ config:system:set ratelimit.protection.enabled --type=boolean --value=false >/dev/null

declare -A TAG
declare -A TAG_NAME=([tag:upload]=outlook:upload [tag:removed]=retention:removed [tag:expired]=retention:expired)
for key in tag:upload tag:removed tag:expired; do
	# start unconfigured, so the tag id has to be saved by the create call
	occ config:app:delete sendent "$key" >/dev/null
	TAG[$key]=$(api POST "$NC_URL/index.php/apps/sendent/api/1.0/tag" "${OCS[@]}" \
		-H 'Content-Type: application/json' -d "{\"name\":\"${TAG_NAME[$key]}\",\"key\":\"$key\"}" | json id)
	[ -n "${TAG[$key]}" ] || die "creating tag ${TAG_NAME[$key]} returned no id"
	check "$key: id of '${TAG_NAME[$key]}' saved in the app config" "${TAG[$key]}" "$(occ config:app:get sendent "$key")"
done

declare -A PERIOD AFTER
existing_rules=$(api GET "$RETENTIONS" "${OCS[@]}")
for key in tag:removed tag:expired; do
	read -r amount unit after <<<"$(assistant_rule "$key")"
	for rule in $(json "ocs.data.[tagid=${TAG[$key]}].id" <<<"$existing_rules"); do
		api DELETE "$RETENTIONS/$rule" "${OCS[@]}" >/dev/null # left by an earlier run
	done
	api POST "$RETENTIONS" "${OCS[@]}" -H 'Content-Type: application/json' \
		-d "{\"tagid\":${TAG[$key]},\"timeamount\":$amount,\"timeunit\":$unit,\"timeafter\":$after}" >/dev/null
	PERIOD[$key]=$(period_days "$amount" "$unit")
	AFTER[$key]=$after
	units=(days weeks months years)
	echo "  rule: ${TAG_NAME[$key]} deleted $amount ${units[$unit]} after $([ "$after" = 1 ] && echo 'the modification time' || echo 'the upload time, or else the modification time')"
done
rules=$(api GET "$RETENTIONS" "${OCS[@]}")
for key in tag:removed tag:expired; do
	check "files_retention has a rule for ${TAG_NAME[$key]}" 1 "$(json "ocs.data.[tagid=${TAG[$key]}].id" <<<"$rules" | wc -l | tr -d ' ')"
done

# ---------------------------------------------------------------- 2. share

# name | kind | tagged | what happens to the share | age when retention runs | expected
SCENARIOS="
expired_recent  folder yes expire  recent kept
expired_old     folder yes expire  old    deleted
removed_recent  folder yes remove  recent kept
removed_old     folder yes remove  old    deleted
untagged        folder no  expire  old    kept
reshared        folder yes reshare old    kept
still_shared    folder yes partial old    kept
"
# Files shared on their own carry an upload time, and folders created over
# WebDAV don't. With timeafter=0 (upload time) files_retention counts from the
# upload, so such a file can go right after its share expires. That's outside
# the folder flow, so these only run when both rules count from the mtime.
if [ "${AFTER[tag:removed]}" = 1 ] && [ "${AFTER[tag:expired]}" = 1 ]; then
	SCENARIOS+="
file_expired_recent file yes expire recent kept
file_expired_old    file yes expire old    deleted
file_removed_recent file yes remove recent kept
"
else
	SKIPPED_FILES=1
fi

section "2. Share, then expire or remove the shares"

declare -A ID SHARE
TOMORROW=$(date -d tomorrow +%F)
YESTERDAY="$(date -d yesterday +%F) 00:00:00"

while read -r name kind tagged mode _; do
	[ -n "$name" ] || continue
	path="$RUN-$name"
	if [ "$kind" = folder ]; then
		api MKCOL "$DAV/$path" >/dev/null
		upload "$path/attachment.pdf" "attachment of $path"
	else
		path="$path.pdf"
		upload "$path" "attachment $path"
	fi
	ID[$name]=$(file_id "$path")
	[ -n "${ID[$name]}" ] || die "no file id for $path"
	if [ "$tagged" = yes ]; then
		api PUT "$NC_URL/remote.php/dav/systemtags-relations/files/${ID[$name]}/${TAG[tag:upload]}" >/dev/null
	fi
	# uploaded long ago (a file also keeps its upload time; a folder has none)
	set_mtime "${ID[$name]}" 200
	if [ "$kind" = file ]; then
		sql "UPDATE *PREFIX*filecache_extended SET upload_time = ? WHERE fileid = ?" "$(days_ago 200)" "${ID[$name]}"
	fi
	case $mode in
		remove) SHARE[$name]=$(share "$path") ;;
		partial) # one share expires, another stays
			SHARE[$name]=$(share "$path" "$TOMORROW")
			share "$path" >/dev/null ;;
		*) SHARE[$name]=$(share "$path" "$TOMORROW") ;;
	esac
done <<<"$SCENARIOS"

while read -r name _ _ mode _; do
	case $mode in expire | reshare | partial)
		sql "UPDATE *PREFIX*share SET expiration = ? WHERE id = ?" "$YESTERDAY" "${SHARE[$name]}" ;;
	esac
done <<<"$SCENARIOS"
run_jobs 'OCA\Files_Sharing\ExpireSharesJob'
while read -r name _ _ mode _; do
	if [ "$mode" = remove ]; then
		api DELETE "$SHARES/${SHARE[$name]}" "${OCS[@]}" >/dev/null
	fi
done <<<"$SCENARIOS"

while read -r name _ tagged mode _; do
	[ -n "$name" ] || continue
	case $tagged/$mode in
		yes/expire | yes/reshare) want=expired restarted=yes ;;
		yes/remove) want=removed restarted=yes ;;
		*) want=none restarted=no ;; # untagged, or a share is left
	esac
	check "$name: retention tag" "$want" "$(retention_tags "${ID[$name]}")"
	age=$(mtime_age_days "${ID[$name]}")
	if [ "$restarted" = yes ]; then
		check "$name: countdown restarted (mtime $age days ago)" yes "$([ "$age" -le 2 ] && echo yes || echo no)"
	else
		check "$name: mtime left alone ($age days ago)" yes "$([ "$age" -ge 199 ] && echo yes || echo no)"
	fi
done <<<"$SCENARIOS"

# Sharing again must take the folder out of retention
share "$RUN-reshared" >/dev/null
check "reshared: retention tag after sharing again" none "$(retention_tags "${ID[reshared]}")"

# ---------------------------------------------------------------- 3. retention

section "3. Let the retention periods pass and run the RetentionJob"

while read -r name _ _ mode when _; do
	if [ "$when" = old ]; then
		# just past the period of the rule that applies
		if [ "$mode" = remove ]; then days=${PERIOD[tag:removed]}; else days=${PERIOD[tag:expired]}; fi
		set_mtime "${ID[$name]}" $((days + 1))
	fi
done <<<"$SCENARIOS"

run_jobs 'OCA\Files_Retention\BackgroundJob\RetentionJob'

while read -r name kind _ _ when expected; do
	[ -n "$name" ] || continue
	path="$RUN-$name"
	[ "$kind" = file ] && path="$path.pdf"
	case $(http_status "$path") in
		207) actual=kept ;;
		404) actual=deleted ;;
		*) actual="status $(http_status "$path")" ;;
	esac
	check "$name ($when): $expected" "$expected" "$actual"
	if [ "$expected" = deleted ]; then
		check "$name: in the trash bin" yes "$(in_trash "$path")"
	fi
done <<<"$SCENARIOS"

# ---------------------------------------------------------------- result

if [ -n "${SKIPPED_FILES:-}" ]; then
	printf '\n  \033[33mSKIP\033[0m files shared on their own: the assistant creates rules with timeafter=0\n'
fi
echo
if [ "$FAILURES" -gt 0 ]; then
	echo "$FAILURES check(s) failed"
	exit 1
fi
echo "All checks passed"
