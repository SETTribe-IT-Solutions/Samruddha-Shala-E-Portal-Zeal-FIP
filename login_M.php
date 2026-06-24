<?php
session_start();
require_once 'include/dbConfig.php';

$message = '';

/* LOGIN */
if(isset($_POST['login']))
{
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if(!empty($username) && !empty($password))
    {
        $stmt = $conn->prepare(
            "SELECT * FROM users
             WHERE BINARY username = ?
             AND is_active = 1"
        );

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();

        if($result->num_rows > 0)
        {
            $user = $result->fetch_assoc();

            if(strcmp($password, $user['password']) === 0)
            {
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = strtoupper($user['username']);

                if($_SESSION['role'] == 'CEO')
                {
                    header("Location: Dashboard/ceo_dashboard.php");
                    exit();
                }
                elseif($_SESSION['role'] == 'SACHIV')
                {
                    header("Location: Dashboard/sachiv_dashboard.php");
                    exit();
                }
                elseif($_SESSION['role'] == 'HM')
                {
                    header("Location: Dashboard/hm_dashboard.php");
                    exit();
                }
                else
                {
                    $message = "Invalid Role.";
                }
            }
            else
            {
                $message = "Invalid Password. Password is case-sensitive.";
            }
        }
        else
        {
            $message = "Invalid Username. Username is case-sensitive.";
        }
    }
}

/* EMAIL VERIFICATION */
if(isset($_POST['verify_email']))
{
    $email = trim($_POST['email']);

    $stmt = $conn->prepare(
        "SELECT id FROM users WHERE BINARY email=?"
    );

    $stmt->bind_param("s",$email);
    $stmt->execute();

    $result = $stmt->get_result();

    if($result->num_rows > 0)
    {
        $_SESSION['verified_email'] = $email;

        header("Location: forgot_password.php");
        exit();
    }
    else
    {
        $message = "Invalid Email ID.";
    }
}
?>

<!DOCTYPE html>
<?php include 'include/landing_header.php'; ?>
<?php include 'include/website_header.php'; ?>
<html>
<head>
<meta charset="utf-8">
<title>Samruddha Shala Login</title>

<style>

body{
margin:0;
font-family:Arial,sans-serif;
background:#f4f8ff;
}

.container{
display:flex;
justify-content:center;
align-items:center;
height:100vh;
}

.login-box{
width:450px;
background:#fff;
padding:30px;
border-radius:12px;
box-shadow:0 0 15px rgba(0,0,0,0.15);
}

h2{
text-align:center;
color:#003366;
}

input{
width:100%;
padding:12px;
margin-top:8px;
margin-bottom:15px;
border:1px solid #ccc;
border-radius:6px;
box-sizing:border-box;
}

button{
width:100%;
padding:12px;
border:none;
background:#0b63b7;
color:#fff;
font-size:16px;
border-radius:6px;
cursor:pointer;
}

button:hover{
background:#084f94;
}

.message{
color:red;
text-align:center;
margin-top:10px;
}

.forgot{
text-align:right;
margin-bottom:15px;
}

.forgot a{
text-decoration:none;
color:#0b63b7;
}

.popup{
position:fixed;
top:0;
left:0;
width:100%;
height:100%;
background:rgba(0,0,0,0.5);
display:none;
justify-content:center;
align-items:center;
}

.popup:target{
display:flex;
}

.popup-box{
background:#fff;
padding:25px;
width:350px;
border-radius:10px;
}

.close{
display:block;
text-align:center;
margin-top:10px;
text-decoration:none;
color:red;
}

</style>

</head>

<body>

<div class="container">

<div class="login-box">

<h2>Samruddha Shala E-Portal</h2>

<form method="post">

<label>Username</label> <input type="text" name="username" required>

<label>Password</label> <input type="password" name="password" required>

<div class="forgot">
<a href="#forgotpopup">Forgot Password?</a>
</div>

<button type="submit" name="login">Login</button>

</form>

<?php if(!empty($message)){ ?>

<div class="message"><?php echo $message; ?></div>
<?php } ?>

</div>

</div>

<div id="forgotpopup" class="popup">

<div class="popup-box">

<h3>Email Verification</h3>

<form method="post">

<input
type="email"
name="email"
placeholder="Enter Registered Email"
required>

<button
type="submit"
name="verify_email">
Verify Email </button>

</form>

<a href="#" class="close">Close</a>

</div>

</div>
<?php include 'include/website_footer.php'; ?>
</body>
</html>


