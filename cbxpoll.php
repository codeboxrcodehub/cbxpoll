<?php
/**
 *
 * @link              https://codeboxr.com
 * @since             1.0.0
 * @package           Cbxpoll
 *
 * @wordpress-plugin
 * Plugin Name:       CBX Poll
 * Plugin URI:        https://codeboxr.com/product/cbx-poll-for-wordpress/
 * Description:       Poll and vote system for WordPress
 * Version:           2.0.1
 * Author:            codeboxr
 * Author URI:        https://codeboxr.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cbxpoll
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use Cbx\Poll\Helpers\PollHelper;



//plugin definition specific constants
defined( 'CBXPOLL_PLUGIN_NAME' ) or define( 'CBXPOLL_PLUGIN_NAME', 'cbxpoll' );
defined( 'CBXPOLL_PLUGIN_VERSION' ) or define( 'CBXPOLL_PLUGIN_VERSION', '2.0.1' );
defined( 'CBXPOLL_BASE_NAME' ) or define( 'CBXPOLL_BASE_NAME', plugin_basename( __FILE__ ) );
defined( 'CBXPOLL_ROOT_PATH' ) or define( 'CBXPOLL_ROOT_PATH', plugin_dir_path( __FILE__ ) );
defined( 'CBXPOLL_ROOT_URL' ) or define( 'CBXPOLL_ROOT_URL', plugin_dir_url( __FILE__ ) );

defined( 'CBXPOLL_PRO_VERSION' ) or define( 'CBXPOLL_PRO_VERSION', '2.0.1' );

//plugin functionality specific constants
defined( 'CBXPOLL_COOKIE_EXPIRATION' ) or define( 'CBXPOLL_COOKIE_EXPIRATION', time() + 1209600 ); //Expiration of 14 days.
defined( 'CBXPOLL_COOKIE_NAME' ) or define( 'CBXPOLL_COOKIE_NAME', 'cbxpoll-cookie' );
defined( 'CBXPOLL_RAND_MIN' ) or define( 'CBXPOLL_RAND_MIN', 0 );
defined( 'CBXPOLL_RAND_MAX' ) or define( 'CBXPOLL_RAND_MAX', 999999 );
defined( 'CBXPOLL_COOKIE_EXP_14D' ) or define( 'CBXPOLL_COOKIE_EXP_14D', time() + 1209600 ); //Expiration of 14 days.
defined( 'CBXPOLL_COOKIE_EXP_7D' ) or define( 'CBXPOLL_COOKIE_EXP_7D', time() + 604800 );    //Expiration of 7 days.

defined( 'CBXPOLL_WP_MIN_VERSION' ) or define( 'CBXPOLL_WP_MIN_VERSION', '5.3' );
defined( 'CBXPOLL_PHP_MIN_VERSION' ) or define( 'CBXPOLL_PHP_MIN_VERSION', '7.4' );

// Include the main class
if ( ! class_exists( 'CBXPoll', false ) ) {
    include_once CBXPOLL_ROOT_PATH . 'includes/CBXPoll.php';
}


/**
 * Checking wp version
 *
 * @param $version
 *
 * @return bool
 */
function cbxpoll_compatible_wp_version( $version = '' ) {
    if($version == '') $version = CBXPOLL_WP_MIN_VERSION;

    if ( version_compare( $GLOBALS['wp_version'], $version, '<' ) ) {
        return false;
    }

    // Add sanity checks for other version requirements here

    return true;
}//end method cbxpoll_compatible_wp_version

/**
 * Checking php version
 *
 * @param $version
 *
 * @return bool
 */
function cbxpoll_compatible_php_version( $version = '' ) {
    if($version == '') $version = CBXPOLL_PHP_MIN_VERSION;

    if ( version_compare( PHP_VERSION, $version, '<' ) ) {
        return false;
    }

    return true;
}//end method cbxpoll_compatible_php_version

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/CBXPollActivator.php
 */
function cbxpoll_activate() {
    $wp_version  = CBXPOLL_WP_MIN_VERSION;
    $php_version = CBXPOLL_PHP_MIN_VERSION;

    $activate_ok = true;

    if ( ! cbxpoll_compatible_wp_version() ) {
        $activate_ok = false;

        deactivate_plugins( plugin_basename( __FILE__ ) );

        /* translators: WordPress version */
        wp_die( sprintf( esc_html__( 'CBX Poll plugin requires WordPress %s or higher!', 'cbxpoll' ), esc_attr($wp_version) ) ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    if ( ! cbxpoll_compatible_php_version() ) {
        $activate_ok = false;

        deactivate_plugins( plugin_basename( __FILE__ ) );

        /* translators: PHP version */
        wp_die( sprintf( esc_html__( 'CBX Poll plugin requires PHP %s or higher!', 'cbxpoll' ), esc_attr($php_version) ) ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    if($activate_ok){
	    require_once CBXPOLL_ROOT_PATH . 'includes/CBXPollActivator.php';
	    CBXPollActivator::activate();
    }
}//end function cbxpoll_activate

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/CBXPollDeactivator.php
 */
function cbxpoll_deactivate() {
	require_once CBXPOLL_ROOT_PATH . 'includes/CBXPollDeactivator.php';
	CBXPollDeactivator::deactivate();
}//end function cbxpoll_deactivate

register_activation_hook( __FILE__, 'cbxpoll_activate' );
register_deactivation_hook( __FILE__, 'cbxpoll_deactivate' );

/**
 * Initialize the plugin manually
 *
 * @return CBXPoll|null
 */
function cbxpoll_core() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
    global $cbxpoll_core;

    if ( ! isset( $cbxpoll_core ) ) {
        $cbxpoll_core = cbxpoll_run();
    }

    return $cbxpoll_core;
}//end method cbxpoll_core


/**
 * Begins execution of the plugin.
 *
 * @since    2.0.0
 */
function cbxpoll_run() {
    return CBXPoll::instance();
}//end function cbxpoll_run


//load the plugin
$GLOBALS['cbxpoll_core'] = cbxpoll_run();