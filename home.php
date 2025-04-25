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
    <!-- Add Inter font from Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" />
    <style>
        /* Landing page specific styles */
        body {
            background: linear-gradient(135deg, #f5f7fb 0%, #e4e8f0 100%);
            min-height: 100vh;
        }

        .landing-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .hero-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 60px 0;
        }

        .hero-content {
            flex: 1;
            padding-right: 40px;
        }

        .hero-image {
            flex: 1;
            text-align: center;
        }

        .hero-image img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            color: var(--primary-color);
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: var(--text-light);
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .cta-buttons {
            display: flex;
            gap: 15px;
            margin-top: 40px;
        }

        .cta-button {
            display: inline-block;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .cta-button.primary {
            background-color: var(--primary-color);
            color: white;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.25);
        }

        .cta-button.primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(67, 97, 238, 0.35);
        }

        .cta-button.secondary {
            background-color: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .cta-button.secondary:hover {
            background-color: rgba(67, 97, 238, 0.05);
            transform: translateY(-2px);
        }

        .features-section {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            padding: 60px 0;
        }

        .feature-card {
            background-color: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
        }

        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 20px;
            display: inline-block;
            padding: 15px;
            background: rgba(67, 97, 238, 0.1);
            border-radius: 50%;
        }

        .feature-title {
            font-size: 1.4rem;
            margin-bottom: 15px;
            color: var(--text-color);
            font-weight: 600;
        }

        .feature-description {
            color: var(--text-light);
            line-height: 1.5;
        }

        .footer {
            text-align: center;
            padding: 40px 0;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .hero-section {
                flex-direction: column;
                text-align: center;
            }

            .hero-content {
                padding-right: 0;
                margin-bottom: 40px;
            }

            .features-section {
                grid-template-columns: 1fr;
            }

            .cta-buttons {
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <div class="landing-container">
        <div class="hero-section">
            <div class="hero-content">
                <h1>Test Your Knowledge with QuizMint</h1>
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