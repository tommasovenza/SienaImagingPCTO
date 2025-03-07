<?php

session_start();

// Secure session cookie settings
ini_set('session.cookie_secure', '1'); // Ensure cookies are sent over HTTPS only
ini_set('session.cookie_httponly', '1'); // Prevent access to cookies via JavaScript
ini_set('session.cookie_samesite', 'Strict'); // Prevent CSRF attacks

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

// Include utility functions and logging
require_once "utils.php";
require_once "logger.php";

function validate_username($username) {
    return preg_match('/^[a-zA-Z0-9]{3,20}$/', $username);
}


try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            logError("CSRF token mismatch.");
            echoGenericErrorMessage();
            die();
        }

        $conn = get_db_connection();

        $username = safe_get_or_default_ass_arr($_POST, 'username', '');
        $password = $_POST['password'] ?? '';

        // Input validation
        if (!validate_username($username) || empty($password)) {
            echoGenericErrorMessage();
            $conn = null;
            die();
        }

        $params = array(":username" => $username);

        $param_sql = "SELECT ID, username, privilege_level, password FROM Users WHERE username = :username LIMIT 1";

        $stmt = $conn->prepare($param_sql);
        if (!$stmt->execute($params)) {
            logError("Database query failed: " . json_encode($stmt->errorInfo()));
            echoGenericErrorMessage();
            $conn = null;
            die();
        }

        $user = $stmt->fetch();

        if ($user === false || !password_verify($password, $user['password'])) {
            logError("Invalid login attempt for user: $username");
            echoGenericErrorMessage();
            $conn = null;
            die();
        }

        // Update last login timestamp
        $update_sql = "UPDATE Users SET last_login_date = NOW() WHERE ID = :id";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bindParam(':id', $user['ID'], PDO::PARAM_INT);
        $update_stmt->execute();

        // Regenerate session ID for security
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['ID'];
        $_SESSION['username'] = $username;
        $_SESSION['privilege_level'] = $user['privilege_level'];  // Store privilege level

        // Inspect the session to check if privilege_level is set
        var_dump($_SESSION);  // This will show the session values for debugging

        // Redirect to dashboard
        header("Location: user_dashboard.php");
        exit;
    } else {
        // Generate CSRF token for the form
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
} catch (PDOException $e) {
    logError("Database error: " . $e->getMessage());
    echoGenericErrorMessage();
    $conn = null;
    die();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="Strict-Transport-Security" content="max-age=31536000; includeSubDomains">
    <link rel="stylesheet" href="style.css">
    <title>Secure Login</title>
    <style>

        .link {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <header>
        <h1>Login</h1>
    </header>
    <div class="alline">
        <div class="form-container">
            <form method="POST" action="login.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" pattern="[a-zA-Z0-9]{3,20}" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" minlength="8" required>
                </div>
                <div class="form-group">
                    <button type="submit" name="login">Login</button>
                    <button type="button" onclick="window.location.href='homepage.php';">Back</button>
                </div>
                <div>
                <a>Don't have an account? </a><a href="signup.php">Sign up</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
