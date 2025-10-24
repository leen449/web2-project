
<?php
// ------------------------------------------
// 1. INITIAL SETUP
// ------------------------------------------
session_start();
include 'db.php';

// ------------------------------------------
// 2. HANDLE LOGIN FORM SUBMISSION
// ------------------------------------------
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    // Validate inputs
    if (empty($email) || empty($password) || empty($role)) {
        $error_message = 'Please fill in all fields.';
    } else {
        // Query database for user
        $stmt = $connection->prepare("SELECT id, firstName, lastName, password, userType FROM user WHERE emailAddress = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password (assuming plain text for now, should use password_hash in production)
            if ($password === $user['password']) {
                // Check if role matches
                $db_role = strtolower($user['userType']);
                
                if ($db_role === $role) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_type'] = $user['userType'];
                    $_SESSION['user_name'] = $user['firstName'] . ' ' . $user['lastName'];

                    // Redirect based on role
                    if ($role === 'learner') {
                        header("Location: Learners_homepage.php");
                        exit();
                    } else if ($role === 'educator') {
                        header("Location: Educators homepage.php");
                        exit();
                    }
                } else {
                    $error_message = 'Invalid role selected. You are registered as ' . $user['userType'] . '.';
                }
            } else {
                $error_message = 'Invalid email or password.';
            }
        } else {
            $error_message = 'Invalid email or password.';
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
    
html {
    scroll-behavior: smooth; }

body {
  font-family: Arial, sans-serif;
  margin: 0;
  background: #f0f2f5;
  min-height: 100vh; 
  display: flex;
  flex-direction: column;
opacity: 0;
  transition: opacity 1s ease-in-out; }



header {
    box-shadow: 2px 6px 10px rgba(0, 0, 0, 0.1); }

nav {
background-image: linear-gradient(to right, #7341b1, #ee7979);
clip-path: ellipse(100% 100% at 50% 0%);
overflow: hidden;
width: 100%;
box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);

}



nav li {
list-style-type: none;
float: left; }

nav img {
height: 50%;
width: 40%;
}

main {
  flex: 1; 
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 40px 20px; 
  margin: 60px auto;
  max-width: 700px;  /* wider */
  width: 100%;       /* allows it to stretch */
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.2);
  padding: 40px 20px;


}



footer {
  background-image: linear-gradient(to right, #7341b1, #ee7979);
  clip-path: none; 
  width: 100%;
  padding: 20px 0;
  text-align: center;
  margin-top: auto; 
  box-shadow: 0 -4px 8px rgba(0, 0, 0, 0.1); 
}

footer p {
  margin: 0;
  font-size: 14px; 
  color: #fff; 
}


h2 { text-align: center; }
label { display: block; margin-top: 10px; }
input, select {
  width: 100%;
  box-sizing: border-box;  /* ✅ makes padding count inside width */
  padding: 8px;
  margin-top: 4px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 16px;         /* keeps text size consistent */
  appearance: none;        /* optional: removes browser default arrow style */

 border-radius: 6px; }
 button {
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
  margin-top: 25px;
}
form {
  width: 100%;
  max-width: 500px;   /* form itself doesn’t get *too* wide */
  margin: 0 auto;
}

/* Hover effect */
button:hover {
  background-image: linear-gradient(to right, #8a3ccf, #ff7b90); /* brighter gradient */
  transform: scale(1.05); /* slight zoom */
}

/* Optional: for accessibility (keyboard focus) */
button:focus {
  outline: 2px solid #7341b1;
  outline-offset: 3px;
}
  </style>
</head>
<body onload="document.body.style.opacity='1'">
    <?php
    // Display error messages
    if (!empty($error_message)) {
        echo "<p style='color:red; text-align:center; font-weight:bold;'>" . htmlspecialchars($error_message) . "</p>";
    }
    
    if (isset($_GET['error'])) {
        if ($_GET['error'] === 'access_denied') {
            echo "<p style='color:red; text-align:center;'>Access Denied: You are not an educator.</p>";
        } elseif ($_GET['error'] === 'access_denied_learner') {
            echo "<p style='color:red; text-align:center;'>Access Denied: You are not a learner.</p>";
        } elseif ($_GET['error'] === 'not_logged_in') {
            echo "<p style='color:red; text-align:center;'>Please log in first.</p>";
        }
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
      <label>Email</label><input type="email" name="email" required>
      <label>Password</label><input type="password" name="password" required>
      <label for="role">Sign in as</label>
      <select name="role" id="role" required>
        <option value="">Select Role</option>
        <option value="learner">Learner</option>
        <option value="educator">Educator</option>
      </select>
      
      <button type="submit">Log In</button>
    </form>

  </main>
  <div class="footer-container">
    <footer>
      <p>&copy; 2025 Mindly. All rights reserved.</p>
    </footer>
  </div>

</body>
</html>