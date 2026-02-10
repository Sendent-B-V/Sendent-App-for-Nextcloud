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

if (!defined('PHPUNIT_RUN')) {
	define('PHPUNIT_RUN', 1);
}

if (!($ncRoot = getenv('NEXTCLOUD_ROOT'))) {
	$ncRoot = __DIR__ . '/../../..';
}

require_once $ncRoot . '/lib/base.php';

// Fix for "Autoload path not allowed: .../tests/lib/testcase.php"
if (property_exists(OC::class, 'loader') && \OC::$loader !== null) {
	\OC::$loader->addValidRoot(OC::$SERVERROOT . '/tests');
}

// Fix for "Autoload path not allowed: .../Sendent/tests/testcase.php"
\OC_App::loadApp('sendent');
