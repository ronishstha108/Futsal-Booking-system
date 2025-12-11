<?php
session_start();
include 'includes/config.php';

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['Submit'])) {

    // Check if file is selected
    if (!empty($_FILES['image']['name'])) {

        // File details
        $file = $_FILES['image']['name'];
        $tmp  = $_FILES['image']['tmp_name'];

        // Create a unique filename to avoid overwrite
        $uniqueName = time() . "_" . basename($file);

        // upload folder
        $path = "uploads/" . $uniqueName;

        // Move file
        if (move_uploaded_file($tmp, $path)) {

            // Insert user_id and file into database
            $sql = "INSERT INTO payment (user_id, image) VALUES (:user_id, :image)";
            $stmt = $pdo->prepare($sql);

            if ($stmt->execute([
                'user_id' => $user_id,
                'image'   => $uniqueName
            ])) {
                echo "<script>alert('Payment screenshot uploaded successfully!'); window.location='my-bookings.php';</script>";
            } else {
                echo "<script>alert('Database error occurred!');</script>";
            }

        } else {
            echo "<script>alert('File upload failed!');</script>";
        }
    } else {
        echo "<script>alert('Please select an image!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Payment Screenshot</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<form action="" method="POST" enctype="multipart/form-data" class="card">

    <h1>Esewa Number: <strong>9810160362</strong></h1>
    <h1>Account Details: <strong>012122131231</strong></h1>
    <br><br>

    <label for="payment_screenshot">Upload 30% payment screenshot (JPG / PNG)</label>
    <br>

    <input
        type="file"
        id="payment_screenshot"
        name="image"
        accept=".jpg, .jpeg, .png, image/*"
        required
    >

    <p style="font-size:0.9em; color:#666;">
        Allowed types: JPG, JPEG, PNG
    </p>

    <button type="submit" name="Submit">Submit</button>
</form>

</body>
</html>
