<?php

// Include Firebase JWT Library
use Firebase\JWT\JWT;

//Load composer's autoloader and dotenv which loads .env files
require 'init.php';

//Connect to db
include 'connectToDb.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET");

//Start session
session_start();

//Connect to db and authenticate user logging in
try {

    //Connect to db
    $pdo = connectToDatabase();

    //Get data from front end request
    $eData = file_get_contents("php://input");
    $dData = json_decode($eData, true);

    //Create variables from request data
    if($dData){
        $user = $dData['email'];
        $pass = $dData['password'];
    } else {
        //Handle error
        echo json_encode(["error" => "Invalid request data"]);
        exit;
    }

    //Initialize $result variable
    $result = "";

    //Validate that user credentials are not empty and then query db
    if ($user != "" && $pass != "") {

        //Validate email($user) field
        if (!filter_var($user, FILTER_VALIDATE_EMAIL)) {
            $result = "Invalid email format!";
            echo json_encode(["error" => $result]);
            exit;
        }

        //Validate password($pass) field
        if(strlen($pass) < 8){
            $result = "Password must be atleast 8 characters long!";
            echo json_encode(["error" => $result]);
            exit;
        }

        //Sanitize user input
        $user = filter_var($user, FILTER_SANITIZE_EMAIL);
        $pass = filter_var($pass, FILTER_SANITIZE_STRING);

        //Query db
        $stmt = $pdo->prepare("SELECT * FROM user WHERE username = :username");
        $stmt->bindParam(':username', $user);
        $stmt->execute();

        //Fetch query data
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            if (password_verify($pass, $row['password'])) {
                //Set session variables
                $_SESSION["user_id"] = $row['id'];
                $_SESSION["username"] = $row['username'];
                $_SESSION["full_name"] = $row['full_name'];

                //Create a JWT token
                $token = generateJwtToken($row['id'], $row['username']);

                //Response upon successful login
                $result = "Successful login! Redirecting...";
                echo json_encode(["result" => $result, "token" => $token, "full_name" => $_SESSION["full_name"] ]);
            } else {
                //Response upon unsuccessful login
                $result = "Invalid password!";
                echo json_encode(["result" => $result]);
            }
        } else {
            //Response upon invalid username
            $result = "Invalid username!";
            echo json_encode(["result" => $result]);
        }
    } else {
        //Response upon invalid credentials
        $result = "Empty username or password!";
        echo json_encode(["error" => $result]);
    }
} catch (PDOException $e) {
    //General error response
    $result = "Connection failed: " . $e->getMessage();
    echo json_encode(["error" => $result]);
}

//Generate jwt token
function generateJwtToken($userId, $username)
{
     //Replace with your secret key
    $key = $_ENV['JWT_KEY'];

    // Log JWT key to error log
    error_log("JWT Key: " . $key);

    $issuedAt   = new DateTimeImmutable();
    $expire     = $issuedAt->modify('+1 day')->getTimestamp();
    $serverName = "your.domain.name";
    

    $token = [
        'iat'  => $issuedAt->getTimestamp(),         // Issued at: time when the token was generated
        'iss'  => $serverName,                       // Issuer
        'nbf'  => $issuedAt->getTimestamp(),         // Not before
        'exp'  => $expire,                           // Expiration
        'user_id' => $userId,
        'username' => $username,
    ];

    return JWT::encode($token, $key, 'HS256');
}
?>
