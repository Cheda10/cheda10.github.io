<?php
session_start();
$servername = "localhost";
$username = "root";
$dbPassword = "";
$dbname = "cbe_db";

if (empty($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo 'Bad request';
    exit();
}

$id = (int)$_GET['id'];

$conn = new mysqli($servername, $username, $dbPassword, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    exit();
}

$stmt = $conn->prepare("SELECT image_path, farmer_id FROM products WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    http_response_code(404);
    exit();
}
$row = $result->fetch_assoc();
$imagePath = $row['image_path'];
$ownerId = $row['farmer_id'];
$stmt->close();
$conn->close();

if (empty($imagePath)) {
    http_response_code(404);
    exit();
}

// Access control: buyers can view any image; farmers can view their own images; drivers cannot view images
$role = $_SESSION['user_role'] ?? '';
$userId = $_SESSION['user_id'] ?? 0;

$allowed = false;
if ($role === 'buyer') {
    $allowed = true;
} elseif ($role === 'farmer' && $userId && $userId == $ownerId) {
    $allowed = true;
}

if (!$allowed) {
    http_response_code(403);
    echo 'Forbidden';
    exit();
}

$fullPath = __DIR__ . '/' . $imagePath;
if (!file_exists($fullPath)) {
    http_response_code(404);
    exit();
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$type = finfo_file($finfo, $fullPath);
finfo_close($finfo);

header('Content-Type: ' . $type);
header('Content-Length: ' . filesize($fullPath));
readfile($fullPath);
exit();
