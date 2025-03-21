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
        echo "<p style='color: red;'>You do not have permission to edit physicians.</p>";
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
    
        $param_sql = "SELECT * FROM Physicians WHERE ID_phy = :id";
        $stmt = $conn->prepare($param_sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $physician = $stmt->fetch();

        // Log the action using the log_action function
        $userid = $_SESSION['user_id']; // Get user ID from the session
        $log_str = "Accessed edit page for physician with ID_phy = :id";
        $log_params = [":id" => $id];
        log_action($userid, 'viewed', 'Physicians', 'edit_physician.php', $log_str, unsafe_raw_sql_ass_arr($param_sql, $log_params));
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="style.css">
        <title>Edit Physician</title>

    </head>
    <body>
        <header>
            <h1>Edit Physician</h1>
        </header>
        <div class="alline">
            <div class="form-container">
                <form method="POST" action="save_physician.php">
                    <input type="hidden" name="id" value="<?= safe_input($physician['ID_phy']) ?>">
                    <div class="form-group">
                        <label for="lastname">Last Name:</label>
                        <input type="text" id="lastname" name="lastname" value="<?= safe_input($physician['lastname']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="firstname">First Name:</label>
                        <input type="text" id="firstname" name="firstname" value="<?= safe_input($physician['firstname']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="CF">Codice Fiscale:</label>
                        <input type="text" id="CF" name="CF" value="<?= safe_input($physician['CF']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone:</label>
                        <input type="text" id="phone" name="phone" value="<?= safe_input($physician['phone']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="cell">Cell:</label>
                        <input type="text" id="cell" name="cell" value="<?= safe_input($physician['cell']) ?>">
                    </div>    
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?= safe_input($physician['email']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="specialization">Specialization:</label>
                        <input type="text" id="specialization" name="specialization" value="<?= safe_input($physician['specialization']) ?>">
                    </div>    
                    <div class="form-group">
                        <label for="notes">Notes:</label>
                        <textarea id="notes" name="notes"><?= safe_input($physician['notes']) ?></textarea>
                    </div>    
                    <div class="form-group">
                        <button type="submit">Save</button>
                        <button type="button" onclick="window.location.href='physicians.php';">Back</button>
                        <button type="button" onclick="window.history.back();">Cancel</button>
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
