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

namespace OCA\Sendent\Controller;

use Exception;
use OCA\Sendent\Controller\Dto\LicenseStatus;
use OCA\Sendent\Db\License;
use OCA\Sendent\Service\LicenseManager;
use OCA\Sendent\Service\LicenseService;
use OCA\Sendent\Service\NotFoundException;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Services\IAppConfig;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IUserManager;
use Psr\Log\LoggerInterface;

class LicenseApiController extends ApiController {
	private $appConfig;
	private $service;
	private $groupManager;
	private $userId;
	private $userManager;

	private $licensemanager;

	/** @var IL10N */
	private $l;

	/** @var LoggerInterface */
	private $logger;

	public function __construct(
		$appName,
		IAppConfig $appConfig,
		IRequest $request,
		IGroupManager $groupManager,
		IUserManager $userManager,
		LicenseManager $licensemanager,
		LicenseService $licenseservice,
		LoggerInterface $logger,
		IL10N $l,
		$userId,
	) {
		parent::__construct($appName, $request);
		$this->appConfig = $appConfig;
		$this->service = $licenseservice;
		$this->groupManager = $groupManager;
		$this->userId = $userId;
		$this->userManager = $userManager;
		$this->licensemanager = $licensemanager;
		$this->logger = $logger;
		$this->l = $l;
	}
	/**
	 * @return never
	 */
	private function handleException($e) {
		if (
			$e instanceof DoesNotExistException
			|| $e instanceof MultipleObjectsReturnedException
		) {
			throw new NotFoundException($e->getMessage());
		} else {
			throw $e;
		}
	}

	/**
	 * Returns license status for current user
	 *
	 * @return DataResponse
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function show(): DataResponse {

		$this->logger->info('Getting license information for user ' . $this->userId);

		// Gets groups for which specific settings and/or license are defined
		// Groups are ordered from highest priority to lowest
		$sendentGroups = $this->appConfig->getAppValue('sendentGroups', '');
		$sendentGroups = $sendentGroups !== '' ? json_decode($sendentGroups) : [];

		// Gets user groups
		$user = $this->userManager->get($this->userId);
		$userGroups = $this->groupManager->getUserGroups($user);
		$userGroups = array_map(function ($group) {
			return $group->getGid();
		}, $userGroups);

		// Gets user groups that are sendentGroups
		$userSendentGroups = array_intersect($sendentGroups, $userGroups);

		// Returns settings for 1st matching group
		if (count($userSendentGroups)) {
			$this->logger->info('First matching group of user ' . $this->userId . ' is ' . $userSendentGroups[array_keys($userSendentGroups)[0]]);
			return $this->showForNCGroup($userSendentGroups[array_keys($userSendentGroups)[0]]);
		} else {
			$this->logger->info('User ' . $this->userId . ' is not member of any sendent group');
			return $this->showForNCGroup('');
		}

	}
	/**
	 * Returns license status for current user
	 *
	 * @return DataResponse
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function showInternal(): DataResponse {

		$this->logger->info('Getting license information for user ' . $this->userId);

		// Gets groups for which specific settings and/or license are defined
		// Groups are ordered from highest priority to lowest
		$sendentGroups = $this->appConfig->getAppValue('sendentGroups', '');
		$sendentGroups = $sendentGroups !== '' ? json_decode($sendentGroups) : [];

		// Gets user groups
		$user = $this->userManager->get($this->userId);
		$userGroups = $this->groupManager->getUserGroups($user);
		$userGroups = array_map(function ($group) {
			return $group->getGid();
		}, $userGroups);

		// Gets user groups that are sendentGroups
		$userSendentGroups = array_intersect($sendentGroups, $userGroups);

		// Returns settings for 1st matching group
		if (count($userSendentGroups)) {
			$this->logger->info('First matching group of user ' . $this->userId . ' is ' . $userSendentGroups[array_keys($userSendentGroups)[0]]);
			return $this->showForNCGroup($userSendentGroups[array_keys($userSendentGroups)[0]]);
		} else {
			$this->logger->info('User ' . $this->userId . ' is not member of any sendent group');
			return $this->showForNCGroup('');
		}

	}
	/**
	 * Returns license status for group $ncgroup
	 *
	 * @param string $ncgroup
	 * @return DataResponse
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function showForNCGroup(string $ncgroup = ''): DataResponse {

		if ($ncgroup === '') {
			$this->logger->info('Getting license information for default group');
		} else {
			$this->logger->info('Getting license information for group ' . $ncgroup);
		}

		try {
			// Gets license for group $ncgroup
			$result = $this->service->findByGroup($ncgroup);
			if (isset($result) && $result !== null && $result !== false && is_array($result) && count($result) === 0) {
				// No license for group $ncgroup, getting default license
				$result = $this->service->findByGroup('');
			}

			if (isset($result) && $result !== null && $result !== false) {
				if (is_array($result) && count($result) > 0
				&& $result[0]->getLevel() != 'Error_clear' && $result[0]->getLevel() != 'Error_incomplete') {


					$this->logger->info('Check needed for license ' . $result[0]->getId());
					try {
						$this->licensemanager->renewLicense($result[0]);
						$result = $this->service->findByGroup($result[0]->getNcgroup());
						if (isset($result) && $result !== null && $result !== false) {
							if (is_array($result) && count($result) > 0
							&& $result[0]->getLevel() != 'Error_clear' && $result[0]->getLevel() != 'Error_incomplete') {
							} else {
								//throw new Exception();
							}
						}
					} catch (Exception $e) {
						$this->logger->error('Error while renewing license ' . $result[0]->getId() . '\r\n' . $e);
					}

					// Reports license status
					$email = $result[0]->getEmail();
					$licensekey = $result[0]->getLicensekeytoken();
					$this->logger->info('License key returned is: ' . $licensekey);
					$dateExpiration = $result[0]->getDatelicenseend();
					$dateLastCheck = $result[0]->getDatelastchecked();
					$level = $result[0]->getLevel();
					$group = $result[0]->getNcgroup();
					$product = $result[0]->getProduct();
					$istrial = $result[0]->getIstrial();
					$statusKind = '';
					$status = '';

					if ($result[0]->isCleared()) {
						$status = $this->l->t('No license configured');
						$statusKind = 'nolicense';
					} elseif ($result[0]->isIncomplete()) {
						$status = $this->l->t('Missing email address or license key.');
						$statusKind = 'error_incomplete';
					} elseif ($result[0]->isCheckNeeded()) {
						$status = $this->l->t('Revalidation of your license is required');
						$statusKind = 'check';
					} elseif (!$result[0]->isCheckNeeded() && !$result[0]->isLicenseExpired() && !$result[0]->isSupportedProduct()) {
						$status = $this->l->t('Current license is not intended to be used with Sendent MS Office products. It is only intended to be used for configuring: ' . $product)
							. '</br>'
							. $this->l->t('%1$sContact support%2$s if you experience issues with configuring your license key.', ["<a href='mailto:info@sendent.com' style='color:blue'>", '</a>']);
						$statusKind = 'expired';
					} elseif ($result[0]->isLicenseRenewedOrSwitched()) {
						$status = $this->l->t('Current license has been changed.')
							. '</br>'
							. $this->l->t('%1$sContact support%2$s if you experience issues with configuring your license key.', ["<a href='mailto:info@sendent.com' style='color:blue'>", '</a>']);
						$statusKind = 'check';
					} elseif ($result[0]->isLicenseInactive()) {
						$status = $this->l->t('Current license has been deactivated by Sendent.')
							. '</br>'
							. $this->l->t('%1$sContact support%2$s for more information.', ["<a href='mailto:info@sendent.com' style='color:blue'>", '</a>']);
						$statusKind = 'check';
					} elseif ($result[0]->isLicenseSuspended()) {
						$status = $this->l->t('Current license has been suspended by Sendent.')
							. '</br>'
							. $this->l->t('%1$sContact support%2$s for more information.', ["<a href='mailto:info@sendent.com' style='color:blue'>", '</a>']);
						$statusKind = 'check';
					} elseif ($result[0]->isLicenseExpired()) {
						$status = $this->l->t('Current license has expired.')
							. '</br>'
							. $this->l->t('%1$sContact support%2$s if you experience issues with configuring your license key.', ["<a href='mailto:info@sendent.com' style='color:blue'>", '</a>']);
						$statusKind = 'expired';
					} elseif (!$result[0]->isCheckNeeded() && !$result[0]->isLicenseExpired()) {
						$status = $this->l->t('Current license is valid');
						$statusKind = 'valid';
					} elseif (!$this->licensemanager->isWithinUserCount($result[0]) && $this->licensemanager->isWithinGraceUserCount($result[0])) {
						$status = $this->l->t('Current amount of active users exceeds licensed amount. Some users might not be able to use Sendent.');
						$statusKind = 'userlimit';
					} elseif (!$this->licensemanager->isWithinUserCount($result[0]) && !$this->licensemanager->isWithinGraceUserCount($result[0])) {
						$status = $this->l->t('Current amount of active users exceeds licensed amount. Additional users trying to use Sendent will be prevented from doing so.');
						$statusKind = 'userlimit';
					}
					return new DataResponse(new LicenseStatus($status, $statusKind, $level, $licensekey, $dateExpiration, $dateLastCheck, $email, $product, $istrial, $group));
				} elseif (count($result) > 0 && $result[0]->getLevel() == 'Error_incomplete') {
					$email = $result[0]->getEmail();
					$licensekey = $result[0]->getLicensekey();
					$group = $result[0]->getNcgroup();
					$status = $this->l->t('Missing (or incorrect) email address or license key. %1$sContact support%2$s to get your correct license information.', ["<a href='mailto:support@sendent.nl' style='color:blue'>", '</a>']);
					return new DataResponse(new LicenseStatus($status, 'error_incomplete', '-', $licensekey, '-', '-', $email, '-', 0, $group));
				} elseif (count($result) > 0 && $result[0]->getLevel() == License::ERROR_VALIDATING) {
					$email = $result[0]->getEmail();
					$licensekey = $result[0]->getLicensekey();
					$group = $result[0]->getNcgroup();
					$istrial = $result[0]->getIstrial();
					return new DataResponse(new LicenseStatus($this->l->t("Cannot verify your license. Please make sure your licensekey and email address are correct before you try to 'Activate license'."), 'error_validating', '-', $licensekey, '-', '-', $email, '-', $istrial, $group));
				} else {
					return new DataResponse(new LicenseStatus($this->l->t('No license configured'), 'nolicense', '-', '-', '-', '-', '-', '-', 0, '-'));
				}
			} else {
				return new DataResponse(new LicenseStatus($this->l->t('No license configured'), 'nolicense', '-', '-', '-', '-', '-', '-', 0, '-'));
			}
		} catch (Exception $e) {
			$this->logger->error('Cannot verify license');
			return new DataResponse(new LicenseStatus($this->l->t("Cannot verify your license. Please make sure your licensekey and email address are correct before you try to 'Activate license'."), 'fatal', '-', '-', '-', '-', '-', '-', 0, $ncgroup));
		}
	}
	/**
	 * Returns license status for group $ncgroup
	 *
	 * @param string $ncgroup
	 * @return DataResponse
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function showForNCGroupInternal(string $ncgroup = ''): DataResponse {

		if ($ncgroup === '') {
			$this->logger->info('Getting license information for default group');
		} else {
			$this->logger->info('Getting license information for group ' . $ncgroup);
		}

		try {
			// Gets license for group $ncgroup
			$result = $this->service->findByGroup($ncgroup);
			if (isset($result) && $result !== null && $result !== false && is_array($result) && count($result) === 0) {
				// No license for group $ncgroup, getting default license
				$result = $this->service->findByGroup('');
			}

			if (isset($result) && $result !== null && $result !== false) {
				if (is_array($result) && count($result) > 0
				&& $result[0]->getLevel() != 'Error_clear' && $result[0]->getLevel() != 'Error_incomplete') {


					$this->logger->info('Check needed for license ' . $result[0]->getId());
					try {
						$this->licensemanager->renewLicense($result[0]);
						$result = $this->service->findByGroup($result[0]->getNcgroup());
						if (isset($result) && $result !== null && $result !== false) {
							if (is_array($result) && count($result) > 0
							&& $result[0]->getLevel() != 'Error_clear' && $result[0]->getLevel() != 'Error_incomplete') {
							} else {
								//throw new Exception();
							}
						}
					} catch (Exception $e) {
						$this->logger->error('Error while renewing license ' . $result[0]->getId() . '\r\n' . $e);
					}

					// Reports license status
					$email = $result[0]->getEmail();
					$licensekey = $result[0]->getLicensekey();
					$this->logger->info('License key returned is: ' . $licensekey);
					$dateExpiration = $result[0]->getDatelicenseend();
					$dateLastCheck = $result[0]->getDatelastchecked();
					$level = $result[0]->getLevel();
					$group = $result[0]->getNcgroup();
					$product = $result[0]->getProduct();
					$istrial = $result[0]->getIstrial();
					$statusKind = '';
					$status = '';

					if ($result[0]->isCleared()) {
						$status = $this->l->t('No license configured');
						$statusKind = 'nolicense';
					} elseif ($result[0]->isIncomplete()) {
						$status = $this->l->t('Missing email address or license key.');
						$statusKind = 'error_incomplete';
					} elseif ($result[0]->isCheckNeeded()) {
						$status = $this->l->t('Revalidation of your license is required');
						$statusKind = 'check';
					} elseif (!$result[0]->isCheckNeeded() && !$result[0]->isLicenseExpired() && !$result[0]->isSupportedProduct()) {
						$status = $this->l->t('Current license is not intended to be used with the Sendent app. <br/>Please install/configure Sendent Synchroniser because this license is only intended to be used for configuring: ' . $product)
							. '</br>'
							. $this->l->t('%1$sContact support%2$s if you experience issues with configuring your license key.', ["<a href='mailto:info@sendent.com' style='color:blue'>", '</a>']);
						$statusKind = 'expired';
					} elseif ($result[0]->isLicenseRenewedOrSwitched()) {
						$status = $this->l->t('Current license has been changed.')
							. '</br>'
							. $this->l->t('%1$sContact support%2$s if you experience issues with configuring your license key.', ["<a href='mailto:info@sendent.com' style='color:blue'>", '</a>']);
						$statusKind = 'check';
					} elseif ($result[0]->isLicenseInactive()) {
						$status = $this->l->t('Current license has been deactivated by Sendent.')
							. '</br>'
							. $this->l->t('%1$sContact support%2$s for more information.', ["<a href='mailto:info@sendent.com' style='color:blue'>", '</a>']);
						$statusKind = 'check';
					} elseif ($result[0]->isLicenseSuspended()) {
						$status = $this->l->t('Current license has been suspended by Sendent.')
							. '</br>'
							. $this->l->t('%1$sContact support%2$s for more information.', ["<a href='mailto:info@sendent.com' style='color:blue'>", '</a>']);
						$statusKind = 'check';
					} elseif ($result[0]->isLicenseExpired()) {
						$status = $this->l->t('Current license has expired.')
							. '</br>'
							. $this->l->t('%1$sContact support%2$s if you experience issues with configuring your license key.', ["<a href='mailto:info@sendent.com' style='color:blue'>", '</a>']);
						$statusKind = 'expired';
					} elseif (!$result[0]->isCheckNeeded() && !$result[0]->isLicenseExpired() && $result[0]->isSupportedProduct()) {
						$status = $this->l->t('Current license is valid');
						$statusKind = 'valid';
					} elseif (!$result[0]->isCheckNeeded() && !$result[0]->isLicenseExpired()) {
						$status = $this->l->t('Current license is valid but there could be an issue for the supported product(s): ' . $product);
						$statusKind = 'valid';
					} elseif (!$this->licensemanager->isWithinUserCount($result[0]) && $this->licensemanager->isWithinGraceUserCount($result[0])) {
						$status = $this->l->t('Current amount of active users exceeds licensed amount. Some users might not be able to use Sendent.');
						$statusKind = 'userlimit';
					} elseif (!$this->licensemanager->isWithinUserCount($result[0]) && !$this->licensemanager->isWithinGraceUserCount($result[0])) {
						$status = $this->l->t('Current amount of active users exceeds licensed amount. Additional users trying to use Sendent will be prevented from doing so.');
						$statusKind = 'userlimit';
					}
					return new DataResponse(new LicenseStatus($status, $statusKind, $level, $licensekey, $dateExpiration, $dateLastCheck, $email, $product, $istrial, $group));
				} elseif (count($result) > 0 && $result[0]->getLevel() == 'Error_incomplete') {
					$email = $result[0]->getEmail();
					$licensekey = $result[0]->getLicensekey();
					$group = $result[0]->getNcgroup();
					$product = $result[0]->getProduct();
					$istrial = $result[0]->getIstrial();
					$status = $this->l->t('Missing (or incorrect) email address or license key. %1$sContact support%2$s to get your correct license information.', ["<a href='mailto:support@sendent.nl' style='color:blue'>", '</a>']);
					return new DataResponse(new LicenseStatus($status, 'error_incomplete', '-', $licensekey, '-', '-', $email, '-', 0, $group));
				} elseif (count($result) > 0 && $result[0]->getLevel() == License::ERROR_VALIDATING) {
					$email = $result[0]->getEmail();
					$licensekey = $result[0]->getLicensekey();
					$this->logger->info('License key returned is: ' . $licensekey);
					$dateExpiration = $result[0]->getDatelicenseend();
					$dateLastCheck = $result[0]->getDatelastchecked();
					$level = $result[0]->getLevel();
					$group = $result[0]->getNcgroup();
					$product = $result[0]->getProduct();
					$istrial = $result[0]->getIstrial();
					return new DataResponse(new LicenseStatus($this->l->t("Cannot verify your license. Please make sure your licensekey and email address are correct before you try to 'Activate license'."), 'error_validating', $level, $licensekey, $dateExpiration, $dateLastCheck, $email, $product, $istrial, $group));
				} else {
					return new DataResponse(new LicenseStatus($this->l->t('No license configured'), 'nolicense', '-', '-', '-', '-', '-', '-', 0, '-'));
				}
			} else {
				return new DataResponse(new LicenseStatus($this->l->t('No license configured'), 'nolicense', '-', '-', '-', '-', '-', '-', 0, '-'));
			}
		} catch (Exception $e) {
			$this->logger->error('Cannot verify license');
			$email = $result[0]->getEmail();
			$licensekey = $result[0]->getLicensekey();
			$this->logger->info('License key returned is: ' . $licensekey);
			$dateExpiration = $result[0]->getDatelicenseend();
			$dateLastCheck = $result[0]->getDatelastchecked();
			$level = $result[0]->getLevel();
			$group = $result[0]->getNcgroup();
			$product = $result[0]->getProduct();
			$istrial = $result[0]->getIstrial();
			return new DataResponse(new LicenseStatus($this->l->t("Cannot verify your license. Please make sure your licensekey and email address are correct before you try to 'Activate license'."), 'fatal', $level, $licensekey, $dateExpiration, $dateLastCheck, $email, $product, $istrial, $group));
		}
	}
	/**
	 * Admin-only: creates a new license
	 *
	 * @param string $license
	 * @param string $email
	 * @param string $ncgroup
	 */
	public function create(string $license, string $email, string $ncgroup) {
		return $this->licensemanager->createLicense($license, '', '', $email, $ncgroup);
	}

	/**
	 * Admin-only: deletes a license for a group
	 *
	 * @param string $group
	 */
	public function delete(string $group) {
		// Deletes requested settinglicense
		return $this->licensemanager->deleteLicense($group);
	}

	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function renew() {
		// Finds out user's license
		$license = $this->service->findUserLicense($this->userId);

		$this->licensemanager->renewLicense($license);
	}

	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function validate() {
		// Finds out user's license
		$license = $this->service->findUserLicense($this->userId);

		// Unlicensed?
		if (is_null($license)) {
			return false;
		}

		$this->licensemanager->renewLicense($license);
		return $this->licensemanager->isLocalValid($license);
	}

	/**
	 * Admin-only: generates a report of all licenses used
	 */
	public function report() {

		// Gets groups for which specific settings and/or license are defined and add it the default one
		$sendentGroups = $this->appConfig->getAppValue('sendentGroups', '');
		$sendentGroups = json_decode($sendentGroups);
		array_push($sendentGroups, '');

		// Gets license of each groups, handling inheritance
		$licenses = [];
		foreach ($sendentGroups as $gid) {
			$license = $this->service->findByGroup($gid);
			if (!empty($license)) {
				$license = $license[0]->jsonSerialize();
				$license += ['inherited' => false];
				if ($gid === '') {
					$license['ncgroup'] = 'Default license';
				}
			} else {
				$license = new License;
				$license = $license->jsonSerialize();
				$license += ['inherited' => true];
				$license['ncgroup'] = $gid; // Maybe we should use displayName here
			}
			array_push($licenses, $license);
		}
		return json_encode($licenses);
	}
}
