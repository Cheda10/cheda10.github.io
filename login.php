<?php
session_start();
$servername = "localhost";
$username = "root";
$dbPassword = "";
$dbname = "cbe_db";

$message = "";
$messageClass = "";

// Show message when redirected from registration
if (isset($_GET['registered']) && $_GET['registered'] == '1') {
    $message = 'Usajili umefanikiwa. Tafadhali ingia sasa.';
    $messageClass = 'success';
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $emailOrPhone = trim($_POST['email'] ?? '');
    $raw_password = $_POST['password'] ?? '';

    if (empty($emailOrPhone) || empty($raw_password)) {
        $message = 'Tafadhali jaza barua pepe na neno la siri!';
        $messageClass = 'error';
    } else {
        $conn = new mysqli($servername, $username, $dbPassword, $dbname);

        if ($conn->connect_error) {
            die("Connection Failed: " . $conn->connect_error);
        }

        $stmt = $conn->prepare("SELECT id, first_name, middle_name, last_name, password, role FROM passengers WHERE email = ? OR phone = ? LIMIT 1");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ss", $emailOrPhone, $emailOrPhone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ($raw_password === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['fullname'] = trim($user['first_name'] . ' ' . $user['middle_name'] . ' ' . $user['last_name']);
                $_SESSION['user_role'] = $user['role'];

                $stmt->close();
                $conn->close();

                switch ($user['role']) {
                    case 'farmer':
                        header("Location: farmer.php");
                        break;
                    case 'buyer':
                        header("Location: buyer.php");
                        break;
                    case 'driver':
                        header("Location: driver.php");
                        break;
                    default:
                        header("Location: welcome.php");
                        break;
                }
                exit();
            }

            $message = 'Hitilafu: Neno la siri si sahihi!';
            $messageClass = 'error';
        } else {
            $message = 'Hitilafu: Akaunti yenye barua pepe hii haipo!';
            $messageClass = 'error';
        }

        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(rgba(12, 15, 28, 0.35), rgba(12, 15, 28, 0.35)), url('wap1.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 10px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.16);
            width: 400px;
            margin: 20px auto;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            color: #f7f9fb;
        }

        h2 {
            text-align: center;
            color: #211bd9;
            margin-top: 0;
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 14px;
        }

        input:focus,
        select:focus {
            border-color: #3498db;
            outline: none;
        }

        input[type="submit"] {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #2980b9;
        }

        .message {
            padding: 12px;
            margin-bottom: 16px;
            border-radius: 5px;
            font-weight: bold;
        }

        .message.error {
            background-color: #fdecea;
            color: #b71c1c;
        }
        .message.success {
            background-color: #e6ffed;
            color: #1e7b22;
        }

        .login-link {
            margin-top: 10px;
            text-align: center;
        }

        .login-link a {
            color: #3498db;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="form-container">
    <h2>Login</h2>

    <?php if ($message): ?>
        <div class="message <?php echo $messageClass; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label for="email">Email or Phone</label>
            <input type="text" id="email" name="email" placeholder="Enter Email Address or Phone" required value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter Password" required>

        <input type="submit" value="Login">
    </form>

    <div class="login-link">
        Dont have an account? <a href="index.php">Register here</a>
    </div>
</div>
</body>
</html>
