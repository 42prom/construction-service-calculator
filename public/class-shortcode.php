<?php
/**
 * The shortcode functionality of the plugin.
 *
 * @link       https://github.com/42prom
 * @since      1.0.0
 *
 * @package    Construction_Service_Calculator
 * @subpackage Construction_Service_Calculator/public
 */

/**
 * The shortcode functionality of the plugin.
 *
 * Defines and handles the shortcode for displaying the calculator on the frontend.
 *
 * @package    Construction_Service_Calculator
 * @subpackage Construction_Service_Calculator/public
 * @author     Mikheili Nakeuri
 */
class Construction_Service_Calculator_Shortcode {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version           The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the shortcode.
     *
     * @since    1.0.0
     */
    public function register_shortcode() {
        add_shortcode('construction_calculator', array($this, 'render_calculator'));
    }

    /**
     * Render the calculator shortcode.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string            The calculator HTML.
     */
    public function render_calculator($atts) {
        // Process shortcode attributes
        $atts = shortcode_atts(
            array(
                'title' => get_option('csc_form_title', __('Construction Service Calculator', 'construction-service-calculator')),
                'description' => get_option('csc_form_description', __('Calculate the cost of your construction project instantly', 'construction-service-calculator')),
                'category' => '',
                'services' => '',
                'theme' => get_option('csc_theme', 'default'),
                'submit_button' => get_option('csc_submit_button_text', __('Submit Inquiry', 'construction-service-calculator')),
                'show_contact_form' => 'yes',
                'columns' => '2'
            ),
            $atts,
            'construction_calculator'
        );
        
        // Get selected services
        $selected_services = array();
        if (!empty($atts['services'])) {
            $service_ids = explode(',', $atts['services']);
            foreach ($service_ids as $id) {
                $selected_services[] = intval(trim($id));
            }
        }
        
        // Get service categories
        $categories = get_option('csc_service_categories', array());
        
        // Selected category
        $selected_categories = array();
        if (!empty($atts['category'])) {
            $categories_list = explode(',', $atts['category']);
            foreach ($categories_list as $cat) {
                $selected_categories[] = trim($cat);
            }
        }
        
        // Get services
        $data_handler = new Construction_Service_Calculator_Data_Handler();
        
        // Initialize services array to hold all services grouped by category
        $services_by_category = array();
        
        // If specific services are selected
        if (!empty($selected_services)) {
            foreach ($selected_services as $service_id) {
                $service = get_post($service_id);
                if ($service && $service->post_type === 'csc_service') {
                    $service->metadata = $data_handler->get_service_metadata($service_id);
                    $category = $service->metadata['category'];
                    
                    if (!isset($services_by_category[$category])) {
                        $services_by_category[$category] = array();
                    }
                    
                    $services_by_category[$category][] = $service;
                }
            }
        } 
        // If a specific category is selected
        elseif (!empty($selected_categories)) {
            foreach ($selected_categories as $category) {
                $services = $data_handler->get_services($category);
                if (!empty($services)) {
                    $services_by_category[$category] = $services;
                }
            }
        } 
        // Get all services grouped by category
        else {
            $services = $data_handler->get_services();
            
            foreach ($services as $service) {
                $category = $service->metadata['category'];
                
                if (!isset($services_by_category[$category])) {
                    $services_by_category[$category] = array();
                }
                
                $services_by_category[$category][] = $service;
            }
        }
        
        // Apply theme
        $theme_class = 'csc-theme-' . $atts['theme'];
        
        // Set columns
        $columns = intval($atts['columns']);
        $columns = $columns > 0 ? $columns : 2;
        $columns_class = 'csc-columns-' . $columns;
        
        // Start output buffering
        ob_start();
        
        // Include calculator template
        include CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'public/partials/calculator-form.php';
        
        // Return the calculator HTML
        return ob_get_clean();
    }
}