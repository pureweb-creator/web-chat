<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helper;
use App\Core\Middleware;
use App\Core\View;
use App\Models\UserModel;
use Monolog\Logger;
use PHPMailer\PHPMailer\PHPMailer;

class ConfirmController extends Controller
{
    protected UserModel $userModel;
    protected Logger $logger;
    protected View $view;

    public function __construct(View $view, Logger $logger)
    {
        parent::__construct();
        $this->view = $view;
        $this->logger = $logger;
        $this->userModel = new UserModel();
    }
    public function index(): void
    {
        Middleware::Authentication();

        if (isset($_GET['email']))
            $this->sendConfirmation($_GET['email']);

        $this->data = [
            'email' => $_GET['email'] ?? false
        ];

        echo $this->view->render('confirm.twig', $this->data);
    }

    public function process(){

        if (!$_POST) die;

        Middleware::Authentication();
        Middleware::Csrf();

        $email = htmlspecialchars(trim($_POST['email']));
        $code = htmlspecialchars(trim($_POST['code']));
        $code = str_replace(',','',$code);

        if (empty($code))
            Helper::response('No confirmation code', false);

        $user = $this->userModel->loadUser('email', $email)[0];

        if ($user['is_banned'] && time() > strtotime($user['banned_until'])) {
            # unban user
            $this->userModel->update($email, 'is_banned', 0);
            $this->userModel->update($email, 'banned_until', null);
            $this->userModel->update($email, 'login_attempts', 0);
        }
        else if ($user['is_banned'] && time() < strtotime($user['banned_until']))
            Helper::response('You were banned. Try again after 24 hours.', false);

        if ($user['confirmation_code'] !== $code) {

            $login_attempts = $this->userModel->loadUser('email', $email, 'login_attempts')[0]['login_attempts'];

            if ($login_attempts < MAX_LOGIN_ATTEMPTS-1){
                $login_attempts++;
                $this->userModel->update($email, 'login_attempts', $login_attempts);
                $attempts_left=MAX_LOGIN_ATTEMPTS-$login_attempts;
                Helper::response("Wrong confirmation code. Attempts left: $attempts_left", false);
            }

            if ($login_attempts == MAX_LOGIN_ATTEMPTS-1){
                $this->userModel->update($email, 'is_banned', true);
                $this->userModel->update($email, 'banned_until', date('Y-m-d H:i:s', time()+3600*24));
                Helper::response('You were banned. Try again after 24 hours.', false);
            }
        }

        $this->userModel->update($email, 'confirmed', 1); # for registration only
        $this->userModel->update($email, 'login_attempts', 0);
        $_SESSION['logged_user'] = $user;
        $this->userModel->update($email, 'confirmation_code', '');

        Helper::response();
    }

    public function sendConfirmation($email): void
    {
        $confirmation_code = rand(10000, 99999);
        $this->userModel->update($email, 'confirmation_code', $confirmation_code);

        $mail = new PHPMailer();
        try{
            $mail->isSMTP();
            $mail->SMTPDebug  = false;
            $mail->Host       = GOOGLE_SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = GOOGLE_SMTP_USERNAME;
            $mail->Password   = GOOGLE_SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = GOOGLE_SMTP_SSL_PORT;

            $mail->setFrom(GOOGLE_SMTP_USERNAME);
            $mail->addAddress($email);
            $mail->addReplyTo(GOOGLE_SMTP_USERNAME);

            $mail->isHTML();
            $mail->Subject = "E-mail confirmation";
            $mail->Body = "<h1><strong>$confirmation_code</strong></h1>";
            $mail->send();

        } catch (\Exception $e){
            $this->logger->error("Message could not be sent. Mail error: {$mail->ErrorInfo}");
        }
    }
}