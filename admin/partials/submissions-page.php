<?php
/**
 * Template for the submissions list page.
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

// Get status options
$status_options = array(
    '' => __('All Submissions', 'construction-service-calculator'),
    'new' => __('New', 'construction-service-calculator'),
    'in-progress' => __('In Progress', 'construction-service-calculator'),
    'completed' => __('Completed', 'construction-service-calculator'),
    'cancelled' => __('Cancelled', 'construction-service-calculator')
);

// Get the current status filter
$current_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
?>

<div class="wrap csc-submissions-container">
    <div class="csc-submissions-header">
        <h1 class="csc-submissions-title"><?php _e('Submissions', 'construction-service-calculator'); ?></h1>
    </div>
    
    <?php 
    // Display bulk action results
    if (isset($_GET['bulk-action']) && isset($_GET['count'])) {
        $action = sanitize_text_field($_GET['bulk-action']);
        $count = intval($_GET['count']);
        
        $messages = array(
            'delete' => __('Deleted %d submissions.', 'construction-service-calculator'),
            'mark_complete' => __('Marked %d submissions as completed.', 'construction-service-calculator'),
            'mark_in_progress' => __('Marked %d submissions as in progress.', 'construction-service-calculator'),
            'mark_cancelled' => __('Marked %d submissions as cancelled.', 'construction-service-calculator')
        );
        
        if (isset($messages[$action])) {
            printf('<div class="notice notice-success is-dismissible"><p>' . $messages[$action] . '</p></div>', $count);
        }
    }
    
    // Display individual action messages
    if (isset($_GET['deleted']) && $_GET['deleted'] == '1') {
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Submission deleted successfully.', 'construction-service-calculator') . '</p></div>';
    }
    
    if (isset($_GET['updated']) && $_GET['updated'] == 'true') {
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Submission updated successfully.', 'construction-service-calculator') . '</p></div>';
    }
    ?>
    
    <div class="csc-submission-filters">
        <form method="get" action="">
            <input type="hidden" name="page" value="csc-submissions">
            
            <div class="csc-filter-row">
                <label for="status-filter"><?php _e('Filter by Status:', 'construction-service-calculator'); ?></label>
                <select name="status" id="status-filter">
                    <?php foreach ($status_options as $value => $label) : ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($current_status, $value); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" class="button"><?php _e('Filter', 'construction-service-calculator'); ?></button>
                
                <?php if (!empty($current_status)) : ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=csc-submissions')); ?>" class="button">
                        <?php _e('Reset', 'construction-service-calculator'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <form method="post" action="">
        <?php wp_nonce_field('csc_submissions_bulk_action', 'csc_submissions_nonce'); ?>
        
        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <label for="bulk-action-selector-top" class="screen-reader-text"><?php _e('Select bulk action', 'construction-service-calculator'); ?></label>
                <select name="csc_bulk_action" id="bulk-action-selector-top">
                    <option value=""><?php _e('Bulk Actions', 'construction-service-calculator'); ?></option>
                    <option value="delete"><?php _e('Delete', 'construction-service-calculator'); ?></option>
                    <option value="mark_complete"><?php _e('Mark as Completed', 'construction-service-calculator'); ?></option>
                    <option value="mark_in_progress"><?php _e('Mark as In Progress', 'construction-service-calculator'); ?></option>
                    <option value="mark_cancelled"><?php _e('Mark as Cancelled', 'construction-service-calculator'); ?></option>
                </select>
                <input type="submit" class="button action" value="<?php esc_attr_e('Apply', 'construction-service-calculator'); ?>">
            </div>
            
            <?php if ($total_pages > 1) : ?>
                <div class="tablenav-pages">
                    <span class="displaying-num">
                        <?php 
                        printf(
                            _n('%s item', '%s items', $total_submissions, 'construction-service-calculator'),
                            number_format_i18n($total_submissions)
                        ); 
                        ?>
                    </span>
                    <span class="pagination-links">
                        <?php
                        echo paginate_links(array(
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => '&laquo;',
                            'next_text' => '&raquo;',
                            'total' => $total_pages,
                            'current' => $current_page
                        ));
                        ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
        
        <table class="wp-list-table widefat fixed striped submissions">
            <thead>
                <tr>
                    <td id="cb" class="manage-column column-cb check-column">
                        <input id="csc_select_all" type="checkbox">
                    </td>
                    <th scope="col" class="manage-column column-id"><?php _e('ID', 'construction-service-calculator'); ?></th>
                    <th scope="col" class="manage-column column-customer"><?php _e('Customer', 'construction-service-calculator'); ?></th>
                    <th scope="col" class="manage-column column-email"><?php _e('Email', 'construction-service-calculator'); ?></th>
                    <th scope="col" class="manage-column column-date"><?php _e('Date', 'construction-service-calculator'); ?></th>
                    <th scope="col" class="manage-column column-total"><?php _e('Total', 'construction-service-calculator'); ?></th>
                    <th scope="col" class="manage-column column-status"><?php _e('Status', 'construction-service-calculator'); ?></th>
                    <th scope="col" class="manage-column column-actions"><?php _e('Actions', 'construction-service-calculator'); ?></th>
                </tr>
            </thead>
            
            <tbody>
                <?php if (empty($submissions)) : ?>
                    <tr>
                        <td colspan="8"><?php _e('No submissions found.', 'construction-service-calculator'); ?></td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($submissions as $submission) : 
                        $metadata = $submission->metadata;
                        $date = isset($metadata['date']) ? $metadata['date'] : $submission->post_date;
                        $status = isset($metadata['status']) ? $metadata['status'] : 'new';
                        $calculation = isset($metadata['calculation']) ? $metadata['calculation'] : array();
                        $customer_info = isset($metadata['customer_info']) ? $metadata['customer_info'] : array();
                        
                        // Format the date
                        $formatted_date = date_i18n(get_option('date_format'), strtotime($date));
                        
                        // Get customer info
                        $customer_name = isset($customer_info['name']) ? $customer_info['name'] : __('Anonymous', 'construction-service-calculator');
                        $customer_email = isset($customer_info['email']) ? $customer_info['email'] : '';
                        
                        // Get total
                        $total = isset($calculation['grand_total_formatted']) ? $calculation['grand_total_formatted'] : __('N/A', 'construction-service-calculator');
                        
                        // Get status class and label
                        $status_class = 'csc-status-' . $status;
                        $status_labels = array(
                            'new' => __('New', 'construction-service-calculator'),
                            'in-progress' => __('In Progress', 'construction-service-calculator'),
                            'completed' => __('Completed', 'construction-service-calculator'),
                            'cancelled' => __('Cancelled', 'construction-service-calculator')
                        );
                        $status_label = isset($status_labels[$status]) ? $status_labels[$status] : $status;
                    ?>
                        <tr>
                            <th scope="row" class="check-column">
                                <input type="checkbox" name="csc_submissions[]" value="<?php echo esc_attr($submission->ID); ?>" class="csc-submission-checkbox">
                            </th>
                            <td><?php echo esc_html($submission->ID); ?></td>
                            <td><?php echo esc_html($customer_name); ?></td>
                            <td>
                                <?php if (!empty($customer_email)) : ?>
                                    <a href="mailto:<?php echo esc_attr($customer_email); ?>"><?php echo esc_html($customer_email); ?></a>
                                <?php else : ?>
                                    <?php _e('N/A', 'construction-service-calculator'); ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($formatted_date); ?></td>
                            <td><?php echo esc_html($total); ?></td>
                            <td>
                                <span class="csc-submission-status <?php echo esc_attr($status_class); ?>">
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
                <?php endif; ?>
            </tbody>
            
            <tfoot>
                <tr>
                    <td class="manage-column column-cb check-column">
                        <input type="checkbox">
                    </td>
                    <th scope="col" class="manage-column column-id"><?php _e('ID', 'construction-service-calculator'); ?></th>
                    <th scope="col" class="manage-column column-customer"><?php _e('Customer', 'construction-service-calculator'); ?></th>
                    <th scope="col" class="manage-column column-email"><?php _e('Email', 'construction-service-calculator'); ?></th>
                    <th scope="col" class="manage-column column-date"><?php _e('Date', 'construction-service-calculator'); ?></th>
                    <th scope="col" class="manage-column column-total"><?php _e('Total', 'construction-service-calculator'); ?></th>
                    <th scope="col" class="manage-column column-status"><?php _e('Status', 'construction-service-calculator'); ?></th>
                    <th scope="col" class="manage-column column-actions"><?php _e('Actions', 'construction-service-calculator'); ?></th>
                </tr>
            </tfoot>
        </table>
        
        <div class="tablenav bottom">
            <div class="alignleft actions bulkactions">
                <label for="bulk-action-selector-bottom" class="screen-reader-text"><?php _e('Select bulk action', 'construction-service-calculator'); ?></label>
                <select name="csc_bulk_action" id="bulk-action-selector-bottom">
                    <option value=""><?php _e('Bulk Actions', 'construction-service-calculator'); ?></option>
                    <option value="delete"><?php _e('Delete', 'construction-service-calculator'); ?></option>
                    <option value="mark_complete"><?php _e('Mark as Completed', 'construction-service-calculator'); ?></option>
                    <option value="mark_in_progress"><?php _e('Mark as In Progress', 'construction-service-calculator'); ?></option>
                    <option value="mark_cancelled"><?php _e('Mark as Cancelled', 'construction-service-calculator'); ?></option>
                </select>
                <input type="submit" class="button action" value="<?php esc_attr_e('Apply', 'construction-service-calculator'); ?>">
            </div>
            
            <?php if ($total_pages > 1) : ?>
                <div class="tablenav-pages">
                    <span class="displaying-num">
                        <?php 
                        printf(
                            _n('%s item', '%s items', $total_submissions, 'construction-service-calculator'),
                            number_format_i18n($total_submissions)
                        ); 
                        ?>
                    </span>
                    <span class="pagination-links">
                        <?php
                        echo paginate_links(array(
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => '&laquo;',
                            'next_text' => '&raquo;',
                            'total' => $total_pages,
                            'current' => $current_page
                        ));
                        ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </form>
</div>