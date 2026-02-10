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

use Exception;
use OCA\Sendent\Http\Dto\AppVersionResponse;
use Psr\Log\LoggerInterface;

class AppVersionHttpClient {
	/** @var LicenseHttpClient */
	protected $licenseHttpClient;

	/** @var ConnectedUserService */
	protected $connectedUserService;

	/** @var LoggerInterface */
	protected $logger;

	public function __construct(LicenseHttpClient $licenseHttpClient, LoggerInterface $logger) {
		$this->licenseHttpClient = $licenseHttpClient;
		$this->logger = $logger;
	}

	public function latest($assembly): ?AppVersionResponse {
		$this->logger->info('AppVersionHttpClient-latest');

		try {
			$result = $this->licenseHttpClient->getJson('ApplicationVersion/ByAssembly/' . $assembly . '/Latest');

			if (isset($result)) {
				$appVersionResponse = new AppVersionResponse();
				$appVersionResponse->applicationName = $result->applicationName;
				$appVersionResponse->version = $result->version;
				$appVersionResponse->releaseDate = date_format(date_create($result->releaseDate), 'Y-m-d');
				$appVersionResponse->urlManual = $result->urlManual;
				$appVersionResponse->urlReleaseNotes = $result->urlReleaseNotes;
				$appVersionResponse->urlBinary = $result->urlBinary;
				$appVersionResponse->additionalInformation = $result->additionalInformation;
				$appVersionResponse->applicationId = $result->applicationId;

				return $appVersionResponse;
			} else {
				return new AppVersionResponse();
			}
		} catch (Exception $e) {
			$this->logger->error('AppVersionHttpClient-latest-EXCEPTION: ' . $e->getMessage(), [
				'exception' => $e,
			]);
			return new AppVersionResponse();
		}
	}
}
