<?php
require_once('db_connect.php');
session_start();

function validateResetToken($conn, $token) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_code_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc() : false;
}

function resetPassword($conn, $token, $newPassword, $resetCode) {
    // Password strength validation
    if (strlen($newPassword) < 8) {
        return ['status' => 'error', 'message' => 'Password must be at least 8 characters long.'];
    }

    // Validate password complexity (optional but recommended)
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $newPassword)) {
        return [
            'status' => 'error', 
            'message' => 'Password must include uppercase, lowercase, number, and special character.'
        ];
    }

    // Validate reset token and code
    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_code = ? AND reset_code_expiry > NOW()");
    $stmt->bind_param("ss", $token, $resetCode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return ['status' => 'error', 'message' => 'Invalid or expired reset token/code.'];
    }

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    // Update password and clear reset fields
    $updateStmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_code = NULL, reset_code_expiry = NULL WHERE reset_token = ?");
    $updateStmt->bind_param("ss", $hashedPassword, $token);
    
    if ($updateStmt->execute()) {
        return ['status' => 'success', 'message' => 'Password reset successfully.'];
    } else {
        return ['status' => 'error', 'message' => 'Unable to reset password. Please try again.'];
    }
}

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $resetCode = $_POST['reset_code'] ?? '';

    // Validate passwords match
    if ($newPassword !== $confirmPassword) {
        echo json_encode(['status' => 'error', 'message' => 'Passwords do not match.']);
        exit;
    }

    $result = resetPassword($conn, $token, $newPassword, $resetCode);
    echo json_encode($result);
    exit;
}

// Display reset password form
$token = $_GET['token'] ?? '';
$user = validateResetToken($conn, $token);

if (!$user) {
    die("Invalid or expired reset token.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password | M&M Cake Ordering System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .reset-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <h2>Reset Password</h2>
        <div id="message-container"></div>
        <form id="reset-password-form">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            
            <div class="form-group">
                <label>Reset Code</label>
                <input type="text" name="reset_code" class="form-control" required 
                       placeholder="Enter 6-digit reset code">
            </div>
            
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" class="form-control" required 
                       placeholder="Min 8 chars, include uppercase, lowercase, number, special char">
            </div>
            
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" class="form-control" required 
                       placeholder="Repeat new password">
            </div>
            
            <button type="submit" class="btn">Reset Password</button>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#reset-password-form').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var messageContainer = $('#message-container');
            
            messageContainer.empty();
            
            $.ajax({
                url: '',  // Same page
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    try {
                        var result = JSON.parse(response);
                        if (result.status === 'success') {
                            messageContainer.html('<div class="alert alert-success">' + result.message + '</div>');
                            setTimeout(function() {
                                window.location.href = 'login.php';
                            }, 2000);
                        } else {
                            messageContainer.html('<div class="alert alert-danger">' + result.message + '</div>');
                        }
                    } catch (e) {
                        messageContainer.html('<div class="alert alert-danger">Error processing response</div>');
                    }
                },
                error: function() {
                    messageContainer.html('<div class="alert alert-danger">Network error. Please try again.</div>');
                }
            });
        });
    });
    </script>
</body>
</html>