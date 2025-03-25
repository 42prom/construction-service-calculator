<?php
/**
 * The plugin bootstrap file
 *
 * @link              https://github.com/42prom
 * @since             1.0.0
 * @package           Construction_Service_Calculator
 *
 * @wordpress-plugin
 * Plugin Name:       Construction Service Cost Calculator
 * Plugin URI:        https://mikheili-nakeuri.com/plugins/construction-service-calculator
 * Description:       A comprehensive cost calculator for construction and renovation services with real-time calculations and no registration required.
 * Version:           1.0.0
 * Author:            Mikheili Nakeuri
 * Author URI:        https://mikheili-nakeuri.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       construction-service-calculator
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 */
define('CONSTRUCTION_SERVICE_CALCULATOR_VERSION', '1.0.0');
define('CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function activate_construction_service_calculator() {
    require_once CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'includes/class-activator.php';
    Construction_Service_Calculator_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_construction_service_calculator() {
    require_once CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'includes/class-deactivator.php';
    Construction_Service_Calculator_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_construction_service_calculator');
register_deactivation_hook(__FILE__, 'deactivate_construction_service_calculator');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once CONSTRUCTION_SERVICE_CALCULATOR_PLUGIN_DIR . 'includes/class-construction-service-calculator.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_construction_service_calculator() {
    $plugin = new Construction_Service_Calculator();
    $plugin->run();
}

run_construction_service_calculator();