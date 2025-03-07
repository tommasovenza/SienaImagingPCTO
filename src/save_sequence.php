<?php
require_once "utils.php";
require_once "logger.php";

function echoSuccessMessage() {
    echo "<p style='color: green;'>Sequence updated successfully!</p>";
}

try {

    session_start();

    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['privilege_level'])) {
        echoUserNotFoundMessage();
        die();
    }

    // Ensure the user has privilege level 2 or 3
    if ($_SESSION['privilege_level'] != 2 && $_SESSION['privilege_level'] != 3) {
        echo "<p style='color: red;'>You do not have permission to edit sequences.</p>";
        die();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $conn = get_db_connection();

        // Retrieve input data with safe defaults
        $id = safe_get_or_default_ass_arr($_POST, 'id');
        $seq_group = safe_get_or_default_ass_arr($_POST, 'seq_group');
        $seq_name = safe_get_or_default_ass_arr($_POST, 'seq_name');
        $deprecated = safe_get_or_default_ass_arr($_POST, 'deprecated');

        // Fetch old sequence data
        $stmt = $conn->prepare("SELECT * FROM Sequences WHERE ID_seq = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $old_seq = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$old_seq) {
            throw new Exception("Sequence with ID $id not found.");
        }

        $old_seq = array_map('safe_input', $old_seq);

        // Update query and parameters
        $params = [
            ":id" => $id,
            ":seq_group" => $seq_group,
            ":seq_name" => $seq_name,
            ":deprecated" => $deprecated
        ];

        $update_query = "
            UPDATE Sequences
            SET seq_group = :seq_group,
                seq_name = :seq_name,
                deprecated = :deprecated
            WHERE ID_seq = :id;
        ";

        $stmt = $conn->prepare($update_query);
        if (!$stmt->execute($params)) {
            throw new Exception("Failed to update sequence data.");
        }

        header("Location: sequences.php");

        // Logging changes
        $userid = $_SESSION['user_id']; // Get user ID from the session
        $log_str = sprintf(
            "Sequence ID=%s updated: Old(%s) => New(%s)",
            $id,
            json_encode($old_seq),
            json_encode($params)
        );

        log_action($userid, Action::Modified, $log_str, "Sequences", "save_sequence.php", unsafe_raw_sql_ass_arr($update_query, $params));

        $conn = null;
    }
} catch (PDOException $e) {
    echoGenericErrorMessage();
    error_log("Database error: " . $e->getMessage());
    $conn = null;
    die();
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: {$e->getMessage()}</p>";
    error_log("Error: " . $e->getMessage());
    $conn = null;
}
?>
