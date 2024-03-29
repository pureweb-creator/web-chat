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

    public function __construct(protected View $view,  protected Logger $logger)
    {
        parent::__construct();
        $this->userModel = new UserModel();
    }

    public function index(): void
    {
        Middleware::Authentication();
        echo $this->view->render('register.twig');
    }

    public function process(){
        if (!$_POST) die;

        Middleware::Authentication();
        Middleware::Csrf();

        $name = htmlspecialchars(trim($_POST['name']));
        $email = htmlspecialchars(trim($_POST['email']));

        $hexColors1 = ['#B132FF', '#4E95FF', '#2FFFF3', '#FF3489', '#FF8F51', '#3DFF50'];
        $hexColors2 = [];

        foreach ($hexColors1 as $color)
            $hexColors2[] = Helper::darken_color($color, 2);

        $randColor1 = array_rand($hexColors1, 1);
        $randColor2 = array_rand($hexColors2, 1);

        if (empty($name))
            Helper::response('No name', false);

        if (empty($email))
            Helper::response('No email.', false);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            Helper::response('Wrong email.', false);

        if ($this->userModel->loadUser('email', $email))
            Helper::response('User with that email already exists.', false);

        if (!$this->userModel->addUser($email, $name, $hexColors1[$randColor1], $hexColors2[$randColor2]))
            Helper::response('Unexpected error.', false);

        Helper::response();
    }
}