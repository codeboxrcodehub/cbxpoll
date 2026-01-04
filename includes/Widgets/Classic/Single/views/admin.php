<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
// If this file is called directly, abort.
use Cbx\Poll\Helpers\PollHelper;

if ( ! defined( 'WPINC' ) ) {
	die;
}
?>
    <!-- This file is used to markup the administration form of the widget. -->

    <!-- Custom  Title Field -->
    <p>
        <label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php esc_html_e( 'Title', "cbxpoll" ); ?></label>

        <input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"
               name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>" type="text" value="<?php echo esc_attr($title); ?>"/>
    </p>
    <p>
        <label for="<?php echo esc_attr($this->get_field_id( 'poll_id' )); ?>"><?php esc_html_e( 'Select Poll(Poll post id)',
				"cbxpoll" ); ?></label>
        <input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'poll_id' )); ?>"
               name="<?php echo esc_attr($this->get_field_name( 'poll_id' )); ?>" type="text" value="<?php echo esc_attr($poll_id); ?>"/>
    </p>
    <p>
		<?php
		$poll_display_methods = PollHelper::cbxpoll_display_options();
		?>
        <label for="<?php echo esc_attr($this->get_field_id( 'chart_type' )); ?>"><?php esc_html_e( 'Chart Type',
				"cbxpoll" ); ?></label>
        <select class="widefat" id="<?php echo esc_attr($this->get_field_id( 'chart_type' )); ?>"
                name="<?php echo esc_attr($this->get_field_name( 'chart_type' )); ?>">
			<?php
			foreach ( $poll_display_methods as $key => $method ) {
				echo '<option value="' . esc_attr($key) . '" ' . selected( $chart_type, $key,
						false ) . '>' . esc_attr( $method['title'] ) . '</option>';
			}
			?>
            <option <?php selected( $chart_type, '', true ); ?>
                    value=""><?php esc_attr_e( 'Use from poll post setting', 'cbxpoll' ) ?></option>
        </select>
    </p>
    <p>
        <label for="<?php echo esc_attr($this->get_field_id( 'description' )); ?>"><?php esc_html_e( 'Show Poll description',
				"cbxpoll" ); ?></label>
        <select class="widefat" id="<?php echo esc_attr($this->get_field_id( 'description' )); ?>"
                name="<?php echo esc_attr($this->get_field_name( 'description' )); ?>">
            <option <?php selected( $description, '1', true ); ?>
                    value="1"><?php esc_html_e( 'Yes', 'cbxpoll' ) ?></option>
            <option <?php selected( $description, '0', true ); ?>
                    value="0"><?php esc_html_e( 'No', 'cbxpoll' ) ?></option>
            <option <?php selected( $description, '2', true ); ?>
                    value="2"><?php esc_html_e( 'Use from poll post setting', 'cbxpoll' ) ?></option>
        </select>
    </p>
    <p>
        <label for="<?php echo esc_attr($this->get_field_id( 'grid' )); ?>"><?php esc_html_e( 'Answer Format', "cbxpoll" ); ?></label>
        <select class="widefat" id="<?php echo esc_attr($this->get_field_id( 'grid' )); ?>"
                name="<?php echo esc_attr($this->get_field_name( 'grid' )); ?>">
            <option <?php selected( $grid, '0', true ); ?> value="1"><?php esc_html_e( 'List', 'cbxpoll' ) ?></option>
            <option <?php selected( $grid, '1', true ); ?> value="0"><?php esc_html_e( 'Grid', 'cbxpoll' ) ?></option>
            <option <?php selected( $grid, '2', true ); ?>
                    value="2"><?php esc_html_e( 'Use from poll post setting', 'cbxpoll' ) ?></option>
        </select>
    </p>
    <input type="hidden" id="<?php echo esc_attr($this->get_field_id( 'submit' )); ?>"
           name="<?php echo esc_attr($this->get_field_name( 'submit' )); ?>" value="1"/>
<?php
do_action( 'cbxpollsinglewidget_form_admin', $instance, $this );