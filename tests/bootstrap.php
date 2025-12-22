<?php
/**
 * Bootstrap file for PHPUnit tests
 * Sets up the environment and includes necessary files
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Get the root directory
$rootDir = dirname(__DIR__);

// Load environment variables if .env exists
$envFile = $rootDir . '/.env.test';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

// Set default environment variables for testing
if (!getenv('DB_HOST')) putenv('DB_HOST=localhost');
if (!getenv('DB_USER')) putenv('DB_USER=root');
if (!getenv('DB_PASS')) putenv('DB_PASS=');
if (!getenv('DB_NAME')) putenv('DB_NAME=padelup_test');

// Include autoloader if using Composer
if (file_exists($rootDir . '/vendor/autoload.php')) {
    require_once $rootDir . '/vendor/autoload.php';
}

// Set up test database connection (optional, can be used by tests)
$GLOBALS['test_db_config'] = [
    'host' => getenv('DB_HOST'),
    'user' => getenv('DB_USER'),
    'pass' => getenv('DB_PASS'),
    'name' => getenv('DB_NAME')
];
