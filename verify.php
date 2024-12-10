<?php session_start(); ?>
<?php
include('admin/db_connect.php');

// Check if the user is logged in and has an email stored in session
if (!isset($_SESSION['email'])) {
    header("Location: signup.php");
    exit();
}

$email = $_SESSION['email'];
$otpError = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otpInput = htmlspecialchars(trim($_POST['otp']));

    // Check OTP in the database
    $stmt = $conn->prepare("SELECT * FROM user_info WHERE email = ? AND otp = ?");
    $stmt->bind_param("si", $email, $otpInput);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // OTP is valid
        $stmt = $conn->prepare("UPDATE user_info SET otp = NULL WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        // Redirect to a success page or the main site
        header("Location: success.php"); // Change this to your desired success page
        exit();
    } else {
        $otpError = 'Invalid OTP. Please try again.';
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <!-- Include your CSS and other headers -->
</head>
<body>
    <div class="container">
        <form action="" method="POST">
            <div class="form-group">
                <label for="otp">Enter OTP</label>
                <input type="text" name="otp" class="form-control" required>
            </div>
            <?php if ($otpError): ?>
                <div class="alert alert-danger"><?php echo $otpError; ?></div>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">Verify OTP</button>
        </form>
    </div>
</body>
</html>
