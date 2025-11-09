<?php
session_start();

// Assuming learner is logged in and their ID is stored in session
$learnerID = $_SESSION['userID'] ?? 3346; // Default for testing

// Database connection
$host = "localhost";
$user = "root";      // replace with your DB username
$pass = "";          // replace with your DB password
$dbname = "mindlydatabase";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch topics
$topics = [];
$result = $conn->query("SELECT id, topicName FROM topic ORDER BY topicName ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $topics[] = $row;
    }
}

// Fetch educators (Phase 2: all educators)
$educators = [];
$result = $conn->query("SELECT id, firstName, lastName FROM user WHERE userType='Educator' ORDER BY firstName ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $educators[] = $row;
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $topicID = $_POST['topic'];
    $educatorID = $_POST['educator'];
    $question = $_POST['question'];
    $choiceA = $_POST['choiceA'];
    $choiceB = $_POST['choiceB'];
    $choiceC = $_POST['choiceC'];
    $choiceD = $_POST['choiceD'];
    $correct = $_POST['correct'];
    
    // Handle file upload if exists
    $figureFileName = "";
    if (isset($_FILES['figure']) && $_FILES['figure']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $figureFileName = time() . "_" . basename($_FILES['figure']['name']);
        move_uploaded_file($_FILES['figure']['tmp_name'], $uploadDir . $figureFileName);
    }

    // Determine quizID for this topic & educator (simplified: pick first matching)
    $quizIDResult = $conn->query("SELECT id FROM quiz WHERE topicID='$topicID' AND educatorID='$educatorID' LIMIT 1");
    $quizID = $quizIDResult->fetch_assoc()['id'] ?? 0;

    // Insert recommended question
    $stmt = $conn->prepare("INSERT INTO recommendedquestion (quizID, learnerID, question, questionFigureFileName, answerA, answerB, answerC, answerD, correctAnswer, status, comments) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', '')");
    $stmt->bind_param("iisssssss", $quizID, $learnerID, $question, $figureFileName, $choiceA, $choiceB, $choiceC, $choiceD, $correct);
    $stmt->execute();
    $stmt->close();

    // Redirect to learner homepage
    echo "<script>alert('Question added successfully!'); window.location.href='Learners_homepage.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Recommend Question - Mindly</title>
  <style>
    /* Your existing CSS code here (kept intact) */
    button:hover { background: #7b34b4; }
    html { scroll-behavior: smooth; }
    header { box-shadow: 2px 6px 10px rgba(0,0,0,0.1); }
    nav { background-image: linear-gradient(to right, #7341b1, #ee7979); clip-path: ellipse(100% 100% at 50% 0%); overflow: hidden; width: 100%; box-shadow: 0 4px 8px rgba(0,0,0,0.1); padding: 10px 18px; }
    nav ul { margin:0; padding:0; }
    nav li { list-style:none; float:left; }
    nav img { height:50%; width:40%; }
    footer { background-image: linear-gradient(to right, #7341b1, #ee7979); clip-path: ellipse(100% 100% at 50% 100%); width: 100%; padding: 18px 0; text-align: center; box-shadow: 0 6px 12px rgba(0,0,0,0.15); }
    footer p { margin:0; font-size:15px; color:#0f1214; }
    body { font-family: Arial, sans-serif; margin: 0; background: #f8f9fa; color: #222; opacity: 0; transition: opacity 1s ease-in-out; }
    body.loaded { opacity: 1; }
    .question-frame { max-width: 900px; margin: 18px auto; background: #fff; border: 3px solid #000; border-radius: 6px; padding: 18px 22px; box-shadow: 0 4px 10px rgba(0,0,0,0.06); }
    .frame-title { text-align: center; font-weight: bold; margin: 0 0 12px; font-size: 20px; }
    form .grid { display: grid; grid-template-columns: 170px 1fr; grid-row-gap: 12px; grid-column-gap: 18px; align-items: start; padding: 6px 6px 18px; }
    .grid label { font-weight: bold; padding-top: 6px; align-self: start; }
    .grid input[type="text"], .grid input[type="file"], .grid select, .grid textarea { width: 100%; padding: 8px 10px; border-radius: 6px; border: 1px solid #999; font-size: 14px; box-sizing: border-box; }
    textarea#questionText { min-height: 140px; resize: vertical; }
    .correct-wrap { display: flex; gap: 8px; align-items: center; }
    .correct-wrap select { width: 68px; padding: 6px; }
    .correct-wrap input[type="text"] { width: 70px; padding: 6px; text-align: center; }
    .button-row { text-align: center; margin-top: 18px; display: flex; justify-content: center; gap: 12px; }
    .btn { display: inline-block; padding: 12px 25px; border-radius: 8px; border: none; cursor: pointer; font-size: 16px; font-weight: bold; text-decoration: none; color: #fff; background-image: linear-gradient(to right, #7341b1, #ee7979); transition: background 0.3s ease, transform 0.3s ease; }
    .btn:hover { background-image: linear-gradient(to right, #8a3ccf, #ff7b90); transform: scale(1.05); }
    .btn:focus { outline: 2px solid #7341b1; outline-offset: 3px; }
    @media (max-width:700px){ .grid { grid-template-columns: 1fr; } .grid label { padding-top: 2px; } .button-row { flex-direction: column; } }
  </style>
</head>
<body>
  <!-- Header -->
  <header>
    <nav>
      <ul>
        <li><a href="Learners_homepage.php"><img src="images/mindly.png" alt="Mindly Logo" /></a></li>
      </ul>
    </nav>
  </header>

  <!-- Main frame -->
  <div class="question-frame">
    <h3 class="frame-title">Recommend a Question</h3>

    <form id="recommendForm" method="POST" enctype="multipart/form-data">
      <div class="grid">
        <!-- Topic -->
        <label for="topic">Topic:</label>
        <select id="topic" name="topic" required>
          <option value="">Select Topic</option>
          <?php foreach ($topics as $topic) { ?>
            <option value="<?php echo $topic['id']; ?>"><?php echo $topic['topicName']; ?></option>
          <?php } ?>
        </select>

        <!-- Educator -->
        <label for="educator">Educator:</label>
        <select id="educator" name="educator" required>
          <option value="">Select Educator</option>
          <?php foreach ($educators as $edu) { ?>
            <option value="<?php echo $edu['id']; ?>"><?php echo $edu['firstName'] . ' ' . $edu['lastName']; ?></option>
          <?php } ?>
        </select>

        <!-- Question -->
        <label for="questionText">Question:</label>
        <textarea id="questionText" name="question" required placeholder="Type your question here..."></textarea>

        <!-- Upload Figure -->
        <label for="figure">Upload Question Figure:</label>
        <input type="file" id="figure" name="figure" accept="image/*">

        <!-- Answers -->
        <label for="ansA">Answer A:</label>
        <input type="text" id="ansA" name="choiceA" required>

        <label for="ansB">Answer B:</label>
        <input type="text" id="ansB" name="choiceB" required>

        <label for="ansC">Answer C:</label>
        <input type="text" id="ansC" name="choiceC" required>

        <label for="ansD">Answer D:</label>
        <input type="text" id="ansD" name="choiceD" required>

        <!-- Correct Answer -->
        <label for="correctSelect">Correct Answer:</label>
        <div class="correct-wrap">
          <select id="correctSelect" name="correct" required>
            <option value="">▼</option>
            <option value="A">A</option>
            <option value="B">B</option>
            <option value="C">C</option>
            <option value="D">D</option>
          </select>
        </div>
      </div>

      <div class="button-row">
        <button type="button" class="btn" onclick="goBack()">Back</button>
        <button type="submit" class="btn">Add</button>
      </div>
    </form>
  </div>

  <!-- Footer -->
  <footer>
    <p>&copy; 2025 Mindly. All rights reserved.</p>
  </footer>

  <script>
    window.addEventListener("load", function() {
      document.body.classList.add("loaded");
    });

    function goBack() {
      window.location.href = "Learners_homepage.php";
    }
  </script>
</body>
</html>
