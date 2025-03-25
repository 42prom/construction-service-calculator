<?php
/**
 * Template for the settings page.
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

$service_units = get_option('csc_service_units', array());
?>

<div class="wrap csc-settings-container">
    <h1><?php _e('Construction Service Calculator Settings', 'construction-service-calculator'); ?></h1>
    
    <?php settings_errors(); ?>
    
    <div class="csc-settings-tabs">
        <button class="csc-settings-tab active" data-tab="general"><?php _e('General', 'construction-service-calculator'); ?></button>
        <button class="csc-settings-tab" data-tab="form"><?php _e('Form', 'construction-service-calculator'); ?></button>
        <button class="csc-settings-tab" data-tab="email"><?php _e('Email', 'construction-service-calculator'); ?></button>
        <button class="csc-settings-tab" data-tab="units"><?php _e('Units', 'construction-service-calculator'); ?></button>
    </div>
    
    <div class="csc-settings-sections">
        <!-- General Settings Section -->
        <div class="csc-settings-section active" id="general-section">
            <form method="post" action="options.php">
                <?php
                settings_fields('csc_general_settings');
                do_settings_sections('csc_general_settings');
                submit_button();
                ?>
            </form>
        </div>
        
        <!-- Form Settings Section -->
        <div class="csc-settings-section" id="form-section">
            <form method="post" action="options.php">
                <?php
                settings_fields('csc_form_settings');
                do_settings_sections('csc_form_settings');
                submit_button();
                ?>
            </form>
        </div>
        
        <!-- Email Settings Section -->
        <div class="csc-settings-section" id="email-section">
            <form method="post" action="options.php">
                <?php
                settings_fields('csc_email_settings');
                do_settings_sections('csc_email_settings');
                submit_button();
                ?>
            </form>
        </div>
        
        <!-- Units Settings Section -->
        <div class="csc-settings-section" id="units-section">
            <div class="csc-units-container">
                <div class="csc-units-header">
                    <h2 class="csc-units-title"><?php _e('Service Units', 'construction-service-calculator'); ?></h2>
                    <button type="button" class="button csc-add-unit-button">
                        <?php _e('Add New Unit', 'construction-service-calculator'); ?>
                    </button>
                </div>
                
                <p class="description">
                    <?php _e('Define the units of measurement that can be used for services. These units determine how quantities are calculated and displayed.', 'construction-service-calculator'); ?>
                </p>
                
                <form method="post" action="" id="csc-units-form">
                    <?php wp_nonce_field('csc_units_form', 'csc_units_nonce'); ?>
                    <input type="hidden" name="csc_action" value="save_units">
                    
                    <!-- Add new unit section -->
                    <div class="csc-add-unit-form" style="display: none;">
                        <h3><?php _e('Add New Unit', 'construction-service-calculator'); ?></h3>
                        
                        <div class="csc-form-row">
                            <label for="csc_new_unit_key" class="csc-form-label">
                                <?php _e('Key', 'construction-service-calculator'); ?>
                                <span class="description"><?php _e('(unique identifier, lowercase with underscores)', 'construction-service-calculator'); ?></span>
                            </label>
                            <input type="text" id="csc_new_unit_key" name="csc_new_unit_key" class="regular-text">
                        </div>
                        
                        <div class="csc-form-row">
                            <label for="csc_new_unit_name" class="csc-form-label">
                                <?php _e('Name', 'construction-service-calculator'); ?>
                            </label>
                            <input type="text" id="csc_new_unit_name" name="csc_new_unit_name" class="regular-text">
                        </div>
                        
                        <div class="csc-form-row">
                            <label for="csc_new_unit_symbol" class="csc-form-label">
                                <?php _e('Symbol', 'construction-service-calculator'); ?>
                                <span class="description"><?php _e('(e.g., m², ft², hr)', 'construction-service-calculator'); ?></span>
                            </label>
                            <input type="text" id="csc_new_unit_symbol" name="csc_new_unit_symbol" class="regular-text">
                        </div>
                        
                        <div class="csc-form-row">
                            <label for="csc_new_unit_type" class="csc-form-label">
                                <?php _e('Type', 'construction-service-calculator'); ?>
                            </label>
                            <select id="csc_new_unit_type" name="csc_new_unit_type">
                                <option value="area"><?php _e('Area', 'construction-service-calculator'); ?></option>
                                <option value="length"><?php _e('Length', 'construction-service-calculator'); ?></option>
                                <option value="time"><?php _e('Time', 'construction-service-calculator'); ?></option>
                                <option value="quantity"><?php _e('Quantity', 'construction-service-calculator'); ?></option>
                                <option value="volume"><?php _e('Volume', 'construction-service-calculator'); ?></option>
                                <option value="weight"><?php _e('Weight', 'construction-service-calculator'); ?></option>
                                <option value="other"><?php _e('Other', 'construction-service-calculator'); ?></option>
                            </select>
                        </div>
                        
                        <div class="csc-form-row">
                            <button type="button" class="button button-primary csc-add-unit-save">
                                <?php _e('Add Unit', 'construction-service-calculator'); ?>
                            </button>
                            <button type="button" class="button csc-cancel-add-unit">
                                <?php _e('Cancel', 'construction-service-calculator'); ?>
                            </button>
                        </div>
                    </div>
                    
                    <table class="csc-units-table widefat">
                        <thead>
                            <tr>
                                <th><?php _e('Key', 'construction-service-calculator'); ?></th>
                                <th><?php _e('Name', 'construction-service-calculator'); ?></th>
                                <th><?php _e('Symbol', 'construction-service-calculator'); ?></th>
                                <th><?php _e('Type', 'construction-service-calculator'); ?></th>
                                <th><?php _e('Actions', 'construction-service-calculator'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($service_units)) : ?>
                                <tr class="csc-no-units-row">
                                    <td colspan="5"><?php _e('No units defined yet.', 'construction-service-calculator'); ?></td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($service_units as $key => $unit) : ?>
                                    <tr>
                                        <td>
                                            <input type="hidden" name="csc_unit_key[]" value="<?php echo esc_attr($key); ?>">
                                            <?php echo esc_html($key); ?>
                                        </td>
                                        <td>
                                            <input type="text" name="csc_unit_name[]" value="<?php echo esc_attr($unit['name']); ?>" class="regular-text" required>
                                        </td>
                                        <td>
                                            <input type="text" name="csc_unit_symbol[]" value="<?php echo esc_attr($unit['symbol']); ?>" class="regular-text" required>
                                        </td>
                                        <td>
                                            <select name="csc_unit_type[]">
                                                <option value="area" <?php selected(isset($unit['type']) ? $unit['type'] : '', 'area'); ?>><?php _e('Area', 'construction-service-calculator'); ?></option>
                                                <option value="length" <?php selected(isset($unit['type']) ? $unit['type'] : '', 'length'); ?>><?php _e('Length', 'construction-service-calculator'); ?></option>
                                                <option value="time" <?php selected(isset($unit['type']) ? $unit['type'] : '', 'time'); ?>><?php _e('Time', 'construction-service-calculator'); ?></option>
                                                <option value="quantity" <?php selected(isset($unit['type']) ? $unit['type'] : '', 'quantity'); ?>><?php _e('Quantity', 'construction-service-calculator'); ?></option>
                                                <option value="volume" <?php selected(isset($unit['type']) ? $unit['type'] : '', 'volume'); ?>><?php _e('Volume', 'construction-service-calculator'); ?></option>
                                                <option value="weight" <?php selected(isset($unit['type']) ? $unit['type'] : '', 'weight'); ?>><?php _e('Weight', 'construction-service-calculator'); ?></option>
                                                <option value="other" <?php selected(isset($unit['type']) ? $unit['type'] : '', 'other'); ?>><?php _e('Other', 'construction-service-calculator'); ?></option>
                                            </select>
                                        </td>
                                        <td>
                                            <button type="button" class="button button-small csc-remove-unit-button">
                                                <?php _e('Remove', 'construction-service-calculator'); ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    
                    <?php submit_button(__('Save Units', 'construction-service-calculator')); ?>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Tab functionality
    $('.csc-settings-tab').on('click', function() {
        var tabId = $(this).data('tab');
        $('.csc-settings-tab').removeClass('active');
        $('.csc-settings-section').removeClass('active');
        $(this).addClass('active');
        $('#' + tabId + '-section').addClass('active');
    });

    // Add Unit button
    $('.csc-add-unit-button').on('click', function() {
        $('.csc-add-unit-form').slideDown();
        $('#csc_new_unit_key').focus();
    });

    // Cancel Add Unit
    $('.csc-cancel-add-unit').on('click', function() {
        $('.csc-add-unit-form').slideUp();
    });

    // Add Unit Save
    $('.csc-add-unit-save').on('click', function() {
        var key = $('#csc_new_unit_key').val().trim();
        var name = $('#csc_new_unit_name').val().trim();
        var symbol = $('#csc_new_unit_symbol').val().trim();
        var type = $('#csc_new_unit_type').val();

        if (key && name && symbol) {
            var $tbody = $('.csc-units-table tbody');
            $('.csc-no-units-row').remove();

            var $newRow = $('<tr>' +
                '<td><input type="hidden" name="csc_unit_key[]" value="' + key + '">' + key + '</td>' +
                '<td><input type="text" name="csc_unit_name[]" value="' + name + '" class="regular-text" required></td>' +
                '<td><input type="text" name="csc_unit_symbol[]" value="' + symbol + '" class="regular-text" required></td>' +
                '<td><select name="csc_unit_type[]">' +
                    '<option value="area"' + (type === 'area' ? ' selected' : '') + '>Area</option>' +
                    '<option value="length"' + (type === 'length' ? ' selected' : '') + '>Length</option>' +
                    '<option value="time"' + (type === 'time' ? ' selected' : '') + '>Time</option>' +
                    '<option value="quantity"' + (type === 'quantity' ? ' selected' : '') + '>Quantity</option>' +
                    '<option value="volume"' + (type === 'volume' ? ' selected' : '') + '>Volume</option>' +
                    '<option value="weight"' + (type === 'weight' ? ' selected' : '') + '>Weight</option>' +
                    '<option value="other"' + (type === 'other' ? ' selected' : '') + '>Other</option>' +
                '</select></td>' +
                '<td><button type="button" class="button button-small csc-remove-unit-button">Remove</button></td>' +
                '</tr>');
            
            $tbody.append($newRow);
            $('.csc-add-unit-form').slideUp();
            $('#csc_new_unit_key, #csc_new_unit_name, #csc_new_unit_symbol').val('');
        } else {
            alert('Please fill in all required fields.');
        }
    });

    // Remove Unit button
    $(document).on('click', '.csc-remove-unit-button', function() {
        var $row = $(this).closest('tr');
        var unitName = $row.find('input[name="csc_unit_name[]"]').val();
        if (confirm('Are you sure you want to remove the unit "' + unitName + '"?')) {
            $row.remove();
            if ($('.csc-units-table tbody tr').length === 0) {
                $('.csc-units-table tbody').append('<tr class="csc-no-units-row"><td colspan="5"><?php _e('No units defined yet.', 'construction-service-calculator'); ?></td></tr>');
            }
        }
    });
});
</script>