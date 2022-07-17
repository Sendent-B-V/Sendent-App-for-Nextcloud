<?php

namespace OCA\Sendent\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\ApiController;
use OCA\Sendent\Db\Status;
use OCA\Sendent\Http\AppVersionHttpClient;
use OCA\Sendent\Service\LicenseManager;
use OCA\Sendent\Service\LicenseService;
use OCP\App\IAppManager;

class StatusApiController extends ApiController {
	/** @var IAppManager */
	private $appManager;

	private $userId;
	private $licensemanager;
	private $appVersionClient;
	private $licenseservice;

	public function __construct(
		$appName,
		IRequest $request,
		IAppManager $appManager,
		$userId,
		LicenseManager $licensemanager,
		AppVersionHttpClient $appVersionClient,
		LicenseService $licenseservice
	) {
		parent::__construct($appName, $request);
		$this->appManager = $appManager;
		$this->userId = $userId;
		$this->appVersionClient = $appVersionClient;
		$this->licensemanager = $licensemanager;
		$this->licenseservice = $licenseservice;
	}
	/**
	 * @NoAdminRequired
	 *
	 * @NoCSRFRequired
	 *
	 * @return DataResponse
	 */
	public function index(): DataResponse {
		$statusobj = new Status();
		$statusobj->app = "sendent";
		$statusobj->version = $this->appManager->getAppVersion("sendent");
		$statusobj->currentuserid = $this->userId;
		$statusobj->licenseaction = "Free";
		$statusobj->maxusersgrace = 0;
		$statusobj->maxusers = 0;
		$statusobj->currentusers = 0;
		$statusobj->validlicense = false;

		
		if ($this->licensemanager->isLicenseCheckNeeded()) {
			$result = $this->licensemanager->renewLicense();
		}

		$result = $this->licenseservice->findAll();

		if (isset($result) && $result !== null && $result !== false) {
			if (is_array($result) && count($result) > 0
				&& $result[0]->getLevel() != "Error_clear" && $result[0]->getLevel() != "Error_incomplete") {
				$statusobj->datelicenseend = $result[0]->getDatelicenseend();
				$statusobj->maxusers = $result[0]->getMaxusers();
				$statusobj->dategraceperiodend = $result[0]->getDategraceperiodend();
				$statusobj->maxusersgrace = $result[0]->getMaxgraceusers();
				$statusobj->currentusers = $this->licensemanager->getCurrentUserCount();
				$statusobj->validLicense = !$result[0]->isLicenseExpired();
				$status = "";
				if ($result[0]->isCheckNeeded()) {
					$status = "RevalidationRequired";
				}
				if ($result[0]->isLicenseExpired()) {
					$status = "Expired";
				}
				if (!$result[0]->isCheckNeeded() && !$result[0]->isLicenseExpired()) {
					$status = "Valid";
				}
				$statusobj->licenseaction = $status;

				$appVersionVSTO = $this->appVersionClient->latest('vstoaddin');
				$appVersionNCApp = $this->appVersionClient->latest('ncapp');

				$statusobj->latestVSTOAddinVersion = $appVersionVSTO;
				$statusobj->latestNCAppVersion = $appVersionNCApp;
			}
		}


		return new DataResponse($statusobj);
	}
}
