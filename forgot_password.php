
<?php
session_start();
require_once 'include/dbConfig.php';

$message = '';

if(isset($_POST['reset']))
{
    $username         = trim($_POST['username']);
    $new_password     = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if(empty($username) || empty($new_password) || empty($confirm_password))
    {
        $message = "Please fill all fields.";
    }
    elseif($new_password != $confirm_password)
    {
        $message = "Passwords do not match.";
    }
    else
    {
        $check = $conn->prepare("SELECT id FROM users WHERE username=?");
        $check->bind_param("s",$username);
        $check->execute();
        $result = $check->get_result();

        if($result->num_rows > 0)
        {
            $update = $conn->prepare("UPDATE users SET password=? WHERE username=?");
            $update->bind_param("ss",$new_password,$username);

            if($update->execute())
            {
                echo "<script>
                        alert('Password Updated Successfully');
                        window.location='login.php';
                      </script>";
                exit();
            }
            else
            {
                $message = "Unable to update password.";
            }
        }
        else
        {
            $message = "Username not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Forgot Password - Samruddha Shala E-Portal</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<?php include 'include/landing_header.php'; ?>
<?php include 'include/website_header.php'; ?>


<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

html,
body{
    width:100%;
    height:100%;
    overflow:hidden;
    background:#f4f8ff;
}

.main-container{
    width:100%;
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:15px;
}

.forgot-panel{
    width:95%;
    max-width:1400px;
    height:90vh;
    background:#ffffff;
    border-radius:20px;
    overflow:hidden;
    display:flex;
    box-shadow:0 10px 30px rgba(0,0,0,0.10);
}

.left-section{
    flex:1.7;
    display:flex;
    justify-content:center;
    align-items:center;
    background:#ffffff;
    padding:15px;
}

.left-section img{
    width:100%;
    height:100%;
    object-fit:contain;
}

.right-section{
    flex:0.8;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:30px;
    background:linear-gradient(to bottom,#ffffff,#f7fbff);
}

.form-container{
    width:100%;
    max-width:420px;
}

.logo{
    text-align:center;
    margin-bottom:20px;
}

.logo img{
    width:90px;
    margin-bottom:10px;
}

.logo h2{
    color:#003366;
    font-size:28px;
    margin-bottom:5px;
}

.logo p{
    color:#666;
    font-size:14px;
}

.form-group{
    margin-bottom:15px;
}

.form-group label{
    display:block;
    margin-bottom:5px;
    font-weight:600;
    color:#333;
}

.form-control{
    width:100%;
    padding:12px;
    border:1px solid #d5dce5;
    border-radius:8px;
    font-size:14px;
}

.form-control:focus{
    outline:none;
    border-color:#0b63b7;
    box-shadow:0 0 5px rgba(11,99,183,0.25);
}

.btn-reset{
    width:100%;
    padding:12px;
    border:none;
    border-radius:8px;
    background:linear-gradient(90deg,#0b63b7,#053a8a);
    color:#fff;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
}

.btn-reset:hover{
    opacity:0.95;
}

.message{
    margin-top:15px;
    text-align:center;
    color:red;
}

.back-link{
    text-align:center;
    margin-top:15px;
}

.back-link a{
    text-decoration:none;
    color:#0b63b7;
    font-weight:500;
}

.back-link a:hover{
    text-decoration:underline;
}

@media(max-width:991px)
{
    body{
        overflow:auto;
    }

    .forgot-panel{
        flex-direction:column;
        height:auto;
    }

    .left-section{
        height:300px;
    }
}

</style>

</head>

<body>

<div class="main-container">

    <div class="forgot-panel">

        <div class="left-section">
            <img src="images/LoginImage_SamruddhaShala.png" alt="Login Image">
        </div>

        <div class="right-section">

            <div class="form-container">

                <div class="logo">
                    
                    <h2>Forgot Password</h2>
                    <p>Samruddha Shala E-Portal</p>
                </div>

                <form method="POST">

                    <div class="form-group">
                        <label>Username</label>
                        <input type="text"
                               name="username"
                               class="form-control"
                               placeholder="Enter Username"
                               required>
                    </div>

                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password"
                               name="new_password"
                               class="form-control"
                               placeholder="Enter New Password"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password"
                               name="confirm_password"
                               class="form-control"
                               placeholder="Confirm Password"
                               required>
                    </div>

                    <button type="submit"
                            name="reset"
                            class="btn-reset">
                        Reset Password
                    </button>

                </form>

                <?php if(!empty($message)){ ?>
                    <div class="message">
                        <?php echo $message; ?>
                    </div>
                <?php } ?>

                <div class="back-link">
                    <a href="login.php">← Back to Login</a>
                    <?php include 'include/website_footer.php'; ?>
                </div>

            </div>

        </div>

    </div>

</div>
</body>
</html>

