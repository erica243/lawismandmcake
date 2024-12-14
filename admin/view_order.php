<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        #uni_modal .modal-dialog {
            max-width: 90%;
            width: auto;
        }
        #uni_modal .modal-body {
            overflow-x: auto;
        }
        #uni_modal .modal-footer {
            display: none;
        }
    </style>
</head>
<body>
<?php

include 'db_connect.php';

$orderId = $_GET['id'];

// Use prepared statements for security
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$orderStatus = $order['status']; // 1 for confirmed, 0 for not confirmed
$deliveryStatus = $order['delivery_status']; // Fetch delivery status

// Convert the order date to 'm-d-Y' format
$formatted_order_date = date("m-d-Y", strtotime($order['order_date']));

// Fetch order items
$stmt = $conn->prepare("SELECT o.qty, p.name, p.description, p.price, 
                                (o.qty * p.price) AS amount
                        FROM order_list o 
                        INNER JOIN product_list p ON o.product_id = p.id 
                        WHERE o.order_id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$orderItems = $stmt->get_result();

// Fetch shipping information based on the order's address
$address = $order['address']; // Get the address from the order
$shippingStmt = $conn->prepare("SELECT shipping_amount FROM shipping_info WHERE address = ?");
$shippingStmt->bind_param("s", $address);
$shippingStmt->execute();
$shippingResult = $shippingStmt->get_result();
$shippingAmount = $shippingResult->fetch_assoc()['shipping_amount'] ?? 0;
// Fetch proof of delivery if uploaded
$proofStmt = $conn->prepare("SELECT proof_of_delivery FROM orders WHERE id = ?");
$proofStmt->bind_param("i", $orderId);
$proofStmt->execute();
$proofResult = $proofStmt->get_result();
$proofOfDelivery = $proofResult->fetch_assoc()['proof_of_delivery'];
?>


<div class="container-fluid mt-4">
    <h4>Order Details</h4>
    <table class="table table-bordered mt-4">
        <thead>
            <tr>
                <th>Order Date</th>
                <th>Order Number</th>
                <th>Customer Name</th>
                <th>Address</th>
                <th>Delivery Method</th>
                <th>Mode of Payment</th>
                <th>Qty</th>
                <th>Product</th>
                <th>Description</th>
                <th>Price</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $total = 0;
            while ($row = $orderItems->fetch_assoc()):
                $total += $row['amount'];
            ?>
            <tr>
                <td><?php echo $formatted_order_date; ?></td>
                <td><?php echo $order['order_number']; ?></td>
                <td><?php echo $order['name']; ?></td>
                <td><?php echo $order['address']; ?></td>
                <td><?php echo $order['delivery_method']; ?></td>
                <td><?php echo $order['payment_method']; ?></td>
                <td><?php echo $row['qty']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['description']; ?></td>
                <td><?php echo number_format($row['price'], 2); ?></td>
                <td><?php echo number_format($row['amount'], 2); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="10" class="text-right">Subtotal</th>
                <th><?php echo number_format($total, 2); ?></th>
            </tr>
            <tr>
                <th colspan="10" class="text-right">Shipping Amount</th>
                <th><?php echo number_format($shippingAmount, 2); ?></th>
            </tr>
            <tr>
                <th colspan="10" class="text-right">TOTAL</th>
                <th><?php echo number_format($total + $shippingAmount, 2); ?></th>
            </tr>
        </tfoot>
         
        <?php if ($deliveryStatus === 'delivered'): ?>
    <button class="btn btn-success" onclick="send_receipt()">Send Receipt to Email</button>
<?php else: ?>
    <button class="btn btn-success" disabled title="Receipt can only be sent once the order is delivered.">
        Send Receipt to Email
    </button>
<?php endif; ?>

    </table>

    <div class="text-center mt-4">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

        <?php if ($deliveryStatus == 'confirmed' || $deliveryStatus == 'delivered'): ?>
    <button class="btn btn-success" type="button" onclick="print_receipt()">Print Receipt</button>
<?php endif; ?>


        <button class="btn btn-danger" type="button" id="delete_order" onclick="delete_order()">Delete Order</button>

        <!-- Delivery Status Dropdown -->
        <label for="delivery_status" class="mt-3">Update Delivery Status:</label>
        <select id="delivery_status" class="form-control w-50 mx-auto mt-2" onchange="update_delivery_status()">
            <option value="pending" 
                <?php echo !isset($deliveryStatus) || $deliveryStatus == 'pending' ? 'selected' : 'disabled'; ?>>
                Pending
            </option>
            <option value="confirmed" 
                <?php echo $deliveryStatus == 'confirmed' ? 'selected' : (!isset($deliveryStatus) || $deliveryStatus == 'pending' ? '' : 'disabled'); ?>>
                Confirmed
            </option>
            <option value="preparing" 
                <?php echo $deliveryStatus == 'preparing' ? 'selected' : (in_array($deliveryStatus, ['ready', 'in_transit', 'delivered']) ? 'disabled' : ''); ?>>
                Preparing
            </option>
            <option value="ready" 
                <?php echo $deliveryStatus == 'ready' ? 'selected' : (in_array($deliveryStatus, ['in_transit', 'delivered']) ? 'disabled' : ''); ?>>
                Ready For Delivery
            </option>
            <option value="in_transit" 
                <?php echo $deliveryStatus == 'in_transit' ? 'selected' : ($deliveryStatus == 'delivered' ? 'disabled' : ''); ?>>
                In transit
            </option>
            <option value="delivered" 
                <?php echo $deliveryStatus == 'delivered' ? 'selected disabled' : ''; ?>>
                Delivered
            </option>
        </select>
    </div>
</div><div class="mt-4">
    <h5>Proof of Delivery</h5>
    <?php if ($deliveryStatus == 'delivered' && $proofOfDelivery): ?>
        <img src="uploads/<?php echo $proofOfDelivery; ?>" alt="Proof of Delivery" class="img-thumbnail" style="max-width: 100px;">
        <p>You have already uploaded the proof of delivery.</p>
    <?php elseif ($deliveryStatus == 'delivered'): ?>
        <p>No proof of delivery uploaded yet.</p>
    <?php else: ?>
        <p>Proof of delivery will be available once the order is marked as delivered.</p>
    <?php endif; ?>
    
    <?php if ($deliveryStatus == 'delivered' && !$proofOfDelivery): ?>
        <form id="proofUploadForm" enctype="multipart/form-data" class="mt-2">
            <div class="form-group">
                <label for="proofInput">Upload Proof of Delivery:</label>
                <input type="file" id="proofInput" name="proof_of_delivery" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary" id="uploadButton">Upload</button>
        </form>
    <?php elseif ($deliveryStatus == 'delivered' && $proofOfDelivery): ?>
        <script>
            // Disable the button after the proof is uploaded
            document.getElementById('uploadButton').disabled = true;
        </script>
    <?php endif; ?>
</div>

<script>
    function confirm_order() {
        Swal.fire({
            title: 'Confirm Order',
            text: 'Are you sure you want to confirm this order?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, confirm!'
        }).then((result) => {
            if (result.isConfirmed) {
                start_load();
                $.ajax({
                    url: 'ajax.php?action=confirm_order',
                    method: 'POST',
                    data: { id: '<?php echo $_GET['id'] ?>' },
                    success: function(resp) {
                        if (resp == 1) {
                            Swal.fire('Confirmed!', 'Order has been successfully confirmed.', 'success').then(function() {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error!', 'Error confirming order: ' + resp, 'error');
                        }
                        end_load();
                    },
                    error: function() {
                        end_load();
                        Swal.fire('Error!', 'AJAX request failed.', 'error');
                    }
                });
            }
        });
    }
    
    function update_delivery_status() {
        var status = $('#delivery_status').val();
        var orderId = '<?php echo $_GET["id"]; ?>';

        Swal.fire({
            title: 'Update Delivery Status',
            text: 'Are you sure you want to update the delivery status?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, update it!'
        }).then((result) => {
            if (result.isConfirmed) {
                start_load();
                $.ajax({
                    url: 'ajax.php?action=update_delivery_status',
                    method: 'POST',
                    data: {
                        id: orderId,
                        status: status
                    },
                    success: function(resp) {
                        try {
                            var jsonResp = JSON.parse(resp); // Parse the JSON response

                            if (jsonResp.success) {
                                Swal.fire('Updated!', jsonResp.message, 'success').then(function() {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error!', jsonResp.message, 'error');
                            }
                        } catch (e) {
                            Swal.fire('Error!', 'Unexpected response: ' + resp, 'error');
                        }
                        end_load();
                    },
                    error: function() {
                        end_load();
                        Swal.fire('Error!', 'AJAX request failed.', 'error');
                    }
                });
            }
        });
    }

    function print_receipt() {
    // Open a new window for the receipt
    var receiptWindow = window.open('', '', 'height=800,width=600,location=no');

    // Logo and signature image URLs
    var logoUrl = '1.jpg'; // Replace with the actual logo URL
    var signatureUrl = '3.png'; // Corrected signature URL

    var orderNumber = '<?php echo $order["order_number"]; ?>';
    var orderDate = '<?php echo $formatted_order_date; ?>';
    var customerName = '<?php echo $order["name"]; ?>';
    var address = '<?php echo $order["address"]; ?>';
    var deliveryMethod = '<?php echo $order["delivery_method"]; ?>';
    var paymentMethod = '<?php echo $order["payment_method"]; ?>';
    var shippingAmount = '<?php echo number_format($shippingAmount, 2); ?>';
    var total = '<?php echo number_format($total + $shippingAmount, 2); ?>';

    // Write receipt content
    receiptWindow.document.write('<html><head><title>Receipt</title>');
    receiptWindow.document.write('<style>');
    
    // Custom style for POS receipt size
    receiptWindow.document.write('@page { size: 3in 6in; margin: 0; }');  // Set width to 3in and height to 6in (adjust as needed)
    receiptWindow.document.write('body { font-family: Arial, sans-serif; font-size: 12px; margin: 0; padding: 10px; }');
    receiptWindow.document.write('h2, h3 { text-align: center; margin: 5px 0; font-size: 16px; }');
    receiptWindow.document.write('.logo img { max-width: 150px; display: block; margin: 0 auto; }');
    receiptWindow.document.write('table { width: 100%; border-collapse: collapse; margin: 10px 0; }');
    receiptWindow.document.write('table, th, td { border: 1px solid #ddd; padding: 5px; font-size: 12px; }');
    receiptWindow.document.write('th { background-color: #f2f2f2; }');
    receiptWindow.document.write('.total { font-weight: bold; }');
    
    // Signature section style - adjust margin and padding to bring it closer
    receiptWindow.document.write('.signature { text-align: center; margin-top: 10px; }');
    receiptWindow.document.write('.signature img { max-width: 120px; margin-top: 5px; }');  // Ensure the signature image is directly below the text
    receiptWindow.document.write('.signature p { margin: 0; font-size: 12px; text-align: center; }');  // Remove margin and center the text

    receiptWindow.document.write('</style></head><body>');

    // Add logo
    receiptWindow.document.write('<div class="logo"><img src="' + logoUrl + '" alt="Company Logo"></div>');
    receiptWindow.document.write('<h2>M&M Cake Ordering System</h2>');
    receiptWindow.document.write('<h3>Poblacion Madridejos Cebu</h3>');
    receiptWindow.document.write('<hr>');

    // Add order details
    receiptWindow.document.write('<p><strong>Order Number:</strong> ' + orderNumber + '</p>');
    receiptWindow.document.write('<p><strong>Order Date:</strong> ' + orderDate + '</p>');
    receiptWindow.document.write('<p><strong>Customer Name:</strong> ' + customerName + '</p>');
    receiptWindow.document.write('<p><strong>Address:</strong> ' + address + '</p>');
    receiptWindow.document.write('<p><strong>Delivery Method:</strong> ' + deliveryMethod + '</p>');
    receiptWindow.document.write('<p><strong>Payment Method:</strong> ' + paymentMethod + '</p>');

    // Add order items table
    receiptWindow.document.write('<table>');
    receiptWindow.document.write('<thead><tr><th>Product</th><th>Description</th><th>Qty</th><th>Price</th><th>Amount</th></tr></thead>');
    receiptWindow.document.write('<tbody>');

    <?php
    $orderItems->data_seek(0);
    while ($row = $orderItems->fetch_assoc()): ?>
        receiptWindow.document.write('<tr>');
        receiptWindow.document.write('<td><?php echo $row["name"]; ?></td>');
        receiptWindow.document.write('<td><?php echo $row["description"]; ?></td>');
        receiptWindow.document.write('<td><?php echo $row["qty"]; ?></td>');
        receiptWindow.document.write('<td><?php echo number_format($row["price"], 2); ?></td>');
        receiptWindow.document.write('<td><?php echo number_format($row["amount"], 2); ?></td>');
        receiptWindow.document.write('</tr>');
    <?php endwhile; ?>

    receiptWindow.document.write('</tbody>');
    receiptWindow.document.write('<tfoot>');
    receiptWindow.document.write('<tr><td colspan="4" class="total">Subtotal</td><td class="total"><?php echo number_format($total, 2); ?></td></tr>');
    receiptWindow.document.write('<tr><td colspan="4" class="total">Shipping Amount</td><td class="total">' + shippingAmount + '</td></tr>');
    receiptWindow.document.write('<tr><td colspan="4" class="total">Grand Total</td><td class="total">' + total + '</td></tr>');
    receiptWindow.document.write('</tfoot>');
    receiptWindow.document.write('</table>');

    // Add signature
    receiptWindow.document.write('<p>Authorized Signature:</p>');
    receiptWindow.document.write('<div class="signature">');
    receiptWindow.document.write('<img src="' + signatureUrl + '" alt="Signature">');
 
    // Add thank you note
    receiptWindow.document.write('<p style="text-align: center; margin-top: 20px;">Thank you for your order!</p>');
    receiptWindow.document.write('</body></html>');

    receiptWindow.document.close(); // Close document
    
    // Ensure images are loaded before printing
    receiptWindow.onload = function () {
        receiptWindow.print();
    };
}


function send_receipt() {
    Swal.fire({
        title: 'Send Receipt to Email',
        text: 'Are you sure you want to send this receipt to the customer?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, send!'
    }).then((result) => {
        if (result.isConfirmed) {
            start_load();
            $.ajax({
                url: 'send_receipt.php',  // This should be the PHP file for sending the email
                method: 'POST',
                data: { order_id: '<?php echo $_GET['id']; ?>' },  // Send order ID for the backend
                success: function(response) {
                    if (response == 'Receipt sent to email!') {
                        Swal.fire('Success!', 'Receipt has been sent to the customer.', 'success');
                    } else {
                        Swal.fire('Error!', response, 'error');
                    }
                    end_load();
                },
                error: function() {
                    end_load();
                    Swal.fire('Error!', 'AJAX request failed.', 'error');
                }
            });
        }
    });
}
    function start_load() {
        $('body').prepend('<div id="preloader2"></div>');
    }

    function end_load() {
        $('#preloader2').fadeOut('fast', function() {
            $(this).remove();
        });
    }
    function delete_order() {
        Swal.fire({
            title: 'Delete Order',
            text: 'Are you sure you want to delete this order?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                start_load();
                $.ajax({
                    url: 'ajax.php?action=delete_order',
                    method: 'POST',
                    data: { id: '<?php echo $_GET['id'] ?>' },
                    success: function(resp) {
                        if (resp == 1) {
                            Swal.fire('Deleted!', 'Order has been successfully deleted.', 'success').then(function() {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error!', 'Error deleting order: ' + resp, 'error');
                        }
                        end_load();
                    },
                    error: function() {
                        end_load();
                        Swal.fire('Error!', 'AJAX request failed.', 'error');
                    }
                });
            }
        });
    }

    function start_load() {
        $('body').prepend('<div id="preloader2"></div>');
    }

    function end_load() {
        $('#preloader2').fadeOut('fast', function() {
            $(this).remove();
        });
    }
    $('#proofUploadForm').on('submit', function (e) {
    e.preventDefault(); // Prevent the default form submission
    var formData = new FormData(this); // Create a FormData object
    formData.append('order_id', '<?php echo $orderId; ?>'); // Include order ID

    Swal.fire({
        title: 'Uploading Proof of Delivery',
        text: 'Please wait...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: 'upload_proof.php', // Make sure this matches your server endpoint
        type: 'POST',
        data: formData,
        processData: false, // Necessary for FormData
        contentType: false, // Necessary for FormData
        success: function (response) {
            Swal.close();
            if (response === 'success') {
                Swal.fire('Success!', 'Proof of delivery uploaded successfully!', 'success').then(() => {
                    location.reload(); // Reload page to reflect the uploaded proof
                });
            } else {
                Swal.fire('Error!', response, 'error'); // Show error returned by the server
            }
        },
        error: function (xhr, status, error) {
            Swal.close();
            Swal.fire('Error!', 'AJAX request failed: ' + error, 'error'); // Display error details
        }
    });
});
</script>

<div id="preloader2" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.8); z-index: 9999;">
    <div class="text-center" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
</div>
</body>
</html>
