<?php
require_once 'includes/auth.php';
redirectIfNotLoggedIn();

require_once 'includes/config.php';

// Fetch logged-in user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Futsal Booking</title>
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
                <li><a href="dashboard.php">Book Now</a></li>
                <li><a href="my-bookings.php">My Bookings</a></li>
                <li><a href="profile.php" class="active">Profile</a></li>

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
            <h1>My Profile</h1>

            <form action="api/auth.php" method="POST">
                <input type="hidden" name="action" value="update_profile">

                <div class="group">
                    <label for="name">Full Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="<?= htmlspecialchars($user['name'] ?? '') ?>" 
                        required
                    >
                </div>

                <div class="group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        value="<?= htmlspecialchars($user['email'] ?? '') ?>" 
                        readonly
                    >
                    <small>Email cannot be changed</small>
                </div>

                <div class="group">
                    <label for="phone">Phone Number</label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone" 
                        value="<?= htmlspecialchars($user['phone'] ?? '') ?>" 
                        required
                    >
                </div>

                <div class="group">
                    <label for="role">Account Type</label>
                    <input 
                        type="text" 
                        id="role" 
                        value="<?= ucfirst($user['role'] ?? 'user') ?>" 
                        readonly
                    >
                </div>

                <button type="submit" class="button">Update Profile</button>
            </form>

        </div>
    </div>
</main>

</body>
</html>
