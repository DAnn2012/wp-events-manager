<?php
/**
 * WP Events Manager Booking class
 *
 * @author        ThimPress, leehld
 * @package       WP-Events-Manager/Class
 * @version       2.1.7
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit;

/**
 * WPEMS_Booking
 */
class WPEMS_Booking {


	private static $instance = array();
	public $post             = null;
	public $ID               = null;
	private $model           = null;

	public function __construct( $id = null ) {

		if ( $id instanceof \WPEMS\Models\BookingPostModel ) {
			$this->model = $id;
		} elseif ( is_numeric( $id ) && $id ) {
			$this->model = \WPEMS\Models\BookingPostModel::find( absint( $id ) );
		} elseif ( $id instanceof WP_Post || is_object( $id ) ) {
			$this->post = $id;

			if ( ! empty( $id->ID ) && ( empty( $id->post_type ) || $id->post_type === 'event_auth_book' ) ) {
				$this->model = new \WPEMS\Models\BookingPostModel( $id );
			}
		}

		if ( $this->model ) {
				$this->ID   = $this->model->get_id();
				$this->post = $this->post ? $this->post : $this->post_from_model( $this->model );
		}
	}

	/**
	 * Build a WP_Post-like object from the model data.
	 *
	 * @param \WPEMS\Models\BookingPostModel $model Booking model.
	 *
	 * @return WP_Post
	 */
	private function post_from_model( \WPEMS\Models\BookingPostModel $model ) {

		return new WP_Post(
			(object) array(
				'ID'                => $model->ID,
				'post_author'       => $model->post_author,
				'post_date'         => $model->post_date,
				'post_date_gmt'     => $model->post_date_gmt,
				'post_modified'     => $model->post_modified,
				'post_modified_gmt' => $model->post_modified_gmt,
				'post_content'      => $model->post_content,
				'post_title'        => $model->post_title,
				'post_excerpt'      => $model->post_excerpt,
				'post_status'       => $model->post_status,
				'post_password'     => $model->post_password,
				'post_name'         => $model->post_name,
				'post_type'         => $model->post_type,
				'post_parent'       => $model->post_parent,
				'filter'            => $model->filter,
			)
		);
	}

	/**
	 * Get wrapped model.
	 *
	 * @return \WPEMS\Models\BookingPostModel|null
	 */
	public function get_model() {

		return $this->model;
	}
	/**
	 * Megic method
	 *
	 * @param type $key
	 *
	 * @return mixed
	 */
	public function __get( $key = null ) {

		return $this->model ? $this->model->get_meta( (string) $key ) : null;
	}

	// create booking
	public function create_booking( $args = array(), $payment = '' ) {

		$model      = new \WPEMS\Models\BookingPostModel();
		$booking_id = $model->create_booking( (array) $args, (string) $payment );

		if ( ! is_wp_error( $booking_id ) ) {
			$this->model = $model;
			$this->ID    = $model->get_id();
			$this->post  = $this->post_from_model( $model );
		}

		return $booking_id;
	}

	// update status
	public function update_status( $status = 'ea-completed' ) {

		if ( ! $this->model && $this->ID ) {
			$this->model = \WPEMS\Models\BookingPostModel::find( absint( $this->ID ) );
		}

		if ( ! $this->model ) {
			return;
		}

		return $this->model->update_status( (string) $status );
	}

	public static function instance( $id = null ) {

		if ( $id instanceof \WPEMS\Models\BookingPostModel ) {
			$booking_id = $id->get_id();
		} elseif ( is_numeric( $id ) ) {
			$booking_id = absint( $id );
		} elseif ( $id instanceof WP_Post || is_object( $id ) ) {
			$booking_id = ! empty( $id->ID ) ? absint( $id->ID ) : 0;
		} else {
			$booking_id = 0;
		}

		if ( $booking_id && ! empty( self::$instance[ $booking_id ] ) ) {
			return self::$instance[ $booking_id ];
		}

		$instance = new self( $id );
		if ( $instance->ID ) {
			self::$instance[ $instance->ID ] = $instance;
		}

		return $instance;
	}
}
