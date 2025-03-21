<?php
require_once "utils.php";
require_once "logger.php";

function echoSuccessMessage() {
    echo "<p style='color: green;'>Session updated successfully!</p>";
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
        echo "<p style='color: red;'>You do not have permission to edit sessions.</p>";
        die();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $conn = get_db_connection();

        // Fetch POST parameters
        $id = safe_get_or_default_ass_arr($_POST, 'id');
        $title = safe_get_or_default_ass_arr($_POST, 'title');
        $description = safe_get_or_default_ass_arr($_POST, 'description');
        $date = safe_get_or_default_ass_arr($_POST, 'date');
        $duration = safe_get_or_default_ass_arr($_POST, 'duration');
        $nifti_paths_raw = safe_get_or_default_ass_arr($_POST, 'nifti_paths');
        $notes = safe_get_or_default_ass_arr($_POST, 'notes');

        // Split multiple nifti paths by newline
        $nifti_paths = array_filter(array_map('trim', explode("\n", $nifti_paths_raw)));

        // Retrieve the current session record
        $stmt = $conn->prepare("SELECT * FROM Sessions WHERE session_id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $old_session = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$old_session) {
            echoGenericErrorMessage();
            $conn = null;
            die();
        }

        // Update the session record in Sessions table
        $params = [
            ":id" => $id,
            ":title" => $title,
            ":description" => $description,
            ":date" => $date,
            ":duration" => $duration,
            ":notes" => $notes
        ];

        $param_sql = "UPDATE Sessions
                      SET title = :title, description = :description, session_date = :date, 
                          duration = :duration, notes = :notes
                      WHERE session_id = :id;";

        $stmt = $conn->prepare($param_sql);
        if (!$stmt->execute($params)) {
            echoGenericErrorMessage();
            $conn = null;
            die();
        }

        // Delete old nifti paths for the session
        $delete_sql = "DELETE FROM Sessions_sequences WHERE ID_sed = :id";
        $stmt = $conn->prepare($delete_sql);
        $stmt->execute([":id" => $id]);

        // Insert new nifti paths into Sessions_sequences table
        $insert_sql = "INSERT INTO Sessions_sequences (ID_sed, nifti_path) VALUES (:id, :nifti_path)";
        $stmt = $conn->prepare($insert_sql);

        foreach ($nifti_paths as $path) {
            $stmt->execute([
                ":id" => $id,
                ":nifti_path" => $path
            ]);
        }

        // Log the action
        $userid = $_SESSION['user_id'];
        $log_str = "Updated session ID=:id from (title=:old_title, description=:old_description, session_date=:old_date, 
                     duration=:old_duration, notes=:old_notes) 
                     to (title=:title, description=:description, session_date=:date, 
                     duration=:duration, notes=:notes) with new nifti paths.";

        $log_params = array_merge(
            [
                ":id" => $id,
                ":old_title" => $old_session['title'],
                ":old_description" => $old_session['description'],
                ":old_date" => $old_session['session_date'],
                ":old_duration" => $old_session['duration'],
                ":old_notes" => $old_session['notes']
            ],
            $params
        );

        log_action($userid, 'modified', 'Sessions', 'save_session.php', unsafe_raw_sql_ass_arr($log_str, $log_params), unsafe_raw_sql_ass_arr($param_sql, $params));

        // Redirect to the sessions list page
        header("Location: sessions.php");
        exit();
    }   
    // Exception
} catch (PDOException $e) {
    echoGenericErrorMessage();
    error_log("Error: " . $e->getMessage());
    $conn = null;
    die();
}

?>
