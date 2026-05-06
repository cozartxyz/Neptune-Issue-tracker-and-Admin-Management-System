<?php

// database stuffs

//require_once __DIR__ . "/includes/functions.php";

$host = "localhost";
$dbname = "issue_tracker";
$username = "root";
$password = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // catch error for db

} catch (PDOException $e) {
    neptuneMessage("Database error connection failed: " . $e->getMessage());
}

