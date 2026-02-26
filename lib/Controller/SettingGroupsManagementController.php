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

namespace OCA\Sendent\Controller;

use OCA\Sendent\Db\SettingGroupValueMapper;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Services\IAppConfig;
use OCP\IRequest;

class SettingGroupsManagementController extends ApiController {

	/** @var IAppConfig */
	private $appConfig;

	/** @var SettingGroupValueMapper */
	private $mapper;

	public function __construct($appName, IAppConfig $appConfig, IRequest $request, SettingGroupValueMapper $mapper) {
		parent::__construct($appName, $request);
		$this->appConfig = $appConfig;
		$this->mapper = $mapper;
	}

	/**
	 * Admin-only: updates group configuration and cleans up deleted group settings.
	 * Note: license data for deleted groups is not yet cleaned up.
	 */
	public function update($newSendentGroups) {

		// Delete its settings when a group was deleted
		$sendentGroups = $this->appConfig->getAppValue('sendentGroups', '');
		$sendentGroups = $sendentGroups !== '' ? json_decode($sendentGroups) : [];
		$deletedGroup = array_diff($sendentGroups, $newSendentGroups);
		if (count($deletedGroup) > 0) {
			$this->mapper->deleteSettingsForGroup($deletedGroup[array_keys($deletedGroup)[0]]);
		}

		// Saves new groups list
		$this->appConfig->setAppValue('sendentGroups', json_encode($newSendentGroups));
		return;
	}
}
