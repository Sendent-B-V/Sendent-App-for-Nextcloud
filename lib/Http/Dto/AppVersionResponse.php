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

namespace OCA\Sendent\Http\Dto;

use JsonSerializable;

class AppVersionResponse implements JsonSerializable {
	public $applicationName;
	public $version;
	public $releaseDate;
	public $urlManual;
	public $urlReleaseNotes;
	public $urlBinary;
	public $additionalInformation;
	public $applicationId;

	public function __construct() {
		// add types in constructor
	}
	public function jsonSerialize() {
		return [
			'ApplicationName' => $this->applicationName,
			'Version' => $this->version,
			'ReleaseDate' => $this->releaseDate,
			'UrlManual' => $this->urlManual,
			'UrlReleaseNotes' => $this->urlReleaseNotes,
			'UrlBinary' => $this->urlBinary,
			'AdditionalInformation' => $this->additionalInformation,
			'ApplicationId' => $this->applicationId
		];
	}
}
