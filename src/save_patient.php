<?php
require_once "utils.php";
require_once "logger.php";

function echoSuccessMessage() {
    echo "<p style='color: green;'>Patient updated successfully!</p>";
}

try {

    session_start();

    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['privilege_level'])) {
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

        // Fetch POST parameters
        $id = safe_get_or_default_ass_arr($_POST, 'id');
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

        // Retrieve the current patient record
        $stmt = $conn->prepare("SELECT * FROM Patients WHERE ID_anag = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $old_patient = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$old_patient) {
            echoGenericErrorMessage();
            $conn = null;
            die();
        }

        // Update the patient record
        $params = [
            ":id" => $id,
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

        $param_sql = "UPDATE Patients
                      SET lastname = :lastname, firstname = :firstname, birth = :birth, birth_city = :birth_city, 
                          birth_province = :birth_province, birth_state = :birth_state, sex = :sex, address = :address, 
                          CAP = :CAP, city = :city, province = :province, state = :state, CF = :CF, email = :email, 
                          phone = :phone, notes = :notes
                      WHERE ID_anag = :id;";

        $stmt = $conn->prepare($param_sql);
        if ($stmt->execute($params)) {
            header("Location: patients.php");
        } else {
            echoGenericErrorMessage();
        }

        // Log the action
        $userid = $_SESSION['user_id']; // Get user ID from the session
        $log_str = "Updated patient ID=:id from (lastname=:old_lastname, firstname=:old_firstname, birth=:old_birth, birth_city=:old_birth_city, 
                     birth_province=:old_birth_province, birth_state=:old_birth_state, sex=:old_sex, address=:old_address, CAP=:old_CAP, 
                     city=:old_city, province=:old_province, state=:old_state, CF=:old_CF, email=:old_email, phone=:old_phone, notes=:old_notes) 
                     to (lastname=:lastname, firstname=:firstname, birth=:birth, birth_city=:birth_city, birth_province=:birth_province, 
                     birth_state=:birth_state, sex=:sex, address=:address, CAP=:CAP, city=:city, province=:province, state=:state, 
                     CF=:CF, email=:email, phone=:phone, notes=:notes)";

        $log_params = array_merge(
            [
                ":id" => $id,
                ":old_lastname" => $old_patient['lastname'],
                ":old_firstname" => $old_patient['firstname'],
                ":old_birth" => $old_patient['birth'],
                ":old_birth_city" => $old_patient['birth_city'],
                ":old_birth_province" => $old_patient['birth_province'],
                ":old_birth_state" => $old_patient['birth_state'],
                ":old_sex" => $old_patient['sex'],
                ":old_address" => $old_patient['address'],
                ":old_CAP" => $old_patient['CAP'],
                ":old_city" => $old_patient['city'],
                ":old_province" => $old_patient['province'],
                ":old_state" => $old_patient['state'],
                ":old_CF" => $old_patient['CF'],
                ":old_email" => $old_patient['email'],
                ":old_phone" => $old_patient['phone'],
                ":old_notes" => $old_patient['notes']
            ],
            $params
        );

        log_action($userid, 'modified', 'Patients', 'save_patient.php', unsafe_raw_sql_ass_arr($log_str, $log_params), unsafe_raw_sql_ass_arr($param_sql, $params));

        $conn = null;
    }
} catch (PDOException $e) {
    echoGenericErrorMessage();
    error_log("Error: " . $e->getMessage());
    $conn = null;
    die();
}
?>
