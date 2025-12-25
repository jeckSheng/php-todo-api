<?php

namespace App\Models;

class UserModel
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByEmail($email)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM do_users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function create($email, $password)
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO do_users (email, password) VALUES (?, ?)");
        $stmt->execute([$email, $hash]);
        return $this->pdo->lastInsertId();
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM do_users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
