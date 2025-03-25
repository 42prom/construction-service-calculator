<?php
/**
 * Template for viewing a single submission.
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

// Get submission metadata
$data_handler = new Construction_Service_Calculator_Data_Handler();
$metadata = $data_handler->get_submission_metadata($submission->ID);

// Display updated message
if (isset($_GET['updated']) && $_GET['updated'] === 'true') {
    echo '<div class="notice notice-success is-dismissible"><p>' . __('Submission updated successfully.', 'construction-service-calculator') . '</p></div>';
}

// Extract metadata for easy access
$date = isset($metadata['date']) ? $metadata['date'] : $submission->post_date;
$status = isset($metadata['status']) ? $metadata['status'] : 'new';
$calculation = isset($metadata['calculation']) ? $metadata['calculation'] : array();
$customer_info = isset($metadata['customer_info']) ? $metadata['customer_info'] : array();
$notes = isset($metadata['notes']) ? $metadata['notes'] : '';
$html_estimate = isset($metadata['html_estimate']) ? $metadata['html_estimate'] : '';

// Get customer information
$customer_name = isset($customer_info['name']) ? sanitize_text_field($customer_info['name']) : __('Anonymous', 'construction-service-calculator');
$customer_email = isset($customer_info['email']) ? sanitize_email($customer_info['email']) : '';
$customer_phone = isset($customer_info['phone']) ? sanitize_text_field($customer_info['phone']) : '';
$customer_message = isset($customer_info['message']) ? sanitize_textarea_field($customer_info['message']) : '';

// Format the date
$formatted_date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($date));

// Get status options
$status_options = array(
    'new' => __('New', 'construction-service-calculator'),
    'in-progress' => __('In Progress', 'construction-service-calculator'),
    'completed' => __('Completed', 'construction-service-calculator'),
    'cancelled' => __('Cancelled', 'construction-service-calculator')
);
?>

<div class="wrap csc-submission-view">
    <h1 class="wp-heading-inline">
        <?php printf(__('Submission #%d', 'construction-service-calculator'), $submission->ID); ?>
    </h1>
    
    <a href="<?php echo esc_url(admin_url('admin.php?page=csc-submissions')); ?>" class="page-title-action">
        <?php _e('Back to Submissions', 'construction-service-calculator'); ?>
    </a>
    
    <hr class="wp-header-end">
    
    <form method="post" id="post">
        <?php wp_nonce_field('csc_submission_meta_nonce', 'csc_submission_meta_nonce'); ?>
        
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <!-- Main Content -->
                <div id="post-body-content">
                    <!-- Submission Details -->
                    <div class="postbox">
                        <h2 class="hndle"><?php _e('Submission Details', 'construction-service-calculator'); ?></h2>
                        <div class="inside">
                            <table class="form-table csc-meta-table">
                                <tr>
                                    <th><?php _e('Submission ID', 'construction-service-calculator'); ?></th>
                                    <td><?php echo esc_html($submission->ID); ?></td>
                                </tr>
                                <tr>
                                    <th><?php _e('Date', 'construction-service-calculator'); ?></th>
                                    <td><?php echo esc_html($formatted_date); ?></td>
                                </tr>
                                <tr>
                                    <th><?php _e('Status', 'construction-service-calculator'); ?></th>
                                    <td>
                                        <select name="csc_submission_status" id="csc_submission_status">
                                            <?php foreach ($status_options as $value => $label) : ?>
                                                <option value="<?php echo esc_attr($value); ?>" <?php selected($status, $value); ?>>
                                                    <?php echo esc_html($label); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php _e('Notes', 'construction-service-calculator'); ?></th>
                                    <td>
                                        <textarea name="csc_submission_notes" id="csc_submission_notes" rows="4" class="large-text"><?php echo esc_textarea($notes); ?></textarea>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Requested Services -->
                    <div class="postbox">
                        <h2 class="hndle"><?php _e('Requested Services', 'construction-service-calculator'); ?></h2>
                        <div class="inside">
                            <?php if (empty($calculation) || empty($calculation['services'])) : ?>
                                <p><?php _e('No services in this submission.', 'construction-service-calculator'); ?></p>
                            <?php else : ?>
                                <table class="widefat striped">
                                    <thead>
                                        <tr>
                                            <th><?php _e('Service', 'construction-service-calculator'); ?></th>
                                            <th><?php _e('Rate', 'construction-service-calculator'); ?></th>
                                            <th><?php _e('Quantity', 'construction-service-calculator'); ?></th>
                                            <th><?php _e('Subtotal', 'construction-service-calculator'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($calculation['services'] as $service) : ?>
                                            <tr>
                                                <td><?php echo esc_html($service['service_name']); ?></td>
                                                <td>
                                                    <?php 
                                                    echo esc_html($service['rate_formatted']); 
                                                    echo ' / ';
                                                    echo esc_html($service['unit_symbol']);
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    echo esc_html($service['quantity']); 
                                                    echo ' ';
                                                    echo esc_html($service['unit_symbol']);
                                                    ?>
                                                </td>
                                                <td><?php echo esc_html($service['subtotal_formatted']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3"><?php _e('Subtotal', 'construction-service-calculator'); ?></th>
                                            <th><?php echo esc_html($calculation['total_subtotal_formatted']); ?></th>
                                        </tr>
                                        <?php if (isset($calculation['total_tax_formatted']) && !empty($calculation['total_tax_formatted'])) : ?>
                                            <tr>
                                                <th colspan="3">
                                                    <?php 
                                                    printf(
                                                        __('Tax (%s%%)', 'construction-service-calculator'),
                                                        isset($calculation['tax_rate']) ? $calculation['tax_rate'] : ''
                                                    ); 
                                                    ?>
                                                </th>
                                                <th><?php echo esc_html($calculation['total_tax_formatted']); ?></th>
                                            </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <th colspan="3"><?php _e('Grand Total', 'construction-service-calculator'); ?></th>
                                            <th><?php echo esc_html($calculation['grand_total_formatted']); ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div id="postbox-container-1" class="postbox-container">
                    <!-- Actions Box -->
                    <div class="postbox">
                        <h2 class="hndle"><?php _e('Actions', 'construction-service-calculator'); ?></h2>
                        <div class="inside">
                            <div class="csc-submission-actions">
                                <button type="submit" class="button button-primary" name="csc_save_submission">
                                    <?php _e('Save Changes', 'construction-service-calculator'); ?>
                                </button>
                                
                                <button type="submit" class="button" name="csc_save_and_return">
                                    <?php _e('Save and Return to List', 'construction-service-calculator'); ?>
                                </button>
                                
                                <div class="csc-action-divider"></div>
                                
                                <?php if (!empty($html_estimate)) : ?>
                                    <a href="<?php echo esc_url(admin_url('admin-ajax.php?action=csc_view_estimate&submission_id=' . $submission->ID . '&nonce=' . wp_create_nonce('csc_view_estimate'))); ?>" class="button" target="_blank">
                                        <?php _e('View Estimate', 'construction-service-calculator'); ?>
                                    </a>
                                <?php endif; ?>
                                
                                <?php
                                // Add delete link
                                $delete_url = wp_nonce_url(
                                    add_query_arg(
                                        array(
                                            'page' => 'csc-submissions',
                                            'view' => $submission->ID,
                                            'action' => 'delete'
                                        ),
                                        admin_url('admin.php')
                                    ),
                                    'delete_submission_' . $submission->ID
                                );
                                ?>
                                <a href="<?php echo esc_url($delete_url); ?>" class="button" style="color: #a00;" onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this submission?', 'construction-service-calculator'); ?>');">
                                    <?php _e('Delete Submission', 'construction-service-calculator'); ?>
                                </a>
                                
                                <?php if (!empty($customer_email)) : ?>
                                    <button type="button" class="button csc-reply-button">
                                        <?php _e('Reply to Customer', 'construction-service-calculator'); ?>
                                    </button>
                                    
                                    <div class="csc-reply-form" style="display: none;">
                                        <h4><?php _e('Reply to Customer', 'construction-service-calculator'); ?></h4>
                                        <input type="hidden" name="csc_reply_to" value="<?php echo esc_attr($customer_email); ?>">
                                        <input type="text" name="csc_reply_subject" placeholder="<?php esc_attr_e('Subject', 'construction-service-calculator'); ?>" class="widefat">
                                        <textarea name="csc_reply_message" rows="5" placeholder="<?php esc_attr_e('Message', 'construction-service-calculator'); ?>" class="widefat"></textarea>
                                        <button type="button" class="button button-primary csc-send-reply-button">
                                            <?php _e('Send Reply', 'construction-service-calculator'); ?>
                                        </button>
                                        <button type="button" class="button csc-cancel-reply-button">
                                            <?php _e('Cancel', 'construction-service-calculator'); ?>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Customer Information Box -->
                    <div class="postbox">
                        <h2 class="hndle"><?php _e('Customer Information', 'construction-service-calculator'); ?></h2>
                        <div class="inside">
                            <table class="form-table csc-meta-table">
                                <tr>
                                    <th><?php _e('Name', 'construction-service-calculator'); ?></th>
                                    <td><?php echo esc_html($customer_name); ?></td>
                                </tr>
                                <?php if (!empty($customer_email)) : ?>
                                    <tr>
                                        <th><?php _e('Email', 'construction-service-calculator'); ?></th>
                                        <td>
                                            <a href="mailto:<?php echo esc_attr($customer_email); ?>"><?php echo esc_html($customer_email); ?></a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <?php if (!empty($customer_phone)) : ?>
                                    <tr>
                                        <th><?php _e('Phone', 'construction-service-calculator'); ?></th>
                                        <td><?php echo esc_html($customer_phone); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </table>
                            
                            <?php if (!empty($customer_message)) : ?>
                                <div class="csc-customer-message">
                                    <h4><?php _e('Customer Message', 'construction-service-calculator'); ?></h4>
                                    <div class="csc-message-content">
                                        <?php echo wpautop(esc_html($customer_message)); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    
    <script>
        // Reply form toggle
        jQuery(document).ready(function($) {
            $('.csc-reply-button').on('click', function() {
                $('.csc-reply-form').slideToggle();
            });
            
            $('.csc-cancel-reply-button').on('click', function() {
                $('.csc-reply-form').slideUp();
            });
            
            $('.csc-send-reply-button').on('click', function() {
                // Validate form
                var subject = $('input[name="csc_reply_subject"]').val();
                var message = $('textarea[name="csc_reply_message"]').val();
                
                if (!subject || !message) {
                    alert('<?php esc_attr_e('Please enter both subject and message.', 'construction-service-calculator'); ?>');
                    return;
                }
                
                // Submit the form
                $(this).prop('disabled', true).text('<?php esc_attr_e('Sending...', 'construction-service-calculator'); ?>');
                $('form#post').submit();
            });
            
            // Fix WordPress postboxes functionality
            if (typeof postboxes !== 'undefined') {
                // Close postboxes that should be closed
                $('.if-js-closed').removeClass('if-js-closed').addClass('closed');
                // Postboxes setup
                postboxes.add_postbox_toggles('csc_submission');
            }
        });
    </script>
</div>