<?php
// ------------------------------------------
// 1. INITIAL SETUP AND REVIEW HANDLING
// ------------------------------------------
session_start();
include 'db.php';

// --- Handle Review Submission (Before any HTML output) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rec_id'])) {
    $recID = $_POST['rec_id'];
    $decision = $_POST['decision']; // 'yes' or 'no'
    $comment = $_POST['comments'] ?? '';

    $approvedValue = ($decision === 'yes') ? 1 : 0;

    $stmt = $connection->prepare("UPDATE RecommendedQuestion SET approved = ?, comments = ? WHERE id = ?");
    $stmt->bind_param("isi", $approvedValue, $comment, $recID);
    $stmt->execute();
    $stmt->close();

    header("Location: Educators homepage.php?success=review_updated");
    exit();
}

// ------------------------------------------
// 2. SESSION AND USER VALIDATION
// ------------------------------------------
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: login.php?error=not_logged_in");
    exit();
}

if ($_SESSION['user_type'] !== 'educator') {
    header("Location: login.php?error=access_denied");
    exit();
}

$educator_id = $_SESSION['user_id'];

// ------------------------------------------
// 3. FETCH EDUCATOR INFO
// ------------------------------------------
$stmt = $connection->prepare("
    SELECT firstName, lastName, emailAddress, photoFileName 
    FROM User 
    WHERE id = ?
");
$stmt->bind_param("i", $educator_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "<p>No educator found!</p>";
    exit();
}
$educator = $result->fetch_assoc();
$stmt->close();

// ------------------------------------------
// 4. FETCH EDUCATOR'S QUIZZES
// ------------------------------------------
$sql_quizzes = "
    SELECT q.id AS quizID, t.topicName
    FROM Quiz q
    JOIN Topic t ON q.topicID = t.id
    WHERE q.educatorID = ?
";
$stmt = $connection->prepare($sql_quizzes);
$stmt->bind_param("i", $educator_id);
$stmt->execute();
$result_quizzes = $stmt->get_result();

// ------------------------------------------
// 5. FETCH RECOMMENDED QUESTIONS
// ------------------------------------------
$sql_recommend = "
    SELECT rq.id AS recID, rq.question AS questionText, rq.questionFigureFileName,
           rq.status, rq.comments,
           u.firstName AS learnerFirst, u.lastName AS learnerLast, u.photoFileName AS learnerPhoto,
           t.topicName
    FROM RecommendedQuestion rq
    JOIN Quiz q ON rq.quizID = q.id
    JOIN Topic t ON q.topicID = t.id
    JOIN User u ON rq.learnerID = u.id
    WHERE q.educatorID = ? AND rq.status = 'Pending'
";
$stmt_recommend = $connection->prepare($sql_recommend);
$stmt_recommend->bind_param("i", $educator_id);
$stmt_recommend->execute();
$result_recommend = $stmt_recommend->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Educator's Homepage</title>
  <link rel="stylesheet" href="Educators homepage.css" />
  <link rel="stylesheet" href="style.css" />
</head>

<body onload="start()">
<?php
// Success message after redirect
if (isset($_GET['success']) && $_GET['success'] === 'review_updated') {
    echo "<script>alert('Review updated successfully!');</script>";
}
?>

<header>
  <nav>
    <ul>
      <li><a href="index.php"><img src="images/mindly.png" alt="Mindly Logo" /></a></li>
    </ul>
  </nav>
</header>

<section>
  <div class="topHeader">
    <span class="welcome" style="color: #a654e6">
      Welcome <span style="color: #5945ec">
        <?php echo htmlspecialchars($educator['firstName'] . ' ' . $educator['lastName']); ?>
      </span>
    </span>
  </div>

  <div style="display: flex; justify-content: flex-end;">
    <a href="index.php" style="text-decoration: underline;">log-out</a>
  </div>
</section>

<section>
  <div class="EduInfo">
    <div class="EduDetails">
      <p>Name: <?php echo htmlspecialchars($educator['firstName'] . ' ' . $educator['lastName']); ?></p>
      <p>Email: <?php echo htmlspecialchars($educator['emailAddress']); ?></p>
    </div>
    <div class="EduImg">
      <img
        style="border: 0.3px; height: 100px; object-fit: contain"
        src="uploads/<?php echo htmlspecialchars($educator['photoFileName']); ?>"
        alt="profile picture"
      />
    </div>
  </div>
</section>

<main>
  <!-- ===================== -->
  <!-- EDUCATOR'S QUIZZES -->
  <!-- ===================== -->
  <table>
    <caption>Your Quizzes</caption>
    <thead>
      <tr>
        <th>Topic</th>
        <th>Number Of Questions</th>
        <th>Quiz Statistics</th>
        <th>Quiz Feedback</th>
      </tr>
    </thead>

    <tbody>
    <?php if ($result_quizzes->num_rows > 0): ?>
      <?php while ($quiz = $result_quizzes->fetch_assoc()): ?>
        <?php
          $quizID = $quiz['quizID'];

          // Count number of questions
          $stmtQ = $connection->prepare("SELECT COUNT(*) AS total FROM QuizQuestion WHERE quizID = ?");
          $stmtQ->bind_param("i", $quizID);
          $stmtQ->execute();
          $countQ = $stmtQ->get_result()->fetch_assoc()['total'] ?? 0;

          // Average score
          $stmtScore = $connection->prepare("SELECT AVG(score) AS avgScore FROM TakenQuiz WHERE quizID = ?");
          $stmtScore->bind_param("i", $quizID);
          $stmtScore->execute();
          $avgScore = $stmtScore->get_result()->fetch_assoc()['avgScore'] ?? null;
          $avgScoreDisplay = $avgScore ? round($avgScore, 2) . '%' : 'No data';

          // Average rating
          $stmtRating = $connection->prepare("SELECT AVG(rating) AS avgRating FROM QuizFeedback WHERE quizID = ?");
          $stmtRating->bind_param("i", $quizID);
          $stmtRating->execute();
          $avgRating = $stmtRating->get_result()->fetch_assoc()['avgRating'] ?? null;
          $avgRatingDisplay = $avgRating ? round($avgRating, 1) . '/5' : 'No feedback';

          // Comments existence
          $stmtComments = $connection->prepare("SELECT COUNT(*) AS total FROM QuizFeedback WHERE quizID = ? AND comments IS NOT NULL AND comments <> ''");
          $stmtComments->bind_param("i", $quizID);
          $stmtComments->execute();
          $hasComments = $stmtComments->get_result()->fetch_assoc()['total'] ?? 0;

          $commentsLink = ($hasComments > 0)
              ? "<a href='comments.php?quizID=$quizID'>View Comments</a>"
              : "No comments yet";
        ?>

        <tr>
          <td><a href="Quiz page.php?quizID=<?php echo htmlspecialchars($quizID); ?>"><?php echo htmlspecialchars($quiz['topicName']); ?></a></td>
          <td><?php echo htmlspecialchars($countQ); ?></td>
          <td>
            Number of Quiz Takers: <?php echo ($avgScore ? 'Available' : 'No takers yet'); ?><br>
            Average Score: <?php echo htmlspecialchars($avgScoreDisplay); ?>
          </td>
          <td>
            Average Rating: <?php echo htmlspecialchars($avgRatingDisplay); ?><br>
            <?php echo $commentsLink; ?>
          </td>
        </tr>

      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="4" style="text-align:center;">No quizzes found for this educator.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>

  <!-- ===================== -->
  <!-- RECOMMENDED QUESTIONS -->
  <!-- ===================== -->
  <table class="recommendation-table">
    <caption>Question Recommendations</caption>
    <thead>
      <tr>
        <th>Topic</th>
        <th>Learner</th>
        <th>Question</th>
        <th>Review</th>
      </tr>
    </thead>

    <tbody>
    <?php if ($result_recommend->num_rows > 0): ?>
      <?php while ($rec = $result_recommend->fetch_assoc()): ?>
      <tr>
        <td><?php echo htmlspecialchars($rec['topicName']); ?></td>
        <td>
          <div style="display: flex; align-items: center; gap: 10px; justify-content: center;">
            <span><?php echo htmlspecialchars($rec['learnerFirst'] . ' ' . $rec['learnerLast']); ?></span>
            <img src="uploads/<?php echo htmlspecialchars($rec['learnerPhoto']); ?>" 
                 alt="Learner photo"
                 style="border-radius: 50%; height: 40px; width: 40px; object-fit: cover;">
          </div>
        </td>
        <td>
          <?php if (!empty($rec['questionFigureFileName'])): ?>
            <img src="uploads/<?php echo htmlspecialchars($rec['questionFigureFileName']); ?>" 
                 alt="Question Image"
                 style="height: 100px; object-fit: contain; margin-bottom: 10px; box-shadow: 0px 3px 17px rgb(61,61,61);">
          <?php endif; ?>
          <p><?php echo htmlspecialchars($rec['questionText']); ?></p>
        </td>
        <td>
           <form action="review_recommendation.php" method="POST" style="text-align:center;">
                  <input type="hidden" name="recID" value="<?php echo $rec['recID']; ?>">

                  <div style="display: flex; align-items: center; gap: 10px; justify-content: center;">
                    <label class="comment-label">comment:</label>
                    <textarea class="comment-textarea" name="comments" rows="2"><?php echo htmlspecialchars($rec['comments'] ?? ''); ?></textarea>
                  </div>

                  <br />

                  <ul style="list-style-type: none; padding: 0; display: flex; gap: 10px; justify-content: center; align-items: center;">
                    <li>Approve:</li>
                    <li>
                      <label><input name="status" type="radio" value="approved" required /> Yes</label>
                    </li>
                    <li>
                      <label><input name="status" type="radio" value="disapproved" required /> No</label>
                    </li>
                  </ul>

                  <br />
                  <button type="submit" style="margin-left: 300px;">Submit</button>
                </form>
        </td>
      </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="4" style="text-align:center;">No pending recommendations available.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</main>
<br/>
<div class="footer-container">
  <footer>
    <p>&copy; 2025 Mindly. All rights reserved.</p>
  </footer>
</div>

<script>
function start() {
    document.body.style.opacity = "1";
    const el = document.querySelector(".welcome");
    const text = "<?php echo 'Welcome ' . htmlspecialchars($educator['firstName'] . ' ' . $educator['lastName']); ?>";
    const textLength = text.length;
    el.textContent = "";
    el.style.borderRight = "1px solid #000";
    el.style.width = "0";
    const style = document.createElement("style");
    style.innerHTML = `
        @keyframes typing { from { width: 0; } to { width: ${textLength}ch; } }
        @keyframes blinkCursor { 50% { border-color: transparent; } }
        .welcome {
            animation: typing 2s steps(${textLength}) 1s 1 forwards, 
                       blinkCursor 0.75s step-end infinite;
            white-space: nowrap;
            overflow: hidden;
        }`;
    document.head.appendChild(style);
    setTimeout(() => { el.textContent = text; }, 1000);
    el.addEventListener("animationend", () => { el.style.borderRight = "none"; });
}
</script>
</body>
</html>
