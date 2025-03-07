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
    if ($_SESSION['privilege_level'] != 4) {
        echo "<p style='color: red;'>You do not have permission to edit pathologies.</p>";
        die();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $conn = get_db_connection();

        $id = safe_get_or_default_ass_arr($_POST, 'id');
        if ($id === null) {
            echo "<p style='color: red;'>Invalid pathology ID.</p>";
            $conn = null;
            die();
        }

        // Fetch the pathology details
        $param_sql = "SELECT * FROM Pathologies WHERE ID_patol = :id";
        $stmt = $conn->prepare($param_sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $pathology = $stmt->fetch();

        if (!$pathology) {
            echo "<p style='color: red;'>Pathology not found.</p>";
            $conn = null;
            die();
        }

        // Log the action
        $userid = $_SESSION['user_id'];
        $log_str = "Accessed edit page for pathology with ID_patol = :id";
        $log_params = [":id" => $id];
        log_action($userid, 'viewed', 'Pathologies', 'edit_pathologies.php', $log_str, unsafe_raw_sql_ass_arr($param_sql, $log_params));
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit Pathology</title>
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
            select, button {
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
        </style>
    </head>
    <body>
        <div class="content">
            <h1>Edit Pathology</h1>
            <form method="POST" action="save_pathology.php">
                <input type="hidden" name="id" value="<?= safe_input($pathology['ID_patol']) ?>">

                <label for="deprecated">Deprecated:</label>
                <select id="deprecated" name="deprecated" required>
                    <option value="0" <?= !$pathology['deprecated'] ? 'selected' : '' ?>>No</option>
                    <option value="1" <?= $pathology['deprecated'] ? 'selected' : '' ?>>Yes</option>
                </select>

                <button type="submit">Save</button>
                <button type="button" onclick="window.history.back();">Back</button>
            </form>
        </div>
    </body>
    </html>
    <?php
} catch (PDOException $e) {
    echoGenericErrorMessage(); 
    logError(message: $e->getMessage());
    $conn = null;
    die();
}
?>
