<?php

//Firebase jwt library
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

//Load composer's autoloader and dotenv which loads .env files
require './utils/init.php';

require './config/connectToDb.php';
require './config/jwt_functions.php';

//Function to set headers
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: {$_ENV['ALLOWED_ORIGIN']}");
header("Access-Control-Allow-Methods: PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Check if it's an OPTIONS request and handle it
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
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
$token = substr($token, 7);
$key = $_ENV['JWT_KEY'];

try {
    // Verify and decode jwt
    $decoded = verifyJWT($token);

    //Extract userID from jwt
    $userId = $decoded->user_id;
    
    // Connect to the database
    $pdo = connectToDatabase();

    // Get data from the JSON request
    $requestData = json_decode(file_get_contents("php://input"), true);

    //Check if taskId and Completed data keys exist in the request array before using them
    if(isset($requestData['taskId']) && isset($requestData['completed'])){
        
        //Create variables from request data keys
        $taskId = $requestData['taskId'];
        $completed = $requestData['completed'];
        $completedAt = $requestData['completedAt'];

        //Convert completedAt to a date format that can be stored in mysql database
        if($completedAt){
            $completedAt = date('Y-m-d H:i:s', strtotime($completedAt));
        }

        try {
        //Prepare and execute the SQL update statement
        $stmt = $pdo->prepare("UPDATE todos SET completed = :completed, completedAt = :completedAt WHERE id = :taskId AND user_id = :userId");
        $stmt->bindParam(':completed', $completed);
        $stmt->bindParam(':completedAt', $completedAt);
        $stmt->bindParam(':taskId', $taskId);
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        
        //Return a success message
        echo json_encode(["success" => "Task updated successfully"]);
        } catch(PDOEXCEPTION $e){
            // Handle the exception
            echo json_encode(["error" => $e->getMessage()]);
            exit();
        }
        
    }

}  catch (PDOException $e) {
      // Log the error for debugging purposes
      error_log("Database error: " . $e->getMessage());

      // Send a generic error message to the client
      http_response_code(500);
      echo json_encode(["error" => "Internal Server Error"]);
}
?>
