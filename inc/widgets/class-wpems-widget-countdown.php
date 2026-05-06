<?php
/**
 * WP Events Manager Event Countdown widget
 *
 * @author        ThimPress, leehld
 * @package       WP-Events-Manager/Widget
 * @version       2.1.7
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit;

/**
 * Adds Foo_Widget widget.
 */
class WPEMS_Widget_Countdown extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'wpems_widget_countdown', // Base ID
			__( 'WP Event Countdown', 'wp-events-manager' ), // Name
			array( 'description' => __( 'Countdown timer for event', 'wp-events-manager' ) ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		echo wp_kses_post( $args['before_widget'] );
		if ( ! empty( $instance['title'] ) ) {
			echo wp_kses_post( $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'] );
		}

		unset( $instance['title'] );

		$html   = array();
		$html[] = '[wp_event_countdown';

		foreach ( $instance as $key => $value ) {
			if ( strpos( $key, 'wp_' ) !== 0 ) {
				continue;
			}

			$key = sanitize_key( substr( $key, 3 ) );

			if ( $key == 'events' ) {
				$value  = array_map( 'absint', array_values( (array) $value ) );
				$html[] = ' event_id="' . implode( ',', $value ) . '"';
			} else {
				$html[] = $key . '="' . esc_attr( sanitize_text_field( $value ) ) . '"';
			}
		}
		$html[] = ']';
		echo do_shortcode( implode( ' ', $html ) );
		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title      = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$selected   = ! empty( $instance['wp_events'] ) ? $instance['wp_events'] : array();
		$nav        = isset( $instance['wp_navigation'] ) ? $instance['wp_navigation'] : false;
		$pagination = isset( $instance['wp_pagination'] ) ? $instance['wp_pagination'] : false;
		$slide      = isset( $instance['wp_slide'] ) ? $instance['wp_slide'] : false;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'wp-events-manager' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'wp_slide' ) ); ?>"><?php esc_html_e( 'Carousel Slide:', 'wp-events-manager' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'wp_slide' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'wp_slide' ) ); ?>" type="checkbox" value="true"<?php checked( $slide, 'true' ); ?>>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'wp_navigation' ) ); ?>"><?php esc_html_e( 'Navigation:', 'wp-events-manager' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'wp_navigation' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'wp_navigation' ) ); ?>" type="checkbox" value="true"<?php checked( $nav, 'true' ); ?>>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'wp_pagination' ) ); ?>"><?php esc_html_e( 'Pagination:', 'wp-events-manager' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'wp_pagination' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'wp_pagination' ) ); ?>" type="checkbox" value="true"<?php checked( $pagination, 'true' ); ?>>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'wp_events' ) ); ?>"><?php esc_html_e( 'Events:', 'wp-events-manager' ); ?></label>
			<?php echo $this->events( $selected ); ?>
		</p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';

		$instance['wp_events'] = isset( $new_instance['wp_events'] ) ? array_map( 'absint', (array) $new_instance['wp_events'] ) : array();

		$instance['wp_slide'] = isset( $new_instance['wp_slide'] ) && 'true' === $new_instance['wp_slide'] ? 'true' : false;

		$instance['wp_navigation'] = isset( $new_instance['wp_navigation'] ) && 'true' === $new_instance['wp_navigation'] ? 'true' : false;

		$instance['wp_pagination'] = isset( $new_instance['wp_pagination'] ) && 'true' === $new_instance['wp_pagination'] ? 'true' : false;
		return $instance;
	}

	public function events( $selected ) {
		$status   = array(
			'upcoming'  => __( 'Upcoming', 'wp-events-manager' ),
			'happening' => __( 'Happening', 'wp-events-manager' ),
			'expired'   => __( 'Expired', 'wp-events-manager' ),
		);
		$selected = array_map( 'intval', $selected );

		$status = apply_filters( 'tp_event_widget_countdown', $status );
		$i      = 0;
		?>
		<ul class="tp_event_widget_tab">
			<?php foreach ( $status as $key => $label ) : ?>

				<li>
					<a href="#" data-tab="<?php echo esc_attr( $key ); ?>" class="button<?php echo ( $i === 0 ) ? esc_attr( ' button-primary' ) : ''; ?>">
						<?php echo esc_html( $label ); ?>
					</a>
				</li>
				<?php ++$i; ?>
			<?php endforeach; ?>
		</ul>
		<?php $i = 0; ?>
		<?php
		foreach ( $status as $stt => $label ) {
			$args = array(
				'post_type'      => 'tp_event',
				'posts_per_page' => - 1,
				'meta_query'     => array(
					array(
						'key'   => 'tp_event_status',
						'value' => $stt,
					),
				),
			);

			$results = new WP_Query( $args );
			if ( $results->have_posts() ) :
				?>

				<div class="tp_event_admin_widget<?php echo ( $i === 0 ) ? esc_attr( ' active' ) : ''; ?>" data-status="<?php echo esc_attr( $stt ); ?>">
					<ul>
						<?php
						while ( $results->have_posts() ) :
							$results->the_post();
							?>

							<li>
								<p>
									<input id="<?php echo esc_attr( $this->id . '-' . get_the_ID() ); ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'wp_events' ) ); ?>[]" value="<?php echo esc_attr( get_the_ID() ); ?>" <?php checked( in_array( get_the_ID(), $selected, true ) ); ?>/>
									<label for="<?php echo esc_attr( $this->id . '-' . get_the_ID() ); ?>"><?php echo esc_html( get_the_title() ); ?></label>
								</p>
							</li>

							<?php
						endwhile;
						wp_reset_postdata();
						?>
					</ul>
				</div>

				<?php
				++$i;
			endif;
		}
	}
}
