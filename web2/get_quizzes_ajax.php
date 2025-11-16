<?php
session_start();
require 'db.php';

header("Content-Type: application/json");

$topicID = $_GET['topic'] ?? 'all';

// Build SQL
if ($topicID === "all") {
    $sql = "
        SELECT q.id AS quizID, t.topicName,
               u.firstName, u.lastName, u.photoFileName
        FROM quiz q
        JOIN topic t ON q.topicID = t.id
        JOIN user u ON q.educatorID = u.id
        ORDER BY t.topicName
    ";
    $stmt = $connection->prepare($sql);

} else {
    $sql = "
        SELECT q.id AS quizID, t.topicName,
               u.firstName, u.lastName, u.photoFileName
        FROM quiz q
        JOIN topic t ON q.topicID = t.id
        JOIN user u ON q.educatorID = u.id
        WHERE t.id = ?
        ORDER BY t.topicName
    ";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $topicID);
}

$stmt->execute();
$result = $stmt->get_result();

$quizzes = [];

while ($row = $result->fetch_assoc()) {

    // Count questions
    $qstmt = $connection->prepare("SELECT COUNT(*) AS total FROM quizquestion WHERE quizID = ?");
    $qstmt->bind_param("i", $row['quizID']);
    $qstmt->execute();
    $count = $qstmt->get_result()->fetch_assoc()['total'] ?? 0;
    $qstmt->close();

    $quizzes[] = [
        "quizID" => $row['quizID'],
        "topicName" => $row['topicName'],
        "educator" => $row['firstName'] . " " . $row['lastName'],
        "eduPhoto" => $row['photoFileName'],
        "questionCount" => $count
    ];
}

echo json_encode($quizzes);
exit;
?>
