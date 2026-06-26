<?php
$host = "82.25.121.144";
$username = "u196817721_S_Eportal_U";
$password = "Sam_shalaEportal@2026";
$database = "u196817721_S_shalaEportal";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$queries = [
    "CREATE TABLE IF NOT EXISTS work_type_master (
        id INT AUTO_INCREMENT PRIMARY KEY,
        work_type_name VARCHAR(100) NOT NULL,
        status VARCHAR(20) DEFAULT 'Active'
    )",
    "CREATE TABLE IF NOT EXISTS work_name_master (
        id INT AUTO_INCREMENT PRIMARY KEY,
        work_type_id INT NOT NULL,
        work_name VARCHAR(200) NOT NULL,
        status VARCHAR(20) DEFAULT 'Active',
        FOREIGN KEY (work_type_id) REFERENCES work_type_master(id)
    )",
    "CREATE TABLE IF NOT EXISTS work_master (
        id INT AUTO_INCREMENT PRIMARY KEY,
        work_type_id INT NOT NULL,
        work_name_id INT NOT NULL,
        additional_notes TEXT,
        created_by INT,
        status VARCHAR(50) DEFAULT 'Pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (work_type_id) REFERENCES work_type_master(id),
        FOREIGN KEY (work_name_id) REFERENCES work_name_master(id)
    )",
    "CREATE TABLE IF NOT EXISTS work_stages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        work_id INT NOT NULL,
        stage_name VARCHAR(200) NOT NULL,
        stage_percentage DECIMAL(5,2) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (work_id) REFERENCES work_master(id) ON DELETE CASCADE
    )"
];

foreach ($queries as $query) {
    if (!mysqli_query($conn, $query)) {
        echo "Error executing query: " . mysqli_error($conn) . "\n";
    }
}

mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");
mysqli_query($conn, "TRUNCATE TABLE work_stages");
mysqli_query($conn, "TRUNCATE TABLE work_master");
mysqli_query($conn, "TRUNCATE TABLE work_name_master");
mysqli_query($conn, "TRUNCATE TABLE work_type_master");
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");

mysqli_query($conn, "INSERT INTO work_type_master (work_type_name) VALUES ('Civilian')");
$civilian_id = mysqli_insert_id($conn);

mysqli_query($conn, "INSERT INTO work_type_master (work_type_name) VALUES ('Non-Civilian')");
$non_civilian_id = mysqli_insert_id($conn);

$work_names = [
    "Construction of Classrooms" => $civilian_id,
    "Construction of Toilets" => $civilian_id,
    "Water Facility Installation" => $civilian_id,
    "Fencing Boundary Walls" => $civilian_id,
    "IT Equipment Supply" => $non_civilian_id,
    "Sports Equipment Supply" => $non_civilian_id,
    "Uniform Distribution" => $non_civilian_id
];

foreach ($work_names as $name => $type_id) {
    $name_esc = mysqli_real_escape_string($conn, $name);
    mysqli_query($conn, "INSERT INTO work_name_master (work_type_id, work_name) VALUES ($type_id, '$name_esc')");
}

echo "Database setup completed successfully.\n";
mysqli_close($conn);
?>
