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
use OCA\Sendent\Db\License;
use OCA\Sendent\Http\Dto\SubscriptionIn;
use OCA\Sendent\Service\ConnectedUserService;
use Psr\Log\LoggerInterface;

class SubscriptionValidationHttpClient {
	/** @var LicenseHttpClient */
	protected $licenseHttpClient;

	/** @var ConnectedUserService */
	protected $connectedUserService;

	/** @var LoggerInterface */
	protected $logger;

	public function __construct(LicenseHttpClient $licenseHttpClient, ConnectedUserService $connectedUserService, LoggerInterface $logger) {
		$this->licenseHttpClient = $licenseHttpClient;
		$this->connectedUserService = $connectedUserService;
		$this->logger = $logger;
	}

	public function validate(License $licenseData, $connectedUserCount = null): ?License {
		$this->logger->info('SUBSCRIPTIONVALIDATIONHTTPCLIENT-VALIDATE');
		error_log(print_r('SUBSCRIPTIONVALIDATIONHTTPCLIENT-VALIDATE', true));

		if ($licenseData->getLicensekey() === '' || $licenseData->getEmail() === '') {
			$this->logger->info('SUBSCRIPTIONVALIDATIONHTTPCLIENT-VALIDATE: No key or email information found for license');
			error_log(print_r('SUBSCRIPTIONVALIDATIONHTTPCLIENT-VALIDATE: No key or email information found for license', true));
			return null;
		}

		$connectedUserCount = $connectedUserCount ?? $this->connectedUserService->getCount($licenseData->getId());

		$this->logger->info('SUBSCRIPTIONVALIDATIONHTTPCLIENT-USERCOUNT= ' . $connectedUserCount);
		error_log(print_r('SUBSCRIPTIONVALIDATIONHTTPCLIENT-USERCOUNT= ' . $connectedUserCount, true));

		$data = new SubscriptionIn($licenseData, $connectedUserCount);

		$validatedLicense = new License();
		$validatedLicense->setId($licenseData->getId());
		$validatedLicense->setLicensekey($licenseData->getLicensekey());
		$validatedLicense->setEmail($licenseData->getEmail());
		$validatedLicense->setNcgroup($licenseData->getNcgroup());

		try {
			$result = $this->licenseHttpClient->post('subscription/validate', $data);

			if (isset($result) && $result != null) {
				$validatedLicense->setLevel($result->level);

				$validatedLicense->setDategraceperiodend(date_format(date_create($result->gracePeriodEnd), 'Y-m-d'));
				$validatedLicense->setDatelicenseend(date_format(date_create($result->expirationDate), 'Y-m-d'));
				$validatedLicense->setMaxusers($result->amountUsers);
				$validatedLicense->setLicensekeytoken($result->key);
				$validatedLicense->setSubscriptionstatus($result->subscriptionStatus);
				$validatedLicense->setMaxgraceusers($result->amountUsersMax);
				$validatedLicense->setDatelastchecked(date_format(date_create('now'), 'Y-m-d'));
				$validatedLicense->setIstrial($result->isTrial);
				$validatedLicense->setTechnicallevel($result->technicalProductLevel);
				$validatedLicense->setProduct($result->product);


				$this->logger->info('SUBSCRIPTIONVALIDATIONHTTPCLIENT-LEVEL=		' . $result->level);
				$this->logger->info('SUBSCRIPTIONVALIDATIONHTTPCLIENT-KEY=			' . $result->key);

				error_log(print_r('SUBSCRIPTIONVALIDATIONHTTPCLIENT-LEVEL=		' . $result->level, true));

				return $validatedLicense;
			} else {
				$validatedLicense->setLevel(License::ERROR_VALIDATING);
				$validatedLicense->setSubscriptionstatus(License::ERROR_VALIDATING);
				$validatedLicense->setIstrial(-1);
				$validatedLicense->setTechnicallevel(License::ERROR_VALIDATING);
				$validatedLicense->setProduct(License::ERROR_VALIDATING);
				error_log(print_r('SUBSCRIPTIONVALIDATIONHTTPCLIENT-VALIDATE SETTING LEVEL TO ERROR_VALIDATING', true));

			}
		} catch (Exception $e) {
			$this->logger->error('SUBSCRIPTIONVALIDATIONHTTPCLIENT-VALIDATE-EXCEPTION: ' . $e->getMessage(), [
				'exception' => $e,
			]);
			error_log(print_r('SUBSCRIPTIONVALIDATIONHTTPCLIENT-VALIDATE-EXCEPTION: ' . $e->getMessage(), true));
			$validatedLicense->setSubscriptionstatus(License::ERROR_VALIDATING);
			$validatedLicense->setLevel(License::ERROR_VALIDATING);
			$validatedLicense->setIstrial(-1);
			$validatedLicense->setTechnicallevel(License::ERROR_VALIDATING);
			$validatedLicense->setProduct(License::ERROR_VALIDATING);
		}

		return $validatedLicense;
	}

	public function activate(License $licenseData): ?License {
		$this->logger->info('SUBSCRIPTIONVALIDATIONHTTPCLIENT-ACTIVATE');

		return $this->validate($licenseData, 1);
	}
}
