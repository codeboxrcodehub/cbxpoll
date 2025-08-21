<?php
// If this file is called directly, abort.
if ( ! defined('WPINC')) {
	die;
}

// If this file is called directly, abort.
use Cbx\Poll\Helpers\PollHelper;
use enshrined\svgSanitize\Sanitizer;


/**
 * Wrapper for deprecated functions so we can apply some extra logic.
 *
 * @param  string  $function
 * @param  string  $version
 * @param  string  $replacement
 *
 * @since  1.2.11
 *
 */
function cbxpoll_deprecated_function( $function, $version, $replacement = null ) {
	if ( defined( 'DOING_AJAX' ) ) {
		do_action( 'deprecated_function_run', $function, $replacement, $version );
		$log_string = "The {$function} function is deprecated since version {$version}.";
		$log_string .= $replacement ? " Replace with {$replacement}." : '';
		error_log( $log_string );//phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	} else {
		_deprecated_function( $function, $version, $replacement );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}//end function cbxpoll_deprecated_function

if(!function_exists('cbxpoll_sanitize_wp_kses')){
	function  cbxpoll_sanitize_wp_kses($html = '') {
		return PollHelper::sanitize_wp_kses($html);
	}//end function cbxpoll_sanitize_wp_kses
}

if ( ! function_exists( 'cbxpoll_icon_path' ) ) {
	/**
	 * Resume icon path
	 *
	 * @return mixed|null
	 * @since 1.0.0
	 */
	function cbxpoll_icon_path() {
		$directory = trailingslashit( CBXPOLL_ROOT_PATH ) . 'assets/icons/';

		return apply_filters( 'cbxpoll_icon_path', $directory );
	}//end method cbxpoll_icon_path
}


if ( ! function_exists( 'cbxpoll_load_svg' ) ) {
	/**
	 * Load an SVG file from a directory.
	 *
	 * @param string $svg_name The name of the SVG file (without the .svg extension).
	 * @param string $directory The directory where the SVG files are stored.
	 *
	 * @return string|false The SVG content if found, or false on failure.
	 * @since 1.0.0
	 */
	function cbxpoll_load_svg( $svg_name = '', $folder = '' ) {
		//note: code partially generated using chatgpt
		if ( $svg_name == '' ) {
			return '';
		}

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		$credentials = request_filesystem_credentials( site_url() . '/wp-admin/', '', false, false, null );
		if ( ! WP_Filesystem( $credentials ) ) {
			return ''; // Error handling here
		}

		global $wp_filesystem;

		$directory = cbxpoll_icon_path();

		// Sanitize the file name to prevent directory traversal attacks.
		$svg_name = sanitize_file_name( $svg_name );

		if($folder != ''){
			$folder = trailingslashit($folder);
		}

		// Construct the full file path.
		$file_path = $directory. $folder . $svg_name . '.svg';
		$file_path = apply_filters('cbxpoll_svg_file_path', $file_path, $svg_name);

		// Check if the file exists.
		//if ( file_exists( $file_path ) && is_readable( $file_path ) ) {
		if ( $wp_filesystem->exists( $file_path ) && is_readable( $file_path ) ) {
			// Get the SVG file content.
			return $wp_filesystem->get_contents( $file_path );
		} else {
			// Return false if the file does not exist or is not readable.
			return '';
		}
	}//end method cbxpoll_load_svg
}

if(!function_exists('cbxpoll_is_rest_api_request')){
	/**
	 * Check if doing rest request
	 *
	 * @return bool
	 */
	function cbxpoll_is_rest_api_request() {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return true;
		}

		$REQUEST_URI = isset($_SERVER['REQUEST_URI'])? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';

		if ( empty( $REQUEST_URI ) ) {
			return false;
		}

		$rest_prefix = trailingslashit( rest_get_url_prefix() );


		return ( false !== strpos( $REQUEST_URI, $rest_prefix ) );
	}//end function cbxpoll_is_rest_api_request
}

if(!function_exists('cbxpoll_doing_it_wrong')){
	/**
	 * Wrapper for _doing_it_wrong().
	 *
	 * @since  1.0.0
	 * @param string $function Function used.
	 * @param string $message Message to log.
	 * @param string $version Version the message was added in.
	 */
	function cbxpoll_doing_it_wrong( $function, $message, $version ) {
		// @codingStandardsIgnoreStart
		$message .= ' Backtrace: ' . wp_debug_backtrace_summary();

		if ( wp_doing_ajax() || cbxpoll_is_rest_api_request() ) {
			do_action( 'doing_it_wrong_run', $function, $message, $version );
			error_log( "{$function} was called incorrectly. {$message}. This message was added in version {$version}." );
		} else {
			_doing_it_wrong( $function, $message, $version );
		}
		// @codingStandardsIgnoreEnd
	}//end function cbxpoll_doing_it_wrong
}

if ( ! function_exists( 'cbxpoll_esc_svg' ) ) {
    /**
     * SVG sanitizer
     *
     * @param string $svg_content The content of the SVG file
     *
     * @return string|false The SVG content if found, or false on failure.
     * @since 1.0.0
     */
    function cbxpoll_esc_svg( $svg_content = '' ) {
        // Create a new sanitizer instance
        $sanitizer = new Sanitizer();

        return $sanitizer->sanitize( $svg_content );
    }// end method cbxpoll_esc_svg
}

if ( ! function_exists( 'cbxpoll_mailer' ) ) {
    /**
     * Init the cbxpoll_mailer
     *
     */
    function cbxpoll_mailer() {
        if ( ! class_exists( 'CBXPollEmails' ) ) {
            include_once __DIR__ . '/../CBXPollEmails.php';
        }

        return CBXPollEmails::instance();
    }//end method cbxpoll_mailer
}

if ( ! function_exists( 'cbxpoll_wp_kses_link' ) ) {
    function cbxpoll_wp_kses_link() {
        return [ 'a' => [ 'href' => [], 'target' => [], 'class' => [], 'style' => [] ], 'strong' => [] ];
    }//end function cbxpoll_wp_kses_link
}

if ( ! function_exists( 'cbxpoll_check_and_deactivate_plugin' ) ) {
    /**
     * Check any plugin and if version less than
     *
     * @param string $plugin_slug plugin slug
     * @param string $required_version required plugin version
     * @param string $transient transient name
     *
     * @return bool|void
     * @since 2.0.0
     */
    function cbxpoll_check_and_deactivate_plugin( $plugin_slug = '', $required_version = '', $transient = '' ) {
        if ( $plugin_slug == '' ) {
            return;
        }

        if ( $required_version == '' ) {
            return;
        }

        if ( ! function_exists( 'is_plugin_active' ) ) {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        // Check if the plugin is active
        if ( is_plugin_active( $plugin_slug ) ) {
            // Get the plugin data
            $plugin_data    = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_slug );
            $plugin_version = isset( $plugin_data['Version'] ) ? $plugin_data['Version'] : '';
            if ( $plugin_version == '' || is_null( $plugin_version ) ) {
                return;
            }

            // Compare the plugin version with the required version
            if ( version_compare( $plugin_version, $required_version, '<' ) ) {
                // Deactivate the plugin
                deactivate_plugins( $plugin_slug );
                if ( $transient != '' ) {
                    set_transient( $transient, 1 );
                }
            }
        }

        //return false;
    }//end method check_and_deactivate_plugin
}

if(!function_exists('cbxpoll_vote_count')){
    /**
     * Poll vote count by poll id
     *
     * @param $poll_id
     *
     * @return int
     * @since 2.0.0
     */
    function  cbxpoll_vote_count($poll_id = 0)
    {
        $poll_id = absint($poll_id);
        return absint( get_post_meta( $poll_id, '_cbxpoll_total_votes', true ) );
    }//end function cbxpoll_vote_count
}

if(!function_exists('cbxpoll_vote_status_all')){
    /**
     * All status of poll vote
     *
     * @return mixed|null
     * @since 2.0.0
     */
    function cbxpoll_vote_status_all()
    {
        return apply_filters('cbxpoll_vote_status_all', [
            '0' => esc_html__( 'Unpublished', 'cbxpoll' ),
            '1' => esc_html__( 'Published', 'cbxpoll' ),
            '2' => esc_html__( 'Spam', 'cbxpoll' ),
            '3' => esc_html__( 'Unverified', 'cbxpoll' ),
        ]);
    }
}

if(!function_exists('cbxpoll_poll_status_text')){
    function cbxpoll_poll_status_text($status) {
        $labels = [
            'publish' => esc_html__('Published', 'cbxpoll'),
            'draft'   => esc_html__('Draft', 'cbxpoll'),
            'pending' => esc_html__('Pending Review', 'cbxpoll'),
            'private' => esc_html__('Private', 'cbxpoll'),
            'future'  => esc_html__('Scheduled', 'cbxpoll'),
            'trash'   => esc_html__('Trashed', 'cbxpoll'),
        ];
        return $labels[$status] ?? esc_html__('Unknown', 'cbxpoll');
    }
}

if ( ! function_exists( 'cbxpoll_login_url_with_redirect' ) ) {
    /**
     * Login redirect url
     *
     * @return string
     * @since 2.0.0
     */
    function cbxpoll_login_url_with_redirect() {
        //$login_url          = wp_login_url();
        //$redirect_url       = '';

        if ( is_singular() ) {
            $login_url = wp_login_url( get_permalink() );
            //$redirect_url = get_permalink();
        } else {
            global $wp;
            $login_url = wp_login_url( home_url( add_query_arg( [], $wp->request ) ) );
            //$redirect_url = home_url( add_query_arg( [], $wp->request ) );
        }

        return $login_url;
    }//end function cbxpoll_login_url_with_redirect
}