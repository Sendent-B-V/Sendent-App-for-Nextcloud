<?php

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
