<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://codeboxr.com
 * @since      1.0.0
 *
 * @package    CBXPoll
 * @subpackage CBXPoll/admin
 */

namespace Cbx\Poll;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

use Cbx\Poll\Helpers\PollHelper;
use Cbx\Poll\PollSettings;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    CBXPoll
 * @subpackage CBXPoll/includes
 * @author     codeboxr <info@codeboxr.com>
 */
class CBXPollAdmin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $ver The current version of this plugin.
     */
    private $version;


    private $settings;


    /**
     * CBXPoll HTML Session Object.
     *
     * This holds cart items, purchase sessions, and anything else stored in the session.
     *
     * @var object|CBXPOLL_Session
     * @since 1.5
     */
    //public $session;

    /**
     * Initialize the class and set its properties.
     *
     * @param  string  $plugin_name  The name of this plugin.
     * @param  string  $ver  The version of this plugin.
     *
     * @since    1.0.0
     *
     */
    public function __construct() {
        $this->plugin_name = CBXPOLL_PLUGIN_NAME;
        $this->version     = CBXPOLL_PLUGIN_VERSION;

        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $this->version = current_time( 'timestamp' ); //for development time only
        }

        $this->settings = new PollSettings();
    }//end of constructor

    public function init_cbxpoll_type() {
        PollHelper::create_cbxpoll_post_type();

        // Check the option we set on activation.
        if ( get_option( 'cbxpoll_flush_rewrite_rules' ) == 'true' ) {
            flush_rewrite_rules();
            delete_option( 'cbxpoll_flush_rewrite_rules' );
        }
    }//end method init_cbxpoll_type

    /**
     * Register and enqueue admin-specific style sheet.
     *
     * @param $hook
     *
     * @return void
     */
    public function enqueue_styles( $hook ) {
        global $post_type, $post;

        //basic vars
        $page     = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $ver      = $this->version;
        $suffix   = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
        $settings = $this->settings;


        $css_url_part     = CBXPOLL_ROOT_URL . 'assets/css/';
        $js_url_part      = CBXPOLL_ROOT_URL . 'assets/js/';
        $vendors_url_part = CBXPOLL_ROOT_URL . 'assets/vendors/';

        $css_path_part     = CBXPOLL_ROOT_PATH . 'assets/css/';
        $js_path_part      = CBXPOLL_ROOT_PATH . 'assets/js/';
        $vendors_path_part = CBXPOLL_ROOT_PATH . 'assets/vendors/';

        //poll listing
        if ( in_array( $hook, [ 'edit.php' ] ) && ( 'cbxpoll' == $post_type ) && $page == '' ) {
            wp_register_style( 'awesome-notifications', $vendors_url_part . 'awesome-notifications/style.css', [], $ver );


            wp_register_style( 'cbxpoll-admin', $css_url_part . 'cbxpoll-admin.css', [], $ver );


            $listing_css_deps = apply_filters( 'cbxpoll_listing_css_deps', [ 'awesome-notifications', 'cbxpoll-admin' ] );

            wp_register_style( 'cbxpoll-listing', $css_url_part . 'cbxpoll-listing.css', $listing_css_deps, $ver );

            //enqueue dependency libs
            foreach ( $listing_css_deps as $css_dep ) {
                wp_enqueue_style( $css_dep );
            }

            wp_enqueue_style( 'cbxpoll-listing' );
            do_action( 'cbxpolladmin_custom_style', 'listing' );


        }//end poll listing

        //poll edit
        if ( in_array( $hook, [ 'post.php', 'post-new.php' ] ) && 'cbxpoll' == $post_type && $page == '' ) {
            wp_register_style( 'awesome-notifications', $vendors_url_part . 'awesome-notifications/style.css', [], $ver );
            wp_register_style( 'select2', $vendors_url_part . 'select2/select2.min.css', [], $ver );
            wp_register_style( 'pickr', $vendors_url_part . 'pickr/classic.min.css', [], $ver );
            wp_register_style( 'flatpickr', $vendors_url_part . 'flatpickr/flatpickr.min.css', [], $ver );
            wp_register_style( 'jquery-ui', $vendors_url_part . 'ui-lightness/jquery-ui.min.css', [], $ver );

            wp_register_style( 'cbxpoll-admin', $css_url_part . 'cbxpoll-admin.css', [], $ver );


            $edit_css_deps = apply_filters( 'cbxpoll_edit_css_deps',
                    [ 'awesome-notifications', 'select2', 'pickr', 'flatpickr', 'flatpickr', 'jquery-ui', 'cbxpoll-admin' ] );

            wp_register_style( 'cbxpoll-edit', $css_url_part . 'cbxpoll-edit.css', $edit_css_deps, $ver );

            //enqueue dependency libs
            foreach ( $edit_css_deps as $css_dep ) {
                wp_enqueue_style( $css_dep );
            }

            wp_enqueue_style( 'cbxpoll-edit' );
            do_action( 'cbxpolladmin_custom_style', 'edit' );
        }//end poll edit


        if ( $page == 'cbxpoll-settings' ) {
            wp_register_style( 'awesome-notifications', $vendors_url_part . 'awesome-notifications/style.css', [], $ver );
            wp_register_style( 'pickr', $vendors_url_part . 'pickr/classic.min.css', [], $ver );
            wp_register_style( 'select2', $vendors_url_part . 'select2/select2.min.css', [], $ver );

            wp_register_style( 'cbxpoll-admin', $css_url_part . 'cbxpoll-admin.css', [], $ver );

            //poll setting
            $setting_css_deps = apply_filters( 'cbxpoll_setting_admin_css_deps',
                    [ 'select2', 'awesome-notifications', 'pickr', 'cbxpoll-admin' ] );

            wp_register_style( 'cbxpoll-setting', $css_url_part . 'cbxpoll-setting.css', $setting_css_deps, $ver );

            //enqueue settings css dependency css libs
            foreach ( $setting_css_deps as $css_dep ) {
                wp_enqueue_style( $css_dep );
            }

            wp_enqueue_style( 'cbxpoll-setting' );
        }//end css for page 'cbxpoll-settings'

        if ( $page == 'cbxpoll-support' ) {
            wp_register_style( 'cbxpoll-admin', $css_url_part . 'cbxpoll-admin.css', [], $ver );
            wp_enqueue_style( 'cbxpoll-admin' );//common admin styles
        }

        if ( $page == 'cbxpoll-emails' ) {
            wp_register_style( 'cbxpoll-email-manager', $css_url_part . 'cbxpoll-email-manager.css', [], $ver );
            wp_enqueue_style( 'cbxpoll-email-manager' );
        }
    }//end of method enqueue_styles


    /**
     * Register and enqueue admin-specific JavaScript.
     *
     * @param $hook
     *
     * @return void
     */
    public function enqueue_scripts( $hook ) {
        global $post_type, $post;

        $ver = $this->version;

        $page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $view = isset( $_GET['view'] ) ? sanitize_text_field( wp_unslash( $_GET['view'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

        $css_url_part     = CBXPOLL_ROOT_URL . 'assets/css/';
        $js_url_part      = CBXPOLL_ROOT_URL . 'assets/js/';
        $vendors_url_part = CBXPOLL_ROOT_URL . 'assets/vendors/';

        $css_path_part     = CBXPOLL_ROOT_PATH . 'assets/css/';
        $js_path_part      = CBXPOLL_ROOT_PATH . 'assets/js/';
        $vendors_path_part = CBXPOLL_ROOT_PATH . 'assets/vendors/';


        $in_footer = [
                'in_footer' => true,
        ];

        $in_head = [
                'in_footer' => false,
        ];

        wp_register_script( 'cbxpoll-jseventmanager', $js_url_part . 'cbxpoll-jseventmanager.js', [], $ver, $in_footer );

        $common_js_vars = PollHelper::cbxpoll_common_js_vars();

        //poll listing
        if ( in_array( $hook, [ 'edit.php' ] ) && 'cbxpoll' == $post_type && $page == '' ) {
            wp_enqueue_script( 'jquery' );

            wp_register_script( 'awesome-notifications', $vendors_url_part . 'awesome-notifications/script.js', [], $ver,
                    $in_footer );
            //wp_register_script( 'flatpickr', $vendors_url_part . 'flatpickr/flatpickr.min.js', [], $ver, $in_footer );
            //wp_register_script( 'pickr', $vendors_url_part . 'pickr/pickr.min.js', [], $ver, $in_footer );

            $listing_js_deps = apply_filters( 'cbxpoll_listing_js_deps',
                    [ 'cbxpoll-jseventmanager', 'awesome-notifications', 'jquery', 'jquery-ui-core', 'jquery-ui-sortable' ] );

            wp_register_script( 'cbxpoll-listing', $js_url_part . 'cbxpoll-listing.js', $listing_js_deps, $ver,
                    $in_footer );

            $listing_js_vars = apply_filters( 'cbxpoll_listing_js_vars', $common_js_vars );
            wp_localize_script( 'cbxpoll-listing', 'cbxpoll_listing', $listing_js_vars );


            //enqueue dependency libs
            foreach ( $listing_js_deps as $js_dep ) {
                wp_enqueue_script( $js_dep );
            }
            wp_enqueue_script( 'cbxpoll-listing' );

            do_action( 'cbxpolladmin_custom_script', 'listing' );
        }//end poll listing

        //poll edit
        if ( in_array( $hook, [ 'post.php', 'post-new.php' ] ) && 'cbxpoll' == $post_type && $page == '' ) {
            wp_enqueue_script( 'jquery' );

            wp_register_script( 'awesome-notifications', $vendors_url_part . 'awesome-notifications/script.js', [], $ver,
                    $in_footer );
            wp_register_script( 'flatpickr', $vendors_url_part . 'flatpickr/flatpickr.min.js', [], $ver, $in_footer );
            wp_register_script( 'pickr', $vendors_url_part . 'pickr/pickr.min.js', [], $ver, $in_footer );
            wp_register_script( 'select2', $vendors_url_part . 'select2/select2.min.js', [ 'jquery' ], $ver, $in_footer );

            $edit_js_deps = apply_filters( 'cbxpoll_edit_js_deps',
                    [ 'cbxpoll-jseventmanager', 'awesome-notifications', 'flatpickr', 'pickr', 'select2', 'jquery' ] );

            wp_register_script( 'cbxpoll-edit', $js_url_part . 'cbxpoll-edit.js', $edit_js_deps, $ver, $in_footer );

            $edit_js_vars = apply_filters( 'cbxpoll_edit_js_vars', $common_js_vars );
            wp_localize_script( 'cbxpoll-edit', 'cbxpoll_edit', $edit_js_vars );

            //enqueue dependency libs
            foreach ( $edit_js_deps as $js_dep ) {
                wp_enqueue_script( $js_dep );
            }
            wp_enqueue_script( 'cbxpoll-edit' );

            do_action( 'cbxpolladmin_custom_script', 'edit' );
        }//end poll edit


        /*wp_register_script( 'cbxpoll-jseventmanager', $js_url_part . 'cbxpolljsactionandfilter.js', [], $ver, true );
		wp_register_script( 'select2', $vendors_url_part . 'select2/js/select2.min.js', [ 'jquery' ], $ver, true );

		wp_register_script( 'cbxpoll-ui-time-script',
			$js_url_part . 'jquery-ui-timepicker-addon.js',
			[
				'jquery',
				'jquery-ui-datepicker'
			], $ver, true );

		wp_register_script( 'cbxpoll-plyjs', $js_url_part . 'ply.min.js', [ 'jquery' ], $ver, true );
		wp_register_script( 'cbxpoll-switcheryjs', $js_url_part . 'switchery.min.js', [ 'jquery' ], $ver, true );


		//if ((in_array($hook, array('edit.php', 'post.php', 'post-new.php')) && 'cbxpoll' == $post_type) || ($hook == 'cbxpollsetting')) {

		//admin poll listing
		wp_register_script( 'cbxpolladminlisting', $js_url_part . 'cbxpoll_admin_listing.js',
			[
				'cbxpoll-jseventmanager',
				'jquery',
				'cbxpoll-switcheryjs',
				'cbxpoll-plyjs',
				'cbxpoll-switcheryjs'
			], $ver, true );

		if ( in_array( $hook, [ 'edit.php' ] ) && 'cbxpoll' == $post_type ) {
			//adding translation and other variables from php to js for single post edit screen
			$admin_listing_arr = [
				'copy'                => esc_html__( 'Click to copy', 'cbxpoll' ),
				'copied'              => esc_html__( 'Copied to clipboard', 'cbxpoll' ),
				'remove_label'        => esc_html__( 'Remove', 'cbxpoll' ),
				'move_label'          => esc_html__( 'Move', 'cbxpoll' ),
				'move_title'          => esc_html__( 'Drag and Drop to reorder answers', 'cbxpoll' ),
				'deleteconfirm'       => esc_html__( 'Are you sure to delete this item?', 'cbxpoll' ),
				'deleteconfirmok'     => esc_html__( 'Sure', 'cbxpoll' ),
				'deleteconfirmcancel' => esc_html__( 'Oh! No', 'cbxpoll' ),
				'ajaxurl'             => admin_url( 'admin-ajax.php' ),
				'nonce'               => wp_create_nonce( 'cbxpoll' ),
				'please_select'       => esc_html__( 'Please select', 'cbxpoll' )
			];

			wp_localize_script( 'cbxpolladminlisting', 'cbxpolladminlistingObj', $admin_listing_arr );

			wp_enqueue_script( 'cbxpoll-jseventmanager' );
			wp_enqueue_script( 'jquery' );


			wp_enqueue_script( 'cbxpoll-plyjs' );
			wp_enqueue_script( 'cbxpoll-switcheryjs' );
			wp_enqueue_script( 'cbxpolladminlisting' );

			do_action( 'cbxpolladmin_custom_script' );
		}*/


        /*//admin poll single edit
		wp_register_script( 'cbxpolladminsingle', $js_url_part . 'cbxpoll-admin-single.js',
			[
				'cbxpoll-jseventmanager',
				'jquery',
				'wp-color-picker',
				'jquery-ui-core',
				'jquery-ui-datepicker',
				'jquery-ui-sortable',
				'select2',
				'cbxpoll-ui-time-script',
				'cbxpoll-plyjs',
				'cbxpoll-switcheryjs',
			], $ver, true );

		if ( in_array( $hook, [ 'post.php', 'post-new.php' ] ) && 'cbxpoll' == $post_type ) {

			if ( ! class_exists( '_WP_Editors', false ) ) {
				require( ABSPATH . WPINC . '/class-wp-editor.php' );
			}

			wp_enqueue_script( 'cbxpoll-jseventmanager' );
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_media();

			wp_enqueue_style( 'jquery-ui-core' );       //jquery ui core
			wp_enqueue_style( 'jquery-ui-datepicker' ); //jquery ui datepicker
			wp_enqueue_style( 'jquery-ui-sortable' );   //jquery ui sortable


			wp_enqueue_script( 'select2' );
			wp_enqueue_script( 'cbxpoll-ui-time-script' );

			wp_enqueue_script( 'cbxpoll-plyjs' );
			wp_enqueue_script( 'cbxpoll-switcheryjs' );


			//adding translation and other variables from php to js for single post edit screen
			$admin_single_arr = [
				'copy'                  => esc_html__( 'Click to copy', 'cbxpoll' ),
				'copied'                => esc_html__( 'Copied to clipboard', 'cbxpoll' ),
				'remove_label'          => esc_html__( 'Remove', 'cbxpoll' ),
				'move_label'            => esc_html__( 'Move', 'cbxpoll' ),
				'move_title'            => esc_html__( 'Drag and Drop to reorder answers', 'cbxpoll' ),
				'answer_label'          => esc_html__( 'Answer', 'cbxpoll' ),
				'deleteconfirm'         => esc_html__( 'Are you sure to delete this item?', 'cbxpoll' ),
				'deleteconfirmok'       => esc_html__( 'Sure', 'cbxpoll' ),
				'deleteconfirmcancel'   => esc_html__( 'Oh! No', 'cbxpoll' ),
				'ajaxurl'               => admin_url( 'admin-ajax.php' ),
				'nonce'                 => wp_create_nonce( 'cbxpoll' ),
				'teeny_editor_settings' => [
					'teeny'         => true,
					'textarea_name' => '',
					'textarea_rows' => 10,
					'media_buttons' => false,
					'editor_class'  => ''
				],
				'please_select'         => esc_html__( 'Please select', 'cbxpoll' )
			];

			wp_localize_script( 'cbxpolladminsingle', 'cbxpolladminsingleObj', $admin_single_arr );

			wp_enqueue_script( 'cbxpolladminsingle' );

			do_action( 'cbxpolladmin_single_custom_script' );
		}*/


        if ( $page == 'cbxpoll-settings' ) {
            wp_enqueue_script( 'jquery' );

            wp_enqueue_media();

            wp_register_script( 'pickr', $vendors_url_part . 'pickr/pickr.min.js', [], $ver, $in_footer );
            wp_register_script( 'awesome-notifications', $vendors_url_part . 'awesome-notifications/script.js', [], $ver,
                    $in_footer );
            wp_register_script( 'select2', $vendors_url_part . 'select2/select2.min.js', [ 'jquery' ], $ver, $in_footer );

            $setting_js_deps = apply_filters( 'cbxpoll_setting_admin_js_deps', [
                    'jquery',
                    'select2',
                    'pickr',
                    'awesome-notifications'
            ] );
            wp_register_script( 'cbxpoll-setting', $js_url_part . 'cbxpoll-setting.js', $setting_js_deps, $ver,
                    $in_footer );

            $setting_js_vars = apply_filters( 'cbxpoll_setting_js_vars', $common_js_vars );

            wp_localize_script( 'cbxpoll-setting', 'cbxpoll_setting', $setting_js_vars );

            wp_enqueue_script( 'select2' );
            wp_enqueue_script( 'pickr' );
            wp_enqueue_script( 'awesome-notifications' );

            wp_enqueue_script( 'cbxpoll-setting' );
        }//end settings page
    }//end method enqueue_scripts

    /**
     * on admin init initialize setting and handle cbxpoll type post delete
     */
    public function admin_init() {
        //init setting api
        $this->settings->set_sections( $this->get_setting_sections() );
        $this->settings->set_fields( $this->get_setting_fields() );

        //initialize them
        $this->settings->admin_init();


        //handle cbxpoll type post delete
        add_action( 'before_delete_post', [ $this, 'on_poll_delete_vote_delete' ], 10 );
    }//end method admin_init

    /**
     * Delete all votes for a given poll.
     *
     * @param  int  $poll_id  Poll ID.
     */
    function on_poll_delete_vote_delete( $poll_id ) {
        global $wpdb;

        $poll_vote_table = esc_sql( PollHelper::poll_table_name() );
        $poll_votes      = PollHelper::getAllVotes( 'id', 'desc', - 1, 1, $poll_id );

        if ( ! empty( $poll_votes ) ) {
            foreach ( $poll_votes as $log_info ) {
                $id = absint( $log_info['id'] );

                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $delete_status = $wpdb->query( $wpdb->prepare( "DELETE FROM {$poll_vote_table} WHERE id = %d", $id ) );

                /**
                 * Fires before a poll vote is deleted.
                 *
                 * @param  array  $log_info  Information about the vote being deleted.
                 */
                do_action( 'cbxpoll_vote_delete_before', $log_info );

                if ( $delete_status && $delete_status > 0 ) {
                    /**
                     * Fires after a poll vote has been deleted.
                     *
                     * @param  array  $log_info  Information about the deleted vote.
                     */
                    do_action( 'cbxpoll_vote_delete_after', $log_info );
                }
            }
        }

    }//end method on_poll_delete_vote_delete

    /**
     * Deletes a user's votes when the user is deleted.
     *
     * @param  int  $user_id  The user ID.
     */
    public function on_user_delete_vote_delete( $user_id ) {
        global $wpdb;

        $user_id         = absint( $user_id );
        $poll_vote_table = esc_sql( PollHelper::poll_table_name() );

        $poll_votes = PollHelper::getAllVotesByUser( $user_id, 'id', 'desc', - 1, 1 );

        if ( ! empty( $poll_votes ) ) {
            foreach ( $poll_votes as $log_info ) {
                $id      = absint( $log_info['id'] );
                $poll_id = absint( $log_info['poll_id'] );

                //$sql           = $wpdb->prepare( "DELETE FROM {$poll_vote_table} WHERE id = %d", $id );

                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $delete_status = $wpdb->query( $wpdb->prepare( "DELETE FROM {$poll_vote_table} WHERE id = %d", $id ) );

                /**
                 * Fires before a poll vote is deleted.
                 *
                 * @param  array  $log_info  Information about the vote being deleted.
                 */
                do_action( 'cbxpoll_vote_delete_before', $log_info );

                if ( $delete_status && $delete_status > 0 ) {
                    /**
                     * Fires after a poll vote has been deleted.
                     *
                     * @param  array  $log_info  Information about the deleted vote.
                     */
                    do_action( 'cbxpoll_vote_delete_after', $log_info );

                    if ( absint( $log_info['published'] ) === 1 ) {
                        // If the vote was published, decrement total votes count.
                        $poll_total = (int) get_post_meta( $poll_id, '_cbxpoll_total_votes', true );

                        $poll_total = max( 0, $poll_total - 1 );

                        update_post_meta( $poll_id, '_cbxpoll_total_votes', $poll_total );
                    }
                }
            }
        }
    }//end method on_user_delete_vote_delete


    /**
     * CBX Poll Core Global Setting Sections
     *
     * @return mixed|void
     */
    public function get_setting_sections() {
        return PollHelper::get_settings_sections();
    }//end method get_setting_sections

    /**
     * CBX Poll Setting Core Fields
     *
     * @return mixed|void
     */
    public function get_setting_fields() {
        $gust_login_forms = PollHelper::guest_login_forms();
        $roles            = PollHelper::user_roles( false, true );


        $table_html = '<div id="cbxpoll_resetinfo_wrap">' . esc_html__( 'Loading ...', 'cbxpoll' ) . '</div>';


        $poll_display_methods = PollHelper::cbxpoll_display_options();
        $poll_display_methods = PollHelper::cbxpoll_display_options_linear( $poll_display_methods );


        $fields = [
                'cbxpoll_global_settings' => apply_filters( 'cbxpoll_global_general_fields', [
                        'poll_defaults_heading' => [
                                'name'    => 'poll_defaults_heading',
                                'label'   => esc_html__( 'Poll Default Settings', 'cbxpoll' ),
                                'type'    => 'heading',
                                'default' => '',
                        ],
                        'result_chart_type'     => [
                                'name'    => 'result_chart_type',
                                'label'   => esc_html__( 'Result Chart Style', 'cbxpoll' ),
                                'desc'    => __( 'Poll result display styles, text and polar area display type are free, you can buy more display option from <a href="https://codeboxr.com/product/cbx-poll-for-wordpress/" target="_blank">here</a>',
                                        'cbxpoll' ),
                                'type'    => 'select',
                                'default' => 'text',
                                'options' => $poll_display_methods,
                        ],
                        'poll_multivote'        => [
                                'name'    => 'poll_multivote',
                                'label'   => esc_html__( 'Enable Multi Choice', 'cbxpoll' ),
                                'desc'    => esc_html__( 'Can user vote multiple option', 'cbxpoll' ),
                                'type'    => 'radio',
                                'default' => '0',
                                'options' => [
                                        '1' => esc_html__( 'Yes', 'cbxpoll' ),
                                        '0' => esc_html__( 'No', 'cbxpoll' )
                                ]
                        ],
                        'user_roles'            => [
                                'name'        => 'user_roles',
                                'label'       => esc_html__( 'Who Can Vote', 'cbxpoll' ),
                                'desc'        => esc_html__( 'which user role will have vote capability', 'cbxpoll' ),
                                'type'        => 'multiselect',
                            //'optgroup' => 0,
                                'default'     => [
                                        'administrator',
                                        'editor',
                                        'author',
                                        'contributor',
                                        'subscriber',
                                        'guest'
                                ],
                                'options'     => $roles,
                                'optgroup'    => 1,
                                'placeholder' => esc_html__( 'Select user roles', 'cbxpoll' )
                        ],

                        'content'                   => [
                                'name'    => 'content',
                                'label'   => esc_html__( 'Show Poll Description', 'cbxpoll' ),
                                'desc'    => esc_html__( 'Show description from poll post type', 'cbxpoll' ),
                                'type'    => 'radio',
                                'default' => 1,
                                'options' => [
                                        '1' => esc_html__( 'Yes', 'cbxpoll' ),
                                        '0' => esc_html__( 'No', 'cbxpoll' )
                                ]
                        ],
                        'never_expire'              => [
                                'name'    => 'never_expire',
                                'label'   => esc_html__( 'Never Expire', 'cbxpoll' ),
                                'desc'    => esc_html__( 'If set polls will never expire. You can also set individual poll end time.',
                                        'cbxpoll' ),
                                'type'    => 'radio',
                                'default' => 0,
                                'options' => [
                                        '1' => esc_html__( 'Yes', 'cbxpoll' ),
                                        '0' => esc_html__( 'No', 'cbxpoll' )
                                ]
                        ],
                        'show_result_before_expire' => [
                                'name'    => 'show_result_before_expire',
                                'label'   => esc_html__( 'Show result before expires', 'cbxpoll' ),
                                'desc'    => esc_html__( 'Select if you want poll to show result before expires. After expires the result will be shown always. Please check it if poll never expires.',
                                        'cbxpoll' ),
                                'type'    => 'radio',
                                'default' => 1, //new change 0 -> 1
                                'options' => [
                                        '1' => esc_html__( 'Yes', 'cbxpoll' ),
                                        '0' => esc_html__( 'No', 'cbxpoll' )
                                ]
                        ],
                        'cookiedays'                => [
                                'name'        => 'cookiedays',
                                'label'       => esc_html__( 'Cookie Expiration Days', 'cbxpoll' ),
                                'desc'        => esc_html__( 'For guest user cookie is placed in browser, For how many days cookie will not expire. Default is 30 days',
                                        'cbxpoll' ),
                                'type'        => 'number',
                                'default'     => '30',
                                'placeholder' => esc_html__( 'Number of days', 'cbxpoll' )

                        ],
                        'logmethod'                 => [
                                'name'    => 'logmethod',
                                'label'   => esc_html__( 'Log Method', 'cbxpoll' ),
                                'desc'    => __( 'Logging method. [<strong> Note:</strong> Please Select at least one or a guest user will vote multiple time for a poll.]',
                                        'cbxpoll' ),
                                'type'    => 'select',
                                'default' => 'both',
                                'options' => [
                                        'ip'     => esc_html__( 'IP', 'cbxpoll' ),
                                        'cookie' => esc_html__( 'Cookie', 'cbxpoll' ),
                                        'both'   => esc_html__( 'Both(IP or cookie any one)', 'cbxpoll' ),
                                ]
                        ],
                        'answer_grid_list'          => [
                                'name'    => 'answer_grid_list',
                                'label'   => esc_html__( 'Answer Display Format', 'cbxpoll' ),
                                'desc'    => esc_html__( 'Traditionally answer is shown as vericala list but sometimes grid presentation better for user experience.',
                                        'cbxpoll' ),
                                'type'    => 'radio',
                                'default' => 0,
                                'options' => [
                                        '0' => esc_html__( 'List', 'cbxpoll' ),
                                        '1' => esc_html__( 'Grid', 'cbxpoll' )
                                ]
                        ],
                        'guest_login_form'          => [
                                'name'    => 'guest_login_form',
                                'label'   => esc_html__( 'Guest User Login Form', 'cbxpoll' ),
                                'desc'    => esc_html__( 'Default guest user is shown wordpress core login form. Pro addon helps to integrate 3rd party plugins like woocommerce, restrict content pro etc.',
                                        'cbxpoll' ),
                                'type'    => 'select',
                                'default' => 'wordpress',
                                'options' => $gust_login_forms
                        ],
                        'guest_show_register'       => [
                                'name'    => 'guest_show_register',
                                'label'   => esc_html__( 'Show Register link to guest', 'cbxpoll' ),
                                'desc'    => esc_html__( 'Show register link to guest, depends on if registration is enabled in wordpress core',
                                        'cbxpoll' ),
                                'type'    => 'radio',
                                'default' => 1,
                                'options' => [
                                        1 => esc_html__( 'Yes', 'cbxpoll' ),
                                        0 => esc_html__( 'No', 'cbxpoll' ),
                                ],
                        ],
                ] ),
                'cbxpoll_slugs_settings'  => apply_filters( 'cbxpoll_global_slugs_fields', [
                        'slugs_heading'    => [
                                'name'    => 'slugs_heading',
                                'label'   => esc_html__( 'Poll Slugs and Urls', 'cbxpoll' ),
                                'type'    => 'heading',
                                'default' => '',
                        ],
                        'slugs_subheading' => [
                                'name'    => 'slugs_subheading',
                            /* translators: %s: Permalink setting url */
                                'label'   => sprintf( wp_kses( __( 'Please save <a target="_blank" href="%s">permalink</a> once after changing any slug.',
                                        'cbxpoll' ), [ 'a' => [ 'href' => [], 'target' => [] ] ] ), esc_url( admin_url( 'options-permalink.php' ) ) ),
                                'type'    => 'subheading',
                                'default' => '',
                        ],
                        'slug_single'      => [
                                'name'    => 'slug_single',
                                'label'   => esc_html__( 'Poll details url slug', 'cbxpoll' ),
                                'desc'    => esc_html__( 'Slug used for permalink for poll details url', 'cbxpoll' ),
                                'type'    => 'slug',
                                'default' => 'cbxpoll',
                        ],
                        'slug_archive'     => [
                                'name'    => 'slug_archive',
                                'label'   => esc_html__( 'Poll archive srl slug', 'cbxpoll' ),
                                'desc'    => esc_html__( 'Slug used for permalink for poll archive url', 'cbxpoll' ),
                                'type'    => 'slug',
                                'default' => 'cbxpolls',
                        ],
                ] ),
                'cbxpoll_email_tpl'       => apply_filters( 'cbxpoll_global_email_fields', [
                        'email_template_heading' => [
                                'name'    => 'email_template_heading',
                                'label'   => esc_html__( 'Poll Email Template', 'cbxpoll' ),
                                'type'    => 'heading',
                                'default' => '',
                        ],
                        'headerimage'            => [
                                'name'    => 'headerimage',
                                'label'   => esc_html__( 'Header Image', 'cbxpoll' ),
                                'type'    => 'file',
                                'default' => '',
                        ],
                        'footertext'             => [
                                'name'    => 'footertext',
                                'label'   => esc_html__( 'Footer Text', 'cbxpoll' ),
                                'desc'    => wp_kses( __( 'The text to appear at the email footer. Syntax available - <code>{site_title}</code>',
                                        'cbxpoll' ), [ 'code' => [] ] ),
                                'type'    => 'wysiwyg',
                                'default' => '{site_title}',
                        ],
                        'basecolor'              => [
                                'name'    => 'basecolor',
                                'label'   => esc_html__( 'Base Color', 'cbxpoll' ),
                                'desc'    => esc_html__( 'The base color of the email.', 'cbxpoll' ),
                                'type'    => 'color',
                                'default' => '#557da1',
                        ],
                        'backgroundcolor'        => [
                                'name'    => 'backgroundcolor',
                                'label'   => esc_html__( 'Background Colour', 'cbxpoll' ),
                                'desc'    => esc_html__( 'The background color of the email.', 'cbxpoll' ),
                                'type'    => 'color',
                                'default' => '#f5f5f5',
                        ],
                        'bodybackgroundcolor'    => [
                                'name'    => 'bodybackgroundcolor',
                                'label'   => esc_html__( 'Body Background Color', 'cbxpoll' ),
                                'desc'    => esc_html__( 'The background colour of the main body of email.', 'cbxpoll' ),
                                'type'    => 'color',
                                'default' => '#fdfdfd',
                        ],
                        'bodytextcolor'          => [
                                'name'    => 'bodytextcolor',
                                'label'   => esc_html__( 'Body Text Color', 'cbxpoll' ),
                                'desc'    => esc_html__( 'The body text colour of the main body of email.', 'cbxpoll' ),
                                'type'    => 'color',
                                'default' => '#505050',
                        ],
                        'footertextcolor'        => [
                                'name'    => 'footertextcolor',
                                'label'   => esc_html__( 'Footer Text Color', 'cbxpoll' ),
                                'desc'    => esc_html__( 'The footer text colour of the footer of email.', 'cbxpoll' ),
                                'type'    => 'color',
                                'default' => '#3c3c3c',
                        ],
                ] ),
                'cbxpoll_tools'           => apply_filters( 'cbxpoll_global_tools_fields', [
                        'tools_heading'        => [
                                'name'    => 'tools_heading',
                                'label'   => esc_html__( 'Tools Settings', 'cbxpoll' ),
                                'type'    => 'heading',
                                'default' => '',
                        ],
                        'delete_global_config' => [
                                'name'    => 'delete_global_config',
                                'label'   => esc_html__( 'On Uninstall delete plugin data', 'cbxpoll' ),
                                'desc'    => '<p>' . esc_html__( 'Delete Global Config data and custom table created by this plugin on uninstall.', 'cbxpoll' ) . '</p>',
                                'type'    => 'radio',
                                'options' => [
                                        'yes' => esc_html__( 'Yes', 'cbxpoll' ),
                                        'no'  => esc_html__( 'No', 'cbxpoll' ),
                                ],
                                'default' => 'no'
                        ],
                        'reset_data'           => [
                                'name'    => 'reset_data',
                                'label'   => esc_html__( 'Reset all data', 'cbxpoll' ),
                                'desc'    => $table_html . '<p>' . esc_html__( 'Reset option values and all tables created by this plugin',
                                                'cbxpoll' ) . '<a data-busy="0" class="button secondary ml-20" id="reset_data_trigger"  href="#">' . esc_html__( 'Reset Data',
                                                'cbxpoll' ) . '</a></p>',
                                'type'    => 'html',
                                'default' => 'off'
                        ]
                ] )
        ];

        $settings_fields = []; //final setting array that will be passed to different filters

        $sections = $this->get_setting_sections();

        foreach ( $sections as $section ) {
            if ( ! isset( $fields[ $section['id'] ] ) ) {
                $fields[ $section['id'] ] = [];
            }
        }

        foreach ( $sections as $section ) {
            $settings_fields[ $section['id'] ] = apply_filters( 'cbxpoll_global_' . $section['id'] . '_fields', $fields[ $section['id'] ] );
        }

        return apply_filters( 'cbxpoll_global_fields', $settings_fields ); //final filter if need

        //return apply_filters('cbxpoll_global_fields', $fields);
    }//end method get_setting_fields

    /**
     *  add setting page menu
     */
    public function admin_menu() {
        global $submenu;
        $setting_page_hook = add_submenu_page( 'edit.php?post_type=cbxpoll', esc_html__( 'Poll Settings', 'cbxpoll' ),
                esc_html__( 'Settings', 'cbxpoll' ), 'manage_options', 'cbxpoll-settings', [
                        $this,
                        'admin_menu_settings_page'
                ] );

        if ( isset( $submenu['edit.php?post_type=cbxpoll'][5][0] ) ) {
            $submenu['edit.php?post_type=cbxpoll'][5][0] = esc_html__( 'Polls', 'cbxpoll' );
        }

        //add email menu for this plugin
        add_submenu_page( 'edit.php?post_type=cbxpoll',
                esc_html__( 'CBX Poll: Email Manager', 'cbxpoll' ),
                esc_html__( 'Emails', 'cbxpoll' ),
                'manage_options',
                'cbxpoll-emails',
                [ $this, 'admin_menu_display_emails' ], 8
        );

        $hook = add_submenu_page( 'edit.php?post_type=cbxpoll', esc_html__( 'Helps & Updates', 'cbxpoll' ),
                esc_html__( 'Helps & Updates', 'cbxpoll' ), 'manage_options', 'cbxpoll-support', [
                        $this,
                        'cbxpoll_helps_updates_display'
                ] );

    }//end method admin_menu

    /**
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */
    public function admin_menu_settings_page() {
        //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo cbxpoll_get_template_html( 'admin/settings.php', [ 'ref' => $this, 'settings' => $this->settings ] );
    }//end method admin_menu_settings_page


    /**
     * Loads emails menu template
     *
     * @since 2.0.0
     */
    public function admin_menu_display_emails() {
        $settings = $this->settings;

        $mail_helper = cbxpoll_mailer();
        $emails      = $mail_helper->emails;

        $template_data = [ 'settings' => $settings, 'emails' => $emails, 'edit' => 0 ];

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( isset( $_REQUEST['edit'] ) && $_REQUEST['edit'] != '' ) {
            $email_id              = sanitize_text_field( wp_unslash( $_REQUEST['edit'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $template_data['edit'] = 1;
            $template_data['id']   = $email_id;
        }

        //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo cbxpoll_get_template_html( 'admin/email_manager.php',
                $template_data );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }//end method admin_menu_display_emails


    /**
     * Render the help & support page for this plugin.
     *
     * @since    1.0.0
     */
    public function cbxpoll_helps_updates_display() {
        //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo cbxpoll_get_template_html( 'admin/support.php', [ 'ref' => $this, 'settings' => $this->settings ] );
    }//end method cbxpoll_helps_updates_display

    /**
     * cbxpoll type post listing extra cols
     *
     * @param $cbxpoll_columns
     *
     * @return mixed
     *
     */
    public function add_new_poll_columns( $cbxpoll_columns ) {
        $cbxpoll_columns['title']      = esc_html__( 'Poll Title', 'cbxpoll' );
        $cbxpoll_columns['pollexpire'] = esc_html__( 'Expired', 'cbxpoll' );
        $cbxpoll_columns['startdate']  = esc_html__( 'Start Date', 'cbxpoll' );
        $cbxpoll_columns['enddate']    = esc_html__( 'End Date', 'cbxpoll' );
        $cbxpoll_columns['date']       = esc_html__( 'Created', 'cbxpoll' );
        $cbxpoll_columns['pollvotes']  = esc_html__( 'Votes', 'cbxpoll' );
        $cbxpoll_columns['pollstatus'] = esc_html__( 'Status', 'cbxpoll' );

        //$cbxpoll_columns['shortcode']  = esc_html__( 'Shortcode', 'cbxpoll' );

        return $cbxpoll_columns;
    }//end method add_new_poll_columns

    /**
     * cbxpoll type post listing extra col values
     *
     * @param $column_name
     *
     */
    public function manage_poll_columns( $column_name, $post_id ) {

        global $post;

        //$post_id = $post->ID;

        $end_date     = get_post_meta( $post_id, '_cbxpoll_end_date', true );
        $start_date   = get_post_meta( $post_id, '_cbxpoll_start_date', true );
        $never_expire = absint( get_post_meta( $post_id, '_cbxpoll_never_expire', true ) );
        //$total_votes  = absint( get_post_meta( $post_id, '_cbxpoll_total_votes', true ) );
        $total_votes = cbxpoll_vote_count( $post_id );
        $status      = get_post_status( $post_id );
        $status_text = cbxpoll_poll_status_text( $status );

        switch ( $column_name ) {
            case 'pollexpire':
                // Get number of images in gallery
                if ( $never_expire == 1 ) {
                    if ( new \DateTime( $start_date ) > new \DateTime() ) {
                        echo '<span class="dashicons dashicons-calendar"></span> ' . esc_html__( 'Yet to Start',
                                        'cbxpoll' ); //
                    } else {
                        echo '<span class="dashicons dashicons-yes"></span> ' . esc_html__( 'Active', 'cbxpoll' );
                    }

                } else {
                    if ( new \DateTime( $start_date ) > new \DateTime() ) {
                        echo '<span class="dashicons dashicons-calendar"></span> ' . esc_html__( 'Yet to Start',
                                        'cbxpoll' ); //
                    } else {
                        if ( new \DateTime( $start_date ) <= new \DateTime() && new \DateTime( $end_date ) > new \DateTime() ) {
                            echo '<span class="dashicons dashicons-yes"></span> ' . esc_html__( 'Active', 'cbxpoll' );
                        } else {
                            if ( new \DateTime( $end_date ) <= new \DateTime() ) {
                                echo '<span class="dashicons dashicons-lock"></span> ' . esc_html__( 'Expired', 'cbxpoll' );
                            }
                        }
                    }
                }
                break;
            case 'startdate':
                echo esc_html( $start_date );
                break;
            case 'enddate':
                echo esc_html( $end_date );
                break;
            case 'pollvotes':
                echo apply_filters( 'cbxpoll_admin_listing_votes', $total_votes, $post_id );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                break;
            case 'pollstatus':
                echo '<span class="component_status component_status_icon component_status_poll component_status_' . esc_attr( $status ) . '">' . esc_html( $status_text ) . '</span>';
                break;
            default:
                break;
        } // end switch

    }//end method manage_poll_columns

    /**
     * cbxpoll type post liting extra col sortable
     *
     * make poll table columns sortable
     */
    function cbxpoll_column_sort( $columns ) {
        $columns['startdate']  = 'startdate';
        $columns['enddate']    = 'enddate';
        $columns['pollstatus'] = 'pollstatus';
        $columns['pollvotes']  = 'pollvotes';

        return $columns;
    }//end method cbxpoll_column_sort

    /**
     * Hook custom meta box
     */
    function metaboxes_display() {

        //add meta box in left side to show poll setting
        add_meta_box( 'pollcustom_meta_box', esc_html__( 'Poll Options', 'cbxpoll' ), [ $this, 'metabox_setting_display' ],
                'cbxpoll', 'normal', 'high' );

        //add meta box in right col to show the result
        add_meta_box( 'pollresult_meta_box', esc_html__( 'Poll Result', 'cbxpoll' ), [ $this, 'metabox_result_display' ],
                'cbxpoll', 'side', 'low' );

        //add meta box in right col to show the result
        add_meta_box( 'pollshortcode_meta_box', esc_html__( 'Shortcode', 'cbxpoll' ), [ $this, 'metabox_shortcode_display' ],
                'cbxpoll', 'side', 'low' );
    }//end method metaboxes_display

    /**
     * Meta box display: Setting
     */
    function metabox_setting_display() {

        echo '<div id="cbxpoll_meta_fields_wrapper">';
        global $post;
        $post_meta_fields = PollHelper::get_meta_fields();


        $post_id = isset( $post->ID ) ? intval( $post->ID ) : 0;

        $prefix = '_cbxpoll_';

        //$answer_counter = 0;
        $new_index = 0;

        $is_voted     = 0;
        $poll_answers = [];
        $poll_colors  = [];

        if ( $post_id > 0 ):
            //$is_voted           = intval( get_post_meta( $post_id, '_cbxpoll_is_voted', true ) );
            $is_voted = PollHelper::is_poll_voted( $post_id );

            $poll_answers       = get_post_meta( $post_id, '_cbxpoll_answer', true );
            $poll_colors        = get_post_meta( $post_id, '_cbxpoll_answer_color', true );
            $poll_answers_extra = get_post_meta( $post_id, '_cbxpoll_answer_extra', true );

            $new_index = isset( $poll_answers_extra['answercount'] ) ? intval( $poll_answers_extra['answercount'] ) : 0;


            if ( is_array( $poll_answers ) ) {
                if ( $new_index == 0 && sizeof( $poll_answers ) > 0 ) {
                    $old_index = $new_index;
                    foreach ( $poll_answers as $index => $poll_answer ) {
                        if ( $index > $old_index ) {
                            $old_index = $index;
                        } //find the greater index
                    }

                    if ( $old_index > $new_index ) {
                        $new_index = intval( $old_index ) + 1;
                    }
                }
            } else {
                $poll_answers = [];
            }


            wp_nonce_field( 'cbxpoll_meta_box', 'cbxpoll_meta_box_nonce' );

            echo '<div id="cbxpoll_answer_wrap" class="cbxpoll_answer_wrap" data-postid="' . absint( $post_id ) . '">';
            echo '<h4>' . esc_html__( 'Poll Answers', 'cbxpoll' ) . '</h4>';
            echo '<p>' . wp_kses( __( '[<strong>Note : </strong>  <span>Please select different color for each field.]</span>',
                            'cbxpoll' ), [ 'strong' => [], 'span' => [] ] ) . '</p>';


            echo '<ul id="cbx_poll_answers_items" class="cbx_poll_answers_items cbx_poll_answers_items_' . absint( $post->ID ) . '">';
            if ( sizeof( $poll_answers ) > 0 ) {
                foreach ( $poll_answers as $index => $poll_answer ) {
                    if ( isset( $poll_answer ) ) {
                        $poll_answers_extra[ $index ] = isset( $poll_answers_extra[ $index ] ) ? wp_unslash( $poll_answers_extra[ $index ] ) : [];
                        //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        echo PollHelper::cbxpoll_answer_field_template( $index, $poll_answer, esc_html( $poll_colors[ $index ] ),
                                absint( $is_voted ), $poll_answers_extra[ $index ], absint( $post_id ) ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    }
                }
            }


            //$answer_counter         = 3;
            if ( ! $is_voted && sizeof( $poll_answers ) == 0 ) {
                $default_answers_titles = [
                        esc_html__( 'Yes', 'cbxpoll' ),
                        esc_html__( 'No', 'cbxpoll' ),
                        esc_html__( 'No comments', 'cbxpoll' )
                ];

                $default_answers_colors = [
                        '#2f7022',
                        '#dd6363',
                        '#e4e4e4'
                ];

                $answers_extra = [ 'type' => 'default' ];

                foreach ( $default_answers_titles as $index => $answers_title ) {
                    $title = $default_answers_titles[ $index ];
                    $color = $default_answers_colors[ $index ];
                    //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    echo PollHelper::cbxpoll_answer_field_template( absint( $index ) + $new_index, $title, $color, absint( $is_voted ), $answers_extra, absint( $post_id ) );
                }

                $new_index = absint( $index ) + $new_index + 1;
            }
            echo '</ul>';
            ?>
            <input type="hidden" id="cbxpoll_answer_extra_answercount" value="<?php echo absint( $new_index ); ?>"
                   name="_cbxpoll_answer_extra[answercount]"/>
            <?php
            $plus_svg = cbxpoll_esc_svg( cbxpoll_load_svg( 'icon_plus' ) );
            ?>
            <div class="add-cbx-poll-answer-wrap" data-busy="0" data-postid="<?php echo absint( $post_id ); ?>">
                <a data-type="default" id="add-cbx-poll-answer-default"
                   class="button primary icon icon-inline add-cbx-poll-answer add-cbx-poll-answer-default add-cbx-poll-answer-<?php echo absint( $post_id ); ?>">
                    <i class="cbx-icon"><?php echo $plus_svg; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        ?></i>
                    <span class="button-label"><?php echo esc_html__( 'Add Answer', 'cbxpoll' ); ?></span>
                </a>
                <?php do_action( 'cbxpolladmin_add_answertype', $post_id, $new_index ); ?>
            </div>

            <br/>

            <?php
            echo '</div>';


            echo '<table class="table form-table">';

            foreach ( $post_meta_fields as $field ) {

                $meta = get_post_meta( $post_id, $field['id'], true );

                if ( $meta == '' && isset( $field['default'] ) ) {
                    $meta = $field['default'];
                }

                $label = isset( $field['label'] ) ? $field['label'] : '';

                echo '<tr>';
                echo '<th><label for="' . esc_attr( $field['id'] ) . '">' . esc_html( $label ) . '</label></th>';
                echo '<td>';


                switch ( $field['type'] ) {

                    case 'text':
                        echo '<input type="text" class="regular-text" name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '-text-' . esc_attr( $post_id ) . '" value="' . esc_attr( $meta ) . '" size="30" />
			            <span class="description">' . wp_kses_post( $field['desc'] ) . '</span>';
                        break;
                    case 'number':
                        echo '<input type="number" class="regular-text" name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '-number-' . esc_attr( $post_id ) . '" value="' . esc_attr( $meta ) . '" size="30" />
			            <span class="description">' . wp_kses_post( $field['desc'] ) . '</span>';
                        break;

                    case 'date':

                        echo '<input type="text" class="cbxpollmetadatepicker" name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '-date-' . esc_attr( $post_id ) . '" value="' . esc_attr( $meta ) . '" size="30" />
			            <span class="description">' . wp_kses_post( $field['desc'] ) . '</span>';
                        break;

                    case 'colorpicker':
                        $choose_color = esc_html__( 'Choose Color', 'cbxpoll' );
                        echo '<div class="meta-color-picker-wrapper">';
                        echo '<input type="hidden" class="cbxpoll-colorpicker setting-color-picker" name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '-date-' . esc_attr( $post_id ) . '" value="' . esc_attr( $meta ) . '" />';
                        echo '<span data-current-color="' . esc_attr( $meta ) . '"  class="button setting-color-picker-fire">' . esc_html( $choose_color ) . '</span>';
                        echo '</div>';
                        echo '<span class="description">' . wp_kses_post( $field['desc'] ) . '</span>';
                        break;

                    case 'multiselect':
                        $placeholder = esc_html__( 'Please select', 'cbxpoll' );
                        echo '<div class="selecttwo-select-wrapper" data-placeholder="' . esc_attr( $placeholder ) . '" data-allow-clear="0"><select name="' . esc_attr( $field['id'] ) . '[]" id="' . esc_attr( $field['id'] ) . '-chosen-' . esc_attr( $post_id ) . '" class="selecttwo-select" multiple="multiple">';

                        if ( isset( $field['optgroup'] ) && intval( $field['optgroup'] ) ) {
                            foreach ( $field['options'] as $optlabel => $data ) {
                                echo '<optgroup label="' . esc_attr( $optlabel ) . '">';
                                foreach ( $data as $key => $val ) {
                                    echo '<option value="' . esc_attr( $key ) . '"', is_array( $meta ) && in_array( $key,
                                            $meta ) ? ' selected="selected"' : '', ' >' . esc_attr( $val ) . '</option>';
                                }
                                echo '</optgroup>';
                            }

                        } else {
                            foreach ( $field['options'] as $key => $val ) {
                                echo '<option value="' . esc_attr( $key ) . '"', is_array( $meta ) && in_array( $key,
                                        $meta ) ? ' selected="selected"' : '', ' >' . esc_attr( $val ) . '</option>';
                            }
                        }

                        echo '</select></div><span class="description">' . wp_kses_post( $field['desc'] ) . '</span>';
                        break;

                    case 'select':
                        $placeholder = esc_html__( 'Please select', 'cbxpoll' );
                        echo '<div class="selecttwo-select-wrapper" data-placeholder="' . esc_attr( $placeholder ) . '" data-allow-clear="0"><select name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '-select-' . esc_attr( $post_id ) . '" class="cb-select select-' . esc_attr( $post_id ) . '">';

                        if ( isset( $field['optgroup'] ) && intval( $field['optgroup'] ) ) {

                            foreach ( $field['options'] as $optlabel => $data ) {
                                echo '<optgroup label="' . esc_attr( $optlabel ) . '">';
                                foreach ( $data as $index => $option ) {
                                    echo '<option ' . ( ( $meta == $index ) ? ' selected="selected"' : '' ) . ' value="' . esc_attr( $index ) . '">' . esc_attr( $option ) . '</option>';
                                }

                            }
                        } else {
                            foreach ( $field['options'] as $index => $option ) {
                                echo '<option ' . ( ( $meta == $index ) ? ' selected="selected"' : '' ) . ' value="' . esc_attr( $index ) . '">' . esc_attr( $option ) . '</option>';
                            }
                        }


                        echo '</select></div><span class="description">' . wp_kses_post( $field['desc'] ) . '</span>';
                        break;
                    case 'radio':
                        echo '<div class="radio_fields magic_radio_fields radio_fields_inline">';
                        foreach ( $field['options'] as $key => $value ) {
                            echo '<div class="magic-radio-field" title="g:i a" for="' . esc_attr( $field['id'] ) . '-radio-' . esc_attr( $post_id ) . '-' . esc_attr( $key ) . '">
										<input class="magic-radio" id="' . esc_attr( $field['id'] ) . '-radio-' . esc_attr( $post_id ) . '-' . esc_attr( $key ) . '" type="radio" name="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $key ) . '" ' . ( ( $meta == $key ) ? '  checked="checked" ' : '' ) . '  />
										<label for="' . esc_attr( $field['id'] ) . '-radio-' . esc_attr( $post_id ) . '-' . esc_attr( $key ) . '">' . esc_html( $value ) . '</label>
									</div>';
                        }
                        echo '</div>';
                        echo '<br/><span class="description">' . wp_kses_post( $field['desc'] ) . '</span>';
                        break;

                    case 'checkbox':
                        echo '<div class="checkbox_field magic_checkbox_field">';
                        echo '<input type="checkbox" name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '-checkbox-' . absint( $post_id ) . '" class="magic-checkbox cb-checkbox checkbox-' . absint( $post_id ) . '" ', $meta ? ' checked="checked"' : '', '/>
                        <label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['desc'] ) . '</label>';
                        echo '</div>';
                        break;
                    case 'checkbox_group':
                        if ( $meta == '' ) {
                            $meta = [];
                            foreach ( $field['options'] as $option ) {
                                array_push( $meta, $option['value'] );
                            }
                        }

                        foreach ( $field['options'] as $option ) {
                            echo '<input type="checkbox" value="' . esc_attr( $option['value'] ) . '" name="' . esc_attr( $field['id'] ) . '[]" id="' . esc_attr( $option['value'] ) . '-mult-chk-' . esc_attr( $post_id ) . '-field-' . esc_attr( $field['id'] ) . '" class="cb-multi-check mult-check-' . esc_attr( $post_id ) . '"', $meta && in_array( $option['value'],
                                    $meta ) ? ' checked="checked"' : '', ' />
                        <label for="' . esc_attr( $option['value'] ) . '">' . esc_attr( $option['label'] ) . '</label><br/>';
                        }

                        echo '<span class="description">' . wp_kses_post( $field['desc'] ) . '</span>';
                        break;

                }
                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';

        else:
            echo esc_html__( 'Please save the post once to enter poll answers.', 'cbxpoll' );
        endif;

        echo '</div>';

    }//end method metabox_setting_display

    /**
     * Renders metabox in right col to show result
     */
    function metabox_result_display() {
        global $post;
        $post_id     = $post->ID;
        $poll_output = PollHelper::show_single_poll_result( $post_id, 'shortcode', 'text' );

        echo $poll_output;//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }//end method metabox_result_display

    /**
     * Renders metabox in right col to show  shortcode with copy to clipboard
     */
    function metabox_shortcode_display() {
        global $post;
        $post_id = $post->ID;

        echo '<div class="cbxshortcode-wrap">';
        echo '<span data-clipboard-text=\'[cbxpoll id="' . absint( $post_id ) . '"]\' title="' . esc_html__( "Click to clipboard",
                        "cbxpoll" ) . '" id="cbxpollshortcode-' . absint( $post_id ) . '" class="cbxshortcode cbxshortcode-edit cbxshortcode-' . absint( $post_id ) . '">[cbxpoll id="' . absint( $post_id ) . '"]</span>';
        echo '<span class="cbxballon_ctp_btn cbxballon_ctp" aria-label="' . esc_html__( 'Click to copy',
                        'cbxpoll' ) . '" data-balloon-pos="up"><i></i></span>';
        echo '</div>';
    }//end method metabox_shortcode_display

    /**
     * Save cbxpoll metabox
     *
     * @param $post_id
     *
     * @return bool
     */
    function metabox_save( $post_id ) {
        // Check if our nonce is set.
        if ( ! isset( $_POST['cbxpoll_meta_box_nonce'] ) ) {
            return;
        }

        // Verify that the nonce is valid.
        //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cbxpoll_meta_box_nonce'] ) ), 'cbxpoll_meta_box' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }


        // Check the user's permissions.
        if ( isset( $_POST['post_type'] ) && 'cbxpoll' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }
        }


        global $post;
        $post   = get_post( $post_id );
        $status = $post->post_status;

        $prefix = '_cbxpoll_';

        //handle answer colors
        if ( isset( $_POST[ $prefix . 'answer_color' ] ) ) {
            $colors = isset( $_POST[ $prefix . 'answer_color' ] ) ? wp_unslash( $_POST[ $prefix . 'answer_color' ] ) : [];//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            foreach ( $colors as $index => $color ) {
                $colors[ $index ] = PollHelper::sanitize_hex_color( $color );
            }

            $unique_color = array_unique( $colors );

            if ( ( count( $unique_color ) ) == ( count( $colors ) ) ) {
                update_post_meta( $post_id, $prefix . 'answer_color', $colors );
            } else {
                $error = '<div class="error"><p>' . esc_html__( 'Error: Answer Color repeat error',
                                'cbxpoll' ) . '</p></div>';

                return false;
            }
        } else {
            delete_post_meta( $post_id, $prefix . 'answer_color' );
        }

        //handling extra fields
        if ( isset( $_POST[ $prefix . 'answer_extra' ] ) ) {
            $extra = isset( $_POST[ $prefix . 'answer_extra' ] ) ? wp_unslash( $_POST[ $prefix . 'answer_extra' ] ) : [];//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            update_post_meta( $post_id, $prefix . 'answer_extra', $extra );

        } else {
            delete_post_meta( $post_id, $prefix . 'answer_extra' );
        }

        //handle answer titles
        if ( isset( $_POST[ $prefix . 'answer' ] ) ) {
            $titles = isset( $_POST[ $prefix . 'answer' ] ) ? wp_unslash( $_POST[ $prefix . 'answer' ] ) : [];//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

            foreach ( $titles as $index => $title ) {
                $titles[ $index ] = sanitize_text_field( wp_unslash( $title ) );
            }

            update_post_meta( $post_id, $prefix . 'answer', $titles );
        } else {
            delete_post_meta( $post_id, $prefix . 'answer' );
        }

        $this->metabox_extra_save( $post_id );
    }//end method metabox_save

    /**
     * Save cbxpoll meta fields except poll color and titles
     *
     * @param $post_id
     *
     * @return bool|void
     */
    function metabox_extra_save( $post_id ) {
        //global $post_meta_fields;
        $post_meta_fields = PollHelper::get_meta_fields();

        $prefix = '_cbxpoll_';


        $cb_date_array = [];
        foreach ( $post_meta_fields as $field ) {
            $old = get_post_meta( $post_id, $field['id'], true );

            $default = $field['default'];
            //$default_type = is_array($default)? [] : '';

            $new = isset( $_POST[ $field['id'] ] ) ? wp_unslash( $_POST[ $field['id'] ] ) : $default;//phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized


            if ( ( $prefix . 'start_date' == $field['id'] && $new == '' ) || ( $prefix . 'end_date' == $field['id'] && $new == '' ) ) {

                //$cbpollerror = '<div class="notice notice-error inline"><p>'.esc_html__('Error:: Start or End date any one empty','cbxpoll').'</p></div>';


                //return false; //might stop processing here
                continue;
            } else {

                $sanitize_func = isset( $field['sanitize'] ) ? $field['sanitize'] : '';
                if ( $sanitize_func != '' ) {
                    $new = $sanitize_func( $new );
                }

                update_post_meta( $post_id, $field['id'], $new );
            }
        }
    }//end method metabox_extra_save

    /**
     * Get Text answer templte
     */
    public function get_answer_template() {
        //security check
        check_ajax_referer( 'cbxpollnonce', 'security' );

        //get the fields
        $index        = isset( $_POST['answer_counter'] ) ? intval( $_POST['answer_counter'] ) : 0;
        $answer_color = isset( $_POST['answer_color'] ) ? sanitize_text_field( wp_unslash( $_POST['answer_color'] ) ) : '';
        $is_voted     = isset( $_POST['is_voted'] ) ? absint( $_POST['is_voted'] ) : 0;
        $poll_id      = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
        $answer_type  = isset( $_POST['answer_type'] ) ? sanitize_text_field( wp_unslash( $_POST['answer_type'] ) ) : '';

        $answers_extra = [ 'type' => $answer_type ];

        /* translators: Answer Index*/
        $poll_answer = sprintf( esc_html__( 'Answer %d', 'cbxpoll' ), ( $index + 1 ) );

        $template = PollHelper::cbxpoll_answer_field_template( $index, $poll_answer, $answer_color, $is_voted,
                $answers_extra, $poll_id );

        echo json_encode( $template );
        die();
    }//end method get_answer_template

    /**
     * Add settings action link to the plugins page.
     *
     * @since    1.0.0
     */
    public function plugin_listing_setting_link( $links ) {
        return array_merge( [
                'settings' => '<a style="color: #2153cc; font-weight: bold;" href="' . esc_url( admin_url( 'edit.php?post_type=cbxpoll&page=cbxpoll-settings' ) ) . '">' . esc_html__( 'Settings',
                                'cbxpoll' ) . '</a>'
        ], $links );

    }//end method plugin_listing_setting_link

    /**
     * Filters the array of row meta for each/specific plugin in the Plugins list table.
     * Appends additional links below each/specific plugin on the plugins page.
     *
     * @access  public
     *
     * @param  array  $links_array  An array of the plugin's metadata
     * @param  string  $plugin_file_name  Path to the plugin file
     * @param  array  $plugin_data  An array of plugin data
     * @param  string  $status  Status of the plugin
     *
     * @return  array       $links_array
     * @since 1.0.0
     */
    public function custom_plugin_row_meta( $links_array, $plugin_file_name, $plugin_data, $status ) {
        if ( strpos( $plugin_file_name, CBXPOLL_BASE_NAME ) !== false ) {
            $links_array[] = '<a target="_blank" style="color:#2153cc !important; font-weight: bold;" href="https://wordpress.org/support/plugin/cbxpoll/" aria-label="' . esc_attr__( 'Free Support',
                            'cbxpoll' ) . '">' . esc_html__( 'Free Support', 'cbxpoll' ) . '</a>';
            $links_array[] = '<a target="_blank" style="color:#2153cc !important; font-weight: bold;" href="https://wordpress.org/plugins/cbxpoll/#reviews" aria-label="' . esc_attr__( 'Reviews',
                            'cbxpoll' ) . '">' . esc_html__( 'Reviews', 'cbxpoll' ) . '</a>';
            $links_array[] = '<a target="_blank" style="color:#2153cc !important; font-weight: bold;" href="https://codeboxr.com/doc/cbxpoll-doc/" aria-label="' . esc_attr__( 'Documentation',
                            'cbxpoll' ) . '">' . esc_html__( 'Documentation', 'cbxpoll' ) . '</a>';

            if ( ! defined( 'CBXPOLLPROADDON_PLUGIN_NAME' ) ) {
                $links_array[] = '<a target="_blank" style="color:#2153cc !important; font-weight: bold;" href="https://codeboxr.com/product/cbx-poll-for-wordpress/#downloadarea" aria-label="' . esc_attr__( 'Try Pro Addon',
                                'cbxpoll' ) . '">' . esc_html__( 'Try Pro Addon', 'cbxpoll' ) . '</a>';
            }

        }

        return $links_array;
    }//end method custom_plugin_row_meta


    /**
     * If we need to do something in upgrader process is completed
     *
     */
    public function plugin_upgrader_process_complete() {
        $saved_version = get_option( 'cbxpoll_version' );

        if ( $saved_version === false || version_compare( $saved_version, CBXPOLL_PLUGIN_VERSION, '<' ) ) {
            PollHelper::install_table();
            set_transient( 'cbxpoll_flush_rewrite_rules', 1 );
            set_transient( 'cbxpoll_upgraded_notice', 1 );
            update_option( 'cbxpoll_version', CBXPOLL_PLUGIN_VERSION );
        }
    }//end method plugin_upgrader_process_complete

    /**
     * Show a notice to anyone who has just installed the plugin for the first time
     * This notice shouldn't display to anyone who has just updated this plugin
     */
    public function plugin_activate_upgrade_notices() {
        // Check the transient to see if cbxpollproaddon has been force deactivated
        if ( get_transient( 'cbxpollproaddon_forcedactivated_notice' ) ) {
            echo '<div style="border-left:4px solid #d63638;" class="notice notice-error is-dismissible">';
            echo '<p>' . wp_kses( __( '<strong>CBX Poll Pro Addon</strong> has been deactivated as it\'s not compatible with core plugin <strong>CBX Poll</strong> current installed version. Please upgrade CBX Poll Pro Addon to latest version ',
                            'cbxpoll' ), [ 'strong' => [] ] ) . '</p>';
            echo '</div>';

            // Delete the transient so we don't keep displaying the activation message
            delete_transient( 'cbxpollproaddon_forcedactivated_notice' );
        }

        // Check the transient to see if we've just activated the plugin
        if ( get_transient( 'cbxpoll_activated_notice' ) ) {
            echo '<div style="border-left:4px solid #2153cc;" class="notice notice-success is-dismissible">';

            /* translators: 1: Plugin Version 2. Product details url  */
            echo '<p>' . sprintf( wp_kses( __( 'Thanks for installing/deactivating <strong>CBX Poll</strong> V%1$s - <a href="%2$s" target="_blank">Codeboxr Team</a>',
                            'cbxpoll' ), [ 'a' => [ 'href' => [], 'target' => [], 'class' => [] ] ] ), esc_attr( CBXPOLL_PLUGIN_VERSION ),
                            'https://codeboxr.com/product/cbx-poll-for-wordpress/' ) . '</p>';

            /* translators: 1: Plugin setting url 2. Plugin Documentation url  */
            echo '<p>' . sprintf( wp_kses( __( 'Explore <a href="%1$s" target="_blank">Plugin Setting</a> | <a href="%2$s" target="_blank">Documentation</a>',
                            'cbxpoll' ), [ 'a' => [ 'href' => [], 'target' => [] ] ] ), esc_url( admin_url( 'edit.php?post_type=cbxpoll&page=cbxpoll-settings' ) ),
                            'https://codeboxr.com/doc/cbxpoll-doc/' ) . '</p>';
            echo '</div>';

            // Delete the transient so we don't keep displaying the activation message
            delete_transient( 'cbxpoll_activated_notice' );

            $this->pro_addon_compatibility_campaign();
        }

        // Check the transient to see if we've just activated the plugin
        if ( get_transient( 'cbxpoll_upgraded_notice' ) ) {
            echo '<div style="border-left:4px solid #2153cc;" class="notice notice-success is-dismissible">';

            /* translators: 1: Plugin Version 2. Product details url  */
            echo '<p>' . sprintf( wp_kses( __( 'Thanks for upgrading <strong>CBX Poll</strong> V%1$s , enjoy the new features and bug fixes - <a href="%2$s" target="_blank">Codeboxr Team</a>',
                            'cbxpoll' ), [ 'a' => [ 'href' => [], 'target' => [], 'class' => [] ] ] ), esc_attr( CBXPOLL_PLUGIN_VERSION ),
                            'https://codeboxr.com/product/cbx-poll-for-wordpress/' ) . '</p>';
            /* translators: 1: Plugin setting url 2. Plugin Documentation url  */
            echo '<p>' . sprintf( wp_kses( __( 'Explore <a href="%1$s" target="_blank">Plugin Setting</a> | <a href="%2$s" target="_blank">Documentation</a>',
                            'cbxpoll' ), [ 'a' => [ 'href' => [], 'target' => [], 'class' => [] ] ] ), esc_url( admin_url( 'edit.php?post_type=cbxpoll&page=cbxpoll-settings' ) ),
                            'https://codeboxr.com/doc/cbxpoll-doc/' ) . '</p>';
            echo '</div>';

            // Delete the transient so we don't keep displaying the activation message
            delete_transient( 'cbxpoll_upgraded_notice' );

            $this->pro_addon_compatibility_campaign();
        }
    }//end method plugin_activate_upgrade_notices

    /**
     * Check plugin compatibility
     */
    public function pro_addon_compatibility_campaign() {
        // check if pro addon installed and active
        if ( ! defined( 'CBXPOLLPROADDON_PLUGIN_NAME' ) ) {
            /* translators: %s: product description url */
            echo '<div style="border-left-color: #005ae0;" class="notice notice-warning is-dismissible"><p>' . sprintf( wp_kses( __( '<a target="_blank" href="%s">CBX Poll Pro Addon</a> has extended features, settings, widgets and shortcodes. try it  - Codeboxr Team',
                            'cbxpoll' ), [ 'a' => [ 'href' => [], 'target' => [] ] ] ), 'https://codeboxr.com/product/cbx-poll-for-wordpress/' ) . '</p></div>';
        }

    }//end method pro_addon_compatibility_campaign

    /**
     * Init all gutenberg blocks
     */
    public function gutenberg_blocks() {
        if ( ! function_exists( 'register_block_type' ) ) {
            return;
        }

        $args = [
                'post_type'      => 'cbxpoll',
                'orderby'        => 'ID',
                'order'          => 'DESC',
                'post_status'    => 'publish',
                'posts_per_page' => - 1,
        ];

        $polls = get_posts( $args );

        $poll_id_options   = [];
        $poll_id_options[] = [
                'label' => esc_html__( 'Select Poll', 'cbxpoll' ),
                'value' => 0
        ];

        foreach ( $polls as $post ) :
            //PollHelper::setup_admin_postdata( $post );
            $post_id    = $post->ID;
            $post_title = get_the_title( $post_id );

            $poll_id_options[] = [
                    'label' => esc_attr( $post_title ),
                    'value' => absint( $post_id )
            ];
        endforeach;
        //PollHelper::wp_reset_admin_postdata();


        $chart_type_arr     = PollHelper::cbxpoll_display_options();
        $chart_type_options = [];
        foreach ( $chart_type_arr as $key => $method ) {
            $chart_type_options[] = [
                    'label' => esc_attr( $method['title'] ),
                    'value' => $key
            ];
        }

        $chart_type_options[] = [
                'label' => esc_html__( 'Use from poll post setting', 'cbxpoll' ),
                'value' => ''
        ];


        $description_arr = [
                '1' => esc_html__( 'Yes', 'cbxpoll' ),
                '0' => esc_html__( 'No', 'cbxpoll' ),
                '2' => esc_html__( 'Use Poll Post Setting', 'cbxpoll' ),
        ];

        $description_options = [];
        foreach ( $description_arr as $value => $label ) {
            $description_options[] = [
                    'label' => $label,
                    'value' => $value
            ];
        }

        $grid_arr = [
                0 => esc_html__( 'List', 'cbxpoll' ),
                1 => esc_html__( 'Grid', 'cbxpoll' ),
                2 => esc_html__( 'Use Poll Post Setting', 'cbxpoll' ),
        ];

        $grid_options = [];
        foreach ( $grid_arr as $value => $label ) {
            $grid_options[] = [
                    'label' => $label,
                    'value' => absint( $value )
            ];
        }

        //phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
        wp_register_script( 'cbxpoll-singlepoll-block',
                plugin_dir_url( __FILE__ ) . '../assets/js/blocks/cbxpoll-singlepoll-block.js', [
                        'wp-blocks',
                        'wp-element',
                        'wp-components',
                        'wp-editor',
                    //'jquery',
                ], filemtime( plugin_dir_path( __FILE__ ) . '../assets/js/blocks/cbxpoll-singlepoll-block.js' ) );

        wp_register_style( 'cbxpoll-block', plugin_dir_url( __FILE__ ) . '../assets/css/cbxpoll-block.css', [],
                filemtime( plugin_dir_path( __FILE__ ) . '../assets/css/cbxpoll-block.css' ) );

        $js_vars = apply_filters( 'cbxpoll_singlepoll_block_js_vars',
                [
                        'block_title'      => esc_html__( 'CBX Poll Single Block', 'cbxpoll' ),
                        'block_category'   => 'codeboxr',
                        'block_icon'       => 'universal-access-alt',
                        'general_settings' => [
                                'heading'             => esc_html__( 'CBXPoll Single Block Settings', 'cbxpoll' ),
                                'poll_id'             => esc_html__( 'Poll', 'cbxpoll' ),
                                'poll_id_options'     => $poll_id_options,
                                'chart_type'          => esc_html__( 'Chart Type', 'cbxpoll' ),
                                'chart_type_options'  => $chart_type_options,
                                'description'         => esc_html__( 'Show Poll description', 'cbxpoll' ),
                                'description_options' => $description_options,
                                'grid'                => esc_html__( 'Answer Format', 'cbxpoll' ),
                                'grid_options'        => $grid_options
                        ],
                ] );

        wp_localize_script( 'cbxpoll-singlepoll-block', 'cbxpoll_singlepoll_block', $js_vars );

        register_block_type( 'codeboxr/cbxpoll-single', [
                'editor_script'   => 'cbxpoll-singlepoll-block',
                'editor_style'    => 'cbxpoll-block',
                'attributes'      => apply_filters( 'cbxpoll_singlepoll_block_attributes', [
                        'poll_id'     => [
                                'type'    => 'integer',
                                'default' => 0,
                        ],
                        'chart_type'  => [
                                'type'    => 'string',
                                'default' => 'text'
                        ],
                        'description' => [
                                'type'    => 'integer',
                                'default' => 1
                        ],
                        'grid'        => [
                                'type'    => 'integer',
                                'default' => 0
                        ],

                ] ),
                'render_callback' => [ $this, 'cbxpoll_single_block_render' ]
        ] );

    }//end gutenberg_blocks

    /**
     * Getenberg server side render
     *
     * @param $settings
     *
     * @return string
     */
    public function cbxpoll_single_block_render( $attributes ) {
        $settings = new PollSettings();

        $attr = [];

        $id          = $attr['id'] = isset( $attributes['poll_id'] ) ? absint( $attributes['poll_id'] ) : 0;
        $chart_type  = $attr['chart_type'] = isset( $attributes['chart_type'] ) ? sanitize_text_field( wp_unslash( $attributes['chart_type'] ) ) : 'text';
        $description = $attr['description'] = isset( $attributes['description'] ) ? absint( $attributes['description'] ) : 1;
        $grid        = $attr['grid'] = isset( $attributes['grid'] ) ? absint( $attributes['grid'] ) : 0;

        //2 = means ignore shortcode params, use from poll
        if ( $description == 2 ) {
            $description = '';
        }

        //2 = means ignore shortcode params, use from poll
        if ( $grid == 2 ) {
            $grid = '';
        }


        $attr['description'] = $description;
        $attr['grid']        = $grid;

        $attr = apply_filters( 'cbxpoll_singlepoll_block_shortcode_builder_attr', $attr, $attributes );

        $attr_html = '';

        foreach ( $attr as $key => $value ) {
            $attr_html .= ' ' . $key . '="' . $value . '" ';
        }

        //return do_shortcode( '[cbxpoll ' . $attr_html . ']' );
        return '[cbxpoll ' . $attr_html . ']';
    }//end cbxpoll_single_block_render

    /**
     * Register New Gutenberg block Category if need
     *
     * @param $categories
     * @param $post
     *
     * @return mixed
     */
    public function gutenberg_block_categories( $categories, $post ) {
        $found = false;
        foreach ( $categories as $category ) {
            if ( $category['slug'] == 'codeboxr' ) {
                $found = true;
                break;
            }
        }

        if ( ! $found ) {
            return array_merge(
                    $categories,
                    [
                            [
                                    'slug'  => 'codeboxr',
                                    'title' => esc_html__( 'CBX Blocks', 'cbxpoll' ),
                            ],
                    ]
            );
        }

        return $categories;
    }//end gutenberg_block_categories


    /**
     * Enqueue style for block editor
     */
    public function enqueue_block_editor_assets() {

    }//end enqueue_block_editor_assets

    /**
     * Permalink cache clear
     *
     * @return void
     */
    public function permalink_cache_clear(): void {
        //security check
        check_ajax_referer( 'cbxpollnonce', 'security' );

        $msg            = [];
        $msg['message'] = esc_html__( 'Permalink cache cleared successfully', 'cbxpoll' );
        $msg['success'] = 1;

        if ( ! current_user_can( 'manage_options' ) ) {
            $msg['message'] = esc_html__( 'Sorry, you don\'t have enough permission', 'cbxpoll' );
            $msg['success'] = 0;
            wp_send_json( $msg );
        }

        flush_rewrite_rules();

        wp_send_json( $msg );
    } //end method permalink_cache_clear


    /**
     * Load setting html
     *
     * @return void
     */
    public function settings_reset_load() {
        //security check
        check_ajax_referer( 'cbxpollnonce', 'security' );

        $msg            = [];
        $msg['html']    = '';
        $msg['message'] = esc_html__( 'Poll reset setting html loaded successfully', 'cbxpoll' );
        $msg['success'] = 1;

        if ( ! current_user_can( 'manage_options' ) ) {
            $msg['message'] = esc_html__( 'Sorry, you don\'t have enough permission', 'cbxpoll' );
            $msg['success'] = 0;
            wp_send_json( $msg );
        }

        $msg['html'] = PollHelper::setting_reset_html_table();

        wp_send_json( $msg );
    }//end method settings_reset_load

    /**
     * Full plugin reset and redirect
     */
    public function plugin_reset() {
        //security check
        check_ajax_referer( 'cbxpollnonce', 'security' );

        $url = admin_url( 'edit.php?post_type=cbxpoll&page=cbxpoll-settings' );

        $msg            = [];
        $msg['message'] = esc_html__( 'Poll setting reset scheduled successfully', 'cbxpoll' );
        $msg['success'] = 1;
        $msg['url']     = $url;

        if ( ! current_user_can( 'manage_options' ) ) {
            $msg['message'] = esc_html__( 'Sorry, you don\'t have enough permission', 'cbxpoll' );
            $msg['success'] = 0;
            wp_send_json( $msg );
        }

        //before hook
        do_action( 'cbxpoll_plugin_reset_before' );

        $plugin_resets = isset( $_POST ) ? wp_unslash( $_POST ) : [];

        //delete options
        do_action( 'cbxpoll_plugin_options_deleted_before' );

        $reset_options = isset( $plugin_resets['reset_options'] ) ? $plugin_resets['reset_options'] : [];
        $option_values = ( is_array( $reset_options ) && sizeof( $reset_options ) > 0 ) ? array_values( $reset_options ) : array_values( PollHelper::getAllOptionNamesValues() );

        foreach ( $option_values as $key => $option ) {
            do_action( 'cbxpoll_plugin_option_delete_before', $option );
            delete_option( $option );
            do_action( 'cbxpoll_plugin_option_delete_after', $option );
        }

        do_action( 'cbxpoll_plugin_options_deleted_after' );
        do_action( 'cbxpoll_plugin_options_deleted' );
        //end delete options

        //delete tables
        $reset_tables = isset( $plugin_resets['reset_tables'] ) ? $plugin_resets['reset_tables'] : [];
        $table_names  = ( is_array( $reset_tables ) && sizeof( $reset_tables ) > 0 ) ? array_values( $reset_tables ) : array_values( PollHelper::getAllDBTablesList() );


        if ( is_array( $table_names ) && count( $table_names ) ) {
            do_action( 'cbxpoll_plugin_tables_delete_before', $table_names );

            global $wpdb;

            foreach ( $table_names as $table_name ) {
                $table_name = esc_sql( $table_name );
                //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
                $query_result = $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
            }

            do_action( 'cbxpoll_plugin_tables_deleted_after', $table_names );
            do_action( 'cbxpoll_plugin_tables_deleted' );
        }
        //end delete tables

        //reset total vote count meta for all cbxpoll type post
        delete_post_meta_by_key( '_cbxpoll_total_votes' );
        //end reset total vote count meta for all cbxpoll type post

        //after hook
        do_action( 'cbxpoll_plugin_reset_after' );


        //general hook
        do_action( 'cbxpoll_plugin_reset' );

        wp_send_json( $msg );
    }//end plugin_reset

    /**
     * Save email/notification setting
     *
     * @return void
     * @since 2.0.0
     */
    public function save_email_setting() {
        if ( isset( $_REQUEST['cbxpoll_email_edit'] ) ) {
            $email_id = isset( $_POST['email_id'] ) ? sanitize_text_field( wp_unslash( $_POST['email_id'] ) ) : '';
            $nonce    = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if ( $email_id != '' ) {
                if ( ! wp_verify_nonce( $nonce,
                        'cbxpoll_email_edit_' . $email_id ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                    die( esc_html__( 'Security check failed!', 'cbxpoll' ) );
                } else {
                    // Do stuff here.
                    $admin_url    = admin_url( 'edit.php?post_type=cbxpoll&page=cbxpoll-emails' );
                    $redirect_url = add_query_arg( [ 'edit' => $email_id ], $admin_url );

                    $mail_helper = cbxpoll_mailer();
                    $emails      = $mail_helper->emails;
                    $email       = $emails[ $email_id ];
                    $form_fields = $email->form_fields;
                    $settings    = $email->settings;

                    foreach ( $form_fields as $field_key => $form_field ) {
                        if ( isset( $_POST[ $field_key ] ) ) {
                            $type = $form_field['type'];
                            if ( $type === 'checkbox' ) {
                                $settings[ $field_key ] = sanitize_text_field( wp_unslash( $_POST[ $field_key ] ) );
                            } elseif ( $type === 'textarea' ) {
                                $settings[ $field_key ] = sanitize_textarea_field( wp_unslash( $_POST[ $field_key ] ) );
                            } else {
                                $settings[ $field_key ] = sanitize_text_field( wp_unslash( $_POST[ $field_key ] ) );
                            }
                        } else {
                            $settings[ $field_key ] = $form_field['default'];
                        }
                    }

                    $email_options = get_option( 'cbxpoll_emails', [] );

                    $email_options[ $email_id ] = $settings;
                    update_option( 'cbxpoll_emails', $email_options );

                    wp_safe_redirect( $redirect_url );
                    exit;
                }
            } else {
                die( esc_html__( 'Sorry, invalid email id', 'cbxpoll' ) );
            }
        }
    }//end method save_email_setting

    /**
     * Show notice about pro addon deactivation
     *
     * @return void
     * @since 2.0.0
     */
    public function check_pro_addon() {
        cbxpoll_check_and_deactivate_plugin( 'cbxpollproaddon/cbxpollproaddon.php', '2.0.1', 'cbxpolln_proaddon_deactivated' );
    }//end method check_pro_addon

    /**
     * Show plugin update
     *
     * @param $plugin_file
     * @param $plugin_data
     *
     * @return void
     */
    public function custom_message_after_plugin_row_proaddon( $plugin_file, $plugin_data ) {
        if ( $plugin_file !== 'cbxpollproaddon/cbxpollproaddon.php' ) {
            return;
        }

        //if pro addon active then this msg will be handled from pro addon, we skip here
        if ( defined( 'CBXPOLLPROADDON_PLUGIN_NAME' ) ) {
            return;
        }

        $pro_addon_version  = PollHelper::get_any_plugin_version( 'cbxpollproaddon/cbxpollproaddon.php' );
        $pro_latest_version = CBXPOLL_PRO_VERSION;

        if ( $pro_addon_version != '' && version_compare( $pro_addon_version, $pro_latest_version, '<' ) ) {
            // Custom message to display


            //$plugin_manual_update = 'https://codeboxr.com/manual-update-pro-addon/';


            /* translators:translators: 1. Plugin latest version 2. Plugin latest version */
            $custom_message = wp_kses( sprintf( __( '<strong>Note:</strong> CBX Poll Pro Addon is custom plugin. <strong style="color: red;">It seems this plugin\'s current version is older than %2$s . To get the latest pro addon features, this plugin needs to upgrade to %2$s or later.</strong>',
                    'cbxpoll' ), $pro_latest_version, $pro_latest_version ), [ 'strong' => [ 'style' => [] ], 'a' => [ 'href' => [], 'target' => [] ] ] );

            // Output a row with custom content
            echo '<tr class="plugin-update-tr">
            <td colspan="3" class="plugin-update colspanchange">
                <div class="notice notice-warning inline">
                    ' . wp_kses_post( $custom_message ) . '
                </div>
            </td>
          </tr>';
        }
    }//end method custom_message_after_plugin_row_proaddon
}//end class CBXPoll