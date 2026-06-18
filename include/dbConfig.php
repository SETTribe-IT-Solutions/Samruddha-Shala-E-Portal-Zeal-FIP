<?php

$host = "82.25.121.144";
$username = "u196817721_S_Eportal_U";
$password = "Sam_shalaEportal@2026";
$database = "u196817721_S_shalaEportal";

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die(
        "<h3 style='color:red'>
        ❌ Database Connection Failed<br>
        Error: " . mysqli_connect_error() . "
        </h3>"
    );
}

// Set character set
mysqli_set_charset($conn, "utf8");

// Success message
echo "<h3 style='color:green'>
✅ Database Connected Successfully
</h3>";

?>