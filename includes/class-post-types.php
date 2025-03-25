<?php
/**
 * Register custom post types for the plugin
 *
 * @link       https://github.com/42prom
 * @since      1.0.0
 *
 * @package    Construction_Service_Calculator
 * @subpackage Construction_Service_Calculator/includes
 */

/**
 * Register custom post types for the plugin.
 *
 * Define and register custom post types for services and submissions.
 *
 * @package    Construction_Service_Calculator
 * @subpackage Construction_Service_Calculator/includes
 * @author     Mikheili Nakeuri
 */
class Construction_Service_Calculator_Post_Types {

    /**
     * Register custom post types.
     *
     * @since    1.0.0
     */
    public function register_post_types() {
        // Register Service post type
        register_post_type('csc_service', array(
            'labels' => array(
                'name'               => __('Services', 'construction-service-calculator'),
                'singular_name'      => __('Service', 'construction-service-calculator'),
                'menu_name'          => __('Services', 'construction-service-calculator'),
                'name_admin_bar'     => __('Service', 'construction-service-calculator'),
                'add_new'            => __('Add New', 'construction-service-calculator'),
                'add_new_item'       => __('Add New Service', 'construction-service-calculator'),
                'new_item'           => __('New Service', 'construction-service-calculator'),
                'edit_item'          => __('Edit Service', 'construction-service-calculator'),
                'view_item'          => __('View Service', 'construction-service-calculator'),
                'all_items'          => __('All Services', 'construction-service-calculator'),
                'search_items'       => __('Search Services', 'construction-service-calculator'),
                'parent_item_colon'  => __('Parent Services:', 'construction-service-calculator'),
                'not_found'          => __('No services found.', 'construction-service-calculator'),
                'not_found_in_trash' => __('No services found in Trash.', 'construction-service-calculator')
            ),
            'public'               => false,
            'show_ui'              => true,
            'show_in_menu'         => false, // Will be added as submenu of custom plugin menu
            'supports'             => array('title', 'editor', 'thumbnail'),
            'has_archive'          => false,
            'rewrite'              => false,
            'menu_icon'            => 'dashicons-hammer',
            'capability_type'      => 'post',
            'show_in_rest'         => true,
            'rest_base'            => 'csc-services'
        ));

        // Register Submission post type
        register_post_type('csc_submission', array(
            'labels' => array(
                'name'               => __('Submissions', 'construction-service-calculator'),
                'singular_name'      => __('Submission', 'construction-service-calculator'),
                'menu_name'          => __('Submissions', 'construction-service-calculator'),
                'name_admin_bar'     => __('Submission', 'construction-service-calculator'),
                'add_new'            => __('Add New', 'construction-service-calculator'),
                'add_new_item'       => __('Add New Submission', 'construction-service-calculator'),
                'new_item'           => __('New Submission', 'construction-service-calculator'),
                'edit_item'          => __('View Submission', 'construction-service-calculator'),
                'view_item'          => __('View Submission', 'construction-service-calculator'),
                'all_items'          => __('All Submissions', 'construction-service-calculator'),
                'search_items'       => __('Search Submissions', 'construction-service-calculator'),
                'parent_item_colon'  => __('Parent Submissions:', 'construction-service-calculator'),
                'not_found'          => __('No submissions found.', 'construction-service-calculator'),
                'not_found_in_trash' => __('No submissions found in Trash.', 'construction-service-calculator')
            ),
            'public'               => false,
            'show_ui'              => true,
            'show_in_menu'         => false, // Will be added as submenu of custom plugin menu
            'supports'             => array('title'),
            'has_archive'          => false,
            'rewrite'              => false,
            'capability_type'      => 'post',
            'capabilities' => array(
                'create_posts' => false,
            ),
            'map_meta_cap' => true,
        ));
    }
}