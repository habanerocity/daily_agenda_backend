<?php

//Firebase jwt library
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

//Load composer's autoloader and dotenv which loads .env files
require 'init.php';

require 'jwt_functions.php';
require 'connectToDb.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: {$_ENV['ALLOWED_ORIGIN']}");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Check if it's an OPTIONS request and handle it
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed"]);
    exit();
}

// Check if Authorization header is present
if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(["error" => 'Authorization header missing']);
    exit();
}

// Receive JWT from Frontend
$token = $_SERVER['HTTP_AUTHORIZATION'];
$token = substr($token, 7); // Remove "Bearer " from token
$key = $_ENV['JWT_KEY'];

try {
    // Verify and decode jwt
    $decoded = verifyJWT($token);

    //Extract userID from jwt
    $userId = $decoded->user_id;
    $pdo = connectToDatabase();

    // Get data from the JSON request
    $requestData = json_decode(file_get_contents("php://input"), true);

// Check if 'completed' and 'data' keys exist in the array before using them
if (isset($requestData['completed']) && isset($requestData['data'])) {
   
    $completed = $requestData['completed'];
    $data = $requestData['data'];

    $stmt = $pdo->prepare("INSERT INTO todos (user_id, description, completed) VALUES (:user_id, :description, :completed)");
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':description', $data);
    $stmt->bindParam(':completed', $completed);
    $stmt->execute();

} else {
    // Handle the case where 'completed' or 'data' is missing
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "Incomplete data"]);
    exit();
}

    echo json_encode(["success" => "Todo added successfully!"]);
} catch (PDOException $e) {
      // Log the error for debugging purposes
      error_log("Database error: " . $e->getMessage());

      // Send a generic error message to the client
      http_response_code(500);
      echo json_encode(["error" => "Internal Server Error"]);
}
?>
