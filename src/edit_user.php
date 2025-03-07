<?php
require_once "utils.php";
require_once "logger.php";

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
        echo "<p style='color: red;'>You do not have permission to edit users.</p>";
        die();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $conn = get_db_connection();
        
        $id = safe_get_or_default_ass_arr($_POST, 'id');
        if ($id === null) {
            echoGenericErrorMessage();
            $conn = null;
            die();
        }

        $param_sql = "SELECT * FROM Users WHERE ID = :id";
        $stmt = $conn->prepare($param_sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $user = $stmt->fetch();

        // Log the action using the log_action function
        $userid = $_SESSION['user_id']; // Get user ID from the session
        $log_str = "Accessed edit page for user with ID = :id";
        $log_params = [":id" => $id];
        log_action($userid, 'viewed', 'Users', 'edit_user.php', $log_str, unsafe_raw_sql_ass_arr($param_sql, $log_params));
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit User</title>
        <style>
            /* Add your existing styles here */
        </style>
    </head>
    <body>
        <div class="content">
            <h1>Edit User</h1>
            <form method="POST" action="save_user.php">
                <input type="hidden" name="id" value="<?= safe_input($user['ID']) ?>">

                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" value="<?= safe_input($user['first_name']) ?>" required>

                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" value="<?= safe_input($user['last_name']) ?>" required>

                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?= safe_input($user['username']) ?>" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= safe_input($user['email']) ?>" required>

                <label for="privilege_level">Privilege Level:</label>
                <select id="privilege_level" name="privilege_level">
                    <option value="0" <?= safe_input($user['privilege_level']) == '0' ? 'selected' : '' ?>>User</option>
                    <option value="1" <?= safe_input($user['privilege_level']) == '1' ? 'selected' : '' ?>>Admin</option>
                    <option value="2" <?= safe_input($user['privilege_level']) == '2' ? 'selected' : '' ?>>Super Admin</option>
                    <option value="3" <?= safe_input($user['privilege_level']) == '3' ? 'selected' : '' ?>>System Admin</option>
                </select>

                <label for="last_login_date">Last Login Date:</label>
                <input type="text" id="last_login_date" name="last_login_date" value="<?= safe_input($user['last_login_date']) ?>" readonly>

                <div class="buttons">
                    <button type="submit">Save</button>
                    <button type="button" onclick="window.history.back();">Back</button>
                </div>
            </form>
        </div>
    </body>
    </html>
    <?php
} catch (PDOException $e) {
    echoGenericErrorMessage();
    logError(message: $e->getMessage()); //logs in case of failure
    $conn = null;
    die();
}
?>
