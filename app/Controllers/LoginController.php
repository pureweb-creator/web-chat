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

        echo json_encode([
            'success' => true
        ]);
    }
}

