<?php
// ------------------------------------------
// 1. INITIAL SETUP
// ------------------------------------------
session_start();
require_once 'reqLog.php';
require 'db.php';

// ------------------------------------------
// 2. SESSION AND USER VALIDATION
// ------------------------------------------
 if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: login.php?error=not_logged_in");
    exit();
}

if (strtolower($_SESSION['user_type']) !== 'learner') {
    header("Location: login.php?error=access_denied_learner");
    exit();
} 

$learner_id = $_SESSION['user_id'];

// ------------------------------------------
// 3. FETCH LEARNER INFO
// ------------------------------------------
$stmt = $connection->prepare("
    SELECT firstName, lastName, emailAddress, photoFileName 
    FROM user 
    WHERE id = ?
");
$stmt->bind_param("i", $learner_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "<p>No learner found!</p>";
    exit();
}
$learner = $result->fetch_assoc();
$stmt->close();

// ------------------------------------------
// 4. FETCH ALL TOPICS FOR FILTER
// ------------------------------------------
$sql_topics = "SELECT DISTINCT id, topicName FROM topic ORDER BY topicName";
$result_topics = $connection->query($sql_topics);

// ------------------------------------------
// 5. FETCH QUIZZES (GET or POST)
// ------------------------------------------
$selected_topic = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['topic_filter'])) {
    // POST: Filter by topic
    $selected_topic = $_POST['topic_filter'];
    
    if ($selected_topic === 'all') {
        // Show all quizzes
        $sql_quizzes = "
            SELECT q.id AS quizID, t.topicName, 
                   u.firstName AS eduFirst, u.lastName AS eduLast, u.photoFileName AS eduPhoto
            FROM quiz q
            JOIN topic t ON q.topicID = t.id
            JOIN user u ON q.educatorID = u.id
            ORDER BY t.topicName
        ";
        $stmt_quizzes = $connection->prepare($sql_quizzes);
    } else {
        // Filter by specific topic
        $sql_quizzes = "
            SELECT q.id AS quizID, t.topicName, 
                   u.firstName AS eduFirst, u.lastName AS eduLast, u.photoFileName AS eduPhoto
            FROM quiz q
            JOIN topic t ON q.topicID = t.id
            JOIN user u ON q.educatorID = u.id
            WHERE t.id = ?
            ORDER BY t.topicName
        ";
        $stmt_quizzes = $connection->prepare($sql_quizzes);
        $stmt_quizzes->bind_param("i", $selected_topic);
    }
} else {
    // GET: Show all quizzes
    $sql_quizzes = "
        SELECT q.id AS quizID, t.topicName, 
               u.firstName AS eduFirst, u.lastName AS eduLast, u.photoFileName AS eduPhoto
        FROM quiz q
        JOIN topic t ON q.topicID = t.id
        JOIN user u ON q.educatorID = u.id
        ORDER BY t.topicName
    ";
    $stmt_quizzes = $connection->prepare($sql_quizzes);
}

$stmt_quizzes->execute();
$result_quizzes = $stmt_quizzes->get_result();

// ------------------------------------------
// 6. FETCH RECOMMENDED QUESTIONS BY THIS LEARNER
// ------------------------------------------
$sql_recommend = "
    SELECT rq.id AS recID, rq.question AS questionText, rq.questionFigureFileName,
           rq.status, rq.comments,
           u.firstName AS eduFirst, u.lastName AS eduLast, u.photoFileName AS eduPhoto,
           t.topicName
    FROM recommendedquestion rq
    JOIN quiz q ON rq.quizID = q.id
    JOIN topic t ON q.topicID = t.id
    JOIN user u ON q.educatorID = u.id
    WHERE rq.learnerID = ?
    ORDER BY rq.id DESC
";
$stmt_recommend = $connection->prepare($sql_recommend);
$stmt_recommend->bind_param("i", $learner_id);
$stmt_recommend->execute();
$result_recommend = $stmt_recommend->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Learner's Homepage</title>
    <link rel="stylesheet" href="Learn&QF.css" />
    <link rel="stylesheet" href="style.css" />
</head>

<body onload="start()">
    <?php
    // Display success message
    if (isset($_GET['success']) && $_GET['success'] === 'feedback_submitted') {
        echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; text-align: center; border: 1px solid #c3e6cb; margin: 10px; border-radius: 5px;'>✓ Feedback submitted successfully! Thank you for your input.</div>";
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
                    <?php echo htmlspecialchars($learner['firstName'] . ' ' . $learner['lastName']); ?>
                </span>
            </span>
        </div>

        <div style="display: flex; justify-content: flex-end;">
            <a href="logout.php" style="text-decoration: underline;">log-out</a>
        </div>
    </section>

    <section>
        <div class="LearnerInfo">
            <div class="LearnerDetails">
                <p>Name: <?php echo htmlspecialchars($learner['firstName'] . ' ' . $learner['lastName']); ?></p>
                <p>Email: <?php echo htmlspecialchars($learner['emailAddress']); ?></p>
            </div>
            <div class="LearnerImg">
                <img
                    style="border: 0.3px; height: 100px; object-fit: contain"
                    src="uploads/<?php echo htmlspecialchars($learner['photoFileName']); ?>"
                    alt="profile picture"
                />
            </div>
        </div>
    </section>

    <main>
        <!-- ===================== -->
        <!-- FILTER FORM -->
        <!-- ===================== -->
        <div style="text-align: center; margin: 20px 0;">
            <form method="POST" action="Learners_homepage.php" style="display: inline-flex; gap: 10px; align-items: center;">
                <label for="topic_filter" style="font-weight: bold;">Filter by Topic:</label>
                <select name="topic_filter" id="topic_filter" style="padding: 8px; border-radius: 5px; border: 1px solid #ccc;">
                    <option value="all" <?php echo ($selected_topic === 'all' || $selected_topic === null) ? 'selected' : ''; ?>>All Topics</option>
                    <?php 
                    if ($result_topics->num_rows > 0):
                        while ($topic = $result_topics->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $topic['id']; ?>" <?php echo ($selected_topic == $topic['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($topic['topicName']); ?>
                        </option>
                    <?php 
                        endwhile;
                    endif;
                    ?>
                </select>
                <button type="submit" class="filter-btn" style="padding: 8px 20px; background-color: #5945ec; color: white; border: none; border-radius: 5px; cursor: pointer;">Filter</button>
            </form>
        </div>

        <!-- ===================== -->
        <!-- ALL AVAILABLE QUIZZES -->
        <!-- ===================== -->
        <table>
            <caption>All Available Quizzes</caption>
            <thead>
                <tr>
                    <th>Topic</th>
                    <th>Educator</th>
                    <th>Number of Questions</th>
                    <th></th>
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
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($quiz['topicName']); ?></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 15px; justify-content: center;">
                                <span><?php echo htmlspecialchars($quiz['eduFirst'] . ' ' . $quiz['eduLast']); ?></span>
                                <img
                                    style="border-radius: 50%; height: 40px; width: 40px; object-fit: cover;"
                                    src="uploads/<?php echo htmlspecialchars($quiz['eduPhoto']); ?>"
                                    alt="educator picture"
                                />
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($countQ); ?></td>
                        <td>
                            <?php if ($countQ > 0): ?>
                                <a href="TakeAquiz.php?quizID=<?php echo htmlspecialchars($quizID); ?>">
                                    <button class="take-quiz-btn">Take Quiz</button>
                                </a>
                            <?php else: ?>
                                <span style="color: #999;">No questions</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" style="text-align:center;">No quizzes found.</td></tr>
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
                    <th>Educator</th>
                    <th>Question</th>
                    <th>Status</th>
                    <th>Comments</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result_recommend->num_rows > 0): ?>
                <?php while ($rec = $result_recommend->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($rec['topicName']); ?></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 15px; justify-content: center;">
                            <span><?php echo htmlspecialchars($rec['eduFirst'] . ' ' . $rec['eduLast']); ?></span>
                            <img
                                style="border-radius: 50%; height: 40px; width: 40px; object-fit: cover;"
                                src="uploads/<?php echo htmlspecialchars($rec['eduPhoto']); ?>"
                                alt="educator picture"
                            />
                        </div>
                    </td>
                    <td>
                        <?php if (!empty($rec['questionFigureFileName']) && trim($rec['questionFigureFileName']) !== ''): ?>
                            <img
                                style="height: 100px; object-fit: contain; margin-left: 20svh; box-shadow: 0px 3px 17px rgb(61, 61, 61);"
                                src="uploads/<?php echo htmlspecialchars($rec['questionFigureFileName']); ?>"
                                alt="question picture"
                            /><br /><br />
                        <?php endif; ?>
                        <?php echo htmlspecialchars($rec['questionText']); ?>
                    </td>
                    <td><?php echo htmlspecialchars(ucfirst($rec['status'])); ?></td>
                    <td><?php echo !empty($rec['comments']) ? htmlspecialchars($rec['comments']) : 'No comments yet'; ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center;">No recommended questions yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        
        <div style="text-align: center; margin: 30px 0;">
            <form action="recommended.php"> 
                <button class="recommend-btn" type="submit">Recommend a Question</button>
            </form>
        </div>
    </main>
    
    <br />
    <div class="footer-container">
        <footer>
            <p>&copy; 2025 Mindly. All rights reserved.</p>
        </footer>
    </div>
    
    <script>
    function start() {
        document.body.style.opacity = "1";
        
        // Typing animation with cursor
        const el = document.querySelector(".welcome");
        const text = "<?php echo 'Welcome ' . htmlspecialchars($learner['firstName'] . ' ' . $learner['lastName']); ?>";
        const textLength = text.length;
        
        // Clear existing content and set initial state
        el.textContent = "";
        el.style.borderRight = "1px solid #000";
        el.style.width = "0";
        
        // Create dynamic CSS keyframes
        const style = document.createElement("style");
        style.innerHTML = `
            @keyframes typing {
                from { width: 0; }
                to { width: ${textLength}ch; }
            }
            
            @keyframes blinkCursor {
                50% { border-color: transparent; }
            }
            
            .welcome {
                animation: typing 2s steps(${textLength}) 1s 1 forwards, 
                           blinkCursor 0.75s step-end infinite;
                white-space: nowrap;
                overflow: hidden;
            }
        `;
        document.head.appendChild(style);
        
        // Set the text content after a delay to match animation
        setTimeout(() => {
            el.textContent = text;
        }, 1000);
        
        // Remove cursor after animation ends
        el.addEventListener("animationend", () => {
            el.style.borderRight = "none";
        });
    }
    </script>
</body>
</html>
