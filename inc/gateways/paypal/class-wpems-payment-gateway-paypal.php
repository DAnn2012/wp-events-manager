<?php
/**
 * Legacy PayPal gateway compatibility class.
 *
 * @package WP-Events-Manager/Class
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( '\WPEMS\Gateways\PaypalGateway' ) ) {
	require_once WPEMS_INC . 'Gateways/PaypalGateway.php';
}

/**
 * Backward-compatible class name for older code that instantiates PayPal directly.
 */
class WPEMS_Payment_Gateway_Paypal extends \WPEMS\Gateways\PaypalGateway {}
