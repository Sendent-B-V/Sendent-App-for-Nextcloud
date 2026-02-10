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

use OCA\Sendent\Db\SettingTemplate;
use OCA\Sendent\Db\SettingTemplateMapper;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;

use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class SettingTemplateApiController extends Controller {
	private $mapper;

	public function __construct(IRequest $request, SettingTemplateMapper $mapper) {
		parent::__construct(
			'sendent',
			$request,
			'PUT, POST, GET, DELETE, PATCH',
			'Authorization, Content-Type, Accept',
			3600);
		$this->mapper = $mapper;
	}

	/**
	 * @return DataResponse
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function index(): DataResponse {
		return new DataResponse($this->mapper->findAll());
	}

	/**
	 * @param int $id
	 *
	 * @return DataResponse
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function show(int $id): DataResponse {
		try {
			return new DataResponse($this->mapper->find($id));
		} catch (Exception $e) {
			return new DataResponse([], Http::STATUS_NOT_FOUND);
		}
	}
	/**
	 * @param string $templateKey
	 *
	 * @return DataResponse
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function showByTemplateKey(string $templateKey): DataResponse {
		try {
			return new DataResponse($this->mapper->getByTemplateKey($templateKey));
		} catch (Exception $e) {
			return new DataResponse([], Http::STATUS_NOT_FOUND);
		}
	}
	/**
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
