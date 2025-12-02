<?php
namespace KateAI\Controllers;

/**
 * Message Controller - User-to-User Chat System
 * Handles sending, receiving, and managing messages between users
 */
class MessageController {
    private $db_manager;
    private $logger;
    
    public function __construct($db_manager = null, $logger = null) {
        $this->db_manager = $db_manager;
        $this->logger = $logger;
    }
    
    /**
     * Send a message to another user
     */
    public function sendMessage($sender_id, $recipient_id, $message) {
        if (empty($message) || $sender_id == $recipient_id) {
            return ['success' => false, 'error' => 'Invalid message'];
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'rtf_platform_messages';
        
        // Verify recipient exists and allows messages
        $recipient = $wpdb->get_row($wpdb->prepare(
            "SELECT u.id, p.allow_messages 
             FROM {$wpdb->prefix}rtf_platform_users u
             LEFT JOIN {$wpdb->prefix}rtf_platform_privacy p ON p.user_id = u.id
             WHERE u.id = %d AND u.is_active = 1",
            $recipient_id
        ));
        
        if (!$recipient) {
            return ['success' => false, 'error' => 'Recipient not found'];
        }
        
        if (isset($recipient->allow_messages) && !$recipient->allow_messages) {
            return ['success' => false, 'error' => 'Recipient does not accept messages'];
        }
        
        // Insert message
        $result = $wpdb->insert($table, [
            'sender_id' => $sender_id,
            'recipient_id' => $recipient_id,
            'message' => sanitize_textarea_field($message),
            'read_status' => 0,
            'created_at' => current_time('mysql')
        ]);
        
        if ($result) {
            $message_id = $wpdb->insert_id;
            
            if ($this->logger) {
                $this->logger->info("Message sent from user $sender_id to user $recipient_id");
            }
            
            return [
                'success' => true,
                'message_id' => $message_id,
                'created_at' => current_time('mysql')
            ];
        }
        
        return ['success' => false, 'error' => 'Failed to send message'];
    }
    
    /**
     * Get conversation between two users
     */
    public function getConversation($user_id, $other_user_id, $limit = 50, $offset = 0) {
        global $wpdb;
        $table = $wpdb->prefix . 'rtf_platform_messages';
        $users_table = $wpdb->prefix . 'rtf_platform_users';
        
        $messages = $wpdb->get_results($wpdb->prepare(
            "SELECT m.*, 
                    s.username as sender_username,
                    s.profile_image as sender_image,
                    r.username as recipient_username,
                    r.profile_image as recipient_image
             FROM $table m
             LEFT JOIN $users_table s ON s.id = m.sender_id
             LEFT JOIN $users_table r ON r.id = m.recipient_id
             WHERE (m.sender_id = %d AND m.recipient_id = %d)
                OR (m.sender_id = %d AND m.recipient_id = %d)
             ORDER BY m.created_at DESC
             LIMIT %d OFFSET %d",
            $user_id, $other_user_id,
            $other_user_id, $user_id,
            $limit, $offset
        ));
        
        // Reverse to show oldest first
        $messages = array_reverse($messages);
        
        // Mark messages as read
        $wpdb->update(
            $table,
            ['read_status' => 1],
            [
                'recipient_id' => $user_id,
                'sender_id' => $other_user_id,
                'read_status' => 0
            ],
            ['%d'],
            ['%d', '%d', '%d']
        );
        
        return [
            'success' => true,
            'messages' => $messages,
            'count' => count($messages)
        ];
    }
    
    /**
     * Get list of conversations for a user
     */
    public function getConversationList($user_id, $limit = 20) {
        global $wpdb;
        $table = $wpdb->prefix . 'rtf_platform_messages';
        $users_table = $wpdb->prefix . 'rtf_platform_users';
        
        // Get unique conversation partners with last message
        $conversations = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                CASE 
                    WHEN m.sender_id = %d THEN m.recipient_id
                    ELSE m.sender_id
                END as other_user_id,
                u.username as other_username,
                u.profile_image as other_profile_image,
                u.full_name as other_full_name,
                MAX(m.created_at) as last_message_time,
                (SELECT message FROM $table m2 
                 WHERE ((m2.sender_id = %d AND m2.recipient_id = other_user_id)
                        OR (m2.sender_id = other_user_id AND m2.recipient_id = %d))
                 ORDER BY m2.created_at DESC LIMIT 1) as last_message,
                (SELECT COUNT(*) FROM $table m3
                 WHERE m3.recipient_id = %d 
                   AND m3.sender_id = other_user_id 
                   AND m3.read_status = 0) as unread_count
             FROM $table m
             LEFT JOIN $users_table u ON u.id = CASE 
                WHEN m.sender_id = %d THEN m.recipient_id 
                ELSE m.sender_id 
             END
             WHERE m.sender_id = %d OR m.recipient_id = %d
             GROUP BY other_user_id
             ORDER BY last_message_time DESC
             LIMIT %d",
            $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $limit
        ));
        
        return [
            'success' => true,
            'conversations' => $conversations,
            'count' => count($conversations)
        ];
    }
    
    /**
     * Get unread message count for user
     */
    public function getUnreadCount($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'rtf_platform_messages';
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table 
             WHERE recipient_id = %d AND read_status = 0",
            $user_id
        ));
        
        return [
            'success' => true,
            'unread_count' => (int)$count
        ];
    }
    
    /**
     * Mark messages as read
     */
    public function markAsRead($user_id, $other_user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'rtf_platform_messages';
        
        $result = $wpdb->update(
            $table,
            ['read_status' => 1],
            [
                'recipient_id' => $user_id,
                'sender_id' => $other_user_id,
                'read_status' => 0
            ],
            ['%d'],
            ['%d', '%d', '%d']
        );
        
        return [
            'success' => true,
            'marked_count' => $result
        ];
    }
    
    /**
     * Delete a message (only sender can delete)
     */
    public function deleteMessage($message_id, $user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'rtf_platform_messages';
        
        // Verify ownership
        $message = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $message_id
        ));
        
        if (!$message || $message->sender_id != $user_id) {
            return ['success' => false, 'error' => 'Unauthorized'];
        }
        
        $result = $wpdb->delete($table, ['id' => $message_id], ['%d']);
        
        return [
            'success' => $result !== false,
            'deleted' => $result > 0
        ];
    }
    
    /**
     * Search users to start conversation
     */
    public function searchUsers($user_id, $query, $limit = 10) {
        global $wpdb;
        $users_table = $wpdb->prefix . 'rtf_platform_users';
        $privacy_table = $wpdb->prefix . 'rtf_platform_privacy';
        
        $search = '%' . $wpdb->esc_like($query) . '%';
        
        $users = $wpdb->get_results($wpdb->prepare(
            "SELECT u.id, u.username, u.full_name, u.profile_image, p.allow_messages
             FROM $users_table u
             LEFT JOIN $privacy_table p ON p.user_id = u.id
             WHERE u.is_active = 1 
               AND u.id != %d
               AND (u.username LIKE %s OR u.full_name LIKE %s)
             LIMIT %d",
            $user_id, $search, $search, $limit
        ));
        
        return [
            'success' => true,
            'users' => $users,
            'count' => count($users)
        ];
    }
    
    /**
     * Get new messages since timestamp (for polling)
     */
    public function getNewMessages($user_id, $since_timestamp) {
        global $wpdb;
        $table = $wpdb->prefix . 'rtf_platform_messages';
        $users_table = $wpdb->prefix . 'rtf_platform_users';
        
        $messages = $wpdb->get_results($wpdb->prepare(
            "SELECT m.*, 
                    u.username as sender_username,
                    u.profile_image as sender_image
             FROM $table m
             LEFT JOIN $users_table u ON u.id = m.sender_id
             WHERE m.recipient_id = %d 
               AND m.created_at > %s
             ORDER BY m.created_at ASC",
            $user_id,
            $since_timestamp
        ));
        
        return [
            'success' => true,
            'messages' => $messages,
            'count' => count($messages)
        ];
    }
}
