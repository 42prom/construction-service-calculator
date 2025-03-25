<?php
/**
 * The submissions management functionality of the plugin.
 *
 * @link       https://github.com/42prom
 * @since      1.0.0
 *
 * @package    Construction_Service_Calculator
 * @subpackage Construction_Service_Calculator/admin
 */

/**
 * The submissions management functionality of the plugin.
 *
 * Handles the viewing and management of customer submissions.
 *
 * @package    Construction_Service_Calculator
 * @subpackage Construction_Service_Calculator/admin
 * @author     Mikheili Nakeuri
 */
class Construction_Service_Calculator_Submissions {

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
        
        // Register AJAX handler for viewing HTML estimate
        add_action('wp_ajax_csc_view_estimate', array($this, 'ajax_view_estimate'));
    }
    
    /**
     * Handle AJAX view estimate request.
     *
     * @since    1.0.0
     */
    public function ajax_view_estimate() {
        // Check for nonce
        if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'csc_view_estimate')) {
            wp_die(__('Security check failed.', 'construction-service-calculator'));
        }
        
        // Check for submission ID
        if (!isset($_GET['submission_id'])) {
            wp_die(__('No submission ID provided.', 'construction-service-calculator'));
        }
        
        // Get submission ID
        $submission_id = intval($_GET['submission_id']);
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to view this estimate.', 'construction-service-calculator'));
        }
        
        // Get HTML estimate
        $html_estimate = get_post_meta($submission_id, '_csc_html_estimate', true);
        
        if (empty($html_estimate)) {
            wp_die(__('No HTML estimate found for this submission.', 'construction-service-calculator'));
        }
        
        // Output the HTML estimate
        echo $html_estimate;
        exit;
    }

    /**
     * Register meta boxes for the submission post type.
     *
     * @since    1.0.0
     */
    public function register_meta_boxes() {
        add_meta_box(
            'csc_submission_details',
            __('Submission Details', 'construction-service-calculator'),
            array($this, 'render_submission_details_meta_box'),
            'csc_submission',
            'normal',
            'high'
        );
        
        add_meta_box(
            'csc_submission_services',
            __('Requested Services', 'construction-service-calculator'),
            array($this, 'render_submission_services_meta_box'),
            'csc_submission',
            'normal',
            'default'
        );
        
        add_meta_box(
            'csc_submission_customer',
            __('Customer Information', 'construction-service-calculator'),
            array($this, 'render_submission_customer_meta_box'),
            'csc_submission',
            'side',
            'default'
        );
        
        add_meta_box(
            'csc_submission_actions',
            __('Actions', 'construction-service-calculator'),
            array($this, 'render_submission_actions_meta_box'),
            'csc_submission',
            'side',
            'high'
        );
    }

    /**
     * Render the submission details meta box.
     *
     * @since    1.0.0
     * @param    WP_Post    $post    The post object.
     */
    public function render_submission_details_meta_box($post) {
        // Get submission metadata
        $data_handler = new Construction_Service_Calculator_Data_Handler();
        $metadata = $data_handler->get_submission_metadata($post->ID);
        
        $date = isset($metadata['date']) ? $metadata['date'] : $post->post_date;
        $status = isset($metadata['status']) ? $metadata['status'] : 'new';
        $calculation = isset($metadata['calculation']) ? $metadata['calculation'] : array();
        
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
        <table class="form-table csc-meta-table">
            <tr>
                <th><?php _e('Submission ID', 'construction-service-calculator'); ?></th>
                <td><?php echo esc_html($post->ID); ?></td>
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
                <th><?php _e('Subtotal', 'construction-service-calculator'); ?></th>
                <td>
                    <?php 
                    if (isset($calculation['total_subtotal_formatted'])) {
                        echo esc_html($calculation['total_subtotal_formatted']);
                    } else {
                        _e('N/A', 'construction-service-calculator');
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th><?php _e('Tax', 'construction-service-calculator'); ?></th>
                <td>
                    <?php 
                    if (isset($calculation['total_tax_formatted'])) {
                        echo esc_html($calculation['total_tax_formatted']);
                    } else {
                        _e('N/A', 'construction-service-calculator');
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th><?php _e('Grand Total', 'construction-service-calculator'); ?></th>
                <td>
                    <?php 
                    if (isset($calculation['grand_total_formatted'])) {
                        echo '<strong>' . esc_html($calculation['grand_total_formatted']) . '</strong>';
                    } else {
                        _e('N/A', 'construction-service-calculator');
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th><?php _e('Notes', 'construction-service-calculator'); ?></th>
                <td>
                    <textarea name="csc_submission_notes" id="csc_submission_notes" rows="4" class="large-text"><?php echo esc_textarea(isset($metadata['notes']) ? $metadata['notes'] : ''); ?></textarea>
                </td>
            </tr>
        </table>
        
        <?php wp_nonce_field('csc_submission_meta_nonce', 'csc_submission_meta_nonce'); ?>
        <?php
    }

    /**
     * Render the submission services meta box.
     *
     * @since    1.0.0
     * @param    WP_Post    $post    The post object.
     */
    public function render_submission_services_meta_box($post) {
        // Get submission metadata
        $data_handler = new Construction_Service_Calculator_Data_Handler();
        $metadata = $data_handler->get_submission_metadata($post->ID);
        
        $calculation = isset($metadata['calculation']) ? $metadata['calculation'] : array();
        $services = isset($calculation['services']) ? $calculation['services'] : array();
        
        if (empty($services)) {
            echo '<p>' . __('No services in this submission.', 'construction-service-calculator') . '</p>';
            return;
        }
        ?>
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
                <?php foreach ($services as $service) : ?>
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
                <tr>
                    <th colspan="3"><?php _e('Grand Total', 'construction-service-calculator'); ?></th>
                    <th><?php echo esc_html($calculation['grand_total_formatted']); ?></th>
                </tr>
            </tfoot>
        </table>
        <?php
    }

    /**
     * Render the submission customer meta box.
     *
     * @since    1.0.0
     * @param    WP_Post    $post    The post object.
     */
    public function render_submission_customer_meta_box($post) {
        // Get submission metadata
        $data_handler = new Construction_Service_Calculator_Data_Handler();
        $metadata = $data_handler->get_submission_metadata($post->ID);
        
        $customer_info = isset($metadata['customer_info']) ? $metadata['customer_info'] : array();
        
        // Get customer information
        $customer_name = isset($customer_info['name']) ? $customer_info['name'] : __('Anonymous', 'construction-service-calculator');
        $customer_email = isset($customer_info['email']) ? $customer_info['email'] : '';
        $customer_phone = isset($customer_info['phone']) ? $customer_info['phone'] : '';
        $customer_message = isset($customer_info['message']) ? $customer_info['message'] : '';
        ?>
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
        <?php
    }

    /**
     * Render the submission actions meta box.
     *
     * @since    1.0.0
     * @param    WP_Post    $post    The post object.
     */
    public function render_submission_actions_meta_box($post) {
        // Get submission metadata
        $data_handler = new Construction_Service_Calculator_Data_Handler();
        $metadata = $data_handler->get_submission_metadata($post->ID);
        
        $customer_info = isset($metadata['customer_info']) ? $metadata['customer_info'] : array();
        $html_estimate = isset($metadata['html_estimate']) ? $metadata['html_estimate'] : '';
        
        // Get customer email
        $customer_email = isset($customer_info['email']) ? $customer_info['email'] : '';
        ?>
        <div class="csc-submission-actions">
            <button type="submit" class="button button-primary" name="csc_save_submission">
                <?php _e('Save Changes', 'construction-service-calculator'); ?>
            </button>
            
            <div class="csc-action-divider"></div>
            
            <?php if (!empty($html_estimate)) : ?>
                <a href="<?php echo esc_url(admin_url('admin-ajax.php?action=csc_view_estimate&submission_id=' . $post->ID . '&nonce=' . wp_create_nonce('csc_view_estimate'))); ?>" class="button" target="_blank">
                    <?php _e('View Estimate', 'construction-service-calculator'); ?>
                </a>
            <?php endif; ?>
            
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
        <?php
    }

    /**
     * Save submission meta when the post is saved.
     *
     * @since    1.0.0
     * @param    int    $post_id    The ID of the post being saved.
     */
    public function save_submission_meta($post_id) {
        // Check if our nonce is set
        if (!isset($_POST['csc_submission_meta_nonce'])) {
            return;
        }
        
        // Verify the nonce
        if (!wp_verify_nonce($_POST['csc_submission_meta_nonce'], 'csc_submission_meta_nonce')) {
            return;
        }
        
        // If this is an autosave, we don't want to do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check the user's permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save submission status
        if (isset($_POST['csc_submission_status'])) {
            update_post_meta($post_id, '_csc_status', sanitize_text_field($_POST['csc_submission_status']));
        }
        
        // Save submission notes
        if (isset($_POST['csc_submission_notes'])) {
            update_post_meta($post_id, '_csc_notes', sanitize_textarea_field($_POST['csc_submission_notes']));
        }
        
        // Handle reply to customer
        if (isset($_POST['csc_reply_to']) && isset($_POST['csc_reply_subject']) && isset($_POST['csc_reply_message'])) {
            $this->send_customer_reply($post_id, $_POST['csc_reply_to'], $_POST['csc_reply_subject'], $_POST['csc_reply_message']);
        }
    }

    /**
     * Send reply to customer.
     *
     * @since    1.0.0
     * @param    int       $submission_id    Submission post ID.
     * @param    string    $to               Customer email.
     * @param    string    $subject          Email subject.
     * @param    string    $message          Email message.
     */
    private function send_customer_reply($submission_id, $to, $subject, $message) {
        // Sanitize inputs
        $to = sanitize_email($to);
        $subject = sanitize_text_field($subject);
        $message = sanitize_textarea_field($message);
        
        // Check for valid email
        if (!is_email($to)) {
            return;
        }
        
        // Get site info
        $site_name = get_bloginfo('name');
        
        // Set up headers
        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . $site_name . ' <' . get_option('admin_email') . '>',
            'Reply-To: ' . get_option('admin_email')
        );
        
        // Send the email
        $sent = wp_mail($to, $subject, $message, $headers);
        
        // Log the result
        if ($sent) {
            // Add note about the reply
            $notes = get_post_meta($submission_id, '_csc_notes', true);
            $notes .= "\n\n" . sprintf(
                __('Reply sent to customer on %s - Subject: %s', 'construction-service-calculator'),
                current_time('mysql'),
                $subject
            );
            update_post_meta($submission_id, '_csc_notes', $notes);
        }
    }

    /**
     * Display the submissions admin page.
     *
     * @since    1.0.0
     */
    public function display_submissions_page() {
        // Check if viewing a single submission
        if (isset($_GET['view'])) {
            $submission_id = intval($_GET['view']);
            $submission = get_post($submission_id);
            
            if (!$submission || $submission->post_type !== 'csc_submission') {
                wp_die(__('Submission not found.', 'construction-service-calculator'));
            }
            
            // Handle form submissions
            if (isset($_POST['csc_save_and_return'])) {
                // Update submission data
                $this->save_submission_meta($submission_id);
                
                // Redirect back to list view
                $redirect_url = admin_url('admin.php?page=csc-submissions&updated=true');
                echo "<script>window.location.href = '{$redirect_url}';</script>";
                echo "<meta http-equiv='refresh' content='0;URL={$redirect_url}'>";
                echo "<p>Redirecting to <a href='{$redirect_url}'>submissions list</a>...</p>";
                exit;
            }
            else if (isset($_POST['csc_save_submission'])) {
                // Update submission
                $this->save_submission_meta($submission_id);
                
                // Redirect to same page with updated parameter
                $redirect_url = admin_url('admin.php?page=csc-submissions&view=' . $submission_id . '&updated=true');
                wp_redirect($redirect_url);
                exit;
            }
            
            // Show single submission view
            require_once CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'admin/partials/submission-view.php';
        } 
        else {
            // Process bulk actions
            if (isset($_POST['csc_bulk_action']) && !empty($_POST['csc_bulk_action']) && 
                isset($_POST['csc_submissions']) && !empty($_POST['csc_submissions'])) {
                
                // Verify nonce
                check_admin_referer('csc_submissions_bulk_action', 'csc_submissions_nonce');
                
                $action = sanitize_text_field($_POST['csc_bulk_action']);
                $ids = array_map('intval', $_POST['csc_submissions']);
                $count = count($ids);
                
                switch ($action) {
                    case 'delete':
                        foreach ($ids as $id) {
                            wp_delete_post($id, true);
                        }
                        break;
                        
                    case 'mark_complete':
                        foreach ($ids as $id) {
                            update_post_meta($id, '_csc_status', 'completed');
                        }
                        break;
                        
                    case 'mark_in_progress':
                        foreach ($ids as $id) {
                            update_post_meta($id, '_csc_status', 'in-progress');
                        }
                        break;
                        
                    case 'mark_cancelled':
                        foreach ($ids as $id) {
                            update_post_meta($id, '_csc_status', 'cancelled');
                        }
                        break;
                }
                
                // Use JavaScript redirect as a fallback
                $redirect_url = admin_url('admin.php?page=csc-submissions&bulk-action=' . $action . '&count=' . $count);
                echo "<script>window.location.href = '{$redirect_url}';</script>";
                echo "<meta http-equiv='refresh' content='0;URL={$redirect_url}'>";
                echo "<p>Redirecting to <a href='{$redirect_url}'>submissions list</a>...</p>";
                exit;
            }
            
            // Get submissions list
            $data_handler = new Construction_Service_Calculator_Data_Handler();
            
            // Pagination
            $per_page = 20;
            $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
            $offset = ($current_page - 1) * $per_page;
            
            // Status filter
            $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
            
            // Get submissions
            $submissions = $data_handler->get_submissions($status_filter, $per_page, $offset);
            
            // Count total submissions for pagination
            $total_submissions = wp_count_posts('csc_submission')->publish;
            $total_pages = ceil($total_submissions / $per_page);
            
            // Show submissions list
            require_once CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'admin/partials/submissions-page.php';
        }
    }

    /**
     * Process bulk actions on submissions.
     *
     * @since    1.0.0
     * @param    string    $action         The bulk action to perform.
     * @param    array     $submission_ids Array of submission IDs.
     */
    private function process_bulk_action($action, $submission_ids) {
        // Verify nonce
        if (!isset($_POST['csc_submissions_nonce']) || !wp_verify_nonce($_POST['csc_submissions_nonce'], 'csc_submissions_bulk_action')) {
            return;
        }
        
        // Check user permissions
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Make sure IDs are integers
        $submission_ids = array_map('intval', $submission_ids);
        
        switch ($action) {
            case 'delete':
                foreach ($submission_ids as $id) {
                    wp_delete_post($id, true);
                }
                break;
                
            case 'mark_complete':
                foreach ($submission_ids as $id) {
                    update_post_meta($id, '_csc_status', 'completed');
                }
                break;
                
            case 'mark_in_progress':
                foreach ($submission_ids as $id) {
                    update_post_meta($id, '_csc_status', 'in-progress');
                }
                break;
                
            case 'mark_cancelled':
                foreach ($submission_ids as $id) {
                    update_post_meta($id, '_csc_status', 'cancelled');
                }
                break;
        }
        
        // Redirect to prevent form resubmission, using add_query_arg to preserve any status filters
        $redirect_url = add_query_arg(
            array(
                'page' => 'csc-submissions',
                'bulk-action' => $action,
                'count' => count($submission_ids)
            ),
            admin_url('admin.php')
        );
        
        // Preserve any existing status filter
        if (isset($_GET['status']) && !empty($_GET['status'])) {
            $redirect_url = add_query_arg('status', sanitize_text_field($_GET['status']), $redirect_url);
        }
        
        wp_redirect($redirect_url);
        exit;
    }
}