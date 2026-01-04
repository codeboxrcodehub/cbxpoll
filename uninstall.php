<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Fired when the plugin is uninstalled.
 *
 *
 * @link       http://codeboxr.com
 * @since      2.0.0
 *
 * @package    cbxpoll
 */

// If uninstall not called from WordPress, then exit.
use Cbx\Poll\CBXPollUninstall;

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}


/**
 * The code that runs during plugin uninstall.
 */
function cbxpoll_uninstall() {
	require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
	CBXPollUninstall::uninstall();
}//end function cbxpoll_uninstall

if ( ! defined( 'CBXPOLL_PLUGIN_NAME' ) ) {
	cbxpoll_uninstall();
}