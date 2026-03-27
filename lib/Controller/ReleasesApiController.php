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

use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\Http\Client\IClientService;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

class ReleasesApiController extends ApiController {
	private const BASE_URL = 'https://changelog.scwcloud.sendent.nl/';

	private const WORKSPACES = [
		'outlook-cross-platform' => 'outlook-cross-platform',
		'ms-teams' => 'ms-teams',
		'outlook-windows' => 'outlook-windows',
	];

	private $httpClient;
	private $logger;

	public function __construct(
		$appName,
		IRequest $request,
		IClientService $clientService,
		LoggerInterface $logger,
	) {
		parent::__construct($appName, $request);
		$this->httpClient = $clientService->newClient();
		$this->logger = $logger;
	}

	/**
	 * Fetches the latest release entry for all products.
	 *
	 * @return DataResponse
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function index(): DataResponse {
		$results = [];
		foreach (self::WORKSPACES as $slug => $uuid) {
			try {
				$response = $this->httpClient->get(self::BASE_URL . $uuid, ['timeout' => 5]);
				$data = json_decode($response->getBody(), true);
				if ($data) {
					$results[$slug] = $data;
				}
			} catch (\Exception $e) {
				$this->logger->warning('Failed to fetch release for ' . $slug . ': ' . $e->getMessage());
			}
		}
		return new DataResponse($results);
	}
}