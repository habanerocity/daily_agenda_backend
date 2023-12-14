<?php

function connectToDatabase() {
    $db_host = 'localhost';
    $db_user = 'root';
    $db_password = 'root';
    $db_db = 'daily_agenda_todo';

    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_db", $db_user, $db_password);
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