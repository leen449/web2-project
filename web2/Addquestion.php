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

// Get quiz ID from URL ( req 1 )
if (!isset($_GET['quizID'])) {
    die("Quiz ID is missing.");
}
$quizID = $_GET['quizID'];

// ===== 3. Handle form submission =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = mysqli_real_escape_string($conn, $_POST['question']);
    $answerA = mysqli_real_escape_string($conn, $_POST['answerA']);
    $answerB = mysqli_real_escape_string($conn, $_POST['answerB']);
    $answerC = mysqli_real_escape_string($conn, $_POST['answerC']);
    $answerD = mysqli_real_escape_string($conn, $_POST['answerD']);
    $correctAnswer = $_POST['correctAnswer'];
    
    // Handle optional image upload
    $imagePath = NULL;
    if (isset($_FILES['questionImage']) && $_FILES['questionImage']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $targetFile = $targetDir . basename($_FILES["questionImage"]["name"]);
        move_uploaded_file($_FILES["questionImage"]["tmp_name"], $targetFile);
        $imagePath = $targetFile;
    }

    // Insert question into database ( req 2 )
    $sql = "INSERT INTO quizquestion 
            (quizID, question, questionFigureFileName, answerA, answerB, answerC, answerD, correctAnswer)
            VALUES ('$quizID', '$question', '$imagePath', '$answerA', '$answerB', '$answerC', '$answerD', '$correctAnswer')";

    if (mysqli_query($conn, $sql)) {
        //Redirect to quiz page
         echo "<script>
    alert('Question added successfully!');
    window.location.href='quiz_page.php?quizID=$quizID';
  </script>";
    exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// ===== 4. Fetch existing questions for this quiz =====
$questions = [];
$sql = "SELECT * FROM quizquestion WHERE quizID = '$quizID'";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $questions[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"> 
  <title>Add New Question</title>
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
  background: white;
  padding: 20px 30px;
  border: 2px solid #000;
  border-radius: 8px;
  width: 400px;
  box-shadow: 0px 4px 8px rgba(0,0,0,0.1);
  margin: auto;
}

.container h2 {
  text-align: center;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 15px;
}

label {
  display: block;
  font-weight: bold;
  margin-bottom: 6px;
}

textarea, input[type="text"], select {
  width: 100%;
  padding: 8px;
  border: 1px solid #666;
  border-radius: 4px;
}

input[type="file"] {
  margin-top: 5px;
}

button {
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

button:hover {
  background-image: linear-gradient(to right, #8a3ccf, #ff7b90);
  transform: scale(1.05);
}

button:focus {
  outline: 2px solid #7341b1;
  outline-offset: 3px;
}

header {
  box-shadow: 2px 6px 10px rgba(0, 0, 0, 0.1);
}

nav {
  background-image: linear-gradient(to right, #7341b1, #ee7979);
  clip-path: ellipse(100% 100% at 50% 0%);
  overflow: hidden;
  width: 100%;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

nav li {
  list-style-type: none;
  float: left;
}

nav img {
  height: 50%;
  width: 40%;
}

footer {
  margin-top: auto;
  background-image: linear-gradient(to right, #7341b1, #ee7979);
  clip-path: ellipse(100% 100% at 50% 100%);
  width: 100%;
  padding: 20px 0;
  text-align: center;
}

footer p {
  margin: 0;
  font-size: 16px;
  color: #0f1214;
}

.question-list {
  margin-top: 40px;
}

.question-item {
  border: 1px solid #ccc;
  border-radius: 6px;
  padding: 10px;
  margin-bottom: 15px;
  background-color: #fafafa;
}

.question-item img {
  max-width: 100%;
  margin-top: 5px;
}
    
  </style>
</head>
<body>
    <header>
        <nav>
          <ul>
            <li><a href="Educators_homepage.php"><img src="images/mindly.png" alt="Mindly Logo" /></a></li>
          </ul>
        </nav>
    </header>

  <br/><br/>

  <div class="container">
    <h2>Add New Question</h2>
    <!-- sends data to php ( req 2 ) -->
    <form method="POST" enctype="multipart/form-data">
        <!-- req 2 -->
      <input type="hidden" name="quizID" value="<?php echo $quizID; ?>">
      <div class="form-group">
        <label>Question:</label>
        <textarea name="question" rows="4" required></textarea>
      </div>
      <div class="form-group">
        <label>Upload Question Figure:</label>
        <input type="file" name="questionImage" accept="image/*">
      </div>
      <div class="form-group">
        <label>Answer A:</label>
        <input type="text" name="answerA" required>
      </div>
      <div class="form-group">
        <label>Answer B:</label>
        <input type="text" name="answerB" required>
      </div>
      <div class="form-group">
        <label>Answer C:</label>
        <input type="text" name="answerC" required>
      </div>
      <div class="form-group">
        <label>Answer D:</label>
        <input type="text" name="answerD" required>
      </div>
      <div class="form-group">
        <label>Correct Answer:</label>
        <select name="correctAnswer" required>
          <option value="A">A</option>
          <option value="B">B</option>
          <option value="C">C</option>
          <option value="D">D</option>
        </select>
      </div>
      <button type="submit">Add</button>
    </form>
  </div>

  <div class="container question-list">
    <h3>Existing Questions</h3>
    <?php if (count($questions) > 0): ?>
        <?php foreach ($questions as $q): ?>
            <div class="question-item">
                <p><strong>Q:</strong> <?php echo htmlspecialchars($q['question']); ?></p>
                <?php if (!empty($q['questionFigureFileName'])): ?>
                    <img src="<?php echo htmlspecialchars($q['questionFigureFileName']); ?>" alt="Question Image">
                <?php endif; ?>
                <p><strong>A:</strong> <?php echo htmlspecialchars($q['answerA']); ?></p>
                <p><strong>B:</strong> <?php echo htmlspecialchars($q['answerB']); ?></p>
                <p><strong>C:</strong> <?php echo htmlspecialchars($q['answerC']); ?></p>
                <p><strong>D:</strong> <?php echo htmlspecialchars($q['answerD']); ?></p>
                <p><strong>Correct Answer:</strong> <?php echo $q['correctAnswer']; ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No questions added yet.</p>
    <?php endif; ?>
  </div>

  <br/><br/>

  <footer>
    <p>&copy; 2025 Mindly. All rights reserved.</p>
  </footer>
</body>
</html>
