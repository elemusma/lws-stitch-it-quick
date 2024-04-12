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
 * Payeezy Credit Card Class
 *
 * Handles credit card specific functionality
 *
 * @since 4.0.0
 */
class WC_Gateway_First_Data_Payeezy_Credit_Card extends WC_Gateway_First_Data_Payeezy {


	/** @var string the meta key used to store the client token from Payment.JS session authorization */
	const PAYMENT_JS_CLIENT_TOKEN_META = '_wc_' . \WC_First_Data::PAYEEZY_CREDIT_CARD_GATEWAY_ID . '_payment_js_client_token';

	/** @var string the meta key used to store the base64 nonce from Payment.JS session authorization */
	const PAYMENT_JS_NONCE_META = '_wc_' . \WC_First_Data::PAYEEZY_CREDIT_CARD_GATEWAY_ID . '_payment_js_nonce';


	/** @var string production JS security key */
	protected $js_security_key;

	/** @var string production Transarmor token */
	protected $transarmor_token;

	/** @var string production Payment.JS secret */
	protected $payment_js_secret;

	/** @var string Payment.JS confirmation */
	protected $payment_js_confirmation;

	/** @var string sandbox JS security key */
	protected $sandbox_js_security_key;

	/** @var string sandbox TransArmor token */
	protected $sandbox_transarmor_token;

	/** @var string sandbox Payment.JS secret */
	protected $sandbox_payment_js_secret;

	/** @var string whether soft descriptors are enabled or not */
	protected $soft_descriptors_enabled;

	/** @var string DBA name soft descriptor */
	protected $soft_descriptor_dba_name;

	/** @var string street soft descriptor */
	protected $soft_descriptor_street;

	/** @var string city soft descriptor */
	protected $soft_descriptor_city;

	/** @var string region soft descriptor */
	protected $soft_descriptor_region;

	/** @var string postal code soft descriptor */
	protected $soft_descriptor_postal_code;

	/** @var string country code soft descriptor */
	protected $soft_descriptor_country_code;

	/** @var string MID soft descriptor */
	protected $soft_descriptor_mid;

	/** @var string MCC soft descriptor */
	protected $soft_descriptor_mcc;

	/** @var string merchant contact info soft descriptor */
	protected $soft_descriptor_merchant_contact_info;


	/**
	 * Setup the class
	 *
	 * @since 4.0.0
	 */
	public function __construct() {

		parent::__construct(
			\WC_First_Data::PAYEEZY_CREDIT_CARD_GATEWAY_ID,
			wc_first_data(),
			[
				'method_title'       => __( 'Payeezy Credit Card', 'woocommerce-gateway-firstdata' ),
				'method_description' => __( 'Allow customers to securely pay using their credit card via Payeezy.', 'woocommerce-gateway-firstdata' ),
				'supports'           => [
					self::FEATURE_PRODUCTS,
					self::FEATURE_CARD_TYPES,
					self::FEATURE_TOKENIZATION,
					self::FEATURE_CREDIT_CARD_CHARGE,
					self::FEATURE_CREDIT_CARD_CHARGE_VIRTUAL,
					self::FEATURE_CREDIT_CARD_AUTHORIZATION,
					self::FEATURE_CREDIT_CARD_CAPTURE,
					self::FEATURE_DETAILED_CUSTOMER_DECLINE_MESSAGES,
					self::FEATURE_REFUNDS,
					self::FEATURE_VOIDS,
					self::FEATURE_ADD_PAYMENT_METHOD,
					self::FEATURE_TOKEN_EDITOR,
					self::FEATURE_PAYMENT_FORM,
				],
				'payment_type'       => self::PAYMENT_TYPE_CREDIT_CARD,
				'environments'       => $this->get_payeezy_environments(),
				'shared_settings'    => $this->shared_settings_names,
				'card_types'         => [
					'VISA'    => 'Visa',
					'MC'      => 'MasterCard',
					'AMEX'    => 'American Express',
					'DISC'    => 'Discover',
					'DINERS'  => 'Diners',
					'JCB'     => 'JCB',
				],
			]
		);

		// reset since it depends on a setting
		$this->order_button_text = $this->get_order_button_text();

		if ( $this->should_use_payment_js() ) {

			// process requests with Payment.JS
			add_action( 'woocommerce_api_wc-first-data/webhook' , [ $this, 'handle_payment_js_tokenization_request'] );

		} else {

			// add hidden inputs that client-side JS populates with token/last 4 of card
			add_action( 'wc_first_data_payeezy_credit_card_payment_form', [ $this, 'render_hidden_inputs' ] );

			// remove card number/csc input names so they're not POSTed
			add_filter( 'wc_first_data_payeezy_credit_card_payment_form_default_credit_card_fields', [ $this, 'remove_credit_card_field_input_names' ] );
		}
	}


	/**
	 * Initializes the capture handler.
	 *
	 * @since 4.4.0
	 */
	public function init_capture_handler() {

		$this->capture_handler = new \Atreus\WooCommerce\First_Data\Payeezy\Capture( $this );
	}


	/**
	 * Gets the payment form instance.
	 *
	 * This could be the default payment form from the framework or a Payment.JS form if Payment.JS is enabled.
	 *
	 * @internal
	 *
	 * @since 4.7.3
	 *
	 * @return \Atreus\WooCommerce\First_Data\Payeezy\PaymentJS|Framework\SV_WC_Payment_Gateway_Payment_Form
	 */
	public function init_payment_form_instance() {

		if ( $this->should_use_payment_js() ) {

			require_once( $this->get_plugin()->get_plugin_path() . '/src/payeezy/PaymentJS.php' );

			$payment_form = new \Atreus\WooCommerce\First_Data\Payeezy\PaymentJS( $this );

		} else {

			$payment_form = parent::init_payment_form_instance();
		}

		return $payment_form;
	}


	/** Admin Methods *********************************************************/


	/**
	 * Adds the enable Card Security Code form fields.
	 *
	 * Overridden as Payeezy.JS always requires a CSC value.
	 *
	 * @see Framework\SV_WC_Payment_Gateway::add_csc_form_fields()
	 *
	 * @since 4.3.3
	 *
	 * @param array $form_fields gateway form fields
	 * @return array
	 */
	protected function add_csc_form_fields( $form_fields ) {

		return $form_fields;
	}


	/**
	 * Returns an array of form fields specific for the method
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_Payment_Gateway::get_method_form_fields()
	 * @return array of form fields
	 */
	protected function get_method_form_fields() {

		// force tokenization to be enabled, since Payeezy.js requires it ¯\_(ツ)_/¯
		$this->form_fields['tokenization'] = array(
			'title'       => __( 'Required Transarmor Entitlement', 'woocommerce-gateway-firstdata' ),
			'label'       => __( 'I certify that I have enabled Transarmor entitlement on my Payeezy account by contacting my merchant account representative.', 'woocommerce-gateway-firstdata' ),
			'type'        => 'checkbox',
			'default'     => 'yes',
			'description' => sprintf(
				/* translators: Placeholders: %1$s - <strong> tag, %2$s - </strong> tag */
				__( 'Note that you %1$smust enable%2$s the Transarmor entitlement on your Payeezy account by contacting your merchant account representative. This will additionally allow customers to securely save their payment details for future checkout.', 'woocommerce-gateway-firstdata' ),
				'<strong>', '</strong>'
			),
		);

		$form_fields = [

			'transarmor_token' => [
				'title'    => __( 'Transarmor Token', 'woocommerce-gateway-firstdata' ),
				'type'     => 'text',
				'class'    => 'environment-field production-field',
				'desc_tip' => __( 'Your Transarmor token.', 'woocommerce-gateway-firstdata' ),
			],

			'sandbox_transarmor_token' => [
				'title'    => __( 'Sandbox Transarmor Token', 'woocommerce-gateway-firstdata' ),
				'type'     => 'text',
				'class'    => 'environment-field sandbox-field',
				'desc_tip' => __( 'Your sandbox Transarmor token.', 'woocommerce-gateway-firstdata' ),
				'default'  => 'NOIW',
			],

			// Payment.JS
			'payment_js_secret' => [
				'title'    => __( 'Payment.JS Secret', 'woocommerce-gateway-firstdata' ),
				'type'     => 'password',
				'class'    => 'environment-field production-field',
				'desc_tip' => __( 'Your Payment.JS Secret', 'woocommerce-gateway-firstdata' ),
			],

			'sandbox_payment_js_secret' => [
				'title'    => __( 'Sandbox Payment.JS Secret', 'woocommerce-gateway-firstdata' ),
				'type'     => 'password',
				'class'    => 'environment-field sandbox-field',
				'desc_tip' => __( 'Your sandbox Payment.JS Secret', 'woocommerce-gateway-firstdata' ),
			],

			/** @see \WC_Gateway_First_Data_Payeezy_Credit_Card::generate_payment_js_webhook_url_html() */
			'payment_js_webhook' => [
				'title' => __( 'Webhook URL', 'woocommerce-gateway-firstdata' ),
				'type'  => 'payment_js_webhook_url',
				'css'   => 'display: inline-block; padding: 4px 5px; margin-bottom: 5px;'
			],

			'payment_js_confirmation' => [
				'type'  => 'checkbox',
				'label' => sprintf(
				/* translators: Placeholders: %1$s - <strong> tag, %2$s - </strong> tag */
					__( 'I certify that I have registered the above %1$sWebhook URL%2$s for my Payeezy account.', 'woocommerce-payment-firstdata' ),
					'<strong>', '</strong>'
				),
				'description' => sprintf(
				/* translators: Placeholders: %1$s - <strong> tag, %2$s - </strong> tag, %3$s - <a> tag, %4$s - </a> tag */
					__( 'You %1$smust%2$s register for Payment.JS using %3$sthis form%4$s and set your %1$sPayment.JS secret%2$s in order to process payments.', 'woocommerce-payment-firstdata' ),
					'<strong>', '</strong>',
					'<a href="https://docs.firstdata.com/req/paymentjs" target="_blank">', '</a>'
				),
			],

			// soft descriptors
			'soft_descriptors_title' => [
				'title' => __( 'Soft Descriptor Settings', 'woocommerce-gateway-firstdata' ),
				'type'  => 'title',
			],

			'soft_descriptors_enabled' => [
				'title'       => __( 'Soft Descriptors', 'woocommerce-gateway-firstdata' ),
				'label'       => __( 'Enable soft descriptors', 'woocommerce-gateway-firstdata' ),
				'type'        => 'checkbox',
				'description' => 'All of the soft descriptors are optional.  If you would like to use Soft Descriptors, please contact your First Data Relationship Manager or Sales Rep and have them set your "Foreign Indicator" in your "North Merchant Manager File" to "5".',
				'default'     => 'no',
			],

			'soft_descriptor_dba_name' => [
				'title'    => __( 'DBA Name', 'woocommerce-gateway-firstdata' ),
				'desc_tip' => __( 'Business name.', 'woocommerce-gateway-firstdata' ),
				'class'    => 'soft-descriptor',
				'type'     => 'text',
			],

			'soft_descriptor_street' => [
				'title'    => __( 'Street', 'woocommerce-gateway-firstdata' ),
				'desc_tip' => __( 'Business address.', 'woocommerce-gateway-firstdata' ),
				'class'    => 'soft-descriptor',
				'type'     => 'text',
			],

			'soft_descriptor_city' => [
				'title'    => __( 'City', 'woocommerce-gateway-firstdata' ),
				'desc_tip' => __( 'Business city.', 'woocommerce-gateway-firstdata' ),
				'class'    => 'soft-descriptor',
				'type'     => 'text',
			],

			'soft_descriptor_region' => [
				'title'    => __( 'Region', 'woocommerce-gateway-firstdata' ),
				'desc_tip' => __( 'Business region.', 'woocommerce-gateway-firstdata' ),
				'class'    => 'soft-descriptor',
				'type'     => 'text',
			],

			'soft_descriptor_postal_code' => [
				'title'    => __( 'Postal Code', 'woocommerce-gateway-firstdata' ),
				'desc_tip' => __( 'Business postal/zip code.', 'woocommerce-gateway-firstdata' ),
				'class'    => 'soft-descriptor',
				'type'     => 'text',
			],

			'soft_descriptor_country_code' => [
				'title'    => __( 'Country Code', 'woocommerce-gateway-firstdata' ),
				'desc_tip' => __( 'Business country.', 'woocommerce-gateway-firstdata' ),
				'class'    => 'soft-descriptor',
				'type'     => 'text',
			],

			'soft_descriptor_mid' => [
				'title'    => __( 'MID', 'woocommerce-gateway-firstdata' ),
				'desc_tip' => __( 'Merchant ID.', 'woocommerce-gateway-firstdata' ),
				'class'    => 'soft-descriptor',
				'type'     => 'text',
			],

			'soft_descriptor_mcc' => [
				'title'    => __( 'MCC', 'woocommerce-gateway-firstdata' ),
				'desc_tip' => __( 'Your Merchant Category Code.', 'woocommerce-gateway-firstdata' ),
				'class'    => 'soft-descriptor',
				'type'     => 'text',
			],

			'soft_descriptor_merchant_contact_info' => [
				'title'    => __( 'Merchant Contact Info', 'woocommerce-gateway-firstdata' ),
				'desc_tip' => __( 'Merchant contact information.', 'woocommerce-gateway-firstdata' ),
				'class'    => 'soft-descriptor',
				'type'     => 'text',
			],
		];

		if ( wc_string_to_bool( get_option( 'wc_first_data_payeezy_display_payeezy_js_settings' ) ) ) {

			$form_fields[] = [
				'type'        => 'title',
				'title'       => __( 'Payeezy.js settings', 'woocommerce-gateway-firstdata' ),
				'description' => __( 'This is a legacy integration and will soon be retired. Please configure Payment.JS above.', 'woocommerce-gateway-firstdata' ),
			];

			$form_fields['js_security_key'] = [
				'title'    => __( 'JS Security Key', 'woocommerce-gateway-firstdata' ),
				'type'     => 'text',
				'class'    => 'environment-field production-field',
				'desc_tip' => __( 'Your JS security key.', 'woocommerce-gateway-firstdata' ),
			];

			$form_fields['sandbox_js_security_key'] = [
				'title'    => __( 'Sandbox JS Security Key', 'woocommerce-gateway-firstdata' ),
				'type'     => 'text',
				'class'    => 'environment-field sandbox-field',
				'desc_tip' => __( 'Your sandbox JS security key.', 'woocommerce-gateway-firstdata' ),
			];
		}

		return array_merge( parent::get_method_form_fields(), $form_fields );
	}


	/**
	 * Generates the Payment.JS Webhook URL placeholder field.
	 *
	 * @internal
	 *
	 * @since 4.7.0
	 *
	 * @param string $key field key
	 * @param array $args array of field data
	 * @return string HTML
	 */
	public function generate_payment_js_webhook_url_html( $key, $args ) {

		$url  = $this->get_payment_js_webhook_url();
		$key  = $this->get_field_key( $key );
		$args = wp_parse_args( $args, [
			'title'       => '',
			'disabled'    => false,
			'class'       => '',
			'css'         => '',
			'type'        => 'text',
			'desc_tip'    => false,
			'description' => '',
		] );

		ob_start();

		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<?php echo $this->get_tooltip_html( $args ); ?>
				<label for="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $args['title'] ); ?></label>
			</th>
			<td class="forminp">
				<code
					class="<?php echo esc_attr( $args['class'] ); ?>"
					id="<?php echo esc_attr( $key ); ?>"
					style="<?php echo esc_attr( $args['css'] ); ?>"
				><?php echo esc_html( $url ); ?></code>
				<br />
				<?php echo $this->get_description_html( $args ); ?>
			</td>
		</tr>
		<?php

		return ob_get_clean();
	}


	/**
	 * Display the settings page with some additional JS to hide the soft descriptor
	 * settings if not enabled, and hide the transarmor token field if tokenization
	 * is not enabled
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_Payment_Gateway::admin_options()
	 */
	public function admin_options() {

		parent::admin_options();

		ob_start();

		?>
		$( '#woocommerce_first_data_payeezy_credit_card_soft_descriptors_enabled' ).change( function() {
			if ( $( this ).is( ':checked' ) ) {
				$( '.soft-descriptor' ).closest( 'tr' ).show();
			} else {
				$( '.soft-descriptor' ).closest( 'tr' ).hide();
			}
		} ).change();
		<?php

		wc_enqueue_js( ob_get_clean() );
	}


	/** Frontend Methods ******************************************************/


	/**
	 * Renders the gateway payment fields.
	 *
	 * @since 4.7.0
	 */
	public function payment_fields() {

		// don't display fields on the regular checkout page
		if ( $this->should_use_payment_js() && is_checkout() && ! is_checkout_pay_page() ) {

			$description = $this->get_description();

			if ( $description ) {
				echo wpautop( wptexturize( $description ) );
			}

			return;
		}

		parent::payment_fields();
	}


	/**
	 * Outputs the payment page form.
	 *
	 * @internal
	 *
	 * @since 4.7.0
	 *
	 * @param int $order_id ID of the order that needs payment
	 */
	public function payment_page( $order_id ) {

		if ( $this->should_use_payment_js() ) :

			?>
			<form id="order_review" method="post" data-order-id="<?php echo esc_attr( $order_id ); ?>">
				<div id="payment">
					<div class="payment_box payment_method_<?php echo esc_attr( $this->get_id() ); ?>">
						<?php $this->get_payment_form_instance()->render(); ?>
					</div>
				</div>
				<button
					type="submit"
					id="place_order"
					class="button alt"
					name="woocommerce_pay"
				><?php esc_html_e( 'Place order', 'woocommerce-gateway-firstdata' ); ?></button>
				<input
					type="radio"
					name="payment_method"
					value="<?php echo esc_attr( $this->get_id() ); ?>"
					checked
				/>
				<input
					type="hidden"
					name="woocommerce_pay"
					value=""
				/>
				<?php wp_nonce_field( 'woocommerce-pay', 'woocommerce-pay-nonce' ); ?>
			</form>
			<?php

		else :

			parent::payment_page( $order_id );

		endif;
	}


	/**
	 * Enqueues the gateway-level assets.
	 *
	 * @since 4.7.0
	 */
	protected function enqueue_gateway_assets() {

		// enqueued elsewhere if Payment.JS is being used
		if ( $this->should_use_payment_js() ) {
			return;
		}

		parent::enqueue_gateway_assets();
	}


	/**
	 * Gets the gateway JS handle used to load/localize JS.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	protected function get_gateway_js_handle() {

		return 'wc-first-data-payeezy';
	}


	/**
	 * Gets the required localized JS data for the Payeezy tokenization handler.
	 *
	 * @since 4.0.0
	 *
	 * @return array
	 */
	protected function get_gateway_js_localized_script_params() {

		return [
			'api_url'          => $this->is_production_environment() ? 'https://api.payeezy.com/v1/securitytokens' : 'https://api-cert.payeezy.com/v1/securitytokens',
			'api_key'          => $this->get_api_key(),
			'js_security_key'  => $this->get_js_security_key(),
			'transarmor_token' => $this->get_transarmor_token(),
			'pre_auth'         => $this->perform_pre_auth(),
			'ajax_url'         => admin_url( 'admin-ajax.php' ),
			'ajax_log_nonce'   => wp_create_nonce( 'wc_' . $this->get_id() . '_log_js_data' ),
			'ajax_log'         => ! $this->debug_off(),
		];
	}


	/**
	 * Remove the input names for the card number and CSC fields so they're
	 * not POSTed to the server, for security and compliance with Payeezy.js
	 *
	 * @since 4.0.0
	 * @param array $fields credit card fields
	 * @return array
	 */
	public function remove_credit_card_field_input_names( $fields ) {

		$fields['card-number']['name'] = '';

		if ( ! empty( $fields['card-csc'] ) ) {
			$fields['card-csc']['name'] = '';
		}

		return $fields;
	}


	/**
	 * Renders hidden inputs on the payment form for the card token, last four,
	 * and card type. These are populated by the client-side JS after successful
	 * tokenization.
	 *
	 * @since 4.0.0
	 */
	public function render_hidden_inputs() {

		// token
		printf( '<input type="hidden" id="%1$s" name="%1$s" />', 'wc-' . $this->get_id_dasherized() . '-token' );

		// card last four
		printf( '<input type="hidden" id="%1$s" name="%1$s" />', 'wc-' . $this->get_id_dasherized() . '-last-four' );

		// card type
		printf( '<input type="hidden" id="%1$s" name="%1$s" />', 'wc-' . $this->get_id_dasherized() . '-card-type' );
	}


	/**
	 * Return the default values for this payment method, used to pre-fill
	 * a valid test account number when in testing mode
	 *
	 * @since 2.0.0
	 * @see Framework\SV_WC_Payment_Gateway::get_payment_method_defaults()
	 * @return array
	 */
	public function get_payment_method_defaults() {

		$defaults = parent::get_payment_method_defaults();

		if ( $this->is_test_environment() ) {

			$defaults['account-number'] = '4012000033330026';
		}

		return $defaults;
	}


	/**
	 * Validates the provided credit card data, including card type, last four, and token.
	 *
	 * This primarily ensures the data is safe to set on the order object in get_order() below.
	 * @see Framework\SV_WC_Payment_Gateway_Direct::validate_credit_card_fields()
	 * Note: when using Payment.JS no fields are provided at checkout, and validation is handled at checkout pay page.
	 *
	 * @internal
	 *
	 * @since 4.0.0
	 *
	 * @param bool $is_valid true if the fields are valid, false otherwise
	 * @return bool true if the fields are valid, false otherwise
	 */
	protected function validate_credit_card_fields( $is_valid ) {

		// when using Payment.JS delegate validation to payment form in pay page
		if ( $this->should_use_payment_js() ) {
			return $is_valid;
		}

		// when using a saved credit card, there is no further validation required
		if ( Framework\SV_WC_Helper::get_posted_value( 'wc-' . $this->get_id_dasherized() . '-payment-token' ) ) {
			return $is_valid;
		}

		$card_type  = Framework\SV_WC_Helper::get_posted_value( 'wc-' . $this->get_id_dasherized() . '-card-type' );
		$last_four  = Framework\SV_WC_Helper::get_posted_value( 'wc-' . $this->get_id_dasherized() . '-last-four' );
		$card_token = Framework\SV_WC_Helper::get_posted_value( 'wc-' . $this->get_id_dasherized() . '-token' );

		$valid_card_types = array(
			Framework\SV_WC_Payment_Gateway_Helper::CARD_TYPE_VISA,
			Framework\SV_WC_Payment_Gateway_Helper::CARD_TYPE_MASTERCARD,
			Framework\SV_WC_Payment_Gateway_Helper::CARD_TYPE_AMEX,
			Framework\SV_WC_Payment_Gateway_Helper::CARD_TYPE_DISCOVER,
			Framework\SV_WC_Payment_Gateway_Helper::CARD_TYPE_DINERSCLUB,
			Framework\SV_WC_Payment_Gateway_Helper::CARD_TYPE_JCB,
		);

		// card type
		if ( ! in_array( $card_type, $valid_card_types, true ) ) {

			Framework\SV_WC_Helper::wc_add_notice( __( 'Provided card type is invalid.', 'woocommerce-gateway-firstdata' ), 'error' );
			$is_valid = false;
		}

		// last four
		if ( preg_match( '/\D/', $last_four ) ) {

			Framework\SV_WC_Helper::wc_add_notice( __( 'Provided card last four is invalid.', 'woocommerce-gateway-firstdata' ), 'error' );
			$is_valid = false;
		}

		// token
		if ( preg_match( '/\W/', $card_token ) ) {

			Framework\SV_WC_Helper::wc_add_notice( __( 'Provided card token is invalid.', 'woocommerce-gateway-firstdata' ), 'error' );
			$is_valid = false;
		}

		return $is_valid;
	}


	/**
	 * The CSC field is verified client-side and thus always valid.
	 *
	 * @since 4.0.0
	 * @param string $field
	 * @return bool
	 */
	protected function validate_csc( $field ) {

		return true;
	}


	/**
	 * Determines if the CSC field is enabled.
	 *
	 * Overridden as Payeezy.JS always requires a CSC value.
	 *
	 * @see Framework\SV_WC_Payment_Gateway::csc_enabled()
	 *
	 * @since 4.3.3
	 *
	 * @return bool
	 */
	public function csc_enabled() {

		return true;
	}


	/** Gateway Methods *******************************************************/


	/**
	 * Payeezy tokenizes payment methods with the sale
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_Payment_Gateway_Direct::tokenize_with_sale()
	 * @return bool
	 */
	public function tokenize_with_sale() {

		return true;
	}


	/**
	 * Gets the order, adds Payeezy Gateway specific info to the order:
	 *
	 * + payment->full_type (string) Payeezy Gateway card type for tokenized transactions
	 * + payment->soft_descriptors (array) pre-sanitized array of soft descriptors for transaction
	 *
	 * @see Framework\SV_WC_Payment_Gateway_Direct::get_order()
	 *
	 * @since 4.0.0
	 *
	 * @param int|\WC_Order $order_id
	 * @return \WC_Order
	 */
	public function get_order( $order_id ) {

		$order = parent::get_order( $order_id );

		if ( empty( $order->payment->token ) ) {

			if ( $this->should_use_payment_js() ) {

				$order = $this->get_order_payment_js_tokenization_payment_data( $order );

			} else {

				// expiry month/year
				list( $order->payment->exp_month, $order->payment->exp_year ) = array_map( 'trim', explode( '/', Framework\SV_WC_Helper::get_posted_value( 'wc-' . $this->get_id_dasherized() . '-expiry' ) ) );

				// format the expiration date as mm & yy
				$order->payment->exp_month = zeroise( $order->payment->exp_month, 2 );
				$order->payment->exp_year  = substr( $order->payment->exp_year, -2 );

				// card type
				$card_type                 = Framework\SV_WC_Helper::get_posted_value( 'wc-' . $this->get_id_dasherized() . '-card-type' );
				$order->payment->card_type = Framework\SV_WC_Payment_Gateway_Helper::normalize_card_type( $card_type );

				// last four
				$order->payment->last_four = Framework\SV_WC_Helper::get_posted_value( 'wc-' . $this->get_id_dasherized() . '-last-four' );

				// fake the account number so other parts of the framework that expect a full account number work as expected
				$order->payment->account_number = '*' . $order->payment->last_four;

				// token
				$order->payment->token = Framework\SV_WC_Helper::get_posted_value( 'wc-' . $this->get_id_dasherized() . '-token' );
			}
		}

		// required for transactions with tokens
		$order->payment->full_type = $this->get_full_card_type( $order );

		// soft descriptors
		$order->payment->soft_descriptors = $this->soft_descriptors_enabled() ? $this->get_soft_descriptors() : array();

		// test amount when in demo environment
		if ( $this->is_test_environment() && ( $test_amount = Framework\SV_WC_Helper::get_posted_value( 'wc-' . $this->get_id_dasherized() . '-test-amount' ) ) ) {
			$order->payment_total = Framework\SV_WC_Helper::number_format( $test_amount );
		}

		return $order;
	}


	/**
	 * Get the order for capturing, adds:
	 *
	 * + capture->transaction_tag (string)
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_Payment_Gateway_Direct::get_order_for_capture()
	 * @param \WC_Order|int $order the order being processed
	 * @param float|null $amount amount to capture or null for the full order amount
	 * @return \WC_Order
	 */
	public function get_order_for_capture( $order, $amount = NULL ) {

		$order = parent::get_order_for_capture( $order, $amount );

		$order->capture->transaction_tag = $this->get_order_meta( $order, 'transaction_tag' );

		return $order;
	}


	/**
	 * Get the order for refunding/voiding, adds:
	 *
	 * + capture->transaction_tag (string)
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_Payment_Gateway::get_order_for_refund()
	 * @param int|\WC_Order $order either Order ID or WC_Order object
	 * @param float $amount order refund amount
	 * @param string $reason refund reason
	 * @return int|\WC_Order either Order ID or WC_Order object
	 */
	protected function get_order_for_refund( $order, $amount, $reason ) {

		$order = parent::get_order_for_refund( $order, $amount, $reason );

		// if an authorize-only transaction was captured, use the capture trans ID instead
		if ( $capture_trans_tag = $this->get_order_meta( $order, 'capture_transaction_tag' ) ) {

			$order->refund->transaction_tag = $capture_trans_tag;
			$order->refund->trans_id = $this->get_order_meta( $order, 'capture_trans_id' );

		} else {

			$order->refund->transaction_tag = $this->get_order_meta( $order, 'transaction_tag' );
		}

		return $order;
	}


	/**
	 * Updates a transaction's payment method.
	 *
	 * First Data doesn't currently support this, so skip it.
	 *
	 * @since 4.4.0
	 *
	 * @param \WC_Order $order order object
	 * @return \WC_Order
	 */
	protected function update_transaction_payment_method( \WC_Order $order ) {

		return $order;
	}


	/** Subscriptions *********************************************************/


	/**
	 * Tweak the labels shown when editing the payment method for a Subscription
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_Payment_Gateway_Integration_Subscriptions::admin_add_payment_meta()
	 * @param array $meta payment meta
	 * @param \WC_Subscription $subscription subscription being edited
	 * @return array
	 */
	public function subscriptions_admin_add_payment_meta( $meta, $subscription ) {

		// note that for EU merchants, the tokens are called "DataVault" instead of "TransArmor"
		if ( isset( $meta[ $this->get_id() ] ) ) {
			$meta[ $this->get_id() ]['post_meta'][ $this->get_order_meta_prefix() . 'payment_token' ]['label'] = __( 'TransArmor Token', 'woocommerce-gateway-firstdata' );
		}

		return $meta;
	}


	/**
	 * Validate that the TransArmor token for a Subscription is alphanumeric
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_Payment_Gateway_Integration_Subscriptions::admin_validate_payment_meta()
	 * @param array $meta payment meta
	 * @throws \Exception if transarmor token is not numeric
	 */
	public function subscriptions_admin_validate_payment_meta( $meta ) {

		// transarmor token (payment_token) must be alphanumeric
		if ( ! ctype_alnum( (string) $meta['post_meta'][ $this->get_order_meta_prefix() . 'payment_token' ]['value'] ) ) {
			throw new \Exception( __( 'TransArmor token can only include letters and/or numbers.', 'woocommerce-gateway-firstdata' ) );
		}
	}


	/** Getters ***************************************************************/


	/**
	 * Determines whether the gateway is properly configured to perform transactions.
	 *
	 * @see \WC_Gateway_First_Data_Payeezy::is_configured()
	 *
	 * @since 4.0.0
	 *
	 * @return bool
	 */
	public function is_configured() {

		$is_configured = parent::is_configured();

		// tokenization is required / missing required configuration fields
		if ( ! $this->tokenization_enabled() || ! $this->get_transarmor_token() ) {
			$is_configured = false;
		}

		// ensure either Payment.JS or Payeezy.JS are configured
		$is_configured = $is_configured && ( $this->is_payment_js_configured() || $this->get_js_security_key() );

		return $is_configured;
	}


	/**
	 * Determines whether Payment.JS has been configured.
	 *
	 * @since 4.7.0
	 *
	 * @return bool
	 */
	public function is_payment_js_configured() {

		return $this->get_payment_js_secret() && $this->get_payment_js_confirmation();
	}


	/**
	 * Determines whether Payment.JS should be used.
	 *
	 * @since 4.7.0
	 *
	 * @return bool
	 */
	public function should_use_payment_js() {

		/**
		 * Filters whether Payment.JS should be used.
		 *
		 * @since 4.7.0
		 *
		 * @param bool $use_payment_js defaults to true if Payment.JS is configured, false otherwise
		 */
		return (bool) apply_filters( 'wc_first_data_payeezy_payment_js_enabled', $this->is_payment_js_configured() );
	}


	/**
	 * Get the full card type for a given order, Payeezy requires this
	 * to be set when performing a transaction with a saved token
	 *
	 * @since 4.0.0
	 * @param \WC_Order $order
	 * @return string|null
	 */
	protected function get_full_card_type( \WC_Order $order ) {

		$types = array(
			Framework\SV_WC_Payment_Gateway_Helper::CARD_TYPE_AMEX       => 'American Express',
			Framework\SV_WC_Payment_Gateway_Helper::CARD_TYPE_VISA       => 'Visa',
			Framework\SV_WC_Payment_Gateway_Helper::CARD_TYPE_MASTERCARD => 'Mastercard',
			Framework\SV_WC_Payment_Gateway_Helper::CARD_TYPE_DISCOVER   => 'Discover',
			Framework\SV_WC_Payment_Gateway_Helper::CARD_TYPE_DINERSCLUB => 'Diners Club',
			Framework\SV_WC_Payment_Gateway_Helper::CARD_TYPE_JCB        => 'JCB',
		);

		return ! empty( $order->payment->card_type ) && isset( $types[ $order->payment->card_type ] ) ? $types[ $order->payment->card_type ] : null;
	}


	/**
	 * Returns the JS security key based on the current environment
	 *
	 * @since 4.0.0
	 * @param string $environment_id optional, defaults to production
	 * @return string JS security key
	 */
	public function get_js_security_key( $environment_id = null ) {

		if ( is_null( $environment_id ) ) {
			$environment_id = $this->get_environment();
		}

		return self::ENVIRONMENT_PRODUCTION === $environment_id ? $this->js_security_key : $this->sandbox_js_security_key;
	}


	/**
	 * Returns the transarmor token based on the current environment
	 *
	 * @since 4.0.0
	 * @param string $environment_id optional, defaults to production
	 * @return string transarmor token
	 */
	public function get_transarmor_token( $environment_id = null ) {

		if ( is_null( $environment_id ) ) {
			$environment_id = $this->get_environment();
		}

		return self::ENVIRONMENT_PRODUCTION === $environment_id ? $this->transarmor_token : $this->sandbox_transarmor_token;
	}


	/**
	 * Gets the Payment JS secret.
	 *
	 * @since 4.7.0
	 *
	 * @param string $environment_id desired environment. Defaults to the setting.
	 * @return string
	 */
	public function get_payment_js_secret( $environment_id = null ) {

		if ( null === $environment_id ) {
			$environment_id = $this->get_environment();
		}

		return self::ENVIRONMENT_PRODUCTION === $environment_id ? $this->payment_js_secret : $this->sandbox_payment_js_secret;
	}


	/**
	 * Gets the Payment.JS confirmation.
	 *
	 * @since 4.7.0
	 *
	 * @return string
	 */
	public function get_payment_js_confirmation() {

		return wc_string_to_bool( $this->payment_js_confirmation );
	}


	/**
	 * Gets the Payment.JS Webhook URL.
	 *
	 * This is the URL that should be configured in First.
	 * The gateway will listen for a generated token posted to this address to process a transaction.
	 *
	 * @since 4.7.0
	 *
	 * @return string URL
	 */
	public function get_payment_js_webhook_url() {

		// e.g. website.example.com/wc-api/wc-first-data/webhook
		$endpoint_url = untrailingslashit( get_home_url( null, 'wc-api/wc-first-data/webhook' ) );

		/**
		 * Filters the Webhook URL used by Payment.JS.
		 *
		 * @since 4.7.0
		 *
		 * @param string $endpoint_url defaults to <site_url>/wc-api/wc-first-data/webhook
		 */
		return (string) apply_filters( 'wc_first_data_payeezy_payment_js_webhook_url', $endpoint_url );
	}


	/**
	 * Returns true if soft descriptors are enabled
	 *
	 * @since 4.0.0
	 * @return bool
	 */
	public function soft_descriptors_enabled() {

		return 'yes' === $this->soft_descriptors_enabled;
	}


	/**
	 * Returns an array of soft descriptors as entered by the admin, sanitized
	 * and ready for adding to the transaction request data
	 *
	 * @since 4.0.0
	 * @return array
	 */
	public function get_soft_descriptors() {

		$descriptor_names = array(  'dba_name', 'street', 'city', 'region', 'postal_code',
			'country_code', 'mid', 'mcc', 'merchant_contact_info',
		);

		$descriptors = array();

		foreach ( $descriptor_names as $descriptor_name ) {

			$descriptor_key = "soft_descriptor_{$descriptor_name}";

			if ( ! empty( $this->$descriptor_key ) ) {

				// ASCII required
				$descriptor = Framework\SV_WC_Helper::str_to_ascii( $this->$descriptor_key );

				// truncate to 3 chars
				if ( 'region' === $descriptor_name || 'country_code' === $descriptor_name ) {
					$descriptor = substr( $descriptor, 0, 3 );
				}

				$descriptors[ $descriptor_name ] = $descriptor;
			}
		}

		return $descriptors;
	}


	/**
	 * Handles a tokenization request submitted via Payment.JS webhook.
	 *
	 * @internal
	 *
	 * @since 4.7.0
	 */
	public function handle_payment_js_tokenization_request() {

		$object   = null;
		$response = null;

		try {

			// check that some data is present
			if ( ! is_array( $_SERVER ) || empty( $_SERVER ) ) {
				throw new Framework\SV_WC_API_Exception( 'Header information is missing' );
			}

			$client_token = ! empty( $_SERVER['HTTP_CLIENT_TOKEN'] ) ? $_SERVER['HTTP_CLIENT_TOKEN'] : '';
			$nonce        = ! empty( $_SERVER['HTTP_NONCE'] ) ? $_SERVER['HTTP_NONCE'] : '';
			$request_data = file_get_contents( 'php://input' );

			if ( ! $client_token ) {
				throw new Framework\SV_WC_API_Exception( 'Client token is missing', 422 );
			}

			if ( ! $nonce ) {
				throw new Framework\SV_WC_API_Exception( 'Nonce is missing', 422 );
			}

			if ( ! $request_data ) {
				throw new Framework\SV_WC_API_Exception( 'Tokenization data is missing', 422 );
			}

			// log the data for easier debugging
			if ( $decoded_data = json_decode( $request_data, true ) ) {
				$this->add_debug_message( $this->get_plugin()->get_api_log_message( $decoded_data ), 'message' );
			}

			// first try and get an order object with matching client token and nonce
			$object = $this->get_order_from_tokenization_response( $client_token, $nonce );

			// if no order was found, look for a matching customer
			if ( ! $object instanceof \WC_Order ) {
				$object = $this->get_customer_from_tokenization_response( $client_token, $nonce );
			}

			// if no object could be found
			if ( ! $object instanceof \WC_Data ) {
				throw new Framework\SV_WC_API_Exception( 'No resource found with matching client token and nonce', 404 );
			}

			// build the response object
			$response = new \Atreus\WooCommerce\First_Data\Payeezy\API\Response\PaymentJS\Create_Payment_Token( $request_data );

			// some processor-level error
			if ( ! $response->transaction_approved() ) {
				throw new Framework\SV_WC_API_Exception( $response->get_status_message(), 200 ); // 200 is intentional, as the request succeeded
			}

			// store the payment.JS data to the found object
			$this->store_payment_js_tokenization_data( $object, $response );

			status_header( 200 );

		} catch ( Framework\SV_WC_API_Exception $exception ) {

			$message = sprintf(
				/* translators: Placeholders: %s - tokenization request error message */
				__( 'Could not process tokenization request. %s', 'woocommerce-gateway-firstdata' ),
				$exception->getMessage()
			);

			// if there was an order matched, mark it as failed
			if ( $object instanceof \WC_Order ) {
				$this->mark_order_as_failed( $object, $exception->getMessage(), $response );
			} else {
				$this->add_debug_message( $message, 'error' );
			}

			status_header( $exception->getCode() ?: 400 );
		}
	}


	/**
	 * Gets an in-progress order from the given client token and nonce.
	 *
	 * @since 4.7.0
	 *
	 * @param string $client_token temporary client token
	 * @param string $nonce temporary nonce
	 * @return \WC_Order|null
	 * @throws Framework\SV_WC_API_Exception
	 */
	private function get_order_from_tokenization_response( $client_token, $nonce ) {

		add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', function( $query, $query_vars ) {

			$client_token = ! empty( $query_vars['payeezy_client_token'] ) ? $query_vars['payeezy_client_token'] : '';
			$nonce        = ! empty( $query_vars['payeezy_nonce'] ) ? $query_vars['payeezy_nonce'] : '';

			$meta_query = [];

			if ( $client_token && $nonce ) {
				$meta_query['relation'] = 'AND';
			}

			if ( $client_token ) {

				$meta_query[] = [
					'key'     => self::PAYMENT_JS_CLIENT_TOKEN_META,
					'value'   => $client_token,
					'compare' => '=',
				];
			}

			if ( $nonce ) {

				$meta_query[] = [
					'key'     => self::PAYMENT_JS_NONCE_META,
					'value'   => $nonce,
					'compare' => '=',
				];
			}

			$query['meta_query'][] = $meta_query;

			return $query;

		}, 10, 2 );

		$orders = wc_get_orders( [
			'payeezy_client_token' => $client_token,
			'payeezy_nonce'        => $nonce,
			'payment_method'       => $this->get_id(),
			'limit'                => 1,
		] );

		$order = current( $orders );

		// if the order does not need payment, for sanity
		if ( $order instanceof \WC_Order && ! $order->needs_payment() ) {
			throw new Framework\SV_WC_API_Exception( 'The order has already been paid', 422 );
		}

		return $order instanceof \WC_Order ? $order : null;
	}


	/**
	 * Gets a user from the given client token and nonce.
	 *
	 * @since 4.7.0
	 *
	 * @param string $client_token temporary client token
	 * @param string $nonce temporary nonce
	 * @return \WC_Customer|null
	 * @throws Framework\SV_WC_API_Exception
	 */
	public function get_customer_from_tokenization_response( $client_token, $nonce ) {

		$customer   = null;
		$meta_query = [];

		if ( $client_token && $nonce ) {
			$meta_query['relation'] = 'AND';
		}

		if ( $client_token ) {

			$meta_query[] = [
				'key'     => self::PAYMENT_JS_CLIENT_TOKEN_META,
				'value'   => $client_token,
				'compare' => '=',
			];
		}

		if ( $nonce ) {

			$meta_query[] = [
				'key'     => self::PAYMENT_JS_NONCE_META,
				'value'   => $nonce,
				'compare' => '=',
			];
		}

		$users = get_users( [
			'meta_query' => [
				$meta_query,
			],
		] );

		$user = current( $users );

		if ( $user instanceof \WP_User ) {

			try {

				$customer = new \WC_Customer( $user->ID );

			} catch ( \Exception $exception ) {}
		}

		return $customer;
	}


	/**
	 * Stores the given response data to the given object.
	 *
	 * @since 4.7.0
	 *
	 * @param \WC_Data $object object to store the data
	 * @param \Atreus\WooCommerce\First_Data\Payeezy\API\Response\PaymentJS\Create_Payment_Token $response Payment.JS tokenization response
	 */
	private function store_payment_js_tokenization_data( \WC_Data $object, \Atreus\WooCommerce\First_Data\Payeezy\API\Response\PaymentJS\Create_Payment_Token $response ) {

		$meta_key = '_wc_' . $this->get_id() . '_payment_js_';

		$object->update_meta_data( $meta_key . 'token',          wc_clean( $response->get_payment_token() ) );
		$object->update_meta_data( $meta_key . 'last_four',      wc_clean( $response->get_last_four() ) );
		$object->update_meta_data( $meta_key . 'expiry_month',   wc_clean( $response->get_expiry_month() ) );
		$object->update_meta_data( $meta_key . 'expiry_year',    wc_clean( $response->get_expiry_year() ) );
		$object->update_meta_data( $meta_key . 'card_type',      wc_clean( $response->get_type() ) );
		$object->update_meta_data( $meta_key . 'card_name',      wc_clean( $response->get_account_name() ) );
		$object->update_meta_data( $meta_key . 'correlation_id', wc_clean( $response->get_transaction_id() ) );

		$object->save_meta_data();
	}


	/**
	 * Clears all temporary Payment.JS data from the given object.
	 *
	 * @since 4.7.0
	 *
	 * @param \WC_Data $object object to clear, such as a \WC_Order or \WC_Customer
	 */
	private function clear_payment_js_tokenization_data( \WC_Data $object ) {

		$meta_key = '_wc_' . $this->get_id() . '_payment_js_';

		// clear any payment method data that ends up a duplicate of what's usually stored to the order
		$object->delete_meta_data( $meta_key . 'token' );
		$object->delete_meta_data( $meta_key . 'last_four' );
		$object->delete_meta_data( $meta_key . 'expiry_month' );
		$object->delete_meta_data( $meta_key . 'expiry_year' );
		$object->delete_meta_data( $meta_key . 'card_type' );

		// clear the temporary authorize session data
		$object->delete_meta_data( self::PAYMENT_JS_CLIENT_TOKEN_META );
		$object->delete_meta_data( self::PAYMENT_JS_NONCE_META );

		$object->save_meta_data();
	}


	/**
	 * Adds Payment.JS payment data to the given order.
	 *
	 * If the order doesn't have the necessary meta set, we look for the order's customer meta if it has one.
	 *
	 * @since 4.7.0
	 *
	 * @param \WC_Order $order order to add the Payment.JS data to
	 * @return \WC_Order order with Payment.JS data added
	 */
	private function get_order_payment_js_tokenization_payment_data( \WC_Order $order ) {

		$object       = $order;
		$posted_token = Framework\SV_WC_Helper::get_posted_value( 'wc-' . $this->get_id_dasherized() . '-client-token' );
		$stored_token = $order->get_meta( self::PAYMENT_JS_CLIENT_TOKEN_META );

		// no client token stored to the order, so check the customer
		if ( ! $stored_token && $order->get_customer_id() ) {

			try {

				$object = new \WC_Customer( $order->get_customer_id() );

				$stored_token = $object->get_meta( self::PAYMENT_JS_CLIENT_TOKEN_META );

			} catch ( \Exception $exception ) {}
		}

		// if the POSTed token matches what's stored, set the order data
		if ( $posted_token === $stored_token ) {

			$meta_key = '_wc_' . $this->get_id() . '_payment_js_';

			$order->payment->token = $object->get_meta( $meta_key . 'token' );

			$order->payment->exp_month = zeroise( $object->get_meta( $meta_key . 'expiry_month' ), 2 );
			$order->payment->exp_year  = substr( (int) $object->get_meta( $meta_key . 'expiry_year' ), -2 );

			$order->payment->card_type = $object->get_meta( $meta_key . 'card_type' );

			$order->payment->last_four = $order->payment->account_number = $object->get_meta( $meta_key . 'last_four' );

			// the stage is set, so remove the temporary Payment.JS data since it's no good anymore
			$this->clear_payment_js_tokenization_data( $object );
		}

		return $order;
	}


	/**
	 * Gets an order that's prepared for an add payment method request.
	 *
	 * @since 4.7.0
	 *
	 * @return \WC_Order
	 * @throws \Exception
	 */
	protected function get_order_for_add_payment_method() {

		$order = parent::get_order_for_add_payment_method();

		if ( $this->should_use_payment_js() ) {
			$order = $this->get_order_payment_js_tokenization_payment_data( $order );
		}

		return $order;
	}


	/**
	 * Whether to perform pre-authorization when tokenizing card details.
	 *
	 * @since 4.2.3
	 *
	 * @return bool
	 */
	public function perform_pre_auth() {

		/**
		 * Filters whether the tokenization JS should pre-authorize cards details.
		 *
		 * @since 4.2.3
		 *
		 * @param bool $pre_auth whether the tokenization JS should pre-authorize cards details
		 */
		return (bool) apply_filters( 'wc_first_data_payeezy_credit_card_perform_pre_auth', false, $this );
	}


	/**
	 * Processes a payment.
	 *
	 * When using Payment.JS, this will effectively bypass payment at checkout, redirecting to a pay page.
	 *
	 * @since 4.7.0
	 *
	 * @param int $order_id the ID of the order being created and up for payment
	 * @return array payment data
	 */
	public function process_payment( $order_id ) {

		if ( $this->should_use_payment_js() && is_checkout() && ! is_checkout_pay_page() ) {

			$order  = wc_get_order( $order_id );
			$result = [
				'result'   => 'success',
				'redirect' => $order ? $order->get_checkout_payment_url( true ) : '',
			];

		} else {

			$result = parent::process_payment( $order_id );
		}

		return $result;
	}


	/**
	 * Determines if this is a hosted gateway.
	 *
	 * It effectively is if Payment.JS is enabled.
	 *
	 * @since 4.7.0
	 *
	 * @return bool
	 */
	public function is_hosted_gateway() {

		return $this->should_use_payment_js();
	}


}
