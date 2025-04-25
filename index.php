<?php
// Include auth middleware
require_once 'middleware/auth_middleware.php';

// Check if user is authenticated
$isAuthenticated = isAuthenticated();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>QuizMint | Interactive Quiz Application</title>
    <link
        rel="icon"
        type="image/svg+xml"
        href="/quizmint/assets/img/logo.svg" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <!-- Add Inter font from Google Fonts -->
    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" />
    <style>
        /* Additional styles for authenticated user header */
        .auth-header-actions {
            display: flex;
            justify-content: flex-end;
            padding: 15px 25px;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .user-welcome {
            margin-right: 15px;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <div class="quiz-container">
        <?php if ($isAuthenticated): ?>
            <!-- Show authenticated user header -->
            <div class="auth-header-actions">
                <span class="user-welcome">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <a href="dashboard.php" class="btn">My Dashboard</a>
            </div>
        <?php endif; ?>

        <div class="header">
            <h1>QuizMint</h1>
            <p class="quiz-subtitle">
                Test your knowledge with our interactive quiz
            </p>
        </div>

        <!-- Category Selection Screen -->
        <div id="category-selection">
            <h2 class="category-selection-title">Choose a Category</h2>
            <p class="category-selection-subtitle">
                Select a quiz category to begin
            </p>

            <div class="categories-grid">
                <div class="category-card" data-category="geography">
                    <div class="category-icon">🌍</div>
                    <h3>Geography</h3>
                    <p>Test your knowledge about countries, capitals, and landmarks</p>
                </div>

                <div class="category-card" data-category="science">
                    <div class="category-icon">🔬</div>
                    <h3>Science</h3>
                    <p>Questions about physics, chemistry, biology, and astronomy</p>
                </div>

                <div class="category-card" data-category="history">
                    <div class="category-icon">📜</div>
                    <h3>History</h3>
                    <p>Explore past events, civilizations, and important figures</p>
                </div>

                <div class="category-card" data-category="trivia">
                    <div class="category-icon">🎮</div>
                    <h3>Trivia</h3>
                    <p>General knowledge questions across various topics</p>
                </div>

                <div class="category-card" data-category="technology">
                    <div class="category-icon">💻</div>
                    <h3>Technology</h3>
                    <p>Questions about gadgets, innovations, and tech companies</p>
                </div>

                <div class="category-card" data-category="programming">
                    <div class="category-icon">👨‍💻</div>
                    <h3>Programming</h3>
                    <p>Web development, coding languages, and frameworks</p>
                </div>
            </div>

            <?php if (!$isAuthenticated): ?>
                <div class="auth-prompt" style="margin-top: 30px; text-align: center;">
                    <p style="margin-bottom: 15px;">Create an account to track your progress and see statistics!</p>
                    <div style="display: flex; justify-content: center; gap: 10px;">
                        <a href="login.php" class="btn">Login</a>
                        <a href="signup.php" class="btn btn-secondary">Sign Up</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div id="quiz-content" style="display: none">
            <div class="quiz-actions">
                <div class="quiz-info">
                    <span id="question-number">Question 1</span> of
                    <span id="total-questions">0</span>
                </div>
                <div class="timer" id="timer">
                    <span class="timer-icon">⏱️</span>
                    <span id="timer-value">00:00</span>
                </div>
            </div>

            <div class="progress-container">
                <div class="progress-bar" id="progress-bar" style="width: 0%"></div>
            </div>

            <div id="question-container">
                <p id="question-text"></p>
                <div id="options-container">
                    <!-- Options will be loaded here -->
                </div>
            </div>

            <div id="feedback-container" class="feedback"></div>

            <div id="navigation-container">
                <button id="submit-button" class="btn">Submit Answer</button>
                <button id="next-button" class="btn" style="display: none">
                    Next Question
                </button>
            </div>
        </div>

        <div id="result-container" style="display: none">
            <h2 class="result-title">Quiz Completed!</h2>
            <p class="result-details">
                You've completed the quiz. Here's your result:
            </p>

            <div class="score-container">
                <span id="final-score">0/0</span>
            </div>

            <div class="score-details">
                <div class="score-item">
                    <div class="score-number" id="correct-answers">0</div>
                    <div class="score-label">Correct</div>
                </div>
                <div class="score-item">
                    <div class="score-number" id="incorrect-answers">0</div>
                    <div class="score-label">Incorrect</div>
                </div>
                <div class="score-item">
                    <div class="score-number" id="completion-time">00:00</div>
                    <div class="score-label">Time</div>
                </div>
            </div>

            <div class="result-actions">
                <button id="restart-button" class="btn">Take Quiz Again</button>
                <button id="change-category-button" class="btn btn-secondary">
                    Change Category
                </button>
                <?php if ($isAuthenticated): ?>
                    <a href="dashboard.php" class="btn btn-secondary">View Dashboard</a>
                <?php else: ?>
                    <a href="signup.php" class="btn btn-secondary">Create Account to Save Results</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
</body>

</html>