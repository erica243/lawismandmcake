<?php
require_once("./db_connect.php");

// Fetch data for total sales including shipping for confirmed, delivered, or other specified statuses
$total_sales_result = $conn->query("
    SELECT 
        SUM((p.price * ol.qty) + IFNULL(s.shipping_amount, 0)) AS total_sales
    FROM 
        orders o
    JOIN 
        order_list ol ON o.id = ol.order_id
    JOIN 
        product_list p ON ol.product_id = p.id
    LEFT JOIN 
        shipping_info s ON o.address = s.address
    WHERE 
        o.delivery_status IN ('pending','confirmed', 'preparing', 'ready','in_transit','delivered') -- Replace 'other_status' with any additional status
");
$total_sales = $total_sales_result->fetch_assoc()['total_sales'];

$cancelled_orders = $conn->query("SELECT * FROM orders WHERE status = 0")->num_rows;
$confirmed_orders = $conn->query("SELECT * FROM orders WHERE status = 1")->num_rows;

// Fetch data for pie chart (sales by address)
$sales_by_address_data = $conn->query("
    SELECT o.address AS address, SUM((p.price * ol.qty) + IFNULL(s.shipping_amount, 0)) AS total_sales 
    FROM order_list ol
    JOIN product_list p ON ol.product_id = p.id
    JOIN orders o ON ol.order_id = o.id
    LEFT JOIN 
        shipping_info s ON o.address = s.address
    WHERE o.delivery_status IN ('pending','confirmed', 'preparing', 'ready','in_transit','delivered')
    GROUP BY o.address
    ORDER BY total_sales DESC
");

$data = [];
while ($row = $sales_by_address_data->fetch_assoc()) {
    $data[] = $row;
}

// Fetch monthly sales data for the last 12 months
$monthly_sales_data = [];
for ($i = 0; $i < 12; $i++) {
    $date = date('Y-m', strtotime("-$i months"));
    $monthly_sales_result = $conn->query("SELECT SUM((p.price * ol.qty) + IFNULL(s.shipping_amount, 0)) AS monthly_sales 
                                          FROM orders o 
                                          JOIN order_list ol ON o.id = ol.order_id
                                          JOIN product_list p ON ol.product_id = p.id 
                                          LEFT JOIN 
        shipping_info s ON o.address = s.address
                                          WHERE  o.delivery_status IN ('pending','confirmed', 'preparing', 'ready','in_transit','delivered') AND DATE_FORMAT(o.created_at, '%Y-%m') = '$date'");
    $monthly_sales = $monthly_sales_result->fetch_assoc()['monthly_sales'];
    $month_name = date('F', strtotime($date));

    $monthly_sales_data[$month_name] = $monthly_sales ?: 0;
}

// Query to fetch total number of categories
$sql = "SELECT COUNT(*) AS total_categories FROM category_list";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_categories = $row['total_categories'];
} else {
    $total_categories = 0; // Default value if no categories found
}

// Query to fetch total number of products
$sql = "SELECT COUNT(*) AS total_products FROM product_list";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_products = $row['total_products'];
} else {
    $total_products = 0; // Default value if no products found
}

// Fetch total number of users
$sql = "SELECT COUNT(*) as total_users FROM user_info";
$result = $conn->query($sql);

$total_users = 0;
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_users = $row['total_users'];
}
// Fetch total number of orders
$sql = "SELECT COUNT(*) as total_orders FROM orders";
$result = $conn->query($sql);

$total_orders = 0;
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_orders = $row['total_orders'];
}
// Fetch the number of Pending Orders (where delivery status is 'pending', NULL, or empty)
$pending_orders_result = $conn->query("
    SELECT * 
    FROM orders 
    WHERE delivery_status = 'pending' 
       OR delivery_status IS NULL 
       OR delivery_status = ''
");
$pending_orders = $pending_orders_result->num_rows;

 
/// Fetch the number of Confirmed Orders (including statuses: confirmed, preparing, read, in_transit, delivered)
$confirmed_orders_result = $conn->query("
SELECT * 
FROM orders 
WHERE delivery_status IN ('confirmed', 'preparing', 'ready', 'in_transit', 'delivered')
");
$confirmed_orders = $confirmed_orders_result->num_rows;
$top_products_query = "
    SELECT 
        p.id, 
        p.name, 
        SUM(ol.qty) as total_quantity_sold, 
        SUM(ol.qty * p.price) as total_product_sales
    FROM 
        order_list ol
    JOIN 
        product_list p ON ol.product_id = p.id
    JOIN 
        orders o ON ol.order_id = o.id
    WHERE 
        o.delivery_status IN ('pending','confirmed', 'preparing', 'ready','in_transit','delivered')
    GROUP BY 
        p.id, p.name
    ORDER BY 
        total_product_sales DESC
    LIMIT 10
";
$top_products_result = $conn->query($top_products_query);
$top_products = [];
while ($row = $top_products_result->fetch_assoc()) {
    $top_products[] = $row;
}

// New query to fetch monthly sales for top products
$monthly_product_sales = [];
$top_product_ids = array_column($top_products, 'id');
if (!empty($top_product_ids)) {
    $product_ids_string = implode(',', $top_product_ids);
    
    for ($i = 0; $i < 12; $i++) {
        $date = date('Y-m', strtotime("-$i months"));
        $monthly_product_query = "
        SELECT 
            p.id, 
            p.name, 
            SUM(ol.qty * p.price) as monthly_product_sales
        FROM 
            order_list ol
        JOIN 
            product_list p ON ol.product_id = p.id
        JOIN 
            orders o ON ol.order_id = o.id
        WHERE 
            p.id IN ($product_ids_string)
            AND o.delivery_status IN ('pending','confirmed', 'preparing','ready','in_transit','delivered')
            AND DATE_FORMAT(o.created_at, '%Y-%m') = '$date'
        GROUP BY 
            p.id, p.name
    ";
        $monthly_product_result = $conn->query($monthly_product_query);
        
        while ($row = $monthly_product_result->fetch_assoc()) {
            $monthly_product_sales[$date][$row['id']] = $row['monthly_product_sales'] ?: 0;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Responsive Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />


    <style>
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-20px);
            }
            60% {
                transform: translateY(-10px);
            }
        }

        .bounce {
            animation: bounce 2s infinite;
        }

        .custom-menu {
            z-index: 1000;
            position: absolute;
            background-color: #ffffff;
            border: 1px solid #0000001c;
            border-radius: 5px;
            padding: 8px;
            min-width: 13vw;
        }

        a.custom-menu-list {
            width: 100%;
            display: flex;
            color: #4c4b4b;
            font-weight: 600;
            font-size: 1em;
            padding: 1px 11px;
        }

        span.card-icon {
            position: absolute;
            font-size: 3em;
            bottom: .2em;
            color: #ffffff80;
        }

        .file-item {
            cursor: pointer;
        }

        a.custom-menu-list:hover, .file-item:hover, .file-item.active {
            background: #80808024;
        }

        table th, td {
            /*border-left:1px solid gray;*/
        }

        a.custom-menu-list span.icon {
            width: 1em;
            margin-right: 5px;
        }

        .candidate {
            margin: auto;
            width: 23vw;
            padding: 0 10px;
            border-radius: 20px;
            margin-bottom: 1em;
            display: flex;
            border: 3px solid #00000008;
            background: #8080801a;
        }

        .candidate_name {
            margin: 8px;
            margin-left: 3.4em;
            margin-right: 3em;
            width: 100%;
        }

        .img-field {
            display: flex;
            height: 8vh;
            width: 4.3vw;
            padding: .3em;
            background: #80808047;
            border-radius: 50%;
            position: absolute;
            left: -.7em;
            top: -.7em;
        }

        .candidate img {
            height: 100%;
            width: 100%;
            margin: auto;
            border-radius: 50%;
        }

        .vote-field {
            position: absolute;
            right: 0;
            bottom: -.4em;
        }

        .card-custom {
            border-left: 4px solid #007bff;
        }

        .card-custom-primary {
            border-left-color: #007bff;
        }

        .card-custom-danger {
            border-left-color: #dc3545;
        }

        .card-custom-success {
            border-left-color: #28a745;
        }

        .card-custom-warning {
            border-left-color: #ffc107;
        }

        .bg-light-blue {
            background-color: #cce5ff;
        }

        .bg-light-red {
            background-color: #f8d7da;
        }

        .bg-light-green {
            background-color: #d4edda;
        }

        .bg-light-yellow {
            background-color: #fff3cd;
        }

        .fa-bounce {
            animation: bounce 2s infinite;
        }
           /* Media Queries for Responsiveness */
           @media (max-width: 768px) {
            .card-body h5 {
                font-size: 20px;
            }

            .card-body h2 {
                font-size: 24px;
            }

            .media .fa {
                font-size: 2em !important;
            }
        }

        /* Enhanced Responsiveness */
        body {
            overflow-x: hidden;
        }

        .dashboard-container {
            width: 100%;
            max-width: 100%;
        }

        /* Card Responsive Adjustments */
         .card-custom {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card-custom:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .bounce {
            animation: bounce 1s infinite alternate;
        }
        @keyframes bounce {
            from { transform: translateY(0); }
            to { transform: translateY(-10px); }
        }
        /* Ensure text doesn't overflow */
        .card-body h5, .card-body h2 {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .card-custom {
                margin-bottom: 15px;
            }
            .card-body {
                text-align: center;
            }
            .card-body .media {
                flex-direction: column;
                align-items: center;
            }
            .card-body .media-left {
                margin-bottom: 10px;
            }
        }
        /* Chart Container Responsiveness */
        .chart-container {
            position: relative;
            width: 100%;
            height: 300px;
        }

        @media (max-width: 576px) {
            .chart-container {
                height: 250px;
            }
        }

        /* Scrollable Cards on Small Screens */
        @media (max-width: 768px) {
            .card-responsive {
                overflow-x: auto;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@2.0.1"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@latest"></script>


</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h1 >Dashboard</h1>
            </div>
        </div>
<br>
        <div class="row g-3">
            <!-- Total Sales Card -->
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
                <div class="card card-custom rounded-0 shadow h-100" style="background: #bf80ff;">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                        <div class="icon-container mb-3">
                            <i class="fa fa-money-bill-wave bounce" style="font-size: 50px; color: green;" aria-hidden="true"></i>
                        </div>
                        <div class="text-container">
                        <h5 style="color: black; font-size: 24px; font-family: 'Courier New', monospace; font-weight: bold;">Total Sales</h5>

                            <h2 style="color: black;"><b>₱<?= number_format($total_sales, 2) ?></b></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Orders Card -->
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
                <div class="card card-custom rounded-0 shadow h-100" style="background: #ff99ff;">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                        <div class="icon-container mb-3">
                            <i class="fa fa-times-circle bounce" style="font-size: 50px; color: red;" aria-hidden="true"></i>
                        </div>
                        <div class="text-container">
                            <h5 style="color: black; font-size: 24px; font-family: 'Courier New', monospace;font-weight: bold;">Pending Orders</h5>
                            <h2 style="color: black;"><b><?= number_format($pending_orders) ?></b></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Confirmed Orders Card -->
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
                <div class="card card-custom rounded-0 shadow h-100" style="background: #80ff80;">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                        <div class="icon-container mb-3">
                            <i class="fa fa-check-circle bounce" style="font-size: 50px; color: green;" aria-hidden="true"></i>
                        </div>
                        <div class="text-container">
                            <h5 style="color: black; font-size: 24px; font-family: 'Courier New', monospace; font-weight: bold;">Confirmed Orders</h5>
                            <h2 style="color: black;"><b><?= number_format($confirmed_orders) ?></b></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sales This Month Card -->
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
                <div class="card card-custom rounded-0 shadow h-100" style="background: #ffff99;">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                        <div class="icon-container mb-3">
                            <i class="fa fa-chart-bar bounce" style="font-size: 50px; color: orange;" aria-hidden="true"></i>
                        </div>
                        <div class="text-container">
                            <h5 style="color: black; font-size: 24px; font-family: 'Courier New', monospace; font-weight: bold;">Sales This Month</h5>
                            <h2 style="color: black;"><b>₱<?= number_format(array_sum($monthly_sales_data)) ?></b></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Categories Card -->
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
                <div class="card card-custom rounded-0 shadow h-100" style="background: #d1c7b5;">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                        <div class="icon-container mb-3">
                            <i class="fa fa-folder-open bounce" style="font-size: 50px; color: #26bf33;" aria-hidden="true"></i>
                        </div>
                        <div class="text-container">
                            <h5 style="color: black; font-size: 24px; font-family: 'Courier New', monospace; font-weight: bold;">Total Categories</h5>
                            <h2 style="color: black;"><b><?= $total_categories ?></b></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Products Card -->
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
                <div class="card card-custom rounded-0 shadow h-100" style="background: #cce5ff;">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                        <div class="icon-container mb-3">
                            <i class="fa fa-cube bounce" style="font-size: 50px; color: #0056b3;" aria-hidden="true"></i>
                        </div>
                        <div class="text-container">
                            <h5 style="color: black; font-size: 24px; font-family: 'Courier New', monospace; font-weight: bold;">Total Products</h5>
                            <h2 style="color: black;"><b><?= $total_products ?></b></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Users Card -->
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
                <div class="card card-custom rounded-0 shadow h-100" style="background: #d1ecf1;">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                        <div class="icon-container mb-3">
                            <i class="fa fa-users bounce" style="font-size: 50px; color: #0c5460;" aria-hidden="true"></i>
                        </div>
                        <div class="text-container">
                            <h5 style="color: black; font-size: 24px; font-family: 'Courier New', monospace; font-weight: bold;">Total Users</h5>
                            <h2 style="color: black;"><b><?= $total_users ?></b></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Orders Card -->
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
                <div class="card card-custom rounded-0 shadow h-100" style="background: #f8d7da;">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                        <div class="icon-container mb-3">
                            <i class="fa fa-shopping-cart bounce" style="font-size: 50px; color: #721c24;" aria-hidden="true"></i>
                        </div>
                        <div class="text-container">
                            <h5 style="color: black; font-size: 24px; font-family: 'Courier New', monospace; font-weight: bold;">Total Orders</h5>
                            <h2 style="color: black;"><b><?= $total_orders ?></b></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="row m-3">
    <!-- Pie Chart for Sales by Address -->
    <div class="col-lg-6 col-md-12 mb-3">
        <div class="card rounded-0 shadow">
            <div class="card-body">
                <h5 class="card-title">Sales by Address</h5>
                <canvas id="salesByAddressChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Monthly Sales Chart -->
    <div class="col-lg-6 col-md-12 mb-3">
        <div class="card rounded-0 shadow">
            <div class="card-body">
                <h5 class="card-title">Monthly Sales for the Last 12 Months</h5>
                <canvas id="monthlySalesChart"></canvas>
            </div>
        </div>
    </div>
</div>
<div class="row m-3">
    <div class="col-12">
        <div class="card rounded-0 shadow">
            <div class="card-body">
                <h5 class="card-title">Most Sold Product</h5>
                <div class="row mb-3">
                    <div class="col-12">
                    <h6>Product: <?= $top_products[0]['name'] ?> (<?= $top_products[0]['total_quantity_sold'] ?> quantity sold)</h6>
                     </div>
                </div>
                <canvas id="topProductsSalesChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
  // Sales by Address Chart
const salesByAddressCtx = document.getElementById('salesByAddressChart').getContext('2d');
const salesByAddressData = {
    labels: <?= json_encode(array_column($data, 'address')) ?>,
    datasets: [{
        label: 'Total Sales',
        data: <?= json_encode(array_column($data, 'total_sales')) ?>,
        backgroundColor: [
            ' #ff4dff',
            '#4d4dff',
            '#e699ff', // Red
            ' #ff4da6', // Blue
            '#FFCE56', // Yellow
            '#4BC0C0', // Teal
            '#9966FF', // Purple
            '##b84dff', // Orange
            '#E7E9ED', // Gray
            '#C9CBCF'  // Light Gray
        ],
        borderColor: 'rgba(75, 192, 192, 1)',
        borderWidth: 1
    }]
};
const salesByAddressChart = new Chart(salesByAddressCtx, {
    type: 'pie',
    data: salesByAddressData,
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Sales by Address'
            }
        }
    }
});


   // Monthly Sales Chart
const monthlySalesCtx = document.getElementById('monthlySalesChart').getContext('2d');
const monthlySalesData = {
    labels: <?= json_encode(array_keys($monthly_sales_data)) ?>,
    datasets: [{
        label: 'Monthly Sales',
        data: <?= json_encode(array_values($monthly_sales_data)) ?>,
        backgroundColor: [
            '#c266ff',  // Red
            '#64b4b4',  // Blue
            'rgba(255, 206, 86, 0.6)',  // Yellow
            'rgba(75, 192, 192, 0.6)',  // Teal
            'rgba(153, 102, 255, 0.6)', // Purple
            'rgba(255, 159, 64, 0.6)',  // Orange
            ' #ff471a',  // Red
            'rgba(54, 162, 235, 0.6)',  // Blue
            'rgba(255, 206, 86, 0.6)',  // Yellow
            'rgba(75, 192, 192, 0.6)',  // Teal
            'rgba(153, 102, 255, 0.6)', // Purple
            'rgba(255, 159, 64, 0.6)'   // Orange
        ],
        borderColor: [
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)'
        ],
        borderWidth: 1
    }]
};
const monthlySalesChart = new Chart(monthlySalesCtx, {
    type: 'bar',
    data: monthlySalesData,
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Monthly Sales for the Last 12 Months'
            }
        }
    }
});
const topProductsSalesCtx = document.getElementById('topProductsSalesChart').getContext('2d');
const monthLabels = <?= json_encode(array_keys($monthly_product_sales)) ?>;

// Prepare datasets for top products
const productDatasets = <?= json_encode(array_map(function($product) use ($monthly_product_sales) {
    $productSalesData = [];
    foreach (array_keys($monthly_product_sales) as $month) {
        $productSalesData[] = isset($monthly_product_sales[$month][$product['id']]) 
            ? $monthly_product_sales[$month][$product['id']] 
            : 0;
    }
    return [
        'label' => $product['name'],
        'data' => $productSalesData,
        'fill' => false,
        'borderColor' => sprintf('#%06X', mt_rand(0, 0xFFFFFF)),
        'tension' => 0.1
    ];
}, $top_products)) ?>;

const topProductsSalesChart = new Chart(topProductsSalesCtx, {
    type: 'line',
    data: {
        labels: monthLabels.reverse(), // Reverse to show most recent month first
        datasets: productDatasets
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Most Sold Products'
            },
            legend: {
                display: true,
                position: 'bottom',  // Place the legend under the chart
                labels: {
                    boxWidth: 20,  // Size of the color box
                    padding: 20,   // Spacing between legend items
                    font: {
                        size: 14  // Font size for legend text
                    }
                }
            },
            annotation: {
                annotations: productDatasets.map(function(dataset, index) {
                    return {
                        type: 'line',
                        borderColor: dataset.borderColor,  // Line color matches the dataset
                        borderWidth: 2,
                        label: {
                            enabled: true,
                            content: dataset.label,
                            position: 'start',  // Position of the label
                            font: {
                                size: 14
                            },
                            padding: 5
                        },
                        // Coordinates of the start and end points of the line
                        scaleID: 'y',
                        value: Math.max(...dataset.data), // Use the max value for the Y coordinate
                        endValue: 0,  // End line at Y=0 (the X-axis)
                        xMin: 0,  // Start from the left of the chart
                        xMax: monthLabels.length - 1,  // End at the last month
                        drawTime: 'afterDatasetsDraw'  // Draw after the datasets
                    };
                })
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Sales Amount (₱)'
                }
            }
        }
    }
});
</script>

</body>
</html>
