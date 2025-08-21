<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


// If this file is called directly, abort.
use Cbx\Poll\PollSettings;


/**
 * Single poll widget functionality of the plugin.
 *
 *
 * @package    CBXPoll
 * @subpackage CBXPoll/widgets
 * @author     codeboxr <info@codeboxr.com>
 */
class CBXPollSingleClassicWidget extends WP_Widget {

	/**
	 *
	 * Unique identifier for your widget.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * widget file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $widget_slug = 'cbxpollsingle'; //main parent plugin's language file


	public function __construct() {

		parent::__construct(
			$this->get_widget_slug(),
			esc_html__( 'CBX Poll Display Widget', 'cbxpoll' ),
			[
				'classname'   => $this->get_widget_slug() . '-class',
				'description' => esc_html__( 'Displays single poll from CBX Poll', 'cbxpoll' )
			]
		);

	} // end constructor


	/**
	 * Return the widget slug.
	 *
	 * @return    Plugin slug variable.
	 * @since    1.0.0
	 *
	 */
	public function get_widget_slug() {
		return $this->widget_slug;
	}

	/*--------------------------------------------------*/
	/* Widget API Functions
	/*--------------------------------------------------*/

	/**
	 * Outputs the content of the widget.
	 *
	 * @param  array args  The array of form elements
	 * @param  array instance The current instance of the widget
	 */
	public function widget( $args, $instance ) {
		$setting_api = new PollSettings();

		extract( $args, EXTR_SKIP );

		$widget_string = $before_widget;

		$title = apply_filters( 'widget_title',
			empty( $instance['title'] ) ? esc_html__( 'CBX Poll Single Widget', 'cbxpoll' ) : $instance['title'], $instance,
			$this->id_base );
		// Defining the Widget Title
		if ( $title ) {
			$widget_string .= $args['before_title'] . $title . $args['after_title'];
		} else {
			$widget_string .= $args['before_title'] . $args['after_title'];
		}

		$instance = apply_filters( 'cbxpollsinglewidget_widget', $instance );

		$instance['poll_id']     = $poll_id = isset( $instance['poll_id'] ) ? absint( $instance['poll_id'] ) : 0; //in widget we will display single poll
		$instance['chart_type']  = $chart_type = isset( $instance['chart_type'] ) ? sanitize_text_field( wp_unslash($instance['chart_type']) ) : 'text';
		$instance['description'] = $description = isset( $instance['description'] ) ? absint( $instance['description'] ) : 0;
		$instance['grid']        = $grid = isset( $instance['grid'] ) ? absint( $instance['grid'] ) : 0;

		if ( $description == 2 ) {
			$description = '';
		} //2 = means ignore shortcode params, use from poll
		if ( $grid == 2 ) {
			$grid = '';
		} //2 = means ignore shortcode params, use from poll

		extract( $instance );

		if ( intval( $poll_id ) > 0 && ( false !== get_post_status( $poll_id ) ) ) {
			$widget_string .= do_shortcode( '[cbxpoll id="' . $poll_id . '" reference="widget" description="' . $description . '" chart_type="' . $chart_type . '" grid="' . $grid . '"]' );
		} else {
			$widget_string .= '<p class="cbxpoll-voted-info cbxpoll_missing">' . esc_html__( 'Poll id missing or poll doesn\'t exists',
					'cbxpoll' ) . '</p>';
		}

		$widget_string .= $after_widget;
		echo $widget_string;//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}//end widget


	/**
	 * Processes the widget's options to be saved.
	 *
	 * @param  array  $new_instance
	 * @param  array  $old_instance
	 *
	 * @return array|mixed|void
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']       = sanitize_text_field( wp_unslash($new_instance['title']) );
		$instance['poll_id']     = absint( $new_instance['poll_id'] );
		$instance['chart_type']  = sanitize_text_field( wp_unslash($new_instance['chart_type']) );
		$instance['description'] = absint( $new_instance['description'] );
		$instance['grid']        = absint( $new_instance['grid'] );

		return apply_filters( 'cbxpollsinglewidget_update', $instance, $new_instance );
	}//end widget

	/**
	 * Generates the administration form for the widget.
	 *
	 * @param  array instance The array of keys and values for the widget.
	 */
	public function form( $instance ) {
		$fields = [
			'title'       => esc_html__( 'CBXPoll Single Widget', 'cbxpoll' ),
			'poll_id'     => 0,
			//poll id,
			'chart_type'  => 'text',
			//chart type, leave empty to use from poll post setting
			'description' => 0,
			//show description, 1= show, 0 = hide, 2 = use from poll post setting, showing description in widget is  a mess
			'grid'        => 0,
			//display answer  as list = , as grid = 1, 2 = use from poll post setting
		];

		$fields = apply_filters( 'cbxpollsinglewidget_widget_form_fields', $fields );

		$instance = wp_parse_args(
			(array) $instance,
			$fields
		);

		$instance = apply_filters( 'cbxpollsinglewidget_widget_form', $instance );

		extract( $instance, EXTR_SKIP );

		// Display the admin form
		include( plugin_dir_path( __FILE__ ) . 'views/admin.php' );
	}//end form
}//end class CBXPollSingleClassicWidget