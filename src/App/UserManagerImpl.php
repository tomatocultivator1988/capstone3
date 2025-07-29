<?php

namespace App;

class UserManagerImpl
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function addUser($school_id, $full_name, $role, $year_level = null, $section = null)
    {
        $plainPassword = $school_id . $full_name;
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (school_id, full_name, password, role, year_level, section, created_at, updated_at)
                VALUES (:school_id, :full_name, :password, :role, :year_level, :section, NOW(), NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':school_id', $school_id);
        $stmt->bindValue(':full_name', $full_name);
        $stmt->bindValue(':password', $hashedPassword);
        $stmt->bindValue(':role', $role);
        $stmt->bindValue(':year_level', $role === 'student' ? $year_level : null, \PDO::PARAM_INT);
        $stmt->bindValue(':section', $role === 'student' ? $section : null);

        if ($stmt->execute()) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    public function updateUser($user_id, $school_id, $full_name, $role, $year_level = null, $section = null)
    {
        $sql = "UPDATE users SET school_id = :school_id, full_name = :full_name, role = :role, year_level = :year_level, section = :section, updated_at = NOW()
                WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':school_id', $school_id);
        $stmt->bindValue(':full_name', $full_name);
        $stmt->bindValue(':role', $role);
        $stmt->bindValue(':year_level', $role === 'student' ? $year_level : null, \PDO::PARAM_INT);
        $stmt->bindValue(':section', $role === 'student' ? $section : null);
        $stmt->bindValue(':user_id', $user_id, \PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function deleteUser($user_id)
    {
        $sql = "DELETE FROM users WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, \PDO::PARAM_INT);
        return $stmt->execute();
    }
}