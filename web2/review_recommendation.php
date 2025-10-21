<?php
session_start();
include 'db.php';

// --- 1. Check login and educator role ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: login.php?error=not_logged_in");
    exit();
}

if ($_SESSION['user_type'] !== 'educator') {
    header("Location: login.php?error=access_denied");
    exit();
}

$educator_id = $_SESSION['user_id'];

// --- 2. Validate form input ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $recID = $_POST['recID'] ?? null;  // ✅ fixed name
    $status = $_POST['status'] ?? null;  // “approved” or “disapproved”
    $comment = $_POST['comments'] ?? null;

    if (!$recID || !$status) {
        die("Invalid request: Missing data.");
    }

    // --- 3. Retrieve recommended question details ---
    $sql_getRec = "
        SELECT rq.quizID, rq.question, rq.questionFigureFileName
        FROM RecommendedQuestion rq
        JOIN Quiz q ON rq.quizID = q.id
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

    // --- 4. Update RecommendedQuestion status and comments ---
    $sql_update = "UPDATE RecommendedQuestion SET status = ?, comments = ? WHERE id = ?";
    $stmt_update = $connection->prepare($sql_update);
    $stmt_update->bind_param("ssi", $status, $comment, $recID);
    $stmt_update->execute();

    // --- 5. If approved, insert question into QuizQuestion ---
    if (strtolower($status) === "approved") {
        $sql_insert = "INSERT INTO QuizQuestion (quizID, question, questionFigureFileName) VALUES (?, ?, ?)";
        $stmt_insert = $connection->prepare($sql_insert);
        $stmt_insert->bind_param("iss", $rec['quizID'], $rec['question'], $rec['questionFigureFileName']);
        $stmt_insert->execute();
    }

    // --- 6. Redirect back with a success message ---
    header("Location: Educators homepage.php?success=review_updated");
    exit();
} else {
    header("Location: Educators homepage.php?error=invalid_access");
    exit();
}
?>
