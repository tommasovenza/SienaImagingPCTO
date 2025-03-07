<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website</title>
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
        <a href="homepage.php" class="<?php require_once "utils.php"; basename(safe_input($_SERVER['PHP_SELF'])) === 'homepage.php' ? 'active' : '' ?>">Home</a>
        <a href="about.php" class="<?php require_once "utils.php"; basename(safe_input($_SERVER['PHP_SELF'])) === 'about.php' ? 'active' : '' ?>">About Us</a>
        <a href="find_us.php" class="<?php require_once "utils.php"; basename(safe_input($_SERVER['PHP_SELF'])) === 'find_us.php' ? 'active' : '' ?>">Find Us</a>
        <a href="search.php" class="<?php require_once "utils.php"; basename(safe_input($_SERVER['PHP_SELF'])) === 'search.php' ? 'active' : '' ?>">Search</a>
        <a href="team.php" class="<?php require_once "utils.php"; basename(safe_input($_SERVER['PHP_SELF'])) === 'team.php' ? 'active' : '' ?>">The Team</a>
        <a href="contact.php" class="<?php require_once "utils.php"; basename(safe_input($_SERVER['PHP_SELF'])) === 'contact.php' ? 'active' : '' ?>">Contact</a>
        <a href="login.php" class="<?php require_once "utils.php"; basename(safe_input($_SERVER['PHP_SELF'])) === 'login.php' ? 'active' : '' ?>">Login</a>
    </div>
