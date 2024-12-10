<?php
session_start();
include('admin/db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['login_user_id'])) {
    header("Location: index.php");
    exit;
}

// Get user info from database
$user_id = $_SESSION['login_user_id'];
$query = $conn->prepare("SELECT * FROM user_info WHERE user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$user = $query->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle form submission to update user info
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $address = $_POST['address'];
    $street = $_POST['street'];  // Added street field

    $update_query = $conn->prepare("
        UPDATE user_info 
        SET first_name = ?, last_name = ?, email = ?, mobile = ?, address = ?, street = ? 
        WHERE user_id = ?
    ");
    $update_query->bind_param("ssssssi", $first_name, $last_name, $email, $mobile, $address, $street, $user_id);
    
    if ($update_query->execute()) {
        // Update session variables
        $_SESSION['login_first_name'] = $first_name;
        $_SESSION['login_last_name'] = $last_name;
        $_SESSION['login_email'] = $email;
        $_SESSION['login_mobile'] = $mobile;
        $_SESSION['login_address'] = $address;
        $_SESSION['login_street'] = $street;  // Update session for street

        // Redirect to index.php after successful update
        header("Location: index.php?page=home");
        exit;
    } else {
        echo "<p class='text-danger'>Failed to update profile.</p>";
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Container Styling */
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }

        /* Heading */
        h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
            text-align: center;
        }

        /* Form Styling */
        form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        /* Form Group */
        .form-group {
            margin-bottom: 15px;
        }

        /* Labels */
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        /* Input Fields */
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        /* Textarea */
        textarea.form-control {
            resize: vertical;
        }

        /* Submit Button */
        .btn-primary {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            display: inline-block;
            text-align: center;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        /* Back Button */
        .btn-back {
            background-color: #6c757d;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            display: inline-block;
            margin-right: 10px;
        }

        .btn-back:hover {
            background-color: #5a6268;
        }

        /* Error Message */
        .text-danger {
            color: #dc3545;
            text-align: center;
            margin-top: 10px;
        }

        /* Media Queries for Mobile */
        @media (max-width: 600px) {
            .container {
                padding: 15px;
                margin: 10px;
            }

            h2 {
                font-size: 20px;
            }

            .form-control {
                padding: 8px;
                font-size: 14px;
            }

            .btn-primary, .btn-back {
                width: 100%;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Update Profile</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="mobile">Mobile</label>
                <input type="text" class="form-control" id="mobile" name="mobile" value="<?php echo htmlspecialchars($user['mobile']); ?>" required>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($user['address']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="street">Street</label>
                <input type="text" class="form-control" id="street" name="street" value="<?php echo htmlspecialchars($user['street']); ?>" required>
            </div>
            <button type="submit" class="btn-primary">Update Profile</button>
            <a href="javascript:history.back()" class="btn-back">Back</a>
        </form>
    </div>
</body>
</html>
