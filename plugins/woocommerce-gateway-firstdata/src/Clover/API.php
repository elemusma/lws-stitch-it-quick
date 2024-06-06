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

namespace Kestrel\WooCommerce\First_Data\Clover;

use SkyVerge\WooCommerce\PluginFramework\v5_12_4 as Framework;

defined( 'ABSPATH' ) or exit;

/**
 * The base Clover API class.
 *
 * @since 5.0.0
 */
class API extends Framework\SV_WC_API_Base implements Framework\SV_WC_Payment_Gateway_API {


	/** @var string production platform API domain */
	const PRODUCTION_PLATFORM_API_DOMAIN = 'api.clover.com';

	/** @var string sandbox platform API domain */
	const SANDBOX_PLATFORM_API_DOMAIN = 'sandbox.dev.clover.com';

	/** @var string production ecommerce service API domain */
	const PRODUCTION_ECOMMERCE_SERVICE_API_DOMAIN = 'scl.clover.com';

	/** @var string sandbox ecommerce service API domain */
	const SANDBOX_ECOMMERCE_SERVICE_API_DOMAIN = 'scl-sandbox.dev.clover.com';

	/** @var string gateway ID */
	protected $gateway_id;

	/** @var string merchant ID */
	protected $merchant_id;

	/** @var string private token */
	protected $private_token;

	/** @var string environment */
	protected $environment;

	/** @var int current user id, if any */
	protected $current_user_id;

	/** @var \WC_Order WooCommerce order object */
	protected $order;


	/**
	 * Constructs the class.
	 *
	 * @since 5.0.0
	 *
	 * @param string $merchant_id merchant ID
	 * @param string $private_token private token
	 * @param string $environment current API environment, either `production` or `test
	 * @param string $gateway_id current gateway ID
	 * @param int    $current_user_id the id of the currently logged in user (if any)
	 */
	public function __construct( string $merchant_id, string $private_token, string $environment, string $gateway_id, int $current_user_id ) {

		$this->merchant_id     = $merchant_id;
		$this->private_token   = $private_token;
		$this->environment     = $environment;
		$this->gateway_id      = $gateway_id;
		$this->current_user_id = $current_user_id;

		$this->set_request_content_type_header( 'application/json' );
		$this->set_request_accept_header( 'application/json' );
	}


	/**
	 * Override this method to set the authorization header, but late enough such
	 * that the $order is available, in case actors want to change the credentials on a per-order basis (e.g. multi-currency)
	 *
	 * TODO: it would be nice to have a do_pre_request() stub method in the framework class to override when needed,
	 * rather than abusing this one {MR 2022-09-13}
	 *
	 * @since 5.0.0
	 */
	protected function reset_response() {

		$credentials = [
			'merchant_id'   => $this->merchant_id,
			'private_token' => $this->private_token,
		];

		/**
		 * Filters the Clover merchant ID / private token.
		 *
		 * @since 5.0.0
		 *
		 * @param array $credentials
		 * @param \API $this API instance
		 */
		$credentials = apply_filters( 'wc_first_data_clover_credit_card_api_credentials', $credentials, $this );

		$this->set_request_header( 'authorization', 'Bearer ' . $credentials['private_token'] );

		parent::reset_response();
	}


	/**
	 * Performs a credit card charge.
	 *
	 * @param \WC_Order $order WooCommerce order object
	 *
	 * @return API\Response\Response|object|Framework\SV_WC_API_Request|Framework\SV_WC_API_Response
	 * @throws Framework\SV_WC_API_Exception
	 * @since 5.0.0
	 *
	 */
	public function credit_card_charge( \WC_Order $order ) {

		$this->order = $order;

		// required for charges
		$this->set_idempotency_key( $order );

		$request = $this->get_new_request( 'charge' );

		$request->set_charge_data( $order, $this->is_customer_initiated( $order ) );

		return $this->perform_request( $request );
	}


	/**
	 * Performs a credit card authorization.
	 *
	 * @param \WC_Order $order WooCommerce order object
	 *
	 * @return API\Response\Response|object|Framework\SV_WC_API_Request|Framework\SV_WC_API_Response
	 * @throws Framework\SV_WC_API_Exception
	 * @since 5.0.0
	 *
	 */
	public function credit_card_authorization( \WC_Order $order ) {

		$this->order = $order;

		// required for charges
		$this->set_idempotency_key( $order );

		$request = $this->get_new_request( 'charge' );

		$request->set_auth_data( $order, $this->is_customer_initiated( $order ) );

		return $this->perform_request( $request );
	}


	/**
	 * Captures an authorized credit card payment.
	 *
	 * @param \WC_Order $order order object
	 *
	 * @return API\Response\Response|object|Framework\SV_WC_API_Request|Framework\SV_WC_API_Response
	 * @throws Framework\SV_WC_API_Exception
	 * @since 5.0.0
	 *
	 */
	public function credit_card_capture( \WC_Order $order ) {

		$this->order = $order;

		$request = $this->get_new_request( 'capture' );

		$request->set_capture_data( $order );

		return $this->perform_request( $request );
	}


	/**
	 * Refunds a transaction.
	 *
	 * @param \WC_Order $order order object
	 *
	 * @return API\Response\Response|object|Framework\SV_WC_API_Request|Framework\SV_WC_API_Response
	 * @throws Framework\SV_WC_API_Exception
	 * @since 5.0.0
	 *
	 */
	public function refund( \WC_Order $order ) {

		$this->order = $order;

		$request = $this->get_new_request( 'refund' );

		$request->set_refund_data( $order );

		return $this->perform_request( $request );
	}


	/**
	 * Voids a transaction.
	 *
	 * @param \WC_Order $order order object
	 *
	 * @return API\Response\Response|object|Framework\SV_WC_API_Request|Framework\SV_WC_API_Response
	 * @throws Framework\SV_WC_API_Exception
	 * @since 5.0.0
	 */
	public function void( \WC_Order $order ) {

		$this->order = $order;

		$request = $this->get_new_request( 'void' );

		$request->set_void_data( $order );

		return $this->perform_request( $request );
	}


	/**
	 * Tokenize the credit card.
	 *
	 * @param \WC_Order $order order object
	 *
	 * @since 5.0.0
	 * @see Framework\SV_WC_Payment_Gateway_API::tokenize_payment_method()
	 * @return API\Response\Response|object|Framework\SV_WC_API_Request|Framework\SV_WC_API_Response
	 * @throws Framework\SV_WC_API_Exception
	 */
	public function tokenize_payment_method( \WC_Order $order ) {

		$this->order = $order;
		$request = $this->get_new_request( 'customer' );

		// I guess this is the right way to do this? {JS: 2022-11-20}
		if ( $order->customer_id && strpos( $order->customer_id, 'wc-' ) !== 0 ) {

			// if the customer_id does not start with 'wc-', i.e. it's a Clover customer id, add the new saved card
			$request->update_customer_with_saved_card( $order );

		} else {

			// new customer, create customer with saved card
			$request->create_customer_with_saved_card( $order );
		}

		$response = $this->perform_request( $request );

		if ( ! $response->transaction_approved() ) {
			return $response;
		} else {
			// pass the payment token information in from the checkout page POST data
			return $response->set_payment_token( $this->build_payment_token( $order, $response->get_card_id() ) );
		}
	}


	/**
	 * Remove a saved card from a customer
	 *
	 * @since 5.0.0
	 *
	 * @param PaymentToken $token payment method token
	 * @param string $customer_id unique customer ID
	 * @return API\Response\Response|object|Framework\SV_WC_API_Request|Framework\SV_WC_API_Response
	 * @throws Framework\SV_WC_API_Exception
	 */
	public function remove_tokenized_payment_method( $token, $customer_id ) {

		$request = $this->get_new_request( 'customer' );

		$request->revoke_card( $token->get_card_id(), $customer_id );

		return $this->perform_request( $request );
	}


	/** Validation methods ****************************************************/


	/**
	 * Handle "400 Bad Request" errors. Note that "402 Payment Required" errors are handled
	 * within the request class as it represents a transaction decline rather than a transaction error.
	 *
	 * @since 5.0.0
	 * @throws Framework\SV_WC_Payment_Gateway_Exception
	 */
	protected function do_post_parse_response_validation() {

		if ( 400 === (int) $this->get_response_code() ) {

			if ( ! empty( $this->response->response_data->error ) ) {

				$error_message = sprintf( '%1$s (%2$s): %3$s',
					$this->response->message, $this->response->error->code, $this->response->error->message );

			} else {
				$error_message = $this->response->message;
			}

			throw new Framework\SV_WC_Payment_Gateway_Exception( $error_message );
		}
	}


	/** Helper methods ********************************************************/


	/**
	 * Is this a customer initiated transaction?
	 *
	 * @since 5.0.0
	 *
	 * @param \WC_Order $order WooCommerce order object
	 * @return bool true if customer initiated, false otherwise (merchant initiated)
	 */
	protected function is_customer_initiated( \WC_Order $order ) : bool {
		return $order->get_user_id() == $this->current_user_id;
	}


	/**
	 * Return the payment token from the order payment information.
	 *
	 * @since 5.0.0
	 *
	 * @param \WC_Order $order the order
	 * @param string $card_id the Clover card id
	 * @return PaymentToken
	 */
	protected function build_payment_token( \WC_Order $order, string $card_id ) : Framework\SV_WC_Payment_Gateway_Payment_Token {

		$data = [
			'default'   => true,
			'type'      => 'credit_card',
			'card_type' => $order->payment->card_type,
			'last_four' => $order->payment->last_four,
			'exp_month' => $order->payment->exp_month,
			'exp_year'  => $order->payment->exp_year,
			'card_id'   => $card_id,
		];

		return new PaymentToken( $order->payment->js_token, $data );
	}


	/**
	 * Builds and returns a new API request object.
	 *
	 * @param string $type request type to get
	 *
	 * @return API\Request\Request
	 * @throws Framework\SV_WC_API_Exception
	 * @since 5.0.0
	 */
	protected function get_new_request( $type = '' ) {

		switch ( $type ) {

			case 'charge':
				$request_handler  = "\Kestrel\WooCommerce\First_Data\Clover\API\Request\Charge";
				$this->set_response_handler( "\Kestrel\WooCommerce\First_Data\Clover\API\Response\Charge" );
				$this->request_uri = $this->get_api_request_domain( 'ecommerce' );
				break;

			case 'capture':
				$request_handler = "\Kestrel\WooCommerce\First_Data\Clover\API\Request\Capture";
				$this->set_response_handler( "\Kestrel\WooCommerce\First_Data\Clover\API\Response\Charge" );
				$this->request_uri = $this->get_api_request_domain( 'ecommerce' );
				break;

			case 'refund':
				$request_handler = "\Kestrel\WooCommerce\First_Data\Clover\API\Request\Refund";
				$this->set_response_handler( "\Kestrel\WooCommerce\First_Data\Clover\API\Response\Refund" );
				$this->request_uri = $this->get_api_request_domain( 'ecommerce' );
				break;

			case 'customer':
				$request_handler = "\Kestrel\WooCommerce\First_Data\Clover\API\Request\Customer";
				$this->set_response_handler( "\Kestrel\WooCommerce\First_Data\Clover\API\Response\Customer" );
				$this->request_uri = $this->get_api_request_domain( 'ecommerce' );
				break;

			default:
				throw new Framework\SV_WC_API_Exception( 'Invalid request type' );
		}



		return new $request_handler;
	}


	/**
	 * Return the API request domain, which varies based on the type of request.
	 *
	 * @since 5.0.0
	 *
	 * @param string $type either ecommerce or platform, each has their own domain
	 *
	 * @return string
	 */
	protected function get_api_request_domain( string $type ) : string {

		if ( 'ecommerce' === $type ) {

			$domain = $this->environment === Framework\SV_WC_Payment_Gateway::ENVIRONMENT_PRODUCTION ? self::PRODUCTION_ECOMMERCE_SERVICE_API_DOMAIN : self::SANDBOX_ECOMMERCE_SERVICE_API_DOMAIN;

		} elseif ( 'platform' === $type ) {

			$domain = $this->environment === Framework\SV_WC_Payment_Gateway::ENVIRONMENT_PRODUCTION ? self::PRODUCTION_PLATFORM_API_DOMAIN : self::SANDBOX_PLATFORM_API_DOMAIN;
		}

		return "https://{$domain}/";
	}


	/**
	 * Set the idempotency key request header
	 *
	 * @since 5.0.0
	 *
	 * @param WC_Order $order
	 */
	protected function set_idempotency_key( \WC_Order $order ) {

		$this->set_request_header( 'idempotency-key', $order->payment->idempotency_key );
	}


	/**
	 * Return the parsed response object for the request
	 *
	 * Overridden because the response code is needed for parsing
	 *
	 * @since 5.0.0
	 * @param string $raw_response_body
	 * @return API\Response\Response class instance which implements SV_WC_API_Request
	 */
	protected function get_parsed_response( $raw_response_body ) {

		$handler_class = $this->get_response_handler();

		return new $handler_class( $raw_response_body, $this->get_response_code() );
	}


	/**
	 * Gets the order associated with the request.
	 *
	 * @since 5.0.0
	 *
	 * @return \WC_Order|null
	 */
	public function get_order() {

		return $this->order;
	}


	/**
	 * Return the API ID, mainly used for namespacing actions
	 *
	 * @since 5.0.0
	 * @return string
	 */
	protected function get_api_id() : string {
		return $this->gateway_id;
	}


	/**
	 * Gets the main plugin instance.
	 *
	 * @see Framework\SV_WC_API_Base::get_plugin()
	 *
	 * @since 5.0.0
	 *
	 * @return \WC_First_Data
	 */
	protected function get_plugin() {

		return wc_first_data();
	}


	/**
	 * Clover supports removing previously tokenized payment methods.
	 *
	 * @see Framework\SV_WC_Payment_Gateway_API::supports_remove_tokenized_payment_method()
	 *
	 * @since 5.0.0
	 *
	 * @return false
	 */
	public function supports_remove_tokenized_payment_method() : bool {

		return true;
	}


	/* No-op methods **********************************************************/


	/**
	 * No-op: we do not currently support echeck transactions.
	 *
	 * @since 5.0.0
	 *
	 * @param \WC_Order $order order object
	 */
	public function check_debit( \WC_Order $order ) { }


	/**
	 * No-op: Clover doesn't support updating previously tokenized payment methods.
	 *
	 * @since 5.0.0
	 *
	 * @param \WC_Order $order order object
	 */
	public function update_tokenized_payment_method( \WC_Order $order ) { }


	/**
	 * No-op: Clover doesn't support getting previously tokenized payment methods.
	 *
	 * @since 5.0.0
	 *
	 * @param string $customer_id unique customer ID
	 */
	public function get_tokenized_payment_methods( $customer_id ) { }


	/**
	 * Clover doesn't support getting previously tokenized payment methods.
	 *
	 * @see Framework\SV_WC_Payment_Gateway_API::supports_get_tokenized_payment_methods()
	 *
	 * @since 5.0.0
	 *
	 * @return false
	 */
	public function supports_get_tokenized_payment_methods() : bool {

		return false;
	}


	/**
	 * Clover doesn't support updating previously tokenized payment methods.
	 *
	 * @see Framework\SV_WC_Payment_Gateway_API::supports_update_tokenized_payment_method()
	 *
	 * @since 5.0.0
	 *
	 * @return false
	 */
	public function supports_update_tokenized_payment_method() : bool {

		return false;
	}


}
