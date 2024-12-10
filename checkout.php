<?php
include 'admin/db_connect.php';

if (!isset($_SESSION['login_user_id'])) {
    echo "<script>alert('Please login first.'); location.replace('login.php')</script>";
}

$chk = $conn->query("SELECT * FROM cart WHERE user_id = {$_SESSION['login_user_id']}")->num_rows;
if ($chk <= 0) {
    echo "<script>alert('You don\'t have an Item in your cart yet.'); location.replace('./')</script>";
}
?>
<header class="masthead">
    <div class="container h-100">
        <div class="row h-100 align-items-center justify-content-center text-center">
            <div class="col-lg-10 align-self-center mb-4 page-title">
                <h1 class="text-white">Checkout</h1>
                <hr class="divider my-4 bg-dark" />
            </div>
        </div>
    </div>
</header>
<div class="container">
    <div class="card">
        <div class="card-body">
            <form action="" id="checkout-frm">
                <h4>Select Payment Method</h4>
                <div class="form-group">
                    <select name="payment_method" id="payment_method" class="form-control" required>
                        <option value="">Select Payment Method</option>
                        <option value="cash">Cash on Delivery</option>
                        <option value="gcash">G-Cash</option>
                    </select>
                </div>

                <div class="form-group order-type-selection" style="display:none;">
                    <label class="control-label">Order Type</label>
                    <div>
                        <label><input type="radio" name="order_type" value="delivery" id="delivery"> Delivery</label>
                        <label><input type="radio" name="order_type" value="pickup" id="pickup"> Pick-up</label>
                    </div>
                </div>

                <div class="form-group delivery-info" style="display:none;">
                    <label class="control-label">Firstname</label>
                    <input type="text" name="first_name" class="form-control"
                        value="<?php echo htmlspecialchars($_SESSION['login_first_name']); ?>" readonly>
                </div>
                <div class="form-group delivery-info" style="display:none;">
                    <label class="control-label">Lastname</label>
                    <input type="text" name="last_name" class="form-control"
                        value="<?php echo htmlspecialchars($_SESSION['login_last_name']); ?>" readonly>
                </div>
                <div class="form-group delivery-info" style="display:none;">
                    <label class="control-label">Contact</label>
                    <input type="text" name="mobile" class="form-control"
                        value="<?php echo htmlspecialchars($_SESSION['login_mobile']); ?>" readonly>
                </div>
                <div class="form-group delivery-info" style="display:none;">
                    <label class="control-label">Address</label>
                    <textarea cols="30" rows="3" name="address" class="form-control"
                        readonly><?php echo htmlspecialchars($_SESSION['login_address']); ?></textarea>
                </div>
                <div class="form-group delivery-info" style="display:none;">
                    <label class="control-label">Email</label>
                    <input type="email" name="email" class="form-control"
                        value="<?php echo htmlspecialchars($_SESSION['login_email']); ?>" readonly>
                </div>

                <div class="form-group pickup-info" style="display:none;">
                    <label class="control-label">Pick-up Date</label>
                    <input type="date" name="pickup_date" class="form-control" min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group pickup-info" style="display:none;">
                    <label class="control-label">Pick-up Time</label>
                    <input type="time" name="pickup_time" class="form-control">
                </div>

                <div class="form-group gcash-only" style="display:none;">
                    <label for="payment-proof" class="control-label">Upload Payment Proof</label>
                    <div class="d-flex align-items-center">
                        <div id="image-preview" style="margin-right: 10px;">
                            <!-- Image preview will be displayed here -->
                        </div>
                        <input type="file" name="payment_proof" id="payment-proof" accept="image/*"
                            class="form-control">
                    </div>
                </div>
                <div class="form-group gcash-only" style="display:none;">
                    <label for="ref-no" class="control-label">Reference Number</label>
                    <input type="text" name="ref_no" id="ref-no" class="form-control" placeholder="Reference Number" readonly>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="agree_terms" required>
                        <label class="form-check-label" for="agree_terms">
                            I agree that orders cannot be canceled after placing.
                        </label>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-block btn-outline-dark">Place Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SweetAlert2 and jQuery CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@2.1.1/dist/tesseract.min.js"></script>

<script>
$(document).ready(function () {
    // Add these elements to a variable for easy reference
    const paymentProofGroup = $('.gcash-only');

    // Initially hide delivery and pickup info
    $('.delivery-info').hide();
    $('.pickup-info').hide();
    $('.gcash-only').hide();

    $('#payment_method').change(function () {
        $('.order-type-selection').show();
        if ($(this).val() == 'gcash') {
            Swal.fire({
                title: 'Scan QR Code with G-Cash',
                imageUrl: 'assets/img/gcash.jpg',
                imageWidth: 500,
                imageHeight: 600,
                imageAlt: 'G-Cash QR Code',
                showCloseButton: true,
                confirmButtonText: 'Proceed',
            }).then((result) => {
                if (result.isConfirmed) {
                    $('.gcash-only').show();
                }
            });
        } else {
            // Hide G-Cash related fields for other payment methods
            $('.gcash-only').hide();
            // Clear the fields when hiding them
            $('#payment-proof').val('');
            $('#ref-no').val('');
            $('#image-preview').empty();
        }
    });

    $('input[name="order_type"]').change(function () {
        const selectedType = $(this).val();
        
        if (selectedType === 'delivery') {
            $('.delivery-info').show();
            $('.pickup-info').hide();
            // Make delivery fields required
            $('.delivery-info input, .delivery-info textarea').prop('required', true);
            $('.pickup-info input').prop('required', false);
        } else if (selectedType === 'pickup') {
            $('.pickup-info').show();
            $('.delivery-info').hide();
            // Make pickup fields required
            $('.pickup-info input').prop('required', true);
            $('.delivery-info input, .delivery-info textarea').prop('required', false);
        }
    });

    $('#checkout-frm').submit(function (e) {
        e.preventDefault();

        // Basic validation
        const paymentMethod = $('#payment_method').val();
        const orderType = $('input[name="order_type"]:checked').val();

        // Validate payment method selection
        if (!paymentMethod) {
            Swal.fire({
                icon: 'warning',
                title: 'Payment Method Required',
                text: 'Please select a payment method.',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Validate order type selection
        if (!orderType) {
            Swal.fire({
                icon: 'warning',
                title: 'Order Type Required',
                text: 'Please select an order type (Delivery or Pick-up).',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Validate G-Cash specific requirements
        if (paymentMethod === 'gcash') {
            if (!$('#ref-no').val().trim()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Reference Number Required',
                    text: 'Please upload a payment proof with a valid reference number before placing your order.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            if (!$('#payment-proof').val()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Payment Proof Required',
                    text: 'Please upload your payment proof.',
                    confirmButtonText: 'OK'
                });
                return;
            }
        }

        // Validate pickup details if pickup is selected
        if (orderType === 'pickup') {
            if (!$('input[name="pickup_date"]').val() || !$('input[name="pickup_time"]').val()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pickup Details Required',
                    text: 'Please fill in both pickup date and time.',
                    confirmButtonText: 'OK'
                });
                return;
            }
        }

        if (!$("#agree_terms").is(":checked")) {
            Swal.fire({
                icon: 'warning',
                title: 'Agreement Required',
                text: 'Please agree that orders cannot be canceled after placing.',
                showConfirmButton: true,
            });
            return;
        }

        start_load();
        var formData = new FormData(this);
        $.ajax({
            url: "admin/ajax.php?action=save_order",
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (resp) {
                if (resp == 1) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Order Placed Successfully!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function () {
                        location.replace('index.php?page=home');
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to Place Order',
                        text: resp.msg || 'Please try again later.',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
                end_load();
            },
            error: function (xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to process the order: ' + error,
                    showConfirmButton: true,
                });
                end_load();
            }
        });
    });

    document.getElementById('payment-proof').addEventListener('change', function (event) {
        const file = event.target.files[0];
        const preview = document.getElementById('image-preview');
        const refNoInput = document.getElementById('ref-no');

        // Clear previous preview and reference number
        preview.innerHTML = '';
        refNoInput.value = '';

        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.maxWidth = '100px';
                img.style.maxHeight = '100px';
                preview.appendChild(img);

                // Perform OCR
                Tesseract.recognize(
                    e.target.result,
                    'eng',
                    {
                        logger: info => console.log(info)
                    }
                ).then(({ data: { text } }) => {
                    console.log(text);

                    const refNoPattern = /Ref\.?\s*No\.?\s*([\d\s]+)/i;
                    const match = text.match(refNoPattern);
                    if (match) {
                        refNoInput.value = match[1].trim();
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Reference Number Not Found',
                            text: 'No reference number was detected in the uploaded image. Please try again with a clearer image.',
                            confirmButtonText: 'OK'
                        });
                    }
                }).catch(err => {
                    console.error('OCR Error:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'OCR Error',
                        text: 'Failed to extract text from the image. Please try again.',
                        confirmButtonText: 'OK'
                    });
                });
            }
            reader.readAsDataURL(file);
        }
    });
});

function start_load() {
    $('body').prepend('<div id="preloader"></div>');
}

function end_load() {
    $('#preloader').fadeOut('fast', function () {
        $(this).remove();
    });
}
</script>