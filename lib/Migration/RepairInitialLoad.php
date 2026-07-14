<?php

declare(strict_types=1);

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

namespace OCA\Sendent\Migration;

use OCA\Sendent\Service\InitialLoadManager;
use OCP\Migration\IOutput;
use OCP\Migration\IRepairStep;
use Throwable;

/**
 * Reseeds the setting templates and setting keys when they are missing,
 * ignoring the firstRunAppVersion gate. Runs on install and after every
 * app update (post-migration), and via `occ maintenance:repair`, so
 * installs where the initial load once failed silently are healed
 * automatically.
 */
class RepairInitialLoad implements IRepairStep {
	private InitialLoadManager $initialLoadManager;

	public function __construct(InitialLoadManager $initialLoadManager) {
		$this->initialLoadManager = $initialLoadManager;
	}

	public function getName(): string {
		return 'Ensure Sendent setting templates and setting keys are seeded';
	}

	public function run(IOutput $output): void {
		// When updating from a version without ensureSeeded(), the old
		// InitialLoadManager class is already loaded in this PHP process and
		// cannot be redefined. Skip; the version-bound check in
		// checkUpdateNeeded() reseeds on the next request with the new code.
		if (!method_exists($this->initialLoadManager, 'ensureSeeded')) {
			$output->info('Sendent seeding skipped during self-update, it will run on the next request.');

			return;
		}

		try {
			if ($this->initialLoadManager->ensureSeeded()) {
				$output->info('Sendent setting templates and setting keys are present.');
			} else {
				$output->warning('Sendent setting templates or setting keys could not be seeded completely, see nextcloud.log for details.');
			}
		} catch (Throwable $e) {
			// Never break an install/update over seeding; it is retried on boot.
			$output->warning('Sendent seeding failed: ' . $e->getMessage() . ' (see nextcloud.log). It will be retried on the next request.');
		}
	}
}
