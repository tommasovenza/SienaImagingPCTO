<?php
require_once "utils.php";
require_once "logger.php";

function echoSuccessMessage() {
    echo "<p style='color: green;'>Account created, you can now login!</p>";
} 

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $conn = get_db_connection();
    
        $first_name = safe_get_or_default_ass_arr($_POST, 'first_name');
        $last_name = safe_get_or_default_ass_arr($_POST, 'last_name');
        $username = safe_get_or_default_ass_arr($_POST, 'username');
        $email = safe_get_or_default_ass_arr($_POST, 'email');
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    
        $params = array(":first_name"=>$first_name, ":last_name"=>$last_name, ":username"=>$username, ":email"=>$email, ":password"=>$password);

        $param_sql = "INSERT INTO Users (first_name, last_name, username, email, password, privilege_level, signup_date)
        VALUES (:first_name, :last_name, :username, :email, :password, '0', NOW())";

        $stmt = $conn->prepare($param_sql);
        if ($stmt->execute($params)) {
            echoSuccessMessage();
        } else {
            echoGenericErrorMessage();
        }
    }
} catch (PDOException $e) {
    echoGenericErrorMessage();
    $conn = null;
    die();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;
        }
        .form-container {
            margin-top: 100px;
        }
        form {
            display: inline-block;
            text-align: left;
        }
        input {
            display: block;
            margin: 10px 0;
            padding: 8px;
            width: 100%;
        }
        button {
            padding: 10px 20px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Sign Up</h1>
        <form method="POST" action="">
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" required>
            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" required>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit" name="signup">Sign Up</button>
            <button type="button" onclick="window.location.href='homepage.php';">Cancel</button>
        </form>
    </div>
</body>
</html>
