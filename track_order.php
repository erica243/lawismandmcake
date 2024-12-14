<?php
session_start();
include('admin/db_connect.php');

if (!isset($_SESSION['login_user_id'])) {
    die("User not logged in.");
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$user_id = $_SESSION['login_user_id'];

$query = "SELECT o.*, 
          DATE_FORMAT(o.order_date, '%M %d, %Y %h:%i %p') as formatted_order_date,
          DATE_FORMAT(o.status_updated_at, '%M %d, %Y %h:%i %p') as last_update,
          DATE_FORMAT(o.estimated_delivery, '%M %d, %Y') as delivery_date,
          o.proof_of_delivery
          FROM orders o
          JOIN user_info u ON u.email = o.email
          WHERE o.id = ? AND u.user_id = ?";


$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    die("Order not found or access denied.");
}

$stages = [
    'pending' => [
        'title' => 'Order Pending',
        'description' => 'Your order is awaiting confirmation.',
        'icon' => 'clock',
        'matches' => ['pending', 'Pending']
    ],
    'confirmed' => [
        'title' => 'Order Confirmed',
        'description' => 'We have confirmed your order and started processing.',
        'icon' => 'check-circle',
        'matches' => ['confirmed', 'Confirmed']
    ],
    'preparing' => [
        'title' => 'Preparing',
        'description' => 'Your cake is being freshly baked and decorated.',
        'icon' => 'clock',
        'matches' => ['preparing', 'Preparing', 'in preparation', 'In Preparation']
    ],
    'ready' => [
        'title' => 'Ready for Delivery/Pickup',
        'description' => 'Your order is ready and waiting for delivery or pickup.',
        'icon' => 'package',
        'matches' => ['ready', 'Ready', 'ready for delivery', 'Ready for Delivery', 'ready for pickup', 'Ready for Pickup']
    ],
    'in_transit' => [
        'title' => 'In Transit',
        'description' => 'Your order is on its way to you.',
        'icon' => 'truck',
        'matches' => ['in_transit', 'in transit', 'In Transit', 'out for delivery', 'Out for Delivery']
    ],
    'delivered' => [
        'title' => 'Delivered',
        'description' => 'Your order has been delivered successfully.',
        'icon' => 'check-square',
        'matches' => ['delivered', 'Delivered', 'completed', 'Completed']
    ]
];

function normalizeStatus($status, $stages) {
    if (empty($status)) return 'pending';
    
    foreach ($stages as $key => $stage) {
        if (in_array($status, $stage['matches'], true)) {
            return $key;
        }
    }
    return 'pending';
}

$current_stage = normalizeStatus($order['delivery_status'], $stages);
$stages_array = array_keys($stages);
$current_index = array_search($current_stage, $stages_array);

$total_stages = count($stages) - 1;
$progress_percentage = ($current_index / $total_stages) * 100;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order #<?php echo htmlspecialchars($order['order_number']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.css" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .tracking-line {
            position: absolute;
            top: 24px;
            left: 24px;
            width: calc(100% - 48px);
            height: 2px;
            background-color: #E5E7EB;
            z-index: 0;
        }
        
        .tracking-line-progress {
            height: 100%;
            background-color: #3B82F6;
            transition: width 0.5s ease-in-out;
        }

        .stage-icon {
            z-index: 1;
            background-color: white;
        }

        .stage-icon:hover {
            background-color: #3B82F6;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .tracking-line-progress {
            animation: progressBar 1s ease-out forwards;
        }

        @keyframes progressBar {
            from {
                width: 0;
            }
            to {
                width: <?php echo $progress_percentage . '%'; ?>;
            }
        }

        .order-info p,
        .tracking-notes p {
            line-height: 1.5;
        }

        .stage-title {
            font-size: 1rem;
            color: #2d3748;
        }

        .stage-description {
            font-size: 0.875rem;
            color: #718096;
        }

        .stage-icon i {
            transition: all 0.3s ease-in-out;
        }

        .stage-icon.completed i {
            color: white;
        }
        
        .tracking-line-progress {
            transition: width 0.5s ease-in-out;
        }

        .tracking-line-progress[data-progress="100%"] {
            background-color: #34D399; /* Delivered - green */
        }

        .text-white {
            --tw-text-opacity: 1;
            color: #444a57;
        }

        @media (max-width: 640px) {
            .tracking-line {
                top: 0;
                left: 0;
                width: 100%;
            }

            .tracking-line-progress {
                height: 4px;
            }

            .tracking-line-progress[data-progress="100%"] {
                background-color: #34D399; /* Delivered - green */
            }

            .flex-col {
                flex-direction: column;
            }

            .stage-icon {
                margin-bottom: 1rem;
            }

            .order-info,
            .delivery-info {
                margin-bottom: 1.5rem;
            }
        }
        
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-6">
            <a href="my_orders.php" class="text-blue-600 hover:text-blue-800 flex items-center">
                <i data-feather="arrow-left" class="w-4 h-4 mr-2"></i>
                Back to My Orders
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h1 class="text-3xl font-bold text-center text-gray-900 mb-4">Track Order #<?php echo htmlspecialchars($order['order_number']); ?></h1>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                <div class="order-info">
                    <h2 class="text-gray-600 font-semibold mb-4">Order Information</h2>
                    <p><strong>Ordered:</strong> <?php echo htmlspecialchars($order['formatted_order_date']); ?></p>
                    <p><strong>Last Updated:</strong> <?php echo htmlspecialchars($order['last_update']); ?></p>
                    <?php if ($order['estimated_delivery']): ?>
                        <p><strong>Estimated Delivery:</strong> <?php echo htmlspecialchars($order['delivery_date']); ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="delivery-info">
                    <h2 class="text-gray-600 font-semibold mb-4">Delivery Details</h2>
                    <p><strong>Delivery Method:</strong> <?php echo htmlspecialchars($order['delivery_method']); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                    <p><strong>Contact:</strong> <?php echo htmlspecialchars($order['mobile']); ?></p>
                </div>
            </div>

            <div class="relative mb-8">
                <div class="tracking-line">
                    <div class="tracking-line-progress" data-progress="<?php echo $progress_percentage; ?>" style="width: <?php echo $progress_percentage . '%'; ?>"></div>
                </div>

                <div class="flex flex-wrap justify-between relative space-y-6 sm:space-y-0 sm:flex-row sm:space-x-4">
                    <?php foreach ($stages as $stage_key => $stage): 
                        $stage_index = array_search($stage_key, $stages_array);
                        $is_completed = $stage_index <= $current_index;
                        $is_current = $stage_key === $current_stage;
                    ?>
                    <div class="flex flex-col items-center w-full sm:w-32">
                        <div class="stage-icon rounded-full p-3 <?php echo $is_completed ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-400'; ?> <?php echo $is_current ? 'ring-4 ring-blue-300' : ''; ?>">
                            <i data-feather="<?php echo $stage['icon']; ?>" class="w-8 h-8"></i>
                        </div>
                        <div class="text-center mt-4">
                            <p class="font-semibold text-sm <?php echo $is_completed ? 'text-blue-600' : 'text-gray-400'; ?>"><?php echo htmlspecialchars($stage['title']); ?></p>
                            <p class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($stage['description']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php
if ($order['proof_of_delivery']) {
    $proofOfDelivery = 'admin/uploads/' . htmlspecialchars($order['proof_of_delivery']);
    
    // Check if the image exists before displaying it
    if (file_exists($proofOfDelivery)) {
        // Display the proof of delivery image
        echo '<img src="' . $proofOfDelivery . '" alt="Proof of Delivery" class="img-thumbnail" style="max-width: 100px;">';
    } else {
        echo '<p class="text-gray-500">No proof of delivery available.</p>';
    }
} else {
    // If proof_of_delivery is not set or is null
    echo '<p class="text-gray-500">No proof of delivery uploaded.</p>';
}
?>


       
<div id="proofImageModal" class="modal fixed inset-0 bg-gray-800 bg-opacity-75 flex justify-center items-center hidden z-50">
    <div class="modal-content bg-white p-4 rounded-lg shadow-lg max-w-4xl w-full">
        <button onclick="closeModal('#proofImageModal')" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-2">X</button>
        <img id="modalImage" class="w-full max-h-[80vh] object-contain" alt="Full Proof of Delivery">
    </div>
</div> 

            <?php if ($order['tracking_notes']): ?>
            <div class="tracking-notes bg-gray-50 rounded-lg p-6">
                <h2 class="text-gray-600 font-semibold mb-4">Tracking Notes</h2>
                <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($order['tracking_notes'])); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Open Modal with the clicked image
    function openModal(modalId) {
        const modal = document.querySelector(modalId);
        const imageSrc = event.target.src; // Get the source of the clicked image
        const modalImage = modal.querySelector('#modalImage');
        modalImage.src = imageSrc; // Set the image source for the modal

        modal.classList.remove('hidden'); // Show the modal
    }

    // Close the Modal
    function closeModal(modalId) {
        const modal = document.querySelector(modalId);
        modal.classList.add('hidden'); // Hide the modal
    }
        feather.replace();
    </script>
</body>
</html>
