<?php
require_once "utils.php";
require_once "logger.php";

function echoSuccessMessage() {
    echo "<p style='color: green;'>User updated successfully!</p>";
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
        echo "<p style='color: red;'>You do not have permission to edit users.</p>";
        die();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $conn = get_db_connection();

        // Fetch POST parameters
        $id = safe_get_or_default_ass_arr($_POST, 'id');
        $first_name = safe_get_or_default_ass_arr($_POST, 'first_name');
        $last_name = safe_get_or_default_ass_arr($_POST, 'last_name');
        $username = safe_get_or_default_ass_arr($_POST, 'username');
        $email = safe_get_or_default_ass_arr($_POST, 'email');
        $privilege_level = safe_get_or_default_ass_arr($_POST, 'privilege_level');
        $last_login_date = safe_get_or_default_ass_arr($_POST, 'last_login_date'); // Optional field, could be readonly

        // Retrieve the current user record
        $stmt = $conn->prepare("SELECT * FROM Users WHERE ID = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $old_user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$old_user) {
            echoGenericErrorMessage();
            $conn = null;
            die();
        }

        // Update the user record
        $params = [
            ":id" => $id,
            ":first_name" => $first_name,
            ":last_name" => $last_name,
            ":username" => $username,
            ":email" => $email,
            ":privilege_level" => $privilege_level,
            ":last_login_date" => $last_login_date
        ];

        $param_sql = "UPDATE Users
                      SET first_name = :first_name, last_name = :last_name, username = :username, 
                          email = :email, privilege_level = :privilege_level, last_login_date = :last_login_date
                      WHERE ID = :id;";

        $stmt = $conn->prepare($param_sql);
        if ($stmt->execute($params)) {
            header("Location: users.php"); // Redirect to users page or wherever you need
            exit();
        } else {
            echoGenericErrorMessage();
        }

        // Log the action
        $userid = $_SESSION['user_id']; // Get user ID from the session
        $log_str = "Updated user ID=:id from (first_name=:old_first_name, last_name=:old_last_name, username=:old_username, 
                     email=:old_email, privilege_level=:old_privilege_level, last_login_date=:old_last_login_date) 
                     to (first_name=:first_name, last_name=:last_name, username=:username, email=:email, 
                     privilege_level=:privilege_level, last_login_date=:last_login_date)";

        $log_params = array_merge(
            [
                ":id" => $id,
                ":old_first_name" => $old_user['first_name'],
                ":old_last_name" => $old_user['last_name'],
                ":old_username" => $old_user['username'],
                ":old_email" => $old_user['email'],
                ":old_privilege_level" => $old_user['privilege_level'],
                ":old_last_login_date" => $old_user['last_login_date']
            ],
            $params
        );

        log_action($userid, 'modified', 'Users', 'save_user.php', unsafe_raw_sql_ass_arr($log_str, $log_params), unsafe_raw_sql_ass_arr($param_sql, $params));

        $conn = null;
    }
} catch (PDOException $e) {
    echoGenericErrorMessage();
    error_log("Error: " . $e->getMessage());
    $conn = null;
    die();
}
?>
