<?php
/**
 * Template for the admin dashboard page.
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

// Get analytics data
$data_handler = new Construction_Service_Calculator_Data_Handler();
$analytics = $data_handler->get_analytics_data('month');

// Get recent submissions
$submissions = $data_handler->get_submissions('', 5);

// Get service counts
$service_count = wp_count_posts('csc_service')->publish;
$category_count = count(get_option('csc_service_categories', array()));
$submission_count = wp_count_posts('csc_submission')->publish;
?>

<div class="wrap csc-admin-dashboard">
    <h1><?php _e('Construction Service Calculator', 'construction-service-calculator'); ?></h1>
    
    <div class="csc-admin-header">
        <div class="csc-admin-header-left">
            <p class="csc-version">
                <?php printf(
                    __('Version %s', 'construction-service-calculator'),
                    CONSTRUCTION_SERVICE_CALCULATOR_VERSION
                ); ?>
            </p>
        </div>
        <div class="csc-admin-header-right">
            <a href="<?php echo esc_url(admin_url('post-new.php?post_type=csc_service')); ?>" class="button button-primary">
                <?php _e('Add New Service', 'construction-service-calculator'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=csc-settings')); ?>" class="button">
                <?php _e('Settings', 'construction-service-calculator'); ?>
            </a>
        </div>
    </div>
    
    <div class="csc-admin-cards">
        <div class="csc-admin-card">
            <div class="csc-admin-card-header">
                <h2><span class="dashicons dashicons-hammer"></span> <?php _e('Services', 'construction-service-calculator'); ?></h2>
            </div>
            <div class="csc-admin-card-content">
                <div class="csc-admin-card-stat"><?php echo esc_html($service_count); ?></div>
                <p><?php _e('Total services available', 'construction-service-calculator'); ?></p>
            </div>
            <div class="csc-admin-card-footer">
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=csc_service')); ?>">
                    <?php _e('Manage Services', 'construction-service-calculator'); ?> →
                </a>
            </div>
        </div>
        
        <div class="csc-admin-card">
            <div class="csc-admin-card-header">
                <h2><span class="dashicons dashicons-category"></span> <?php _e('Categories', 'construction-service-calculator'); ?></h2>
            </div>
            <div class="csc-admin-card-content">
                <div class="csc-admin-card-stat"><?php echo esc_html($category_count); ?></div>
                <p><?php _e('Service categories', 'construction-service-calculator'); ?></p>
            </div>
            <div class="csc-admin-card-footer">
                <a href="<?php echo esc_url(admin_url('admin.php?page=csc-categories')); ?>">
                    <?php _e('Manage Categories', 'construction-service-calculator'); ?> →
                </a>
            </div>
        </div>
        
        <div class="csc-admin-card">
            <div class="csc-admin-card-header">
                <h2><span class="dashicons dashicons-email"></span> <?php _e('Submissions', 'construction-service-calculator'); ?></h2>
            </div>
            <div class="csc-admin-card-content">
                <div class="csc-admin-card-stat"><?php echo esc_html($submission_count); ?></div>
                <p><?php _e('Total customer submissions', 'construction-service-calculator'); ?></p>
            </div>
            <div class="csc-admin-card-footer">
                <a href="<?php echo esc_url(admin_url('admin.php?page=csc-submissions')); ?>">
                    <?php _e('View Submissions', 'construction-service-calculator'); ?> →
                </a>
            </div>
        </div>
        
        <div class="csc-admin-card">
            <div class="csc-admin-card-header">
                <h2><span class="dashicons dashicons-chart-bar"></span> <?php _e('Revenue', 'construction-service-calculator'); ?></h2>
            </div>
            <div class="csc-admin-card-content">
                <div class="csc-admin-card-stat"><?php echo esc_html($analytics['total_revenue_formatted']); ?></div>
                <p><?php _e('Estimated total revenue', 'construction-service-calculator'); ?></p>
            </div>
            <div class="csc-admin-card-footer">
                <a href="<?php echo esc_url(admin_url('admin.php?page=csc-tools')); ?>">
                    <?php _e('View Analytics', 'construction-service-calculator'); ?> →
                </a>
            </div>
        </div>
    </div>
    
    <div class="csc-admin-boxes">
        <div class="csc-admin-box">
            <div class="csc-admin-box-header">
                <h2><?php _e('Recent Submissions', 'construction-service-calculator'); ?></h2>
            </div>
            <div class="csc-admin-box-content">
                <?php if (!empty($submissions)) : ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('ID', 'construction-service-calculator'); ?></th>
                                <th><?php _e('Customer', 'construction-service-calculator'); ?></th>
                                <th><?php _e('Date', 'construction-service-calculator'); ?></th>
                                <th><?php _e('Total', 'construction-service-calculator'); ?></th>
                                <th><?php _e('Status', 'construction-service-calculator'); ?></th>
                                <th><?php _e('Actions', 'construction-service-calculator'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submissions as $submission) : 
                                $metadata = $submission->metadata;
                                $date = isset($metadata['date']) ? $metadata['date'] : $submission->post_date;
                                $status = isset($metadata['status']) ? $metadata['status'] : 'new';
                                $calculation = isset($metadata['calculation']) ? $metadata['calculation'] : array();
                                $customer_info = isset($metadata['customer_info']) ? $metadata['customer_info'] : array();
                                
                                // Format the date
                                $formatted_date = date_i18n(get_option('date_format'), strtotime($date));
                                
                                // Get customer name
                                $customer_name = isset($customer_info['name']) ? $customer_info['name'] : __('Anonymous', 'construction-service-calculator');
                                
                                // Get total
                                $total = isset($calculation['grand_total_formatted']) ? $calculation['grand_total_formatted'] : __('N/A', 'construction-service-calculator');
                                
                                // Get status class
                                $status_class = 'csc-status-' . $status;
                                
                                // Get status label
                                $status_labels = array(
                                    'new' => __('New', 'construction-service-calculator'),
                                    'in-progress' => __('In Progress', 'construction-service-calculator'),
                                    'completed' => __('Completed', 'construction-service-calculator'),
                                    'cancelled' => __('Cancelled', 'construction-service-calculator')
                                );
                                $status_label = isset($status_labels[$status]) ? $status_labels[$status] : $status;
                            ?>
                                <tr>
                                    <td><?php echo esc_html($submission->ID); ?></td>
                                    <td><?php echo esc_html($customer_name); ?></td>
                                    <td><?php echo esc_html($formatted_date); ?></td>
                                    <td><?php echo esc_html($total); ?></td>
                                    <td>
                                        <span class="csc-status <?php echo esc_attr($status_class); ?>">
                                            <?php echo esc_html($status_label); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=csc-submissions&view=' . $submission->ID)); ?>" class="button button-small">
                                            <?php _e('View', 'construction-service-calculator'); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="csc-admin-box-footer">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=csc-submissions')); ?>" class="button">
                            <?php _e('View All Submissions', 'construction-service-calculator'); ?>
                        </a>
                    </div>
                <?php else : ?>
                    <p><?php _e('No submissions yet.', 'construction-service-calculator'); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="csc-admin-box">
            <div class="csc-admin-box-header">
                <h2><?php _e('Popular Services', 'construction-service-calculator'); ?></h2>
            </div>
            <div class="csc-admin-box-content">
                <?php if (!empty($analytics['popular_services'])) : ?>
                    <div class="csc-popular-services">
                        <?php 
                        $popular_services = array_slice($analytics['popular_services'], 0, 5);
                        foreach ($popular_services as $service_id => $service_data) : 
                        ?>
                            <div class="csc-popular-service">
                                <div class="csc-popular-service-name">
                                    <?php echo esc_html($service_data['name']); ?>
                                </div>
                                <div class="csc-popular-service-count">
                                    <?php printf(
                                        _n('%s request', '%s requests', $service_data['count'], 'construction-service-calculator'),
                                        number_format_i18n($service_data['count'])
                                    ); ?>
                                </div>
                                <div class="csc-popular-service-bar">
                                    <div class="csc-popular-service-bar-inner" style="width: <?php echo esc_attr(($service_data['count'] / $popular_services[array_key_first($popular_services)]['count']) * 100); ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p><?php _e('No service data available yet.', 'construction-service-calculator'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="csc-admin-help">
        <h2><?php _e('Using the Calculator', 'construction-service-calculator'); ?></h2>
        <p><?php _e('Use the shortcode below to add the calculator to any page or post:', 'construction-service-calculator'); ?></p>
        <div class="csc-admin-shortcode">
            <code>[construction_calculator]</code>
            <button class="csc-copy-shortcode button button-small" data-shortcode="[construction_calculator]">
                <?php _e('Copy', 'construction-service-calculator'); ?>
            </button>
        </div>
        
        <h3><?php _e('Shortcode Attributes', 'construction-service-calculator'); ?></h3>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Attribute', 'construction-service-calculator'); ?></th>
                    <th><?php _e('Description', 'construction-service-calculator'); ?></th>
                    <th><?php _e('Example', 'construction-service-calculator'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>title</code></td>
                    <td><?php _e('Custom calculator title', 'construction-service-calculator'); ?></td>
                    <td><code>title="Renovation Cost Calculator"</code></td>
                </tr>
                <tr>
                    <td><code>category</code></td>
                    <td><?php _e('Display services from specific categories (comma-separated for multiple)', 'construction-service-calculator'); ?></td>
                    <td><code>category="flooring"</code> or <code>category="flooring,interior,exterior"</code></td>
                </tr>
                <tr>
                    <td><code>services</code></td>
                    <td><?php _e('Display specific services only (comma-separated IDs)', 'construction-service-calculator'); ?></td>
                    <td><code>services="123,456,789"</code></td>
                </tr>
                <tr>
                    <td><code>theme</code></td>
                    <td><?php _e('Color theme (default, blue, orange, dark)', 'construction-service-calculator'); ?></td>
                    <td><code>theme="blue"</code></td>
                </tr>
                <tr>
                    <td><code>show_contact_form</code></td>
                    <td><?php _e('Show or hide the contact form (yes/no)', 'construction-service-calculator'); ?></td>
                    <td><code>show_contact_form="no"</code></td>
                </tr>
                <tr>
                    <td><code>columns</code></td>
                    <td><?php _e('Number of columns for service display (1-4)', 'construction-service-calculator'); ?></td>
                    <td><code>columns="3"</code></td>
                </tr>
            </tbody>
        </table>
        
        <h3><?php _e('Example with Multiple Attributes', 'construction-service-calculator'); ?></h3>
        <div class="csc-admin-shortcode">
            <code>[construction_calculator title="Flooring Cost Calculator" category="flooring" theme="blue" columns="2"]</code>
            <button class="csc-copy-shortcode button button-small" data-shortcode='[construction_calculator title="Flooring Cost Calculator" category="flooring" theme="blue" columns="2"]'>
                <?php _e('Copy', 'construction-service-calculator'); ?>
            </button>
        </div>
    </div>
</div>

<style>
    .csc-admin-dashboard {
        margin-top: 20px;
    }
    
    .csc-admin-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .csc-version {
        color: #777;
        font-style: italic;
    }
    
    .csc-admin-cards {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .csc-admin-card {
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .csc-admin-card-header {
        padding: 15px;
        background-color: #f5f5f5;
        border-bottom: 1px solid #ddd;
    }
    
    .csc-admin-card-header h2 {
        margin: 0;
        font-size: 16px;
        display: flex;
        align-items: center;
    }
    
    .csc-admin-card-header h2 .dashicons {
        margin-right: 5px;
    }
    
    .csc-admin-card-content {
        padding: 20px;
        text-align: center;
    }
    
    .csc-admin-card-stat {
        font-size: 36px;
        font-weight: 600;
        margin-bottom: 10px;
        color: #0073aa;
    }
    
    .csc-admin-card-footer {
        padding: 10px 15px;
        background-color: #f9f9f9;
        border-top: 1px solid #ddd;
        text-align: center;
    }
    
    .csc-admin-boxes {
        display: grid;
        grid-template-columns: 3fr 2fr;
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .csc-admin-box {
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .csc-admin-box-header {
        padding: 15px;
        background-color: #f5f5f5;
        border-bottom: 1px solid #ddd;
    }
    
    .csc-admin-box-header h2 {
        margin: 0;
        font-size: 16px;
    }
    
    .csc-admin-box-content {
        padding: 20px;
    }
    
    .csc-admin-box-footer {
        padding-top: 15px;
        text-align: right;
    }
    
    .csc-status {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .csc-status-new {
        background-color: #e3f2fd;
        color: #0288d1;
    }
    
    .csc-status-in-progress {
        background-color: #fff8e1;
        color: #ffa000;
    }
    
    .csc-status-completed {
        background-color: #e8f5e9;
        color: #388e3c;
    }
    
    .csc-status-cancelled {
        background-color: #ffebee;
        color: #d32f2f;
    }
    
    .csc-popular-service {
        margin-bottom: 15px;
    }
    
    .csc-popular-service-name {
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .csc-popular-service-count {
        font-size: 12px;
        color: #777;
        margin-bottom: 5px;
    }
    
    .csc-popular-service-bar {
        height: 6px;
        background-color: #f0f0f0;
        border-radius: 3px;
        overflow: hidden;
    }
    
    .csc-popular-service-bar-inner {
        height: 100%;
        background-color: #0073aa;
        width: 0;
    }
    
    .csc-admin-help {
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        padding: 20px;
    }
    
    .csc-admin-help h2 {
        margin-top: 0;
    }
    
    .csc-admin-shortcode {
        background-color: #f5f5f5;
        border: 1px solid #ddd;
        border-radius: 3px;
        padding: 10px 15px;
        margin: 15px 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .csc-admin-shortcode code {
        background: none;
        padding: 0;
        margin: 0;
        font-size: 14px;
    }
    
    @media screen and (max-width: 1200px) {
        .csc-admin-cards {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media screen and (max-width: 782px) {
        .csc-admin-boxes {
            grid-template-columns: 1fr;
        }
    }
    
    @media screen and (max-width: 600px) {
        .csc-admin-cards {
            grid-template-columns: 1fr;
        }
        
        .csc-admin-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .csc-admin-header-right {
            margin-top: 10px;
        }
    }
</style>

<script>
    // Copy shortcode functionality
    document.addEventListener('DOMContentLoaded', function() {
        const copyButtons = document.querySelectorAll('.csc-copy-shortcode');
        
        copyButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const shortcode = this.getAttribute('data-shortcode');
                
                // Create temporary textarea
                const textarea = document.createElement('textarea');
                textarea.value = shortcode;
                textarea.setAttribute('readonly', '');
                textarea.style.position = 'absolute';
                textarea.style.left = '-9999px';
                document.body.appendChild(textarea);
                
                // Select and copy
                textarea.select();
                document.execCommand('copy');
                
                // Remove textarea
                document.body.removeChild(textarea);
                
                // Show feedback
                const originalText = this.textContent;
                this.textContent = '<?php _e('Copied!', 'construction-service-calculator'); ?>';
                
                // Reset after 2 seconds
                setTimeout(function() {
                    button.textContent = originalText;
                }, 2000);
            });
        });
    });
</script>