<?php

//Firebase jwt library
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

//Load composer's autoloader and dotenv which loads .env files
require 'init.php';

//Connect to db
include 'connectToDb.php';

function setHeaders(){
    header("Content-Type: application/json");
    header("Access-Control-Allow-Origin: http://localhost:3000");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
}

// Check if it's an OPTIONS request and handle it
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    setHeaders();
    header("HTTP/1.1 200 OK");
    exit();
}

setHeaders();

// Check if Authorization header is present
if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(["error" => 'Authorization header missing']);
    exit();
}

// Receive JWT from Frontend
$token = $_SERVER['HTTP_AUTHORIZATION'];
$token = substr($token, 7);
$key = $_ENV['JWT_KEY'];

try {
    // Verify and decode the token
    $decoded = JWT::decode($token, new Key($key, 'HS256'));

    // Extract user ID from jwt
    $userId = $decoded->user_id;

    // Connect to the database
    $pdo = connectToDatabase();

    // Get data from the JSON request
    $requestData = json_decode(file_get_contents("php://input"), true);

    // Check if the task ID is provided in the request
    if (!isset($requestData['taskId'])) {
        http_response_code(400); // Bad Request
        echo json_encode(["error" => 'Task ID is missing']);
        exit();
    }

    // Sanitize and validate the task ID
    $taskId = filter_var($requestData['taskId'], FILTER_VALIDATE_INT);

    if ($taskId === false || $taskId <= 0) {
        http_response_code(400); // Bad Request
        echo json_encode(["error" => 'Invalid Task ID']);
        exit();
    }

    // Prepare and execute the SQL delete statement
    $stmt = $pdo->prepare("DELETE FROM todos WHERE id = :taskId AND user_id = :userId");
    $stmt->bindParam(':taskId', $taskId);
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();

    // Check if a row was affected
    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => "Task removed successfully"]);
    } else {
        http_response_code(404); // Not Found
        echo json_encode(["error" => 'Task not found or unauthorized']);
    }

} catch (PDOException $e) {
    // Log the error for debugging purposes
    error_log("Database error: " . $e->getMessage());

    // Send a generic error message to the client
    http_response_code(500);
    echo json_encode(["error" => "Internal Server Error"]);
}

?>
