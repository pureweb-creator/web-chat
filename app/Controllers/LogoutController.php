<?php

namespace App\Controllers;

use \App\Core\Controller;
use App\Core\View;
use Monolog\Logger;

class LogoutController extends Controller
{
    protected View $view;
    public function __construct(View $view, Logger $logger)
    {
        parent::__construct();
        $this->view = $view;
    }

    public function index()
    {
        unset($_SESSION['logged_user']);
        header('Location: ./login');
    }
}