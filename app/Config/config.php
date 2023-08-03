<?php

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../../');
$dotenv->load();

// Define some important constants
define('SITE_URL', $_ENV['SITE_URL']);

define('DB_USERNAME', $_ENV['MYSQL_USER']);
define('DB_PASSWORD', $_ENV['MYSQL_ROOT_PASSWORD']);
define('DB_NAME', $_ENV['MYSQL_DATABASE']);
define('DB_HOST', $_ENV['MYSQL_HOST_ALIAS']);