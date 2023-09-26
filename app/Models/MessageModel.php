<?php

namespace App\Models;

use App\Core\Model;

class MessageModel extends Model
{
    public function loadMessages($offset, $limit, $message_from, $message_to)
    {
        $stmt = $this->pdo->prepare("
            SELECT message.*, message.id as message_id, user.id as sender_id, user.*
            FROM message
            RIGHT JOIN user
            ON message.message_from = user.id
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

        return $result ?? false;
    }

    public function addMessage($message): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO message (message_from, message_to, message_text) VALUES (?,?,?)');
        $stmt->execute([$message['message_from'], $message['message_to'], $message['message_text']]);
    }

    public function deleteMessage($msgId){
        $stmt = $this->pdo->prepare("DELETE FROM message WHERE id = ?");
        $stmt->execute([$msgId]);
    }
}