<?php

namespace App;

class TaskController
{
    private Task $gateway;
    private int  $userId;

    public function __construct(Task $gateway, int $userId)
    {
        $this->gateway = $gateway;
        $this->userId = $userId;
    }
    public function processRequest(string $method, ?string $id): void
    {
        if ($id == null) {
            switch ($method) {
                case 'GET':
                    echo json_encode($this->gateway->getAllForUser($this->userId));
                    break;
                case 'POST':
                    $todos = $this->gateway->getAllForUser($this->userId);
                    $errors = $this->getValidationErrors($_POST, true, $todos);
                    if (!empty($errors)) {
                        http_response_code(422);
                        echo json_encode(["errors" => $errors]);
                        return;
                    }

                    $todos = $this->gateway->getTodosByUser($this->userId);
                    if (count($todos) > 5) {
                        http_response_code(423);
                        echo json_encode(["message" => "Too many todos"]);
                        return;
                    }

                    $id = $this->gateway->createForUser($_POST, $this->userId);
                    http_response_code(201);
                    echo json_encode(["message" => "Task is created", "id" => $id]);
                    break;
                default:
                    http_response_code(405);
                    header("Allow: GET, POST");
            }
        } else {
            $task = $this->gateway->getForUser($id, $this->userId);
            if ($task === false) {
                http_response_code(404);
                echo json_encode(["message" => "Task not found"]);
                return;
            }
            switch ($method) {
                case 'GET':
                    echo json_encode($task);
                    break;
                case 'PATCH':
                    $data = json_decode(file_get_contents("php://input"), true);

                    $errors = $this->getValidationErrors($data, false);
                    if (!empty($errors)) {
                        http_response_code(422);
                        echo json_encode(["errors" => $errors]);
                        return;
                    }

                    $rows = $this->gateway->updateForUser($id, $data, $this->userId);
                    http_response_code(200);
                    echo json_encode(["rows" =>  $rows, "message" => "Task is updated"]);
                    break;
                case 'DELETE':
                    $rows = $this->gateway->delete($id);
                    http_response_code(200);
                    echo json_encode(["rows" => $rows, "message" => "Task is deleted"]);
                    break;
                default:
                    http_response_code(405);
                    header("Allow: GET, PATCH', 'DELETE");
            }
        }
    }
    private function getValidationErrors(array $data, bool $is_new = true, $todos = []): array
    {
        $errors = [];

        $allTodosNames = array_column($todos, 'name');
        if (in_array($data['name'], $allTodosNames)) {
            $errors[] = "Todo's name is already exists";
        }

        if ($is_new && empty($data["name"])) {
            $errors[] = "Todo's name is required";
        }

        if (!empty($data["priority"])) {
            if (filter_var($data["priority"], FILTER_VALIDATE_INT) === false) {
                $errors[] = "Todo's priority must be an integer";
            }
        }

        return $errors;
    }
}
