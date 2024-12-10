<?php
session_start();
include('admin/db_connect.php');

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['login_user_id'])) {
    die("User not logged in.");
}

// Validate order_id
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    die("Invalid Order ID.");
}

$order_id = intval($_GET['order_id']);

// Fetch order and user details, including shipping amount with default value
$query = "SELECT o.order_number, o.order_date, o.delivery_method, o.payment_method, 
                 u.first_name, u.last_name, u.address, 
                 IFNULL(s.shipping_amount, 0.00) AS shipping_amount
          FROM orders o
          JOIN user_info u ON o.user_id = u.user_id
          LEFT JOIN shipping_info s ON o.id = s.id
          WHERE o.id = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows === 0) {
    die("No order found.");
}
$order_details = $order_result->fetch_assoc();

// Debugging: Verify if shipping_amount is retrieved
// Uncomment the line below to debug
// var_dump($order_details);

$shipping_amount = $order_details['shipping_amount']; // Ensure this is correctly assigned

// Fetch products for the order
$product_query = "SELECT p.name AS product_name, ol.qty AS quantity, p.price 
                  FROM order_list ol
                  JOIN product_list p ON ol.product_id = p.id
                  WHERE ol.order_id = ?";
$product_stmt = $conn->prepare($product_query);

if (!$product_stmt) {
    die("Prepare failed: " . $conn->error);
}

$product_stmt->bind_param("i", $order_id);
$product_stmt->execute();
$product_result = $product_stmt->get_result();

$products = [];
$total_qty = 0;
$total_price = 0.00;

while ($row = $product_result->fetch_assoc()) {
    $products[] = $row;
    $total_qty += $row['quantity'];
    $total_price += $row['quantity'] * $row['price'];
}

$grand_total = $total_price + $shipping_amount; // Adding shipping amount to grand total

$stmt->close();
$product_stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - Order #<?php echo htmlspecialchars($order_details['order_number']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1, h2 {
            text-align: center;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            max-width: 150px;
        }
        .customer-info, .order-info {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
        .total {
            font-weight: bold;
        }
        .signature {
            margin-top: 50px;
            text-align: center;
        }
        .signature div {
            margin: 20px 0;
        }
        @media print {
            body {
                margin: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="logo">
        <img src="1.jpg" alt="Company Logo">
        <h2>M&M Cake Ordering System</h2>
        <h3>Poblacion Madridejos Cebu</h3>
    </div>

    <p>Receipt for Order #<?php echo htmlspecialchars($order_details['order_number']); ?></p>

    <div class="customer-info">
        <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($order_details['first_name'] . ' ' . $order_details['last_name']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($order_details['address']); ?></p>
    </div>

    <div class="order-info">
        <p><strong>Order Date:</strong> <?php echo htmlspecialchars($order_details['order_date']); ?></p>
        <p><strong>Delivery Method:</strong> <?php echo htmlspecialchars($order_details['delivery_method']); ?></p>
        <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order_details['payment_method']); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                    <td><?php echo number_format($product['price'], 2); ?></td>
                    <td><?php echo number_format($product['quantity'] * $product['price'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td class="total">Total Quantity</td>
                <td class="total"><?php echo $total_qty; ?></td>
                <td class="total">Total Price</td>
                <td class="total"><?php echo number_format($total_price, 2); ?></td>
            </tr>
            <tr>
                <td colspan="3" class="total">Shipping Amount</td>
                <td class="total"><?php echo number_format($shipping_amount, 2); ?></td>
            </tr>
            <tr>
                <td colspan="3" class="total">Grand Total</td>
                <td class="total"><?php echo number_format($grand_total, 2); ?></td>
            </tr>
        </tbody>
    </table>

    <div class="signature">
        <div>
            <strong>Authorized Signature</strong>
            <br>
            <img src="3.png" alt="Authorized Signature" style="max-width: 150px; height: auto;">
        </div>
        <div>
            <strong>Date:</strong> <?php echo date('F j, Y'); ?>
        </div>
    </div>

    <script>
        window.print();
    </script>
</body>
</html>
