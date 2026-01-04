<?php
// If this file is called directly, abort.
if ( ! defined('WPINC')) {
	die;
}

/**
 * Fired during plugin activation
 *
 * @link       https://codeboxr.com
 * @since      1.0.0
 *
 * @package    Cbxpoll
 * @subpackage Cbxpoll/includes
 */

use Cbx\Poll\Helpers\PollHelper;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    CBXPoll
 * @subpackage CBXPoll/includes
 * @author     codeboxr <info@codeboxr.com>
 */
class CBXPollActivator {

	/**
	 * Plugin activation method
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		PollHelper::install_table();

		//add flag for rewrite cache flush
		add_option( 'cbxpoll_flush_rewrite_rules', 'true' );

		//add flag for activation notice
		set_transient( 'cbxpoll_activated_notice', 1 );

		// Update the saved version
		update_option('cbxpoll_version', CBXPOLL_PLUGIN_VERSION);

		//hook for others
		do_action( 'cbxpoll_on_activation' );
	}//end method activate
}//end class CBXPollActivator