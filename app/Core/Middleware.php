<?php

namespace App\Core;

abstract class Middleware
{
    public static function Authentication($guard='guest'): void
    {
        switch ($guard){
            case 'guest':
                if (isset($_SESSION['logged_user']))
                    header('Location: ./');
                break;

            case 'user':
                if (!isset($_SESSION['logged_user']))
                    header('Location: ./');
                break;
        }
    }

    public static function Csrf(){
        if (!isset($_POST['_token']) || $_POST['_token']!==$_SESSION['_token']){
            $response = [
                'success' => false,
                'message' => 'Invalid CSRF token.'
            ];

            echo json_encode($response);
            die;
        }
    }
}