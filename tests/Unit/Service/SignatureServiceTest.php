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

use OCA\Sendent\Service\SignatureService;
use OCP\Accounts\IAccount;
use OCP\Accounts\IAccountManager;
use OCP\Accounts\IAccountProperty;
use OCP\Accounts\PropertyDoesNotExistException;
use OCP\IConfig;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SignatureServiceTest extends TestCase {

	private const USER_ID = 'luc';

	private function user(string $displayName, string $email): IUser {
		$user = $this->createMock(IUser::class);
		$user->method('getDisplayName')->willReturn($displayName);
		$user->method('getEMailAddress')->willReturn($email);
		return $user;
	}

	/**
	 * @param array<string, string> $properties map of account property name => value
	 */
	private function account(array $properties): IAccount {
		$account = $this->createMock(IAccount::class);
		$account->method('getProperty')->willReturnCallback(
			function (string $name) use ($properties): IAccountProperty {
				if (!array_key_exists($name, $properties)) {
					throw new PropertyDoesNotExistException($name);
				}
				$property = $this->createMock(IAccountProperty::class);
				$property->method('getValue')->willReturn($properties[$name]);
				return $property;
			}
		);
		return $account;
	}

	private function userManager(?IUser $user, string $uid = self::USER_ID): IUserManager {
		$manager = $this->createMock(IUserManager::class);
		$manager->method('get')->willReturnCallback(
			static fn (string $requested): ?IUser => $requested === $uid ? $user : null
		);
		return $manager;
	}

	private function accountManager(IUser $user, IAccount $account): IAccountManager {
		$manager = $this->createMock(IAccountManager::class);
		$manager->method('getAccount')->with($user)->willReturn($account);
		return $manager;
	}

	/**
	 * Config stub for logoUrl(): 'theming'/'logoMime' drives which branch is
	 * taken ('' = no custom logo uploaded = core-fallback path), 'cachebuster'
	 * feeds the ?v= query param. Defaults to '' (no custom logo) since this is
	 * the stock state and existing tests' templates never reference {LOGO}.
	 */
	private function config(string $logoMime = '', string $cacheBuster = '0'): IConfig {
		$config = $this->createMock(IConfig::class);
		$config->method('getAppValue')->willReturnMap([
			['theming', 'cachebuster', '0', $cacheBuster],
			['theming', 'logoMime', '', $logoMime],
		]);
		return $config;
	}

	/**
	 * Stubs the core-fallback logo path taken when config('') (no custom logo)
	 * is used: logoUrl() calls imagePath('core', 'logo/logo.png') then
	 * getAbsoluteURL() on the result. Pair with config() (its default).
	 */
	private function urlGenerator(): IURLGenerator {
		$generator = $this->createMock(IURLGenerator::class);
		$generator->method('imagePath')->with('core', 'logo/logo.png')->willReturn('/core/img/logo/logo.png');
		$generator->method('getAbsoluteURL')->with('/core/img/logo/logo.png')->willReturn('https://cloud.example.com/core/img/logo/logo.png');
		return $generator;
	}

	/**
	 * Stubs the themed logo path taken when config('image/png') (a custom logo
	 * is uploaded) is used: logoUrl() calls
	 * linkToRouteAbsolute('theming.Theming.getImage', ['key' => 'logo', 'useSvg' => 0, 'v' => $cacheBuster]).
	 *
	 * @param string|null $logoUrl Return value; null makes it throw \RuntimeException,
	 *                             simulating the theming route being unavailable.
	 */
	private function themedUrlGenerator(?string $logoUrl, string $cacheBuster = '0'): IURLGenerator {
		$generator = $this->createMock(IURLGenerator::class);
		$expectation = $generator->method('linkToRouteAbsolute')
			->with('theming.Theming.getImage', ['key' => 'logo', 'useSvg' => 0, 'v' => $cacheBuster]);
		if ($logoUrl === null) {
			$expectation->willThrowException(new \RuntimeException('theming route unavailable'));
		} else {
			$expectation->willReturn($logoUrl);
		}
		return $generator;
	}

	private function service(
		IUserManager $userManager,
		IAccountManager $accountManager,
		?LoggerInterface $logger = null,
		?IConfig $config = null,
		?IURLGenerator $urlGenerator = null,
	): SignatureService {
		return new SignatureService(
			$userManager,
			$accountManager,
			$config ?? $this->config(),
			$urlGenerator ?? $this->urlGenerator(),
			$logger ?? $this->createMock(LoggerInterface::class),
		);
	}

	/**
	 * Fresh mocks + fresh service per call: composes user()/account()/userManager()/
	 * accountManager()/service() for the common case of a resolvable USER_ID.
	 *
	 * @param array<string, string> $properties map of account property name => value
	 */
	private function serviceFor(string $displayName, string $email, array $properties, ?LoggerInterface $logger = null, ?IConfig $config = null, ?IURLGenerator $urlGenerator = null): SignatureService {
		$user = $this->user($displayName, $email);
		return $this->service(
			$this->userManager($user),
			$this->accountManager($user, $this->account($properties)),
			$logger,
			$config,
			$urlGenerator,
		);
	}

	public function testSubstitutesProfileTags(): void {
		$service = $this->serviceFor('Luc van der Berg', 'l.pasmans@sendent.com', [
			IAccountManager::PROPERTY_PHONE => '+31612345678',
			IAccountManager::PROPERTY_ORGANISATION => 'Sendent',
			IAccountManager::PROPERTY_ROLE => 'Developer',
		]);

		$result = $service->render(
			'<td>{DISPLAYNAME} / {EMAIL} / {PHONE} / {ROLE} at {ORGANISATION}</td>',
			self::USER_ID
		);

		$this->assertSame(
			'<td>Luc van der Berg / l.pasmans@sendent.com / +31612345678 / Developer at Sendent</td>',
			$result
		);
	}

	public function testSplitsDisplayNameWithTussenvoegsel(): void {
		$service = $this->serviceFor('Luc van der Berg', 'l@s.com', []);

		$result = $service->render('{FIRSTNAME}|{MIDDLENAME}|{LASTNAME}', self::USER_ID);

		$this->assertSame('Luc|van der|Berg', $result);
	}

	public function testSplitsTwoPartAndSingleNames(): void {
		$service = $this->serviceFor('Luc Pasmans', 'l@s.com', []);
		$this->assertSame('Luc||Pasmans', $service->render('{FIRSTNAME}|{MIDDLENAME}|{LASTNAME}', self::USER_ID));
	}

	public function testMissingPropertyResolvesToEmptyString(): void {
		// 'pronouns' does not exist as a property on NC 30 — must degrade to ''
		// silently: PropertyDoesNotExistException is the expected path, so it
		// must NOT be logged (unlike the catch-all \Throwable path).
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->never())->method('warning');

		$service = $this->serviceFor('Luc Pasmans', 'l@s.com', [], $logger);

		$this->assertSame('()', $service->render('({PRONOUNS})', self::USER_ID));
	}

	public function testEscapesHtmlInPropertyValues(): void {
		$service = $this->serviceFor('Luc <script>alert(1)</script>', 'l@s.com', []);

		$result = $service->render('{DISPLAYNAME}', self::USER_ID);

		$this->assertStringNotContainsString('<script>', $result);
		$this->assertStringContainsString('&lt;script&gt;', $result);
	}

	public function testBiographyGetsLineBreaks(): void {
		$service = $this->serviceFor('Luc Pasmans', 'l@s.com', [
			IAccountManager::PROPERTY_BIOGRAPHY => "line one\nline two",
		]);

		$result = $service->render('{BIOGRAPHY}', self::USER_ID);

		$this->assertStringContainsString('<br', $result);
		$this->assertStringContainsString('line one', $result);
	}

	public function testUnknownUserReturnsTemplateUnrendered(): void {
		$service = $this->service($this->userManager(null, 'ghost'), $this->createMock(IAccountManager::class));

		$this->assertSame('{DISPLAYNAME}', $service->render('{DISPLAYNAME}', 'ghost'));
	}

	public function testRendersAllFourteenTags(): void {
		// All values are free of HTML-special characters and BIOGRAPHY is kept to
		// a single line, so the expected string does not need to account for
		// escaping or nl2br() — it stays a plain, readable comparison. 'pronouns'
		// is given a PRESENT value here to prove the string-literal property name
		// actually resolves data, not just that it degrades to '' when absent
		// (covered separately by testMissingPropertyResolvesToEmptyString).
		$service = $this->serviceFor('Eva de Wit', 'eva@example.com', [
			'pronouns' => 'she/her',
			IAccountManager::PROPERTY_PHONE => '+31600000000',
			IAccountManager::PROPERTY_ADDRESS => 'Amsterdam',
			IAccountManager::PROPERTY_WEBSITE => 'https://example.com',
			IAccountManager::PROPERTY_ORGANISATION => 'Sendent',
			IAccountManager::PROPERTY_ROLE => 'Engineer',
			IAccountManager::PROPERTY_HEADLINE => 'Building things',
			IAccountManager::PROPERTY_BIOGRAPHY => 'Loves coffee',
		], null, $this->config('image/png'), $this->themedUrlGenerator('https://cloud.example.com/apps/theming/image/logo'));

		$template = '{DISPLAYNAME}|{FIRSTNAME}|{MIDDLENAME}|{LASTNAME}|{PRONOUNS}|{EMAIL}|'
			. '{PHONE}|{LOCATION}|{WEBSITE}|{ORGANISATION}|{ROLE}|{HEADLINE}|{BIOGRAPHY}|{LOGO}';

		$result = $service->render($template, self::USER_ID);

		$this->assertSame(
			'Eva de Wit|Eva|de|Wit|she/her|eva@example.com|'
				. '+31600000000|Amsterdam|https://example.com|Sendent|Engineer|Building things|Loves coffee|'
				. 'https://cloud.example.com/apps/theming/image/logo',
			$result
		);
	}

	public function testLogoFallsBackToCoreLogoWhenNoCustomLogoUploaded(): void {
		$urlGenerator = $this->urlGenerator();
		$urlGenerator->expects($this->never())->method('linkToRouteAbsolute');

		$service = $this->serviceFor('Luc Pasmans', 'l@s.com', [], null, $this->config(''), $urlGenerator);

		$this->assertSame(
			'https://cloud.example.com/core/img/logo/logo.png?v=0',
			$service->render('{LOGO}', self::USER_ID)
		);
	}

	public function testLogoResolvesToEmptyStringWhenThemingRouteUnavailable(): void {
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->never())->method('warning');
		$logger->expects($this->once())->method('debug');

		$service = $this->serviceFor(
			'Luc Pasmans',
			'l@s.com',
			[],
			$logger,
			$this->config('image/png'),
			$this->themedUrlGenerator(null)
		);

		$this->assertSame('()', $service->render('({LOGO})', self::USER_ID));
	}

	public function testLogoUrlIsHtmlEscaped(): void {
		$service = $this->serviceFor(
			'Luc Pasmans',
			'l@s.com',
			[],
			null,
			$this->config('image/png'),
			$this->themedUrlGenerator('https://cloud.example.com/logo?a=1&b=2')
		);

		$result = $service->render('{LOGO}', self::USER_ID);

		$this->assertSame('https://cloud.example.com/logo?a=1&amp;b=2', $result);
	}

	public function testLogoRendersInsideImgSrcAttribute(): void {
		$service = $this->serviceFor(
			'Luc Pasmans',
			'l@s.com',
			[],
			null,
			$this->config('image/png'),
			$this->themedUrlGenerator('https://cloud.example.com/apps/theming/image/logo')
		);

		$result = $service->render('<img src="{LOGO}">', self::USER_ID);

		$this->assertSame('<img src="https://cloud.example.com/apps/theming/image/logo">', $result);
	}

	public function testSingleTokenDisplayName(): void {
		$service = $this->serviceFor('Madonna', 'madonna@example.com', []);

		$this->assertSame('Madonna||', $service->render('{FIRSTNAME}|{MIDDLENAME}|{LASTNAME}', self::USER_ID));
	}

	public function testEmptyDisplayName(): void {
		$service = $this->serviceFor('', 'nobody@example.com', []);

		$this->assertSame('||', $service->render('{FIRSTNAME}|{MIDDLENAME}|{LASTNAME}', self::USER_ID));
	}

	public function testTagLikeValueIsNotReSubstituted(): void {
		// strtr() regression guard: a display name that literally spells out
		// another tag must not be re-substituted by that tag's own value.
		$service = $this->serviceFor('{EMAIL}', 'l@s.com', []);

		$result = $service->render('{DISPLAYNAME}', self::USER_ID);

		$this->assertSame('{EMAIL}', $result);
	}
}
