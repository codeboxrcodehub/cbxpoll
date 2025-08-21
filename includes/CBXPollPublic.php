<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://codeboxr.com
 * @since      1.0.0
 *
 * @package    CBXPoll
 * @subpackage CBXPoll/public
 */

namespace Cbx\Poll;

// If this file is called directly, abort.
if ( ! defined('WPINC')) {
	die;
}

use Cbx\Poll\Helpers\PollHelper;
use Cbx\Poll\PollSettings;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    CBXPoll
 * @subpackage CBXPoll/includes
 * @author     codeboxr <info@codeboxr.com>
 */
class CBXPollPublic {

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
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version Setting api instance.
	 */
	private $settings;

	/**
	 * CBXPoll HTML Session Object.
	 *
	 * This holds cart items, purchase sessions, and anything else stored in the session.
	 *
	 * @var object|CBXPOLL_Session
	 * @since 1.5
	 */
	public $session;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param  string  $plugin_name  The name of the plugin.
	 * @param  string  $version  The version of this plugin.
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

	public function init_cookie() {
		//if session is not started, let's start it
		/*if ( ! session_id() ) {
			session_start();
		}*/

		/**
		 * Start sessions if not exists
		 *
		 * @author     Ivijan-Stefan Stipic <creativform@gmail.com>
		 */
		/*if ( version_compare( PHP_VERSION, '7.0.0', '>=' ) ) {
			if ( function_exists( 'session_status' ) && session_status() == PHP_SESSION_NONE ) {
				session_start( array(
					'cache_limiter'  => 'private_no_expire',
					'read_and_close' => false,
				) );
			}
		} else if ( version_compare( PHP_VERSION, '5.4.0', '>=' ) && version_compare( PHP_VERSION, '7.0.0', '<' ) ) {
			if ( function_exists( 'session_status' ) && session_status() == PHP_SESSION_NONE ) {
				session_cache_limiter( 'private_no_expire' );
				session_start();
			}
		} else {
			if ( session_id() == '' ) {
				if ( version_compare( PHP_VERSION, '4.0.0', '>=' ) ) {
					session_cache_limiter( 'private_no_expire' );
				}
				session_start();
			}
		}*/


		//init cookie
		PollHelper::init_cookie();
	}//end method init_cookie

	/**
	 * Inits all shortcodes
	 */
	public function init_shortcodes() {
		add_shortcode( 'cbxpoll', [ $this, 'cbxpoll_shortcode' ] );   //single poll shortcode
		add_shortcode( 'cbxpolls', [ $this, 'cbxpolls_shortcode' ] ); //all polls shortcode
	}//end method init_shortcodes

	/**
	 *  Add Text type poll result display method
	 *
	 * @param  array  $methods
	 *
	 * @return array
	 */
	public function poll_display_methods_text( $methods ) {
		$methods['text'] = [
			'title'  => esc_html__( 'Text', 'cbxpoll' ),
			'method' => [ $this, 'poll_display_methods_text_result' ],
		];

		return $methods;
	}//end method poll_display_methods_text

	/**
	 * Display poll result as text method
	 *
	 * @param  int  $poll_id
	 * @param  string  $reference
	 *
	 * @param  string  $poll_result
	 */
	public function poll_display_methods_text_result( $poll_id, $reference = 'shortcode', $poll_result = [] ) {

		$total  = intval( $poll_result['total'] );
		$colors = $poll_result['colors'];

		$answers = isset( $poll_result['answer'] ) ? $poll_result['answer'] : [];


		$output_result = '';

		if ( $total > 0 ) {
			/* translators: 1: Total Votes  */
			$output = '<p>' . sprintf( esc_html__( 'Total votes: %d', 'cbxpoll' ), number_format_i18n( $total ) ) . '</p>';

			$output .= '<ul>';

			$total_percent = 0;

			foreach ( $poll_result['weighted_index'] as $index => $vote_count ) {
				$answer_title = isset( $answers[ $index ] ) ? esc_html( $answers[ $index ] ) : esc_html__( 'Unknown Answer',
					'cbxpoll' );
				$color_style  = isset( $colors[ $index ] ) ? 'color:' . $colors[ $index ] . ';' : '';

				$percent       = ( $vote_count * 100 ) / $total;
				$total_percent += $percent;
				$output_result .= '<li style="' . $color_style . '"><strong>' . $answer_title . ': ' . $vote_count . ' (' . number_format_i18n( $percent,
						2 ) . '%)</strong></li>';

			}


			if ( $total_percent > 0 ) {
				$output_result = '';

				foreach ( $poll_result['weighted_index'] as $index => $vote_count ) {
					$answer_title = isset( $answers[ $index ] ) ? esc_html( $answers[ $index ] ) : esc_html__( 'Unknown Answer',
						'cbxpoll' );
					$color_style  = isset( $colors[ $index ] ) ? 'color:' . $colors[ $index ] . ';' : '';

					$percent    = ( $vote_count * 100 ) / $total;
					$re_percent = ( $percent * 100 ) / $total_percent;

					$output_result .= '<li style="' . $color_style . '"><strong>' . $answer_title . ': ' . $vote_count . ' (' . number_format_i18n( $re_percent,
							2 ) . '%)</strong></li>';

				}
			}

			$output .= $output_result;
			$output .= '</ul>';
		} else {
			$output = '<p>' . esc_html__( 'No approved vote yet', 'cbxpoll' ) . '</p>';
		}

		echo $output;//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}//end method poll_display_methods_text_result

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		$ver = $this->version;

		$css_url_part     = CBXPOLL_ROOT_URL . 'assets/css/';
		$js_url_part      = CBXPOLL_ROOT_URL . 'assets/js/';
		$vendors_url_part = CBXPOLL_ROOT_URL . 'assets/vendors/';

		$css_path_part     = CBXPOLL_ROOT_PATH . 'assets/css/';
		$js_path_part      = CBXPOLL_ROOT_PATH . 'assets/js/';
		$vendors_path_part = CBXPOLL_ROOT_PATH . 'assets/vendors/';

		wp_register_style( 'awesome-notifications', $vendors_url_part . 'awesome-notifications/style.css', [], $ver );
		wp_register_style( 'cbxpoll-public', $css_url_part . 'cbxpoll-public.css', [ 'awesome-notifications' ], $ver );

		wp_enqueue_style( 'awesome-notifications' );
		wp_enqueue_style( 'cbxpoll-public' );

		do_action( 'cbxpoll_custom_style' );
	}//end method enqueue_styles

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		$ver = $this->version;

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

		wp_register_script( 'cbxpoll-base64', $js_url_part . 'jquery.base64.js', [ 'jquery' ], $ver, $in_footer );
		wp_register_script( 'pristine', $vendors_url_part . 'pristine/pristine.min.js', [], $ver, $in_footer );

		wp_register_script( 'awesome-notifications', $vendors_url_part . 'awesome-notifications/script.js', [], $ver,
			$in_footer );
		wp_register_script( 'cbxpoll-publicjs', $js_url_part . 'cbxpoll-public.js',
			[ 'jquery', 'cbxpoll-base64', 'pristine', 'awesome-notifications' ], $ver, $in_footer );


		$common_translations = PollHelper::cbxpoll_common_js_vars();

		$public_js_locales = [
			//'ajaxurl'                 => admin_url('admin-ajax.php'),
			'no_answer_error'         => esc_html__( 'Please select at least one answer', 'cbxpoll' ),
			'other_reason_text_empty' => esc_html__( 'Please write note for voting reason other', 'cbxpoll' ),
			'reason_enabled'          => 0, //hard coded
		];


		wp_localize_script( 'cbxpoll-publicjs', 'cbxpollpublic',
			apply_filters( 'cbxpoll_public_js_locales', $public_js_locales + $common_translations ) );

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'cbxpoll-base64' );
		wp_enqueue_script( 'pristine' );
		wp_enqueue_script( 'awesome-notifications' );
		wp_enqueue_script( 'cbxpoll-publicjs' );

		do_action( 'cbxpoll_custom_script' );
	}//end method enqueue_scripts

	/**
	 * Shortcode callback function to display all polls or poll archive
	 *
	 * @param $atts
	 *
	 * @return string
	 */
	public static function cbxpolls_shortcode( $atts ) {
		// normalize attribute keys, lowercase
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		$setting_api = new PollSettings();


		//$global_result_chart_type = isset($setting_api['result_chart_type']) ? $setting_api['result_chart_type'] : 'text';
		$global_result_chart_type = $setting_api->get_option( 'result_chart_type', 'cbxpoll_global_settings', 'text' );
		$global_answer_grid_list  = $setting_api->get_option( 'answer_grid_list',
			'cbxpoll_global_settings',
			0 ); //0 = list 1 = grid


		$nonce          = wp_create_nonce( 'cbxpollslisting' );
		$show_load_more = true;

		$options = shortcode_atts( [
			'per_page'    => 5,
			'chart_type'  => $global_result_chart_type, //chart type, default will be always 'text' if not defined
			//'chart_type'  => '', //chart type, default will be always 'text' if not defined
			'grid'        => $global_answer_grid_list,  //show grid or list as answer
			'description' => 1,                         //show poll description,
			'user_id'     => 0                          //if we want to show polls from any user
		], $atts );

		$per_page            = (int) $options['per_page']; //just for check now its 2 after get from args
		$current_page_number = 1;

		$description      = intval( $options['description'] );
		$chart_type       = $options['chart_type'];
		$answer_grid_list = intval( $options['grid'] );
		$user_id          = intval( $options['user_id'] );

		$content = '<div class="cbxpoll-listing-wrap">';
		$content .= '<div class="cbxpoll-listing">';

		$poll_list_output = PollHelper::poll_list( $user_id,
			$per_page,
			$current_page_number,
			$chart_type,
			$answer_grid_list,
			$description,
			'shortcode' );


		if ( intval( $poll_list_output['found'] ) ) {
			$content .= $poll_list_output['content'];
		} else {
			$content        .= esc_html__( 'No poll found', 'cbxpoll' );
			$show_load_more = false;
		}

		$content .= '</div>';

		$current_page_number ++;

		if ( $show_load_more && $poll_list_output['max_num_pages'] == 1 ) {
			$show_load_more = false;
		}


		if ( $show_load_more && (int) $options['per_page'] != - 1 && $options['per_page'] != '' ) {
			$content .= '<p class="cbxpoll-listing-more"><a class="cbxpoll-listing-trig ld-ext-right" href="#" data-user_id="' . intval( $user_id ) . '" data-security="' . $nonce . '" data-page-no="' . $current_page_number . '"  data-busy ="0" data-per-page="' . $per_page . '"><span>' . esc_html__( 'View More Polls',
					'cbxpoll' ) . '</span><span class="ld ld-spin ld-ring"></span></a></p>';
		}

		$content .= '</div>';

		return $content;
	}//end method cbxpolls_shortcode

	/**
	 * Shortcode callback function to display single poll [cbxpoll id="comma separated poll id"]
	 *
	 * @param $atts
	 *
	 * @return string
	 * @throws Exception
	 */
	public function cbxpoll_shortcode( $atts ) {

		// normalize attribute keys, lowercase
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		//$setting_api = get_option('cbxpoll_global_settings');
		$setting_api = new PollSettings();


		//$global_result_chart_type = isset($setting_api['result_chart_type']) ? $setting_api['result_chart_type'] : 'text';
		$global_result_chart_type = $setting_api->get_option( 'result_chart_type', 'cbxpoll_global_settings', 'text' );
		$global_answer_grid_list  = $setting_api->get_option( 'answer_grid_list',
			'cbxpoll_global_settings',
			0 ); //0 = list 1 = grid

		$options = shortcode_atts( [
			'id'          => '',
			'reference'   => 'shortcode',
			'description' => '', //show poll description in shortcode
			'chart_type'  => $global_result_chart_type,
			'grid'        => $global_answer_grid_list,
		], $atts, 'cbxpoll' );

		$reference   = esc_attr( $options['reference'] );
		$chart_type  = esc_attr( $options['chart_type'] );
		$description = esc_attr( $options['description'] );
		$grid        = intval( $options['grid'] );


		$poll_ids = array_map( 'trim', explode( ',', $options['id'] ) );


		$output = '';
		if ( is_array( $poll_ids ) && sizeof( $poll_ids ) > 0 ) {
			foreach ( $poll_ids as $poll_id ) {
				$output .= PollHelper::cbxpoll_single_display( $poll_id,
					$reference,
					$chart_type,
					$grid,
					$description );
			}
		}


		return $output;
	}//end method cbxpoll_shortcode

	/**
	 * Poll Session data
	 *
	 * @param $content
	 *
	 * @return mixed|string
	 */
	/*public function poll_session_data( $content ) {
		global $post;

		$post_id     = intval( $post->ID );
		$setting_api = new PollSettings();

		//$global_result_chart_type = $setting_api->get_option('result_chart_type', 'cbxpoll_global_settings', 'text');
		//$global_answer_grid_list = $setting_api->get_option('answer_grid_list', 'cbxpoll_global_settings', 0); //0 = list 1 = grid

		$cbxpoll_messages = $this->session->get( 'cbxpoll_messages' );


		if ( $cbxpoll_messages !== false ) {
			//compatible with new version
			if ( is_array( $cbxpoll_messages ) ) {
				if ( isset( $cbxpoll_messages[ $post_id ] ) ) {
					$message = $cbxpoll_messages[ $post_id ];
					$content .= '<p class="cbxpoll_messages cbxpoll_messages' . $post_id . '">' . esc_html( apply_filters( 'cbxpoll_messages',
							$message,
							$post_id ) ) . '</p>';
					//unset( $_SESSION['cbxpoll_messages'][ $post_id ] );
					unset( $cbxpoll_messages[ $post_id ] );
					$this->session->set( 'cbxpoll_messages', $cbxpoll_messages );
				}
			} else {
				$message = $cbxpoll_messages;
				$content .= '<p class="cbxpoll_messages">' . esc_html( apply_filters( 'cbxpoll_messages',
						$message,
						$post_id ) ) . '</p>';
				//unset( $_SESSION['cbxpoll_messages'] );
				$this->session->set( 'cbxpoll_messages', false );
			}

		}

		return $content;
	}*/

	/**
	 * Auto integration for 'the_excerpt'
	 *
	 * @param $content
	 *
	 * @return string
	 * @throws Exception
	 */
	public function cbxpoll_the_excerpt( $content ) {
		global $post;


		//for single or archive cbxpoll where 'the_content' hook is available
		if ( isset( $post->post_type ) && ( $post->post_type == 'cbxpoll' ) ) {
			$post_id = intval( $post->ID );

			/*$post_id = intval( $post->ID );

			$setting_api = new PollSettings();



			if ( isset( $_SESSION['cbxpoll_messages'] ) ) {
				//compatible with new version
				if ( is_array( $_SESSION['cbxpoll_messages'] ) ) {
					if ( isset( $_SESSION['cbxpoll_messages'][ $post_id ] ) ) {
						$message = $_SESSION['cbxpoll_messages'][ $post_id ];
						$content .= '<p class="cbxpoll_messages cbxpoll_messages' . $post_id . '">' . esc_html( apply_filters( 'cbxpoll_messages', $message, $post_id ) ) . '</p>';
						unset( $_SESSION['cbxpoll_messages'][ $post_id ] );
					}
				} else {
					$content .= '<p class="cbxpoll_messages">' . esc_html( apply_filters( 'cbxpoll_messages', $_SESSION['cbxpoll_messages'], $post_id ) ) . '</p>';
					unset( $_SESSION['cbxpoll_messages'] );
				}

			}*/

			//$content = $this->poll_session_data( $content );

			$content .= PollHelper::cbxpoll_single_display( $post_id, 'content_hook', '', '', 0 );
		}

		return $content;
	}//end  the_excerpt_auto_integration

	/**
	 * Append poll with the poll post type description
	 *
	 * @param $content
	 *
	 * @return string
	 * @throws Exception
	 */
	function cbxpoll_the_content( $content ) {
		if ( in_array( 'get_the_excerpt', $GLOBALS['wp_current_filter'] ) ) {
			return $content;
		}

		global $post;


		//for single or archive cbxpoll where 'the_content' hook is available
		if ( isset( $post->post_type ) && ( $post->post_type == 'cbxpoll' ) ) {
			$post_id = absint( $post->ID );

			$content .= PollHelper::cbxpoll_single_display( $post_id, 'content_hook', '', '', 0 );
		}

		return $content;

	}//end method cbxpoll_the_content

	/**
	 * ajax function for vote
	 */
	function ajax_vote() {
		//security check
		check_ajax_referer( 'cbxpolluservote', 'nonce' );

		global $wpdb;
		$poll_vote_table = esc_sql( PollHelper::poll_table_name() );


		$poll_result          = [];
		$poll_result['error'] = 0;

		$setting_api = get_option( 'cbxpoll_global_settings', [] );

		$current_user = wp_get_current_user();
		$user_id      = $current_user->ID;


		$poll_id       = isset( $_POST['poll_id'] ) ? absint( $_POST['poll_id'] ) : 0;
		$user_answer_t = isset( $_POST['user_answer'] ) ? base64_decode( sanitize_text_field( wp_unslash( $_POST['user_answer'] ) ) ) : '';
		$user_answer_t = maybe_unserialize( $user_answer_t ); //why maybe
		parse_str( $user_answer_t, $user_answer );

		$user_answer_final = [];
		foreach ( $user_answer as $answer ) {
			$user_answer_final[] = $answer;
		}


		$user_answer_final = maybe_serialize( $user_answer_final );

		$chart_type = isset( $_POST['chart_type'] ) ? sanitize_text_field( wp_unslash( $_POST['chart_type'] ) ) : '';
		$reference  = isset( $_POST['reference'] ) ? sanitize_text_field( wp_unslash( $_POST['reference'] ) ) : '';

		$poll_info = get_post( $poll_id );


		if ( $user_id == 0 ) {
			$user_session   = isset( $_COOKIE[ CBXPOLL_COOKIE_NAME ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ CBXPOLL_COOKIE_NAME ] ) ) : ''; //this is string
			$user_ip        = PollHelper::get_ipaddress();
			$this_user_role = [ 'guest' ];

		} elseif ( is_user_logged_in() ) {
			$user_session = 'user-' . $user_id; //this is string
			$user_ip      = PollHelper::get_ipaddress();
			global $current_user;
			$this_user_role = $current_user->roles;
		}


		//poll information from meta

		$poll_start_date = get_post_meta( $poll_id, '_cbxpoll_start_date', true );                                                                                                                                                                                                                                                                                                                                                                                                        //poll start date
		$poll_end_date   = get_post_meta( $poll_id, '_cbxpoll_end_date', true );                                                                                                                                                                                                                                                                                                                                                                                                          //poll end date
		$poll_user_roles = get_post_meta( $poll_id, '_cbxpoll_user_roles', true );                                                                                                                                                                                                                                                                                                                                                                                                        //poll user roles
		if ( ! is_array( $poll_user_roles ) ) {
			$poll_user_roles = [];
		}

		//$poll_content                   = get_post_meta( $poll_id, '_cbxpoll_content', true ); //poll content
		$poll_never_expire              = absint( get_post_meta( $poll_id, '_cbxpoll_never_expire',true ) );                                                                                                                                                                                                                                                                                                                                                                                                                                                                     //poll never expire
		$poll_show_result_before_expire = absint( get_post_meta( $poll_id, '_cbxpoll_show_result_before_expire', true ) );                                                                                                                                                                                                                                                                                                                                                                //poll never expire
		//$poll_show_result_all           = get_post_meta($poll_id, '_cbxpoll_show_result_all', true); //show_result_all
		$poll_result_chart_type = get_post_meta( $poll_id, '_cbxpoll_result_chart_type', true );                                                                                                                                                                                                                                                                                                                                                                                          //chart type

		//$poll_is_voted          = intval( get_post_meta( $poll_id, '_cbxpoll_is_voted', true ) ); //at least a single vote
		$poll_is_voted = PollHelper::is_poll_voted( $poll_id );

		//$global_result_chart_type   = isset($setting_api['result_chart_type'])? $setting_api['result_chart_type']: 'text';
		$poll_result_chart_type = get_post_meta( $poll_id, '_cbxpoll_result_chart_type', true );
		$poll_result_chart_type = ( $chart_type != '' ) ? $chart_type : $poll_result_chart_type; //honor shortcode or widget  as user input

		//fallback as text if addon no installed
		$poll_result_chart_type = PollHelper::chart_type_fallback( $poll_result_chart_type );    //make sure that if chart type is from pro addon then it's installed

		$poll_answers = get_post_meta( $poll_id, '_cbxpoll_answer', true );

		$poll_answers = is_array( $poll_answers ) ? $poll_answers : [];
		$poll_colors  = get_post_meta( $poll_id, '_cbxpoll_answer_color', true );

		$log_method = $setting_api['logmethod'];

		$log_method = ( $log_method != '' ) ? $log_method : 'both';

		$is_poll_expired = new \DateTime( $poll_end_date ) < new \DateTime();        //check if poll expired from it's end data
		$is_poll_expired = ( $poll_never_expire == 1 ) ? false : $is_poll_expired;   //override expired status based on the meta information

		//$poll_allowed_user_group = empty($poll_user_roles) ? $setting_api['user_roles'] : $poll_user_roles;
		$poll_allowed_user_group = $poll_user_roles;

		$allowed_user_group = array_intersect( $poll_allowed_user_group, $this_user_role );

		if ( new \DateTime( $poll_start_date ) > new \DateTime() ) {
			$poll_result['error'] = 1;
			$poll_result['text']  = esc_html__( 'Sorry, poll didn\'t start yet.', 'cbxpoll' );

			wp_send_json( $poll_result );

			//echo json_encode( $poll_result );
			//die();
		}

		if ( $is_poll_expired ) {

			$poll_result['error'] = 1;
			$poll_result['text']  = esc_html__( 'Sorry, you can not vote. Poll has already expired.', 'cbxpoll' );

			wp_send_json( $poll_result );

			//echo json_encode( $poll_result );
			//die();

		}

		//check if the user has permission to vote
		if ( ( sizeof( $allowed_user_group ) ) < 1 ) {
			$poll_result['error'] = 1;
			$poll_result['text']  = esc_html__( 'Sorry, you are not allowed to vote.', 'cbxpoll' );

			wp_send_json( $poll_result );

			//echo json_encode( $poll_result );
			//die();
		}

		do_action( 'cbxpoll_form_validation', $poll_result, $poll_id );


		$cookie_value = isset( $_COOKIE[ CBXPOLL_COOKIE_NAME ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ CBXPOLL_COOKIE_NAME ] ) ) : '';

		$insertArray['poll_id']      = $poll_id;
		$insertArray['poll_title']   = $poll_info->post_title;
		$insertArray['user_name']    = ( $user_id == 0 ) ? 'guest' : $current_user->user_login;
		$insertArray['is_logged_in'] = ( $user_id == 0 ) ? 0 : 1;
		$insertArray['user_cookie']  = ( $user_id != 0 ) ? 'user-' . $user_id : $cookie_value;
		$insertArray['user_ip']      = PollHelper::get_ipaddress();
		$insertArray['user_id']      = $user_id;


		$insertArray['user_answer'] = $user_answer_final;

		$status = 1;
		$status = apply_filters( 'cbxpoll_vote_status', $status, $poll_id );;

		$insertArray['published'] = $status; //need to make this col as published 1 or 0, 2= spam

		$insertArray['comment']     = '';
		$insertArray['guest_hash']  = '';
		$insertArray['guest_name']  = '';
		$insertArray['guest_email'] = '';
		$insertArray['created']     = time();
		//$insertArray['paused'] 			= 0;

		$insertArray = apply_filters( 'cbxpoll_form_extra_process', $insertArray, $poll_id );

		$count = 0;

		//for logged in user ip or cookie or ip-cookie should not be used, those option should be used for guest user

		if ( $log_method == 'cookie' ) {

			//phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$sql = $wpdb->prepare( "SELECT COUNT(ur.id) AS count FROM $poll_vote_table ur WHERE  ur.poll_id=%d AND ur.user_id=%d AND ur.user_cookie = %s",
				$insertArray['poll_id'],
				$user_id,
				$user_session );

			$count = $wpdb->get_var( $sql );//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		} elseif ( $log_method == 'ip' ) {
			//phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$sql   = $wpdb->prepare( "SELECT COUNT(ur.id) AS count FROM $poll_vote_table ur WHERE  ur.poll_id=%d AND ur.user_id=%d AND ur.user_ip = %s",
				$insertArray['poll_id'],
				$user_id,
				$user_ip );
			$count = $wpdb->get_var( $sql ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		} else {
			if ( $log_method == 'both' ) {

				//find cookie count
				$sql = $wpdb->prepare( "SELECT COUNT(ur.id) AS count FROM $poll_vote_table ur WHERE  ur.poll_id=%d AND ur.user_id=%d AND ur.user_cookie = %s", $insertArray['poll_id'], $user_id, $user_session ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

				$vote_count_cookie = $wpdb->get_var( $sql );//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching


				//find ip count
				$sql = $wpdb->prepare( "SELECT COUNT(ur.id) AS count FROM $poll_vote_table ur WHERE  ur.poll_id=%d AND ur.user_id=%d AND ur.user_ip = %s", $insertArray['poll_id'], $user_id, $user_ip ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

				$vote_count_ip = $wpdb->get_var( $sql );//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

				if ( $vote_count_cookie >= 1 || $vote_count_ip >= 1 ) {
					$count = 1;
				}
			}
		}

		$count = apply_filters( 'cbxpoll_is_user_voted', $count );

		//check guest user if voted from same email before


		$poll_result['poll_id'] = $poll_id;

		$poll_result['chart_type'] = $poll_result_chart_type;

		//already voted
		if ( $count >= 1 ) {
			//already voted, just show the result

			$poll_result['error'] = 1;
			$poll_result['text']  = esc_html__( 'You already voted this poll !', 'cbxpoll' );

			echo json_encode( $poll_result );
			die();

		} else {
			//user didn't vote and good to go


			//add the vote
			$vote_id = PollHelper::update_poll( $insertArray ); //let the user vote


			if ( $vote_id !== false ) {
				//poll vote action
				//update the post as at least on vote is done to restrict for sorting order and edit answer labels
				do_action( 'cbxpoll_on_vote', $insertArray, $vote_id, $insertArray['published'] );

			} else {

				//at least we show some msg for such case.

				$poll_result['error'] = 1;
				$poll_result['text']  = esc_html__( 'Sorry, something wrong while voting, please refresh this page',
					'cbxpoll' );

				echo json_encode( $poll_result );
				die();
			}
		}

		//$poll_result['user_answer'] = $user_answer;
		$poll_result['user_answer'] = $user_answer_final;
		$poll_result['reference']   = $reference;
		$poll_result['colors']      = wp_json_encode( $poll_colors );
		$poll_result['answers']     = wp_json_encode( $poll_answers );

		$total_results = PollHelper::get_pollResult( $insertArray['poll_id'] );

		$total_votes = count( $total_results );

		$poll_result['total']       = $total_votes;
		$poll_result['show_result'] = ''; //todo: need to check if user allowed to view result with all condition

		$poll_answers_weight = [];

		foreach ( $total_results as $result ) {
			$user_ans = maybe_unserialize( $result['user_answer'] );
			if ( is_array( $user_ans ) ) {
				foreach ( $user_ans as $u_ans ) {
					$old_val                       = isset( $poll_answers_weight[ $u_ans ] ) ? absint( $poll_answers_weight[ $u_ans ] ) : 0;
					$poll_answers_weight[ $u_ans ] = ( $old_val + 1 );
				}
			} else {
				//backward compatible
				$user_ans                         = absint( $user_ans );
				$old_val                          = isset( $poll_answers_weight[ $user_ans ] ) ? absint( $poll_answers_weight[ $user_ans ] ) : 0;
				$poll_answers_weight[ $user_ans ] = ( $old_val + 1 );

			}


		}

		$poll_result['answers_weight'] = $poll_answers_weight;

		//ready mix :)
		$poll_weighted_labels = [];
		foreach ( $poll_answers as $index => $answer ) {
			$poll_weighted_labels[ $answer ] = isset( $poll_answers_weight[ $index ] ) ? $poll_answers_weight[ $index ] : 0;
		}
		$poll_result['weighted_label'] = $poll_weighted_labels;

		//this will help to show vote result easily
		update_post_meta( $poll_id,
			'_cbxpoll_total_votes',
			absint( $total_votes ) ); //can help for showing most voted poll //meta added

		$poll_result['text'] = esc_html__( 'Thanks for voting!', 'cbxpoll' );

		//we will only show result if permitted and for successful voting only

		//at least a successful vote happen
		//let's check if permission to see result >> as has vote capability to can see result
		//let's check if has permission to see before expire

		if ( $poll_show_result_before_expire == 1 ) {
			$poll_result['show_result'] = 1;
			$poll_result['html']        = PollHelper:: show_single_poll_result( $poll_id, $reference, $chart_type );

		}

		//echo wp_json_encode($poll_result);
		//die();

		wp_send_json( $poll_result );
	}//end method ajax_vote

	/**
	 * cbxpoll_ajax_poll_list
	 */
	public function ajax_poll_list() {
		check_ajax_referer( 'cbxpollslisting', 'security' );

		$post_per_page = isset( $_POST['per_page'] ) ? intval( $_POST['per_page'] ) : 10;
		$current_page  = isset( $_POST['page_no'] ) ? intval( $_POST['page_no'] ) : 1;
		$user_id       = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;

		$output = PollHelper::poll_list( $user_id, $post_per_page, $current_page );

		//$poll_page_data ['content'] = json_encode($content);

		echo wp_json_encode( $output );
		wp_die();
	}//end method ajax_poll_list


	/**
	 * Initialize the widgets
	 */
	public function init_widgets() {
		require_once CBXPOLL_ROOT_PATH . 'includes/Widgets/Classic/Single/CBXPollSingleClassicWidget.php';


		register_widget( 'CBXPollSingleClassicWidget' );
	}//end method init_widgets

	public function init_misc() {
		add_action( 'elementor/widgets/register', [ $this, 'init_elementor_widgets' ] );
		add_action( 'elementor/elements/categories_registered', [ $this, 'add_elementor_widget_categories' ] );
		add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'elementor_icon_loader' ], 99999 );
	}//end method init_misc

	/**
	 * Init elementor widget
	 *
	 * @throws Exception
	 */
	public function init_elementor_widgets() {
		//include the file
		require_once CBXPOLL_ROOT_PATH . 'includes/Widgets/Elementor/SinglePoll/CBXPollSingleElementorWidget.php';

		//register the widget
		\Elementor\Plugin::instance()->widgets_manager->register( new \CBXPollSingleElementorWidget() );

	}//end method widgets_registered

	/**
	 * Add new category to elementor
	 *
	 * @param $elements_manager
	 */
	public function add_elementor_widget_categories( $elements_manager ) {
		$elements_manager->add_category(
			'codeboxr',
			[
				'title' => esc_html__( 'Codeboxr Widgets', 'cbxpoll' ),
				'icon'  => 'fa fa-plug',
			]
		);
	}//end method add_elementor_widget_categories

	/**
	 * Load Elementor Custom Icon
	 */
	function elementor_icon_loader() {
		$ver = $this->version;

		wp_register_style( 'cbxpoll-elementor-icon', CBXPOLL_ROOT_URL . 'assets/css/cbxpoll-elementor-icon.css', false, $ver );
		wp_enqueue_style( 'cbxpoll-elementor-icon' );
	}//end method elementor_icon_loader


	public function admin_init_ajax_lang() {
		if ( defined( 'DOING_AJAX' ) ) {
			//work in progress
		}
	}//end method admin_init_ajax_lang
}//end class CBXPollPublic