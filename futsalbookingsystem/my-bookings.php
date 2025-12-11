<?php
require_once 'includes/auth.php';
redirectIfNotLoggedIn();

require_once 'includes/config.php';
$stmt = $pdo->prepare("
    SELECT b.*, s.start_time, s.end_time 
    FROM bookings b 
    JOIN slots s ON b.slot_id = s.id 
    WHERE b.user_id = ? 
    ORDER BY b.booking_date DESC, s.start_time DESC
");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Futsal Booking</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="nav">
                <div class="brand">
                    <h1>Futsal Booking</h1>
                </div>
                <ul class="menu">
                  <!--   <li><a href="index.html">Home</a></li> -->
                    <li><a href="dashboard.php">Book Now</a></li>
                    <li><a href="my-bookings.php" class="active">My Bookings</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <?php if (isAdmin()): ?>
                        <li><a href="admin/dashboard.php">Admin</a></li>
                    <?php endif; ?>
                    <li><a href="api/auth.php?action=logout">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="card">
                <h1>My Bookings</h1>
                
                <?php if (empty($bookings)): ?>
                    <p>No bookings found. <a href="dashboard.php">Book your first slot!</a></p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Action</th>
                                <th>Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><?= date('M j, Y', strtotime($booking['booking_date'])) ?></td>
                                    <td><?= date('g:i A', strtotime($booking['start_time'])) ?> - <?= date('g:i A', strtotime($booking['end_time'])) ?></td>
                                    <td>
                                        <span class="badge status-<?= $booking['status'] ?>">
                                            <?= ucfirst($booking['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($booking['status'] == 'pending'): ?>
                                            <a href="api/bookings.php?action=cancel&id=<?= $booking['id'] ?>" 
                                               class="danger" 
                                               onclick="return confirm('Cancel this booking?')">Cancel</a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                         <?php if ($booking['status'] == 'pending'): ?>
                                            <a href="payment.php" 
                                               class="success" 
                                               onclick="return confirm('Confirm Payment?')">Pay</a>
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