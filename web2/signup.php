<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sign Up</title>
  <style>
/* your original styles remain the same */
body { font-family: Arial, sans-serif; margin: 0; background: #f0f2f5; min-height: 100vh; display: flex; flex-direction: column; opacity: 0; transition: opacity 1s ease-in-out; }
header { box-shadow: 2px 6px 10px rgba(0,0,0,0.1); }
nav { background-image: linear-gradient(to right, #7341b1, #ee7979); clip-path: ellipse(100% 100% at 50% 0%); overflow: hidden; width: 100%; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
nav li { list-style-type: none; float: left; }
nav img { height: 50%; width: 40%; }
footer { background-image: linear-gradient(to right, #7341b1, #ee7979); width: 100%; padding: 20px 0; text-align: center; margin-top: auto; box-shadow: 0 -4px 8px rgba(0,0,0,0.1);}
footer p { margin: 0; font-size: 14px; color: #fff; }
main { background: #fff; padding: 20px; margin: 40px auto; max-width: 420px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
h2 { text-align: center; }
label { display: block; margin-top: 10px; }
input, select { width: 100%; padding: 8px; margin-top: 4px; border: 1px solid #ccc; border-radius: 6px; }
.topics label { display: block; margin: 4px 0; }
button { margin-top: 18px; display: inline-block; padding: 12px 25px; border-radius: 8px; border: none; cursor: pointer; font-size: 16px; font-weight: bold; color: #fff; background-image: linear-gradient(to right, #7341b1, #ee7979); transition: background 0.3s ease, transform 0.3s ease; }
button:hover { background-image: linear-gradient(to right, #8a3ccf, #ff7b90); transform: scale(1.05); }
form { display: none; }
.error { color: #b00020; font-weight: bold; text-align: center; }
  </style>
</head>
<body onload="document.body.style.opacity='1'">

  <?php 
  if (isset($_GET['error'])) {
      echo "<p class='error'>" . htmlspecialchars($_GET['error']) . "</p>";
  }
  ?>

  <header>
    <nav>
      <ul>
        <li><a href="index.php"><img src="images/mindly.png" alt="Mindly Logo"/></a></li>
      </ul>
    </nav>
  </header>

  <main>
    <h2>Create an Account</h2>

    <label for="role">Make an account as</label>
    <select id="role">
      <option value="">Role</option>
      <option value="learner">Learner</option>
      <option value="educator">Educator</option>
    </select>

    <!-- Learner form -->
    <form id="learnerForm" action="signup_process.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="role" value="learner">
      <label>First Name</label><input type="text" name="firstName" required>
      <label>Last Name</label><input type="text" name="lastName" required>
      <label>Email</label><input type="email" name="email" required>
      <label>Password</label><input type="password" name="password" required>
      <label>Profile Image</label><input type="file" name="photo" accept="image/*">
      <button type="submit">Sign Up as Learner</button>
    </form>

    <!-- Educator form -->
    <form id="educatorForm" action="signup_process.php" method="POST" enctype="multipart/form-data"> 
      <input type="hidden" name="role" value="educator">
      <label>First Name</label><input type="text" name="firstName" required>
      <label>Last Name</label><input type="text" name="lastName" required>
      <label>Email</label><input type="email" name="email" required>
      <label>Password</label><input type="password" name="password" required>
      <label>Profile Image</label><input type="file" name="photo" accept="image/*">

      <div class="topics" style="margin-top:10px;">
        <p><strong>Choose Specialization Topic(s):</strong></p>
        <label><input type="checkbox" name="topic[]" value="1111"> Web Development</label>
        <label><input type="checkbox" name="topic[]" value="2222"> Math</label>
        <label><input type="checkbox" name="topic[]" value="3333"> English</label>
      </div>

      <button type="submit">Sign Up as Educator</button>
    </form>
  </main>

  <footer><p>&copy; 2025 Mindly. All rights reserved.</p></footer>

  <script>
    const roleSelect = document.getElementById("role");
    const learnerForm = document.getElementById("learnerForm");
    const educatorForm = document.getElementById("educatorForm");

    roleSelect.addEventListener("change", () => {
      learnerForm.style.display  = (roleSelect.value === "learner") ? "block" : "none";
      educatorForm.style.display = (roleSelect.value === "educator") ? "block" : "none";
    });
  </script>
</body>
</html>
