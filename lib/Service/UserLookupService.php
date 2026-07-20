<?php

/**
 * @copyright Copyright (c) 2026 Sendent B.V.
 *
 * @author Sendent B.V. <info@sendent.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\Sendent\Service;

use OC\KnownUser\KnownUserService;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IUser;
use OCP\IUserManager;

/**
 * Resolves email addresses to Nextcloud accounts in bulk.
 *
 * This is a bulk, exact-match counterpart to the collaborators autocomplete /
 * sharee search APIs. It relies on IUserManager::getByEmail(), a direct indexed
 * lookup against the accounts table, instead of the collaborator search pipeline
 * (which fans out across plugins, contacts and cloud-id resolution per term and
 * sits behind the OCS brute-force rate limiter). Exact matching is all we need,
 * so the cheaper primitive is also the faithful one.
 *
 * Because we bypass that pipeline we must re-apply, by hand, the same privacy
 * gates it would apply to an EXACT match. Mirrored from core's UserPlugin /
 * MailPlugin (verified against stable30):
 *   - shareapi_restrict_user_enumeration_full_match (+ _email): gate exact
 *     email matching entirely.
 *   - shareapi_only_share_with_group_members (+ _exclude_group_list): the match
 *     must share a non-excluded group with the caller.
 *   - shareapi_restrict_user_enumeration_to_phone: the match must be a "known
 *     user" of the caller (phonebook match).
 * Settings that do NOT constrain exact matches are deliberately ignored:
 * shareapi_allow_share_dialog_user_enumeration and
 * shareapi_restrict_user_enumeration_to_group (both affect partial results only).
 *
 * NOTE: KnownUserService lives in OC\ (a non-public API); this coupling is the
 * accepted trade-off for keeping the fast getByEmail path instead of reusing the
 * collaborator pipeline. Re-verify it on each Nextcloud major upgrade.
 */
class UserLookupService {

	/** Backend class name used by the nextcloud/guests app. */
	private const GUESTS_BACKEND = 'Guests';

	private IUserManager $userManager;
	private IConfig $config;
	private IGroupManager $groupManager;
	private KnownUserService $knownUserService;

	public function __construct(
		IUserManager $userManager,
		IConfig $config,
		IGroupManager $groupManager,
		KnownUserService $knownUserService,
	) {
		$this->userManager = $userManager;
		$this->config = $config;
		$this->groupManager = $groupManager;
		$this->knownUserService = $knownUserService;
	}

	/**
	 * Resolve a list of email addresses to accounts, from the perspective of
	 * $callerId (whose privacy scope governs what may be resolved).
	 *
	 * @param string[] $emails
	 * @return array<string, array{userId: string, type: string}|null> keyed by
	 *                                                                 the exact input email; value is null when no account is resolvable.
	 */
	public function resolve(array $emails, string $callerId): array {
		// Honour the admin's exact-match enumeration privacy settings, exactly as
		// the autocomplete/sharee UserPlugin does: a full (exact) email match is
		// only allowed when both the master full-match switch and its email
		// sub-key are enabled (both default 'yes'). Note that disabling general
		// enumeration (shareapi_allow_share_dialog_user_enumeration) does NOT
		// block exact matches, so we deliberately do not gate on it.
		$emailLookupAllowed
			= $this->config->getAppValue('core', 'shareapi_restrict_user_enumeration_full_match', 'yes') === 'yes'
			&& $this->config->getAppValue('core', 'shareapi_restrict_user_enumeration_full_match_email', 'yes') === 'yes';

		$onlyGroupMembers = $this->config->getAppValue('core', 'shareapi_only_share_with_group_members', 'no') === 'yes';
		$restrictToPhone = $this->config->getAppValue('core', 'shareapi_restrict_user_enumeration_to_phone', 'no') === 'yes';

		$caller = $this->userManager->get($callerId);

		// Caller's group set (minus the admin's exclude list), computed once.
		$callerGroups = [];
		if ($onlyGroupMembers && $caller !== null) {
			$callerGroups = $this->groupManager->getUserGroupIds($caller);
			$excluded = json_decode(
				$this->config->getAppValue('core', 'shareapi_only_share_with_group_members_exclude_group_list', ''),
				true,
			);
			if (is_array($excluded)) {
				$callerGroups = array_diff($callerGroups, $excluded);
			}
		}

		$result = [];
		foreach ($emails as $email) {
			if (!is_string($email)) {
				continue;
			}
			$email = trim($email);
			if ($email === '') {
				continue;
			}

			// Preserve the exact string the caller sent so they can match back.
			$result[$email] = null;
			if (!$emailLookupAllowed) {
				continue;
			}

			$user = $this->findAccount($email);
			if ($user === null) {
				continue;
			}
			if (!$this->isResolvableBy($user, $callerId, $caller, $onlyGroupMembers, $callerGroups, $restrictToPhone)) {
				continue;
			}

			$result[$email] = [
				'userId' => $user->getUID(),
				'type' => $this->typeOf($user),
			];
		}

		return $result;
	}

	private function findAccount(string $email): ?IUser {
		// Primary path: matches the account's system email address across every
		// user backend, including guests (the guests app stores the guest's email
		// via setSystemEMailAddress(), so getByEmail() finds them too).
		$users = $this->userManager->getByEmail($email);
		$user = $users[0] ?? null;

		// Fallback for legacy guest accounts whose uid IS the email address but
		// whose system email may not be populated. Only trusted when the matched
		// account is actually a guest, to avoid resolving on an unrelated uid.
		if ($user === null) {
			$byUid = $this->userManager->get($email);
			if ($byUid !== null && $byUid->getBackendClassName() === self::GUESTS_BACKEND) {
				$user = $byUid;
			}
		}

		return $user;
	}

	/**
	 * Re-applies the group-members / phone gates that the collaborator pipeline
	 * would apply to an exact match. The caller can always resolve their own
	 * account (autocomplete never gates the current user).
	 *
	 * @param string[] $callerGroups already excludes the admin's exclude list
	 */
	private function isResolvableBy(
		IUser $user,
		string $callerId,
		?IUser $caller,
		bool $onlyGroupMembers,
		array $callerGroups,
		bool $restrictToPhone,
	): bool {
		if ($user->getUID() === $callerId) {
			return true;
		}

		if ($onlyGroupMembers) {
			// Mirrors UserPlugin: a shared, non-excluded group is required. This
			// is the dominant filter — when it is active, core does not fall back
			// to the phone/known-user candidate set for exact matches.
			if ($caller === null) {
				return false;
			}
			$common = array_intersect($callerGroups, $this->groupManager->getUserGroupIds($user));
			return $common !== [];
		}

		if ($restrictToPhone) {
			// Mirrors the phone-restricted candidate set (searchKnownUsersBy...):
			// only users known to the caller are enumerable.
			return $this->knownUserService->isKnownToUser($callerId, $user->getUID());
		}

		return true;
	}

	private function typeOf(IUser $user): string {
		return $user->getBackendClassName() === self::GUESTS_BACKEND ? 'guest' : 'user';
	}
}
