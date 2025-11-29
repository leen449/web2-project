<?php
    ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'reqLog.php';


require 'db.php';


//  Check quizID from URL ( req 1 )
if (!isset($_GET['quizID'])) {
    die("Error: Quiz ID not provided.");
}
$quizID = intval($_GET['quizID']);

// Retrieve quiz info( req 2 )
$quizQuery = "
    SELECT q.id AS quizID,
           t.topicName,
           CONCAT(u.firstName, ' ', u.lastName) AS educatorName
    FROM quiz q
    JOIN topic t ON q.topicID = t.id
    JOIN user u  ON q.educatorID = u.id
    WHERE q.id = $quizID
";
$quizResult = mysqli_query($connection, $quizQuery);
if (mysqli_num_rows($quizResult) == 0) {
    die('Error: Quiz not found.');
}
$quiz = mysqli_fetch_assoc($quizResult);


// Retrieve questions ( req 3 )
$questionQuery  = "SELECT * FROM quizquestion WHERE quizID = $quizID";
$questionResult = mysqli_query($connection, $questionQuery);

if (mysqli_num_rows($questionResult) == 0) {
    die("Error: No questions found for this quiz.");
}


$questions = [];
while ($row = mysqli_fetch_assoc($questionResult)) {
    $questions[] = $row;
}

// Randomly select 5 (or all if ≤5) ( req 3)
shuffle($questions);
$selectedQuestions = array_slice($questions, 0, min(5, count($questions)));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Take Quiz - <?php echo htmlspecialchars($quiz['topicName']); ?></title>
  <link rel="stylesheet" href="style.css" />
  <style>
    body { font-family: Arial, sans-serif; margin: 0; opacity: 0; transition: opacity 1s ease-in-out; }
    .quiz-container { max-width: 800px; margin: 40px auto; background: #fff; padding: 20px; border: 2px solid #ccc; border-radius: 10px; box-shadow: 0px 3px 15px rgba(0,0,0,0.1);}
    h2 { text-align: center; color: #a654e6; }
    .question-box { border: 1px solid #aaa; padding: 15px; margin: 20px 0; border-radius: 6px; }
    .question-box img { display: block; max-height: 120px; margin: 0 auto 10px; box-shadow: 0px 3px 10px rgba(0,0,0,0.2);}
    .answers label { display: block; margin: 5px 0; cursor: pointer; }
    .done-btn { display: inline-block; padding: 12px 25px; border-radius: 8px; border: none; cursor: pointer; font-size: 16px; font-weight: bold; color: #fff; background-image: linear-gradient(to right, #7341b1, #ee7979); transition: background 0.3s ease, transform 0.3s ease;}
    .done-btn:hover { background-image: linear-gradient(to right, #8a3ccf, #ff7b90); transform: scale(1.05);}
    .done-btn:focus { outline: 2px solid #7341b1; outline-offset: 3px; }
  </style>
</head>
<body onload="document.body.style.opacity='1'">

  <header>
    <nav>
      <ul>
        <li><a href="Learners_homepage.php"><img src="images/mindly.png" alt="Mindly Logo" /></a></li>
      </ul>
    </nav>
  </header>

  <div class="quiz-container">
    <h2>Quiz in <?php echo htmlspecialchars($quiz['topicName']); ?></h2>
    <p><strong>Educator:</strong> <?php echo htmlspecialchars($quiz['educatorName']); ?></p>
 <!-- request to score and feedback ( req 5 )  -->
    <form action="Quiz score and feedback.php" method="post">
      <!-- Hidden quiz ID ( req 4 )  -->
      <input type="hidden" name="quizID" value="<?php echo $quizID; ?>">

      <?php //( req 3 )
      $qNum = 1;
      foreach ($selectedQuestions as $q) {
          echo '<div class="question-box">';
          echo "<h3>Question $qNum</h3>";

          if (!empty($q['questionFigureFileName'])) {
              echo "<img src='uploads/" . htmlspecialchars($q['questionFigureFileName']) . "' alt='Question Image'>";
          }

          echo "<p>" . htmlspecialchars($q['question']) . "</p>";

          // Hidden input for question ID  ( req 4 )
          echo "<input type='hidden' name='questionIDs[]' value='" . $q['id'] . "'>";

         
          echo '<div class="answers">';
          echo "<label><input type='radio' name='question_" . $q['id'] . "' value='A'> A) " . htmlspecialchars($q['answerA']) . "</label>";
          echo "<label><input type='radio' name='question_" . $q['id'] . "' value='B'> B) " . htmlspecialchars($q['answerB']) . "</label>";
          echo "<label><input type='radio' name='question_" . $q['id'] . "' value='C'> C) " . htmlspecialchars($q['answerC']) . "</label>";
          echo "<label><input type='radio' name='question_" . $q['id'] . "' value='D'> D) " . htmlspecialchars($q['answerD']) . "</label>";
          echo '</div></div>';

          $qNum++;
      }
      ?>

      <button type="submit" class="done-btn">Done</button>
    </form>
  </div>

  <footer>
    <p style="text-align:center; margin:20px;">&copy; 2025 Mindly. All rights reserved.</p>
  </footer>
</body>
</html>
