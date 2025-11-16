<?php
// ------------------------------------------
// 1. INITIAL SETUP
// ------------------------------------------
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
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
// 5. FETCH RECOMMENDED QUESTIONS
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

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <link rel="stylesheet" href="Learn&QF.css" />
    <link rel="stylesheet" href="style.css" />
</head>

<body onload="start()">
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
    <!-- FILTER DROPDOWN (AJAX) -->
    <!-- ===================== -->
    <div style="text-align: center; margin: 20px 0;">
        <label for="topic_filter" style="font-weight: bold;">Filter by Topic:</label>
        <select id="topic_filter" style="padding: 8px; border-radius: 5px; border: 1px solid #ccc;">
            <option value="all">All Topics</option>

            <?php 
            if ($result_topics->num_rows > 0):
                while ($topic = $result_topics->fetch_assoc()): 
            ?>
                <option value="<?php echo $topic['id']; ?>">
                    <?php echo htmlspecialchars($topic['topicName']); ?>
                </option>
            <?php 
                endwhile;
            endif;
            ?>
        </select>
    </div>

    <!-- ===================== -->
    <!-- QUIZ TABLE (AJAX UPDATES THIS) -->
    <!-- ===================== -->
    <table id="quizTable">
        <caption>All Available Quizzes</caption>
        <thead>
            <tr>
                <th>Topic</th>
                <th>Educator</th>
                <th>Number of Questions</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="quizBody">
            <!-- AJAX INSERTS ROWS HERE -->
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
                        <?php if (!empty($rec['questionFigureFileName'])): ?>
                            <img
                                style="height: 100px; object-fit: contain; box-shadow: 0px 3px 17px rgb(61, 61, 61);"
                                src="uploads/<?php echo htmlspecialchars($rec['questionFigureFileName']); ?>"
                                alt="question picture"
                            /><br><br>
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

<div class="footer-container">
    <footer>
        <p>&copy; 2025 Mindly. All rights reserved.</p>
    </footer>
</div>


<!-- ====================================== -->
<!-- AJAX SCRIPT TO LOAD QUIZZES DYNAMICALLY -->
<!-- ====================================== -->
<script>
$(document).ready(function () {

    // Load ALL quizzes initially
    loadQuizzes("all");

    // When user selects a topic
    $("#topic_filter").change(function () {
        loadQuizzes($(this).val());
    });

    // AJAX function
    function loadQuizzes(topic) {
        $.ajax({
            url: "get_quizzes_ajax.php",
            type: "GET",
            data: { topic: topic },
            dataType: "json",

            success: function (response) {
                $("#quizBody").empty();

                if (response.length === 0) {
                    $("#quizBody").append(
                        "<tr><td colspan='4' style='text-align:center;'>No quizzes found.</td></tr>"
                    );
                    return;
                }

                $.each(response, function (i, quiz) {
                    let row = `
                        <tr>
                            <td>${quiz.topicName}</td>
                            <td>
                                <div style="display:flex; align-items:center; justify-content:center; gap:15px;">
                                    <span>${quiz.educator}</span>
                                    <img src="uploads/${quiz.eduPhoto}" 
                                        style="border-radius: 50%; height: 40px; width: 40px; object-fit: cover;">
                                </div>
                            </td>
                            <td>${quiz.questionCount}</td>
                            <td>
                                ${quiz.questionCount > 0
                                    ? `<a href="TakeAquiz.php?quizID=${quiz.quizID}">
                                         <button class="take-quiz-btn">Take Quiz</button>
                                       </a>`
                                    : "<span style='color:#999;'>No questions</span>"
                                }
                            </td>
                        </tr>
                    `;

                    $("#quizBody").append(row);
                });
            },
            error: function () {
                alert("Error loading quizzes.");
            }
        });
    }
});
</script>
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
