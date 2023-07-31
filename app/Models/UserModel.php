<?php

namespace App\Models;

use App\Core\Model;

class UserModel extends Model
{
    public function loadUsers(): array
    {
        try {
            $result = $this->pdo->query('SELECT * FROM user')->fetchAll();
        } catch (\PDOException $e){
            $this->logger->critical($e->getMessage());
        }

        return $result;
    }

    public function loadUser($email): bool|array
    {
        try{
            $stmt = $this->pdo->prepare('SELECT * FROM user WHERE user.email = ?');
            $stmt->execute([$email]);
            $result = $stmt->fetchAll();
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
}