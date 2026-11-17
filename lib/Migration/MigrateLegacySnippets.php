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

use OCA\Sendent\AppInfo\Application;
use OCA\Sendent\Service\InitialLoadManager;
use OCP\App\IAppManager;
use OCP\Migration\IOutput;
use OCP\Migration\IRepairStep;
use Throwable;

/**
 * Replaces stored HTML snippets that still equal a previously released
 * default (assets/templates/legacy/<template>-<version>.html) with the
 * current default. Customised snippets are left unchanged.
 *
 * This is its own repair step, not part of InitialLoadManager, because on an
 * update from the Apps page or `occ app:update` the old InitialLoadManager
 * class is already loaded in the updating process. A new class is loaded
 * from the new files. Only InitialLoadManager methods that also exist in
 * older versions are used here.
 */
class MigrateLegacySnippets implements IRepairStep {
	/** setting key => [template name, released versions that had no {URL}] */
	private const LEGACY_SNIPPETS = [
		302 => ['htmlsnippetguestaccounts', ['1.3', '2.0', '4.0', '4.4']],
		303 => ['htmlsnippetpublicaccounts', ['2.0', '4.0', '4.4']],
	];

	private InitialLoadManager $initialLoadManager;
	private IAppManager $appManager;

	public function __construct(InitialLoadManager $initialLoadManager, IAppManager $appManager) {
		$this->initialLoadManager = $initialLoadManager;
		$this->appManager = $appManager;
	}

	public function getName(): string {
		return 'Update Sendent HTML snippets that still use a previous default';
	}

	public function run(IOutput $output): void {
		foreach (self::LEGACY_SNIPPETS as $key => [$template, $versions]) {
			try {
				$stored = $this->initialLoadManager->showBySettingKeyId($key);
				if ($stored === null || !$this->isLegacyDefault($stored->getValue(), $template, $versions)) {
					continue;
				}
				$this->initialLoadManager->update($stored->getId(), $key, $stored->getGroupid(), $this->readTemplate($template));
				$output->info('Updated ' . $template . ' to the new default.');
			} catch (Throwable $e) {
				// Never break an update over a snippet; the old snippet keeps working.
				$output->warning('Could not update ' . $template . ': ' . $e->getMessage());
			}
		}
	}

	/**
	 * @param string[] $versions
	 */
	private function isLegacyDefault(string $value, string $template, array $versions): bool {
		$normalise = fn (string $html): string => trim(str_replace("\r\n", "\n", $html));
		foreach ($versions as $version) {
			if ($normalise($value) === $normalise($this->readTemplate('legacy/' . $template . '-' . $version))) {
				return true;
			}
		}
		return false;
	}

	private function readTemplate(string $name): string {
		$content = file_get_contents($this->appManager->getAppPath(Application::APPID) . '/assets/templates/' . $name . '.html');
		if ($content === false) {
			throw new \RuntimeException('Template ' . $name . ' not found');
		}
		return $content;
	}
}
