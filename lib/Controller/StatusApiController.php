<?php

/**
 * @copyright Copyright (c) 2026 Sendent B.V.
 *
 * @author Sendent B.V. <info@sendent.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\Sendent\Controller;

use OCA\Sendent\Db\Status;
use OCA\Sendent\Http\AppVersionHttpClient;
use OCA\Sendent\Service\LicenseManager;
use OCA\Sendent\Service\LicenseService;
use OCP\App\IAppManager;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

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
		LicenseService $licenseservice,
	) {
		parent::__construct($appName, $request);
		$this->appManager = $appManager;
		$this->userId = $userId;
		$this->appVersionClient = $appVersionClient;
		$this->licensemanager = $licensemanager;
		$this->licenseservice = $licenseservice;
	}
	/**
	 * Get the status of the user's license
	 *
	 * @return DataResponse
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function index(): DataResponse {
		$statusobj = new Status();
		$statusobj->app = 'sendent';
		$statusobj->version = $this->appManager->getAppVersion('sendent');
		$statusobj->currentuserid = $this->userId;
		$statusobj->licenseaction = 'Free';
		$statusobj->maxusersgrace = 0;
		$statusobj->maxusers = 0;
		$statusobj->currentusers = 0;
		$statusobj->validLicense = false;
		try {
			// Finds out user's license
			$result = $this->licenseservice->findUserLicense($this->userId);

			// Unlicensed?
			if (is_null($result) || $result->getEmail() == '') {
				return new DataResponse($statusobj);
			}

			// Renews license if needed
			if ($result->isCheckNeeded()) {
				$result = $this->licensemanager->renewLicense($result);
			}

			// Gets all license status information
			if (isset($result) && $result !== null && $result !== false) {
				if ($result->getLevel() != 'Error_clear' && $result->getLevel() != 'Error_incomplete') {
					$statusobj->datelicenseend = $result->getDatelicenseend();
					$statusobj->maxusers = $result->getMaxusers();
					$statusobj->dategraceperiodend = $result->getDategraceperiodend();
					$statusobj->maxusersgrace = $result->getMaxgraceusers();
					$statusobj->currentusers = !is_null($result->getId()) ? $this->licensemanager->getCurrentUserCount($result->getId()) : 0;
					$statusobj->validLicense = !$result->isLicenseExpired();
					$status = '';
					if ($result->isCheckNeeded()) {
						$status = 'RevalidationRequired';
					}
					if ($result->isLicenseExpired()) {
						$status = 'Expired';
					}
					if (!$result->isCheckNeeded() && !$result->isLicenseExpired()) {
						$status = 'Valid';
					}

					$statusobj->licenseaction = $status;

					if (!str_contains($result->getEmail(), 'OFFLINE_')) {
						$appVersionVSTO = $this->appVersionClient->latest('vstoaddin');
						$appVersionNCApp = $this->appVersionClient->latest('ncapp');
						if (!is_null($appVersionVSTO)) {
							$statusobj->latestVSTOAddinVersion = $appVersionVSTO;
						}
						if (!is_null($appVersionNCApp)) {
							$statusobj->latestNCAppVersion = $appVersionNCApp;
						}
					}

				}
			}

		} catch (Exception $e) {

		}
		// Returns license status
		return new DataResponse($statusobj);
	}
}
