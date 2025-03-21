<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">    
    <script src="script.js"></script>
    <title>Website</title>
    
</head>
<body>
    <header>
        <div class="navbar-container">
            <nav class="topnav" id="myTopnav">
                <a href="javascript:void(0);" class="icon" onclick="myFunction()"> &#9776;</a>
                <a href="user_dashboard.php" class="<?php require_once "utils.php"; basename(safe_input($_SERVER['PHP_SELF'])) === 'user_dashboard.php' ? 'active' : '' ?>">Home</a>
                <a href="patients.php" class="<?php require_once "utils.php"; basename(safe_input($_SERVER['PHP_SELF'])) === 'patients.php' ? 'active' : '' ?>">See Patients</a>
                <a href="add_patient.php" class="<?php require_once "utils.php"; basename(safe_input($_SERVER['PHP_SELF'])) === 'add_patient.php' ? 'active' : '' ?>">Add Patients</a>
                <a href="registries.php" class="<?php require_once "utils.php"; basename(safe_input($_SERVER['PHP_SELF'])) === 'registries.php' ? 'active' : '' ?>">Registries</a>
                <a href="add_user.php" class="<?php require_once "utils.php"; basename(safe_input($_SERVER['PHP_SELF'])) === 'add_user.php' ? 'active' : '' ?>">Add User</a>
                <a href="logout.php" class="login">Logout</a>
            </nav>
        </div>
    </header>
</body>

