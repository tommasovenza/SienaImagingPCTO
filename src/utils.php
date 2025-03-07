<?php

/* Prints a generic success */
function echoGenericSuccessMessage() {
    echo "<p style='color: green;'>Success!</p>";
}

/* Prints a generic error */
function echoGenericErrorMessage() {
    echo "<p style='color: red;'>An error has occurred. Please try again later.</p>";
}

function echoUserNotFoundMessage(): void {
    echo "<p style='color: red;'>You must be logged in to access this page.</p>";
}

/* ALWAYS USE THIS
 * The main purpose of this function is to sanitize the input from html elements.
 * Use this on all inputs even those not coming directly from the user for example a database query
 */
function safe_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/* Simple logic to get data either from $_POST or $_GET */
function request_params($METHOD) {
    if ($METHOD === "POST") {
        return $_POST;
    }
    if ($METHOD === "GET") {
    	return $_GET;
    }
    return null;
}

/* Checks if an association in an associative array is valid. 
 * If it, it returns the associated value otherwise it returns $default which is by default null.
 * Does not sanitize the input.      
 * For that it is always recommend to use safe_get_or_default_ass_arr.
 */
function unsafe_get_or_default_ass_arr($arr, $what, $default=null) {
    if (isset($arr[$what])) {
        return $arr[$what];
    }

    return $default;
}

/* Checks if an association in an associative array is valid. 
 * If it, it returns the associated value otherwise it returns $default which is by default null.
 * DOES sanitize the input. 
 * For that it is always recommend this instead of unsafe_get_or_default_ass_arr except when the input has already been sanitized.
 */
function safe_get_or_default_ass_arr($arr, $what, $default=null) {
    return safe_input(unsafe_get_or_default_ass_arr($arr, $what, $default));
}

/* This function has a really specific purpose.
 * It is used during the logging stage and should not be used anywhere else.
 * If you DO NOT UNDERSTAND what this is doing or where it should be used DO NOT USE IT.
 */
function unsafe_raw_sql_ass_arr($prepared_statement, $replacement) {
    foreach ($replacement as $parameter => $value) {
        if ($value === null) {
            $value = "NULL";
        }
        $prepared_statement = str_replace($parameter, $value, $prepared_statement);
    }
    return $prepared_statement;
}

/* ALWAYS use this to get a connection to the database */
function get_db_connection() {
    $host = "localhost";
    $dbname = "QNL";
    $username = "balboa";
    $password = "balboa";
    
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $conn;
}

/* Logs user actions to the Log table */
function log_action($user_id, $action_type, $table_name, $page, $basic_log, $query_log) {
    try {
        $conn = get_db_connection();

        //Prepare the SQL query to insert the log into the Log table
        $sql = "
            INSERT INTO Log (basic_log, query_log, action_type, table_name, page, user_id)
            VALUES (:basic_log, :query_log, :action_type, :table_name, :page, :user_id);
        ";

        //Prepare the parameters for the query
        $params = [
            ":basic_log" => $basic_log,
            ":query_log" => $query_log,
            ":action_type" => $action_type,
            ":table_name" => $table_name,
            ":page" => $page,
            ":user_id" => $user_id
        ];

        //Execute the query
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        $conn = null;
    } catch (PDOException $e) {
        //Handle the error if the log insertion fails
        error_log("Log insertion failed: " . $e->getMessage());
    }
}
?>