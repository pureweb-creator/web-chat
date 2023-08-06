<?php 
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helper;
use App\Core\Middleware;
use App\Models\MessageModel;
use App\Models\UserModel;

class HomeController extends Controller{

    protected UserModel $userModel;
    protected MessageModel $messageModel;

    public function __construct()
    {
        parent::__construct();

        $this->userModel = new UserModel($this->logger);
        $this->messageModel = new MessageModel($this->logger);
    }

    public function index(): void
    {
        // check user role
        if (!isset($_SESSION['logged_user']))
            header('Location: ./login');

        $message_to = isset($_GET['private']) && isset($_GET['uid']) ? $_GET['uid'] : -1;

        if ($message_to != -1)
            $recipient = $this->userModel->loadUser('id', $message_to)[0] ?? false;

        $users = $this->userModel->loadUsers();

        $this->data = [
            'title'=>'Chat',
            'users'=>$users,
            'logged_user'=>$_SESSION['logged_user'],
            'message_to'=>$recipient ?? false
        ];

		echo $this->view->render('index.twig', $this->data);
	}

    public function loadMessages(): void
    {
        $offset = intval(htmlspecialchars(trim($_POST['offset'])));
        $limit = intval(htmlspecialchars(trim($_POST['limit'])));
        $messageFrom = intval(htmlspecialchars(trim($_POST['message_from'])));
        $messageTo = intval(htmlspecialchars(trim($_POST['message_to'])));

        Middleware::Authentication('user', $messageFrom);
        Middleware::Csrf();

        if (!$messageFrom || !$messageTo)
            Helper::response('Some fields not filled', false);

        echo json_encode($this->messageModel->loadMessages($offset, $limit, $messageFrom, $messageTo), JSON_HEX_QUOT | JSON_HEX_TAG);
    }

    public function loadFirstMessage(): void
    {
        $messageFrom = intval(htmlspecialchars(trim($_POST['message_from'])));
        $messageTo = intval(htmlspecialchars(trim($_POST['message_to'])));

        Middleware::Authentication('user', $messageFrom);
        Middleware::Csrf();

        if (!$messageFrom || !$messageTo)
            Helper::response('Some fields not filled', false);

        echo json_encode($this->messageModel->loadFirstMessage($messageFrom, $messageTo), JSON_HEX_QUOT | JSON_HEX_TAG);
    }

    public function deleteMessage(): void
    {
        $id = intval(htmlspecialchars(trim($_POST['id'])));
        $messageFrom = intval(htmlspecialchars(trim($_POST['message_from'])));

        Middleware::Authentication('user', $messageFrom);
        Middleware::Csrf();

        if (empty($id))
            Helper::response('No message.', false);

        if ($this->messageModel->deleteMessage($id))
            Helper::response();
    }
}