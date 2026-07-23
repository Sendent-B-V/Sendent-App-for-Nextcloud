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

use OC\KnownUser\KnownUserService;
use OCA\Sendent\Service\UserLookupService;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IUser;
use OCP\IUserManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class UserLookupServiceTest extends TestCase {

	private const CALLER = 'caller';

	/**
	 * @param array<string, string> $appValues overrides for core app config
	 */
	private function config(array $appValues = []): IConfig {
		$config = $this->createMock(IConfig::class);
		$config->method('getAppValue')->willReturnCallback(
			static function (string $app, string $key, $default = '') use ($appValues) {
				return $appValues[$key] ?? $default;
			}
		);
		return $config;
	}

	private function user(string $uid, string $backend = 'Database'): IUser {
		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn($uid);
		$user->method('getBackendClassName')->willReturn($backend);
		return $user;
	}

	/**
	 * @param array<string, IUser[]> $byEmail map email => matched users
	 * @param array<string, IUser> $byUid map uid => user (for get())
	 */
	private function userManager(array $byEmail, array $byUid = []): IUserManager {
		$manager = $this->createMock(IUserManager::class);
		$manager->method('getByEmail')->willReturnCallback(
			static fn (string $email): array => $byEmail[$email] ?? []
		);
		$manager->method('get')->willReturnCallback(
			static fn (string $uid): ?IUser => $byUid[$uid] ?? null
		);
		return $manager;
	}

	/**
	 * @param array<string, string[]> $groupsByUid map uid => group ids
	 */
	private function groupManager(array $groupsByUid = []): IGroupManager {
		$manager = $this->createMock(IGroupManager::class);
		$manager->method('getUserGroupIds')->willReturnCallback(
			static fn (IUser $user): array => $groupsByUid[$user->getUID()] ?? []
		);
		return $manager;
	}

	/**
	 * @param array<string, string[]> $known map knownTo => contact uids known to them
	 */
	private function knownUsers(array $known = []): KnownUserService {
		$service = $this->getMockBuilder(KnownUserService::class)
			->disableOriginalConstructor()
			->getMock();
		$service->method('isKnownToUser')->willReturnCallback(
			static fn (string $knownTo, string $contact): bool => in_array($contact, $known[$knownTo] ?? [], true)
		);
		return $service;
	}

	private function service(
		IUserManager $userManager,
		IConfig $config,
		?IGroupManager $groupManager = null,
		?KnownUserService $knownUsers = null,
		?LoggerInterface $logger = null,
	): UserLookupService {
		return new UserLookupService(
			$userManager,
			$config,
			$groupManager ?? $this->groupManager(),
			$knownUsers ?? $this->knownUsers(),
			$logger ?? $this->createMock(LoggerInterface::class),
		);
	}

	public function testResolvesRegularUser(): void {
		$service = $this->service(
			$this->userManager(['alice@example.com' => [$this->user('alice')]], [self::CALLER => $this->user(self::CALLER)]),
			$this->config(),
		);

		$result = $service->resolve(['alice@example.com'], self::CALLER);

		$this->assertSame(['alice@example.com' => ['userId' => 'alice', 'type' => 'user']], $result);
	}

	public function testResolvesGuestUser(): void {
		$service = $this->service(
			$this->userManager(['g@example.com' => [$this->user('guest-uid', 'Guests')]], [self::CALLER => $this->user(self::CALLER)]),
			$this->config(),
		);

		$result = $service->resolve(['g@example.com'], self::CALLER);

		$this->assertSame(['g@example.com' => ['userId' => 'guest-uid', 'type' => 'guest']], $result);
	}

	public function testGuestFallbackByUidWhenEmailNotStored(): void {
		// getByEmail misses, but the uid IS the email and it's a guest backend.
		$service = $this->service(
			$this->userManager(
				[],
				[
					self::CALLER => $this->user(self::CALLER),
					'legacy@example.com' => $this->user('legacy@example.com', 'Guests'),
				],
			),
			$this->config(),
		);

		$result = $service->resolve(['legacy@example.com'], self::CALLER);

		$this->assertSame(['legacy@example.com' => ['userId' => 'legacy@example.com', 'type' => 'guest']], $result);
	}

	public function testUidFallbackIgnoresNonGuest(): void {
		// A regular account whose uid happens to equal the searched string must
		// NOT be resolved via the guest-only fallback.
		$service = $this->service(
			$this->userManager(
				[],
				[
					self::CALLER => $this->user(self::CALLER),
					'someone@example.com' => $this->user('someone@example.com', 'Database'),
				],
			),
			$this->config(),
		);

		$result = $service->resolve(['someone@example.com'], self::CALLER);

		$this->assertSame(['someone@example.com' => null], $result);
	}

	public function testUnknownEmailReturnsNull(): void {
		$service = $this->service(
			$this->userManager([], [self::CALLER => $this->user(self::CALLER)]),
			$this->config(),
		);

		$this->assertSame(['nobody@example.com' => null], $service->resolve(['nobody@example.com'], self::CALLER));
	}

	public function testFullMatchDisabledResolvesNothing(): void {
		$manager = $this->createMock(IUserManager::class);
		$manager->method('get')->willReturn($this->user(self::CALLER));
		// The master full-match switch is off: no email lookup must happen at all.
		$manager->expects($this->never())->method('getByEmail');

		$service = $this->service($manager, $this->config(['shareapi_restrict_user_enumeration_full_match' => 'no']));

		$this->assertSame(['alice@example.com' => null], $service->resolve(['alice@example.com'], self::CALLER));
	}

	public function testFullMatchEmailDisabledResolvesNothing(): void {
		$manager = $this->createMock(IUserManager::class);
		$manager->method('get')->willReturn($this->user(self::CALLER));
		$manager->expects($this->never())->method('getByEmail');

		$service = $this->service($manager, $this->config(['shareapi_restrict_user_enumeration_full_match_email' => 'no']));

		$this->assertSame(['alice@example.com' => null], $service->resolve(['alice@example.com'], self::CALLER));
	}

	public function testGroupMembersGateAllowsCommonGroup(): void {
		$service = $this->service(
			$this->userManager(['bob@example.com' => [$this->user('bob')]], [self::CALLER => $this->user(self::CALLER)]),
			$this->config(['shareapi_only_share_with_group_members' => 'yes']),
			$this->groupManager([self::CALLER => ['g1', 'g2'], 'bob' => ['g2']]),
		);

		$result = $service->resolve(['bob@example.com'], self::CALLER);

		$this->assertSame(['bob@example.com' => ['userId' => 'bob', 'type' => 'user']], $result);
	}

	public function testGroupMembersGateBlocksWhenNoCommonGroup(): void {
		$service = $this->service(
			$this->userManager(['bob@example.com' => [$this->user('bob')]], [self::CALLER => $this->user(self::CALLER)]),
			$this->config(['shareapi_only_share_with_group_members' => 'yes']),
			$this->groupManager([self::CALLER => ['g1'], 'bob' => ['g3']]),
		);

		$this->assertSame(['bob@example.com' => null], $service->resolve(['bob@example.com'], self::CALLER));
	}

	public function testGroupMembersExcludeListRemovesSharedGroup(): void {
		// Caller and bob only share g2, but g2 is excluded from the group-only
		// scope, so bob becomes unresolvable — mirroring core's array_diff.
		$service = $this->service(
			$this->userManager(['bob@example.com' => [$this->user('bob')]], [self::CALLER => $this->user(self::CALLER)]),
			$this->config([
				'shareapi_only_share_with_group_members' => 'yes',
				'shareapi_only_share_with_group_members_exclude_group_list' => '["g2"]',
			]),
			$this->groupManager([self::CALLER => ['g1', 'g2'], 'bob' => ['g2']]),
		);

		$this->assertSame(['bob@example.com' => null], $service->resolve(['bob@example.com'], self::CALLER));
	}

	public function testPhoneGateAllowsKnownUser(): void {
		$service = $this->service(
			$this->userManager(['alice@example.com' => [$this->user('alice')]], [self::CALLER => $this->user(self::CALLER)]),
			$this->config(['shareapi_restrict_user_enumeration_to_phone' => 'yes']),
			null,
			$this->knownUsers([self::CALLER => ['alice']]),
		);

		$result = $service->resolve(['alice@example.com'], self::CALLER);

		$this->assertSame(['alice@example.com' => ['userId' => 'alice', 'type' => 'user']], $result);
	}

	public function testPhoneGateBlocksUnknownUser(): void {
		$service = $this->service(
			$this->userManager(['alice@example.com' => [$this->user('alice')]], [self::CALLER => $this->user(self::CALLER)]),
			$this->config(['shareapi_restrict_user_enumeration_to_phone' => 'yes']),
			null,
			$this->knownUsers([self::CALLER => []]),
		);

		$this->assertSame(['alice@example.com' => null], $service->resolve(['alice@example.com'], self::CALLER));
	}

	public function testSelfLookupBypassesGates(): void {
		// Group-only is active and the caller shares no group with anyone, yet
		// resolving the caller's own email must still succeed.
		$service = $this->service(
			$this->userManager([self::CALLER . '@example.com' => [$this->user(self::CALLER)]], [self::CALLER => $this->user(self::CALLER)]),
			$this->config(['shareapi_only_share_with_group_members' => 'yes']),
			$this->groupManager([self::CALLER => []]),
		);

		$result = $service->resolve([self::CALLER . '@example.com'], self::CALLER);

		$this->assertSame([self::CALLER . '@example.com' => ['userId' => self::CALLER, 'type' => 'user']], $result);
	}

	public function testAmbiguousEmailResolvesNullAndLogsWarning(): void {
		// Nextcloud does not enforce unique email addresses: two accounts can
		// share one. Resolving would silently pick an arbitrary account, so an
		// ambiguous email must resolve to null and be logged as a warning.
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->once())
			->method('warning')
			->with(
				$this->stringContains('multiple accounts'),
				$this->callback(
					static fn (array $context): bool => ($context['email'] ?? null) === 'shared@example.com'
				),
			);

		$service = $this->service(
			$this->userManager(
				['shared@example.com' => [$this->user('alice'), $this->user('bob')]],
				[self::CALLER => $this->user(self::CALLER)],
			),
			$this->config(),
			null,
			null,
			$logger,
		);

		$this->assertSame(['shared@example.com' => null], $service->resolve(['shared@example.com'], self::CALLER));
	}

	public function testAmbiguousEmailDoesNotFallBackToGuestUid(): void {
		// The uid fallback is only for emails that matched NO account; an
		// ambiguous email must not sidestep the ambiguity via the guest path.
		$service = $this->service(
			$this->userManager(
				['shared@example.com' => [$this->user('alice'), $this->user('bob')]],
				[
					self::CALLER => $this->user(self::CALLER),
					'shared@example.com' => $this->user('shared@example.com', 'Guests'),
				],
			),
			$this->config(),
		);

		$this->assertSame(['shared@example.com' => null], $service->resolve(['shared@example.com'], self::CALLER));
	}

	public function testAmbiguousEmailResolvesWhenGatesLeaveSingleAccount(): void {
		// Two accounts share the email, but the group-members gate hides bob
		// from the caller: from the caller's perspective the match is
		// unambiguous, so it must resolve — and not log an ambiguity warning.
		// bob is deliberately listed first to prove this is gate-filtering, not
		// first-element picking.
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->never())->method('warning');

		$service = $this->service(
			$this->userManager(
				['shared@example.com' => [$this->user('bob'), $this->user('alice')]],
				[self::CALLER => $this->user(self::CALLER)],
			),
			$this->config(['shareapi_only_share_with_group_members' => 'yes']),
			$this->groupManager([self::CALLER => ['g1'], 'alice' => ['g1'], 'bob' => ['g9']]),
			null,
			$logger,
		);

		$this->assertSame(
			['shared@example.com' => ['userId' => 'alice', 'type' => 'user']],
			$service->resolve(['shared@example.com'], self::CALLER),
		);
	}

	public function testAmbiguousEmailWithNoResolvableAccountIsNullWithoutWarning(): void {
		// Gates hide every account holding the email: plain unresolvable, not
		// ambiguous — no warning must be logged.
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->never())->method('warning');

		$service = $this->service(
			$this->userManager(
				['shared@example.com' => [$this->user('alice'), $this->user('bob')]],
				[self::CALLER => $this->user(self::CALLER)],
			),
			$this->config(['shareapi_only_share_with_group_members' => 'yes']),
			$this->groupManager([self::CALLER => ['g1'], 'alice' => ['g8'], 'bob' => ['g9']]),
			null,
			$logger,
		);

		$this->assertSame(['shared@example.com' => null], $service->resolve(['shared@example.com'], self::CALLER));
	}

	public function testAmbiguousEmailDoesNotBlockOtherEmailsInBatch(): void {
		$service = $this->service(
			$this->userManager(
				[
					'shared@example.com' => [$this->user('alice'), $this->user('bob')],
					'carol@example.com' => [$this->user('carol')],
				],
				[self::CALLER => $this->user(self::CALLER)],
			),
			$this->config(),
		);

		$this->assertSame(
			[
				'shared@example.com' => null,
				'carol@example.com' => ['userId' => 'carol', 'type' => 'user'],
			],
			$service->resolve(['shared@example.com', 'carol@example.com'], self::CALLER),
		);
	}

	public function testKeysByExactInputTrimsAndSkipsBlanks(): void {
		$service = $this->service(
			$this->userManager(['alice@example.com' => [$this->user('alice')]], [self::CALLER => $this->user(self::CALLER)]),
			$this->config(),
		);

		$result = $service->resolve(['  alice@example.com  ', '', '   ', 'ghost@example.com'], self::CALLER);

		$this->assertSame(
			[
				'alice@example.com' => ['userId' => 'alice', 'type' => 'user'],
				'ghost@example.com' => null,
			],
			$result,
		);
	}
}
