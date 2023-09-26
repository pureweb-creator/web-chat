<?php

namespace App\Controllers;

use App\Core\Helper;
use App\Models\MessageModel;
use App\Models\UserModel;
use Monolog\Logger;
use Workerman\Worker;

class WebsocketController extends \App\Core\Controller
{
    protected MessageModel $messageModel;
    protected UserModel $userModel;
    public function __construct(Logger $logger)
    {
        parent::__construct();
        $this->messageModel = new MessageModel();
        $this->userModel = new UserModel();
    }

    public function listen(): void
    {
        $wsWorker = new Worker('websocket://0.0.0.0:8282'); // 8282, 8000
        $wsWorker->count = 1;
        $activeConnections = [];

        $wsWorker->onConnect = function ($conn) use (&$activeConnections) {
            $conn->onWebSocketConnect = function($conn) use (&$activeConnections){
                
                // collect connection by user id
                $activeConnections[$_GET['user']] = $conn;

                $this->userModel->updateOnlineStatus($_GET['user'], 1);
                
                $response = json_encode([
                    'action'=>'onConnect',
                    'data'=>$this->userModel->loadUsers()
                ]);

                foreach ($activeConnections as $key=>$value)
                    $value->send($response);
            };
        };

        $wsWorker->onMessage = function ($conn, $data) use ($wsWorker, &$activeConnections) {
            $data = json_decode($data);

            switch ($data->action) {
                case 'addMessage':
                    $message = json_decode($data->message, true);
                    $message_text = htmlspecialchars(trim($message['message_text']));
                    
                    $pattern = "/
                        (
                            (
                                (
                                    ((ftp|http)s?:\/\/)? # protocols
                                    (www\.)?
                                    [A-Za-z0-9.-]+
                                    \.
                                    [A-Za-z]{2,6}
                                ) |
                                (
                                    ((ftp|http)s?:\/\/)?
                                    ((25[0-5]|(2[0-4]|1\d|[1-9]|)\d)\.?\b){4} # ip address
                                )
                            )
                            [a-zA-Z0-9\.\-_\~!$&\'\(\)\*\+,;=:@%\?\#\/]{0,} # least url parts
                        )
                        /x";

                    $message_text = preg_replace_callback(
                        $pattern,
                        function($matches){
                            if (str_starts_with($matches[0], 'http://') || 
                                str_starts_with($matches[0], 'https://') ||
                                str_starts_with($matches[0], 'ftp://') ||
                                str_starts_with($matches[0], 'ftps://')
                            )
                                return "<a href='{$matches[0]}' target='_blank'>{$matches[0]}</a>";
                            
                            return "<a href='https://{$matches[0]}' target='_blank'>{$matches[0]}</a>";
                                
                        },
                        $message_text);

                    $message_text = nl2br($message_text);

                    $message_text = preg_replace('/\*\*(.*?)\*\*/isx', '<b>$1</b>', $message_text);
                    $message_text = preg_replace('/--(.*?)--/isx', '<em>$1</em>', $message_text);
                    $message_text = preg_replace('/```(.*?)```/isx', '<pre>$1</pre>', $message_text);
                    $message_text = preg_replace('/__(.*?)__/isx', '<s>$1</s>', $message_text);

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
                            // return last 100 messages from conversation
                            'data' => $this->messageModel->loadMessages(0, 100, $message['message_from'], $message['message_to'])
                        ]);

                        // send message to current connection
                        $activeConnections[$message['message_from']]->send($response);

                        // send message to recipient connection (but not to self)
                        if (array_key_exists($message['message_to'], $activeConnections) && $message['message_from'] !== $message['message_to'])
                            $activeConnections[$message['message_to']]->send($response);
                    }
                    break;
                case 'startTyping':
                    if (array_key_exists($data->message_to, $activeConnections)) {
                        $activeConnections[$data->message_to]->send(
                            json_encode([
                                'action' => 'onStartTyping',
                            ])
                        );
                    }
                    break;
                case 'endTyping':
                    if (array_key_exists($data->message_to, $activeConnections)) {
                        $activeConnections[$data->message_to]->send(
                            json_encode([
                                'action' => 'onEndTyping',
                            ])
                        );
                    }
                    break;
            }
        };

        $wsWorker->onClose = function ($conn) use (&$activeConnections) {

            $user = array_search($conn, $activeConnections);

            $this->userModel->updateOnlineStatus($user, 0);
                
            $response = json_encode([
                'action'=>'onDisconnect',
                'data'=>$this->userModel->loadUsers()
            ]);

            foreach ($activeConnections as $key=>$value)
                $value->send($response);

            unset($activeConnections[$user]);
        };

        Worker::runAll();
    }
}