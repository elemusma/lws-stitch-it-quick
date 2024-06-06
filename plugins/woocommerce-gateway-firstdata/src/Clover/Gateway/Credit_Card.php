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

namespace Kestrel\WooCommerce\First_Data\Clover\Gateway;

use Kestrel\WooCommerce\First_Data\Clover\Payment_Tokens_Handler;
use SkyVerge\WooCommerce\PluginFramework\v5_12_4 as Framework;
use Kestrel\WooCommerce\First_Data\Clover\API;
use Kestrel\WooCommerce\First_Data\Clover\Blocks\Credit_Card_Checkout_Block_Integration;
use Kestrel\WooCommerce\First_Data\Clover\Blocks\Gateway_Blocks_Handler;

defined( 'ABSPATH' ) or exit;


/**
 * Clover Credit Card gateway
 *
 * @since 5.0.0
 */
#[\AllowDynamicProperties]
class Credit_Card extends Framework\SV_WC_Payment_Gateway_Direct {


	/** @var string merchant ID */
	protected $merchant_id;

	/** @var string sandbox merchant ID */
	protected $sandbox_merchant_id;

	/** @var string public token */
	protected $public_token;

	/** @var string sandbox public token */
	protected $sandbox_public_token;

	/** @var string private token */
	protected $private_token;

	/** @var string sandbox private token */
	protected $sandbox_private_token;

	/** @var string how to handle AVS street address field: 'hide', 'show', 'require' */
	protected $avs_street_address;

	/** @var API API handler instance */
	protected $api;

	/** @var Credit_Card_Checkout_Block_Integration|null $credit_card_checkout_block */
	protected ?Credit_Card_Checkout_Block_Integration $credit_card_checkout_block = null;


	/**
	 * Constructs the gateway.
	 *
	 * @since 5.0.0
	 */
	public function __construct() {

		add_filter( 'wc_payment_gateway_' . wc_first_data()->get_id() . '_javascript_url', array( $this, 'payment_form_javascript_url' ) );

		parent::__construct(
			\WC_First_Data::CLOVER_CREDIT_CARD_GATEWAY_ID,
			wc_first_data(),
			array(
				'method_title'       => __( 'Clover Credit Card', 'woocommerce-gateway-firstdata' ),
				'method_description' => __( 'Allow customers to securely pay using their credit cards with Clover.', 'woocommerce-gateway-firstdata' ),
				'supports'           => array(
					self::FEATURE_PRODUCTS,
					self::FEATURE_CARD_TYPES,
					self::FEATURE_PAYMENT_FORM,
					self::FEATURE_CREDIT_CARD_CHARGE,
					self::FEATURE_CREDIT_CARD_CHARGE_VIRTUAL,
					self::FEATURE_CREDIT_CARD_AUTHORIZATION,
					self::FEATURE_CREDIT_CARD_CAPTURE,
					self::FEATURE_CREDIT_CARD_PARTIAL_CAPTURE,
					self::FEATURE_DETAILED_CUSTOMER_DECLINE_MESSAGES,
					self::FEATURE_REFUNDS,
					self::FEATURE_TOKENIZATION,
					self::FEATURE_CUSTOMER_ID,
					self::FEATURE_ADD_PAYMENT_METHOD,
					self::FEATURE_TOKEN_EDITOR,
				),
				'environments' => array(
					self::ENVIRONMENT_PRODUCTION => esc_html_x( 'Production', 'software environment', 'woocommerce-gateway-firstdata' ),
					self::ENVIRONMENT_TEST       => esc_html_x( 'Sandbox', 'software environment', 'woocommerce-gateway-firstdata' ),
				),
				'payment_type' => self::PAYMENT_TYPE_CREDIT_CARD,
			)
		);
	}


	/**
	 * Gets the checkout block integration instance.
	 *
	 * @since 5.2.0
	 *
	 * @return Credit_Card_Checkout_Block_Integration
	 */
	public function get_checkout_block_integration_instance(): ?Framework\Payment_Gateway\Blocks\Gateway_Checkout_Block_Integration {

		if ( null === $this->credit_card_checkout_block ) {

			require_once( $this->get_plugin()->get_plugin_path() . '/src/Clover/Blocks/Credit_Card_Checkout_Block_Integration.php' );

			$this->credit_card_checkout_block = new Credit_Card_Checkout_Block_Integration( $this->get_plugin(), $this );
		}

		return $this->credit_card_checkout_block;
	}


	/**
	 * Load non-minified JS if SCRIPT_DEBUG is enabled
	 *
	 * TODO: this should be a default part of the framework {JS: 2022-11-13}
	 *
	 * @since 5.0.0
	 *
	 * @param string $url javascript URL
	 * @return string javascript URL
	 */
	public function payment_form_javascript_url (string $url ) : string {

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$url = str_replace( '.min', '', $url );
		}

		return $url;
	}


	/**
	 * Gets the gateway form fields.
	 *
	 * @see Framework\SV_WC_Payment_Gateway::get_method_form_fields()
	 *
	 * @since 5.0.0
	 *
	 * @return array
	 */
	protected function get_method_form_fields() : array {

		// TODO: tooltips instead of the sub-field text?
		return [
			'merchant_id' => [
				'title'       => __( 'Merchant ID', 'woocommerce-gateway-firstdata' ),
				'description' => __( 'Your merchant ID, provided by Clover.', 'woocommerce-gateway-firstdata' ),
				'type'        => 'text',
				'class'       => 'environment-field production-field',
			],
			'sandbox_merchant_id' => [
				'title'       => __( 'Sandbox Merchant ID', 'woocommerce-gateway-firstdata' ),
				'description' => __( 'Your sandbox merchant ID, provided by Clover.', 'woocommerce-gateway-firstdata' ),
				'type'        => 'text',
				'class'       => 'environment-field test-field',
			],
			'public_token' => [
				'title'       => __( 'Public Token', 'woocommerce-gateway-firstdata' ),
				'description' => __( 'Your Ecommerce API public token from the Clover control panel.', 'woocommerce-gateway-firstdata' ),
				'type'        => 'text',
				'class'       => 'environment-field production-field',
			],
			'sandbox_public_token' => [
				'title'       => __( 'Sandbox Public Token', 'woocommerce-gateway-firstdata' ),
				'description' => __( 'Your Ecommerce API public token from the Clover sandbox control panel.', 'woocommerce-gateway-firstdata' ),
				'type'        => 'text',
				'class'       => 'environment-field test-field',
			],
			'private_token' => [
				'title'       => __( 'Private Token', 'woocommerce-gateway-firstdata' ),
				'description' => __( 'Your Ecommerce API private token from the Clover control panel.', 'woocommerce-gateway-firstdata' ),
				'type'        => 'password',
				'class'       => 'environment-field production-field',
			],
			'sandbox_private_token' => [
				'title'       => __( 'Sandbox Private Token', 'woocommerce-gateway-firstdata' ),
				'description' => __( 'Your Ecommerce API private token from the sandbox Clover control panel.', 'woocommerce-gateway-firstdata' ),
				'type'        => 'password',
				'class'       => 'environment-field test-field',
			],
			'avs_street_address' => [
				'title'    => __( 'Address Verification Service (AVS)', 'woocommerce-gateway-firstdata' ),
				'label'    => __( 'Show the Street Address payment field.', 'woocommerce-gateway-firstdata' ),
				'type'     => 'checkbox',
				'default'  => 'no',
				'desc_tip' => __( "To enable AVS Fraud checks you must enable the Address Verification System Fraud Prevention Tool in your Clover merchant account.", 'woocommerce-gateway-firstdata' ),
			],
		];
	}


	/**
	 * Returns true if the AVS Street Address checkout field should be shown.
	 *
	 * @since 5.1.0
	 * @return boolean
	 */
	public function avs_street_address() {
		return $this->avs_street_address === 'yes';
	}


	/**
	 * Customize the wording of the "Enable Partial Capture" admin setting to
	 * reflect the capabilities and limitations of the Clover API
	 *
	 * @since 5.1.0
	 * @see SV_WC_Payment_Gateway::add_authorization_charge_form_fields()
	 * @param array $form_fields Associative array of form fields list
	 * @return array
	 */
	protected function add_authorization_charge_form_fields( $form_fields ) {

		$form_fields = parent::add_authorization_charge_form_fields( $form_fields );

		$form_fields['enable_partial_capture']['description'] = esc_html__( 'Allow charge captures of less than or more than the authorization transaction amount.', 'woocommerce-plugin-framework' );

		return $form_fields;
	}


	/**
	 * Initializes the payment form instance.
	 *
	 * @since 5.0.0
	 *
	 * @return Payment_Form
	 */
	protected function init_payment_form_instance() {

		return new Payment_Form( $this );
	}


	/**
	 * Enqueues the gateway assets.
	 *
	 * Adds the Clover hosted iframe SDK
	 *
	 * @since 5.0.0
	 */
	public function enqueue_gateway_assets() {

		if ( $this->is_available() && ( is_checkout() && ! is_checkout_pay_page() ) || is_add_payment_method_page() ) {

			parent::enqueue_gateway_assets();

			wp_enqueue_script( 'clover-hosted-iframe-sdk', $this->get_hosted_iframe_sdk_url(), array(), $this->get_plugin()->get_version() );
		}
	}


	/**
	 * Determines whether the front end gateway assets should load.
	 *
	 * By default, we don't load the legacy frontend when the checkout block is in use.
	 *
	 * Override so that we can call Gateway_Blocks_Handler::page_contains_checkout_shortcode()
	 *
	 * @since 5.2.0
	 * @see SV_WC_Payment_Gateway::should_enqueue_gateway_assets()
	 *
	 * @return bool
	 */
	protected function should_enqueue_gateway_assets() : bool {
		global $post;

		if ( is_checkout() && ! is_checkout_pay_page() && Framework\Blocks\Blocks_Handler::is_checkout_block_in_use() && ( $post && ! Gateway_Blocks_Handler::page_contains_checkout_shortcode( $post ) ) ) {
			return false;
		}

		return true;
	}


	/**
	 * Gets the gateway JS handle used to load/localize JS.
	 *
	 * @since 5.0.0
	 *
	 * @return string
	 */
	protected function get_gateway_js_handle() {

		return 'wc-first-data-clover-payment-form';
	}


	/**
	 * Bypasses direct credit card validation.
	 *
	 * @since 5.0.0
	 *
	 * @param bool $is_valid whether the credit card fields are valid
	 * @return bool
	 */
	protected function validate_credit_card_fields( $is_valid ) {

		if ( ! Framework\SV_WC_Helper::get_posted_value( 'wc-' . $this->get_id_dasherized() . '-js-token' ) ) {
			$this->add_debug_message( 'Payment token is missing', 'error' );
			$is_valid = false;
		}

		return $is_valid;
	}


	/**
	 * Gets an order with payment data added.
	 *
	 * @see Credit_Card::get_order()
	 * @see Credit_Card::add_payment_gateway_transaction_data()
	 * @see Payment::get_card_on_file_data()
	 *
	 * @since 2.0.0
	 *
	 * @param int $order_id order ID
	 * @return \WC_Order $order order object
	 */
	public function get_order( $order_id ) {

		$order = parent::get_order( $order_id );

		$order->payment->js_token = Framework\SV_WC_Helper::get_posted_value( 'wc-' . $this->get_id_dasherized() . '-js-token' );

		$order->payment->idempotency_key = $this->get_idempotency_key( $order );

		return $order;
	}


	/**
	 * Get the idempotency key for an order
	 *
	 * @link https://docs.clover.com/docs/ecommerce-accepting-payments#using-idempotency-keys
	 *
	 * @since 5.0.0
	 *
	 * @param \WC_Order $order
	 *
	 * @return string
	 */
	protected function get_idempotency_key( \WC_Order $order ) : string {

		// this is intended to be reused for the same logical payment request in order to prevent duplicate charges
		// in the event of network timeouts, etc. however, it fails in the following condition:
		// 1. charge attempt with one card that's declined
		// 2. charge attempt made with *another* card, when using the same key as the first request
		// perhaps intended behavior but it makes it difficult to know when to generate a new key, so we're simply
		// generating a new one for each request and that'll have to be good enough for now {MR 2022-09-13}
		return wp_generate_uuid4();
	}


	/**
	 * Gets the API handler.
	 *
	 * @since 5.0.0
	 *
	 * @return API
	 */
	public function get_api() : API {

		if ( null === $this->api ) {
			$this->api = new API( $this->get_merchant_id(), $this->get_private_token(), $this->get_environment(), $this->get_id(), get_current_user_id() );
		}

		return $this->api;
	}


	/**
	 * Determines if the gateway is properly configured to perform transactions.
	 *
	 * @since 5.0.0
	 * @see Framework\SV_WC_Payment_Gateway_Direct::is_configured()
	 *
	 * @return bool
	 */
	public function is_configured() : bool {

		return $this->get_merchant_id() && $this->get_public_token() && $this->get_private_token();
	}


	/**
	 * Gets the merchant ID.
	 *
	 * TODO: this pattern seems like it could be abstracted into a framework method {MR 2022-09-11}
	 *
	 * @since 5.0.0
	 *
	 * @param string $environment_id gateway environment ID
	 * @return string
	 */
	public function get_merchant_id( $environment_id = null ) {

		if ( null === $environment_id ) {
			$environment_id = $this->get_environment();
		}

		return self::ENVIRONMENT_PRODUCTION === $environment_id ? $this->merchant_id : $this->sandbox_merchant_id;
	}


	/**
	 * Gets public token.
	 *
	 * @since 5.0.0
	 *
	 * @param string $environment_id gateway environment ID
	 * @return string
	 */
	public function get_public_token( $environment_id = null ) {

		if ( null === $environment_id ) {
			$environment_id = $this->get_environment();
		}

		return self::ENVIRONMENT_PRODUCTION === $environment_id ? $this->public_token : $this->sandbox_public_token;
	}


	/**
	 * Gets private token.
	 *
	 * @since 5.0.0
	 *
	 * @param string $environment_id gateway environment ID
	 * @return string
	 */
	public function get_private_token( $environment_id = null ) {

		if ( null === $environment_id ) {
			$environment_id = $this->get_environment();
		}

		return self::ENVIRONMENT_PRODUCTION === $environment_id ? $this->private_token : $this->sandbox_private_token;
	}


	/**
	 * Get the hosted iframe SDK URL
	 *
	 * @since 5.0.0
	 *
	 * @param string $environment_id gateway environment ID
	 * @return string
	 */
	protected function get_hosted_iframe_sdk_url( $environment_id = null ) : string {

		if ( null === $environment_id ) {
			$environment_id = $this->get_environment();
		}

		return self::ENVIRONMENT_PRODUCTION === $environment_id ? 'https://checkout.clover.com/sdk.js' : 'https://checkout.sandbox.dev.clover.com/sdk.js';
	}


	/**
	 * Adds the CSC gateway settings fields, overridden as Clover always requires the CSC
	 * to be sent regardless, so it can't be disabled by the user.
	 *
	 * Note that Clover *does not* require the CSC for tokenized transactions (i.e. using a saved card) so
	 * `enable_token_csc` should never be set to true. {MR 2024-01-18}
	 *
	 * @since 5.0.0
	 * @see Framework\SV_WC_Payment_Gateway_Direct::add_csc_form_fields()
	 *
	 * @param array $form_fields gateway settings fields
	 * @return array
	 */
	protected function add_csc_form_fields( $form_fields ) {

		return $form_fields;
	}


	/**
	 * Determines whether CSC is enabled, Clover always requires CSC (except when using a saved card).
	 *
	 * @since 5.0.0
	 * @see Framework\SV_WC_Payment_Gateway_Direct::csc_enabled()
	 *
	 * @return true
	 */
	public function csc_enabled() {

		return true;
	}


	/**
	 * With Clover we receive a card token on checkout from the hosted fields,
	 * and then we save it to a remote Customer record to allow it to be used
	 * in subsequent transactions.
	 *
	 * @since 5.0.0
	 *
	 * @return bool
	 */
	public function tokenize_before_sale() {
		return true;
	}


	/**
	 * Gets the payment tokens handler instance.
	 *
	 * Concrete classes can override this method to return a custom implementation.
	 *
	 * @since 5.0.0
	 *
	 * @return Payment_Tokens_Handler
	 */
	protected function build_payment_tokens_handler() {

		return new Payment_Tokens_Handler( $this );
	}


	/**
	 * Gets the payment field styles.
	 *
	 * These are shared between the {@see Payment_Form} and the {@see Credit_Card_Checkout_Block_Integration}
	 *
	 * @since 5.2.0
	 *
	 * @return array
	 */
	public function get_hosted_element_styles() : array {

		$styles = [
			'body' => [
				'fontSize' => '1em',
			],
			'input' => [
				'fontSize' => '1em',
			]
		];

		/**
		 * Filters the Clover payment field styles.
		 *
		 * @since 5.0.0
		 *
		 * @param array $styles payment field styles
		 * @param Payment_Form $form_handler payment form handler
		 */
		return apply_filters( 'wc_' . $this->get_id() . '_payment_field_styles', $styles, $this );
	}


}
