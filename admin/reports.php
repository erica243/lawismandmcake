<?php
include 'db_connect.php'; // Include your database connection

// Set default values for date filters
$from_date = isset($_POST['from_date']) ? $_POST['from_date'] : '';
$to_date = isset($_POST['to_date']) ? $_POST['to_date'] : '';

// Adjusted query to include confirmed orders (status = 1 and delivery_status = 'Confirmed') and date range if provided
$query = "
    SELECT o.order_date, ol.qty, ol.order_id, p.name AS product_name, o.order_number, o.payment_method, p.price, s.shipping_amount
    FROM orders o
    JOIN order_list ol ON o.id = ol.order_id
    JOIN product_list p ON ol.product_id = p.id
    LEFT JOIN shipping_info s ON o.address = s.address  -- Join based on shipping address
    WHERE o.delivery_status IN ('Confirmed', 'preparing', 'ready', 'in_transit', 'delivered')
";

if ($from_date && $to_date) {
    $query .= " AND o.order_date BETWEEN '$from_date' AND '$to_date'";
}

$query .= " ORDER BY o.order_date DESC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Responsive Modifications */
        @media (max-width: 768px) {
            .container-fluid {
                padding: 10px;
            }
            
            .form-inline {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            
            .form-inline .form-group {
                width: 100%;
                margin-right: 0 !important;
            }
            
            .table-responsive {
                overflow-x: auto;
            }
            
            .table {
                width: 100%;
            }
            
            .table td {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 150px;
            }
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
        <h2>Order Reports</h2>
        <form method="post" class="form-inline d-flex flex-column flex-md-row">
            <div class="form-group mr-2">
                <label for="from_date" class="mr-2">From Date:</label>
                <input type="date" class="form-control" name="from_date" id="from_date" value="<?php echo htmlspecialchars($from_date); ?>">
            </div>
            <div class="form-group mr-2">
                <label for="to_date" class="mr-2">To Date:</label>
                <input type="date" class="form-control" name="to_date" id="to_date" value="<?php echo htmlspecialchars($to_date); ?>">
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>
    <button onclick="printReport()" class="btn btn-primary mb-3">Print Reports</button>
    
    <div class="table-responsive">
        <?php if ($result->num_rows > 0): ?>
            <table class="table table-bordered" id="order-report-table">
                <thead>
                    <tr>
                        <th>Order Date</th>
                        <th>Product Name</th>
                        <th>Transaction ID</th>
                        <th>Mode of Payment</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Shipping Amount</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php
                        // Calculate total amount (price * quantity + shipping amount)
                        $shipping_amount = $row['shipping_amount'] ?? 0; // Handle null shipping amount
                        $total_amount = ($row['qty'] * $row['price']) + $shipping_amount;
                        ?>
                        <tr>
                            <td><?php echo date('m-d-Y', strtotime($row['order_date'])); ?></td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['order_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                            <td><?php echo htmlspecialchars($row['qty']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($row['price'], 2)); ?></td>
                            <td><?php echo htmlspecialchars(number_format($shipping_amount, 2)); ?></td>
                            <td><?php echo htmlspecialchars(number_format($total_amount, 2)); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <h2>No confirmed orders found</h2>
        <?php endif; ?>
    </div>
</div>

<?php
$conn->close();
?>
<script>
function printReport() {
    var printContents = document.getElementById('order-report-table').outerHTML;
    var originalContents = document.body.innerHTML;

    // Get current date
    var currentDate = new Date().toLocaleDateString();

    var headerContent = `
        <div style="text-align: center; margin-bottom: 20px;">
            <img id="print-logo" src="assets/img/logo.jpg" alt="Logo" style="max-width: 100px;">
            <h1>Order Reports</h1>
            <h2>M&M Cake Ordering System</h2>
        </div>
        <div style="text-align: right;">
            <p>${currentDate}</p>
        </div>
    `;
    var style = `
        <style>
            body { font-family: Arial, sans-serif; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #000; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
        </style>
    `;
    document.body.innerHTML = "<html><head><title>Order Reports</title>" + style + "</head><body>" + headerContent + printContents + "</body></html>";

    // Ensure the image is loaded before printing
    var printLogo = document.getElementById('print-logo');
    printLogo.onload = function() {
        window.print();
        document.body.innerHTML = originalContents;
        window.location.reload(); // Reload the page to restore the original content
    };

    // In case the image fails to load
    printLogo.onerror = function() {
        console.error("Failed to load logo image for printing.");
        window.print();
        document.body.innerHTML = originalContents;
        window.location.reload(); // Reload the page to restore the original content
    };
}
</script>
</body>
</html>