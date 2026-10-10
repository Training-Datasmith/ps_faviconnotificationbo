<?php

if (!defined('_PS_VERSION_')) {
    define('_PS_VERSION_', '8.1.7-test');
}

require_once __DIR__ . '/stubs/PrestaShopDoubles.php';

$moduleRoot = dirname(dirname(__DIR__));

require_once $moduleRoot . '/ps_faviconnotificationbo.php';
require_once $moduleRoot . '/controllers/admin/AdminConfigureFaviconBoController.php';
require_once $moduleRoot . '/upgrade/upgrade-2.1.0.php';
