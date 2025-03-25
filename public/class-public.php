<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/42prom
 * @since      1.0.0
 *
 * @package    Construction_Service_Calculator
 * @subpackage Construction_Service_Calculator/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for
 * enqueuing the public-facing stylesheet and JavaScript.
 *
 * @package    Construction_Service_Calculator
 * @subpackage Construction_Service_Calculator/public
 * @author     Mikheili Nakeuri
 */
class Construction_Service_Calculator_Public {

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
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        // Get selected theme
        $theme = get_option('csc_theme', 'default');
        
        // Enqueue base styles
        wp_enqueue_style(
            $this->plugin_name,
            CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_URL . 'public/css/public-style.css',
            array(),
            $this->version,
            'all'
        );
        
        // Enqueue theme-specific styles
        if ($theme !== 'default') {
            wp_enqueue_style(
                $this->plugin_name . '-theme',
                CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_URL . 'assets/css/themes/' . $theme . '.css',
                array($this->plugin_name),
                $this->version,
                'all'
            );
        }
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_URL . 'public/js/public-script.js',
            array('jquery'),
            $this->version,
            true
        );
        
        // Add localized script data
        wp_localize_script(
            $this->plugin_name,
            'csc_vars',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('csc_ajax_nonce'),
                'currency_symbol' => get_option('csc_currency_symbol', '$'),
                'currency_position' => get_option('csc_currency_position', 'before'),
                'decimal_separator' => get_option('csc_decimal_separator', '.'),
                'thousand_separator' => get_option('csc_thousand_separator', ','),
                'decimals' => intval(get_option('csc_decimals', 2)),
                'tax_display' => get_option('csc_tax_display', 'yes'),
                'tax_rate' => floatval(get_option('csc_tax_rate', 20)),
                'strings' => array(
                    'required_field' => __('This field is required.', 'construction-service-calculator'),
                    'invalid_email' => __('Please enter a valid email address.', 'construction-service-calculator'),
                    'min_order' => __('Minimum order quantity is %s.', 'construction-service-calculator'),
                    'max_order' => __('Maximum order quantity is %s.', 'construction-service-calculator'),
                    'select_service' => __('Please select at least one service.', 'construction-service-calculator'),
                    'calculating' => __('Calculating...', 'construction-service-calculator'),
                    'submitting' => __('Submitting...', 'construction-service-calculator'),
                    'subtotal' => __('Subtotal', 'construction-service-calculator'),
                    'tax' => __('Tax', 'construction-service-calculator'),
                    'total' => __('Total', 'construction-service-calculator'),
                    'error' => __('An error occurred. Please try again.', 'construction-service-calculator'),
                    'success' => __('Your inquiry has been submitted successfully.', 'construction-service-calculator'),
                    'show_more' => __('Show More', 'construction-service-calculator'),
                    'show_less' => __('Show Less', 'construction-service-calculator'),
                    'print' => __('Print', 'construction-service-calculator'),
                    'save_html' => __('Save as HTML', 'construction-service-calculator')
                )
            )
        );
    }
}