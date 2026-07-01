<?php
$host = "82.25.121.144";
$db_user = "u196817721_S_Eportal_U";
$db_pass = "Sam_shalaEportal@2026";
$db_name = "u196817721_S_shalaEportal";
$conn = mysqli_connect($host, $db_user, $db_pass, $db_name);
$cols = [];
if ($conn) {
    $res = mysqli_query($conn, 'SHOW COLUMNS FROM talukas_school_data');
    while($row = mysqli_fetch_assoc($res)) {
        $cols[] = $row;
    }
}
file_put_contents('schema.json', json_encode($cols, JSON_PRETTY_PRINT));
echo "Done.";
?>
