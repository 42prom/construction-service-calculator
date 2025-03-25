<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/42prom
 * @since      1.0.0
 *
 * @package    Construction_Service_Calculator
 * @subpackage Construction_Service_Calculator/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two hooks for
 * enqueuing the admin-specific stylesheet and JavaScript.
 *
 * @package    Construction_Service_Calculator
 * @subpackage Construction_Service_Calculator/admin
 * @author     Mikheili Nakeuri
 */
class Construction_Service_Calculator_Admin {

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
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name . '-admin',
            CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_URL . 'admin/css/admin-style.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name . '-admin',
            CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_URL . 'admin/js/admin-script.js',
            array('jquery', 'wp-color-picker'),
            $this->version,
            false
        );

        // Add the WordPress color picker dependency
        wp_enqueue_style('wp-color-picker');
        
        // Add the media uploader scripts
        wp_enqueue_media();
        
        // Add localized script data
        wp_localize_script(
            $this->plugin_name . '-admin',
            'csc_admin_vars',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('csc_admin_nonce'),
                'svg_upload_error' => __('Error uploading SVG file. Please try again.', 'construction-service-calculator'),
                'svg_invalid_error' => __('Invalid SVG file. Please upload a valid SVG.', 'construction-service-calculator'),
                'confirm_delete' => __('Are you sure you want to delete this item? This action cannot be undone.', 'construction-service-calculator')
            )
        );
    }

    /**
     * Register the admin menu items.
     *
     * @since    1.0.0
     */
    public function register_admin_menu() {
        // Main menu
        add_menu_page(
            __('Construction Calculator', 'construction-service-calculator'),
            __('Construction Calculator', 'construction-service-calculator'),
            'manage_options',
            'construction-service-calculator',
            array($this, 'display_admin_dashboard'),
            'dashicons-hammer',
            30
        );
        
        // Services submenu
        add_submenu_page(
            'construction-service-calculator',
            __('Services', 'construction-service-calculator'),
            __('Services', 'construction-service-calculator'),
            'manage_options',
            'edit.php?post_type=csc_service',
            null
        );
        
        // Add New Service submenu
        add_submenu_page(
            'construction-service-calculator',
            __('Add New Service', 'construction-service-calculator'),
            __('Add New Service', 'construction-service-calculator'),
            'manage_options',
            'post-new.php?post_type=csc_service',
            null
        );
        
        // Categories submenu
        add_submenu_page(
            'construction-service-calculator',
            __('Categories', 'construction-service-calculator'),
            __('Categories', 'construction-service-calculator'),
            'manage_options',
            'csc-categories',
            array($this, 'display_categories_page')
        );
        
        // Submissions submenu
        add_submenu_page(
            'construction-service-calculator',
            __('Submissions', 'construction-service-calculator'),
            __('Submissions', 'construction-service-calculator'),
            'manage_options',
            'csc-submissions',
            array($this, 'display_submissions_page')
        );
        
        // Settings submenu
        add_submenu_page(
            'construction-service-calculator',
            __('Settings', 'construction-service-calculator'),
            __('Settings', 'construction-service-calculator'),
            'manage_options',
            'csc-settings',
            array($this, 'display_settings_page')
        );
        
        // Tools submenu (for import/export)
        add_submenu_page(
            'construction-service-calculator',
            __('Tools', 'construction-service-calculator'),
            __('Tools', 'construction-service-calculator'),
            'manage_options',
            'csc-tools',
            array($this, 'display_tools_page')
        );
    }

    /**
     * Display the admin dashboard page.
     *
     * @since    1.0.0
     */
    public function display_admin_dashboard() {
        // Include dashboard template
        require_once CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'admin/partials/dashboard-page.php';
    }

    /**
     * Display the categories admin page.
     *
     * @since    1.0.0
     */
    public function display_categories_page() {
        $categories = new Construction_Service_Calculator_Categories($this->plugin_name, $this->version);
        $categories->display_categories_page();
    }

    /**
     * Display the submissions admin page.
     *
     * @since    1.0.0
     */
    public function display_submissions_page() {
        $submissions = new Construction_Service_Calculator_Submissions($this->plugin_name, $this->version);
        $submissions->display_submissions_page();
    }

    /**
     * Display the settings admin page.
     *
     * @since    1.0.0
     */
    public function display_settings_page() {
        $settings = new Construction_Service_Calculator_Settings($this->plugin_name, $this->version);
        $settings->display_settings_page();
    }

    /**
     * Display the tools admin page.
     *
     * @since    1.0.0
     */
    public function display_tools_page() {
        // Include tools template
        require_once CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'admin/partials/tools-page.php';
    }
}