<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Add Session</title>
</head>
<body>
    <header>
        <h1>Add Session</h1>
    </header>
    <div class="form-container">
        

<?php
require_once "utils.php";
require_once "logger.php";

function echoSuccessMessage() {
    echo "<p style='color: green;'>Session and files added successfully!</p>";
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

    // Ensure the user has privilege level 2 or 3
    if ($_SESSION['privilege_level'] != 2 && $_SESSION['privilege_level'] != 3) {
        echo "<p style='color: red;'>You do not have permission to add sessions.</p>";
        die();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $conn = get_db_connection();

        // Get values from the form
        $session_date = safe_get_or_default_ass_arr($_POST, 'session_date');
        $filename_num = safe_get_or_default_ass_arr($_POST, 'filename_num');
        $storage = safe_get_or_default_ass_arr($_POST, 'storage');
        $optical_disk = safe_get_or_default_ass_arr($_POST, 'optical_disk');
        $richiesta = safe_get_or_default_ass_arr($_POST, 'richiesta');
        $reg_provenienza = safe_get_or_default_ass_arr($_POST, 'reg_provenienza');
        $study = safe_get_or_default_ass_arr($_POST, 'study');
        $nifti_paths = safe_get_or_default_ass_arr($_POST, 'nifti_paths'); // Modified to handle multiple paths

        if ($session_date === null || $nifti_paths === null) {
            echo "<p style='color: red;'>Please fill out all required fields.</p>";
            $conn = null;
            die();
        }

        // Insert into the Sessions table
        $session_sql = "
            INSERT INTO Sessions (session_date, filename_num, storage, optical_disk, richiesta, reg_provenienza, study)
            VALUES (:session_date, :filename_num, :storage, :optical_disk, :richiesta, :reg_provenienza, :study);
        ";
        $session_params = [
            ":session_date" => $session_date,
            ":filename_num" => $filename_num,
            ":storage" => $storage,
            ":optical_disk" => $optical_disk,
            ":richiesta" => $richiesta,
            ":reg_provenienza" => $reg_provenienza,
            ":study" => $study
        ];

        $stmt = $conn->prepare($session_sql);
        if (!$stmt->execute($session_params)) {
            echo "<p style='color: red;'>Failed to add session. Please try again later.</p>";
            $conn = null;
            die();
        }

        // Get the last inserted ID_sed
        $session_id = $conn->lastInsertId();

        // Insert each file path into the Sessions_sequences table
        $nifti_paths_array = explode("\n", $nifti_paths); // Split multiple paths into an array
        $sequence_sql = "
            INSERT INTO Sessions_sequences (ID_sed, nifti_path)
            VALUES (:ID_sed, :nifti_path);
        ";
        $stmt = $conn->prepare($sequence_sql);

        foreach ($nifti_paths_array as $path) {
            $path = trim($path); // Trim whitespace
            if (empty($path)) continue; // Skip empty lines

            $sequence_params = [
                ":ID_sed" => $session_id,
                ":nifti_path" => $path
            ];

            if (!$stmt->execute($sequence_params)) {
                echo "<p style='color: red;'>Failed to associate file '$path' with session. Please try again later.</p>";
            }
        }

        echoSuccessMessage();

        // Log the action
        $userid = $_SESSION['user_id'];
        $log_str = "added a new session (ID_sed: $session_id) with multiple nifti_paths";
        log_action($userid, 'added', 'Sessions_sequences', 'add_session.php', $log_str, unsafe_raw_sql_ass_arr($sequence_sql, $sequence_params));

        $conn = null;
    }
} catch (PDOException $e) {
    echoGenericErrorMessage();
    logError(message: $e->getMessage()); // logs in case of failure
    $conn = null;
    die();
}
?>
    <div class="alline">
        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <label for="session_date">Session Date:</label>
                    <input type="date" id="session_date" name="session_date" required>
                </div>
                <div class="form-group">
                    <label for="filename_num">Filename Number:</label>
                    <input type="text" id="filename_num" name="filename_num">
                </div>
                <div class="form-group">
                    <label for="storage">Storage:</label>
                    <input type="text" id="storage" name="storage">
                </div>
                <div class="form-group">
                    <label for="optical_disk">Optical Disk:</label>
                    <input type="text" id="optical_disk" name="optical_disk">
                </div>
                <div class="form-group">
                    <label for="richiesta">Request (Richiesta):</label>
                    <input type="text" id="richiesta" name="richiesta">
                </div>
                <div class="form-group">
                    <label for="reg_provenienza">Region of Origin (Reg Provenienza):</label>
                    <input type="text" id="reg_provenienza" name="reg_provenienza">
                </div>    
                <div class="form-group">
                    <label for="study">Study:</label>
                    <input type="text" id="study" name="study">
                </div>
                <div class="form-group">
                    <label for="nifti_paths">Nifti Paths (one per line):</label>
                    <textarea id="nifti_paths" name="nifti_paths" rows="5" required></textarea>
                </div>
                <div class="form-group">
                    <button type="submit">Save</button>
                    <button type="button" onclick="window.history.back();">Back</button>
                    <button type="button" onclick="window.location.href='sessions.php';">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
