<?php

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

	public function update($newSendentGroups) {

		// Delete its settings when a group was deleted
		$sendentGroups = $this->appConfig->getAppValue('sendentGroups', '');
		$sendentGroups = $sendentGroups !== '' ? json_decode($sendentGroups) : [];
		$deletedGroup = array_diff($sendentGroups, $newSendentGroups);
		if (count($deletedGroup) > 0) {
			$this->mapper->deleteSettingsForGroup($deletedGroup[array_keys($deletedGroup)[0]]);
			// TODO: We should probably remove the license too here
		}

		// Saves new groups list
		$this->appConfig->setAppValue('sendentGroups', json_encode($newSendentGroups));
		return;
	}
}
