<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Take Quiz - Math</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      opacity: 0;
  transition: opacity 1s ease-in-out;
    }
    .quiz-container {
      max-width: 800px;
      margin: 40px auto;
      background: #fff;
      padding: 20px;
      border: 2px solid #ccc;
      border-radius: 10px;
      box-shadow: 0px 3px 15px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
      color: #a654e6;
    }
    .question-box {
      border: 1px solid #aaa;
      padding: 15px;
      margin: 20px 0;
      border-radius: 6px;
    }
    .question-box img {
      display: block;
      max-height: 120px;
      margin: 0 auto 10px;
      box-shadow: 0px 3px 10px rgba(0,0,0,0.2);
    }
    .answers label {
      display: block;
      margin: 5px 0;
      cursor: pointer;
    }

    .done-btn {
  display: inline-block;
  padding: 12px 25px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  font-size: 16px;
  font-weight: bold;
  text-decoration: none; /* works for <a> and <button> */
  color: #fff;
  background-image: linear-gradient(to right, #7341b1, #ee7979); /* purple → pink */
  transition: background 0.3s ease, transform 0.3s ease;
}

/* Hover effect */
.done-btn:hover  {
  background-image: linear-gradient(to right, #8a3ccf, #ff7b90); /* brighter gradient */
  transform: scale(1.05); /* slight zoom */
}

/* Optional: for accessibility (keyboard focus) */
.done-btn:focus {
  outline: 2px solid #7341b1;
  outline-offset: 3px;
}
  </style>
</head>
<body onload="document.body.style.opacity='1'">

  <!-- Header -->
  <header>
    <nav>
      <ul>
        <li><a href="Learner’s homepage.php"><img src="images/mindly.png" alt="Mindly Logo" /></a></li>
      </ul>
    </nav>
  </header>

  
  <div class="quiz-container">
    <h2>Quiz in Math</h2>
    <p><strong>Educator:</strong>Mohammed Ali </p>
    
    <form action="Quiz score and feedback.php">
     
      <div class="question-box">
        <h3>Question 1</h3>
        <img src="images/math.png" alt="Question Image">
        <p>What is 4 + 4 ?</p>
        <div class="answers">
          <label><input type="radio" name="q1" value="5"> A) 5</label>
          <label><input type="radio" name="q1" value="6"> B) 6</label>
          <label><input type="radio" name="q1" value="7"> C) 7</label>
          <label><input type="radio" name="q1" value="8"> D) 8</label>
        </div>
      </div>

     
      <div class="question-box">
        <h3>Question 2</h3>
        <p>What is 10 - 3 ?</p>
        <div class="answers">
          <label><input type="radio" name="q2" value="5"> A) 5</label>
          <label><input type="radio" name="q2" value="6"> B) 6</label>
          <label><input type="radio" name="q2" value="7"> C) 7</label>
          <label><input type="radio" name="q2" value="8"> D) 8</label>
        </div>
      </div>

      <!-- Question 3 -->
      <div class="question-box">
        <h3>Question 3</h3>
        <p>What is 3 × 3 ?</p>
        <div class="answers">
          <label><input type="radio" name="q3" value="6"> A) 6</label>
          <label><input type="radio" name="q3" value="9"> B) 9</label>
          <label><input type="radio" name="q3" value="12"> C) 12</label>
          <label><input type="radio" name="q3" value="15"> D) 15</label>
        </div>
      </div>

      <!-- Question 4 -->
      <div class="question-box">
        <h3>Question 4</h3>
        <p>What is 12 ÷ 4 ?</p>
        <div class="answers">
          <label><input type="radio" name="q4" value="2"> A) 2</label>
          <label><input type="radio" name="q4" value="3"> B) 3</label>
          <label><input type="radio" name="q4" value="4"> C) 4</label>
          <label><input type="radio" name="q4" value="6"> D) 6</label>
        </div>
      </div>

      <!-- Question 5 -->
      <div class="question-box">
        <h3>Question 5</h3>
        <p>What is 7 + 2 ?</p>
        <div class="answers">
          <label><input type="radio" name="q5" value="8"> A) 8</label>
          <label><input type="radio" name="q5" value="9"> B) 9</label>
          <label><input type="radio" name="q5" value="10"> C) 10</label>
          <label><input type="radio" name="q5" value="11"> D) 11</label>
        </div>
      </div>

      <!-- Done button -->
      <button type="submit" class="done-btn">Done</button>
    </form>
  </div>

  <!-- Footer -->
  <div class="footer-container">
    <footer>
      <p>&copy; 2025 Mindly. All rights reserved.</p>
    </footer>
  </div>

  

</body>
</html>
