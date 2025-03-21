<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Patient</title>
</head>
<body>
    <div class="content">
        <h1>Add Patient</h1>

        <?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "utils.php";
require_once "logger.php";

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['privilege_level'])) {
    echoUserNotFoundMessage();
    die();
}

// Ensure the user has privilege level 2 or 3
if ($_SESSION['privilege_level'] != 2 && $_SESSION['privilege_level'] != 3) {
    echo "<p style='color: red;'>You do not have permission to add patients.</p>";
    die();
}

function echoSuccessMessage() {
    echo "<p style='color: green;'>Patient added successfully!</p>";
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $conn = get_db_connection();

        $lastname = safe_get_or_default_ass_arr($_POST, 'lastname');
        $firstname = safe_get_or_default_ass_arr($_POST, 'firstname');
        $birth = safe_get_or_default_ass_arr($_POST, 'birth');
        $birth_city = safe_get_or_default_ass_arr($_POST, 'birth_city');
        $birth_province = safe_get_or_default_ass_arr($_POST, 'birth_province');
        $birth_state = safe_get_or_default_ass_arr($_POST, 'birth_state');
        $sex = safe_get_or_default_ass_arr($_POST, 'sex');
        $address = safe_get_or_default_ass_arr($_POST, 'address');
        $CAP = safe_get_or_default_ass_arr($_POST, 'CAP');
        $city = safe_get_or_default_ass_arr($_POST, 'city');
        $province = safe_get_or_default_ass_arr($_POST, 'province');
        $state = safe_get_or_default_ass_arr($_POST, 'state');
        $CF = safe_get_or_default_ass_arr($_POST, 'CF');
        $email = safe_get_or_default_ass_arr($_POST, 'email');
        $phone = safe_get_or_default_ass_arr($_POST, 'phone');
        $notes = safe_get_or_default_ass_arr($_POST, 'notes');

        if ($lastname === null || $firstname === null || $birth_state === null || $state === null) {
            echo "<p style='color: red;'>Please fill out all required fields.</p>";
            $conn = null;
            die();
        }

        $params = [
            ":lastname" => $lastname,
            ":firstname" => $firstname,
            ":birth" => $birth,
            ":birth_city" => $birth_city,
            ":birth_province" => $birth_province,
            ":birth_state" => $birth_state,
            ":sex" => $sex,
            ":address" => $address,
            ":CAP" => $CAP,
            ":city" => $city,
            ":province" => $province,
            ":state" => $state,
            ":CF" => $CF,
            ":email" => $email,
            ":phone" => $phone,
            ":notes" => $notes
        ];

        $param_sql = "
            INSERT INTO Patients (lastname, firstname, birth, birth_city, birth_province,
            birth_state, sex, address, CAP, city, province,
            state, CF, email, phone, notes)
            VALUES (:lastname, :firstname, :birth, :birth_city, :birth_province, 
            :birth_state, :sex, :address, :CAP, :city, :province,
            :state, :CF, :email, :phone, :notes)
            RETURNING ID_anag;
        ";

        $stmt = $conn->prepare($param_sql);
        if ($stmt->execute($params)) {
            $id_anag = $stmt->fetch()['ID_anag'];
            echoSuccessMessage();

            // Log the action
            $userid = $_SESSION['user_id'];
            $log_str = "Added a new patient with ID: $id_anag";
            log_action($userid, 'added', 'Patients', 'add_patient.php', $log_str, unsafe_raw_sql_ass_arr($param_sql, $params));
        } else {
            echo "<p style='color: red;'>Failed to add patient. Please try again later.</p>";
        }

        $conn = null;
    }
} catch (PDOException $e) {
    echoGenericErrorMessage();
    logError("Database error: " . $e->getMessage());
    die();
}
?>


        <form method="POST">
            <label for="lastname">Last Name:</label>
            <input type="text" id="lastname" name="lastname" required>

            <label for="firstname">First Name:</label>
            <input type="text" id="firstname" name="firstname" required>

            <label for="birth">Birth Date:</label>
            <input type="date" id="birth" name="birth">

            <label for="birth_city">Birth City:</label>
            <input type="text" id="birth_city" name="birth_city">

            <label for="birth_province">Birth Province:</label>
            <input type="text" id="birth_province" name="birth_province">

            <label for="birth_state">Birth State:</label>
            <input type="text" id="birth_state" name="birth_state">

            <label for="sex">Gender:</label>
            <input type="radio" id="male" name="sex" value="M">
            <label for="male">Male</label>
            <input type="radio" id="female" name="sex" value="F">
            <label for="female">Female</label>

            <label for="address">Address:</label>
            <input type="text" id="address" name="address">

            <label for="CAP">CAP:</label>
            <input type="text" id="CAP" name="CAP">

            <label for="city">City:</label>
            <input type="text" id="city" name="city">

            <label for="province">Province:</label>
            <input type="text" id="province" name="province">

            <label for="state">State:</label>
            <input type="text" id="state" name="state">

            <label for="CF">CF:</label>
            <input type="text" id="CF" name="CF">

            <label for="email">Email:</label>
            <input type="text" id="email" name="email">

            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone">

            <label for="notes">Notes:</label>
            <textarea id="notes" name="notes" rows="4"></textarea>

            <div class="buttons">
                <button type="submit">Save</button>
                <button type="button" onclick="window.history.back();">Back</button>
                <button type="button" onclick="window.location.href='patients.php';">Cancel</button>
            </div>
        </form>
    </div>
</body>
</html>
