<?php

//Connect to db
require './config/connectToDb.php';

//Load composer's autoloader and dotenv which loads .env files
require './utils/init.php';

//Set headers
header("Access-Control-Allow-Origin: {$_ENV['ALLOWED_ORIGIN']}");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

//Check if it's an OPTIONS request and handle it
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
   header("HTTP/1.1 200 OK");
   exit();
}

try {
    //Connect to db
    $pdo = connectToDatabase();

    //Get request data
    $eData = file_get_contents('php://input');
    $dData = json_decode($eData, true);

    //Create variables with requesta data
    if($dData){
        $full_name = $dData['fullName'];
        $email = $dData['email'];
        $password = $dData['password'];
    } else {
        //Handle error
        echo json_encode(["error" => "Invalid request data"]);
    }

    if($full_name != "" && $email != "" && $password != ""){
        //Valide user input fields
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
            http_response_code(400); //Bad Request
            echo json_encode(["error" => "Invalid email format!"]);
            exit();      
        } else if (strlen($password) < 8) {
            http_response_code(400); //Bad Request
            echo json_encode(["error" => "Password must be atleast 8 characters long!"]);
            exit();
        }

        //Sanitize user input
        $full_name = filter_var($full_name, FILTER_SANITIZE_STRING);
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $password = filter_var($password, FILTER_SANITIZE_STRING);

        //Hash pw
        $hash = password_hash($password, PASSWORD_DEFAULT);
    
        //Check if the email already exists
        $stmt = $pdo->prepare("SELECT * FROM user WHERE username = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
    
        //Guard clause in case email already exists
        if ($stmt->rowCount() > 0) {
            http_response_code(400); // Bad request
            echo json_encode(["error" => "Email already exists"]);
            exit();
        }
    
        // Insert data into the database
        $stmt = $pdo->prepare("INSERT INTO user (full_name, username, password) VALUES (:full_name, :email, :hash)");
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':hash', $hash);
        $stmt->execute();
    
        //Server response if registration successful
        http_response_code(200);
        echo json_encode(["message" => "Registration successful"]);

    } else {
        //Server response if registration unsuccessful
        http_response_code(400); //Bad request
        echo json_encode(["error" => "Invalid request data"]);
        exit();
    }

} catch (PDOException $e) {
    //Generalized error if there is server error
    error_log("Database error: " . $e->getMessage());
    http_response_code(500); // Internal Server Error
    echo json_encode(["error" => "Internal Server Error"]);
}

?>
