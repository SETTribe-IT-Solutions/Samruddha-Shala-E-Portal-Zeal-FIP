<?php
session_start();

$users = [
    'ceo' => [
        'email' => 'ceo@samruddhashala.com',
        'password' => 'ceo123',
        'role' => 'CEO',
        'name' => 'Chief Executive Officer'
    ],
    'sachiv' => [
        'email' => 'sachiv@samruddhashala.com',
        'password' => 'sachiv123',
        'role' => 'Sachiv',
        'name' => 'Sachiv'
    ],
    'hm' => [
        'email' => 'hm@samruddhashala.com',
        'password' => 'hm123',
        'role' => 'HM',
        'name' => 'Head Master'
    ]
];

function redirect($path)
{
    header('Location: ' . $path);
    exit;
}

function isLoggedIn()
{
    return isset($_SESSION['user']);
}

function requireLogin()
{
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

function requireRole($allowedRoles)
{
    requireLogin();
    $userRole = $_SESSION['user']['role'] ?? '';
    
    $normalizedAllowed = [];
    foreach ($allowedRoles as $role) {
        $normalizedAllowed[] = $role;
        if ($role === 'HM') {
            $normalizedAllowed[] = 'Head Master';
        } elseif ($role === 'Head Master') {
            $normalizedAllowed[] = 'HM';
        }
    }
    
    if (!in_array($userRole, $normalizedAllowed, true)) {
        http_response_code(403);
        echo 'Access denied';
        exit;
    }
}
?>
