<?php
// ------------------------------------------
// 1. INITIAL SETUP
// ------------------------------------------
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('session.cookie_path', '/');
session_start();
//require_once 'reqLog.php';
require 'db.php';

// ------------------------------------------
// 2. SESSION AND USER VALIDATION
// ------------------------------------------
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: login.php?error=not_logged_in");
    exit();
}

if (strtolower($_SESSION['user_type']) !== 'educator') {
    header("Location: login.php?error=access_denied");
    exit();
}

$educator_id = $_SESSION['user_id'];

// ------------------------------------------
// 3. FETCH EDUCATOR INFO
// ------------------------------------------
$stmt = $connection->prepare("
    SELECT firstName, lastName, emailAddress, photoFileName 
    FROM user 
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
// 4. FETCH EDUCATOR’S QUIZZES
// ------------------------------------------
$sql_quizzes = "
    SELECT q.id AS quizID, t.topicName
    FROM quiz q
    JOIN topic t ON q.topicID = t.id
    WHERE q.educatorID = ?
";
$stmt = $connection->prepare($sql_quizzes);
$stmt->bind_param("i", $educator_id);
$stmt->execute();
$result_quizzes = $stmt->get_result();

// ------------------------------------------
// 5. FETCH RECOMMENDED QUESTIONS (PENDING ONLY)
// ------------------------------------------
$sql_recommend = "
    SELECT rq.id AS recID, rq.question AS questionText, rq.questionFigureFileName,
           rq.status, rq.comments,
           u.firstName AS learnerFirst, u.lastName AS learnerLast, u.photoFileName AS learnerPhoto,
           t.topicName
    FROM recommendedquestion rq
    JOIN quiz q ON rq.quizID = q.id
    JOIN topic t ON q.topicID = t.id
    JOIN user u ON rq.learnerID = u.id
    WHERE q.educatorID = ? AND rq.status = 'pending'
    ORDER BY rq.id DESC
";
$stmt_recommend = $connection->prepare($sql_recommend);
$stmt_recommend->bind_param("i", $educator_id);
$stmt_recommend->execute();
$result_recommend = $stmt_recommend->get_result();

/*=========================
 alert action 
  ========================
 */

?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Educator's Homepage</title>
    <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="Educators homepage.css" />

</head>

<body onload="start()">
    <?php if (isset($_GET['success']) && $_GET['success'] === 'deleted') {
    echo "<script>
        alert('Question deleted successfully!');
    </script>";
}?>
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
            <a href="logout.php" style="text-decoration: underline;">Log out</a>
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
        <!-- EDUCATOR’S QUIZZES -->
        <!-- ===================== -->
        <table>
            <caption>Your Quizzes</caption>
            <thead>
                <tr>
                    <th>Topic</th>
                    <th>Number of Questions</th>
                    <th>Average Score</th>
                    <th>Average Rating</th>
                    <th>Comments</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result_quizzes->num_rows > 0): ?>
                <?php while ($quiz = $result_quizzes->fetch_assoc()): ?>
                    <?php
                        $quizID = $quiz['quizID'];

                        // Count number of questions
                        $stmtQ = $connection->prepare("SELECT COUNT(*) AS total FROM quizquestion WHERE quizID = ?");
                        $stmtQ->bind_param("i", $quizID);
                        $stmtQ->execute();
                        $countQ = $stmtQ->get_result()->fetch_assoc()['total'] ?? 0;
                        $stmtQ->close();

                        // Average score
                        $stmtScore = $connection->prepare("SELECT AVG(score) AS avgScore FROM takenquiz WHERE quizID = ?");
                        $stmtScore->bind_param("i", $quizID);
                        $stmtScore->execute();
                        $avgScore = $stmtScore->get_result()->fetch_assoc()['avgScore'] ?? null;
                        $stmtScore->close();
                        $avgScoreDisplay = $avgScore ? round($avgScore, 2) . '%' : 'Quiz not taken yet';

                        // Average rating
                        $stmtFeedback = $connection->prepare("SELECT AVG(rating) AS avgRating FROM quizfeedback WHERE quizID = ?");
                        $stmtFeedback->bind_param("i", $quizID);
                        $stmtFeedback->execute();
                        $avgRating = $stmtFeedback->get_result()->fetch_assoc()['avgRating'] ?? null;
                        $stmtFeedback->close();
                        $avgRatingDisplay = $avgRating ? round($avgRating, 1) . '/5' : 'No feedback yet';

                        // Comments link (only if comments exist)
                        $stmtComments = $connection->prepare("SELECT COUNT(*) AS total FROM quizfeedback WHERE quizID = ? AND comments IS NOT NULL AND comments <> ''");
                        $stmtComments->bind_param("i", $quizID);
                        $stmtComments->execute();
                        $commentsCount = $stmtComments->get_result()->fetch_assoc()['total'] ?? 0;
                        $stmtComments->close();
                        $commentsLink = ($commentsCount > 0)
                            ? "<a href='comments.php?quizID=$quizID'>View Comments</a>"
                            : "No comments yet";
                    ?>
                    <tr>
                        <td>
                            <a href="Quiz_page.php?quizID=<?php echo htmlspecialchars($quizID); ?>">
                                <?php echo htmlspecialchars($quiz['topicName']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($countQ); ?></td>
                        <td><?php echo htmlspecialchars($avgScoreDisplay); ?></td>
                        <td><?php echo htmlspecialchars($avgRatingDisplay); ?></td>
                        <td><?php echo $commentsLink; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center;">No quizzes found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>

        <!-- ===================== -->
        <!-- RECOMMENDED QUESTIONS -->
        <!-- ===================== -->
        <table class="recommendation-table">
            <caption>Recommended Questions</caption>
            <thead>
                <tr>
                    <th>Topic</th>
                    <th>Learner</th>
                    <th>Question</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result_recommend->num_rows > 0): ?>
                <?php while ($rec = $result_recommend->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($rec['topicName']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($rec['learnerFirst'] . ' ' . $rec['learnerLast']); ?>
                            <br>
                            <img style="border-radius: 50%; height: 40px; width: 40px; object-fit: cover;"
                                 src="uploads/<?php echo htmlspecialchars($rec['learnerPhoto']); ?>"
                                 alt="Learner photo">
                        </td>
                        <td>
                            <?php echo htmlspecialchars($rec['questionText']); ?>
                            <?php if (!empty($rec['questionFigureFileName'])): ?>
                                <br><img style="height: 70px; object-fit: contain; margin-top: 10px; box-shadow: 0px 3px 17px rgb(61,61,61); margin-left: 150px;"
                                         src="uploads/<?php echo htmlspecialchars($rec['questionFigureFileName']); ?>"
                                         alt="Question image">
                            <?php endif; ?>
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
                  <button type="submit" style="margin-left: 80px; margin-bottom: 10px;">Submit</button>
                </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" style="text-align:center;">No recommended questions yet.</td></tr>
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
