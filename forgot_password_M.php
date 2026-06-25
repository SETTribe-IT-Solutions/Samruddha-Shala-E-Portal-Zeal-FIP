<?php
session_start();
require_once 'include/dbConfig.php';

if(!isset($_SESSION['verified_email']))
{
    header("Location: login.php");
    exit();
}

$message = '';
$email = $_SESSION['verified_email'];

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
    elseif(strlen($new_password) < 6 || !preg_match('/[0-9]/', $new_password) || !preg_match('/[^A-Za-z0-9]/', $new_password))
    {
        $message = "Password must be at least 6 characters long, and contain at least one digit and one special symbol.";
    }
    else
    {
        $check = $conn->prepare(
            "SELECT id
             FROM users
             WHERE BINARY username = ?
             AND BINARY email = ?"
        );

        $check->bind_param("ss", $username, $email);
        $check->execute();

        $result = $check->get_result();

        if($result->num_rows > 0)
        {
            $update = $conn->prepare(
                "UPDATE users
                 SET password = ?
                 WHERE BINARY username = ?
                 AND BINARY email = ?"
            );

            $update->bind_param(
                "sss",
                $new_password,
                $username,
                $email
            );

            if($update->execute())
            {
                unset($_SESSION['verified_email']);
                $success = true;
            }
            else
            {
                $message = "Unable to update password.";
            }
        }
        else
        {
            $message = "Username and Email do not match.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password</title>

<?php include 'include/landing_header.php'; ?>
<?php include 'include/website_header.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>

body{
    font-family: Arial, sans-serif;
    background:#f4f8ff;
    margin:0;
    padding:0;
}

.container{
    width:100%;
    min-height:80vh;
    display:flex;
    justify-content:center;
    align-items:center;
}

.card{
    width:500px;
    background:#fff;
    padding:30px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
}

h2{
    text-align:center;
    color:#003366;
}

.form-group{
    margin-bottom:15px;
}

label{
    display:block;
    margin-bottom:5px;
    font-weight:bold;
}

input{
    width:100%;
    padding:10px;
    border:1px solid #ccc;
    border-radius:5px;
}

.btn{
    width:100%;
    padding:12px;
    background:#0b63b7;
    color:white;
    border:none;
    border-radius:5px;
    cursor:pointer;
}

.btn:hover{
    background:#084d8f;
}

.message{
    text-align:center;
    color:red;
    margin-top:10px;
}

.back{
    text-align:center;
    margin-top:15px;
}

.back a{
    color:#0b63b7;
    text-decoration:none;
}

</style>

</head>
<body>

<div class="container">

    <div class="card">

        <h2>Forgot Password</h2>

        <p style="text-align:center;">
            Verified Email:
            <strong><?php echo htmlspecialchars($email); ?></strong>
        </p>

        <form method="POST">

            <div class="form-group">
                <label>Username</label>
                <input type="text"
                       name="username"
                       required>
            </div>

            <div class="form-group">
                <label>New Password</label>
                <input type="password"
                       name="new_password"
                       required>
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password"
                       name="confirm_password"
                       required>
            </div>

            <button type="submit"
                    name="reset"
                    class="btn">
                Reset Password
            </button>

        </form>

        <?php if(!empty($message)) { ?>
            <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo htmlspecialchars($message); ?>',
                confirmButtonColor: '#0b63b7'
            });
            </script>
        <?php } ?>

        <?php if(isset($success) && $success) { ?>
            <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'password changed successfully',
                confirmButtonColor: '#0b63b7'
            }).then(() => {
                window.location = 'login.php';
            });
            </script>
        <?php } ?>

        <div class="back">
            <a href="login.php">← Back to Login</a>
        </div>

    </div>

</div>

<?php include 'include/website_footer.php'; ?>

</body>
</html>