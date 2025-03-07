<?php

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
    <title>User Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: #333;
            overflow: hidden;
            display: flex;
            justify-content: space-around;
            padding: 14px 0;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 14px 20px;
            font-size: 16px;
        }
        .navbar a:hover {
            background-color: #575757;
            color: white;
        }
        .navbar a.active {
            background-color: #04AA6D;
            color: white;
        }
        .content {
            padding: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="patients.php" class="<?php require_once "utils.php"; basename(safe_input($_SERVER['PHP_SELF'])) === 'patients.php' ? 'active' : '' ?>">See Patients</a>
        <a href="add_patient.php" class="<?php require_once "utils.php"; basename(safe_input($_SERVER['PHP_SELF'])) === 'add_patient.php' ? 'active' : '' ?>">Add Patients</a>
        <a href="registries.php" class="<?php require_once "utils.php"; basename(safe_input($_SERVER['PHP_SELF'])) === 'registries.php' ? 'active' : '' ?>">Registries</a>
        <a href="add_user.php" class="<?php require_once "utils.php"; basename(safe_input($_SERVER['PHP_SELF'])) === 'add_user.php' ? 'active' : '' ?>">Users</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="content">
        <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
        <p>Use the navigation bar to explore features.</p>
    </div>
</body>
</html>
