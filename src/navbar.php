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
                <a href="homepage.php" class="<?php require_once "utils.php"; basename(safe_input($_SERVER['PHP_SELF'])) === 'homepage.php' ? 'active' : '' ?>">Home</a>
                <a href="about.php" class="<?php require_once "utils.php"; basename(safe_input($_SERVER['PHP_SELF'])) === 'about.php' ? 'active' : '' ?>">About Us</a>
                <a href="find_us.php" class="<?php require_once "utils.php"; basename(safe_input($_SERVER['PHP_SELF'])) === 'find_us.php' ? 'active' : '' ?>">Find Us</a>
                <a href="search.php" class="<?php require_once "utils.php"; basename(safe_input($_SERVER['PHP_SELF'])) === 'search.php' ? 'active' : '' ?>">Search</a>
                <a href="team.php" class="<?php require_once "utils.php"; basename(safe_input($_SERVER['PHP_SELF'])) === 'team.php' ? 'active' : '' ?>">The Team</a>
                <a href="contact.php" class="<?php require_once "utils.php"; basename(safe_input($_SERVER['PHP_SELF'])) === 'contact.php' ? 'active' : '' ?>">Contact</a>
                <a href="login.php" class="login <?php require_once "utils.php"; basename(safe_input($_SERVER['PHP_SELF'])) === 'login.php' ? 'active' : '' ?>">Login</a>
            </nav>
        </div>
        <div class="logo"><a>LOGO</a></div>
    </header>
</body>
</html>