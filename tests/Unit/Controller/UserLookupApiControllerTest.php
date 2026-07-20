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

use OCA\Sendent\Controller\UserLookupApiController;
use OCA\Sendent\Service\UserLookupService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use PHPUnit\Framework\TestCase;

class UserLookupApiControllerTest extends TestCase {

	private function controller(UserLookupService $service, ?string $userId): UserLookupApiController {
		return new UserLookupApiController(
			'sendent',
			$this->createMock(IRequest::class),
			$service,
			$userId,
		);
	}

	private function service(): UserLookupService {
		return $this->getMockBuilder(UserLookupService::class)
			->disableOriginalConstructor()
			->getMock();
	}

	public function testUnauthenticatedReturns401(): void {
		$service = $this->service();
		$service->expects($this->never())->method('resolve');

		$response = $this->controller($service, null)->resolve(['a@example.com']);

		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame(Http::STATUS_UNAUTHORIZED, $response->getStatus());
	}

	public function testEmptyListReturnsEmptyObject(): void {
		$service = $this->service();
		$service->expects($this->never())->method('resolve');

		$response = $this->controller($service, 'caller')->resolve([]);

		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		// Must serialize as {} rather than [] so the response shape is
		// consistently an object.
		$this->assertEquals(new \stdClass(), $response->getData());
		$this->assertSame('{}', json_encode($response->getData()));
	}

	/**
	 * @dataProvider nonArrayEmailsProvider
	 */
	public function testNonArrayEmailsReturns400(mixed $emails): void {
		$service = $this->service();
		$service->expects($this->never())->method('resolve');

		$response = $this->controller($service, 'caller')->resolve($emails);

		$this->assertSame(Http::STATUS_BAD_REQUEST, $response->getStatus());
	}

	public static function nonArrayEmailsProvider(): array {
		return [
			'string' => ['hello'],
			'int' => [42],
			'bool' => [true],
			'null is not the default' => [null],
		];
	}

	public function testTooManyEmailsReturns400(): void {
		$service = $this->service();
		$service->expects($this->never())->method('resolve');

		$emails = array_fill(0, 1001, 'a@example.com');
		$response = $this->controller($service, 'caller')->resolve($emails);

		$this->assertSame(Http::STATUS_BAD_REQUEST, $response->getStatus());
	}

	public function testDelegatesToServiceWithCaller(): void {
		$emails = ['alice@example.com', 'nobody@example.com'];
		$expected = [
			'alice@example.com' => ['userId' => 'alice', 'type' => 'user'],
			'nobody@example.com' => null,
		];

		$service = $this->service();
		$service->expects($this->once())
			->method('resolve')
			->with($emails, 'caller')
			->willReturn($expected);

		$response = $this->controller($service, 'caller')->resolve($emails);

		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		// Cast to object so the endpoint always emits a JSON object.
		$this->assertEquals((object)$expected, $response->getData());
	}
}
