<?php

namespace OCA\Sendent\Controller;

use Exception;

use OCP\IRequest;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\ApiController;

use OCA\Sendent\Service\TermsAgreementService;

class TermsAgreementApiController extends ApiController {
	private $service;

	public function __construct(
			  $appName,
			  IRequest $request,
			  TermsAgreementService $service
	   ) {
		parent::__construct($appName, $request);
		$this->service = $service;
	}
	/**
	 * @NoAdminRequired
	 *
	 * @NoCSRFRequired
	 *
	 * @param string $version
	 * @return DataResponse
	 */
	public function agree($version): DataResponse {
		try {
			$termsAgreed = $this->service->update($version, "yes");
			return new DataResponse($termsAgreed);
		} catch (Exception $e) {
			error_log(print_r("TermsAgreementApiController-Agree-EXCEPTION=" . $e, true));
			return new DataResponse(null);
		}
	}
	/**
	 * @NoAdminRequired
	 *
	 * @NoCSRFRequired
	 *
	 * @param string $version
	 * @return DataResponse
	 */
	public function isAgreed($version): DataResponse {
		try {
			$termsAgreed = $this->service->isAgreed($version);
			return new DataResponse($termsAgreed);
		} catch (Exception $e) {
			error_log(print_r("TermsAgreementApiController-IsAgreed-EXCEPTION=" . $e, true));
			return new DataResponse(null);
		}
	}
}
