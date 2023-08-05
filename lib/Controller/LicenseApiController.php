<?php

namespace OCA\Sendent\Controller;

use Exception;
use OCA\Sendent\Controller\Dto\LicenseStatus;
use OCA\Sendent\Db\License;
use OCA\Sendent\Service\LicenseManager;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUserManager;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Services\IAppConfig;
use OCA\Sendent\Service\LicenseService;
use OCA\Sendent\Service\NotFoundException;
use OCP\IL10N;
use Psr\Log\LoggerInterface;

class LicenseApiController extends ApiController {
	private $appConfig;
	private $service;
	private $groupManager;
	private $userId;
	private $userManager;

	private $licensemanager;

	/** @var IL10N */
	private $l;

	/** @var LoggerInterface */
	private $logger;

	public function __construct(
			  $appName,
			  IAppConfig $appConfig,
			  IRequest $request,
			  IGroupManager $groupManager,
			  IUserManager $userManager,
			  LicenseManager $licensemanager,
			  LicenseService $licenseservice,
			  LoggerInterface $logger,
			  IL10N $l,
			  $userId	  
	   ) {
		parent::__construct($appName, $request);
		$this->appConfig = $appConfig;
		$this->service = $licenseservice;
		$this->groupManager = $groupManager;
		$this->userId = $userId;
		$this->userManager = $userManager;
		$this->licensemanager = $licensemanager;
		$this->logger = $logger;
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
	 * @NoCSRFRequired
	 *
	 * Returns license status for current user
	 *
	 * @return DataResponse
	 */
	public function show(): DataResponse {

		$this->logger->info('Getting license information for user ' . $this->userId);

		// Gets groups for which specific settings and/or license are defined
		// Groups are ordered from highest priority to lowest
		$sendentGroups = $this->appConfig->getAppValue('sendentGroups', '');
		$sendentGroups = $sendentGroups !== '' ? json_decode($sendentGroups) : [];

		// Gets user groups
		$user = $this->userManager->get($this->userId);
		$userGroups = $this->groupManager->getUserGroups($user);
		$userGroups = array_map(function ($group) {
			return $group->getGid();
		}, $userGroups);

		// Gets user groups that are sendentGroups
		$userSendentGroups = array_intersect($sendentGroups, $userGroups);

		// Returns settings for 1st matching group
		if (count($userSendentGroups)) {
			$this->logger->info('First matching group of user ' . $this->userId . ' is ' . $userSendentGroups[array_keys($userSendentGroups)[0]]);
			return $this->showForNCGroup($userSendentGroups[array_keys($userSendentGroups)[0]]);
		} else {
			$this->logger->info('User ' . $this->userId . ' is not member of any sendent group');
			return $this->showForNCGroup('');
		}

	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * Returns license status for group $ncgroup
	 *
	 * @param string $ncgroup
	 * @return DataResponse
	 */
	public function showForNCGroup(string $ncgroup = ''): DataResponse {

		if ($ncgroup === "") {
			$this->logger->info('Getting license information for default group');
		} else {
			$this->logger->info('Getting license information for group ' . $ncgroup);
		}

		try {
			// Gets license for group $ncgroup
			$result = $this->service->findByGroup($ncgroup);
			if (isset($result) && $result !== null && $result !== false && is_array($result) && count($result) === 0) {
				// No license for group $ncgroup, getting default license
				$result = $this->service->findByGroup('');
			}
			
			if (isset($result) && $result !== null && $result !== false) {
				if (is_array($result) && count($result) > 0
				&& $result[0]->getLevel() != "Error_clear" && $result[0]->getLevel() != "Error_incomplete") {


					$this->logger->info('Check needed for license ' . $result[0]->getId());
					try {
						$this->licensemanager->renewLicense($result[0]);
						$result = $this->service->findByGroup($result[0]->getNcgroup());
						if (isset($result) && $result !== null && $result !== false) {
							if (is_array($result) && count($result) > 0
							&& $result[0]->getLevel() != "Error_clear" && $result[0]->getLevel() != "Error_incomplete") {
							} else {
								throw new Exception();
							}
						}
					} catch (Exception $e) {
						$this->logger->error('Error while renewing license ' . $result[0]->getId());
					}

					// Reports license status
					$email = $result[0]->getEmail();
					$licensekey = $result[0]->getLicensekey();
					$dateExpiration = $result[0]->getDatelicenseend();
					$dateLastCheck = $result[0]->getDatelastchecked();
					$level = $result[0]->getLevel();
					$group = $result[0]->getNcgroup();
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
							$this->l->t('%1$sContact support%2$s if you experience issues with configuring your license key.', ["<a href='mailto:info@sendent.com' style='color:blue'>", "</a>"]);
						$statusKind = "expired";
					} elseif (!$result[0]->isCheckNeeded() && !$result[0]->isLicenseExpired()) {
						$status = $this->l->t("Current license is valid");
						$statusKind = "valid";
					} elseif (!$this->licensemanager->isWithinUserCount($result[0]) && $this->licensemanager->isWithinGraceUserCount($result[0])) {
						$status = $this->l->t("Current amount of active users exceeds licensed amount. Some users might not be able to use Sendent.");
						$statusKind = "userlimit";
					} elseif (!$this->licensemanager->isWithinUserCount($result[0]) && !$this->licensemanager->isWithinGraceUserCount($result[0])) {
						$status = $this->l->t("Current amount of active users exceeds licensed amount. Additional users trying to use Sendent will be prevented from doing so.");
						$statusKind = "userlimit";
					}
					return new DataResponse(new LicenseStatus($status, $statusKind, $level,$licensekey, $dateExpiration, $dateLastCheck, $email, $group));
				} elseif (count($result) > 0 && $result[0]->getLevel() == "Error_incomplete") {
					$email = $result[0]->getEmail();
					$licensekey = $result[0]->getLicensekey();
					$group = $result[0]->getNcgroup();
					$status = $this->l->t('Missing (or incorrect) email address or license key. %1$sContact support%2$s to get your correct license information.', ["<a href='mailto:support@sendent.nl' style='color:blue'>", "</a>"]);
					return new DataResponse(new LicenseStatus($status, "error_incomplete" ,"-", $licensekey, "-", "-", $email, $group));
				} elseif (count($result) > 0 && $result[0]->getLevel() == License::ERROR_VALIDATING) {
					$email = $result[0]->getEmail();
					$licensekey = $result[0]->getLicensekey();
					$group = $result[0]->getNcgroup();
					return new DataResponse(new LicenseStatus($this->l->t("Cannot verify your license. Please make sure your licensekey and email address are correct before you try to 'Activate license'."), "error_validating","-", $licensekey, "-", "-", $email, $group));
				} else {
					return new DataResponse(new LicenseStatus($this->l->t("No license configured"), "nolicense" ,"-", "-", "-", "-", "-"));
				}
			} else {
				return new DataResponse(new LicenseStatus($this->l->t("No license configured"), "nolicense" ,"-", "-", "-", "-", "-"));
			}
		} catch (Exception $e) {
			$this->logger->error('Cannot verify license');
			return new DataResponse(new LicenseStatus($this->l->t("Cannot verify your license. Please make sure your licensekey and email address are correct before you try to 'Activate license'."), "fatal" ,"-", "-", "-", "-", "-"));
		}
	}

	/**
	 * @param string $license
	 * @param string $email
	 * @param string $ncgroup
	 */
	public function create(string $license, string $email, string $ncgroup) {
		return $this->licensemanager->createLicense($license, $email, $ncgroup);
	}

	/**
	 * @param string $group
	 */
	public function delete(string $group) {
		// Deletes requested settinglicense
		return $this->licensemanager->deleteLicense($group);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function renew() {
		// Finds out user's license
		$license = $this->service->findUserLicense($this->userId);

		$this->licensemanager->renewLicense($license);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function validate() {
		// Finds out user's license
		$license = $this->service->findUserLicense($this->userId);

		// Unlicensed?
		if (is_null($license)) {
			return false;
		}

		$this->licensemanager->renewLicense($license);
		return $this->licensemanager->isLocalValid($license);
	}

	/**
	 *
	 * Generates a report of all licenses used
	 *
	 */
	public function report() {

		// Gets groups for which specific settings and/or license are defined and add it the default one
		$sendentGroups = $this->appConfig->getAppValue('sendentGroups', '');
		$sendentGroups = json_decode($sendentGroups);
		array_push($sendentGroups,'');

		// Gets license of each groups, handling inheritance
		$licenses = [];
		foreach($sendentGroups as $gid) {
			$license = $this->service->findByGroup($gid);
			if (!empty($license)) {
				$license = $license[0]->jsonSerialize();
				$license += ['inherited' => false];
				if ($gid === '') {
					$license['ncgroup'] = 'Default license';
				}
			} else {
				$license = new License;
				$license = $license->jsonSerialize();
				$license += ['inherited' => true];
				$license['ncgroup'] = $gid; // Maybe we should use displayName here
			}
			array_push($licenses, $license);
		}
		return json_encode($licenses);
	}
}
