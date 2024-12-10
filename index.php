<?php ob_start(); ?>

<!DOCTYPE html>
<html lang="en">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<?php
session_start();

// Notification Helper Functions
function setNotification($type, $message) {
    $_SESSION['notification'] = [
        'type' => $type,    // success, error, warning, info
        'message' => $message,
        'timestamp' => time()
    ];
}

function getNotification() {
    if (isset($_SESSION['notification'])) {
        $notification = $_SESSION['notification'];
        unset($_SESSION['notification']); // Clear after retrieving
        return $notification;
    }
    return null;
}

include('header.php');
include('admin/db_connect.php');

$query = $conn->query("SELECT * FROM system_settings limit 1")->fetch_array();
foreach ($query as $key => $value) {
  if(!is_numeric($key))
    $_SESSION['setting_'.$key] = $value;
}

$recaptchaSecret = '6LcoapYqAAAAAKvZv36lF1Ru5fk24phEAjbhMak4';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recaptchaResponse = $_POST['g-recaptcha-response'];

    // Verify reCAPTCHA
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => $recaptchaSecret,
        'response' => $recaptchaResponse,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];

    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => http_build_query($data)
        ]
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $response = json_decode($result, true);

    if ($response['success']) {
        setNotification('success', 'reCAPTCHA verified successfully.');
    } else {
        setNotification('error', 'reCAPTCHA verification failed. Please try again.');
    }
}

?>

<style>
  header.masthead {
    background: url(assets/img/<?php echo $_SESSION['setting_cover_img'] ?>);
    background-repeat: no-repeat;
    background-size: cover;
    background-position: center center;
    position: relative;
    height: 85vh !important;
  }

  header.masthead:before {
    content: "";
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
    backdrop-filter: brightness(0.8);
  }

  /* Add styling for dropdown in mobile view */
  @media (max-width: 768px) {
    .navbar .dropdown-menu {
      position: static;
      float: none;
      width: 100%;
      background-color: #fff;
    }
    
    .navbar .dropdown-menu a {
      font-size: 18px;
      padding: 10px 20px;
      text-align: left;
    }
    
    .navbar .dropdown-menu a.dropdown-item {
      color: #333;
      width: 100%;
      text-align: left;
    }
  }

  /* Notification Styles */
  .notification {
    position: fixed;
    top: 85px;
    right: 20px;
    z-index: 1000;
    max-width: 350px;
    padding: 15px;
    border-radius: 4px;
    animation: slideIn 0.5s ease-in-out;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  }

  .notification.success {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
  }

  .notification.error {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
  }

  .notification.warning {
    background-color: #fff3cd;
    border-color: #ffeeba;
    color: #856404;
  }

  .notification.info {
    background-color: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
  }

  @keyframes slideIn {
    from {
      transform: translateX(100%);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }

  .notification-close {
    float: right;
    font-size: 20px;
    font-weight: bold;
    line-height: 1;
    cursor: pointer;
    margin-left: 10px;
  }
  /* Apply the custom font to the entire body or specific elements */
body {
    font-family: 'Poppins', sans-serif; /* Change to your preferred font */
}

/* Navigation styling */
.navbar-nav .nav-item .nav-link {
    font-size: 18px; /* Default font size */
    color: #333; /* Default color */
    transition: color 0.3s ease, transform 0.3s ease; /* Smooth transition for color change */
    font-weight: 400;
}

/* Hover effect for navigation links */
.navbar-nav .nav-item .nav-link:hover {
    color: #007bff; /* Change to your desired color on hover */
    text-decoration: underline; /* Underline the text on hover */
    transform: scale(1.05); /* Slightly scale the text on hover */
}

/* Navbar active state (if needed) */
.navbar-nav .nav-item .nav-link.active {
    color: #0056b3; /* Change active link color */
    font-weight: 600; /* Bold active link */
}

/* Adjust the navbar brand and logo font */
.navbar-brand {
    font-family: 'Poppins', sans-serif; /* Same font for consistency */
    font-weight: 600; /* Bold logo */
    font-size: 24px;
}

/* Notification Container Styling */
.notification {
    position: fixed;
    top: 85px;
    right: 20px;
    z-index: 1000;
    max-width: 350px;
    padding: 15px;
    border-radius: 4px;
    animation: slideIn 0.5s ease-in-out;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    font-family: 'Poppins', sans-serif;
}

.notification-close {
    float: right;
    font-size: 20px;
    font-weight: bold;
    line-height: 1;
    cursor: pointer;
    margin-left: 10px;
}
nav#mainNav * {
    color: #fff !important;
    text-shadow: 0px 0px 5px #000;
    font-family: "Times New Roman", Times, serif !important;
}/* Change the hover color of dropdown items */
.dropdown-item:hover {
    background-color: #007bff; /* Custom hover background color */
    color: black; /* Change text color to black on hover */
}
  /* Force dropdown visibility on mobile */
  @media (max-width: 767px) {
  .navbar-nav .dropdown-menu {
    display: block !important;
    position: relative !important;
  }
}
</style>

<body id="page-top">
  <!-- Notification Container -->
  <div id="notification-container"></div>

  <!-- Navigation-->
  <div class="toast" id="alert_toast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-body text-white">
    </div>
  </div>
  <nav class="navbar navbar-expand-lg navbar-light fixed-top py-3" id="mainNav">
  <div class="container">
    <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse"
      data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false"
      aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="navbarResponsive">
      <ul class="navbar-nav ml-auto my-2 my-lg-0">
        <li class="nav-item"><a class="nav-link js-scroll-trigger" href="index.php?page=home"style="font-size: 20px";>Home</a></li>
        <li class="nav-item"><a class="nav-link js-scroll-trigger" href="index.php?page=cart_list"style="font-size: 20px";><span><span class="badge badge-danger item_count"style="font-size: 15px";>0</span> <i class="fa fa-shopping-cart"></i> </span>Cart</a></li>
        <li class="nav-item"><a class="nav-link js-scroll-trigger" href="index.php?page=about"style="font-size: 20px";>About</a></li>
       
          <?php if (isset($_SESSION['login_user_id'])): ?>
            <li class="nav-item">
    <a class="nav-link js-scroll-trigger" href="notification.php" style="font-size: 20px;">
        <i class="fa fa-bell"></i>
        <?php
        // Check if the user is logged in
        if (isset($_SESSION['login_user_id'])) {
            $user_id = $_SESSION['login_user_id'];

            // Prepared statement to get the count of unread notifications with admin replies
            $stmt = $conn->prepare("SELECT COUNT(*) as count 
                                    FROM notifications 
                                    WHERE user_id = ? AND message IS NOT NULL AND is_read = 0");
            $stmt->bind_param("i", $user_id);  // Bind the user_id as an integer parameter

            $stmt->execute();
            $result = $stmt->get_result();

            if ($result) {
                // Fetch the count of unread admin replies
                $notify_count = $result->fetch_assoc()['count'];

                // If there are unread notifications, display the badge
                if ($notify_count > 0):
        ?>
                    <span class="badge badge-danger notification-count"><?php echo $notify_count; ?></span>
        <?php
                endif;
            } else {
                // Handle query execution error
                echo "Error fetching notification count: " . $stmt->error;
            }

            $stmt->close(); // Close the statement
        } else {
            echo "User is not logged in.";
        }
        ?>
    </a>
</li>



  <li class="nav-item"><a class="nav-link js-scroll-trigger" href="my_orders.php"style="font-size: 20px";>Your Orders</a></li>
          <!---  <li class="nav-item"><a class="nav-link js-scroll-trigger" href="message.php" style="font-size: 18px;">Messages</a></li>
--->
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle js-scroll-trigger" href="#" id="navbarDropdown" style="font-size: 18px;"
                role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <?php echo "Welcome ".$_SESSION['login_first_name'].' '.$_SESSION['login_last_name'] ?>
                <i class="fa fa-user"></i>
              </a>
              
              </button>
  <div class="dropdown-menu" aria-labelledby="navbarDropdown">
    <a class="dropdown-item" href="profile.php"style="color:black;">Profile</a>
    <a class="dropdown-item" href="admin/ajax.php?action=logout2">Logout</a>
  </div>
</div>
            </li>
            </li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link js-scroll-trigger" href="javascript:void(0)" id="login_now" style="font-size: 18px;">Login</a></li>
          <!---  <li class="nav-item"><a class="nav-link js-scroll-trigger" href="./admin" style="font-size: 18px;">Admin Login</a></li>
          --> <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <?php 
  $page = isset($_GET['page']) ? $_GET['page'] : "home";
  include $page.'.php';
  ?>

  <div class="modal fade" id="confirm_modal" role='dialog'>
    <div class="modal-dialog modal-md" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirmation</h5>
        </div>
        <div class="modal-body">
          <div id="delete_content"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id='confirm' onclick="">Continue</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="uni_modal" role='dialog'>
    <div class="modal-dialog modal-md" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"></h5>
        </div>
        <div class="modal-body">
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="uni_modal_right" role='dialog'>
    <div class="modal-dialog modal-full-height  modal-md" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span class="fa fa-arrow-right"></span>
          </button>
        </div>
        <div class="modal-body">
        </div>
      </div>
    </div>
  </div>

  <footer class="bg-light py-5">
    <div class="container">
      <div class="small text-center text-muted">Copyright © 2024 - M&M Cake Ordering System | </div>
    </div>
  </footer>

  <?php include('footer.php') ?>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>

  <!-- Notification JavaScript -->
  <script>
    function showNotification(type, message, duration = 5000) {
        const container = document.getElementById('notification-container');
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        
        notification.innerHTML = `
            <span class="notification-close">&times;</span>
            <div class="notification-message">${message}</div>
        `;
        
        container.appendChild(notification);
        
        // Close button functionality
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.onclick = () => {
            notification.remove();
        };
        
        // Auto-remove after duration
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, duration);
    }

    // Check for PHP session notification on page load
    <?php
    $notification = getNotification();
    if ($notification): 
    ?>
        document.addEventListener('DOMContentLoaded', function() {
            showNotification('<?php echo $notification['type']; ?>', 
                            '<?php echo htmlspecialchars($notification['message'], ENT_QUOTES); ?>');
        });
    <?php endif; ?>
  </script>
</body>

</html>
