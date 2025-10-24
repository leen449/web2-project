<?php
// ------------------------------------------
// 1. INITIAL SETUP
// ------------------------------------------
session_start();
include 'db.php';

// ------------------------------------------
// 2. SESSION AND USER VALIDATION
// ------------------------------------------
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: login.php?error=not_logged_in");
    exit();
}

if ($_SESSION['user_type'] !== 'Learner') {
    header("Location: login.php?error=access_denied_learner");
    exit();
}

$learner_id = $_SESSION['user_id'];

// ------------------------------------------
// 3. HANDLE FEEDBACK SUBMISSION (if POST with feedback data)
// ------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $quizID = $_POST['quizID'] ?? null;
    $rating = $_POST['rating'] ?? null;
    $comments = $_POST['comments'] ?? '';
    
    if (!empty($quizID) && !empty($rating)) {
        $stmt = $connection->prepare("INSERT INTO quizfeedback (quizID, rating, comments) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $quizID, $rating, $comments);
        $stmt->execute();
        $stmt->close();
        
        // Redirect to learner's homepage with success message
        header("Location: Learners_homepage.php?success=feedback_submitted");
        exit();
    }
}

// ------------------------------------------
// 4. GET QUIZ ID AND VALIDATE
// ------------------------------------------
if (!isset($_POST['quizID']) || empty($_POST['quizID'])) {
    header("Location: Learners_homepage.php?error=no_quiz_selected");
    exit();
}

$quizID = $_POST['quizID'];

// ------------------------------------------
// 5. FETCH QUIZ DETAILS (Topic and Educator)
// ------------------------------------------
$stmt = $connection->prepare("
    SELECT q.id, t.topicName, 
           u.firstName AS eduFirst, u.lastName AS eduLast, u.photoFileName AS eduPhoto
    FROM quiz q
    JOIN topic t ON q.topicID = t.id
    JOIN user u ON q.educatorID = u.id
    WHERE q.id = ?
");
$stmt->bind_param("i", $quizID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: Learners_homepage.php?error=quiz_not_found");
    exit();
}

$quiz = $result->fetch_assoc();
$stmt->close();

// ------------------------------------------
// 6. CALCULATE SCORE
// ------------------------------------------
$score = 0;
$totalQuestions = 0;
$scorePercentage = 0;

// Get all questions for this quiz
$stmt = $connection->prepare("SELECT id, correctAnswer FROM quizquestion WHERE quizID = ?");
$stmt->bind_param("i", $quizID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $totalQuestions = $result->num_rows;
    
    while ($question = $result->fetch_assoc()) {
        $questionID = $question['id'];
        $correctAnswer = $question['correctAnswer'];
        
        // Check if this question was answered
        if (isset($_POST['question_' . $questionID])) {
            $userAnswer = $_POST['question_' . $questionID];
            
            // Compare answers
            if ($userAnswer === $correctAnswer) {
                $score++;
            }
        }
    }
    
    // Calculate percentage
    if ($totalQuestions > 0) {
        $scorePercentage = round(($score / $totalQuestions) * 100);
    }
}
$stmt->close();

// ------------------------------------------
// 7. SAVE SCORE TO DATABASE
// ------------------------------------------
$stmt = $connection->prepare("INSERT INTO takenquiz (quizID, learnerID, score) VALUES (?, ?, ?)");
$stmt->bind_param("iii", $quizID, $learner_id, $scorePercentage);
$stmt->execute();
$stmt->close();

// ------------------------------------------
// 8. DETERMINE REACTION VIDEO BASED ON SCORE
// ------------------------------------------
$videoFile = 'videos/congrats.mp4';
$videoMessage = '';

if ($scorePercentage >= 90) {
    $videoMessage = 'Excellent! Outstanding performance!';
} elseif ($scorePercentage >= 70) {
    $videoMessage = 'Great job! Well done!';
} elseif ($scorePercentage >= 50) {
    $videoMessage = 'Good effort! Keep practicing!';
} else {
    $videoMessage = 'Keep trying! Practice makes perfect!';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Score and Feedback Page</title>
    <link rel="stylesheet" href="Learn&QF.css">
    <link rel="stylesheet" href="style.css">
</head>
<body onload="start()">
    <header>
        <nav>
            <ul>
                <li><a href="Learners_homepage.php"><img src="images/mindly.png" alt="Mindly Logo" /></a></li>
            </ul>
        </nav>
    </header>

    <main style="max-width: 800px; margin: 0 auto; padding: 20px; text-align: center;">
        <div style="position: relative; margin-bottom: 30px;">
            <h1 style="font-size: 2em; margin: 0; text-align: center;">Quiz in <?php echo htmlspecialchars($quiz['topicName']); ?></h1>
            <a href="Learners_homepage.php" class="back-btn" style="position: absolute; top: 0; right: 0; display: inline-block; padding: 8px 16px; background-color: #5945ec; color: white; text-decoration: none; border-radius: 5px;">Back to Homepage</a>
        </div>

        <div style="margin: 30px auto; max-width: 500px;">
            <label for="educator" style="font-weight: bold; display: block; margin-bottom: 5px; text-align: left;">Educator:</label>
            <div style="display: flex; align-items: center; gap: 10px; padding: 8px; border: 1px solid #333; background-color: #f9f9f9; border-radius: 4px;">
                <span style="flex-grow: 1;"><?php echo htmlspecialchars($quiz['eduFirst'] . ' ' . $quiz['eduLast']); ?></span>
                <img src="uploads/<?php echo htmlspecialchars($quiz['eduPhoto']); ?>" alt="Educator" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
            </div>
        </div>

        <div style="margin: 30px 0;">
            <h2 style="font-size: 1.8em; color: <?php echo $scorePercentage >= 70 ? '#2e7d32' : '#d32f2f'; ?>;">
                Quiz Score: <?php echo $scorePercentage; ?>%
            </h2>
            <p style="font-size: 1.2em; color: #666;">
                You got <?php echo $score; ?> out of <?php echo $totalQuestions; ?> questions correct!
            </p>
            <p style="font-size: 1.1em; font-weight: bold; color: #5945ec;">
                <?php echo $videoMessage; ?>
            </p>
        </div>

        <div style="margin: 40px 0;">
            <video controls width="400" height="300" autoplay muted style="border: 2px solid #333; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                <source src="<?php echo $videoFile; ?>" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>

        <div style="margin: 40px auto; max-width: 600px; text-align: left;">
            <h2 style="font-size: 1.5em; margin-bottom: 25px; text-align: center;">Feedback about Quiz:</h2>
            
            <form method="POST" action="Quiz score and feedback.php" onsubmit="return validateFeedback();">
                <!-- Hidden inputs -->
                <input type="hidden" name="quizID" value="<?php echo htmlspecialchars($quizID); ?>">
                <input type="hidden" name="submit_feedback" value="1">
                
                <div style="margin-bottom: 25px; display: flex; align-items: center; gap: 15px;">
                    <label for="rating" style="font-weight: bold;">Rating (out of 5):</label>
                    <select id="rating" name="rating" required style="padding: 8px 12px; border: 1px solid #333; background-color: white; border-radius: 4px;">
                        <option value="">Select rating</option>
                        <option value="5">5 - Excellent</option>
                        <option value="4">4 - Very Good</option>
                        <option value="3">3 - Good</option>
                        <option value="2">2 - Fair</option>
                        <option value="1">1 - Poor</option>
                    </select>
                </div>

                <div style="margin-bottom: 30px;">
                    <label for="comments" style="font-weight: bold; display: block; margin-bottom: 8px;">Comments:</label>
                    <textarea id="comments" name="comments" rows="8" required placeholder="Share your thoughts about this quiz..." style="width: 100%; padding: 12px; border: 1px solid #333; border-radius: 4px; resize: vertical; font-family: Arial, sans-serif;"></textarea>
                </div>

                <div style="text-align: center; margin: 30px 0;">
                    <button type="submit" class="submit-feedback-btn" style="padding: 12px 30px; background-color: #5945ec; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; font-weight: bold;">Submit Feedback</button>
                </div>
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
        }
        
        function validateFeedback() {
            const rating = document.getElementById('rating').value;
            const comments = document.getElementById('comments').value.trim();
            
            if (!rating) {
                alert('Please select a rating.');
                return false;
            }
            
            if (comments === '') {
                alert('Please enter your comments.');
                return false;
            }
            
            return true;
        }
    </script>
</body>
</html>
