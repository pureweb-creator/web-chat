<?php

namespace App\Models;

use App\Core\Model;

class UserModel extends Model
{
    public function loadUsers(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT user_name, email, id FROM user");
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e){
            $this->logger->critical($e->getMessage());
        }

        return $result;
    }

    public function loadUser($field, $value): bool|array
    {
        try{
            $stmt = $this->pdo->prepare('SELECT * FROM user WHERE user.'.$field.' = ?');
            $stmt->execute([$value]);
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e){
            $this->logger->critical($e->getMessage());
        }

        return $result;
    }

    public function updateConfirmationCode($email, $code): bool|\PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare('UPDATE user SET user.confirmation_code = ? WHERE user.email = ?');
            $result = $stmt->execute([$code, $email]);
        } catch (\PDOException $e){
            $this->logger->critical($e->getMessage());
        }

        return $result;
    }

    public function addUser($email, $code): bool|\PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare('INSERT INTO user (user_name, email) VALUES (?,?)');
            $result = $stmt->execute([$code, $email]);
        } catch (\PDOException $e){
            $this->logger->critical($e->getMessage(), ['file'=>$e->getFile(),'line'=>$e->getLine()]);
        }

        return $result;
    }

    public function updateConfirmationStatus($email, $value): bool
    {
        try {
            $stmt = $this->pdo->prepare('UPDATE user SET user.confirmed = ? WHERE user.email = ?');
            $result = $stmt->execute([$value, $email]);
        } catch (\PDOException $e){
            $this->logger->critical($e->getMessage());
        }

        return $result;
    }

    public function updateOnlineStatus($uid, $online){
        try {
            $stmt = $this->pdo->prepare('UPDATE user SET user.online = ? AND user.last_seen = now() WHERE user.id = ?');
            $result = $stmt->execute([$online, time(), $uid]);
        } catch (\PDOException $e){
            $this->logger->critical($e->getMessage());
        }

        return $result;
    }

}