<?php
//admin_class.php
session_start();

Class Action {
    private $db;

    public function __construct() {
        ob_start();
        include 'db_connect.php';
        $this->db = $conn;
    }

    function __destruct() {
        $this->db->close();
        ob_end_flush();
    }

    function login() {
        extract($_POST);
        $qry = $this->db->query("SELECT * FROM `users` WHERE username = '".$username."'");
        if($qry->num_rows > 0) {
            $result = $qry->fetch_array();
            $is_verified = password_verify($password, $result['password']);
            if($is_verified) {
                foreach ($result as $key => $value) {
                    if($key != 'password' && !is_numeric($key))
                        $_SESSION['login_'.$key] = $value;
                }
                return 1;
            }
        }
        return 3;
    }

    function login2() {
        extract($_POST);
        
        // Initialize session variables for tracking login attempts
        if (!isset($_SESSION['failed_attempts'])) {
            $_SESSION['failed_attempts'] = 0;
            $_SESSION['last_failed_time'] = time();
        }
    
        // Set max attempts and lockout time (in seconds)
        $max_attempts = 3;
        $lockout_time = 10; // 15 minutes
    
        // If the max attempts are reached, check if lockout time has passed
        if ($_SESSION['failed_attempts'] >= $max_attempts) {
            // If lockout time has not passed, block login
            if (time() - $_SESSION['last_failed_time'] < $lockout_time) {
                $remaining_lockout = $lockout_time - (time() - $_SESSION['last_failed_time']);
                return json_encode(['status' => 'error', 'message' => 'Too many failed attempts. Please try again in ' . ceil($remaining_lockout / 60) . ' minutes.']);
            } else {
                // Reset failed attempts after lockout time
                $_SESSION['failed_attempts'] = 0;
            }
        }
    
        // Query the user by email
        $qry = $this->db->query("SELECT * FROM user_info WHERE email = '".$email."'");
        
        if ($qry->num_rows > 0) {
            $result = $qry->fetch_array();
            
            // Check if the account is active
            if ($result['active'] == 0) {
                return json_encode(['status' => 'error', 'message' => 'Your account is not activated. Please verify your email.']);
            }
    
            // Verify password
            $is_verified = password_verify($password, $result['password']);
            
            if ($is_verified) {
                // Reset failed attempts on successful login
                $_SESSION['failed_attempts'] = 0;
    
                // Set session variables for the logged-in user
                foreach ($result as $key => $value) {
                    if ($key != 'password' && !is_numeric($key)) {
                        $_SESSION['login_'.$key] = $value;
                    }
                }
    
                // Update the cart with the logged-in user ID
                $ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
                $this->db->query("UPDATE cart SET user_id = '".$_SESSION['login_user_id']."' WHERE client_ip = '$ip'");
    
                return json_encode(['status' => 'success']);
            }
        }
    
        // Failed login attempt: Increment the counter and record the time of failure
        $_SESSION['failed_attempts']++;
        $_SESSION['last_failed_time'] = time();
    
        return json_encode(['status' => 'error', 'message' => 'Email or password is incorrect.']);
    }
    function logout() {
        session_destroy();
        foreach ($_SESSION as $key => $value) {
            unset($_SESSION[$key]);
        }
        header("location:login.php");
    }

    function logout2() {
        session_destroy();
        foreach ($_SESSION as $key => $value) {
            unset($_SESSION[$key]);
        }
        header("location:../index.php");
    }

    function save_user() {
        extract($_POST);
        $password = password_hash($password, PASSWORD_DEFAULT);
        $data = " `name` = '$name' ";
        $data .= ", `username` = '$username' ";
        $data .= ", `password` = '$password' ";
        $data .= ", `type` = '$type' ";
        if(empty($id)) {
            $save = $this->db->query("INSERT INTO users SET ".$data);
        } else {
            $save = $this->db->query("UPDATE users SET ".$data." WHERE id = ".$id);
        }
        if($save) {
            return 1;
        }
    }

   function signup() {
    extract($_POST); // Extract POST variables, including 'street'
    $password = password_hash($password, PASSWORD_DEFAULT); // Hash the password
    $data = " first_name = '$first_name' ";
    $data .= ", last_name = '$last_name' ";
    $data .= ", mobile = '$mobile' ";
    $data .= ", address = '$address' ";
    $data .= ", street = '$street' "; // Add the street to the query
    $data .= ", email = '$email' ";
    $data .= ", password = '$password' ";

    // Check if the email is already in use
    $chk = $this->db->query("SELECT * FROM user_info WHERE email = '$email'")->num_rows;
    if($chk > 0) {
        return 2; // Email already exists
        exit;
    }

    // Insert the new user record
    $save = $this->db->query("INSERT INTO user_info SET ".$data);
    if($save) {
        $login = $this->login2(); // Optional: Automatically log in the user after signup
        return 1; // Signup successful
    }
}

    function save_settings() {
        extract($_POST);
        $data = " name = '$name' ";
        $data .= ", email = '$email' ";
        $data .= ", contact = '$contact' ";
        $data .= ", about_content = '".htmlentities(str_replace("'","&#x2019;",$about))."' ";
        if($_FILES['img']['tmp_name'] != '') {
            $fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
            $move = move_uploaded_file($_FILES['img']['tmp_name'], '../assets/img/'. $fname);
            $data .= ", cover_img = '$fname' ";
        }
        $chk = $this->db->query("SELECT * FROM system_settings");
        if($chk->num_rows > 0) {
            $save = $this->db->query("UPDATE system_settings SET ".$data." WHERE id =".$chk->fetch_array()['id']);
        } else {
            $save = $this->db->query("INSERT INTO system_settings SET ".$data);
        }
        if($save) {
            $query = $this->db->query("SELECT * FROM system_settings LIMIT 1")->fetch_array();
            foreach ($query as $key => $value) {
                if(!is_numeric($key))
                    $_SESSION['setting_'.$key] = $value;
            }
            return 1;
        }
    }

    function save_category() {
        extract($_POST);
        $data = " name = '$name' ";
        if(empty($id)) {
            $save = $this->db->query("INSERT INTO category_list SET ".$data);
        } else {
            $save = $this->db->query("UPDATE category_list SET ".$data." WHERE id=".$id);
        }
        if($save)
            return 1;
    }

    function delete_category() {
        extract($_POST);
        $delete = $this->db->query("DELETE FROM category_list WHERE id = ".$id);
        if($delete)
            return 1;
    }
    public function save_menu() {
        // Check if database connection is initialized
        if (!isset($this->db)) {
            return "Database connection error.";
        }
    
        // Extract form data
        extract($_POST);
    
        // Prepare data to be updated/inserted with proper escaping
        $data = "name = '" . $this->db->real_escape_string($name) . "'";
        $data .= ", price = '" . $this->db->real_escape_string($price) . "'";
        $data .= ", category_id = '" . $this->db->real_escape_string($category_id) . "'";
        $data .= ", description = '" . $this->db->real_escape_string($description) . "'";
        $data .= ", size = '" . $this->db->real_escape_string($size) . "'"; // Added size
        $data .= ", size_unit = '" . $this->db->real_escape_string($size_unit) . "'"; // Handle size unit
        $data .= ", stock = '" . intval($stock) . "'"; // Add stock handling
        $data .= ", status = '" . ($status == 'Available' ? 'Available' : 'Unavailable') . "'"; // Handle availability
    
        // Handle file upload
        if (!empty($_FILES['img']['tmp_name'])) {
            $fileName = strtotime(date('m-d-Y H:i')) . '_' . $_FILES['img']['name'];
            $uploadDir = '../assets/img/';
            $uploadFile = $uploadDir . $fileName;
    
            // Move uploaded file
            if (move_uploaded_file($_FILES['img']['tmp_name'], $uploadFile)) {
                $data .= ", img_path = '" . $this->db->real_escape_string($fileName) . "'";
            } else {
                // Handle file upload error
                return "Failed to upload image.";
            }
        }
    
        // Perform insert or update
        if (empty($id)) {
            $query = "INSERT INTO product_list SET " . $data;
        } else {
            $query = "UPDATE product_list SET " . $data . " WHERE id=" . intval($id);
        }
    
        $save = $this->db->query($query);
    
        // Check for SQL errors
        if (!$save) {
            return "Database error: " . $this->db->error;
        }
    
        return 1;
    }
    

    function delete_menu() {
        extract($_POST);
        $delete = $this->db->query("DELETE FROM product_list WHERE id = ".$id);
        if($delete)
            return 1;
    }

    function delete_cart() {
        // Extract the cart ID from the URL parameter
        extract($_GET);
        
        // Get the product details (quantity and stock) from the cart
        $product_query = $this->db->query("SELECT c.qty, p.stock, c.product_id FROM cart c INNER JOIN product_list p ON c.product_id = p.id WHERE c.id = $id");
        $product = $product_query->fetch_assoc();
        
        if ($product) {
            // Get the product quantity in the cart and current stock
            $product_qty = $product['qty'];
            $current_stock = $product['stock'];
            $product_id = $product['product_id'];
    
            // Calculate the new stock after removing the item
            $new_stock = $current_stock + $product_qty;
    
            // Update the stock of the product in the product list
            $update_stock_query = $this->db->query("UPDATE product_list SET stock = $new_stock WHERE id = $product_id");
    
            // If stock update is successful, proceed with deletion
            if ($update_stock_query) {
                // Delete the item from the cart
                $delete_query = $this->db->query("DELETE FROM cart WHERE id = $id");
    
                if ($delete_query) {
                    // Redirect back to the previous page if deletion is successful
                    header('location:' . $_SERVER['HTTP_REFERER']);
                    exit;
                } else {
                    // Handle error if deletion fails
                    echo "Error: Unable to delete the item from the cart.";
                }
            } else {
                // Handle error if stock update fails
                echo "Error: Unable to update the stock.";
            }
        } else {
            // Handle error if the cart item is not found
            echo "Error: Cart item not found.";
        }
    }
    

    function add_to_cart() {
        extract($_POST);
        
        // Start transaction
        $this->db->begin_transaction();
        
        try {
            // Check current stock
            $stock_check = $this->db->query("SELECT stock FROM product_list WHERE id = $pid FOR UPDATE");
            if (!$stock_check) {
                throw new Exception("Error checking stock");
            }
            
            $current_stock = $stock_check->fetch_assoc()['stock'];
            $qty = isset($qty) ? intval($qty) : 1;
            
            // Validate stock availability
            if ($current_stock < $qty) {
                throw new Exception("Not enough stock available. Current stock: $current_stock");
            }
            
            // Prepare cart data
            $data = " product_id = $pid ";    
            $data .= ", qty = $qty ";    
            
            if(isset($_SESSION['login_user_id'])) {
                $data .= ", user_id = '".$_SESSION['login_user_id']."' ";    
            } else {
                $ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
                $data .= ", client_ip = '".$ip."' ";    
            }
            
            // Update stock
            $new_stock = $current_stock - $qty;
            $update_stock = $this->db->query("UPDATE product_list SET stock = $new_stock WHERE id = $pid");
            if (!$update_stock) {
                throw new Exception("Error updating stock");
            }
            
            // Add to cart
            $save = $this->db->query("INSERT INTO cart SET ".$data);
            if (!$save) {
                throw new Exception("Error adding to cart");
            }
            
            // Commit transaction
            $this->db->commit();
            return 1;
            
        } catch (Exception $e) {
            // Rollback on error
            $this->db->rollback();
            return $e->getMessage();
        }
    }

    public function get_cart_count() {
        extract($_POST);
        
        // Check if the user is logged in
        if (isset($_SESSION['login_user_id'])) {
            $where = " WHERE user_id = '" . $_SESSION['login_user_id'] . "' ";
        } else {
            // Fall back to client IP
            $ip = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
            $where = " WHERE client_ip = '$ip' ";
        }
    
        // Execute query to get the cart count
        $result = $this->db->query("SELECT SUM(qty) AS cart FROM cart $where");
        
        // Fetch result if available, otherwise return 0
        if ($result && $result->num_rows > 0) {
            $cart = $result->fetch_array()['cart'];
            return (int) ($cart ?? 0);  // Ensure integer output, default to 0
        } else {
            return 0;
        }
    }
    

   
    function update_cart_qty() {
        extract($_POST);
        
        // Start transaction
        $this->db->begin_transaction();
        
        try {
            // Get current cart item details
            $cart_query = $this->db->query("SELECT product_id, qty FROM cart WHERE id = $id");
            if (!$cart_query) {
                throw new Exception("Error fetching cart details");
            }
            
            $cart_item = $cart_query->fetch_assoc();
            $old_qty = $cart_item['qty'];
            $product_id = $cart_item['product_id'];
            
            // Get current stock
            $stock_query = $this->db->query("SELECT stock FROM product_list WHERE id = $product_id FOR UPDATE");
            if (!$stock_query) {
                throw new Exception("Error checking stock");
            }
            
            $current_stock = $stock_query->fetch_assoc()['stock'];
            $qty_difference = $qty - $old_qty;
            
            // Check if update is possible
            if ($qty_difference > 0 && $current_stock < $qty_difference) {
                throw new Exception("Not enough stock available. Current stock: $current_stock");
            }
            
            // Update stock
            $new_stock = $current_stock - $qty_difference;
            $update_stock = $this->db->query("UPDATE product_list SET stock = $new_stock WHERE id = $product_id");
            if (!$update_stock) {
                throw new Exception("Error updating stock");
            }
            
            // Update cart quantity
            $update_cart = $this->db->query("UPDATE cart SET qty = $qty WHERE id = $id");
            if (!$update_cart) {
                throw new Exception("Error updating cart");
            }
            
            // Commit transaction
            $this->db->commit();
            return 1;
            
        } catch (Exception $e) {
            // Rollback on error
            $this->db->rollback();
            return $e->getMessage();
        }
    }
    function save_order() {
        // Log incoming data
        error_log("Order details: " . json_encode($_POST));
    
        // Validate payment method
        $valid_payment_methods = ['cash', 'gcash']; // Add other valid methods here
        $payment_method = $this->db->real_escape_string($_POST['payment_method']);
        if (!in_array($payment_method, $valid_payment_methods)) {
            error_log("Invalid payment method: " . $payment_method);
            return "Error: Invalid payment method";
        }
    
        // Use mysqli_real_escape_string to escape all inputs
        $user_id = $_SESSION['login_user_id']; // Fetch logged-in user's ID
        $order_number = rand(1000, 9999); // Example random order number
        $order_date = date('Y-m-d H:i:s'); // Current date and time
        $delivery_method = isset($_POST['order_type']) ? $this->db->real_escape_string($_POST['order_type']) : 'Delivery'; // Default to delivery
        $first_name = $this->db->real_escape_string($_POST['first_name']);
        $last_name = $this->db->real_escape_string($_POST['last_name']);
        $address = $this->db->real_escape_string($_POST['address']);
        $mobile = $this->db->real_escape_string($_POST['mobile']);
        $email = $this->db->real_escape_string($_POST['email']);
        $transaction_id = isset($_POST['transaction_id']) ? $this->db->real_escape_string($_POST['transaction_id']) : '';
        $ref_no = isset($_POST['ref_no']) ? $this->db->real_escape_string($_POST['ref_no']) : ''; // Capture the reference number
    
        // Log payment method for debugging
        error_log("Payment Method: " . $payment_method);
    
        // Handle pickup date and time
        $pickup_date = isset($_POST['pickup_date']) && !empty($_POST['pickup_date']) ? $this->db->real_escape_string($_POST['pickup_date']) : 'N/A';
        $pickup_time = isset($_POST['pickup_time']) && !empty($_POST['pickup_time']) ? $this->db->real_escape_string($_POST['pickup_time']) : 'N/A';
    
        $payment_proof_path = '';
        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/payment_proof/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_name = time() . '_' . basename($_FILES['payment_proof']['name']);
            $target_file = $upload_dir . $file_name;
            // Move the uploaded file
            if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $target_file)) {
                $payment_proof_path = $target_file;
            } else {
                return "Error: Could not upload payment proof.";
            }
        }
    
        $sql = "INSERT INTO orders (user_id, order_number, order_date, delivery_method, name, address, mobile, email, payment_method, transaction_id, ref_no, pickup_date, pickup_time, payment_proof) 
            VALUES ('$user_id', '$order_number', '$order_date', '$delivery_method', '$first_name $last_name', '$address', '$mobile', '$email', '$payment_method', '$transaction_id', '$ref_no', '$pickup_date', '$pickup_time', '$payment_proof_path')";

        // Execute query
        $save = $this->db->query($sql);
        if (!$save) {
            return "Error: " . $this->db->error;
        }
        $id = $this->db->insert_id; // Get the last inserted ID
    
        // Check if user is logged in
        if (!isset($_SESSION['login_user_id'])) {
            error_log("User not logged in");
            return "Error: User not logged in";
        }
    
        $qry = $this->db->query("SELECT * FROM cart WHERE user_id = " . $_SESSION['login_user_id']);
        while ($row = $qry->fetch_assoc()) {
            $product_id = $this->db->real_escape_string($row['product_id']);
            $qty = $this->db->real_escape_string($row['qty']);
            $sql2 = "INSERT INTO order_list (order_id, product_id, qty) VALUES ('$id', '$product_id', '$qty')";
            $save2 = $this->db->query($sql2);
            if (!$save2) {
                error_log("Error in order_list: " . $this->db->error);
                return "Error: " . $this->db->error;
            }
            // Remove item from cart
            $this->db->query("DELETE FROM cart WHERE id = " . $row['id']);
        }
        return 1; // Indicate success
    }
    function confirm_order() {
        extract($_POST);
        $date = date("m-d-Y H:i:s");
        $save = $this->db->query("UPDATE orders SET status = 1, created_at = '$date' WHERE id= ".$id);
        if($save)
            return 1;
    }

    function cancel_order() {
        extract($_POST);
        $update = $this->db->query("UPDATE orders SET status = 'Canceled' WHERE id = $id");
        if($update)
            return 1;
        else
            return 0;
    }

    // New method for deleting a user
    function delete_user() {
        extract($_POST);
        // Make sure to handle both 'users' and 'user_info' tables if necessary
        $delete = $this->db->query("DELETE FROM users WHERE id = ".$id);
        if($delete) {
            $this->db->query("DELETE FROM user_info WHERE id = ".$id); // If user_info table exists and should also be cleaned up
            return 1;
        }
    }
        public function update_delivery_status($order_id, $new_status) {
            // Validate new status
            $allowed_statuses = ['Pending', 'Confirmed', 'Delivered', 'Arrived', 'Completed'];
            if (!in_array($new_status, $allowed_statuses)) {
                return "Invalid delivery status.";
            }
        
            // Prepare the SQL query
            $sql = "UPDATE orders SET delivery_status = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
        
            if (!$stmt) {
                return "Error preparing statement: " . $this->db->error;
            }
        
            // Bind parameters
            $stmt->bind_param('si', $new_status, $order_id);
        
            // Log the SQL query for debugging
            error_log("SQL Query: " . $sql); // Log the query
        
            // Execute the statement
            if (!$stmt->execute()) {
                return "Error updating delivery status: " . $stmt->error; // Return error if the execution fails
            }
        
            // Log successful update
            error_log("Delivery status updated for order ID $order_id to '$new_status'.");
        
            return 1; // Return success code
        }
        
        function delete_order() {
            global $conn;
            $orderId = $_POST['id'];
            if (isset($orderId)) {
                $qry = $conn->query("DELETE FROM orders WHERE id = '$orderId'");
                return $qry ? 1 : 0; // Return 1 on success, 0 on failure
            }
            return 0; // In case id is not set
        }
    }
   // Forgot Password Function
function forgot_password() {
    global $conn;
    
    $email = $_POST['email'];
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT user_id FROM user_info WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows === 0) {
        return json_encode(['status' => 'error', 'message' => 'Email not found']);
    }
    
    // Generate 6-digit OTP
    $otp = rand(100000, 999999);
    $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    
    // Store OTP in database
    $stmt = $conn->prepare("UPDATE user_info SET otp = ?, otp_expiry = ?, reset_time = CURRENT_TIME() WHERE email = ?");
    $stmt->bind_param("iss", $otp, $expiry, $email);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        return json_encode(['status' => 'error', 'message' => 'Failed to store OTP']);
    }
    
    // Send email using PHPMailer
    require 'PHPMailer/PHPMailer.php';
    require 'PHPMailer/SMTP.php';
    require 'PHPMailer/Exception.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'mandmcakeorderingsystem@gmail.com'; 
        $mail->Password = 'dgld kvqo yecu wdka'; 
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Recipients
        $mail->setFrom('mandmcakeorderingsystem@gmail.com', 'M&M Cake Ordering System');
        $mail->addAddress($email);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset OTP';
        $mail->Body = "
            <h2>Password Reset Request</h2>
            <p>Your OTP for password reset is: <strong>$otp</strong></p>
            <p>This OTP will expire in 15 minutes.</p>
            <p>If you didn't request this, please ignore this email.</p>
        ";
        
        $mail->send();
        return json_encode(['status' => 'success']);
        
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        return json_encode(['status' => 'error', 'message' => 'Email could not be sent. Error: ' . $mail->ErrorInfo]);
    }
}

function reset_password() {
    global $conn;

    // Get the form data
    $email = $_POST['email'];
    $otp = $_POST['otp'];
    $password = $_POST['password'];

    // Validate password (e.g., minimum length)
    if (strlen($password) < 8) {
        return json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters long']);
    }

    // Hash the password securely
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Verify OTP
    $stmt = $conn->prepare("SELECT * FROM user_info WHERE email = ? AND otp = ? AND otp_expiry > NOW()");
    $stmt->bind_param("si", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return json_encode(['status' => 'error', 'message' => 'Invalid or expired OTP']);
    }

    // Update password and clear OTP
    $stmt = $conn->prepare("UPDATE user_info SET password = ?, otp = NULL, otp_expiry = NULL WHERE email = ?");
    $stmt->bind_param("ss", $passwordHash, $email);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        return json_encode(['status' => 'success']);
    } else {
        return json_encode(['status' => 'error', 'message' => 'Failed to reset password']);
    }
}
function createNotification($user_id, $order_id, $message) {
    global $conn;
    $sql = "INSERT INTO notifications (user_id, order_id, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $user_id, $order_id, $message);
    return $stmt->execute();
}
function sendEmail($to, $receiptHtml) {
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.example.com';  // Set the SMTP server to send through
        $mail->SMTPAuth = true;
        $mail->Username = 'mandmcakeorderingsystem@gmail.com'; 
        $mail->Password = 'dgld kvqo yecu wdka'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        //Recipients
        $mail->setFrom('mandmcakeorderingsystem@gmail.com', 'M&M Cake Ordering System');
        $mail->addAddress($to);  // Add a recipient

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Receipt for Your Order';
        $mail->Body    = $receiptHtml;  // The receipt HTML or a PDF if needed

        // Send the email
        $mail->send();
        echo 'Message has been sent';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>