<?php
include 'user_navbar.php';
require_once "utils.php";
require_once "logger.php";

try {
    $conn = get_db_connection();

    $sql = "SELECT * FROM Physicians";
    $stmt = $conn->query($sql);

    $userid = $_SESSION['user_id']; // Get user ID from the session
    db_log($userid, Action::Viewed, "All Physicians", "Physicians", "physicians.php", $sql);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="style.css">
        <script src="script.js"></script>
        <title>Physicians List</title>
    </head>
    <body>
        <main class="main">
            <div class="content2"><h1>Physicians List</h1></div>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>CF</th>
                    <th>Phone</th>
                    <th>Cell</th>
                    <th>Email</th>
                    <th>Specialization</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
                <tr>
                    <td><?= safe_input($row['ID_phy']) ?></td>
                    <td><?= safe_input($row['lastname']) ?></td>
                    <td><?= safe_input($row['firstname']) ?></td>
                    <td><?= safe_input($row['CF']) ?></td>
                    <td><?= safe_input($row['phone']) ?></td>
                    <td><?= safe_input($row['cell']) ?></td>
                    <td><?= safe_input($row['email']) ?></td>
                    <td><?= safe_input($row['specialization']) ?></td>
                    <td>
                        <div class="dropdown">
                            <button class="dropbtn" onclick="toggleDropdown(<?= $row['ID_phy'] ?>)">Notes</button>
                            <div class="dropdown-content" id="dropdownContent_<?= $row['ID_phy'] ?>">
                                <?= safe_input($row['notes']) ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <form method="POST" action="edit_physician.php" class="max-width">
                            <input type="hidden" name="id" value="<?= safe_input($row['ID_phy']) ?>">
                            <button type="submit">Edit</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
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
