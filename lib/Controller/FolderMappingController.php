<?php

namespace OCA\Sendent\Controller;

use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCP\AppFramework\Http;
use OCA\Sendent\Service\FolderMappingService;

class FolderMappingController extends ApiController {

	private FolderMappingService $service;

	public function __construct(
		string $appName,
		IRequest $request,
		FolderMappingService $service
	) {
		parent::__construct($appName, $request);
		$this->service = $service;
	}

	/**
	 * @NoAdminRequired
	 */
	public function getFolderIdByMsId(string $msId): DataResponse {
		$result = $this->service->getByMsId($msId);

		if ($result) {
			return new DataResponse($result);
		}

		return new DataResponse(['error' => 'Mapping not found'], Http::STATUS_NOT_FOUND);
	}

	/**
	 * @NoAdminRequired
	 */
	public function createMapping(string $msId, string $folderId, string $type): JSONResponse {
		if (!in_array($type, ['channel', 'team'])) {
			return new JSONResponse(['error' => 'Invalid type'], Http::STATUS_BAD_REQUEST);
		}

		$this->service->create($msId, $folderId, $type);
		return new JSONResponse(['status' => 'success']);
	}

	/**
	 * @NoAdminRequired
	 */
	public function updateMapping(int $id, string $msId, string $folderId, string $type): JSONResponse {
		if (!in_array($type, ['channel', 'team'])) {
			return new JSONResponse(['error' => 'Invalid type'], Http::STATUS_BAD_REQUEST);
		}

		$this->service->update($id, $msId, $folderId, $type);
		return new JSONResponse(['status' => 'updated']);
	}

	/**
	 * @NoAdminRequired
	 */
	public function deleteMapping(string $msId): JSONResponse {
		$deleted = $this->service->deleteByMsId($msId);

		if ($deleted === 0) {
			return new JSONResponse(['error' => 'Mapping not found'], Http::STATUS_NOT_FOUND);
		}
		return new JSONResponse(['status' => 'deleted']);
	}
}
