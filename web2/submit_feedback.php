<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'db.php';

// Check if user is logged in and is a learner
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || strtolower($_SESSION['user_type']) !== 'learner') {
    header("Location: login.php?error=access_denied");
    exit();
}

// Check if this is a POST request with feedback data
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submit_feedback'])) {
    header("Location: Learners_homepage.php?error=invalid_request");
    exit();
}

// Get and validate input
$quizID = filter_input(INPUT_POST, 'quizID', FILTER_VALIDATE_INT);
$rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
$comments = filter_input(INPUT_POST, 'comments', FILTER_SANITIZE_STRING) ?? '';

// Validate required fields
if (empty($quizID) || empty($rating) || $rating < 1 || $rating > 5) {
    header("Location: Quiz score and feedback.php?error=invalid_input&quizID=" . ($quizID ?? ''));
    exit();
}

try {
    // Always insert new feedback (allow multiple comments per quiz)
    $stmt = $connection->prepare("
        INSERT INTO quizfeedback (quizID, rating, comments, date)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->bind_param("iis", $quizID, $rating, $comments);

    $stmt->execute();
    $stmt->close();

    header("Location: Learners_homepage.php?success=feedback_submitted");
    exit();

} catch (Exception $e) {
    error_log("Error submitting feedback: " . $e->getMessage());
    header("Location: Quiz score and feedback.php?error=submission_failed&quizID=" . $quizID);
    exit();
}

?>
