<?php

require_once __DIR__."/Config/config.php";

session_start();

// Debug mode
error_reporting(E_ALL);
ini_set('display_errors', DEBUG_MODE);
ini_set('log_errors', 'on');
ini_set('error_log', __DIR__.'/../debug.log');

if (!isset($_SESSION['_token']))
    $_SESSION['_token'] = bin2hex(random_bytes(16));

// Run Application
App\Core\Router::run();