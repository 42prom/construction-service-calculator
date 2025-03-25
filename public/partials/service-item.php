<?php
/**
 * Template for a single service item in the calculator.
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

// Setup service variables
$service_id = $service->ID;
$service_name = $service->post_title;
$service_description = isset($service->metadata['description']) ? $service->metadata['description'] : '';
$service_rate = isset($service->metadata['rate']) ? $service->metadata['rate'] : 0;
$service_unit = isset($service->metadata['unit']) ? $service->metadata['unit'] : '';
$service_unit_symbol = isset($service->metadata['unit_symbol']) ? $service->metadata['unit_symbol'] : '';
$service_min_order = isset($service->metadata['min_order']) && !empty($service->metadata['min_order']) ? $service->metadata['min_order'] : 0.1;
$service_max_order = isset($service->metadata['max_order']) && !empty($service->metadata['max_order']) ? $service->metadata['max_order'] : 1000;
$service_step = isset($service->metadata['step']) && !empty($service->metadata['step']) ? $service->metadata['step'] : 0.1;

// Format rate for display
$rate_formatted = '';
$currency_symbol = get_option('csc_currency_symbol', '$');
$currency_position = get_option('csc_currency_position', 'before');
$decimal_separator = get_option('csc_decimal_separator', '.');
$thousand_separator = get_option('csc_thousand_separator', ',');
$decimals = intval(get_option('csc_decimals', 2));

// Format rate
$formatted_rate = number_format($service_rate, $decimals, $decimal_separator, $thousand_separator);
if ($currency_position === 'before') {
    $rate_formatted = $currency_symbol . $formatted_rate;
} else {
    $rate_formatted = $formatted_rate . $currency_symbol;
}

// Get icon content
$icon_content = '';
if (!empty($service->metadata['icon_content'])) {
    $icon_content = $service->metadata['icon_content'];
} elseif (!empty($service->metadata['icon_url'])) {
    // Load icon if URL is provided but content is not
    require_once CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'includes/class-svg-handler.php';
    $icon_content = Construction_Service_Calculator_SVG_Handler::get_svg_content($service->metadata['icon_url']);
}

// Use default icon if no icon is set
if (empty($icon_content)) {
    $icon_content = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2L1 12h3v9h6v-6h4v6h6v-9h3L12 2z"/></svg>';
}
?>

<div class="csc-service-item <?php echo esc_attr($hidden_class); ?>" data-service-id="<?php echo esc_attr($service_id); ?>">
<div class="csc-service-header">
        <div class="csc-service-icon">
            <?php echo $icon_content; ?>
        </div>
        <div class="csc-service-info">
            <h4 class="csc-service-name"><?php echo esc_html($service_name); ?></h4>
            <?php if (!empty($service_description)) : ?>
                <div class="csc-service-description"><?php echo esc_html($service_description); ?></div>
            <?php endif; ?>
            <div class="csc-service-rate">
                <?php 
                printf(
                    __('%s per %s', 'construction-service-calculator'),
                    $rate_formatted,
                    esc_html($service_unit_symbol)
                ); 
                ?>
            </div>
        </div>
    </div>
    
    <div class="csc-service-details">
        <div class="csc-service-quantity-wrapper">
            <label class="csc-quantity-label"><?php _e('Qty:', 'construction-service-calculator'); ?></label>
            <input 
                type="text" 
                class="csc-service-quantity" 
                data-service-id="<?php echo esc_attr($service_id); ?>" 
                value="0" 
                data-min="<?php echo esc_attr($service_min_order); ?>" 
                <?php if (!empty($service_max_order)) : ?>data-max="<?php echo esc_attr($service_max_order); ?>"<?php endif; ?> 
                data-step="<?php echo esc_attr($service_step); ?>"
            >
            <span class="csc-unit-symbol"><?php echo esc_html($service_unit_symbol); ?></span>
        </div>
        <div class="csc-service-subtotal" data-service-id="<?php echo esc_attr($service_id); ?>"></div>
    </div>
</div>