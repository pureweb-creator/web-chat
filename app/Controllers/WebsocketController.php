<?php

namespace App\Controllers;

use App\Core\Helper;
use App\Core\Middleware;
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
        $wsWorker = new Worker('websocket://0.0.0.0:8282'); // 8282 for docker. 8000 for wamp
        $wsWorker->count = 1;
        $activeConnections = [];

        $wsWorker->onConnect = function ($conn) use (&$activeConnections) {
            $conn->onWebSocketConnect = function($conn) use (&$activeConnections){
                // collect connection by user id
                $activeConnections[$_GET['user']] = $conn;
            };
        };

        $wsWorker->onMessage = function ($conn, $data) use ($wsWorker, &$activeConnections) {
            $data = json_decode($data);

            switch ($data->action) {
                case "addMessage":
                    $message = json_decode($data->message, true);

                    $message_text = htmlspecialchars(trim($message['message_text']));
                    $message_text = preg_replace('/\*\*(.*?)\*\*/isx', '<b>$1</b>', $message_text);
                    $message_text = preg_replace('/--(.*?)--/isx', '<em>$1</em>', $message_text);
                    $message_text = preg_replace('/```(.*?)```/isx', '<pre>$1</pre>', $message_text);
                    $message_text = preg_replace('/__(.*?)__/isx', '<s>$1</s>', $message_text);
                    $message_text = nl2br($message_text);

                    $message['message_text'] = $message_text;

                    $this->messageModel->addMessage($message);

                    $addedMessage = $this->messageModel->loadMessages(0, 1, $message['message_from'], $message['message_to']);

                    $response = json_encode([
                        'action'=>'addMessage',
                        'data'=>$addedMessage
                    ]);

                    // send message to current connection
                    $activeConnections[$message['message_from']]->send($response);

                    // send message to recipient connection (but not to self)
                    if (array_key_exists($message['message_to'], $activeConnections) && $message['message_from'] !== $message['message_to'])
                        $activeConnections[$message['message_to']]->send($response);

                    break;

                case 'deleteMessage':
                    $message = json_decode($data->message, true);

                    if (empty($message['message_id']))
                        Helper::response('No message.', false);

                    if ($this->messageModel->deleteMessage($message['message_id'])){

                        $response = json_encode([
                            'action' => 'deleteMessage',
                            'success' => true,
                            'data' => $this->messageModel->loadMessages(0, 100, $message['message_from'], $message['message_to'])
                        ]);

                        // send message to current connection
                        $activeConnections[$message['message_from']]->send($response);

                        // send message to recipient connection (but not to self)
                        if (array_key_exists($message['message_to'], $activeConnections) && $message['message_from'] !== $message['message_to'])
                            $activeConnections[$message['message_to']]->send($response);
                    }
            }
        };

        $wsWorker->onClose = function ($conn) use (&$activeConnections) {
            $user = array_search($conn, $activeConnections);
            unset($activeConnections[$user]);
        };

        Worker::runAll();
    }
}