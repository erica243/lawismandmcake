<?php 
include 'admin/db_connect.php';
session_start();

// Handle form submission for ratings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_rating'])) {
    $product_id = $_POST['product_id'];
    $rating = $_POST['rating'];
    $feedback = $_POST['feedback'];
    $user = $_SESSION['user_id'];

    // Insert rating and feedback into the database
    $stmt = $conn->prepare("INSERT INTO product_ratings (product_id, user_id, rating, feedback) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $product_id, $user, $rating, $feedback);

    if ($stmt->execute()) {
        $success_message = 'Your rating has been submitted.';
    } else {
        $error_message = 'There was an error submitting your rating.';
    }
}

// Fetch product details
$product_id = intval($_GET['id']);
$qry = $conn->query("SELECT * FROM product_list WHERE id = $product_id")->fetch_array();

// Fetch average rating
$rating_qry = $conn->query("SELECT AVG(rating) as avg_rating FROM product_ratings WHERE product_id = $product_id");
$avg_rating = $rating_qry->fetch_assoc()['avg_rating'];
$avg_rating = $avg_rating ? number_format($avg_rating, 1) : 'No ratings yet';

// Fetch all ratings and feedback for the product, along with the user's email from user_info table
$feedback_qry = $conn->query("
    SELECT pr.rating, pr.feedback, ui.email 
    FROM product_ratings pr
    JOIN user_info ui ON pr.user_id = ui.user_id
    WHERE pr.product_id = $product_id
");

$feedbacks = $feedback_qry->fetch_all(MYSQLI_ASSOC);

// Check product availability
$availability = $qry['status'];
$stock_quantity = $qry['stock'];

function display_star_rating($rating) {
    $full_stars = floor($rating);
    $half_star = ($rating - $full_stars >= 0.5) ? 1 : 0;
    $empty_stars = 5 - ($full_stars + $half_star);

    for ($i = 0; $i < $full_stars; $i++) {
        echo '<i class="fas fa-star" style="color: #ffd700;"></i>';
    }
    if ($half_star) {
        echo '<i class="fas fa-star-half-alt" style="color: #ffd700;"></i>';
    }
    for ($i = 0; $i < $empty_stars; $i++) {
        echo '<i class="far fa-star" style="color: #ffd700;"></i>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .star { cursor: pointer; font-size: 2rem; color: #ddd; }
        .star.selected { color: #ffd700; }
        .btn.disabled { opacity: 0.65; cursor: not-allowed; }
        .cart-count { position: relative; font-size: 1.2rem; color: red; }
        
        <style>
        .steps {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .step-item {
            text-align: center;
            color: white;
            opacity: 0;
            transform: translateY(20px);
            animation: slideIn 0.5s forwards;
            margin: 0 15px;
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
  background-color: white; /* Dark background */
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
 .btn:hover {
        background-color: #6c757d; /* Light gray color */
        border-color: #6c757d;     /* Border color matches the background */
    }

    .btn:disabled:hover {
        background-color: #f8f9fa; /* Background stays white for disabled buttons */
        border-color: #f8f9fa;     /* Border stays white for disabled buttons */
    }


    </style>
</head>
<body>
<div class="container-fluid mt-4">
   
    <div class="card">
        <img src="assets/img/<?php echo htmlspecialchars($qry['img_path']) ?>" class="card-img-top" alt="Product Image">
        <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($qry['name']) ?></h5>
            <p class="card-text"><?php echo htmlspecialchars($qry['description']) ?></p>
            <p class="card-text">Price: <?php echo number_format($qry['price'], 2) ?></p>
            <p class="card-text">Stock: <span id="stock_display"><?php echo $stock_quantity; ?></span> available</p>
            
            <p class="card-text">Average Rating: 
                <?php if ($avg_rating !== 'No ratings yet'): ?>
                    <?php display_star_rating($avg_rating); ?> (<?php echo $avg_rating; ?> / 5)
                <?php else: ?>
                    No ratings yet
                <?php endif; ?>
            </p>
            
            <div class="row mb-3" >
                <div class="col-md-2">
                    <label class="control-label">Qty</label>
                </div>
                <div class="input-group col-md-7">
                    <div class="input-group-prepend">
                    <button class="btn btn-outline-secondary" type="button" style="height: 38px; display: flex; justify-content: center; align-items: center;" id="qty-minus" <?php echo $stock_quantity <= 0 ? 'disabled' : ''; ?>>
    <span class="fa fa-minus"></span>
</button>

                    </div>
                    <input type="number" id="qty-input" readonly value="1" min="1" max="<?php echo $stock_quantity; ?>" class="form-control text-center" name="qty" <?php echo $stock_quantity <= 0 ? 'disabled' : ''; ?>>
                    <div class="input-group-append">
                        <button class="btn btn-outline-dark" type="button"  style="height: 38px; display: flex; justify-content: center; align-items: center;"id="qty-plus" <?php echo $stock_quantity <= 0 ? 'disabled' : ''; ?>>
                            <span class="fa fa-plus"></span>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="text-center mb-4">
                <button 
                    class="btn btn-outline-dark btn-sm btn-block <?php echo !$availability || $stock_quantity <= 0 ? 'disabled' : ''; ?>" 
                    id="add_to_cart_modal" 
                    data-availability="<?php echo $availability; ?>" 
                    data-stock="<?php echo $stock_quantity; ?>"
                    <?php echo !$availability || $stock_quantity <= 0 ? 'disabled' : ''; ?>
                >
                    <i class="fa fa-cart-plus"></i> <?php echo ($availability && $stock_quantity > 0) ? 'Add to Cart' : 'Unavailable'; ?>
                </button>
            </div>
        </div>
    </div>

    <h5 class="mt-4">User Ratings and Feedback</h5>
    <?php if ($feedbacks): ?>
        <div class="list-group">
            <?php foreach ($feedbacks as $feedback): ?>
                <div class="list-group-item">
                    <h6 class="mb-1">Rating: 
                        <?php display_star_rating($feedback['rating']); ?> 
                        (<?php echo htmlspecialchars($feedback['rating']); ?> / 5)
                    </h6>
                    <p><?php echo htmlspecialchars($feedback['feedback']); ?></p>
                    <small>Submitted by: <?php echo htmlspecialchars($feedback['email']); ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No feedback available yet.</p>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
   $(document).ready(function() {
        // Load initial cart count
        updateCartCount();

        // Quantity adjustment
        $('#qty-minus').click(function(){
            var qty = $('input[name="qty"]').val();
            if (qty > 1) {
                $('input[name="qty"]').val(parseInt(qty) - 1);
            }
        });

        $('#qty-plus').click(function(){
            var qty = $('input[name="qty"]').val();
            var maxQty = <?php echo $stock_quantity; ?>;
            if (qty < maxQty) {
                $('input[name="qty"]').val(parseInt(qty) + 1);
            } else {
                Swal.fire('Limit Reached', 'You have reached the maximum quantity available.', 'warning');
            }
        });

        $('#add_to_cart_modal').click(function(){
    var availability = $(this).data('availability');
    var stock = $(this).data('stock');
    
    if (!availability || stock <= 0) {
        Swal.fire('Unavailable', 'This product is currently unavailable or out of stock.', 'warning');
        return;
    }
    
    Swal.fire({
        title: 'Add to Cart',
        text: 'Are you sure you want to add this item to your cart?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, add it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'admin/ajax.php?action=add_to_cart',
                method: 'POST',
                data: { pid: '<?php echo $product_id ?>', qty: $('input[name="qty"]').val() },
                success: function(resp) {
                    if (resp == 1) {
                        let currentStock = parseInt($('#stock_display').text());
                        let qty = parseInt($('input[name="qty"]').val());
                        $('#stock_display').text(currentStock - qty); // Update stock display on the page
                        Swal.fire('Added!', 'The product has been added to your cart.', 'success').then(() => {
                            location.reload(); // Refresh the page
                        });
                        
                        // Fetch updated cart count
                        updateCartCount();
                    } else {
                        Swal.fire('Error!', 'There was an error adding the product to your cart.', 'error');
                    }
                }
            });
        }
    });
});

        // Function to update the cart count
        function updateCartCount() {
            $.ajax({
                url: 'admin/ajax.php?action=get_cart_count',
                method: 'GET',
                success: function(resp) {
                    $('#cart_count').text(resp); // Update the cart count element
                }
            });
        }
    });
</script>
</body>
</html>
