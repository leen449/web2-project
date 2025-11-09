<?php
session_start();
require "db.php";

// ---- must be logged in as learner ----
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || strtolower($_SESSION['user_type']) !== 'learner') {
    header("Location: login.php?error=not_logged_in");
    exit;
}

$learnerID = (int) $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // validate
    $topicID    = (int)($_POST['topic'] ?? 0);
    $educatorID = (int)($_POST['educator'] ?? 0);
    $question   = trim($_POST['question'] ?? '');
    $choiceA    = trim($_POST['choiceA'] ?? '');
    $choiceB    = trim($_POST['choiceB'] ?? '');
    $choiceC    = trim($_POST['choiceC'] ?? '');
    $choiceD    = trim($_POST['choiceD'] ?? '');
    $correct    = trim($_POST['correct'] ?? '');

    if (!$topicID || !$educatorID || $question === '' || $choiceA === '' || $choiceB === '' || $choiceC === '' || $choiceD === '' || !in_array($correct, ['A','B','C','D'], true)) {
        echo "<script>alert('Please fill all fields correctly.'); history.back();</script>";
        exit;
    }

    // File upload (store filename only)
    $figureFileName = "";
    if (isset($_FILES['figure']) && isset($_FILES['figure']['error']) && $_FILES['figure']['error'] === UPLOAD_ERR_OK) {
        $uploadDirFs = __DIR__ . "/uploads/";
        if (!is_dir($uploadDirFs)) {
            mkdir($uploadDirFs, 0775, true);
        }
        $ext = strtolower(pathinfo($_FILES['figure']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext, $allowed, true)) {
            // use time + random bytes for filename 
            try {
                $figureFileName = time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
            } catch (Exception $e) {
                $figureFileName = time() . "_" . bin2hex(openssl_random_pseudo_bytes(4)) . "." . $ext;
            }
            if (!move_uploaded_file($_FILES['figure']['tmp_name'], $uploadDirFs . $figureFileName)) {
                $figureFileName = ""; // fallback if move fails
            }
        }
    }

    // Find quiz for educator + topic (prepared)
    $quizStmt = $connection->prepare("SELECT id FROM quiz WHERE topicID = ? AND educatorID = ? LIMIT 1");
    $quizStmt->bind_param("ii", $topicID, $educatorID);
    $quizStmt->execute();
    $quizRes = $quizStmt->get_result();
    $quizRow = $quizRes->fetch_assoc();
    $quizStmt->close();

    if (!$quizRow) {
        echo "<script>alert('No quiz found for the selected educator and topic.'); history.back();</script>";
        exit;
    }
    $quizID = (int)$quizRow['id'];

    // Insert recommended question (status = pending, comments = '')
    $stmt = $connection->prepare("
        INSERT INTO recommendedquestion
            (quizID, learnerID, question, questionFigureFileName, answerA, answerB, answerC, answerD, correctAnswer, status, comments)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', '')
    ");
    $stmt->bind_param(
        "iisssssss",
        $quizID, $learnerID, $question, $figureFileName, $choiceA, $choiceB, $choiceC, $choiceD, $correct
    );
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('Question added successfully!'); window.location.href='Learners_homepage.php';</script>";
    exit;
}

// If somebody navigates here directly without POST, redirect back
header("Location: recommended.php");
exit;
