<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helper;
use App\Core\View;
use App\Models\UserModel;
use App\Core\Middleware;
use Monolog\Logger;

class LoginController extends Controller
{
    protected UserModel $userModel;
    protected View $view;

    public function __construct(View $view, Logger $logger)
    {
        parent::__construct();

        $this->view = $view;
        $this->userModel = new UserModel();
    }

    public function index(): void
    {
        Middleware::Authentication();

        echo $this->view->render('login.twig');
    }

    public function process(): void
    {
        if (!$_POST) die;

        Middleware::Authentication();
        Middleware::Csrf();

        $email = htmlspecialchars(trim($_POST['email']));

        if (empty($email))
            Helper::response('No email.', false);

        if (!$this->userModel->loadUser('email', $email))
            Helper::response('User with that email does not exist.', false);

        Helper::response();
    }
}

