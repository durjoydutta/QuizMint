<?php
// Include auth middleware
require_once 'middleware/auth_middleware.php';
// Include CORS middleware for API requests
require_once 'middleware/cors_middleware.php';

// Require authentication for this page
requireAuth();

// Now we know the user is authenticated, get user data
$username = htmlspecialchars($_SESSION['username']);
$userInitial = strtoupper(substr($username, 0, 1));
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
    <link rel="stylesheet" href="assets/css/modern-ui.css" />
    <!-- Add Inter font from Google Fonts -->
    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" />
</head>

<body>
    <div class="quiz-container">
        <!-- User Avatar and Menu -->
        <div class="user-avatar-container">
            <div id="user-avatar" class="user-avatar"><?php echo $userInitial; ?></div>
            <div id="user-menu" class="user-menu">
                <div class="user-menu-header">
                    <div class="user-menu-name"><?php echo $username; ?></div>
                    <div class="user-menu-email"><?php echo $_SESSION['email']; ?></div>
                </div>
                <div class="user-menu-items">
                    <a href="dashboard.php" class="user-menu-item">
                        <div>📊</div> <span>Dashboard</span>
                    </a>
                    <div class="user-menu-item" id="logout-button">
                        <div>🚪</div> <span>Logout</span>
                    </div>
                </div>
            </div>
        </div>

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
                <a href="dashboard.php" class="btn btn-secondary">View Dashboard</a>
            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
    <script src="assets/js/avatar-menu.js"></script>
</body>

</html>