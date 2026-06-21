<?php

$servername = "localhost";
$username = "root";
$dbPassword = "";
$dbname = "cbe_db";

$conn = new mysqli($servername, $username, $dbPassword, $dbname);

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

$fname = $_POST['fname'];
$mname = $_POST['mname'];
$sname = $_POST['sname'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$password = $_POST['password'];
$role = $_POST['role'];

$checkSql = "SELECT id FROM passengers WHERE email = ? LIMIT 1";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param('s', $email);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    echo "User is already exist";
    $checkStmt->close();
    $conn->close();
    exit();
}

$checkStmt->close();

$sql = "INSERT INTO passengers
(first_name, middle_name, last_name, email, phone, password, role)
VALUES
('$fname','$mname','$sname','$email','$phone','$password','$role')";

if ($conn->query($sql) === TRUE) {
    // Redirect user to login page after successful registration
    header('Location: login.php?registered=1&email=' . urlencode($email));
    $conn->close();
    exit();
} else {
    echo "Error: " . $conn->error;
}

$conn->close();

?>