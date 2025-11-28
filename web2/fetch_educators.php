<?php
session_start();
require "db.php";

// ---- must be logged in as learner ----
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || strtolower($_SESSION['user_type']) !== 'learner') {
    http_response_code(403);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$topicID = (int)($_GET['topicID'] ?? 0);

if ($topicID <= 0) {
    echo json_encode([]);
    exit;
}

// Fetch educators who have a quiz in this topic
$stmt = $connection->prepare("
    SELECT DISTINCT u.id, u.firstName, u.lastName
    FROM user u
    JOIN quiz q ON q.educatorID = u.id
    WHERE u.userType = 'Educator' AND q.topicID = ?
    ORDER BY u.firstName ASC
");
$stmt->bind_param("i", $topicID);
$stmt->execute();
$result = $stmt->get_result();

$educators = [];
while ($row = $result->fetch_assoc()) {
    $educators[] = [
        'id' => (int)$row['id'],
        'name' => $row['firstName'] . ' ' . $row['lastName']
    ];
}

$stmt->close();

header('Content-Type: application/json');
echo json_encode($educators);
exit;
