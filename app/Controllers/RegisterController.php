<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helper;
use App\Core\View;
use App\Models\UserModel;
use App\Core\Middleware;
use Monolog\Logger;

class RegisterController extends Controller
{
    protected UserModel $userModel;
    protected View $view;

    public function __construct(View $view, Logger $logger)
    {
        parent::__construct();
        $this->view = $view;
        $this->userModel = new UserModel($logger);
    }

    public function index(): void
    {
        Middleware::Authentication('guest');

        $this->data = [
            'title' => 'Register'
        ];

        echo $this->view->render('register.twig', $this->data);
    }

    public function process(){
        if (!$_POST) die;

        Middleware::Authentication('guest');
        Middleware::Csrf();

        $name = htmlspecialchars(trim($_POST['name']));
        $email = htmlspecialchars(trim($_POST['email']));

        if (empty($name))
            Helper::response('No name', false);

        if (empty($email))
            Helper::response('No email.', false);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            Helper::response('Wrong email.', false);

        if ($this->userModel->loadUser('email', $email))
            Helper::response('User with that email already exists.', false);

        if (!$this->userModel->addUser($email, $name))
            Helper::response('Unexpected error.', false);

        Helper::response();
    }
}