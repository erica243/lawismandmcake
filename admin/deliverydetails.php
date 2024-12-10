<?php
session_start(); // Start session to access session variables

// Include your database connection file
include 'db_connect.php';

// Initialize variables
$delivery_name = "";
$delivery_number = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and assign the input values
    $delivery_name = trim($_POST['delivery_name']);
    $delivery_number = trim($_POST['delivery_number']);

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("INSERT INTO delivery_details (delivery_name, delivery_number) VALUES (?, ?)");
    $stmt->bind_param("ss", $delivery_name, $delivery_number);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Delivery details added successfully!";
        // Clear the input fields after successful submission
        $delivery_name = "";
        $delivery_number = "";
    } else {
        $_SESSION['error'] = "Error adding delivery details.";
    }
}

// Fetch existing delivery details if needed
$delivery_details = [];
$result = $conn->query("SELECT * FROM delivery_details");

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $delivery_details[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Details</title>
    <link rel="stylesheet" href="path/to/your/css/style.css"> <!-- Add your CSS path -->
    <!-- SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
</head>
<body>
    <h1>Delivery Details</h1>

    <!-- Display success or error messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <script>
            swal("Success!", "<?php echo $_SESSION['success']; unset($_SESSION['success']); ?>", "success");
        </script>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <script>
            swal("Error!", "<?php echo $_SESSION['error']; unset($_SESSION['error']); ?>", "error");
        </script>
    <?php endif; ?>

    <!-- Delivery Details Form -->
    <form action="deliverydetails.php" method="POST">
        <input type="text" name="delivery_name" placeholder="Delivery Name" value="<?php echo htmlspecialchars($delivery_name); ?>" required>
        <input type="text" name="delivery_number" placeholder="Delivery Number" value="<?php echo htmlspecialchars($delivery_number); ?>" required>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>

    <!-- Display existing delivery details -->
    <h2>Existing Delivery Details</h2>
    <table>
        <thead>
            <tr>
                <th>Delivery Name</th>
                <th>Delivery Number</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($delivery_details)): ?>
                <tr>
                    <td colspan="2">No delivery details found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($delivery_details as $detail): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($detail['delivery_name']); ?></td>
                        <td><?php echo htmlspecialchars($detail['delivery_number']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- SweetAlert JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
</body>
</html>
