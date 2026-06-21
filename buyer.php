<?php
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'buyer') {
    header('Location: login.php');
    exit();
}
include 'db.php';
$conn = getConnection();
ensureAppTables($conn);
$userId = $_SESSION['user_id'];
$fullname = htmlspecialchars($_SESSION['fullname']);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['request_vehicle'])) {
        $productId = (int)$_POST['product_id'];
        $vehicleId = (int)$_POST['vehicle_id'];
        $driverId = !empty($_POST['driver_id']) ? (int)$_POST['driver_id'] : null;
        $details = trim($_POST['details'] ?? '');
        $requesterName = $_SESSION['fullname'];
        if ($driverId) {
            $stmt = $conn->prepare("INSERT INTO transport_requests (requester_id, requester_name, requester_role, product_id, vehicle_id, driver_id, details) VALUES (?, ?, 'buyer', ?, ?, ?, ?)");
            $stmt->bind_param('isisis', $userId, $requesterName, $productId, $vehicleId, $driverId, $details);
        } else {
            $stmt = $conn->prepare("INSERT INTO transport_requests (requester_id, requester_name, requester_role, product_id, vehicle_id, driver_id, details) VALUES (?, ?, 'buyer', ?, ?, NULL, ?)");
            $stmt->bind_param('isiss', $userId, $requesterName, $productId, $vehicleId, $details);
        }
        if ($stmt->execute()) {
            $message = 'Vehicle request created successfully.';
        } else {
            $message = 'Could not create vehicle request.';
        }
        $stmt->close();
    }

    if (isset($_POST['cancel_request'])) {
        $requestId = (int)$_POST['request_id'];
        $stmt = $conn->prepare("UPDATE transport_requests SET status = 'cancelled' WHERE id = ? AND requester_id = ?");
        $stmt->bind_param('ii', $requestId, $userId);
        if ($stmt->execute()) {
            $message = 'Request cancelled.';
        } else {
            $message = 'Could not cancel request.';
        }
        $stmt->close();
    }
}

$products = $conn->query("SELECT p.id, p.title, p.description, p.image_path, pa.first_name, pa.middle_name, pa.last_name
    FROM products p
    LEFT JOIN passengers pa ON p.farmer_id = pa.id
    ORDER BY p.created_at DESC");
$availableVehicles = $conn->query("SELECT * FROM vehicles WHERE status = 'available' ORDER BY created_at DESC");
$availableDrivers = $conn->query("SELECT id, first_name, middle_name, last_name FROM passengers WHERE role = 'driver' ORDER BY first_name ASC");
$requests = $conn->query("SELECT tr.id, tr.status, tr.created_at, v.name AS vehicle_name, p.title AS product_title, d.first_name AS driver_first, d.middle_name AS driver_middle, d.last_name AS driver_last
    FROM transport_requests tr
    LEFT JOIN vehicles v ON tr.vehicle_id = v.id
    LEFT JOIN products p ON tr.product_id = p.id
    LEFT JOIN passengers d ON tr.driver_id = d.id
    WHERE tr.requester_id = $userId AND tr.requester_role = 'buyer'
    ORDER BY tr.created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Buyer Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); margin: 0; padding: 20px; min-height: 100vh; }
        .wrapper { max-width: 1200px; margin: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 8px 25px rgba(102, 126, 234, 0.25); }
        .header div:first-child h2 { font-size: 28px; margin-bottom: 5px; font-weight: 600; }
        .header p { font-size: 14px; opacity: 0.9; }
        .card { background: #fff; border-radius: 12px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 25px; border: 1px solid rgba(0,0,0,0.05); }
        .card h3 { margin: 0 0 20px 0; font-size: 18px; color: #333; font-weight: 600; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; color: #444; font-size: 14px; }
        input, textarea, select { width: 100%; padding: 12px 14px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; transition: all 0.3s; }
        input:focus, textarea:focus, select:focus { border-color: #667eea; outline: none; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        input[type="submit"], button { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; border: none; padding: 12px 20px; border-radius: 6px; cursor: pointer; font-weight: 500; transition: all 0.3s; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3); }
        input[type="submit"]:hover, button:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4); }
        .product-grid { display: grid; gap: 20px; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }
        .product-card { border: 1px solid #eee; border-radius: 12px; overflow: hidden; background: #fff; transition: all 0.3s; box-shadow: 0 2px 10px rgba(0,0,0,0.06); }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.12); }
        .product-card img { width: 100%; height: 200px; object-fit: cover; }
        .product-card .content { padding: 15px; }
        .product-card h4 { font-size: 15px; font-weight: 600; margin-bottom: 8px; color: #333; }
        .product-card p { font-size: 13px; color: #666; margin-bottom: 10px; line-height: 1.4; }
        .message { padding: 15px 20px; background: #d4edda; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #28a745; color: #155724; font-weight: 500; }
        .message.error { background: #f8d7da; border-left-color: #dc3545; color: #721c24; }
        .no-items { color: #999; font-style: italic; padding: 20px; text-align: center; }
        .top-bar { display: flex; align-items: center; }
        .top-bar a { color: #fff; text-decoration: none; font-weight: 500; transition: opacity 0.3s; padding: 8px 12px; border-radius: 4px; }
        .top-bar a:hover { opacity: 0.8; background: rgba(255,255,255,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; font-weight: 600; color: #333; padding: 12px; text-align: left; border-bottom: 2px solid #ddd; font-size: 14px; }
        td { padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; }
        tr:hover { background: #f9f9f9; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <div>
            <h2>Buyer Dashboard</h2>
            <p>Welcome, <?php echo $fullname; ?>.</p>
        </div>
        <div class="top-bar">
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="card">
        <h3>Available Products</h3>
        <?php if ($products && $products->num_rows): ?>
            <div class="product-grid">
                <?php while ($product = $products->fetch_assoc()): ?>
                    <div class="product-card">
                        <?php if ($product['image_path']): ?>
                            <img src="image.php?id=<?php echo $product['id']; ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
                        <?php else: ?>
                            <div style="height:180px;background:#ddd;display:flex;align-items:center;justify-content:center;color:#666;">No image</div>
                        <?php endif; ?>
                        <div class="content">
                            <h4><?php echo htmlspecialchars($product['title']); ?></h4>
                            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                            <p><strong>Farmer:</strong> <?php echo htmlspecialchars(trim($product['first_name'] . ' ' . $product['middle_name'] . ' ' . $product['last_name'])); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="no-items">No products are available right now.</p>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3>Request Vehicle for Pickup</h3>
        <?php if ($availableVehicles && $availableVehicles->num_rows): ?>
            <form method="POST">
                <label for="product_id">Choose Product</label>
                <select id="product_id" name="product_id" required>
                    <?php
                    $productsAgain = $conn->query("SELECT id, title FROM products ORDER BY created_at DESC");
                    while ($prod = $productsAgain->fetch_assoc()):
                    ?>
                        <option value="<?php echo $prod['id']; ?>"><?php echo htmlspecialchars($prod['title']); ?></option>
                    <?php endwhile; ?>
                </select>
                <label for="vehicle_id">Choose Vehicle</label>
                <select id="vehicle_id" name="vehicle_id" required>
                    <?php while ($vehicle = $availableVehicles->fetch_assoc()): ?>
                        <option value="<?php echo $vehicle['id']; ?>"><?php echo htmlspecialchars($vehicle['name'] . ' (' . $vehicle['capacity'] . ')'); ?></option>
                    <?php endwhile; ?>
                </select>
                <label for="driver_id">Request Specific Driver</label>
                <select id="driver_id" name="driver_id">
                    <option value="">Any available driver</option>
                    <?php while ($driver = $availableDrivers->fetch_assoc()): ?>
                        <option value="<?php echo $driver['id']; ?>"><?php echo htmlspecialchars(trim($driver['first_name'] . ' ' . $driver['middle_name'] . ' ' . $driver['last_name'])); ?></option>
                    <?php endwhile; ?>
                </select>
                <label for="details">Pickup Details</label>
                <textarea id="details" name="details" rows="3"></textarea>
                <input type="submit" name="request_vehicle" value="Request Vehicle">
            </form>
        <?php else: ?>
            <p class="no-items">No available vehicles at the moment.</p>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3>Your Requests</h3>
        <?php if ($requests && $requests->num_rows): ?>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left;padding:8px;border-bottom:1px solid #ddd;">Product</th>
                        <th style="text-align:left;padding:8px;border-bottom:1px solid #ddd;">Vehicle</th>
                        <th style="text-align:left;padding:8px;border-bottom:1px solid #ddd;">Driver</th>
                        <th style="text-align:left;padding:8px;border-bottom:1px solid #ddd;">Status</th>
                        <th style="text-align:left;padding:8px;border-bottom:1px solid #ddd;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($request = $requests->fetch_assoc()): ?>
                        <tr>
                            <td style="padding:8px;border-bottom:1px solid #eee;"><?php echo htmlspecialchars($request['product_title']); ?></td>
                            <td style="padding:8px;border-bottom:1px solid #eee;"><?php echo htmlspecialchars($request['vehicle_name']); ?></td>
                            <td style="padding:8px;border-bottom:1px solid #eee;"><?php echo htmlspecialchars(trim($request['driver_first'] . ' ' . $request['driver_middle'] . ' ' . $request['driver_last'])); ?></td>
                            <td style="padding:8px;border-bottom:1px solid #eee;"><?php echo htmlspecialchars($request['status']); ?></td>
                            <td style="padding:8px;border-bottom:1px solid #eee;">
                                <?php if ($request['status'] === 'pending'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                        <button type="submit" name="cancel_request">Cancel</button>
                                    </form>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-items">No requests yet.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
