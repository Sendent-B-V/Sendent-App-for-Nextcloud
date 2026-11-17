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
 * Replaces stored HTML snippets that still equal a previous default with the
 * current default. Previous defaults are kept byte for byte in
 * assets/templates/legacy/<template>/<appversion>-<commit>.html; to cover a
 * future template change, add the old default there. Customised snippets are
 * left unchanged.
 *
 * This is its own repair step, not part of InitialLoadManager, because on an
 * update from the Apps page or `occ app:update` the old InitialLoadManager
 * class is already loaded in the updating process. A new class is loaded
 * from the new files. Only InitialLoadManager methods that also exist in
 * older versions are used here.
 */
class MigrateLegacySnippets implements IRepairStep {
	/** setting key => template name */
	private const SNIPPETS = [
		9 => 'sharefolderhtml',
		10 => 'sharefilehtml',
		12 => 'securemailhtml',
		30 => 'htmlsnippetpassword',
		203 => 'talkhtml',
		210 => 'talkpwhtml',
		302 => 'htmlsnippetguestaccounts',
		303 => 'htmlsnippetpublicaccounts',
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
		$templates = $this->appManager->getAppPath(Application::APPID) . '/assets/templates/';
		foreach (self::SNIPPETS as $key => $template) {
			try {
				$stored = $this->initialLoadManager->showBySettingKeyId($key);
				if ($stored === null) {
					continue;
				}
				$legacyFiles = glob($templates . 'legacy/' . $template . '/*.html') ?: [];
				if ($key === 12) {
					// Secure Mail used to be seeded with the file share default.
					$legacyFiles = array_merge($legacyFiles, glob($templates . 'legacy/sharefilehtml/*.html') ?: [], [$templates . 'sharefilehtml.html']);
				}
				if (!$this->matchesAny($stored->getValue(), $legacyFiles)) {
					continue;
				}
				$this->initialLoadManager->update($stored->getId(), $key, $stored->getGroupid(), $this->read($templates . $template . '.html'));
				$output->info('Updated ' . $template . ' to the new default.');
			} catch (Throwable $e) {
				// Never break an update over a snippet; the old snippet keeps working.
				$output->warning('Could not update ' . $template . ': ' . $e->getMessage());
			}
		}
	}

	/**
	 * @param string[] $files
	 */
	private function matchesAny(string $value, array $files): bool {
		$normalise = fn (string $html): string => trim(str_replace("\r\n", "\n", $html));
		foreach ($files as $file) {
			if ($normalise($value) === $normalise($this->read($file))) {
				return true;
			}
		}
		return false;
	}

	private function read(string $file): string {
		$content = file_get_contents($file);
		if ($content === false) {
			throw new \RuntimeException('Template ' . $file . ' not found');
		}
		return $content;
	}
}
