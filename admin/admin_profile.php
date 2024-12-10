<?php
session_start();
include 'db_connect.php'; // Include your database connection file

// Fetch current admin details
$admin_id = $_SESSION['id']; // Assuming you store admin ID in session
$query = "SELECT username, profile_picture FROM users WHERE id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = $_POST['username'];
    $new_password = $_POST['password'];
    $upload_dir = 'uploads/';

    // Handle profile picture upload
    if ($_FILES['profile_picture']['name']) {
        $file_name = basename($_FILES['profile_picture']['name']);
        $target_file = $upload_dir . $file_name;
        $upload_ok = 1;

        // Check file type
        $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if ($image_file_type != "jpg" && $image_file_type != "png" && $image_file_type != "jpeg") {
            echo "Sorry, only JPG, JPEG, & PNG files are allowed.";
            $upload_ok = 0;
        }

        // Check if file already exists
        if (file_exists($target_file)) {
            echo "Sorry, file already exists.";
            $upload_ok = 0;
        }

        // Attempt to upload file
        if ($upload_ok && move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            // Update profile picture path in the database
            $update_pic_query = "UPDATE user_info SET profile_picture = ? WHERE id = ?";
            $stmt = $conn->prepare($update_pic_query);
            $stmt->bind_param("si", $file_name, $admin_id);
            $stmt->execute();
        }
    }

    // Update username and password
    if (!empty($new_username) || !empty($new_password)) {
        if (!empty($new_password)) {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE user_info SET username = ?, password = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssi", $new_username, $hashed_password, $admin_id);
        } else {
            $update_query = "UPDATE user_info SET username = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("si", $new_username, $admin_id);
        }
        $stmt->execute();
        echo "Profile updated successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Profile</title>
</head>
<body>
    <h1>Admin Profile</h1>
    <form action="admin_profile.php" method="POST" enctype="multipart/form-data">
        <div>
            <label for="username">Username:</label>
            <input type="email" name="username" id="username" value="<?php echo htmlspecialchars($admin['username']); ?>" required>
        </div>
        <div>
            <label for="password">New Password:</label>
            <input type="password" name="password" id="password">
        </div>
        <div>
            <label for="profile_picture">Profile Picture:</label>
            <input type="file" name="profile_picture" id="profile_picture" accept="image/*">
        </div>
        <div>
            <button type="submit">Update Profile</button>
        </div>
    </form>
    <img src="<?php echo 'uploads/' . htmlspecialchars($admin['profile_picture']); ?>" alt="Profile Picture" width="100">
</body>
</html>
