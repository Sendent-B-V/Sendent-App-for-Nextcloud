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

use OC\Memcache\ArrayCache;
use OCA\Sendent\Controller\ReleasesApiController;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\Http\Client\IResponse;
use OCP\ICacheFactory;
use OCP\IRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ReleasesApiControllerTest extends TestCase {

	/** @var IClient&MockObject */
	private $client;

	/** @var ICacheFactory&MockObject */
	private $cacheFactory;

	/** @var string[] URLs requested from the changelog */
	private $requested = [];

	/** @var string[] URL suffixes that fail when requested */
	private $failing = [];

	public function setUp(): void {
		$this->client = $this->createMock(IClient::class);
		$this->client->method('get')->willReturnCallback(function (string $url) {
			$this->requested[] = $url;
			foreach ($this->failing as $suffix) {
				if (str_ends_with($url, '/' . $suffix)) {
					throw new \Exception('changelog unreachable');
				}
			}
			$response = $this->createMock(IResponse::class);
			$response->method('getBody')->willReturn(json_encode(['title' => 'Release notes v1.0.0', 'url' => $url]));
			return $response;
		});

		// One shared cache, so separate controller instances behave like separate requests
		$cache = new ArrayCache('sendent-releases');
		$this->cacheFactory = $this->createMock(ICacheFactory::class);
		$this->cacheFactory->method('createDistributed')->willReturn($cache);
	}

	private function makeController(): ReleasesApiController {
		$clientService = $this->createMock(IClientService::class);
		$clientService->method('newClient')->willReturn($this->client);
		return new ReleasesApiController(
			'sendent',
			$this->createMock(IRequest::class),
			$clientService,
			$this->createMock(LoggerInterface::class),
			$this->cacheFactory,
		);
	}

	public function testSecondRequestIsServedFromCache(): void {
		$first = $this->makeController()->index()->getData();
		$this->assertCount(5, $this->requested);
		$this->assertArrayHasKey('mac-mail', $first);

		$this->requested = [];
		$second = $this->makeController()->index()->getData();
		$this->assertSame([], $this->requested);
		$this->assertSame($first, $second);
	}

	public function testIndexIsAdminOnlyAndCsrfProtected(): void {
		$method = new \ReflectionMethod(ReleasesApiController::class, 'index');
		$this->assertSame([], $method->getAttributes(NoAdminRequired::class));
		$this->assertSame([], $method->getAttributes(NoCSRFRequired::class));
		$this->assertSame([], $method->getAttributes(PublicPage::class));
	}

	public function testFailedFetchIsRetriedOnNextRequest(): void {
		$this->failing = ['thunderbird'];
		$first = $this->makeController()->index()->getData();
		$this->assertArrayNotHasKey('thunderbird', $first);
		$this->assertCount(4, $first);

		$this->failing = [];
		$this->requested = [];
		$second = $this->makeController()->index()->getData();
		$this->assertCount(1, $this->requested);
		$this->assertStringEndsWith('/thunderbird', $this->requested[0]);
		$this->assertArrayHasKey('thunderbird', $second);
	}
}
