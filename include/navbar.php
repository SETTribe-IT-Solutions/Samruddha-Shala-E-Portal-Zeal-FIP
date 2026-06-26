<?php if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); } ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
            <img src="images/demo.jpg"
                 alt="ZP Kolhapur Logo"
                 class="me-2"
                 style="height: 40px; width: auto;">
            <span data-en="Samruddha Shala E-Portal" data-mr="समृद्ध शाळा ई-पोर्टल">Samruddha Shala E-Portal</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="navbar-nav mx-auto align-items-center nav-center-links">
                <a class="nav-link" href="index.php" data-en="Home" data-mr="मुख्यपृष्ठ">मुख्यपृष्ठ</a>
                <a class="nav-link" href="#about" data-en="School Info" data-mr="शाळा माहिती">शाळा माहिती</a>
                <a class="nav-link" href="#features" data-en="Reports" data-mr="अहवाल">अहवाल</a>
                <a class="nav-link" href="#contact" data-en="Contact" data-mr="संपर्क">संपर्क</a>
            </div>
            <div class="d-flex align-items-center gap-2 ms-auto nav-right-actions">
                <div class="language-switcher">
                    <button class="lang-btn" data-lang="mr">मराठी</button>
                    <span class="lang-separator">|</span>
                    <button class="lang-btn active" data-lang="en">English</button>
                </div>
                <?php if(!empty($_SESSION['username'])): ?>
                    <a class="btn btn-outline-light btn-sm" href="logout.php" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
                <?php else: ?>
                    <a class="btn btn-warning btn-sm nav-login-btn" href="login.php" data-en="Login" data-mr="लॉगिन">लॉगिन</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
