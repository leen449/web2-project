<?php
// ------------------------------------------
// 1. INITIAL SETUP
// ------------------------------------------
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('session.cookie_path', '/');
session_start();
include 'db.php';

// ------------------------------------------
// 2. SESSION AND ROLE VALIDATION
// ------------------------------------------
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: login.php?error=not_logged_in");
    exit();
}
if (strtolower($_SESSION['user_type']) !== 'educator') {
    header("Location: login.php?error=access_denied");
    exit();
}

// ------------------------------------------
// 3. VALIDATE quizID AND FETCH QUIZ INFO
// ------------------------------------------
if (!isset($_GET['quizID']) || empty($_GET['quizID'])) {
    die("<p style='color:red; text-align:center;'>Invalid quiz request.</p>");
}

$quizID = intval($_GET['quizID']);

// Get quiz topic name
$stmt = $connection->prepare("
    SELECT t.topicName 
    FROM quiz q
    JOIN topic t ON q.topicID = t.id
    WHERE q.id = ? AND q.educatorID = ?
");
$stmt->bind_param("ii", $quizID, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("<p style='color:red; text-align:center;'>Quiz not found.</p>");
}
$quiz = $result->fetch_assoc();
$stmt->close();

// ------------------------------------------
// 4. FETCH QUESTIONS FOR THIS QUIZ
// ------------------------------------------
$sql = "SELECT id, question, questionFigureFileName, answerA, answerB, answerC, answerD, correctAnswer 
        FROM quizquestion 
        WHERE quizID = ?
        ORDER BY id ASC";

$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $quizID);
$stmt->execute();
$result_questions = $stmt->get_result();

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'deleted') echo "<script>alert('Question deleted successfully!');</script>";
    elseif ($_GET['success'] === 'added') echo "<script>alert('Question added successfully!');</script>";
    elseif ($_GET['success'] === 'edited') echo "<script>alert('Question updated successfully!');</script>";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quiz Page</title>
  <link rel="stylesheet" href="Educators homepage.css">
  <link rel="stylesheet" href="style.css">
</head>
<body onload="document.body.style.opacity='1'">


  <header>
    <nav>
      <ul>
        <li><a href="Educators_homepage.php"><img src="images/mindly.png" alt="Mindly Logo"></a></li>
      </ul>
    </nav>
  </header>

  <main>
    <!-- Quiz Title and Add Button -->
    <table>
      <caption style="text-align: left;">
        <span style="float: left; font-weight: bolder; color: #a654e6; font-size: 2rem;">
          Quiz for <?php echo htmlspecialchars($quiz['topicName']); ?>
        </span>
        <a href="Addquestion.php?quizID=<?php echo $quizID; ?>" 
           style="float: right; font-size: medium; font-weight: normal; margin-top: 30px;">
          Add New Question
        </a>
      </caption>
      <thead>
        <tr>
          <th>Question</th>
          <th>Edit</th>
          <th>Delete</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result_questions->num_rows > 0): ?>
          <?php while ($row = $result_questions->fetch_assoc()): ?>
            <tr>
              <td>
                <?php if (!empty($row['questionFigureFileName'])): ?>
                  <img style="height: 100px; object-fit: contain; margin-left: 80vh; box-shadow: 0px 3px 17px rgb(61, 61, 61);"
                       src="uploads/<?php echo htmlspecialchars($row['questionFigureFileName']); ?>"
                       alt="question image"><br>
                <?php endif; ?>

                <?php echo htmlspecialchars($row['question']); ?><br>
                <ol>
                  <li <?php if ($row['correctAnswer'] === 'A') echo 'style="background-color: lightgreen;"'; ?>>
                    <?php echo htmlspecialchars($row['answerA']); ?>
                  </li>
                  <li <?php if ($row['correctAnswer'] === 'B') echo 'style="background-color: lightgreen;"'; ?>>
                    <?php echo htmlspecialchars($row['answerB']); ?>
                  </li>
                  <li <?php if ($row['correctAnswer'] === 'C') echo 'style="background-color: lightgreen;"'; ?>>
                    <?php echo htmlspecialchars($row['answerC']); ?>
                  </li>
                  <li <?php if ($row['correctAnswer'] === 'D') echo 'style="background-color: lightgreen;"'; ?>>
                    <?php echo htmlspecialchars($row['answerD']); ?>
                  </li>
                </ol>
              </td>

              <td>
                <a href="EditQuestion.php?questionID=<?php echo htmlspecialchars($row['id']); ?>">Edit</a>
              </td>
              <td>
                <a href="DeleteQuestion.php?questionID=<?php echo htmlspecialchars($row['id']); ?>&quizID=<?php echo htmlspecialchars($quizID); ?>"
                   onclick="return confirm('Are you sure you want to delete this question?');">
                   Delete
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="3" style="text-align:center;">No questions found for this quiz.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </main>
    <br>
    <br>

  <div class="footer-container">
    <footer>
      <p>&copy; 2025 Mindly. All rights reserved.</p>
    </footer>
  </div>
</body>
</html>
