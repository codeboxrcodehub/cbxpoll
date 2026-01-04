<?php
// If this file is called directly, abort.
if ( ! defined('WPINC')) {
	die;
}

use Cbx\Poll\Helpers\PollHelper;
use Cbx\Poll\CBXPollAdmin;
use Cbx\Poll\CBXPollPublic;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://codeboxr.com
 * @since      1.0.0
 *
 * @package    CBXPoll
 * @subpackage CBXPoll/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    CBXPollapi
 * @subpackage CBXPoll/includes
 * @author     codeboxr <info@codeboxr.com>
 */
final class CBXPoll {
	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since  2.0.0
	 */
	private static $instance = null;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	//protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	//protected $version;

    /**
     * Singleton Instance.
     * @return CBXPoll|self|null
     * @since 2.0.0
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }//end method instance

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		//$this->version     = CBXPOLL_PLUGIN_VERSION;
		//$this->plugin_name = CBXPOLL_PLUGIN_NAME;

        if ( cbxpoll_compatible_php_version() ) {
            $GLOBALS['cbxpoll_loaded'] = true;

            $this->include_files();

            $this->define_common_hooks();
            $this->define_admin_hooks();
            $this->define_public_hooks();
        }
        else {
            add_action( 'admin_notices', [ $this, 'php_version_notice' ] );
        }


		//$this->load_dependencies();


	}//end constructor



	/**
	 * Cloning is forbidden.
	 *
	 * @since 2.0.0
	 */
	public function __clone() {
		cbxpoll_doing_it_wrong( __FUNCTION__, esc_html__( 'Cloning is forbidden.', 'cbxpoll' ), '2.0.0' );
	}//end method clone

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 2.0.0
	 */
	public function __wakeup() {
		cbxpoll_doing_it_wrong( __FUNCTION__, esc_html__( 'Unserializing instances of this class is forbidden.', 'cbxpoll' ), '2.0.0' );
	}//end method wakeup

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @return void
	 */
	private function include_files() {
        require_once __DIR__ . '/../vendor/autoload.php';
        include_once __DIR__ . '/CBXPollEmails.php';
	}//end method include_files

    /**
     * Email Class.
     *
     * @return CBXPollEmails
     * @since 2.0.0
     */
    public function mailer() {
        return CBXPollEmails::instance();
    }//end method mailer


	/**
	 * All the common hooks
	 *
	 * @since    1.1.1
	 * @access   private
	 */
	private function define_common_hooks() {
        $helper = new PollHelper();

		//add_action( 'plugins_loaded', [ $this, 'load_plugin_textdomain' ] );

        add_action( 'init', [ $helper, 'load_mailer' ] );
	}//end method define_common_hooks



	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.1.1
	 */
	public function load_plugin_textdomain() {
		//load_plugin_textdomain( 'cbxpoll', false, CBXPOLL_ROOT_PATH . 'languages/' );
	}//end method load_plugin_textdomain


	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		global $wp_version;

		$plugin_admin = new CBXPollAdmin();

		// init cookie and custom post types
		add_action( 'init', [ $plugin_admin, 'init_cbxpoll_type' ] );


		//add js and css in admin end
		add_action( 'admin_enqueue_scripts', [ $plugin_admin, 'enqueue_styles' ] );
		add_action( 'admin_enqueue_scripts', [ $plugin_admin, 'enqueue_scripts' ] );

		//on admin init setting init and cbxpoll type post tdelete hook
		add_action( 'admin_init', [ $plugin_admin, 'admin_init' ] );

		// add global settings menu
		add_action( 'admin_menu', [ $plugin_admin, 'admin_menu' ], 11 );

		// add custom status column in table
		add_filter( 'manage_edit-cbxpoll_columns', [ $plugin_admin, 'add_new_poll_columns' ] );
		add_action( 'manage_cbxpoll_posts_custom_column', [ $plugin_admin, 'manage_poll_columns' ], 10, 2 );
		//add_filter( 'manage_edit-cbxpoll_sortable_columns', [ $plugin_admin, 'cbxpoll_column_sort' ] );


		// add meta box and hook save meta box
		add_action( 'add_meta_boxes', [ $plugin_admin, 'metaboxes_display' ] );
		add_action( 'save_post', [ $plugin_admin, 'metabox_save' ] );

		add_action( 'wp_ajax_cbxpoll_get_answer_template', [ $plugin_admin, 'get_answer_template' ] );


		//on user delete
		add_action( 'delete_user', [ $plugin_admin, 'on_user_delete_vote_delete' ] );


		//plugin upgrade and notice
		add_action( 'plugins_loaded', [ $plugin_admin, 'plugin_upgrader_process_complete' ]);
		add_action( 'admin_notices', [ $plugin_admin, 'plugin_activate_upgrade_notices' ] );
		add_filter( 'plugin_action_links_' . CBXPOLL_BASE_NAME, [ $plugin_admin, 'plugin_listing_setting_link' ] );
		add_filter( 'plugin_row_meta', [ $plugin_admin, 'custom_plugin_row_meta' ], 10, 4 );
		add_action( 'activated_plugin', [ $plugin_admin, 'check_pro_addon' ] );
		add_action( 'init', [ $plugin_admin, 'check_pro_addon' ] );
		add_action( 'after_plugin_row_cbxpollproaddon/cbxpollproaddon.php', [
			$plugin_admin,
			'custom_message_after_plugin_row_proaddon'
		], 10, 2 );


		//gutenberg
		add_action( 'init', [ $plugin_admin, 'gutenberg_blocks' ] );
		if ( version_compare( $wp_version, '5.8' ) >= 0 ) {
			add_filter( 'block_categories_all', [ $plugin_admin, 'gutenberg_block_categories' ], 10, 2 );
		} else {
			add_filter( 'block_categories', [ $plugin_admin, 'gutenberg_block_categories' ], 10, 2 );
		}

		add_action( 'enqueue_block_editor_assets', [ $plugin_admin, 'enqueue_block_editor_assets' ] );

		//ajax plugin reset
		add_action( 'wp_ajax_cbxpoll_settings_reset_load', [ $plugin_admin, 'settings_reset_load' ] );
		add_action( 'wp_ajax_cbxpoll_settings_reset', [ $plugin_admin, 'plugin_reset' ] );

		//setting misc
		add_action( 'wp_ajax_cbxpoll_permalink_cache_clear', [ $plugin_admin, 'permalink_cache_clear' ] );

        add_action( 'admin_init', [ $plugin_admin, 'save_email_setting' ] );
	}//end method define_admin_hooks

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new CBXPollPublic();

		// init cookie
		add_action( 'template_redirect', [ $plugin_public, 'init_cookie' ] );
		//add_action( 'init', [$plugin_public, 'init_cookie'] ); //need to check

		//poll display method 'text' hook
		add_filter( 'cbxpoll_display_options', [ $plugin_public, 'poll_display_methods_text' ] );

		add_action( 'wp_enqueue_scripts', [ $plugin_public, 'enqueue_styles' ] );
		add_action( 'wp_enqueue_scripts', [ $plugin_public, 'enqueue_scripts' ] );

		//adding shortcode

		add_action( 'init', [ $plugin_public, 'init_shortcodes' ] );


		//Show poll in details poll post type
		if ( ! is_admin() ) {
			add_filter( 'the_content', [ $plugin_public, 'cbxpoll_the_content' ] );
			add_filter( 'the_excerpt', [ $plugin_public, 'cbxpoll_the_excerpt' ] );
		}

		// ajax for voting
		add_action( 'wp_ajax_cbxpoll_user_vote', [ $plugin_public, 'ajax_vote' ] );
		add_action( 'wp_ajax_nopriv_cbxpoll_user_vote', [ $plugin_public, 'ajax_vote'] );

		// ajax for read more page
		add_action( 'wp_ajax_cbxpoll_list_pagination', [ $plugin_public, 'ajax_poll_list' ] );
		add_action( 'wp_ajax_nopriv_cbxpoll_list_pagination', [ $plugin_public, 'ajax_poll_list' ] );

		add_action( 'widgets_init', [ $plugin_public, 'init_widgets' ] );

		//elementor
		add_action( 'init', [ $plugin_public, 'init_misc' ] );

		//add_action('admin_init', [$plugin_public,  'admin_init_ajax_lang']);
	}//end method define_public_hooks


	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	/*public function get_plugin_name() {
		return $this->plugin_name;
	}//end method get_plugin_name*/

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	/*public function get_version() {
		return $this->version;
	}//end method get_version*/

    /**
     * Show php version notice in dashboard
     *
     * @return void
     */
    public function php_version_notice() {
        echo '<div class="error"><p>';
        /* Translators:  PHP Version */
        echo sprintf(esc_html__( 'CBX Poll requires at least PHP %s. Please upgrade PHP to run CBX Poll.', 'cbxpoll' ), esc_attr(CBXPOLL_PHP_MIN_VERSION));
        echo '</p></div>';
    }//end method php_version_notice
}//end class CBXPoll