<?php
// If this file is called directly, abort.
if ( ! defined('WPINC')) {
	die;
}

/**
 * Get the template path.
 *
 * @since 1.2.11
 *
 * @return string
 */
function cbxpoll_template_path() {
	return apply_filters( 'cbxpoll_template_path', 'cbxpoll/' );
}//end cbxpoll_template_path

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 * yourtheme/$template_path/$template_name
 * yourtheme/$template_name
 * $default_path/$template_name
 *
 * @param  string  $template_name  Template name.
 * @param  string  $template_path  Template path. (default: '').
 * @param  string  $default_path  Default path. (default: '').
 *
 * @since 1.2.11
 *
 * @return string
 */
function cbxpoll_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path ) {
		$template_path = cbxpoll_template_path();
	}

	if ( ! $default_path ) {
		$default_path = CBXPOLL_ROOT_PATH . 'templates/';
	}

	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		[
			trailingslashit( $template_path ) . $template_name,
			$template_name,
		]
	);

	// Get default template/.
	if ( ! $template ) {
		$template = $default_path . $template_name;
	}

	// Return what we found.
	return apply_filters( 'cbxpoll_locate_template', $template, $template_name, $template_path );
}//end function cbxpoll_locate_template

/**
 * Get other templates (e.g. product attributes) passing attributes and including the file.
 *
 * @param  string  $template_name  Template name.
 * @param  array  $args  Arguments. (default: array).
 * @param  string  $template_path  Template path. (default: '').
 * @param  string  $default_path  Default path. (default: '').
 *
 * @since 1.2.11
 */
function cbxpoll_get_template( $template_name, $args = [], $template_path = '', $default_path = '' ) {
	if ( ! empty( $args ) && is_array( $args ) ) {
		extract( $args ); // @codingStandardsIgnoreLine
	}

	$located = cbxpoll_locate_template( $template_name, $template_path, $default_path );

	if ( ! file_exists( $located ) ) {
		/* translators: %s template */
		_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( '%s does not exist.', 'cbxpoll' ), '<code>' . esc_html($located) . '</code>' ), '1.0.0' );

		return;
	}

	// Allow 3rd party plugin filter template file from their plugin.
	$located = apply_filters( 'cbxpoll_get_template', $located, $template_name, $args, $template_path, $default_path );

	do_action( 'cbxpoll_before_template_part', $template_name, $template_path, $located, $args );

	include $located;

	do_action( 'cbxpoll_after_template_part', $template_name, $template_path, $located, $args );
}//end function cbxpoll_get_template

/**
 * Like wc_get_template, but returns the HTML instead of outputting.
 *
 * @param  string  $template_name  Template name.
 * @param  array  $args  Arguments. (default: array).
 * @param  string  $template_path  Template path. (default: '').
 * @param  string  $default_path  Default path. (default: '').
 *
 * @return string
 * @since 1.2.11
 *
 * @see   wc_get_template
 */
function cbxpoll_get_template_html( $template_name, $args = [], $template_path = '', $default_path = '' ) {
	ob_start();
	cbxpoll_get_template( $template_name, $args, $template_path, $default_path );

	return ob_get_clean();
}//end function cbxpoll_get_template_html