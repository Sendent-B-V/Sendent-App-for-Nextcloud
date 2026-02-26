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

namespace OCA\Sendent\Tests\Unit\Controller;

use OC\SystemTag\SystemTag;

use OCA\Sendent\Controller\TagController;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\SystemTag\ISystemTagManager;
use OCP\SystemTag\TagAlreadyExistsException;
use OCP\SystemTag\TagNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TagControllerTest extends TestCase {
	/** @var MockObject */
	private $request;

	/** @var MockObject */
	private $tagManager;

	/** @var TagController */
	private $controller;

	public function setUp(): void {
		/** @var IRequest */
		$this->request = $this->getMockBuilder(IRequest::class)->getMock();
		/** @var ISystemTagManager */
		$this->tagManager = $this->getMockBuilder(ISystemTagManager::class)->getMock();

		$this->controller = new TagController(
			'sendent',
			$this->request,
			$this->tagManager
		);
	}

	public function testShowExistingTag() {
		$tag = new SystemTag('1', 'foo', true, false);
		$this->tagManager
			->expects($this->once())
			->method('getTagsByIds')
			->with('1')
			->willReturn(['1' => $tag]);

		$response = $this->controller->show('1');

		$this->assertInstanceOf(JSONResponse::class, $response);

		$data = $response->getData();

		$this->assertEquals($tag->getId(), $data['id']);
		$this->assertEquals($tag->getName(), $data['name']);
		$this->assertEquals($tag->isUserVisible(), $data['isVisible']);
		$this->assertEquals($tag->isUserAssignable(), $data['isAssignable']);
	}

	public function testShowNonexistingTag() {
		$this->tagManager
			->expects($this->once())
			->method('getTagsByIds')
			->with('2')
			->willThrowException(new TagNotFoundException());

		$response = $this->controller->show('2');

		$this->assertInstanceOf(JSONResponse::class, $response);

		$this->assertEquals(404, $response->getStatus());
	}

	public function testCreateNewTag() {
		$name = 'foobar';
		$tag = new SystemTag('1', $name, true, false);
		$this->tagManager
			->expects($this->once())
			->method('createTag')
			->with($name, true, false)
			->willReturn($tag);

		$response = $this->controller->create($name);

		$this->assertInstanceOf(JSONResponse::class, $response);

		$data = $response->getData();

		$this->assertEquals($tag->getId(), $data['id']);
		$this->assertEquals($name, $data['name']);
		$this->assertEquals($tag->isUserVisible(), $data['isVisible']);
		$this->assertEquals($tag->isUserAssignable(), $data['isAssignable']);
	}

	public function testCreateDuplicate() {
		$name = 'foobar';
		$tag = new SystemTag('1', $name, true, false);
		$this->tagManager
			->expects($this->once())
			->method('createTag')
			->with($name, true, false)
			->willThrowException(new TagAlreadyExistsException());

		$this->tagManager
			->expects($this->once())
			->method('getTag')
			->with($name, true, false)
			->willReturn($tag);

		$response = $this->controller->create($name);

		$this->assertInstanceOf(JSONResponse::class, $response);

		$data = $response->getData();

		$this->assertEquals($tag->getId(), $data['id']);
		$this->assertEquals($name, $data['name']);
		$this->assertEquals($tag->isUserVisible(), $data['isVisible']);
		$this->assertEquals($tag->isUserAssignable(), $data['isAssignable']);
	}
}
