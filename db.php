<?php
$servername = "localhost";
$username = "root";
$dbPassword = "";
$dbname = "cbe_db";

function getConnection() {
    global $servername, $username, $dbPassword, $dbname;
    $conn = new mysqli($servername, $username, $dbPassword, $dbname);
    if ($conn->connect_error) {
        die("Connection Failed: " . $conn->connect_error);
    }
    return $conn;
}

function ensureAppTables($conn) {
    $conn->query(
        "CREATE TABLE IF NOT EXISTS vehicles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            capacity VARCHAR(50) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'available',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB"
    );

    $conn->query(
        "CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            farmer_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            image_path VARCHAR(255),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB"
    );

    $conn->query(
        "CREATE TABLE IF NOT EXISTS transport_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            requester_id INT NOT NULL,
            requester_name VARCHAR(255) NOT NULL,
            requester_role VARCHAR(50) NOT NULL,
            product_id INT NULL,
            vehicle_id INT NULL,
            driver_id INT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            details TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB"
    );

    $result = $conn->query("SELECT COUNT(*) AS count FROM vehicles");
    if ($result) {
        $row = $result->fetch_assoc();
        if ((int)$row['count'] === 0) {
            $conn->query("INSERT INTO vehicles (name, capacity, status) VALUES
                ('Small Truck', '2 tons', 'available'),
                ('Medium Truck', '4 tons', 'available'),
                ('Large Truck', '8 tons', 'available')");
        }
    }
}

function ensureUploads() {
    $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'products';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    return $uploadDir;
}
