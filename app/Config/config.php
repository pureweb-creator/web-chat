<?php

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../../');
$dotenv->load();

// Define some important constants
define('SITE_URL', $_ENV['SITE_URL']);

define('DB_USERNAME', $_ENV['DB_USERNAME']);
define('DB_PASSWORD', $_ENV['DB_PASSWORD']);
define('DB_NAME', $_ENV['DB_NAME']);
define('DB_HOST', $_ENV['DB_HOST']);