<?php
// Include necessary files and start session
include 'admin/db_connect.php';
session_start();

// Enable error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Check if the user is logged in
if (!isset($_SESSION['login_user_id'])) {
    die("You must be logged in to leave a comment.");
}

// Fetch the logged-in user's ID
$user_id = intval($_SESSION['login_user_id']);

// Check if `order_id` is provided in the URL
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    die("Order ID is required to leave a comment.");
}

// Fetch the `order_id` from the URL
$order_id = intval($_GET['order_id']);

// Query to fetch the email and order_number for the given order_id
$stmt = $conn->prepare("SELECT order_number, email FROM orders WHERE id = ?");
if (!$stmt) {
    die("Failed to prepare query: " . $conn->error);
}
$stmt->bind_param("i", $order_id);
$stmt->execute();
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

    // Handle optional photo upload
    if ($uploaded_file && $uploaded_file['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $file_name = basename($uploaded_file['name']);
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $file_mime = mime_content_type($uploaded_file['tmp_name']);
        $max_file_size = 2 * 1024 * 1024; // 2MB

        // Validate file extension and MIME type
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];

        if (!in_array($file_extension, $allowed_extensions) || !in_array($file_mime, $allowed_mime_types)) {
            die("Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.");
        }

        if ($uploaded_file['size'] > $max_file_size) {
            die("File size exceeds the maximum limit of 2MB.");
        }

        $target_path = $upload_dir . uniqid() . '.' . $file_extension;

        // Ensure upload directory exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Move uploaded file
        if (move_uploaded_file($uploaded_file['tmp_name'], $target_path)) {
            $photo_path = $target_path;
        } else {
            die("Failed to upload file. Please try again.");
        }
    } else {
        $photo_path = null; // No file uploaded
    }

    // Insert comment into the database, including the logged-in user's ID
    $stmt = $conn->prepare("INSERT INTO messages (user_id, order_number, email, message, photo_path) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Failed to prepare insert query: " . $conn->error);
    }
    $stmt->bind_param("issss", $user_id, $order_number, $email, $comment, $photo_path);
    $stmt->execute();

    // Redirect to avoid resubmitting on refresh
    header("Location: ?order_id=" . $order_id . "&submitted=1");
    exit();
}

// Delete comment functionality
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // Prepare the query for deleting the comment
    $stmt = $conn->prepare("DELETE FROM messages WHERE user_id = ? AND id = ?");
    if (!$stmt) {
        die("Failed to prepare delete query: " . $conn->error);
    }

    // Bind parameters and execute the query
    $stmt->bind_param("ii", $user_id, $delete_id); // Only the owner can delete their comment
    $stmt->execute();
}

// Fetch the comments and admin replies
$stmt = $conn->prepare("SELECT user_id, message, photo_path, admin_reply FROM messages WHERE order_number = ? ORDER BY created_at DESC");
if (!$stmt) {
    die("Failed to prepare select query: " . $conn->error);
}
$stmt->bind_param("s", $order_number);
$stmt->execute();
$comments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>For Customization Leave a Message</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Custom styles omitted for brevity */
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>For Customization Leave a Message #<?php echo $order_number; ?></h2>
        <p>Email: <?php echo $email; ?></p>

        <?php if (isset($_GET['submitted']) && $_GET['submitted'] == 1): ?>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Comment Submitted!',
                    text: 'Your comment has been successfully submitted.',
                });
            </script>
        <?php endif; ?>

        <!-- Form and Comments Section -->
        <!-- Omitted for brevity -->
    </div>
</body>
</html>
