<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    header("Location: ../login.html");
    exit;
}

// Get available slots for a specific date
if (isset($_GET['date'])) {
    $date = $_GET['date'];
    
    $stmt = $pdo->prepare("
        SELECT s.* FROM slots s 
        WHERE s.is_available = 1 
        AND s.id NOT IN (
            SELECT slot_id FROM bookings 
            WHERE booking_date = ? AND status IN ('pending', 'approved')
        )
        ORDER BY s.start_time
    ");
    $stmt->execute([$date]);
    $slots = $stmt->fetchAll();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $slots]);
    exit;
}

header("Location: ../dashboard.php");
?>