<?php

// db/author.php
namespace OCA\Sendent\Db;

use DateInterval;
use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class License extends Entity implements JsonSerializable {
	public const ERROR_INCOMPLETE = 'Error_incomplete';
	public const ERROR_VALIDATING = 'Error_validating';
	public const OFFLINE_MODE = 'Offline_mode';

	protected $licensekey;
	protected $licensekeytoken;
	protected $email;
	protected $dategraceperiodend;
	protected $datelicenseend;
	protected $maxusers;
	protected $maxgraceusers;
	protected $datelastchecked;
	protected $level;
	protected $ncgroup;
	protected $subscriptionstatus;
	protected $technicallevel;
	protected $product;
	protected $istrial;

	public function __construct() {
		// add types in constructor
	}
	public function jsonSerialize() {
		return [
			'id' => $this->id,
			'licensekey' => $this->licensekey,
			'licensekeytoken' => $this->licensekeytoken,
			'email' => $this->email,
			'subscriptionstatus' => $this->subscriptionstatus,
			'dategraceperiodend' => $this->dategraceperiodend,
			'maxusers' => $this->maxusers,
			'maxgraceusers' => $this->maxgraceusers,
			'level' => $this->level,
			'datelicenseend' => $this->datelicenseend,
			'datelastchecked' => $this->datelastchecked,
			'ncgroup' => $this->ncgroup,
			'technicallevel' => $this->technicallevel,
			'product' => $this->product,
			'istrial' => $this->istrial
		];
	}

	public function isCheckNeeded(): bool {
		$diffDay = new DateInterval('P7D');
		if ((date_create($this->datelastchecked) >= date_sub(date_create('now'), $diffDay) && $this->level != License::ERROR_VALIDATING) || $this->subscriptionstatus != License::OFFLINE_MODE) {
			error_log(print_r('LICENSE-ISCHECKNEEDED: FALSE', true));
			return false;
		}
		error_log(print_r('LICENSE-ISCHECKNEEDED: TRUE', true));

		return true;
	}

	public function isIncomplete(): bool {
		if ($this->level == 'Error_incomplete' || (!isset($this->licensekey) || !isset($this->licensekey)) || ($this->licensekey == '' || $this->email == '')) {
			return true;
		}
		return false;
	}

	public function isCleared(): bool {
		if ((!isset($this->licensekey) && !isset($this->licensekey)) || ($this->licensekey == '' && $this->email == '')) {
			return true;
		}
		return false;
	}

	public function isLicenseExpired(): bool {
		if (((date_create($this->datelicenseend) < date_create('now')
		&& date_create($this->dategraceperiodend) < date_create('now')) && $this->subscriptionstatus != License::OFFLINE_MODE)
		|| ($this->subscriptionstatus == '2' || $this->subscriptionstatus == '4' || $this->subscriptionstatus == '5' || $this->subscriptionstatus == '6' || $this->subscriptionstatus == '7')) {
			return true;
		}
		return false;
	}
	public function isTrial() : bool {
		return $this->istrial == 1;
	}
	public function isSupportedProduct() : bool {
		return str_contains($this->product, 'Outlook') || str_contains($this->product, 'outlook')
			|| str_contains($this->product, 'Basic') || str_contains($this->product, 'basic')
			|| str_contains($this->product, 'Professional') || str_contains($this->product, 'professional')
			|| str_contains($this->product, 'Premium') || str_contains($this->product, 'premium')
			|| str_contains($this->product, 'Teams') || str_contains($this->product, 'teams')
			|| str_contains($this->level, 'Teams') || str_contains($this->product, 'teams') || $this->subscriptionstatus == License::OFFLINE_MODE;
	}
	public function isLicenseSuspended(): bool {
		return $this->subscriptionstatus == '5';
	}

	public function isLicenseInactive(): bool {
		return $this->subscriptionstatus == '4';
	}

	public function isLicenseRenewedOrSwitched(): bool {
		return $this->subscriptionstatus == '6' || $this->subscriptionstatus == '7';
	}
}
