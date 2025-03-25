<?php
/**
 * Template for the HTML estimate.
 *
 * @link       https://github.com/42prom
 * @since      1.0.0
 *
 * @package    Construction_Service_Calculator
 * @subpackage Construction_Service_Calculator/public/partials
 */

// Exit if accessed directly
if (!defined('WPINC')) {
    die;
}

// Get site info
$site_name = get_bloginfo('name');
$site_url = get_bloginfo('url');
$site_description = get_bloginfo('description');

// Get customer info
$customer_name = isset($customer_info['name']) ? sanitize_text_field($customer_info['name']) : __('Anonymous', 'construction-service-calculator');
$customer_email = isset($customer_info['email']) ? sanitize_email($customer_info['email']) : '';
$customer_phone = isset($customer_info['phone']) ? sanitize_text_field($customer_info['phone']) : '';
$customer_message = isset($customer_info['message']) ? sanitize_textarea_field($customer_info['message']) : '';

// Get date and time
$date = current_time(get_option('date_format'));
$time = current_time(get_option('time_format'));

// Get calculation data
$services = isset($calculation['services']) ? $calculation['services'] : array();
$total_subtotal = isset($calculation['total_subtotal_formatted']) ? $calculation['total_subtotal_formatted'] : '';
$total_tax = isset($calculation['total_tax_formatted']) ? $calculation['total_tax_formatted'] : '';
$grand_total = isset($calculation['grand_total_formatted']) ? $calculation['grand_total_formatted'] : '';
$tax_rate = isset($calculation['tax_rate']) ? $calculation['tax_rate'] : '';

// Get reference number
$reference = isset($submission_id) ? $submission_id : sprintf('%s%s', date('Ymd'), wp_rand(1000, 9999));

// Get estimate title
$estimate_title = sprintf(
    __('Construction Service Estimate #%s', 'construction-service-calculator'),
    $reference
);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($estimate_title); ?></title>
    <style>
        * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }
            
            body {
                font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
                line-height: 1.5;
                color: #333;
                margin: 0;
                padding: 0;
                background-color: #f9f9f9;
            }
            
            .estimate-container {
                width: 100%;
                max-width: 800px;
                margin: 20px auto;
                padding: 20px;
                background-color: #fff;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }
            
            .estimate-header {
                text-align: center;
                margin-bottom: 20px;
                padding-bottom: 15px;
                border-bottom: 2px solid #eee;
            }
            
            .estimate-title {
                font-size: 24px;
                margin-bottom: 10px;
                color: #333;
                word-wrap: break-word;
            }
            
            .estimate-subtitle {
                font-size: 14px;
                color: #666;
            }
            
            .estimate-meta {
                display: flex;
                flex-direction: column;
                margin-bottom: 20px;
            }
            
            @media screen and (min-width: 768px) {
                .estimate-meta {
                    flex-direction: row;
                    justify-content: space-between;
                }
                
                .estimate-company, .estimate-customer {
                    flex: 1;
                }
                
                .estimate-customer {
                    text-align: right;
                }
            }
            
            .estimate-company, .estimate-customer {
                margin-bottom: 15px;
            }
            
            .estimate-meta h3 {
                font-size: 16px;
                margin-bottom: 8px;
                color: #333;
            }
            
            .estimate-meta p {
                margin: 3px 0;
                font-size: 14px;
                color: #666;
            }
            
            .estimate-date {
                margin-bottom: 20px;
            }
            
            .estimate-date p {
                font-size: 14px;
                color: #666;
                margin: 5px 0;
            }
            
            .estimate-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
                font-size: 14px;
            }
            
            .estimate-table th,
            .estimate-table td {
                padding: 10px;
                text-align: left;
                border-bottom: 1px solid #eee;
                word-break: break-word;
            }
            
            .estimate-table th {
                background-color: #f5f5f5;
                font-weight: 600;
                color: #333;
            }
            
            .estimate-table tr:last-child td {
                border-bottom: none;
            }
            
            /* Mobile table styles */
            @media screen and (max-width: 600px) {
                .estimate-table, .estimate-table thead, .estimate-table tbody, 
                .estimate-table th, .estimate-table td, .estimate-table tr {
                    display: block;
                }
                
                .estimate-table thead tr {
                    position: absolute;
                    top: -9999px;
                    left: -9999px;
                }
                
                .estimate-table tr {
                    border: 1px solid #ddd;
                    margin-bottom: 10px;
                }
                
                .estimate-table td {
                    border: none;
                    border-bottom: 1px solid #eee;
                    position: relative;
                    padding-left: 50%;
                    min-height: 36px;
                }
                
                .estimate-table td:last-child {
                    border-bottom: 0;
                }
                
                .estimate-table td:before {
                    position: absolute;
                    top: 10px;
                    left: 10px;
                    width: 45%;
                    padding-right: 10px;
                    white-space: nowrap;
                    font-weight: 600;
                }
                
                /* Labels for mobile view */
                .estimate-table td:nth-of-type(1):before { content: "Service"; }
                .estimate-table td:nth-of-type(2):before { content: "Rate"; }
                .estimate-table td:nth-of-type(3):before { content: "Quantity"; }
                .estimate-table td:nth-of-type(4):before { content: "Subtotal"; }
            }
            
            .estimate-totals {
                margin-left: auto;
                margin-right: 0;
                width: 100%;
                max-width: 300px;
                margin-bottom: 20px;
            }
            
            @media screen and (max-width: 600px) {
                .estimate-totals {
                    max-width: 100%;
                }
            }
            
            .estimate-total-row {
                display: flex;
                justify-content: space-between;
                padding: 8px 0;
                font-size: 14px;
            }
            
            .estimate-total-row:not(:last-child) {
                border-bottom: 1px solid #eee;
            }
            
            .estimate-total-label {
                color: #666;
            }
            
            .estimate-total-value {
                font-weight: 600;
                color: #333;
            }
            
            .estimate-grand-total {
                font-size: 16px;
                font-weight: 700;
                color: #333;
                margin-top: 10px;
                padding-top: 10px;
                border-top: 2px solid #eee;
            }
            
            .estimate-notes, 
            .customer-message {
                margin-bottom: 20px;
                padding: 15px;
                background-color: #f9f9f9;
                border-radius: 5px;
            }
            
            .estimate-notes h3,
            .customer-message h3 {
                font-size: 16px;
                margin-bottom: 10px;
                color: #333;
            }
            
            .estimate-notes p,
            .customer-message p {
                font-size: 14px;
                color: #666;
                margin-bottom: 8px;
            }
            
            .estimate-footer {
                margin-top: 30px;
                padding-top: 15px;
                border-top: 1px solid #eee;
                text-align: center;
                font-size: 12px;
                color: #999;
            }
            
            @media print {
                body {
                    background-color: #fff;
                    -webkit-print-color-adjust: exact !important;
                    color-adjust: exact !important;
                }
                
                .estimate-container {
                    box-shadow: none;
                    margin: 0;
                    padding: 0;
                }
            }
    </style>
</head>
<body>
<div class="estimate-container">
        <div class="estimate-header">
            <h1 class="estimate-title"><?php echo esc_html($estimate_title); ?></h1>
            <div class="estimate-subtitle">
                <?php printf(
                    __('Created on %s at %s', 'construction-service-calculator'),
                    esc_html($date),
                    esc_html($time)
                ); ?>
            </div>
        </div>
        
        <div class="estimate-meta">
            <div class="estimate-company">
                <h3><?php _e('From', 'construction-service-calculator'); ?></h3>
                <p><?php echo esc_html($site_name); ?></p>
                <p><?php echo esc_html($site_url); ?></p>
                <?php if (!empty($site_description)) : ?>
                    <p><?php echo esc_html($site_description); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="estimate-customer">
                <h3><?php _e('To', 'construction-service-calculator'); ?></h3>
                <p><?php echo esc_html($customer_name); ?></p>
                <?php if (!empty($customer_email)) : ?>
                    <p><?php echo esc_html($customer_email); ?></p>
                <?php endif; ?>
                <?php if (!empty($customer_phone)) : ?>
                    <p><?php echo esc_html($customer_phone); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="estimate-date">
            <p><strong><?php _e('Reference:', 'construction-service-calculator'); ?></strong> <?php echo esc_html($reference); ?></p>
            <p><strong><?php _e('Date:', 'construction-service-calculator'); ?></strong> <?php echo esc_html($date); ?></p>
        </div>
        
        <table class="estimate-table">
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
        </table>
        
        <div class="estimate-totals">
            <div class="estimate-total-row">
                <div class="estimate-total-label"><?php _e('Subtotal', 'construction-service-calculator'); ?></div>
                <div class="estimate-total-value"><?php echo esc_html($total_subtotal); ?></div>
            </div>
            
            <?php if (get_option('csc_tax_display', 'yes') === 'yes' && !empty($total_tax)) : ?>
                <div class="estimate-total-row">
                    <div class="estimate-total-label">
                        <?php 
                        printf(
                            __('Tax (%s%%)', 'construction-service-calculator'),
                            esc_html($tax_rate)
                        ); 
                        ?>
                    </div>
                    <div class="estimate-total-value"><?php echo esc_html($total_tax); ?></div>
                </div>
            <?php endif; ?>
            
            <div class="estimate-total-row estimate-grand-total">
                <div class="estimate-total-label"><?php _e('Total', 'construction-service-calculator'); ?></div>
                <div class="estimate-total-value"><?php echo esc_html($grand_total); ?></div>
            </div>
        </div>
        
        <?php if (!empty($customer_message)) : ?>
            <div class="customer-message">
                <h3><?php _e('Customer Message', 'construction-service-calculator'); ?></h3>
                <p><?php echo wp_kses_post(nl2br($customer_message)); ?></p>
            </div>
        <?php endif; ?>
        
        <div class="estimate-notes">
            <h3><?php _e('Notes', 'construction-service-calculator'); ?></h3>
            <p><?php _e('This is an estimate based on the information provided. Actual prices may vary depending on the specific requirements of the project.', 'construction-service-calculator'); ?></p>
            <p><?php _e('This estimate is valid for 30 days from the date of issue.', 'construction-service-calculator'); ?></p>
        </div>
        
        <div class="estimate-footer">
            <p>
                <?php printf(
                    __('Generated by %s on %s', 'construction-service-calculator'),
                    esc_html($site_name),
                    esc_html($date . ' ' . $time)
                ); ?>
            </p>
        </div>
    </div>
</body>
</html>