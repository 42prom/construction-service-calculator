<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://github.com/42prom
 * @since      1.0.0
 *
 * @package    Construction_Service_Calculator
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
$options = array(
    'csc_currency',
    'csc_currency_symbol',
    'csc_currency_position',
    'csc_decimal_separator',
    'csc_thousand_separator',
    'csc_decimals',
    'csc_tax_rate',
    'csc_tax_display',
    'csc_theme',
    'csc_form_title',
    'csc_form_description',
    'csc_submit_button_text',
    'csc_email_notifications',
    'csc_admin_email',
    'csc_service_categories',
    'csc_service_units'
);

foreach ($options as $option) {
    delete_option($option);
}

// Get custom post types
$post_types = array('csc_service', 'csc_submission');

// Delete all posts of these types
foreach ($post_types as $post_type) {
    $items = get_posts(array(
        'post_type' => $post_type,
        'post_status' => 'any',
        'numberposts' => -1,
        'fields' => 'ids'
    ));
    
    if (!empty($items)) {
        foreach ($items as $item) {
            wp_delete_post($item, true);
        }
    }
}

// Delete uploaded SVG files directory
$upload_dir = wp_upload_dir();
$svg_dir = $upload_dir['basedir'] . '/csc-svg-icons';
$exports_dir = $upload_dir['basedir'] . '/csc-exports';

// Function to recursively delete a directory
function csc_delete_directory($dir) {
    if (!file_exists($dir)) {
        return true;
    }
    
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        
        if (!csc_delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    
    return rmdir($dir);
}

// Delete directories
if (file_exists($svg_dir)) {
    csc_delete_directory($svg_dir);
}

if (file_exists($exports_dir)) {
    csc_delete_directory($exports_dir);
}