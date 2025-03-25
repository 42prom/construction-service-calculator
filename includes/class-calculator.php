<?php
/**
 * Handle calculation logic for the construction service calculator
 *
 * @link       https://github.com/42prom
 * @since      1.0.0
 *
 * @package    Construction_Service_Calculator
 * @subpackage Construction_Service_Calculator/includes
 */

/**
 * Handle calculation logic for the construction service calculator.
 *
 * Core functionality for calculating service costs based on user input.
 *
 * @package    Construction_Service_Calculator
 * @subpackage Construction_Service_Calculator/includes
 * @author     Mikheili Nakeuri
 */
class Construction_Service_Calculator_Calculator {

    /**
     * Calculate service cost based on provided data.
     *
     * @since    1.0.0
     * @param    array    $service_data    Service data with ID, rate, quantity, etc.
     * @return   array                     Calculation results.
     */
    public function calculate_service_cost($service_data) {
        // Extract service data
        $service_id = isset($service_data['service_id']) ? intval($service_data['service_id']) : 0;
        $quantity = isset($service_data['quantity']) ? floatval($service_data['quantity']) : 0;
        
        // Validate inputs
        if (empty($service_id) || $quantity <= 0) {
            return array(
                'success' => false,
                'message' => __('Invalid service data.', 'construction-service-calculator')
            );
        }
        
        // Get service details
        $service = get_post($service_id);
        if (!$service || $service->post_type !== 'csc_service') {
            return array(
                'success' => false,
                'message' => __('Service not found.', 'construction-service-calculator')
            );
        }
        
        // Get service metadata
        $rate = floatval(get_post_meta($service_id, '_csc_rate', true));
        $unit_type = get_post_meta($service_id, '_csc_unit', true);
        
        // Get service units
        $service_units = get_option('csc_service_units', array());
        $unit_symbol = isset($service_units[$unit_type]['symbol']) ? $service_units[$unit_type]['symbol'] : '';
        
        // Calculate subtotal
        $subtotal = $rate * $quantity;
        
        // Get tax settings
        $tax_rate = floatval(get_option('csc_tax_rate', 0));
        $tax_display = get_option('csc_tax_display', 'yes');
        
        // Calculate tax and total
        $tax_amount = ($tax_display === 'yes') ? ($subtotal * $tax_rate / 100) : 0;
        $total = $subtotal + $tax_amount;
        
        // Format currency values
        $currency_symbol = get_option('csc_currency_symbol', '$');
        $currency_position = get_option('csc_currency_position', 'before');
        $decimal_separator = get_option('csc_decimal_separator', '.');
        $thousand_separator = get_option('csc_thousand_separator', ',');
        $decimals = intval(get_option('csc_decimals', 2));
        
        $subtotal_formatted = $this->format_price($subtotal, $currency_symbol, $currency_position, $decimal_separator, $thousand_separator, $decimals);
        $tax_formatted = $this->format_price($tax_amount, $currency_symbol, $currency_position, $decimal_separator, $thousand_separator, $decimals);
        $total_formatted = $this->format_price($total, $currency_symbol, $currency_position, $decimal_separator, $thousand_separator, $decimals);
        
        // Return calculated values
        return array(
            'success' => true,
            'service_id' => $service_id,
            'service_name' => $service->post_title,
            'rate' => $rate,
            'rate_formatted' => $this->format_price($rate, $currency_symbol, $currency_position, $decimal_separator, $thousand_separator, $decimals),
            'quantity' => $quantity,
            'unit_type' => $unit_type,
            'unit_symbol' => $unit_symbol,
            'subtotal' => $subtotal,
            'subtotal_formatted' => $subtotal_formatted,
            'tax_rate' => $tax_rate,
            'tax_amount' => $tax_amount,
            'tax_formatted' => $tax_formatted,
            'total' => $total,
            'total_formatted' => $total_formatted
        );
    }
    
    /**
     * Format price according to settings.
     *
     * @since    1.0.0
     * @param    float     $price                 Price to format.
     * @param    string    $currency_symbol       Currency symbol.
     * @param    string    $currency_position     Position of currency symbol (before or after).
     * @param    string    $decimal_separator     Decimal separator.
     * @param    string    $thousand_separator    Thousand separator.
     * @param    int       $decimals              Number of decimals.
     * @return   string                           Formatted price.
     */
    public function format_price($price, $currency_symbol, $currency_position, $decimal_separator, $thousand_separator, $decimals) {
        $price = number_format($price, $decimals, $decimal_separator, $thousand_separator);
        
        if ($currency_position === 'before') {
            return $currency_symbol . $price;
        } else {
            return $price . $currency_symbol;
        }
    }
    
    /**
     * Handle AJAX calculation request.
     *
     * @since    1.0.0
     */
    public function ajax_calculate() {
        // Check for nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'csc_ajax_nonce')) {
            wp_send_json_error(__('Security check failed.', 'construction-service-calculator'));
        }
        
        // Get service data from request
        $service_data = isset($_POST['service_data']) ? $_POST['service_data'] : array();
        
        // Validate service data
        if (empty($service_data) || !isset($service_data['service_id']) || !isset($service_data['quantity'])) {
            wp_send_json_error(__('Invalid service data.', 'construction-service-calculator'));
        }
        
        // Calculate service cost
        $result = $this->calculate_service_cost($service_data);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    /**
     * Calculate total cost for multiple services.
     *
     * @since    1.0.0
     * @param    array    $services    Array of service data.
     * @return   array                 Calculation results.
     */
    public function calculate_total_cost($services) {
        if (empty($services) || !is_array($services)) {
            return array(
                'success' => false,
                'message' => __('No services selected.', 'construction-service-calculator')
            );
        }
        
        $total_subtotal = 0;
        $total_tax = 0;
        $total = 0;
        $results = array();
        
        // Get tax settings
        $tax_rate = floatval(get_option('csc_tax_rate', 0));
        $tax_display = get_option('csc_tax_display', 'yes');
        
        // Calculate each service
        foreach ($services as $service_data) {
            $result = $this->calculate_service_cost($service_data);
            
            if ($result['success']) {
                $results[] = $result;
                $total_subtotal += $result['subtotal'];
                $total_tax += $result['tax_amount'];
            }
        }
        
        // Calculate grand total
        $total = $total_subtotal + $total_tax;
        
        // Format currency values
        $currency_symbol = get_option('csc_currency_symbol', '$');
        $currency_position = get_option('csc_currency_position', 'before');
        $decimal_separator = get_option('csc_decimal_separator', '.');
        $thousand_separator = get_option('csc_thousand_separator', ',');
        $decimals = intval(get_option('csc_decimals', 2));
        
        $total_subtotal_formatted = $this->format_price($total_subtotal, $currency_symbol, $currency_position, $decimal_separator, $thousand_separator, $decimals);
        $total_tax_formatted = $this->format_price($total_tax, $currency_symbol, $currency_position, $decimal_separator, $thousand_separator, $decimals);
        $total_formatted = $this->format_price($total, $currency_symbol, $currency_position, $decimal_separator, $thousand_separator, $decimals);
        
        return array(
            'success' => true,
            'services' => $results,
            'total_subtotal' => $total_subtotal,
            'total_subtotal_formatted' => $total_subtotal_formatted,
            'tax_rate' => $tax_rate,
            'total_tax' => $total_tax,
            'total_tax_formatted' => $total_tax_formatted,
            'grand_total' => $total,
            'grand_total_formatted' => $total_formatted
        );
    }
    
    /**
     * Generate HTML estimate.
     *
     * @since    1.0.0
     * @param    array    $calculation    Calculation results.
     * @param    array    $customer_info  Customer information.
     * @return   string                   HTML content.
     */
    public function generate_html_estimate($calculation, $customer_info = array()) {
        if (!isset($calculation['success']) || !$calculation['success']) {
            return __('Invalid calculation data.', 'construction-service-calculator');
        }
        
        ob_start();
        
        // Get template file path
        $template_file = CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'public/partials/estimate-template.php';
        
        // Allow theme to override template
        $theme_template = locate_template('construction-service-calculator/estimate-template.php');
        if (!empty($theme_template)) {
            $template_file = $theme_template;
        }
        
        // Include template
        include $template_file;
        
        return ob_get_clean();
    }
    
    /**
     * Handle AJAX submit inquiry request.
     *
     * @since    1.0.0
     */
    public function ajax_submit_inquiry() {
        // Check for nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'csc_ajax_nonce')) {
            wp_send_json_error(__('Security check failed.', 'construction-service-calculator'));
            return;
        }
        
        // Get data from request and decode JSON if needed
        $services = isset($_POST['services']) ? $_POST['services'] : array();
        $customer_info = isset($_POST['customer_info']) ? $_POST['customer_info'] : array();
        
        // If services is a JSON string, decode it
        if (is_string($services)) {
            $services = json_decode(stripslashes($services), true);
        }
        
        // If customer_info is a JSON string, decode it
        if (is_string($customer_info)) {
            $customer_info = json_decode(stripslashes($customer_info), true);
        }
        
        // Validate data
        if (empty($services) || !is_array($services)) {
            wp_send_json_error(__('No services selected.', 'construction-service-calculator'));
            return;
        }
        
        // Calculate total cost
        $calculation = $this->calculate_total_cost($services);
        
        if (!$calculation['success']) {
            wp_send_json_error($calculation['message']);
            return;
        }
        
        // Create submission post
        $submission_title = sprintf(
            __('Estimate Request from %s', 'construction-service-calculator'),
            isset($customer_info['name']) ? sanitize_text_field($customer_info['name']) : __('Anonymous', 'construction-service-calculator')
        );
        
        $submission_id = wp_insert_post(array(
            'post_title' => $submission_title,
            'post_type' => 'csc_submission',
            'post_status' => 'publish'
        ));
        
        if (is_wp_error($submission_id)) {
            wp_send_json_error(__('Failed to create submission.', 'construction-service-calculator'));
            return;
        }
        
        // Sanitize customer info
        $sanitized_customer_info = array();
        if (is_array($customer_info)) {
            $sanitized_customer_info['name'] = isset($customer_info['name']) ? sanitize_text_field($customer_info['name']) : '';
            $sanitized_customer_info['email'] = isset($customer_info['email']) ? sanitize_email($customer_info['email']) : '';
            $sanitized_customer_info['phone'] = isset($customer_info['phone']) ? sanitize_text_field($customer_info['phone']) : '';
            $sanitized_customer_info['message'] = isset($customer_info['message']) ? sanitize_textarea_field($customer_info['message']) : '';
        }
        
        // Save calculation and customer info as post meta
        update_post_meta($submission_id, '_csc_calculation', $calculation);
        update_post_meta($submission_id, '_csc_customer_info', $sanitized_customer_info);
        update_post_meta($submission_id, '_csc_status', 'new');
        update_post_meta($submission_id, '_csc_date', current_time('mysql'));
        
        // Generate HTML estimate
        $html_estimate = $this->generate_html_estimate($calculation, $sanitized_customer_info);
        update_post_meta($submission_id, '_csc_html_estimate', $html_estimate);
        
        // Send email notification if enabled
        $email_notifications = get_option('csc_email_notifications', 'yes');
        if ($email_notifications === 'yes') {
            $this->send_email_notification($submission_id, $calculation, $sanitized_customer_info);
        }
        
        wp_send_json_success(array(
            'submission_id' => $submission_id,
            'html_estimate' => $html_estimate
        ));
    }
    
    /**
     * Handle AJAX generate HTML request.
     *
     * @since    1.0.0
     */
    public function ajax_generate_html() {
        // Check for nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'csc_ajax_nonce')) {
            wp_send_json_error(__('Security check failed.', 'construction-service-calculator'));
        }
        
        // Get data from request
        $services = isset($_POST['services']) ? $_POST['services'] : array();
        $customer_info = isset($_POST['customer_info']) ? $_POST['customer_info'] : array();
        
        // Validate data
        if (empty($services) || !is_array($services)) {
            wp_send_json_error(__('No services selected.', 'construction-service-calculator'));
        }
        
        // Calculate total cost
        $calculation = $this->calculate_total_cost($services);
        
        if (!$calculation['success']) {
            wp_send_json_error($calculation['message']);
        }
        
        // Generate HTML estimate
        $html_estimate = $this->generate_html_estimate($calculation, $customer_info);
        
        wp_send_json_success(array(
            'html_estimate' => $html_estimate
        ));
    }
    
    /**
     * Send email notification for new submission.
     *
     * @since    1.0.0
     * @param    int      $submission_id    Submission post ID.
     * @param    array    $calculation      Calculation results.
     * @param    array    $customer_info    Customer information.
     */
    private function send_email_notification($submission_id, $calculation, $customer_info) {
        $admin_email = get_option('csc_admin_email', get_option('admin_email'));
        $site_name = get_bloginfo('name');
        
        $subject = sprintf(
            __('[%s] New Construction Estimate Request #%d', 'construction-service-calculator'),
            $site_name,
            $submission_id
        );
        
        // Get customer information
        $customer_name = isset($customer_info['name']) ? sanitize_text_field($customer_info['name']) : __('Anonymous', 'construction-service-calculator');
        $customer_email = isset($customer_info['email']) ? sanitize_email($customer_info['email']) : '';
        $customer_phone = isset($customer_info['phone']) ? sanitize_text_field($customer_info['phone']) : '';
        $customer_message = isset($customer_info['message']) ? sanitize_textarea_field($customer_info['message']) : '';
        
        // Build email content
        $message = __('A new construction estimate request has been submitted.', 'construction-service-calculator') . "\n\n";
        $message .= __('Submission ID:', 'construction-service-calculator') . ' ' . $submission_id . "\n";
        $message .= __('Date:', 'construction-service-calculator') . ' ' . current_time('mysql') . "\n\n";
        
        $message .= __('Customer Information:', 'construction-service-calculator') . "\n";
        $message .= __('Name:', 'construction-service-calculator') . ' ' . $customer_name . "\n";
        if (!empty($customer_email)) {
            $message .= __('Email:', 'construction-service-calculator') . ' ' . $customer_email . "\n";
        }
        if (!empty($customer_phone)) {
            $message .= __('Phone:', 'construction-service-calculator') . ' ' . $customer_phone . "\n";
        }
        if (!empty($customer_message)) {
            $message .= __('Message:', 'construction-service-calculator') . "\n" . $customer_message . "\n";
        }
        
        $message .= "\n" . __('Estimate Summary:', 'construction-service-calculator') . "\n";
        foreach ($calculation['services'] as $service) {
            $message .= sprintf(
                "%s (%s %s): %s\n",
                $service['service_name'],
                $service['quantity'],
                $service['unit_symbol'],
                $service['subtotal_formatted']
            );
        }
        
        $message .= "\n" . __('Subtotal:', 'construction-service-calculator') . ' ' . $calculation['total_subtotal_formatted'] . "\n";
        $message .= __('Tax:', 'construction-service-calculator') . ' ' . $calculation['total_tax_formatted'] . "\n";
        $message .= __('Grand Total:', 'construction-service-calculator') . ' ' . $calculation['grand_total_formatted'] . "\n\n";
        
        $message .= __('View this submission in your admin panel:', 'construction-service-calculator') . "\n";
        $message .= admin_url('admin.php?page=csc-submissions&view=' . $submission_id) . "\n";
        
        // Send the email
        $headers = array();
        
        // Add customer as reply-to if email is provided
        if (!empty($customer_email)) {
            $headers[] = 'Reply-To: ' . $customer_name . ' <' . $customer_email . '>';
        }
        
        wp_mail($admin_email, $subject, $message, $headers);
        
        // Send confirmation to customer if email is provided
        if (!empty($customer_email)) {
            $this->send_customer_confirmation($customer_email, $customer_name, $submission_id, $calculation);
        }
    }
    
    /**
     * Send confirmation email to customer.
     *
     * @since    1.0.0
     * @param    string   $email            Customer email.
     * @param    string   $name             Customer name.
     * @param    int      $submission_id    Submission post ID.
     * @param    array    $calculation      Calculation results.
     */
    private function send_customer_confirmation($email, $name, $submission_id, $calculation) {
        $site_name = get_bloginfo('name');
        
        $subject = sprintf(
            __('[%s] Your Construction Estimate Request', 'construction-service-calculator'),
            $site_name
        );
        
        // Build email content
        $message = sprintf(
            __('Dear %s,', 'construction-service-calculator'),
            $name
        ) . "\n\n";
        
        $message .= __('Thank you for your construction estimate request. We have received your inquiry and will respond to you shortly.', 'construction-service-calculator') . "\n\n";
        
        $message .= __('Estimate Summary:', 'construction-service-calculator') . "\n";
        foreach ($calculation['services'] as $service) {
            $message .= sprintf(
                "%s (%s %s): %s\n",
                $service['service_name'],
                $service['quantity'],
                $service['unit_symbol'],
                $service['subtotal_formatted']
            );
        }
        
        $message .= "\n" . __('Subtotal:', 'construction-service-calculator') . ' ' . $calculation['total_subtotal_formatted'] . "\n";
        $message .= __('Tax:', 'construction-service-calculator') . ' ' . $calculation['total_tax_formatted'] . "\n";
        $message .= __('Grand Total:', 'construction-service-calculator') . ' ' . $calculation['grand_total_formatted'] . "\n\n";
        
        $message .= __('Please note that this is an automated estimate based on the information you provided. Final prices may vary depending on specific project requirements.', 'construction-service-calculator') . "\n\n";
        
        $message .= __('We will contact you soon to discuss your project in more detail.', 'construction-service-calculator') . "\n\n";
        
        $message .= __('Best regards,', 'construction-service-calculator') . "\n";
        $message .= $site_name . "\n";
        
        // Send the email
        $headers = array();
        wp_mail($email, $subject, $message, $headers);
    }
}
