<?php
session_start();
include("dbConfig.php");

$username = isset($_POST['username']) ? $_POST['username'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Check only CEO username
$sql = "SELECT * FROM users WHERE username='CEO'";
$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) > 0)
{
    $row = mysqli_fetch_assoc($result);

    // Verify entered username and password
    if($username == 'CEO' && password_verify($password, $row['password']))
    {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = 'CEO';

        header("Location: ceo_dashboard.php");
        exit();
    }
    else
    {
        echo "<script>
                alert('Invalid Username or Password');
                window.location='login.php';
              </script>";
        exit();
    }
}
else
{
    echo "<script>
            alert('CEO account not found');
            window.location='login.php';
          </script>";
    exit();
}
?>