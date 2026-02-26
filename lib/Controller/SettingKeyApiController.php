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

use OCA\Sendent\Service\SettingKeyService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\DataResponse;

use OCP\IRequest;

class SettingKeyApiController extends ApiController {
	private $service;

	public function __construct($appName,
		IRequest $request,
		SettingKeyService $service) {
		parent::__construct($appName, $request);
		$this->service = $service;
	}

	/**
	 * @return DataResponse
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function index(): DataResponse {
		return new DataResponse($this->service->findAll());
	}

	/**
	 * @param int $id
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function show(int $id) {
		return $this->service->find($id);
	}

	/**
	 * @param string $key
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function showByKey(string $key) {
		return $this->service->findByKey($key);
	}

	#[NoAdminRequired]
	#[NoCSRFRequired]
	#[PublicPage]
	public function showTheming() {
		return $this->service->findByTemplateId(1);
	}
	/**
	 * @param int $templateid
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function showByTemplateId(int $templateid) {
		return $this->service->findByTemplateId($templateid);
	}
	/**
	 * @param string $key
	 * @param string $name
	 * @param string $templateid
	 * @param string $valuetype
	 */
	public function create(string $key, string $name, string $templateid, string $valuetype) {
		return $this->service->create($key, $name, $templateid, $valuetype);
	}

	/**
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
	 * @param int $id
	 */
	public function destroy(int $id) {
		return $this->service->destroy($id);
	}
}
