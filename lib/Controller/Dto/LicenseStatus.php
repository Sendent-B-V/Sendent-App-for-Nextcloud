<?php

namespace OCA\Sendent\Controller\Dto;

use JsonSerializable;

class LicenseStatus implements JsonSerializable {
	public $status;
	public $statusKind;
	public $dateExpiration;
	public $dateLastCheck;
	public $email;
	public $licensekey;
	public $level;
	public $ncgroup;
	public $product;
	public $istrial;

	public function __construct(string $status, string $statusKind,
		string $level, string $licensekey,
		string $dateExpiration, string $dateLastCheck, string $email, string $product = '', int $istrial = -1, string $ncgroup = '') {
		// add types in constructor
		$this->status = $status;
		$this->statusKind = $statusKind;
		$this->licensekey = $licensekey;
		$this->dateExpiration = $dateExpiration;
		$this->dateLastCheck = $dateLastCheck;
		$this->email = $email;
		$this->level = $level;
		$this->ncgroup = $ncgroup;
		$this->product = $product;
		$this->istrial = $istrial;
	}

	public function jsonSerialize() {
		return [
			'status' => $this->status,
			'statusKind' => $this->statusKind,
			'dateExpiration' => $this->dateExpiration,
			'email' => $this->email,
			'level' => $this->level,
			'licensekey' => $this->licensekey,
			'dateLastCheck' => $this->dateLastCheck,
			'ncgroup' => $this->ncgroup,
			'product' => $this->product,
			'istrial' => $this->istrial,
		];
	}
}
