<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/* translators: %1$s: Login Link */
echo '<div class="guest_login_url_wrap"><p class="mb-0">'.esc_html__('Sorry, log in feature is not available from this area.', 'cbxpoll').'</p></div>';