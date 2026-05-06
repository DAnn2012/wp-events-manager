<?php
/**
 * Shared unit test base.
 *
 * @package WPEMS\Tests\Unit
 */

namespace WPEMS\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use ReflectionClass;
use WP_Post;

/**
 * Base test case with Brain Monkey lifecycle.
 */
abstract class TestCase extends PHPUnitTestCase {

	/**
	 * Set up common WordPress function doubles.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		$_GET     = array();
		$_POST    = array();
		$_REQUEST = array();

		Functions\when( '__' )->returnArg( 1 );
		Functions\when( 'esc_html__' )->returnArg( 1 );
		Functions\when( 'esc_attr__' )->returnArg( 1 );
		Functions\when( 'esc_html' )->returnArg( 1 );
		Functions\when( 'esc_attr' )->returnArg( 1 );
		Functions\when( 'esc_url' )->returnArg( 1 );
		Functions\when( 'esc_url_raw' )->returnArg( 1 );
		Functions\when( 'wp_json_encode' )->alias( 'json_encode' );
		Functions\when( 'wp_unslash' )->alias(
			function ( $value ) {
				if ( is_array( $value ) ) {
					return array_map( 'stripslashes', $value );
				}

				return stripslashes( (string) $value );
			}
		);
		Functions\when( 'sanitize_text_field' )->alias(
			function ( $value ) {
				return trim( strip_tags( (string) $value ) );
			}
		);
		Functions\when( 'sanitize_key' )->alias(
			function ( $value ) {
				return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $value ) );
			}
		);
		Functions\when( 'sanitize_email' )->alias(
			function ( $value ) {
				return filter_var( (string) $value, FILTER_SANITIZE_EMAIL );
			}
		);
		Functions\when( 'sanitize_user' )->alias(
			function ( $value ) {
				return preg_replace( '/[^A-Za-z0-9_\-.@]/', '', (string) $value );
			}
		);
		Functions\when( 'absint' )->alias(
			function ( $value ) {
				return abs( (int) $value );
			}
		);
		Functions\when( 'maybe_unserialize' )->returnArg( 1 );
		Functions\when( 'is_wp_error' )->alias(
			function ( $value ) {
				return $value instanceof \WP_Error;
			}
		);
		Functions\when( 'wp_parse_args' )->alias(
			function ( $args, $defaults = array() ) {
				return array_merge( (array) $defaults, (array) $args );
			}
		);
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'apply_filters' )->alias(
			function ( $tag, $value = null ) {
				return $value;
			}
		);
	}

	/**
	 * Tear down Brain Monkey and Mockery.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		Mockery::close();
		parent::tearDown();
	}

	/**
	 * Build a WP_Post double.
	 *
	 * @param int    $id        Post ID.
	 * @param string $post_type Post type.
	 * @param string $title     Post title.
	 * @param string $status    Post status.
	 *
	 * @return WP_Post
	 */
	protected function makePost( int $id, string $post_type = 'post', string $title = 'Test post', string $status = 'publish' ): WP_Post {
		return new WP_Post(
			array(
				'ID'                => $id,
				'post_author'       => 1,
				'post_date'         => '2026-01-01 00:00:00',
				'post_date_gmt'     => '2026-01-01 00:00:00',
				'post_modified'     => '2026-01-01 00:00:00',
				'post_modified_gmt' => '2026-01-01 00:00:00',
				'post_content'      => 'Content',
				'post_title'        => $title,
				'post_excerpt'      => 'Excerpt',
				'post_status'       => $status,
				'post_password'     => '',
				'post_name'         => 'test-post',
				'post_type'         => $post_type,
				'post_parent'       => 0,
				'filter'            => 'raw',
			)
		);
	}

	/**
	 * Reset a private static class property.
	 *
	 * @param string $class    Class name.
	 * @param string $property Property name.
	 * @param mixed  $value    New value.
	 *
	 * @return void
	 */
	protected function resetStaticProperty( string $class, string $property, $value ): void {
		$reflection = new ReflectionClass( $class );
		$prop       = $reflection->getProperty( $property );
		$prop->setAccessible( true );
		$prop->setValue( null, $value );
	}
}
