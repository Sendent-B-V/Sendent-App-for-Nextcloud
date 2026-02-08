<?php

namespace OCA\Sendent\Http\Dto;

use JsonSerializable;
use OCA\Sendent\Db\License;

class SubscriptionIn implements JsonSerializable {
	protected $key;
	protected $amountusers;
	protected $email;

	public function __construct(License $license, int $connectedusercount) {
		// add types in constructor
		$this->key = $license->getLicensekey();
		$this->email = $license->getEmail();
		$this->amountusers = $connectedusercount;
	}
	public function jsonSerialize() {
		return [
			'Key' => $this->key,
			'AmountUsers' => $this->amountusers,
			'Email' => $this->email
		];
	}
}
