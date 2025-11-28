<?php
session_start();
require "db.php";

// ---- must be logged in as learner ----
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || strtolower($_SESSION['user_type']) !== 'learner') {
    header("Location: login.php?error=not_logged_in");
    exit;
}

// Fetch topics
$topics = [];
$res = $connection->query("SELECT id, topicName FROM topic ORDER BY topicName ASC");
while ($row = $res->fetch_assoc()) {
    $topics[] = $row;
}
$res->free();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Recommend Question - Mindly</title>
<style>
/* your existing styles here */
</style>
</head>
<body>
<header>
  <nav>
    <ul>
      <li><a href="Learners_homepage.php"><img src="images/mindly.png" alt="Mindly Logo" /></a></li>
    </ul>
  </nav>
</header>

<div class="question-frame">
<h3 class="frame-title">Recommend a Question</h3>
<form id="recommendForm" method="POST" enctype="multipart/form-data" action="submit_recommended.php">
<div class="grid">
    <!-- Topic -->
    <label for="topic">Topic:</label>
    <select id="topic" name="topic" required>
        <option value="">Select Topic</option>
        <?php foreach ($topics as $topic): ?>
            <option value="<?php echo (int)$topic['id']; ?>">
                <?php echo htmlspecialchars($topic['topicName']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <!-- Educator -->
    <label for="educator">Educator:</label>
    <select id="educator" name="educator" required>
        <option value="">Select Educator</option>
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

<footer>
  <p>&copy; 2025 Mindly. All rights reserved.</p>
</footer>

<script>
function goBack() { window.location.href = "Learners_homepage.php"; }

// AJAX: update educators when topic changes
document.getElementById('topic').addEventListener('change', function() {
    const topicID = this.value;
    const eduSelect = document.getElementById('educator');
    eduSelect.innerHTML = '<option value="">Loading...</option>';

    if (!topicID) {
        eduSelect.innerHTML = '<option value="">Select Educator</option>';
        return;
    }

    fetch('fetch_educators.php?topicID=' + topicID)
        .then(res => res.json())
        .then(data => {
            eduSelect.innerHTML = '<option value="">Select Educator</option>';
            if (data.length === 0) {
                eduSelect.innerHTML = '<option value="">No educators found</option>';
            } else {
                data.forEach(edu => {
                    const opt = document.createElement('option');
                    opt.value = edu.id;
                    opt.textContent = edu.name;
                    eduSelect.appendChild(opt);
                });
            }
        })
        .catch(err => {
            eduSelect.innerHTML = '<option value="">Error loading educators</option>';
            console.error(err);
        });
});
</script>
</body>
</html>
