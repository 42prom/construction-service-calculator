<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://github.com/42prom
 * @since      1.0.0
 *
 * @package    Construction_Service_Calculator
 * @subpackage Construction_Service_Calculator/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Construction_Service_Calculator
 * @subpackage Construction_Service_Calculator/includes
 * @author     Mikheili Nakeuri
 */
class Construction_Service_Calculator_Deactivator {

    /**
     * Deactivate the plugin.
     *
     * Flush rewrite rules on deactivation.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}