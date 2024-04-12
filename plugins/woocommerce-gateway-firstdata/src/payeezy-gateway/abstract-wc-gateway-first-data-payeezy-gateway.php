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
 * Payeezy Gateway Abstract Class
 *
 * Handles functionality common to both credit card and eCheck classes.
 *
 * @since 4.0.0
 */
abstract class WC_Gateway_First_Data_Payeezy_Gateway extends Framework\SV_WC_Payment_Gateway_Direct {


	/** demo environment ID */
	const ENVIRONMENT_DEMO = 'demo';

	/** @var string gateway ID */
	protected $gateway_id;

	/** @var string gateway password */
	protected $gateway_password;

	/** @var string API access key ID */
	protected $key_id;

	/** @var string API access HMAC key */
	protected $hmac_key;

	/** @var string gateway ID */
	protected $demo_gateway_id;

	/** @var string gateway password */
	protected $demo_gateway_password;

	/** @var string API access key ID */
	protected $demo_key_id;

	/** @var string API access HMAC key */
	protected $demo_hmac_key;

	/** @var \WC_First_Data_Payeezy_Gateway_API instance */
	protected $api;

	/** @var array shared settings names */
	protected $shared_settings_names = [ 'gateway_id', 'gateway_password', 'key_id', 'hmac_key', 'demo_gateway_id', 'demo_gateway_password', 'demo_key_id', 'demo_hmac_key' ];


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
			'gateway_id' => [
				'title'    => __( 'Gateway ID', 'woocommerce-gateway-firstdata' ),
				'type'     => 'text',
				'class'    => 'environment-field production-field',
				'desc_tip' => __( 'Your unique Gateway ID, note this is not the same as your account user name.', 'woocommerce-gateway-firstdata' ),
			],

			'gateway_password' => [
				'title'    => __( 'Gateway Password', 'woocommerce-gateway-firstdata' ),
				'type'     => 'password',
				'class'    => 'environment-field production-field',
				'desc_tip' => __( 'Your private gateway password, note this is not the same as your account user password.', 'woocommerce-gateway-firstdata' ),
			],

			'key_id' => [
				'title'    => __( 'Key id', 'woocommerce-gateway-firstdata' ),
				'type'     => 'text',
				'class'    => 'environment-field production-field',
				'desc_tip' => __( 'Your terminal API Access Key id.', 'woocommerce-gateway-firstdata' ),
			],

			'hmac_key'        => [
				'title'    => __( 'HMAC Key', 'woocommerce-gateway-firstdata' ),
				'type'     => 'password',
				'class'    => 'environment-field production-field',
				'desc_tip' => __( 'Your terminal API secret HMAC key.', 'woocommerce-gateway-firstdata' ),
			],

			// demo
			'demo_gateway_id' => [
				'title'    => __( 'Demo Gateway ID', 'woocommerce-gateway-firstdata' ),
				'type'     => 'text',
				'class'    => 'environment-field demo-field',
				'desc_tip' => __( 'Your demo unique Gateway ID, note this is not the same as your account user name and will be in the format of Axxxxx-xx.', 'woocommerce-gateway-firstdata' ),
			],

			'demo_gateway_password' => [
				'title'    => __( 'Demo Gateway Password', 'woocommerce-gateway-firstdata' ),
				'type'     => 'password',
				'class'    => 'environment-field demo-field',
				'desc_tip' => __( 'Your demo private gateway password, note this is not the same as your account user password.', 'woocommerce-gateway-firstdata' ),
			],

			'demo_key_id' => [
				'title'    => __( 'Demo Key id', 'woocommerce-gateway-firstdata' ),
				'type'     => 'text',
				'class'    => 'environment-field demo-field',
				'desc_tip' => __( 'Your demo terminal API Access Key id.', 'woocommerce-gateway-firstdata' ),
			],

			'demo_hmac_key' => [
				'title'    => __( 'Demo HMAC Key', 'woocommerce-gateway-firstdata' ),
				'type'     => 'password',
				'class'    => 'environment-field demo-field',
				'desc_tip' => __( 'Your demo terminal API secret HMAC key.', 'woocommerce-gateway-firstdata' ),
			],
		];
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
		if ( ! $this->get_gateway_id() || ! $this->get_gateway_password() || ! $this->get_key_id() || ! $this->get_hmac_key() ) {
			$is_configured = false;
		}

		return $is_configured;
	}


	/**
	 * Get the API class instance
	 *
	 * @since 3.0.0
	 * @see Framework\SV_WC_Payment_Gateway::get_api()
	 * @return \WC_First_Data_Payeezy_Gateway_API instance
	 */
	public function get_api() {

		if ( is_object( $this->api ) ) {
			return $this->api;
		}

		$path = wc_first_data()->get_plugin_path() . '/src/payeezy-gateway/api';

		// base classes
		require_once( $path . '/class-wc-first-data-payeezy-gateway-api.php' );

		// requests
		require_once( $path . '/requests/abstract-wc-first-data-payeezy-gateway-api-request.php' );
		require_once( $path . '/requests/class-wc-first-data-payeezy-gateway-api-transaction-request.php' );

		// responses
		require_once( $path . '/responses/abstract-wc-first-data-payeezy-gateway-api-response.php' );
		require_once( $path . '/responses/class-wc-first-data-payeezy-gateway-api-transaction-response.php' );

		return $this->api = new \WC_First_Data_Payeezy_Gateway_API( $this );
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
			return self::ENVIRONMENT_DEMO === $environment_id;
		}

		// otherwise default to checking the current environment
		return $this->is_environment( self::ENVIRONMENT_DEMO );
	}


	/**
	 * Returns the gateway ID based on the current environment
	 *
	 * @since 4.0.0
	 * @param string $environment_id optional, defaults to production
	 * @return string gateway ID
	 */
	public function get_gateway_id( $environment_id = null ) {

		if ( is_null( $environment_id ) ) {
			$environment_id = $this->get_environment();
		}

		return self::ENVIRONMENT_PRODUCTION === $environment_id ? $this->gateway_id : $this->demo_gateway_id;
	}


	/**
	 * Returns the gateway password based on the current environment
	 *
	 * @since 4.0.0
	 * @param string $environment_id optional, defaults to production
	 * @return string gateway password
	 */
	public function get_gateway_password( $environment_id = null) {

		if ( is_null( $environment_id ) ) {
			$environment_id = $this->get_environment();
		}

		return self::ENVIRONMENT_PRODUCTION === $environment_id ? $this->gateway_password : $this->demo_gateway_password;
	}


	/**
	 * Returns the key id based on the current environment
	 *
	 * @since 4.0.0
	 * @param string $environment_id optional, defaults to production
	 * @return string key id
	 */
	public function get_key_id( $environment_id = null ) {

		if ( is_null( $environment_id ) ) {
			$environment_id = $this->get_environment();
		}

		return self::ENVIRONMENT_PRODUCTION === $environment_id ? $this->key_id : $this->demo_key_id;
	}


	/**
	 * Returns the HMAC key based on the current environment
	 *
	 * @since 4.0.0
	 * @param string $environment_id optional, defaults to production
	 * @return string HMAC key
	 */
	public function get_hmac_key( $environment_id = null ) {

		if ( is_null( $environment_id ) ) {
			$environment_id = $this->get_environment();
		}

		return self::ENVIRONMENT_PRODUCTION === $environment_id ? $this->hmac_key : $this->demo_hmac_key;
	}


	/**
	 * Return an array of valid Payeezy Gateway environments
	 *
	 * @since 4.0.0
	 * @return array
	 */
	protected function get_payeezy_gateway_environments() {

		return [ self::ENVIRONMENT_PRODUCTION => __( 'Production', 'woocommerce-gateway-firstdata' ), self::ENVIRONMENT_DEMO => __( 'Demo', 'woocommerce-gateway-firstdata' ) ];
	}


	/**
	 * Initializes the payment form handler.
	 *
	 * @since 4.7.3
	 *
	 * @return \Atreus\WooCommerce\First_Data\Payeezy_Gateway\Payment_Form
	 */
	protected function init_payment_form_instance() {

		return new \Atreus\WooCommerce\First_Data\Payeezy_Gateway\Payment_Form( $this );
	}


}
