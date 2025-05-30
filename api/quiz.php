<?php
session_start();
// Include CORS middleware
require_once '../middleware/cors_middleware.php';
header('Content-Type: application/json');

require_once '../db/db.php';

/**
 * QuizMint - Quiz Application Backend
 * 
 * This file handles all server-side operations for the QuizMint application
 * including loading quiz data, handling question delivery, processing answers,
 * and managing quiz state through sessions.
 */

class QuizHandler
{
    private $questions = [];
    private $totalQuestions = 0;
    private $quizMetadata = [];
    private $categories = [];
    private $availableCategories = [
        'geography',
        'science',
        'history',
        'trivia',
        'technology',
        'programming'
    ];

    /**
     * Initialize the quiz handler by loading questions from XML
     */
    public function __construct()
    {
        $this->initializeSession();

        // Load category-specific XML file if a category is selected
        if (isset($_SESSION['selected_category']) && in_array($_SESSION['selected_category'], $this->availableCategories)) {
            $this->loadDataFromXML("../data/{$_SESSION['selected_category']}.xml");
        } else {
            // throw error if no valid category is selected
            $this->respondWithError('No valid category selected. Please set a category first.');
            return;
        }
    }

    /**
     * Load all data from the specified XML file
     * 
     * @param string $xmlFile The XML file to load
     */
    private function loadDataFromXML($xmlFile)
    {
        try {
            $xml = simplexml_load_file($xmlFile);
            if ($xml === false) {
                throw new Exception("Error: Cannot load quiz data XML from $xmlFile");
            }

            // Load metadata
            if (isset($xml->metadata)) {
                $this->quizMetadata = [
                    'title' => (string)$xml->metadata->title,
                    'description' => (string)$xml->metadata->description,
                    'author' => (string)$xml->metadata->author,
                    'created' => (string)$xml->metadata->created
                ];
            }

            // Load categories
            if (isset($xml->categories)) {
                foreach ($xml->categories->category as $category) {
                    $id = (string)$category['id'];
                    $name = (string)$category;
                    $this->categories[$id] = $name;
                }
            }

            // Load questions
            foreach ($xml->question as $questionNode) {
                $options = [];
                foreach ($questionNode->options->option as $option) {
                    $options[] = (string)$option;
                }

                $this->questions[] = [
                    'text' => (string)$questionNode->text,
                    'options' => $options,
                    'answer' => (string)$questionNode->answer,
                    'difficulty' => (string)$questionNode->difficulty ?: 'medium',
                    'category' => (string)$questionNode->category ?: 'general',
                    'feedback' => (string)$questionNode->feedback ?: ''
                ];
            }

            $this->totalQuestions = count($this->questions);
        } catch (Exception $e) {
            $this->respondWithError($e->getMessage());
        }
    }

    /**
     * Initialize session variables if not already set
     */
    private function initializeSession()
    {
        if (!isset($_SESSION['current_question_index']) || !isset($_SESSION['selected_category'])) {
            $this->restartQuiz();
        }
    }

    /**
     * Handle API requests based on the 'action' parameter
     */
    public function handleRequest()
    {
        $action = $_GET['action'] ?? '';

        // Special actions that don't require category selection
        switch ($action) {
            case 'restart':
                $this->restartQuiz();
                $this->respondWithSuccess(['status' => 'Quiz restarted']);
                break;

            case 'get_available_categories':
                $this->getAvailableCategories();
                break;
        }

        // Actions that require a category
        if (isset($_SESSION['selected_category']) && in_array($_SESSION['selected_category'], $this->availableCategories)) {
            switch ($action) {
                case 'get_question':
                    $this->getQuestion();
                    break;

                case 'submit_answer':
                    $this->submitAnswer();
                    break;

                case 'get_stats':
                    $this->getQuizStats();
                    break;

                case 'get_quiz_info':
                    $this->getQuizInfo();
                    break;

                case 'set_category':
                    $this->setCategory();
                    break;

                default:
                    $this->respondWithError('Invalid action');
            }
        } else {
            // Only respond with error for actions that need a category
            if ($action !== 'get_available_categories' && $action !== 'set_category') {
                $this->respondWithError('No valid category selected. Please set a category first.');
            }
        }
    }

    /**
     * Get all available categories for selection
     */
    private function getAvailableCategories()
    {
        $categoryDetails = [];

        foreach ($this->availableCategories as $categoryId) {
            try {
                // Try to load metadata from each category file
                $xml = simplexml_load_file('../data/' . $categoryId . '.xml');
                if ($xml !== false && isset($xml->metadata)) {
                    $categoryDetails[] = [
                        'id' => $categoryId,
                        'title' => (string)$xml->metadata->title,
                        'description' => (string)$xml->metadata->description
                    ];
                }
            } catch (Exception $e) {
                // Skip if file doesn't exist or has invalid format
                continue;
            }
        }

        $this->respondWithSuccess([
            'categories' => $categoryDetails
        ]);
    }

    /**
     * Set the selected category
     */
    private function setCategory()
    {
        $requestBody = file_get_contents('php://input');
        $data = json_decode($requestBody, true);

        if (!$data || !isset($data['category'])) {
            $this->respondWithError('Invalid request format');
            return;
        }

        $category = $data['category'];

        if (!in_array($category, $this->availableCategories)) {
            $this->respondWithError('Invalid category');
            return;
        }

        // Set the selected category and restart the quiz
        $_SESSION['selected_category'] = $category;
        $this->restartQuiz();

        // Reload the XML file for the selected category
        $this->questions = [];
        $this->totalQuestions = 0;
        $this->quizMetadata = [];
        $this->categories = [];
        $this->loadDataFromXML('../data/' . $category . '.xml');

        $this->respondWithSuccess([
            'status' => 'Category set successfully',
            'category' => $category,
            'metadata' => $this->quizMetadata
        ]);
    }

    /**
     * Get current question information
     */
    private function getQuestion()
    {
        $index = $_SESSION['current_question_index'];

        if ($index < $this->totalQuestions && !$_SESSION['quiz_complete']) {
            $currentQuestion = $this->questions[$index];

            // Get category name if available
            $categoryId = $currentQuestion['category'];
            $categoryName = $this->categories[$categoryId] ?? $categoryId;

            $this->respondWithSuccess([
                'question' => $currentQuestion['text'],
                'options' => $currentQuestion['options'],
                'question_number' => $index + 1,
                'total_questions' => $this->totalQuestions,
                'quiz_complete' => false,
                'difficulty' => $currentQuestion['difficulty'],
                'category' => $categoryName,
                'selected_category' => $_SESSION['selected_category']
            ]);
        } else {
            $this->respondWithSuccess([
                'quiz_complete' => true,
                'score' => $_SESSION['score'],
                'total_questions' => $this->totalQuestions,
                'selected_category' => $_SESSION['selected_category']
            ]);
        }
    }

    /**
     * Process answer submission
     */
    private function submitAnswer()
    {
        $index = $_SESSION['current_question_index'];

        if ($index >= $this->totalQuestions || $_SESSION['quiz_complete']) {
            $this->respondWithError('Invalid request or quiz already completed.', [
                'quiz_complete' => $_SESSION['quiz_complete'],
                'score' => $_SESSION['score'],
                'total_questions' => $this->totalQuestions
            ]);
            return;
        }

        try {
            // Get the user's answer from the request body
            $requestBody = file_get_contents('php://input');
            $data = json_decode($requestBody, true);

            if (!$data || !isset($data['answer'])) {
                throw new Exception('Invalid request format');
            }

            $userAnswer = $data['answer'];
            $correctAnswer = $this->questions[$index]['answer'];
            $isCorrect = ($userAnswer === $correctAnswer);
            $feedback = $this->questions[$index]['feedback'] ?: null;
            $difficulty = $this->questions[$index]['difficulty'];

            // Track the answer in session for statistics
            if (!isset($_SESSION['answers'])) {
                $_SESSION['answers'] = [];
            }

            $_SESSION['answers'][] = [
                'question_index' => $index,
                'user_answer' => $userAnswer,
                'correct_answer' => $correctAnswer,
                'is_correct' => $isCorrect,
                'difficulty' => $difficulty
            ];

            // Update score if correct - simple 1 point per correct answer
            if ($isCorrect) {
                $_SESSION['score']++;
            }

            // Move to the next question
            $_SESSION['current_question_index']++;

            // Check if quiz is complete
            $isQuizComplete = ($_SESSION['current_question_index'] >= $this->totalQuestions);
            if ($isQuizComplete) {
                $_SESSION['quiz_complete'] = true;
                $_SESSION['completion_time'] = time();
            }

            $this->respondWithSuccess([
                'correct' => $isCorrect,
                'correct_answer' => $correctAnswer,
                'feedback' => $feedback,
                'score' => $_SESSION['score'],
                'quiz_complete' => $isQuizComplete,
                'total_questions' => $this->totalQuestions
            ]);
        } catch (Exception $e) {
            $this->respondWithError($e->getMessage());
        }
    }

    /**
     * Reset the quiz state
     */
    public function restartQuiz()
    {
        $_SESSION['current_question_index'] = 0;
        $_SESSION['score'] = 0; // Simple score - just count of correct answers
        $_SESSION['quiz_complete'] = false;
        $_SESSION['start_time'] = time();
        $_SESSION['answers'] = [];

        // Initialize selected category if not set
        if (!isset($_SESSION['selected_category'])) {
            $_SESSION['selected_category'] = 'geography'; // Default to geography
        }
    }

    /**
     * Save quiz results to the database
     */
    private function saveQuizResults()
    {
        if (!isset($_SESSION['user_id'])) {
            // Only save results for logged-in users
            return;
        }

        try {
            $userId = $_SESSION['user_id'];
            $category = $_SESSION['selected_category'];
            $score = $_SESSION['score'];
            $totalQuestions = $this->totalQuestions;
            $completionTime = isset($_SESSION['completion_time']) ?
                ($_SESSION['completion_time'] - $_SESSION['start_time']) : 0;

            // Insert into quiz_results
            $stmt = $GLOBALS['con']->prepare("
                INSERT INTO quiz_results 
                (user_id, category, score, total_questions, completion_time) 
                VALUES (?, ?, ?, ?, ?)
            ");

            // Change from "isiii" to "ssiii" because user_id is VARCHAR in the database
            $stmt->bind_param("ssiii", $userId, $category, $score, $totalQuestions, $completionTime);
            $stmt->execute();

            // Get the quiz result ID
            $quizResultId = $stmt->insert_id;

            // Save individual question answers
            if (isset($_SESSION['answers']) && $quizResultId) {
                foreach ($_SESSION['answers'] as $answer) {
                    $questionIdx = $answer['question_index'];

                    // Make sure the question index is valid
                    if (isset($this->questions[$questionIdx])) {
                        $questionText = $this->questions[$questionIdx]['text'];
                        $userAnswer = $answer['user_answer'];
                        $correctAnswer = $answer['correct_answer'];
                        $isCorrect = $answer['is_correct'] ? 1 : 0;
                        $difficulty = $answer['difficulty'];
                        $category = $this->questions[$questionIdx]['category'];

                        $stmt = $GLOBALS['con']->prepare("
                            INSERT INTO question_answers 
                            (user_id, quiz_result_id, question_text, user_answer, correct_answer, is_correct, category, difficulty) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                        ");

                        // Change first parameter from "i" to "s" for user_id
                        $stmt->bind_param(
                            "sissssss",
                            $userId,
                            $quizResultId,
                            $questionText,
                            $userAnswer,
                            $correctAnswer,
                            $isCorrect,
                            $category,
                            $difficulty
                        );
                        $stmt->execute();
                    }
                }
            }

            // Add error checking and debugging
            if ($GLOBALS['con']->error) {
                error_log('MySQL Error: ' . $GLOBALS['con']->error);
            }
        } catch (Exception $e) {
            // Log the error but don't interrupt the response
            error_log('Error saving quiz results: ' . $e->getMessage());
        }
    }

    /**
     * Get quiz statistics
     */
    private function getQuizStats()
    {
        if (!$_SESSION['quiz_complete'] && $_SESSION['current_question_index'] < $this->totalQuestions) {
            $this->respondWithError('Quiz not completed yet');
            return;
        }

        // Save quiz results to database for logged-in users
        $this->saveQuizResults();

        $correctCount = 0;
        $incorrectCount = 0;

        // Track count of correct answers by difficulty
        $difficultyStats = [
            'easy' => ['correct' => 0, 'total' => 0],
            'medium' => ['correct' => 0, 'total' => 0],
            'hard' => ['correct' => 0, 'total' => 0]
        ];

        // Calculate statistics from answers
        if (isset($_SESSION['answers'])) {
            foreach ($_SESSION['answers'] as $answer) {
                // Get the difficulty level
                $difficulty = $answer['difficulty'] ?? 'medium'; // Default to medium if not set

                // Make sure the difficulty is one of our expected values
                if (!isset($difficultyStats[$difficulty])) {
                    $difficulty = 'medium';
                }

                // Count by difficulty
                $difficultyStats[$difficulty]['total']++;

                if ($answer['is_correct']) {
                    $correctCount++;
                    $difficultyStats[$difficulty]['correct']++;
                } else {
                    $incorrectCount++;
                }
            }
        }

        $completionTime = isset($_SESSION['completion_time']) ?
            ($_SESSION['completion_time'] - $_SESSION['start_time']) : 0;

        // Calculate per-category performance
        $categoryStats = [];

        if (isset($_SESSION['answers'])) {
            foreach ($_SESSION['answers'] as $idx => $answer) {
                $questionIdx = $answer['question_index'];

                // Ensure the question index is valid
                if (isset($this->questions[$questionIdx])) {
                    $category = $this->questions[$questionIdx]['category'];
                    $categoryName = $this->categories[$category] ?? $category;

                    if (!isset($categoryStats[$categoryName])) {
                        $categoryStats[$categoryName] = ['correct' => 0, 'total' => 0];
                    }

                    // Always increment total count for this category
                    $categoryStats[$categoryName]['total']++;

                    // Increment correct count only if answer was correct
                    if ($answer['is_correct']) {
                        $categoryStats[$categoryName]['correct']++;
                    }
                }
            }
        }

        // The final score is simply the count of correct answers
        $finalScore = $correctCount;

        // Make sure the incorrect count is consistent
        $incorrectCount = $this->totalQuestions - $correctCount;

        $this->respondWithSuccess([
            'total_questions' => $this->totalQuestions,
            'correct_answers' => $correctCount,
            'incorrect_answers' => $incorrectCount,
            'score' => $finalScore,
            'completion_time' => $completionTime,
            'answer_details' => $_SESSION['answers'] ?? [],
            'category_performance' => $categoryStats,
            'difficulty_performance' => $difficultyStats, // New field for difficulty stats
            'selected_category' => $_SESSION['selected_category']
        ]);
    }

    /**
     * Get quiz basic information
     */
    private function getQuizInfo()
    {
        $userInfo = null;

        // Add user info if logged in
        if (isset($_SESSION['user_id'])) {
            $userInfo = [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'] ?? null
            ];
        }

        $this->respondWithSuccess([
            'metadata' => $this->quizMetadata,
            'categories' => $this->categories,
            'selected_category' => $_SESSION['selected_category'],
            'user' => $userInfo
        ]);
    }

    /**
     * Send an error response
     */
    private function respondWithError($message, $additionalData = [])
    {
        $response = array_merge(['error' => $message], $additionalData);
        http_response_code(400);
        echo json_encode($response);
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

// Initialize and handle the request
try {
    $quizHandler = new QuizHandler();
    $quizHandler->handleRequest();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
