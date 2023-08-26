<?php

namespace App;

use PDO;

class Image
{
    protected PDO $conn;
    protected string $tableName = 'image';

    public function __construct($database,)
    {
        $this->conn = $database->getConnection();
    }
    public function getAllForUser(int $userId): array
    {
        $sql = "SELECT id, title, url
                FROM {$this->tableName}
                WHERE user_id = :user_id
                ORDER BY title";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":user_id", $userId, PDO::PARAM_INT);
        $stmt->execute();
        $data = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return $data;
    }
    public function createForUser(array $data, int $userId): string
    {
        $sql = "INSERT INTO image (title, url, user_id)
                VALUES (:title, :url, :user_id)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":title", $data["title"], PDO::PARAM_STR);
        $stmt->bindValue(":url", $data["url"], PDO::PARAM_STR);
        $stmt->bindValue(":user_id", $userId, PDO::PARAM_STR);

        $stmt->execute();

        return $this->conn->lastInsertId();
    }
    public function deleteForUser(int $id, int $userId): int
    {
        $sql = "DELETE FROM {$this->tableName}
        WHERE id = :id AND user_id = :user_id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->bindValue(":user_id", $userId, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }
    public function getTitleById(int $id, int $userId): string
    {
        $sql = "SELECT title FROM {$this->tableName}
        WHERE id = :id AND user_id = :user_id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->bindValue(":user_id", $userId, PDO::PARAM_INT);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_NUM);
        return $data[0];
    }
}
