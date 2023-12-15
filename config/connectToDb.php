<?php

function connectToDatabase() {
 
    try {
        $pdo = new PDO("mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_DB']}", $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        http_response_code(500); // Internal Server Error
        echo json_encode(["error" => "Internal Server Error"]);
        exit();
    }
}

?>