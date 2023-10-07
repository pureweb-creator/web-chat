<?php

namespace App\Controllers;

use \App\Core\Controller;
use App\Core\View;
use Monolog\Logger;

class LogoutController extends Controller
{
    public function __construct(protected View $view, protected Logger $logger)
    {
        parent::__construct();
    }

    public function index()
    {
        unset($_SESSION['logged_user']);
        header('Location: ./login');
    }
}