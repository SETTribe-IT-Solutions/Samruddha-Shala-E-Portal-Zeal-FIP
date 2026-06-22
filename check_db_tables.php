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
$res = $conn->query('SHOW TABLES');
echo "TABLES:\n";
while ($row = $res->fetch_row()) {
    echo $row[0] . "\n";
}
$conn->close();
