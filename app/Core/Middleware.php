<?php

namespace App\Core;

abstract class Middleware
{
    public static function Authentication($guard='guest', $userId=false): void
    {
        switch ($guard){
            case 'guest':
                if (isset($_SESSION['logged_user']))
                    header('Location: ./');
                break;

            case 'user':
                if (!isset($_SESSION['logged_user'])) {
                    header('Location: ./login');
                    exit;
                }

                if ($userId && $userId !== $_SESSION['logged_user']['id']){
                    header($_SERVER['SERVER_PROTOCOL'] . ' 405 Method Not Allowed');
                    exit;
                }
                break;
        }
    }

    public static function Csrf(){
        if (!isset($_POST['_token']) || $_POST['_token']!==$_SESSION['_token']){
            header($_SERVER['SERVER_PROTOCOL'] . ' 405 Method Not Allowed');
            exit;
        }
    }
}