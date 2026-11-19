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

use OCA\Sendent\Constants;
use OCA\Sendent\Controller\TagController;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Services\IAppConfig;
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

	/** @var IAppConfig&MockObject */
	private $appConfig;

	/** @var TagController */
	private $controller;

	public function setUp(): void {
		/** @var IRequest */
		$this->request = $this->getMockBuilder(IRequest::class)->getMock();
		/** @var ISystemTagManager */
		$this->tagManager = $this->getMockBuilder(ISystemTagManager::class)->getMock();
		$this->appConfig = $this->createMock(IAppConfig::class);

		$this->controller = new TagController(
			'sendent',
			$this->request,
			$this->tagManager,
			$this->appConfig
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

	public function testCreateWithoutConfigKeyDoesNotTouchConfig() {
		$this->tagManager
			->method('createTag')
			->willReturn(new SystemTag('1', 'foobar', true, false));

		$this->appConfig->expects($this->never())->method('setAppValueString');

		$response = $this->controller->create('foobar');

		$this->assertEquals(200, $response->getStatus());
	}

	/**
	 * The tag id must be persisted server-side: since NC 33 the frontend
	 * OCP.AppConfig.setValue() sends the id as a JSON number, which the
	 * provisioning API rejects with a 400 (string $value TypeError).
	 */
	public function testCreateWithConfigKeySavesTagIdAsString() {
		$this->tagManager
			->expects($this->once())
			->method('createTag')
			->with('retention:removed', true, false)
			->willReturn(new SystemTag('8', 'retention:removed', true, false));

		$this->appConfig
			->expects($this->once())
			->method('setAppValueString')
			->with(Constants::CONFIG_REMOVED_TAG, '8');

		$response = $this->controller->create('retention:removed', Constants::CONFIG_REMOVED_TAG);

		$this->assertEquals(200, $response->getStatus());
		$this->assertSame(8, $response->getData()['id']);
	}

	public function testCreateDuplicateWithConfigKeySavesExistingTagId() {
		$this->tagManager
			->method('createTag')
			->willThrowException(new TagAlreadyExistsException());
		$this->tagManager
			->method('getTag')
			->with('retention:expired', true, false)
			->willReturn(new SystemTag('9', 'retention:expired', true, false));

		$this->appConfig
			->expects($this->once())
			->method('setAppValueString')
			->with(Constants::CONFIG_EXPIRED_TAG, '9');

		$response = $this->controller->create('retention:expired', Constants::CONFIG_EXPIRED_TAG);

		$this->assertEquals(200, $response->getStatus());
		$this->assertSame(9, $response->getData()['id']);
	}

	public function testCreateWithUnknownConfigKeyIsRejected() {
		$this->tagManager->expects($this->never())->method('createTag');
		$this->appConfig->expects($this->never())->method('setAppValueString');

		$response = $this->controller->create('foobar', 'licensekey');

		$this->assertEquals(400, $response->getStatus());
	}
}
