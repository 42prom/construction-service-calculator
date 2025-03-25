<?php
/**
 * Template for the tools page.
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

// Initialize data handler
$data_handler = new Construction_Service_Calculator_Data_Handler();

// Handle imports
$import_result = null;
if (isset($_POST['csc_action']) && $_POST['csc_action'] === 'import_services') {
    // Verify nonce
    if (isset($_POST['csc_import_nonce']) && wp_verify_nonce($_POST['csc_import_nonce'], 'csc_import_services')) {
        // Check file upload
        if (isset($_FILES['csv_file']) && !empty($_FILES['csv_file']['tmp_name'])) {
            $file = $_FILES['csv_file'];
            
            // Check file type
            $file_type = wp_check_filetype($file['name']);
            if ($file_type['ext'] === 'csv') {
                // Process the import
                $import_result = $data_handler->import_services_from_csv($file['tmp_name']);
            } else {
                $import_result = array(
                    'success' => false,
                    'message' => __('Invalid file type. Please upload a CSV file.', 'construction-service-calculator')
                );
            }
        } else {
            $import_result = array(
                'success' => false,
                'message' => __('No file uploaded.', 'construction-service-calculator')
            );
        }
    }
}

// Handle exports
$export_result = null;
if (isset($_POST['csc_action']) && $_POST['csc_action'] === 'export_services') {
    // Verify nonce
    if (isset($_POST['csc_export_nonce']) && wp_verify_nonce($_POST['csc_export_nonce'], 'csc_export_services')) {
        // Get selected category
        $category = isset($_POST['export_category']) ? sanitize_text_field($_POST['export_category']) : '';
        
        // Process the export
        $export_result = $data_handler->export_services_to_csv($category);
    }
}

// Get analytics data
$period = isset($_GET['period']) ? sanitize_text_field($_GET['period']) : 'month';
$analytics = $data_handler->get_analytics_data($period);

// Get categories for filter
$categories = get_option('csc_service_categories', array());
?>

<div class="wrap csc-tools-container">
    <h1 class="csc-tools-title"><?php _e('Tools & Analytics', 'construction-service-calculator'); ?></h1>
    
    <div class="csc-tools-box">
        <div class="csc-tools-box-header">
            <h2><?php _e('Import Services', 'construction-service-calculator'); ?></h2>
            <p class="csc-tools-box-description">
                <?php _e('Import services from a CSV file. The CSV should include columns for name, rate, unit, and category.', 'construction-service-calculator'); ?>
            </p>
        </div>
        
        <div class="csc-import-form">
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('csc_import_services', 'csc_import_nonce'); ?>
                <input type="hidden" name="csc_action" value="import_services">
                
                <div class="csc-form-row">
                    <label for="csv_file" class="csc-form-label"><?php _e('Select CSV File', 'construction-service-calculator'); ?></label>
                    <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
                </div>
                
                <p>
                    <?php _e('Required columns:', 'construction-service-calculator'); ?>
                    <code>name</code>, <code>rate</code>, <code>unit</code>, <code>category</code>
                </p>
                
                <p>
                    <?php _e('Optional columns:', 'construction-service-calculator'); ?>
                    <code>description</code>, <code>min_order</code>, <code>max_order</code>, <code>step</code>, <code>icon_url</code>
                </p>
                
                <p>
                    <button type="submit" class="button button-primary">
                        <?php _e('Import Services', 'construction-service-calculator'); ?>
                    </button>
                </p>
            </form>
        </div>
        
        <?php if ($import_result !== null) : ?>
            <div class="csc-import-result <?php echo $import_result['success'] ? 'csc-import-success' : 'csc-import-error'; ?>">
                <?php if ($import_result['success']) : ?>
                    <p>
                        <?php printf(
                            __('Successfully imported %d services. Skipped %d rows.', 'construction-service-calculator'),
                            $import_result['imported'],
                            $import_result['skipped']
                        ); ?>
                    </p>
                    
                    <?php if (!empty($import_result['errors'])) : ?>
                        <div class="csc-import-errors">
                            <h4><?php _e('Errors:', 'construction-service-calculator'); ?></h4>
                            <ul>
                                <?php foreach ($import_result['errors'] as $error) : ?>
                                    <li><?php echo esc_html($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                <?php else : ?>
                    <p><?php echo esc_html($import_result['message']); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="csc-tools-box">
        <div class="csc-tools-box-header">
            <h2><?php _e('Export Services', 'construction-service-calculator'); ?></h2>
            <p class="csc-tools-box-description">
                <?php _e('Export services to a CSV file for backup or editing in a spreadsheet application.', 'construction-service-calculator'); ?>
            </p>
        </div>
        
        <div class="csc-export-form">
            <form method="post">
                <?php wp_nonce_field('csc_export_services', 'csc_export_nonce'); ?>
                <input type="hidden" name="csc_action" value="export_services">
                
                <div class="csc-form-row">
                    <label for="export_category" class="csc-form-label"><?php _e('Category Filter', 'construction-service-calculator'); ?></label>
                    <select name="export_category" id="export_category">
                        <option value=""><?php _e('All Categories', 'construction-service-calculator'); ?></option>
                        <?php foreach ($categories as $key => $name) : ?>
                            <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <p>
                    <button type="submit" class="button button-primary">
                        <?php _e('Export Services', 'construction-service-calculator'); ?>
                    </button>
                </p>
            </form>
        </div>
        
        <?php if ($export_result !== null) : ?>
            <div class="csc-import-result <?php echo $export_result['success'] ? 'csc-import-success' : 'csc-import-error'; ?>">
                <?php if ($export_result['success']) : ?>
                    <p>
                        <?php _e('Export completed successfully.', 'construction-service-calculator'); ?>
                    </p>
                    <p>
                        <a href="<?php echo esc_url($export_result['file_url']); ?>" class="button" download>
                            <?php _e('Download CSV File', 'construction-service-calculator'); ?>
                        </a>
                    </p>
                <?php else : ?>
                    <p><?php echo esc_html($export_result['message']); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="csc-tools-box">
        <div class="csc-analytics-header">
            <h2 class="csc-analytics-title"><?php _e('Analytics', 'construction-service-calculator'); ?></h2>
            
            <div class="csc-analytics-period">
                <form method="get" action="">
                    <input type="hidden" name="page" value="csc-tools">
                    <label for="period"><?php _e('Time Period:', 'construction-service-calculator'); ?></label>
                    <select name="period" id="period" class="csc-analytics-period-select">
                        <option value="week" <?php selected($period, 'week'); ?>><?php _e('Last Week', 'construction-service-calculator'); ?></option>
                        <option value="month" <?php selected($period, 'month'); ?>><?php _e('Last Month', 'construction-service-calculator'); ?></option>
                        <option value="year" <?php selected($period, 'year'); ?>><?php _e('Last Year', 'construction-service-calculator'); ?></option>
                    </select>
                </form>
            </div>
        </div>
        
        <div class="csc-analytics-cards">
            <div class="csc-analytics-card">
                <div class="csc-analytics-card-value"><?php echo esc_html($analytics['total_submissions']); ?></div>
                <div class="csc-analytics-card-label"><?php _e('Total Submissions', 'construction-service-calculator'); ?></div>
            </div>
            
            <div class="csc-analytics-card">
                <div class="csc-analytics-card-value"><?php echo esc_html($analytics['total_revenue_formatted']); ?></div>
                <div class="csc-analytics-card-label"><?php _e('Estimated Revenue', 'construction-service-calculator'); ?></div>
            </div>
            
            <div class="csc-analytics-card">
                <div class="csc-analytics-card-value">
                    <?php 
                    echo count($analytics['popular_services']) > 0 ? 
                        esc_html(reset($analytics['popular_services'])['name']) : 
                        __('N/A', 'construction-service-calculator'); 
                    ?>
                </div>
                <div class="csc-analytics-card-label"><?php _e('Most Popular Service', 'construction-service-calculator'); ?></div>
            </div>
            
            <div class="csc-analytics-card">
                <div class="csc-analytics-card-value">
                    <?php 
                    $avg_value = $analytics['total_submissions'] > 0 ? 
                        $analytics['total_revenue'] / $analytics['total_submissions'] : 0;
                    
                    echo esc_html(get_option('csc_currency_symbol', '$') . number_format($avg_value, 2));
                    ?>
                </div>
                <div class="csc-analytics-card-label"><?php _e('Average Request Value', 'construction-service-calculator'); ?></div>
            </div>
        </div>
        
        <div class="csc-analytics-charts">
            <div class="csc-analytics-box">
                <h3><?php _e('Popular Services', 'construction-service-calculator'); ?></h3>
                
                <?php if (empty($analytics['popular_services'])) : ?>
                    <p><?php _e('No data available.', 'construction-service-calculator'); ?></p>
                <?php else : ?>
                    <div class="csc-popular-services">
                        <?php 
                        $max_count = reset($analytics['popular_services'])['count'];
                        foreach ($analytics['popular_services'] as $service_id => $service_data) : 
                            $percentage = ($service_data['count'] / $max_count) * 100;
                        ?>
                            <div class="csc-popular-service">
                                <div class="csc-popular-service-name"><?php echo esc_html($service_data['name']); ?></div>
                                <div class="csc-popular-service-count">
                                    <?php 
                                    printf(
                                        _n('%s request', '%s requests', $service_data['count'], 'construction-service-calculator'),
                                        number_format_i18n($service_data['count'])
                                    );
                                    ?>
                                    -
                                    <?php echo esc_html(get_option('csc_currency_symbol', '$') . number_format($service_data['revenue'], 2)); ?>
                                </div>
                                <div class="csc-popular-service-bar">
                                    <div class="csc-popular-service-bar-inner" style="width: <?php echo esc_attr($percentage); ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="csc-analytics-box">
                <h3><?php _e('Submissions by Date', 'construction-service-calculator'); ?></h3>
                
                <?php if (empty($analytics['submissions_by_date'])) : ?>
                    <p><?php _e('No data available.', 'construction-service-calculator'); ?></p>
                <?php else : ?>
                    <div class="csc-submissions-chart" style="height: 300px;">
                        <!-- Placeholder for future chart implementation -->
                        <p><?php _e('Chart data is available. For a visual representation, consider implementing a JavaScript chart library like Chart.js or Google Charts.', 'construction-service-calculator'); ?></p>
                        
                        <table class="widefat">
                            <thead>
                                <tr>
                                    <th><?php _e('Date', 'construction-service-calculator'); ?></th>
                                    <th><?php _e('Submissions', 'construction-service-calculator'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($analytics['submissions_by_date'] as $submission_data) : ?>
                                    <tr>
                                        <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($submission_data['date']))); ?></td>
                                        <td><?php echo esc_html($submission_data['count']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>