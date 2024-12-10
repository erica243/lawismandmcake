<?php
session_start();
require_once('admin/db_connect.php');

// Fetch unique municipalities
$municipalities = [];
$query = $conn->query("SELECT DISTINCT municipality FROM shipping_info WHERE municipality IS NOT NULL ORDER BY municipality");
while ($row = $query->fetch_assoc()) {
    $municipalities[] = $row['municipality'];
}

// Fetch all shipping info for client-side filtering
$shipping_info = [];
$query = $conn->query("SELECT address, municipality FROM shipping_info");
while ($row = $query->fetch_assoc()) {
    $shipping_info[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - M&M Cake Ordering System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
      body {
        background-color: #f4f4f4;
        padding-top: 20px; /* Reduced padding for smaller screens */
    }
    .signup-container {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        padding: 20px; /* Reduced padding */
        width: 100%; /* Full width on mobile */
        max-width: 500px;
        margin: 0 auto;
    }
    .form-group {
        margin-bottom: 15px;
    }
    .modal-scrollable {
        max-height: 400px; /* Adjusted for mobile scrolling */
        overflow-y: auto;
    }
    .is-invalid {
        border-color: red;
    }
    /* Added media query for extra small devices */
    @media (max-width: 576px) {
        .signup-container {
            padding: 15px;
            border-radius: 0; /* Remove border radius on very small screens */
            box-shadow: none;
        }
        .modal-dialog {
            margin: 0;
            width: 100%;
            max-width: none;
            height: 100%;
        }
        .modal-content {
            height: 100%;
            border: none;
            border-radius: 0;
        }
        .modal-body {
            padding: 10px;
        }
        .form-control, .btn {
            font-size: 16px; /* Prevent zoom on input focus */
        }
    }
    </style>
</head>
<body>
    <div class="container">
        <div class="signup-container">
            <form id="signup-form" method="POST">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                </div>

                <div class="form-group">
                    <label for="mobile">Contact Number</label>
                    <input type="tel" class="form-control" id="mobile" name="mobile" maxlength="11" required>
                </div>

                <div class="form-group">
                    <label for="municipality">Municipality</label>
                    <select class="form-control" id="municipality" name="municipality" required>
                        <option value="">Select Municipality</option>
                        <?php foreach ($municipalities as $municipality) : ?>
                            <option value="<?php echo htmlspecialchars($municipality); ?>">
                                <?php echo htmlspecialchars($municipality); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <select class="form-control" id="address" name="address" required disabled>
                        <option value="">Select Municipality First</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="street">Street</label>
                    <input type="text" class="form-control" id="street" name="street" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                    <small class="form-text text-muted">
                        Password must be at least 8 characters long and include uppercase, lowercase, numbers, and symbols.
                    </small>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" id="termsCheckbox" class="form-check-input" required>
                    <label for="termsCheckbox" class="form-check-label">
                        I agree to the <a href="#" id="openModal">Terms and Conditions</a>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-100">Create Account</button>
            </form>
        </div>
    </div>

    <!-- Terms Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Terms and Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
            <p>Welcome to the M&M Cake Ordering System! By using our website and services, you agree to the following terms and conditions. Please read them carefully.</p>

<h5>1. Acceptance of Terms</h5>
<p>By placing an order through our M&M Cake Ordering System, you agree to be bound by these terms and conditions. Please read them carefully before proceeding with your order.</p>

<h5>2. Ordering Process</h5>
<p>Customers are responsible for providing accurate and complete information, including contact details, delivery address, and cake specifications.</p>
<p>  Orders will be confirmed via email after payment is received (if payment method is G- Cash).</p>

<h5>3. User Accounts</h5>
<p>To place an order, you may need to create an account. You are responsible for maintaining the confidentiality of your account information and for all activities that occur under your account. Please notify us immediately of any unauthorized use of your account.</p>

<h5>4. Payment Terms</h5>
<p>All orders must be paid in full at the time of placing the order unless otherwise agreed.</p>
<p>Accepted payment methods include [Cash , Gcash].</p>
<p>Prices are subject to change without notice but will not affect orders already confirmed.</p>
<p>Payment must be made at the time of order placement. We accept various payment methods, including credit/debit cards and other specified options.</p>

<h5>5. Delivery and Pickup</h5>
<p>Delivery options and fees will be provided during the checkout process. We will make every effort to deliver your order on time; however, we are not responsible for delays caused by circumstances beyond our control.</p>
               <p> Customers opting for pickup must arrive at the scheduled time to avoid delays.</p>
<p>Delivery is available within Local areas for an additional fee depending on the distance of area.</p>
<p>We are not responsible for damages to cakes once they have been picked up or delivered successfully.</p>
<h5>6. No Cancellations</h5>
<p>Orders can only be canceled if the delivery status has not been confirmed. Once the delivery status is confirmed, cancellations and refunds will no longer be accepted.</p>

<h5>7. Refunds</h5>
<p>Refunds will be issued at our discretion and only in cases where an error has occurred on our part. Please contact us for further assistance if you believe you are eligible for a refund.</p>

<h5>8.Customization</h5>
<p>Customization requests (e.g., specific designs, colors, or additional toppings) must be submitted at the time of ordering.
While we will make every effort to match designs and colors, slight variations may occur due to the handmade nature of our cakes.</p>


<h5>9.Allergies and Dietary Restrictions</h5>
<p>Our cakes may contain or come into contact with allergens such as nuts, dairy, eggs, gluten, and soy.</p>
<p>It is the customer’s responsibility to inform us of any allergies or dietary restrictions at the time of ordering.</p>
<p>While we take precautions to minimize cross-contamination, we cannot guarantee a completely allergen-free product.</p>
<h5>10. Liability</h5>
<p>We are not liable for delays caused by circumstances beyond our control (e.g., adverse weather, transportation issues).</p>
<p> In the rare event of an issue with your order, please contact us within  1-2 hours before of pickup/delivery for resolution.</p>
<h5>11. Changes to Terms</h5>
<p>We reserve the right to update or modify these terms and conditions at any time. Any changes will be communicated via our website or directly to customers with active orders.
</p>

<h5>12. Contact Us</h5>
<p>If you have any questions about these Terms and Conditions, please contact us at:</p>
<p><strong>M&M Cake Ordering System</strong><br>
Phone: 09158259643<br>
Email: mandmcakeorderingsystem@gmail.com<br>
Address: Poblacion, Madridejos, Cebu</p>


            <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">accept</button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Prevent zoom on input focus for mobile
            $('input, select, textarea').on('focus', function() {
                if (window.innerWidth <= 576) {
                    $(this).css('font-size', '16px');
                }
            });

            // Store shipping info for client-side filtering
            const shippingInfo = <?php echo json_encode($shipping_info); ?>;

            // Municipality change handler
            $('#municipality').change(function() {
                const selectedMunicipality = $(this).val();
                const addressSelect = $('#address');

                // Clear and disable address select if no municipality is selected
                if (!selectedMunicipality) {
                    addressSelect.html('<option value="">Select Municipality First</option>').prop('disabled', true);
                    return;
                }

                // Filter addresses for selected municipality
                const filteredAddresses = shippingInfo.filter(info => info.municipality === selectedMunicipality);

                // Enable and populate address select
                addressSelect.prop('disabled', false);
                addressSelect.html('<option value="">Select Address</option>');

                filteredAddresses.forEach(info => {
                    addressSelect.append(`<option value="${info.address}">${info.address}</option>`);
                });
            });

            // Terms Modal
            const termsModal = new bootstrap.Modal(document.getElementById('termsModal'));
            $('#openModal').on('click', function(e) {
                e.preventDefault();
                termsModal.show();
            });

            // Password visibility toggle
            $('#togglePassword').click(function() {
                const password = $('#password');
                const icon = $(this).find('i');

                if (password.attr('type') === 'password') {
                    password.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    password.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            // Submit form with validation
            $('#signup-form').on('submit', function(e) {
                e.preventDefault();

                // Validate terms checkbox
                if (!$('#termsCheckbox').is(':checked')) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terms Not Accepted',
                        text: 'Please accept the Terms and Conditions'
                    });
                    return;
                }

                // Validate first and last names to only allow letters (no spaces or special characters)
                const firstName = $('#first_name').val();
                const lastName = $('#last_name').val();

                const nameRegex = /^[A-Za-z]+$/; // Only letters allowed

                if (!nameRegex.test(firstName)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid First Name',
                        text: 'First Name must contain only letters (no spaces or special characters)'
                    });
                    return;
                }

                if (!nameRegex.test(lastName)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Last Name',
                        text: 'Last Name must contain only letters (no spaces or special characters)'
                    });
                    return;
                }

                // Password validation
                const password = $('#password').val();
                if (password.length < 8 || 
                    !/[A-Z]/.test(password) || 
                    !/[a-z]/.test(password) || 
                    !/[0-9]/.test(password) || 
                    !/[^A-Za-z0-9]/.test(password)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Password',
                        text: 'Password must include uppercase, lowercase, numbers, and symbols'
                    });
                    return;
                }

                // Mobile number validation
                const mobile = $('#mobile').val();
                if (mobile.length !== 11) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Mobile Number',
                        text: 'Please enter an 11-digit mobile number'
                    });
                    return;
                }

                // Prepare form data
                const formData = $(this).serialize();

                // AJAX submission
                $.ajax({
                    url: 'signup_action.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Creating Account',
                            text: 'Please wait...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(function() {
                                window.location.href = 'email_otp.php';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred. Please try again later.'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
