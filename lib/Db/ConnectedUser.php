<?php

// db/author.php
namespace OCA\Sendent\Db;

use OCP\AppFramework\Db\Entity;
use JsonSerializable;

class ConnectedUser extends Entity implements JsonSerializable {
	protected $userid;
	protected $dateconnected;
	protected $licenseid;

	public function __construct() {
		// add types in constructor
	}
	public function jsonSerialize() {
		return [
			'id' => $this->id,
			'userid' => $this->userid,
			'dateconnected' => $this->dateconnected,
			'licenseid' => $this->licenseid,
		];
	}
}
