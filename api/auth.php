<?php
session_start();
header('Content-Type: application/json');
require_once '../db/db.php';

class AuthHandler
{
    private $conn;

    public function __construct($connection)
    {
        $this->conn = $connection;
    }

    /**
     * Handle API requests based on the 'action' parameter
     */
    public function handleRequest()
    {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'register':
                $this->register();
                break;

            case 'login':
                $this->login();
                break;

            case 'logout':
                $this->logout();
                break;

            case 'get_user_info':
                $this->getUserInfo();
                break;

            case 'get_user_stats':
                $this->getUserStats();
                break;

            case 'update_user':
                $this->updateUser();
                break;

            case 'get_leaderboard':
                $this->getLeaderboard();
                break;

            default:
                $this->respondWithError('Invalid action');
        }
    }

    /**
     * Register a new user
     */
    private function register()
    {
        try {
            // Get and validate input
            $requestBody = file_get_contents('php://input');
            $data = json_decode($requestBody, true);

            if (!$data || !isset($data['id']) || !isset($data['username']) || !isset($data['email']) || !isset($data['password'])) {
                $this->respondWithError('Missing required fields');
                return;
            }

            $userId = $data['id'];
            $username = trim($data['username']);
            $email = trim($data['email']);
            $password = $data['password'];

            // Validate inputs
            if (empty($username) || strlen($username) < 3) {
                $this->respondWithError('Username must be at least 3 characters');
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->respondWithError('Invalid email address');
                return;
            }

            if (strlen($password) < 8) {
                $this->respondWithError('Password must be at least 8 characters');
                return;
            }

            // Check if username or email already exists
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $this->respondWithError('Username or email already exists');
                return;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert the new user
            $stmt = $this->conn->prepare("INSERT INTO users (id, username, email, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $userId, $username, $email, $hashedPassword);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                // Log the user in
                $_SESSION['user_id'] = $userId;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;

                $this->respondWithSuccess([
                    'message' => 'Registration successful',
                    'user_id' => $userId,
                    'username' => $username
                ]);
            } else {
                $this->respondWithError('Registration failed');
            }
        } catch (Exception $e) {
            $this->respondWithError('Registration error: ' . $e->getMessage());
        }
    }

    /**
     * Log in an existing user
     */
    private function login()
    {
        try {
            // Get and validate input
            $requestBody = file_get_contents('php://input');
            $data = json_decode($requestBody, true);

            if (!$data || !isset($data['username']) || !isset($data['password'])) {
                $this->respondWithError('Missing required fields');
                return;
            }

            $username = trim($data['username']);
            $password = $data['password'];

            // Find the user
            $stmt = $this->conn->prepare("SELECT id, username, email, password FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $this->respondWithError('Invalid username or password');
                return;
            }

            $user = $result->fetch_assoc();

            // Verify password
            if (!password_verify($password, $user['password']) && $password != $user['password']) {
                $this->respondWithError('Invalid username or password');
                return;
            }

            // Update last login time
            $stmt = $this->conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->bind_param("s", $user['id']);
            $stmt->execute();

            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];

            // Return success with user info
            $this->respondWithSuccess([
                'message' => 'Login successful',
                'user_id' => $user['id'],
                'username' => $user['username']
            ]);
        } catch (Exception $e) {
            $this->respondWithError('Login error: ' . $e->getMessage());
        }
    }

    /**
     * Update user information (username, password)
     */
    private function updateUser()
    {
        // Check if user is authenticated
        if (!isset($_SESSION['user_id'])) {
            $this->respondWithError('Not logged in', 401);
            return;
        }

        try {
            // Get the request body
            $requestBody = file_get_contents('php://input');
            $data = json_decode($requestBody, true);

            if (!$data) {
                $this->respondWithError('Invalid request data');
                return;
            }

            $userId = $_SESSION['user_id'];
            $displayName = $data['display_name'] ?? null;
            $currentPassword = $data['current_password'] ?? null;
            $newPassword = $data['new_password'] ?? null;

            // If updating display name/username
            if ($displayName) {
                // Validate the display name
                if (strlen($displayName) < 3) {
                    $this->respondWithError('Display name must be at least 3 characters long');
                    return;
                }

                // Check if the new username is already taken by another user
                $stmt = $this->conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
                $stmt->bind_param("ss", $displayName, $userId);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $this->respondWithError('Username already taken');
                    return;
                }
            }

            // Start with an update for the username if provided
            if ($displayName) {
                $stmt = $this->conn->prepare("UPDATE users SET username = ? WHERE id = ?");
                $stmt->bind_param("ss", $displayName, $userId);
                $stmt->execute();

                // Update session data
                $_SESSION['username'] = $displayName;
            }

            // If updating password
            if ($newPassword) {
                // Verify current password first
                $stmt = $this->conn->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->bind_param("s", $userId);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    $this->respondWithError('User not found');
                    return;
                }

                $user = $result->fetch_assoc();

                // Verify the current password
                if (!password_verify($currentPassword, $user['password'])) {
                    $this->respondWithError('Current password is incorrect');
                    return;
                }

                // Validate the new password
                if (strlen($newPassword) < 8) {
                    $this->respondWithError('New password must be at least 8 characters long');
                    return;
                }

                // Hash and update the new password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("ss", $hashedPassword, $userId);
                $stmt->execute();
            }

            // Update the record update timestamp
            $stmt = $this->conn->prepare("UPDATE users SET updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("s", $userId);
            $stmt->execute();

            // Get updated user info
            $stmt = $this->conn->prepare("SELECT id, username, email, created_at, last_login FROM users WHERE id = ?");
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $updatedUser = $result->fetch_assoc();

            $this->respondWithSuccess([
                'message' => 'User information updated successfully',
                'user' => $updatedUser
            ]);
        } catch (Exception $e) {
            $this->respondWithError('Error updating user information: ' . $e->getMessage());
        }
    }

    /**
     * Log out the current user
     */
    private function logout()
    {
        // Clear all session data
        $_SESSION = [];

        // If a session cookie is used, clear it
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Destroy the session
        session_destroy();

        $this->respondWithSuccess([
            'message' => 'Logout successful'
        ]);
    }

    /**
     * Get current user info
     */
    private function getUserInfo()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->respondWithError('Not logged in', 401);
            return;
        }

        $userId = $_SESSION['user_id'];

        try {
            $stmt = $this->conn->prepare("SELECT id, username, email, created_at, last_login FROM users WHERE id = ?");
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $this->respondWithError('User not found', 404);
                return;
            }

            $user = $result->fetch_assoc();

            $this->respondWithSuccess([
                'user' => $user
            ]);
        } catch (Exception $e) {
            $this->respondWithError('Error retrieving user info: ' . $e->getMessage());
        }
    }

    /**
     * Get user statistics and performance data
     */
    private function getUserStats()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->respondWithError('Not logged in', 401);
            return;
        }

        $userId = $_SESSION['user_id'];

        try {
            // Get overall stats
            $stmt = $this->conn->prepare("
                SELECT 
                    COUNT(*) as total_quizzes,
                    SUM(score) as total_score,
                    SUM(total_questions) as total_questions,
                    AVG(score * 100.0 / total_questions) as average_percentage,
                    MIN(date_taken) as first_quiz,
                    MAX(date_taken) as last_quiz
                FROM quiz_results
                WHERE user_id = ?
            ");
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $overallStats = $stmt->get_result()->fetch_assoc();

            // Get stats by category
            $stmt = $this->conn->prepare("
                SELECT 
                    category,
                    COUNT(*) as quizzes_taken,
                    AVG(score * 100.0 / total_questions) as average_percentage,
                    SUM(score) as correct_answers,
                    SUM(total_questions) as total_questions,
                    MAX(score * 100.0 / total_questions) as best_score
                FROM quiz_results
                WHERE user_id = ?
                GROUP BY category
                ORDER BY average_percentage DESC
            ");
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $categoryStats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // Get stats by difficulty
            $stmt = $this->conn->prepare("
                SELECT 
                    difficulty,
                    COUNT(*) as total_questions,
                    SUM(CASE WHEN is_correct THEN 1 ELSE 0 END) as correct_answers,
                    (SUM(CASE WHEN is_correct THEN 1 ELSE 0 END) * 100.0 / COUNT(*)) as percentage
                FROM question_answers
                WHERE user_id = ?
                GROUP BY difficulty
                ORDER BY difficulty
            ");
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $difficultyStats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // Get recent quizzes
            $stmt = $this->conn->prepare("
                SELECT 
                    quiz_results.id,
                    quiz_results.category,
                    quiz_results.score,
                    quiz_results.total_questions,
                    quiz_results.completion_time,
                    quiz_results.date_taken
                FROM quiz_results
                WHERE user_id = ?
                ORDER BY date_taken DESC
                LIMIT 5
            ");
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $recentQuizzes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            $this->respondWithSuccess([
                'overall_stats' => $overallStats,
                'category_stats' => $categoryStats,
                'difficulty_stats' => $difficultyStats,
                'recent_quizzes' => $recentQuizzes
            ]);
        } catch (Exception $e) {
            $this->respondWithError('Error retrieving stats: ' . $e->getMessage());
        }
    }

    /**
     * Get leaderboard data
     */
    private function getLeaderboard()
    {
        try {
            // Get top 10 users by average score with minimum of 1 quizzes
            $query = "
                SELECT 
                    u.username,
                    ROUND(AVG(qr.score / qr.total_questions * 100)) as avg_score,
                    COUNT(qr.id) as quizzes 
                FROM 
                    users u 
                JOIN 
                    quiz_results qr ON u.id = qr.user_id 
                GROUP BY 
                    u.id, u.username 
                HAVING 
                    COUNT(qr.id) >= 1 
                ORDER BY 
                    avg_score DESC, quizzes DESC 
                LIMIT 10
            ";

            $result = $this->conn->query($query);

            if (!$result) {
                throw new Exception("Database error: " . $this->conn->error);
            }

            $leaderboard = [];
            while ($row = $result->fetch_assoc()) {
                $leaderboard[] = $row;
            }

            $this->respondWithSuccess(['leaderboard' => $leaderboard]);
        } catch (Exception $e) {
            $this->respondWithError($e->getMessage());
        }
    }

    /**
     * Send an error response
     */
    private function respondWithError($message, $statusCode = 400)
    {
        http_response_code($statusCode);
        echo json_encode(['error' => $message]);
        exit;
    }

    /**
     * Send a success response
     */
    private function respondWithSuccess($data)
    {
        echo json_encode($data);
        exit;
    }
}

// Ensure DB connection doesn't output anything before JSON
ob_start();
include_once '../db/db.php';
ob_end_clean();

// Initialize and handle the request
try {
    $authHandler = new AuthHandler($con);
    $authHandler->handleRequest();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
