<?php

namespace App\Controllers;

use App\Core\View;
use Monolog\Logger;

class NotFoundController extends \App\Core\Controller
{
    protected View $view;
    public function __construct(View $view, Logger $logger)
    {
        parent::__construct();
        $this->view = $view;
    }

    public function index()
    {
        echo $this->view->render('404.twig');
    }
}