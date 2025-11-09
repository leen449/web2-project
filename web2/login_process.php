<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        header("Location: login.php?error=empty_fields");
        exit();
    }

    // Fetch user by email
    $stmt = $connection->prepare("
        SELECT id, firstName, lastName, password, userType 
        FROM user 
        WHERE emailAddress = ?
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        
        if (password_verify($password, $user['password'])) {
            // SUCCESS
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_type'] = strtolower($user['userType']);
            $_SESSION['user_name'] = $user['firstName'] . ' ' . $user['lastName'];

            if ($_SESSION['user_type'] === 'learner') {
                header("Location: Learners_homepage.php");
                exit();
            } else {
                header("Location: Educators_homepage.php");
                exit();
            }
        }
    }

    // FAILED
    header("Location: login.php?error=invalid_credentials");
    exit();
}
else {
    header("Location: login.php");
    exit();
}
?>
