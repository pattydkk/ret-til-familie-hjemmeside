<?php

namespace KateAI\Controllers;

/**
 * ShareController
 * 
 * Handles content sharing functionality across platform.
 * Users can share posts, news articles, and forum topics to their wall.
 */
class ShareController {
    private $db_manager;
    private $logger;
    
    public function __construct($db_manager, $logger = null) {
        $this->db_manager = $db_manager;
        $this->logger = $logger;
    }
    
    /**
     * Share content to user's wall
     * 
     * @param int $user_id Current user ID
     * @param string $source_type Content type (post|news|forum)
     * @param int $source_id Content ID
     * @return array Result with success status
     */
    public function shareContent($user_id, $source_type, $source_id) {
        global $wpdb;
        
        try {
            // Validate inputs
            if (empty($user_id) || empty($source_type) || empty($source_id)) {
                return [
                    'success' => false,
                    'error' => 'Manglende påkrævede felter' // Missing required fields
                ];
            }
            
            // Validate source type
            $valid_types = ['post', 'news', 'forum'];
            if (!in_array($source_type, $valid_types)) {
                return [
                    'success' => false,
                    'error' => 'Ugyldig indholdstype' // Invalid content type
                ];
            }
            
            // Verify content exists and get original user
            $original_user_id = $this->getOriginalUser($source_type, $source_id);
            if (!$original_user_id) {
                return [
                    'success' => false,
                    'error' => 'Indhold ikke fundet' // Content not found
                ];
            }
            
            // Check if already shared
            $table = $wpdb->prefix . 'rtf_platform_shares';
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table 
                 WHERE user_id = %d AND source_type = %s AND source_id = %d",
                $user_id, $source_type, $source_id
            ));
            
            if ($existing) {
                return [
                    'success' => false,
                    'error' => 'Du har allerede delt dette indhold' // Already shared
                ];
            }
            
            // Insert share
            $result = $wpdb->insert(
                $table,
                [
                    'user_id' => $user_id,
                    'source_type' => $source_type,
                    'source_id' => $source_id,
                    'original_user_id' => $original_user_id,
                    'created_at' => current_time('mysql')
                ],
                ['%d', '%s', '%d', '%d', '%s']
            );
            
            if ($result === false) {
                if ($this->logger) {
                    $this->logger->error('Failed to insert share', [
                        'user_id' => $user_id,
                        'error' => $wpdb->last_error
                    ]);
                }
                
                return [
                    'success' => false,
                    'error' => 'Kunne ikke dele indhold' // Could not share
                ];
            }
            
            return [
                'success' => true,
                'share_id' => $wpdb->insert_id,
                'message' => 'Indhold delt til din væg' // Shared to your wall
            ];
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Share error: ' . $e->getMessage());
            }
            
            return [
                'success' => false,
                'error' => 'Der opstod en fejl' // An error occurred
            ];
        }
    }
    
    /**
     * Get user's shared content
     * 
     * @param int $user_id User ID
     * @param int $limit Number of shares to retrieve
     * @param int $offset Offset for pagination
     * @return array Result with shares
     */
    public function getShares($user_id, $limit = 20, $offset = 0) {
        global $wpdb;
        
        try {
            $table = $wpdb->prefix . 'rtf_platform_shares';
            
            $shares = $wpdb->get_results($wpdb->prepare(
                "SELECT s.*, 
                        u.username, u.full_name, u.email,
                        orig.username as original_username, 
                        orig.full_name as original_full_name
                 FROM $table s
                 LEFT JOIN {$wpdb->prefix}rtf_platform_users u ON s.user_id = u.id
                 LEFT JOIN {$wpdb->prefix}rtf_platform_users orig ON s.original_user_id = orig.id
                 WHERE s.user_id = %d
                 ORDER BY s.created_at DESC
                 LIMIT %d OFFSET %d",
                $user_id, $limit, $offset
            ), ARRAY_A);
            
            // Enrich shares with content details
            $enriched_shares = [];
            foreach ($shares as $share) {
                $content = $this->getContentDetails($share['source_type'], $share['source_id']);
                $share['content'] = $content;
                $enriched_shares[] = $share;
            }
            
            return [
                'success' => true,
                'shares' => $enriched_shares,
                'count' => count($enriched_shares)
            ];
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Get shares error: ' . $e->getMessage());
            }
            
            return [
                'success' => false,
                'error' => 'Kunne ikke hente delinger' // Could not retrieve shares
            ];
        }
    }
    
    /**
     * Delete share (unshare)
     * 
     * @param int $share_id Share ID
     * @param int $user_id Current user ID (for ownership check)
     * @return array Result with success status
     */
    public function deleteShare($share_id, $user_id) {
        global $wpdb;
        
        try {
            $table = $wpdb->prefix . 'rtf_platform_shares';
            
            // Verify ownership
            $share = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table WHERE id = %d",
                $share_id
            ), ARRAY_A);
            
            if (!$share) {
                return [
                    'success' => false,
                    'error' => 'Deling ikke fundet' // Share not found
                ];
            }
            
            if ($share['user_id'] != $user_id) {
                return [
                    'success' => false,
                    'error' => 'Ikke autoriseret' // Not authorized
                ];
            }
            
            // Delete share
            $result = $wpdb->delete(
                $table,
                ['id' => $share_id],
                ['%d']
            );
            
            if ($result === false) {
                return [
                    'success' => false,
                    'error' => 'Kunne ikke slette deling' // Could not delete
                ];
            }
            
            return [
                'success' => true,
                'deleted' => true,
                'message' => 'Deling fjernet' // Share removed
            ];
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Delete share error: ' . $e->getMessage());
            }
            
            return [
                'success' => false,
                'error' => 'Der opstod en fejl' // An error occurred
            ];
        }
    }
    
    /**
     * Get all shared content for wall feed
     * 
     * @param int $user_id User ID (for friends/following filter)
     * @param int $limit Number of shares to retrieve
     * @param int $offset Offset for pagination
     * @return array Result with shares from network
     */
    public function getFeedShares($user_id, $limit = 20, $offset = 0) {
        global $wpdb;
        
        try {
            $table = $wpdb->prefix . 'rtf_platform_shares';
            
            // Get shares from user's network (for now, all public shares)
            // TODO: Add friends/following filter when friend system is implemented
            $shares = $wpdb->get_results($wpdb->prepare(
                "SELECT s.*, 
                        u.username, u.full_name, u.email,
                        orig.username as original_username, 
                        orig.full_name as original_full_name
                 FROM $table s
                 LEFT JOIN {$wpdb->prefix}rtf_platform_users u ON s.user_id = u.id
                 LEFT JOIN {$wpdb->prefix}rtf_platform_users orig ON s.original_user_id = orig.id
                 WHERE u.is_active = 1
                 ORDER BY s.created_at DESC
                 LIMIT %d OFFSET %d",
                $limit, $offset
            ), ARRAY_A);
            
            // Enrich shares with content details
            $enriched_shares = [];
            foreach ($shares as $share) {
                $content = $this->getContentDetails($share['source_type'], $share['source_id']);
                if ($content) { // Only include if content still exists
                    $share['content'] = $content;
                    $enriched_shares[] = $share;
                }
            }
            
            return [
                'success' => true,
                'shares' => $enriched_shares,
                'count' => count($enriched_shares)
            ];
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Get feed shares error: ' . $e->getMessage());
            }
            
            return [
                'success' => false,
                'error' => 'Kunne ikke hente feed' // Could not retrieve feed
            ];
        }
    }
    
    /**
     * Get original user ID for content
     * 
     * @param string $source_type Content type
     * @param int $source_id Content ID
     * @return int|null Original user ID or null if not found
     */
    private function getOriginalUser($source_type, $source_id) {
        global $wpdb;
        
        switch ($source_type) {
            case 'post':
                $table = $wpdb->prefix . 'rtf_platform_posts';
                return $wpdb->get_var($wpdb->prepare(
                    "SELECT user_id FROM $table WHERE id = %d",
                    $source_id
                ));
                
            case 'news':
                $table = $wpdb->prefix . 'rtf_platform_news';
                return $wpdb->get_var($wpdb->prepare(
                    "SELECT author_id FROM $table WHERE id = %d",
                    $source_id
                ));
                
            case 'forum':
                $table = $wpdb->prefix . 'rtf_platform_forum_topics';
                return $wpdb->get_var($wpdb->prepare(
                    "SELECT user_id FROM $table WHERE id = %d",
                    $source_id
                ));
                
            default:
                return null;
        }
    }
    
    /**
     * Get content details for enrichment
     * 
     * @param string $source_type Content type
     * @param int $source_id Content ID
     * @return array|null Content data or null if not found
     */
    private function getContentDetails($source_type, $source_id) {
        global $wpdb;
        
        switch ($source_type) {
            case 'post':
                $table = $wpdb->prefix . 'rtf_platform_posts';
                $post = $wpdb->get_row($wpdb->prepare(
                    "SELECT p.*, u.username, u.full_name 
                     FROM $table p
                     LEFT JOIN {$wpdb->prefix}rtf_platform_users u ON p.user_id = u.id
                     WHERE p.id = %d",
                    $source_id
                ), ARRAY_A);
                
                if ($post) {
                    $post['type'] = 'post';
                }
                return $post;
                
            case 'news':
                $table = $wpdb->prefix . 'rtf_platform_news';
                $news = $wpdb->get_row($wpdb->prepare(
                    "SELECT n.*, u.username, u.full_name 
                     FROM $table n
                     LEFT JOIN {$wpdb->prefix}rtf_platform_users u ON n.author_id = u.id
                     WHERE n.id = %d",
                    $source_id
                ), ARRAY_A);
                
                if ($news) {
                    $news['type'] = 'news';
                }
                return $news;
                
            case 'forum':
                $table = $wpdb->prefix . 'rtf_platform_forum_topics';
                $topic = $wpdb->get_row($wpdb->prepare(
                    "SELECT t.*, u.username, u.full_name 
                     FROM $table t
                     LEFT JOIN {$wpdb->prefix}rtf_platform_users u ON t.user_id = u.id
                     WHERE t.id = %d",
                    $source_id
                ), ARRAY_A);
                
                if ($topic) {
                    $topic['type'] = 'forum';
                }
                return $topic;
                
            default:
                return null;
        }
    }
}
