<?php

namespace OCA\Sendent\Tests\Unit\Settings;

use OCA\Sendent\Constants;
use OCA\Sendent\Settings\SendentSettings;
use OCA\Sendent\Service\LicenseManager;
use OCA\Sendent\Service\LicenseService;
use OCP\App\IAppManager;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IAppConfig;
use OCP\AppFramework\Services\IInitialState;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\SystemTag\ISystemTagManager;
use OCP\SystemTag\TagNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SendentSettingsTest extends TestCase {
	/** @var MockObject */
	private $appManager;

	/** @var MockObject */
	private $initialState;

	/** @var MockObject */
	private $appConfig;

	/** @var MockObject */
	private $tagManager;

	/** @var MockObject */
	private $l;

	/** @var MockObject */
	private $licenseManager;

	/** @var MockObject */
	private $licenseService;

	/** @var SendentSettings */
	private $settings;

	public function setUp(): void {
		/** @var IAppManager */
		$this->appManager = $this->getMockBuilder(IAppManager::class)->getMock();
		/** @var IGroupManager */
		$this->groupManager = $this->getMockBuilder(IGroupManager::class)->getMock();
		/** @var IInitialState */
		$this->initialState = $this->getMockBuilder(IInitialState::class)->getMock();
		/** @var IAppConfig */
		$this->appConfig = $this->getMockBuilder(IAppConfig::class)->getMock();
		/** @var ISystemTagManager */
		$this->tagManager = $this->getMockBuilder(ISystemTagManager::class)->getMock();
		/** @var IL10N */
		$this->l = $this->getMockBuilder(IL10N::class)->getMock();
		/** @var LicenseManager */
		$this->licenseManager = $this->getMockBuilder(LicenseManager::class)
										->disableOriginalConstructor()
										->getMock();
		/** @var LicenseService */
		$this->licenseService = $this->getMockBuilder(LicenseService::class)
										->disableOriginalConstructor()
										->getMock();
		$this->licenseService->method('findByGroup')
								->willReturn([]);

		$this->settings = new SendentSettings(
			$this->appManager,
			$this->groupManager,
			$this->initialState,
			$this->appConfig,
			$this->tagManager,
			$this->l,
       		$this->licenseManager,
       		$this->licenseService
		);
	}

	public function testForm() {
		$this->appManager
			->expects($this->exactly(2))
			->method('isInstalled')
			->will($this->returnValueMap([
				['files_retention', true],
				['files_automatedtagging', false],
			]));
		$this->appManager
			->expects($this->once())
			->method('getAppVersion')
			->with('files_retention')
			->willReturn('1.2.3');

		$this->appConfig
			->expects($this->exactly(4))
			->method('getAppValue')
			->will($this->returnValueMap([
				[Constants::CONFIG_UPLOAD_TAG , '', ''],
				[Constants::CONFIG_EXPIRED_TAG , '', '1'],
				[Constants::CONFIG_REMOVED_TAG , '', '2'],
				['sendentGroups' , '', ''],
			]));
		$this->groupManager
			->expects($this->once())
			->method('search')
			->willReturn([]);
		$this->tagManager
			->expects($this->exactly(2))
			->method('getTagsByIds')
			->will($this->returnCallback(function ($tagId) {
				if ($tagId === '2') {
					return [];
				}

				throw new TagNotFoundException();
			}));

		$this->initialState
			->expects($this->exactly(2))
			->method('provideInitialState')
			->will($this->returnValueMap([
				['apps', [
					'files_retention' => '1.2.3',
					'files_automatedtagging' => false,
				]],
				['tags', [
					Constants::CONFIG_UPLOAD_TAG => -1,
					Constants::CONFIG_EXPIRED_TAG => -1,
					Constants::CONFIG_REMOVED_TAG => 42,
				]],
			]));

		$response = $this->settings->getForm();

		$this->assertInstanceOf(TemplateResponse::class, $response);
	}

	public function testSection() {
		$this->assertEquals('sendent', $this->settings->getSection());
	}
}
