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

namespace OCA\Sendent\Http;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\TransferException;
use OCP\AppFramework\Http;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use Psr\Log\LoggerInterface;

class LicenseHttpClient {

	/** @var IClient */
	protected $client;

	/** @var LoggerInterface */
	protected $logger;

	/** @var string */
	protected $baseUrl;

	public function __construct(IClientService $clientService, LoggerInterface $logger, string $baseUrl = 'https://api.scwcloud.sendent.nl/') {
		$this->client = $clientService->newClient();
		$this->logger = $logger;
		$this->baseUrl = $baseUrl;
	}

	public function get(string $request): bool {
		$response = $this->client->get($this->baseUrl . $request);

		return $response->getStatusCode() === Http::STATUS_OK;
	}
	public function getJson(string $request) {
		$response = $this->client->get($this->baseUrl . $request);
		return json_decode($response->getBody());
	}
	public function post(string $request, Dto\SubscriptionIn $data) {
		$uri = $this->baseUrl . $request;

		try {
			$response = $this->client->post($uri, [
				'json' => $data->jsonSerialize(),
				'headers' => [
					'api-version' => '2.0',
				],
			]);
		} catch (BadResponseException $e) {
			$this->logger->warning('License client received error response with status ' . $e->getResponse()->getStatusCode());

			return null;
		} catch (TransferException $e) {
			$this->logger->error('License client could not connect to license server: ' . $e->getMessage());

			return null;
		} catch (Exception $e) {
			$this->logger->error('License client could not connect to license server. There was an undefined error: ' . $e->getMessage());
			return null;
		}
		try {
			if ($response->getStatusCode() === Http::STATUS_OK) {
				$this->logger->info('Successfully contacted license server');

				return json_decode($response->getBody());
			}
		} catch (Exception $e) {
			$this->logger->error('Unknown error from license client: ' . $response->getStatusCode() . ' ' . $response->getBody() . $e->getMessage());
		}
		return null;
	}

	public function put(string $request, $data): bool {
		$uri = $this->baseUrl . $request;

		$response = $this->client->put($uri, [
			'json' => $data,
		]);

		return $response->getStatusCode() === Http::STATUS_OK;
	}
}
