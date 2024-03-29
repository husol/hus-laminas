<?php

declare(strict_types=1);

use Laminas\Config\Factory;
use Laminas\Mvc\Application;
use Laminas\Stdlib\ArrayUtils;

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

// Start Custom Definition
date_default_timezone_set('Asia/Ho_Chi_Minh');

define('APPLICATION_PATH', dirname(__DIR__));
define('DS', DIRECTORY_SEPARATOR);
defined('APP_DIR') || define('APP_DIR', dirname(__FILE__));
defined('ROOT_DIR') || define('ROOT_DIR', realpath(APP_DIR . '/..'));
// End Custom Definition

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server') {
    $path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (is_string($path) && __FILE__ !== $path && is_file($path)) {
        return false;
    }
    unset($path);
}

// Composer autoloading
include __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/helpers.php';

if (! class_exists(Application::class)) {
    throw new RuntimeException(
        "Unable to load application.\n"
        . "- Type `composer install` if you are developing locally.\n"
        . "- Type `vagrant ssh -c 'composer install'` if you are using Vagrant.\n"
        . "- Type `docker-compose run lamians composer install` if you are using Docker.\n"
    );
}

// Setup for Sentry
$config = Factory::fromFile(ROOT_DIR . '/module/Application/config/config.php');
if (!empty($config['SENTRY_DSN']) && in_array($config['STAGE'], ['DEV', 'STG', 'PROD'])) {
  $cfg = Factory::fromFile(ROOT_DIR . '/module/Application/config/common.php');
  \Sentry\init([
    'dsn' => $config['SENTRY_DSN'],
    'environment' => $config['STAGE'],
    'release' => $cfg['APP_VERSION'],
    'attach_stacktrace' => true,
  ]);
}

// Retrieve configuration
$appConfig = require __DIR__ . '/../config/application.config.php';
if (file_exists(__DIR__ . '/../config/development.config.php')) {
    $appConfig = ArrayUtils::merge($appConfig, require __DIR__ . '/../config/development.config.php');
}

// Run the application!
Application::init($appConfig)->run();
