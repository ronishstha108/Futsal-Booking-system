<?php
require_once '../includes/auth.php';
redirectIfNotAdmin();

require_once '../includes/config.php';

// Get stats
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_bookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$pending_bookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();
$today_bookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_date = CURDATE()")->fetchColumn();

// Recent bookings
$recent_bookings = $pdo->query("
    SELECT b.*, u.name as user_name, s.start_time, s.end_time 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN slots s ON b.slot_id = s.id 
    ORDER BY b.created_at DESC 
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Futsal Booking</title>
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
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="manage-bookings.php">Manage Bookings</a></li>
                    <li><a href="manage-users.php">Manage Users</a></li>
                    <li><a href="manage-slots.php">Manage Slots</a></li>
                    <li><a href="../api/auth.php?action=logout">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <h1>Admin Dashboard</h1>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 30px 0;">
                <div class="card" style="text-align: center;">
                    <h3>Total Users</h3>
                    <p style="font-size: 2rem; color: #3498db;"><?= $total_users ?></p>
                </div>
                <div class="card" style="text-align: center;">
                    <h3>Total Bookings</h3>
                    <p style="font-size: 2rem; color: #27ae60;"><?= $total_bookings ?></p>
                </div>
                <div class="card" style="text-align: center;">
                    <h3>Pending Bookings</h3>
                    <p style="font-size: 2rem; color: #f39c12;"><?= $pending_bookings ?></p>
                </div>
                <div class="card" style="text-align: center;">
                    <h3>Today's Bookings</h3>
                    <p style="font-size: 2rem; color: #9b59b6;"><?= $today_bookings ?></p>
                </div>
            </div>

            <div style="display: flex; gap: 15px; margin: 20px 0;">
                <a href="manage-users.php" class="button">Manage Users</a>
                <a href="manage-bookings.php" class="button">Manage Bookings</a>
                <a href="manage-slots.php" class="button">Manage Slots</a>
            </div>

            <div class="card">
                <h2>Recent Bookings</h2>
                <?php if (empty($recent_bookings)): ?>
                    <p>No recent bookings found.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_bookings as $booking): ?>
                                <tr>
                                    <td><?= htmlspecialchars($booking['user_name']) ?></td>
                                    <td><?= date('M j, Y', strtotime($booking['booking_date'])) ?></td>
                                    <td><?= date('g:i A', strtotime($booking['start_time'])) ?> - <?= date('g:i A', strtotime($booking['end_time'])) ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $booking['status'] ?>">
                                            <?= ucfirst($booking['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($booking['status'] == 'pending'): ?>
                                            <a href="manage-bookings.php?action=approve&id=<?= $booking['id'] ?>" class="success">Approve</a>
                                            <a href="manage-bookings.php?action=reject&id=<?= $booking['id'] ?>" class="danger">Reject</a>
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