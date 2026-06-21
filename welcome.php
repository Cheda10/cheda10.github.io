<?php
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$fullname = htmlspecialchars($_SESSION['fullname'] ?? 'Mwenyeji');
$role = $_SESSION['user_role'] ?? 'Unknown';

switch ($role) {
    case 'farmer':
        header('Location: farmer.php');
        exit();
    case 'buyer':
        header('Location: buyer.php');
        exit();
    case 'driver':
        header('Location: driver.php');
        exit();
}

$role = htmlspecialchars($role);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eae9e8;
            margin: 0;
            padding: 10px;
            display: inline;
            justify-content: center;
            height: 100vh;
        }

        .page-container {
            background-color: #e9edf0;
            width: 400px;
            margin: 20px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        h2 {
            color: #211bd9;
            margin-top: 0;
        }

        p {
            font-size: 16px;
            margin: 10px 0;
        }

        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
<div class="page-container">
    <h2>Karibu, <?php echo $fullname; ?>!</h2>
    <p>Umeingia kama <strong><?php echo $role; ?></strong>.</p>
    <p>Karibu kwenye mfumo wa usajili wa abiria.</p>
    <a class="btn" href="logout.php">Toka</a>
</div>
</body>
</html>
