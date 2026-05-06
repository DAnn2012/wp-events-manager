<?php
/**
 * Event database tests.
 *
 * @package WPEMS\Tests\Unit\Databases
 */

namespace WPEMS\Tests\Unit\Databases;

use Mockery;
use stdClass;
use WPEMS\Databases\EventDB;
use WPEMS\Tests\Unit\TestCase;

/**
 * Test event database query wrapper.
 */
class EventDBTest extends TestCase {

	/**
	 * Reset singleton.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->resetStaticProperty( EventDB::class, 'instance', null );
	}

	/**
	 * It returns zero for invalid event IDs.
	 *
	 * @return void
	 */
	public function test_get_booked_quantity_returns_zero_for_empty_event_id(): void {
		$this->assertSame( 0, EventDB::getInstance()->get_booked_quantity( 0 ) );
	}

	/**
	 * It returns booked quantity from wpdb.
	 *
	 * @return void
	 */
	public function test_get_booked_quantity_returns_int(): void {
		$wpdb = $this->mockWpdb();
		$wpdb->shouldReceive( 'prepare' )->once()->andReturn( 'prepared quantity sql' );
		$wpdb->shouldReceive( 'get_var' )->once()->with( 'prepared quantity sql' )->andReturn( '4' );

		$this->assertSame( 4, EventDB::getInstance()->get_booked_quantity( 22 ) );
	}

	/**
	 * It includes user ID when requested.
	 *
	 * @return void
	 */
	public function test_get_booked_quantity_accepts_user_filter(): void {
		$wpdb = $this->mockWpdb();
		$wpdb->shouldReceive( 'prepare' )
			->once()
			->andReturnUsing(
				function ( $query ) {
					$this->assertStringContainsString( 'AND user.ID = %d', $query );

					return 'prepared user quantity sql';
				}
			);
		$wpdb->shouldReceive( 'get_var' )->once()->with( 'prepared user quantity sql' )->andReturn( '2' );

		$this->assertSame( 2, EventDB::getInstance()->get_booked_quantity( 22, 9 ) );
	}

	/**
	 * It returns registered booking rows.
	 *
	 * @return void
	 */
	public function test_get_registered_bookings_returns_array(): void {
		$rows = array(
			(object) array( 'ID' => 300 ),
			(object) array( 'ID' => 301 ),
		);

		$wpdb = $this->mockWpdb();
		$wpdb->shouldReceive( 'prepare' )->once()->andReturn( 'prepared registered sql' );
		$wpdb->shouldReceive( 'get_results' )->once()->with( 'prepared registered sql' )->andReturn( $rows );

		$this->assertSame( $rows, EventDB::getInstance()->get_registered_bookings( 22 ) );
	}

	/**
	 * Build a wpdb double.
	 *
	 * @return Mockery\MockInterface
	 */
	private function mockWpdb() {
		global $wpdb;

		$wpdb           = Mockery::mock( stdClass::class );
		$wpdb->posts    = 'wp_posts';
		$wpdb->postmeta = 'wp_postmeta';
		$wpdb->users    = 'wp_users';

		return $wpdb;
	}
}
