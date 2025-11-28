<?php
session_start();
require "db.php";

// ---- must be logged in as learner ----
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || strtolower($_SESSION['user_type']) !== 'learner') {
    header("Location: login.php?error=not_logged_in");
    exit;
}

$learnerID = (int) $_SESSION['user_id'];

// ---- Fetch topics ----
$topics = [];
if ($res = $connection->query("SELECT id, topicName FROM topic ORDER BY topicName ASC")) {
    while ($row = $res->fetch_assoc()) {
        $topics[] = $row;
    }
    $res->free();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Recommend Question - Mindly</title>
  <style>
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

    <!-- note: action points to submit_recommended.php to handle processing -->
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

        <!-- Educator (AJAX-filled) -->
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

  <!-- Footer -->
  <footer>
    <p>&copy; 2025 Mindly. All rights reserved.</p>
  </footer>

  <script>
    window.addEventListener("load", function() {
      document.body.classList.add("loaded");
    });

    function goBack() { window.location.href = "Learners_homepage.php"; }

    // ----- AJAX: update educators based on topic -----
    document.getElementById('topic').addEventListener('change', function() {
        const topicID = this.value;
        const educatorSelect = document.getElementById('educator');
        educatorSelect.innerHTML = '<option value="">Loading...</option>';

        fetch('fetch_educators.php?topicID=' + topicID)
            .then(response => response.json())
            .then(data => {
                educatorSelect.innerHTML = '<option value="">Select Educator</option>';
                data.forEach(edu => {
                    const opt = document.createElement('option');
                    opt.value = edu.id;
                    opt.textContent = edu.name; // using "name" from FetchEducators.php JSON
                    educatorSelect.appendChild(opt);
                });
            })
            .catch(err => {
                educatorSelect.innerHTML = '<option value="">Error loading educators</option>';
                console.error(err);
            });
    });
  </script>
</body>
</html>
