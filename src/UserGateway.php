<?php

class UserGateway
{
    private PDO $conn;

    public function __construct($database)
    {
        $this->conn = $database->getConnection();
    }

    public function getByAPIKey(string $key)
    {
        $sql = "SELECT *
                FROM user
                WHERE api_key = :api_key";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":api_key", $key, PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
