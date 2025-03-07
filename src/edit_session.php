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

    // Ensure the user has privilege level 2 or 3
    if ($_SESSION['privilege_level'] != 2 && $_SESSION['privilege_level'] != 3) {
        echo "<p style='color: red;'>You do not have permission to edit sessions.</p>";
        die();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $conn = get_db_connection();

        // Get session ID
        $id_session = safe_get_or_default_ass_arr($_POST, 'id_session');
        if ($id_session === null) {
            echoGenericErrorMessage();
            $conn = null;
            die();
        }

        // Get values from form
        $patient_id = safe_get_or_default_ass_arr($_POST, 'patient_id');
        $date = safe_get_or_default_ass_arr($_POST, 'date');
        $time = safe_get_or_default_ass_arr($_POST, 'time');
        $duration = safe_get_or_default_ass_arr($_POST, 'duration');
        $nifti_paths = safe_get_or_default_ass_arr($_POST, 'nifti_paths'); // Multiple paths
        $notes = safe_get_or_default_ass_arr($_POST, 'notes');

        if ($patient_id === null || $date === null || $time === null || $nifti_paths === null) {
            echo "<p style='color: red;'>Please fill out all required fields.</p>";
            $conn = null;
            die();
        }

        // Update session in the Sessions table
        $session_sql = "
            UPDATE Sessions
            SET patient_id = :patient_id, date = :date, time = :time, duration = :duration, notes = :notes
            WHERE id_session = :id_session;
        ";
        $session_params = [
            ":id_session" => $id_session,
            ":patient_id" => $patient_id,
            ":date" => $date,
            ":time" => $time,
            ":duration" => $duration,
            ":notes" => $notes
        ];

        $stmt = $conn->prepare($session_sql);
        if (!$stmt->execute($session_params)) {
            echo "<p style='color: red;'>Failed to update session. Please try again later.</p>";
            $conn = null;
            die();
        }

        // Delete old file paths for the session
        $delete_sequence_sql = "DELETE FROM Sessions_sequences WHERE ID_sed = :id_session;";
        $delete_stmt = $conn->prepare($delete_sequence_sql);
        $delete_stmt->execute([":id_session" => $id_session]);

        // Insert new file paths into the Sessions_sequences table
        $nifti_paths_array = explode("\n", $nifti_paths); // Split paths into an array
        $sequence_sql = "
            INSERT INTO Sessions_sequences (ID_sed, nifti_path)
            VALUES (:ID_sed, :nifti_path);
        ";
        $stmt = $conn->prepare($sequence_sql);

        foreach ($nifti_paths_array as $path) {
            $path = trim($path); // Trim whitespace
            if (empty($path)) continue; // Skip empty lines

            $sequence_params = [
                ":ID_sed" => $id_session,
                ":nifti_path" => $path
            ];

            if (!$stmt->execute($sequence_params)) {
                echo "<p style='color: red;'>Failed to associate file '$path' with session. Please try again later.</p>";
            }
        }

        echo "<p style='color: green;'>Session and file associations updated successfully!</p>";

        // Log the action
        $userid = $_SESSION['user_id'];
        $log_str = "edited session with id_session = :id_session and updated file paths.";
        log_action($userid, 'updated', 'Sessions', 'edit_session.php', $log_str, unsafe_raw_sql_ass_arr($session_sql, $session_params));

        $conn = null;
    }

    // Fetch session details for editing
    if (isset($_GET['id_session'])) {
        $id_session = safe_get_or_default_ass_arr($_GET, 'id_session');
        $conn = get_db_connection();

        $param_sql = "SELECT * FROM Sessions WHERE id_session = :id_session";
        $stmt = $conn->prepare($param_sql);
        $stmt->bindParam(":id_session", $id_session);
        $stmt->execute();
        $session = $stmt->fetch();

        if (!$session) {
            echo "<p style='color: red;'>Session not found.</p>";
            $conn = null;
            die();
        }

        // Fetch associated file paths
        $sequence_sql = "SELECT nifti_path FROM Sessions_sequences WHERE ID_sed = :id_session";
        $stmt = $conn->prepare($sequence_sql);
        $stmt->execute([":id_session" => $id_session]);
        $paths = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $nifti_paths = implode("\n", $paths); // Join paths with newline for the textarea
    }
} catch (PDOException $e) {
    echoGenericErrorMessage();
    logError(message: $e->getMessage());
    $conn = null;
    die();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Session</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .content {
            padding: 20px;
            text-align: center;
        }
        form {
            display: inline-block;
            text-align: left;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
            width: 80%;
            max-width: 600px;
        }
        input, textarea, button {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #04AA6D;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .buttons {
            display: flex;
            justify-content: space-between;
        }
        .buttons button {
            width: 30%;
        }
    </style>
</head>
<body>
    <div class="content">
        <h1>Edit Session</h1>
        <form method="POST">
            <input type="hidden" name="id_session" value="<?= safe_input($session['id_session']) ?>">

            <label for="patient_id">Patient ID:</label>
            <input type="text" id="patient_id" name="patient_id" value="<?= safe_input($session['patient_id']) ?>" required>

            <label for="date">Date:</label>
            <input type="date" id="date" name="date" value="<?= safe_input($session['date']) ?>" required>

            <label for="time">Time:</label>
            <input type="time" id="time" name="time" value="<?= safe_input($session['time']) ?>" required>

            <label for="duration">Duration (minutes):</label>
            <input type="number" id="duration" name="duration" value="<?= safe_input($session['duration']) ?>">

            <label for="nifti_paths">Nifti Paths (one per line):</label>
            <textarea id="nifti_paths" name="nifti_paths" rows="5" required><?= safe_input($nifti_paths) ?></textarea>

            <label for="notes">Notes:</label>
            <textarea id="notes" name="notes" rows="4"><?= safe_input($session['notes']) ?></textarea>

            <div class="buttons">
                <button type="submit">Save</button>
                <button type="button" onclick="window.history.back();">Back</button>
            </div>
        </form>
    </div>
</body>
</html>
