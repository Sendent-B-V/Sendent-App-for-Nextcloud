<?php

// db/author.php
namespace OCA\Sendent\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class SettingTemplate extends Entity implements JsonSerializable {
	protected $templatename;
	protected $templatekey;
	public function __construct() {
		// add types in constructor
	}

	public function jsonSerialize() {
		return [
			'id' => $this->id,
			'templatename' => $this->templatename,
			'templatekey' => $this->templatekey
		];
	}
}
