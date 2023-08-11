<?php

namespace App\Core;

/**
 * Controller
 */
abstract class Controller
{
	protected Model $model;
	protected array $data;

	public function __construct()
    {
        unset($_SESSION["response"]);
    }
}