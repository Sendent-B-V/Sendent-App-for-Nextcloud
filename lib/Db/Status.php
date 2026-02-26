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

namespace OCA\Sendent\Db;

use JsonSerializable;

class Status implements JsonSerializable {
	public $version;
	public $currentuserid;
	public $app;
	public $datelicenseend;
	public $maxusers;
	public $validLicense;
	public $licenseaction;
	public $dategraceperiodend;
	public $maxusersgrace;
	public $currentusers;

	public $latestVSTOAddinVersion;
	public $latestNCAppVersion;

	public function __construct() {
		// add types in constructor
	}
	public function jsonSerialize() {
		return [
			'Version' => $this->version,
			'CurrentUserId' => $this->currentuserid,
			'App' => $this->app,
			'DateLicenseEnd' => $this->datelicenseend,
			'MaxUsers' => $this->maxusers,
			'ValidLicense' => $this->validLicense,
			'LicenseAction' => $this->licenseaction,
			'DateGracePeriodEnd' => $this->dategraceperiodend,
			'MaxGraceUsers' => $this->maxusersgrace,
			'CurrentUserCount' => $this->currentusers,
			'LatestVSTOAddinVersion' => $this->latestVSTOAddinVersion,
			'LatestNCAppVersion' => $this->latestNCAppVersion
		];
	}
}
