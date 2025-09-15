<?php

namespace OCA\Sendent\AppInfo;

use OCA\Files\Event\LoadAdditionalScriptsEvent;
use OCA\Files_Sharing\Event\BeforeTemplateRenderedEvent;
use OCA\Sendent\Listener\LoadAdditionalScriptsListener;
use OCA\Sendent\Listener\ShareCreatedListener;
use OCA\Sendent\Listener\ShareDeletedListener;
use OCA\Sendent\Service\InitialLoadManager;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\FilesMetadata\IFilesMetadataManager;
use OCP\FilesMetadata\Model\IMetadataValueWrapper;

class Application extends App implements IBootstrap {
	public const APPID = 'sendent';

	/**
	 * @param array $params
	 */
	public function __construct(array $params = []) {
		parent::__construct('sendent', $params);
	}

	public function register(IRegistrationContext $context): void {
		$context->registerEventListener(LoadAdditionalScriptsEvent::class, LoadAdditionalScriptsListener::class);
		$context->registerEventListener(BeforeTemplateRenderedEvent::class, LoadAdditionalScriptsListener::class);

		if (class_exists('\\OCP\\Share\\Events\\ShareDeletedEvent')) {
			$context->registerEventListener(\OCP\Share\Events\ShareDeletedEvent::class, ShareDeletedListener::class);
			$context->registerEventListener(\OCP\Share\Events\ShareCreatedEvent::class, ShareCreatedListener::class);
		}
	}

	public function boot(IBootContext $context): void {
		// Trigger service loading
		$context->getAppContainer()->query(InitialLoadManager::class);

		/** @var IFilesMetadataManager $metadataManager */
		$metadataManager = $context->getServerContainer()->get(IFilesMetadataManager::class);

		// The corresponding WebDAV tag becomes <nc:metadata-sendent-folder-channel-id/>.
		$metadataManager->initMetadata(
			'sendent-msteams-folder-id', 
			IMetadataValueWrapper::TYPE_STRING,
			true,                              // index for SearchDAV
			IMetadataValueWrapper::EDIT_REQ_OWNERSHIP
		);
	}
}
