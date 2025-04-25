<?php
// Include auth middleware
require_once 'middleware/auth_middleware.php';

// Require authentication for this page
requireAuth();

// If we get here, the user is authenticated, so we can render the dashboard
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard | QuizMint</title>
    <link rel="icon" type="image/svg+xml" href="/quizmint/assets/logo.svg" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="stylesheet" href="assets/css/dashboard.css" />
    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" />
    <!-- Add Chart.js library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <div class="logo">
                <h1>QuizMint</h1>
            </div>
            <div class="user-menu">
                <span id="user-greeting">Welcome!</span>
                <button id="logout-button" class="btn btn-secondary">Logout</button>
            </div>
        </header>

        <div class="dashboard-content">
            <div class="sidebar">
                <div class="user-info">
                    <div class="user-avatar">
                        <span id="user-initial">U</span>
                    </div>
                    <h3 id="username-display">Username</h3>
                    <p id="email-display">user@example.com</p>
                </div>

                <nav class="dashboard-nav">
                    <ul>
                        <li class="active" data-target="overview">
                            <span class="icon">📊</span> Overview
                        </li>
                        <li data-target="performance">
                            <span class="icon">📈</span> Performance
                        </li>
                        <li data-target="history">
                            <span class="icon">📜</span> Quiz History
                        </li>
                        <li data-target="settings">
                            <span class="icon">⚙️</span> Account Settings
                        </li>
                    </ul>
                </nav>

                <div class="start-quiz">
                    <a href="index.php" class="btn">Take a New Quiz</a>
                </div>
            </div>

            <div class="main-content">
                <!-- Overview Section -->
                <section id="overview" class="dashboard-section active">
                    <h2>Dashboard Overview</h2>

                    <div class="stats-summary">
                        <div class="stat-card">
                            <div class="stat-value" id="total-quizzes">0</div>
                            <div class="stat-label">Quizzes Taken</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value" id="avg-score">0%</div>
                            <div class="stat-label">Average Score</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value" id="best-category">-</div>
                            <div class="stat-label">Strongest Category</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value" id="questions-answered">0</div>
                            <div class="stat-label">Questions Answered</div>
                        </div>
                    </div>

                    <div class="recent-activity">
                        <h3>Recent Activity</h3>
                        <div id="recent-quizzes" class="activity-list">
                            <p class="no-data">No recent quizzes found</p>
                        </div>
                    </div>

                    <div class="category-overview">
                        <h3>Performance by Category</h3>
                        <div id="category-stats" class="category-stats">
                            <p class="no-data">No category data available</p>
                        </div>
                    </div>
                </section>

                <!-- Performance Section -->
                <section id="performance" class="dashboard-section">
                    <h2>Detailed Performance</h2>

                    <div class="performance-charts">
                        <div class="chart-container">
                            <h3>Category Performance</h3>
                            <div id="category-chart" class="chart">
                                <!-- Chart will be rendered here -->
                            </div>
                        </div>

                        <div class="chart-container">
                            <h3>Difficulty Distribution</h3>
                            <div id="difficulty-chart" class="chart">
                                <!-- Chart will be rendered here -->
                            </div>
                        </div>
                    </div>

                    <div class="performance-details">
                        <h3>Performance by Difficulty</h3>
                        <div class="difficulty-stats" id="difficulty-stats">
                            <!-- Difficulty stats will be populated here -->
                        </div>
                    </div>
                </section>

                <!-- Quiz History Section -->
                <section id="history" class="dashboard-section">
                    <h2>Quiz History</h2>

                    <div class="history-filters">
                        <select id="category-filter">
                            <option value="all">All Categories</option>
                        </select>
                        <select id="sort-filter">
                            <option value="date-desc">Newest First</option>
                            <option value="date-asc">Oldest First</option>
                            <option value="score-desc">Highest Score</option>
                            <option value="score-asc">Lowest Score</option>
                        </select>
                    </div>

                    <div id="quiz-history" class="quiz-history">
                        <div class="history-header">
                            <span>Date</span>
                            <span>Category</span>
                            <span>Score</span>
                            <span>Time</span>
                        </div>
                        <div id="history-items" class="history-items">
                            <p class="no-data">No quiz history available</p>
                        </div>
                    </div>
                </section>

                <!-- Account Settings Section -->
                <section id="settings" class="dashboard-section">
                    <h2>Account Settings</h2>

                    <div class="settings-form">
                        <div class="form-group">
                            <label for="display-name">Display Name</label>
                            <input type="text" id="display-name" />
                        </div>

                        <div class="form-group">
                            <label for="current-password">Current Password</label>
                            <input type="password" id="current-password" />
                        </div>

                        <div class="form-group">
                            <label for="new-password">New Password</label>
                            <input type="password" id="new-password" />
                        </div>

                        <div class="form-group">
                            <label for="confirm-new-password">Confirm New Password</label>
                            <input type="password" id="confirm-new-password" />
                        </div>

                        <div id="settings-message" class="settings-message"></div>

                        <button id="save-settings" class="btn">Save Changes</button>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script src="assets/js/dashboard.js"></script>
    <script src="assets/js/avatar-menu.js"></script>
</body>

</html>