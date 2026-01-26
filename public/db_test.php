<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=sgaf2', 'root', '');
    echo "Database connection successful!\n";
    
    $stmt = $pdo->query("SELECT id, email, password, nombre FROM users LIMIT 1");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Users found: " . count($users) . "\n";
    print_r($users);
    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
