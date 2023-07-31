<?php

namespace App\Controllers;

use App\Models\MessageModel;
use Workerman\Worker;

class WebsocketController extends \App\Core\Controller
{
    protected MessageModel $messageModel;
    public function __construct()
    {
        parent::__construct();
        $this->messageModel = new MessageModel($this->logger);
    }

    public function listen(): void
    {
        $wsWorker = new Worker('websocket://0.0.0.0:2346');
        $wsWorker->count = 1;
        $activeConnections = [];

        $wsWorker->onConnect = function ($conn) use (&$activeConnections) {
            $conn->onWebSocketConnect = function($conn) use (&$activeConnections){
                echo 'Connection opened ';
                $conn->send('message');

                // collect connection by user id
                $activeConnections[$_GET['user']] = $conn;
            };
        };

        $wsWorker->onMessage = function ($conn, $data) use ($wsWorker, &$activeConnections) {

            $message_data = json_decode($data, true);

            $message_text = htmlspecialchars(trim($message_data['message_text']));
            $message_text = preg_replace('/\*\*(.*?)\*\*/isx', '<b>$1</b>', $message_text);
            $message_text = preg_replace('/--(.*?)--/isx', '<em>$1</em>', $message_text);
            $message_text = preg_replace('/```(.*?)```/isx', '<pre>$1</pre>', $message_text);
            $message_text = preg_replace('/__(.*?)__/isx', '<s>$1</s>', $message_text);
            $message_data['message_text'] = $message_text;

            $this->messageModel->addMessage($message_data);

            $addedMessage = json_encode($this->messageModel->loadMessages(0, 1, $message_data['message_from'], $message_data['message_to']));

            // send message to current connection
            $activeConnections[$message_data['message_from']]->send($addedMessage);

            // send message to recipient connection (but not to self)
            if (array_key_exists($message_data['message_to'], $activeConnections) && $message_data['message_from'] !== $message_data['message_to'])
                $activeConnections[$message_data['message_to']]->send($addedMessage);
        };

        $wsWorker->onClose = function ($conn) use (&$activeConnections) {
            echo 'Connection closed ';
            $user = array_search($conn, $activeConnections);
            unset($activeConnections[$user]);
        };

        Worker::runAll();
    }
}