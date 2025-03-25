<?php
/**
 * The services management functionality of the plugin.
 *
 * @link       https://github.com/42prom
 * @since      1.0.0
 *
 * @package    Construction_Service_Calculator
 * @subpackage Construction_Service_Calculator/admin
 */

/**
 * The services management functionality of the plugin.
 *
 * Handles the creation and management of construction services.
 *
 * @package    Construction_Service_Calculator
 * @subpackage Construction_Service_Calculator/admin
 * @author     Mikheili Nakeuri
 */
class Construction_Service_Calculator_Services {

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
     * Register meta boxes for the service post type.
     *
     * @since    1.0.0
     */
    public function register_meta_boxes() {
        add_meta_box(
            'csc_service_details',
            __('Service Details', 'construction-service-calculator'),
            array($this, 'render_service_details_meta_box'),
            'csc_service',
            'normal',
            'high'
        );
        
        add_meta_box(
            'csc_service_icon',
            __('Service Icon', 'construction-service-calculator'),
            array($this, 'render_service_icon_meta_box'),
            'csc_service',
            'side',
            'default'
        );
        
        add_meta_box(
            'csc_service_custom_fields',
            __('Custom Fields', 'construction-service-calculator'),
            array($this, 'render_service_custom_fields_meta_box'),
            'csc_service',
            'normal',
            'low'
        );
    }

    /**
     * Render the service details meta box.
     *
     * @since    1.0.0
     * @param    WP_Post    $post    The post object.
     */
    public function render_service_details_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('csc_service_meta_nonce', 'csc_service_meta_nonce');
        
        // Get saved values
        $rate = get_post_meta($post->ID, '_csc_rate', true);
        $unit = get_post_meta($post->ID, '_csc_unit', true);
        $category = get_post_meta($post->ID, '_csc_category', true);
        $description = get_post_meta($post->ID, '_csc_description', true);
        $min_order = get_post_meta($post->ID, '_csc_min_order', true);
        $max_order = get_post_meta($post->ID, '_csc_max_order', true);
        $step = get_post_meta($post->ID, '_csc_step', true);
        
        // Get service units
        $service_units = get_option('csc_service_units', array());
        
        // Get service categories
        $service_categories = get_option('csc_service_categories', array());
        
        // Output the fields
        ?>
        <table class="form-table csc-meta-table">
            <tr>
                <th><label for="csc_rate"><?php _e('Service Rate', 'construction-service-calculator'); ?></label></th>
                <td>
                    <input type="number" step="0.01" min="0" name="csc_rate" id="csc_rate" value="<?php echo esc_attr($rate); ?>" class="regular-text" required />
                    <p class="description"><?php _e('The rate per unit for this service', 'construction-service-calculator'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="csc_unit"><?php _e('Unit Type', 'construction-service-calculator'); ?></label></th>
                <td>
                    <select name="csc_unit" id="csc_unit" required>
                        <option value=""><?php _e('Select Unit Type', 'construction-service-calculator'); ?></option>
                        <?php foreach ($service_units as $key => $unit_data) : ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($unit, $key); ?>>
                                <?php echo esc_html($unit_data['name']); ?> (<?php echo esc_html($unit_data['symbol']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php _e('The unit of measurement for this service', 'construction-service-calculator'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="csc_category"><?php _e('Category', 'construction-service-calculator'); ?></label></th>
                <td>
                    <select name="csc_category" id="csc_category" required>
                        <option value=""><?php _e('Select Category', 'construction-service-calculator'); ?></option>
                        <?php foreach ($service_categories as $key => $name) : ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($category, $key); ?>>
                                <?php echo esc_html($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php _e('The category this service belongs to', 'construction-service-calculator'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="csc_description"><?php _e('Short Description', 'construction-service-calculator'); ?></label></th>
                <td>
                    <textarea name="csc_description" id="csc_description" rows="3" class="large-text"><?php echo esc_textarea($description); ?></textarea>
                    <p class="description"><?php _e('A short description of this service (optional)', 'construction-service-calculator'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="csc_min_order"><?php _e('Minimum Order', 'construction-service-calculator'); ?></label></th>
                <td>
                    <input type="number" step="0.01" min="0" name="csc_min_order" id="csc_min_order" value="<?php echo esc_attr($min_order); ?>" class="regular-text" />
                    <p class="description"><?php _e('The minimum quantity that can be ordered (leave empty for no minimum)', 'construction-service-calculator'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="csc_max_order"><?php _e('Maximum Order', 'construction-service-calculator'); ?></label></th>
                <td>
                    <input type="number" step="0.01" min="0" name="csc_max_order" id="csc_max_order" value="<?php echo esc_attr($max_order); ?>" class="regular-text" />
                    <p class="description"><?php _e('The maximum quantity that can be ordered (leave empty for no maximum)', 'construction-service-calculator'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="csc_step"><?php _e('Step', 'construction-service-calculator'); ?></label></th>
                <td>
                    <input type="number" step="0.01" min="0.01" name="csc_step" id="csc_step" value="<?php echo esc_attr($step ? $step : '0.1'); ?>" class="regular-text" />
                    <p class="description"><?php _e('The step value for quantity increments (default: 0.1)', 'construction-service-calculator'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Render the service icon meta box.
     *
     * @since    1.0.0
     * @param    WP_Post    $post    The post object.
     */
    public function render_service_icon_meta_box($post) {
        // Get saved icon URL
        $icon_url = get_post_meta($post->ID, '_csc_icon_url', true);
        
        // Get SVG content if URL exists
        $icon_content = '';
        if (!empty($icon_url)) {
            require_once CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'includes/class-svg-handler.php';
            $icon_content = Construction_Service_Calculator_SVG_Handler::get_svg_content($icon_url);
        }
        ?>
        <div class="csc-icon-uploader">
            <div class="csc-icon-preview">
                <?php if (!empty($icon_content)) : ?>
                    <div class="csc-svg-preview">
                        <?php echo $icon_content; ?>
                    </div>
                <?php else : ?>
                    <div class="csc-no-icon">
                        <?php _e('No icon selected', 'construction-service-calculator'); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <input type="hidden" name="csc_icon_url" id="csc_icon_url" value="<?php echo esc_attr($icon_url); ?>" />
            
            <div class="csc-icon-buttons">
                <button type="button" class="button csc-upload-icon-button">
                    <?php _e('Upload SVG Icon', 'construction-service-calculator'); ?>
                </button>
                
                <button type="button" class="button csc-select-library-icon-button">
                    <?php _e('Select from Library', 'construction-service-calculator'); ?>
                </button>
                
                <?php if (!empty($icon_url)) : ?>
                    <button type="button" class="button csc-remove-icon-button">
                        <?php _e('Remove Icon', 'construction-service-calculator'); ?>
                    </button>
                <?php endif; ?>
            </div>
            
            <p class="description">
                <?php _e('Upload or select an SVG icon for this service. For best results, use simple, single-color SVG files.', 'construction-service-calculator'); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Render the service custom fields meta box.
     *
     * @since    1.0.0
     * @param    WP_Post    $post    The post object.
     */
    public function render_service_custom_fields_meta_box($post) {
        // Get saved custom fields
        $custom_fields = get_post_meta($post->ID, '_csc_custom_fields', true);
        if (!is_array($custom_fields)) {
            $custom_fields = array();
        }
        ?>
        <div class="csc-custom-fields">
            <p class="description">
                <?php _e('Add custom fields for this service. These fields can be used for additional information or specific calculations.', 'construction-service-calculator'); ?>
            </p>
            
            <table class="widefat csc-custom-fields-table">
                <thead>
                    <tr>
                        <th class="csc-cf-name"><?php _e('Field Name', 'construction-service-calculator'); ?></th>
                        <th class="csc-cf-value"><?php _e('Field Value', 'construction-service-calculator'); ?></th>
                        <th class="csc-cf-action"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($custom_fields)) : ?>
                        <?php foreach ($custom_fields as $key => $value) : ?>
                            <tr class="csc-custom-field-row">
                                <td>
                                    <input type="text" name="csc_custom_field_keys[]" value="<?php echo esc_attr($key); ?>" class="regular-text" />
                                </td>
                                <td>
                                    <input type="text" name="csc_custom_field_values[]" value="<?php echo esc_attr($value); ?>" class="regular-text" />
                                </td>
                                <td>
                                    <button type="button" class="button csc-remove-field-button">
                                        <?php _e('Remove', 'construction-service-calculator'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr class="csc-no-custom-fields">
                            <td colspan="3"><?php _e('No custom fields added yet.', 'construction-service-calculator'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3">
                            <button type="button" class="button csc-add-field-button">
                                <?php _e('Add Custom Field', 'construction-service-calculator'); ?>
                            </button>
                        </td>
                    </tr>
                </tfoot>
            </table>
            
            <!-- Template for new custom field row -->
            <template id="csc-custom-field-template">
                <tr class="csc-custom-field-row">
                    <td>
                        <input type="text" name="csc_custom_field_keys[]" value="" class="regular-text" />
                    </td>
                    <td>
                        <input type="text" name="csc_custom_field_values[]" value="" class="regular-text" />
                    </td>
                    <td>
                        <button type="button" class="button csc-remove-field-button">
                            <?php _e('Remove', 'construction-service-calculator'); ?>
                        </button>
                    </td>
                </tr>
            </template>
        </div>
        <?php
    }

    /**
     * Save service meta when the post is saved.
     *
     * @since    1.0.0
     * @param    int    $post_id    The ID of the post being saved.
     */
    public function save_service_meta($post_id) {
        // Check if our nonce is set
        if (!isset($_POST['csc_service_meta_nonce'])) {
            return;
        }
        
        // Verify the nonce
        if (!wp_verify_nonce($_POST['csc_service_meta_nonce'], 'csc_service_meta_nonce')) {
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
        
        // Save standard meta fields
        $fields = array(
            'csc_rate' => 'floatval',
            'csc_unit' => 'sanitize_text_field',
            'csc_category' => 'sanitize_text_field',
            'csc_description' => 'sanitize_textarea_field',
            'csc_min_order' => 'floatval',
            'csc_max_order' => 'floatval',
            'csc_step' => 'floatval',
            'csc_icon_url' => 'esc_url_raw'
        );
        
        foreach ($fields as $field => $sanitize_callback) {
            if (isset($_POST[$field])) {
                $value = call_user_func($sanitize_callback, $_POST[$field]);
                update_post_meta($post_id, '_' . $field, $value);
            }
        }
        
        // Save custom fields
        if (isset($_POST['csc_custom_field_keys']) && isset($_POST['csc_custom_field_values'])) {
            $keys = $_POST['csc_custom_field_keys'];
            $values = $_POST['csc_custom_field_values'];
            
            $custom_fields = array();
            
            for ($i = 0; $i < count($keys); $i++) {
                $key = sanitize_text_field($keys[$i]);
                $value = sanitize_text_field($values[$i]);
                
                if (!empty($key)) {
                    $custom_fields[$key] = $value;
                }
            }
            
            update_post_meta($post_id, '_csc_custom_fields', $custom_fields);
        } else {
            // If no custom fields submitted, clear the meta
            delete_post_meta($post_id, '_csc_custom_fields');
        }
    }

    /**
     * Handle AJAX upload SVG request.
     *
     * @since    1.0.0
     */
    public function ajax_upload_svg() {
        // Check for nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'csc_admin_nonce')) {
            wp_send_json_error(__('Security check failed.', 'construction-service-calculator'));
        }
        
        // Check if file was uploaded
        if (!isset($_FILES['svg_file']) || empty($_FILES['svg_file']['tmp_name'])) {
            wp_send_json_error(__('No file uploaded.', 'construction-service-calculator'));
        }
        
        // Check if user has permission
        if (!current_user_can('upload_files')) {
            wp_send_json_error(__('You do not have permission to upload files.', 'construction-service-calculator'));
        }
        
        // Get optional category
        $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
        
        // Process the upload
        require_once CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'includes/class-svg-handler.php';
        $result = Construction_Service_Calculator_SVG_Handler::upload_svg($_FILES['svg_file'], $category);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * Handle AJAX get SVG library request.
     *
     * @since    1.0.0
     */
    public function ajax_get_svg_library() {
        // Check for nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'csc_admin_nonce')) {
            wp_send_json_error(__('Security check failed.', 'construction-service-calculator'));
        }
        
        // Get optional category
        $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
        
        // Get the icons
        require_once CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'includes/class-svg-handler.php';
        $icons = Construction_Service_Calculator_SVG_Handler::get_svg_library($category);
        
        wp_send_json_success(array(
            'icons' => $icons
        ));
    }
}