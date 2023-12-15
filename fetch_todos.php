<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

//Load composer's autoloader and dotenv which loads .env files
require 'init.php';

require 'jwt_functions.php';
include 'connectToDb.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: {$_ENV['ALLOWED_ORIGIN']}");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
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
$token = substr($token, 7);  // Remove "Bearer " from token
$key = $_ENV['JWT_KEY'];

if (!$token) {
    http_response_code(401); // Unauthorized
    echo json_encode(["error" => 'Token is missing']);
    exit();
}

try {
    // Verify and decode jwt
    $decoded = verifyJWT($token);

    //Extract userID from jwt
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
