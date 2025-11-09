<?php
<<<<<<< HEAD
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in, if not redirect to index.php
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: index.php?error=not_logged_in");
    exit();
}
?>
=======
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: login.php?error=not_logged_in");
    exit();
} ?>
>>>>>>> d536aad2c627f36efe9f532f8812d5c018ca1d6d
