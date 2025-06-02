<?php

// authentication middleware

function isAuthenticated()
{
    // start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireAuth($redirectTo = 'login.php')
{
    if (!isAuthenticated()) {
        header("Location: $redirectTo");
        exit;
    }
}

function redirectIfAuthenticated($redirectTo = 'dashboard.php')
{
    if (isAuthenticated()) {
        header("Location: $redirectTo");
        exit;
    }
}
