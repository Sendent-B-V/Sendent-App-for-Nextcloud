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
 * current default. Customised snippets are left unchanged.
 *
 * The stored snippet is a copy of the default made at install time, with no
 * record of whether an admin edited it. So we compare it against the SHA-1 of
 * every default that shipped (CRLF normalised to LF, then trimmed). When you
 * change a template, add the hash of the old one here:
 *   php -r 'echo sha1(trim(str_replace("\r\n", "\n", file_get_contents($argv[1])))), "\n";' assets/templates/<template>.html
 *
 * This is its own repair step, not part of InitialLoadManager, because on an
 * update from the Apps page or `occ app:update` the old InitialLoadManager
 * class is already loaded in the updating process. A new class is loaded
 * from the new files. Only InitialLoadManager methods that also exist in
 * older versions are used here.
 */
class MigrateLegacySnippets implements IRepairStep {
	/** setting key => [template name, hashes of previous defaults (app version-commit)] */
	private const SNIPPETS = [
		9 => ['sharefolderhtml', [
			'c5253642a0f732c2f30020cbc348980a1c965c6b', // 1.0.18-b3fde6d
			'01c8c9344af15094e9bfe0187ddd384b2261241d', // 1.0.19-6b6fef5
			'8c8adb91d75df3044670c2d9ec59cddfbefa290c', // 1.0.22-3ccddd2
			'ab6f8a033eaeb255030261bbf731f6e0b083fed7', // 1.2.9-65dcdf1
		]],
		10 => ['sharefilehtml', [
			'13aefd1e5bff2433b03b374f0a48c25b640ae752', // 1.0.18-b3fde6d
			'c44027764c57a29c2f6e171f532d7ca2ebf36e2f', // 1.0.18-b88beba
			'b259eeab692d3e3bd2052aca4f0999a5bf697033', // 1.0.19-6b6fef5
			'e405be671153a4a0c5922ef812fe6c570b4270df', // 1.0.22-3ccddd2
			'2a62de0011b511737e6605646510f78aa4c04271', // 1.2.7-e3e6656
			'53a9ede4ea9a83a2ec4f3d182d91ffd051851550', // 1.2.9-65dcdf1
		]],
		12 => ['securemailhtml', [
			'cea560e694dca1136e4be91e2e77ea91f427305b', // 1.0.18-b3fde6d
			'1037e5aeff34feb5c8712a7f4ed1a98d99dcdd32', // 1.0.19-6b6fef5
			'204af76e54f4c4cff01a79ee9d27e534bf75db90', // 1.0.22-3ccddd2
			'f91cde2e435ab7950332cbd881517af6e4aec009', // 1.2.9-65dcdf1
			// Secure Mail used to be seeded with the file share default.
			'13aefd1e5bff2433b03b374f0a48c25b640ae752', // sharefilehtml 1.0.18-b3fde6d
			'c44027764c57a29c2f6e171f532d7ca2ebf36e2f', // sharefilehtml 1.0.18-b88beba
			'b259eeab692d3e3bd2052aca4f0999a5bf697033', // sharefilehtml 1.0.19-6b6fef5
			'e405be671153a4a0c5922ef812fe6c570b4270df', // sharefilehtml 1.0.22-3ccddd2
			'2a62de0011b511737e6605646510f78aa4c04271', // sharefilehtml 1.2.7-e3e6656
			'53a9ede4ea9a83a2ec4f3d182d91ffd051851550', // sharefilehtml 1.2.9-65dcdf1
			'6e4ef68edcce5fa2ab0ed38467a5ad2e608fade3', // sharefilehtml 4.6.4
		]],
		30 => ['htmlsnippetpassword', [
			'f275638a5cd3fe135f96e7007d2b8dac24403642', // 1.0.18-b3fde6d
			'd3b259b56ff8d5535da23279bc078d83e3a4d19a', // 1.0.18-b88beba
			'f747bdda00e506d49d7b4209cbd9d2d325ca6455', // 1.0.19-508afc6
			'a04dd7b03fa43a558c915b983b7de8a630203058', // 1.0.19-6b6fef5
			'487e5cc6930fed211b2355632400b4d6fa867dec', // 1.0.22-3ccddd2
			'70e3dada2e208bb2129d9a0e3274cea33cd5b736', // 1.2.7-e3e6656
			'5e924ea93d50f10a61be055b92027cbc4f67ae98', // 1.2.9-65dcdf1
		]],
		203 => ['talkhtml', [
			'4eb1427a5f14605200fafb673e11b20e5d021169', // 3.0.4-7ce785c
			'9e69ecb254ccb317e041b7de8b9e599c5140b974', // 4.2.0-079cb77
		]],
		210 => ['talkpwhtml', [
			'b96405fd70ed73b66c6d1e090c5841192004d704', // 3.1.0-374b3d7
			'f8bececa59e19da66c3b012e1d693f1b1717cf72', // 4.2.0-079cb77
		]],
		302 => ['htmlsnippetguestaccounts', [
			'6230feb24a191ce514f775aad6aec7546e92bc3c', // 1.3.2-8e27858
			'10e137e4a35d8248ae3270c6a63a01c0c618c960', // 1.3.2-cb445f5
			'669e6b18b65dde7d69de1f990dfee1f8d51b6a12', // 2.0.0-9148fd4
			'a200fbdf0b2392ace5fe44a9e68a196005ccb316', // 4.0.0-0ff3671
			'daf57ace31451d7de6a421440b9043d05fe5830f', // 4.2.0-079cb77
		]],
		303 => ['htmlsnippetpublicaccounts', [
			'e075fd77f1e3a7a00581cf1bf9d84fd5f45ff434', // 2.0.0-9148fd4
			'99c258d52c034a3b23b3c2df53323f73091ecb62', // 4.0.0-0ff3671
			'acfaafd64d4cd202c067f9bb67264b219c704cd5', // 4.2.0-079cb77
		]],
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
		foreach (self::SNIPPETS as $key => [$template, $legacyHashes]) {
			try {
				$stored = $this->initialLoadManager->showBySettingKeyId($key);
				if ($stored === null) {
					continue;
				}
				$hash = sha1(trim(str_replace("\r\n", "\n", $stored->getValue())));
				if (!in_array($hash, $legacyHashes, true)) {
					continue;
				}
				$default = file_get_contents($templates . $template . '.html');
				if ($default === false) {
					throw new \RuntimeException('Template ' . $template . '.html not found');
				}
				$this->initialLoadManager->update($stored->getId(), $key, $stored->getGroupid(), $default);
				$output->info('Updated ' . $template . ' to the new default.');
			} catch (Throwable $e) {
				// Never break an update over a snippet; the old snippet keeps working.
				$output->warning('Could not update ' . $template . ': ' . $e->getMessage());
			}
		}
	}
}
