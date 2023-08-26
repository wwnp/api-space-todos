<?php

namespace App;



class ImageController
{
    protected Image $model;
    protected int  $userId;
    protected $s3;
    public function __construct($model, $userId, $s3)
    {
        $this->model = $model;
        $this->userId = $userId;
        $this->s3 = $s3;
    }
    public function processRequest(string $method, ?string $id): void
    {
        if ($id == null) {
            switch ($method) {
                case 'GET':
                    http_response_code(200);
                    echo json_encode($this->model->getAllForUser($this->userId));
                    break;
                case 'POST':
                    $images = $this->model->getAllForUser($this->userId);
                    $errors = $this->getValidationErrors(array_merge($_POST, $_FILES), true, $images);
                    if (!empty($errors)) {
                        http_response_code(422);
                        echo json_encode(["errors" => $errors]);
                        return;
                    }

                    $bucketName = 'gallery-draen';
                    $objectKey = 'images/' . $_POST['title']; // You can adjust the path and object key

                    $result = $this->s3->putObject([
                        'Bucket' => $bucketName,
                        'Key'    => $objectKey,
                        'Body'   => fopen($_FILES['image']['tmp_name'], 'r'),
                    ]);

                    // file_put_contents('file_upload_debug.log', $ext . "\n\n", FILE_APPEND);

                    if ($result) {
                        http_response_code(201);
                        $id = $this->model->createForUser(array_merge($_POST, ["url" => $result['ObjectURL']]), $this->userId);
                        echo json_encode(["message" => "Image uploaded successfully", "id" => $id]);
                    } else {
                        http_response_code(500);
                        echo json_encode(["message" => "Image upload failed"]);
                    }
                    break;
                default:
                    http_response_code(405);
                    header("Allow: GET, PATCH, POST, DELETE");
                    break;
            }
        } else {
            switch ($method) {
                case 'DELETE':
                    $titleOfImage = $this->model->getTitleById($id, $this->userId);

                    $objectKey = 'images/' . $titleOfImage;
                    $deleteResult = $this->s3->deleteObject([
                        'Bucket' => 'gallery-draen',
                        'Key'    => $objectKey,
                    ]);

                    $rowCount = $this->model->deleteForUser($id, $this->userId);

                    if ($deleteResult && $rowCount) {
                        http_response_code(200);
                        echo json_encode(["rowCount" => $rowCount, "message" => "Image was deleted"]);
                    } else {
                        http_response_code(500);
                        echo json_encode(["message" => "Image deleting failed"]);
                    }
                    break;
                default:
                    http_response_code(405);
                    header("Allow: GET, PATCH, POST, DELETE");
            }
        }
    }


    private function getValidationErrors(array $data, bool $is_new = true, $images = []): array
    {
        $errors = [];

        $allImagesNames = array_column($images, 'title');
        if (in_array($data['title'], $allImagesNames)) {
            $errors[] = "Image's title is already exists";
        }

        if ($is_new && empty($data["title"])) {
            $errors[] = "Image's title is required";
        }

        return $errors;
    }
}
