<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up</title>
  <style>
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
header {
box-shadow: 2px 6px 10px rgba(0, 0, 0, 0.1);
}

nav {
background-image: linear-gradient(to right, #7341b1, #ee7979);
clip-path: ellipse(100% 100% at 50% 0%);
overflow: hidden;
width: 100%;
box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}



nav li {
list-style-type: none;
float: left;
}

nav img {
height: 50%;
width: 40%;
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
main { background: white; padding: 20px; margin: 40px auto; max-width: 400px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
h2 { text-align: center; }
label { display: block; margin-top: 10px; }
input { width: 100%; padding: 8px; margin-top: 4px; border: 1px solid #ccc; border-radius: 6px; }
.topics label { display: block; margin: 4px 0; }
button {
  margin-top: 25px;
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
button:hover {
  background-image: linear-gradient(to right, #8a3ccf, #ff7b90); /* brighter gradient */
  transform: scale(1.05); /* slight zoom */
}

/* Optional: for accessibility (keyboard focus) */
button:focus {
  outline: 2px solid #7341b1;
  outline-offset: 3px;
}

form { display: none; }
  </style>
</head>
<body onload="document.body.style.opacity='1'">
  <header>
    <nav>
      <ul>
        <li><a href="index.php"><img src="images/mindly.png" alt="Mindly Logo" /></a></li>

      </ul>
    </nav>
  </header>  
  <main>
    <h2>Create an Account</h2>
    <label for="role">Make an account as</label>
    <select id="role">
      <option value="">Role </option>
      <option value="learner">Learner</option>
      <option value="educator">Educator</option>
    </select>

    
    <form id="learnerForm" onsubmit="event.preventDefault(); window.location.href='Learners_homepage.php';">
      <label>First Name</label><input type="text" required>
      <label>Last Name</label><input type="text" required>
      <label>Email</label><input type="email" required>
      <label>Password</label><input type="password" required>
      <label>Profile Image</label><input type="file" accept="image/*">
      <button type="submit">Sign Up as Learner</button>
    </form>

   
    <form id="educatorForm" onsubmit="event.preventDefault(); window.location.href='Educators homepage.php';"> 
      <label>First Name</label><input type="text" required>
      <label>Last Name</label><input type="text" required>
      <label>Email</label><input type="email" required>
      <label>Password</label><input type="password" required>
      <label>Profile Image</label><input type="file" accept="image/*">
      <div class="topics">
        <p>Choose Specialization Topic:</p>
        <label><input type="radio" name="topic" value="math" required> Mathematics</label>
        <label><input type="radio" name="topic" value="web"> Web Development</label>
        <label><input type="radio" name="topic" value="english"> English</label>
        
      </div>
      <button type="submit">Sign Up as Educator</button>
      
    </form>
  </main>
  <div class="footer-container">
    <footer>
      <p>&copy; 2025 Mindly. All rights reserved.</p>
    </footer>
  </div>

  <script> //to make the sign up form different depending on the role
    const roleSelect = document.getElementById("role");
    const learnerForm = document.getElementById("learnerForm");
    const educatorForm = document.getElementById("educatorForm");

    roleSelect.addEventListener("change", () => {
      learnerForm.style.display = roleSelect.value === "learner" ? "block" : "none";
      educatorForm.style.display = roleSelect.value === "educator" ? "block" : "none";
    });
  </script>
</body>
</html>
