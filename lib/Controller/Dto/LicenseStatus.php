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

namespace OCA\Sendent\Controller\Dto;

use JsonSerializable;

class LicenseStatus implements JsonSerializable {
	public $status;
	public $statusKind;
	public $dateExpiration;
	public $dateLastCheck;
	public $email;
	public $licensekey;
	public $level;
	public $ncgroup;
	public $product;
	public $istrial;

	public function __construct(string $status, string $statusKind,
		string $level, string $licensekey,
		string $dateExpiration, string $dateLastCheck, string $email, string $product = '', int $istrial = -1, string $ncgroup = '') {
		// add types in constructor
		$this->status = $status;
		$this->statusKind = $statusKind;
		$this->licensekey = $licensekey;
		$this->dateExpiration = $dateExpiration;
		$this->dateLastCheck = $dateLastCheck;
		$this->email = $email;
		$this->level = $level;
		$this->ncgroup = $ncgroup;
		$this->product = $product;
		$this->istrial = $istrial;
	}

	public function jsonSerialize() {
		return [
			'status' => $this->status,
			'statusKind' => $this->statusKind,
			'dateExpiration' => $this->dateExpiration,
			'email' => $this->email,
			'level' => $this->level,
			'licensekey' => $this->licensekey,
			'dateLastCheck' => $this->dateLastCheck,
			'ncgroup' => $this->ncgroup,
			'product' => $this->product,
			'istrial' => $this->istrial,
		];
	}
}
