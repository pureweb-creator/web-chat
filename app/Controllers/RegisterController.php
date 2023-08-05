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

        echo json_encode([
            'success' => true
        ]);
    }
}