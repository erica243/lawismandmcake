
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        .search-bar {
            margin-bottom: 20px;
        }
        .payment-proof img {
            max-width: 50px; /* Smaller thumbnail for table */
            height: auto;
            border: 1px solid #ddd;
            padding: 5px;
            cursor: pointer; /* Change cursor to pointer */
        }
        @media (max-width: 768px) {
            .table-responsive {
                overflow-x: auto;
            }
        }
        /* Style for the full-screen modal */
        .img-preview {
            max-width: 100%;
            max-height: 80vh;
            display: block;
            margin: auto;
        }
        .cancelled-order-message {
            color: red;
            font-style: italic;
        }  @media (max-width: 768px) {
            .table-responsive-stack tr {
                display: -webkit-box;
                display: -ms-flexbox;
                display: flex;
                -webkit-box-orient: vertical;
                -webkit-box-direction: normal;
                    -ms-flex-direction: column;
                        flex-direction: column;
                margin-bottom: 1rem;
                border: 1px solid #ddd;
            }
            
            .table-responsive-stack td {
                display: block;
                text-align: right;
                border-bottom: 1px solid #ddd;
                padding: 0.6rem;
            }
            
            .table-responsive-stack td:before {
                content: attr(data-label);
                float: left;
                font-weight: bold;
                text-transform: uppercase;
            }
        }

        /* Modern rounded search bar */
        .search-bar {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            border-radius: 50px;
            background-color: #f0f0f0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 5px 15px;
        }

        .search-bar input {
            border: none;
            outline: none;
            border-radius: 50px;
            padding: 10px 20px;
            font-size: 1rem;
            flex: 1;
            background-color: #fff;
            color: #333;
            transition: all 0.3s ease;
        }

        .search-bar input:focus {
            background-color: #e9ecef;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.25);
        }

        .search-bar button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            margin-left: 10px;
            border-radius: 50px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .search-bar button:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        .search-bar button i {
            font-size: 1.2rem;
        }

        @media (max-width: 768px) {
            .search-bar {
                padding: 5px;
            }
            .search-bar input {
                padding: 8px 15px;
            }
            .search-bar button {
                padding: 8px 15px;
            }
        }
        .payment-proof img {
            max-width: 50px;
            height: auto;
            border: 1px solid #ddd;
            padding: 5px;
            cursor: pointer;
        }
        .img-preview {
            max-width: 100%;
            max-height: 80vh;
            display: block;
            margin: auto;
        }
        .cancelled-order-message {
            color: red;
            font-style: italic;
        }

        /* Additional mobile-friendly adjustments */
        body {
            font-size: 14px;
        }
        .container-fluid {
            padding: 10px;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="card">
    <div class="card-header" style="background-color:  #bbc7dd; color: white;">
            <div class="search-bar">
                <input type="text" id="searchInput" class="form-control" placeholder="Search orders...">
                <button>
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-responsive-stack">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Order Number</th>
                            <th>Customer Name</th>
                            <th>Address</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Order Date</th>
                            <th>Delivery Method</th>
                            <th>Pick-up Date</th>
                            <th>Pick-up Time</th>
                            <th>Delivery Status</th>
                            <th>Proof of Payment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="orderTableBody">
                        <?php 
                        include 'db_connect.php';
                        $qry = $conn->query("SELECT * FROM orders");
                        $i = 1;
                        while($row = $qry->fetch_assoc()): 
                        ?>
                        <tr>
                            <td data-label="#"><?php echo $i++ ?></td>
                            <td data-label="Order Number"><?php echo $row['order_number'] ?></td>
                            <td data-label="Customer Name"><?php echo $row['name'] ?></td>
                            <td data-label="Address"><?php echo $row['address'] ?></td>
                            <td data-label="Email"><?php echo $row['email'] ?></td>
                            <td data-label="Mobile"><?php echo $row['mobile'] ?></td>
                            <td data-label="Order Date"><?php echo date('m-d-Y', strtotime($row['order_date'])); ?></td>
                            <td data-label="Delivery Method"><?php echo $row['delivery_method'] ?></td>
                            <td data-label="Pick-up Date"><?php echo !empty($row['pickup_date']) ? date('m-d-Y', strtotime($row['pickup_date'])) : 'N/A'; ?></td>
                            <td data-label="Pick-up Time"><?php echo !empty($row['pickup_time']) ? $row['pickup_time'] : 'N/A'; ?></td>
                            <td data-label="Delivery Status" class="text-center">
                                <?php
                                switch ($row['delivery_status']) {
                                    case 'pending':
                                        echo '<span class="badge badge-warning">Pending</span>';
                                        break;
                                    case 'confirmed':
                                        echo '<span class="badge badge-info">Confirmed</span>';
                                        break;
                                    case 'preparing':
                                        echo '<span class="badge badge-primary">Preparing</span>';
                                        break;
                                    case 'ready':
                                        echo '<span class="badge badge-success">Ready for Delivery</span>';
                                        break;
                                    case 'in_transit':
                                        echo '<span class="badge badge-success">In Transit</span>';
                                        break;
                                    case 'delivered':
                                        echo '<span class="badge badge-dark">Delivered</span>';
                                        break;
                                    case 'cancelled':
                                        echo '<span class="badge badge-danger">Cancelled</span>';
                                        break;
                                    default:
                                        echo '<span class="badge badge-secondary">Pending</span>';
                                        break;
                                }
                                ?>
                            </td>
                            <td data-label="Proof of Payment" class="text-center payment-proof">
                                <?php if (!empty($row['payment_proof'])): ?>
                                    <img src="<?php echo $row['payment_proof']; ?>" alt="Proof of Payment" onclick="viewImage('<?php echo $row['payment_proof']; ?>')">
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td data-label="Actions">
                                <?php if ($row['delivery_status'] === 'cancelled'): ?>
                                    <div class="cancelled-order-message">Order Cancelled</div>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-primary view_order" data-id="<?php echo $row['id'] ?>">View</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Order Details Modal -->
<div class="modal fade" id="uniModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Order Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalContent">
                <!-- Order details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Proof of Payment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="fullImage" class="img-preview" src="" alt="Proof of Payment">
            </div>
        </div>
    </div>
</div>

<script>
    $(document).on('click', '.delete_order', function() {
    const orderId = $(this).attr('data-id');
    if (confirm('Are you sure you want to delete this order?')) {
        $.ajax({
            url: 'ajax.php', // Point to your AJAX handler
            type: 'POST',
            data: { action: 'delete_order', id: orderId },
            success: function(response) {
                if (response == 1) {
                    alert('Order deleted successfully.');
                    location.reload(); // Reload the page to see changes
                } else {
                    alert('Failed to delete order. Please try again.');
                }
            },
            error: function() {
                alert('An error occurred while deleting the order.');
            }
        });
    }
});

    $(document).ready(function(){
        $('.view_order').click(function(){
            uni_modal('Order', 'view_order.php?id=' + $(this).attr('data-id'));
        });

        $("#searchInput").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#orderTableBody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
    });

    function uni_modal(title, url) {
        $('#uniModal .modal-title').html(title);
        $('#modalContent').load(url, function() {
            $('#uniModal').modal('show');
        });
    }

    function viewImage(imageUrl) {
        $('#fullImage').attr('src', imageUrl);
        $('#imageModal').modal('show');
    }
    
</script>

</body>
</html>
