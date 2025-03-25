<?php
/**
 * The settings functionality of the plugin.
 *
 * @link       https://github.com/42prom
 * @since      1.0.0
 *
 * @package    Construction_Service_Calculator
 * @subpackage Construction_Service_Calculator/admin
 */

/**
 * The settings functionality of the plugin.
 *
 * Handles the plugin settings and configuration.
 *
 * @package    Construction_Service_Calculator
 * @subpackage Construction_Service_Calculator/admin
 * @author     Mikheili Nakeuri
 */
class Construction_Service_Calculator_Settings {

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
    }

    /**
     * Register settings for the plugin.
     *
     * @since    1.0.0
     */
    public function register_settings() {
        // General Settings
        register_setting(
            'csc_general_settings',
            'csc_currency',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => 'USD'
            )
        );
        
        register_setting(
            'csc_general_settings',
            'csc_currency_symbol',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '$'
            )
        );
        
        register_setting(
            'csc_general_settings',
            'csc_currency_position',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => 'before'
            )
        );
        
        register_setting(
            'csc_general_settings',
            'csc_decimal_separator',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '.'
            )
        );
        
        register_setting(
            'csc_general_settings',
            'csc_thousand_separator',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ','
            )
        );
        
        register_setting(
            'csc_general_settings',
            'csc_decimals',
            array(
                'sanitize_callback' => 'intval',
                'default' => 2
            )
        );
        
        register_setting(
            'csc_general_settings',
            'csc_tax_rate',
            array(
                'sanitize_callback' => 'floatval',
                'default' => 20
            )
        );
        
        register_setting(
            'csc_general_settings',
            'csc_tax_display',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => 'yes'
            )
        );
        
        register_setting(
            'csc_general_settings',
            'csc_theme',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => 'default'
            )
        );
        
        // Form Settings
        register_setting(
            'csc_form_settings',
            'csc_form_title',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => __('Construction Service Calculator', 'construction-service-calculator')
            )
        );
        
        register_setting(
            'csc_form_settings',
            'csc_form_description',
            array(
                'sanitize_callback' => 'sanitize_textarea_field',
                'default' => __('Calculate the cost of your construction project instantly', 'construction-service-calculator')
            )
        );
        
        register_setting(
            'csc_form_settings',
            'csc_submit_button_text',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => __('Submit Inquiry', 'construction-service-calculator')
            )
        );
        
        // Email Settings
        register_setting(
            'csc_email_settings',
            'csc_email_notifications',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => 'yes'
            )
        );
        
        register_setting(
            'csc_email_settings',
            'csc_admin_email',
            array(
                'sanitize_callback' => 'sanitize_email',
                'default' => get_option('admin_email')
            )
        );
        
        // Service Units
        register_setting(
            'csc_units_settings',
            'csc_service_units',
            array(
                'sanitize_callback' => array($this, 'sanitize_service_units'),
                'default' => array()
            )
        );
        
        // Add settings sections
        add_settings_section(
            'csc_general_section',
            __('General Settings', 'construction-service-calculator'),
            array($this, 'render_general_section'),
            'csc_general_settings'
        );
        
        add_settings_section(
            'csc_form_section',
            __('Form Settings', 'construction-service-calculator'),
            array($this, 'render_form_section'),
            'csc_form_settings'
        );
        
        add_settings_section(
            'csc_email_section',
            __('Email Notifications', 'construction-service-calculator'),
            array($this, 'render_email_section'),
            'csc_email_settings'
        );
        
        add_settings_section(
            'csc_units_section',
            __('Service Units', 'construction-service-calculator'),
            array($this, 'render_units_section'),
            'csc_units_settings'
        );
        
        // Add settings fields
        
        // General Settings Fields
        add_settings_field(
            'csc_currency',
            __('Currency', 'construction-service-calculator'),
            array($this, 'render_currency_field'),
            'csc_general_settings',
            'csc_general_section'
        );
        
        add_settings_field(
            'csc_currency_symbol',
            __('Currency Symbol', 'construction-service-calculator'),
            array($this, 'render_currency_symbol_field'),
            'csc_general_settings',
            'csc_general_section'
        );
        
        add_settings_field(
            'csc_currency_position',
            __('Currency Position', 'construction-service-calculator'),
            array($this, 'render_currency_position_field'),
            'csc_general_settings',
            'csc_general_section'
        );
        
        add_settings_field(
            'csc_number_formatting',
            __('Number Formatting', 'construction-service-calculator'),
            array($this, 'render_number_formatting_field'),
            'csc_general_settings',
            'csc_general_section'
        );
        
        add_settings_field(
            'csc_tax_settings',
            __('Tax Settings', 'construction-service-calculator'),
            array($this, 'render_tax_settings_field'),
            'csc_general_settings',
            'csc_general_section'
        );
        
        add_settings_field(
            'csc_theme',
            __('Color Theme', 'construction-service-calculator'),
            array($this, 'render_theme_field'),
            'csc_general_settings',
            'csc_general_section'
        );
        
        // Form Settings Fields
        add_settings_field(
            'csc_form_title',
            __('Form Title', 'construction-service-calculator'),
            array($this, 'render_form_title_field'),
            'csc_form_settings',
            'csc_form_section'
        );
        
        add_settings_field(
            'csc_form_description',
            __('Form Description', 'construction-service-calculator'),
            array($this, 'render_form_description_field'),
            'csc_form_settings',
            'csc_form_section'
        );
        
        add_settings_field(
            'csc_submit_button_text',
            __('Submit Button Text', 'construction-service-calculator'),
            array($this, 'render_submit_button_text_field'),
            'csc_form_settings',
            'csc_form_section'
        );
        
        // Email Settings Fields
        add_settings_field(
            'csc_email_notifications',
            __('Email Notifications', 'construction-service-calculator'),
            array($this, 'render_email_notifications_field'),
            'csc_email_settings',
            'csc_email_section'
        );
        
        add_settings_field(
            'csc_admin_email',
            __('Admin Email', 'construction-service-calculator'),
            array($this, 'render_admin_email_field'),
            'csc_email_settings',
            'csc_email_section'
        );
    }

    /**
     * Sanitize service units data.
     *
     * @since    1.0.0
     * @param    array    $input    The service units data to sanitize.
     * @return   array              Sanitized service units data.
     */
    public function sanitize_service_units($input) {
        $sanitized = array();
        
        if (is_array($input)) {
            foreach ($input as $key => $unit) {
                $sanitized_unit = array(
                    'name' => isset($unit['name']) ? sanitize_text_field($unit['name']) : '',
                    'symbol' => isset($unit['symbol']) ? sanitize_text_field($unit['symbol']) : '',
                    'type' => isset($unit['type']) ? sanitize_text_field($unit['type']) : ''
                );
                
                $sanitized[sanitize_key($key)] = $sanitized_unit;
            }
        }
        
        return $sanitized;
    }

    /**
     * Display the settings page.
     *
     * @since    1.0.0
     */
    public function display_settings_page() {
        // Process service units form
        if (isset($_POST['csc_action']) && $_POST['csc_action'] === 'save_units') {
            $this->process_units_form();
        }
        
        // Include settings page template
        require_once CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'admin/partials/settings-page.php';
    }

    /**
     * Process service units form submission.
     *
     * @since    1.0.0
     */
    private function process_units_form() {
        // Debug: Log the POST data to help identify issues
        error_log('Processing units form. POST data: ' . print_r($_POST, true));
        
        // Check for nonce
        if (!isset($_POST['csc_units_nonce']) || !wp_verify_nonce($_POST['csc_units_nonce'], 'csc_units_form')) {
            add_settings_error(
                'csc_units_settings',
                'save_error',
                __('Security check failed.', 'construction-service-calculator'),
                'error'
            );
            return;
        }
        
        // Check user permissions
        if (!current_user_can('manage_options')) {
            add_settings_error(
                'csc_units_settings',
                'save_error',
                __('You do not have sufficient permissions to edit service units.', 'construction-service-calculator'),
                'error'
            );
            return;
        }
        
        // Get existing units to preserve them
        $service_units = get_option('csc_service_units', array());
        
        // Process unit updates (preserves existing units and updates edited ones)
        if (isset($_POST['csc_unit_key']) && isset($_POST['csc_unit_name']) && isset($_POST['csc_unit_symbol']) && isset($_POST['csc_unit_type'])) {
            $keys = $_POST['csc_unit_key'];
            $names = $_POST['csc_unit_name'];
            $symbols = $_POST['csc_unit_symbol'];
            $types = $_POST['csc_unit_type'];
            
            // Create a new array for the updated units
            $updated_units = array();
            
            for ($i = 0; $i < count($keys); $i++) {
                $key = sanitize_key($keys[$i]);
                $name = sanitize_text_field($names[$i]);
                $symbol = sanitize_text_field($symbols[$i]);
                $type = sanitize_text_field($types[$i]);
                
                if (!empty($key) && !empty($name) && !empty($symbol)) {
                    $updated_units[$key] = array(
                        'name' => $name,
                        'symbol' => $symbol,
                        'type' => $type
                    );
                }
            }
            
            // Replace entire units array with updated units
            $service_units = $updated_units;
        }
        
        // Add new unit if provided
        if (isset($_POST['csc_new_unit_key']) && !empty($_POST['csc_new_unit_key']) &&
            isset($_POST['csc_new_unit_name']) && !empty($_POST['csc_new_unit_name']) &&
            isset($_POST['csc_new_unit_symbol']) && !empty($_POST['csc_new_unit_symbol']) &&
            isset($_POST['csc_new_unit_type'])) {
            
            $new_key = sanitize_key($_POST['csc_new_unit_key']);
            $new_name = sanitize_text_field($_POST['csc_new_unit_name']);
            $new_symbol = sanitize_text_field($_POST['csc_new_unit_symbol']);
            $new_type = sanitize_text_field($_POST['csc_new_unit_type']);
            
            error_log("Adding new unit: $new_key, $new_name, $new_symbol, $new_type");
            
            $service_units[$new_key] = array(
                'name' => $new_name,
                'symbol' => $new_symbol,
                'type' => $new_type
            );
            
            error_log('Updated units array: ' . print_r($service_units, true));
        }
        
        // Update service units option
        update_option('csc_service_units', $service_units);
        
        // Add success message
        add_settings_error(
            'csc_units_settings',
            'save_success',
            __('Service units updated successfully.', 'construction-service-calculator'),
            'success'
        );
    }

    /**
     * Render the general settings section.
     *
     * @since    1.0.0
     */
    public function render_general_section() {
        _e('Configure general settings for the construction service calculator.', 'construction-service-calculator');
    }

    /**
     * Render the form settings section.
     *
     * @since    1.0.0
     */
    public function render_form_section() {
        _e('Customize the calculator form appearance and text.', 'construction-service-calculator');
    }

    /**
     * Render the email settings section.
     *
     * @since    1.0.0
     */
    public function render_email_section() {
        _e('Configure email notification settings for new submissions.', 'construction-service-calculator');
    }

    /**
     * Render the service units section.
     *
     * @since    1.0.0
     */
    public function render_units_section() {
        _e('Manage measurement units for construction services.', 'construction-service-calculator');
    }

    /**
     * Render the currency field.
     *
     * @since    1.0.0
     */
    public function render_currency_field() {
        $currency = get_option('csc_currency', 'USD');
        ?>
        <select name="csc_currency" id="csc_currency">
            <option value="USD" <?php selected($currency, 'USD'); ?>><?php _e('US Dollar (USD)', 'construction-service-calculator'); ?></option>
            <option value="EUR" <?php selected($currency, 'EUR'); ?>><?php _e('Euro (EUR)', 'construction-service-calculator'); ?></option>
            <option value="GBP" <?php selected($currency, 'GBP'); ?>><?php _e('British Pound (GBP)', 'construction-service-calculator'); ?></option>
            <option value="CAD" <?php selected($currency, 'CAD'); ?>><?php _e('Canadian Dollar (CAD)', 'construction-service-calculator'); ?></option>
            <option value="AUD" <?php selected($currency, 'AUD'); ?>><?php _e('Australian Dollar (AUD)', 'construction-service-calculator'); ?></option>
            <option value="JPY" <?php selected($currency, 'JPY'); ?>><?php _e('Japanese Yen (JPY)', 'construction-service-calculator'); ?></option>
            <option value="INR" <?php selected($currency, 'INR'); ?>><?php _e('Indian Rupee (INR)', 'construction-service-calculator'); ?></option>
            <option value="CNY" <?php selected($currency, 'CNY'); ?>><?php _e('Chinese Yuan (CNY)', 'construction-service-calculator'); ?></option>
            <option value="OTHER" <?php selected($currency, 'OTHER'); ?>><?php _e('Other (custom)', 'construction-service-calculator'); ?></option>
        </select>
        <p class="description"><?php _e('Select the currency for price calculations.', 'construction-service-calculator'); ?></p>
        <?php
    }

    /**
     * Render the currency symbol field.
     *
     * @since    1.0.0
     */
    public function render_currency_symbol_field() {
        $currency_symbol = get_option('csc_currency_symbol', '$');
        ?>
        <input type="text" name="csc_currency_symbol" id="csc_currency_symbol" value="<?php echo esc_attr($currency_symbol); ?>" class="regular-text" />
        <p class="description"><?php _e('Enter the currency symbol (e.g., $, €, £).', 'construction-service-calculator'); ?></p>
        <?php
    }

    /**
     * Render the currency position field.
     *
     * @since    1.0.0
     */
    public function render_currency_position_field() {
        $currency_position = get_option('csc_currency_position', 'before');
        ?>
        <select name="csc_currency_position" id="csc_currency_position">
            <option value="before" <?php selected($currency_position, 'before'); ?>><?php _e('Before - $100', 'construction-service-calculator'); ?></option>
            <option value="after" <?php selected($currency_position, 'after'); ?>><?php _e('After - 100$', 'construction-service-calculator'); ?></option>
        </select>
        <p class="description"><?php _e('Select the position of the currency symbol.', 'construction-service-calculator'); ?></p>
        <?php
    }

    /**
     * Render the number formatting field.
     *
     * @since    1.0.0
     */
    public function render_number_formatting_field() {
        $decimal_separator = get_option('csc_decimal_separator', '.');
        $thousand_separator = get_option('csc_thousand_separator', ',');
        $decimals = get_option('csc_decimals', 2);
        ?>
        <div class="csc-number-formatting">
            <label>
                <span><?php _e('Decimal Separator:', 'construction-service-calculator'); ?></span>
                <input type="text" name="csc_decimal_separator" value="<?php echo esc_attr($decimal_separator); ?>" maxlength="1" />
            </label>
            <br>
            <label>
                <span><?php _e('Thousand Separator:', 'construction-service-calculator'); ?></span>
                <input type="text" name="csc_thousand_separator" value="<?php echo esc_attr($thousand_separator); ?>" maxlength="1" />
            </label>
            <br>
            <label>
                <span><?php _e('Number of Decimals:', 'construction-service-calculator'); ?></span>
                <input type="number" name="csc_decimals" value="<?php echo esc_attr($decimals); ?>" min="0" max="4" step="1" />
            </label>
        </div>
        <p class="description"><?php _e('Configure how numbers and prices are formatted.', 'construction-service-calculator'); ?></p>
        <?php
    }

    /**
     * Render the tax settings field.
     *
     * @since    1.0.0
     */
    public function render_tax_settings_field() {
        $tax_rate = get_option('csc_tax_rate', 20);
        $tax_display = get_option('csc_tax_display', 'yes');
        ?>
        <div class="csc-tax-settings">
            <label>
                <span><?php _e('Tax Rate (%):', 'construction-service-calculator'); ?></span>
                <input type="number" name="csc_tax_rate" value="<?php echo esc_attr($tax_rate); ?>" min="0" max="100" step="0.01" />
            </label>
            <br>
            <label>
                <input type="radio" name="csc_tax_display" value="yes" <?php checked($tax_display, 'yes'); ?> />
                <?php _e('Show tax separately in calculations', 'construction-service-calculator'); ?>
            </label>
            <br>
            <label>
                <input type="radio" name="csc_tax_display" value="no" <?php checked($tax_display, 'no'); ?> />
                <?php _e('Include tax in the total (don\'t show separately)', 'construction-service-calculator'); ?>
            </label>
        </div>
        <p class="description"><?php _e('Configure tax settings for price calculations.', 'construction-service-calculator'); ?></p>
        <?php
    }

    /**
     * Render the theme field.
     *
     * @since    1.0.0
     */
    public function render_theme_field() {
        $theme = get_option('csc_theme', 'default');
        ?>
        <select name="csc_theme" id="csc_theme">
            <option value="default" <?php selected($theme, 'default'); ?>><?php _e('Default', 'construction-service-calculator'); ?></option>
            <option value="blue" <?php selected($theme, 'blue'); ?>><?php _e('Blue', 'construction-service-calculator'); ?></option>
            <option value="orange" <?php selected($theme, 'orange'); ?>><?php _e('Orange', 'construction-service-calculator'); ?></option>
            <option value="dark" <?php selected($theme, 'dark'); ?>><?php _e('Dark', 'construction-service-calculator'); ?></option>
        </select>
        
        <div class="csc-theme-preview">
            <div class="csc-theme-sample default <?php echo $theme === 'default' ? 'active' : ''; ?>">
                <div class="csc-theme-sample-header"><?php _e('Default Theme', 'construction-service-calculator'); ?></div>
                <div class="csc-theme-sample-body"></div>
            </div>
            <div class="csc-theme-sample blue <?php echo $theme === 'blue' ? 'active' : ''; ?>">
                <div class="csc-theme-sample-header"><?php _e('Blue Theme', 'construction-service-calculator'); ?></div>
                <div class="csc-theme-sample-body"></div>
            </div>
            <div class="csc-theme-sample orange <?php echo $theme === 'orange' ? 'active' : ''; ?>">
                <div class="csc-theme-sample-header"><?php _e('Orange Theme', 'construction-service-calculator'); ?></div>
                <div class="csc-theme-sample-body"></div>
            </div>
            <div class="csc-theme-sample dark <?php echo $theme === 'dark' ? 'active' : ''; ?>">
                <div class="csc-theme-sample-header"><?php _e('Dark Theme', 'construction-service-calculator'); ?></div>
                <div class="csc-theme-sample-body"></div>
            </div>
        </div>
        
        <p class="description"><?php _e('Select a color theme for the calculator.', 'construction-service-calculator'); ?></p>
        <?php
    }

    /**
     * Render the form title field.
     *
     * @since    1.0.0
     */
    public function render_form_title_field() {
        $form_title = get_option('csc_form_title', __('Construction Service Calculator', 'construction-service-calculator'));
        ?>
        <input type="text" name="csc_form_title" id="csc_form_title" value="<?php echo esc_attr($form_title); ?>" class="regular-text" />
        <p class="description"><?php _e('The title displayed at the top of the calculator form.', 'construction-service-calculator'); ?></p>
        <?php
    }

    /**
     * Render the form description field.
     *
     * @since    1.0.0
     */
    public function render_form_description_field() {
        $form_description = get_option('csc_form_description', __('Calculate the cost of your construction project instantly', 'construction-service-calculator'));
        ?>
        <textarea name="csc_form_description" id="csc_form_description" rows="3" class="large-text"><?php echo esc_textarea($form_description); ?></textarea>
        <p class="description"><?php _e('A short description displayed below the form title.', 'construction-service-calculator'); ?></p>
        <?php
    }

    /**
     * Render the submit button text field.
     *
     * @since    1.0.0
     */
    public function render_submit_button_text_field() {
        $submit_button_text = get_option('csc_submit_button_text', __('Submit Inquiry', 'construction-service-calculator'));
        ?>
        <input type="text" name="csc_submit_button_text" id="csc_submit_button_text" value="<?php echo esc_attr($submit_button_text); ?>" class="regular-text" />
        <p class="description"><?php _e('The text displayed on the submission button.', 'construction-service-calculator'); ?></p>
        <?php
    }

    /**
     * Render the email notifications field.
     *
     * @since    1.0.0
     */
    public function render_email_notifications_field() {
        $email_notifications = get_option('csc_email_notifications', 'yes');
        ?>
        <label>
            <input type="checkbox" name="csc_email_notifications" value="yes" <?php checked($email_notifications, 'yes'); ?> />
            <?php _e('Send email notifications for new submissions', 'construction-service-calculator'); ?>
        </label>
        <p class="description"><?php _e('Enable or disable email notifications when a new inquiry is submitted.', 'construction-service-calculator'); ?></p>
        <?php
    }

    /**
     * Render the admin email field.
     *
     * @since    1.0.0
     */
    public function render_admin_email_field() {
        $admin_email = get_option('csc_admin_email', get_option('admin_email'));
        ?>
        <input type="email" name="csc_admin_email" id="csc_admin_email" value="<?php echo esc_attr($admin_email); ?>" class="regular-text" />
        <p class="description"><?php _e('The email address where notifications will be sent.', 'construction-service-calculator'); ?></p>
        <?php
    }
}