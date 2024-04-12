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
 * Global Gateway API Class
 *
 * Handles API calls to the Global Gateway API
 *
 * @link https://www.firstdata.com/downloads/marketing-merchant/fdgg-api-user-manual.pdf
 *
 * @since 4.0.0
 */
class WC_First_Data_Global_Gateway_API extends Framework\SV_WC_API_Base implements Framework\SV_WC_Payment_Gateway_API {


	/** global gateway production URL */
	const PRODUCTION_URL = 'https://secure.linkpt.net:1129';

	/** global gateway production URL */
	const STAGING_URL = 'https://staging.linkpt.net:1129';

	/** @var \WC_Gateway_First_Data_Global_Gateway gateway associated with request */
	protected $gateway;

	/** @var \WC_Order|null order associated with the request, if any */
	protected $order;


	/**
	 * Setup request object and set endpoint
	 *
	 * @since 4.0.0
	 * @param \WC_Gateway_First_Data_global_Gateway $gateway
	 */
	public function __construct( $gateway ) {

		$this->gateway = $gateway;

		// request URI does not vary per request
		$this->request_uri = $gateway->is_production_environment() ? self::PRODUCTION_URL : self::STAGING_URL;

		// XML ¯\_(ツ)_/¯
		$this->set_request_content_type_header( 'application/xml' );
		$this->set_request_accept_header( 'application/xml' );

		// disable safe URL check, as it fails with the port 1129 in the request URL
		add_filter( 'http_request_args', [ $this, 'disable_safe_url_check' ] );
	}


	/**
	 * Creates a new credit card charge transaction.
	 *
	 * @since 4.0.0
	 *
	 * @param \WC_Order $order order
	 * @return \WC_First_Data_Global_Gateway_API_Transaction_Response
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
	 * @param \WC_Order $order order
	 * @return \WC_First_Data_Global_Gateway_API_Transaction_Response
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
	 * @param \WC_Order $order order
	 * @return \WC_First_Data_Global_Gateway_API_Transaction_Response
	 * @throws Framework\SV_WC_API_Exception
	 */
	public function credit_card_capture( \WC_Order $order ) {

		return $this->perform_transaction( __FUNCTION__, $order );
	}


	/**
	 * Creates a new refund transaction.
	 *
	 * @since 4.0.0
	 *
	 * @param \WC_Order $order order
	 * @return \WC_First_Data_Global_Gateway_API_Transaction_Response
	 * @throws Framework\SV_WC_API_Exception
	 */
	public function refund( \WC_Order $order ) {

		return $this->perform_transaction( __FUNCTION__, $order );
	}


	/**
	 * Helper method to perform the given transaction and DRY up the required
	 * interface methods above, as the global Gateway conveniently does not
	 * support the methods that don't require order objects.
	 *
	 * @since 4.0.0
	 *
	 * @param string $type transaction type, e.g. credit_card_capture
	 * @param \WC_Order $order order associated with transaction request
	 * @return \WC_First_Data_Global_Gateway_API_Transaction_Response
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
	 * Set the PEM file required for authentication with the Global Gateway API
	 *
	 * @since 4.0.0
	 * @param resource $curl_handle
	 */
	public function set_pem_file( $curl_handle ) {

		if ( ! $curl_handle ) {
			return;
		}

		curl_setopt( $curl_handle, CURLOPT_SSLCERT, $this->get_gateway()->get_pem_file_path() );
	}


	/**
	 * Performs the remote request.
	 *
	 * WP 4.6 decided to make adding our own `curl_setopt` impossible, so we have to build a custom
	 * request in those cases.
	 *
	 * @since 4.1.4
	 *
	 * @param string $request_uri the request URL
	 * @param array $request_args the request args as used by `wp_safe_remote_request()`
	 * @return array|WP_Error
	 */
	protected function do_remote_request( $request_uri, $request_args ) {

		// create a custom request for WP 4.6+
		if ( version_compare( get_bloginfo( 'version' ), '4.6', '>=' ) ) {

			$headers = $request_args['headers'];
			$type    = $request_args['method'];
			$data    = $request_args['body'];

			$options = [
				'timeout'          => $request_args['timeout'],
				'useragent'        => $request_args['user-agent'],
				'blocking'         => $request_args['blocking'],
				'follow_redirects' => false,
				'verify'           => ABSPATH . WPINC . '/certificates/ca-bundle.crt',
				'hooks'            => new Requests_Hooks(),
			];

			// set PEM file cert for requests
			$options['hooks']->register( 'curl.before_send', [ $this, 'set_pem_file' ] );

			// documented by WP in wp-includes/class-wp-http.php
			$options['verify'] = apply_filters( 'https_ssl_verify', $options['verify'] );

			try {

				$response = Requests::request( $request_uri, $headers, $data, $type, $options );

				// convert the response into an array
				$http_response = new \WP_HTTP_Requests_Response( $response );
				$response      = $http_response->to_array();

				// add the original object to the array
				$response['http_response'] = $http_response;

			}
			catch ( Requests_Exception $e ) {

				$response = new \WP_Error( 'http_request_failed', $e->getMessage() );
			}

			// otherwise, do a good old-fashioned request
		} else {

			// set PEM file cert for requests
			add_action( 'http_api_curl', [ $this, 'set_pem_file' ] );

			$response = wp_safe_remote_request( $request_uri, $request_args );
		}

		return $response;
	}


	/**
	 * Disable the safe URL check done by wp_remote_request() as it rejects the
	 * non-standard port number used for the endpoint
	 *
	 * @since 4.0.0
	 * @param array $args wp_remote_request() args
	 * @return array
	 */
	public function disable_safe_url_check( $args ) {

		// only for our requests
		if ( isset( $args['user-agent'] ) && $args['user-agent'] === $this->get_request_user_agent() ) {
			$args['reject_unsafe_urls'] = false;
		}

		return $args;
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

		// bad requests will return non HTTP 200
		if ( 200 != $this->get_response_code() ) {

			throw new Framework\SV_WC_API_Exception( sprintf( 'HTTP %1$s %2$s: %3$s', $this->get_response_code(), $this->get_response_message(), $this->get_raw_response_body() ) );
		}
	}


	/** Non-supported methods *************************************************/


	/**
	 * Checks transactions are not supported.
	 *
	 * @since 4.0.0
	 *
	 * @param \WC_Order $order order
	 */
	public function check_debit( \WC_Order $order ) {
	}


	/**
	 * Global Gateway does not support voids (transactions must always be captured
	 * prior to refund/void ¯\_(ツ)_/¯ )
	 *
	 * @since 4.0.0
	 *
	 * @param \WC_Order $order order
	 */
	public function void( \WC_Order $order ) {
	}


	/**
	 * Tokenizing payment methods is not supported.
	 *
	 * @since 4.0.0
	 *
	 * @param \WC_Order $order order
	 */
	public function tokenize_payment_method( \WC_Order $order ) {
	}


	/**
	 * Global Gateway does not support getting tokenized payment methods.
	 *
	 * @since 4.0.0
	 */
	public function supports_get_tokenized_payment_methods() {
	}


	/**
	 * Global Gateway does not support getting tokenized payment methods.
	 *
	 * @since 4.0.0
	 *
	 * @param string $customer_id unused
	 */
	public function get_tokenized_payment_methods( $customer_id ) {
	}


	/**
	 * Global Gateway does not support removing tokenized payment methods.
	 *
	 * @since 4.0.0
	 */
	public function supports_remove_tokenized_payment_method() {
	}


	/**
	 * Global Gateway does not support removing tokenized payment methods.
	 *
	 * @since 4.0.0
	 *
	 * @param string $token unused
	 * @param string $customer_id unused
	 */
	public function remove_tokenized_payment_method( $token, $customer_id ) {
	}


	/**
	 * Updates a tokenized payment method.
	 *
	 * @since 4.4.0
	 *
	 * @param \WC_Order $order order object
	 */
	public function update_tokenized_payment_method( \WC_Order $order ) {
	}


	/**
	 * Determines if this API supports updating tokenized payment methods.
	 *
	 * @since 4.4.0
	 */
	public function supports_update_tokenized_payment_method() {

		return false;
	}


	/** Getters ***************************************************************/


	/**
	 * Instantiates and return the proper request object.
	 *
	 * @since 4.0.0
	 *
	 * @param array $args
	 * @return \WC_First_Data_Global_Gateway_API_Request
	 * @throws Framework\SV_WC_API_Exception
	 */
	protected function get_new_request( $args = [] ) {

		// sanity check
		if ( empty( $args['type'] ) || empty( $args['order'] ) || ! $args['order'] instanceof \WC_Order ) {
			throw new Framework\SV_WC_API_Exception( 'Request type is missing, or order is missing/invalid' );
		}

		$this->set_response_handler( 'WC_First_Data_Global_Gateway_API_Transaction_Response' );

		return new \WC_First_Data_Global_Gateway_API_Transaction_Request( $args['order'], $this->get_gateway()->get_store_number() );
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
	 * @return \WC_Gateway_First_Data_Global_Gateway
	 */
	protected function get_gateway() {

		return $this->gateway;
	}


}
