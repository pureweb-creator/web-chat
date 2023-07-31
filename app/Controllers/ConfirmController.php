<?php

namespace App\Controllers;

use App\Core\Controller;
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

        $this->data = [
            'title' => 'Confirmation'
        ];

        echo $this->view->render('confirm.twig', $this->data);
    }

    public function process(){

        if (!$_POST) die;

        Middleware::Authentication('guest');
        Middleware::Csrf();

        // check user role
        if (isset($_SESSION['logged_user']))
            header('Location: ./');

        $email = htmlspecialchars(trim($_POST['email']));
        $code = htmlspecialchars(trim($_POST['code']));

        if (empty($code)){
            $response = [
                'success'=>false,
                'message'=>'No confirmation code'
            ];

            echo json_encode($response);
            die;
        }

        $user = $this->userModel->loadUser($email)[0];
        if ($user['confirmation_code'] !== $code){
            $response = [
                'success'=>false,
                'message'=>'Wrong confirmation code'
            ];

            echo json_encode($response);
            die;
        }

        $_SESSION['logged_user'] = $user;

        $response = [
            'success'=>true
        ];

        echo json_encode($response);
    }
}