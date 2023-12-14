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
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
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

if (!$token) {
    http_response_code(401); // Unauthorized
    echo json_encode(["error" => 'Token is missing']);
    exit();
}

try {
    // Verify and decode the token
    $decoded = JWT::decode($token, new Key($key, 'HS256'));

    // Extract user ID
    $userId = $decoded->user_id;

    // Connect to the database
    $pdo = connectToDatabase();

    //Query table for todos
    $stmt = $pdo->prepare("SELECT * 
                           FROM todos 
                           WHERE user_id = :user_id");

    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();

    //Fetch the todos as an associative array
    $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    //Return the todos as JSON
    echo json_encode($todos);

}  catch (PDOException $e) {
      // Log the error for debugging purposes
      error_log("Database error: " . $e->getMessage());

      // Send a generic error message to the client
      http_response_code(500);
      echo json_encode(["error" => "Internal Server Error"]);
}
?>
