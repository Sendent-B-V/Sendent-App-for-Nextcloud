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
use OCA\Sendent\Listener\ShareDeletedListener;
use OCP\AppFramework\Services\IAppConfig;
use OCP\Files\File;
use OCP\IUser;
use OCP\Share\Events\ShareDeletedEvent;
use OCP\Share\IManager;
use OCP\Share\IShare;
use OCP\SystemTag\ISystemTagObjectMapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ShareDeletedListenerTest extends TestCase {
	/** @var MockObject */
	private $logger;

	/** @var MockObject */
	private $tagObjectMapper;

	/** @var MockObject */
	private $shareManager;

	/** @var MockObject */
	private $appConfig;

	/** @var ShareDeletedListener */
	private $listener;

	public function setUp(): void {
		if (\OCP\Util::getVersion()[0] < 21) {
			$this->markTestSkipped('Requires at least Nextcloud 21.');
		}

		/** @var LoggerInterface */
		$this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
		/** @var ISystemTagObjectMapper */
		$this->tagObjectMapper = $this->getMockBuilder(ISystemTagObjectMapper::class)->getMock();
		/** @var IManager */
		$this->shareManager = $this->getMockBuilder(IManager::class)->getMock();
		/** @var IAppConfig */
		$this->appConfig = $this->getMockBuilder(IAppConfig::class)->getMock();

		$this->listener = new ShareDeletedListener(
			$this->logger,
			$this->tagObjectMapper,
			$this->shareManager,
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

		$this->tagObjectMapper->expects($this->never())->method('assignTags');

		$event = $this->createEvent();
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

		$this->tagObjectMapper->expects($this->never())->method('assignTags');

		$event = $this->createEvent();
		$this->listener->handle($event);
	}

	public function testNodeHasShares() {
		$this->tagObjectMapper
			->expects($this->once())
			->method('getTagIdsForObjects')
			->with(42, 'files')
			->willReturn([42 => ['7', '8', '1']]);

		$this->appConfig
			->expects($this->once())
			->method('getAppValue')
			->with(Constants::CONFIG_UPLOAD_TAG, '-1')
			->willReturn('1');

		$this->shareManager->method('shareProviderExists')->willReturn(true);
		$this->shareManager
			->expects($this->once())
			->method('getSharesBy')
			->with('smith', 0, $this->anything(), true)
			->willReturn(['']);

		$this->tagObjectMapper->expects($this->never())->method('assignTags');

		$event = $this->createEvent();
		$this->listener->handle($event);
	}

	public function testShareIsExpired() {
		$this->tagObjectMapper
			->expects($this->once())
			->method('getTagIdsForObjects')
			->with(42, 'files')
			->willReturn([42 => ['7', '8', '1']]);

		$this->appConfig
			->expects($this->exactly(2))
			->method('getAppValue')
			->will($this->returnValueMap([
				[Constants::CONFIG_UPLOAD_TAG, '-1', '1'],
				[Constants::CONFIG_EXPIRED_TAG, '', '2'],
			]));

		$this->shareManager->method('shareProviderExists')->willReturn(true);
		$this->shareManager
			->method('getSharesBy')
			->with('smith', $this->anything(), $this->anything(), true, 50, 0)
			->willReturn([]);

		$this->tagObjectMapper->expects($this->once())->method('assignTags')->with(42, 'files', '2');

		$event = $this->createEvent(true);
		$this->listener->handle($event);
	}

	public function testShareIsRemoved() {
		$this->tagObjectMapper
			->expects($this->once())
			->method('getTagIdsForObjects')
			->with(42, 'files')
			->willReturn([42 => ['7', '8', '1']]);

		$this->appConfig
			->expects($this->exactly(2))
			->method('getAppValue')
			->will($this->returnValueMap([
				[Constants::CONFIG_UPLOAD_TAG, '-1', '1'],
				[Constants::CONFIG_REMOVED_TAG, '', '3'],
			]));

		$this->shareManager->method('shareProviderExists')->willReturn(true);
		$this->shareManager
			->method('getSharesBy')
			->with('smith', $this->anything(), $this->anything(), true, 50, 0)
			->willReturn([]);

		$this->tagObjectMapper->expects($this->once())->method('assignTags')->with(42, 'files', '3');

		$event = $this->createEvent(false);
		$this->listener->handle($event);
	}

	private function createEvent(?bool $isExpired = null) {
		$user = $this->getMockBuilder(IUser::class)->getMock();
		$user->method('getUID')->willReturn('smith');
		$node = $this->getMockBuilder(File::class)->getMock();
		$node->method('getId')->willReturn(42);
		$node->method('getOwner')->willReturn($user);
		$share = $this->getMockBuilder(IShare::class)->getMock();
		$share->expects($this->once())->method('getNode')->willReturn($node);

		if ($isExpired !== null) {
			$share->expects($this->once())->method('isExpired')->willReturn($isExpired);

			if ($isExpired) {
				$time = new \DateTime();
				$share->expects($this->once())->method('getExpirationDate')->willReturn($time);
				$node->expects($this->once())->method('touch')->with($time->getTimestamp());
			} else {
				$node->expects($this->once())->method('touch')->with(null);
			}
		}

		/** @var IShare $share */
		return new ShareDeletedEvent($share);
	}
}
