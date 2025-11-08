<?php
include 'db.php'; // اتصال بقاعدة البيانات

// ===== التحقق من quiz_id و question_id =====
if (!isset($_GET['quiz_id']) || !isset($_GET['question_id'])) {
    die("Quiz ID or Question ID is missing!");
}

$quiz_id = intval($_GET['quiz_id']);
$question_id = intval($_GET['question_id']);

// ===== جلب بيانات السؤال =====
$sql = "SELECT * FROM quizquestion WHERE id = $question_id AND quizID = $quiz_id";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Question not found for this quiz.");
}

$question = mysqli_fetch_assoc($result);

// ===== معالجة الفورم عند الإرسال =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $questionText = $_POST['questionText'];
    $answerA = $_POST['answerA'];
    $answerB = $_POST['answerB'];
    $answerC = $_POST['answerC'];
    $answerD = $_POST['answerD'];
    $correctAnswer = $_POST['correctAnswer'];

    // ===== رفع الصورة الجديدة إذا تم اختيارها =====
    $newImage = $question['questionFigureFileName']; // الصورة القديمة
    if (isset($_FILES['questionImage']) && $_FILES['questionImage']['error'] === 0) {
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) mkdir($uploadDir);

        $ext = pathinfo($_FILES['questionImage']['name'], PATHINFO_EXTENSION);
        $filename = "quiz" . $quiz_id . "_question_" . $question_id . "_" . time() . "." . $ext;
        $targetFile = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['questionImage']['tmp_name'], $targetFile)) {
            $newImage = $filename; // فقط اسم الملف يُخزن في DB
        }
    }

    // ===== تحديث قاعدة البيانات =====
    $updateSql = "UPDATE quizquestion SET 
                    question = '".mysqli_real_escape_string($conn,$questionText)."',
                    questionFigureFileName = '".mysqli_real_escape_string($conn,$newImage)."',
                    answerA = '".mysqli_real_escape_string($conn,$answerA)."',
                    answerB = '".mysqli_real_escape_string($conn,$answerB)."',
                    answerC = '".mysqli_real_escape_string($conn,$answerC)."',
                    answerD = '".mysqli_real_escape_string($conn,$answerD)."',
                    correctAnswer = '".mysqli_real_escape_string($conn,$correctAnswer)."'
                  WHERE id = $question_id AND quizID = $quiz_id";

    if (mysqli_query($conn, $updateSql)) {
        header("Location: quiz_page.php?quizID=$quiz_id");
        exit;
    } else {
        echo "Error updating question: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Question</title>
<link rel="stylesheet" href="style.css">
<style>
body {
  font-family: Arial, sans-serif;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  margin: 0;
  background: #f4f4f9;
}
.container {
  background: #fff;
  padding: 20px 30px;
  border: 2px solid #000;
  border-radius: 8px;
  width: 450px;
  margin: 40px auto;
  flex-grow: 1;
}
h2 { text-align: center; margin-bottom: 15px; }
label { display: block; margin-top: 12px; font-weight: bold; }
textarea, input[type="text"], select {
  width: 100%;
  padding: 8px;
  margin-top: 5px;
  border: 1px solid #ccc;
  border-radius: 4px;
}
.file-section { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; }
.file-section img { max-width: 120px; max-height: 100px; border: 1px solid #ccc; margin-left: 10px; }
.button {
  display: inline-block;
  padding: 12px 25px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  font-size: 16px;
  font-weight: bold;
  text-decoration: none;
  color: #fff;
  background-image: linear-gradient(to right, #7341b1, #ee7979);
  transition: background 0.3s ease, transform 0.3s ease;
}
.button:hover {
  background-image: linear-gradient(to right, #8a3ccf, #ff7b90);
  transform: scale(1.05);
}
footer {
  background-image: linear-gradient(to right, #7341b1, #ee7979);
  clip-path: ellipse(100% 100% at 50% 100%);
  overflow: visible;
  width: 100%;
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
  padding: 20px 0;
  text-align: center;
}
footer p { margin: 0; font-size: 16px; color: #0f1214; }
</style>
</head>
<body>

<header>
  <nav>
    <ul>
      <li><a href="Educators homepage.php"><img src="images/mindly.png" alt="Mindly Logo" /></a></li>
    </ul>
  </nav>
</header>

<div class="container">
  <h2>Edit Question</h2>
  <form method="post" enctype="multipart/form-data">
    <label for="questionText">Question:</label>
    <textarea id="questionText" name="questionText" rows="3"><?php echo htmlspecialchars($question['question']); ?></textarea>

    <label>Upload Question Figure:</label>
    <div class="form-group file-section">
      <input type="file" name="questionImage" id="questionImage" accept="image/*">
      <?php if(!empty($question['questionFigureFileName'])): ?>
        <img id="currentImage" src="uploads/<?php echo htmlspecialchars($question['questionFigureFileName']); ?>" alt="Current Image">
      <?php endif; ?>
    </div>

    <label for="answerA">Answer A:</label>
    <input type="text" name="answerA" value="<?php echo htmlspecialchars($question['answerA']); ?>"> 

    <label for="answerB">Answer B:</label>
    <input type="text" name="answerB" value="<?php echo htmlspecialchars($question['answerB']); ?>">

    <label for="answerC">Answer C:</label>
    <input type="text" name="answerC" value="<?php echo htmlspecialchars($question['answerC']); ?>">

    <label for="answerD">Answer D:</label>
    <input type="text" name="answerD" value="<?php echo htmlspecialchars($question['answerD']); ?>">

    <label for="correctAnswer">Correct Answer:</label>
    <select name="correctAnswer" id="correctAnswer" required>
      <option value="" disabled>-- Select Correct Answer --</option>
      <option value="A" <?php if($question['correctAnswer']=='A') echo 'selected'; ?>>A</option>
      <option value="B" <?php if($question['correctAnswer']=='B') echo 'selected'; ?>>B</option>
      <option value="C" <?php if($question['correctAnswer']=='C') echo 'selected'; ?>>C</option>
      <option value="D" <?php if($question['correctAnswer']=='D') echo 'selected'; ?>>D</option>
    </select>

    <button type="submit" class="button">Save</button>
  </form>
</div>

<footer>
  <p>&copy; 2025 Mindly. All rights reserved.</p>
</footer>

</body>
</html>
