<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=asadvanceit', 'root', '');
    echo "SUCCESS: Connected to database\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>