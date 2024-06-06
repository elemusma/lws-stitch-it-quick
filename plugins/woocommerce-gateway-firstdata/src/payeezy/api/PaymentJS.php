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

namespace Kestrel\WooCommerce\First_Data\Payeezy\API;

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\PluginFramework\v5_12_4 as Framework;
use Kestrel\WooCommerce\First_Data\Payeezy\API\Request\PaymentJS as Request;
use Kestrel\WooCommerce\First_Data\Payeezy\API\Response\PaymentJS as Response;

/**
 * Payeezy Payment.JS API Class.
 *
 * Handles API calls to the Payeezy Payment.JS REST API.
 *
 * @link https://docs.paymentjs.firstdata.com/
 *
 * @since 4.7.0
 */
class PaymentJS extends Framework\SV_WC_API_Base {


	/** @var string production API base URL */
	const PRODUCTION_URL = 'https://prod.api.firstdata.com/paymentjs/v2/';

	/** @var string sandbox API base URL */
	const SANDBOX_URL = 'https://cert.api.firstdata.com/paymentjs/v2/';


	/** @var \WC_Gateway_First_Data_Payeezy_Credit_Card gateway instance */
	private $gateway;


	/**
	 * Setup request object and set endpoint
	 *
	 * @since 4.0.0
	 *
	 * @param \WC_Gateway_First_Data_Payeezy_Credit_Card $gateway
	 */
	public function __construct( $gateway ) {

		$this->gateway     = $gateway;
		$this->request_uri = $gateway->is_production_environment() ? self::PRODUCTION_URL : self::SANDBOX_URL;

		$this->set_request_accept_header( 'application/json' );
	}


	/**
	 * Gets the gateway instance.
	 *
	 * @since 4.7.0
	 *
	 * @return \WC_Gateway_First_Data_Payeezy_Credit_Card
	 */
	public function get_gateway() {

		return $this->gateway;
	}


	/**
	 * Gets the main plugin instance.
	 *
	 * @since 4.7.0
	 *
	 * @return \WC_First_Data
	 */
	public function get_plugin() {

		return $this->get_gateway()->get_plugin();
	}


	/**
	 * Calculates the HMAC hash and set the required headers for the request.
	 *
	 * @link https://docs.paymentjs.firstdata.com/#authorize-session
	 * @link https://docs.paymentjs.firstdata.com/#integratoin-example for a PHP example
	 * In particular {@link https://github.com/GBSEcom/paymentJS_php_integration/blob/master/auth.php} for the authorization request
	 *
	 * @since 4.7.0
	 *
	 * @param Request $request payment request object
	 */
	protected function set_credential_headers( $request ) {

		$gateway    = $this->get_gateway();
		$api_key    = $gateway->get_api_key();
		$api_secret = $gateway->get_payment_js_secret();
		$nonce      = wp_rand();
		$payload    = $request->to_string();
		$timestamp  = (int) microtime( true ) * 1000; // in microseconds

		// calculate authorization HMAC hash
		$hmac_hash = base64_encode( hash_hmac( 'sha256', ( $api_key . $nonce . $timestamp . $payload ), $api_secret, false ) );

		$this->set_request_headers( [
			'Api-Key'           => $api_key,
			'Content-Type'      => 'application/json',
			'Content-Length'    => strlen( $payload ),
			'Message-Signature' => $hmac_hash,
			'Nonce'             => $nonce,
			'Timestamp'         => $timestamp,
		] );
	}


	/**
	 * Instantiates and returns the proper request object.
	 *
	 * @since 4.7.0
	 *
	 * @param array $args new request arguments
	 * @return Request payment rquest object
	 * @throws Framework\SV_WC_API_Exception
	 */
	protected function get_new_request( $args = [] ) {

		if ( isset( $args['type'] ) && 'authorize_session' === $args['type'] ) {

			$this->set_response_handler( \Kestrel\WooCommerce\First_Data\Payeezy\API\Response\PaymentJS\Authorize_Session::class );

			return new Request\Authorize_Session();
		}

		throw new Framework\SV_WC_API_Exception( 'Invalid request type.' );
	}


	/**
	 * Authorizes a session with Payment.JS.
	 *
	 * @since 4.7.0
	 *
	 * @return Response\Authorize_Session
	 * @throws Framework\SV_WC_API_Exception
	 */
	public function authorize_session() {

		/** @var Request\Authorize_Session $request */
		$request = $this->get_new_request( [ 'type' => __FUNCTION__ ] );

		$request->authorize_session();

		/** @var Response\Authorize_Session $response */
		$response = $this->perform_request( $request );
		$headers  = $this->get_response_headers( false );

		// set the header values
		$response->set_client_token( ! empty( $headers['client-token'] ) ? (string) $headers['client-token'] : '' );
		$response->set_nonce( ! empty( $headers['nonce'] ) ? (string) $headers['nonce'] : '' );

		return $response;
	}


	/**
	 * Performs a request to Payment.JS API.
	 *
	 * @since 4.7.0
	 *
	 * @param Request $request payment request object
	 * @return Response payment response object
	 * @throws Framework\SV_WC_API_Exception
	 */
	protected function perform_request( $request ) {

		$this->set_credential_headers( $request );

		return parent::perform_request( $request );
	}


	/**
	 * Handles response validation after the data has been parsed.
	 *
	 * @since 4.7.0
	 *
	 * @return bool
	 * @throws Framework\SV_WC_API_Exception
	 */
	protected function do_post_parse_response_validation() {

		$response = $this->get_response();

		if ( $response && $response->has_errors() ) {
			throw new Framework\SV_WC_API_Exception( $response->get_error() );
		}

		return true;
	}


	/**
	 * Gets the sanitized request headers, for logging.
	 *
	 * @since 4.7.0
	 *
	 * @return array
	 */
	protected function get_sanitized_request_headers() {

		$headers = $this->get_request_headers();

		foreach ( [ 'Api-Key', 'Message-Signature', 'Nonce' ] as $key ) {

			if ( ! empty( $headers[ $key ] ) ) {
				$headers[ $key ] = str_repeat( '*', strlen( $headers[ $key ] ) );
			}
		}

		return $headers;
	}


	/**
	 * Gets the response headers.
	 *
	 * @since 4.7.0
	 *
	 * @param bool $sanitize whether to sanitize sensitive information in the headers
	 * @return array
	 */
	protected function get_response_headers( $sanitize = true ) {

		$headers = parent::get_response_headers();

		if ( $sanitize ) {

			foreach ( [ 'client-token', 'nonce' ] as $key ) {

				if ( ! empty( $headers[ $key ] ) ) {
					$headers[ $key ] = str_repeat( '*', strlen( $headers[ $key ] ) );
				}
			}
		}

		return $headers;
	}


	/**
	 * Gets the API ID.
	 *
	 * @since 4.7.0
	 *
	 * @return string
	 */
	protected function get_api_id() {

		return $this->get_gateway()->get_id();
	}


}
