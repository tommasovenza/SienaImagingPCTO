<?php
require_once "user_dashboard.php";
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
        <title>Physicians List</title>
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
            <h1>Physicians List</h1>
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
                        <td><?= safe_input($row['notes']) ?></td>
                      	<td>
                            <form method="POST" action="edit_physician.php">
                                <input type="hidden" name="id" value="<?= safe_input($row['ID_phy']) ?>">
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
