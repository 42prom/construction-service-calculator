<?php
/**
 * The file that defines the core plugin class
 *
 * @link       https://github.com/42prom
 * @since      1.0.0
 *
 * @package    Construction_Service_Calculator
 * @subpackage Construction_Service_Calculator/includes
 */

/**
 * The core plugin class.
 *
 * @since      1.0.0
 * @package    Construction_Service_Calculator
 * @subpackage Construction_Service_Calculator/includes
 * @author     Mikheili Nakeuri
 */
class Construction_Service_Calculator {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Construction_Service_Calculator_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if (defined('CONSTRUCTION_SERVICE_CALCULATOR_VERSION')) {
            $this->version = CONSTRUCTION_SERVICE_CALCULATOR_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'construction-service-calculator';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'includes/class-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'includes/class-i18n.php';

        /**
         * Core plugin functionality
         */
        require_once CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'includes/class-post-types.php';
        require_once CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'includes/class-svg-handler.php';
        require_once CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'includes/class-calculator.php';
        require_once CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'includes/class-data-handler.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'admin/class-admin.php';
        require_once CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'admin/class-services.php';
        require_once CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'admin/class-categories.php';
        require_once CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'admin/class-settings.php';
        require_once CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'admin/class-submissions.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'public/class-public.php';
        require_once CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'public/class-shortcode.php';

        $this->loader = new Construction_Service_Calculator_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new Construction_Service_Calculator_i18n();
        $plugin_i18n->set_domain($this->get_plugin_name());

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        // Core admin functionality
        $plugin_admin = new Construction_Service_Calculator_Admin($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'register_admin_menu');

        // Post types
        $post_types = new Construction_Service_Calculator_Post_Types();
        $this->loader->add_action('init', $post_types, 'register_post_types');

        // Services manager
        $services = new Construction_Service_Calculator_Services($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('admin_init', $services, 'register_meta_boxes');
        $this->loader->add_action('save_post_csc_service', $services, 'save_service_meta');
        $this->loader->add_action('wp_ajax_csc_upload_svg', $services, 'ajax_upload_svg');
        $this->loader->add_action('wp_ajax_csc_get_svg_library', $services, 'ajax_get_svg_library');

        // Categories manager
        $categories = new Construction_Service_Calculator_Categories($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('admin_init', $categories, 'register_category_settings');

        // Settings
        $settings = new Construction_Service_Calculator_Settings($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('admin_init', $settings, 'register_settings');

        // Submissions manager
        $submissions = new Construction_Service_Calculator_Submissions($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('admin_init', $submissions, 'register_meta_boxes');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new Construction_Service_Calculator_Public($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        
        // Register shortcode
        $shortcode = new Construction_Service_Calculator_Shortcode($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('init', $shortcode, 'register_shortcode');
        
        // AJAX handlers
        $calculator = new Construction_Service_Calculator_Calculator();
        
        // Make sure all AJAX handlers are correctly registered for both logged-in and non-logged-in users
        $this->loader->add_action('wp_ajax_csc_calculate', $calculator, 'ajax_calculate');
        $this->loader->add_action('wp_ajax_nopriv_csc_calculate', $calculator, 'ajax_calculate');
        
        $this->loader->add_action('wp_ajax_csc_submit_inquiry', $calculator, 'ajax_submit_inquiry');
        $this->loader->add_action('wp_ajax_nopriv_csc_submit_inquiry', $calculator, 'ajax_submit_inquiry');
        
        $this->loader->add_action('wp_ajax_csc_generate_html', $calculator, 'ajax_generate_html');
        $this->loader->add_action('wp_ajax_nopriv_csc_generate_html', $calculator, 'ajax_generate_html');
        
        // Allow SVG uploads for service icons
        $this->loader->add_filter('upload_mimes', 'Construction_Service_Calculator_SVG_Handler', 'add_svg_mime_type');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Construction_Service_Calculator_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}