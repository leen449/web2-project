<?php
require 'db.php';
require_once 'reqLog.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $question = $_POST['question'];
    $topic_id = $_POST['topic'];
    $educator_id = $_POST['educator'];
    $status = "Pending";

    $insertQuery = "INSERT INTO recommended_questions (question_text, topic_id, educator_id, status)
                    VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("siis", $question, $topic_id, $educator_id, $status);
    $stmt->execute();

    // Redirect back to the learner homepage
    header("Location: learner_home.php");
    exit();
}
?>
