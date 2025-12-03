<?php
/**
 * RTF User System - Production-Ready User Management
 * 
 * Handles ALL user operations: registration, authentication, subscription management
 * Single source of truth for user data and operations
 * 
 * @version 2.0.0
 * @author Ret til Familie
 */

class RtfUserSystem {
    
    private $wpdb;
    private $table_users;
    private $table_privacy;
    private $table_payments;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_users = $wpdb->prefix . 'rtf_platform_users';
        $this->table_privacy = $wpdb->prefix . 'rtf_platform_privacy';
        $this->table_payments = $wpdb->prefix . 'rtf_stripe_payments';
    }
    
    /**
     * Register new user
     * 
     * @param array $data User registration data
     * @return array ['success' => bool, 'user_id' => int|null, 'error' => string|null]
     */
    public function register($data) {
        // Validate required fields
        $required = ['username', 'email', 'password', 'full_name', 'birthday', 'phone'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return [
                    'success' => false,
                    'error' => "Missing required field: $field"
                ];
            }
        }
        
        // Sanitize inputs
        $username = sanitize_text_field($data['username']);
        $email = sanitize_email($data['email']);
        $full_name = sanitize_text_field($data['full_name']);
        $birthday = sanitize_text_field($data['birthday']);
        $phone = sanitize_text_field($data['phone']);
        $bio = isset($data['bio']) ? sanitize_textarea_field($data['bio']) : '';
        $language = isset($data['language_preference']) ? sanitize_text_field($data['language_preference']) : 'da_DK';
        
        // Validate email format
        if (!is_email($email)) {
            return [
                'success' => false,
                'error' => 'Invalid email format'
            ];
        }
        
        // Validate username (alphanumeric + underscore, 3-50 chars)
        if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
            return [
                'success' => false,
                'error' => 'Username must be 3-50 characters (letters, numbers, underscore only)'
            ];
        }
        
        // Validate password strength (min 8 chars)
        if (strlen($data['password']) < 8) {
            return [
                'success' => false,
                'error' => 'Password must be at least 8 characters'
            ];
        }
        
        // Check for duplicates
        $exists = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT id, username, email FROM {$this->table_users} WHERE username = %s OR email = %s",
            $username,
            $email
        ));
        
        if ($exists) {
            if ($exists->username === $username) {
                return ['success' => false, 'error' => 'Username already exists'];
            }
            if ($exists->email === $email) {
                return ['success' => false, 'error' => 'Email already registered'];
            }
        }
        
        // Map language to country
        $country_map = [
            'da_DK' => 'DK',
            'sv_SE' => 'SE',
            'en_US' => 'INTL'
        ];
        $country = $country_map[$language] ?? 'DK';
        
        // Hash password
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Prepare user data (ONLY fields that exist in table)
        $user_data = [
            'username' => $username,
            'email' => $email,
            'password' => $password_hash,
            'full_name' => $full_name,
            'birthday' => $birthday,
            'phone' => $phone,
            'language_preference' => $language,
            'country' => $country,
            'subscription_status' => 'inactive',
            'is_admin' => 0,
            'is_active' => 1,
            'created_at' => current_time('mysql')
        ];
        
        // Add optional bio if provided
        if (!empty($bio)) {
            $user_data['bio'] = $bio;
        }
        
        // Insert user
        $insert_result = $this->wpdb->insert($this->table_users, $user_data);
        
        if ($insert_result === false) {
            // Log database error
            error_log('RTF Registration Error: ' . $this->wpdb->last_error);
            error_log('RTF Registration Data: ' . print_r($user_data, true));
            
            return [
                'success' => false,
                'error' => 'Database error: Could not create user account'
            ];
        }
        
        $user_id = $this->wpdb->insert_id;
        
        // Create privacy settings
        $privacy_result = $this->wpdb->insert($this->table_privacy, [
            'user_id' => $user_id,
            'gdpr_anonymize_birthday' => 1,
            'profile_visibility' => 'all',
            'show_in_forum' => 1,
            'allow_messages' => 1,
            'created_at' => current_time('mysql')
        ]);
        
        if ($privacy_result === false) {
            error_log('RTF Privacy Creation Error for user_id ' . $user_id . ': ' . $this->wpdb->last_error);
            // Don't fail registration, just log it
        }
        
        // Log successful registration
        error_log("RTF Registration Success: User $username (ID: $user_id, Email: $email)");
        
        return [
            'success' => true,
            'user_id' => $user_id,
            'username' => $username,
            'email' => $email
        ];
    }
    
    /**
     * Authenticate user
     * 
     * @param string $username_or_email Username or email
     * @param string $password Plain text password
     * @return array ['success' => bool, 'user' => object|null, 'error' => string|null]
     */
    public function authenticate($username_or_email, $password) {
        if (empty($username_or_email) || empty($password)) {
            return [
                'success' => false,
                'error' => 'Username/email and password are required'
            ];
        }
        
        // Find user by username OR email
        $user = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->table_users} WHERE (username = %s OR email = %s) AND is_active = 1",
            $username_or_email,
            $username_or_email
        ));
        
        if (!$user) {
            // User not found or inactive
            error_log("RTF Login Failed: User not found or inactive - $username_or_email");
            return [
                'success' => false,
                'error' => 'Invalid credentials or account inactive'
            ];
        }
        
        // Verify password
        if (!password_verify($password, $user->password)) {
            error_log("RTF Login Failed: Wrong password for user {$user->username} (ID: {$user->id})");
            return [
                'success' => false,
                'error' => 'Invalid credentials'
            ];
        }
        
        // Successful authentication
        error_log("RTF Login Success: User {$user->username} (ID: {$user->id})");
        
        return [
            'success' => true,
            'user' => $user
        ];
    }
    
    /**
     * Get user by ID
     * Always returns fresh data from database - NO CACHING
     * 
     * @param int $user_id User ID
     * @return object|null User object or null if not found
     */
    public function get_user($user_id) {
        $user = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->table_users} WHERE id = %d AND is_active = 1",
            $user_id
        ));
        
        return $user;
    }
    
    /**
     * Get user by email
     * 
     * @param string $email Email address
     * @return object|null User object or null if not found
     */
    public function get_user_by_email($email) {
        $user = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->table_users} WHERE email = %s",
            $email
        ));
        
        return $user;
    }
    
    /**
     * Activate subscription for user
     * 
     * @param int $user_id User ID
     * @param string $stripe_customer_id Stripe customer ID
     * @param int $days_valid Number of days subscription is valid (default 30)
     * @return bool Success
     */
    public function activate_subscription($user_id, $stripe_customer_id = null, $days_valid = 30) {
        $end_date = date('Y-m-d H:i:s', strtotime("+{$days_valid} days"));
        
        $update_data = [
            'subscription_status' => 'active',
            'subscription_end_date' => $end_date
        ];
        
        if ($stripe_customer_id) {
            $update_data['stripe_customer_id'] = $stripe_customer_id;
        }
        
        $result = $this->wpdb->update(
            $this->table_users,
            $update_data,
            ['id' => $user_id],
            ['%s', '%s', '%s'],
            ['%d']
        );
        
        if ($result === false) {
            error_log("RTF Subscription Activation FAILED for user_id $user_id: " . $this->wpdb->last_error);
            return false;
        }
        
        error_log("RTF Subscription Activation SUCCESS for user_id $user_id until $end_date");
        return true;
    }
    
    /**
     * Activate subscription by email (for webhook)
     * 
     * @param string $email User email
     * @param string $stripe_customer_id Stripe customer ID
     * @param int $days_valid Number of days subscription is valid (default 30)
     * @return bool Success
     */
    public function activate_subscription_by_email($email, $stripe_customer_id = null, $days_valid = 30) {
        $end_date = date('Y-m-d H:i:s', strtotime("+{$days_valid} days"));
        
        $update_data = [
            'subscription_status' => 'active',
            'subscription_end_date' => $end_date
        ];
        
        if ($stripe_customer_id) {
            $update_data['stripe_customer_id'] = $stripe_customer_id;
        }
        
        $result = $this->wpdb->update(
            $this->table_users,
            $update_data,
            ['email' => $email],
            ['%s', '%s', '%s'],
            ['%s']
        );
        
        if ($result === false) {
            error_log("RTF Subscription Activation FAILED for email $email: " . $this->wpdb->last_error);
            return false;
        }
        
        if ($result === 0) {
            error_log("RTF Subscription Activation: No user found with email $email");
            return false;
        }
        
        error_log("RTF Subscription Activation SUCCESS for email $email until $end_date");
        return true;
    }
    
    /**
     * Log payment transaction
     * 
     * @param array $data Payment data
     * @return bool Success
     */
    public function log_payment($data) {
        $payment_data = [
            'user_id' => $data['user_id'] ?? null,
            'stripe_customer_id' => $data['stripe_customer_id'] ?? null,
            'stripe_subscription_id' => $data['stripe_subscription_id'] ?? null,
            'amount' => $data['amount'] ?? 0,
            'currency' => $data['currency'] ?? 'DKK',
            'status' => $data['status'] ?? 'completed',
            'payment_intent_id' => $data['payment_intent_id'] ?? null,
            'created_at' => current_time('mysql')
        ];
        
        $result = $this->wpdb->insert($this->table_payments, $payment_data);
        
        if ($result === false) {
            error_log('RTF Payment Log Error: ' . $this->wpdb->last_error);
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if user has active subscription
     * 
     * @param int $user_id User ID
     * @return bool Has active subscription
     */
    public function has_active_subscription($user_id) {
        $user = $this->get_user($user_id);
        
        if (!$user) {
            return false;
        }
        
        // Admin always has access
        if ($user->is_admin) {
            return true;
        }
        
        // Check subscription status
        if ($user->subscription_status !== 'active') {
            return false;
        }
        
        // Check if subscription has expired
        if ($user->subscription_end_date) {
            $end_timestamp = strtotime($user->subscription_end_date);
            if ($end_timestamp < time()) {
                // Expired - update status
                $this->wpdb->update(
                    $this->table_users,
                    ['subscription_status' => 'expired'],
                    ['id' => $user_id]
                );
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Delete user and all related data (Admin function)
     * 
     * @param int $user_id User ID to delete
     * @return array ['success' => bool, 'message' => string]
     */
    public function delete_user($user_id) {
        $user_id = intval($user_id);
        
        if (!$user_id) {
            return [
                'success' => false,
                'message' => 'Invalid user ID'
            ];
        }
        
        // Check if user exists
        $user = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT id, username, email FROM {$this->table_users} WHERE id = %d",
            $user_id
        ));
        
        if (!$user) {
            error_log("RTF UserSystem: Attempted to delete non-existent user ID $user_id");
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }
        
        error_log("RTF UserSystem: Starting deletion of user ID $user_id ({$user->username})");
        
        // Define all tables with user data
        $posts_table = $this->wpdb->prefix . 'rtf_platform_posts';
        $forum_table = $this->wpdb->prefix . 'rtf_platform_forum';
        $messages_table = $this->wpdb->prefix . 'rtf_platform_messages';
        $friends_table = $this->wpdb->prefix . 'rtf_platform_friends';
        $shares_table = $this->wpdb->prefix . 'rtf_platform_shares';
        $likes_table = $this->wpdb->prefix . 'rtf_platform_likes';
        $comments_table = $this->wpdb->prefix . 'rtf_platform_comments';
        
        // Delete all related data (cascade delete)
        $deleted_counts = [];
        
        // Messages (sent and received)
        $deleted_counts['messages_sent'] = $this->wpdb->delete($messages_table, ['sender_id' => $user_id]);
        $deleted_counts['messages_received'] = $this->wpdb->delete($messages_table, ['recipient_id' => $user_id]);
        
        // Forum posts
        $deleted_counts['forum_posts'] = $this->wpdb->delete($forum_table, ['user_id' => $user_id]);
        
        // Platform posts
        $deleted_counts['posts'] = $this->wpdb->delete($posts_table, ['user_id' => $user_id]);
        
        // Friends/connections
        $this->wpdb->delete($friends_table, ['user_id' => $user_id]);
        $this->wpdb->delete($friends_table, ['friend_id' => $user_id]);
        
        // Shares
        $this->wpdb->delete($shares_table, ['user_id' => $user_id]);
        
        // Likes
        $this->wpdb->delete($likes_table, ['user_id' => $user_id]);
        
        // Comments
        $this->wpdb->delete($comments_table, ['user_id' => $user_id]);
        
        // Payments (keep for records but could be deleted)
        // $this->wpdb->delete($this->table_payments, ['user_id' => $user_id]);
        
        // Privacy settings
        $this->wpdb->delete($this->table_privacy, ['user_id' => $user_id]);
        
        // Finally delete user
        $deleted = $this->wpdb->delete($this->table_users, ['id' => $user_id]);
        
        if ($deleted) {
            error_log("RTF UserSystem: Successfully deleted user ID $user_id ({$user->username}, {$user->email})");
            error_log("RTF UserSystem: Deleted data - " . json_encode($deleted_counts));
            return [
                'success' => true,
                'message' => 'User and all related data deleted successfully'
            ];
        } else {
            error_log("RTF UserSystem: FAILED to delete user ID $user_id - DB Error: " . $this->wpdb->last_error);
            return [
                'success' => false,
                'message' => 'Database error: Could not delete user'
            ];
        }
    }
    
    /**
     * Update user subscription status (Admin function)
     * 
     * @param int $user_id User ID
     * @param string $status New status (active, inactive, expired, canceled, past_due)
     * @param int|null $days_valid Days to add to current date (optional)
     * @return array ['success' => bool, 'message' => string]
     */
    public function admin_update_subscription($user_id, $status, $days_valid = null) {
        $user_id = intval($user_id);
        $status = sanitize_text_field($status);
        
        $valid_statuses = ['active', 'inactive', 'expired', 'canceled', 'past_due'];
        if (!in_array($status, $valid_statuses)) {
            return [
                'success' => false,
                'message' => 'Invalid status. Must be: ' . implode(', ', $valid_statuses)
            ];
        }
        
        $update_data = ['subscription_status' => $status];
        
        // If setting to active and days provided, calculate end date
        if ($status === 'active' && $days_valid) {
            $end_date = date('Y-m-d H:i:s', strtotime("+{$days_valid} days"));
            $update_data['subscription_end_date'] = $end_date;
        }
        
        $updated = $this->wpdb->update(
            $this->table_users,
            $update_data,
            ['id' => $user_id]
        );
        
        if ($updated !== false) {
            error_log("RTF UserSystem: Admin updated user $user_id subscription to $status");
            return [
                'success' => true,
                'message' => 'Subscription updated successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to update subscription'
        ];
    }
    
    /**
     * Get all users with filters (Admin function)
     * 
     * @param array $filters ['status' => string, 'search' => string, 'limit' => int, 'offset' => int]
     * @return array ['success' => bool, 'users' => array, 'total' => int]
     */
    public function admin_get_users($filters = []) {
        $where = ["1=1"];
        $params = [];
        
        // Filter by subscription status
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $where[] = "subscription_status = %s";
            $params[] = $filters['status'];
        }
        
        // Search by username or email
        if (!empty($filters['search'])) {
            $where[] = "(username LIKE %s OR email LIKE %s OR full_name LIKE %s)";
            $search = '%' . $this->wpdb->esc_like($filters['search']) . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        $where_clause = implode(' AND ', $where);
        
        // Count total
        $count_sql = "SELECT COUNT(*) FROM {$this->table_users} WHERE $where_clause";
        if (!empty($params)) {
            $count_sql = $this->wpdb->prepare($count_sql, $params);
        }
        $total = $this->wpdb->get_var($count_sql);
        
        // Get users with pagination
        $limit = isset($filters['limit']) ? intval($filters['limit']) : 20;
        $offset = isset($filters['offset']) ? intval($filters['offset']) : 0;
        
        $sql = "SELECT * FROM {$this->table_users} WHERE $where_clause ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $params[] = $limit;
        $params[] = $offset;
        
        $users = $this->wpdb->get_results($this->wpdb->prepare($sql, $params));
        
        return [
            'success' => true,
            'users' => $users ?: [],
            'total' => intval($total)
        ];
    }
}
