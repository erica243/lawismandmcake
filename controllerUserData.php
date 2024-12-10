<?php 

require "connection.php";

$email = "";
$errors = array();

if (isset($_POST['check-email'])) {
    $email = mysqli_real_escape_string($mysqli, $_POST['email']);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format!";
    } else {
        $check_email = "SELECT * FROM admin WHERE email=?";
        $stmt = $mysqli->prepare($check_email);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $token = bin2hex(random_bytes(16));
            $token_hash = hash("sha256", $token);
            $expiry = date("Y-m-d H:i:s", time() + 60 * 30); // 30 minutes expiry

            $update_token = "UPDATE admin SET reset_token_hash = ?, reset_token_expires_at = ? WHERE email = ?";
            $stmt = $mysqli->prepare($update_token);
            $stmt->bind_param("sss", $token_hash, $expiry, $email);
            $run_query = $stmt->execute();

            if ($run_query) {
                $subject = "Password Reset";
                $message = <<<END
                Click <a href="http://localhost/xampp/Library/new-password.php?token=$token">here</a> to reset your password.
                END;
                $sender = "From: johnchristianfariola@gmail.com";

                // Assuming $mail is already set up in mailer.php
                $mail = require __DIR__ . "/mailer.php";
                $mail->setFrom("noreply@example.com");
                $mail->addAddress($email);
                $mail->Subject = $subject;
                $mail->Body = $message;

                try {
                    if ($mail->send()) {
                        $info = "We've sent a password reset code to your email - $email";
                        $_SESSION['info'] = $info;
                        $_SESSION['email'] = $email;
                        header('location: reset-code.php');
                        exit();
                    } else {
                        $errors['otp-error'] = "Failed while sending code!";
                    }
                } catch (Exception $e) {
                    $errors['otp-error'] = "Message could not be sent. Mailer error: {$mail->ErrorInfo}";
                }
            } else {
                $errors['db-error'] = "Something went wrong!";
            }
        } else {
            $errors['email'] = "This email address does not exist!";
        }
    }
}



if (isset($_POST['change-password'])) {
    $_SESSION['info'] = "";
    $token = $_POST["token"];
    $token_hash = hash("sha256", $token);

    $mysqli = require __DIR__ . "/connection.php";

    $sql = "SELECT * FROM admin WHERE reset_token_hash = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $token_hash);
    $stmt->execute(); 
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user === null) {
        $errors['token'] = "Token not found";
    } elseif (strtotime($user["reset_token_expires_at"]) <= time()) {
        $errors['token'] = "Token has expired";
    } elseif (strlen($_POST["password"]) < 8) {
        $errors['password'] = "Password must be at least 8 characters";
    } elseif (!preg_match("/[a-z]/i", $_POST["password"])) {
        $errors['password'] = "Password must contain at least one letter";
    } elseif (!preg_match("/[0-9]/", $_POST["password"])) {
        $errors['password'] = "Password must contain at least one number";
    } elseif ($_POST["password"] !== $_POST["password_confirmation"]) {
        $errors['password'] = "Passwords must match";
    } else {
        $password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);
        $sql = "UPDATE admin SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ss", $password_hash, $user["id"]);
        if ($stmt->execute()) {
            $_SESSION['info'] = "Your password changed. Now you can login with your new password.";
            header('Location: admin/index.php');
            exit();
        } else {
            $errors['db-error'] = "Failed to change your password!";
        }
    }

    // Store errors in the session and redirect back if there are any errors
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header('Location: forgot-password.php');
        exit();
    }
}

?>