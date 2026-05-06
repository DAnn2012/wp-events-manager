<?php
/**
 * WP Events Manager Event class
 *
 * @author        ThimPress, leehld
 * @package       WP-Events-Manager/Class
 * @version       2.1.7
 */
use WPEMS\Models\EventPostModel;
/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit;

class WPEMS_Event {

	public $post     = null;
	public $ID       = null;
	private $model   = null;
	static $instance = array();

	public function __construct( $id = null ) {
		if ( $id instanceof EventPostModel ) {
			$this->model = $id;
		} elseif ( is_numeric( $id ) && $id ) {
			$this->model = EventPostModel::find( absint( $id ) );
		} elseif ( $id instanceof WP_Post || is_object( $id ) ) {
			$this->post = $id;

			if ( ! empty( $id->ID ) && ( empty( $id->post_type ) || $id->post_type === 'tp_event' ) ) {
				$this->model = new EventPostModel( $id );
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
	 * @param EventPostModel $model Event model.
	 *
	 * @return WP_Post
	 */
	private function post_from_model( EventPostModel $model ) {
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
	 * @return EventPostModel|null
	 */
	public function get_model() {
		return $this->model;
	}

	/**
	 * Magic method
	 *
	 * @param type $key
	 *
	 * @return mixed
	 */
	public function __get( $key = null ) {
		return $this->model ? $this->model->get_meta( (string) $key ) : null;
	}

	/**
	 * get event title
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->model ? $this->model->get_title() : '';
	}

	/**
	 * is free
	 *
	 * @return type boolean
	 */
	public function is_free() {
		return $this->model ? $this->model->is_free() : true;
	}

	/**
	 * get price
	 *
	 * @return type float
	 */
	public function get_price() {
		return $this->model ? $this->model->get_price() : 0.0;
	}

	/**
	 * registered
	 *
	 * @global type $wpdb
	 * @return array
	 */
	public function load_registered() {
		return $this->model ? $this->model->load_registered() : array();
	}

	/**
	 * get available slot
	 *
	 * @return type
	 */
	public function get_slot_available() {
		return $this->model ? $this->model->get_slot_available() : 0;
	}

	/**
	 * register time
	 *
	 * @return init
	 */
	public function get_registered_time() {
		return $this->model ? $this->model->get_registered_time() : 0;
	}

	/**
	 * get booked quantity
	 *
	 * @global type $wpdb
	 *
	 * @param type $user_id
	 *
	 * @return init
	 */
	public function booked_quantity( $user_id = null ) {
		return $this->model ? $this->model->booked_quantity( is_null( $user_id ) ? null : absint( $user_id ) ) : 0;
	}

	/**
	 * WPEMS_Event instance
	 *
	 * @param WP_Post $id
	 *
	 * @return type
	 */
	public static function instance( $id, $option = null ) {
		if ( $id instanceof EventPostModel ) {
			$event_id = $id->get_id();
		} elseif ( is_numeric( $id ) ) {
			$event_id = absint( $id );
		} elseif ( $id instanceof WP_Post || is_object( $id ) ) {
			$event_id = ! empty( $id->ID ) ? absint( $id->ID ) : 0;
		} else {
			$event_id = 0;
		}

		if ( $event_id && ! empty( self::$instance[ $event_id ] ) ) {
			return self::$instance[ $event_id ];
		}

		$instance = new self( $id );
		if ( $instance->ID ) {
			self::$instance[ $instance->ID ] = $instance;
		}

		return $instance;
	}
}
