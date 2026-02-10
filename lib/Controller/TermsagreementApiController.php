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

use OCA\Sendent\Service\TermsAgreementService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataResponse;

use OCP\IRequest;

class TermsAgreementApiController extends ApiController {
	private $service;

	public function __construct(
		$appName,
		IRequest $request,
		TermsAgreementService $service,
	) {
		parent::__construct($appName, $request);
		$this->service = $service;
	}
	/**
	 * @param string $version
	 * @return DataResponse
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function agree($version): DataResponse {
		try {
			$termsAgreed = $this->service->update($version, 'yes');
			return new DataResponse($termsAgreed);
		} catch (Exception $e) {
			error_log(print_r('TermsAgreementApiController-Agree-EXCEPTION=' . $e, true));
			return new DataResponse(null);
		}
	}
	/**
	 * @param string $version
	 * @return DataResponse
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function isAgreed($version): DataResponse {
		try {
			$termsAgreed = $this->service->isAgreed($version);
			return new DataResponse($termsAgreed);
		} catch (Exception $e) {
			error_log(print_r('TermsAgreementApiController-IsAgreed-EXCEPTION=' . $e, true));
			return new DataResponse(null);
		}
	}
}
