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

namespace OCA\Sendent\Tests\Unit\Listener;

use OCA\Sendent\Constants;
use OCA\Sendent\Listener\ShareCreatedListener;
use OCP\AppFramework\Services\IAppConfig;
use OCP\Files\File;
use OCP\Share\Events\ShareCreatedEvent;
use OCP\Share\IShare;
use OCP\SystemTag\ISystemTagObjectMapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ShareCreatedListenerTest extends TestCase {
	/** @var MockObject */
	private $logger;

	/** @var MockObject */
	private $tagObjectMapper;

	/** @var MockObject */
	private $appConfig;

	/** @var ShareCreatedListener */
	private $listener;

	public function setUp(): void {
		/** @var LoggerInterface */
		$this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
		/** @var ISystemTagObjectMapper */
		$this->tagObjectMapper = $this->getMockBuilder(ISystemTagObjectMapper::class)->getMock();
		/** @var IAppConfig */
		$this->appConfig = $this->getMockBuilder(IAppConfig::class)->getMock();

		$this->listener = new ShareCreatedListener(
			$this->logger,
			$this->tagObjectMapper,
			$this->appConfig
		);
	}

	public function testUploadTagNotConfigured() {
		$this->tagObjectMapper
			->expects($this->once())
			->method('getTagIdsForObjects')
			->with(42, 'files')
			->willReturn([42 => []]);

		$this->appConfig
			->expects($this->once())
			->method('getAppValue')
			->with(Constants::CONFIG_UPLOAD_TAG, '-1')
			->willReturn('-1');

		$this->tagObjectMapper->expects($this->never())->method('unassignTags');

		$event = $this->createShare();
		$this->listener->handle($event);
	}

	public function testNodeNotTagged() {
		$this->tagObjectMapper
			->expects($this->once())
			->method('getTagIdsForObjects')
			->with(42, 'files')
			->willReturn([42 => ['7', '8']]);

		$this->appConfig
			->expects($this->once())
			->method('getAppValue')
			->with(Constants::CONFIG_UPLOAD_TAG, '-1')
			->willReturn('1');

		$this->tagObjectMapper->expects($this->never())->method('unassignTags');

		$event = $this->createShare();
		$this->listener->handle($event);
	}

	public function testRetentionTagsNotConfigured() {
		$this->tagObjectMapper
			->expects($this->once())
			->method('getTagIdsForObjects')
			->with(42, 'files')
			->willReturn([42 => ['7', '8', '1']]);

		$this->appConfig
			->expects($this->exactly(3))
			->method('getAppValue')
			->will($this->returnValueMap([
				[Constants::CONFIG_UPLOAD_TAG, '-1', '1'],
				[Constants::CONFIG_EXPIRED_TAG, '', ''],
				[Constants::CONFIG_REMOVED_TAG, '', ''],
			]));

		$this->tagObjectMapper->expects($this->never())->method('unassignTags');

		$event = $this->createShare();
		$this->listener->handle($event);
	}

	public function testRemoveAssignedRetentionTags() {
		$this->tagObjectMapper
			->expects($this->once())
			->method('getTagIdsForObjects')
			->with(42, 'files')
			->willReturn([42 => ['7', '8', '1', '2', '3']]);

		$this->appConfig
			->expects($this->exactly(3))
			->method('getAppValue')
			->will($this->returnValueMap([
				[Constants::CONFIG_UPLOAD_TAG, '-1', '1'],
				[Constants::CONFIG_EXPIRED_TAG, '', '2'],
				[Constants::CONFIG_REMOVED_TAG, '', '3'],
			]));

		$this->tagObjectMapper
			->expects($this->exactly(2))
			->method('unassignTags')
			->will($this->returnValueMap([
				[42, 'files', '2'],
				[42, 'files', '3'],
			]));

		$event = $this->createShare();
		$this->listener->handle($event);
	}

	public function testRemoveNotAssignedRetentionTags() {
		$this->tagObjectMapper
			->expects($this->once())
			->method('getTagIdsForObjects')
			->with(42, 'files')
			->willReturn([42 => ['7', '8', '1']]);

		$this->appConfig
			->expects($this->exactly(3))
			->method('getAppValue')
			->will($this->returnValueMap([
				[Constants::CONFIG_UPLOAD_TAG, '-1', '1'],
				[Constants::CONFIG_EXPIRED_TAG, '', '2'],
				[Constants::CONFIG_REMOVED_TAG, '', '3'],
			]));

		$this->tagObjectMapper->expects($this->never())->method('unassignTags');

		$event = $this->createShare();
		$this->listener->handle($event);
	}

	private function createShare() {
		$node = $this->getMockBuilder(File::class)->getMock();
		$node->expects($this->once())->method('getId')->willReturn(42);
		$share = $this->getMockBuilder(IShare::class)->getMock();
		$share->expects($this->once())->method('getNode')->willReturn($node);

		/** @var IShare $share */
		return new ShareCreatedEvent($share);
	}
}
