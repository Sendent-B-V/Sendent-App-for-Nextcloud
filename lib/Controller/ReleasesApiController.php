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
use OCP\AppFramework\Http\DataResponse;
use OCP\Http\Client\IClientService;
use OCP\ICache;
use OCP\ICacheFactory;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

class ReleasesApiController extends ApiController {
	private const BASE_URL = 'https://changelog.scwcloud.sendent.nl/';

	/** Seconds a fetched release entry is reused before the changelog is asked again (8 hours) */
	private const CACHE_TTL = 8 * 60 * 60;

	private const WORKSPACES = [
		'outlook-cross-platform' => 'outlook-cross-platform',
		'ms-teams' => 'ms-teams',
		'outlook-windows' => 'outlook-windows',
		'thunderbird' => 'thunderbird',
		'mac-mail' => 'mac-mail',
	];

	private $httpClient;
	private $logger;

	/** @var ICache */
	private $cache;

	public function __construct(
		$appName,
		IRequest $request,
		IClientService $clientService,
		LoggerInterface $logger,
		ICacheFactory $cacheFactory,
	) {
		parent::__construct($appName, $request);
		$this->httpClient = $clientService->newClient();
		$this->logger = $logger;
		$this->cache = $cacheFactory->createDistributed('sendent-releases');
	}

	/**
	 * Fetches the latest release entry for all products. Entries are cached for
	 * CACHE_TTL; failed fetches are not cached, so they are retried on the next request.
	 *
	 * Admin only, with the CSRF check: only the admin settings page uses this.
	 *
	 * @return DataResponse
	 */
	public function index(): DataResponse {
		$results = [];
		foreach (self::WORKSPACES as $slug => $uuid) {
			$cached = $this->cache->get($slug);
			if (is_array($cached)) {
				$results[$slug] = $cached;
				continue;
			}
			try {
				$response = $this->httpClient->get(self::BASE_URL . $uuid, ['timeout' => 5]);
				$data = json_decode($response->getBody(), true);
				if (is_array($data) && $data) {
					$results[$slug] = $data;
					$this->cache->set($slug, $data, self::CACHE_TTL);
				}
			} catch (\Exception $e) {
				$this->logger->warning('Failed to fetch release for ' . $slug . ': ' . $e->getMessage());
			}
		}
		return new DataResponse($results);
	}
}
