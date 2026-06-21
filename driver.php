<?php
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'driver') {
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
    if (isset($_POST['take_action'])) {
        $requestId = (int)$_POST['request_id'];
        $action = ($_POST['take_action'] ?? '') === 'accept' ? 'accepted' : 'rejected';
        $stmt = $conn->prepare("UPDATE transport_requests SET status = ?, driver_id = ? WHERE id = ? AND status = 'pending'");
        $stmt->bind_param('sii', $action, $userId, $requestId);
        if ($stmt->execute()) {
            $message = 'Order has been ' . ($action === 'accepted' ? 'accepted.' : 'rejected.');
        } else {
            $message = 'Could not update order status.';
        }
        $stmt->close();
    }
}

$pendingRequests = $conn->query("SELECT tr.id, tr.requester_name, tr.requester_role, tr.status, tr.details, v.name AS vehicle_name, p.title AS product_title
    FROM transport_requests tr
    LEFT JOIN vehicles v ON tr.vehicle_id = v.id
    LEFT JOIN products p ON tr.product_id = p.id
    WHERE tr.status = 'pending' AND (tr.driver_id IS NULL OR tr.driver_id = $userId)
    ORDER BY tr.created_at ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Driver Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); margin: 0; padding: 20px; min-height: 100vh; }
        .wrapper { max-width: 1200px; margin: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 8px 25px rgba(102, 126, 234, 0.25); }
        .header div:first-child h2 { font-size: 28px; margin-bottom: 5px; font-weight: 600; }
        .header p { font-size: 14px; opacity: 0.9; }
        .card { background: #fff; border-radius: 12px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 25px; border: 1px solid rgba(0,0,0,0.05); }
        .card h3 { margin: 0 0 20px 0; font-size: 18px; color: #333; font-weight: 600; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; font-weight: 600; color: #333; padding: 12px; text-align: left; border-bottom: 2px solid #ddd; font-size: 14px; }
        td { padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; }
        tr:hover { background: #f9f9f9; }
        button { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer; margin-right: 8px; font-weight: 500; transition: all 0.3s; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3); }
        button.reject { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); box-shadow: 0 4px 12px rgba(245, 87, 108, 0.3); }
        button:hover { transform: translateY(-2px); opacity: 0.95; }
        button.reject:hover { box-shadow: 0 6px 16px rgba(245, 87, 108, 0.4); }
        .message { padding: 15px 20px; background: #d4edda; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #28a745; color: #155724; font-weight: 500; }
        .message.error { background: #f8d7da; border-left-color: #dc3545; color: #721c24; }
        .top-bar { display: flex; align-items: center; }
        .top-bar a { color: #fff; text-decoration: none; font-weight: 500; transition: opacity 0.3s; padding: 8px 12px; border-radius: 4px; }
        .top-bar a:hover { opacity: 0.8; background: rgba(255,255,255,0.1); }
        .no-items { color: #999; font-style: italic; padding: 20px; text-align: center; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <div>
            <h2>Driver Dashboard</h2>
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
        <h3>Available Orders</h3>
        <?php if ($pendingRequests && $pendingRequests->num_rows): ?>
            <table>
                <thead>
                    <tr>
                        <th>Requester</th>
                        <th>Role</th>
                        <th>Product</th>
                        <th>Vehicle</th>
                        <th>Details</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $pendingRequests->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['requester_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['requester_role']); ?></td>
                            <td><?php echo htmlspecialchars($order['product_title'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($order['vehicle_name'] ?? 'N/A'); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($order['details'])); ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="request_id" value="<?php echo $order['id']; ?>">
                                    <button type="submit" name="take_action" value="accept">Accept</button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="request_id" value="<?php echo $order['id']; ?>">
                                    <button type="submit" name="take_action" value="reject" class="reject">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-items">No pending orders right now.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
