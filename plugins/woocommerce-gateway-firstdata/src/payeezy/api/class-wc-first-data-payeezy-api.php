<?php
/**
 * WooCommerce First Data
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@kestrelwp.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce First Data to newer
 * versions in the future. If you wish to customize WooCommerce First Data for your
 * needs please refer to http://docs.woocommerce.com/document/firstdata/
 *
 * @author      Kestrel
 * @copyright   Copyright (c) 2013-2024, Kestrel
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\PluginFramework\v5_12_4 as Framework;

/**
 * Payeezy API Class
 *
 * Handles API calls to the Payeezy REST API
 *
 * @link https://developer.payeezy.com/apis
 *
 * @since 4.0.0
 */
class WC_First_Data_Payeezy_API extends Framework\SV_WC_API_Base implements Framework\SV_WC_Payment_Gateway_API {


	/** production API base URL */
	const PRODUCTION_URL = 'https://api.payeezy.com/v1/';

	/** sandbox API base URL */
	const SANDBOX_URL = 'https://api-cert.payeezy.com/v1/';

	/** @var \WC_Gateway_First_Data_Payeezy_Credit_Card|\WC_Gateway_First_Data_Payeezy_eCheck gateway associated with request */
	protected $gateway;

	/** @var \WC_Order|null order associated with the request, if any */
	protected $order;


	/**
	 * Setup request object and set endpoint
	 *
	 * @since 4.0.0
	 *
	 * @param \WC_Gateway_First_Data_Payeezy_Credit_Card|\WC_Gateway_First_Data_Payeezy_eCheck $gateway
	 */
	public function __construct( $gateway ) {

		$this->gateway = $gateway;

		// base request URI
		$this->request_uri = $gateway->is_production_environment() ? self::PRODUCTION_URL : self::SANDBOX_URL;

		// #json4life
		$this->set_request_content_type_header( 'application/json;' );
		$this->set_request_accept_header( 'application/json' );
	}


	/**
	 * Creates a new credit card charge transaction.
	 *
	 * @since 4.0.0
	 *
	 * @see Framework\SV_WC_Payment_Gateway_API::credit_card_charge()
	 * @param \WC_Order $order order
	 * @return \WC_First_Data_Payeezy_API_Transaction_Response
	 * @throws Framework\SV_WC_API_Exception
	 */
	public function credit_card_charge( \WC_Order $order ) {

		return $this->perform_transaction( __FUNCTION__, $order );
	}


	/**
	 * Creates a new credit card authorization transaction.
	 *
	 * @since 4.0.0
	 *
	 * @see Framework\SV_WC_Payment_Gateway_API::credit_card_authorization()
	 * @param \WC_Order $order order
	 * @return \WC_First_Data_Payeezy_API_Transaction_Response
	 * @throws Framework\SV_WC_API_Exception
	 */
	public function credit_card_authorization( \WC_Order $order ) {

		return $this->perform_transaction( __FUNCTION__, $order );
	}


	/**
	 * Creates a new credit card capture transaction.
	 *
	 * @since 4.0.0
	 *
	 * @see Framework\SV_WC_Payment_Gateway_API::credit_card_capture()
	 * @param \WC_Order $order order
	 * @return \WC_First_Data_Payeezy_API_Transaction_Response
	 * @throws Framework\SV_WC_API_Exception
	 */
	public function credit_card_capture( \WC_Order $order ) {

		return $this->perform_transaction( __FUNCTION__, $order );
	}


	/**
	 * Creates a new eCheck debit transaction.
	 *
	 * @since 4.0.0
	 *
	 * @see Framework\SV_WC_Payment_Gateway_API::check_debit()
	 * @param \WC_Order $order order
	 * @return \WC_First_Data_Payeezy_API_Transaction_Response
	 * @throws Framework\SV_WC_API_Exception
	 */
	public function check_debit( \WC_Order $order ) {

		return $this->perform_transaction( __FUNCTION__, $order );
	}


	/**
	 * Creates a new refund transaction.
	 *
	 * @since 4.0.0
	 *
	 * @see Framework\SV_WC_Payment_Gateway_API::refund()
	 * @param \WC_Order $order order
	 * @return \WC_First_Data_Payeezy_API_Transaction_Response
	 * @throws Framework\SV_WC_API_Exception
	 */
	public function refund( \WC_Order $order ) {

		return $this->perform_transaction( __FUNCTION__, $order );
	}


	/**
	 * Creates a new void transaction.
	 *
	 * @since 4.0.0
	 *
	 * @see Framework\SV_WC_Payment_Gateway_API::void()
	 * @param \WC_Order $order order
	 * @return \WC_First_Data_Payeezy_API_Transaction_Response
	 * @throws Framework\SV_WC_API_Exception
	 */
	public function void( \WC_Order $order ) {

		return $this->perform_transaction( __FUNCTION__, $order );
	}


	/**
	 * Creates a new tokenize payment method transaction.
	 *
	 * @since 4.0.0
	 *
	 * @see Framework\SV_WC_Payment_Gateway_API::tokenize_payment_method()
	 * @param \WC_Order $order order
	 * @return \WC_First_Data_Payeezy_API_Tokenize_Credit_Card_Response
	 * @throws Framework\SV_WC_API_Exception
	 */
	public function tokenize_payment_method( \WC_Order $order ) {

		if ( ! empty( $order->payment->token ) ) {

			return new \WC_First_Data_Payeezy_API_Tokenize_Credit_Card_Response( $order );
		}
	}


	/**
	 * Helper method to perform the given transaction and DRY up the required
	 * interface methods above, as Payeezy conveniently does not
	 * support the methods that don't require order objects
	 *
	 * @since 4.0.0
	 *
	 * @param string $type transaction type, e.g. credit_card_capture
	 * @param \WC_Order $order order associated with transaction request
	 * @return \WC_First_Data_Payeezy_API_Transaction_Response
	 * @throws Framework\SV_WC_API_Exception
	 */
	protected function perform_transaction( $type, \WC_Order $order ) {

		$request = $this->get_new_request( array( 'type' => $type, 'order' => $order ) );

		// e.g. create_credit_card_capture
		$type = "create_{$type}";

		// e.g. $request->create_credit_card_capture()
		$request->$type();

		return $this->perform_request( $request );
	}


	/**
	 * Overrides the perform_request() method to set the credentials required for
	 * the transaction. This is done *after* the request data has been formed
	 * as the HMAC hash requires the request body.
	 *
	 * @since 4.0.0
	 *
	 * @see Framework\SV_WC_API_Base::perform_request()
	 * @param \WC_First_Data_Payeezy_API_Credit_Card_Transaction_Request|\WC_First_Data_Payeezy_API_eCheck_Transaction_Request $request
	 * @return \WC_First_Data_Payeezy_API_Request
	 * @throws Framework\SV_WC_API_Exception
	 */
	protected function perform_request( $request ) {

		$this->set_credential_headers( $request );

		return parent::perform_request( $request );
	}


	/**
	 * Calculate the HMAC hash and set the required headers for the request
	 *
	 * @link https://github.com/payeezy/payeezy_direct_API/blob/master/payeezy_php/example/src/Payeezy.php#L595-L616
	 *
	 * @since 4.0.0
	 * @param \WC_First_Data_Payeezy_API_Transaction_Request $request
	 */
	protected function set_credential_headers( $request ) {

		$api_key    = $this->get_gateway()->get_api_key();
		$api_secret = $this->get_gateway()->get_api_secret();
		$nonce      = wp_rand();
		$payload    = $request->to_string();
		$token      = $this->get_gateway()->get_merchant_token();
		$timestamp  = time() * 1000; // in microseconds

		// calculate authorization HMAC hash
		$hmac_hash = base64_encode( hash_hmac( 'sha256', ( $api_key . $nonce . $timestamp . $token . $payload ), $api_secret, false ) );

		$this->set_request_headers( array(
			'apikey'        => $api_key,
			'token'         => $token,
			'nonce'         => $nonce,
			'timestamp'     => $timestamp,
			'authorization' => $hmac_hash,
		) );
	}


	/**
	 * Get sanitized request headers suitable for logging, stripped of any
	 * confidential information
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_API_Base::get_sanitized_request_headers()
	 * @return array
	 */
	protected function get_sanitized_request_headers() {

		$headers = $this->get_request_headers();

		foreach ( array( 'apikey', 'token', 'nonce', 'authorization' ) as $key ) {

			if ( ! empty( $headers[ $key ] ) ) {
				$headers[ $key ] = str_repeat( '*', strlen( $headers[ $key ] ) );
			}
		}

		return $headers;
	}


	/**
	 * Check if the response has any status code errors
	 *
	 * @since 4.0.0
	 *
	 * @see Framework\SV_WC_API_Base::do_pre_parse_response_validation()
	 * @throws Framework\SV_WC_API_Exception non HTTP 200 status
	 */
	protected function do_pre_parse_response_validation() {

		// bad requests will return non HTTP 201
		if ( ! in_array( $this->get_response_code(), array( 200, 201 ) ) ) {

			$response = json_decode( $this->get_raw_response_body() );

			throw new Framework\SV_WC_API_Exception( sprintf( 'HTTP %1$s %2$s: %3$s', $this->get_response_code(), $this->get_response_message(), isset( $response->message ) ? $response->message : $this->get_raw_response_body() ) );
		}
	}


	/**
	 * Return the parsed response object for the request, overrides the parent
	 * method to include the request when instantiating the response
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_API_Base::get_parsed_response()
	 * @param string $raw_response_body
	 * @return \WC_First_Data_Payeezy_API_Transaction_Response
	 */
	protected function get_parsed_response( $raw_response_body ) {

		$handler_class = $this->get_response_handler();

		return new $handler_class( $this->get_request(), $raw_response_body );
	}


	/** Getters ***************************************************************/


	/**
	 * Instantiates and returns the proper request object.
	 *
	 * @since 4.0.0
	 *
	 * @param array $args
	 * @return \WC_First_Data_Payeezy_API_Request
	 * @throws Framework\SV_WC_API_Exception
	 */
	protected function get_new_request( $args = array() ) {

		// sanity check
		if ( empty( $args['type'] ) || empty( $args['order'] ) || ! $args['order'] instanceof \WC_Order ) {
			throw new Framework\SV_WC_API_Exception( 'Request type is missing, or order is missing/invalid' );
		}

		switch ( $args['type'] ) {

			// eCheck transaction
			case 'check_debit':

				$this->set_response_handler( 'WC_First_Data_Payeezy_API_Transaction_Response' );

				return new \WC_First_Data_Payeezy_API_eCheck_Transaction_Request( $args['order'] );

			// all other transactions
			default:

				$this->set_response_handler( 'WC_First_Data_Payeezy_API_Transaction_Response' );

				return new \WC_First_Data_Payeezy_API_Credit_Card_Transaction_Request( $args['order'] );
		}
	}


	/**
	 * Return the order associated with the request, if any
	 *
	 * @since 4.0.0
	 * @return \WC_Order|null
	 */
	public function get_order() {

		return $this->order;
	}


	/**
	 * Get the ID for the API, used primarily to namespace the action name
	 * for broadcasting requests
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_API_Base::get_api_id()
	 * @return string
	 */
	protected function get_api_id() {

		return $this->get_gateway()->get_id();
	}


	/**
	 * Return the main plugin class instance
	 *
	 * @since 4.0.0
	 * @return \WC_First_Data
	 */
	protected function get_plugin() {

		return wc_first_data();
	}


	/**
	 * Return the gateway class instance associated with this request
	 *
	 * @since 4.0.0
	 * @return \WC_Gateway_First_Data_Payeezy_Credit_Card|\WC_Gateway_First_Data_Payeezy_eCheck
	 */
	protected function get_gateway() {

		return $this->gateway;
	}


	/** No-op methods *************************************************************************************************/


	/**
	 * Payeezy does not support getting tokenized payment methods
	 *
	 * @since 4.0.0
	 */
	public function supports_get_tokenized_payment_methods() { }


	/**
	 * Payeezy does not support getting tokenized payment methods
	 *
	 * @since 4.0.0
	 *
	 * @param string $customer_id unused
	 */
	public function get_tokenized_payment_methods( $customer_id ) { }


	/**
	 * Payeezy does not support removing tokenized payment methods
	 *
	 * @since 4.0.0
	 */
	public function supports_remove_tokenized_payment_method() { }


	/**
	 * Payeezy does not support removing tokenized payment methods
	 *
	 * @since 4.0.0
	 *
	 * @param string $token unused
	 * @param string $customer_id unused
	 */
	public function remove_tokenized_payment_method( $token, $customer_id ) { }


	/**
	 * Updates a tokenized payment method.
	 *
	 * @since 4.4.0
	 *
	 * @param \WC_Order $order order object
	 */
	public function update_tokenized_payment_method( \WC_Order $order ) { }


	/**
	 * Determines if this API supports updating tokenized payment methods.
	 *
	 * @since 4.4.0
	 *
	 * @return bool
	 */
	public function supports_update_tokenized_payment_method() {

		return false;
	}


}
