<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/* translators: %1$s: Login Link */
echo '<div class="guest_login_url_wrap"><p class="mb-0">'.wp_kses(sprintf(__('Please <a href="%1$s">login</a> to access.', 'cbxpoll'), esc_url(cbxpoll_login_url_with_redirect())), ['a' => ['href' => [], 'class' => []]]).'</p></div>';