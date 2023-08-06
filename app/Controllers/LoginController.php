<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helper;
use App\Models\UserModel;
use App\Core\Middleware;

class LoginController extends Controller
{
    protected UserModel $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel($this->logger);
    }

    public function index(): void
    {
        Middleware::Authentication('guest');

        $this->data = [
            'title' => 'Login'
        ];

        echo $this->view->render('login.twig', $this->data);
    }

    public function process(): void
    {
        if (!$_POST) die;

        Middleware::Authentication('guest');
        Middleware::Csrf();

        $email = htmlspecialchars(trim($_POST['email']));

        if (empty($email))
            Helper::response('No email.', false);

        if (!$this->userModel->loadUser('email', $email))
            Helper::response('User with that email doesn\'t exists.', false);

        Helper::response();
    }
}

