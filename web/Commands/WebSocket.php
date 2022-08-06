<?php

require_once __DIR__."/../../vendor/autoload.php";
require_once __DIR__."/../Kernel/config.php";
require_once __DIR__."/../Kernel/validator.php";

use Web\Controllers\Controller;

$socket = new Controller(CONFIG);
$socket->websocket();