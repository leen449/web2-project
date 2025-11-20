<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db.php'; // قاعدة البيانات

if (!isset($_GET['quiz_id']) || !isset($_GET['question_id'])) {
    die("Quiz ID or Question ID is missing!");
}

$quiz_id = intval($_GET['quiz_id']);
$question_id = intval($_GET['question_id']);

// جلب بيانات السؤال
$sql = "SELECT * FROM quizquestion WHERE id = $question_id AND quizID = $quiz_id";
$result = mysqli_query($connection, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Question not found for this quiz.");
}

$question = mysqli_fetch_assoc($result);

// معالجة تحديث السؤال عند الضغط على حفظ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $questionText = $_POST['questionText'];
    $answerA = $_POST['answerA'];
    $answerB = $_POST['answerB'];
    $answerC = $_POST['answerC'];
    $answerD = $_POST['answerD'];
    $correctAnswer = $_POST['correctAnswer'];

    $newImage = $question['questionFigureFileName'];

    if (isset($_FILES['questionImage']) && $_FILES['questionImage']['error'] === 0) {
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) mkdir($uploadDir);

        $ext = pathinfo($_FILES['questionImage']['name'], PATHINFO_EXTENSION);
        $filename = "quiz" . $quiz_id . "_question_" . $question_id . "_" . time() . "." . $ext;
        $targetFile = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['questionImage']['tmp_name'], $targetFile)) {
            $newImage = $filename;
        }
    }

    $updateSql = "UPDATE quizquestion SET 
                    question = '" . mysqli_real_escape_string($connection, $questionText) . "',
                    questionFigureFileName = '" . mysqli_real_escape_string($connection, $newImage) . "',
                    answerA = '" . mysqli_real_escape_string($connection, $answerA) . "',
                    answerB = '" . mysqli_real_escape_string($connection, $answerB) . "',
                    answerC = '" . mysqli_real_escape_string($connection, $answerC) . "',
                    answerD = '" . mysqli_real_escape_string($connection, $answerD) . "',
                    correctAnswer = '" . mysqli_real_escape_string($connection, $correctAnswer) . "'
                  WHERE id = $question_id AND quizID = $quiz_id";

    if (mysqli_query($connection, $updateSql)) {
        echo "<script>
                alert('Question Edited successfully!');
                window.location.href='quiz_page.php?quizID=$quiz_id';
              </script>";
        exit;
    } else {
        echo "Error updating question: " . mysqli_error($connection);
    }
}
?>

