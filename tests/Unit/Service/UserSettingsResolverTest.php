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

use OCA\Sendent\Db\SettingGroupValue;
use OCA\Sendent\Db\SettingGroupValueMapper;
use OCA\Sendent\Service\SendentFileStorageManager;
use OCA\Sendent\Service\UserSettingsResolver;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Services\IAppConfig;
use OCP\IGroup;
use OCP\IGroupManager;
use OCP\IUser;
use OCP\IUserManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class UserSettingsResolverTest extends TestCase {

	private const USER_ID = 'luc';

	/**
	 * @param string $sendentGroupsJson raw value of the 'sendentGroups' app-config key
	 */
	private function appConfig(string $sendentGroupsJson): IAppConfig {
		$config = $this->createMock(IAppConfig::class);
		$config->method('getAppValue')->willReturnCallback(
			static fn (string $key, string $default = ''): string => $key === 'sendentGroups' ? $sendentGroupsJson : $default
		);
		return $config;
	}

	private function group(string $gid): IGroup {
		$group = $this->createMock(IGroup::class);
		$group->method('getGid')->willReturn($gid);
		return $group;
	}

	/**
	 * @param array<string, string[]> $groupsByUid map uid => group ids the user belongs to
	 */
	private function groupManager(array $groupsByUid = []): IGroupManager {
		$manager = $this->createMock(IGroupManager::class);
		$manager->method('getUserGroups')->willReturnCallback(
			function (IUser $user) use ($groupsByUid): array {
				$gids = $groupsByUid[$user->getUID()] ?? [];
				return array_map(fn (string $gid): IGroup => $this->group($gid), $gids);
			}
		);
		return $manager;
	}

	/**
	 * @param array<string, IUser> $byUid map uid => user, absent uids resolve to null (unknown user)
	 */
	private function userManager(array $byUid): IUserManager {
		$manager = $this->createMock(IUserManager::class);
		$manager->method('get')->willReturnCallback(
			static fn (string $uid): ?IUser => $byUid[$uid] ?? null
		);
		return $manager;
	}

	private function user(string $uid = self::USER_ID): IUser {
		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn($uid);
		return $user;
	}

	/**
	 * @param array<int, array<string, string>> $rows map settingkeyid => [ncgroup => value]
	 */
	private function mapper(array $rows = []): SettingGroupValueMapper {
		$mapper = $this->getMockBuilder(SettingGroupValueMapper::class)
			->disableOriginalConstructor()
			->getMock();
		$mapper->method('findBySettingKeyId')->willReturnCallback(
			function (int $settingkeyid, string $gid = '') use ($rows): SettingGroupValue {
				if (!isset($rows[$settingkeyid][$gid])) {
					throw new DoesNotExistException('not found');
				}
				$row = new SettingGroupValue();
				$row->setSettingkeyid($settingkeyid);
				$row->setGroupid(0);
				$row->setNcgroup($gid);
				$row->setValue($rows[$settingkeyid][$gid]);
				return $row;
			}
		);
		return $mapper;
	}

	/**
	 * $rehydratedContent is null by default, which registers NO blanket
	 * getContent() stub — tests that need to assert on the call (e.g. via
	 * expects()->with()) must not have a competing unconstrained stub already
	 * configured on the same mock.
	 */
	private function fileStorageManager(?string $rehydratedContent = null): SendentFileStorageManager {
		$manager = $this->getMockBuilder(SendentFileStorageManager::class)
			->disableOriginalConstructor()
			->getMock();
		if ($rehydratedContent !== null) {
			$manager->method('getContent')->willReturn($rehydratedContent);
		}
		return $manager;
	}

	private function resolver(
		IAppConfig $appConfig,
		IGroupManager $groupManager,
		IUserManager $userManager,
		SettingGroupValueMapper $mapper,
		?SendentFileStorageManager $fileStorageManager = null,
		?LoggerInterface $logger = null,
	): UserSettingsResolver {
		return new UserSettingsResolver(
			$appConfig,
			$groupManager,
			$userManager,
			$mapper,
			$fileStorageManager ?? $this->fileStorageManager(),
			$logger ?? $this->createMock(LoggerInterface::class),
		);
	}

	// -- sendentGroupFor() ---------------------------------------------------

	public function testSendentGroupForRespectsPriorityOrder(): void {
		// User is a member of the 2nd and 3rd configured groups; the 2nd
		// (higher priority) must win.
		$user = $this->user();
		$resolver = $this->resolver(
			$this->appConfig(json_encode(['group-a', 'group-b', 'group-c'])),
			$this->groupManager([self::USER_ID => ['group-b', 'group-c']]),
			$this->userManager([self::USER_ID => $user]),
			$this->mapper(),
		);

		$this->assertSame('group-b', $resolver->sendentGroupFor(self::USER_ID));
	}

	public function testSendentGroupForReturnsEmptyStringWhenNoMembership(): void {
		$user = $this->user();
		$resolver = $this->resolver(
			$this->appConfig(json_encode(['group-a', 'group-b'])),
			$this->groupManager([self::USER_ID => ['some-other-group']]),
			$this->userManager([self::USER_ID => $user]),
			$this->mapper(),
		);

		$this->assertSame('', $resolver->sendentGroupFor(self::USER_ID));
	}

	public function testSendentGroupForReturnsEmptyStringWhenConfigEmpty(): void {
		$user = $this->user();
		$resolver = $this->resolver(
			$this->appConfig(''),
			$this->groupManager([self::USER_ID => ['group-a']]),
			$this->userManager([self::USER_ID => $user]),
			$this->mapper(),
		);

		$this->assertSame('', $resolver->sendentGroupFor(self::USER_ID));
	}

	public function testSendentGroupForReturnsEmptyStringWhenUserDoesNotExist(): void {
		$resolver = $this->resolver(
			$this->appConfig(json_encode(['group-a'])),
			$this->groupManager(),
			$this->userManager([]), // 'ghost' resolves to null
			$this->mapper(),
		);

		$this->assertSame('', $resolver->sendentGroupFor('ghost'));
	}

	public function testSendentGroupForReturnsEmptyStringWhenConfigIsMalformedJson(): void {
		$user = $this->user();
		$resolver = $this->resolver(
			$this->appConfig('not json'),
			$this->groupManager([self::USER_ID => ['group-a']]),
			$this->userManager([self::USER_ID => $user]),
			$this->mapper(),
		);

		$this->assertSame('', $resolver->sendentGroupFor(self::USER_ID));
	}

	public function testSendentGroupForReturnsEmptyStringWhenConfigIsJsonObjectNotArray(): void {
		// json_decode('{"a":"x"}') yields a stdClass, not an array — must be
		// treated the same as an absent/empty config, not fatal.
		$user = $this->user();
		$resolver = $this->resolver(
			$this->appConfig('{"a":"x"}'),
			$this->groupManager([self::USER_ID => ['group-a']]),
			$this->userManager([self::USER_ID => $user]),
			$this->mapper(),
		);

		$this->assertSame('', $resolver->sendentGroupFor(self::USER_ID));
	}

	// -- effectiveValue() -----------------------------------------------------

	public function testEffectiveValueReturnsGroupOverrideOverDefault(): void {
		$resolver = $this->resolver(
			$this->appConfig(''),
			$this->groupManager(),
			$this->userManager([]),
			$this->mapper([
				800 => ['group-a' => 'True', '' => 'False'],
			]),
		);

		$this->assertSame('True', $resolver->effectiveValue(800, 'group-a'));
	}

	public function testEffectiveValueFallsBackToDefaultWhenGroupHasNoRow(): void {
		$resolver = $this->resolver(
			$this->appConfig(''),
			$this->groupManager(),
			$this->userManager([]),
			$this->mapper([
				800 => ['' => 'False'],
			]),
		);

		$this->assertSame('False', $resolver->effectiveValue(800, 'group-a'));
	}

	public function testEffectiveValueReturnsNullWhenNeitherRowExists(): void {
		$resolver = $this->resolver(
			$this->appConfig(''),
			$this->groupManager(),
			$this->userManager([]),
			$this->mapper([]),
		);

		$this->assertNull($resolver->effectiveValue(800, 'group-a'));
	}

	public function testEffectiveValueRehydratesFileSpilledGroupRowWithItsOwnNcgroup(): void {
		$mapper = $this->mapper([
			801 => ['group-a' => 'group-a_801settinggroupvaluefile.txt'],
		]);
		$fileStorageManager = $this->fileStorageManager();
		$fileStorageManager->expects($this->once())
			->method('getContent')
			->with(0, 801, 'group-a')
			->willReturn('<html>group template</html>');

		$resolver = $this->resolver(
			$this->appConfig(''),
			$this->groupManager(),
			$this->userManager([]),
			$mapper,
			$fileStorageManager,
		);

		$this->assertSame('<html>group template</html>', $resolver->effectiveValue(801, 'group-a'));
	}

	public function testEffectiveValueRehydratesFileSpilledDefaultRowFallbackWithItsOwnNcgroup(): void {
		// The requested ncgroup is 'group-a', but the row that actually serves
		// the value is the DEFAULT group's row (ncgroup ''): rehydration must
		// be called with the row's own ncgroup ('') — not the requested one.
		$mapper = $this->mapper([
			801 => ['' => '_801settinggroupvaluefile.txt'],
		]);
		$fileStorageManager = $this->fileStorageManager();
		$fileStorageManager->expects($this->once())
			->method('getContent')
			->with(0, 801, '')
			->willReturn('<html>default template</html>');

		$resolver = $this->resolver(
			$this->appConfig(''),
			$this->groupManager(),
			$this->userManager([]),
			$mapper,
			$fileStorageManager,
		);

		$this->assertSame('<html>default template</html>', $resolver->effectiveValue(801, 'group-a'));
	}

	public function testEffectiveValueDoesNotRehydrateOrdinaryValues(): void {
		$fileStorageManager = $this->fileStorageManager();
		$fileStorageManager->expects($this->never())->method('getContent');

		$resolver = $this->resolver(
			$this->appConfig(''),
			$this->groupManager(),
			$this->userManager([]),
			$this->mapper([800 => ['' => 'False']]),
			$fileStorageManager,
		);

		$this->assertSame('False', $resolver->effectiveValue(800, ''));
	}

	public function testEffectiveValueReturnsEmptyStringWhenSpilledFileIsMissing(): void {
		// SendentFileStorageManager::getContent() itself returns '' when the
		// appdata file is missing (it swallows NotFoundException internally) —
		// the resolver must surface that '' as-is, never the raw stored
		// '...settinggroupvaluefile...' placeholder path.
		$mapper = $this->mapper([
			801 => ['' => '_801settinggroupvaluefile.txt'],
		]);
		$fileStorageManager = $this->fileStorageManager('');

		$resolver = $this->resolver(
			$this->appConfig(''),
			$this->groupManager(),
			$this->userManager([]),
			$mapper,
			$fileStorageManager,
		);

		$result = $resolver->effectiveValue(801, '');

		$this->assertSame('', $result);
		$this->assertStringNotContainsString('settinggroupvaluefile', $result);
	}

	public function testEffectiveValueReturnsEmptyStringAndLogsWhenRehydrationThrows(): void {
		// getContent() only swallows its OWN NotFoundException; any other
		// storage failure (e.g. NotPermittedException) must not bubble up and
		// 500 this endpoint — it degrades to '' and is logged instead.
		$mapper = $this->mapper([
			801 => ['' => '_801settinggroupvaluefile.txt'],
		]);
		$fileStorageManager = $this->fileStorageManager();
		$fileStorageManager->method('getContent')->willThrowException(new \RuntimeException('storage unavailable'));

		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->once())
			->method('warning')
			->with($this->anything(), $this->callback(static fn (array $context): bool => ($context['settingkeyid'] ?? null) === 801));

		$resolver = $this->resolver(
			$this->appConfig(''),
			$this->groupManager(),
			$this->userManager([]),
			$mapper,
			$fileStorageManager,
			$logger,
		);

		$this->assertSame('', $resolver->effectiveValue(801, ''));
	}
}
