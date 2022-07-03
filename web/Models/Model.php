<?php

namespace Web\Models;

use \RedBeanPHP\R as R;
use Dotenv\Dotenv;

class Model extends \RedBeanPHP\SimpleModel
{
    private static string $host,$db_name,$db_username,$db_password;
    protected array $env;

    protected function __construct()
    {
        $dotenv = Dotenv::createImmutable("http://localhost/chat");
        $this->env = $dotenv->load();

        self::$host = "localhost";//$this->env['DB_HOST'];
        self::$db_name = "chat_development_db";//$this->env['DB_NAME'];
        self::$db_username = "root";//$this->env['DB_USERNAME'];
        self::$db_password = "";//$this->env['DB_PASSWORD'];

        R::setup("mysql:host=".self::$host.";dbname=".self::$db_name, self::$db_username, self::$db_password);
        if (!R::testConnection())
            die('No db connection');
    }

    protected function load_user($email)
    {
        return R::findOne('user', 'WHERE email = ?', [$email]);
    }

    protected function add_message($text, $user_id, $user_name)
    {
        $msg = R::dispense("message");
        $msg->message_text = $text;
        $msg->user_id = $user_id;
        $msg->user_name = $user_name;
        R::store($msg);
    }

    public function add_user($name,$email,$confirmation_code)
    {
        $user = R::dispense('user');
        $user->user_name = $name;
        $user->email = $email;
        $user->confirmation_code = $confirmation_code;
        R::store($user);
    }

    protected function update_confirmation_code($email, $confirmation_code)
    {
        $user = R::findOne('user', 'WHERE email = ?', [$email]);
        $user->confirmation_code = $confirmation_code;
        R::store($user);
    }

    protected function get_first_message()
    {
        return R::findOne('message', 'LIMIT 1');
    }

    protected function load($offset, $limit)
    {
        $message = R::findAll('message','ORDER BY id DESC LIMIT ?,?',[$offset, $limit]);

        return json_encode($message);
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
    }
}