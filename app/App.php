<?php

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

require_once __DIR__."/Config/config.php";

session_start();

// Debug mode
error_reporting(E_ALL);
ini_set('display_errors', DEBUG_MODE);
ini_set('log_errors', 'on');
ini_set('error_log', __DIR__.'/../debug.log');

$logger = new Logger('main');
$logger->pushHandler(new StreamHandler(__DIR__.'/../debug.log', Level::Warning));

// Updates every session
if (!isset($_SESSION['_token']))
    $_SESSION['_token'] = bin2hex(random_bytes(16));

$view = new App\Core\View($logger);

// Run Application
App\Core\Router::run($view, $logger);