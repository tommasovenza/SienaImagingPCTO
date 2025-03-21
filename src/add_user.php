<?php
require_once "utils.php";
require_once "logger.php";

function echoSuccessMessage() {
    echo "<p style='color: green;'>User added successfully!</p>";
}

try {
    session_start();

    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    // Debugging: Check if session variables are set
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['privilege_level']) || !isset($_SESSION['username'])) {
        echo "Session variables are not set properly.";
        var_dump($_SESSION);  // This will output the session variables for debugging purposes
        echoUserNotFoundMessage();
        die();
    }   
    
    // Ensure the user has privilege level 4 
    if ($_SESSION['privilege_level'] != 4) {
        echo "<p style='color: red;'>You do not have permission to add users.</p>";
        die();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $conn = get_db_connection();

        $first_name = safe_get_or_default_ass_arr($_POST, 'first_name');
        $last_name = safe_get_or_default_ass_arr($_POST, 'last_name');
        $username = safe_get_or_default_ass_arr($_POST, 'username');
        $email = safe_get_or_default_ass_arr($_POST, 'email');
        $password = safe_get_or_default_ass_arr($_POST, 'password');
        $privilege_level = safe_get_or_default_ass_arr($_POST, 'privilege_level', 0);
        
        if ($first_name === null || $last_name === null || $username === null || $email === null || $password === null) {
            echo "<p style='color: red;'>Please fill out all required fields.</p>";
            $conn = null;
            die();
        }

        // Hash password for security
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $params = array(
            ":first_name" => $first_name,
            ":last_name" => $last_name,
            ":username" => $username,
            ":email" => $email,
            ":password" => $hashed_password,
            ":privilege_level" => $privilege_level
        );

        $sql = "INSERT INTO Users (first_name, last_name, username, email, password, privilege_level) 
                VALUES (:first_name, :last_name, :username, :email, :password, :privilege_level);";

        $stmt = $conn->prepare($sql);
        if ($stmt->execute($params)) {
            echoSuccessMessage();
        } else {
            echo "<p style='color: red;'>Failed to add user. Please try again later.</p>";
        }

        $conn = null;
    }
} catch (PDOException $e) {
    echoGenericErrorMessage();
    logError(message: $e->getMessage());
    $conn = null;
    die();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Add User</title>
</head>
<body>
    <header>
        <h1>Add User</h1>
    </header>
    <div class="align">
        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="privilege_level">Privilege Level:</label>
                    <select id="privilege_level" name="privilege_level">
                        <option value="0">Not Authorized</option>
                        <option value="1">Support Staff</option>
                        <option value="2">Office Staff</option>
                        <option value="3">Medical Staff</option>
                        <option value="4">Website admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit">Save</button>
                    <button type="button" onclick="window.location.href='user_dashboard.php';">Back</button>
                    <button type="button" onclick="window.history.back();">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>