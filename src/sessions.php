<?php
include 'user_navbar.php';
require_once "utils.php";
require_once "logger.php";

try {
    // Ensure the patient ID is passed in the URL
    if (!isset($_GET['patient_id']) || empty($_GET['patient_id'])) {
        echo "No patient selected.";
        exit;
    }

    $patient_id = $_GET['patient_id'];

    $conn = get_db_connection();

    // SQL to fetch all sessions for the patient, ordered by session date
    $sql = "SELECT * FROM Sessions WHERE ID_anag = :patient_id ORDER BY session_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
    $stmt->execute();

    // Log the action of viewing patient sessions without user-related data
    db_log(null, Action::Viewed, "Patient Sessions", "Sessions", "sessions.php", $sql);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="style.css">
        <title>Patient Sessions</title>
    </head>
    <body>
        <main class="main">
            <div class="content2"><div class="alline"><h1>Sessions for Patient ID: <?= safe_input($patient_id) ?></h1></div></div>
            <div class="navbar-container">            
                <table>
                    <tr>
                        <th>Session ID</th>
                        <th>Date</th>
                        <th>Physician ID</th>
                        <th>Instrument ID</th>
                        <th>Storage</th>
                        <th>Optical Disk</th>
                        <th>Study</th>
                        <th>Lesion Volume</th>
                        <th>Lesion Count</th>
                        <th>Session Diagnosis</th>
                        <th>Actions</th>
                    </tr>
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
                        <tr>
                            <td><?= safe_input($row['ID_sed']) ?></td>
                            <td><?= safe_input($row['session_date']) ?></td>
                            <td><?= safe_input($row['ID_phy']) ?></td>
                            <td><?= safe_input($row['ID_strumento']) ?></td>
                            <td><?= safe_input($row['storage']) ?></td>
                            <td><?= safe_input($row['optical_disk']) ?></td>
                            <td><?= safe_input($row['study']) ?></td>
                            <td><?= safe_input($row['les_volume']) ?></td>
                            <td><?= safe_input($row['les_count']) ?></td>
                            <td><?= safe_input($row['session_diagnosis']) ?></td>
                            <td>
                                <form method="POST" action="edit_session.php">
                                    <input type="hidden" name="session_id" value="<?= safe_input($row['ID_sed']) ?>">
                                    <button type="submit">Edit</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        <div class="form-group">
            <button type="button" onclick="window.history.back();">Back</button>
            <a href="add_session.php?patient_id=<?= safe_input($patient_id) ?>">
                <button class="add-session-button">Add Session</button>
            </a>
        </div>
    </main>
</body>
</html>
    <?php
} catch (PDOException $e) {
    echoGenericErrorMessage();
    $conn = null;
    die();
}
?>
