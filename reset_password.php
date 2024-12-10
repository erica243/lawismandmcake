<?php
require_once('admin/db_connect.php');
session_start();

function validateResetToken($conn, $token) {
    // Check if the token is valid and not expired
    $stmt = $conn->prepare("SELECT * FROM user_info WHERE token = ? AND token_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc() : false;
}

function resetPassword($conn, $token, $newPassword, $confirmPassword, $resetCode) {
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

    // Validate reset token and OTP code
    $stmt = $conn->prepare("SELECT * FROM user_info WHERE token = ? AND otp = ? AND otp_expiry > NOW()");
    $stmt->bind_param("si", $token, $resetCode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return ['status' => 'error', 'message' => 'Invalid or expired reset token/OTP.'];
    }

    // Validate that the passwords match
    if ($newPassword !== $confirmPassword) {
        return ['status' => 'error', 'message' => 'Passwords do not match.'];
    }

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    // Update password and clear reset fields (token, otp, etc.)
    $updateStmt = $conn->prepare("UPDATE user_info SET password = ?, token = NULL, otp = NULL, otp_expiry = NULL WHERE token = ?");
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

    $result = resetPassword($conn, $token, $newPassword, $confirmPassword, $resetCode);
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
        .input-group {
            display: flex;
            align-items: center;
        }
        .input-group-text {
            cursor: pointer;
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
                <label>Reset Code (OTP)</label>
                <input type="text" name="reset_code" class="form-control" required 
                       placeholder="Enter 6-digit OTP">
            </div>
            
            <div class="form-group">
                <label>New Password</label>
                <div class="input-group">
                    <input type="password" name="new_password" class="form-control" required 
                           placeholder="Min 8 chars, include uppercase, lowercase, number, special char" id="new_password">
                    <div class="input-group-append">
                        <span class="input-group-text" id="toggle_new_password">
                            <i class="fa fa-eye-slash"></i>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label>Confirm New Password</label>
                <div class="input-group">
                    <input type="password" name="confirm_password" class="form-control" required 
                           placeholder="Repeat new password" id="confirm_password">
                    <div class="input-group-append">
                        <span class="input-group-text" id="toggle_confirm_password">
                            <i class="fa fa-eye-slash"></i>
                        </span>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn">Reset Password</button>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
    <script>
    $(document).ready(function() {
        // Toggle visibility for new password
        $('#toggle_new_password').on('click', function() {
            var passwordField = $('#new_password');
            var icon = $(this).find('i');
            if (passwordField.attr('type') === 'password') {
                passwordField.attr('type', 'text');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            } else {
                passwordField.attr('type', 'password');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            }
        });

        // Toggle visibility for confirm password
        $('#toggle_confirm_password').on('click', function() {
            var confirmPasswordField = $('#confirm_password');
            var icon = $(this).find('i');
            if (confirmPasswordField.attr('type') === 'password') {
                confirmPasswordField.attr('type', 'text');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            } else {
                confirmPasswordField.attr('type', 'password');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            }
        });

        // Handle form submission
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
                                window.location.href = 'index.php';
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
