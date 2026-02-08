<?php

namespace OCA\Sendent\Service;

use Exception;
use OCA\Sendent\Db\License;
use OCA\Sendent\Http\SubscriptionValidationHttpClient;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use Psr\Log\LoggerInterface;

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
			$e instanceof DoesNotExistException
			|| $e instanceof MultipleObjectsReturnedException
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
			if (str_contains($license->getEmail(), 'OFFLINE_')) {
				$this->logger->info('NOT pinging licensing server because of offline support with license ' . $license->getId());
			} else {
				error_log(print_r('Pinging licensing server with license ' . $license->getId(), true));
				$this->logger->info('Pinging licensing server with license ' . $license->getId());
				$pingResultLicense = $this->subscriptionvalidationhttpclient->validate($license);
			}
		} catch (Exception $e) {
			$this->logger->error('Error while pinging licensing server');
		}
	}

	public function renewLicense(License $license) {
		if (isset($license) && $license != null) {
			$this->logger->info('Local provided license for renewal is not null we CAN continue with license: ' . $license->getId());

		} else {

			$license = new License();
			$license->setLevel('nolicense');
			return $license;
		}
		$this->logger->info('Renewing license ' . $license->getId());
		error_log(print_r('Renewing license ' . $license->getId(), true));
		if (str_contains($license->getEmail(), 'OFFLINE_')) {
			$this->logger->info('NOT pinging licensing server because of offline support with license ' . $license->getId());
			return $this->licenseservice->update(
				$license->getId(),
				$license->getLicensekey(),
				$license->getLicensekey(),
				License::OFFLINE_MODE,
				date_create('+1 day'),
				date_create('+1 day'),
				1,
				1,
				$license->getEmail(),
				date_create('now'),
				License::OFFLINE_MODE,
				License::OFFLINE_MODE,
				'Outlook',
				-1,
				$license->getNcgroup()
			);
		} else {
			$validatedLicense = $this->subscriptionvalidationhttpclient->validate($license);
			if (isset($validatedLicense) && $validatedLicense != null) {
				$maxUsers = $validatedLicense->getMaxusers();
				if (!isset($maxUsers)) {
					$maxUsers = 1;
				}
				$maxGraceUsers = $validatedLicense->getMaxgraceusers();
				if (!isset($maxGraceUsers)) {
					$maxGraceUsers = 1;
				}
				$level = $validatedLicense->getLevel();

				if ($level != License::ERROR_VALIDATING) {
					error_log(print_r('RENEWLICENSE LICENSE LEVEL IS NOT ERROR_VALIDATING', true));

					return $this->licenseservice->update(
						$validatedLicense->getId(),
						$validatedLicense->getLicensekey(),
						$validatedLicense->getLicensekeytoken(),
						$validatedLicense->getSubscriptionstatus(),
						date_create($validatedLicense->getDategraceperiodend()),
						date_create($validatedLicense->getDatelicenseend()),
						$maxUsers,
						$maxGraceUsers,
						$validatedLicense->getEmail(),
						date_create($validatedLicense->getDatelastchecked()),
						$level,
						$validatedLicense->getTechnicallevel(),
						$validatedLicense->getProduct(),
						$validatedLicense->getIstrial(),
						$validatedLicense->getNcgroup()
					);
				}
			} elseif ($this->isLocalValid($license)) {
				return $license;
			} else {
				$license = new License();
				$license->setLevel('nolicense');
				return $license;
			}
		}
	}

	public function createLicense(string $license, string $licenseKeyToken, string $subscriptionStatus, string $email, string $ncgroup = '') {
		$this->logger->info('Creating license');
		$this->deleteLicense($ncgroup);
		if (str_contains($email, 'OFFLINE_')) {
			$this->logger->info('Overriding licensekeytoken for offline support ' . $email);
			$licenseData = $this->licenseservice->createNew($license, $license, $subscriptionStatus, $email, $ncgroup);
			return $this->activateLicense($licenseData);
		} else {
			$licenseData = $this->licenseservice->createNew($license, $licenseKeyToken, $subscriptionStatus, $email, $ncgroup);
			return $this->activateLicense($licenseData);
		}

	}

	public function deleteLicense(string $ncgroup = '') {
		try {
			if ($ncgroup === '') {
				$this->logger->info('Deleting license for default group');
			} else {
				$this->logger->info('Deleting license for group ' . $ncgroup);
			}
			$this->licenseservice->delete($ncgroup);
		} catch (Exception $e) {
			$this->logger->error('Error while deleting license');
		}
	}

	public function activateLicense(License $license) {
		error_log(print_r('LICENSEMANAGER-ACTIVATELICENSE', true));
		if (str_contains($license->getEmail(), 'OFFLINE_')) {
			$this->logger->info('Overriding licensekeytoken for offline support ' . $license->getId());
			return $license;
		} else {
			$activatedLicense = $this->subscriptionvalidationhttpclient->activate($license);
			if (isset($activatedLicense)) {
				$level = $activatedLicense->getLevel();
				error_log(print_r('LICENSEMANAGER-LEVEL=		' . $level, true));

				if (!isset($level) && ($activatedLicense->getEmail() == '' || $activatedLicense->getLicensekey() == '')) {
					$level = 'Error_incomplete';
					error_log(print_r('LICENSEMANAGER-LEVEL=		Error_incomplete', true));
				} elseif (!isset($level)) {
					$level = License::ERROR_VALIDATING;
					error_log(print_r('LICENSEMANAGER-LEVEL=		' . License::ERROR_VALIDATING, true));
				}
				$maxUsers = $activatedLicense->getMaxusers();
				if (!isset($maxUsers)) {
					$maxUsers = 1;
				}
				$maxGraceUsers = $activatedLicense->getMaxgraceusers();
				if (!isset($maxGraceUsers)) {
					$maxGraceUsers = 1;
				}
				error_log(print_r('LICENSEMANAGER-LEVEL=		' . $level, true));

				return $this->licenseservice->create(
					$activatedLicense->getLicensekey(),
					$activatedLicense->getLicensekeytoken(),
					$activatedLicense->getSubscriptionstatus(),
					date_create($activatedLicense->getDategraceperiodend()),
					date_create($activatedLicense->getDatelicenseend()),
					$maxUsers,
					$maxGraceUsers,
					$activatedLicense->getEmail(),
					date_create('now'),
					$level,
					$license->getTechnicallevel(),
					$license->getProduct(),
					$license->getIstrial(),
					$license->getNcgroup()
				);
			} elseif ($this->isLocalValid($license)) {
				return $license;
			} else {
				$license = new License();
				$license->setLevel('nolicense');
				return $license;
			}
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
