<?php
// ------------------------------------------
// 1. INITIAL SETUP
// ------------------------------------------
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db.php';

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
// 4. FETCH QUESTION INFO AND CHECK OWNERSHIP
// ------------------------------------------
$stmt = $connection->prepare("
    SELECT qq.questionFigureFileName, q.educatorID 
    FROM quizquestion qq
    JOIN quiz q ON qq.quizID = q.id
    WHERE qq.id = ?
");
$stmt->bind_param("i", $questionID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    die("<p style='color:red; text-align:center;'>Question not found.</p>");
}

$question = $result->fetch_assoc();
$stmt->close();

// Ensure educator owns this quiz
if ($question['educatorID'] != $_SESSION['user_id']) {
    die("<p style='color:red;text-align:center;'>Access denied.</p>");
}

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
        unlink($filePath); // delete image file
    }
}

// ------------------------------------------
// 7. RETURN RESPONSE
// ------------------------------------------

// If the request came from an AJAX call, just return "true"
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    echo 'true';
    exit();
}

// Otherwise, keep the old behaviour (non-AJAX fallback)
header("Location: Quiz_page.php?quizID=$quizID&success=deleted");
exit();

?>
