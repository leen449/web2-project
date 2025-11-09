<?php
// --- BACKEND LOGIC ---
require "db.php";
require_once 'reqLog.php';


if (isset($_GET['quizID'])) {
    $quizID = $_GET['quizID'];

    // Retrieve feedback for this quiz ordered from newest to oldest
    $query = "SELECT * FROM quizfeedback WHERE quizID = ? ORDER BY id DESC";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "i", $quizID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    die("Quiz ID not provided in the request.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quiz Comments - Mindly</title>
  <style>
    /* === Your Original Styling === */
    html { scroll-behavior: smooth; }
    body {
      font-family: Arial, sans-serif;
      margin: 0; padding: 0;
      background: #f8f9fa; color: #333;
      opacity: 0; transition: opacity 1s ease-in-out;
    }
    body.loaded { opacity: 1; }
    header { box-shadow: 2px 6px 10px rgba(0,0,0,0.1); }
    nav {
      background-image: linear-gradient(to right, #7341b1, #ee7979);
      clip-path: ellipse(100% 100% at 50% 0%);
      overflow: hidden; width: 100%;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    nav li { list-style-type: none; float: left; }
    nav img { height: 50%; width: 40%; }
    footer {
      background-image: linear-gradient(to right, #7341b1, #ee7979);
      clip-path: ellipse(100% 100% at 50% 100%);
      overflow: visible; width: 100%;
      box-shadow: 0 6px 12px rgba(0,0,0,0.15);
      padding: 20px 0; text-align: center; position: relative;
    }
    footer p { margin: 0; font-size: 16px; color: #0f1214; }
    .container {
      max-width: 850px; margin: 40px auto; padding: 30px;
      background: #fff; border-radius: 15px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.15);
      border-left: 6px solid #a654e6;
    }
    h2 { text-align: center; margin-bottom: 10px; color: #a654e6; }
    .note {
      text-align: center; font-size: 0.9em; color: #555;
      margin-bottom: 25px; font-style: italic;
    }
    .comment {
      border-bottom: 1px solid #eee; padding: 18px 15px;
      border-radius: 8px; background: #fafafa;
      margin-bottom: 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }
    .comment-header {
      display: flex; justify-content: space-between;
      align-items: center; margin-bottom: 8px;
    }
    .comment-anon { font-weight: bold; color: #a654e6; font-size: 0.95em; }
    .comment-date { font-size: 0.9em; color: #777; }
    .comment-text { font-size: 1.05em; line-height: 1.5; }
    .back-link {
      display: inline-block; margin-top: 25px; text-decoration: none;
      background-image: linear-gradient(to right, #7341b1, #ee7979);
      color: #fff !important; padding: 12px 25px;
      border-radius: 8px; font-weight: bold;
      transition: background 0.3s, transform 0.3s;
    }
    .back-link:hover {
      background-image: linear-gradient(to right, #8a3ccf, #ff7b90);
      transform: scale(1.05);
    }
  </style>
</head>
<body>
  <!-- Header -->
  <header>
    <nav>
      <ul>
       <a href="Educators_homepage.php"> <li><img src="images/mindly.png" alt="Mindly Logo"></li></a>
      </ul>
    </nav>
  </header>

  <!-- Comments content -->
  <div class="container">
    <h2>Comments</h2>
    <div class="note">All comments are posted anonymously</div>

    <?php
    // --- DISPLAY COMMENTS ---
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<div class='comment'>";
            echo "<div class='comment-header'>";
            echo "<span class='comment-anon'>Anonymous</span>";
            echo "<span class='comment-date'>" . htmlspecialchars($row['date']) . "</span>";
            echo "</div>";
            echo "<div class='comment-text'>" . htmlspecialchars($row['comments']) . "</div>";
            echo "</div>";
        }
    } else {
        echo "<p style='text-align:center; color:#777;'>No comments yet for this quiz.</p>";
    }
    ?>

    <a href="educators_homepage.php" class="back-link">← Back to Homepage</a>
  </div>

  <!-- Footer -->
  <footer>
    <p>&copy; 2025 Mindly. All rights reserved.</p>
  </footer>

  <script>
    // Fade-in effect
    window.addEventListener('load', () => {
      document.body.classList.add('loaded');
    });
  </script>
</body>
</html>
