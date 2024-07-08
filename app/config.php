<?php
require __DIR__ . '/vendor/autoload.php'; 

use Dotenv\Dotenv;


$dotenv = Dotenv::createImmutable(__DIR__); 
$dotenv->load();


$dbConfig = [
    'host' => $_ENV['DB_HOST'],
    'dbname' => $_ENV['DB_NAME'],
    'charset' => $_ENV['DB_CHARSET'],
    'user' => $_ENV['DB_USER'],
    'pass' => $_ENV['DB_PASS']
];
?>