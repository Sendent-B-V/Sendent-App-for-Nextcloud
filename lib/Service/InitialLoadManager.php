<?php

namespace OCA\Sendent\Service;

use Exception;
use OCA\Sendent\AppInfo\Application;
use OCA\Sendent\Db\SettingKey;
use OCA\Sendent\Db\SettingKeyMapper;
use OCA\Sendent\Db\SettingGroupValueMapper;
use OCA\Sendent\Db\SettingGroupValue;
use OCP\App\IAppManager;
use OCP\IConfig;
use OCP\PreConditionNotMetException;

class InitialLoadManager {
	private $SettingKeyMapper;
	private $SettingGroupValueMapper;
	private $SendentFileStorageManager;
	private $config;

	/** @var IAppManager */
	private $appManager;

	public function __construct(
		SettingKeyMapper $SettingKeyMapper,
		SettingGroupValueMapper $SettingGroupValueMapper,
		SendentFileStorageManager $SendentFileStorageManager,
		IConfig $config,
		IAppManager $appManager) {
		$this->SettingKeyMapper = $SettingKeyMapper;
		$this->SettingGroupValueMapper = $SettingGroupValueMapper;
		$this->SendentFileStorageManager = $SendentFileStorageManager;
		$this->config = $config;
		$this->appManager = $appManager;

		$this->checkUpdateNeeded115();
	}

	/**
	 * Return true if this is the first time a user is acessing their instance with deck enabled
	 *
	 * @param $userId
	 * @return bool
	 */
	public function checkUpdateNeeded115(): bool {
		$firstRun = $this->config->getAppValue('sendent', 'firstRunAppVersion');

		if ($firstRun !== '1.3.2') {
			try {
				$this->runInitialLoadTasks115();
				$this->config->setAppValue('sendent', 'firstRunAppVersion', '1.3.2');
			} catch (PreConditionNotMetException $e) {
				return false;
			}
			return true;
		}

		return false;
	}
	private function runInitialLoadTasks115(): void {
		try {
			if ($this->SettingKeyMapper->settingKeyCount("20") < 1) {
				$this->initialLoading();
			}
			if ($this->SettingKeyMapper->settingKeyCount("23") < 1) {
				$this->addSendmode();
			}
			if ($this->SettingKeyMapper->settingKeyCount("30") < 1) {
				$this->addHtmlpasswordsnippet();
			}
			if ($this->SettingKeyMapper->settingKeyCount("31") < 1) {
				$this->addPopupExternalMail();
			}
			if ($this->SettingKeyMapper->settingKeyCount("81") < 1 || $this->showNameBySettingKeyId("81") !== "GeneralIconColor"
			|| $this->showTemplateBySettingKeyId("81") !== 1 || $this->showGroupIdBySettingKeyId("81") !== 1) {
				$this->addAdvancedTheming();
			}
			if ($this->SettingKeyMapper->settingKeyCount("94") < 1 || $this->showNameBySettingKeyId("94") !== "TaskpaneActivityTrackerFontColor"
			|| $this->showTemplateBySettingKeyId("94") !== 1 || $this->showGroupIdBySettingKeyId("94") !== 1) {
				$this->addAdvancedThemingUpdate();
			}
			if ($this->SettingKeyMapper->settingKeyCount("98") < 1 || $this->showNameBySettingKeyId("98") !== "ButtonSecondaryIconColor"
			|| $this->showTemplateBySettingKeyId("98") !== 1 || $this->showGroupIdBySettingKeyId("98") !== 1) {
				$this->addAdvancedThemingUpdate2();
			}
			if ($this->SettingKeyMapper->settingKeyCount("99") < 1 || $this->showNameBySettingKeyId("99") !== "TaskpaneSecureMailControlColor"
			|| $this->showTemplateBySettingKeyId("99") !== 1 || $this->showGroupIdBySettingKeyId("99") !== 1) {
				$this->addAdvancedThemingUpdate3();
			}
			if ($this->SettingKeyMapper->settingKeyCount("100") < 1 || $this->showNameBySettingKeyId("100") !== "DialogFooterBackgroundColor"
			|| $this->showTemplateBySettingKeyId("100") !== 1 || $this->showGroupIdBySettingKeyId("100") !== 1) {
				$this->addAdvancedThemingUpdate4();
			}
			if ($this->SettingKeyMapper->settingKeyCount("104") < 1) {
				$this->addAdvancedThemingNameUpdate();
			}
			if ($this->SettingKeyMapper->settingKeyCount("201") < 1) {
				$this->addTalkSettingUpdate();
			}
			if ($this->SettingKeyMapper->settingKeyCount("202") < 1) {
				$this->addTalkEnabledUpdate();
			}
			if ($this->SettingKeyMapper->settingKeyCount("301") < 1) {
				$this->addGuestAccountSettings();
			}
			$this->fixPaths();
			$this->fixSnippets();
		} catch (Exception $e) {
		}
	}

	private function fixPaths(): void {
		try {
			$filepath = $this->showBySettingKeyId(8);
			$folderpath = $this->showBySettingKeyId(7);
			$securemailpath = $this -> showBySettingKeyId(24);
			if (!is_null($folderpath)) {
				if ($folderpath->getValue() === '') {
					$this->update($folderpath->getId(), $folderpath->getSettingkeyid(), $folderpath->getGroupid(), "/Outlook/Public-Share/");
				}
			}
			if (!is_null($filepath)) {
				if ($filepath->getValue() === '') {
					$this->update($filepath->getId(), $filepath->getSettingkeyid(), $filepath->getGroupid(), "/Outlook/Upload-Share/");
				}
			}
			if (!is_null($securemailpath)) {
				if ($securemailpath->getValue() === '') {
					$this->update($securemailpath->getId(), $securemailpath->getSettingkeyid(), $securemailpath->getGroupid(), "/Outlook/SecureMail-Share/");
				}
			}
		} catch (Exception $exception) {
		}
	}

	private function fixSnippets(): void {
		try {
			$filepath = $this->showBySettingKeyId(10);
			$folderpath = $this->showBySettingKeyId(9);
			$securemailpath = $this->showBySettingKeyId(12);
			$passwordhtmlsnippet = $this->showBySettingKeyId(30);
			$guestaccounthtmlsnippet = $this->showBySettingKeyId(302);
			if (!is_null($folderpath)) {
				if ($folderpath->getValue() === '') {
					$this->update($folderpath->getId(), $folderpath->getSettingkeyid(), $folderpath->getGroupid(), $this->getsharefolderhtml());
				}
			}
			if (!is_null($filepath)) {
				if ($filepath->getValue() === '') {
					$this->update($filepath->getId(), $filepath->getSettingkeyid(), $filepath->getGroupid(), $this->getsharefilehtml());
				}
			}
			if (!is_null($securemailpath)) {
				if ($securemailpath->getValue() === '') {
					$this->update($securemailpath->getId(), $securemailpath->getSettingkeyid(), $securemailpath->getGroupid(), $this->getsecuremailhtml());
				}
			}
			if (!is_null($passwordhtmlsnippet)) {
				if ($passwordhtmlsnippet->getValue() === '') {
					$this->update($passwordhtmlsnippet->getId(), $passwordhtmlsnippet->getSettingkeyid(), $passwordhtmlsnippet->getGroupid(), $this->gethtmlpasswordsnippet());
				}
			}
			if (!is_null($guestaccounthtmlsnippet)) {
				if ($guestaccounthtmlsnippet->getValue() === '') {
					$this->update($guestaccounthtmlsnippet->getId(), $guestaccounthtmlsnippet->getSettingkeyid(), $guestaccounthtmlsnippet->getGroupid(), $this->getguestaccountshtml());
				}
			}
		} catch (Exception $exception) {
		}
	}

	public function addPopupExternalMail(): void {
		$this->createKey("31", "attachmentdomainexceptionsexternalpopup", "0", "select-one");
		$this->createGroupValue("0", "31", "False");
	}

	public function addHtmlpasswordsnippet(): void {
		$this->createKey("30", "htmlsnippetpassword", "0", "textarea");
		$this->createGroupValue("0", "30", $this->gethtmlpasswordsnippet());
	}
	public function addTalkSettingUpdate(): void {
		$this->createKey("201", "generatetalkpassword", "0", "textarea");
		$this->createGroupValue("0", "201", "False");
	}
	public function addTalkEnabledUpdate(): void {
		$this->createKey("202", "talkenabled", "0", "select-one");
		$this->createGroupValue("0", "202", "True");
	}
	public function addAdvancedTheming(): void {
		if (!is_null($this->showNameBySettingKeyId("81"))) {
			$this->updateKey("81", "GeneralIconColor", "1", "text");
			$value = $this->showBySettingKeyId(81);
			$this->update($value->getId(), 81, 1, $value->getValue());
		} else {
			$this->createKey("81", "GeneralIconColor", "1", "text");
			$this->createGroupValue("1", "81", "#151c62");
		}
		if (!is_null($this->showNameBySettingKeyId("82"))) {
			$this->updateKey("82", "DialogFooterIconBackgroundColor", "1", "text");
			$value = $this->showBySettingKeyId(82);
			$this->update($value->getId(), 82, 1, $value->getValue());
		} else {
			$this->createKey("82", "DialogFooterIconBackgroundColor", "1", "text");
			$this->createGroupValue("1", "82", "#FFFFFF");
		}
		if (!is_null($this->showNameBySettingKeyId("83"))) {
			$this->updateKey("83", "TaskpaneActivityTrackerColor", "1", "text");
			$value = $this->showBySettingKeyId(83);
			$this->update($value->getId(), 83, 1, $value->getValue());
		} else {
			$this->createKey("83", "TaskpaneActivityTrackerColor", "1", "text");
			$this->createGroupValue("1", "83", "#151c62");
		}
		if (!is_null($this->showNameBySettingKeyId("84"))) {
			$this->updateKey("84", "DialogHeaderColor", "1", "text");
			$value = $this->showBySettingKeyId(84);
			$this->update($value->getId(), 84, 1, $value->getValue());
		} else {
			$this->createKey("84", "DialogHeaderColor", "1", "text");
			$this->createGroupValue("1", "84", "#161c5e");
		}
		if (!is_null($this->showNameBySettingKeyId("85"))) {
			$this->updateKey("85", "ButtonPrimaryColor", "1", "text");
			$value = $this->showBySettingKeyId(85);
			$this->update($value->getId(), 85, 1, $value->getValue());
		} else {
			$this->createKey("85", "ButtonPrimaryColor", "1", "text");
			$this->createGroupValue("1", "85", "#151c62");
		}
		if (!is_null($this->showNameBySettingKeyId("86"))) {
			$this->updateKey("86", "ButtonPrimaryFontColor", "1", "text");
			$value = $this->showBySettingKeyId(86);
			$this->update($value->getId(), 86, 1, $value->getValue());
		} else {
			$this->createKey("86", "ButtonPrimaryFontColor", "1", "text");
			$this->createGroupValue("1", "86", "#FFFFFF");
		}
		if (!is_null($this->showNameBySettingKeyId("87"))) {
			$this->updateKey("87", "ButtonPrimaryHoverColor", "1", "text");
			$value = $this->showBySettingKeyId(87);
			$this->update($value->getId(), 87, 1, $value->getValue());
		} else {
			$this->createKey("87", "ButtonPrimaryHoverColor", "1", "text");
			$this->createGroupValue("1", "87", "#5d66cd");
		}
		if (!is_null($this->showNameBySettingKeyId("88"))) {
			$this->updateKey("88", "ButtonSecondaryColor", "1", "text");
			$value = $this->showBySettingKeyId(88);
			$this->update($value->getId(), 88, 1, $value->getValue());
		} else {
			$this->createKey("88", "ButtonSecondaryColor", "1", "text");
			$this->createGroupValue("1", "88", "#EDEDED");
		}
		if (!is_null($this->showNameBySettingKeyId("89"))) {
			$this->updateKey("89", "ButtonSecondaryHoverColor", "1", "text");
			$value = $this->showBySettingKeyId(89);
			$this->update($value->getId(), 89, 1, $value->getValue());
		} else {
			$this->createKey("89", "ButtonSecondaryHoverColor", "1", "text");
			$this->createGroupValue("1", "89", "#adadad");
		}
		if (!is_null($this->showNameBySettingKeyId("90"))) {
			$this->updateKey("90", "ButtonSecondaryFontColor", "1", "text");
			$value = $this->showBySettingKeyId(90);
			$this->update($value->getId(), 90, 1, $value->getValue());
		} else {
			$this->createKey("90", "ButtonSecondaryFontColor", "1", "text");
			$this->createGroupValue("1", "90", "#151c62");
		}
		if (!is_null($this->showNameBySettingKeyId("91"))) {
			$this->updateKey("91", "TaskpaneSecureMailColor", "1", "text");
			$value = $this->showBySettingKeyId(91);
			$this->update($value->getId(), 91, 1, $value->getValue());
		} else {
			$this->createKey("91", "TaskpaneSecureMailColor", "1", "text");
			$this->createGroupValue("1", "91", "#151C62");
		}
		if (!is_null($this->showNameBySettingKeyId("92"))) {
			$this->updateKey("92", "PopupBackgroundColor", "1", "text");
			$value = $this->showBySettingKeyId(92);
			$this->update($value->getId(), 92, 1, $value->getValue());
		} else {
			$this->createKey("92", "PopupBackgroundColor", "1", "text");
			$this->createGroupValue("1", "92", "#cfd2f1");
		}
		if (!is_null($this->showNameBySettingKeyId("93"))) {
			$this->updateKey("93", "GeneralFontColor", "1", "text");
			$value = $this->showBySettingKeyId(93);
			$this->update($value->getId(), 93, 1, $value->getValue());
		} else {
			$this->createKey("93", "GeneralFontColor", "1", "text");
			$this->createGroupValue("1", "93", "#FFFFFF");
		}
		if (!is_null($this->showNameBySettingKeyId("80"))) {
			$this->updateKey("80", "AdvancedThemingEnabled", "1", "text");
			$value = $this->showBySettingKeyId(80);
			$this->update($value->getId(), 80, 1, $value->getValue());
		} else {
			$this->createKey("80", "AdvancedThemingEnabled", "1", "select-one");
			$this->createGroupValue("1", "80", "false");
		}
	}

	public function addAdvancedThemingUpdate(): void {
		if (!is_null($this->showNameBySettingKeyId("94"))) {
			$this->updateKey("94", "TaskpaneActivityTrackerFontColor", "1", "text");
			$value = $this->showBySettingKeyId(94);
			$this->update($value->getId(), 94, 1, $value->getValue());
		} else {
			$this->createKey("94", "TaskpaneActivityTrackerFontColor", "1", "text");
			$this->createGroupValue("1", "94", "#FFFFFF");
		}
		if (!is_null($this->showNameBySettingKeyId("95"))) {
			$this->updateKey("95", "DialogHeaderFontColor", "1", "text");
			$value = $this->showBySettingKeyId(95);
			$this->update($value->getId(), 95, 1, $value->getValue());
		} else {
			$this->createKey("95", "DialogHeaderFontColor", "1", "text");
			$this->createGroupValue("1", "95", "#FFFFFF");
		}
		if (!is_null($this->showNameBySettingKeyId("96"))) {
			$this->updateKey("96", "TaskpaneSecureMailFontColor", "1", "text");
			$value = $this->showBySettingKeyId(96);
			$this->update($value->getId(), 96, 1, $value->getValue());
		} else {
			$this->createKey("96", "TaskpaneSecureMailFontColor", "1", "text");
			$this->createGroupValue("1", "96", "#FFFFFF");
		}
	}

	public function addAdvancedThemingUpdate2(): void {
		if (!is_null($this->showNameBySettingKeyId("97"))) {
			$this->updateKey("97", "ButtonPrimaryIconColor", "1", "text");
			$value = $this->showBySettingKeyId(97);
			$this->update($value->getId(), 97, 1, $value->getValue());
		} else {
			$this->createKey("97", "ButtonPrimaryIconColor", "1", "text");
			$this->createGroupValue("1", "97", "#FFFFFF");
		}
		if (!is_null($this->showNameBySettingKeyId("98"))) {
			$this->updateKey("98", "ButtonSecondaryIconColor", "1", "text");
			$value = $this->showBySettingKeyId(98);
			$this->update($value->getId(), 98, 1, $value->getValue());
		} else {
			$this->createKey("98", "ButtonSecondaryIconColor", "1", "text");
			$this->createGroupValue("1", "98", "#151c62");
		}
	}

	public function addAdvancedThemingUpdate3(): void {
		if (!is_null($this->showNameBySettingKeyId("99"))) {
			$this->updateKey("99", "TaskpaneSecureMailControlColor", "1", "text");
			$value = $this->showBySettingKeyId(99);
			$this->update($value->getId(), 99, 1, $value->getValue());
		} else {
			$this->createKey("99", "TaskpaneSecureMailControlColor", "1", "text");
			$this->createGroupValue("1", "99", "#FFFFFF");
		}
	}

	public function addAdvancedThemingUpdate4(): void {
		if (!is_null($this->showNameBySettingKeyId("100"))) {
			$this->updateKey("100", "DialogFooterBackgroundColor", "1", "text");
			$value = $this->showBySettingKeyId(100);
			$this->update($value->getId(), 100, 1, $value->getValue());
		} else {
			$this->createKey("100", "DialogFooterBackgroundColor", "1", "text");
			$this->createGroupValue("1", "100", "#cfd2f1");
		}
		if (!is_null($this->showNameBySettingKeyId("101"))) {
			$this->updateKey("101", "DialogFooterFontColor", "1", "text");
			$value = $this->showBySettingKeyId(101);
			$this->update($value->getId(), 101, 1, $value->getValue());
		} else {
			$this->createKey("101", "DialogFooterFontColor", "1", "text");
			$this->createGroupValue("1", "101", "#000000");
		}
		if (!is_null($this->showNameBySettingKeyId("102"))) {
			$this->updateKey("102", "DialogFooterHoverColor", "1", "text");
			$value = $this->showBySettingKeyId(102);
			$this->update($value->getId(), 102, 1, $value->getValue());
		} else {
			$this->createKey("102", "DialogFooterHoverColor", "1", "text");
			$this->createGroupValue("1", "102", "#616bd5");
		}
		if (!is_null($this->showNameBySettingKeyId("103"))) {
			$this->updateKey("103", "DialogFooterIconColor", "1", "text");
			$value = $this->showBySettingKeyId(103);
			$this->update($value->getId(), 103, 1, $value->getValue());
		} else {
			$this->createKey("103", "DialogFooterIconColor", "1", "text");
			$this->createGroupValue("1", "103", "#151c62");
		}
	}
	public function addAdvancedThemingNameUpdate(): void {
		if (!is_null($this->showNameBySettingKeyId("104"))) {
			$this->updateKey("104", "VendorName", "1", "text");
			$value = $this->showBySettingKeyId(104);
			$this->update($value->getId(), 104, 1, $value->getValue());
		} else {
			$this->createKey("104", "VendorName", "1", "text");
			$this->createGroupValue("1", "104", "Sendent");
		}
	}

	public function addGuestAccountSettings(): void {
		if (!is_null($this->showNameBySettingKeyId("301"))) {
			$this->updateKey("301", "disableanonymousshare", "0", "select-one");
			$value = $this->showBySettingKeyId(301);
			$this->update($value->getId(), 301, 0, $value->getValue());
		} else {
			$this->createKey("301", "disableanonymousshare", "0", "select-one");
			$this->createGroupValue("0", "301", "False");
		}

		if (!is_null($this->showNameBySettingKeyId("302"))) {
			$this->updateKey("302", "htmlsnippetguestaccounts", "0", "textarea");
			$value = $this->showBySettingKeyId(302);
			$this->update($value->getId(), 302, 0, $value->getValue());
		} else {
			$this->createKey("302", "htmlsnippetguestaccounts", "0", "textarea");
			$this->createGroupValue("0", "302", $this->getguestaccountshtml());
		}
	}

	public function addSendmode(): void {
		$this->createKey("23", "sendmode", "0", "select-one");
		$this->createGroupValue("0", "23", "CurrentMail");
	}

	public function initialLoading(): void {
		$this->createKey("20", "setlanguage", "0", "select-one");
		$this->createKey("19", "passwordcontrolbehavior", "0", "select-one");
		$this->createKey("28", "insertatcursor", "0", "select-one");
		$this->createKey("30", "htmlsnippetpassword", "0", "textarea");
		$this->createKey("6", "dateaddition", "0", "select-one");
		$this->createKey("22", "debugmode", "0", "select-one");
		$this->createKey("23", "sendmode", "0", "select-one");
		$this->createKey("17", "disablesettings", "0", "select-one");
		$this->createKey("2", "attachmentdomainexceptionsinternal", "0", "text");
		$this->createKey("0", "attachmentdomainexceptions", "0", "text");
		$this->createKey("3", "attachmentmode", "0", "select-one");
		$this->createKey("4", "attachmentsize", "0", "text");
		$this->createKey("7", "pathpublicshare", "0", "text");
		$this->createKey("10", "sharefilehtml", "0", "textarea");
		$this->createKey("8", "pathuploadfiles", "0", "text");
		$this->createKey("9", "sharefolderhtml", "0", "textarea");
		$this->createKey("11", "securemail", "0", "select-one");
		$this->createKey("25", "securemailenforced", "0", "select-one");
		$this->createKey("24", "pathsecuremailbox", "0", "text");
		$this->createKey("12", "securemailhtml", "0", "textarea");
		$this->createKey("27", "guestaccountsenabled", "0", "select-one");
		$this->createKey("26", "guestaccountsenforced", "0", "select-one");
		$this->createKey("301", "disableanonymousshare", "0", "select-one");
		$this->createKey("302", "htmlsnippetguestaccounts", "0", "textarea");

		$this->createGroupValue("0", "20", "en");
		$this->createGroupValue("0", "19", "BeforeSend");
		$this->createGroupValue("0", "28", "True");
		$this->createGroupValue("0", "23", "CurrentMail");
		$this->createGroupValue("0", "6", "True");
		$this->createGroupValue("0", "22", "False");
		$this->createGroupValue("0", "17", "True");
		$this->createGroupValue("0", "2", "");
		$this->createGroupValue("0", "0", "");
		$this->createGroupValue("0", "3", "MaximumAttachmentSize");
		$this->createGroupValue("0", "4", "1");
		$this->createGroupValue("0", "7", "/Outlook/Public-Share/");
		$this->createGroupValue("0", "10", $this->getsharefilehtml());
		$this->createGroupValue("0", "8", "Outlook/Upload-Share/");
		$this->createGroupValue("0", "9", $this->getsharefolderhtml());
		$this->createGroupValue("0", "11", "False");
		$this->createGroupValue("0", "25", "False");
		$this->createGroupValue("0", "24", "/Outlook/SecureMail-Share/");
		$this->createGroupValue("0", "12", $this->getsharefilehtml());
		$this->createGroupValue("0", "30", $this->gethtmlpasswordsnippet());
		$this->createGroupValue("0", "27", "False");
		$this->createGroupValue("0", "26", "False");
		$this->createGroupValue("0", "301", "False");
		$this->createGroupValue("0", "302", $this->getguestaccountshtml());
	}

	public function createKey(string $key, string $name, string $templateid, string $valuetype) {
		try {
			$SettingKey = new settingkey();
			$SettingKey->setKey($key);
			$SettingKey->setName($name);
			$SettingKey->setTemplateid($templateid);
			$SettingKey->setValuetype($valuetype);
			return $this->SettingKeyMapper->insert($SettingKey);
		} catch (Exception $e) {
			return null;
		}
	}

	public function updateKey(string $key, string $name, string $templateid, string $valuetype) {
		try {
			$SettingKey = $this->SettingKeyMapper->findByKey($key);
			$SettingKey->setName($name);
			$SettingKey->setTemplateid($templateid);
			$result = $this->SettingKeyMapper->update($SettingKey);
			return $this->showBySettingKeyId($key);
		} catch (Exception $e) {
			return null;
		}
	}

	public function createGroupValue(string $groupid, string $settingkeyid, string $value) {
		try {
			$SettingGroupValue = new SettingGroupValue();
			$SettingGroupValue->setGroupid($groupid);
			$SettingGroupValue->setSettingkeyid($settingkeyid);
			if ($this->valueSizeForDb($value) !== true) {
				$value = $this->SendentFileStorageManager->writeTxt($groupid, $settingkeyid, $value);
			}
			$SettingGroupValue->setValue($value);
			return $this->SettingGroupValueMapper->insert($SettingGroupValue);
		} catch (Exception $e) {
			return null;
		}
	}

	private function valueIsSettingGroupValueFilePath($value): bool {
		if (strpos($value, 'settinggroupvaluefile') !== false) {
			return true;
		}
		return false;
	}

	private function valueSizeForDb(string $value): bool {
		return strlen($value) < 256 !== false;
	}

	public function showBySettingKeyId(int $settingkeyid) {
		try {
			$result = $this->SettingGroupValueMapper->findBySettingKeyId($settingkeyid);
			if ($this->valueIsSettingGroupValueFilePath($result->getValue()) !== false) {
				$result->setValue($this->SendentFileStorageManager->getContent($result->getGroupid(), $result->getSettingkeyid()));
			}
			return $result;
		} catch (Exception $e) {
			return null;
		}
	}

	public function showNameBySettingKeyId(int $settingkeyid) {
		try {
			$result = $this->SettingKeyMapper->findByKey($settingkeyid);
			return $result->getName();
		} catch (Exception $e) {
			return null;
		}
	}
	public function showTemplateBySettingKeyId(int $settingkeyid) {
		try {
			$result = $this->SettingKeyMapper->findByKey($settingkeyid);
			return $result->getTemplateId();
		} catch (Exception $e) {
			return null;
		}
	}
	public function showGroupIdBySettingKeyId(int $settingkeyid) {
		try {
			$result = $this->SettingGroupValueMapper->findBySettingKeyId($settingkeyid);
			return $result->getGroupId();
		} catch (Exception $e) {
			return null;
		}
	}
	public function update(int $id,int $settingkeyid, int $groupid, string $value) {
		try {
			$SettingGroupValue = $this->SettingGroupValueMapper->find($settingkeyid);
			$SettingGroupValue->setSettingkeyid($settingkeyid);
			$SettingGroupValue->setGroupid($groupid);
			if ($this->valueSizeForDb($value) === false) {
				$value = $this->SendentFileStorageManager->writeTxt($groupid, $settingkeyid, $value);
			}
			$SettingGroupValue->setValue($value);

			$result = $this->SettingGroupValueMapper->update($SettingGroupValue);
			return $this->showBySettingKeyId($settingkeyid);
		} catch (Exception $e) {
			return null;
		}
	}

	public function getsecuremailhtml(): string {
		return $this->getHTMLTemplate('securemailhtml');
	}

	public function getsharefilehtml(): string {
		return $this->getHTMLTemplate('sharefilehtml');
	}

	public function gethtmlpasswordsnippet(): string {
		return $this->getHTMLTemplate('htmlsnippetpassword');
	}

	public function getsharefolderhtml(): string {
		return $this->getHTMLTemplate('sharefolderhtml');
	}

	public function getguestaccountshtml(): string {
		return $this->getHTMLTemplate('guestaccountshtml');
	}

	private function getHTMLTemplate(string $id): string {
		$appRoot = $this->appManager->getAppPath(Application::APPID);

		return file_get_contents($appRoot . '/assets/templates/'. $id . '.html');
	}
}
