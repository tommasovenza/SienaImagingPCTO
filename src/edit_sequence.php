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
        echo "<p style='color: red;'>You do not have permission to edit sequences.</p>";
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
    
        $param_sql = "SELECT * FROM Sequences WHERE ID_seq = :id";
        $stmt = $conn->prepare($param_sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $sequence = $stmt->fetch();

        // Log the action using the log_action function
        $userid = $_SESSION['user_id']; // Get user ID from the session
        $log_str = "Accessed edit page for sequence with ID_seq = :id";
        $log_params = [":id" => $id];
        log_action($userid, 'viewed', 'Sequences', 'edit_sequence.php', $log_str, unsafe_raw_sql_ass_arr($param_sql, $log_params));
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="style.css">
        <title>Edit Sequence</title>
    </head>
    <body>
        <header>
            <h1>Edit Sequence</h1>
        </header>
        <div class="alline">
            <div class="form-container">
                <form method="POST" action="save_sequence.php">
                    <input type="hidden" name="id" value="<?= safe_input($sequence['ID_seq']) ?>">
                    <div class="form-group">
                        <label for="seq_group">Sequence Group:</label>
                        <select id="seq_group" name="seq_group" required>
                            <option value="MRI" <?= safe_input($sequence['seq_group']) == 'MRI' ? 'selected' : '' ?>>MRI</option>
                            <option value="FMRI" <?= safe_input($sequence['seq_group']) == 'FMRI' ? 'selected' : '' ?>>FMRI</option>
                            <option value="DTI" <?= safe_input($sequence['seq_group']) == 'DTI' ? 'selected' : '' ?>>DTI</option>
                            <option value="SPETTRO" <?= safe_input($sequence['seq_group']) == 'SPETTRO' ? 'selected' : '' ?>>SPETTRO</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="seq_name">Sequence Name:</label>
                        <input type="text" id="seq_name" name="seq_name" value="<?= safe_input($sequence['seq_name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="deprecated">Deprecated:</label>
                        <select id="deprecated" name="deprecated">
                            <option value="no" <?= safe_input($sequence['deprecated']) == 'no' ? 'selected' : '' ?>>No</option>
                            <option value="yes" <?= safe_input($sequence['deprecated']) == 'yes' ? 'selected' : '' ?>>Yes</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit">Save</button>
                        <button type="button" onclick="window.history.back();">Back</button>
                        <button type="button" onclick="window.location.href='sequences.php';">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </body>
    </html>
    <?php
} catch (PDOException $e) {
    echoGenericErrorMessage();
    $conn = null;
    logError(message: $e->getMessage()); // Logs in case of failure
    die();
}
?>
