<?php

namespace OCA\Sendent\Controller;

use Exception;
use OCA\Sendent\Controller\Dto\LicenseStatus;
use OCA\Sendent\Service\LicenseManager;
use OCP\IRequest;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCA\Sendent\Service\LicenseService;
use OCA\Sendent\Service\NotFoundException;
use OCP\IL10N;

class LicenseApiController extends ApiController {
	private $service;
	private $userId;
	private $licensemanager;

	/** @var IL10N */
	private $l;

	public function __construct(
			  $appName,
			  IRequest $request,
			  LicenseManager $licensemanager,
			  LicenseService $licenseservice,
			  IL10N $l,
			  $userId
	   ) {
		parent::__construct($appName, $request);
		$this->service = $licenseservice;
		$this->userId = $userId;
		$this->licensemanager = $licensemanager;
		$this->l = $l;
	}
	/**
	 * @return never
	 */
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
	/**
	 * @NoAdminRequired
	 *
	 * @NoCSRFRequired
	 *
	 * @return DataResponse
	 */
	public function show(): DataResponse {
		try {
			try {
				$this->licensemanager->pingLicensing();
			} catch (Exception $e) {
			}
			$result = $this->service->findAll();
			if (isset($result) && $result !== null && $result !== false) {
				if (is_array($result) && count($result) > 0
				&& $result[0]->getLevel() != "Error_clear" && $result[0]->getLevel() != "Error_incomplete") {
					if ($result[0]->isCheckNeeded()) {
						try {
							$this->licensemanager->renewLicense();
							$result = $this->service->findAll();
							if (isset($result) && $result !== null && $result !== false) {
								if (is_array($result) && count($result) > 0
								&& $result[0]->getLevel() != "Error_clear" && $result[0]->getLevel() != "Error_incomplete") {
								} else {
									throw new Exception();
								}
							}
						} catch (Exception $e) {
						}
					}
					$email = $result[0]->getEmail();
					$licensekey = $result[0]->getLicensekey();
					$dateExpiration = $result[0]->getDatelicenseend();
					$dateLastCheck = $result[0]->getDatelastchecked();
					$level = $result[0]->getLevel();
					$statusKind = "";
					$status = "";

					if ($result[0]->isCleared()) {
						$status = $this->l->t("No license configured");
						$statusKind = "nolicense";
					} elseif ($result[0]->isIncomplete()) {
						$status = $this->l->t("Missing email address or license key.");
						$statusKind = "error_incomplete";
					} elseif ($result[0]->isCheckNeeded()) {
						$status = $this->l->t("Revalidation of your license is required");
						$statusKind = "check";
					} elseif ($result[0]->isLicenseExpired()) {
						$status = $this->l->t("Current license has expired.") .
							"</br>" .
							$this->l->t('%1$sContact sales%2$s to renew your license.', ["<a href='mailto:info@sendent.nl' style='color:blue'>", "</a>"]);
						$statusKind = "expired";
					} elseif (!$result[0]->isCheckNeeded() && !$result[0]->isLicenseExpired()) {
						$status = $this->l->t("Current license is valid");
						$statusKind = "valid";
					} elseif (!$this->licensemanager->isWithinUserCount() && $this->licensemanager->isWithinGraceUserCount()) {
						$status = $this->l->t("Current amount of active users exceeds licensed amount. Some users might not be able to use Sendent.");
						$statusKind = "userlimit";
					} elseif (!$this->licensemanager->isWithinUserCount() && !$this->licensemanager->isWithinGraceUserCount()) {
						$status = $this->l->t("Current amount of active users exceeds licensed amount. Additional users trying to use Sendent will be prevented from doing so.");
						$statusKind = "userlimit";
					}
					return new DataResponse(new LicenseStatus($status, $statusKind, $level,$licensekey, $dateExpiration, $dateLastCheck, $email));
				} elseif (count($result) > 0 && $result[0]->getLevel() == "Error_incomplete") {
					$email = $result[0]->getEmail();
					$licensekey = $result[0]->getLicensekey();
					$status = $this->l->t('Missing (or incorrect) email address or license key. %1$sContact support%2$s to get your correct license information.', ["<a href='mailto:support@sendent.nl' style='color:blue'>", "</a>"]);
					return new DataResponse(new LicenseStatus($status, "error_incomplete" ,"-", $licensekey, "-", "-", $email));
				} elseif (count($result) > 0 && $result[0]->getLevel() == "Error_validating") {
					$email = $result[0]->getEmail();
					$licensekey = $result[0]->getLicensekey();
					return new DataResponse(new LicenseStatus($this->l->t("Cannot verify your license. Please make sure your licensekey and email address are correct before you try to 'Activate license'."), "error_validating","-", $licensekey, "-", "-", $email));
				} else {
					return new DataResponse(new LicenseStatus($this->l->t("No license configured"), "nolicense" ,"-", "-", "-", "-", "-"));
				}
			} else {
				return new DataResponse(new LicenseStatus($this->l->t("No license configured"), "nolicense" ,"-", "-", "-", "-", "-"));
			}
		} catch (Exception $e) {
			return new DataResponse(new LicenseStatus($this->l->t("Cannot verify your license. Please make sure your licensekey and email address are correct before you try to 'Activate license'."), "fatal" ,"-", "-", "-", "-", "-"));
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @param string $license
	 * @param string $email
	 */
	public function create(string $license, string $email) {
		return $this->licensemanager->createLicense($license, $email);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function renew() {
		return $this->licensemanager->renewLicense();
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function validate() {
		$this->licensemanager->renewLicense();
		return $this->licensemanager->isLocalValid();
	}
}
