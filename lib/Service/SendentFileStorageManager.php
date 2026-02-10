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

use OCP\Files\IAppData;
use OCP\Files\NotFoundException;

class SendentFileStorageManager {
	private $appData;

	public function __construct(IAppData $appData) {
		$this->appData = $appData;
		$this->ensureFolderExists();
	}

	private function ensureFolderExists(): void {
		try {
			$folder = $this->appData->getFolder('settings');
		} catch (NotFoundException $e) {
			$folder = $this->appData->newFolder('settings');
		}
	}

	public function writeTxt($group, $key, $content, string $gid = ''): string {
		$this->ensureFolderExists();
		$folder = $this->appData->getFolder('settings');
		$filename = $gid . $group . '_' . $key . 'settinggroupvaluefile.txt';
		try {
			if (!$folder->fileExists($key)) {
				$pngFile = $folder->newFile($filename);
			} else {
				$pngFile = $folder->getFile($filename);
			}
		} catch (NotFoundException $e) {
			$pngFile = $folder->newFile($filename);
		}

		$pngFile->putContent($content);
		return $filename;
	}

	public function writeLicenseTxt(string $content, string $gid = ''): string {
		$this->ensureFolderExists();
		$folder = $this->appData->getFolder('settings');
		try {
			if (!$folder->fileExists('licenseKeyFile')) {
				$pngFile = $folder->newFile($gid . 'licenseKeyFile.txt');
			} else {
				$pngFile = $folder->getFile($gid . 'licenseKeyFile.txt');
			}
		} catch (NotFoundException $e) {
			$pngFile = $folder->newFile($gid . 'licenseKeyFile.txt');
		}

		$pngFile->putContent($content);
		return $gid . 'licenseKeyFile.txt';
	}
	public function writeCurrentlyActiveLicenseTxt(string $content, string $gid = ''): string {
		$this->ensureFolderExists();
		$folder = $this->appData->getFolder('settings');
		try {
			if (!$folder->fileExists('tokenlicenseKeyFile')) {
				$pngFile = $folder->newFile($gid . 'tokenlicenseKeyFile.txt');
			} else {
				$pngFile = $folder->getFile($gid . 'tokenlicenseKeyFile.txt');
			}
		} catch (NotFoundException $e) {
			$pngFile = $folder->newFile($gid . 'tokenlicenseKeyFile.txt');
		}

		$pngFile->putContent($content);
		return $gid . 'tokenlicenseKeyFile.txt';
	}
	public function writeLicenseProductsTxt(string $content, string $gid = ''): string {
		$this->ensureFolderExists();
		$folder = $this->appData->getFolder('settings');
		try {
			if (!$folder->fileExists('licenseProductKeyFile')) {
				$pngFile = $folder->newFile($gid . 'licenseProductKeyFile.txt');
			} else {
				$pngFile = $folder->getFile($gid . 'licenseProductKeyFile.txt');
			}
		} catch (NotFoundException $e) {
			$pngFile = $folder->newFile($gid . 'licenseProductKeyFile.txt');
		}

		$pngFile->putContent($content);
		return $gid . 'licenseProductKeyFile.txt';
	}
	public function fileExists($group, $key): bool {
		try {
			$folder = $this->appData->getFolder('settings');
			$folder->getFile($group . '_' . $key . 'settinggroupvaluefile.txt');
			return true;
		} catch (NotFoundException $e) {
			return false;
		}
	}
	public function fileLicenseExists(): bool {
		try {
			$folder = $this->appData->getFolder('settings');
			$folder->getFile('licenseKeyFile.txt');
			return true;
		} catch (NotFoundException $e) {
			return false;
		}
	}
	public function getContent($group, $key, $gid = '') {
		try {
			$folder = $this->appData->getFolder('settings');
			$file = $folder->getFile($gid . $group . '_' . $key . 'settinggroupvaluefile.txt');
			return $file->getContent();
		} catch (NotFoundException $e) {
			return '';
		}
	}
	public function getLicenseContent($gid = '') {
		try {
			$folder = $this->appData->getFolder('settings');
			$file = $folder->getFile($gid . 'licenseKeyFile.txt');
			return $file->getContent();
		} catch (NotFoundException $e) {
			return '';
		}
	}
	public function getLicenseProductContent($gid = '') {
		try {
			$folder = $this->appData->getFolder('settings');
			$file = $folder->getFile($gid . 'licenseProductKeyFile.txt');
			return $file->getContent();
		} catch (NotFoundException $e) {
			return '';
		}
	}
	public function getCurrentlyActiveLicenseContent($gid = '') {
		try {
			$folder = $this->appData->getFolder('settings');
			$file = $folder->getFile($gid . 'tokenlicenseKeyFile.txt');
			return $file->getContent();
		} catch (NotFoundException $e) {
			return '';
		}
	}
}
