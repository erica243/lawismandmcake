<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Admin | M&M Cake Ordering System</title>

  <?php include('./header.php'); ?>
  <?php include('./db_connect.php'); ?>
  <?php 
    session_start();
    if(isset($_SESSION['login_id']))
    header("location:index.php?page=home");

    $query = $conn->query("SELECT * FROM system_settings limit 1")->fetch_array();
    foreach ($query as $key => $value) {
        if(!is_numeric($key))
            $_SESSION['setting_'.$key] = $value;
    }
  ?>

  <style>
    body {
      width: 100%;
      height: calc(100%);
    }
    main#main {
      width: 100%;
      height: calc(100%);
      background: white;
    }
    #login-right {
      position: absolute;
      right:0;
      width:40%;
      height: calc(100%);
      background:white;
      display: flex;
      align-items: center;
      justify-content: center;
      background-image: linear-gradient(to top, #a8edea 0%, #fed6e3 100%);
    }
    #login-left {
      position: absolute;
      left: 0;
      width: 60%;
      height: calc(100%);
      background: #00000061;
      display: flex;
      align-items: center;
    }
    #login-right .card {
      margin: auto;
      width: 100%;
      max-width: 400px;
    }
    .logo {
      margin: auto;
      font-size: 8rem;
      background: white;
      border-radius: 50% 50%;
      height: 29vh;
      width: 13vw;
      display: flex;
      align-items: center;
    }
    .logo img {
      height: 80%;
      width: 80%;
      margin: auto;
    }
    #login-left {
      background: url(./../assets/img/<?php echo $_SESSION['setting_cover_img'] ?>);
      background-repeat: no-repeat;
      background-size: cover;
      background-position: center center;
    }
    #login-left:before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      height: 100%;
      width: 100%;
      backdrop-filter: brightness(.8);
      z-index: 1;
    }
    #login-left .d-flex {
      position: relative;
      z-index: 2;
    }
    #login-left h1 {
      font-family: 'Dancing Script', cursive !important;
      font-weight: bolder;
      font-size: 4.5em;
      color: #fff;
      text-shadow: 0px 0px 5px #000;
    }
    .show-password {
      cursor: pointer;
      color: #007bff;
      font-size: 0.875rem;
    }
    .form-message {
      margin-bottom: 15px;
      text-align: center;
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
  <main id="main" class=" bg-dark">
    <div id="login-left" class="">
      <div class="h-100 w-100 d-flex justify-content-center align-items-center">
        <h1 class="text-center"><?= $_SESSION['setting_name'] ?> - Admin Site</h1>
      </div>
    </div>
    <div id="login-right">
      <div class="card col-md-8">
        <div class="card-body">
          <div id="login-container">
            <form id="login-form">
              <div id="form-message-container"></div>
              <div class="form-group">
                <label for="username" class="control-label">Username</label>
                <input type="email" id="username" name="username" autofocus class="form-control" required>
              </div>
              <div class="form-group">
                <label for="password" class="control-label">Password</label>
                <div class="input-group">
                  <input type="password" id="password" name="password" class="form-control" required>
                  <div class="input-group-append">
                    <span class="input-group-text show-password" id="password-toggle">
                      <i class="fa fa-eye"></i>
                    </span>
                  </div>
                </div>
              </div>
              <div class="form-group text-center">
                <a href="./../" class="text-dark">Back to Website</a>
              </div>
              <center>
                <button type="submit" class="btn-sm btn-block btn-wave col-md-4 btn-dark">Login</button>
              </center>
              <div class="form-group text-center mt-2">
                <a href="#" class="text-dark forgot-password-link">Forgot Password?</a>
              </div>
            </form>

            <form id="forgot-password-form" style="display:none;">
              <div id="forgot-form-message-container"></div>
              <div class="form-message">
                <h4>Reset Password</h4>
                <p>Enter your email to receive a password reset link</p>
              </div>
              <div class="form-group">
                <label for="reset-email" class="control-label">Email</label>
                <input type="email" id="reset-email" name="reset-email" class="form-control" required>
              </div>
              <div class="form-group text-center">
                <a href="#" class="text-dark return-to-login">Back to Login</a>
              </div>
              <center>
                <button type="submit" class="btn-sm btn-block btn-wave col-md-4 btn-dark">Reset Password</button>
              </center>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>

  <a href="#" class="back-to-top"><i class="icofont-simple-up"></i></a>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
  <script>
    $(document).ready(function() {
      // Password toggle functionality
      $('#password-toggle').click(function() {
        var passwordField = $('#password');
        var passwordFieldType = passwordField.attr('type');
        if (passwordFieldType === 'password') {
          passwordField.attr('type', 'text');
          $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
          passwordField.attr('type', 'password');
          $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
        }
      });

      // Switch to forgot password form
      $('.forgot-password-link').click(function(e) {
        e.preventDefault();
        $('#login-form').hide();
        $('#forgot-password-form').show();
      });

      // Return to login form
      $('.return-to-login').click(function(e) {
        e.preventDefault();
        $('#forgot-password-form').hide();
        $('#login-form').show();
      });

      // Login form submission
      $('#login-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var submitButton = form.find('button[type="submit"]');
        
        // Clear previous messages
        $('#form-message-container').empty();
        
        submitButton.prop('disabled', true).html('Logging in...');
        
        $.ajax({
          url: 'ajax.php?action=login',
          method: 'POST',
          data: form.serialize(),
          error: function(err) {
            console.log(err);
            submitButton.prop('disabled', false).html('Login');
            $('#form-message-container').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
          },
          success: function(resp) {
            if (resp == 1) {
              location.href = 'index.php?page=home';
            } else {
              submitButton.prop('disabled', false).html('Login');
              $('#form-message-container').html('<div class="alert alert-danger">Username or password is incorrect.</div>');
            }
          }
        });
      });

      // Forgot Password form submission
      $('#forgot-password-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var submitButton = form.find('button[type="submit"]');
        var resetEmail = $('#reset-email').val();
        
        // Clear previous messages
        $('#forgot-form-message-container').empty();
        
        submitButton.prop('disabled', true).html('Sending...');
        
        $.ajax({
          url: 'forgot_password.php',
          method: 'POST',
          data: { 
            action: 'forgot_password', 
            email: resetEmail 
          },
          error: function(err) {
            console.log(err);
            submitButton.prop('disabled', false).html('Reset Password');
            $('#forgot-form-message-container').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
          },
          success: function(resp) {
            submitButton.prop('disabled', false).html('Reset Password');
            try {
              var response = JSON.parse(resp);
              if (response.status === 'success') {
                $('#forgot-form-message-container').html('<div class="alert alert-success">' + response.message + '</div>');
                // Optional: Clear the email field after successful submission
                $('#reset-email').val('');
              } else {
                $('#forgot-form-message-container').html('<div class="alert alert-danger">' + response.message + '</div>');
              }
            } catch (e) {
              $('#forgot-form-message-container').html('<div class="alert alert-danger">Unable to process request.</div>');
            }
          }
        });
      });
    });
  </script>
</body>

</html>