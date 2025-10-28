<?php
// ------------------------------------------
// 1. INITIAL SETUP
// ------------------------------------------
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('session.cookie_path', '/');
session_start();
include 'db.php';

// ------------------------------------------
// 2. SESSION AND ROLE VALIDATION
// ------------------------------------------
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: login.php?error=not_logged_in");
    exit();
}
if (strtolower($_SESSION['user_type']) !== 'educator') {
    header("Location: login.php?error=access_denied");
    exit();
}

// ------------------------------------------
// 3. VALIDATE IDS FROM QUERY STRING
// ------------------------------------------
if (!isset($_GET['questionID']) || !isset($_GET['quizID'])) {
    die("<p style='color:red; text-align:center;'>Invalid request: Missing parameters.</p>");
}

$questionID = intval($_GET['questionID']);
$quizID = intval($_GET['quizID']);

// ------------------------------------------
// 4. FETCH QUESTION INFO BEFORE DELETING
// ------------------------------------------
$stmt = $connection->prepare("SELECT questionFigureFileName FROM quizquestion WHERE id = ?");
$stmt->bind_param("i", $questionID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    die("<p style='color:red; text-align:center;'>Question not found.</p>");
}

$question = $result->fetch_assoc();
$stmt->close();

// ------------------------------------------
// 5. DELETE QUESTION FROM DATABASE
// ------------------------------------------
$stmt_delete = $connection->prepare("DELETE FROM quizquestion WHERE id = ?");
$stmt_delete->bind_param("i", $questionID);
$stmt_delete->execute();
$stmt_delete->close();

// ------------------------------------------
// 6. DELETE ASSOCIATED IMAGE FILE (IF EXISTS)
// ------------------------------------------
if (!empty($question['questionFigureFileName'])) {
    $filePath = "uploads/" . $question['questionFigureFileName'];
    if (file_exists($filePath)) {
        unlink($filePath); // delete image file from system
    }
}

// ------------------------------------------
// 7. REDIRECT BACK TO QUIZ PAGE WITH MESSAGE
// ------------------------------------------
header("Location: Quiz page.php?quizID=$quizID&success=deleted");
exit();
?>