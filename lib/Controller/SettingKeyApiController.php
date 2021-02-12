<?php

 namespace OCA\Sendent\Controller;

 use OCP\IRequest;
 use OCP\AppFramework\Http\DataResponse;
 use OCP\AppFramework\ApiController;

 use OCA\Sendent\Service\SettingKeyService;

 class SettingKeyApiController extends ApiController {
 	private $service;

 	public function __construct($appName,
	 IRequest $request,
	 SettingKeyService $service,
	 $userId) {
 		parent::__construct($appName, $request);
 		$this->service = $service;
 		$this->userId = $userId;
 	}

 	/**
 	 * @NoAdminRequired
 	 * @NoCSRFRequired
 	 */
 	public function index() {
 		return new DataResponse($this->service->findAll());
 	}

 	/**
 	 * @NoAdminRequired
 	 * @NoCSRFRequired
 	 * @param int $id
 	 */
 	public function show(int $id) {
 		return $this->service->find($id);
 	}

 	/**
 	 * @NoAdminRequired
 	 * @NoCSRFRequired
 	 * @param string $key
 	 */
 	public function showByKey(string $key) {
 		return $this->service->findByKey($key);
 	}
	 
 	/**
 	 * @NoAdminRequired
 	 * @NoCSRFRequired
 	 * @PublicPage
 	 */
 	public function showTheming() {
 		return $this->service->findByTemplateId(1);
 	}

 	/**
 	 * @NoAdminRequired
 	 * @NoCSRFRequired
 	 * @param int $templateid
 	 */
 	public function showByTemplateId(int $templateid) {
 		return $this->service->findByTemplateId($templateid);
 	}

 	/**
 	 * @NoAdminRequired
 	 * @NoCSRFRequired
 	 * @param string $key
 	 * @param string $name
 	 * @param string $templateid
 	 * @param string $valuetype
 	 */
 	public function create(string $key, string $name, string $templateid, string $valuetype) {
 		return $this->service->create($key, $name, $templateid, $valuetype);
 	}

 	/**
 	 * @NoAdminRequired
 	 * @NoCSRFRequired
 	 * @param int $id
 	 * @param string $key
 	 * @param string $name
 	 * @param string $templateid
 	 * @param string $valuetype
 	 */
 	public function update(int $id, string $key, string $name, string $templateid, string $valuetype) {
 		return $this->service->update($id, $key, $name, $templateid, $valuetype);
 	}

 	/**
 	 * @NoAdminRequired
 	 * @NoCSRFRequired
 	 * @param int $id
 	 */
 	public function destroy(int $id) {
 		return $this->service->destroy($id);
 	}
 }
