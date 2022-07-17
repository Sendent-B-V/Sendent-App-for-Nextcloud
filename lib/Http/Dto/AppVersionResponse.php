<?php

namespace OCA\Sendent\Http\Dto;

use OCA\Sendent\Db\License;
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
