<?php
session_start();
require_once 'include/dbConfig.php';

$message = '';

if(isset($_POST['verify']))
{
    $email = trim($_POST['email']);

    $stmt = $conn->prepare(
        "SELECT id FROM users WHERE BINARY email = ?"
    );

    $stmt->bind_param("s", $email);
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
        $message = "Email ID not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Email Verification - Samruddha Shala E-Portal</title>

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

html,body{
    min-height:100vh;
    background:#f4f8ff;
}

body{
    display:flex;
    flex-direction:column;
}

.main-container{
    flex:1;
    width:100%;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:20px;
}

.forgot-panel{
    width:90%;
    max-width:1300px;
    background:#fff;
    border-radius:20px;
    overflow:hidden;
    display:flex;
    box-shadow:0 10px 30px rgba(0,0,0,0.10);
}

.left-section{
    flex:1.2;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:5px;
    background:#fff;
}

.left-section img{
    width:100%;
    height:auto;
    object-fit:contain;
}

.right-section{
    flex:1;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:25px;
    background:linear-gradient(to bottom,#ffffff,#f7fbff);
}

.form-container{
    width:100%;
    max-width:500px;
}

.logo{
    text-align:center;
    margin-bottom:25px;
}

.logo h2{
    color:#003366;
    font-size:42px;
    font-weight:700;
    margin-bottom:5px;
}

.logo p{
    color:#666;
    font-size:16px;
}

.form-group{
    margin-bottom:18px;
}

.form-group label{
    display:block;
    margin-bottom:8px;
    font-weight:600;
    color:#222;
    font-size:15px;
}

.form-control{
    width:100%;
    padding:14px;
    border:1px solid #d8dde6;
    border-radius:10px;
    font-size:15px;
}

.form-control:focus{
    outline:none;
    border-color:#0b63b7;
    box-shadow:0 0 5px rgba(11,99,183,0.25);
}

.btn-reset{
    width:100%;
    padding:14px;
    border:none;
    border-radius:10px;
    background:linear-gradient(90deg,#0b63b7,#053a8a);
    color:#fff;
    font-size:17px;
    font-weight:600;
    cursor:pointer;
    transition:.3s;
}

.btn-reset:hover{
    opacity:.95;
}

.message{
    margin-top:15px;
    text-align:center;
    color:red;
    font-size:14px;
}

.back-link{
    text-align:center;
    margin-top:18px;
}

.back-link a{
    color:#0b63b7;
    text-decoration:none;
    font-weight:500;
}

.back-link a:hover{
    text-decoration:underline;
}

@media(max-width:991px)
{
    .forgot-panel{
        flex-direction:column;
        width:95%;
    }

    .left-section{
        padding:10px;
    }

    .left-section img{
        max-height:300px;
    }

    .right-section{
        padding:20px;
    }

    .logo h2{
        font-size:32px;
    }
}

</style>
</head>

<body>

<div class="main-container">

    <div class="forgot-panel">

        <div class="left-section">
            <img src="images/LoginImage_SamruddhaShala1.png" alt="Samruddha Shala">
        </div>

        <div class="right-section">

            <div class="form-container">

                <div class="logo">
                    <h2>Email Verification</h2>
                    <p>Verify your registered email address to continue</p>
                </div>

                <form method="POST">

                    <div class="form-group">
                        <label>Registered Email ID / नोंदणीकृत ईमेल</label>
                        <input type="email"
                               name="email"
                               class="form-control"
                               placeholder="Enter Registered Email"
                               required>
                    </div>

                    <button type="submit"
                            name="verify"
                            class="btn-reset">
                        Verify Email
                    </button>

                </form>

                <?php if(!empty($message)) { ?>
                    <div class="message">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php } ?>

                <div class="back-link">
                    <a href="login.php">← Back to Login</a>
                </div>

            </div>

        </div>

    </div>

</div>

<?php include 'include/website_footer.php'; ?>

</body>
</html>