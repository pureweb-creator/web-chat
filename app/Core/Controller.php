<?php

namespace App\Core;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Level;

/**
 * Controller
 */
abstract class Controller
{
    protected ?Logger $logger;
	protected ?View $view;
	protected $model;
	protected array $data;

	public function __construct()
    {
        // Set logging
        $this->logger = new Logger('main');
        $this->logger->pushHandler(new StreamHandler(__DIR__.'/../../debug.log', Level::Warning));

        $this->view = new View($this->logger);
    }
}