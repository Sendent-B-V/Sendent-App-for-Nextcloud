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

use OCA\Sendent\Db\SettingGroupValue;
use OCA\Sendent\Db\SettingGroupValueMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Services\IAppConfig;
use OCP\IGroup;
use OCP\IGroupManager;
use OCP\IUserManager;
use Psr\Log\LoggerInterface;

/**
 * Centralizes the per-user sendent-group resolution previously duplicated in
 * SettingGroupValueApiController::index() and ::byTemplate(), plus the
 * group -> default-group fallback used to resolve a single setting's
 * effective value (a group-specific row wins when present, otherwise the
 * default-group row (ncgroup = '') applies, otherwise null). File-spilled
 * values are rehydrated via SendentFileStorageManager, using the ncgroup of
 * the row that actually served the value — not necessarily the requested
 * group, on a default-group fallback.
 *
 * Two deliberate behavior changes vs. the old inline duplicated code: an
 * unrecognized $userId and a malformed/non-array `sendentGroups` value now
 * both resolve to '' (the default group) instead of fataling with a
 * TypeError (from IGroupManager::getUserGroups(null) / array_intersect(null, ...)
 * respectively).
 */
class UserSettingsResolver {

	private IAppConfig $appConfig;
	private IGroupManager $groupManager;
	private IUserManager $userManager;
	private SettingGroupValueMapper $mapper;
	private SendentFileStorageManager $fileStorageManager;
	private LoggerInterface $logger;

	public function __construct(
		IAppConfig $appConfig,
		IGroupManager $groupManager,
		IUserManager $userManager,
		SettingGroupValueMapper $mapper,
		SendentFileStorageManager $fileStorageManager,
		LoggerInterface $logger,
	) {
		$this->appConfig = $appConfig;
		$this->groupManager = $groupManager;
		$this->userManager = $userManager;
		$this->mapper = $mapper;
		$this->fileStorageManager = $fileStorageManager;
		$this->logger = $logger;
	}

	/**
	 * The first configured sendent group (priority order, as configured via
	 * the `sendentGroups` app-config JSON array) that $userId is a member
	 * of. Returns '' (the default group) when the user matches none of the
	 * configured groups, when `sendentGroups` is empty/unset, or when
	 * $userId does not resolve to an existing account.
	 */
	public function sendentGroupFor(string $userId): string {
		$sendentGroups = $this->appConfig->getAppValue('sendentGroups', '');
		$sendentGroups = $sendentGroups !== '' ? json_decode($sendentGroups) : [];
		if (!is_array($sendentGroups) || $sendentGroups === []) {
			return '';
		}

		$user = $this->userManager->get($userId);
		if ($user === null) {
			return '';
		}

		$userGroups = array_map(
			static fn (IGroup $group): string => $group->getGid(),
			$this->groupManager->getUserGroups($user),
		);

		foreach ($sendentGroups as $sendentGroup) {
			$sendentGroup = (string)$sendentGroup;
			if (in_array($sendentGroup, $userGroups, true)) {
				return $sendentGroup;
			}
		}

		return '';
	}

	/**
	 * The effective value of setting $settingKeyId for $ncgroup: the
	 * group-specific row when one exists, otherwise the default group's row
	 * (ncgroup ''), otherwise null. File-spilled values are rehydrated via
	 * SendentFileStorageManager, passing the row's OWN ncgroup (which, on a
	 * default-group fallback, is '' — not the originally requested group).
	 */
	public function effectiveValue(int $settingKeyId, string $ncgroup): ?string {
		$row = $this->rowFor($settingKeyId, $ncgroup);
		if ($row === null && $ncgroup !== '') {
			$row = $this->rowFor($settingKeyId, '');
		}
		if ($row === null) {
			return null;
		}

		$value = $row->getValue();
		if (is_string($value) && strpos($value, 'settinggroupvaluefile') !== false) {
			try {
				$value = $this->fileStorageManager->getContent($row->getGroupid(), $row->getSettingkeyid(), $row->getNcgroup());
			} catch (\Throwable $e) {
				// getContent() only swallows its own NotFoundException; a storage
				// backend that throws NotPermittedException/LockedException etc.
				// must not 500 this endpoint (it is polled by email clients).
				$this->logger->warning(
					'Failed to rehydrate file-spilled value for setting {settingkeyid}.',
					['settingkeyid' => $row->getSettingkeyid(), 'exception' => $e],
				);
				$value = '';
			}
		}

		return $value;
	}

	private function rowFor(int $settingKeyId, string $ncgroup): ?SettingGroupValue {
		try {
			return $this->mapper->findBySettingKeyId($settingKeyId, $ncgroup);
		} catch (DoesNotExistException|MultipleObjectsReturnedException $e) {
			return null;
		}
	}
}
