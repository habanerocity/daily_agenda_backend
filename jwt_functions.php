<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Function to generate a JWT
function generateJWT($data, $sessionData) {
    $key = 'your_secret_key';

    // Set the issued at, expiration, and server name claims
    $issuedAt   = new DateTimeImmutable();
    $expire     = $issuedAt->modify('+1 day')->getTimestamp();
    $serverName = "your.domain.name";

    $dataToEncode = array(
        'iat' => $issueAt->getTimestamp(),
        'exp' => $expire,
        'iss' => $serverName,
        'userData' => $userData
    );

    //Encode jwt
    $token = JWT::encode($dataToEncode, new Key($key, 'HS256'));

    //Return token
    return $token;
}

// Function to verify and decode a JWT
function verifyJWT($token) {
    $key = getenv('JWT_KEY');
    
    try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        return $decoded;
    } catch (Exception $e) {
        // Handle JWT verification failure, e.g., log error
        error_log('JWT Verification Error: ' . $e->getMessage());
        return null;
    }
}

?>
