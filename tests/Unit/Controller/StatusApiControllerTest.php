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

use OCA\Sendent\Controller\StatusApiController;
use OCA\Sendent\Db\License;
use OCA\Sendent\Http\AppVersionHttpClient;
use OCA\Sendent\Service\LicenseManager;
use OCA\Sendent\Service\LicenseService;
use OCP\App\IAppManager;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StatusApiControllerTest extends TestCase {

	/** @var IAppManager&MockObject */
	private $appManager;

	/** @var LicenseManager&MockObject */
	private $licenseManager;

	/** @var AppVersionHttpClient&MockObject */
	private $appVersionClient;

	/** @var LicenseService&MockObject */
	private $licenseService;

	public function setUp(): void {
		$this->appManager = $this->createMock(IAppManager::class);
		$this->licenseManager = $this->getMockBuilder(LicenseManager::class)
			->disableOriginalConstructor()
			->getMock();
		$this->appVersionClient = $this->getMockBuilder(AppVersionHttpClient::class)
			->disableOriginalConstructor()
			->getMock();
		$this->licenseService = $this->getMockBuilder(LicenseService::class)
			->disableOriginalConstructor()
			->getMock();
	}

	private function controller(?string $userId = 'alice'): StatusApiController {
		return new StatusApiController(
			'sendent',
			$this->createMock(IRequest::class),
			$this->appManager,
			$userId,
			$this->licenseManager,
			$this->appVersionClient,
			$this->licenseService,
		);
	}

	/**
	 * @param bool $guestsEnabled whether the Guests app reports as enabled
	 * @param string $guestsVersion version the app manager returns for 'guests'
	 */
	private function stubApps(bool $guestsEnabled, string $guestsVersion = '4.7.0'): void {
		$this->appManager->method('isEnabledForUser')
			->with('guests')
			->willReturn($guestsEnabled);
		$this->appManager->method('getAppVersion')
			->willReturnCallback(function (string $appId) use ($guestsVersion): string {
				return $appId === 'guests' ? $guestsVersion : '4.6.0';
			});
	}

	/** A licence that is complete, valid and in offline mode so no HTTP calls are made. */
	private function validOfflineLicense(): License {
		$license = new License();
		$license->setId(1);
		$license->setEmail('OFFLINE_admin@example.com');
		$license->setLicensekey('key');
		$license->setLevel('Valid');
		$license->setSubscriptionstatus('');
		$license->setDatelastchecked(date('Y-m-d H:i:s'));
		$license->setDatelicenseend(date('Y-m-d', strtotime('+1 year')));
		$license->setDategraceperiodend(date('Y-m-d', strtotime('+1 year')));
		$license->setMaxusers(10);
		$license->setMaxgraceusers(12);
		return $license;
	}

	public function testUnlicensedUserStillGetsGuestsFields(): void {
		$this->stubApps(true);
		$this->licenseService->method('findUserLicense')->willReturn(null);

		$response = $this->controller()->index();

		$this->assertInstanceOf(DataResponse::class, $response);
		$data = $response->getData()->jsonSerialize();
		$this->assertSame('Free', $data['LicenseAction']);
		$this->assertTrue($data['GuestsAppEnabled']);
		$this->assertSame('4.7.0', $data['GuestsAppVersion']);
	}

	public function testGuestsAppDisabledReportsFalseAndNullVersion(): void {
		$this->stubApps(false);
		$this->licenseService->method('findUserLicense')->willReturn(null);

		$data = $this->controller()->index()->getData()->jsonSerialize();

		$this->assertArrayHasKey('GuestsAppEnabled', $data);
		$this->assertArrayHasKey('GuestsAppVersion', $data);
		$this->assertFalse($data['GuestsAppEnabled']);
		$this->assertNull($data['GuestsAppVersion']);
	}

	public function testVersionIsNotLookedUpWhenGuestsAppIsDisabled(): void {
		$this->appManager->method('isEnabledForUser')->willReturn(false);
		// Only the sendent app version may be requested; asking for 'guests'
		// would leak the '0' placeholder the app manager returns for unknown apps.
		$this->appManager->expects($this->once())
			->method('getAppVersion')
			->with('sendent')
			->willReturn('4.6.0');
		$this->licenseService->method('findUserLicense')->willReturn(null);

		$data = $this->controller()->index()->getData()->jsonSerialize();

		$this->assertNull($data['GuestsAppVersion']);
	}

	public function testLicensedUserGetsGuestsFieldsAlongsideLicenseData(): void {
		$this->stubApps(true, '4.8.1');
		$this->licenseService->method('findUserLicense')->willReturn($this->validOfflineLicense());
		$this->licenseManager->method('getCurrentUserCount')->willReturn(3);
		$this->appVersionClient->expects($this->never())->method('latest');

		$data = $this->controller()->index()->getData()->jsonSerialize();

		$this->assertSame('Valid', $data['LicenseAction']);
		$this->assertTrue($data['ValidLicense']);
		$this->assertSame(3, $data['CurrentUserCount']);
		$this->assertTrue($data['GuestsAppEnabled']);
		$this->assertSame('4.8.1', $data['GuestsAppVersion']);
	}

	public function testExistingFieldsAreUnchanged(): void {
		$this->stubApps(false);
		$this->licenseService->method('findUserLicense')->willReturn(null);

		$data = $this->controller('bob')->index()->getData()->jsonSerialize();

		$this->assertSame([
			'Version',
			'CurrentUserId',
			'App',
			'DateLicenseEnd',
			'MaxUsers',
			'ValidLicense',
			'LicenseAction',
			'DateGracePeriodEnd',
			'MaxGraceUsers',
			'CurrentUserCount',
			'LatestVSTOAddinVersion',
			'LatestNCAppVersion',
			'GuestsAppEnabled',
			'GuestsAppVersion',
		], array_keys($data));
		$this->assertSame('4.6.0', $data['Version']);
		$this->assertSame('bob', $data['CurrentUserId']);
		$this->assertSame('sendent', $data['App']);
	}
}
