<?php

namespace Web\Controllers;

use Web\Models\Model;
use Web\Views\View;
use Web\Controllers\Traits\CheckData;
use Workerman\Worker;

class Controller extends Model
{
    use CheckData;

    public View $view;
    protected $validation = VALIDATION_MESSAGES;
    private $response = [];

    public function __construct()
    {
        $this->view = new View();
        parent::__construct();
    }

    public function home_view()
    {
        if (!isset($_SESSION['logged_user']))
            header("Location: login");

        // render twig template
        return $this->view->generate("home.twig", ["page_title"=>"Chat","user"=>$_SESSION['logged_user']]);
    }

    public function websocket()
    {
        $wsWorker = new Worker('websocket://0.0.0.0:2346');
        $wsWorker->count = 1;

        $wsWorker->onConnect = function ($conn) {
            $conn->send('message');
        };

        $wsWorker->onMessage = function ($conn, $data) use ($wsWorker) {
            $message_data = json_decode($data, true);

            $msg = htmlspecialchars(trim($message_data['message_text']));
            $msg = $message_data['message_text'];

            $msg = preg_split("/\r\n|\n|\r/", $msg);
            print_r($msg);
            $msg = array_map(function($item){
                return explode(' ', $item);
            }, $msg);
            print_r($msg);

            foreach ($msg as &$val){
                $val = preg_replace('/^\*\*/', '<b>', $val);
                $val = preg_replace('/\*\*$/', '</b>', $val);

                $val = preg_replace('/^--/', '<em>', $val);
                $val = preg_replace('/--$/', '</em>', $val);

                $val = preg_replace('/^```/', '<pre>', $val);
                $val = preg_replace('/```$/', '</pre>', $val);

                $val = preg_replace('/^__/', '<s>', $val);
                $val = preg_replace('/__$/', '</s>', $val);
            }

            foreach ($msg as &$val)
                $val = implode(" ", $val);

            print_r($msg);

            $msg = implode("\n", $msg);

            print_r($msg);

            $this->add_message($msg, $message_data['user_id'],$message_data['user_name']);

            foreach ($wsWorker->connections as $connection)
                $connection->send($this->load(0, 100));
        };

        $wsWorker->onClose = function ($conn) {
            echo 'Connection closed ';
        };

        Worker::runAll();
    }

    public function notfound_view(): string
    {
        return $this->view->generate('404.twig', ['page_title' => 'Not found']);
    }

    public function redirect(): string
    {
        // init configuration
        $clientId = $this->env['CLIENT_ID'];
        $clientSecret = $this->env['CLIENT_SECRET'];
        $redirectUri = "https://2d0c-5-153-169-162.eu.ngrok.io/chat/redirect";

        // create Client Request to access Google API
        $client = new \Google_Client();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri($redirectUri);
        $client->addScope("email");
        $client->addScope("profile");

        // authenticate code from Google OAuth Flow
        if (isset($_GET['code'])){
            $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
            $client->setAccessToken($token['access_token']);

            // get profile info
            $google_oauth = new \Google_Service_Oauth2($client);
            $google_oauth_info = $google_oauth->userinfo->get();
            $email = $google_oauth_info->email;
            $name = $google_oauth_info->name;

        // auth
        } else
            header("Location: {$client->createAuthUrl()}");

        return "redirect";
    }

    public function signup_view()
    {
        return $this->view->generate('signup.twig', ['page_title' => 'Sign Up']);
    }

    public function signup_action()
    {
        $name = htmlspecialchars(trim($_POST['name']));
        $email = htmlspecialchars(trim($_POST['email']));

        // do check
        if ($this->is_empty_data($name, "no_name") ||
            $this->is_empty_data($email, "no_email") ||
            $this->is_user_exists($email)) {
            return json_encode($this->response);
        }

        $confimation_code = rand(10000,99999);

        // if data is correct
        $this->add_user($name, $email, $confimation_code);

        $headers = "From: no-reply@chat.com;\nContent-type: text/html charset=utf-8\nReply-to: no-reply@chat.com";
        mail($email, "=?UTF-8?B?".base64_encode("Подтверждение")."?=","Your confimation code is: <b>$confimation_code</b>", $headers);

        $user = $this->load_user($email);

        // make response to front end
        $this->response['ok'] = true;
        return json_encode($this->response);
    }

    public function login_action()
    {
        $email = htmlspecialchars(trim($_POST['email']));

        // do check
        if ($this->is_empty_data($email, "no_email") ||
            !$this->is_user_exists($email)) {
            return json_encode($this->response);
        }

        $confimation_code = rand(10000,99999);
        $this->update_confirmation_code($email, $confimation_code);
        
        $headers = "From: no-reply@chat.com;\nContent-type: text/html charset=utf-8\nReply-to: no-reply@chat.com";
        mail($email, "=?UTF-8?B?".base64_encode("Подтверждение")."?=","Your confimation code is: <b>$confimation_code</b>", $headers);

        // if data is correct
        $user = $this->load_user($email);

        // make response to front end
        $this->response['ok']=true;
        return json_encode($this->response);
    }

    public function confirmation_view()
    {
        return $this->view->generate('confirm.twig',
            ['page_title' => 'Enter code',
                'email'=>$_REQUEST['email']
            ]);
    }

    public function login_view()
    {
        return $this->view->generate('login.twig', ['page_title' => 'Login']);
    }

    public function auth_action()
    {
        $email = htmlspecialchars(trim($_POST['email']));
        $code = htmlspecialchars(trim($_POST['code']));

        // basic check
        if ($this->is_empty_data($email, 'no_email') ||
            $this->is_wrong_email($email) || 
            $this->is_empty_data($code, 'no_code') || 
            $this->is_wrong_code($email, $code)){

            return json_encode($this->response);
        }

        // do auth
        if ($this->is_user_exists($email)) {
            $user = $this->load_user($email);

            $_SESSION['logged_user'] = $user;

            // make response to front end
            $this->response['ok']=true;
            return json_encode($this->response);
        }

        return json_encode($this->response);
    }

    public function get_first_message()
    {
        return parent::get_first_message();
    }

    public function logout()
    {
        unset($_SESSION['logged_user']);
        header("Location: login");
    }

    public function load_messages_action()
    {
        $offset = $_GET['offset'];
        $limit = $_GET['limit'];

        return $this->load($offset, $limit);
    }
}