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
    
    // Ensure the user is a super admin (privilege level 4)
    if ($_SESSION['privilege_level'] != 4) { /** */
        echo "<p style='color: red;'>You do not have permission to edit institutions.</p>";
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

        $param_sql = "SELECT * FROM Institutions WHERE ID_inst = :id";
        $stmt = $conn->prepare($param_sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $institution = $stmt->fetch();

        // Log the action using the log_action function
        $userid = $_SESSION['user_id']; // Get user ID from the session
        $log_str = "Accessed edit page for institution with ID_inst = :id";
        $log_params = [":id" => $id];
        log_action($userid, 'viewed', 'Institutions', 'edit_institution.php', $log_str, unsafe_raw_sql_ass_arr($param_sql, $log_params));
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="style.css">
        <title>Edit Institution</title>
    </head>
    <body>
        <header>
            <h1>Edit Institution</h1>
        </header>
        <div class="alline">
            <div class="form-container">
                <form method="POST" action="save_institution.php">
                    <input type="hidden" name="id" value="<?= safe_input($institution['ID_inst']) ?>">
                    <div class="form-group">
                        <label for="inst_name">Institution Name:</label>
                        <input type="text" id="inst_name" name="inst_name" value="<?= safe_input($institution['inst_name']) ?>" required>
                    </div>    
                    <div class="form-group">
                        <label for="inst_city">City:</label>
                        <input type="text" id="inst_city" name="inst_city" value="<?= safe_input($institution['inst_city']) ?>" required>
                    </div>      
                    <div class="form-group">
                        <button type="submit">Save</button>
                        <button type="button" onclick="window.history.back();">Back</button>
                    </div>  
                </form>
            </div>
        </div>
    </body>
    </html>
    <?php
} catch (PDOException $e) {
    echoGenericErrorMessage();
    logError(message: $e->getMessage()); //logs in case of failure
    $conn = null;
    die();
}
?>
