<?php
require_once "user_dashboard.php";
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
        <title>Institutions List</title>
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
        </style>
    </head>
    <body>
        <div class="content">
            <h1>Institutions List</h1>
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
                            <form method="POST" action="edit_institution.php">
                                <input type="hidden" name="id" value="<?= safe_input($row['ID_inst']) ?>">
                                <button type="submit">Edit</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
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
