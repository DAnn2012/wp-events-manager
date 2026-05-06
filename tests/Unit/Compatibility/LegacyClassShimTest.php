<?php
/**
 * Legacy class shim tests.
 *
 * @package WPEMS\Tests\Unit\Compatibility
 */

namespace WPEMS\Tests\Unit\Compatibility;

use Brain\Monkey\Functions;
use Mockery;
use WPEMS\Models\BookingPostModel;
use WPEMS\Models\EventPostModel;
use WPEMS\Tests\Unit\TestCase;

/**
 * Test legacy class compatibility wrappers.
 */
class LegacyClassShimTest extends TestCase {

	/**
	 * Load legacy classes and reset static caches.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		require_once WPEMS_INC . 'class-wpems-event.php';
		require_once WPEMS_INC . 'class-wpems-booking.php';

		$this->resetStaticProperty( EventPostModel::class, 'instances', array() );
		$this->resetStaticProperty( BookingPostModel::class, 'instances', array() );
		$this->resetStaticProperty( \WPEMS_Event::class, 'instance', array() );
		$this->resetStaticProperty( \WPEMS_Booking::class, 'instance', array() );
	}

	/**
	 * Event shim delegates public reads to EventPostModel.
	 *
	 * @return void
	 */
	public function test_event_shim_delegates_to_model(): void {
		Functions\expect( 'get_post' )
			->once()
			->with( 22 )
			->andReturn( $this->makePost( 22, 'tp_event', 'Legacy event' ) );

		Functions\expect( 'get_the_title' )
			->once()
			->with( 22 )
			->andReturn( 'Legacy event' );

		Functions\expect( 'get_post_meta' )
			->once()
			->with( 22, 'tp_event_price', true )
			->andReturn( '9.5' );

		$event = \WPEMS_Event::instance( 22 );

		$this->assertSame( 22, $event->ID );
		$this->assertSame( 'Legacy event', $event->get_title() );
		$this->assertSame( 9.5, $event->get_price() );
		$this->assertSame( '9.5', $event->price );
		$this->assertFalse( $event->is_free() );
		$this->assertInstanceOf( EventPostModel::class, $event->get_model() );
	}

	/**
	 * Booking shim delegates meta reads and status updates to BookingPostModel.
	 *
	 * @return void
	 */
	public function test_booking_shim_delegates_to_model(): void {
		Functions\expect( 'get_post' )
			->once()
			->with( 33 )
			->andReturn( $this->makePost( 33, 'event_auth_book', 'Legacy booking', 'ea-pending' ) );

		Functions\when( 'get_post_meta' )->alias(
			function ( $post_id, $key ) {
				$this->assertSame( 33, $post_id );
				$meta = array(
					'ea_booking_user_id'    => '44',
					'ea_booking_price'      => '12.25',
					'ea_booking_payment_id' => 'paypal',
				);

				return $meta[ $key ] ?? '';
			}
		);

		Functions\expect( 'get_post_status' )
			->once()
			->with( 33 )
			->andReturn( 'ea-pending' );

		Functions\expect( 'wp_update_post' )
			->once()
			->with(
				array(
					'ID'          => 33,
					'post_status' => 'ea-completed',
				)
			)
			->andReturn( 33 );

		Functions\expect( 'do_action' )
			->twice()
			->withAnyArgs();

		$booking = \WPEMS_Booking::instance( 33 );

		$this->assertSame( 33, $booking->ID );
		$this->assertSame( '44', $booking->user_id );
		$this->assertSame( '12.25', $booking->price );
		$this->assertSame( 'paypal', $booking->payment_id );
		$this->assertTrue( $booking->update_status( 'completed' ) );
		$this->assertInstanceOf( BookingPostModel::class, $booking->get_model() );
	}

	/**
	 * Booking shim keeps create_booking available for old integrations.
	 *
	 * @return void
	 */
	public function test_booking_shim_create_booking_delegates_to_model(): void {
		$booking = new \WPEMS_Booking();

		Functions\expect( 'wp_get_current_user' )
			->once()
			->andReturn(
				(object) array(
					'ID'            => 44,
					'user_nicename' => 'demo_user',
				)
			);

		Functions\expect( 'wp_insert_post' )
			->once()
			->andReturnUsing(
				function ( $data ) {
					$this->assertSame( 77, $data['meta_input']['ea_booking_event_id'] );
					$this->assertSame( 20.0, $data['meta_input']['ea_booking_price'] );

					return 503;
				}
			);

		Functions\expect( 'get_post' )
			->once()
			->with( 503 )
			->andReturn( $this->makePost( 503, 'event_auth_book', 'Saved booking', 'ea-pending' ) );

		Functions\expect( 'do_action' )
			->once()
			->with( 'tp_event_create_new_booking', 503, Mockery::type( 'array' ) );

		$id = $booking->create_booking(
			array(
				'event_id' => 77,
				'qty'      => 1,
				'price'    => 20,
				'currency' => 'USD',
			),
			'paypal'
		);

		$this->assertSame( 503, $id );
		$this->assertSame( 503, $booking->ID );
		$this->assertInstanceOf( BookingPostModel::class, $booking->get_model() );
	}
}
