<?php
require_once '../includes/auth.php';
redirectIfNotAdmin();

require_once '../includes/config.php';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'add_slot') {
        $start_time = $_POST['start_time'] . ':00';
        $end_time = $_POST['end_time'] . ':00';
        
        // Check if slot already exists
        $stmt = $pdo->prepare("SELECT id FROM slots WHERE start_time = ? AND end_time = ?");
        $stmt->execute([$start_time, $end_time]);
        
        if ($stmt->rowCount() == 0) {
            $stmt = $pdo->prepare("INSERT INTO slots (start_time, end_time) VALUES (?, ?)");
            if ($stmt->execute([$start_time, $end_time])) {
                header("Location: manage-slots.php?message=Time slot added successfully");
                exit;
            }
        } else {
            header("Location: manage-slots.php?error=Time slot already exists");
            exit;
        }
    }
}

// Handle toggle availability
if (isset($_GET['action']) && $_GET['action'] == 'toggle' && isset($_GET['id'])) {
    $slot_id = $_GET['id'];
    
    $stmt = $pdo->prepare("SELECT is_available FROM slots WHERE id = ?");
    $stmt->execute([$slot_id]);
    $slot = $stmt->fetch();
    
    if ($slot) {
        $new_status = $slot['is_available'] ? 0 : 1;
        $pdo->prepare("UPDATE slots SET is_available = ? WHERE id = ?")->execute([$new_status, $slot_id]);
        header("Location: manage-slots.php?message=Slot availability updated");
        exit;
    }
}

// Handle delete slot
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $slot_id = $_GET['id'];
    
    // Check if slot has future bookings
    $stmt = $pdo->prepare("
        SELECT id FROM bookings 
        WHERE slot_id = ? AND status IN ('pending', 'approved') 
        AND booking_date >= CURDATE()
    ");
    $stmt->execute([$slot_id]);
    
    if ($stmt->rowCount() == 0) {
        $pdo->prepare("DELETE FROM slots WHERE id = ?")->execute([$slot_id]);
        header("Location: manage-slots.php?message=Slot deleted successfully");
        exit;
    } else {
        header("Location: manage-slots.php?error=Cannot delete slot with active or future bookings");
        exit;
    }
}

// Get all slots
$slots = $pdo->query("SELECT * FROM slots ORDER BY start_time")->fetchAll();

$message = $_GET['message'] ?? '';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Slots - Admin Panel</title>
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
                    <li><a href="manage-bookings.php">Manage Bookings</a></li>
                    <li><a href="manage-users.php">Manage Users</a></li>
                    <li><a href="manage-slots.php" class="active">Manage Slots</a></li>
                    <li><a href="../api/auth.php?action=logout">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <h1>Manage Time Slots</h1>
            
            <?php if ($message): ?>
                <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 15px 0;">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 15px 0;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <h2>Add New Time Slot</h2>
                <form method="POST" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 15px; align-items: end;">
                    <input type="hidden" name="action" value="add_slot">
                    <div class="group">
                        <label for="start_time">Start Time</label>
                        <input type="time" id="start_time" name="start_time" required step="3600">
                    </div>
                    <div class="group">
                        <label for="end_time">End Time</label>
                        <input type="time" id="end_time" name="end_time" required step="3600">
                    </div>
                    <button type="submit" class="button">Add Slot</button>
                </form>
            </div>

            <div class="card">
                <h2>Existing Time Slots</h2>
                <?php if (empty($slots)): ?>
                    <p>No time slots configured.</p>
                <?php else: ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px;">
                        <?php foreach ($slots as $slot): ?>
                            <div style="background: <?= $slot['is_available'] ? '#d4edda' : '#f8d7da' ?>; padding: 15px; border-radius: 5px; border-left: 4px solid <?= $slot['is_available'] ? '#27ae60' : '#e74c3c' ?>;">
                                <div style="font-weight: bold; font-size: 1.1rem;">
                                    <?= date('g:i A', strtotime($slot['start_time'])) ?> - <?= date('g:i A', strtotime($slot['end_time'])) ?>
                                </div>
                                <div style="margin: 10px 0;">
                                    <span class="badge <?= $slot['is_available'] ? 'approved' : 'rejected' ?>">
                                        <?= $slot['is_available'] ? 'Available' : 'Unavailable' ?>
                                    </span>
                                </div>
                                <div style="display: flex; gap: 10px;">
                                    <a href="?action=toggle&id=<?= $slot['id'] ?>" class="button <?= $slot['is_available'] ? 'danger' : 'success' ?>">
                                        <?= $slot['is_available'] ? 'Disable' : 'Enable' ?>
                                    </a>
                                    <a href="?action=delete&id=<?= $slot['id'] ?>" class="danger" onclick="return confirm('Delete this time slot?')">
                                        Delete
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>