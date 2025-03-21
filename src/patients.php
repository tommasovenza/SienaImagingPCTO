<?php
include 'user_navbar.php';
require_once "utils.php";
require_once "logger.php";

try {
    $conn = get_db_connection();

    $sql = "SELECT * FROM Patients";
    $stmt = $conn->query($sql);

    $userid = $_SESSION['user_id']; // Get user ID from the session
    db_log($userid, Action::Viewed, "All Patients", "Patients", "patients.php", $sql);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="style.css">
        <script src="script.js"></script>
        <title>Patients List</title>
    </head>
    <body>
        <main class="main">
            <div class="content2">
                <h1>Patients List</h1>
                <table>
                    <tr>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>Birth</th>
                        <th>Birth City</th>
                        <th>Birth Province</th>
                        <th>Birth State</th>
                        <th>Sex</th>
                        <th>Address</th>
                        <th>CAP</th>
                        <th>City</th>
                        <th>Province</th>
                        <th>State</th>
                        <th>CF</th>
                        <th>Phone</th>
                        <th>Notes</th>
                        <th>Actions</th>
                        <th>Manage Sessions</th> <!-- New column header -->
                    </tr>
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
                        <tr>
                            <td><?= safe_input($row['lastname']) ?></td>
                            <td><?= safe_input($row['firstname']) ?></td>
                            <td><?= safe_input($row['birth']) ?></td>
                            <td><?= safe_input($row['birth_city']) ?></td>
                            <td><?= safe_input($row['birth_province']) ?></td>
                            <td><?= safe_input($row['birth_state']) ?></td>
                            <td><?= safe_input($row['sex']) ?></td>
                            <td><?= safe_input($row['address']) ?></td>
                            <td><?= safe_input($row['CAP']) ?></td>
                            <td><?= safe_input($row['city']) ?></td>
                            <td><?= safe_input($row['province']) ?></td>
                            <td><?= safe_input($row['state']) ?></td>
                            <td><?= safe_input($row['CF']) ?></td>
                            <td><?= safe_input($row['phone']) ?></td>
                            <td>
                                <div class="dropdown">
                                    <button class="dropbtn" onclick="toggleDropdown(<?= $row['ID_anag'] ?>)">Notes</button>
                                    <div class="dropdown-content" id="dropdownContent_<?= $row['ID_anag'] ?>">
                                        <?= safe_input($row['notes']) ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <form method="POST" action="edit_patient.php" class="max-width">
                                    <input type="hidden" name="id" value="<?= safe_input($row['ID_anag']) ?>">
                                    <button type="submit">Edit</button>
                                </form>
                            </td>
                            <td>
                                <form method="GET" action="sessions.php" class="max-width">
                                    <input type="hidden" name="patient_id" value="<?= safe_input($row['ID_anag']) ?>">
                                    <button type="submit">Manage Sessions</button>
                                </form>
                            </td> 
                        </tr>
                    <?php endwhile; ?>
                </table>
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
