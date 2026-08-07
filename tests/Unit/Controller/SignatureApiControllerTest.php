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

use OCA\Sendent\Controller\SignatureApiController;
use OCA\Sendent\Service\SignatureService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use PHPUnit\Framework\TestCase;

class SignatureApiControllerTest extends TestCase {

	private function controller(SignatureService $service, ?string $userId): SignatureApiController {
		return new SignatureApiController(
			'sendent',
			$this->createMock(IRequest::class),
			$service,
			$userId,
		);
	}

	private function service(): SignatureService {
		return $this->getMockBuilder(SignatureService::class)
			->disableOriginalConstructor()
			->getMock();
	}

	public function testRequiresAuthentication(): void {
		$service = $this->service();
		$service->expects($this->never())->method('render');

		$response = $this->controller($service, null)->render('<p>{DISPLAYNAME}</p>');

		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame(Http::STATUS_UNAUTHORIZED, $response->getStatus());
	}

	/**
	 * @dataProvider nonStringHtmlProvider
	 */
	public function testRejectsNonStringBody(mixed $html): void {
		$service = $this->service();
		$service->expects($this->never())->method('render');

		$response = $this->controller($service, 'luc')->render($html);

		$this->assertSame(Http::STATUS_BAD_REQUEST, $response->getStatus());
	}

	public static function nonStringHtmlProvider(): array {
		return [
			'array' => [['not' => 'a string']],
			'int' => [42],
			'bool' => [true],
			// The realistic client bug: a JSON body of {"html": null}.
			'null' => [null],
		];
	}

	public function testRejectsNonStringBodyReportsErrorShape(): void {
		$service = $this->service();
		$service->expects($this->never())->method('render');

		$response = $this->controller($service, 'luc')->render(null);

		$this->assertSame(Http::STATUS_BAD_REQUEST, $response->getStatus());
		$this->assertSame(['error' => 'html must be a string'], $response->getData());
	}

	public function testRejectsOversizedTemplate(): void {
		$service = $this->service();
		$service->expects($this->never())->method('render');

		$response = $this->controller($service, 'luc')->render(str_repeat('x', 100001));

		$this->assertSame(Http::STATUS_BAD_REQUEST, $response->getStatus());
	}

	public function testAcceptsTemplateAtExactSizeLimit(): void {
		$html = str_repeat('x', 100000);

		$service = $this->service();
		$service->expects($this->once())
			->method('render')
			->with($html, 'luc')
			->willReturn('<p>rendered</p>');

		$response = $this->controller($service, 'luc')->render($html);

		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$this->assertSame(['html' => '<p>rendered</p>'], $response->getData());
	}

	public function testRendersForCallingUser(): void {
		$service = $this->service();
		$service->expects($this->once())
			->method('render')
			->with('<p>{DISPLAYNAME}</p>', 'luc')
			->willReturn('<p>Luc Pasmans</p>');

		$response = $this->controller($service, 'luc')->render('<p>{DISPLAYNAME}</p>');

		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$this->assertSame(['html' => '<p>Luc Pasmans</p>'], $response->getData());
	}
}
