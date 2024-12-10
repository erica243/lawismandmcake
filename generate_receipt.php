<?php
include('admin/db_connect.php');

if (!isset($_GET['order_id'])) {
    die("Order ID not specified.");
}

$order_id = intval($_GET['order_id']);

// Fetch order details
$query = "
    SELECT o.order_number, o.order_date, o.delivery_method, o.payment_method, 
           ol.qty AS quantity, p.name AS product_name, p.price, 
           (ol.qty * p.price) AS total_price
    FROM orders o
    JOIN order_list ol ON o.id = ol.order_id
    JOIN product_list p ON ol.product_id = p.id
    WHERE o.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Order not found.");
}

$order = $result->fetch_assoc();
?>

<div class="receipt-details">
    <h1>Order Receipt</h1>
    <p><strong>Order Number:</strong> <?php echo htmlspecialchars($order['order_number']); ?></p>
    <p><strong>Order Date:</strong> <?php echo htmlspecialchars($order['order_date']); ?></p>
    <p><strong>Delivery Method:</strong> <?php echo htmlspecialchars($order['delivery_method']); ?></p>
    <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
    <hr>
    <h2>Items:</h2>
    <p><strong>Product:</strong> <?php echo htmlspecialchars($order['product_name']); ?></p>
    <p><strong>Quantity:</strong> <?php echo htmlspecialchars($order['quantity']); ?></p>
    <p><strong>Price:</strong> $<?php echo number_format($order['price'], 2); ?></p>
    <p><strong>Total:</strong> $<?php echo number_format($order['total_price'], 2); ?></p>
</div>
