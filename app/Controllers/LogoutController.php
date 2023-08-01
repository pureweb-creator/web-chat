<?php

namespace App\Controllers;

use \App\Core\Controller;

class LogoutController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        unset($_SESSION['logged_user']);
        header('Location: ./login');
    }
}