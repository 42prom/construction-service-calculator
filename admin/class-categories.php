<?php
/**
 * The categories management functionality of the plugin.
 *
 * @link       https://github.com/42prom
 * @since      1.0.0
 *
 * @package    Construction_Service_Calculator
 * @subpackage Construction_Service_Calculator/admin
 */

class Construction_Service_Calculator_Categories {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function register_category_settings() {
        register_setting(
            'csc_categories',
            'csc_service_categories',
            array(
                'sanitize_callback' => array($this, 'sanitize_categories'),
                'default' => array()
            )
        );
    }

    public function sanitize_categories($input) {
        $sanitized = array();
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $sanitized[sanitize_key($key)] = sanitize_text_field($value);
            }
        }
        return $sanitized;
    }

    public function display_categories_page() {
        // Handle deletion
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['key']) && isset($_GET['_wpnonce'])) {
            $this->delete_category($_GET['key'], $_GET['_wpnonce']);
        }
        
        // Process form submission
        if (isset($_POST['csc_action']) && $_POST['csc_action'] === 'save_categories') {
            $this->process_categories_form();
        }
        
        // Display messages
        $category_message = get_transient('csc_category_message');
        if ($category_message) {
            add_settings_error(
                'csc_categories',
                'category_message',
                $category_message['message'],
                $category_message['type']
            );
            delete_transient('csc_category_message');
        }
        
        $categories = get_option('csc_service_categories', array());
        require_once CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'admin/partials/categories-page.php';
    }

    private function process_categories_form() {
        if (!isset($_POST['csc_categories_nonce']) || !wp_verify_nonce($_POST['csc_categories_nonce'], 'csc_categories_form')) {
            add_settings_error('csc_categories', 'save_error', __('Security check failed.', 'construction-service-calculator'), 'error');
            return;
        }
        
        if (!current_user_can('manage_options')) {
            add_settings_error('csc_categories', 'save_error', __('You do not have sufficient permissions to edit categories.', 'construction-service-calculator'), 'error');
            return;
        }
        
        $categories = get_option('csc_service_categories', array());
        
        if (isset($_POST['csc_category_key']) && isset($_POST['csc_category_name'])) {
            $keys = $_POST['csc_category_key'];
            $names = $_POST['csc_category_name'];
            $updated_categories = array();
            
            for ($i = 0; $i < count($keys); $i++) {
                $key = sanitize_key($keys[$i]);
                $name = sanitize_text_field($names[$i]);
                if (!empty($key) && !empty($name)) {
                    $updated_categories[$key] = $name;
                }
            }
            
            if (!empty($updated_categories)) {
                $categories = $updated_categories;
            }
        }
        
        if (isset($_POST['csc_new_category_key']) && isset($_POST['csc_new_category_name']) &&
            !empty($_POST['csc_new_category_key']) && !empty($_POST['csc_new_category_name'])) {
            $new_key = sanitize_key($_POST['csc_new_category_key']);
            $new_name = sanitize_text_field($_POST['csc_new_category_name']);
            if (!empty($new_key) && !empty($new_name)) {
                $categories[$new_key] = $new_name;
            }
        }
        
        update_option('csc_service_categories', $categories);
        add_settings_error('csc_categories', 'save_success', __('Categories updated successfully.', 'construction-service-calculator'), 'success');
    }
    
    private function delete_category($key, $nonce) {
        if (!wp_verify_nonce($nonce, 'delete_category_' . $key)) {
            set_transient('csc_category_message', array(
                'type' => 'error',
                'message' => __('Security check failed.', 'construction-service-calculator')
            ), 30);
            return;
        }
        
        if (!current_user_can('manage_options')) {
            set_transient('csc_category_message', array(
                'type' => 'error',
                'message' => __('You do not have sufficient permissions to delete categories.', 'construction-service-calculator')
            ), 30);
            return;
        }
        
        $categories = get_option('csc_service_categories', array());
        if (!isset($categories[$key])) {
            set_transient('csc_category_message', array(
                'type' => 'error',
                'message' => __('Category not found.', 'construction-service-calculator')
            ), 30);
            return;
        }
        
        $category_name = $categories[$key];
        $service_count = $this->count_services_in_category($key);
        
        if ($service_count > 0) {
            set_transient('csc_category_message', array(
                'type' => 'error',
                'message' => sprintf(
                    __('Cannot delete category "%s" because it has %d services. Reassign services first.', 'construction-service-calculator'),
                    $category_name,
                    $service_count
                )
            ), 30);
            return;
        }
        
        unset($categories[$key]);
        update_option('csc_service_categories', $categories);
        set_transient('csc_category_message', array(
            'type' => 'success',
            'message' => sprintf(
                __('Category "%s" deleted successfully.', 'construction-service-calculator'),
                $category_name
            )
        ), 30);
    }
    
    private function count_services_in_category($category_key) {
        $args = array(
            'post_type' => 'csc_service',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => '_csc_category',
                    'value' => $category_key,
                    'compare' => '='
                )
            )
        );
        $services = get_posts($args);
        return count($services);
    }
}