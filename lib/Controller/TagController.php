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

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\SystemTag\ISystemTag;
use OCP\SystemTag\ISystemTagManager;
use OCP\SystemTag\TagAlreadyExistsException;
use OCP\SystemTag\TagNotFoundException;

class TagController extends Controller {

	/** @var ISystemTagManager */
	private $tagManager;

	public function __construct($appName, IRequest $request, ISystemTagManager $tagManager) {
		parent::__construct($appName, $request);
		$this->tagManager = $tagManager;
	}

	public function show(string $id): JSONResponse {
		try {
			$tags = $this->tagManager->getTagsByIds($id);
		} catch (TagNotFoundException $e) {
			return new JSONResponse([], Http::STATUS_NOT_FOUND);
		}

		return new JSONResponse($this->serializeTag($tags[$id]));
	}

	public function create(string $name): JSONResponse {
		try {
			$tag = $this->tagManager->createTag($name, true, false);
		} catch (TagAlreadyExistsException $e) {
			$tag = $this->tagManager->getTag($name, true, false);
		}

		return new JSONResponse($this->serializeTag($tag));
	}

	/**
	 * @return (bool|int|string)[]
	 *
	 * @psalm-return array{id: int, name: string, isVisible: bool, isAssignable: bool}
	 */
	private function serializeTag(ISystemTag $tag): array {
		return [
			'id' => (int)$tag->getId(),
			'name' => $tag->getName(),
			'isVisible' => $tag->isUserVisible(),
			'isAssignable' => $tag->isUserAssignable(),
		];
	}
}
