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


/**
 * The code that runs during plugin uninstall.
 */
function uninstall_cbxpoll() {
	require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
	CBXPollUninstall::uninstall();
}//end function uninstall_cbxpoll

if ( ! defined( 'CBXPOLL_PLUGIN_NAME' ) ) {
	uninstall_cbxpoll();
}