<?php
require_once "utils.php";
require_once "logger.php";

function echoSuccessMessage() {
    echo "<p style='color: green;'>Session deleted successfully!</p>";
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

    // Ensure the user is a super admin (privilege level 4)
    if ($_SESSION['privilege_level'] != 4) {
        echo "<p style='color: red;'>You do not have permission to delete sessions.</p>";
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

        $stmt = $conn->prepare("SELECT * FROM Sessions WHERE session_id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $session = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$session) {
            echo "<p style='color: red;'>Session not found.</p>";
            $conn = null;
            die();
        }

        // Retrieve session details
        $session_title = safe_input($session['title']);
        $session_date = safe_input($session['session_date']);
        $nifti_path = safe_input($session['nifti_path']); // Include nifti_path in log

        // Delete the session
        $params = [":id" => $id];
        $param_sql = "DELETE FROM Sessions WHERE session_id = :id";

        $stmt = $conn->prepare($param_sql);
        if ($stmt->execute($params)) {
            echoSuccessMessage();
        } else {
            echoGenericErrorMessage();
        }

        // Log the action
        $userid = $_SESSION['user_id'];
        $log_str = "Deleted session id=:id (title=:session_title, date=:session_date, nifti_path=:nifti_path)";
        $log_params = [
            ":id" => $id,
            ":session_title" => $session_title,
            ":session_date" => $session_date,
            ":nifti_path" => $nifti_path,
        ];

        log_action(
            $userid,
            'deleted',
            'Sessions',
            'delete_session.php',
            unsafe_raw_sql_ass_arr($log_str, $log_params),
            unsafe_raw_sql_ass_arr($param_sql, $params)
        );

        $conn = null;
    }
} catch (PDOException $e) {
    echoGenericErrorMessage();
    error_log("Error: " . $e->getMessage());
    $conn = null;
    die();
}
?>
