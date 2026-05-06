<?php
/**
 * Event query filter data object.
 *
 * @package WPEMS/Filters
 */

namespace WPEMS\Filters;

defined( 'ABSPATH' ) || exit;

class EventFilter {

	/**
	 * Event ID.
	 *
	 * @var int
	 */
	public $ID = 0;

	/**
	 * Post type.
	 *
	 * @var string
	 */
	public $post_type = 'tp_event';

	/**
	 * Post status.
	 *
	 * @var string
	 */
	public $status = '';

	/**
	 * Date range start.
	 *
	 * @var string
	 */
	public $date_from = '';

	/**
	 * Date range end.
	 *
	 * @var string
	 */
	public $date_to = '';

	/**
	 * Query limit.
	 *
	 * @var int
	 */
	public $limit = 10;

	/**
	 * Query offset.
	 *
	 * @var int
	 */
	public $offset = 0;

	/**
	 * Order by field.
	 *
	 * @var string
	 */
	public $order_by = 'post_date';

	/**
	 * Sort direction.
	 *
	 * @var string
	 */
	public $order = 'DESC';
}
