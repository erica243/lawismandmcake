<?php
// Include necessary files and start session
include 'admin/db_connect.php';
session_start();

// Check if `order_id` is provided in the URL
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    die("Order ID is required to leave a comment.");
}

// Fetch the `order_id` from the URL and sanitize
$order_id = intval($_GET['order_id']);

// Check database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Query to fetch the email and order_number for the given order_id
$stmt = $conn->prepare("SELECT order_number, email FROM orders WHERE id = ?");
if (!$stmt) {
    die("Failed to prepare query: " . $conn->error);
}

$stmt->bind_param("i", $order_id);
if (!$stmt->execute()) {
    die("Failed to execute query: " . $stmt->error);
}

$result = $stmt->get_result();
$order = $result->fetch_assoc();

// Check if the order exists
if (!$order) {
    die("Order not found.");
}

// Variables for order details
$order_number = htmlspecialchars($order['order_number']);
$email = htmlspecialchars($order['email']);

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment = htmlspecialchars($_POST['comment']);
    $uploaded_file = $_FILES['photo'] ?? null;
    $photo_path = null;

    // Handle optional photo upload
    if ($uploaded_file && $uploaded_file['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $file_name = basename($uploaded_file['name']);
        $target_path = $upload_dir . time() . '_' . $file_name;

        // Ensure upload directory exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Move uploaded file
        if (move_uploaded_file($uploaded_file['tmp_name'], $target_path)) {
            $photo_path = $target_path;
        } else {
            $message = "Failed to upload the photo.";
        }
    }

    // Insert comment into the database
    $stmt = $conn->prepare("INSERT INTO messages (order_number, email, message, image_path) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        die("Failed to prepare insert query: " . $conn->error);
    }
    $stmt->bind_param("ssss", $order_number, $email, $comment, $photo_path);
    if ($stmt->execute()) {
        // Redirect to avoid resubmission on refresh
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit(); // Stop further execution
    } else {
        $message = "Failed to submit comment: " . $stmt->error;
    }
}

// Delete comment functionality
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // Query to delete the comment
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ? AND email = ?");
    if (!$stmt) {
        die("Failed to prepare delete query: " . $conn->error);
    }
    $stmt->bind_param("is", $delete_id, $email);  // Only the owner can delete their comment
    if ($stmt->execute()) {
        $message = "Comment deleted successfully.";
        header("Location: " . $_SERVER['REQUEST_URI']); // Refresh the page to reflect changes
        exit(); // Stop further execution
    } else {
        $message = "Failed to delete comment: " . $stmt->error;
    }
}

// Fetch the comments and admin replies
$stmt = $conn->prepare("SELECT id, message, image_path, admin_reply, created_at FROM messages WHERE order_number = ? ORDER BY created_at DESC");
if (!$stmt) {
    die("Failed to prepare comments query: " . $conn->error);
}
$stmt->bind_param("s", $order_number);
if (!$stmt->execute()) {
    die("Failed to fetch comments: " . $stmt->error);
}
$comments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave a Comment</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
        .container {
            max-width: 800px;
        }
        .comment-box {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .comment-box img {
            max-width: 100px;
            border-radius: 5px;
        }
        .admin-reply {
            background-color: #e9f7ef;
            padding: 10px;
            border-left: 3px solid #28a745;
            margin-top: 15px;
        }
        .alert {
            margin-top: 20px;
        }
        .back-button {
            color: #007bff;
            text-decoration: none;
            font-size: 1rem;
            margin-bottom: 20px;
            display: inline-block;
        }
        .back-button:hover {
            text-decoration: underline;
        }
        .delete-button {
            color: red;
            text-decoration: none;
            font-size: 1rem;
        }
        .delete-button:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    
    <div class="container mt-5">
        <a href="my_orders.php" class="back-button">Back to Orders</a>

        <h2 class="mb-4">Leave a Message for Order #<?php echo $order_number; ?></h2>
        <p>Email: <strong><?php echo $email; ?></strong></p>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo strpos($message, 'success') !== false ? 'success' : 'danger'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="comment">Your Message:</label>
                <textarea id="comment" name="comment" class="form-control" rows="4" placeholder="Write your comment here..." required></textarea>
            </div>
            <div class="form-group">
                <label for="photo">Upload a Photo (optional):</label>
                <input type="file" id="photo" name="photo" class="form-control-file" accept="image/*">
            </div>
            <button type="submit" class="btn btn-success btn-block">Submit Message</button>
            <a href="index.php" class="btn btn-secondary btn-block mt-2">Cancel</a>
        </form>

        <hr>
        <h3>Previous Messages:</h3>
        <?php while ($row = $comments->fetch_assoc()): ?>
            <div class="comment-box">
                <p><strong>You:</strong> <?php echo htmlspecialchars($row['message']); ?></p>
                
                <?php if (!empty($row['image_path'])): ?>
                    <p><strong>Photo:</strong> <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="Uploaded Image"></p>
                <?php endif; ?>
                
                <?php if (!empty($row['admin_reply'])): ?>
                    <div class="admin-reply">
                        <strong>Admin Reply:</strong>
                        <p><?php echo htmlspecialchars($row['admin_reply']); ?></p>
                    </div>
                    <div class="alert alert-info mt-2">
                        <strong>Notification:</strong> Admin has replied to your message.
                    </div>
                <?php else: ?>
                    <p><em>No reply from admin yet.</em></p>
                <?php endif; ?>

                <!-- Delete Button -->
                <a href="?order_id=<?php echo $order_id; ?>&delete_id=<?php echo $row['id']; ?>" class="delete-button">Delete</a>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>
