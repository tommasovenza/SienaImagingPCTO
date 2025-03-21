<?php include 'user_dashboard.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registries</title>
    <style>
        .content {
            padding: 20px;
            text-align: center;
        }
        .registry-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }
        .registry-buttons a {
            background-color: #333;
            color: white;
            text-decoration: none;
            padding: 14px 20px;
            font-size: 16px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .registry-buttons a:hover {
            background-color: #575757;
        }
    </style>
</head>
<body>
    <div class="content">
        <h1>Registries</h1>
        <p>Select a registry to manage:</p>
        <div class="registry-buttons">
            <a href="physicians.php">Physicians</a>
            <a href="institutions.php">Institutions</a>
            <a href="sequences.php">Sequences</a>
            <a href="pathologies.php">Pathologies</a>
        </div>
    </div>
</body>
</html>
