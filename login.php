<?php
session_start();

$message = "";

if(isset($_POST['login']))
{
    $mobile   = $_POST['mobile'];
    $password = $_POST['password'];

    // Demo Login
    if($mobile == "9876543210" && $password == "admin123")
    {
        $_SESSION['mobile'] = $mobile;
        header("Location: dashboard.php");
        exit();
    }
    else
    {
        $message = "Invalid Mobile Number or Password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Samruddha Shala E-Portal</title>

<style>
body{
    margin:0;
    padding:0;
    font-family:Arial, sans-serif;
    background:#eef2f7;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

.login-box{
    width:420px;
    background:#ffffff;
    border-radius:12px;
    padding:30px;
    box-shadow:0 0 20px rgba(0,0,0,0.15);
    text-align:center;
}

.logo{
    width:150px;
    height:auto;
    margin-bottom:15px;
}

.portal-title{
    color:#003366;
    font-size:24px;
    font-weight:bold;
    margin-bottom:5px;
}

.portal-subtitle{
    color:#666;
    margin-bottom:25px;
}

input{
    width:100%;
    padding:12px;
    margin-top:12px;
    border:1px solid #ccc;
    border-radius:6px;
    font-size:15px;
    box-sizing:border-box;
}

.login-btn{
    width:100%;
    padding:12px;
    background:#0d6efd;
    color:white;
    border:none;
    border-radius:6px;
    margin-top:18px;
    cursor:pointer;
    font-size:16px;
    font-weight:bold;
}

.login-btn:hover{
    background:#084298;
}

.error{
    color:red;
    margin-top:15px;
}

.footer{
    margin-top:20px;
    font-size:12px;
    color:#777;
}
</style>

</head>

<body>

<div class="login-box">

    <!-- Logo -->
    <img src="loginlogo.png" alt="Samruddha Shala Logo" class="logo">

    <div class="portal-title">
        Samruddha Shala E-Portal
    </div>

    <div class="portal-subtitle">
        Zilla Parishad School Construction Monitoring System
    </div>

    <form method="POST">

        <input
            type="text"
            name="mobile"
            maxlength="10"
            placeholder="Enter Mobile Number"
            required>

        <input
            type="password"
            name="password"
            placeholder="Enter Password"
            required>

        <button type="submit" name="login" class="login-btn">
            LOGIN
        </button>

    </form>

    <?php if(!empty($message)) { ?>
        <div class="error">
            <?php echo $message; ?>
        </div>
    <?php } ?>

    <div class="footer">
        Government of Maharashtra
    </div>

</div>

</body>
</html>