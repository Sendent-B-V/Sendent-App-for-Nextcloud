<?php

// db/author.php
namespace OCA\Sendent\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class SettingKey extends Entity implements JsonSerializable {
	protected $key;
	protected $name;
	protected $valuetype;
	protected $templateid;

	public function __construct() {
		// add types in constructor
	}

	public function jsonSerialize() {
		return [
			'id' => $this->id,
			'key' => $this->key,
			'name' => $this->name,
			'templateid' => $this->templateid,
			'valuetype' => $this->valuetype
		];
	}
}
