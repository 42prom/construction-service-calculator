<?php
/**
 * Template for the calculator form.
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
?>

<div class="construction-service-calculator csc-theme-<?php echo esc_attr($atts['theme']); ?> <?php echo esc_attr($columns_class); ?>">
    <div class="csc-container">
        <!-- Header -->
        <div class="csc-header">
            <h2 class="csc-title"><?php echo esc_html($atts['title']); ?></h2>
            <p class="csc-description"><?php echo esc_html($atts['description']); ?></p>
        </div>
        
        <!-- Messages container -->
        <div class="csc-messages"></div>

        <!-- Mobile progress indicator -->
        <div class="csc-mobile-progress">
            <span class="csc-mobile-step-label">Step <span class="csc-mobile-current-step">1</span> of 3</span>
            <div class="csc-mobile-progress-indicator">
                <div class="csc-mobile-progress-dot active" data-step="1"></div>
                <div class="csc-mobile-progress-dot" data-step="2"></div>
                <div class="csc-mobile-progress-dot" data-step="3"></div>
            </div>
        </div>
        
        <!-- Progress bar -->
        <div class="csc-progress">
            <div class="csc-progress-steps">
                <div class="csc-progress-step active" data-step="1">
                    <div class="csc-step-number">1</div>
                    <div class="csc-step-label"><?php _e('Select Services', 'construction-service-calculator'); ?></div>
                </div>
                <div class="csc-progress-step" data-step="2">
                    <div class="csc-step-number">2</div>
                    <div class="csc-step-label"><?php _e('Review', 'construction-service-calculator'); ?></div>
                </div>
                <div class="csc-progress-step" data-step="3">
                    <div class="csc-step-number">3</div>
                    <div class="csc-step-label"><?php _e('Submit', 'construction-service-calculator'); ?></div>
                </div>
            </div>
            <div class="csc-progress-bar">
                <div class="csc-progress-bar-inner"></div>
            </div>
        </div>
        
        <!-- Calculator form -->
        <form class="csc-calculator-form">
            <!-- Step 1: Service Selection -->
            <div class="csc-step active" data-step="1">
                <h3 class="csc-step-title"><?php _e('Select Services', 'construction-service-calculator'); ?> (<span class="csc-selected-count">0</span> <?php _e('selected', 'construction-service-calculator'); ?>)</h3>
                
                <?php if (count($services_by_category) > 1) : ?>
                    <!-- Category tabs -->
                    <div class="csc-category-tabs">
                        <?php foreach ($services_by_category as $category_key => $services) : ?>
                            <?php if (isset($categories[$category_key])) : ?>
                                <div class="csc-category-tab" data-category="<?php echo esc_attr($category_key); ?>">
                                    <?php echo esc_html($categories[$category_key]); ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Services by category -->
                <?php foreach ($services_by_category as $category_key => $services) : ?>
                    <div class="csc-category-content" data-category="<?php echo esc_attr($category_key); ?>">
                        <div class="csc-service-grid">
                            <?php 
                            $service_count = count($services);
                            $visible_count = min(6, $service_count);
                            $hidden_count = $service_count - $visible_count;
                            
                            for ($i = 0; $i < $service_count; $i++) : 
                                $service = $services[$i];
                                $hidden_class = ($i >= $visible_count) ? 'csc-hidden csc-toggle-visibility' : '';
                                
                                // Include the service item template
                                include CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'public/partials/service-item.php';
                            endfor;
                            ?>
                        </div>
                        
                        <?php if ($hidden_count > 0) : ?>
                            <div class="csc-show-more-container">
                                <button type="button" class="csc-show-more">
                                    <?php _e('Show More', 'construction-service-calculator'); ?> (<?php echo esc_html($hidden_count); ?>)
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                
                <!-- Summary box -->
                <div class="csc-summary-box">
                    <h4 class="csc-summary-title"><?php _e('Estimate Summary', 'construction-service-calculator'); ?></h4>
                    <div class="csc-summary-details">
                        <div class="csc-summary-row">
                            <div class="csc-summary-label"><?php _e('Subtotal', 'construction-service-calculator'); ?></div>
                            <div class="csc-summary-value csc-summary-subtotal">0</div>
                        </div>
                        <?php if (get_option('csc_tax_display', 'yes') === 'yes') : ?>
                            <div class="csc-summary-row">
                                <div class="csc-summary-label">
                                    <?php 
                                    printf(
                                        __('Tax (%s%%)', 'construction-service-calculator'),
                                        esc_html(get_option('csc_tax_rate', 20))
                                    ); 
                                    ?>
                                </div>
                                <div class="csc-summary-value csc-summary-tax">0</div>
                            </div>
                        <?php endif; ?>
                        <div class="csc-summary-row csc-summary-total-row">
                            <div class="csc-summary-label"><?php _e('Total', 'construction-service-calculator'); ?></div>
                            <div class="csc-summary-value csc-summary-total">0</div>
                        </div>
                    </div>
                </div>
                
                <!-- Step buttons -->
                <div class="csc-step-buttons">
                    <button type="button" class="csc-button csc-button-secondary csc-prev-step" style="visibility: hidden"><?php _e('Previous', 'construction-service-calculator'); ?></button>
                    <button type="button" class="csc-button csc-button-primary csc-next-step" disabled><?php _e('Next Step', 'construction-service-calculator'); ?></button>
                </div>
            </div>
            
            <!-- Step 2: Review -->
            <div class="csc-step" data-step="2">
                <h3 class="csc-step-title"><?php _e('Review Your Estimate', 'construction-service-calculator'); ?></h3>
                
                <table class="csc-review-table">
                    <thead>
                        <tr>
                            <th><?php _e('Service', 'construction-service-calculator'); ?></th>
                            <th><?php _e('Rate', 'construction-service-calculator'); ?></th>
                            <th><?php _e('Quantity', 'construction-service-calculator'); ?></th>
                            <th><?php _e('Subtotal', 'construction-service-calculator'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Rows will be populated dynamically -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3"><?php _e('Subtotal', 'construction-service-calculator'); ?></td>
                            <td class="csc-summary-subtotal">0</td>
                        </tr>
                        <?php if (get_option('csc_tax_display', 'yes') === 'yes') : ?>
                            <tr>
                                <td colspan="3">
                                    <?php 
                                    printf(
                                        __('Tax (%s%%)', 'construction-service-calculator'),
                                        esc_html(get_option('csc_tax_rate', 20))
                                    ); 
                                    ?>
                                </td>
                                <td class="csc-summary-tax">0</td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td colspan="3"><?php _e('Total', 'construction-service-calculator'); ?></td>
                            <td class="csc-summary-total">0</td>
                        </tr>
                    </tfoot>
                </table>
                
                <!-- Step buttons -->
                <div class="csc-step-buttons csc-mobile-button-container">
                    <button type="button" class="csc-button csc-button-secondary csc-prev-step"><span class="csc-button-icon">←</span> <?php _e('Previous', 'construction-service-calculator'); ?></button>
                    <button type="button" class="csc-button csc-button-primary csc-next-step"><?php _e('Next Step', 'construction-service-calculator'); ?> <span class="csc-button-icon">→</span></button>
                </div>
            </div>
            
            <!-- Step 3: Submit -->
            <div class="csc-step" data-step="3">
                <h3 class="csc-step-title"><?php _e('Submit Your Inquiry', 'construction-service-calculator'); ?></h3>
                
                <?php if ($atts['show_contact_form'] === 'yes') : ?>
                    <div class="csc-contact-form">
                        <div class="csc-form-row">
                            <div class="csc-form-group">
                                <label for="csc_customer_name" class="csc-form-label">
                                    <?php _e('Name', 'construction-service-calculator'); ?>
                                    <span class="csc-required-label">*</span>
                                </label>
                                <input type="text" id="csc_customer_name" class="csc-form-control csc-required" required>
                            </div>
                        </div>
                        
                        <div class="csc-form-row">
                            <div class="csc-form-group">
                                <label for="csc_customer_email" class="csc-form-label">
                                    <?php _e('Email', 'construction-service-calculator'); ?>
                                    <span class="csc-required-label">*</span>
                                </label>
                                <input type="email" id="csc_customer_email" class="csc-form-control csc-required" required>
                            </div>
                        </div>
                        
                        <div class="csc-form-row">
                            <div class="csc-form-group">
                                <label for="csc_customer_phone" class="csc-form-label">
                                    <?php _e('Phone Number', 'construction-service-calculator'); ?>
                                </label>
                                <input type="tel" id="csc_customer_phone" class="csc-form-control">
                            </div>
                        </div>
                        
                        <div class="csc-form-row">
                            <div class="csc-form-group">
                                <label for="csc_customer_message" class="csc-form-label">
                                    <?php _e('Message', 'construction-service-calculator'); ?>
                                </label>
                                <textarea id="csc_customer_message" class="csc-form-control" rows="4"></textarea>
                                <div class="csc-form-hint">
                                    <?php _e('Please provide any additional details that might help us understand your project better.', 'construction-service-calculator'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Summary box -->
                <div class="csc-summary-box">
                    <h4 class="csc-summary-title"><?php _e('Estimate Summary', 'construction-service-calculator'); ?></h4>
                    <div class="csc-summary-details">
                        <div class="csc-summary-row">
                            <div class="csc-summary-label"><?php _e('Subtotal', 'construction-service-calculator'); ?></div>
                            <div class="csc-summary-value csc-summary-subtotal">0</div>
                        </div>
                        <?php if (get_option('csc_tax_display', 'yes') === 'yes') : ?>
                            <div class="csc-summary-row">
                                <div class="csc-summary-label">
                                    <?php 
                                    printf(
                                        __('Tax (%s%%)', 'construction-service-calculator'),
                                        esc_html(get_option('csc_tax_rate', 20))
                                    ); 
                                    ?>
                                </div>
                                <div class="csc-summary-value csc-summary-tax">0</div>
                            </div>
                        <?php endif; ?>
                        <div class="csc-summary-row csc-summary-total-row">
                            <div class="csc-summary-label"><?php _e('Total', 'construction-service-calculator'); ?></div>
                            <div class="csc-summary-value csc-summary-total">0</div>
                        </div>
                    </div>
                </div>
                
                <!-- Step buttons -->
                <div class="csc-step-buttons csc-mobile-button-container">
                    <button type="button" class="csc-button csc-button-secondary csc-prev-step"><span class="csc-button-icon">←</span> <?php _e('Previous', 'construction-service-calculator'); ?></button>
                    <button type="submit" class="csc-button csc-button-primary csc-submit-button" id="csc-submit-inquiry"><?php echo esc_html($atts['submit_button']); ?> <span class="csc-button-icon">✓</span></button>
                </div>
            </div>
        </form>
        
        <!-- Success container -->
        <div class="csc-success-container"></div>
    </div>
</div>