<?php
include 'db_connect.php'; // Ensure this is included once
include_once 'header.php'; // Ensure header.php is included only once

// Check if admin is logged in
if (!isset($_SESSION['login_id']) || $_SESSION['login_type'] != 1) {
    header('Location: login.php');
    exit;
}

if (isset($_POST['reply'])) {
    $message_id = $_POST['message_id'];
    $reply = $_POST['reply_message'];

    // Check if connection is successful
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Prepare the SQL query
    $stmt = $conn->prepare("UPDATE messages SET admin_reply = ?, reply_date = NOW(), status = 1 WHERE user_id = ?");
    
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error); // Error in the query
    }

    // Bind parameters and execute the statement
    $stmt->bind_param("si", $reply, $message_id); // "s" for string, "i" for integer
    if ($stmt->execute()) {
        echo "<script>alert('Reply sent successfully'); window.location='message.php';</script>";
    } else {
        echo "<script>alert('Failed to send reply');</script>";
    }

    // Close the prepared statement
    $stmt->close();
}

// Fetch messages without admin replies, but include user reply
$stmt = $conn->prepare("SELECT * FROM messages WHERE admin_reply IS NULL ORDER BY created_at DESC");
$stmt->execute();
$messages = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Admin - Messages</title>
    <?php include './header.php'; ?>
</head>
<style>
    main#view-panel {
        margin: 20px;
        padding: 20px;
        margin-left: 140px;
        float: left;
    }

    .table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
    }

    .table th, .table td {
        text-align: left;
        padding: 8px;
        border: 1px solid #ddd;
    }

    .table th {
        background-color: #f4f4f4;
    }

    .table img {
        max-width: 100px;
        max-height: 100px;
        object-fit: cover;
    }
</style>

<body>
    

    <main id="view-panel">
        <h1>Admin Messages</h1>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Message ID</th>
                    <th>Email</th>
                    <th>Order Number</th> <!-- New column for order number -->
                    <th>Message</th>
                    <th>Date</th>
                    <th>Image</th>
                   
                    <th>Reply</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $messages->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['order_number']); ?></td> <!-- Display order number -->
                    <td><?php echo htmlspecialchars($row['message']); ?></td>
                    <td><?php echo date('Y-m-d H:i:s', strtotime($row['created_at'])); ?></td>
                    <td>
    <?php
    if (!empty($row['photo_path'])) { // Correct column name from the database
        $photoPath = "../" . htmlspecialchars($row['photo_path']); // Prepend the correct path to the uploads directory
        
        // Check if the file exists and is an image
        if (file_exists($photoPath)) {
            $mimeType = mime_content_type($photoPath);
            if (strpos($mimeType, 'image/') === 0) {
                echo '<a href="' . htmlspecialchars($photoPath) . '" target="_blank">';
                echo '<img src="' . htmlspecialchars($photoPath) . '" alt="User Uploaded Photo" style="max-width: 100px; max-height: 100px;">';
                echo '</a>';
            } else {
                echo 'File is not a valid image.';
            }
        } else {
            echo 'Image file not found.';
        }
    } else {
        echo 'No image available.';
    }
    ?>
</td>
                  
                    <td>
                        <button class="btn btn-primary" onclick="showReplyForm(<?php echo $row['user_id']; ?>)">Reply</button>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>    
    </main>

    <!-- Modal for replying to messages -->
    <div class="modal fade" id="reply_modal" role="dialog">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reply to Message</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="reply_form" method="post">
                        <input type="hidden" name="message_id" id="message_id">
                        <div class="form-group">
                            <label for="reply_message">Reply Message:</label>
                            <textarea class="form-control" name="reply_message" id="reply_message" rows="4" required></textarea>
                        </div>
                        <button type="submit" name="reply" class="btn btn-primary">Send Reply</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="path/to/jquery.js"></script> <!-- Adjust the path to your jQuery file -->
    <script src="path/to/bootstrap.js"></script> <!-- Adjust the path to your Bootstrap JS file -->
    <script>
        function showReplyForm(messageId) {
            $('#message_id').val(messageId);
            $('#reply_message').val(''); // Clear previous reply
            $('#reply_modal').modal('show');
        }
    </script>
</body>

</html>
