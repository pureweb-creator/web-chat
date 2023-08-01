<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;
use App\Core\Middleware;

class RegisterController extends Controller
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

        if (empty($name)) {
            $response = [
                'success' => false,
                'message' => 'No name.'
            ];

            echo json_encode($response);
            die;
        }

        if (empty($email)) {
            $response = [
                'success' => false,
                'message' => 'No email.'
            ];

            echo json_encode($response);
            die;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response = [
                'success' => false,
                'message' => 'Wrong email.'
            ];

            echo json_encode($response);
            die;
        }

        if ($this->userModel->loadUser('email', $email)) {
            $response = [
                'success' => false,
                'message' => 'User with that email already exists.'
            ];

            echo json_encode($response);
            die;
        }

        if (!$this->userModel->addUser($email, $name)){
            $response = [
                'success' => false,
                'message' => 'Unexpected error.'
            ];

            echo json_encode($response);
            die;
        }

        // send confirmation
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