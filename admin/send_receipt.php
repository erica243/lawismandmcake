<?php
require '../vendor/autoload.php'; // Correct path to Composer's autoload file
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include 'db_connect.php'; // Include your DB connection

if (isset($_POST['order_id'])) {
    $orderId = $_POST['order_id'];

    // Fetch order data
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    // Fetch order items
    $stmt = $conn->prepare("SELECT o.qty, p.name, p.description, p.price, 
                                    (o.qty * p.price) AS amount
                            FROM order_list o 
                            INNER JOIN product_list p ON o.product_id = p.id 
                            WHERE o.order_id = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $orderItems = $stmt->get_result();

    // Get the shipping amount and calculate total
    $shippingStmt = $conn->prepare("SELECT shipping_amount FROM shipping_info WHERE address = ?");
    $shippingStmt->bind_param("s", $order['address']);
    $shippingStmt->execute();
    $shippingResult = $shippingStmt->get_result();
    $shippingAmount = $shippingResult->fetch_assoc()['shipping_amount'] ?? 0;

    $total = 0;
    while ($item = $orderItems->fetch_assoc()) {
        $total += $item['amount'];
    }

    // Setup PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'mandmcakeorderingsystem@gmail.com';
        $mail->Password = 'dgld kvqo yecu wdka';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Add recipient
        $mail->setFrom('mandmcakeorderingsystem@gmail.com', 'M&M Cake Ordering System');
        $mail->addAddress($order['email']);

        // Email Content
        $mail->isHTML(true);
        $mail->Subject = 'Your order has been delivered. Here is your receipt regarding  - ' . $order['order_number'];
        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; font-size: 14px; margin: 0; padding: 0; }
                .logo img { max-width: 150px; display: block; margin: 10px auto; }
                h2, h3 { text-align: center; margin: 5px 0; font-size: 18px; }
                table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                table, th, td { border: 1px solid #ddd; padding: 8px; }
                th { background-color: #f2f2f2; text-align: left; }
                .total { font-weight: bold; text-align: right; }
                .caption { text-align: center; font-size: 16px; margin: 20px 0; }
                .signature img { max-width: 120px; margin-top: 5px; display: block; margin: 0 auto; }
                .signature p { text-align: center; margin: 0; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='logo'>
                <img src='cid:logo' alt='Company Logo'>
            </div>
            <h2>M&M Cake Ordering System</h2>
            <h3>Poblacion Madridejos Cebu</h3>
            <hr>
           
            <p><strong>Order Number:</strong> {$order['order_number']}</p>
            <p><strong>Order Date:</strong> {$order['order_date']}</p>
            <p><strong>Customer Name:</strong> {$order['name']}</p>
            <p><strong>Address:</strong> {$order['address']}</p>
            <p><strong>Delivery Method:</strong> {$order['delivery_method']}</p>
            <p><strong>Payment Method:</strong> {$order['payment_method']}</p>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Description</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>";
        
        $orderItems->data_seek(0);
        while ($item = $orderItems->fetch_assoc()) {
            $mail->Body .= "
                <tr>
                    <td>{$item['name']}</td>
                    <td>{$item['description']}</td>
                    <td>{$item['qty']}</td>
                    <td>" . number_format($item['price'], 2) . "</td>
                    <td>" . number_format($item['amount'], 2) . "</td>
                </tr>";
        }
        
        $mail->Body .= "
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan='4' class='total'>Subtotal</td>
                        <td class='total'>" . number_format($total, 2) . "</td>
                    </tr>
                    <tr>
                        <td colspan='4' class='total'>Shipping Amount</td>
                        <td class='total'>" . number_format($shippingAmount, 2) . "</td>
                    </tr>
                    <tr>
                        <td colspan='4' class='total'>Grand Total</td>
                        <td class='total'>" . number_format($total + $shippingAmount, 2) . "</td>
                    </tr>
                </tfoot>
            </table>
            <div class='signature'>
                <p><strong>Authorized Signature:</strong></p>
                <img src='cid:signature' alt='Signature'>
            </div>
            <p style='text-align: center; margin-top: 20px;'>Thank you for your order!</p>
        </body>
        </html>";

        // Attach inline images
        $mail->addEmbeddedImage('logo.jpg', 'logo');
        $mail->addEmbeddedImage('3.png', 'signature');

        if ($mail->send()) {
            echo "Receipt sent to email!";
        } else {
            echo "Failed to send the receipt.";
        }
    } catch (Exception $e) {
        echo "Mailer Error: {$mail->ErrorInfo}";
    }
}
?>
