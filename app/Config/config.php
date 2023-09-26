<?php

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../../');
$dotenv->load();

// Define some important constants
define('SITE_URL',    $_ENV['SITE_URL']);

define('DB_USERNAME', $_ENV['MYSQL_USER']);
define('DB_PASSWORD', $_ENV['MYSQL_PASSWORD']);
define('DB_NAME',     $_ENV['MYSQL_DATABASE']);
define('DB_HOST',     $_ENV['MYSQL_HOST_ALIAS']);
define('DEBUG_MODE',  $_ENV['DEBUG_MODE']);

define('GOOGLE_SMTP_HOST',     $_ENV['GOOGLE_SMTP_HOST']);
define('GOOGLE_SMTP_SSL_PORT', $_ENV['GOOGLE_SMTP_SSL_PORT']);
define('GOOGLE_SMTP_TLS_PORT', $_ENV['GOOGLE_SMTP_TLS_PORT']);
define('GOOGLE_SMTP_USERNAME', $_ENV['GOOGLE_SMTP_USERNAME']);
define('GOOGLE_SMTP_PASSWORD', $_ENV['GOOGLE_SMTP_PASSWORD']);

define('MAX_LOGIN_ATTEMPTS', $_ENV['MAX_LOGIN_ATTEMPTS']);