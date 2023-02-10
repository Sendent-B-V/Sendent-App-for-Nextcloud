<?php

namespace OCA\Sendent\Http;

use Exception;
use Psr\Log\LoggerInterface;
use OCA\Sendent\Http\Dto\AppVersionResponse;

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
				$appVersionResponse->releaseDate = date_format(date_create($result->releaseDate), "Y-m-d");
				$appVersionResponse->urlManual = $result->urlManual;
				$appVersionResponse->urlReleaseNotes = $result->urlReleaseNotes;
				$appVersionResponse->urlBinary = $result->urlBinary;
				$appVersionResponse->additionalInformation = $result->additionalInformation;
				$appVersionResponse->applicationId = $result->applicationId;

				return $appVersionResponse;
			}
		} catch (Exception $e) {
			$this->logger->error('AppVersionHttpClient-latest-EXCEPTION: ' . $e->getMessage(), [
				'exception' => $e,
			]);
			return null;
		}
	}
}
