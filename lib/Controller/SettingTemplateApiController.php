<?php

 namespace OCA\Sendent\Controller;

 use Exception;

 use OCP\IRequest;
 use OCP\AppFramework\Http;
 use OCP\AppFramework\Http\DataResponse;
 use OCP\AppFramework\Controller;

 use OCA\Sendent\Db\SettingTemplate;
 use OCA\Sendent\Db\SettingTemplateMapper;

 class SettingTemplateApiController extends Controller {
 	private $mapper;

 	public function __construct(IRequest $request, SettingTemplateMapper $mapper) {
 		parent::__construct(
			"sendent",
			$request,
			'PUT, POST, GET, DELETE, PATCH',
			'Authorization, Content-Type, Accept',
			1728000);
 		$this->mapper = $mapper;
 	}

 	/**
 	 * @NoAdminRequired
 	 *
 	 * @NoCSRFRequired
 	 *
 	 * @return DataResponse
 	 */
 	public function index(): DataResponse {
 		return new DataResponse($this->mapper->findAll());
 	}

 	/**
 	 * @NoAdminRequired
 	 *
 	 * @NoCSRFRequired
 	 *
 	 * @param int $id
 	 *
 	 * @return DataResponse
 	 */
 	public function show(int $id): DataResponse {
 		try {
 			return new DataResponse($this->mapper->find($id));
 		} catch (Exception $e) {
 			return new DataResponse([], Http::STATUS_NOT_FOUND);
 		}
 	}
	/**
 	 * @NoAdminRequired
 	 *
 	 * @NoCSRFRequired
 	 *
 	 * @param string $templateKey
 	 *
 	 * @return DataResponse
 	 */
	  public function showByTemplateKey(string $templateKey): DataResponse {
		try {
			return new DataResponse($this->mapper->getByTemplateKey($templateKey));
		} catch (Exception $e) {
			return new DataResponse([], Http::STATUS_NOT_FOUND);
		}
	}
 	/**
 	 * @NoAdminRequired
 	 *
 	 * @NoCSRFRequired
 	 *
 	 * @param string $name
 	 *
 	 * @return DataResponse
 	 */
 	public function create($name): DataResponse {
 		$body = $_POST;
 		$SettingTemplate = new SettingTemplate();
 		$SettingTemplate->setTemplatename($name);
 		return new DataResponse($this->mapper->insert($SettingTemplate));
 	}

 	/**
 	 * @NoAdminRequired
 	 *
 	 * @NoCSRFRequired
 	 *
 	 * @param int $id
 	 * @param string $templatename
 	 *
 	 * @return DataResponse
 	 */
 	public function update(int $id, string $templatename): DataResponse {
 		try {
 			$SettingTemplate = $this->mapper->find($id);
 		} catch (Exception $e) {
 			return new DataResponse([], Http::STATUS_NOT_FOUND);
 		}
 		$SettingTemplate->setTemplatename($templatename);
 		return new DataResponse($this->mapper->update($SettingTemplate));
 	}

 	/**
 	 * @NoAdminRequired
 	 *
 	 * @NoCSRFRequired
 	 *
 	 * @param int $id
 	 *
 	 * @return DataResponse
 	 */
 	public function destroy(int $id): DataResponse {
 		try {
 			$SettingTemplate = $this->mapper->find($id);
 		} catch (Exception $e) {
 			return new DataResponse([], Http::STATUS_NOT_FOUND);
 		}
 		$this->mapper->delete($SettingTemplate);
 		return new DataResponse($SettingTemplate);
 	}
 }
