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

namespace OCA\Sendent\Tests\Integration;

use OCA\Sendent\Constants;
use OCA\Sendent\Controller\TagController;
use OCA\Sendent\Settings\SendentSettings;
use OCP\AppFramework\App;
use OCP\AppFramework\Services\IAppConfig;
use OCP\SystemTag\ISystemTagManager;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * Runs against the real Nextcloud services of every server branch in the CI
 * matrix, so a change in how NC stores/reads system tags or app config breaks
 * here instead of on a customer instance.
 *
 * Regression for: retention tag ids never being saved on NC 33+ (frontend
 * OCP.AppConfig.setValue() posted a JSON number → provisioning API 400), which
 * left the settings page at -1 and made files_retention reject duplicate rules.
 *
 * @group DB
 */
class RetentionTagConfigTest extends TestCase {
	private const KEYS = [
		Constants::CONFIG_UPLOAD_TAG,
		Constants::CONFIG_REMOVED_TAG,
		Constants::CONFIG_EXPIRED_TAG,
	];

	/** @var ContainerInterface */
	private $container;

	/** @var ISystemTagManager */
	private $tagManager;

	/** @var IAppConfig */
	private $appConfig;

	/** @var string[] */
	private $createdTagIds = [];

	protected function setUp(): void {
		parent::setUp();
		$this->container = (new App('sendent'))->getContainer();
		$this->tagManager = $this->container->get(ISystemTagManager::class);
		$this->appConfig = $this->container->get(IAppConfig::class);
		$this->clearKeys();
	}

	protected function tearDown(): void {
		if ($this->createdTagIds !== []) {
			$this->tagManager->deleteTags(array_unique($this->createdTagIds));
		}
		$this->clearKeys();
		parent::tearDown();
	}

	public function testUnconfiguredTagsAreServedAsMinusOne(): void {
		$state = $this->renderTagState();

		foreach (self::KEYS as $key) {
			$this->assertSame(-1, $state[$key], "$key should be -1 when not configured");
		}
	}

	public function testCreatedTagIdIsPersistedAndServedToSettingsPage(): void {
		$id = $this->createTag(Constants::CONFIG_REMOVED_TAG);

		// What the share listeners read
		$this->assertSame((string)$id, $this->appConfig->getAppValue(Constants::CONFIG_REMOVED_TAG));
		// What the settings page receives after a reload
		$this->assertSame($id, $this->renderTagState()[Constants::CONFIG_REMOVED_TAG]);
	}

	public function testCreatingAgainReusesTagAndKeepsConfig(): void {
		$name = $this->uniqueTagName();
		$first = $this->createTag(Constants::CONFIG_EXPIRED_TAG, $name);
		$second = $this->createTag(Constants::CONFIG_EXPIRED_TAG, $name);

		$this->assertSame($first, $second);
		$this->assertSame($first, $this->renderTagState()[Constants::CONFIG_EXPIRED_TAG]);
	}

	/**
	 * Instances configured before this fix stored the id through the
	 * provisioning API, which writes an untyped (mixed) value. Overwriting it
	 * must not hit an AppConfig type conflict.
	 */
	public function testOverwritesValueStoredByProvisioningApi(): void {
		$serverConfig = \OC::$server->get(\OCP\IAppConfig::class);
		if (!method_exists($serverConfig, 'setValueMixed')) {
			$this->markTestSkipped('setValueMixed() not available on this server version');
		}
		/** @psalm-suppress UndefinedInterfaceMethod,InternalMethod */
		$serverConfig->setValueMixed('sendent', Constants::CONFIG_UPLOAD_TAG, '999999');

		$id = $this->createTag(Constants::CONFIG_UPLOAD_TAG);

		$this->assertSame((string)$id, $this->appConfig->getAppValue(Constants::CONFIG_UPLOAD_TAG));
		$this->assertSame($id, $this->renderTagState()[Constants::CONFIG_UPLOAD_TAG]);
	}

	public function testDeletedTagIsServedAsMinusOne(): void {
		$id = $this->createTag(Constants::CONFIG_REMOVED_TAG);
		$this->tagManager->deleteTags((string)$id);
		$this->createdTagIds = [];

		$this->assertSame(-1, $this->renderTagState()[Constants::CONFIG_REMOVED_TAG]);
	}

	private function createTag(string $key, ?string $name = null): int {
		$controller = $this->container->get(TagController::class);
		$response = $controller->create($name ?? $this->uniqueTagName(), $key);

		$this->assertSame(200, $response->getStatus());
		$id = $response->getData()['id'];
		$this->assertIsInt($id);
		$this->createdTagIds[] = (string)$id;
		return $id;
	}

	/**
	 * Render the admin settings form and return the 'tags' initial state
	 * exactly as the browser would receive it.
	 *
	 * @return array<string, int>
	 */
	private function renderTagState(): array {
		$this->container->get(SendentSettings::class)->getForm();

		$states = \OC::$server->get(\OC\InitialStateService::class)->getInitialStates();
		$this->assertArrayHasKey('sendent-tags', $states);
		$raw = $states['sendent-tags'];
		return is_string($raw) ? json_decode($raw, true) : $raw;
	}

	private function uniqueTagName(): string {
		return 'sendent-test:' . bin2hex(random_bytes(4));
	}

	private function clearKeys(): void {
		foreach (self::KEYS as $key) {
			$this->appConfig->deleteAppValue($key);
		}
	}
}
