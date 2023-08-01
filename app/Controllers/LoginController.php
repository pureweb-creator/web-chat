<?php

namespace App\Controllers;

use App\Core\Controller;
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
        $response = [];

        if (empty($email)) {
            $response = [
                'success' => false,
                'message' => 'No email.'
            ];

            echo json_encode($response);
            die;
        }

        if (!$this->userModel->loadUser('email', $email)) {
            $response = [
                'success' => false,
                'message' => 'User with that email doesn\'t exists.'
            ];

            echo json_encode($response);
            die;
        }

        $this->sendConfirmation($email);

        echo json_encode([
            'success' => true
        ]);
    }

    public function sendConfirmation($email): void
    {
        $confirmation_code = rand(10000,99999);

        if (!$this->userModel->updateConfirmationCode($email, $confirmation_code)){
            $response = [
                'success' => false,
                'message' => 'Unexpected error.'
            ];

            echo json_encode($response);
            die;
        }

        $headers = "From: no-reply@chat.com;\nContent-type: text/html charset=utf-8\nReply-to: no-reply@chat.com";
        if (!@mail($email, "=?UTF-8?B?".base64_encode("Подтверждение")."?=","Your confimation code is: <b>$confirmation_code</b>", $headers)){
            $response = [
                'success' => false,
                'message' => 'Email does not sent.'
            ];

            echo json_encode($response);
            die;
        }
    }
}

