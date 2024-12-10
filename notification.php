<?php
session_start();
include 'admin/db_connect.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['login_user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['login_user_id'];

// Function to get user notifications
function getUserNotifications($conn, $userId, $limit = 10, $offset = 0) {
    $query = "
        SELECT 
            n.user_id,
            n.message,
            n.created_at,
            n.type,
            n.is_read,
            n.order_number,
    
            CASE 
                WHEN n.type = 'admin_reply' THEN m.admin_reply
                ELSE NULL 
            END as reply_content
        FROM notifications n
        LEFT JOIN messages m ON n.order_number = m.order_number
            AND m.user_id = n.user_id
        WHERE n.user_id = ? 
        ORDER BY n.created_at DESC
        LIMIT ? OFFSET ? 
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }
    
    $stmt->bind_param("iii", $userId, $limit, $offset);
    if (!$stmt->execute()) {
        die("Query execution failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    $stmt->close();
    return $notifications;
}

// Get total notification count
$countQuery = "SELECT COUNT(*) as total FROM notifications WHERE user_id = ?";
$stmt = $conn->prepare($countQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$totalCount = $stmt->get_result()->fetch_assoc()['total'];

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$totalPages = ceil($totalCount / $limit);

// Get notifications for current page
$notifications = getUserNotifications($conn, $userId, $limit, $offset);

// Get unread count
$unreadQuery = "SELECT COUNT(*) as unread FROM notifications WHERE user_id = ? AND is_read = 0";
$stmt = $conn->prepare($unreadQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$unreadCount = $stmt->get_result()->fetch_assoc()['unread'];

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Notifications</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .notification-item {
            transition: background-color 0.3s;
        }
        .notification-item:hover {
            background-color: #f8f9fa;
        }
        .unread {
            background-color: #f0f7ff;
        }
        .notification-time {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .notification-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .badge-notification {
            font-size: 0.75rem;
            padding: 0.25em 0.6em;
        }
        .pagination {
            margin-bottom: 2rem;
        }
        #loading-spinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <button onclick="history.back()" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
            </div>
            <h3>
                Notifications 
                <?php if ($unreadCount > 0): ?>
                    <span class="badge badge-primary"><?php echo $unreadCount; ?> new</span>
                <?php endif; ?>
            </h3>
            <div>
                <?php if ($unreadCount > 0): ?>
                    <button id="markAllRead" class="btn btn-outline-primary">
                        <i class="fas fa-check-double"></i> Mark All as Read
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Filters -->
      <!-- Filters -->
<div class="mb-3">
    <div class="btn-group" role="group">
        <button type="button" class="btn btn-outline-secondary active" data-filter="all">All</button>
        <button type="button" class="btn btn-outline-secondary" data-filter="admin_reply">Admin Replies</button>
        <button type="button" class="btn btn-outline-secondary" data-filter="delivery_status">Delivery Updates</button> <!-- New Button -->
    </div>
</div>

        
        <div class="list-group" id="notificationsList">
            <?php if (count($notifications) > 0): ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="list-group-item notification-item <?php echo !$notification['is_read'] ? 'unread' : ''; ?>" 
    data-type="<?php echo htmlspecialchars($notification['type']); ?>">

    <div class="d-flex w-100 justify-content-between">
        <h6 class="mb-1">
            <?php if ($notification['type'] === 'admin_reply'): ?>
                <i class="fas fa-reply text-info"></i>
            <?php endif; ?>
            <?php echo htmlspecialchars($notification['message']); ?>
        </h6>
        <?php if (!$notification['is_read']): ?>
            <span class="badge badge-primary badge-notification">New</span>
        <?php endif; ?>
    </div>

    <div class="notification-actions mt-2">
        <small class="notification-time">
            <i class="far fa-clock"></i>
            <?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?>
        </small>
        
        <?php if (!$notification['is_read']): ?>
            <button class="btn btn-sm btn-outline-primary mark-read" 
                    data-id="<?php echo $notification['order_number']; ?>">
                <i class="fas fa-check"></i> Mark as Read
            </button>
        <?php endif; ?>
    </div>

   
    

                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="list-group-item text-center">
                    <p class="mb-0">No notifications found</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Notifications pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page-1; ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page+1; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <!-- Loading Spinner -->
    <div id="loading-spinner" class="text-center">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
$(document).ready(function() {
    // Mark single notification as read
    $('.mark-read').click(function() {
        const btn = $(this);
        const notificationId = btn.data('id');
        
        $.ajax({
            url: 'mark_as_read.php',
            method: 'POST',
            data: {
                notification_id: notificationId
            },
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    if (result.success) {
                        btn.closest('.notification-item').removeClass('unread');
                        btn.remove(); // Remove the "Mark as Read" button
                    } else {
                        console.error('Error marking notification as read:', result.error);
                        alert('Failed to mark notification as read');
                    }
                } catch (e) {
                    console.error('Invalid JSON response:', response);
                    alert('An error occurred');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                alert('Failed to mark notification as read');
            }
        });
    });

    // Mark all notifications as read
    $('#markAllRead').click(function() {
        $.ajax({
            url: 'mark_all_read.php',
            method: 'POST',
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    if (result.success) {
                        $('.notification-item').removeClass('unread');
                        $('.mark-read').remove(); // Remove all "Mark as Read" buttons
                        $('#markAllRead').hide(); // Hide the "Mark All as Read" button
                    } else {
                        console.error('Error marking all notifications as read:', result.error);
                        alert('Failed to mark all notifications as read');
                    }
                } catch (e) {
                    console.error('Invalid JSON response:', response);
                    alert('An error occurred');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                alert('Failed to mark all notifications as read');
            }
        });
    });
    // Filter notifications
    $('[data-filter]').click(function() {
        var filter = $(this).data('filter');
        $('.notification-item').hide();  // Hide all notifications initially
        
        // Show based on the selected filter
        if (filter === 'all') {
            $('.notification-item').show();  // Show all notifications
        } else if (filter === 'admin_reply') {
            $('.notification-item').each(function() {
                if ($(this).data('type') === 'admin_reply') {
                    $(this).show();  // Show only admin replies
                }
            });
        } else if (filter === 'delivery_status') {
            $('.notification-item').each(function() {
                if ($(this).data('type') === 'delivery_status') {
                    $(this).show();  // Show only delivery updates
                }
            });
        }

        // Update active button
        $('.btn-outline-secondary').removeClass('active');
        $(this).addClass('active');
    });

    // Initial filter to show all notifications
    $('[data-filter="all"]').click();
});


    </script>
</body>
</html>
