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

use OCA\Sendent\Service\FolderMappingService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class FolderMappingController extends ApiController {

	private FolderMappingService $service;

	public function __construct(
		string $appName,
		IRequest $request,
		FolderMappingService $service,
	) {
		parent::__construct($appName, $request);
		$this->service = $service;
	}

	#[NoAdminRequired]
	public function getFolderIdByMsId(string $msId): DataResponse {
		$result = $this->service->getByMsId($msId);
		return $result ? new DataResponse($result)
					   : new DataResponse(['error' => 'Mapping not found'], Http::STATUS_NOT_FOUND);
	}

	#[NoAdminRequired]
	public function createMapping(string $msId, string $folderId, string $type, ?string $nextcloudTeamId = null): JSONResponse {
		if (!in_array($type, ['channel', 'team'], true)) {
			return new JSONResponse(['error' => 'Invalid type'], Http::STATUS_BAD_REQUEST);
		}
		$this->service->create($msId, $folderId, $type, $nextcloudTeamId);
		return new JSONResponse(['status' => 'success']);
	}

	#[NoAdminRequired]
	public function updateMapping(string $msId, string $folderId, string $type, ?string $nextcloudTeamId = null): JSONResponse {
		if (!in_array($type, ['channel', 'team'], true)) {
			return new JSONResponse(['error' => 'Invalid type'], Http::STATUS_BAD_REQUEST);
		}

		$affected = $this->service->updateByMsId($msId, $folderId, $type, $nextcloudTeamId);

		if ($affected === 0) {
			return new JSONResponse(['error' => 'Mapping not found'], Http::STATUS_NOT_FOUND);
		}
		return new JSONResponse(['status' => 'updated']);
	}

	#[NoAdminRequired]
	public function deleteMapping(string $msId): JSONResponse {
		$deleted = $this->service->deleteByMsId($msId);
		return $deleted === 0
			? new JSONResponse(['error' => 'Mapping not found'], Http::STATUS_NOT_FOUND)
			: new JSONResponse(['status' => 'deleted']);
	}
}
