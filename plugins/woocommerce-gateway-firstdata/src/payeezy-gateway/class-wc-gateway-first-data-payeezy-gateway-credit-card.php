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
 * Payeezy Gateway Credit Card Class
 *
 * Handles credit card specific functionality
 *
 * @since 4.0.0
 */
#[\AllowDynamicProperties]
class WC_Gateway_First_Data_Payeezy_Gateway_Credit_Card extends WC_Gateway_First_Data_Payeezy_Gateway {


	/** @var string whether partial redemption is enabled or not */
	protected $partial_redemption;

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
			\WC_First_Data::PAYEEZY_GATEWAY_CREDIT_CARD_ID,
			wc_first_data(),
			array(
				'method_title'       => __( 'Payeezy Gateway Credit Card', 'woocommerce-gateway-firstdata' ),
				'method_description' => __( 'Allow customers to securely pay using their credit card via Payeezy Gateway.', 'woocommerce-gateway-firstdata' ),
				'supports'           => array(
					self::FEATURE_PRODUCTS,
					self::FEATURE_CARD_TYPES,
					self::FEATURE_TOKENIZATION,
					self::FEATURE_PAYMENT_FORM,
					self::FEATURE_CREDIT_CARD_CHARGE,
					self::FEATURE_CREDIT_CARD_CHARGE_VIRTUAL,
					self::FEATURE_CREDIT_CARD_AUTHORIZATION,
					self::FEATURE_CREDIT_CARD_CAPTURE,
					self::FEATURE_DETAILED_CUSTOMER_DECLINE_MESSAGES,
					self::FEATURE_REFUNDS,
					self::FEATURE_VOIDS,
					self::FEATURE_ADD_PAYMENT_METHOD,
					self::FEATURE_TOKEN_EDITOR,
				),
				'payment_type'       => self::PAYMENT_TYPE_CREDIT_CARD,
				'environments'       => $this->get_payeezy_gateway_environments(),
				'shared_settings'    => $this->shared_settings_names,
				'card_types' => array(
					'VISA'    => 'Visa',
					'MC'      => 'MasterCard',
					'AMEX'    => 'American Express',
					'DISC'    => 'Discover',
					'DINERS'  => 'Diners',
					'JCB'     => 'JCB',
				),
			)
		);

		// add a test amount input to the payment form
		add_filter( 'wc_first_data_payeezy_gateway_credit_card_payment_form_description', array( $this, 'render_test_amount_input' ) );
	}


	/**
	 * Initializes the capture handler.
	 *
	 * @since 4.4.0
	 */
	public function init_capture_handler() {

		$this->capture_handler = new \Kestrel\WooCommerce\First_Data\Payeezy_Gateway\Capture( $this );
	}


	/** Admin Methods *********************************************************/


	/**
	 * Returns an array of form fields specific for the method
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_Payment_Gateway::get_method_form_fields()
	 * @return array of form fields
	 */
	protected function get_method_form_fields() {

		// add a description for the tokenization checkbox
		$this->form_fields['tokenization']['description'] = sprintf(
		/* translators: Placeholders: %1$s - <strong> tag, %2$s - </strong> tag */
			__( 'Note that you %1$smust first%2$s enable TransArmor Multi-Pay token processing in your account by contacting your merchant services provider for your token and configuring your account with it.', 'woocommerce-gateway-firstdata' ),
			'<strong>', '</strong>'
		);

		$form_fields = array(

			'soft_descriptors_title' => array(
				'title' => __( 'Soft Descriptor Settings', 'woocommerce-gateway-firstdata' ),
				'type'  => 'title',
			),

			'soft_descriptors_enabled' => array(
				'title'       => __( 'Soft Descriptors', 'woocommerce-gateway-firstdata' ),
				'label'       => __( 'Enable soft descriptors', 'woocommerce-gateway-firstdata' ),
				'type'        => 'checkbox',
				'description' => 'All of the soft descriptors are optional.  If you would like to use Soft Descriptors, please contact your First Data Relationship Manager or Sales Rep and have them set your "Foreign Indicator" in your "North Merchant Manager File" to "5".',
				'default'     => 'no',
			),

			'soft_descriptor_dba_name' => array(
				'title'    => __( 'DBA Name', 'woocommerce-gateway-firstdata' ),
				'desc_tip' => __( 'Business name.', 'woocommerce-gateway-firstdata' ),
				'class'    => 'soft-descriptor',
				'type'     => 'text',
			),

			'soft_descriptor_street' => array(
				'title'    => __( 'Street', 'woocommerce-gateway-firstdata' ),
				'desc_tip' => __( 'Business address.', 'woocommerce-gateway-firstdata' ),
				'class'    => 'soft-descriptor',
				'type'     => 'text',
			),

			'soft_descriptor_city' => array(
				'title'    => __( 'City', 'woocommerce-gateway-firstdata' ),
				'desc_tip' => __( 'Business city.', 'woocommerce-gateway-firstdata' ),
				'class'    => 'soft-descriptor',
				'type'     => 'text',
			),

			'soft_descriptor_region' => array(
				'title'    => __( 'Region', 'woocommerce-gateway-firstdata' ),
				'desc_tip' => __( 'Business region.', 'woocommerce-gateway-firstdata' ),
				'class'    => 'soft-descriptor',
				'type'     => 'text',
			),

			'soft_descriptor_postal_code' => array(
				'title'    => __( 'Postal Code', 'woocommerce-gateway-firstdata' ),
				'desc_tip' => __( 'Business postal/zip code.', 'woocommerce-gateway-firstdata' ),
				'class'    => 'soft-descriptor',
				'type'     => 'text',
			),

			'soft_descriptor_country_code' => array(
				'title'    => __( 'Country Code', 'woocommerce-gateway-firstdata' ),
				'desc_tip' => __( 'Business country.', 'woocommerce-gateway-firstdata' ),
				'class'    => 'soft-descriptor',
				'type'     => 'text',
			),

			'soft_descriptor_mid' => array(
				'title'    => __( 'MID', 'woocommerce-gateway-firstdata' ),
				'desc_tip' => __( 'Merchant ID.', 'woocommerce-gateway-firstdata' ),
				'class'    => 'soft-descriptor',
				'type'     => 'text',
			),

			'soft_descriptor_mcc' => array(
				'title'    => __( 'MCC', 'woocommerce-gateway-firstdata' ),
				'desc_tip' => __( 'Your Merchant Category Code.', 'woocommerce-gateway-firstdata' ),
				'class'    => 'soft-descriptor',
				'type'     => 'text',
			),

			'soft_descriptor_merchant_contact_info' => array(
				'title'    => __( 'Merchant Contact Info', 'woocommerce-gateway-firstdata' ),
				'desc_tip' => __( 'Merchant contact information.', 'woocommerce-gateway-firstdata' ),
				'class'    => 'soft-descriptor',
				'type'     => 'text',
			),
		);

		return array_merge( parent::get_method_form_fields(), $form_fields );
	}


	/**
	 * Add the partial redemption setting immediately after the authorization
	 * type select
	 *
	 * @since 4.0.0
	 * @param array $form_fields Associative array of form fields list
	 * @return array
	 */
	protected function add_authorization_charge_form_fields( $form_fields ) {

		$form_fields = parent::add_authorization_charge_form_fields( $form_fields );

		$form_fields['partial_redemption'] = array(
			'title'    => __( 'Partial Redemption', 'woocommerce-gateway-firstdata' ),
			'label'    => __( 'Enable Partial Redemption', 'woocommerce-gateway-firstdata' ),
			'type'     => 'checkbox',
			'desc_tip' => __( 'A partial redemption will be returned if only a portion of the requested funds are available. For example, if a transaction is submitted for $100, but only $80 is available on the customer\'s card, the $80 will be authorized or captured when this property is set to true. This property can be used for all types of pre-authorization and purchase transactions.',  'woocommerce-gateway-firstdata' ),
			'default'  => 'no',
		);

		return $form_fields;
	}


	/**
	 * Display the settings page with some additional JS to hide the soft descriptor
	 * settings if not enabled
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_Payment_Gateway::admin_options()
	 */
	public function admin_options() {

		parent::admin_options();

		ob_start();

		?>
		$( '#woocommerce_first_data_payeezy_gateway_credit_card_soft_descriptors_enabled' ).change( function() {
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

			$defaults['account-number'] = '4111111111111111';
		}

		return $defaults;
	}


	/**
	 * Render a test amount input after the payment form description. Admins
	 * can use this to override the order total and set a specific amount for
	 * testing error conditions
	 *
	 * @since 4.0.0
	 * @param string $desc payment form description HTML
	 * @return string
	 */
	public function render_test_amount_input( $desc ) {

		if ( $this->is_test_environment() && ! is_add_payment_method_page() ) {

			$id = 'wc-' . $this->get_id_dasherized() . '-test-amount';

			ob_start();
			?>
			<p class="form-row">
				<label for="<?php echo esc_attr( $id ); ?>">Test Amount <span style="font-size: 10px;" class="description">- Enter a <a href="https://support.payeezy.com/hc/en-us/articles/204504175-How-to-generate-unsuccessful-transactions-during-testing-">test amount</a> to trigger a specific error response, or leave blank to use the order total.</span></label>
				<input style="width: 100px;" type="text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" />
			</p>
			<?php
			$desc .= ob_get_clean();
		}

		return $desc;
	}


	/** Gateway Methods *******************************************************/


	/**
	 * Payeezy Gateway tokenizes payment methods with the sale
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_Payment_Gateway_Direct::tokenize_with_sale()
	 * @return bool
	 */
	public function tokenize_with_sale() {
		return true;
	}


	/**
	 * Get the order, adds Payeezy Gateway specific info to the order:
	 *
	 * + payment->partial_redemption (bool) true if partial redemption enabled
	 * + payment->full_type (string) Payeezy Gateway card type for tokenized transactions
	 * + payment->has_soft_descriptors (bool) true if soft descriptors are enabled and populated
	 * + payment->soft_descriptors (array) pre-sanitized array of soft descriptors for transaction
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_Payment_Gateway_Direct::get_order()
	 * @param int|\WC_Order $order_id Order ID
	 * @return \WC_Order
	 */
	public function get_order( $order_id ) {

		$order = parent::get_order( $order_id );

		// partial redemption flag
		$order->payment->partial_redemption = $this->partial_redemption_enabled();

		// required for transactions with tokens
		$order->payment->full_type = $this->get_full_card_type( $order );

		// soft descriptors
		$order->payment->has_soft_descriptors = $this->soft_descriptors_enabled();

		if ( $this->soft_descriptors_enabled() ) {

			$order->payment->soft_descriptors = $this->get_soft_descriptors();
		}

		// test amount when in demo environment
		if ( $this->is_test_environment() && ( $test_amount = Framework\SV_WC_Helper::get_posted_value( 'wc-' . $this->get_id_dasherized() . '-test-amount' ) ) ) {
			$order->payment_total = Framework\SV_WC_Helper::number_format( $test_amount );
		}

		return $order;
	}


	/**
	 * Get the order for capturing, adds:
	 *
	 * + capture->auth_num (string) authorization number for transaction
	 *
	 * @since 4.0.0
	 *
	 * @see Framework\SV_WC_Payment_Gateway_Direct::get_order_for_capture()
	 * @param \WC_Order|int $order the order being processed
	 * @param float|null $amount amount to capture or null for the full order amount
	 * @return \WC_Order
	 */
	public function get_order_for_capture( $order, $amount = NULL ) {

		$order = parent::get_order_for_capture( $order, $amount );

		$order->capture->auth_num = $this->get_order_meta( $order, 'authorization_code' );

		return $order;
	}


	/**
	 * Get the order for refunding/voiding, adds:
	 *
	 * + capture->auth_num (string) authorization number for transaction
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_Payment_Gateway::get_order_for_refund()
	 * @param int|\WC_Order $order either order ID or WC_Order object
	 * @param float $amount Order refund amount
	 * @param string $reason Refund reason
	 * @return int|\WC_Order
	 */
	protected function get_order_for_refund( $order, $amount, $reason ) {

		$order = parent::get_order_for_refund( $order, $amount, $reason );

		// if an authorize-only transaction was captured, use the capture trans ID/auth code instead
		if ( $capture_trans_id = $this->get_order_meta( $order, 'capture_trans_id' ) ) {

			$order->refund->trans_id = $capture_trans_id;
			$order->refund->auth_num = $this->get_order_meta( $order, 'capture_authorization_code' );

		} else {

			$order->refund->auth_num = $this->get_order_meta( $order, 'authorization_code' );
		}

		return $order;
	}


	/**
	 * Adds gateway-specific transaction data to the order, for credit cards
	 * this is:
	 *
	 * + sequence number
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_Payment_Gateway_Direct::add_transaction_data()
	 * @param \WC_Order $order the order object
	 * @param \WC_First_Data_Payeezy_Gateway_API_Transaction_Response $response transaction response
	 */
	public function add_payment_gateway_transaction_data( $order, $response ) {

		if ( $response->get_sequence_no() ) {
			$this->update_order_meta( $order, 'sequence_no', $response->get_sequence_no() );
		}
	}


	/**
	 * Save transactional data when a customer adds a new credit via the
	 * add payment method flow
	 *
	 * @since 4.0.0
	 * @param \WC_First_Data_Payeezy_Gateway_API_Transaction_Response $response
	 * @return array
	 */
	protected function get_add_payment_method_payment_gateway_transaction_data( $response ) {

		$data = array();

		// transaction ID
		if ( $response->get_transaction_id() ) {
			$data['trans_id'] = $response->get_transaction_id();
		}

		if ( $response->get_authorization_code() ) {
			$data['authorization_code'] = $response->get_authorization_code();
		}

		return $data;
	}


	/**
	 * Override the default get_order_meta() to provide backwards compatibility
	 * for Subscription/Pre-Order tokens added in versions prior to 4.0.0
	 * , as the meta keys were not scoped per gateway. A bulk upgrade of all meta keys while trying to
	 * account for the gateway used for the original order (only defined by the
	 * payment type saved) is too risky given the potential for timeouts.
	 *
	 * Eventually this method can be removed and an upgrade routine can be added
	 * to catch any straggling meta keys that haven't been updated.
	 *
	 * @TODO: Remove me in March 2017 @MR
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_Payment_Gateway::get_order_meta()
	 * @param int|string $order_id ID for order to get meta for
	 * @param string $key meta key
	 * @return mixed
	 */
	public function get_order_meta( $order_id, $key ) {

		// pre v4.0.0 token order meta handling
		if ( 'payment_token' === $key ) {

			$order = wc_get_order( $order_id );

			if ( ! $order->meta_exists( $this->get_order_meta_prefix() . 'payment_token' ) &&
				 $order->meta_exists( '_wc_firstdata_transarmor_token' ) ) {

				$token = $order->get_meta( '_wc_firstdata_transarmor_token' );

				$this->update_order_meta( $order_id, 'payment_token', $token );

				return $token;
			}
		}

		return parent::get_order_meta( $order_id, $key );
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

		if ( isset( $meta[ $this->get_id() ] ) ) {
			$meta[ $this->get_id() ]['post_meta'][ $this->get_order_meta_prefix() . 'payment_token' ]['label'] = __( 'TransArmor Token', 'woocommerce-gateway-firstdata' );
		}

		$customer_id_meta_key = $this->get_order_meta_prefix() . 'customer_id';

		// if the customer ID meta doesn't exist, that means the subscription was created with
		// pre-4.0.0 FirstData, so the field can be removed and ignored during admin validation.
		if ( ! $subscription->meta_exists( $customer_id_meta_key ) ) {
			unset( $meta[ $this->get_id() ]['post_meta'][ $customer_id_meta_key ] );
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
	 * Get the full card type for a given order, Payeezy Gateway requires this
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

		return isset( $types[ $order->payment->card_type ] ) ? $types[ $order->payment->card_type ] : null;
	}


	/**
	 * Returns true if partial redemption is enabled
	 *
	 * @since 4.0.0
	 * @return bool
	 */
	public function partial_redemption_enabled() {

		return 'yes' === $this->partial_redemption;
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

		$descriptor_names = [
			'dba_name',
			'street',
			'city',
			'region',
			'postal_code',
			'country_code',
			'mid',
			'mcc',
			'merchant_contact_info',
		];

		$descriptors = [];

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


}
