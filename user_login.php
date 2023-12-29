<?php

use Firebase\JWT\JWT;

//Load composer's autoloader and dotenv which loads .env files
require './utils/init.php';

require './config/connectToDb.php';
require './config/jwt_functions.php';

//Set headers
header("Access-Control-Allow-Origin: {$_ENV['ALLOWED_ORIGIN']}");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

//Check if it's an OPTIONS request and handle it
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
   header("HTTP/1.1 200 OK");
   exit();
}

//Connect to db and authenticate user login
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
            echo json_encode(["username_error" => $result]);
            exit;
        }

        //Validate password($pass) field
        if(strlen($pass) < 8){
            $result = "Invalid Password!";
            echo json_encode(["pass_error" => $result]);
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
            
                try {
                    //Generate a JWT token
                    $token = generateJwtToken($row['id'], $row['username']);

                    //Response upon successful login
                    $result = "Successful login! Redirecting...";
                    echo json_encode(["result" => $result, "token" => $token, "full_name" => $row['full_name'] ]);
                } catch (Exception $e) {
                    //Response upon JWT error
                    $result = "Failed to generate JWT Token: " . $e->getMessage();
                    echo json_encode(["error" => $result]);
                    exit;
                }

            } else {
                //Response upon invalid password
                $result = "Invalid password!";
                echo json_encode(["pass_error" => $result]);
            }
        } else {
            //Response upon invalid username
            $result = "Invalid username!";
            echo json_encode(["username_error" => $result]);
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

?>
