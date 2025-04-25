<?php
session_start();

// Load quiz data from XML file
$xml = simplexml_load_file('quiz_data.xml') or die("Error: Cannot create object");
$questions = [];
foreach ($xml->question as $questionNode) {
    $options = [];
    foreach ($questionNode->options->option as $option) {
        $options[] = (string)$option;
    }
    $questions[] = [
        'text' => (string)$questionNode->text,
        'options' => $options,
        'answer' => (string)$questionNode->answer
    ];
}

$totalQuestions = count($questions);

// Initialize session variables if not set
if (!isset($_SESSION['current_question_index'])) {
    $_SESSION['current_question_index'] = 0;
    $_SESSION['score'] = 0;
    $_SESSION['quiz_complete'] = false;
}

// Handle API requests
$action = $_GET['action'] ?? '';

if ($action === 'get_question') {
    $index = $_SESSION['current_question_index'];
    if ($index < $totalQuestions && !$_SESSION['quiz_complete']) {
        $currentQuestion = $questions[$index];
        echo json_encode([
            'question' => $currentQuestion['text'],
            'options' => $currentQuestion['options'],
            'question_number' => $index + 1,
            'total_questions' => $totalQuestions,
            'quiz_complete' => false
        ]);
    } else {
        // Quiz is already complete or index out of bounds, send completion status
        echo json_encode([
            'quiz_complete' => true,
            'score' => $_SESSION['score'],
            'total_questions' => $totalQuestions
        ]);
    }
} elseif ($action === 'submit_answer') {
    $index = $_SESSION['current_question_index'];
    if ($index < $totalQuestions && !$_SESSION['quiz_complete']) {
        $userAnswer = json_decode(file_get_contents('php://input'), true)['answer'] ?? null;
        $correctAnswer = $questions[$index]['answer'];
        $isCorrect = ($userAnswer === $correctAnswer);

        if ($isCorrect) {
            $_SESSION['score']++;
        }

        $_SESSION['current_question_index']++;

        $response = [
            'correct' => $isCorrect,
            'correct_answer' => $correctAnswer,
            'score' => $_SESSION['score']
        ];

        // Check if the quiz is now complete
        if ($_SESSION['current_question_index'] >= $totalQuestions) {
            $_SESSION['quiz_complete'] = true;
            $response['quiz_complete'] = true;
            $response['total_questions'] = $totalQuestions;
        } else {
            $response['quiz_complete'] = false;
        }

        echo json_encode($response);
    } else {
        // Handle cases where the quiz is already complete or index is invalid
        echo json_encode([
            'error' => 'Invalid request or quiz already completed.',
            'quiz_complete' => $_SESSION['quiz_complete'],
            'score' => $_SESSION['score'],
            'total_questions' => $totalQuestions
        ]);
    }
} elseif ($action === 'restart') {
    // Reset session variables
    $_SESSION['current_question_index'] = 0;
    $_SESSION['score'] = 0;
    $_SESSION['quiz_complete'] = false;
    echo json_encode(['status' => 'Quiz restarted']);
} else {
    echo json_encode(['error' => 'Invalid action']);
}
