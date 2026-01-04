<?php
// If this file is called directly, abort.
if ( ! defined('WPINC')) {
	die;
}

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    CBXPoll
 * @subpackage CBXPoll/includes
 * @author     codeboxr <info@codeboxr.com>
 */
class CBXPollDeactivator {
	/**
	 * Plugin Deactivation method
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		delete_option( 'cbxpoll_flush_rewrite_rules' );

		//hook for others
		do_action( 'cbxpoll_on_deactivation' );
	}//end method deactivate
}//end class CBXPollDeactivator
