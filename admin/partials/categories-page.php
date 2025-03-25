<?php
/**
 * Template for the categories management page.
 *
 * @link       https://github.com/42prom
 * @since      1.0.0
 *
 * @package    Construction_Service_Calculator
 * @subpackage Construction_Service_Calculator/admin/partials
 */

// Exit if accessed directly
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap csc-categories-container">
    <div class="csc-categories-header">
        <h1 class="csc-categories-title"><?php _e('Service Categories', 'construction-service-calculator'); ?></h1>
        <button type="button" class="button button-primary csc-add-category-button">
            <?php _e('Add New Category', 'construction-service-calculator'); ?>
        </button>
    </div>
    
    <?php settings_errors(); ?>
    
    <p class="description">
        <?php _e('Manage categories for organizing construction services. Categories help users navigate and find relevant services more easily.', 'construction-service-calculator'); ?>
    </p>
    
    <div class="csc-add-category-form" style="display: none;">
        <h3><?php _e('Add New Category', 'construction-service-calculator'); ?></h3>
        
        <form method="post" action="">
            <?php wp_nonce_field('csc_categories_form', 'csc_categories_nonce'); ?>
            <input type="hidden" name="csc_action" value="save_categories">
            
            <div class="csc-form-row">
                <label for="csc_new_category_key" class="csc-form-label">
                    <?php _e('Category Key', 'construction-service-calculator'); ?>
                    <span class="description"><?php _e('(unique identifier, lowercase with hyphens)', 'construction-service-calculator'); ?></span>
                </label>
                <input type="text" id="csc_new_category_key" name="csc_new_category_key" class="regular-text" required>
            </div>
            
            <div class="csc-form-row">
                <label for="csc_new_category_name" class="csc-form-label">
                    <?php _e('Category Name', 'construction-service-calculator'); ?>
                </label>
                <input type="text" id="csc_new_category_name" name="csc_new_category_name" class="regular-text" required>
            </div>
            
            <p>
                <button type="submit" class="button button-primary">
                    <?php _e('Add Category', 'construction-service-calculator'); ?>
                </button>
            </p>
        </form>
    </div>
    
    <!-- Main form for updating categories -->
    <form method="post" action="" id="categories-form">
        <?php wp_nonce_field('csc_categories_form', 'csc_categories_nonce'); ?>
        <input type="hidden" name="csc_action" value="save_categories">
        
        <table class="csc-categories-table widefat">
            <thead>
                <tr>
                    <th><?php _e('Key', 'construction-service-calculator'); ?></th>
                    <th><?php _e('Name', 'construction-service-calculator'); ?></th>
                    <th><?php _e('Services', 'construction-service-calculator'); ?></th>
                    <th><?php _e('Actions', 'construction-service-calculator'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)) : ?>
                    <tr>
                        <td colspan="4"><?php _e('No categories defined yet.', 'construction-service-calculator'); ?></td>
                    </tr>
                <?php else : ?>
                    <?php 
                    $service_counts = array();
                    $args = array(
                        'post_type' => 'csc_service',
                        'posts_per_page' => -1,
                        'post_status' => 'publish'
                    );
                    $services = get_posts($args);
                    
                    foreach ($services as $service) {
                        $category = get_post_meta($service->ID, '_csc_category', true);
                        if (!empty($category)) {
                            if (!isset($service_counts[$category])) {
                                $service_counts[$category] = 0;
                            }
                            $service_counts[$category]++;
                        }
                    }
                    
                    foreach ($categories as $key => $name) : 
                        $service_count = isset($service_counts[$key]) ? $service_counts[$key] : 0;
                    ?>
                        <tr>
                            <td>
                                <input type="hidden" name="csc_category_key[]" value="<?php echo esc_attr($key); ?>">
                                <?php echo esc_html($key); ?>
                            </td>
                            <td>
                                <input type="text" name="csc_category_name[]" value="<?php echo esc_attr($name); ?>" class="regular-text" required>
                            </td>
                            <td>
                                <?php 
                                printf(
                                    _n('%s service', '%s services', $service_count, 'construction-service-calculator'),
                                    number_format_i18n($service_count)
                                ); 
                                ?>
                            </td>
                            <td>
                                <?php if ($service_count > 0) : ?>
                                    <button type="button" class="button button-small" disabled="disabled" title="<?php _e('Cannot remove categories with services', 'construction-service-calculator'); ?>">
                                        <?php _e('Remove', 'construction-service-calculator'); ?>
                                    </button>
                                    <p class="description">
                                        <?php _e('Cannot remove category with services.', 'construction-service-calculator'); ?>
                                    </p>
                                <?php else : ?>
                                    <?php 
                                    $delete_url = add_query_arg(
                                        array(
                                            'page' => 'csc-categories',
                                            'action' => 'delete',
                                            'key' => $key,
                                            '_wpnonce' => wp_create_nonce('delete_category_' . $key)
                                        ),
                                        admin_url('admin.php')
                                    );
                                    ?>
                                    <a href="<?php echo esc_url($delete_url); ?>" class="button button-small csc-remove-category">
                                        <?php _e('Remove', 'construction-service-calculator'); ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php submit_button(__('Save Changes', 'construction-service-calculator')); ?>
    </form>
</div>