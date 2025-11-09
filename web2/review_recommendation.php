<?php
session_start();
require 'db.php';

//Check login and educator role 
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: login.php?error=not_logged_in");
    exit();
}
if (strtolower($_SESSION['user_type']) !== 'educator') {
    header("Location: login.php?error=access_denied");
    exit();
}

$educator_id = $_SESSION['user_id'];

// ---  Validate form input ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $recID = $_POST['recID'] ?? null;
    $status = $_POST['status'] ?? null;
    $comment = $_POST['comments'] ?? null;

    if (!$recID || !$status) {
        die("Invalid request: Missing data.");
    }

    // ---  Retrieve recommended question details ---
    $sql_getRec = "
        SELECT rq.quizID, rq.question, rq.questionFigureFileName
        FROM recommendedquestion rq
        JOIN quiz q ON rq.quizID = q.id
        WHERE rq.id = ? AND q.educatorID = ?
    ";
    $stmt_getRec = $connection->prepare($sql_getRec);
    $stmt_getRec->bind_param("ii", $recID, $educator_id);
    $stmt_getRec->execute();
    $result = $stmt_getRec->get_result();

    if ($result->num_rows === 0) {
        die("Invalid or unauthorized recommendation ID.");
    }

    $rec = $result->fetch_assoc();

    // ---  Update RecommendedQuestion ---
    $sql_update = "UPDATE recommendedquestion SET status = ?, comments = ? WHERE id = ?";
    $stmt_update = $connection->prepare($sql_update);
    $stmt_update->bind_param("ssi", $status, $comment, $recID);
    $stmt_update->execute();

    // ---  If approved, insert into QuizQuestion ---
    if (strtolower($status) === "approved") {

        // Fetch full details needed for quizquestion INSERT
        $sql_full = "
            SELECT question, questionFigureFileName, answerA, answerB, answerC, answerD, correctAnswer
            FROM recommendedquestion
            WHERE id = ?
        ";
        $stmt_full = $connection->prepare($sql_full);
        $stmt_full->bind_param("i", $recID);
        $stmt_full->execute();
        $full = $stmt_full->get_result()->fetch_assoc();
        $stmt_full->close();

        // Insert full question info
        $sql_insert = "
            INSERT INTO quizquestion 
            (quizID, question, questionFigureFileName, answerA, answerB, answerC, answerD, correctAnswer)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt_insert = $connection->prepare($sql_insert);
        $stmt_insert->bind_param(
            "isssssss",
            $rec['quizID'],
            $full['question'],
            $full['questionFigureFileName'],
            $full['answerA'],
            $full['answerB'],
            $full['answerC'],
            $full['answerD'],
            $full['correctAnswer']
        );
        $stmt_insert->execute();
    }

    // ---  Redirect back ---
    header("Location: Educators_homepage.php?success=review_updated");
    exit();

} else {
    header("Location: Educators_homepage.php?error=invalid_access");
    exit();
}
?>
