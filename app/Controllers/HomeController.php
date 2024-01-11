<?php 
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helper;
use App\Core\Middleware;
use App\Core\View;
use App\Models\MessageModel;
use App\Models\UserModel;
use Monolog\Logger;

class HomeController extends Controller{

    protected UserModel $userModel;
    protected MessageModel $messageModel;

    public function __construct(protected View $view, protected Logger $logger)
    {
        parent::__construct();
    
        $this->userModel = new UserModel();
        $this->messageModel = new MessageModel();
    }

    public function index(): void
    {
        Middleware::Authentication('user');

        $message_to = $_GET['uid'] ?? -1;

        if ($message_to != -1)
            $recipient = $this->userModel->loadUser('id', $message_to)[0] ?? false;



        $this->data = [
            'users'=>$this->userModel->loadUsers(),
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
}