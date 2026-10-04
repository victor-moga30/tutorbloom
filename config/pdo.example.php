<?php

$PDO_HOST = '127.0.0.1';
$PDO_DB   = 'database_name_here';
$PDO_USER = 'username_here';
$PDO_PASS = 'password_here';

try {
    $pdo = new PDO(
        "mysql:host=$PDO_HOST;dbname=$PDO_DB;charset=utf8mb4",
        $PDO_USER,
        $PDO_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die('PDO connection failed.');
}