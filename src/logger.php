<?php

require_once "utils.php";

abstract class Action {
   const Viewed = 0;
   const Added = 1;
   const Modified = 2;
   const Deleted = 3;
}

function action_to_string($action) {
    if ($action === Action::Viewed) { return "Viewed"; }
    if ($action === Action::Added) { return "Added"; }
    if ($action === Action::Modified) { return "Modified"; }
    if ($action === Action::Deleted) { return "Deleted"; }
    return "Unknown";
}

function db_log($id, $action, $what, $table_name, $page, $query) {
    $query = safe_input($query);
    
    try {
        $conn = get_db_connection();
        $action_name = action_to_string($action);

        $basic_log = "User " . $id . " " . $action_name . " " . $what . ", table=" . $table_name . " from page: " . $page;

        $stmt = $conn->prepare("
            INSERT INTO Log (basic_log, query_log, action_type, table_name, page, user_id) VALUES
            (:basic_log, :query_log, :action_type, :table_name, :page, :user_id);"
        );
        
        $params = array(":basic_log"=>$basic_log, ":query_log"=>$query, 
        ":action_type"=>$action_name, ":table_name"=>$table_name,
        ":page"=>$page, ":user_id"=>$id);
        
        $stmt->execute($params);
        $conn = null;
    } catch(PDOException $e) {
        echoGenericErrorMessage();
        $conn = null;
    }
}

// Function to log errors to a file
function logError($message) {
    $logfile = 'error_log.txt'; // Define the log file location
    $timestamp = date('Y-m-d H:i:s');
    $formatted_message = "$timestamp - $message" . PHP_EOL;

    // Append the error message to the log file
    file_put_contents($logfile, $formatted_message, FILE_APPEND);
}
?>
