<?php
include 'include/dbConfig.php';
$q = mysqli_query($conn, 'SHOW COLUMNS FROM hm_work_progress');
if($q) {
    while($r = mysqli_fetch_assoc($q)){
        echo $r['Field']." (".$r['Type'].")\n";
    }
} else {
    echo "Error or table does not exist: " . mysqli_error($conn);
}
?>
