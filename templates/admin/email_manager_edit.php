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
<?php
$list_url = admin_url( 'edit.php?post_type=cbxpoll&page=cbxpoll-emails' );
?>
<div class="section_header row">
    <div class="col-12 section_header_l">
        <h2>
			<?php esc_html_e( 'Edit notifications', 'cbxpoll' ); ?>
        </h2>
    </div>
    <!--                        <div class="col-6 section_header_r"></div>-->
</div>
<div id="email_manager_listing_wrapper">
	<?php
	$settings    = $email->settings;
	$form_fields = $email->form_fields;
	?>
    <div class="cbx-sub-heading-wrap mb-20" id="dashlisting_toolbar">
        <div class="cbx-sub-heading-l">
            <h2 class="cbx-sub-heading">
				<?php
				/* translators:translators: %s: Email title */
				echo sprintf( esc_html__( 'Notification Name: %s', 'cbxpoll' ), esc_html( $email->title ) );
				?>
            </h2>
        </div>
        <div class="cbx-sub-heading-r">
            <a class="button outline secondary"
               href="<?php echo esc_url( $list_url ); ?>"><?php esc_html_e( 'Back to list', 'cbxpoll' ); ?></a>
            <a class="button primary" id="save_email"
               href="#"><?php esc_html_e( 'Save', 'cbxpoll' ); ?></a>
        </div>
    </div>
	<?php echo wpautop( wp_kses_post( $email->get_description() ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

    <form class="global_setting_group" id="cbxpoll_email_edit_form" method="post" action="" enctype="multipart/form-data">
        <input type="hidden" name="email_id" value="<?php echo esc_attr( $id ); ?>"/>
		<?php wp_nonce_field( 'cbxpoll_email_edit_' . esc_attr( $id ) ); ?>
        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th><?php esc_html_e( 'Label', 'cbxpoll' ); ?></th>
                <th><?php esc_html_e( 'Field', 'cbxpoll' ); ?></th>
            </tr>
            </thead>
            <tbody>
			<?php
			foreach ( $form_fields as $field_key => $form_field ) {
				$type        = $form_field['type'];
				$title       = $form_field['title'];
				$label       = isset( $form_field['label'] ) ? $form_field['label'] : '';
				$default     = isset( $form_field['default'] ) ? $form_field['default'] : '';
				$description = isset( $form_field['description'] ) ? wp_specialchars_decode( $form_field['description'], ENT_QUOTES ) : '';
				$desc_tip    = isset( $form_field['desc_tip'] ) ? absint( $form_field['desc_tip'] ) : 0;
				$placeholder = isset( $form_field['placeholder'] ) ? $form_field['placeholder'] : '';
				$options     = isset( $form_field['options'] ) ? $form_field['options'] : [];
				$class       = isset( $form_field['class'] ) ? $form_field['class'] : '';
				$css         = isset( $form_field['css'] ) ? $form_field['css'] : '';
				$value       = isset( $settings[ $field_key ] ) ? $settings[ $field_key ] : '';
				?>
                <tr>
                    <td><?php echo esc_html( $title ); ?></td>
                    <td>
						<?php
						if ( $type == 'checkbox' ) {
							echo '<div class="cbxpoll_email_edit_field checkbox_field form-group d-flex">';
							echo '<input name="' . esc_attr( $field_key ) . '" type="hidden" value="no" />';
							echo '<input name="' . esc_attr( $field_key ) . '" class="magic-checkbox" id="cbxpoll_email_edit_' . esc_attr( $field_key ) . '" type="checkbox" ' . checked( 'yes', $value, false ) . ' value="' . esc_attr( $default ) . '" />';
							echo '<label for="cbxpoll_email_edit_' . esc_attr( $field_key ) . '">' . esc_html( $label ) . '</label>';
							echo '<p class="description" >' . $description . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo '</div>';
						} elseif ( $type == 'text' ) {
							echo '<div class="cbxpoll_email_edit_field text_field form-group">';
							//echo '<label for="cbxpoll_email_edit_' . esc_attr( $field_key ) . '">' . esc_html( $title ) . '</label>';
							echo '<input placeholder="' . esc_attr( $placeholder ) . '" name="' . esc_attr( $field_key ) . '" class="" id="cbxpoll_email_edit_' . esc_attr( $field_key ) . '" type="text"  value="' . esc_attr( $value ) . '" />';
							echo '<p class="description" >' . $description . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo '</div>';
						} elseif ( $type == 'textarea' ) {
							echo '<div class="cbxpoll_email_edit_field textarea_field form-group">';
							//echo '<label for="cbxpoll_email_edit_' . esc_attr( $field_key ) . '">' . esc_html( $title ) . '</label>';
							echo '<textarea placeholder="' . esc_attr( $placeholder ) . '" name="' . esc_attr( $field_key ) . '" class="" id="cbxpoll_email_edit_' . esc_attr( $field_key ) . '" >' . esc_html( $value ) . '</textarea>';
							echo '<p class="description">' . $description . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo '</div>';
						} elseif ( $type == 'select' ) {
							echo '<div class="cbxpoll_email_edit_field select_field form-group">';
							//echo '<label for="cbxpoll_email_edit_' . esc_attr( $field_key ) . '">' . esc_html( $title ) . '</label>';
							echo '<select placeholder="' . esc_attr( $placeholder ) . '" name="' . esc_attr( $field_key ) . '" class="" id="cbxpoll_email_edit_' . esc_attr( $field_key ) . '">';
							foreach ( $options as $option_key => $option_value ) {
								echo '<option ' . selected( $option_key, $value, false ) . ' value="' . esc_attr( $option_key ) . '">' . esc_html( $option_value ) . '</option>';
							}
							echo '</select>';
							echo '<p class="description">' . $description . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo '</div>';
						}
						?>
                    </td>
                </tr>
			<?php } ?>
            </tbody>
        </table>
        <input type="hidden" name="cbxpoll_email_edit" value="1" />
        <p class="button_actions">
            <button class="button primary" name="cbxpoll_email_edit_submit" value="Save changes"
                    type="submit"><?php esc_html_e( 'Save Changes', 'cbxpoll' ); ?></button>
        </p>
    </form>
</div>
<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function () {
        document.getElementById("save_email").addEventListener("click", function () {
            document.getElementById("cbxpoll_email_edit_form").submit();
        });
    });
</script>