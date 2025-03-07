<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Add Sequence</title>
</head>
<body>
    <header>
        <h1>Add Sequence</h1>
    </header>

        

<?php

    require_once "utils.php";
    require_once "logger.php";

    function echoSuccessMessage() {
        echo "<p style='color: green;'>Sequence added successfully!</p>";
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
            echo "<p style='color: red;'>You do not have permission to add sequences.</p>";
            die();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $conn = get_db_connection();

            $seq_group = safe_get_or_default_ass_arr($_POST, 'seqgroup');
            $seq_name = safe_get_or_default_ass_arr($_POST, 'seqname');
            $deprecated = safe_get_or_default_ass_arr($_POST, 'deprecated');

            $params = array(
                ":seq_group" => $seq_group,
                ":seq_name" => $seq_name,
                ":deprecated" => $deprecated,
            );

            $param_sql = "
                INSERT INTO Sequences (seq_group, seq_name, deprecated)
                VALUES (:seq_group, :seq_name, :deprecated)
                RETURNING ID_seq;
            ";
            
            $stmt = $conn->prepare($param_sql);
            if ($stmt->execute($params)) {
                echoSuccessMessage();
            } else {
                echoGenericErrorMessage();
            }

            $id_seq = $stmt->fetch()['ID_seq'];

            $userid = $_SESSION['user_id']; // Get user ID from the session
            $log_str = "a new sequence id=:id";
            $log_str = str_replace(":id", $id_seq, $log_str);

            // Log the action using the log_action function
            log_action($userid, 'added', 'Sequences', 'add_sequence.php', $log_str, unsafe_raw_sql_ass_arr($param_sql, $params));

            $conn = null;
        }
    } catch (PDOException $e) {
        echoGenericErrorMessage();
        logError(message: $e->getMessage()); // Logs in case of failure
        $conn = null;
        die();
    }
?>
    <div class="alline">
        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <label for="seqgroup">Sequence Group:</label>
                    <input type="text" id="seqgroup" name="seqgroup" required>
                </div>
                <div class="form-group">
                    <label for="seqname">Sequence Name:</label>
                    <input type="text" id="seqname" name="seqname" required>
                </div>
                
                <label for="deprecated">Deprecated:</label>
                <div class="form-groups">
                    <div class="point-set">
                        <input type="radio" id="yes" name="deprecated" value="yes">
                        <label for="yes">Yes</label>
                    </div>
                    <div class="point-set">
                        <input type="radio" id="no" name="deprecated" value="no">
                        <label for="no">No</label>
                    </div>
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
