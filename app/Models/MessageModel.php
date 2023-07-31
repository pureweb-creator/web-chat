<?php

namespace App\Models;

use App\Core\Model;

class MessageModel extends Model
{
    public function loadMessages($offset, $limit, $message_from, $message_to): bool|array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT user.id as user_id, user.user_name as user_name, message.id as message_id, message.message_text, message.message_from as message_from, message.message_to as message_to, message.message_pub_date as message_pub_date
                FROM message
                    LEFT JOIN user
                        ON user.id = message.message_from
                        WHERE (message.message_from = :message_from AND message.message_to = :message_to) OR
                               (message.message_from = :message_to AND message.message_to = :message_from)
                        ORDER BY message.message_pub_date
                        DESC LIMIT :offset, :limit
            ");

            $stmt->bindValue(':message_to', $message_to);
            $stmt->bindValue(':message_from', $message_from);
            $stmt->bindValue(':offset', (int)$offset, \PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e){
            $this->logger->critical($e->getMessage());
        }

        return $result;
    }

    public function loadFirstMessage($message_from, $message_to)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT message.id FROM message
                    LEFT JOIN user
                        ON user.id = message.message_from
                        WHERE (message.message_from = :message_from AND message.message_to = :message_to) OR
                               (message.message_from = :message_to AND message.message_to = :message_from)
                        ORDER BY message.message_pub_date
                        LIMIT 0, 1
            ");

            $stmt->bindValue(':message_to', $message_to);
            $stmt->bindValue(':message_from', $message_from);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e){
            $this->logger->critical($e->getMessage(), ['file'=>$e->getFile(),'line'=>$e->getLine()]);
        }

        return $result;
    }

    public function addMessage($message): void
    {
        try {
            $stmt = $this->pdo->prepare('INSERT INTO message (message_from, message_to, message_text) VALUES (?,?,?)');
            $stmt->execute([$message['message_from'], $message['message_to'], $message['message_text']]);
        } catch (\PDOException $e){
            echo $e->getMessage();
            $this->logger->critical($e->getMessage(), ['file'=>$e->getFile(),'line'=>$e->getLine()]);
        }
    }
}