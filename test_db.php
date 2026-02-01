<?php
try {
    $dsn = "mysql:host=localhost;dbname=sgaf2;port=3306";
    $user = "root";
    $pass = "";
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Connected successfully to localhost";
} catch (PDOException $e) {
    echo "Connection failed (localhost): " . $e->getMessage() . "\n";
}

echo "\n----------------\n";

try {
    $dsn = "mysql:host=127.0.0.1;dbname=sgaf2;port=3306";
    $user = "root";
    $pass = "";
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Connected successfully to 127.0.0.1";
} catch (PDOException $e) {
    echo "Connection failed (127.0.0.1): " . $e->getMessage() . "\n";
}
