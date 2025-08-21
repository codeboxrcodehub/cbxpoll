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

		add_option( 'cbxpoll_flush_rewrite_rules', 'true' );

		set_transient( 'cbxpoll_activated_notice', 1 );

		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		if ( in_array( 'cbxpollproaddon/cbxpollproaddon.php', apply_filters( 'active_plugins',
				get_option( 'active_plugins' ) ) ) || defined( 'CBXPOLLPROADDON_PLUGIN_NAME' ) ) {
			//plugin is activated

			$pro_plugin_version = CBXPOLLPROADDON_PLUGIN_VERSION;


			if ( version_compare( $pro_plugin_version, '2.0.0', '<' ) ) {
				deactivate_plugins( 'cbxpollproaddon/cbxpollproaddon.php' );
				set_transient( 'cbxpollproaddon_forcedactivated_notice', 1 );
			}
		}

		// Update the saved version
		update_option('cbxpoll_version', CBXPOLL_PLUGIN_VERSION);
	}//end method activate
}//end class CBXPollActivator