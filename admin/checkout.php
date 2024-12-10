<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="path/to/your/styles.css">
    <style>
        /* Popup styles */
        .popup {
            display: none; /* Hidden by default */
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5); /* Black with opacity */
            z-index: 1000; /* Sit on top */
        }
        .popup-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            text-align: center;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

<header>
    <!-- Add your header content here if needed -->
</header>

<div class="container">
    <div class="card">
        <div class="card-body">
            <form action="" id="checkout-frm">
                <h4>Confirm Delivery Information</h4>
                <div class="form-group">
                    <label for="first_name" class="control-label">Firstname</label>
                    <input type="text" name="first_name" id="first_name" required class="form-control" value="<?php echo $_SESSION['login_first_name'] ?>">
                </div>
                <div class="form-group">
                    <label for="last_name" class="control-label">Last Name</label>
                    <input type="text" name="last_name" id="last_name" required class="form-control" value="<?php echo $_SESSION['login_last_name'] ?>">
                </div>
                <div class="form-group">
                    <label for="mobile" class="control-label">Contact</label>
                    <input type="text" name="mobile" id="mobile" required class="form-control" value="<?php echo $_SESSION['login_mobile'] ?>">
                </div>
                <div class="form-group">
                    <label for="address" class="control-label">Address</label>
                    <textarea cols="30" rows="3" name="address" id="address" required class="form-control"><?php echo $_SESSION['login_address'] ?></textarea>
                </div>
                <div class="form-group">
                    <label for="email" class="control-label">Email</label>
                    <input type="email" name="email" id="email" required class="form-control" value="<?php echo $_SESSION['login_email'] ?>">
                </div>

                <h4>Select Payment Method</h4>
                <div class="form-group">
                    <select name="payment_method" id="payment_method" class="form-control" required>
                        <option value="">Select Payment Method</option>
                        <option value="cash">Cash on Delivery</option>
                        <option value="gcash">G-Cash</option>
                        <!-- Add more payment methods as needed -->
                    </select>
                </div>

                <div class="text-center">
                    <button class="btn btn-block btn-outline-primary">Place Order</button>
                </div>
            </form>

            <!-- Popup HTML -->
            <div id="popup" class="popup">
                <div class="popup-content">
                    <span class="close">&times;</span>
                    <p>SCAN ME TO PAY</p>
                    <img src="assets/img/g.jpg" alt="QR Code">
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="path/to/your/check.js"></script>
<script>
    // JavaScript to handle popup display
    document.getElementById('payment_method').addEventListener('change', function() {
        var popup = document.getElementById('popup');
        if (this.value === 'gcash') {
            popup.style.display = 'block';
        } else {
            popup.style.display = 'none';
        }
    });

    // JavaScript to close the popup
    document.querySelector('.close').addEventListener('click', function() {
        document.getElementById('popup').style.display = 'none';
    });

    // Close popup if clicked outside of the popup-content
    window.addEventListener('click', function(event) {
        if (event.target === document.getElementById('popup')) {
            document.getElementById('popup').style.display = 'none';
        }
    });

    $(document).ready(function(){
        $('#checkout-frm').submit(function(e){
            e.preventDefault();
            start_load(); // Make sure you have this function defined

            $.ajax({
                url: "admin/ajax.php?action=save_order",
                method: 'POST',
                data: $(this).serialize(),
                success: function(resp) {
                    if (resp == 1) {
                        alert_toast("Order successfully Placed.");
                        setTimeout(function() {
                            location.replace('index.php?page=home');
                        }, 1500);
                    }
                }
            });
        });
    });
</script>

</body>
</html>