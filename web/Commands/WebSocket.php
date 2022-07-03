<?php

require_once __DIR__."/../../vendor/autoload.php";

use Web\Controllers\Controller;

$socket = new Controller();
$socket->websocket();