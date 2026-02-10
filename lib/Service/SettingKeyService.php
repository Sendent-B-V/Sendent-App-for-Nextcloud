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

namespace OCA\Sendent\Service;

use Exception;

use OCA\Sendent\Db\SettingKey;
use OCA\Sendent\Db\SettingKeyMapper;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

class SettingKeyService {
	private $mapper;

	public function __construct(SettingKeyMapper $mapper) {
		$this->mapper = $mapper;
	}

	public function findAll() {
		return $this->mapper->findAll();
	}

	/**
	 * @return never
	 */
	private function handleException(Exception $e) {
		if ($e instanceof DoesNotExistException
			|| $e instanceof MultipleObjectsReturnedException) {
			throw new NotFoundException($e->getMessage());
		} else {
			throw $e;
		}
	}

	public function findByKey(string $key) {
		return $this->mapper->findByKey($key);
	}

	public function findByTemplateId(int $id) {
		return $this->mapper->findByTemplateId($id);
	}
	public function findByTemplateKey(int $key) {
		return $this->mapper->findByTemplateKey($key);
	}
	public function find(int $id) {
		try {
			return $this->mapper->find($id);

			// in order to be able to plug in different storage backends like files
			// for instance it is a good idea to turn storage related exceptions
			// into service related exceptions so controllers and service users
			// have to deal with only one type of exception
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function create(string $key, string $name, string $templateid, string $valuetype): \OCP\AppFramework\Db\Entity {
		$SettingKey = new settingkey();
		$SettingKey->setKey($key);
		$SettingKey->setName($name);
		$SettingKey->setTemplateid($templateid);
		$SettingKey->setValuetype($valuetype);
		return $this->mapper->insert($SettingKey);
	}

	public function update(int $id, string $key, string $name, string $templateid, string $valuetype): \OCP\AppFramework\Db\Entity {
		try {
			$SettingKey = $this->mapper->find($id);
		} catch (Exception $e) {
			$this->handleException($e);
		}
		$SettingKey->setKey($key);
		$SettingKey->setName($name);
		$SettingKey->setTemplateid($templateid);
		$SettingKey->setValuetype($valuetype);
		return $this->mapper->update($SettingKey);
	}

	public function destroy(int $id): \OCP\AppFramework\Db\Entity {
		try {
			$SettingKey = $this->mapper->findById($id);
		} catch (Exception $e) {
			$this->handleException($e);
		}
		$this->mapper->delete($SettingKey);
		return $SettingKey;
	}
}
