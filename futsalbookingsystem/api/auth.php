<?php
require_once '../includes/config.php';

// Start session at the beginning
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
} else {
    $action = $_GET['action'] ?? '';
}

switch ($action) {
    case 'login':
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            if ($user['role'] == 'admin') {
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: ../dashboard.php");
            }
            exit;
        } else {
            echo "<script>alert('Invalid email or password'); window.history.back();</script>";
        }
        break;
        
    case 'register':
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $phone = $_POST['phone'];
        
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            echo "<script>alert('Email already registered'); window.history.back();</script>";
            exit;
        }
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone) VALUES (?, ?, ?, ?)");
        
        if ($stmt->execute([$name, $email, $hashed_password, $phone])) {
            echo "<script>alert('Registration successful! Please login.'); window.location.href='../login.html';</script>";
        } else {
            echo "<script>alert('Registration failed'); window.history.back();</script>";
        }
        break;
        
    case 'update_profile':
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        
        $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
        if ($stmt->execute([$name, $phone, $_SESSION['user_id']])) {
            $_SESSION['user_name'] = $name;
            echo "<script>alert('Profile updated successfully'); window.location.href='../profile.php';</script>";
        } else {
            echo "<script>alert('Failed to update profile'); window.history.back();</script>";
        }
        break;
        
    case 'logout':
        session_destroy();
        header("Location: ../index.html");
        break;
        
    default:
        header("Location: ../index.html");
}
?>