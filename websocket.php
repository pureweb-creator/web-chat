<?php

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

ignore_user_abort(true);

require_once __DIR__."/vendor/autoload.php";
require_once __DIR__."/app/Config/config.php";

$logger = new Logger('main');
$logger->pushHandler(new StreamHandler(__DIR__.'/../debug.log', Level::Warning));

$wsInstance = new App\Controllers\WebsocketController($logger);
$wsInstance->listen();