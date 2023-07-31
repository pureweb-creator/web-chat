<?php 
namespace App\Controllers;

use App\Core\Controller;
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

    public function index()
    {
        // check user role
        if (!isset($_SESSION['logged_user']))
            header('Location: ./login');

        $message_to = isset($_GET['private']) && isset($_GET['uid']) ? $_GET['uid'] : -1;

        $users = $this->userModel->loadUsers();

        $this->data = [
            'title'=>'Chat',
            'users'=>$users,
            'logged_user'=>$_SESSION['logged_user'],
            'message_to'=>$message_to
        ];

		echo $this->view->render('index.twig', $this->data);
	}

    public function loadMessages()
    {
        echo json_encode(array_reverse($this->messageModel->loadMessages($_GET['offset'], $_GET['limit'], $_GET['message_from'], $_GET['message_to'])));
    }

    public function loadFirstMessage(){
        echo json_encode($this->messageModel->loadFirstMessage($_GET['message_from'], $_GET['message_to']));
    }

}