<?php
require_once "utils.php";
require_once "logger.php";

function echoSuccessMessage() {
    echo "<p style='color: green;'>Physician updated successfully!</p>";
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
        echo "<p style='color: red;'>You do not have permission to edit physicians.</p>";
        die();
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $conn = get_db_connection();

        // Retrieve input data with safe defaults
        $id = safe_get_or_default_ass_arr($_POST, 'id');
        $lastname = safe_get_or_default_ass_arr($_POST, 'lastname');
        $firstname = safe_get_or_default_ass_arr($_POST, 'firstname');
        $CF = safe_get_or_default_ass_arr($_POST, 'CF');
        $phone = safe_get_or_default_ass_arr($_POST, 'phone');
        $cell = safe_get_or_default_ass_arr($_POST, 'cell');
        $email = safe_get_or_default_ass_arr($_POST, 'email');
        $specialization = safe_get_or_default_ass_arr($_POST, 'specialization');
        $notes = safe_get_or_default_ass_arr($_POST, 'notes');

        // Fetch old physician data
        $stmt = $conn->prepare("SELECT * FROM Physicians WHERE ID_phy = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $old_physician = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$old_physician) {
            throw new Exception("Physician with ID $id not found.");
        }

        $old_physician = array_map('safe_input', $old_physician);

        // Update query and parameters
        $params = [
            ":id" => $id,
            ":lastname" => $lastname,
            ":firstname" => $firstname,
            ":CF" => $CF,
            ":phone" => $phone,
            ":cell" => $cell,
            ":email" => $email,
            ":specialization" => $specialization,
            ":notes" => $notes,
        ];

        $update_query = "
            UPDATE Physicians
            SET lastname = :lastname,
                firstname = :firstname,
                CF = :CF,
                phone = :phone,
                cell = :cell,
                email = :email,
                specialization = :specialization,
                notes = :notes
            WHERE ID_phy = :id;
        ";

        $stmt = $conn->prepare($update_query);
        if (!$stmt->execute($params)) {
            throw new Exception("Failed to update physician data.");
        }

        header("Location: physicians.php");

        // Logging changes
        $userid = $_SESSION['user_id']; // Get user ID from the session
        $log_str = sprintf(
            "Physician ID=%s updated: Old(%s) => New(%s)",
            $id,
            json_encode($old_physician),
            json_encode($params)
        );

        log_action($userid, Action::Modified, $log_str, "Physicians", "save_physician.php", unsafe_raw_sql_ass_arr($update_query, $params));

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
