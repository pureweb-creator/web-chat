<?php

namespace App\Models;

use App\Core\Model;

class UserModel extends Model
{
    public function loadUsers(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM user");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function loadUser($field, $value, $what="*"): bool|array
    {
        $stmt = $this->pdo->prepare("SELECT $what FROM user WHERE user.".$field." = ?");
        $stmt->execute([$value]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function update($email, $what, $value)
    {
        $stmt = $this->pdo->prepare("UPDATE user SET $what = ? WHERE user.email = ?");
        return $stmt->execute([$value, $email]);
    }

    public function addUser($email, $code, $hexColor1, $hexColor2): bool|\PDOStatement
    {
        $stmt = $this->pdo->prepare('INSERT INTO user (user_name, email, avatar_color1, avatar_color2) VALUES (?,?,?,?)');
        return $stmt->execute([$code, $email, $hexColor1, $hexColor2]);
    }

    public function updateOnlineStatus($uid, $status){
  
        $stmt = $this->pdo->prepare('UPDATE user SET user.online = ?, user.last_seen = CURRENT_TIMESTAMP WHERE user.id = ?');
        return $stmt->execute([$status, $uid]);
    }

}