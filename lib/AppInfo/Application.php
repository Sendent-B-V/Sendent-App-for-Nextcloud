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
