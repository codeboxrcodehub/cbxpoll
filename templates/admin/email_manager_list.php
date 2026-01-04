<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Provide a dashboard view for the plugin
 * This file is used to markup the public-facing aspects of the plugin.
 * @link       https://codeboxr.com
 * @since      2.0.0
 * @package    cbxpoll
 * @subpackage cbxpoll/templates/admin
 */

?>
<div class="section_header row">
    <div class="col-12 section_header_l">
        <h2><?php esc_html_e( 'Email notifications', 'cbxpoll' ); ?></h2>
        <p><?php esc_html_e( 'Here are the list of all the email notification send from this accounting system. Please note that, few notification may sent from background without any setting based on the type of not.', 'cbxpoll' ); ?></p>
    </div>
    <!--                        <div class="col-6 section_header_r"></div>-->
</div>
<div id="email_manager_listing_wrapper">
    <h3><?php esc_html_e( 'Notification list', 'cbxpoll' ); ?></h3>
    <table class="table table-bordered table-striped table-hover" id="cbxpoll_email_items">
        <thead>
        <tr>
            <th><?php esc_html_e( 'Title', 'cbxpoll' ); ?></th>
            <th><?php esc_html_e( 'Type', 'cbxpoll' ); ?></th>
            <th><?php esc_html_e( 'Recipient(s)', 'cbxpoll' ); ?></th>
            <th><?php esc_html_e( 'Actions', 'cbxpoll' ); ?></th>
        </tr>
        </thead>
        <tbody>
		<?php
		$admin_url = admin_url( 'edit.php?post_type=cbxpoll&page=cbxpoll-emails' );

		$enabled_svg     = cbxpoll_esc_svg( cbxpoll_load_svg( 'icon_enabled', 'app' ) );
		$disabled_svg    = cbxpoll_esc_svg( cbxpoll_load_svg( 'icon_disabled', 'app' ) );

		foreach ( $emails as $email ):
			$id = $email->id;
			$title       = $email->title;
			$description = $email->description;
			$settings    = $email->settings;
			$user_email  = $email->is_user_email();

			$manual = $email->is_manual();


			if ( ! is_array( $settings ) ) {
				$settings = [];
			}

			$enabled    = isset( $settings['enabled'] ) ? $settings['enabled'] : '';
			$email_type = isset( $settings['email_type'] ) ? $settings['email_type'] : 'html';

			$status_title = ( $enabled == 'yes' ) ? esc_attr__( 'Enabled', 'cbxpoll' ) : esc_attr__( 'Disabled', 'cbxpoll' );

			$button_status_class = ( $enabled == 'yes' ) ? 'cbxpoll_email_status_enabled' : 'cbxpoll_email_status_disabled';
			if ( $manual ) {
				$button_status_class = 'cbxpoll_email_status_manual';
				$status_title        = esc_attr__( 'Manually Triggered', 'cbxpoll' );
			}

			// $enabled_icon_class = ( $enabled == 'yes' ) ? 'cbx-icon-enabled' : 'cbx-icon-disabled';
			$status_svg = ( $enabled == 'yes' ) ? $enabled_svg : $disabled_svg;

			$recipient = $email->get_recipient();

			$action_url = add_query_arg( [ 'edit' => $id ], $admin_url );
			?>
            <tr>
                <td>
                    <span aria-label="<?php echo esc_attr( $status_title ); ?>" data-balloon-pos="up" class="button cbxpoll_email_status <?php echo esc_attr( $button_status_class ); ?> outline secondary icon icon-only">
                        <i class="cbx-icon">
                            <?php echo $status_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            ?>
                        </i>
                    </span>
					<?php echo esc_html( $title ); ?>
                    <p><small><?php echo esc_html( $description ); ?></small></p>
                </td>
                <td><?php echo esc_html( $email->get_content_type() ); ?></td>
                <td><?php echo ( $user_email ) ? esc_html__( 'System User/Guest', 'cbxpoll' ) : esc_html( $recipient ); ?></td>
                <td><a class="button primary icon icon-inline small" href="<?php echo esc_url( $action_url ); ?>">
                        <i class="cbx-icon cbx-icon-edit-white"></i>
                        <span class="button-label"><?php esc_html_e( 'Edit', 'cbxpoll' ); ?></span>
                    </a>
                </td>
            </tr>
		<?php endforeach; ?>
        </tbody>
    </table>
</div>