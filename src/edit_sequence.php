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
        <title>Edit Sequence</title>
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
            label {
                display: block;
                margin: 10px 0;
                font-weight: bold;
            }
            input, select, button {
                width: 100%;
                padding: 10px;
                margin: 5px 0 15px 0;
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
            <h1>Edit Sequence</h1>
            <form method="POST" action="save_sequence.php">
                <input type="hidden" name="id" value="<?= safe_input($sequence['ID_seq']) ?>">

                <label for="seq_group">Sequence Group:</label>
                <select id="seq_group" name="seq_group" required>
                    <option value="MRI" <?= safe_input($sequence['seq_group']) == 'MRI' ? 'selected' : '' ?>>MRI</option>
                    <option value="FMRI" <?= safe_input($sequence['seq_group']) == 'FMRI' ? 'selected' : '' ?>>FMRI</option>
                    <option value="DTI" <?= safe_input($sequence['seq_group']) == 'DTI' ? 'selected' : '' ?>>DTI</option>
                    <option value="SPETTRO" <?= safe_input($sequence['seq_group']) == 'SPETTRO' ? 'selected' : '' ?>>SPETTRO</option>
                </select>

                <label for="seq_name">Sequence Name:</label>
                <input type="text" id="seq_name" name="seq_name" value="<?= safe_input($sequence['seq_name']) ?>" required>

                <label for="deprecated">Deprecated:</label>
                <select id="deprecated" name="deprecated">
                    <option value="no" <?= safe_input($sequence['deprecated']) == 'no' ? 'selected' : '' ?>>No</option>
                    <option value="yes" <?= safe_input($sequence['deprecated']) == 'yes' ? 'selected' : '' ?>>Yes</option>
                </select>

                <div class="buttons">
                    <button type="submit">Save</button>
                    <button type="button" onclick="window.history.back();">Back</button>
                    <button type="button" onclick="window.location.href='sequences.php';">Cancel</button>
                </div>
            </form>
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
