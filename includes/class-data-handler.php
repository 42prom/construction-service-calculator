<?php
/**
 * Handle data management for the plugin
 *
 * @link       https://github.com/42prom
 * @since      1.0.0
 *
 * @package    Construction_Service_Calculator
 * @subpackage Construction_Service_Calculator/includes
 */

/**
 * Handle data management for the plugin.
 *
 * Provides methods for data management, flexible metadata handling,
 * and import/export functionality.
 *
 * @package    Construction_Service_Calculator
 * @subpackage Construction_Service_Calculator/includes
 * @author     Mikheili Nakeuri
 */
class Construction_Service_Calculator_Data_Handler {

    /**
     * Get all services.
     *
     * @since    1.0.0
     * @param    string    $category    Optional category to filter services.
     * @return   array                  Array of service posts.
     */
    public function get_services($category = '') {
        $args = array(
            'post_type' => 'csc_service',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'menu_order',
            'order' => 'ASC'
        );
        
        // Filter by category if specified
        if (!empty($category)) {
            $args['meta_query'] = array(
                array(
                    'key' => '_csc_category',
                    'value' => $category,
                    'compare' => '='
                )
            );
        }
        
        $services = get_posts($args);
        
        // Enhance service objects with metadata
        foreach ($services as &$service) {
            $service->metadata = $this->get_service_metadata($service->ID);
        }
        
        return $services;
    }
    
    /**
     * Get service metadata.
     *
     * @since    1.0.0
     * @param    int       $service_id    Service post ID.
     * @return   array                    Service metadata.
     */
    public function get_service_metadata($service_id) {
        $metadata = array(
            'rate' => floatval(get_post_meta($service_id, '_csc_rate', true)),
            'unit' => get_post_meta($service_id, '_csc_unit', true),
            'category' => get_post_meta($service_id, '_csc_category', true),
            'icon_url' => get_post_meta($service_id, '_csc_icon_url', true),
            'description' => get_post_meta($service_id, '_csc_description', true),
            'min_order' => floatval(get_post_meta($service_id, '_csc_min_order', true)),
            'max_order' => floatval(get_post_meta($service_id, '_csc_max_order', true)),
            'step' => floatval(get_post_meta($service_id, '_csc_step', true))
        );
        
        // Get service units information
        $service_units = get_option('csc_service_units', array());
        $unit_key = $metadata['unit'];
        
        if (isset($service_units[$unit_key])) {
            $metadata['unit_name'] = $service_units[$unit_key]['name'];
            $metadata['unit_symbol'] = $service_units[$unit_key]['symbol'];
            $metadata['unit_type'] = $service_units[$unit_key]['type'];
        } else {
            $metadata['unit_name'] = $unit_key;
            $metadata['unit_symbol'] = $unit_key;
            $metadata['unit_type'] = '';
        }
        
        // Get SVG content if available
        if (!empty($metadata['icon_url'])) {
            require_once CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'includes/class-svg-handler.php';
            $metadata['icon_content'] = Construction_Service_Calculator_SVG_Handler::get_svg_content($metadata['icon_url']);
        } else {
            $metadata['icon_content'] = '';
        }
        
        // Get dynamic custom fields
        $custom_fields = get_post_meta($service_id, '_csc_custom_fields', true);
        if (!empty($custom_fields) && is_array($custom_fields)) {
            $metadata['custom_fields'] = $custom_fields;
        } else {
            $metadata['custom_fields'] = array();
        }
        
        return $metadata;
    }
    
    /**
     * Get all service categories.
     *
     * @since    1.0.0
     * @return   array    Array of service categories.
     */
    public function get_service_categories() {
        return get_option('csc_service_categories', array());
    }
    
    /**
     * Get all service units.
     *
     * @since    1.0.0
     * @return   array    Array of service units.
     */
    public function get_service_units() {
        return get_option('csc_service_units', array());
    }
    
    /**
     * Get submissions.
     *
     * @since    1.0.0
     * @param    string    $status     Optional status to filter submissions.
     * @param    int       $limit      Optional limit for number of submissions.
     * @param    int       $offset     Optional offset for pagination.
     * @return   array                 Array of submission posts.
     */
    public function get_submissions($status = '', $limit = 10, $offset = 0) {
        $args = array(
            'post_type' => 'csc_submission',
            'posts_per_page' => $limit,
            'offset' => $offset,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        // Filter by status if specified
        if (!empty($status)) {
            $args['meta_query'] = array(
                array(
                    'key' => '_csc_status',
                    'value' => $status,
                    'compare' => '='
                )
            );
        }
        
        $submissions = get_posts($args);
        
        // Enhance submission objects with metadata
        foreach ($submissions as &$submission) {
            $submission->metadata = $this->get_submission_metadata($submission->ID);
        }
        
        return $submissions;
    }
    
    /**
     * Get submission metadata.
     *
     * @since    1.0.0
     * @param    int       $submission_id    Submission post ID.
     * @return   array                       Submission metadata.
     */
    public function get_submission_metadata($submission_id) {
        return array(
            'calculation' => get_post_meta($submission_id, '_csc_calculation', true),
            'customer_info' => get_post_meta($submission_id, '_csc_customer_info', true),
            'status' => get_post_meta($submission_id, '_csc_status', true),
            'date' => get_post_meta($submission_id, '_csc_date', true),
            'html_estimate' => get_post_meta($submission_id, '_csc_html_estimate', true),
            'notes' => get_post_meta($submission_id, '_csc_notes', true)
        );
    }
    
    /**
     * Import services from CSV.
     *
     * @since    1.0.0
     * @param    string    $file_path    Path to CSV file.
     * @return   array                   Import results.
     */
    public function import_services_from_csv($file_path) {
        if (!file_exists($file_path)) {
            return array(
                'success' => false,
                'message' => __('File not found.', 'construction-service-calculator')
            );
        }
        
        // Open the file
        $file = fopen($file_path, 'r');
        if (!$file) {
            return array(
                'success' => false,
                'message' => __('Could not open file.', 'construction-service-calculator')
            );
        }
        
        // Get CSV headers
        $headers = fgetcsv($file);
        if (!$headers) {
            fclose($file);
            return array(
                'success' => false,
                'message' => __('Invalid CSV format.', 'construction-service-calculator')
            );
        }
        
        // Validate required headers
        $required_headers = array('name', 'rate', 'unit', 'category');
        $missing_headers = array_diff($required_headers, array_map('strtolower', $headers));
        
        if (!empty($missing_headers)) {
            fclose($file);
            return array(
                'success' => false,
                'message' => sprintf(
                    __('Missing required headers: %s', 'construction-service-calculator'),
                    implode(', ', $missing_headers)
                )
            );
        }
        
        // Process rows
        $imported = 0;
        $skipped = 0;
        $errors = array();
        
        while (($row = fgetcsv($file)) !== false) {
            if (count($row) !== count($headers)) {
                $skipped++;
                $errors[] = sprintf(
                    __('Row %d: Column count mismatch.', 'construction-service-calculator'),
                    $imported + $skipped + 1
                );
                continue;
            }
            
            // Combine headers with row values
            $service_data = array_combine($headers, $row);
            
            // Validate required fields
            if (empty($service_data['name']) || !isset($service_data['rate']) || empty($service_data['unit']) || empty($service_data['category'])) {
                $skipped++;
                $errors[] = sprintf(
                    __('Row %d: Missing required fields.', 'construction-service-calculator'),
                    $imported + $skipped + 1
                );
                continue;
            }
            
            // Create service
            $service_id = wp_insert_post(array(
                'post_title' => sanitize_text_field($service_data['name']),
                'post_type' => 'csc_service',
                'post_status' => 'publish'
            ));
            
            if (is_wp_error($service_id)) {
                $skipped++;
                $errors[] = sprintf(
                    __('Row %d: Failed to create service.', 'construction-service-calculator'),
                    $imported + $skipped + 1
                );
                continue;
            }
            
            // Save standard meta fields
            update_post_meta($service_id, '_csc_rate', floatval($service_data['rate']));
            update_post_meta($service_id, '_csc_unit', sanitize_text_field($service_data['unit']));
            update_post_meta($service_id, '_csc_category', sanitize_text_field($service_data['category']));
            
            // Save optional meta fields
            if (isset($service_data['description'])) {
                update_post_meta($service_id, '_csc_description', sanitize_text_field($service_data['description']));
            }
            
            if (isset($service_data['min_order'])) {
                update_post_meta($service_id, '_csc_min_order', floatval($service_data['min_order']));
            }
            
            if (isset($service_data['max_order'])) {
                update_post_meta($service_id, '_csc_max_order', floatval($service_data['max_order']));
            }
            
            if (isset($service_data['step'])) {
                update_post_meta($service_id, '_csc_step', floatval($service_data['step']));
            }
            
            if (isset($service_data['icon_url'])) {
                update_post_meta($service_id, '_csc_icon_url', esc_url_raw($service_data['icon_url']));
            }
            
            // Save dynamic custom fields
            $custom_fields = array();
            foreach ($service_data as $key => $value) {
                // Skip standard fields
                if (in_array(strtolower($key), array('name', 'rate', 'unit', 'category', 'description', 'min_order', 'max_order', 'step', 'icon_url'))) {
                    continue;
                }
                
                // Add to custom fields
                if (!empty($value)) {
                    $custom_fields[$key] = $value;
                }
            }
            
            if (!empty($custom_fields)) {
                update_post_meta($service_id, '_csc_custom_fields', $custom_fields);
            }
            
            $imported++;
        }
        
        fclose($file);
        
        return array(
            'success' => true,
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors
        );
    }
    
    /**
     * Export services to CSV.
     *
     * @since    1.0.0
     * @param    string    $category    Optional category to filter services.
     * @return   string                 Path to generated CSV file.
     */
    public function export_services_to_csv($category = '') {
        // Get services
        $services = $this->get_services($category);
        
        if (empty($services)) {
            return false;
        }
        
        // Create uploads directory if it doesn't exist
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/csc-exports';
        if (!file_exists($export_dir)) {
            wp_mkdir_p($export_dir);
            
            // Create an index.php file to prevent directory listing
            $index_file = $export_dir . '/index.php';
            file_put_contents($index_file, '<?php // Silence is golden');
        }
        
        // Generate filename
        $filename = 'services-export-' . date('Y-m-d-H-i-s') . '.csv';
        $file_path = $export_dir . '/' . $filename;
        
        // Open file for writing
        $file = fopen($file_path, 'w');
        if (!$file) {
            return false;
        }
        
        // Determine all possible fields
        $all_fields = array('name', 'rate', 'unit', 'category', 'description', 'min_order', 'max_order', 'step', 'icon_url');
        $custom_fields = array();
        
        // Scan all services for custom fields
        foreach ($services as $service) {
            if (!empty($service->metadata['custom_fields']) && is_array($service->metadata['custom_fields'])) {
                foreach ($service->metadata['custom_fields'] as $key => $value) {
                    if (!in_array($key, $custom_fields)) {
                        $custom_fields[] = $key;
                    }
                }
            }
        }
        
        // Combine all fields
        $headers = array_merge($all_fields, $custom_fields);
        
        // Write headers
        fputcsv($file, $headers);
        
        // Write service data
        foreach ($services as $service) {
            $row = array(
                'name' => $service->post_title,
                'rate' => $service->metadata['rate'],
                'unit' => $service->metadata['unit'],
                'category' => $service->metadata['category'],
                'description' => $service->metadata['description'],
                'min_order' => $service->metadata['min_order'],
                'max_order' => $service->metadata['max_order'],
                'step' => $service->metadata['step'],
                'icon_url' => $service->metadata['icon_url']
            );
            
            // Add custom fields
            foreach ($custom_fields as $field) {
                $row[$field] = isset($service->metadata['custom_fields'][$field]) ? $service->metadata['custom_fields'][$field] : '';
            }
            
            fputcsv($file, $row);
        }
        
        fclose($file);
        
        return array(
            'success' => true,
            'file_path' => $file_path,
            'file_url' => $upload_dir['baseurl'] . '/csc-exports/' . $filename
        );
    }
    
    /**
     * Get plugin analytics data.
     *
     * @since    1.0.0
     * @param    string    $period    Time period (week, month, year).
     * @return   array                Analytics data.
     */
    public function get_analytics_data($period = 'month') {
        // Determine date range
        $end_date = current_time('mysql');
        $start_date = '';
        
        switch ($period) {
            case 'week':
                $start_date = date('Y-m-d H:i:s', strtotime('-1 week', strtotime($end_date)));
                break;
            case 'month':
                $start_date = date('Y-m-d H:i:s', strtotime('-1 month', strtotime($end_date)));
                break;
            case 'year':
                $start_date = date('Y-m-d H:i:s', strtotime('-1 year', strtotime($end_date)));
                break;
            default:
                $start_date = date('Y-m-d H:i:s', strtotime('-1 month', strtotime($end_date)));
        }
        
        // Get submissions in the date range
        $args = array(
            'post_type' => 'csc_submission',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'date_query' => array(
                array(
                    'after' => $start_date,
                    'before' => $end_date,
                    'inclusive' => true
                )
            )
        );
        
        $submissions = get_posts($args);
        
        // Initialize analytics data
        $data = array(
            'total_submissions' => count($submissions),
            'total_revenue' => 0,
            'popular_services' => array(),
            'submissions_by_date' => array()
        );
        
        // Process submissions
        $services_count = array();
        
        foreach ($submissions as $submission) {
            $metadata = $this->get_submission_metadata($submission->ID);
            $calculation = isset($metadata['calculation']) ? $metadata['calculation'] : array();
            
            // Add to total revenue
            if (isset($calculation['grand_total'])) {
                $data['total_revenue'] += floatval($calculation['grand_total']);
            }
            
            // Count services
            if (isset($calculation['services']) && is_array($calculation['services'])) {
                foreach ($calculation['services'] as $service) {
                    $service_id = isset($service['service_id']) ? $service['service_id'] : 0;
                    if ($service_id > 0) {
                        if (!isset($services_count[$service_id])) {
                            $services_count[$service_id] = array(
                                'count' => 0,
                                'name' => isset($service['service_name']) ? $service['service_name'] : get_the_title($service_id),
                                'revenue' => 0
                            );
                        }
                        
                        $services_count[$service_id]['count']++;
                        $services_count[$service_id]['revenue'] += isset($service['subtotal']) ? floatval($service['subtotal']) : 0;
                    }
                }
            }
            
            // Group by date
            $date = isset($metadata['date']) ? date('Y-m-d', strtotime($metadata['date'])) : date('Y-m-d', strtotime($submission->post_date));
            if (!isset($data['submissions_by_date'][$date])) {
                $data['submissions_by_date'][$date] = 0;
            }
            $data['submissions_by_date'][$date]++;
        }
        
        // Sort services by count
        arsort($services_count);
        
        // Get top 10 services
        $data['popular_services'] = array_slice($services_count, 0, 10, true);
        
        // Format submissions by date as array
        $formatted_dates = array();
        foreach ($data['submissions_by_date'] as $date => $count) {
            $formatted_dates[] = array(
                'date' => $date,
                'count' => $count
            );
        }
        $data['submissions_by_date'] = $formatted_dates;
        
        // Format currency
        $currency_symbol = get_option('csc_currency_symbol', '$');
        $data['total_revenue_formatted'] = $currency_symbol . number_format($data['total_revenue'], 2);
        
        return $data;
    }
}