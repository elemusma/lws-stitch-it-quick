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

use SkyVerge\WooCommerce\PluginFramework\v5_11_12 as Framework;

/**
 * Payeezy Gateway API Class
 *
 * Handles API calls to the Payeezy Gateway Web Service API
 *
 * @link https://support.payeezy.com/hc/en-us/articles/204029989-First-Data-Payeezy-Gateway-Web-Service-API-Reference-Guide
 *
 * @since 4.0.0
 */
class WC_First_Data_Payeezy_Gateway_API extends Framework\SV_WC_API_Base implements Framework\SV_WC_Payment_Gateway_API {


	/** payeezy gateway API production URL, v19 */
	const PRODUCTION_URL = 'https://api.globalgatewaye4.firstdata.com/transaction/v19';

	/** payeezy gateway API demo URL, v19 */
	const DEMO_URL = 'https://api.demo.globalgatewaye4.firstdata.com/transaction/v19';

	/** @var \WC_Gateway_First_Data_Payeezy_Gateway_Credit_Card|\WC_Gateway_First_Data_Payeezy_Gateway_eCheck gateway associated with request */
	protected $gateway;

	/** @var \WC_Order|null order associated with the request, if any */
	protected $order;


	/**
	 * Setup request object and set endpoint
	 *
	 * @since 4.0.0
	 * @param \WC_Gateway_First_Data_Payeezy_Gateway_Credit_Card|\WC_Gateway_First_Data_Payeezy_Gateway_eCheck $gateway
	 */
	public function __construct( $gateway ) {

		$this->gateway = $gateway;

		// base request URI, however unlike most REST APIs, this *does not* vary per request type
		$this->request_uri = $gateway->is_production_environment() ? self::PRODUCTION_URL : self::DEMO_URL;

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
	 * @return \WC_First_Data_Payeezy_Gateway_API_Transaction_Response
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
	 * @return \WC_First_Data_Payeezy_Gateway_API_Transaction_Response
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
	 * @return \WC_First_Data_Payeezy_Gateway_API_Transaction_Response
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
	 * @return \WC_First_Data_Payeezy_Gateway_API_Transaction_Response
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
	 * @return \WC_First_Data_Payeezy_Gateway_API_Transaction_Response
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
	 * @return \WC_First_Data_Payeezy_Gateway_API_Transaction_Response
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
	 * @return \WC_First_Data_Payeezy_Gateway_API_Transaction_Response
	 * @throws Framework\SV_WC_API_Exception
	 */
	public function tokenize_payment_method( \WC_Order $order ) {

		$payment_total = $order->payment_total;

		// must be zero in order for tokens to be returned
		$order->payment_total = '0';

		$response = $this->perform_transaction( __FUNCTION__, $order );

		$order->payment_total = $payment_total;

		return $response;
	}


	/**
	 * Helper method to perform the given transaction and DRY up the required
	 * interface methods above, as the Payeezy Gateway conveniently does not
	 * support the methods that don't require order objects
	 *
	 * @since 4.0.0
	 *
	 * @param string $type transaction type, e.g. credit_card_capture
	 * @param \WC_Order $order order associated with transaction request
	 * @return \WC_First_Data_Payeezy_Gateway_API_Transaction_Response
	 * @throws Framework\SV_WC_API_Exception
	 */
	protected function perform_transaction( $type, \WC_Order $order ) {

		$request = $this->get_new_request( [ 'type' => $type, 'order' => $order ] );

		// e.g. create_credit_card_capture
		$type = "create_{$type}";

		// e.g. $request->create_credit_card_capture()
		$request->$type();

		return $this->perform_request( $request );
	}


	/**
	 * Override the perform_request() method to calculate and add the HMAC hash
	 * before the request is performed
	 *
	 * @since 4.0.0
	 *
	 * @see Framework\SV_WC_API_Base::perform_request()
	 * @param \WC_First_Data_Payeezy_Gateway_API_Transaction_Request $request
	 * @return \WC_First_Data_Payeezy_Gateway_API_Transaction_Response
	 * @throws Framework\SV_WC_API_Exception
	 */
	protected function perform_request( $request ) {

		$this->set_hmac_hash( $request );

		return parent::perform_request( $request );
	}


	/**
	 * Calculate the HMAC hash and set the required headers for the request
	 *
	 * @link https://support.payeezy.com/hc/en-us/articles/203731149-API-Security-HMAC-Hash
	 *
	 * @since 4.0.0
	 * @param \WC_First_Data_Payeezy_Gateway_API_Transaction_Request $request
	 */
	protected function set_hmac_hash( $request ) {

		$content_type   = $this->request_headers['content-type'];
		$method         = $request->get_method();
		$content_digest = sha1( $request->to_string() );
		$gge4_date      = gmdate( 'Y-m-d\TH:i:s\Z' );
		$request_url    = parse_url( $this->get_request_uri(), PHP_URL_PATH );

		$hmac_hash = base64_encode( hash_hmac( 'sha1', $method . "\n" . $content_type . "\n" . $content_digest . "\n" . $gge4_date . "\n" . $request_url, $this->get_gateway()->get_hmac_key(), true ) );

		$this->set_request_header( 'Authorization', sprintf( 'GGE4_API %1$s:%2$s', $this->get_gateway()->get_key_id(), $hmac_hash ) );
		$this->set_request_header( 'x-gge4-date', $gge4_date );
		$this->set_request_header( 'x-gge4-content-sha1', $content_digest );
	}


	/**
	 * Checks if the response has any status code errors.
	 *
	 * @since 4.0.0
	 *
	 * @see Framework\SV_WC_API_Base::do_pre_parse_response_validation()
	 * @throws Framework\SV_WC_API_Exception non HTTP 200 status
	 */
	protected function do_pre_parse_response_validation() {

		// bad requests will return non HTTP 201
		if ( 201 != $this->get_response_code() ) {

			throw new Framework\SV_WC_API_Exception( sprintf( 'HTTP %1$s %2$s: %3$s', $this->get_response_code(), $this->get_response_message(), $this->get_raw_response_body() ) );
		}
	}


	/**
	 * Return the parsed response object for the request, overrides the parent
	 * method to include the request when instantiating the response
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_API_Base::get_parsed_response()
	 * @param string $raw_response_body
	 * @return \WC_First_Data_Payeezy_Gateway_API_Transaction_Response
	 */
	protected function get_parsed_response( $raw_response_body ) {

		$handler_class = $this->get_response_handler();

		return new $handler_class( $this->get_request(), $raw_response_body );
	}

	/** Non-supported methods *************************************************/


	/**
	 * Payeezy Gateway does not support getting tokenized payment methods
	 *
	 * @since 4.0.0
	 */
	public function supports_get_tokenized_payment_methods() { }


	/**
	 * Payeezy Gateway does not support getting tokenized payment methods
	 *
	 * @since 4.0.0
	 *
	 * @param string $customer_id unused
	 */
	public function get_tokenized_payment_methods( $customer_id ) { }


	/**
	 * Payeezy Gateway does not support removing tokenized payment methods
	 *
	 * @since 4.0.0
	 */
	public function supports_remove_tokenized_payment_method() { }


	/**
	 * Payeezy Gateway does not support removing tokenized payment methods
	 *
	 * @since 4.0.0
	 * @param string $token unused
	 * @param string $customer_id unused
	 */
	public function remove_tokenized_payment_method( $token, $customer_id ) { }


	/**
	 * Payeezy Gateway does not support updating tokenized payment methods.
	 *
	 * @since 4.4.0
	 *
	 * @param \WC_Order $order
	 */
	public function update_tokenized_payment_method( \WC_Order $order ) { }


	/**
	 * Payeezy Gateway does not support updating tokenized payment methods.
	 *
	 * @since 4.4.0
	 */
	public function supports_update_tokenized_payment_method() { }


	/** Getters ***************************************************************/


	/**
	 * Instantiates and return the proper request object.
	 *
	 * @since 4.0.0
	 *
	 * @param array $args
	 * @return \WC_First_Data_Payeezy_Gateway_API_Transaction_Request
	 * @throws Framework\SV_WC_API_Exception
	 */
	protected function get_new_request( $args = [] ) {

		// sanity check
		if ( empty( $args['type'] ) || empty( $args['order'] ) || ! $args['order'] instanceof \WC_Order ) {
			throw new Framework\SV_WC_API_Exception( 'Request type is missing, or order is missing/invalid' );
		}

		$this->set_response_handler( 'WC_First_Data_Payeezy_Gateway_API_Transaction_Response' );

		return new \WC_First_Data_Payeezy_Gateway_API_Transaction_Request( $args['order'], $this->get_gateway()->get_gateway_id(), $this->get_gateway()->get_gateway_password() );
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
	 * Gets the ID for the API, used primarily to namespace the action name
	 * for broadcasting requests.
	 *
	 * @since 4.0.0
	 *
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
	 * @return \WC_Gateway_First_Data_Payeezy_Gateway_Credit_Card|\WC_Gateway_First_Data_Payeezy_Gateway_eCheck
	 */
	protected function get_gateway() {

		return $this->gateway;
	}


}
