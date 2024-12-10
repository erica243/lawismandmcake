<!-- verify_code.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Verify Code | M&M Cake Ordering System</title>
    <?php include('./header.php'); ?>
</head>
<body>
    <div class="container">
        <h2>Enter Verification Code</h2>
        <form method="POST" action="verify_code.php">
            <div class="form-group">
                <label for="code">Enter the 6-digit code sent to your email:</label>
                <input type="text" id="code" name="code" class="form-control" 
                       pattern="[0-9]{6}" maxlength="6" required>
            </div>
            <input type="hidden" name="username" 
                   value="<?php echo isset($_GET['username']) ? htmlspecialchars($_GET['username']) : ''; ?>">
            <button type="submit" class="btn btn-primary">Verify Code</button>
        </form>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            include('./db_connect.php');
            
            $code = $conn->real_escape_string($_POST['code']);
            $username = $conn->real_escape_string($_POST['username']);
            
            // Verify the code matches and hasn't expired
            $query = $conn->query("SELECT * FROM users 
                                 WHERE username = '$username' 
                                 AND reset_code = '$code' 
                                 AND reset_code_expiry > NOW()");
            
            if ($query->num_rows > 0) {
                // Code is valid, generate a temporary token
                $temp_token = bin2hex(random_bytes(32));
                
                // Save the token in the database
                $conn->query("UPDATE users 
                            SET temp_reset_token = '$temp_token' 
                            WHERE username = '$username'");
                
                // Redirect to reset password page
                header("Location: reset_password.php?token=" . urlencode($temp_token));
                exit();
            } else {
                echo "<div class='alert alert-danger'>Invalid or expired verification code.</div>";
            }
        }
        ?>
    </div>
</body>
</html>