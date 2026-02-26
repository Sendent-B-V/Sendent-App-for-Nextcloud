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

class FileStorageManager {
	private $storage;

	public function __construct($storage) {
		$this->storage = $storage;
	}

	public function writeTxt($group, $key, $content) {
		// check if file exists and write to it if possible
		try {
			try {
				$file = $this->storage->get('/sendent/settings/' . 'settinggroupvaluefile' . $group . '_' . $key . '.txt');
			} catch (\OCP\Files\NotFoundException $e) {
				$this->storage->newFile('/sendent/settings/' . 'settinggroupvaluefile' . $group . '_' . $key . '.txt');
				$this->storage->touch('/sendent/settings/' . 'settinggroupvaluefile' . $group . '_' . $key . '.txt');
				$file = $this->storage->get('/sendent/settings/' . 'settinggroupvaluefile' . $group . '_' . $key . '.txt');
			}

			// the id can be accessed by $file->getId();
			$file->putContent($content);
			return '/sendent/settings/' . 'settinggroupvaluefile' . $group . '_' . $key . '.txt';
		} catch (\OCP\Files\NotPermittedException $e) {
			// you have to create this exception by yourself ;)
			$this->storage->newFolder('/sendent/settings');
			if ($this->storage->nodeExists('/sendent/settings') !== false) {
				return $this->writeTxt($group, $key, $content);
			} else {
				throw new StorageException('Cant write to file');
			}
			return 'pleuris';
		}
	}

	public function fileExists($group, $key): bool {
		try {
			$file = $this->storage->get('/sendent/settings/' . 'settinggroupvaluefile' . $group . '_' . $key . '.txt');
			return true;
		} catch (\OCP\Files\NotFoundException $e) {
			return false;
		}
	}

	public function fileExsistsOld($filename): bool {
		try {
			$file = $this->storage->get('/sendent/settings/' . $filename);
			return true;
		} catch (\OCP\Files\NotFoundException $e) {
			return false;
		}
	}

	/**
	 * @return null|string
	 */
	public function getContent($group, $key) {
		// check if file exists and read from it if possible
		try {
			$file = $this->storage->get('/sendent/settings/' . 'settinggroupvaluefile' . $group . '_' . $key . '.txt');
			if ($file instanceof \OCP\Files\File) {
				return $file->getContent();
			} else {
			}
		} catch (\OCP\Files\NotFoundException $e) {
			return 'error';
		}
	}
}
