
<?php

session_start();
require 'db.php';

function back_with($key, $msg) {
    header("Location: signup.php?$key=" . urlencode($msg));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    back_with('error', 'Invalid request.');
}

$first  = trim($_POST['firstName'] ?? '');
$last   = trim($_POST['lastName'] ?? '');
$email  = trim($_POST['email'] ?? '');
$pass   = $_POST['password'] ?? '';
$role   = $_POST['role'] ?? '';           // 'learner' or 'educator'
$topics = $_POST['topic'] ?? [];          // topicIDs for educator only

if ($first === '' || $last === '' || $email === '' || $pass === '' || ($role !== 'learner' && $role !== 'educator')) {
    back_with('error', 'Please fill in all required fields.');
}
if ($role === 'educator' && (!is_array($topics) || count($topics) === 0)) {
    back_with('error', 'Please select at least one topic for Educator accounts.');
}

//if email exists
$stmt = $connection->prepare("SELECT id FROM user WHERE emailAddress = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    back_with('error', 'Email already exists.');
}
$stmt->close();

/* hash password */
$hash = password_hash($pass, PASSWORD_DEFAULT);

//handling photo upload (if no photo put default)
$photo = 'default.jpg';  

if (isset($_FILES['photo']) && is_uploaded_file($_FILES['photo']['tmp_name'])) {
  
    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','webp'];
    if (in_array($ext, $allowed, true)) {
        $filename = uniqid('photo_', true) . '.' . $ext;  
        $dest = __DIR__ . "/uploads/$filename";

        if (!is_dir(__DIR__ . '/uploads')) {
            mkdir(__DIR__ . '/uploads', 0775, true);
        }
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
            $photo = $filename;  
        }
    }
}


//inserting user
$userType = ucfirst($role); // 'Learner' | 'Educator' to match enum
$ins = $connection->prepare(
    "INSERT INTO user (firstName, lastName, emailAddress, password, photoFileName, userType)
     VALUES (?, ?, ?, ?, ?, ?)"
);
$ins->bind_param("ssssss", $first, $last, $email, $hash, $photo, $userType);
if (!$ins->execute()) {
    $ins->close();
    back_with('error', 'Failed to create account. Please try again.');
}
$userId = $ins->insert_id;
$ins->close();


if ($role === 'educator' && is_array($topics)) {
    $q = $connection->prepare("INSERT INTO quiz (educatorID, topicID) VALUES (?, ?)");
    foreach ($topics as $topicID) {
        $tid = (int)$topicID;
        if ($tid > 0) { $q->bind_param("ii", $userId, $tid); $q->execute(); }
    }
    $q->close();
}

// setting session variables then redirecting based of type
session_regenerate_id(true);
$_SESSION['user_id']   = $userId;
$_SESSION['user_type'] = $role;                 // 'learner' or 'educator'
$_SESSION['user_name'] = $first . ' ' . $last;

if ($role === 'learner') {
    header("Location: Learners_homepage.php");
} else {
    header("Location: Educators_homepage.php");
}
exit;
