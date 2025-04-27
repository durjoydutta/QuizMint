<?php

/**
 * Authentication Middleware
 * This file provides functions to check authentication status and protect routes
 */

/**
 * Check if a user is authenticated
 * @return bool True if user is authenticated, false otherwise
 */
function isAuthenticated()
{
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user_id exists in session
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Require authentication for the current page
 * Redirects to login page if user is not authenticated
 * @param string $redirectTo Path to redirect to if not authenticated
 */
function requireAuth($redirectTo = 'login.php')
{
    if (!isAuthenticated()) {
        header("Location: $redirectTo");
        exit;
    }
}

/**
 * Redirect if user is already authenticated
 * Used for login/signup pages - redirects to dashboard if already logged in
 * @param string $redirectTo Path to redirect to if authenticated
 */
function redirectIfAuthenticated($redirectTo = 'dashboard.php')
{
    if (isAuthenticated()) {
        header("Location: $redirectTo");
        exit;
    }
}
