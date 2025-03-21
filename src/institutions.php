<?php
include 'user_navbar.php';
require_once "utils.php";
require_once "logger.php";

try {
    $conn = get_db_connection();

    $sql = "SELECT * FROM Institutions";
    $stmt = $conn->query($sql);

    $userid = $_SESSION['user_id']; // Get user ID from the session
    db_log($userid, Action::Viewed, "All Institutions", "Institutions", "institutions.php", $sql);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="style.css">
        <title>Institutions List</title>

    </head>
    <body>
        <main class="main">
            <div class="content2"><h1>Institutions List</h1></div>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>City</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
                    <tr>
                        <td><?= safe_input($row['ID_inst']) ?></td>
                        <td><?= safe_input($row['inst_name']) ?></td>
                        <td><?= safe_input($row['inst_city']) ?></td>
                        <td>
                            <form method="POST" action="edit_institution.php" class="max-width">
                                <input type="hidden" name="id" value="<?= safe_input($row['ID_inst']) ?>">
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
