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

use Exception;

use OCA\Sendent\Service\ConnectedUserService;
use OCA\Sendent\Service\LicenseService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

use OCP\IUserManager;
use Psr\Log\LoggerInterface;

class ConnecteduserApiController extends ApiController {
	private $licenseService;
	private $logger;
	private $service;
	private $userId;
	private $userManager;

	public function __construct(
		$appName,
		IRequest $request,
		LicenseService $licenseService,
		LoggerInterface $logger,
		ConnectedUserService $service,
		IUserManager $userManager,
		$userId,
	) {
		parent::__construct($appName, $request);
		$this->licenseService = $licenseService;
		$this->logger = $logger;
		$this->service = $service;
		$this->userId = $userId;
	}

	/**
	 * Registers a user connection
	 *
	 * @return DataResponse
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function ping(): DataResponse {

		$this->logger->info('Got outlook add-in connection for user ' . $this->userId);

		// Finds out user's licenceId
		$license = $this->licenseService->findUserLicense($this->userId);
		// Giving $licenseId a null value (which happens when there's no license registered at all) generates an error
		// (as the 'id' field in the database may not be null) but it doesn't seem to break anything
		$licenseId = is_null($license) ? null : $license->getId();

		// Creates or updates the connected user entry
		try {
			$user = $this->service->create($this->userId, date_create('now'), $licenseId);
		} catch (Exception $e) {
			$user = $this->service->findByUserId($this->userId);
			$this->service->update($user->getId(), $this->userId, date_create('now'), $licenseId);
		}

		return new DataResponse($user);
	}
}
