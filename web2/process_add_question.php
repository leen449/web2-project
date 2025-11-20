<?php
// ===== 1. Connect to database =====
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "mindlydatabase";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

require_once 'reqLog.php';

// Get quiz ID from URL
if (!isset($_GET['quizID'])) {
    die("Quiz ID is missing.");
}
$quizID = $_GET['quizID'];

// ===== Handle form submission =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = mysqli_real_escape_string($conn, $_POST['question']);
    $answerA = mysqli_real_escape_string($conn, $_POST['answerA']);
    $answerB = mysqli_real_escape_string($conn, $_POST['answerB']);
    $answerC = mysqli_real_escape_string($conn, $_POST['answerC']);
    $answerD = mysqli_real_escape_string($conn, $_POST['answerD']);
    $correctAnswer = $_POST['correctAnswer'];

    // Image upload
    $imagePath = NULL;
    if (isset($_FILES['questionImage']) && $_FILES['questionImage']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $targetFile = $targetDir . basename($_FILES["questionImage"]["name"]);
        move_uploaded_file($_FILES["questionImage"]["tmp_name"], $targetFile);
        $imagePath = $targetFile;
    }

    // Insert into database
    $sql = "INSERT INTO quizquestion 
            (quizID, question, questionFigureFileName, answerA, answerB, answerC, answerD, correctAnswer)
            VALUES ('$quizID', '$question', '$imagePath', '$answerA', '$answerB', '$answerC', '$answerD', '$correctAnswer')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>
                alert('Question added successfully!');
                window.location.href='Quiz_page.php?quizID=$quizID';
              </script>";
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// ===== Fetch existing questions =====
$questions = [];
$sql = "SELECT * FROM quizquestion WHERE quizID = '$quizID'";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $questions[] = $row;
    }
}
?>

