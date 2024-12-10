<?php
session_start();
include('admin/db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['login_user_id'])) {
    die("User not logged in.");
}

$message = ''; // Variable to store success/error messages

// Handle order cancellation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_order'])) {
    $order_id = intval($_POST['order_id']);

    // First, check the current status of the order
    $status_check_query = "SELECT delivery_status FROM orders WHERE id = ?";
    $status_check_stmt = $conn->prepare($status_check_query);
    $status_check_stmt->bind_param("i", $order_id);
    $status_check_stmt->execute();
    $status_result = $status_check_stmt->get_result();
    $order_status = $status_result->fetch_assoc()['delivery_status'];

    // Only allow cancellation if the order is not confirmed or in progress
    if (strcasecmp($order_status, 'confirmed') != 0 && strcasecmp($order_status, 'in progress') != 0) {
        // Cancel the order by updating its status
        $cancel_query = "UPDATE orders SET delivery_status = 'cancelled' WHERE id = ?";
        $cancel_stmt = $conn->prepare($cancel_query);

        if (!$cancel_stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $cancel_stmt->bind_param("i", $order_id);

        // Execute and check for errors
        if (!$cancel_stmt->execute()) {
            $message = "Error cancelling order: " . $cancel_stmt->error;
        } else {
            $message = "Order cancelled successfully.";
        }
    } else {
        $message = "This order cannot be cancelled as it has been confirmed or is in progress.";
    }
}

// Handle form submission for rating and feedback
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rate_product'])) {
    $rating = intval($_POST['rating']);
    $feedback = isset($_POST['feedback']) ? trim($_POST['feedback']) : '';
    $product_id = intval($_POST['product_id']);
    $user_id = $_SESSION['login_user_id'];

    // Check if the user has already rated this product
    $check_query = "SELECT id FROM product_ratings WHERE user_id = ? AND product_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ii", $user_id, $product_id);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        // Update the rating and feedback
        $update_query = "UPDATE product_ratings SET rating = ?, feedback = ? WHERE user_id = ? AND product_id = ?";
        $update_stmt = $conn->prepare($update_query);
        
        if (!$update_stmt) {
            die("Prepare failed: " . $conn->error);
        }
        
        $update_stmt->bind_param("isii", $rating, $feedback, $user_id, $product_id);
        
        // Execute and check for errors
        if (!$update_stmt->execute()) {
            $message = "Error updating rating and feedback: " . $update_stmt->error;
        } else {
            $message = "Thank you for updating your rating and feedback!";
        }
    } else {
        // Insert rating and feedback into the database
        $rating_query = "INSERT INTO product_ratings (user_id, product_id, rating, feedback) VALUES (?, ?, ?, ?)";
        $rating_stmt = $conn->prepare($rating_query);
        
        if (!$rating_stmt) {
            die("Prepare failed: " . $conn->error);
        }
        
        $rating_stmt->bind_param("iiis", $user_id, $product_id, $rating, $feedback);
        
        // Execute and check for errors
        if (!$rating_stmt->execute()) {
            $message = "Error inserting rating and feedback: " . $rating_stmt->error;
        } else {
            $message = "Thank you for rating the product and leaving feedback!";
        }
    }
}

// Fetch orders for the user
$user_id = $_SESSION['login_user_id'];
$query = "SELECT o.id, o.order_number, o.order_date, o.delivery_method, o.payment_method, 
                 p.id AS product_id, p.name AS product_name, ol.qty AS quantity, p.price, 
                 pr.rating, pr.feedback, o.delivery_status 
          FROM orders o
          JOIN order_list ol ON o.id = ol.order_id
          JOIN product_list p ON ol.product_id = p.id
          JOIN user_info u ON u.email = o.email
          LEFT JOIN product_ratings pr ON pr.user_id = u.user_id AND pr.product_id = p.id
          WHERE u.user_id = ?
          ORDER BY o.order_date DESC";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
       /* Global Styles */
        :root {
            --primary-color: #4caf50;
            --secondary-color: #ff9800;
            --danger-color: #f44336;
            --background-color: #f9f9f9;
            --card-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            --border-color: #ddd;
            --text-color: #333;
            --button-hover: #45a049;
            --button-disabled: #cccccc;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
            padding: 2rem 1rem;
        }

        header, footer {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        header h1 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .back-button {
            padding: 0.6rem 1.2rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 1rem;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            background-color: var(--button-hover);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 8px;
            box-shadow: var(--card-shadow);
            margin-top: 2rem;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid var(--border-color);
        }

        th {
            background-color: #f4f4f4;
            font-weight: bold;
        }

        .star-rating {
            display: inline-flex;
            gap: 0.25rem;
        }

        .star-rating input[type="radio"] {
            display: none;
        }

        .star-rating label {
            font-size: 1.5rem;
            color: #ccc;
            cursor: pointer;
        }

        .star-rating input[type="radio"]:checked ~ label {
            color: gold;
        }

        button {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: opacity 0.3s, background-color 0.3s;
        }

        button[type="submit"] {
            background-color: #e80f1c;
            color: white;
        }

        button[type="submit"]:hover {
            background-color: #e68900;
        }

        /* Update button styling for rounded corners */
button, button[type="submit"], button[name="delete_order"] {
    border-radius: 20px; /* Makes the buttons fully rounded */
    padding: 0.5rem 1rem;
    cursor: pointer;
    font-size: 0.9rem;
    transition: opacity 0.3s, background-color 0.3s;
}

button[type="submit"]:hover {
    background-color: #e68900;
}

button[name="delete_order"]:hover {
    background-color: #d32f2f;
}

/* Update the message container for rounded corners */
.message {
    margin-top: 1rem;
    padding: 1rem;
    background-color: #f7f7f7;
    border: 1px solid #ccc;
    border-radius: 20px; /* Makes the message container rounded */
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.message p {
    margin: 0;
}


        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            table th, table td {
                padding: 10px;
            }
        }

   /* Table Styles */
.table-container {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin: 0;
}

table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: var(--card-shadow);
}

table th,
table td {
    padding: 1rem;
    text-align: left;
    border: 1px solid var(--border-color);
}

/* Sticky header */
table th {
    background-color: #f8f9fa;
    font-weight: 600;
    position: sticky;
    top: 0;
    z-index: 1; /* Ensures header stays on top of the table body */
}

@media (max-width: 768px) {
    table, thead, tbody, th, td, tr {
        display: block;
    }

    thead {
        display: none; /* Hide table headers for mobile */
    }

    tr {
        margin-bottom: 1rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        box-shadow: var(--card-shadow);
        background: white;
        padding: 1rem;
    }

    td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border: none;
        border-bottom: 1px solid var(--border-color);
    }

    td:last-child {
        border-bottom: none;
    }

    td:before {
        content: attr(data-label);
        font-weight: bold;
        flex: 1;
        color: var(--primary-color);
    }

    td span {
        flex: 2;
        text-align: right;
    }
}


/* Small mobile devices */
@media (max-width: 480px) {
    main {
        padding: 0.5rem;
        margin: 0.5rem;
    }

    td {
        font-size: 0.9rem;
        padding: 0.75rem 0.75rem 0.75rem 45%;
    }

    td:before {
        font-size: 0.9rem;
    }

    .star-rating label {
        font-size: 1.25rem;
    }
}
button:disabled {
    background-color: #cccccc;
    cursor: not-allowed;
    opacity: 0.5;
}
/* Style for "Track Order" button */
.track-order-btn {
    display: inline-block;
    padding: 0.6rem 1.2rem;
    background-color: var(--primary-color); /* Same as Cancel Order */
    color: white;
    text-align: center;
    font-size: 0.9rem;
    border-radius: 20px; /* Rounded corners */
    transition: background-color 0.3s, opacity 0.3s;
    text-decoration: none; /* Remove underline */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.track-order-btn:hover {
    background-color: #45a049; /* Darker shade on hover */
    opacity: 0.9; /* Slightly fade to give hover effect */
}

.track-order-btn:active {
    background-color: #388e3c; /* Darker shade on active */
    opacity: 1; /* Reset opacity when clicked */
}

/* Style for "Message" button */
.message-btn {
    display: inline-block;
    padding: 0.6rem 1.2rem;
    background-color: var(--secondary-color); /* Same as Cancel Order */
    color: white;
    text-align: center;
    font-size: 0.9rem;
    border-radius: 20px; /* Rounded corners */
    transition: background-color 0.3s, opacity 0.3s;
    text-decoration: none; /* Remove underline */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.message-btn:hover {
    background-color: #e68900; /* Darker shade on hover */
    opacity: 0.9; /* Slightly fade to give hover effect */
}

.message-btn:active {
    background-color: #d32f2f; /* Darker shade on active */
    opacity: 1; /* Reset opacity when clicked */
}

/* Style for buttons disabled or unavailable */
.text-muted {
    color: #aaa;
    font-size: 0.9rem;
}/* Hover effect for star ratings */
.star:hover,
    .star:focus {
        color: #FFD700;
        transition: color 0.3s ease;
    }

    /* Apply checked star color */
    input[type="radio"]:checked + .star {
        color: #FFD700;
    }

    /* Disable text area resize for better design */
    textarea {
        resize: vertical;
    }

    /* Style the rated-message */
    .rated-message {
        font-size: 1rem;
        font-weight: 600;
        color: #38A169;
        padding: 0.5rem;
        border-radius: 0.375rem;
        background-color: #D1F9E0;
        margin-top: 1rem;
    }
    </style>
</head>
<body>
    <header>
        <h1>My Orders</h1>
    </header>
    <main>
        <a href="index.php" class="back-button">Back to Home</a>

        <div class="table-container">
        <table>
        <thead>
            <tr>
                <th>Order Number</th>
                <th>Order Date</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Delivery Method</th>
                <th>Payment Method</th>
                <th>Delivery Status</th>
                <th>Order Tracking</th>
                <th>Rate Product</th>
                <th>Cancel Order</th>
                <th>Message</th>
            </tr>
        </thead>
        <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td data-label="Order Number">
            <span><?php echo htmlspecialchars($row['order_number']); ?></span>
        </td>
        <td data-label="Order Date">
            <span><?php echo htmlspecialchars($row['order_date']); ?></span>
        </td>
        <td data-label="Product Name">
            <span><?php echo htmlspecialchars($row['product_name']); ?></span>
        </td>
        <td data-label="Quantity">
            <span><?php echo htmlspecialchars($row['quantity']); ?></span>
        </td>
        <td data-label="Price">
            <span><?php echo htmlspecialchars($row['price']); ?></span>
        </td>
        <td data-label="Delivery Method">
            <span><?php echo htmlspecialchars($row['delivery_method']); ?></span>
        </td>
        <td data-label="Payment Method">
            <span><?php echo htmlspecialchars($row['payment_method']); ?></span>
        </td>
        <td data-label="Delivery Status">
            <span><?php echo htmlspecialchars($row['delivery_status']); ?></span>
        </td>
        <td data-label="Order Tracking">
    <span>
        <?php if (strcasecmp($row['delivery_status'], 'cancelled') == 0): ?>
            <span class="text-muted">Tracking Unavailable</span>
            <?php else: ?>
            <a href="track_order.php?order_id=<?php echo $row['id']; ?>" class="track-order-btn">
                Track Order
            </a>
        <?php endif; ?>
    </span>
</td>
<td data-label="Rate Product">
    <span>
        <?php if (strcasecmp($row['delivery_status'], 'delivered') == 0): ?>
            <?php if ($row['rating'] > 0): ?>
                <div class="rated-message text-green-600 font-semibold">You have already rated this product.</div>
            <?php else: ?>
                <form method="POST" action="" class="mt-4 space-y-4">
                    <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                    <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">

                    <label for="rating" class="block text-lg font-semibold text-gray-700">Rate this product:</label>
                    <div class="star-rating flex space-x-1">
                        <!-- Radio buttons for the stars, hidden inputs with labels for display -->
                        <input type="radio" id="star5_<?php echo $row['id']; ?>" name="rating" value="5" <?php if ($row['rating'] == 5) echo 'checked'; ?> class="hidden">
                        <label for="star5_<?php echo $row['id']; ?>" class="star text-2xl cursor-pointer">&#9733;</label>

                        <input type="radio" id="star4_<?php echo $row['id']; ?>" name="rating" value="4" <?php if ($row['rating'] == 4) echo 'checked'; ?> class="hidden">
                        <label for="star4_<?php echo $row['id']; ?>" class="star text-2xl cursor-pointer">&#9733;</label>

                        <input type="radio" id="star3_<?php echo $row['id']; ?>" name="rating" value="3" <?php if ($row['rating'] == 3) echo 'checked'; ?> class="hidden">
                        <label for="star3_<?php echo $row['id']; ?>" class="star text-2xl cursor-pointer">&#9733;</label>

                        <input type="radio" id="star2_<?php echo $row['id']; ?>" name="rating" value="2" <?php if ($row['rating'] == 2) echo 'checked'; ?> class="hidden">
                        <label for="star2_<?php echo $row['id']; ?>" class="star text-2xl cursor-pointer">&#9733;</label>

                        <input type="radio" id="star1_<?php echo $row['id']; ?>" name="rating" value="1" <?php if ($row['rating'] == 1) echo 'checked'; ?> class="hidden">
                        <label for="star1_<?php echo $row['id']; ?>" class="star text-2xl cursor-pointer">&#9733;</label>
                    </div>

                    <div class="flex justify-between items-center">
                        <textarea name="feedback" placeholder="Leave your feedback here..." class="w-full mt-2 p-3 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" rows="4"></textarea>
                    </div>

                    <button type="submit" name="rate_product" class="mt-4 w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-300">Submit Rating</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </span>
</td>
<td data-label="Cancel Order">
    <?php 
    // Check if the order is already cancelled
    $isCancelled = (strcasecmp($row['delivery_status'], 'cancelled') == 0);
    
    // Only show Cancel Order button if status is not confirmed, preparing, ready, delivered, or in transit
    $cancelAllowed = !in_array(strtolower($row['delivery_status']), ['confirmed', 'preparing', 'ready', 'delivered', 'in_transit']);
    ?>
    <?php if ($cancelAllowed): ?>
        <form method="POST">
            <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
            <button 
                type="submit" 
                name="cancel_order" 
                <?php echo $isCancelled ? 'disabled' : ''; ?> 
                onclick="return confirm('Are you sure you want to cancel this order?');"
            >
                <?php echo $isCancelled ? 'Cancelled' : 'Cancel Order'; ?>
            </button>
        </form>
    <?php else: ?>
        <span class="text-muted">
            <?php 
            if ($isCancelled) {
                echo 'Order Cancelled';
            } else {
                echo 'Cancellation not available';
            }
            ?>
        </span>
    <?php endif; ?>
</td>
<td data-label="Message">
    <?php if (strcasecmp($row['delivery_status'], 'delivered') == 0): ?>
        <!-- Optionally add a message if delivered -->
    <?php endif; ?>
    
    <?php if (strcasecmp($row['delivery_status'], '') == 0): ?>
        <a href="comment.php?order_id=<?php echo $row['id']; ?>" class="message-btn">
            Message
        </a>
    <?php else: ?>
        <span class="text-muted">Message no longer available</span>
    <?php endif; ?>
</td>
    </tr>
    <?php endwhile; ?>
    </tbody>
    </table>

    <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>
    </main>

    <script>
   

    // New JavaScript for handling order cancellation
    document.addEventListener('DOMContentLoaded', function() {
        // Select all cancel order forms
        const cancelForms = document.querySelectorAll('form[name="cancel_order"]');

        cancelForms.forEach(form => {
            form.addEventListener('submit', function(event) {
                const cancelButton = this.querySelector('button[name="cancel_order"]');
                
                // Confirm before cancellation
                if (confirm('Are you sure you want to cancel this order?')) {
                    // Disable the button immediately
                    cancelButton.disabled = true;
                    cancelButton.textContent = 'Cancelling...';
                    cancelButton.style.opacity = '0.5';
                    cancelButton.style.cursor = 'not-allowed';
                } else {
                    // Prevent form submission if cancelled
                    event.preventDefault();
                }
            });
        });
    });
    </script>

    <footer>
        <p>&copy; 2024 Cake Ordering System</p>
    </footer>
</body>
</html>