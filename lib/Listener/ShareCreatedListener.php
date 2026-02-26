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

namespace OCA\Sendent\Listener;

use OCA\Sendent\Constants;
use OCP\AppFramework\Services\IAppConfig;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Share\Events\ShareCreatedEvent;
use OCP\SystemTag\ISystemTagObjectMapper;
use Psr\Log\LoggerInterface;

class ShareCreatedListener implements IEventListener {
	/** @var LoggerInterface */
	private $logger;

	/** @var ISystemTagObjectMapper */
	private $tagObjectMapper;

	/** @var IAppConfig */
	private $appConfig;

	public function __construct(
		LoggerInterface $logger,
		ISystemTagObjectMapper $tagObjectMapper,
		IAppConfig $appConfig,
	) {
		$this->logger = $logger;
		$this->tagObjectMapper = $tagObjectMapper;
		$this->appConfig = $appConfig;
	}

	public function handle(Event $event): void {
		if (!($event instanceof ShareCreatedEvent)) {
			return;
		}

		/** @var ShareCreatedEvent $event */
		$share = $event->getShare();
		$node = $share->getNode();
		$nodeId = $node->getId();

		$tags = $this->tagObjectMapper->getTagIdsForObjects($nodeId, 'files')[$nodeId];
		$uploadTagId = (int)$this->appConfig->getAppValue(Constants::CONFIG_UPLOAD_TAG, '-1');

		if ($uploadTagId < 0 || !in_array($uploadTagId, $tags)) {
			return;
		}

		$expiredTagId = $this->appConfig->getAppValue(Constants::CONFIG_EXPIRED_TAG);

		if ($expiredTagId !== '' && in_array($expiredTagId, $tags)) {
			$this->logger->info('Unassign expired tag because share was created', ['nodeId' => $nodeId]);

			$this->tagObjectMapper->unassignTags($nodeId, 'files', $expiredTagId);
		}

		$removedTagId = $this->appConfig->getAppValue(Constants::CONFIG_REMOVED_TAG);

		if ($removedTagId !== '' && in_array($removedTagId, $tags)) {
			$this->logger->info('Unassign remove tag because share was created', ['nodeId' => $nodeId]);

			$this->tagObjectMapper->unassignTags($nodeId, 'files', $removedTagId);
		}
	}
}
