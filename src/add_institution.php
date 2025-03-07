<?php
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
    echo "<p style='color: red;'>You do not have permission to add institutions.</p>";
    die();
}

function echoSuccessMessage() {
    echo "<p style='color: green;'>Institution added successfully!</p>";
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $conn = get_db_connection();

        $inst_name = safe_get_or_default_ass_arr($_POST, 'instname');
        $inst_city = safe_get_or_default_ass_arr($_POST, 'instcity');

        if (empty($inst_name) || empty($inst_city)) {
            echo "<p style='color: red;'>Please fill out all required fields.</p>";
        } else {
            $params = array(":inst_name" => $inst_name, ":inst_city" => $inst_city);

            $param_sql = "INSERT INTO Institutions (inst_name, inst_city) VALUES (:inst_name, :inst_city) RETURNING ID_inst;";
            $stmt = $conn->prepare($param_sql);
            
            if ($stmt->execute($params)) {
                $id_inst = $stmt->fetch()['ID_inst'];
                
                // Log the action
                log_action($_SESSION['user_id'], 'added', 'Institutions', 'add_institution.php', "Added institution ID: $id_inst", unsafe_raw_sql_ass_arr($param_sql, $params));
                
                echoSuccessMessage();
            } else {
                echoGenericErrorMessage();
            }
        }
        $conn = null;
    }
} catch (PDOException $e) {
    logError("Database error: " . $e->getMessage());
    echoGenericErrorMessage();
    die();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Institution</title>
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
        input, button, textarea {
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
        <h1>Add Institution</h1>
        <form method="POST">
            <label for="instname">Institution Name:</label>
            <input type="text" id="instname" name="instname" required>

            <label for="instcity">Institution City:</label>
            <input type="text" id="instcity" name="instcity" required>

            <div class="buttons">
                <button type="submit">Save</button>
                <button type="button" onclick="window.history.back();">Back</button>
                <button type="button" onclick="window.location.href='institutions.php';">Cancel</button>
            </div>
        </form>
    </div>
</body>
</html>
