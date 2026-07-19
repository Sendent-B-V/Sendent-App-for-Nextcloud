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

namespace OCA\Sendent\Tests\Unit\Compatibility;

use OC\KnownUser\KnownUserService;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IUser;
use OCP\IUserManager;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * Upstream-API compatibility guard for {@see \OCA\Sendent\Service\UserLookupService}.
 *
 * The bulk email->account resolver deliberately bypasses the collaborator search
 * pipeline for performance, and therefore leans directly on a handful of core
 * APIs — including OC\KnownUser\KnownUserService, which is NOT part of the public
 * OCP contract and may change without notice across major Nextcloud releases.
 *
 * Because tests boot a real Nextcloud (see tests/bootstrap.php), running this
 * suite against a new server version turns a silent runtime breakage into a
 * failing test: if any depended-on class/method disappears or changes shape,
 * these assertions fail during CI on the upgraded version, telling us exactly
 * what to re-verify before shipping. Update this test in lockstep with the
 * service whenever the resolution logic changes.
 */
class NextcloudApiCompatibilityTest extends TestCase {

	public function testKnownUserServiceClassStillExists(): void {
		$this->assertTrue(
			class_exists(KnownUserService::class),
			'OC\\KnownUser\\KnownUserService (non-public API) is gone — the phone/known-user'
			. ' gate in UserLookupService must be reworked for this Nextcloud version.'
		);
	}

	public function testKnownUserServiceIsKnownToUserSignature(): void {
		$this->assertTrue(
			method_exists(KnownUserService::class, 'isKnownToUser'),
			'KnownUserService::isKnownToUser() is gone — rework the phone gate.'
		);

		$method = new ReflectionMethod(KnownUserService::class, 'isKnownToUser');
		$this->assertTrue($method->isPublic(), 'isKnownToUser() must be public');

		$params = $method->getParameters();
		$this->assertCount(2, $params, 'isKnownToUser() must take (knownTo, contactUserId)');
		$this->assertSame('string', $this->typeName($params[0]->getType()), 'param 1 (knownTo) must be string');
		$this->assertSame('string', $this->typeName($params[1]->getType()), 'param 2 (contactUserId) must be string');
		$this->assertSame('bool', $this->typeName($method->getReturnType()), 'isKnownToUser() must return bool');
	}

	public function testUserManagerGetByEmailStillExists(): void {
		// The core primitive of the whole feature: exact email -> IUser[].
		$this->assertTrue(
			method_exists(IUserManager::class, 'getByEmail'),
			'IUserManager::getByEmail() is gone — the email lookup must be reworked.'
		);
		$params = (new ReflectionMethod(IUserManager::class, 'getByEmail'))->getParameters();
		$this->assertGreaterThanOrEqual(1, count($params), 'getByEmail() must accept an email argument');
	}

	public function testUserManagerGetStillExists(): void {
		// Used for the legacy-guest uid fallback and to resolve the caller.
		$this->assertTrue(
			method_exists(IUserManager::class, 'get'),
			'IUserManager::get() is gone — the uid fallback / caller lookup must be reworked.'
		);
	}

	public function testUserGetBackendClassNameStillExists(): void {
		// Guest vs regular is decided by the backend name.
		$this->assertTrue(
			method_exists(IUser::class, 'getBackendClassName'),
			'IUser::getBackendClassName() is gone — guest detection must be reworked.'
		);
	}

	public function testGroupManagerGetUserGroupIdsStillExists(): void {
		// Backs the shareapi_only_share_with_group_members gate.
		$this->assertTrue(
			method_exists(IGroupManager::class, 'getUserGroupIds'),
			'IGroupManager::getUserGroupIds() is gone — the group-members gate must be reworked.'
		);
		$params = (new ReflectionMethod(IGroupManager::class, 'getUserGroupIds'))->getParameters();
		$this->assertGreaterThanOrEqual(1, count($params), 'getUserGroupIds() must accept a user argument');
	}

	public function testConfigGetAppValueStillExists(): void {
		// How every shareapi_* privacy setting is read.
		$this->assertTrue(
			method_exists(IConfig::class, 'getAppValue'),
			'IConfig::getAppValue() is gone — privacy-setting reads must be reworked.'
		);
	}

	/**
	 * Soft guard: only meaningful when the nextcloud/guests app is installed on
	 * the test server. Confirms the backend class we key guest detection on is
	 * still present so its 'Guests' backend name remains a valid contract.
	 */
	public function testGuestsBackendContractWhenInstalled(): void {
		if (!class_exists('OCA\\Guests\\UserBackend')) {
			$this->markTestSkipped('nextcloud/guests app not installed on the test server.');
		}

		$this->assertTrue(
			method_exists('OCA\\Guests\\UserBackend', 'getBackendName'),
			'OCA\\Guests\\UserBackend::getBackendName() is gone — re-verify the "Guests" backend name'
			. ' used by UserLookupService::GUESTS_BACKEND.'
		);
	}

	private function typeName(?\ReflectionType $type): ?string {
		return $type instanceof ReflectionNamedType ? $type->getName() : null;
	}
}
