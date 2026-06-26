<?php
session_start();

function redirect($path)
{
    header("Location: $path");
    exit();
}

function isLoggedIn()
{
    return isset($_SESSION['role']);
}

function requireLogin()
{
    if (!isLoggedIn()) {
        redirect('../login.php');
    }
}

function requireRole($allowedRoles)
{
    requireLogin();

    $userRole = $_SESSION['role'];

    if (!in_array($userRole, $allowedRoles, true)) {
        echo "<h2 style='color:red;text-align:center;margin-top:50px'>
                Access Denied
              </h2>";
        exit();
    }
}
?>