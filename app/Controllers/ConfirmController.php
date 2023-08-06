<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helper;
use App\Models\UserModel;
use App\Core\Middleware;

class ConfirmController extends Controller
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

        if (isset($_GET['email']))
            $this->sendConfirmation($_GET['email']);

        $this->data = [
            'title' => 'Confirmation',
            'email' => $_GET['email'] ?? false
        ];

        echo $this->view->render('confirm.twig', $this->data);
    }

    public function process(){

        if (!$_POST) die;

        Middleware::Authentication('guest');
        Middleware::Csrf();

        $email = htmlspecialchars(trim($_POST['email']));
        $code = htmlspecialchars(trim($_POST['code']));

        if (empty($code))
            Helper::response('No confirmation code', false);

        $user = $this->userModel->loadUser('email',$email)[0];
        if ($user['confirmation_code'] !== $code)
            Helper::response('Wrong confirmation code', false);

        $this->userModel->updateConfirmationStatus($email, 1);
        $_SESSION['logged_user'] = $user;
        $this->userModel->updateConfirmationCode($email, '');

        Helper::response();
    }

    public function sendConfirmation($email): void
    {
        $confirmation_code = rand(10000,99999);

        if (!$this->userModel->updateConfirmationCode($email, $confirmation_code))
            Helper::response('Unexpected error.', false, true);

        $headers = "From: no-reply@chat.com;\nContent-type: text/html charset=utf-8\nReply-to: no-reply@chat.com";
        if (!@mail($email, "=?UTF-8?B?".base64_encode("Подтверждение")."?=","Your confimation code is: <b>$confirmation_code</b>", $headers))
            Helper::response('Email does not sent. Please, try again later.', false, true);
    }
}