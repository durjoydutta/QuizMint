<?php
// Include auth middleware
require_once 'middleware/auth_middleware.php';

// If user is already authenticated, redirect to index.php (quiz interface)
if (isAuthenticated()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>QuizMint | Test Your Knowledge</title>
    <link rel="icon" type="image/svg+xml" href="/quizmint/assets/img/logo.svg" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="stylesheet" href="assets/css/landing.css" />
    <!-- Add Inter font from Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" />
</head>

<body class="landing-page">
    <div class="landing-container">
        <div class="hero-section">
            <div class="hero-content">
                <h1 class="hero-title">Test Your Knowledge with QuizMint</h1>
                <p class="hero-subtitle">
                    Challenge yourself with our fun, interactive quizzes across various categories.
                    Track your progress, improve your score, and compete with others!
                </p>
                <div class="cta-buttons">
                    <a href="signup.php" class="cta-button primary">Sign Up Free</a>
                    <a href="login.php" class="cta-button secondary">Login</a>
                </div>
            </div>
            <div class="hero-image">
                <img src="https://placehold.co/600x400/4361ee/ffffff?text=QuizMint&font=montserrat" alt="QuizMint Interface Preview">
            </div>
        </div>

        <div class="features-section">
            <div class="feature-card">
                <div class="feature-icon">🌟</div>
                <h3 class="feature-title">Various Categories</h3>
                <p class="feature-description">
                    From Geography to Programming, Science to History - we have quizzes for every interest.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3 class="feature-title">Track Progress</h3>
                <p class="feature-description">
                    Monitor your improvement with detailed statistics and visualizations.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🏆</div>
                <h3 class="feature-title">Compete & Learn</h3>
                <p class="feature-description">
                    Challenge yourself to beat your personal best and expand your knowledge.
                </p>
            </div>
        </div>

        <div class="footer">
            <p>&copy; 2025 QuizMint - Interactive Quiz Application</p>
        </div>
    </div>
</body>

</html>