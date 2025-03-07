<?php include 'user_navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Registries</title>
</head>
<body>
    <main class="main">
        <div class="content">
            <h1>Registries</h1>
            <p>Select a registry to manage:</p>
            <div class="form-group">
                <button type="button" onclick="window.location.href='physicians.php';">Physicians</button>
                <button type="button" onclick="window.location.href='institutions.php';">Institutions</button>
                <button type="button" onclick="window.location.href='sequences.php';">Sequences</button>
                <button type="button" onclick="window.location.href='pathologies.php';">Pathologies</button>
            </div>
        </div>
    </main>
</body>
</html>
