<?php

namespace App\Core;

use Monolog\Logger;

/**
 * Model
 */
abstract class Model
{
	protected \PDO $pdo;
    public function __construct()
	{
        $this->pdo = new \PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME,DB_USERNAME,DB_PASSWORD);
	}
}