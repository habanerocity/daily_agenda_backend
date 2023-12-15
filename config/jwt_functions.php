<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

//Generate jwt token
function generateJwtToken($userId, $username)
{
     //Replace with your secret key
    $key = $_ENV['JWT_KEY'];
    $serverName = $_ENV['SERVER_NAME'];

    $issuedAt   = new DateTimeImmutable();
    $expire     = $issuedAt->modify('+1 day')->getTimestamp();
    
    $token = [
        'iat'  => $issuedAt->getTimestamp(),         // Issued at: time when the token was generated
        'iss'  => $serverName,                       // Issuer
        'nbf'  => $issuedAt->getTimestamp(),         // Not before
        'exp'  => $expire,                           // Expiration
        'user_id' => $userId,
        'username' => $username,
    ];

    try {
        return JWT::encode($token, $key, 'HS256');
    } catch (Exception $e) {
        error_log($e->getMessage());
        return ['error' => 'jwt_encoding_error'];
    }

}

// Function to verify and decode a JWT
function verifyJWT($token) {
    $key = $_ENV['JWT_KEY'];
    
    try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        return $decoded;
    } catch (Exception $e) {
        error_log($e->getMessage());
        http_response_code(401);
        echo json_encode(["error" => 'Invalid token']);
        exit;
    }
}

?>
