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
use Atreus\WooCommerce\First_Data\Payeezy\API\PaymentJS;

/**
 * Payeezy Abstract Class
 *
 * Handles functionality common to both credit card and eCheck classes.
 *
 * @since 4.0.0
 */
abstract class WC_Gateway_First_Data_Payeezy extends Framework\SV_WC_Payment_Gateway_Direct {


	/** sandbox environment ID */
	const ENVIRONMENT_SANDBOX = 'sandbox';

	/** @var string production API key */
	protected $api_key;

	/** @var string production API secret */
	protected $api_secret;

	/** @var string sandbox merchant token */
	protected $merchant_token;

	/** @var string sandbox API key */
	protected $sandbox_api_key;

	/** @var string sandbox API secret */
	protected $sandbox_api_secret;

	/** @var string production merchant token */
	protected $sandbox_merchant_token;

	/** @var \WC_First_Data_Payeezy_API instance */
	protected $api;

	/** @var PaymentJS API instance */
	protected $payment_js_api;

	/** @var array shared settings names */
	protected $shared_settings_names = [ 'api_key', 'api_secret', 'merchant_token', 'sandbox_api_key', 'sandbox_api_secret', 'sandbox_merchant_token' ];


	/**
	 * Returns an array of form fields used for both credit cards and eChecks
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_Payment_Gateway::get_method_form_fields()
	 * @return array of form fields
	 */
	protected function get_method_form_fields() {

		return [

			// production
			'api_key' => [
				'title'    => __( 'API Key', 'woocommerce-gateway-firstdata' ),
				'type'     => 'text',
				'class'    => 'environment-field production-field',
				'desc_tip' => __( 'Your app API Key.', 'woocommerce-gateway-firstdata' ),
			],

			'api_secret' => [
				'title'    => __( 'API Secret', 'woocommerce-gateway-firstdata' ),
				'type'     => 'password',
				'class'    => 'environment-field production-field',
				'desc_tip' => __( 'Your app API Secret Key', 'woocommerce-gateway-firstdata' ),
			],

			'merchant_token'  => [
				'title'    => __( 'Merchant Token', 'woocommerce-gateway-firstdata' ),
				'type'     => 'text',
				'class'    => 'environment-field production-field',
				'desc_tip' => __( 'Your merchant token.', 'woocommerce-gateway-firstdata' ),
			],

			// sandbox
			'sandbox_api_key' => [
				'title'    => __( 'Sandbox API Key', 'woocommerce-gateway-firstdata' ),
				'type'     => 'text',
				'class'    => 'environment-field sandbox-field',
				'desc_tip' => __( 'Your sandbox app API Key.', 'woocommerce-gateway-firstdata' ),
			],

			'sandbox_api_secret' => [
				'title'    => __( 'Sandbox API Secret', 'woocommerce-gateway-firstdata' ),
				'type'     => 'password',
				'class'    => 'environment-field sandbox-field',
				'desc_tip' => __( 'Your sandbox app API Secret Key', 'woocommerce-gateway-firstdata' ),
			],

			'sandbox_merchant_token' => [
				'title'    => __( 'Sandbox Merchant Token', 'woocommerce-gateway-firstdata' ),
				'type'     => 'text',
				'class'    => 'environment-field sandbox-field',
				'desc_tip' => __( 'Your sandbox merchant token.', 'woocommerce-gateway-firstdata' ),
			],
		];
	}


	/** Gateway methods *******************************************************/


	/**
	 * Adds gateway-specific transaction data to the order, for both credit cards
	 * and eChecks, this is:
	 *
	 * + transaction_tag - an additional transaction identifier (like transaction_id)
	 * + correlation_id - internal Payeezy identifier for the request
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_Payment_Gateway_Direct::add_transaction_data()
	 * @param \WC_Order $order the order object
	 * @param \WC_First_Data_Payeezy_API_Transaction_Response $response transaction response
	 */
	public function add_payment_gateway_transaction_data( $order, $response ) {

		// transaction tag
		if ( $response->get_transaction_tag() ) {
			$this->update_order_meta( $order, 'transaction_tag', $response->get_transaction_tag() );
		}

		// correlation ID
		if ( $response->get_correlation_id() ) {
			$this->update_order_meta( $order, 'correlation_id', $response->get_correlation_id() );
		}
	}


	/** Getters ***************************************************************/


	/**
	 * Determines whether the gateway is properly configured to perform transactions.
	 *
	 * @see Framework\SV_WC_Payment_Gateway::is_configured()
	 *
	 * @since 4.0.0
	 *
	 * @return bool
	 */
	public function is_configured() {

		$is_configured = parent::is_configured();

		// missing configuration
		if ( ! $this->get_api_key() || ! $this->get_api_secret() || ! $this->get_merchant_token() ) {
			$is_configured = false;
		}

		return $is_configured;
	}


	/**
	 * Get the API class instance
	 *
	 * @since 3.0.0
	 * @see Framework\SV_WC_Payment_Gateway::get_api()
	 * @return \WC_First_Data_Payeezy_API instance
	 */
	public function get_api() {

		if ( is_object( $this->api ) ) {
			return $this->api;
		}

		$path = wc_first_data()->get_plugin_path() . '/src/payeezy/api';

		// base classes
		require_once( $path . '/class-wc-first-data-payeezy-api.php' );

		// requests
		require_once( $path . '/requests/abstract-wc-first-data-payeezy-api-request.php' );
		require_once( $path . '/requests/class-wc-first-data-payeezy-api-credit-card-transaction-request.php' );
		require_once( $path . '/requests/class-wc-first-data-payeezy-api-echeck-transaction-request.php' );

		// responses
		require_once( $path . '/responses/abstract-wc-first-data-payeezy-api-response.php' );
		require_once( $path . '/responses/class-wc-first-data-payeezy-api-transaction-response.php' );
		require_once( $path . '/responses/class-wc-first-data-payeezy-api-tokenize-credit-card-response.php' );

		return $this->api = new \WC_First_Data_Payeezy_API( $this );
	}


	/**
	 * Gets the Payment.JS API instance.
	 *
	 * @since 4.7.0
	 *
	 * @return \Atreus\WooCommerce\First_Data\Payeezy\API\PaymentJS
	 */
	public function get_payment_js_api() {

		if ( is_object( $this->payment_js_api ) ) {
			return $this->payment_js_api;
		}

		$path = wc_first_data()->get_plugin_path() . '/src/payeezy/api';

		// base classes
		require_once( $path . '/PaymentJS.php' );

		// requests
		require_once( $path . '/requests/PaymentJS.php' );
		require_once( $path . '/requests/PaymentJS/Authorize_Session.php' );

		// responses
		require_once( $path . '/responses/PaymentJS.php' );
		require_once( $path . '/responses/PaymentJS/Authorize_Session.php' );

		return $this->payment_js_api = new \Atreus\WooCommerce\First_Data\Payeezy\API\PaymentJS( $this );
	}


	/**
	 * Returns true if the current gateway environment is configured to 'demo'
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_Payment_Gateway::is_test_environment()
	 * @param string $environment_id optional environment id to check, otherwise defaults to the gateway current environment
	 * @return boolean true if $environment_id (if non-null) or otherwise the current environment is test
	 */
	public function is_test_environment( $environment_id = null ) {

		// if an environment is passed in, check that
		if ( ! is_null( $environment_id ) ) {
			return self::ENVIRONMENT_SANDBOX === $environment_id;
		}

		// otherwise default to checking the current environment
		return $this->is_environment( self::ENVIRONMENT_SANDBOX );
	}


	/**
	 * Returns the API Key based on the current environment
	 *
	 * @since 4.0.0
	 * @param string $environment_id optional, defaults to production
	 * @return string API key
	 */
	public function get_api_key( $environment_id = null ) {

		if ( is_null( $environment_id ) ) {
			$environment_id = $this->get_environment();
		}

		return self::ENVIRONMENT_PRODUCTION === $environment_id ? $this->api_key : $this->sandbox_api_key;
	}


	/**
	 * Returns the API Secret based on the current environment
	 *
	 * @since 4.0.0
	 * @param string $environment_id optional, defaults to production
	 * @return string API Secret
	 */
	public function get_api_secret( $environment_id = null ) {

		if ( is_null( $environment_id ) ) {
			$environment_id = $this->get_environment();
		}

		return self::ENVIRONMENT_PRODUCTION === $environment_id ? $this->api_secret : $this->sandbox_api_secret;
	}


	/**
	 * Returns the merchant token based on the current environment
	 *
	 * @since 4.0.0
	 * @param string $environment_id optional, defaults to production
	 * @return string merchant token
	 */
	public function get_merchant_token( $environment_id = null ) {

		if ( is_null( $environment_id ) ) {
			$environment_id = $this->get_environment();
		}

		return self::ENVIRONMENT_PRODUCTION === $environment_id ? $this->merchant_token : $this->sandbox_merchant_token;
	}


	/**
	 * Return an array of valid Payeezy environments
	 *
	 * @since 4.0.0
	 * @return array
	 */
	protected function get_payeezy_environments() {

		return [ self::ENVIRONMENT_PRODUCTION => __( 'Production', 'woocommerce-gateway-firstdata' ), self::ENVIRONMENT_SANDBOX => __( 'Sandbox', 'woocommerce-gateway-firstdata' ) ];
	}


}
