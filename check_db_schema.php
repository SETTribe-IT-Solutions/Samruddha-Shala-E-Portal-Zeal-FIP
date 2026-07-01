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

$res = $conn->query("SELECT * FROM talukas_school_data LIMIT 5");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
$conn->close();
?>
