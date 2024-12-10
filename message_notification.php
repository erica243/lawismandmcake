<?php
require_once 'admin/db_connect.php';

class MessageNotification {
    private $conn;

    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    // Send a new message
    public function sendMessage($user_id, $order_number, $message, $photo_path = null) {
        $query = "INSERT INTO messages 
            (user_id, email, order_number, message, photo_path) 
            VALUES (?, 
                (SELECT email FROM users WHERE user_id = ?), 
                ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iisss", $user_id, $user_id, $order_number, $message, $photo_path);
        
        return $stmt->execute();
    }

    // Admin reply to a message
    public function adminReply($message_id, $admin_reply) {
        $query = "UPDATE messages 
            SET admin_reply = ?, 
                reply_date = NOW(), 
                status = 1, 
                is_read = 0 
            WHERE message_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $admin_reply, $message_id);
        
        return $stmt->execute();
    }

    // Fetch unread messages with admin replies
    public function getUnreadMessagesWithReplies($user_id) {
        $query = "SELECT 
            message_id,
            order_number, 
            message, 
            admin_reply, 
            reply_date, 
            status 
            FROM messages 
            WHERE user_id = ? 
            AND is_read = 0 
            AND admin_reply IS NOT NULL 
            ORDER BY reply_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Mark messages as read
    public function markMessagesAsRead($user_id) {
        $query = "UPDATE messages 
            SET is_read = 1 
            WHERE user_id = ? 
            AND is_read = 0 
            AND admin_reply IS NOT NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    }

    // Display notifications
    public function displayNotifications($user_id) {
        $messages = $this->getUnreadMessagesWithReplies($user_id);
        
        if (empty($messages)) {
            echo "<div class='no-notifications'>No new notifications</div>";
            return 0;
        }

        echo "<div class='notification-container'>";
        echo "<h3>Your Notifications</h3>";
        
        $unread_count = 0;
        foreach ($messages as $message) {
            $unread_count++;
            echo "<div class='notification-item' data-message-id='" . 
                 htmlspecialchars($message['message_id']) . "'>";
            echo "<div class='notification-header'>";
            echo "<strong>Order #" . htmlspecialchars($message['order_number']) . "</strong>";
            echo "<span class='notification-date'>" . 
                 htmlspecialchars(date('M d, Y H:i', strtotime($message['reply_date']))) . 
                 "</span>";
            echo "</div>";
            
            echo "<div class='notification-content'>";
            echo "<p><strong>Your Original Message:</strong> " . 
                 htmlspecialchars($message['message']) . "</p>";
            echo "<p><strong>Admin Reply:</strong> " . 
                 htmlspecialchars($message['admin_reply']) . "</p>";
            echo "</div>";
            echo "</div>";
        }
        
        echo "</div>";

        // Automatically mark as read after displaying
        $this->markMessagesAsRead($user_id);

        return $unread_count;
    }

    // Get total unread notification count
    public function getUnreadNotificationCount($user_id) {
        $query = "SELECT COUNT(*) as unread_count 
            FROM messages 
            WHERE user_id = ? 
            AND is_read = 0 
            AND admin_reply IS NOT NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $row = $result->fetch_assoc();
        return $row['unread_count'];
    }
}
?>