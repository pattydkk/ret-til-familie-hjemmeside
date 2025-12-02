<?php

namespace KateAI\Controllers;

/**
 * ReportController
 * 
 * Handles reports and analyses from Ret til Familie.
 * Users can browse, filter, and download legal, psychological, and social reports.
 */
class ReportController {
    private $db_manager;
    private $logger;
    
    public function __construct($db_manager, $logger = null) {
        $this->db_manager = $db_manager;
        $this->logger = $logger;
    }
    
    /**
     * Get all reports with filters
     * 
     * @param array $filters (country, city, case_type, report_type, language)
     * @param int $limit Number of reports
     * @param int $offset Pagination offset
     * @return array Result with reports
     */
    public function getReports($filters = [], $limit = 20, $offset = 0) {
        global $wpdb;
        
        try {
            $table = $wpdb->prefix . 'rtf_platform_reports';
            
            // Build WHERE clause
            $where_conditions = ['1=1'];
            $prepare_values = [];
            
            if (!empty($filters['country'])) {
                $where_conditions[] = 'country = %s';
                $prepare_values[] = $filters['country'];
            }
            
            if (!empty($filters['city'])) {
                $where_conditions[] = 'city = %s';
                $prepare_values[] = $filters['city'];
            }
            
            if (!empty($filters['case_type'])) {
                $where_conditions[] = 'case_type = %s';
                $prepare_values[] = $filters['case_type'];
            }
            
            if (!empty($filters['report_type'])) {
                $where_conditions[] = 'report_type = %s';
                $prepare_values[] = $filters['report_type'];
            }
            
            if (!empty($filters['language'])) {
                $where_conditions[] = 'language = %s';
                $prepare_values[] = $filters['language'];
            }
            
            $where_clause = implode(' AND ', $where_conditions);
            
            // Add limit and offset
            $prepare_values[] = $limit;
            $prepare_values[] = $offset;
            
            $query = "SELECT * FROM $table WHERE $where_clause ORDER BY published_date DESC LIMIT %d OFFSET %d";
            
            if (!empty($prepare_values)) {
                $reports = $wpdb->get_results($wpdb->prepare($query, $prepare_values), ARRAY_A);
            } else {
                $reports = $wpdb->get_results($query, ARRAY_A);
            }
            
            // Get total count for pagination
            $count_query = "SELECT COUNT(*) FROM $table WHERE $where_clause";
            if (count($prepare_values) > 2) {
                $total = $wpdb->get_var($wpdb->prepare($count_query, array_slice($prepare_values, 0, -2)));
            } else {
                $total = $wpdb->get_var($count_query);
            }
            
            return [
                'success' => true,
                'reports' => $reports,
                'count' => count($reports),
                'total' => (int)$total
            ];
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Get reports error: ' . $e->getMessage());
            }
            
            return [
                'success' => false,
                'error' => 'Kunne ikke hente rapporter'
            ];
        }
    }
    
    /**
     * Get report by ID
     * 
     * @param int $report_id Report ID
     * @return array Result with report data
     */
    public function getReport($report_id) {
        global $wpdb;
        
        try {
            $table = $wpdb->prefix . 'rtf_platform_reports';
            
            $report = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table WHERE id = %d",
                $report_id
            ), ARRAY_A);
            
            if (!$report) {
                return [
                    'success' => false,
                    'error' => 'Rapport ikke fundet'
                ];
            }
            
            // Increment download count
            $wpdb->update(
                $table,
                ['download_count' => $report['download_count'] + 1],
                ['id' => $report_id],
                ['%d'],
                ['%d']
            );
            
            return [
                'success' => true,
                'report' => $report
            ];
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Get report error: ' . $e->getMessage());
            }
            
            return [
                'success' => false,
                'error' => 'Kunne ikke hente rapport'
            ];
        }
    }
    
    /**
     * Upload new report (admin only)
     * 
     * @param array $data Report data
     * @param array $file Uploaded file
     * @return array Result with success status
     */
    public function uploadReport($data, $file) {
        global $wpdb;
        
        try {
            // Validate required fields
            $required = ['title', 'description', 'country', 'case_type', 'report_type', 'language'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return [
                        'success' => false,
                        'error' => "Manglende felt: $field"
                    ];
                }
            }
            
            // Handle file upload
            if (!empty($file) && $file['error'] === UPLOAD_ERR_OK) {
                $upload_dir = wp_upload_dir();
                $reports_dir = $upload_dir['basedir'] . '/rtf-reports';
                
                if (!is_dir($reports_dir)) {
                    wp_mkdir_p($reports_dir);
                }
                
                $filename = sanitize_file_name($file['name']);
                $filepath = $reports_dir . '/' . time() . '_' . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    $file_url = $upload_dir['baseurl'] . '/rtf-reports/' . basename($filepath);
                } else {
                    return [
                        'success' => false,
                        'error' => 'Kunne ikke uploade fil'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'error' => 'Ingen fil uploadet'
                ];
            }
            
            // Insert report
            $table = $wpdb->prefix . 'rtf_platform_reports';
            $result = $wpdb->insert(
                $table,
                [
                    'title' => sanitize_text_field($data['title']),
                    'description' => sanitize_textarea_field($data['description']),
                    'country' => sanitize_text_field($data['country']),
                    'city' => !empty($data['city']) ? sanitize_text_field($data['city']) : null,
                    'case_type' => sanitize_text_field($data['case_type']),
                    'report_type' => sanitize_text_field($data['report_type']),
                    'language' => sanitize_text_field($data['language']),
                    'file_url' => $file_url,
                    'file_size' => $file['size'],
                    'published_date' => current_time('mysql'),
                    'download_count' => 0
                ],
                ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d']
            );
            
            if ($result === false) {
                return [
                    'success' => false,
                    'error' => 'Kunne ikke gemme rapport'
                ];
            }
            
            return [
                'success' => true,
                'report_id' => $wpdb->insert_id,
                'message' => 'Rapport uploadet succesfuldt'
            ];
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Upload report error: ' . $e->getMessage());
            }
            
            return [
                'success' => false,
                'error' => 'Der opstod en fejl'
            ];
        }
    }
    
    /**
     * Get filter options (cities, case types)
     * 
     * @return array Available filter options
     */
    public function getFilterOptions() {
        global $wpdb;
        
        try {
            $table = $wpdb->prefix . 'rtf_platform_reports';
            
            $cities = $wpdb->get_col("SELECT DISTINCT city FROM $table WHERE city IS NOT NULL ORDER BY city");
            $case_types = $wpdb->get_col("SELECT DISTINCT case_type FROM $table ORDER BY case_type");
            
            return [
                'success' => true,
                'cities' => $cities,
                'case_types' => $case_types,
                'report_types' => ['juridisk', 'psykologisk', 'socialfaglig'],
                'countries' => ['DK', 'SE'],
                'languages' => ['da_DK', 'sv_SE', 'en_US']
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Kunne ikke hente filter muligheder'
            ];
        }
    }
}
