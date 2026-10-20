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

namespace OCA\Sendent\Tests\Unit\Service;

use OCA\Sendent\Db\License;
use OCA\Sendent\Http\SubscriptionValidationHttpClient;
use OCA\Sendent\Service\ConnectedUserService;
use OCA\Sendent\Service\LicenseManager;
use OCA\Sendent\Service\LicenseService;
use OCP\ICache;
use OCP\ICacheFactory;
use OCP\IMemcache;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LicenseManagerTest extends TestCase {

	/** @var SubscriptionValidationHttpClient|MockObject */
	private $validationClient;

	private function license(): License {
		$license = new License();
		$license->setId(42);
		$license->setLicensekey('key');
		$license->setEmail('admin@example.com');
		return $license;
	}

	private function manager(ICache $cache): LicenseManager {
		$cacheFactory = $this->createMock(ICacheFactory::class);
		$cacheFactory->method('createDistributed')->willReturn($cache);
		$this->validationClient = $this->createMock(SubscriptionValidationHttpClient::class);

		return new LicenseManager(
			$this->createMock(LicenseService::class),
			$this->createMock(LoggerInterface::class),
			$this->createMock(ConnectedUserService::class),
			$this->validationClient,
			$cacheFactory
		);
	}

	public function testRenewsWhenMemcacheAddSucceeds(): void {
		$cache = $this->createMock(IMemcache::class);
		$cache->expects($this->once())
			->method('add')
			->with('renewed_42', true, $this->greaterThan(0))
			->willReturn(true);
		$manager = $this->manager($cache);

		$this->validationClient->expects($this->once())->method('validate');

		$manager->renewLicenseIfDue($this->license());
	}

	public function testSkipsWhenMemcacheKeyAlreadySet(): void {
		$cache = $this->createMock(IMemcache::class);
		$cache->method('add')->willReturn(false);
		$manager = $this->manager($cache);

		$this->validationClient->expects($this->never())->method('validate');

		$manager->renewLicenseIfDue($this->license());
	}

	public function testRenewsAndMarksWhenPlainCacheIsEmpty(): void {
		$cache = $this->createMock(ICache::class);
		$cache->method('get')->with('renewed_42')->willReturn(null);
		$cache->expects($this->once())
			->method('set')
			->with('renewed_42', true, $this->greaterThan(0));
		$manager = $this->manager($cache);

		$this->validationClient->expects($this->once())->method('validate');

		$manager->renewLicenseIfDue($this->license());
	}

	public function testSkipsWhenPlainCacheKeyAlreadySet(): void {
		$cache = $this->createMock(ICache::class);
		$cache->method('get')->with('renewed_42')->willReturn(true);
		$cache->expects($this->never())->method('set');
		$manager = $this->manager($cache);

		$this->validationClient->expects($this->never())->method('validate');

		$manager->renewLicenseIfDue($this->license());
	}
}
