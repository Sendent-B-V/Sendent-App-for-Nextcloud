<?php

// db/author.php
namespace OCA\Sendent\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class SettingGroupValue extends Entity implements JsonSerializable {
	protected $groupid;
	protected $settingkeyid;
	protected $value;
	protected $ncgroup;

	public function __construct() {
		// add types in constructor
	}

	public function jsonSerialize() {
		return [
			'id' => $this->id,
			'groupid' => $this->groupid,
			'settingkeyid' => $this->settingkeyid,
			'value' => $this->value,
			'ncgroup' => $this->ncgroup
		];
	}
}
