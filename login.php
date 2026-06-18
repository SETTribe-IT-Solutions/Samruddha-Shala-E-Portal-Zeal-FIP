<?php
session_start();

require_once 'include/header.php';

$message = '';

if(isset($_POST['login']))
{
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users 
            WHERE username = ? 
            AND is_active = 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if($result->num_rows > 0)
    {
        $user = $result->fetch_assoc();

        if($password == $user['password'])
        {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            if($user['username'] == 'CEO')
            {
                header("Location: ceo_dashboard.php");
                exit();
            }
            elseif($user['username'] == 'Sachiv')
            {
                header("Location: sachiv_dashboard.php");
                exit();
            }
            elseif($user['username'] == 'HM')
            {
                header("Location: hm_dashboard.php");
                exit();
            }
        }
        else
        {
            $message = "Invalid Password";
        }
    }
    else
    {
        $message = "Invalid Username";
    }
}
?>

<?php include 'include/header.php'; ?>

<style>
.login-container{
    width:400px;
    margin:50px auto;
    background:#ffffff;
    padding:30px;
    border-radius:10px;
    box-shadow:0px 0px 15px rgba(0,0,0,0.15);
}

.login-container h2{
    text-align:center;
    color:#003366;
    margin-bottom:20px;
}

.logo{
    text-align:center;
    margin-bottom:20px;
}

.logo img{
    width:120px;
}

.form-group{
    margin-bottom:15px;
}

.form-group label{
    display:block;
    margin-bottom:5px;
    font-weight:bold;
}

.form-control{
    width:100%;
    padding:10px;
    border:1px solid #ccc;
    border-radius:5px;
}

.btn-login{
    width:100%;
    background:#003366;
    color:white;
    border:none;
    padding:12px;
    border-radius:5px;
    cursor:pointer;
    font-size:16px;
}

.btn-login:hover{
    background:#0055aa;
}

.error{
    color:red;
    text-align:center;
    margin-top:10px;
}
</style>

<div class="container">

    <div class="login-container">

        <div class="logo">
            <img src="images/logo.png" alt="Samruddha Shala Logo">
        </div>

        <h2>Samruddha Shala E-Portal</h2>

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
                <label>Password</label>
                <input type="password"
                       name="password"
                       class="form-control"
                       placeholder="Enter Password"
                       required>
            </div>

            <button type="submit"
                    name="login"
                    class="btn-login">
                Login
            </button>

        </form>

        <?php if(!empty($message)) { ?>
            <div class="error">
                <?php echo $message; ?>
            </div>
        <?php } ?>

    </div>

</div>

<?php include 'include/footer.php'; ?>