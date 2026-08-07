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

use OCA\Sendent\Service\SignatureService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\UserRateLimit;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class SignatureApiController extends ApiController {

	/** Byte cap on the SUBMITTED template (input only — rendering can grow
	 * the string; keeping the final signature under Outlook's 30,000-char
	 * setSignatureAsync limit is the caller's responsibility). */
	private const MAX_TEMPLATE_LENGTH = 100000;

	private SignatureService $service;
	private ?string $userId;

	public function __construct(
		string $appName,
		IRequest $request,
		SignatureService $service,
		?string $userId,
	) {
		parent::__construct($appName, $request);
		$this->service = $service;
		$this->userId = $userId;
	}

	/**
	 * Render a signature template with the CALLING user's own profile data.
	 * Used by the admin preview; intended also for the Outlook/Thunderbird
	 * add-ins (which fetch the applicable template via the existing settings
	 * endpoints first, so group inheritance stays in one place). NOTE: no
	 * #[CORS] attribute is present — if the add-ins call this via browser
	 * fetch from a foreign origin rather than a native HTTP client, this
	 * endpoint (like user_lookup_api#resolve) will need one.
	 *
	 * Body: { "html": "<table>...{DISPLAYNAME}...</table>" }
	 * Response: { "html": "<table>...Luc Pasmans...</table>" }
	 *
	 * The parameter is deliberately untyped: the AppFramework dispatcher does
	 * not coerce request params, so a `string` type declaration would turn a
	 * malformed body into a TypeError (HTTP 500) instead of the 400 below.
	 *
	 * @param mixed $html string on well-formed requests
	 */
	// #[NoCSRFRequired] is safe here: the endpoint is side-effect-free and
	// its JSON response is not readable cross-origin. Revisit if a side
	// effect (e.g. caching) is ever added.
	#[NoAdminRequired]
	#[NoCSRFRequired]
	#[UserRateLimit(limit: 30, period: 60)]
	public function render(mixed $html = ''): DataResponse {
		if ($this->userId === null) {
			return new DataResponse(['error' => 'Authentication required'], Http::STATUS_UNAUTHORIZED);
		}
		if (!is_string($html)) {
			return new DataResponse(['error' => 'html must be a string'], Http::STATUS_BAD_REQUEST);
		}
		if (strlen($html) > self::MAX_TEMPLATE_LENGTH) {
			return new DataResponse(
				['error' => 'Template too large; maximum is ' . self::MAX_TEMPLATE_LENGTH . ' bytes'],
				Http::STATUS_BAD_REQUEST,
			);
		}

		return new DataResponse(['html' => $this->service->render($html, $this->userId)]);
	}
}
