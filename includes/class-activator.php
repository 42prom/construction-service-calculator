<?php
/**
 * Fired during plugin activation
 *
 * @link       https://github.com/42prom
 * @since      1.0.0
 *
 * @package    Construction_Service_Calculator
 * @subpackage Construction_Service_Calculator/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Construction_Service_Calculator
 * @subpackage Construction_Service_Calculator/includes
 * @author     Mikheili Nakeuri
 */
class Construction_Service_Calculator_Activator {

    /**
     * Activate the plugin.
     *
     * Creates necessary database tables and default settings.
     *
     * @since    1.0.0
     */
    public static function activate() {
        // Register post types so we can flush rewrite rules
        require_once CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'includes/class-post-types.php';
        $post_types = new Construction_Service_Calculator_Post_Types();
        $post_types->register_post_types();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Create default categories
        self::create_default_categories();
        
        // Create default settings
        self::create_default_settings();
        
        // Create default service units
        self::create_default_units();
    }
    
    /**
     * Create default service categories.
     */
    private static function create_default_categories() {
        $default_categories = array(
            'interior' => __('Interior Services', 'construction-service-calculator'),
            'exterior' => __('Exterior Services', 'construction-service-calculator'),
            'plumbing' => __('Plumbing Services', 'construction-service-calculator'),
            'electrical' => __('Electrical Services', 'construction-service-calculator'),
            'flooring' => __('Flooring Services', 'construction-service-calculator')
        );
        
        $existing_categories = get_option('csc_service_categories', array());
        
        if (empty($existing_categories)) {
            update_option('csc_service_categories', $default_categories);
        }
    }
    
    /**
     * Create default settings.
     */
    private static function create_default_settings() {
        $default_settings = array(
            'currency' => 'USD',
            'currency_symbol' => '$',
            'currency_position' => 'before',
            'decimal_separator' => '.',
            'thousand_separator' => ',',
            'decimals' => 2,
            'tax_rate' => 20,
            'tax_display' => 'yes',
            'theme' => 'default',
            'form_title' => __('Construction Service Calculator', 'construction-service-calculator'),
            'form_description' => __('Calculate the cost of your construction project instantly', 'construction-service-calculator'),
            'submit_button_text' => __('Submit Inquiry', 'construction-service-calculator'),
            'email_notifications' => 'yes',
            'admin_email' => get_option('admin_email')
        );
        
        foreach ($default_settings as $key => $value) {
            if (get_option('csc_' . $key) === false) {
                update_option('csc_' . $key, $value);
            }
        }
    }
    
    /**
     * Create default service units.
     */
    private static function create_default_units() {
        $default_units = array(
            'm2' => array(
                'name' => __('Square Meters', 'construction-service-calculator'),
                'symbol' => 'm²',
                'type' => 'area'
            ),
            'ft2' => array(
                'name' => __('Square Feet', 'construction-service-calculator'),
                'symbol' => 'ft²',
                'type' => 'area'
            ),
            'hours' => array(
                'name' => __('Hours', 'construction-service-calculator'),
                'symbol' => 'hr',
                'type' => 'time'
            ),
            'pieces' => array(
                'name' => __('Pieces', 'construction-service-calculator'),
                'symbol' => 'pc',
                'type' => 'quantity'
            ),
            'linear_meter' => array(
                'name' => __('Linear Meters', 'construction-service-calculator'),
                'symbol' => 'm',
                'type' => 'length'
            ),
            'linear_foot' => array(
                'name' => __('Linear Feet', 'construction-service-calculator'),
                'symbol' => 'ft',
                'type' => 'length'
            )
        );
        
        if (get_option('csc_service_units') === false) {
            update_option('csc_service_units', $default_units);
        }
    }
}