<?php

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
