<?php
require_once "user_dashboard.php";
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
        <title>Patient Sessions</title>
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
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
            th {
                background-color: #f4f4f4;
            }
            .back-button {
                margin-top: 20px;
            }
            .add-session-button {
                padding: 10px 20px;
                font-size: 16px;
                background-color: #4CAF50;
                color: white;
                border: none;
                cursor: pointer;
                border-radius: 5px;
            }
            .add-session-button:hover {
                background-color: #45a049;
            }
        </style>
    </head>
    <body>
        <div class="content">
            <h1>Sessions for Patient ID: <?= safe_input($patient_id) ?></h1>
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

            <div class="back-button">
                <a href="patients.php">Back to Patients List</a>
            </div>

            <div class="button-container">
                <a href="add_session.php?patient_id=<?= safe_input($patient_id) ?>">
                    <button class="add-session-button">Add Session</button>
                </a>
            </div>
        </div>
    </body>
    </html>
    <?php
} catch (PDOException $e) {
    echoGenericErrorMessage();
    $conn = null;
    die();
}
?>
