<?php
$host = "82.25.121.144";
$db_user = "u196817721_S_Eportal_U";
$db_pass = "Sam_shalaEportal@2026";
$db_name = "u196817721_S_shalaEportal";
$conn = mysqli_connect($host, $db_user, $db_pass, $db_name);
$tables = [];
if ($conn) {
    $res = mysqli_query($conn, 'SHOW TABLES');
    while($row = mysqli_fetch_row($res)) {
        $tables[] = $row[0];
    }
}
file_put_contents('tables.json', json_encode($tables, JSON_PRETTY_PRINT));
echo "Done.";
?>
