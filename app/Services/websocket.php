<?php
ignore_user_abort(true);

require_once __DIR__."/../../vendor/autoload.php";
require_once __DIR__."/../Config/config.php";

$wsInstance = new App\Controllers\WebsocketController();
$wsInstance->listen();