<?php
require_once '../includes/auth.php';
redirectIfNotAdmin();

require_once '../includes/config.php';

// Handle delete user
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $user_id = $_GET['id'];
    
    // Don't allow deleting own account
    if ($user_id == $_SESSION['user_id']) {
        header("Location: manage-users.php?error=Cannot delete your own account");
        exit;
    }
    
    // Delete user's bookings first
    $pdo->prepare("DELETE FROM bookings WHERE user_id = ?")->execute([$user_id]);
    
    // Delete user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$user_id])) {
        header("Location: manage-users.php?message=User deleted successfully");
        exit;
    }
}

// Get all users
$users = $pdo->query("
    SELECT u.*, COUNT(b.id) as booking_count 
    FROM users u 
    LEFT JOIN bookings b ON u.id = b.user_id 
    GROUP BY u.id 
    ORDER BY u.created_at DESC
")->fetchAll();

$message = $_GET['message'] ?? '';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Panel</title>
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
                    <li><a href="manage-users.php" class="active">Manage Users</a></li>
                    <li><a href="manage-slots.php">Manage Slots</a></li>
                    <li><a href="../api/auth.php?action=logout">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <h1>Manage Users</h1>
            
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
                <?php if (empty($users)): ?>
                    <p>No users found.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Bookings</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['name']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= htmlspecialchars($user['phone']) ?></td>
                                    <td>
                                        <span class="badge <?= $user['role'] == 'admin' ? 'approved' : 'pending' ?>">
                                            <?= ucfirst($user['role']) ?>
                                        </span>
                                    </td>
                                    <td><?= $user['booking_count'] ?></td>
                                    <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <a href="?action=delete&id=<?= $user['id'] ?>" 
                                               class="danger" 
                                               onclick="return confirm('Delete this user? This will also delete all their bookings.')">
                                                Delete
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #999;">Current User</span>
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