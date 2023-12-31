<?php

namespace App;

class Auth
{
    private int $userId;
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function authenticateAPIKey(): bool
    {
        $api_key = $_SERVER["HTTP_X_API_KEY"] ?? null;

        if ($api_key === null) {
            http_response_code(400);
            echo json_encode(["message" => "missing API key"]);
            return false;
        }

        $user = $this->user->getByAPIKey($api_key);
        if ($user === false) {
            http_response_code(401);
            echo json_encode(["message" => "invalid API key"]);
            return false;
        }

        $this->userId = $user["id"];

        if ($this->user->getByAPIKey($api_key) === false) {
            http_response_code(401);
            echo json_encode(["message" => "invalid API key"]);
            return false;
        }

        return true;
    }
    public function getUserID(): int
    {
        return $this->userId;
    }
}
