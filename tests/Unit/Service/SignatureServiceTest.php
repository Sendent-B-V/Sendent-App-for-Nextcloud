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

	private function service(
		IUserManager $userManager,
		IAccountManager $accountManager,
		?LoggerInterface $logger = null,
	): SignatureService {
		return new SignatureService($userManager, $accountManager, $logger ?? $this->createMock(LoggerInterface::class));
	}

	/**
	 * Fresh mocks + fresh service per call: composes user()/account()/userManager()/
	 * accountManager()/service() for the common case of a resolvable USER_ID.
	 *
	 * @param array<string, string> $properties map of account property name => value
	 */
	private function serviceFor(string $displayName, string $email, array $properties, ?LoggerInterface $logger = null): SignatureService {
		$user = $this->user($displayName, $email);
		return $this->service(
			$this->userManager($user),
			$this->accountManager($user, $this->account($properties)),
			$logger,
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

	public function testRendersAllThirteenTags(): void {
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
		]);

		$template = '{DISPLAYNAME}|{FIRSTNAME}|{MIDDLENAME}|{LASTNAME}|{PRONOUNS}|{EMAIL}|'
			. '{PHONE}|{LOCATION}|{WEBSITE}|{ORGANISATION}|{ROLE}|{HEADLINE}|{BIOGRAPHY}';

		$result = $service->render($template, self::USER_ID);

		$this->assertSame(
			'Eva de Wit|Eva|de|Wit|she/her|eva@example.com|'
				. '+31600000000|Amsterdam|https://example.com|Sendent|Engineer|Building things|Loves coffee',
			$result
		);
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
