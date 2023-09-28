<?php

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

ignore_user_abort(true);

require_once __DIR__."/vendor/autoload.php";
require_once __DIR__."/app/Config/config.php";

// Debug mode
error_reporting(E_ALL);
ini_set('display_errors', DEBUG_MODE);
ini_set('log_errors', 'on');
ini_set('error_log', __DIR__.'/../debug.log');

$logger = new Logger('ws-web-chat');
$logger->pushHandler(new StreamHandler('debug.log', Level::Warning));

$wsInstance = new App\Controllers\WebsocketController($logger);
$wsInstance->listen();