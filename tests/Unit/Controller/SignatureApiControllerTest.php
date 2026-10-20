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
use OCA\Sendent\Service\UserSettingsResolver;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SignatureApiControllerTest extends TestCase {

	private function controller(
		SignatureService $service,
		?string $userId,
		?UserSettingsResolver $resolver = null,
		?LoggerInterface $logger = null,
	): SignatureApiController {
		return new SignatureApiController(
			'sendent',
			$this->createMock(IRequest::class),
			$service,
			$resolver ?? $this->resolver(),
			$logger ?? $this->createMock(LoggerInterface::class),
			$userId,
		);
	}

	private function service(): SignatureService {
		return $this->getMockBuilder(SignatureService::class)
			->disableOriginalConstructor()
			->getMock();
	}

	private function resolver(): UserSettingsResolver {
		return $this->getMockBuilder(UserSettingsResolver::class)
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

	// -- get() -----------------------------------------------------------

	public function testGetRequiresAuthentication(): void {
		$service = $this->service();
		$service->expects($this->never())->method('render');

		$response = $this->controller($service, null)->get();

		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame(Http::STATUS_UNAUTHORIZED, $response->getStatus());
	}

	/**
	 * @dataProvider disabledEnabledValueProvider
	 */
	public function testGetReturnsDisabledShapeWhenNotEnabled(?string $enabledValue): void {
		$resolver = $this->resolver();
		$resolver->method('sendentGroupFor')->with('luc')->willReturn('');
		$resolver->expects($this->once())
			->method('effectiveValue')
			->with(800, '')
			->willReturn($enabledValue);

		$service = $this->service();
		$service->expects($this->never())->method('render');

		// $enabled is false here, so the enabled-but-no-template inconsistency
		// warning (only logged when enabled === true) is never reached.
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->never())->method('warning');

		$response = $this->controller($service, 'luc', $resolver, $logger)->get();

		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$this->assertSame(['enabled' => false, 'html' => null, 'hash' => null], $response->getData());
	}

	public static function disabledEnabledValueProvider(): array {
		return [
			'explicitly False' => ['False'],
			'null (no row at all)' => [null],
		];
	}

	public function testGetReturnsDisabledShapeWhenEnabledValueIsLowercaseTrue(): void {
		// Documents the intentionally strict 'True' comparison, coupled to the
		// exact option value the frontend settings registry stores — 'true'
		// must NOT be treated as enabled.
		$resolver = $this->resolver();
		$resolver->method('sendentGroupFor')->with('luc')->willReturn('');
		$resolver->expects($this->once())
			->method('effectiveValue')
			->with(800, '')
			->willReturn('true');

		$service = $this->service();
		$service->expects($this->never())->method('render');

		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->never())->method('warning');

		$response = $this->controller($service, 'luc', $resolver, $logger)->get();

		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$this->assertSame(['enabled' => false, 'html' => null, 'hash' => null], $response->getData());
	}

	/**
	 * @dataProvider emptyTemplateProvider
	 */
	public function testGetReturnsDisabledShapeWhenEnabledButNoTemplate(?string $template): void {
		$resolver = $this->resolver();
		$resolver->method('sendentGroupFor')->with('luc')->willReturn('group-a');
		$resolver->method('effectiveValue')->willReturnMap([
			[800, 'group-a', 'True'],
			[801, 'group-a', $template],
		]);

		$service = $this->service();
		$service->expects($this->never())->method('render');

		// Enabled 'True' with no resolved template is an inconsistent state
		// (801 normally holds a file-spilled value; a missing appdata file
		// silently degrades to this shape otherwise) — must be logged once.
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->once())->method('warning');

		$response = $this->controller($service, 'luc', $resolver, $logger)->get();

		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$this->assertSame(['enabled' => false, 'html' => null, 'hash' => null], $response->getData());
	}

	public static function emptyTemplateProvider(): array {
		return [
			'null template' => [null],
			'empty string template' => [''],
		];
	}

	public function testGetReturnsRenderedSignatureWhenEnabledWithTemplate(): void {
		$resolver = $this->resolver();
		$resolver->expects($this->once())
			->method('sendentGroupFor')
			->with('luc')
			->willReturn('group-a');
		$resolver->method('effectiveValue')->willReturnMap([
			[800, 'group-a', 'True'],
			[801, 'group-a', '<p>{DISPLAYNAME}</p>'],
		]);

		$rendered = '<p>Luc Pasmans</p>';
		$service = $this->service();
		$service->expects($this->once())
			->method('render')
			->with('<p>{DISPLAYNAME}</p>', 'luc')
			->willReturn($rendered);

		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->never())->method('warning');

		$response = $this->controller($service, 'luc', $resolver, $logger)->get();

		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$this->assertSame(
			['enabled' => true, 'html' => $rendered, 'hash' => sha1($rendered)],
			$response->getData()
		);
	}
}
