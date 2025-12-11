<?php
require_once '../includes/auth.php';
redirectIfNotAdmin();

require_once '../includes/config.php';

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $booking_id = $_GET['id'];
    
    $allowed_actions = ['approve', 'reject', 'cancel'];
    if (in_array($action, $allowed_actions)) {
        $status = $action == 'approve' ? 'approved' : ($action == 'reject' ? 'rejected' : 'cancelled');
        
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $booking_id])) {
            header("Location: manage-bookings.php?message=Booking " . $action . "d successfully");
            exit;
        }
    }
}

// Get all bookings
$bookings = $pdo->query("
    SELECT b.*, u.name as user_name, u.email, u.phone, s.start_time, s.end_time 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN slots s ON b.slot_id = s.id 
    ORDER BY b.booking_date DESC, s.start_time DESC
")->fetchAll();

$message = $_GET['message'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="nav">
                <div class="brand">
                    <h1>Admin Panel</h1>
                </div>
                <ul class="menu">
                    <!-- <li><a href="../index.html">Home</a></li> -->
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="manage-bookings.php" class="active">Manage Bookings</a></li>
                    <li><a href="manage-users.php">Manage Users</a></li>
                    <li><a href="manage-slots.php">Manage Slots</a></li>
                    <li><a href="../api/auth.php?action=logout">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <h1>Manage Bookings</h1>
            
            <?php if ($message): ?>
                <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 15px 0;">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <?php if (empty($bookings)): ?>
                    <p>No bookings found.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Booked On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                           <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><?= htmlspecialchars($booking['user_name']) ?></td>
                                    <td><?= htmlspecialchars($booking['email']) ?></td>
                                    <td><?= htmlspecialchars($booking['phone']) ?></td>
                                    <td><?= date('M j, Y', strtotime($booking['booking_date'])) ?></td>
                                    <td><?= date('g:i A', strtotime($booking['start_time'])) ?> - <?= date('g:i A', strtotime($booking['end_time'])) ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $booking['status'] ?>">
                                            <?= ucfirst($booking['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($booking['created_at'])) ?></td>
                                    <td>
                                        <?php if ($booking['status'] == 'pending'): ?>
                                            <a href="?action=approve&id=<?= $booking['id'] ?>" class="success">Approve</a>
                                            <a href="?action=reject&id=<?= $booking['id'] ?>" class="danger">Reject</a>
                                        <?php elseif ($booking['status'] == 'approved'): ?>
                                            <a href="?action=cancel&id=<?= $booking['id'] ?>" class="danger">Cancel</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?> 
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>