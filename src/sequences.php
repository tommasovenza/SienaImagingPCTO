<?php
include 'user_navbar.php';
require_once "utils.php";
require_once "logger.php";

try {
    $conn = get_db_connection();

    $sql = "SELECT * FROM Sequences";
    $stmt = $conn->query($sql);

    $userid = $_SESSION['user_id']; // Get user ID from the session
    db_log($userid, Action::Viewed, "All Sequences", "Sequences", "sequences.php", $sql);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="style.css">
        <title>Sequences</title>

    </head>
    <body>
        <main class="main">
        <div class="content2"><h1>Sequences</h1></div>
        <table>
            <tr>
                <th>ID</th>
                <th>Group</th>
                <th>Name</th>
                <th>Deprecated</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
                <tr>
                    <td><?= safe_input($row['ID_seq']) ?></td>
                    <td><?= safe_input($row['seq_group']) ?></td>
                    <td><?= safe_input($row['seq_name']) ?></td>
                    <td><?= safe_input($row['deprecated']) ?></td>
                    <td>
                    <form method="POST" action="edit_sequence.php" class="max-width">
                            <input type="hidden" name="id" value="<?= safe_input($row['ID_seq']) ?>">
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
