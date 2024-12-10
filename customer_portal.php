<?php
session_start();
include('admin/db_connect.php');

// Ensure the user is logged in
if (!isset($_SESSION['login_user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['login_user_id'];

// Fetch user details from `user_info`
$user_query = $conn->query("SELECT first_name, last_name, email, mobile, address FROM user_info WHERE user_id = $user_id");
if (!$user_query) {
    die("Error fetching user details: " . $conn->error);
}
$user = $user_query->fetch_assoc();

// Fetch recent orders from `orders`
$orders_query = $conn->query("SELECT order_number, delivery_status, DATE_FORMAT(order_date, '%M %d, %Y') as formatted_date FROM orders WHERE user_id = $user_id ORDER BY order_date DESC LIMIT 5");
if (!$orders_query) {
    die("Error fetching orders: " . $conn->error);
}

// Fetch unread notifications from `notifications`
$notifications_query = $conn->query("SELECT message, DATE_FORMAT(created_at, '%M %d, %Y %h:%i %p') as formatted_date FROM notifications WHERE user_id = $user_id AND is_read = 0 ORDER BY created_at DESC");
if (!$notifications_query) {
    die("Error fetching notifications: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<?php include('header.php'); ?>

<body>
    <div class="container mt-5">
        <!-- Back Button -->
        <div class="mt-4">
            <button onclick="history.back()" class="btn btn-secondary">Back</button>
        </div>

        <!-- Welcome and Profile Section -->
        <div class="row mt-4">
            <div class="col-12">
                <h2>Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>!</h2>
                <p>Manage your orders, and view your profile here.</p>
            </div>
        </div>

        <!-- Recent Orders Section -->
        <div class="row mt-4">
            <div class="col-12">
                <h4>Recent Orders</h4>
                <?php if ($orders_query->num_rows > 0): ?>
                    <!-- Make the table responsive on small screens -->
                    <div class="table-responsive"> <!-- Added table-responsive class -->
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Order Number</th>
                                    <th>Delivery Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($order = $orders_query->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo htmlspecialchars($order['order_number']); ?></td>
                                        <td><?php echo htmlspecialchars($order['delivery_status']); ?></td>
                                        <td><?php echo htmlspecialchars($order['formatted_date']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>No recent orders found.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Notifications Section -->
        <div class="row mt-4">
            <div class="col-12">
                <h4>Notifications</h4>
                <?php if ($notifications_query->num_rows > 0): ?>
                    <ul class="list-group">
                        <?php while ($notification = $notifications_query->fetch_assoc()): ?>
                            <li class="list-group-item">
                                <?php echo htmlspecialchars($notification['message']); ?>
                                <small class="text-muted d-block"><?php echo htmlspecialchars($notification['formatted_date']); ?></small>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p>No new notifications.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Profile Details Section -->
        <div class="row mt-4">
            <div class="col-12 col-md-6">
                <h4>Your Profile</h4>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Mobile:</strong> <?php echo htmlspecialchars($user['mobile']); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
                <a href="profile.php" class="btn btn-primary">Edit Profile</a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies (Ensure you have these included in the header for full functionality) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
