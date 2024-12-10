<?php
session_start();
if (!isset($_SESSION['verify_email'])) {
    header('Location: signup.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>OTP Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 500px;
            margin: 50px auto;
            text-align: center;
            padding: 20px;
        }
        
        .otp-field {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 30px 0;
        }
        
        .otp-field input {
            width: 50px;
            height: 50px;
            border: 2px solid #ddd;
            border-radius: 8px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            line-height: 1;
        }
        
        .otp-field input:focus {
            outline: none;
            border-color: #0d6efd;
            box-shadow: 0 0 5px rgba(13, 110, 253, 0.5);
        }
        
        button {
            padding: 12px 30px;
            background-color: #0d6efd;
            border: none;
            border-radius: 6px;
            color: #fff;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        
        button:not(:disabled):hover {
            background-color: #0b5ed7;
        }
        
        .message {
            margin-top: 20px;
            min-height: 24px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Enter Verification Code</h1>
        <p class="text-muted">Please enter the 6-digit code sent to <?php echo htmlspecialchars($_SESSION['verify_email']); ?></p>
        <div class="otp-field">
            <input type="text" maxlength="1" pattern="[0-9]" />
            <input type="text" maxlength="1" pattern="[0-9]" />
            <input type="text" maxlength="1" pattern="[0-9]" />
            <input type="text" maxlength="1" pattern="[0-9]" />
            <input type="text" maxlength="1" pattern="[0-9]" />
            <input type="text" maxlength="1" pattern="[0-9]" />
        </div>
        <button id="verifyBtn" disabled>Verify</button>
        <p class="message"></p>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        const inputs = document.querySelectorAll('.otp-field input');
        const button = document.querySelector('#verifyBtn');
        const message = document.querySelector('.message');

        // Add input event listeners
        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                // Only allow numbers
                e.target.value = e.target.value.replace(/[^0-9]/g, '');

                // Auto focus next input
                if (e.target.value && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }

                // Enable/disable button based on all inputs being filled
                checkButton();
            });

            // Handle backspace
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });

            // Handle paste
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text').slice(0, inputs.length);
                if (/^\d+$/.test(pastedData)) { // Only numbers
                    [...pastedData].forEach((digit, i) => {
                        if (inputs[i]) {
                            inputs[i].value = digit;
                            if (inputs[i + 1]) inputs[i + 1].focus();
                        }
                    });
                    checkButton();
                }
            });
        });

        // Check if all inputs are filled
        function checkButton() {
            const isComplete = Array.from(inputs).every(input => input.value);
            button.disabled = !isComplete;
        }

        // Verify button click handler
        button.addEventListener('click', () => {
            const otp = Array.from(inputs).map(input => input.value).join('');
            
            // Show loading state
            button.disabled = true;
            button.textContent = 'Verifying...';
            
            // Send OTP to server
            $.ajax({
                url: 'verify_otp.php',
                type: 'POST',
                data: { otp: otp },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(function() {
                            window.location.href = 'index.php';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message
                        });
                        // Reset inputs
                        inputs.forEach(input => input.value = '');
                        inputs[0].focus();
                        button.disabled = true;
                        button.textContent = 'Verify';
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred. Please try again.'
                    });
                    button.disabled = false;
                    button.textContent = 'Verify';
                }
            });
        });

        // Focus first input on page load
        window.onload = () => inputs[0].focus();
    </script>
</body>
</html>