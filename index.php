<?php
// Include auth middleware
require_once 'middleware/auth_middleware.php';

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
    <!-- Add Inter font from Google Fonts -->
    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" />
    <style>
        /* Modern UI enhancements */
        body {
            background: linear-gradient(135deg, #f5f7fb 0%, #e4e8f0 100%);
        }

        .quiz-container {
            position: relative;
            margin: 30px auto;
            border-radius: 16px;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
        }

        .user-avatar-container {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 10;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.2em;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .user-avatar:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .user-menu {
            position: absolute;
            top: 55px;
            right: 0;
            background: white;
            border-radius: 8px;
            width: 200px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }

        .user-menu.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .user-menu-header {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .user-menu-name {
            font-weight: 600;
            color: var(--text-color);
        }

        .user-menu-email {
            font-size: 0.8em;
            color: var(--text-light);
        }

        .user-menu-items {
            padding: 10px 0;
        }

        .user-menu-item {
            padding: 10px 15px;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            align-items: center;
        }

        .user-menu-item:hover {
            background: rgba(67, 97, 238, 0.05);
        }

        .user-menu-item span {
            margin-left: 10px;
        }

        .option-button {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
        }

        .option-button:hover {
            transform: translateX(5px);
        }

        .quiz-actions {
            background: rgba(255, 255, 255, 0.8);
            padding: 15px 20px;
            border-radius: 12px;
            backdrop-filter: blur(5px);
            margin-bottom: 25px;
        }
    </style>
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
    <script>
        // Avatar dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const userAvatar = document.getElementById('user-avatar');
            const userMenu = document.getElementById('user-menu');
            const logoutButton = document.getElementById('logout-button');

            // Toggle menu on avatar click
            userAvatar.addEventListener('click', function() {
                userMenu.classList.toggle('active');
            });

            // Close menu when clicking outside
            document.addEventListener('click', function(event) {
                if (!userAvatar.contains(event.target) && !userMenu.contains(event.target)) {
                    userMenu.classList.remove('active');
                }
            });

            // Logout functionality
            logoutButton.addEventListener('click', async function() {
                try {
                    await fetch('/quizmint/api/auth.php?action=logout');
                    window.location.href = 'login.php';
                } catch (error) {
                    console.error('Logout failed:', error);
                }
            });
        });
    </script>
</body>

</html>