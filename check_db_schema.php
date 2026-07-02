<?php
$host = '82.25.121.144';
$user = 'u196817721_S_Eportal_U';
$pass = 'Sam_shalaEportal@2026';
$db = 'u196817721_S_shalaEportal';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo "CONNECT_ERROR: {$conn->connect_error}";
    exit(1);
}
$tables = ['talukas_school_data', 'work_type_master', 'work_name_master', 'work_master', 'work_stages'];
foreach ($tables as $table) {
    echo "\n--- TABLE: $table ---\n";
    $res = $conn->query("DESCRIBE `$table`");
    while ($row = $res->fetch_assoc()) {
        echo $row['Field'] . ' => ' . $row['Type'];
        if ($row['Null'] !== 'YES') echo ' NOT NULL';
        if ($row['Key'] === 'PRI') echo ' PRIMARY';
        if ($row['Default'] !== null) echo ' DEFAULT=' . $row['Default'];
        if ($row['Extra']) echo ' EXTRA=' . $row['Extra'];
        echo "\n";
    }
}
$conn->close();
?>
