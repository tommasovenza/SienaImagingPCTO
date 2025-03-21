<?php
require_once "utils.php";
require_once "logger.php";

function echoSuccessMessage() {
    echo "<p style='color: green;'>Institution deleted successfully!</p>";
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
        echo "<p style='color: red;'>You do not have permission to delete institutions.</p>";
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

        $stmt = $conn->prepare("SELECT * FROM Institutions WHERE ID_inst = :id");
		$stmt->bindParam(":id", $id);
		$stmt->execute();
		$inst = $stmt->fetch(PDO::FETCH_ASSOC);
        $inst_name = safe_input($inst['inst_name']);
        $inst_city = safe_input($inst['inst_city']);

        $params = array(":id"=>$id);
        $param_sql = "DELETE FROM Institutions WHERE ID_inst = :id";
        
        $stmt = $conn->prepare($param_sql);
        if ($stmt->execute($params)) {
            echoSuccessMessage();
        } else {
            echoGenericErrorMessage();
        }

        $userid = $_SESSION['user_id']; // Get user ID from the session
        $log_str = "an institution id=:id Institution(name=:inst_name, city=:inst_city)";
        $log_str = str_replace(":id", $id, $log_str);
        $log_str = str_replace(":inst_name", $inst_name, $log_str);
        $log_str = str_replace(":inst_city", $inst_city, $log_str);
        db_log($userid, Action::Deleted, $log_str, "Institutions", "delete_institution.php", unsafe_raw_sql_ass_arr($param_sql, $params));

        $conn = null;

        // Log the action using the log_action function
        $userid = $_SESSION['user_id']; // Get user ID from the session
        $log_str = "Accessed edit page for sequence with ID_seq = :id";
        $log_params = [":id" => $id];
        log_action($userid, 'deleted', 'Institutions', 'delete_institution.php', $log_str, unsafe_raw_sql_ass_arr($param_sql, $log_params));
    }
} catch (PDOException $e) {
    echoGenericErrorMessage();
    $conn = null;
    die();
}
?>
