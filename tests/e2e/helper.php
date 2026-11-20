<?php

declare(strict_types=1);

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

/*
 * Small CLI helpers for retention-flow.sh, so the script needs nothing but
 * bash, curl and the PHP that runs Nextcloud.
 *
 *   php helper.php sql "<SQL with *PREFIX* and ? placeholders>" [params...]
 *       Runs a query on the Nextcloud database. A SELECT prints one row per
 *       line, columns separated by tabs.
 *
 *   php helper.php json <path>   < document.json
 *       Prints the values at <path>, one per line. The path is dot separated:
 *       a key, '*' for every list element, or '[key=value]' to keep the list
 *       elements whose key equals value. Example: 'ocs.data.[tagid=3].id'.
 */

function sql(array $args): void {
	$ncRoot = getenv('NC_ROOT') ?: __DIR__ . '/../../../..';
	require_once $ncRoot . '/lib/base.php';

	$query = array_shift($args);
	$db = \OCP\Server::get(\OCP\IDBConnection::class);
	$result = $db->prepare($query)->execute($args);

	if (stripos(ltrim($query), 'select') !== 0) {
		return;
	}
	foreach ($result->fetchAll(\PDO::FETCH_NUM) as $row) {
		echo implode("\t", array_map('strval', $row)), "\n";
	}
}

function json(string $path): void {
	$values = [json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR)];

	foreach (explode('.', $path) as $segment) {
		$next = [];
		foreach ($values as $value) {
			if (!is_array($value)) {
				continue;
			}
			if ($segment === '*') {
				array_push($next, ...array_values($value));
			} elseif (preg_match('/^\[(\w+)=(.*)\]$/', $segment, $m)) {
				foreach ($value as $item) {
					if (is_array($item) && array_key_exists($m[1], $item) && (string)$item[$m[1]] === $m[2]) {
						$next[] = $item;
					}
				}
			} elseif (array_key_exists($segment, $value)) {
				$next[] = $value[$segment];
			}
		}
		$values = $next;
	}

	foreach ($values as $value) {
		if (is_bool($value)) {
			$value = $value ? 'true' : 'false';
		} elseif (is_array($value)) {
			$value = json_encode($value);
		}
		echo (string)$value, "\n";
	}
}

$command = $argv[1] ?? '';
if ($command === 'sql') {
	sql(array_slice($argv, 2));
} elseif ($command === 'json') {
	json($argv[2] ?? '');
} else {
	fwrite(STDERR, "usage: php helper.php sql <query> [params...] | json <path>\n");
	exit(2);
}
