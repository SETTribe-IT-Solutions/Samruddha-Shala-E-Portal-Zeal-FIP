<?php
session_start();

require_once 'include/dbConfig.php';




$message = '';

if(isset($_POST['login']))
{
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (!empty($username) && !empty($password))
    {
        $sql = "SELECT * FROM users 
                WHERE BINARY username = ? 
                AND is_active = 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();

        if($result->num_rows > 0)
        {
            $user = $result->fetch_assoc();

            if(strcmp($password, $user['password']) === 0)
            {
                $normalizedRole = strtoupper(trim((string) ($user['role'] ?? '')));
                if ($normalizedRole === '') {
                    $normalizedRole = strtoupper(trim((string) ($user['username'] ?? '')));
                }

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $normalizedRole;
                $_SESSION['name'] = $user['name'] ?? $user['username'];

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
            }
            else
            {
                $message = "Wrong Password";
            }
        }
        else
        {
            $message = "Wrong Username";
        }
    }
    else
    {
        $message = "Please enter both username and password";
    }
}
?>

<?php include 'include/landing_header.php'; ?>
<?php include 'include/website_header.php'; ?>
<link rel="stylesheet" href="login.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');
@keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-8px); } }
@keyframes slideIn { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }
@keyframes pulse { 0%, 100% { opacity: 0.4; } 50% { opacity: 0.8; } }

html, body { height: 100%; font-family: 'Poppins', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; }
body { min-height: 100vh; margin: 0; display: flex; flex-direction: column; background: linear-gradient(135deg, #f3f8ff 0%, #e8f5ff 50%, #fff 100%); }

.site-banner-wrapper { background: linear-gradient(135deg, #fff8dc 0%, #fff2bf 55%, #ffe9a8 100%) !important; border-bottom: 2px solid #ecd28a !important; box-shadow: 0 2px 10px rgba(168, 124, 23, 0.14) !important; }

.login-main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 50px 20px; }

.animated-bg { position: fixed; inset: 0; z-index: 0; pointer-events: none; }
.animated-bg::before { content: ''; position: absolute; width: 300px; height: 300px; background: radial-gradient(circle, rgba(11,99,183,0.08), transparent); border-radius: 50%; top: 10%; left: 5%; animation: pulse 6s infinite; }
.animated-bg::after { content: ''; position: absolute; width: 200px; height: 200px; background: radial-gradient(circle, rgba(5,58,138,0.06), transparent); border-radius: 50%; bottom: 15%; right: 10%; animation: pulse 8s infinite 1s; }

.login-panel { width: 100%; max-width: 1150px; display: flex; background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 25px 60px rgba(13, 71, 161, 0.12); position: relative; z-index: 1; animation: slideIn 0.6s ease-out; }

.login-left { flex: 1.1; background: url('images/LoginImage_SamruddhaShala.png') center center / cover no-repeat, linear-gradient(180deg, #e8f2ff, #f0faff); min-height: 600px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px; position: relative; overflow: hidden; }
.login-left::before { content: ''; position: absolute; width: 150px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 40%; top: 10%; left: 10%; animation: float 6s ease-in-out infinite; }
.login-left::after { content: ''; position: absolute; width: 100px; height: 200px; background: rgba(255,255,255,0.08); border-radius: 40%; bottom: 10%; right: 10%; animation: float 5s ease-in-out infinite 1s; }

.login-right { flex: 0.9; background: linear-gradient(180deg, #ffffff, #fafbff); padding: 52px 48px; display: flex; flex-direction: column; justify-content: center; position: relative; }
.login-right::before { content: ''; position: absolute; top: 0; right: 0; width: 200px; height: 200px; background: radial-gradient(circle, rgba(11,99,183,0.04), transparent); border-radius: 50%; }

.small-illustration { position: absolute; right: 28px; top: 24px; width: 100px; height: auto; border-radius: 12px; box-shadow: 0 12px 28px rgba(11,99,183,0.15); transform: rotate(-8deg) scale(1); transition: transform 0.3s; animation: float 5s ease-in-out infinite; }
.small-illustration:hover { transform: rotate(-5deg) scale(1.05); }

.brand { display: flex; align-items: center; gap: 14px; margin-bottom: 24px; animation: slideIn 0.7s ease-out; }
.brand img { width: 60px; height: 60px; border-radius: 10px; box-shadow: 0 4px 12px rgba(11,99,183,0.2); }
.brand h2 { margin: 0; font-size: 22px; color: #0b3a66; font-weight: 700; letter-spacing: -0.5px; }

.card { background: transparent; border-radius: 8px; padding: 0; position: relative; z-index: 2; }

.form-group { margin-bottom: 16px; animation: slideIn 0.8s ease-out; }
.form-group label { display: block; margin-bottom: 7px; font-weight: 600; color: #1f2937; font-size: 14px; letter-spacing: 0.3px; }
.form-control { width: 100%; padding: 13px 16px 13px 44px; border: 1.5px solid #e5e9f0; border-radius: 11px; font-size: 14px; background: #fbfdff; box-shadow: 0 2px 8px rgba(11,99,183,0.04); transition: all 0.3s; }
.form-control:focus { outline: none; border-color: #0b63b7; box-shadow: 0 4px 16px rgba(11,99,183,0.12); background: white; }

.form-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #6b7280; font-size: 16px; pointer-events: none; }

.input-with-icon { position: relative; }
.input-with-icon .toggle-password { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: transparent; border: none; cursor: pointer; color: #6b7280; font-size: 18px; transition: color 0.3s; }
.input-with-icon .toggle-password:hover { color: #0b63b7; }

.controls { display: flex; align-items: center; justify-content: space-between; margin: 14px 0 18px; animation: slideIn 0.9s ease-out; }
.controls .remember { display: flex; align-items: center; gap: 8px; color: #4b5563; font-size: 13px; }
.controls .remember input { cursor: pointer; }
.controls a { color: #0b63b7; text-decoration: none; font-size: 13px; font-weight: 500; transition: color 0.3s; }
.controls a:hover { color: #053a8a; }

.social-row { display: flex; gap: 12px; margin: 16px 0 8px; animation: slideIn 1s ease-out; }
.social { flex: 1; text-align: center; padding: 11px 8px; border-radius: 10px; background: linear-gradient(135deg, rgba(11,99,183,0.08), rgba(5,58,138,0.04)); color: #06396b; font-weight: 600; font-size: 12px; cursor: pointer; transition: all 0.3s; border: 1px solid rgba(11,99,183,0.1); }
.social:hover { background: linear-gradient(135deg, rgba(11,99,183,0.12), rgba(5,58,138,0.08)); transform: translateY(-2px); box-shadow: 0 6px 16px rgba(11,99,183,0.12); }

.btn-login{
    width:100%;
    display:flex;
    justify-content:center;
    align-items:center;

background: linear-gradient(90deg, #0b63b7, #053a8a); color: white; border: none; padding: 13px 16px; border-radius: 11px; cursor: pointer; font-size: 16px; font-weight: 600; box-shadow: 0 8px 24px rgba(11,99,183,0.2); transition: all 0.3s; animation: slideIn 1.1s ease-out; }
.btn-login:hover { opacity: 0.95; align-items: center; transform: translateY(-2px); box-shadow: 0 12px 32px rgba(11,99,183,0.26); }
.btn-login:active { transform: translateY(0); }

.login-title { font-size: 26px; color: #07325a; margin-bottom: 8px; font-weight: 700; letter-spacing: -0.5px; animation: slideIn 0.6s ease-out; }
.login-sub { color: #6b7280; align-items: center; margin-bottom: 20px; font-size: 14px; animation: slideIn 0.7s ease-out; }

.error { color: #cc1f1f; text-align: center; margin-top: 12px; font-size: 13px; animation: slideIn 0.4s ease-out; }

@media (max-width: 900px) {
    .login-panel { flex-direction: column; max-width: 900px; box-shadow: 0 35px 70px rgba(13, 71, 161, 0.1); }
    .login-left { min-height: 400px; }
    .login-right { padding: 32px 38px; }
    .brand { margin-bottom: 10px; }
    .small-illustration { width: 80px; right: 20px; top: 20px; }
}

</style>

<div class="login-main">
    <div class="animated-bg" aria-hidden="true"></div>
    <div class="container">
        <div class="login-panel">
            <div class="login-left" aria-hidden="true"></div>

            <div class="login-right">
              
                <div class="brand">
                    
                    <div>
                        <h1 align="center" bold>Samruddha Shala</h1>
                        <div style="color:#6b7280; font-size:13px;">E-Portal System</div>
                    </div>
                </div>

                <div class="card">
                    <div class="login-title">लॉगिन / Welcome</div>
                    <div class="login-sub">Sign in to continue to the E-Portal</div>

                    <form method="POST">
                        <div class="form-group">
                            <label>Username / उपयोगकर्ता नाव</label>
                            <div class="input-with-icon">
                                <span class="form-icon">👤</span>
                                <input type="text" name="username" class="form-control" placeholder="Enter username" required>
                            </div>
                        </div>
                        <div class="form-group">
    <label>Password / पासवर्ड</label>

    <div class="input-with-icon">
        <span class="form-icon">🔒</span>

        <input type="password"
               id="passwordField"
               name="password"
               class="form-control"
               placeholder="Enter password"
               required>

        <button type="button"
                class="toggle-password"
                onclick="togglePassword()">👁️</button>
    </div>
</div>

                       

                        <div class="controls">
                            <label class="remember"><input type="checkbox" name="remember"> Remember me</label>
                           <a href="verify_email.php">Forgot Password?</a>
                        </div>

                        <button type="submit" name="login" class="btn-login">Login</button>

                        <div class="social-row">
                          
                        </div>
                         <div class="back-link" align="center">
                    <a href="index.php">← Back to Home</a>
                </div>

                    </form>

                    <?php if(!empty($message)) { ?>
                        <script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Authentication Failed',
                            text: '<?php echo htmlspecialchars($message); ?>',
                            confirmButtonColor: '#0b63b7'
                        });
                        </script>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'include/website_footer.php'; ?>

</body>
</html>

<script>
function togglePassword(){
    var f = document.getElementById('passwordField');
    if(!f) return;
    if(f.type === 'password'){
        f.type = 'text';
    } else {
        f.type = 'password';
    }
}

// small enter-key submit enhancement
document.addEventListener('DOMContentLoaded', function(){
    var form = document.querySelector('form[method="POST"]');
    if(form){
        form.addEventListener('submit', function(){
            // could add client-side validation here
        });
    }
});
</script>

