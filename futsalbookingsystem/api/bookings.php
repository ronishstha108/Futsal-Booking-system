<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    header("Location: ../login.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
} else {
    $action = $_GET['action'] ?? '';
}

switch ($action) {
    case 'create':
        $slot_id = $_POST['slot_id'];
        $booking_date = $_POST['booking_date'];
        
        // Server-side validation: Check if booking date is in the past
        $today = date('Y-m-d');
        if ($booking_date < $today) {
            echo "<script>alert('Cannot book for past dates. Please select today or a future date.'); window.history.back();</script>";
            exit;
        }
        
        // Check if slot is available
        $stmt = $pdo->prepare("
            SELECT id FROM bookings 
            WHERE slot_id = ? AND booking_date = ? AND status IN ('pending', 'approved')
        ");
        $stmt->execute([$slot_id, $booking_date]);
        
        if ($stmt->rowCount() > 0) {
            echo "<script>alert('This slot is already booked'); window.history.back();</script>";
            exit;
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO bookings (user_id, slot_id, booking_date) 
            VALUES (?, ?, ?)
        ");
        
        if ($stmt->execute([$_SESSION['user_id'], $slot_id, $booking_date])) {
            echo "<script>alert('Booking request submitted successfully!'); window.location.href='../my-bookings.php';</script>";
        } else {
            echo "<script>alert('Failed to create booking'); window.history.back();</script>";
        }
        break;
        
    case 'cancel':
        $booking_id = $_GET['id'];
        
        $stmt = $pdo->prepare("
            UPDATE bookings SET status = 'cancelled' 
            WHERE id = ? AND user_id = ?
        ");
        
        if ($stmt->execute([$booking_id, $_SESSION['user_id']])) {
            echo "<script>alert('Booking cancelled successfully'); window.location.href='../my-bookings.php';</script>";
        } else {
            echo "<script>alert('Failed to cancel booking'); window.history.back();</script>";
        }
        break;
        
    default:
        header("Location: ../dashboard.php");
}
?>