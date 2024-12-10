<?php
include('db_connect.php');

if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    // Prepare and execute the select statement
    $stmt = $conn->prepare("SELECT * FROM user_info WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // CSS styles for the user details
        echo '
        <style>
            .user-details {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                margin: 10px;
                padding: 15px;
                border: 1px solid #ddd;
                border-radius: 5px;
                background-color: #f9f9f9;
            }
            .user-details strong {
                color: #333;
            }
            .user-details div {
                margin-bottom: 10px;
            }
            .user-details label {
                font-weight: bold;
            }
            .back-btn, .close-btn {
                margin-bottom: 10px;
                text-decoration: none;
                color: #007bff;
                font-size: 16px;
                border: none;
                background: none;
                cursor: pointer;
                padding: 0;
            }
            .back-btn:hover, .close-btn:hover {
                text-decoration: underline;
            }
            .close-btn {
                color: red; /* Red color for close button */
                float: right; /* Align it to the right */
            }
        </style>
        ';

        // Displaying user details
        echo '<div class="user-details">';
        echo "<div><strong>First Name:</strong> " . htmlspecialchars($row['first_name']) . "</div>";
        echo "<div><strong>Last Name:</strong> " . htmlspecialchars($row['last_name']) . "</div>";
        echo "<div><strong>Email:</strong> " . htmlspecialchars($row['email']) . "</div>";
        echo "<div><strong>Mobile:</strong> " . htmlspecialchars($row['mobile']) . "</div>";
        echo "<div><strong>Address:</strong> " . htmlspecialchars($row['address']) . "</div>";
        echo '</div>';
    } else {
        echo "User not found.";
    }

    $stmt->close();
    $conn->close();
}
?>

<script>
    function closeDetails() {
        // Close the user details view (you can customize this to hide a specific section)
        window.history.back(); // Navigates back to the previous page
    }
</script>
