<?php
require_once "utils.php";
require_once "logger.php";

function echoSuccessMessage() {
    echo "<p style='color: green;'>Pathology updated successfully!</p>";
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
        echo "<p style='color: red;'>You do not have permission to edit pathologies.</p>";
        die();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $conn = get_db_connection();

        $id = safe_get_or_default_ass_arr($_POST, 'id');
        $deprecated = safe_get_or_default_ass_arr($_POST, 'deprecated');

        if ($id === null || $deprecated === null) {
            echo "<p style='color: red;'>Invalid input. Please try again.</p>";
            $conn = null;
            die();
        }

        // Validate the deprecated value (should be 0 or 1)
        if ($deprecated !== '0' && $deprecated !== '1') {
            echo "<p style='color: red;'>Invalid value for deprecated. Must be 0 or 1.</p>";
            $conn = null;
            die();
        }

        // Fetch the current record for logging comparison
        $stmt = $conn->prepare("SELECT * FROM Pathologies WHERE ID_patol = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $old_pathology = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$old_pathology) {
            echo "<p style='color: red;'>Pathology not found.</p>";
            $conn = null;
            die();
        }

        $old_deprecated = $old_pathology['deprecated'];

        // Check if there are changes to save
        if ($old_deprecated == $deprecated) {
            header("Location: pathologies.php");
            $conn = null;
            die();
        }

        // Update the pathology record
        $params = [":id" => $id, ":deprecated" => $deprecated];
        $param_sql = "UPDATE Pathologies
                      SET deprecated = :deprecated
                      WHERE ID_patol = :id;";

        $stmt = $conn->prepare($param_sql);
        if ($stmt->execute($params)) {
            header("Location: pathologies.php");
        } else {
            echo "<p style='color: red;'>Failed to update the pathology.</p>";
        }

        // Log the action using the log_action function
        $userid = $_SESSION['user_id']; // Get user ID from the session
        $log_str = "Updated pathology ID=:id from deprecated=:old_deprecated to deprecated=:deprecated";
        $log_params = [
            ":id" => $id,
            ":old_deprecated" => $old_deprecated,
            ":deprecated" => $deprecated
        ];

        log_action($userid, 'modified', 'Pathologies', 'save_pathologies.php', unsafe_raw_sql_ass_arr($log_str, $log_params), unsafe_raw_sql_ass_arr($param_sql, $params));

        $conn = null;
    }
} catch (PDOException $e) {
    echoGenericErrorMessage(); 
    error_log("Error: " . $e->getMessage());
    $conn = null;
    die();
}
?>
