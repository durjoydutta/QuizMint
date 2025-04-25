<?php
session_start();
header('Content-Type: application/json');

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

    /**
     * Initialize the quiz handler by loading questions from XML
     */
    public function __construct()
    {
        $this->loadDataFromXML();
        $this->initializeSession();
    }

    /**
     * Load all data from the XML file
     */
    private function loadDataFromXML()
    {
        try {
            $xml = simplexml_load_file('quiz_data.xml');
            if ($xml === false) {
                throw new Exception("Error: Cannot load quiz data XML");
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
        if (!isset($_SESSION['current_question_index'])) {
            $this->restartQuiz();
        }
    }

    /**
     * Handle API requests based on the 'action' parameter
     */
    public function handleRequest()
    {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'get_question':
                $this->getQuestion();
                break;

            case 'submit_answer':
                $this->submitAnswer();
                break;

            case 'restart':
                $this->restartQuiz();
                $this->respondWithSuccess(['status' => 'Quiz restarted']);
                break;

            case 'get_stats':
                $this->getQuizStats();
                break;

            case 'get_quiz_info':
                $this->getQuizInfo();
                break;

            case 'get_categories':
                $this->getCategories();
                break;

            default:
                $this->respondWithError('Invalid action');
        }
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
                'category' => $categoryName
            ]);
        } else {
            $this->respondWithSuccess([
                'quiz_complete' => true,
                'score' => $_SESSION['score'],
                'total_questions' => $this->totalQuestions
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

            // Update score if correct
            if ($isCorrect) {
                // Apply score multiplier based on difficulty
                $multiplier = 1;
                if ($difficulty === 'hard') {
                    $multiplier = 3;
                } else if ($difficulty === 'medium') {
                    $multiplier = 2;
                }

                $_SESSION['score'] += $multiplier;
                $_SESSION['points_earned'][] = $multiplier;
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
        $_SESSION['score'] = 0;
        $_SESSION['quiz_complete'] = false;
        $_SESSION['start_time'] = time();
        $_SESSION['answers'] = [];
        $_SESSION['points_earned'] = [];
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

        $correctCount = 0;
        $incorrectCount = 0;

        if (isset($_SESSION['answers'])) {
            foreach ($_SESSION['answers'] as $answer) {
                if ($answer['is_correct']) {
                    $correctCount++;
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
                $category = $this->questions[$questionIdx]['category'];
                $categoryName = $this->categories[$category] ?? $category;

                if (!isset($categoryStats[$categoryName])) {
                    $categoryStats[$categoryName] = ['correct' => 0, 'total' => 0];
                }

                $categoryStats[$categoryName]['total']++;
                if ($answer['is_correct']) {
                    $categoryStats[$categoryName]['correct']++;
                }
            }
        }

        $this->respondWithSuccess([
            'total_questions' => $this->totalQuestions,
            'correct_answers' => $correctCount,
            'incorrect_answers' => $incorrectCount,
            'score' => $_SESSION['score'],
            'completion_time' => $completionTime,
            'answer_details' => $_SESSION['answers'] ?? [],
            'category_performance' => $categoryStats
        ]);
    }

    /**
     * Get quiz basic information
     */
    private function getQuizInfo()
    {
        $this->respondWithSuccess([
            'metadata' => $this->quizMetadata,
            'total_questions' => $this->totalQuestions,
            'categories' => $this->categories
        ]);
    }

    /**
     * Get quiz categories
     */
    private function getCategories()
    {
        $this->respondWithSuccess([
            'categories' => $this->categories
        ]);
    }

    /**
     * Send a success response
     */
    private function respondWithSuccess($data)
    {
        echo json_encode($data);
        exit;
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
}

// Initialize and handle the request
try {
    $quizHandler = new QuizHandler();
    $quizHandler->handleRequest();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
