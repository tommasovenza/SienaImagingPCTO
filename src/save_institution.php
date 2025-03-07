<?php
require_once "utils.php";
require_once "logger.php";

function echoSuccessMessage() {
    echo "<p style='color: green;'>Institution updated successfully!</p>";
}

try {

    session_start();

    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['privilege_level'])) {
        echoUserNotFoundMessage(); 
        die();
    }
    
    // Ensure the user is a super admin (privilege level 4)
    if ($_SESSION['privilege_level'] != 4) {
        echo "<p style='color: red;'>You do not have permission to edit institutions.</p>";
        die();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $conn = get_db_connection();
        
        $id = safe_get_or_default_ass_arr($_POST, 'id');
        $inst_name = safe_get_or_default_ass_arr($_POST, 'inst_name');
        $inst_city = safe_get_or_default_ass_arr($_POST, 'inst_city');

        if ($id === null || $inst_city === null || $inst_name === null) {
            echoGenericErrorMessage();
            $conn = null;
            die();
        }

        // Fetch the current record for logging comparison
        $stmt = $conn->prepare("SELECT * FROM Institutions WHERE ID_inst = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $old_inst = $stmt->fetch(PDO::FETCH_ASSOC);

        $old_inst_name = safe_input($old_inst['inst_name']);
        $old_inst_city = safe_input($old_inst['inst_city']);

        // Check if there are changes to save
        if ($old_inst_name == $inst_name && $old_inst_city == $inst_city) {
            header("Location: institutions.php");
            $conn = null;
            die();
        }
        
        // Update the institution record
        $params = [":id" => $id, ":inst_name" => $inst_name, ":inst_city" => $inst_city];
        $param_sql = "UPDATE Institutions
                      SET inst_name = :inst_name,
                          inst_city = :inst_city
                      WHERE ID_inst = :id;";

        $stmt = $conn->prepare($param_sql);
        if ($stmt->execute($params)) {
            header("Location: institutions.php");
        } else {
            echoGenericErrorMessage();
        }

        // Log the action using the log_action function
        $userid = $_SESSION['user_id']; // Get user ID from the session
        $log_str = "Updated institution ID=:id from (name=:old_inst_name, city=:old_inst_city) to (name=:inst_name, city=:inst_city)";
        $log_params = [
            ":id" => $id,
            ":old_inst_name" => $old_inst_name,
            ":old_inst_city" => $old_inst_city,
            ":inst_name" => $inst_name,
            ":inst_city" => $inst_city
        ];

        log_action($userid, 'modified', 'Institutions', 'save_institution.php', unsafe_raw_sql_ass_arr($log_str, $log_params), unsafe_raw_sql_ass_arr($param_sql, $params));

        $conn = null;
    }
} catch (PDOException $e) {
    echoGenericErrorMessage();
    error_log("Error: " . $e->getMessage());
    $conn = null;
    die();
}
?>
