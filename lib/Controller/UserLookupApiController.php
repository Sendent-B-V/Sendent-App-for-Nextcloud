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

use OCA\Sendent\Service\UserLookupService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class UserLookupApiController extends ApiController {

	/** Safety cap on how many emails may be resolved in a single request. */
	private const MAX_EMAILS = 1000;

	private UserLookupService $service;
	private ?string $userId;

	public function __construct(
		string $appName,
		IRequest $request,
		UserLookupService $service,
		?string $userId,
	) {
		parent::__construct($appName, $request);
		$this->service = $service;
		$this->userId = $userId;
	}

	/**
	 * Resolve a batch of email addresses to accounts.
	 *
	 * Authenticated (any logged-in user); not a public page. Body:
	 *   { "emails": ["a@example.com", "b@example.com"] }
	 * Response is keyed by the exact email sent:
	 *   { "a@example.com": { "userId": "alice", "type": "user" },
	 *     "b@example.com": { "userId": "…", "type": "guest" },
	 *     "unknown@example.com": null }
	 *
	 * @param string[] $emails
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function resolve(array $emails = []): DataResponse {
		if ($this->userId === null) {
			return new DataResponse(['error' => 'Authentication required'], Http::STATUS_UNAUTHORIZED);
		}
		if ($emails === []) {
			return new DataResponse([]);
		}
		if (count($emails) > self::MAX_EMAILS) {
			return new DataResponse(
				['error' => 'Too many emails; maximum is ' . self::MAX_EMAILS . ' per request'],
				Http::STATUS_BAD_REQUEST,
			);
		}

		return new DataResponse($this->service->resolve($emails, $this->userId));
	}
}
