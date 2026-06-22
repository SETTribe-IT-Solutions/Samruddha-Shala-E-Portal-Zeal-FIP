<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'samruddha_shala';

try {
    // 1. Connect to MySQL server first without selecting database
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 2. Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // 3. Connect to the specific database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 4. Create users table if it doesn't exist
    $tableSql = "
        CREATE TABLE IF NOT EXISTS `users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `mobile` VARCHAR(15) UNIQUE NOT NULL,
            `password` VARCHAR(255) NOT NULL,
            `role` VARCHAR(50) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $pdo->exec($tableSql);

    // 5. Create work_types table if it doesn't exist
    $workTypesSql = "
        CREATE TABLE IF NOT EXISTS `work_types` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(150) NOT NULL,
            `status` VARCHAR(20) NOT NULL DEFAULT 'Active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $pdo->exec($workTypesSql);

    // 6. Seed default work types if they don't exist
    $workTypeSeeds = [
        'Construction',
        'Non-Construction'
    ];

    $workTypeCheck = $pdo->prepare("SELECT COUNT(*) FROM `work_types` WHERE `name` = :name");
    $workTypeInsert = $pdo->prepare("INSERT INTO `work_types` (`name`, `status`) VALUES (:name, 'Active')");
    foreach ($workTypeSeeds as $seedName) {
        $workTypeCheck->execute([':name' => $seedName]);
        if ($workTypeCheck->fetchColumn() == 0) {
            $workTypeInsert->execute([':name' => $seedName]);
        }
    }
    
    // 7. Seed default users if they don't exist
    $seeds = [
        [
            'name' => 'Demo Head Master',
            'mobile' => '9876543210',
            'password' => 'password123',
            'role' => 'Head Master'
        ],
        [
            'name' => 'Demo CEO',
            'mobile' => '9876543211',
            'password' => 'password123',
            'role' => 'CEO'
        ],
        [
            'name' => 'Demo Sachiv',
            'mobile' => '9876543212',
            'password' => 'password123',
            'role' => 'Sachiv'
        ]
    ];

    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM `users` WHERE `mobile` = :mobile");
    $insertStmt = $pdo->prepare("INSERT INTO `users` (`name`, `mobile`, `password`, `role`) VALUES (:name, :mobile, :password, :role)");

    foreach ($seeds as $seed) {
        $checkStmt->execute([':mobile' => $seed['mobile']]);
        if ($checkStmt->fetchColumn() == 0) {
            $insertStmt->execute([
                ':name' => $seed['name'],
                ':mobile' => $seed['mobile'],
                ':password' => password_hash($seed['password'], PASSWORD_DEFAULT),
                ':role' => $seed['role']
            ]);
        }
    }
    
} catch (PDOException $e) {
    // In production, you would log the error and show a user-friendly message.
    die("Database Connection / Setup Failed: " . $e->getMessage());
}
?>
