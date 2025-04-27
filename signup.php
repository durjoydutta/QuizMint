<?php
// Include auth middleware
require_once 'middleware/auth_middleware.php';
// Include CORS middleware for API requests
require_once 'middleware/cors_middleware.php';

// Redirect to dashboard if already authenticated
redirectIfAuthenticated();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign Up | QuizMint</title>
    <link rel="icon" type="image/svg+xml" href="/assets/logo.svg" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="stylesheet" href="assets/css/auth.css" />
    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" />
</head>

<body>
    <div class="auth-container">
        <div class="header">
            <h1>QuizMint</h1>
            <p class="quiz-subtitle">Create your account</p>
        </div>

        <div id="auth-form" class="auth-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    placeholder="Choose a username"
                    required />
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    placeholder="Enter your email"
                    required />
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    placeholder="Create a password"
                    required />
            </div>

            <div class="form-group">
                <label for="confirm-password">Confirm Password</label>
                <input
                    type="password"
                    id="confirm-password"
                    placeholder="Confirm your password"
                    required />
            </div>

            <div id="auth-message" class="auth-message"></div>

            <button id="signup-button" class="btn">Sign Up</button>

            <div class="auth-links">
                <p>Already have an account? <a href="login.php">Log in</a></p>
            </div>
        </div>

        <div class="auth-footer">
            <a href="home.php">Back to Home</a>
        </div>
    </div>

    <script src="assets/js/auth.js"></script>
</body>

</html>