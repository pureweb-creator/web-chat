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
    protected View $view;

    public function __construct(View $view, Logger $logger)
    {
        parent::__construct();
        $this->view = $view;
        $this->userModel = new UserModel($logger);
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

        $user = $this->userModel->loadUser('email',$email)[0];
        if ($user['confirmation_code'] !== $code)
            Helper::response('Wrong confirmation code', false);

        $this->userModel->updateConfirmationStatus($email, 1);
        $_SESSION['logged_user'] = $user;
        $this->userModel->updateConfirmationCode($email, '');

        Helper::response();
    }

    public function sendConfirmation($email): void
    {
        $confirmation_code = rand(10000, 99999);
        $this->userModel->updateConfirmationCode($email, $confirmation_code);

        $mail = new PHPMailer();
        try{
            $mail->isSMTP();
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
            Helper::response('Email does not sent. Please, try again later.', false, true);
            $this->logger->error("Message could not be sent. Mail error: {$mail->ErrorInfo}");
        }
    }
}