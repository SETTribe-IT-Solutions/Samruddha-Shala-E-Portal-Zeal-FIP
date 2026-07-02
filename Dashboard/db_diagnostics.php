<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Diagnostics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7fb; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding-top: 50px; }
        .card { border-radius: 15px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .code-box { background-color: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 8px; font-family: monospace; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card p-5 mb-4">
                    <h3 class="text-primary mb-4 text-center">
                        <i class="fa-solid fa-screwdriver-wrench me-2"></i>Database Diagnostics
                    </h3>

                    <?php
                    $host = "82.25.121.144";
                    $username = "u196817721_S_Eportal_U";
                    $password = "Sam_shalaEportal@2026";
                    $database = "u196817721_S_shalaEportal";

                    echo "<h5>1. Testing Connection to Hostinger MySQL ($host)...</h5>";
                    
                    // Enable error reporting
                    mysqli_report(MYSQLI_REPORT_OFF);
                    $startTime = microtime(true);
                    $conn = @mysqli_connect($host, $username, $password, $database);
                    $duration = round(microtime(true) - $startTime, 2);

                    if ($conn) {
                        echo '<div class="alert alert-success mt-2 mb-4">';
                        echo '<strong><i class="fa-solid fa-circle-check me-2"></i>Success!</strong> Connected to database successfully in ' . $duration . ' seconds.';
                        echo '</div>';
                        mysqli_close($conn);
                    } else {
                        $errNo = mysqli_connect_errno();
                        $errMsg = mysqli_connect_error();
                        echo '<div class="alert alert-danger mt-2 mb-4">';
                        echo '<strong><i class="fa-solid fa-circle-exclamation me-2"></i>Connection Failed!</strong> (Took ' . $duration . ' seconds)<br>';
                        echo 'Code: ' . htmlspecialchars($errNo) . '<br>';
                        echo 'Message: ' . htmlspecialchars($errMsg);
                        echo '</div>';
                    }

                    // Get Public IP
                    echo "<h5>2. Your Public IP Address</h5>";
                    $publicIp = @file_get_contents('https://api.ipify.org');
                    if ($publicIp) {
                        echo '<div class="input-group mb-4" style="max-width: 400px;">';
                        echo '<span class="input-group-text bg-white"><i class="fa-solid fa-network-wired text-secondary"></i></span>';
                        echo '<input type="text" class="form-control fw-bold bg-white" value="' . htmlspecialchars($publicIp) . '" readonly>';
                        echo '<button class="btn btn-primary" onclick="navigator.clipboard.writeText(\'' . htmlspecialchars($publicIp) . '\'); alert(\'IP copied!\')">Copy IP</button>';
                        echo '</div>';
                    } else {
                        echo '<div class="alert alert-warning mt-2 mb-4">Could not automatically fetch public IP address. Please visit <a href="https://whatismyipaddress.com/" target="_blank">whatismyipaddress.com</a> to find it.</div>';
                    }
                    ?>

                    <hr class="my-4">

                    <h4 class="mb-3 text-secondary">How to Resolve the Connection Error:</h4>
                    
                    <div class="mb-4">
                        <h6><i class="fa-solid fa-circle-dot text-primary me-2"></i>Option A: Whitelist your IP in Hostinger hPanel</h6>
                        <p class="text-muted ps-4">
                            Hostinger blocks external database requests by default. If your public IP has changed, you must add it to the Remote MySQL whitelist:
                        </p>
                        <ol class="text-muted ps-5">
                            <li>Log in to your Hostinger hPanel.</li>
                            <li>Go to <strong>Databases</strong> &rarr; <strong>Remote MySQL</strong>.</li>
                            <li>Enter the Public IP shown above (e.g. <code><?php echo htmlspecialchars($publicIp ?: 'your-ip'); ?></code>) in the IP field (or use <code>%</code> to allow all IPs).</li>
                            <li>Select the database <code>u196817721_S_shalaEportal</code> and click <strong>Create</strong>.</li>
                        </ol>
                    </div>

                    <div class="mb-4">
                        <h6><i class="fa-solid fa-circle-dot text-primary me-2"></i>Option B: Allow XAMPP Apache in Windows Firewall</h6>
                        <p class="text-muted ps-4">
                            If connection works via command-line but fails in the browser, Windows Firewall is blocking Apache (<code>httpd.exe</code>) from making outbound requests:
                        </p>
                        <ol class="text-muted ps-5">
                            <li>Open Windows Start Menu, search for <strong>Allow an app through Windows Firewall</strong>.</li>
                            <li>Click <strong>Change settings</strong> (administrator privileges required).</li>
                            <li>Look for <strong>Apache HTTP Server</strong>.</li>
                            <li>Ensure both <strong>Private</strong> and <strong>Public</strong> checkboxes are ticked.</li>
                            <li>If Apache is not listed, click <strong>Allow another app...</strong> and add <code>C:\xampp\apache\bin\httpd.exe</code>.</li>
                            <li>Restart Apache in the XAMPP Control Panel.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
