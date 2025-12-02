<?php

namespace KateAI\Controllers;

/**
 * AdminController
 * 
 * Handles all admin panel operations including user management,
 * subscription control, content moderation, and analytics.
 */
class AdminController {
    private $db_manager;
    private $logger;
    
    public function __construct($db_manager, $logger = null) {
        $this->db_manager = $db_manager;
        $this->logger = $logger;
    }
    
    /**
     * Verify admin access
     * 
     * @param int $user_id User ID to check
     * @return bool True if user is admin
     */
    public function verifyAdmin($user_id) {
        global $wpdb;
        
        $user = $wpdb->get_row($wpdb->prepare(
            "SELECT is_admin FROM {$wpdb->prefix}rtf_platform_users WHERE id = %d",
            $user_id
        ));
        
        return $user && $user->is_admin == 1;
    }
    
    /**
     * Get all users with pagination
     * 
     * @param int $limit Number of users per page
     * @param int $offset Offset for pagination
     * @param string $search Search query
     * @return array Users list
     */
    public function getUsers($limit = 50, $offset = 0, $search = '') {
        global $wpdb;
        
        try {
            $table = $wpdb->prefix . 'rtf_platform_users';
            
            $where = "1=1";
            $params = [];
            
            if (!empty($search)) {
                $where .= " AND (username LIKE %s OR full_name LIKE %s OR email LIKE %s)";
                $search_term = '%' . $wpdb->esc_like($search) . '%';
                $params[] = $search_term;
                $params[] = $search_term;
                $params[] = $search_term;
            }
            
            $params[] = $limit;
            $params[] = $offset;
            
            $sql = "SELECT * FROM $table WHERE $where ORDER BY created_at DESC LIMIT %d OFFSET %d";
            
            $users = $wpdb->get_results($wpdb->prepare($sql, $params), ARRAY_A);
            
            $total = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE $where");
            
            return [
                'success' => true,
                'users' => $users,
                'total' => $total,
                'count' => count($users)
            ];
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Get users error: ' . $e->getMessage());
            }
            
            return [
                'success' => false,
                'error' => 'Kunne ikke hente brugere'
            ];
        }
    }
    
    /**
     * Get user details
     * 
     * @param int $user_id User ID
     * @return array User data
     */
    public function getUserDetails($user_id) {
        global $wpdb;
        
        try {
            $user = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}rtf_platform_users WHERE id = %d",
                $user_id
            ), ARRAY_A);
            
            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'Bruger ikke fundet'
                ];
            }
            
            // Get user statistics
            $posts_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}rtf_platform_posts WHERE user_id = %d",
                $user_id
            ));
            
            $messages_sent = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}rtf_platform_messages WHERE sender_id = %d",
                $user_id
            ));
            
            $kate_sessions = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}rtf_kate_chat_sessions WHERE user_id = %d",
                $user_id
            ));
            
            $user['statistics'] = [
                'posts_count' => $posts_count,
                'messages_sent' => $messages_sent,
                'kate_sessions' => $kate_sessions
            ];
            
            return [
                'success' => true,
                'user' => $user
            ];
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Get user details error: ' . $e->getMessage());
            }
            
            return [
                'success' => false,
                'error' => 'Kunne ikke hente brugerdetaljer'
            ];
        }
    }
    
    /**
     * Update user
     * 
     * @param int $user_id User ID
     * @param array $data Update data
     * @return array Result
     */
    public function updateUser($user_id, $data) {
        global $wpdb;
        
        try {
            $allowed_fields = ['full_name', 'email', 'is_active', 'is_admin', 'subscription_status', 'bio', 'language_preference', 'country'];
            $update_data = [];
            
            foreach ($data as $key => $value) {
                if (in_array($key, $allowed_fields)) {
                    $update_data[$key] = $value;
                }
            }
            
            if (empty($update_data)) {
                return [
                    'success' => false,
                    'error' => 'Ingen gyldige felter at opdatere'
                ];
            }
            
            $result = $wpdb->update(
                $wpdb->prefix . 'rtf_platform_users',
                $update_data,
                ['id' => $user_id]
            );
            
            if ($result === false) {
                return [
                    'success' => false,
                    'error' => 'Opdatering mislykkedes'
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Bruger opdateret'
            ];
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Update user error: ' . $e->getMessage());
            }
            
            return [
                'success' => false,
                'error' => 'Der opstod en fejl'
            ];
        }
    }
    
    /**
     * Delete user (soft delete - deactivate)
     * 
     * @param int $user_id User ID
     * @return array Result
     */
    public function deleteUser($user_id) {
        global $wpdb;
        
        try {
            // Soft delete - just deactivate
            $result = $wpdb->update(
                $wpdb->prefix . 'rtf_platform_users',
                ['is_active' => 0],
                ['id' => $user_id]
            );
            
            if ($result === false) {
                return [
                    'success' => false,
                    'error' => 'Sletning mislykkedes'
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Bruger deaktiveret'
            ];
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Delete user error: ' . $e->getMessage());
            }
            
            return [
                'success' => false,
                'error' => 'Der opstod en fejl'
            ];
        }
    }
    
    /**
     * Block user
     * 
     * @param int $user_id User ID
     * @param string $reason Block reason
     * @return array Result
     */
    public function blockUser($user_id, $reason = '') {
        global $wpdb;
        
        try {
            $result = $wpdb->update(
                $wpdb->prefix . 'rtf_platform_users',
                [
                    'is_active' => 0,
                    'subscription_status' => 'blocked'
                ],
                ['id' => $user_id]
            );
            
            if ($result === false) {
                return [
                    'success' => false,
                    'error' => 'Blokering mislykkedes'
                ];
            }
            
            // Log block action
            if ($this->logger) {
                $this->logger->info("User $user_id blocked. Reason: $reason");
            }
            
            return [
                'success' => true,
                'message' => 'Bruger blokeret'
            ];
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Block user error: ' . $e->getMessage());
            }
            
            return [
                'success' => false,
                'error' => 'Der opstod en fejl'
            ];
        }
    }
    
    /**
     * Activate subscription
     * 
     * @param int $user_id User ID
     * @param int $days Number of days to activate
     * @return array Result
     */
    public function activateSubscription($user_id, $days = 30) {
        global $wpdb;
        
        try {
            $expires_at = date('Y-m-d H:i:s', strtotime("+$days days"));
            
            $result = $wpdb->update(
                $wpdb->prefix . 'rtf_platform_users',
                [
                    'subscription_status' => 'active',
                    'subscription_expires_at' => $expires_at
                ],
                ['id' => $user_id]
            );
            
            if ($result === false) {
                return [
                    'success' => false,
                    'error' => 'Aktivering mislykkedes'
                ];
            }
            
            return [
                'success' => true,
                'message' => "Abonnement aktiveret i $days dage",
                'expires_at' => $expires_at
            ];
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Activate subscription error: ' . $e->getMessage());
            }
            
            return [
                'success' => false,
                'error' => 'Der opstod en fejl'
            ];
        }
    }
    
    /**
     * Get platform analytics
     * 
     * @return array Analytics data
     */
    public function getAnalytics() {
        global $wpdb;
        
        try {
            $analytics = [];
            
            // Total users
            $analytics['total_users'] = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->prefix}rtf_platform_users"
            );
            
            // Active users (logged in last 7 days)
            $analytics['active_users'] = $wpdb->get_var(
                "SELECT COUNT(DISTINCT user_id) FROM {$wpdb->prefix}rtf_kate_chat_sessions 
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
            );
            
            // Active subscriptions
            $analytics['active_subscriptions'] = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->prefix}rtf_platform_users 
                 WHERE subscription_status = 'active'"
            );
            
            // Total posts
            $analytics['total_posts'] = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->prefix}rtf_platform_posts"
            );
            
            // Total messages
            $analytics['total_messages'] = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->prefix}rtf_platform_messages"
            );
            
            // Kate AI sessions
            $analytics['kate_sessions'] = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->prefix}rtf_kate_chat_sessions"
            );
            
            // Recent registrations (last 30 days)
            $analytics['recent_registrations'] = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->prefix}rtf_platform_users 
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );
            
            // Language breakdown
            $analytics['language_breakdown'] = $wpdb->get_results(
                "SELECT language_preference, COUNT(*) as count 
                 FROM {$wpdb->prefix}rtf_platform_users 
                 GROUP BY language_preference",
                ARRAY_A
            );
            
            // Country breakdown
            $analytics['country_breakdown'] = $wpdb->get_results(
                "SELECT country, COUNT(*) as count 
                 FROM {$wpdb->prefix}rtf_platform_users 
                 GROUP BY country",
                ARRAY_A
            );
            
            return [
                'success' => true,
                'analytics' => $analytics
            ];
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Get analytics error: ' . $e->getMessage());
            }
            
            return [
                'success' => false,
                'error' => 'Kunne ikke hente statistik'
            ];
        }
    }
    
    /**
     * Moderate content (delete post/news/forum)
     * 
     * @param string $content_type Content type (post|news|forum)
     * @param int $content_id Content ID
     * @return array Result
     */
    public function moderateContent($content_type, $content_id) {
        global $wpdb;
        
        try {
            $table_map = [
                'post' => 'rtf_platform_posts',
                'news' => 'rtf_platform_news',
                'forum' => 'rtf_platform_forum_topics'
            ];
            
            if (!isset($table_map[$content_type])) {
                return [
                    'success' => false,
                    'error' => 'Ugyldig indholdstype'
                ];
            }
            
            $table = $wpdb->prefix . $table_map[$content_type];
            
            $result = $wpdb->delete($table, ['id' => $content_id]);
            
            if ($result === false) {
                return [
                    'success' => false,
                    'error' => 'Sletning mislykkedes'
                ];
            }
            
            // Log moderation action
            if ($this->logger) {
                $this->logger->info("Content moderated: $content_type #$content_id");
            }
            
            return [
                'success' => true,
                'message' => 'Indhold slettet'
            ];
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Moderate content error: ' . $e->getMessage());
            }
            
            return [
                'success' => false,
                'error' => 'Der opstod en fejl'
            ];
        }
    }
    
    /**
     * Create new admin
     * 
     * @param int $user_id User ID to promote
     * @param string $role Admin role
     * @param array $permissions Permissions array
     * @param int $created_by Creator user ID
     * @return array Result
     */
    public function createAdmin($user_id, $role = 'admin', $permissions = [], $created_by = 0) {
        global $wpdb;
        
        try {
            // Update user to admin
            $wpdb->update(
                $wpdb->prefix . 'rtf_platform_users',
                ['is_admin' => 1],
                ['id' => $user_id]
            );
            
            // Insert admin record
            $result = $wpdb->insert(
                $wpdb->prefix . 'rtf_platform_admins',
                [
                    'user_id' => $user_id,
                    'role' => $role,
                    'permissions' => json_encode($permissions),
                    'created_by' => $created_by,
                    'created_at' => current_time('mysql')
                ]
            );
            
            if ($result === false) {
                return [
                    'success' => false,
                    'error' => 'Oprettelse mislykkedes'
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Admin oprettet',
                'admin_id' => $wpdb->insert_id
            ];
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Create admin error: ' . $e->getMessage());
            }
            
            return [
                'success' => false,
                'error' => 'Der opstod en fejl'
            ];
        }
    }
}
