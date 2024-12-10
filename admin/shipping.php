<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Amounts</title>
    <style>
        /* General Styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Form Styles */
        form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"], input[type="number"] {
            padding: 10px;
            margin: 10px 0;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            width: 100%;
            padding: 10px;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f7f7f7;
            font-weight: bold;
        }

        .btn {
            cursor: pointer;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
        }

        .btn-edit {
            background-color: #007bff;
            color: white;
        }

        .btn-delete {
            background-color: #d33;
            color: white;
        }

        .success-message {
            text-align: center;
            color: green;
            margin: 20px 0;
            font-weight: bold;
        }

        .error-message {
            text-align: center;
            color: red;
            margin: 20px 0;
            font-weight: bold;
        }

        /* Search Bar Styling */
        .search-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .search-container input {
            width: 80%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 50px;
            font-size: 16px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.2s ease;
        }

        .search-container input:focus {
            outline: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            input[type="submit"] {
                font-size: 14px;
            }

            table {
                font-size: 12px;
            }

            th, td {
                padding: 6px;
            }

            .search-container input {
                width: 90%;
            }
        }

        @media (max-width: 480px) {
            input[type="submit"] {
                font-size: 12px;
                padding: 8px;
            }

            table {
                font-size: 10px;
            }

            th, td {
                padding: 4px;
            }

            .search-container input {
                width: 100%;
            }
        }
    </style>

    <!-- DataTables CSS and JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container">
        <h1>Set Shipping Amounts</h1>

        <!-- Form -->
        <form id="shippingForm" action="" method="POST">
            <label for="address">Barangay:</label>
            <input type="text" id="address" name="address" placeholder="Enter Barangay" required>

            <label for="municipality">Municipality:</label>
            <input type="text" id="municipality" name="municipality" placeholder="Enter Municipality" required>

            <label for="shipping_amount">Shipping Amount:</label>
            <input type="number" id="shipping_amount" name="shipping_amount" placeholder="Enter Amount" required>

            <input type="submit" value="Save Shipping Amount">
        </form>
        <br>
        <!-- Messages -->
        <div id="messages">
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>
        </div>

        <!-- DataTable -->
        <table id="shippingTable" class="display">
            <thead>
                <tr>
                    <th>Address</th>
                    <th>Municipality</th>
                    <th>Shipping Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include 'db_connect.php';

                // Handle form submissions
                $error_message = $success_message = '';
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if (isset($_POST['delete_id'])) {
                        // Handle delete request
                        $delete_id = intval($_POST['delete_id']);
                        $delete_query = "DELETE FROM shipping_info WHERE id = $delete_id";
                        if ($conn->query($delete_query)) {
                            $success_message = "Shipping record deleted successfully.";
                        } else {
                            $error_message = "Failed to delete record: " . $conn->error;
                        }
                    } else {
                        // Handle add/update request
                        $address = $conn->real_escape_string($_POST['address']);
                        $municipality = $conn->real_escape_string($_POST['municipality']);
                        $amount = floatval($_POST['shipping_amount']);
                        $insert_query = "INSERT INTO shipping_info (address, municipality, shipping_amount)
                                         VALUES ('$address', '$municipality', $amount)
                                         ON DUPLICATE KEY UPDATE municipality = '$municipality', shipping_amount = $amount";
                        if ($conn->query($insert_query)) {
                            $success_message = "Shipping record saved successfully.";
                        } else {
                            $error_message = "Failed to save record: " . $conn->error;
                        }
                    }
                }

                // Fetch all records
                $select_query = "SELECT * FROM shipping_info";
                $result = $conn->query($select_query);
                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                    <td><?php echo htmlspecialchars($row['municipality']); ?></td>
                    <td><?php echo htmlspecialchars($row['shipping_amount']); ?></td>
                    <td>
                        <button class="btn btn-edit" onclick="populateForm('<?php echo htmlspecialchars($row['address']); ?>', '<?php echo htmlspecialchars($row['municipality']); ?>', '<?php echo htmlspecialchars($row['shipping_amount']); ?>')">Edit</button>
                        <form action="" method="POST" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                            <button type="button" class="btn btn-delete" onclick="confirmDelete(this.form)">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Scripts -->
    <script>
        // Initialize DataTable
        $(document).ready(function () {
            $('#shippingTable').DataTable({
                responsive: true,
                autoWidth: false,
            });
        });

        // Populate form with existing data
        function populateForm(address, municipality, shipping_amount) {
            document.getElementById('address').value = address;
            document.getElementById('municipality').value = municipality;
            document.getElementById('shipping_amount').value = shipping_amount;
        }

        // Confirm delete
        function confirmDelete(form) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will permanently delete the shipping amount!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        // Confirm form submission
        document.getElementById('shippingForm').onsubmit = function (event) {
            event.preventDefault();
            Swal.fire({
                title: 'Confirm Submission',
                text: "Are you sure you want to save the shipping amount?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, save it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    event.target.submit();
                }
            });
        };
    </script>
</body>
</html>
