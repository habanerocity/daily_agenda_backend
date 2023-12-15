<?php

// Include composer's autoloader
require __DIR__ . '/../vendor/autoload.php'; 

//Load .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

?>