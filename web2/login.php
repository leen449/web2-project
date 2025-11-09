
<?php
// login.php
session_start();

require 'db.php';

$error_message = "";

// If form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error_message = "Please fill in all fields.";
    } else {
        // Fetch user by email
        $stmt = $connection->prepare("SELECT id, firstName, lastName, password, userType FROM user WHERE emailAddress = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            // Verify hashed password
            if (password_verify($password, $user['password'])) {

                session_regenerate_id(true);
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_type'] = strtolower($user['userType']); 
                $_SESSION['user_name'] = $user['firstName'] . ' ' . $user['lastName'];

                // Redirect based on type
                if ($_SESSION['user_type'] === 'learner') {
                    header("Location: Learners_homepage.php"); 
                    exit;
                } else {
                    header("Location: Educators_homepage.php"); 
                    exit;
                }

            } else {
                $error_message = "Invalid email or password.";
            }
        } else {
            $error_message = "Invalid email or password.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Log In</title>
  
  <style>
/* --- Your page styling (unchanged) --- */

html { scroll-behavior: smooth; }
body {
  font-family: Arial, sans-serif;
  margin: 0;
  background: #f0f2f5;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  opacity: 0;
  transition: opacity 1s ease-in-out;
}
header { box-shadow: 2px 6px 10px rgba(0,0,0,0.1); }

nav {
  background-image: linear-gradient(to right, #7341b1, #ee7979);
  clip-path: ellipse(100% 100% at 50% 0%);
  overflow: hidden;
  width: 100%;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
nav li {
  list-style-type: none;
  float: left;
}
nav img { height: 50%; width: 40%; }

main {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 40px 20px;
  margin: 60px auto;
  max-width: 700px;
  width: 100%;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

footer {
  background-image: linear-gradient(to right, #7341b1, #ee7979);
  width: 100%;
  padding: 20px 0;
  text-align: center;
  margin-top: auto;
  box-shadow: 0 -4px 8px rgba(0,0,0,0.1);
}
footer p { margin: 0; font-size: 14px; color: #fff; }

h2 { text-align: center; }
label { display: block; margin-top: 10px; }

input {
  width: 100%;
  padding: 8px;
  margin-top: 4px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 16px;
}

button {
  margin-top: 25px;
  padding: 12px 25px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  font-size: 16px;
  font-weight: bold;
  color: #fff;
  background-image: linear-gradient(to right, #7341b1, #ee7979);
  transition: background 0.3s ease, transform 0.3s ease;
}
button:hover {
  background-image: linear-gradient(to right, #8a3ccf, #ff7b90);
  transform: scale(1.05);
}
button:focus {
  outline: 2px solid #7341b1;
  outline-offset: 3px;
}

form {
  width: 100%;
  max-width: 500px;
  margin: 0 auto;
}
  </style>
</head>

<body onload="document.body.style.opacity='1'">

  <?php
  if (!empty($error_message)) {
      echo "<p style='color:red; text-align:center; font-weight:bold;'>" . htmlspecialchars($error_message) . "</p>";
  }

  if (isset($_GET['error']) && $_GET['error'] === 'not_logged_in') {
      echo "<p style='color:red; text-align:center;'>Please log in first.</p>";
  }
  ?>

  <header>
    <nav>
      <ul>
        <li><a href="index.php"><img src="images/mindly.png" alt="Mindly Logo" /></a></li>
      </ul>
    </nav>
  </header>

  <main>
    <h2>Log In</h2>

    <form method="POST" action="login.php">
      <label>Email</label>
      <input type="email" name="email" required>

      <label>Password</label>
      <input type="password" name="password" required>

      <button type="submit">Log In</button>
    </form>
  </main>

  <footer>
    <p>&copy; 2025 Mindly. All rights reserved.</p>
  </footer>

</body>
</html>