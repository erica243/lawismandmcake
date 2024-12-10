
<?php
// Include your database connection file
include 'db_connect.php';

// Check if the function already exists to avoid redeclaring it
if (!function_exists('get_new_orders_count')) {
    function get_new_orders_count() {
        global $conn;

        // Include conditions for empty or null delivery_status
        $sql = "SELECT COUNT(*) AS total 
                FROM orders 
                WHERE delivery_status = '0' OR delivery_status IS NULL OR delivery_status = ''";
        $result = $conn->query($sql);

        if ($result) {
            $row = $result->fetch_assoc();
            return $row['total'];
        } else {
            return 0; // In case of error
        }
    }
}

if (!function_exists('get_unread_messages_count')) {
    function get_unread_messages_count() {
        global $conn;

        // Fetch unread messages based on status
        $sql = "SELECT COUNT(*) AS total FROM messages WHERE status = 0";
        $result = $conn->query($sql);

        if ($result) {
            $row = $result->fetch_assoc();
            return $row['total'];
        } else {
            return 0; // In case of error
        }
    }
}

// Fetch new orders count
$newOrdersCount = get_new_orders_count();

// Fetch unread messages count
$unreadMessagesCount = get_unread_messages_count();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>.navbar {
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 1030; /* Ensure it stays above content */
    height: 60px; /* Adjust this to match the navbar height */
}
  body {
    padding-top: 50px !important; /* Adjust according to your topbar height */
}

     .logo {
        
        margin-left: 30px; /* Adjust as needed */ font-size: 20px;
      background: white;
     
      border-radius: 50%;
    
    }

    .logo-text {
        font-family: 'Dancing Script', cursive;
        font-size: 30px;
        margin-left: 30px; /* Adjust as needed */
    }
    .notification-badge {
      position: absolute;
      top: -5px;
      right: -10px;
      background-color: red;
      color: white;
      padding: 5px 8px;
      border-radius: 50%;
      font-size: 12px;
    }
    .notification-icon {
      position: relative;
      cursor: pointer;
      margin-right: 20px;
    }
    .notification-link {
      text-decoration: none;
      color: inherit;
    }
    .dropdown-toggle::after {
      display: none;
    }
    @media (max-width: 768px) {
      .navbar-brand {
        font-size: 20px;
      }
      .notification-icon {
        margin-right: 10px;
      }
      .dropdown-toggle {
        font-size: 14px;
      }
    }@media (max-width: 768px) {
    body {
        padding-top: 40px; /* For smaller screen sizes */
    }
}
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg fixed-top" style="background-color: #4d94ff; color: black;">

  
      <!-- Logo and Branding -->
     
  <!-- Logo Image -->
  <img src="assets/img/logo.jpg" alt="Logo" class="logo me-2" style="width: 50px; height: 50px; object-fit: contain;">
        <span style="font-family: 'Dancing Script', cursive; font-size: 40px;"><b><?php echo $_SESSION['setting_name']; ?></b></span>
    </a>
</a>

      <!-- Toggler for Mobile View -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Collapsible Navbar -->
<div class="collapse navbar-collapse" id="navbarNav">
  <ul class="navbar-nav ms-auto align-items-center">
    <!-- Notifications for Orders -->
    <li class="nav-item">
      <a href="index.php?page=orders" class="nav-link notification-link" style="font-size: 16px;">
        <div class="notification-icon">
          <i class="fa fa-bell"></i>
          <span class="notification-badge"><?php echo $newOrdersCount; ?></span>
        </div>
      </a>
    </li>
    <!-- Notifications for Messages -->
    <li class="nav-item">
      <a href="index.php?page=message" class="nav-link notification-link" style="font-size: 16px;">
        <div class="notification-icon">
          <i class="fa fa-envelope"></i>
          <span class="notification-badge"><?php echo $unreadMessagesCount; ?></span>
        </div>
      </a>
    </li>
    <!-- Admin Dropdown -->
    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="#" role="button" id="adminDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 16px;">
        <?php echo $_SESSION['login_name']; ?>
      </a>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown">
        <!-- <li><a class="dropdown-item" href="admin_profile.php">Profile</a></li> -->
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="ajax.php?action=logout">Logout <i class="fa fa-sign-out-alt"></i></a></li>
        </ul>
    </li>
  </ul>
</div>

        </ul>
      </div>
    </div>
  </nav>

  <!-- Include Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
