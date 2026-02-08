<?php

namespace OCA\Sendent\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class TermsAgreement extends Entity implements JsonSerializable {
	protected $version;
	protected $agreed;

	public function __construct() {
		// add types in constructor
	}
	public function jsonSerialize() {
		return [
			'Version' => $this->version,
			'Agreed' => $this->agreed,
		];
	}
}
