<?php

namespace App\Core;

use Monolog\Logger;

/**
 * Model
 */
abstract class Model
{
	protected \PDO $pdo;
    protected Logger $logger;
    public function __construct(Logger $logger)
	{
        $this->logger = $logger;

        try {
	        $this->pdo = new \PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME,DB_USERNAME,DB_PASSWORD);
        } catch (\Exception $e){
            $this->logger->critical('Could not connect to the database');
        }
	}
}