<?php
header('Content-Type: text/plain');
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = "82.25.121.144";
$username = "u196817721_S_Eportal_U";
$password = "Sam_shalaEportal@2026";
$database = "u196817721_S_shalaEportal";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully to remote DB.\n\n";
    
    $stmt = $pdo->query("SHOW TABLES");
    echo "Tables in database:\n";
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "- " . $row[0] . "\n";
    }
    echo "\n";
    
    // Check if talukas_school_data exists
    $stmt = $pdo->query("SHOW TABLES LIKE '%talukas%'");
    if ($stmt->rowCount() > 0) {
        $table = $stmt->fetch(PDO::FETCH_NUM)[0];
        echo "Found table: $table\n";
        
        // Get columns
        $cols = $pdo->query("SHOW COLUMNS FROM `$table`");
        echo "Columns:\n";
        while ($col = $cols->fetch(PDO::FETCH_ASSOC)) {
            echo "  " . $col['Field'] . "\n";
        }
        
        // Get data
        $data = $pdo->query("SELECT * FROM `$table` LIMIT 10");
        echo "\nData (up to 10 rows):\n";
        $rows = $data->fetchAll(PDO::FETCH_ASSOC);
        print_r($rows);
    } else {
        echo "No table matching '%talukas%' found in this database.\n";
        
        // Look at ceo_create_tasks
        $stmt = $pdo->query("SHOW TABLES LIKE 'ceo_create_tasks'");
        if ($stmt->rowCount() > 0) {
            echo "\nFound table: ceo_create_tasks\n";
            $data = $pdo->query("SELECT * FROM `ceo_create_tasks` LIMIT 5");
            $rows = $data->fetchAll(PDO::FETCH_ASSOC);
            print_r($rows);
        }
    }
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
?>
