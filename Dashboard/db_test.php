<?php
$conn = mysqli_connect('82.25.121.144', 'u196817721_S_Eportal_U', 'Sam_shalaEportal@2026', 'u196817721_S_shalaEportal');
$result = mysqli_query($conn, 'SHOW TABLES');
while($row = mysqli_fetch_row($result)) {
    echo $row[0] . "\n";
}
?>
