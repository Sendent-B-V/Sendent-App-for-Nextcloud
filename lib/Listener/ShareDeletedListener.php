<?php

namespace OCA\Sendent\Listener;

use OCA\Sendent\Constants;
use OCP\AppFramework\Services\IAppConfig;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Files\Node;
use OCP\Share\Events\ShareDeletedEvent;
use OCP\Share\IManager;
use OCP\Share\IShare;
use OCP\SystemTag\ISystemTagObjectMapper;
use OCP\SystemTag\TagNotFoundException;
use Psr\Log\LoggerInterface;

class ShareDeletedListener implements IEventListener {
	/** @var LoggerInterface */
	private $logger;

	/** @var ISystemTagObjectMapper */
	private $tagObjectMapper;

	/** @var IManager */
	private $shareManager;

	/** @var IAppConfig */
	private $appConfig;

	public function __construct(
		LoggerInterface $logger,
		ISystemTagObjectMapper $tagObjectMapper,
		IManager $shareManager,
		IAppConfig $appConfig,
	) {
		$this->logger = $logger;
		$this->tagObjectMapper = $tagObjectMapper;
		$this->shareManager = $shareManager;
		$this->appConfig = $appConfig;
	}

	public function handle(Event $event): void {
		if (!($event instanceof ShareDeletedEvent)) {
			return;
		}

		/** @var ShareDeletedEvent $event */
		$share = $event->getShare();
		$node = $share->getNode();
		$nodeId = $node->getId();

		$tags = $this->tagObjectMapper->getTagIdsForObjects($nodeId, 'files')[$nodeId];
		$uploadTagId = (int)$this->appConfig->getAppValue(Constants::CONFIG_UPLOAD_TAG, '-1');

		if ($uploadTagId < 0 || !in_array($uploadTagId, $tags)) {
			return;
		}

		if ($this->hasShares($node)) {
			return;
		}

		if ($share->isExpired()) {
			$this->handleExpiredShare($node, $share);
		} else {
			$this->handleRemovedShare($node);
		}
	}

	private function hasShares(Node $node): bool {
		$providers = [
			IShare::TYPE_USER,
			IShare::TYPE_GROUP,
			IShare::TYPE_LINK,
			IShare::TYPE_EMAIL,
			IShare::TYPE_EMAIL,
			IShare::TYPE_CIRCLE,
			IShare::TYPE_ROOM,
			IShare::TYPE_DECK
		];

		$nodeOwner = $node->getOwner();
		$ownerId = $nodeOwner->getUID();

		foreach ($providers as $provider) {
			if (!$this->shareManager->shareProviderExists($provider)) {
				continue;
			}

			$shares = $this->shareManager->getSharesBy($ownerId, $provider, $node, true);

			if (count($shares) > 0) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return void
	 */
	private function handleExpiredShare(Node $node, IShare $share) {
		$expiredTagId = $this->appConfig->getAppValue(Constants::CONFIG_EXPIRED_TAG);

		if ($expiredTagId === null || $expiredTagId === '') {
			return;
		}

		$this->logger->info('Tag file because share is expired', ['nodeId' => $node->getId()]);

		try {
			$this->tagObjectMapper->assignTags($node->getId(), 'files', $expiredTagId);
		} catch (TagNotFoundException $e) {
			$this->logger->warning('Could not tag file, because expired tag does not exist');

			return;
		}

		$expirationTimestamp = $share->getExpirationDate()->getTimestamp();
		$node->touch($expirationTimestamp);
	}

	/**
	 * @return void
	 */
	private function handleRemovedShare(Node $node) {
		$removedTagId = $this->appConfig->getAppValue(Constants::CONFIG_REMOVED_TAG);

		if ($removedTagId === null || $removedTagId === '') {
			return;
		}

		$this->logger->info('Tag file because last share has been removed', ['nodeId' => $node->getId()]);

		try {
			$this->tagObjectMapper->assignTags($node->getId(), 'files', $removedTagId);
		} catch (TagNotFoundException $e) {
			$this->logger->warning('Could not tag file, because removed tag does not exist');

			return;
		}

		$node->touch();
	}
}
