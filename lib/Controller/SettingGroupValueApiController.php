<?php

namespace OCA\Sendent\Controller;

use Exception;
use OCA\Sendent\Db\SettingGroupValue;
use OCA\Sendent\Db\SettingGroupValueMapper;
use OCA\Sendent\Service\SendentFileStorageManager;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Services\IAppConfig;

use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUserManager;

class SettingGroupValueApiController extends ApiController {
	private $appConfig;
	private $mapper;
	private $FileStorageManager;
	private $groupManager;
	private $userId;
	private $userManager;

	public function __construct(IAppConfig $appConfig, IRequest $request, SettingGroupValueMapper $mapper,
		SendentFileStorageManager $FileStorageManager, IGroupManager $groupManager, IUserManager $userManager, $userId) {
		parent::__construct(
			'sendent',
			$request,
			'PUT, POST, GET, DELETE, PATCH',
			'Authorization, Content-Type, Accept',
			1728000);
		$this->appConfig = $appConfig;
		$this->mapper = $mapper;
		$this->FileStorageManager = $FileStorageManager;
		$this->groupManager = $groupManager;
		$this->userId = $userId;
		$this->userManager = $userManager;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * Gets settings for a specific user
	 *
	 * @param int $templateId
	 *
	 * @return DataResponse
	 */
	public function index(?int $templateId = null): DataResponse {

		// Gets groups for which specific settings and/or license are defined
		// Groups are ordered from highest priority to lowest
		$sendentGroups = $this->appConfig->getAppValue('sendentGroups', '');
		$sendentGroups = $sendentGroups !== '' ? json_decode($sendentGroups) : [];

		// Gets user groups
		$user = $this->userManager->get($this->userId);
		$userGroups = $this->groupManager->getUserGroups($user);
		$userGroups = array_map(function ($group) {
			return $group->getGid();
		}, $userGroups);

		// Gets user groups that are sendentGroups
		$userSendentGroups = array_intersect($sendentGroups, $userGroups);

		// Returns settings for 1st matching group
		if (count($userSendentGroups)) {
			return $this->getForNCGroup($userSendentGroups[array_keys($userSendentGroups)[0]], $templateId, true);
		} else {
			return $this->getForNCGroup('', $templateId, true);
		}

	}
	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * Gets settings for a specific user
	 *
	 * @param int $templateId
	 *
	 * @return DataResponse
	 */
	public function byTemplate(?int $templateId = null): DataResponse {

		if (!isset($templateId) || $templateId == null) {
			return $this->index();
		}
		// Gets groups for which specific settings and/or license are defined
		// Groups are ordered from highest priority to lowest
		$sendentGroups = $this->appConfig->getAppValue('sendentGroups', '');
		$sendentGroups = $sendentGroups !== '' ? json_decode($sendentGroups) : [];

		// Gets user groups
		$user = $this->userManager->get($this->userId);
		$userGroups = $this->groupManager->getUserGroups($user);
		$userGroups = array_map(function ($group) {
			return $group->getGid();
		}, $userGroups);

		// Gets user groups that are sendentGroups
		$userSendentGroups = array_intersect($sendentGroups, $userGroups);

		// Returns settings for 1st matching group
		if (count($userSendentGroups)) {
			return $this->getForNCGroup($userSendentGroups[array_keys($userSendentGroups)[0]], $templateId, true);
		} else {
			return $this->getForNCGroup('', $templateId, true);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * Get settings for the default group
	 *
	 * @return DataResponse
	 */
	public function getForDefaultGroup(): DataResponse {
		return $this->getForNCGroup();
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * Gets settings for group $ncgroup
	 *
	 * @param string $ncgroup
	 * @param int $templateId
	 * @return DataResponse
	 */
	public function getForNCGroup(string $ncgroup = '', ?int $templateId = null, bool $wantUserSettings = false): DataResponse {

		// Gets settings for group
		$list = $this->mapper->findSettingsForNCGroup($ncgroup);
		foreach ($list as $result) {
			if ($this->valueIsSettingGroupValueFilePath($result->getValue()) !== false) {
				$result->setValue($this->FileStorageManager->getContent($result->getGroupid(), $result->getSettingkeyid(), $ncgroup));
			}
		}

		// Merges settings from default group
		if ($ncgroup !== '') {
			// Gets a list of all settings defined for the group
			$settingkeyidList = array_map(function ($setting) {
				return $setting->getSettingkeyid();
			}, $list);

			// Gets settings for the default group
			$defaults = $this->mapper->findSettingsForNCGroup();

			// Merges group settings with default group settings
			foreach ($defaults as $result) {
				// Initializes value
				if ($this->valueIsSettingGroupValueFilePath($result->getValue()) !== false) {
					$result->setValue($this->FileStorageManager->getContent($result->getGroupid(), $result->getSettingkeyid()));
				}
				// Merges group settings
				if (!in_array($result->getSettingkeyid(), $settingkeyidList)) {
					// Setting is not defined for group, let's set the one from the default group
					array_push($list, $result);
				} elseif (in_array($result->getSettingkeyid(), [0, 2])) {
					// multivalue settings must be merged with the ones from the default group
					$list = array_map(function ($setting) use ($result, $wantUserSettings) {
						if ($setting->getSettingkeyid() === $result->getSettingkeyid()) {
							if ($wantUserSettings) {
								$setting->setValue($result->getValue() . ';' . $setting->getValue());
							} else {
								$setting->setValue([
									'defaultSetting' => $result->getValue(),
									'groupSetting' => $setting->getValue()
								]);
							}
						}
						return $setting;
					}, $list);
				}
			}
		}
		if (isset($templateId) && $templateId != null) {
			$list = array_filter($list, function ($objf) use ($templateId) {
				if ($objf->getGroupid() !== null) {
					return $objf->getGroupid() == $templateId;
				}
			});
		}

		return new DataResponse($list);
	}

	/**
	 * @NoAdminRequired
	 *
	 * @NoCSRFRequired
	 *
	 * @return DataResponse
	 */
	public function theming(): DataResponse {
		$list = $this->mapper->findAll();
		foreach ($list as $result) {
			if ($this->valueIsSettingGroupValueFilePath($result->getValue()) !== false) {
				$result->setValue($this->FileStorageManager->getContent($result->getGroupid(), $result->getSettingkeyid()));
			}
		}
		return new DataResponse($list);
	}

	/**
	 * @NoAdminRequired
	 *
	 * @NoCSRFRequired
	 *
	 * @param int $id
	 *
	 * @return DataResponse
	 */
	public function show(int $id): DataResponse {
		try {
			$result = $this->mapper->find($id);
			if ($this->valueIsSettingGroupValueFilePath($result->getValue()) !== false) {
				$result->setValue($this->FileStorageManager->getContent($result->getGroupid(), $result->getSettingkeyid()));
			}
			return new DataResponse($result);
		} catch (Exception $e) {
			return new DataResponse([], Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param int $settingkeyid
	 *
	 * @return DataResponse
	 */
	public function showBySettingKeyId(int $settingkeyid, string $ncgroup = ''): DataResponse {
		try {
			$result = $this->mapper->findBySettingKeyId($settingkeyid, $ncgroup);
			if ($this->valueIsSettingGroupValueFilePath($result->getValue()) !== false) {
				$result->setValue($this->FileStorageManager->getContent($result->getGroupid(), $result->getSettingkeyid()));
			}
			return new DataResponse($result);
		} catch (Exception $e) {
			return new DataResponse([], Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * @NoAdminRequired
	 *
	 * @NoCSRFRequired
	 *
	 * @PublicPage
	 *
	 * @param int $groupid
	 *
	 * @return DataResponse
	 */
	public function findByGroupId(int $groupid): DataResponse {
		try {
			if ($groupid == 1) {
				$result = $this->mapper->findByGroupId($groupid);
				foreach ($result as $item) {
					if ($this->valueIsSettingGroupValueFilePath($item->getValue()) !== false) {
						$item->setValue($this->FileStorageManager->getContent($item->getGroupid(), $item->getSettingkeyid()));
					}
				}
				return new DataResponse($result);
			} else {
				return new DataResponse([], Http::STATUS_NOT_FOUND);
			}
		} catch (Exception $e) {
			return new DataResponse([], Http::STATUS_NOT_FOUND);
		}
	}
	/**
	 * @NoAdminRequired
	 *
	 * @NoCSRFRequired
	 *
	 * @PublicPage
	 *
	 * @param int $templateId
	 *
	 * @return DataResponse
	 */
	public function findByTemplateId(int $templateId): DataResponse {
		try {

			$result = $this->mapper->findByGroupId($templateId);
			foreach ($result as $item) {
				if ($this->valueIsSettingGroupValueFilePath($item->getValue()) !== false) {
					$item->setValue($this->FileStorageManager->getContent($item->getGroupid(), $item->getSettingkeyid(), $item->getNcgroup()));
				}
			}
			return new DataResponse($result);
		} catch (Exception $e) {
			return new DataResponse([], Http::STATUS_NOT_FOUND);
		}
	}

	private function valueIsSettingGroupValueFilePath($value): bool {
		if (strpos($value, 'settinggroupvaluefile') !== false) {
			return true;
		}
		return false;
	}

	private function valueSizeForDb(string $value): bool {
		return strlen($value) < 255 !== false;
	}

	/**
	 * @param int $settingkeyid
	 * @param int $groupid
	 * @param string $value
	 * @param string $group
	 * @return DataResponse
	 */
	public function create(int $settingkeyid, int $groupid, string $value, string $group = '') {
		if ($this->valueSizeForDb($value) === false) {
			$value = $this->FileStorageManager->writeTxt($groupid, $settingkeyid, $value, $group);
		}
		$SettingGroupValue = new SettingGroupValue();
		$SettingGroupValue->setSettingkeyid($settingkeyid);
		$SettingGroupValue->setGroupid($groupid);
		$SettingGroupValue->setValue($value);
		$SettingGroupValue->setNcgroup($group);
		$result = $this->mapper->insert($SettingGroupValue);
		return $this->showBySettingKeyId($settingkeyid, $group);
	}

	/**
	 * @param int $id
	 * @param int $settingkeyid
	 * @param int $groupid
	 * @param string $value
	 * @param string $group
	 * @return DataResponse
	 */
	public function update(int $id, int $settingkeyid, int $groupid, string $value, string $group = '') {
		try {
			if ($this->valueSizeForDb($value) === false) {
				$value = $this->FileStorageManager->writeTxt($groupid, $settingkeyid, $value, $group);
			}
			$SettingGroupValue = $this->mapper->find($id, $group);
			$SettingGroupValue->setSettingkeyid($settingkeyid);
			$SettingGroupValue->setGroupid($groupid);
			$SettingGroupValue->setValue($value);
			$SettingGroupValue->setNcgroup($group);
			$result = $this->mapper->update($SettingGroupValue);
			return $this->showBySettingKeyId($settingkeyid, $group);
		} catch (Exception $e) {
			return new DataResponse([], Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * @param int $id
	 * @param string $ncgroup
	 *
	 * @return DataResponse
	 */
	public function destroy(int $id, string $ncgroup = ''): DataResponse {
		try {
			$SettingGroupValue = $this->mapper->find($id, $ncgroup);
		} catch (Exception $e) {
			return new DataResponse([], Http::STATUS_NOT_FOUND);
		}
		$this->mapper->delete($SettingGroupValue);
		if ($ncgroup === '') {
			return new DataResponse($SettingGroupValue);
		} else {
			// When we delete the setting of a group we want to get the corresponding default settting back
			return $this->showBySettingKeyId($id, '');
		}
	}
}
