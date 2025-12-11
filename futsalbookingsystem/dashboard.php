<?php
require_once 'includes/auth.php';
redirectIfNotLoggedIn();

// Get the selected date from URL or use today's date
$selected_date = $_GET['date'] ?? date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Futsal - Dashboard</title>
    <link rel="stylesheet" href="css/style.css">

    <style>
        /* Table Styling */
        .slot-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .slot-table th,
        .slot-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        .slot-table th {
            background: #2c3e50;
            color: white;
        }

        .slot-table tr:nth-child(even) {
            background: #f9f9f9;
        }

        .small-button {
            padding: 6px 12px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="nav">
                <div class="brand">
                    <h1>Futsal Booking</h1>
                </div>
                <ul class="menu">
                    <li><a href="dashboard.php" class="active">Book Now</a></li>
                    <li><a href="my-bookings.php">My Bookings</a></li>
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
                <h1>Book Your Futsal Slot</h1>
                <p>Select a date and available time slot</p>
                
                <form method="POST" action="api/bookings.php" onsubmit="return validateBookingDate()">
                    <div class="group">
                        <label for="bookingDate">Select Date:</label>
                        <input type="date" id="bookingDate" name="booking_date"
                               value="<?= $selected_date ?>" required>
                    </div>
                </form>

                <div id="slotsContainer">
                    <h3>Available Time Slots for <?= date('M j, Y', strtotime($selected_date)) ?></h3>

                    <div class="slots-grid">
                        <?php
                        require_once 'includes/config.php';
                        
                        $today = date('Y-m-d');

                        if ($selected_date < $today) {
                            echo '<p style="color: #e74c3c; font-weight: bold;">
                                    Cannot book for past dates. Please select today or a future date.
                                  </p>';
                        } else {

                            $stmt = $pdo->prepare("
                                SELECT s.* FROM slots s 
                                WHERE s.is_available = 1 
                                AND s.id NOT IN (
                                    SELECT slot_id FROM bookings 
                                    WHERE booking_date = ? AND status IN ('pending', 'approved')
                                )
                                ORDER BY s.start_time
                            ");
                            $stmt->execute([$selected_date]);
                            $slots = $stmt->fetchAll();

                            if (empty($slots)) {
                                echo '<p>No available slots for this date. Please select another date.</p>';
                            } else {

                                echo '<table class="slot-table">';
                                echo '<tr><th>Time</th><th>Action</th></tr>';

                                foreach ($slots as $slot):
                                    $start = date('g:i A', strtotime($slot['start_time']));
                                    $end   = date('g:i A', strtotime($slot['end_time']));
                        ?>
                                    <tr>
                                        <td><?= $start ?> - <?= $end ?></td>
                                        <td>
                                            <form method="POST" action="api/bookings.php">
                                                <input type="hidden" name="action" value="create">
                                                <input type="hidden" name="booking_date" value="<?= $selected_date ?>">
                                                <input type="hidden" name="slot_id" value="<?= $slot['id'] ?>">
                                                <button type="submit" class="button small-button">Book Now</button>
                                            </form>
                                        </td>
                                    </tr>
                        <?php
                                endforeach;

                                echo '</table>';
                            }
                        }
                        ?>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script>
        // Auto reload slots on date change
        document.getElementById('bookingDate').addEventListener('change', function() {
            const date = this.value;
            window.location.href = `dashboard.php?date=${date}`;
        });

        // Prevent booking past dates
        function validateBookingDate() {
            const bookingDate = document.getElementById('bookingDate').value;
            const today = new Date().toISOString().split('T')[0];
            
            if (bookingDate < today) {
                alert('Cannot book for past dates. Please select today or a future date.');
                return false;
            }
            return true;
        }

        console.log('Dashboard loaded for date: <?= $selected_date ?>');
    </script>
</body>
</html>
