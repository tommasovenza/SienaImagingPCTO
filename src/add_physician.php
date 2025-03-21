<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Physician</title>
    <link rel="stylesheet" href="style.css">

</head>
<body>
    <header>
        <h1>Add Physician</h1>
    </header>
    <?php
        require_once "utils.php";
        require_once "logger.php";

        function echoSuccessMessage() {
            echo "<p style='color: green;'>Physician added successfully!</p>";
        }

        try {
            session_start();
            
            require_once "utils.php";
            require_once "logger.php";
                
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
                echo "<p style='color: red;'>You do not have permission to add physicians.</p>";
                die();
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $conn = get_db_connection();

                $lastname = safe_get_or_default_ass_arr($_POST, 'lastname');
                $firstname = safe_get_or_default_ass_arr($_POST, 'firstname');
                $CF = safe_get_or_default_ass_arr($_POST, 'CF');
                $phone = safe_get_or_default_ass_arr($_POST, 'phone');
                $cell = safe_get_or_default_ass_arr($_POST, 'cell');
                $email = safe_get_or_default_ass_arr($_POST, 'email');
                $specialization = safe_get_or_default_ass_arr($_POST, 'specialization');
                $notes = safe_get_or_default_ass_arr($_POST, 'notes');

                if ($lastname === null || $firstname === null) {
                    echoGenericErrorMessage();
                    $conn = null;
                    die();
                }

                $params = array(
                    ":lastname" => $lastname,
                    ":firstname" => $firstname,
                    ":CF" => $CF,
                    ":phone" => $phone,
                    ":cell" => $cell,
                    ":email" => $email,
                    ":specialization" => $specialization,
                    ":notes" => $notes
                );

                $param_sql = "
                    INSERT INTO Physicians (lastname, firstname, CF, phone, cell,
                    email, specialization, notes)
                    VALUES (:lastname, :firstname, :CF, :phone, :cell, 
                    :email, :specialization, :notes)
                    RETURNING ID_phy;
                ";

                $stmt = $conn->prepare($param_sql);
                if ($stmt->execute($params)) {
                    echoSuccessMessage();
                } else {
                    echoGenericErrorMessage();
                }

                $id_phy = $stmt->fetch()['ID_phy'];

                $userid = $_SESSION['user_id']; // Use the actual user ID from the session
                $log_str = "a new physician id=:id";
                $log_str = str_replace(":id", $id_phy, $log_str);

                // Log the action using the log_action function
                log_action($userid, 'added', 'Physicians', 'add_physician.php', $log_str, unsafe_raw_sql_ass_arr($param_sql, $params));

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
            <form method="POST" action="add_physician">
                <label for="lastname">Last Name:</label>
                <input type="text" id="lastname" name="lastname" required>
                <label for="firstname">First Name:</label>
                <input type="text" id="firstname" name="firstname" required>
                <label for="CF">Codice Fiscale:</label>
                <input type="text" id="CF" name="CF">
                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone">
                <label for="cell">Cell:</label>
                <input type="text" id="cell" name="cell">
                <label for="email">Email:</label>
                <input type="text" id="email" name="email">
                <label for="specialization">Specialization:</label>
                <input type="text" id="specialization" name="specialization">
                <label for="notes">Notes:</label>
                <textarea id="notes" name="notes" rows="4"></textarea>
                <div class="button">
                    <button type="submit">Save</button>
                    <button type="button" onclick="window.history.back();">Back</button>
                    <button type="button" onclick="window.location.href='physicians.php';">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
