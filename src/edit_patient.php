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
        echo "<p style='color: red;'>You do not have permission to edit patients.</p>";
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

        $param_sql = "SELECT * FROM Patients WHERE ID_anag = :id";
        $stmt = $conn->prepare($param_sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $patient = $stmt->fetch();

        // Log the action using the log_action function
        $userid = $_SESSION['user_id']; // Get user ID from the session
        $log_str = "Accessed edit page for patient with ID_anag = :id";
        $log_params = [":id" => $id];
        log_action($userid, 'viewed', 'Patients', 'edit_patient.php', $log_str, unsafe_raw_sql_ass_arr($param_sql, $log_params));
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit Patient</title>
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
            <h1>Edit Patient</h1>
            <form method="POST" action="save_patient.php">
                <input type="hidden" name="id" value="<?= safe_input($patient['ID_anag']) ?>">

                <label for="lastname">Last Name:</label>
                <input type="text" id="lastname" name="lastname" value="<?= safe_input($patient['lastname']) ?>" required>

                <label for="firstname">First Name:</label>
                <input type="text" id="firstname" name="firstname" value="<?= safe_input($patient['firstname']) ?>" required>

                <label for="birth">Birth Date:</label>
                <input type="date" id="birth" name="birth" value="<?= safe_input($patient['birth']) ?>">

                <label for="birth_city">Birth City:</label>
                <input type="text" id="birth_city" name="birth_city" value="<?= safe_input($patient['birth_city']) ?>">

                <label for="birth_province">Birth Province:</label>
                <input type="text" id="birth_province" name="birth_province" value="<?= safe_input($patient['birth_province']) ?>">

                <label for="birth_state">Birth State:</label>
                <input type="text" id="birth_state" name="birth_state" value="<?= safe_input($patient['birth_state']) ?>">

                <label for="sex">Gender:</label>
                <?php
                if (safe_input($patient['sex'] === "F")) {
                    echo '<input type="radio" id="male" name="sex" value="M">';
                    echo '<label for="male">Male</label>';
                    echo '<input type="radio" id="female" name="sex" value="F" checked="true">';
                    echo '<label for="female">Female</label>';
                } else {
                    echo '<input type="radio" id="male" name="sex" value="M" checked="true">';
                    echo '<label for="male">Male</label>';
                    echo '<input type="radio" id="female" name="sex" value="F">';
                    echo '<label for="female">Female</label>';
                }
                ?>

                <label for="address">Address:</label>
                <input type="text" id="address" name="address" value="<?= safe_input($patient['address']) ?>">

                <label for="CAP">CAP:</label>
                <input type="text" id="CAP" name="CAP" value="<?= safe_input($patient['CAP']) ?>">

                <label for="city">City:</label>
                <input type="text" id="city" name="city" value="<?= safe_input($patient['city']) ?>">

                <label for="province">Province:</label>
                <input type="text" id="province" name="province" value="<?= safe_input($patient['province']) ?>">

                <label for="state">State:</label>
                <input type="text" id="state" name="state" value="<?= safe_input($patient['state']) ?>">

                <label for="CF">CF:</label>
                <input type="text" id="CF" name="CF" value="<?= safe_input($patient['CF']) ?>">

                <label for="email">Email:</label>
                <input type="text" id="email" name="email" value="<?= safe_input($patient['email']) ?>">

                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone" value="<?= safe_input($patient['phone']) ?>">

                <label for="notes">Notes:</label>
                <textarea id="notes" name="notes" rows="4"><?= safe_input($patient['notes']) ?></textarea>

                <div class="buttons">
                    <button type="submit">Save</button>
                    <button type="button" onclick="window.history.back();">Back</button>
                </div>
            </form>
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
