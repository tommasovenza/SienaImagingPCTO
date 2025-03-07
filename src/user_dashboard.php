<?php

include 'user_navbar.php';

session_start();

require_once "utils.php";
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Debugging: Check if session variables are set
if (!isset($_SESSION['user_id']) || !isset($_SESSION['privilege_level']) || !isset($_SESSION['username'])) {
    echo "Session variables are not set properly.";
    var_dump($_SESSION);  // This will output the session variables for debugging purposes
    echoUserNotFoundMessage();
    die();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>User Dashboard</title>
</head>
<body>
    <main class="main">
        <div class="paragraf">
            <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
            <p>Use the navigation bar to explore features.</p>
        </div>
    </main>
</body>
</html>
