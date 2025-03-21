<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Pathology</title>
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
        <h1>Add Pathology</h1>

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

// Ensure the user is a super admin (privilege level 4)
if ($_SESSION['privilege_level'] != 4) {
    echo "<p style='color: red;'>You do not have permission to add pathologies.</p>";
    die();
}

function echoSuccessMessage() {
    echo "<p style='color: green;'>Pathology added successfully!</p>";
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $conn = get_db_connection();

        $patol_name = safe_get_or_default_ass_arr($_POST, 'patolname');

        if ($patol_name === null) {
            echo "<p style='color: red;'>Please fill out all required fields.</p>";
            $conn = null;
            die();
        }

        $params = array(":patol_name" => $patol_name);

        $param_sql = "INSERT INTO Pathologies (patol_name, deprecated) VALUES (:patol_name, FALSE) RETURNING ID_patol;";
        $stmt = $conn->prepare($param_sql);

        if ($stmt->execute($params)) {
            $id_patol = $stmt->fetch()['ID_patol'];
            echoSuccessMessage();

            // Log the action
            $userid = $_SESSION['user_id'];
            $log_str = "Added a new pathology with ID: $id_patol";
            log_action($userid, 'added', 'Pathologies', 'add_pathologies.php', $log_str, unsafe_raw_sql_ass_arr($param_sql, $params));
        } else {
            echo "<p style='color: red;'>An error occurred while adding the pathology.</p>";
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
            <label for="patolname">Pathology Name:</label>
            <input type="text" id="patolname" name="patolname" required>

            <div class="buttons">
                <button type="submit">Save</button>
                <button type="button" onclick="window.history.back();">Back</button>
                <button type="button" onclick="window.location.href='pathologies.php';">Cancel</button>
            </div>
        </form>
    </div>
</body>
</html>
