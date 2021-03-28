<?php

namespace OCA\Sendent\Service;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\Sendent\Db\License;
use OCA\Sendent\Http\SubscriptionValidationHttpClient;

use Exception;

class LicenseManager {
	protected $licenseservice;
	protected $connecteduserservice;
	protected $subscriptionvalidationhttpclient;

	public function __construct(LicenseService $licenseservice,
	ConnectedUserService $connecteduserservice,
	SubscriptionValidationHttpClient $subscriptionvalidationhttpclient) {
		$this->licenseservice = $licenseservice;
		$this->connecteduserservice = $connecteduserservice;
		$this->subscriptionvalidationhttpclient = $subscriptionvalidationhttpclient;
	}

	private function handleException($e) {
		if (
			$e instanceof DoesNotExistException ||
			$e instanceof MultipleObjectsReturnedException
		) {
			throw new NotFoundException($e->getMessage());
		} else {
			throw $e;
		}
	}

	public function renewLicense() {
		try {
			$licenses = $this->licenseservice->findAll();
			if (isset($licenses) && $licenses !== null && $licenses[0] !== null) {
				$license = $licenses[0];
				$license = $this->subscriptionvalidationhttpclient->validate($license);
				if (isset($license)) {
					return $this->licenseservice->update(
						$license->getId(),
						$license->getLicensekey(),
						date_create($license->getDategraceperiodend()),
						date_create($license->getDatelicenseend()),
						$license->getMaxusers(),
						$license->getMaxgraceusers(),
						$license->getEmail(),
						date_create($license->getDatelastchecked()),
						$license->getLevel()
					);
				}
			}
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function createLicense(string $license, string $email) {
		try {
			$existingLicense = $this->licenseservice->findByLicenseKey($license);
			if (isset($existingLicense)) {
				return $this->activateLicense($existingLicense);
			}
		} catch (Exception $e) {
			try {
				$licenseData = $this->licenseservice->createNew($license, $email);
				return $this->activateLicense($licenseData);
			} catch (Exception $e) {
				$this->handleException($e);
			}
		}
	}

	public function activateLicense(License $license) {
		$activatedLicense = $this->subscriptionvalidationhttpclient->activate($license);
		if (isset($activatedLicense)) {
			return $this->licenseservice->update(
				$activatedLicense->getId(),
				$activatedLicense->getLicensekey(),
				date_create($activatedLicense->getDategraceperiodend()),
				date_create($activatedLicense->getDatelicenseend()),
				$activatedLicense->getMaxusers(),
				$activatedLicense->getMaxgraceusers(),
				$activatedLicense->getEmail(),
				date_create("now"),
				$activatedLicense->getLevel()
			);
		}
		return false;
	}

	public function isLocalValid() {
		return $this->licenseExists() && !$this->isExpired() && ($this->isWithinUserCount() || $this->isWithinGraceUserCount()) && !$this->isLicenseCheckNeeded();
	}
	public function isValidLicense() {
		return $this->licenseExists() && !$this->isExpired() && ($this->isWithinUserCount() || $this->isWithinGraceUserCount());
	}
	public function isExpired() {
		$licenses = $this->licenseservice->findAll();
		if (isset($licenses)) {
			foreach($licenses as $license){
				return $license->isLicenseExpired();
			}
		}
		return false;
	}

	public function licenseExists() {
		$licenses = $this->licenseservice->findAll();
		if (isset($licenses)) {
			return true;
		}
		return false;
	}

	public function isWithinUserCount() {
		$licenses = $this->licenseservice->findAll();
		if (isset($licenses)) {
			foreach($licenses as $license){
			$userCount = $this->connecteduserservice->getCount();
			$maxUserCount = $license->getMaxusers();
			return $userCount <= $maxUserCount;
			}
		}
		return false;
	}

	public function isWithinGraceUserCount() {
		$licenses = $this->licenseservice->findAll();
		if (isset($licenses)) {
			foreach($licenses as $license){
			$userCount = $this->connecteduserservice->getCount();
			$maxUserCount = $license->getMaxgraceusers();
			return $userCount <= $maxUserCount;
			}
		}
		return false;
	}
	public function getCurrentUserCount() {
		return $this->connecteduserservice->getCount();
	}

	public function isLicenseCheckNeeded() {
		$licenses = $this->licenseservice->findAll();
		if (isset($licenses)) {
			foreach($licenses as $license){
			return $license->isCheckNeeded();
			}
		}
		return false;
	}
}