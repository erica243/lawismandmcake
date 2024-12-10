<?php
session_start(); // Start session at the beginning of the script

if (isset($_POST["login"])) {
    // Database connection (assuming $conn is your database connection variable)
    include('admin/db_connect.php');

    // Sanitize input data
    $myusername = mysqli_real_escape_string($conn, $_POST['myemail']);
    $mypassword = mysqli_real_escape_string($conn, $_POST['mypassword']);

    // Query to fetch the user details based on email
    $sql = "SELECT id, password FROM register1 WHERE email = '$myusername'";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        if ($row) {
            // Verify the password using password_verify() if you're using password hashing
            if (password_verify($mypassword, $row['password'])) {
                // Set session variable indicating user is logged in
                $_SESSION['login_user'] = $myusername;
                $_SESSION['register1_id'] = $row['id']; // Save the user's register1_id in the session

                echo '<script>
                        document.addEventListener("DOMContentLoaded", function() {
                            Swal.fire({
                                icon: "success",
                                title: "Login Successful",
                                showConfirmButton: false,
                                timer: 1500
                            }).then(function() {
                                window.location.href = "index.php";
                            });
                        });
                      </script>';
            } else {
                // Password incorrect
                echo '<script>
                        document.addEventListener("DOMContentLoaded", function() {
                            Swal.fire({
                                icon: "error",
                                title: "Login Failed",
                                text: "Username or Password is Incorrect",
                            });
                        });
                      </script>';
            }
        } else {
            // No matching user found
            echo '<script>
                    document.addEventListener("DOMContentLoaded", function() {
                        Swal.fire({
                            icon: "error",
                            title: "Login Failed",
                            text: "Username or Password is Incorrect",
                        });
                    });
                  </script>';
        }
    } else {
        // SQL query failed
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/x-icon" href="bhh.jpg">
    <title>Login - MADRIE-BH</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css?family=Roboto:300,400,500,700');

        body {
            background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url('bh.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Roboto', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-page {
            width: 360px;
            padding: 8% 0 0;
            margin: auto;
        }

        .form {
            background: white;
            max-width: 360px;
            margin: auto;
            padding: 45px;
            text-align: center;
            box-shadow: 0 0 20px 0 rgba(0, 0, 0, 0.2), 0 5px 5px 0 rgba(0, 0, 0, 0.24);
            border-radius: 10px;
        }

        .form input {
            width: 100%;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 50px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        .form button {
            width: 100%;
            padding: 15px;
            background: #f9f5f4;
            color: black;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: 0.3s;
        }

        .form button:hover {
            background: #43A047;
            color: white;
        }

        .form .message {
            margin: 15px 0 0;
            color: black;
            font-size: 12px;
        }

        .form .message a {
            color: blue;
            text-decoration: none;
        }

        .form .message a:hover {
            color: #43A047;
        }

        .input-container {
            position: relative;
            margin-bottom: 15px;
        }

        .input-container .icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
        }

        .input-container input {
            padding-left: 40px;
        }

        h2 {
            color: black;
        }
    </style>
</head>
<body>
<div class="login-page">
    <div class="form">
        <h2>Login</h2>
        <form class="login-form" action="" method="POST" onsubmit="return validateTerms();">
            <div class="input-container">
                <i class="fa fa-envelope icon"></i>
                <input type="text" name="myemail" placeholder="Email" required/>
            </div>
            <div class="input-container">
                <i class="fa fa-lock icon"></i>
                <input type="password" name="mypassword" id="mypassword" placeholder="Password" required/>
                <i class="fa fa-eye" id="togglePassword" style="cursor: pointer; position: absolute; right: 15px; top: 50%; transform: translateY(-50%);"></i>
            </div>
            <p style="text-align: left; font-size: 12px;">
                <a href="forgot_pass.php" style="color: blue;">Forgot Password?</a>
            </p>
            <!-- Terms and Conditions -->
            <div style="text-align: left; margin-bottom: 15px;">
                <input type="checkbox" id="agreeTerms" name="agreeTerms" required>
                <label for="agreeTerms" style="font-size: 12px; color: black;">
                    I agree to the <a href="terms_conditions.php" style="color: blue;">Terms and Conditions</a>.
                </label>
            </div>
            <button type="submit" name="login">Login</button>
            <p class="message">Don't have an account? <a href="register_step1.php">Sign up</a></p>
            <p class="message"><a href="index.php">WebPage</a></p>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('mypassword');

    togglePassword.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.classList.toggle('fa-eye-slash');
    });

    function validateTerms() {
        const agreeTerms = document.getElementById('agreeTerms');
        if (!agreeTerms.checked) {
            Swal.fire({
                icon: 'warning',
                title: 'Terms and Conditions',
                text: 'You must agree to the Terms and Conditions to proceed.',
            });
            return false;
        }
        return true;
    }
</script>
</body>
</html>
