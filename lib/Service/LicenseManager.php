<?php

namespace OCA\Sendent\Service;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use Psr\Log\LoggerInterface;

use OCA\Sendent\Db\License;
use OCA\Sendent\Http\SubscriptionValidationHttpClient;

use Exception;

class LicenseManager {
	protected $licenseservice;
	protected $connecteduserservice;
	protected $subscriptionvalidationhttpclient;

	/** @var LoggerInterface */
	private $logger;

	public function __construct(LicenseService $licenseservice, LoggerInterface $logger,
	ConnectedUserService $connecteduserservice,
	SubscriptionValidationHttpClient $subscriptionvalidationhttpclient) {
		$this->licenseservice = $licenseservice;
		$this->logger = $logger;
		$this->connecteduserservice = $connecteduserservice;
		$this->subscriptionvalidationhttpclient = $subscriptionvalidationhttpclient;
	}

	/**
	 * @return never
	 */
	private function handleException(Exception $e) {
		if (
			$e instanceof DoesNotExistException ||
			$e instanceof MultipleObjectsReturnedException
		) {
			throw new NotFoundException($e->getMessage());
		} else {
			throw $e;
		}
	}

	/**
	 *
	 * Reports licenses usage to sendent licensing server
	 *
	 */
	public function pingLicensing(License $license): void {
		try {
			error_log(print_r('Pinging licensing server with license ' . $license->getId() . ' for type: ' . $license->getType(), true));
			$this->logger->info('Pinging licensing server with license ' . $license->getId() . ' for type: ' . $license->getType());
			$pingResultLicense = $this->subscriptionvalidationhttpclient->validate($license);
		} catch (Exception $e) {
			$this->logger->error('Error while pinging licensing server');
		}
	}

	public function renewLicense(License $license) {
		$this->logger->info('Renewing license ' . $license->getId() . ' for type: ' . $license->getType());
		error_log(print_r("Renewing license " . $license->getId() . ' for type: ' . $license->getType(), true));

		$license = $this->subscriptionvalidationhttpclient->validate($license);
		if (isset($license) && $license != null) {
			$maxUsers = $license->getMaxusers();
			if (!isset($maxUsers)) {
				$maxUsers = 1;
			}
			$maxGraceUsers = $license->getMaxgraceusers();
			if (!isset($maxGraceUsers)) {
				$maxGraceUsers = 1;
			}
			$level = $license->getLevel();

			if($level != License::ERROR_VALIDATING)
			{
				error_log(print_r("RENEWLICENSE LICENSE LEVEL IS NOT ERROR_VALIDATING", true));

				return $this->licenseservice->update(
					$license->getId(),
					$license->getLicensekey(),
					$license->getLicensekeytoken(),
					$license->getSubscriptionstatus(),
					date_create($license->getDategraceperiodend()),
					date_create($license->getDatelicenseend()),
					$maxUsers,
					$maxGraceUsers,
					$license->getEmail(),
					date_create($license->getDatelastchecked()),
					$level,
					$license->getTechnicallevel(),
					$license->getProduct(),
					$license->getIstrial(),
					$license->getNcgroup(),
					$license->getType()
				);
		}
		} else if($this->isLocalValid($license)){
			return $license;
		}
		else{
			$license = new License();
			$license->setLevel("nolicense");
			return $license;
		}
	}

	public function createLicense(string $license, string $licenseKeyToken, string $subscriptionStatus, string $email, string $ncgroup = '', string $type = 'outlook') {
		$this->logger->info('Creating license for type: ' . $type);
		$this->deleteLicense($ncgroup);
		$licenseData = $this->licenseservice->createNew($license, $licenseKeyToken, $subscriptionStatus, $email, $ncgroup, $type);
		return $this->activateLicense($licenseData);
	}

	public function deleteLicense(string $ncgroup = '', string $type = 'outlook') {
		try {
			if ($ncgroup === '') {
				$this->logger->info('Deleting license for default group');
			} else {
				$this->logger->info('Deleting license for group ' . $ncgroup);
			}
			$this->licenseservice->delete($ncgroup, $type);
		} catch (Exception $e) {
			$this->logger->error('Error while deleting license');
		}
	}

	public function activateLicense(License $license) {
		error_log(print_r("LICENSEMANAGER-ACTIVATELICENSE", true));

		$activatedLicense = $this->subscriptionvalidationhttpclient->activate($license);
		if (isset($activatedLicense)) {
			$level = $activatedLicense->getLevel();
			error_log(print_r("LICENSEMANAGER-LEVEL=		" . $level, true));

			if (!isset($level) && ($activatedLicense->getEmail() == "" || $activatedLicense->getLicensekey() == "")) {
				$level = "Error_incomplete";
				error_log(print_r("LICENSEMANAGER-LEVEL=		Error_incomplete", true));
			} elseif (!isset($level)) {
				$level = License::ERROR_VALIDATING;
				error_log(print_r("LICENSEMANAGER-LEVEL=		". License::ERROR_VALIDATING, true));
			}
			$maxUsers = $activatedLicense->getMaxusers();
			if (!isset($maxUsers)) {
				$maxUsers = 1;
			}
			$maxGraceUsers = $activatedLicense->getMaxgraceusers();
			if (!isset($maxGraceUsers)) {
				$maxGraceUsers = 1;
			}
			error_log(print_r("LICENSEMANAGER-LEVEL=		" . $level, true));

			return $this->licenseservice->create(
				$activatedLicense->getLicensekey(),
				$activatedLicense->getLicensekeytoken(),
				$activatedLicense->getSubscriptionstatus(),
				date_create($activatedLicense->getDategraceperiodend()),
				date_create($activatedLicense->getDatelicenseend()),
				$maxUsers,
				$maxGraceUsers,
				$activatedLicense->getEmail(),
				date_create("now"),
				$level,
				$activatedLicense->getTechnicallevel(),
				$activatedLicense->getProduct(),
				$activatedLicense->getIstrial(),
				$license->getNcgroup(),
				$license->getType()
			);
		} else if($this->isLocalValid($license)){
			return $license;
		}
		else{
			$license = new License();
			$license->setLevel("nolicense");
			return $license;
		}
		return false;
	}

	public function isLocalValid(License $license): bool {
		return !$license->isLicenseExpired() && ($this->isWithinUserCount($license) || $this->isWithinGraceUserCount($license)) && !$license->isCheckNeeded();
	}

	public function isWithinUserCount(License $license): bool {
		$userCount = $this->connecteduserservice->getCount($license->getId());
		$maxUserCount = $license->getMaxusers();
		return $userCount <= $maxUserCount;
	}

	public function isWithinGraceUserCount(License $license): bool {
		$userCount = $this->connecteduserservice->getCount();
		$maxUserCount = $license->getMaxgraceusers();
		return $userCount <= $maxUserCount;
	}

	public function getCurrentUserCount(string $licenseId) {
		return $this->connecteduserservice->getCount($licenseId);
	}
}
