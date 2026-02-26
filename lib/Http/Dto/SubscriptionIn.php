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
use OCA\Sendent\Db\License;

class SubscriptionIn implements JsonSerializable {
	protected $key;
	protected $amountusers;
	protected $email;

	public function __construct(License $license, int $connectedusercount) {
		// add types in constructor
		$this->key = $license->getLicensekey();
		$this->email = $license->getEmail();
		$this->amountusers = $connectedusercount;
	}
	public function jsonSerialize() {
		return [
			'Key' => $this->key,
			'AmountUsers' => $this->amountusers,
			'Email' => $this->email
		];
	}
}
