<?php 
include 'admin/db_connect.php';

// Default limit and pagination
$limit = 10;
$page = (isset($_GET['_page']) && $_GET['_page'] > 0) ? $_GET['_page'] - 1 : 0;
$offset = $page > 0 ? $page * $limit : 0;

// Get search parameter
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Update status to 'unavailable' for products with 0 stock
$conn->query("UPDATE product_list SET status = 'unavailable' WHERE stock = 0");

// Modify query based on search parameter
$search_query = $search ? "WHERE name LIKE '%$search%' OR description LIKE '%$search%'" : '';
$qry = $conn->query("
    SELECT 
        id, 
        name, 
        description, 
        img_path, 
        size, 
        size_unit, 
        price, 
        stock, 
        CASE 
            WHEN stock = 0 THEN 'unavailable'
            ELSE status
        END AS status 
    FROM product_list 
    $search_query 
    ORDER BY name ASC 
    LIMIT $limit OFFSET $offset
");

// Get total count of items based on search
$total_count_query = $conn->query("SELECT id FROM product_list $search_query");
$all_menu = $total_count_query->num_rows;
$page_btn_count = ceil($all_menu / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Menu Page</title>
    <link rel="stylesheet" href="path/to/bootstrap.min.css">
    <link rel="stylesheet" href="path/to/font-awesome.min.css">
    <link rel="stylesheet" href="path/to/custom-styles.css">
    <script src="path/to/jquery.min.js"></script>
    <script src="path/to/bootstrap.min.js"></script>
    <script src="path/to/sweetalert2.all.min.js"></script>
    <style>
        .steps {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .step-item {
            color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
    text-align: center;
}
        .step-item h4 {
            font-size: 2rem;
            margin-top: 10px;
        }
        .step-item i {
            font-size: 3rem;
            color: white;
            margin-bottom: 10px;
            transition: transform 0.3s;
        }
        .step-item i:hover {
            transform: scale(1.1);
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fa-bounce { animation: bounce 2s infinite; }
        .fa-beat { animation: beat 2s infinite; }
        .fa-spin { animation: spin 2s infinite; }
        body, html { overflow-x: hidden; }
        .container, .steps, .input-group, .card-deck { width: 100%; margin: 0 auto; padding: 0 10px; }
        @media (max-width: 768px) {
            .masthead h1 { font-size: 2rem; }
            .step-item h4 { font-size: 1.5rem; }
            .step-item i { font-size: 2.5rem; }
            .btn-group .btn { font-size: 0.9rem; padding: 6px; }
            .card-deck .col-lg-3 { width: 100%; margin-bottom: 1rem; }
        }
        .search-input {
    background-color: #f8f9fa;
    color: #333;
    border: 1px solid #ddd;
}

.search-button {
    background-color: #6c757d;
    color: #fff;
    border-color: #6c757d;
}

.search-button:hover {
    background-color: #5a6268;
    color: #fff;
}
.enhanced-button {
    background: linear-gradient(45deg, #ff7e5f, #feb47b);
    color: #fff;
    border: none;
    font-weight: bold;
    padding: 10px 20px;
    transition: all 0.3s ease;
    border-radius: 5px; /* Slightly rounded corners */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
}

.enhanced-button:hover {
    background: linear-gradient(45deg, #feb47b, #ff7e5f); /* Reverse gradient on hover */
    transform: translateY(-2px); /* Slight hover lift */
    box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15); /* Enhanced shadow */
}
/* Modern Rounded Search Bar */
.modern-search-bar {
    display: flex;
    align-items: center;
    width: 100%;
    box-shadow: 0 4px 6px #2c3bf0;
    overflow: hidden;
    background-color: #f8f9fa;
    padding: 5px;
    border-radius: 50px; /* Fully rounded */
}

.modern-input {
    border: none;
    padding: 10px 20px;
    font-size: 1rem;
    flex: 1; /* Ensures the input takes the remaining space */
    outline: none;
    border-radius: 50px; /* Matches the parent container */
    background-color: #fff;
    color: #333;
    transition: box-shadow 0.3s ease, background-color 0.3s ease;
}

.modern-input:focus {
    background-color: #fff;
    box-shadow: 0 0 8px rgba(0, 123, 255, 0.25);
}

.modern-button {
    background: linear-gradient(45deg, #007bff, #0056b3);
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 50px; /* Fully rounded */
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modern-button:hover {
    background: linear-gradient(45deg, #0056b3, #007bff);
    transform: scale(1.05);
}

.modern-button i {
    font-size: 1rem;
}
/* Base Styles for Step Items */
.steps {
  font-size: 24px;
  text-align: center;
  color: #444; /* Set a default text color */
}

.step-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  transition: transform 0.3s ease;
  padding: 20px;
}

/* Icon Base Styles */
.step-item i {
  font-size: 50px;
  margin-bottom: 10px;
  transition: color 0.3s ease, transform 0.3s ease;
}

/* Hover Effects for Icons */
.step-item:hover i {
  transform: scale(1.1);
}

.step-item:hover h4 {
  color:	#0df2f2; /* Change text color on hover */
}

/* Enhanced Icon Colors */
.search-icon {
  color: #ff6347; /* Tomato Red */
  transition: color 0.3s ease;
}

.cart-icon {
  color: #32cd32; /* Lime Green */
  transition: color 0.3s ease;
}

.truck-icon {
  color: #ffa500; /* Orange */
  transition: color 0.3s ease;
}

/* Hover Color Change */
.step-item:hover .search-icon {
  color: #ff4500; /* Darker red on hover */
}

.step-item:hover .cart-icon {
  color: #228b22; /* Darker green on hover */
}

.step-item:hover .truck-icon {
  color: #ff8c00; /* Darker orange on hover */
}

/* Animations for Icons (keep these from the previous code) */

/* Search Icon Animation */
@keyframes searchMove {
  0% { transform: translateX(0); }
  50% { transform: translateX(-20px); }
  100% { transform: translateX(0); }
}

.search-icon {
  animation: searchMove 1.5s ease-in-out infinite;
}

/* Cart Icon Animation */
@keyframes cartMove {
  0% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
  100% { transform: translateY(0); }
}

.cart-icon {
  animation: cartMove 1s ease-in-out infinite;
}

/* Truck Icon Animation */
@keyframes truckRun {
  0% { transform: translateX(0); }
  50% { transform: translateX(30px); }
  100% { transform: translateX(0); }
}

.truck-icon {
  animation: truckRun 2s ease-in-out infinite;
}

/* Enhance Divider Color */
.divider {
  border: 0;
  height: 2px;
  background: linear-gradient(to right, #443c3b, #a54622, #4c5258); /* Tomato gradient */
  margin-top: 2rem;
  margin-bottom: 2rem;
}

/* Style for the 'Order Now' Button */
.btn {
  font-size: 18px;
  font-weight: bold;
  padding: 15px 30px;
  border-radius: 30px;
  text-transform: uppercase;
  color: # #668cff; /* White text */
  background-color: #333; /* Dark background */
  transition: background-color 0.3s ease, transform 0.3s ease;
  text-decoration: none; /* Remove underline */
}

/* Hover Effect for Button */
.btn:hover {
  background-color:   #7575a3; /* Change to Tomato Red on hover */
  transform: scale(1.05); /* Slightly enlarge the button on hover */
  cursor: pointer; /* Hand cursor on hover */
}

/* Focus Effect (optional for accessibility) */
.btn:focus {
  outline: none; /* Remove focus outline */
}

/* Add Hand Cursor on Button Hover */
.btn:hover {
  cursor: pointer; /* Hand cursor */
}

/* Optional: Focus Effect for Accessibility */
.btn:focus {
  outline: 2px solid #47476b; /* Focus with Tomato Red border */
  outline-offset: 2px;
}/* Style for the View Button */
.btn-outline-dark {
    color: #343a40; /* Default text color (dark) */
    background-color: transparent; /* Transparent background */
    border-color: #dee2e6; /* Light border color */
}

/* Hover Effect */
.btn-outline-dark:hover {
    color: #fff; /* White text color on hover */
    background-color: #7e8791; /* Background color on hover */
    border-color: #dee2e6; /* Border color on hover */
}

/* Optional: Active State (when button is clicked) */
.btn-outline-dark:active {
    color: #fff; /* White text color when active */
    background-color: #6c757d; /* Darker background on active */
    border-color: #dee2e6; /* Border color remains same */
}

/* Ensure the buttons are centered and responsive */
.paginate-btns {
    display: flex;
    justify-content: center;
    flex-wrap: wrap; /* This will allow pagination buttons to wrap on smaller screens */
    gap: 5px; /* Adds spacing between pagination buttons */
}

.paginate-btns .btn {
    padding: 8px 12px; /* Adjust button padding for mobile */
    font-size: 14px; /* Adjust font size for mobile */
}

@media (max-width: 576px) {
    /* Adjust pagination buttons for smaller screens */
    .paginate-btns .btn {
        padding: 6px 10px; /* Smaller padding for mobile */
        font-size: 12px; /* Smaller font size for mobile */
    }
}

    </style>
</head>
<body>
    <header class="masthead">
        <div class="container h-100">
            <div class="row h-100 align-items-center justify-content-center text-center">
                <div class="col-lg-10 align-self-center mb-4 page-title">
            
                <h1 class="text-white">Welcome to <?php echo htmlspecialchars(isset($_SESSION['setting_name']) ? $_SESSION['setting_name'] : 'M&M Cake Ordering System'); ?></h1>
                    <div class="steps d-flex justify-content-around mt-5">
    <div class="step-item">
        <i class="fas fa-search search-icon"></i>
        <h4>Browse</h4>
    </div>
    <div class="step-item">
        <i class="fas fa-shopping-cart cart-icon"></i>
        <h4>Order</h4>
    </div>
    <div class="step-item">
        <i class="fas fa-truck truck-icon"></i>
        <h4>Deliver</h4>
    </div>
</div>
<!-- Adding Hand Cursor on Hover -->
<a class="btn btn-dark bg-black btn-xl js-scroll-trigger" href="#menu" style="cursor: pointer;">
    <i class="fa fa-shopping-cart mr-2"></i> Order Now
</a>


        </div>
    </header>

    <section class="page-section" id="menu">
        <h1 class="text-center text-cursive" style="font-size:3em"><b>Menu</b></h1>
        <div class="d-flex justify-content-center">
            <hr class="border-dark" width="5%">
        </div>
        <div class="container">
    <form method="GET" action="">
        <div class="input-group mb-3 modern-search-bar">
            <input 
                type="text" 
                class="form-control modern-input" 
                placeholder="Search for cakes..." 
                name="search" 
                value="<?php echo htmlspecialchars($search); ?>">
            <div class="input-group-append">
            <button 
                    class="btn modern-button" 
                    type="submit">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </div>
    </form>
</div>

<br>
<br>
        <div id="menu-field" class="card-deck mt-2">
            <?php while ($row = $qry->fetch_assoc()): ?>
            <div class="col-lg-3 mb-3">
                <div class="card menu-item rounded-0">
                    <div class="position-relative overflow-hidden" id="item-img-holder">
                        <img src="assets/img/<?php echo htmlspecialchars($row['img_path']); ?>" class="card-img-top" alt="Cake Image">
                    </div>
                    <div class="card-body rounded-0">
                        <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                        <p class="card-text truncate"><?php echo htmlspecialchars($row['description']); ?></p>
                        <p class="card-text">Size: <?php echo htmlspecialchars($row['size']) . ' ' . htmlspecialchars($row['size_unit']); ?></p>
                        <p class="card-text">Price: <?php echo htmlspecialchars($row['price']); ?></p>
                        <p class="card-text">Availability: <?php echo htmlspecialchars($row['status']); ?><br>Stock: <?php echo htmlspecialchars($row['stock']); ?></p>
                        <div class="text-center">
                            <button class="btn btn-sm btn-outline-dark view_prod btn-block" data-id="<?php echo htmlspecialchars($row['id']); ?>"><i class="fa fa-eye"></i> View</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
            <div class="w-100 mx-4 d-flex justify-content-center">
    <div class="btn-group paginate-btns flex-wrap justify-content-center">
        <!-- Disable Prev button if on the first page -->
        <a class="btn btn-default border border-dark text-gray" 
           <?php echo ($page == 0 || $page_btn_count == 0) ? 'disabled' : ''; ?> 
           href="./?_page=<?php echo ($page); ?>&search=<?php echo urlencode($search); ?>">Prev.</a>
        
        <!-- Page Number Buttons -->
        <?php for ($i = 1; $i <= $page_btn_count; $i++): ?>
            <a class="btn btn-default border border-dark text-white <?php echo ($i == ($page + 1)) ? 'active' : ''; ?>" 
               href="./?_page=<?php echo $i ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        
        <!-- Disable Next button if on the last page -->
        <a class="btn btn-default border border-dark text-gray" 
           <?php echo (($page + 1) == $page_btn_count || $page_btn_count == 0) ? 'disabled' : ''; ?> 
           href="./?_page=<?php echo ($page + 2); ?>&search=<?php echo urlencode($search); ?>">Next</a>
    </div>
</div>



    </section>

    <script>
        $(document).ready(function() {
            $('.step-item').each(function(index) {
                $(this).css('animation-delay', (index * 0.1) + 's');
            });

            $('.view_prod').click(function() {
                uni_modal_right('Product Details', 'view_prod.php?id=' + $(this).attr('data-id'));
            });

            <?php if (isset($_GET['_page'])): ?>
                $(function() {
                    document.querySelector('html').scrollTop = $('#menu').offset().top - 100;
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>
