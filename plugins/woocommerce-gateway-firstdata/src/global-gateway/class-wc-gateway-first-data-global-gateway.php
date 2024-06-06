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
 * Global Gateway Class
 *
 * Main class for Global Gateway
 *
 * @since 4.0.0
 */
class WC_Gateway_First_Data_Global_Gateway extends Framework\SV_WC_Payment_Gateway_Direct {


	/** staging environment ID */
	const ENVIRONMENT_STAGING = 'staging';

	/** @var string store number */
	protected $store_number;

	/** @var string fully qualified path to PEM file */
	protected $pem_file_path;

	/** @var string store number for staging */
	protected $staging_store_number;

	/** @var string fully qualified path to PEM file for staging */
	protected $staging_pem_file_path;

	/** @var \WC_First_Data_Global_Gateway_API instance */
	protected $api;


	/**
	 * Setup the gateway
	 *
	 * @since 4.0.0
	 */
	public function __construct() {

		parent::__construct(
			WC_First_Data::GLOBAL_GATEWAY_ID,
			wc_first_data(),
			[
				'method_title'       => __( 'Global Gateway Credit Card', 'woocommerce-gateway-firstdata' ),
				'method_description' => __( 'Allow customers to securely pay using their credit card via First Data Global Gateway.', 'woocommerce-gateway-firstdata' ),
				'supports'           => [
					self::FEATURE_PRODUCTS,
					self::FEATURE_CARD_TYPES,
					self::FEATURE_PAYMENT_FORM,
					self::FEATURE_CREDIT_CARD_CHARGE,
					self::FEATURE_CREDIT_CARD_CHARGE_VIRTUAL,
					self::FEATURE_CREDIT_CARD_AUTHORIZATION,
					self::FEATURE_CREDIT_CARD_CAPTURE,
					self::FEATURE_REFUNDS,
				],
				'payment_type'       => self::PAYMENT_TYPE_CREDIT_CARD,
				'environments'       => $this->get_global_gateway_environments(),
				'card_types'         => [
					'VISA'   => 'Visa',
					'MC'     => 'MasterCard',
					'AMEX'   => 'American Express',
					'DISC'   => 'Discover',
					'DINERS' => 'Diners',
					'JCB'    => 'JCB',
				],
			]
		);

		// add a test amount input to the payment form
		add_filter( 'wc_first_data_global_gateway_payment_form_description', [ $this, 'render_test_amount_input' ] );
	}


	/** Admin Methods *********************************************************/


	/**
	 * Returns an array of form fields specific to Global Gateway
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_Payment_Gateway::get_method_form_fields()
	 * @return array of form fields
	 */
	protected function get_method_form_fields() {

		return [

			// production
			'store_number' => [
				'title'    => __( 'Store Number', 'woocommerce-gateway-firstdata' ),
				'type'     => 'text',
				'class'    => 'environment-field production-field',
				'desc_tip' => __( 'Your store number, as provided by First Data.', 'woocommerce-gateway-firstdata' ),
			],

			'pem_file_path'        => [
				'title'       => __( 'PEM File Path', 'woocommerce-gateway-firstdata' ),
				'type'        => 'text',
				'class'       => 'environment-field production-field',
				/* translators: Placeholders:  */
				'description' => sprintf( __( 'The full system path to your .PEM file from First Data. For security reasons you should store this outside of your web root.%1$sFor reference, your current web root path is: %2$s', 'woocommerce-gateway-firstdata' ),
					'<br/>', '<code>' . ABSPATH . '</code>' ),
			],

			// staging
			'staging_store_number' => [
				'title'    => __( 'Store Number', 'woocommerce-gateway-firstdata' ),
				'type'     => 'text',
				'class'    => 'environment-field staging-field',
				'desc_tip' => __( 'Your store number, as provided by First Data.', 'woocommerce-gateway-firstdata' ),
			],

			'staging_pem_file_path' => [
				'title'       => __( 'PEM File Path', 'woocommerce-gateway-firstdata' ),
				'type'        => 'text',
				'class'       => 'environment-field staging-field',
				'description' => sprintf( __( 'The full system path to your .PEM file from First Data. For security reasons you should store this outside of your web root.%1$sFor reference, your current web root path is: %2$s', 'woocommerce-gateway-firstdata' ),
					'<br/>', '<code>' . ABSPATH . '</code>' ),
			],
		];
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

		if ( $this->is_test_environment() ) {

			$id = 'wc-' . $this->get_id_dasherized() . '-test-amount';

			ob_start();
			?>
			<p class="form-row">
				<label for="<?php echo esc_attr( $id ); ?>">Test Amount <span style="font-size: 10px;" class="description">- Enter a <a href="https://www.firstdata.com/downloads/marketing-merchant/ffd-cte-v2.pdf">test amount</a> to trigger a specific error response, or leave blank to use the order total.</span>
				</label>
				<input style="width: 100px;" type="text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" />
			</p>
			<?php
			$desc .= ob_get_clean();
		}

		return $desc;
	}


	/** Gateway Methods *******************************************************/


	/**
	 * Get the order, adds Global Gateway specific info to the order:
	 *
	 * + test amount, when in staging mode and populated
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_Payment_Gateway_Direct::get_order()
	 * @param int|\WC_Order $order_id either order ID or WC_Order object
	 * @return \WC_Order
	 */
	public function get_order( $order_id ) {

		$order = parent::get_order( $order_id );

		// test amount when in staging environment
		if ( $this->is_test_environment() && ( $test_amount = Framework\SV_WC_Helper::get_posted_value( 'wc-' . $this->get_id_dasherized() . '-test-amount' ) ) ) {

			$order->payment_total = Framework\SV_WC_Helper::number_format( $test_amount );

			$order->payment->has_test_amount = true;
		}

		return $order;
	}


	/**
	 * Adds gateway-specific transaction data to the order, for credit cards
	 * this is:
	 *
	 * + timestamp - global gateway server timestamp
	 * + reference_number - provided by card processor
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_Payment_Gateway_Direct::add_transaction_data()
	 * @param \WC_Order $order the order object
	 * @param \WC_First_Data_Global_Gateway_API_Transaction_Response $response transaction response
	 */
	public function add_payment_gateway_transaction_data( $order, $response ) {

		// global gateway server timestamp
		if ( $response->get_timestamp() ) {
			$this->update_order_meta( $order, 'timestamp', $response->get_timestamp() );
		}

		// reference number from card processor
		if ( $response->get_reference_number() ) {
			$this->update_order_meta( $order, 'reference_number', $response->get_reference_number() );
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
		if ( ! $this->get_store_number() || ! $this->is_pem_file_readable() ) {
			$is_configured = false;
		}

		return $is_configured;
	}


	/**
	 * Get the API class instance
	 *
	 * @since 3.0.0
	 * @see Framework\SV_WC_Payment_Gateway::get_api()
	 * @return \WC_First_Data_Global_Gateway_API instance
	 */
	public function get_api() {

		if ( is_object( $this->api ) ) {
			return $this->api;
		}

		$path = wc_first_data()->get_plugin_path() . '/src/global-gateway/api';

		// base classes
		require_once( $path . '/class-wc-first-data-global-gateway-api.php' );

		// requests
		require_once( $path . '/requests/abstract-wc-first-data-global-gateway-api-request.php' );
		require_once( $path . '/requests/class-wc-first-data-global-gateway-api-transaction-request.php' );

		// responses
		require_once( $path . '/responses/abstract-wc-first-data-global-gateway-api-response.php' );
		require_once( $path . '/responses/class-wc-first-data-global-gateway-api-transaction-response.php' );

		return $this->api = new \WC_First_Data_Global_Gateway_API( $this );
	}


	/**
	 * Returns true if the current gateway environment is configured to 'staging'
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_Payment_Gateway::is_test_environment()
	 * @param string $environment_id optional environment id to check, otherwise defaults to the gateway current environment
	 * @return boolean true if $environment_id (if non-null) or otherwise the current environment is test
	 */
	public function is_test_environment( $environment_id = null ) {

		// if an environment is passed in, check that
		if ( ! is_null( $environment_id ) ) {
			return self::ENVIRONMENT_STAGING === $environment_id;
		}

		// otherwise default to checking the current environment
		return $this->is_environment( self::ENVIRONMENT_STAGING );
	}


	/**
	 * Returns the store number based on the current environment
	 *
	 * @since 4.0.0
	 * @param string $environment_id optional, defaults to production
	 * @return string store number
	 */
	public function get_store_number( $environment_id = null ) {

		if ( is_null( $environment_id ) ) {
			$environment_id = $this->get_environment();
		}

		return self::ENVIRONMENT_PRODUCTION === $environment_id ? $this->store_number : $this->staging_store_number;
	}


	/**
	 * Returns the PEM file path based on the current environment
	 *
	 * @since 4.0.0
	 * @param string $environment_id optional, defaults to production
	 * @return string PEM file path
	 */
	public function get_pem_file_path( $environment_id = null ) {

		if ( is_null( $environment_id ) ) {
			$environment_id = $this->get_environment();
		}

		return self::ENVIRONMENT_PRODUCTION === $environment_id ? $this->pem_file_path : $this->staging_pem_file_path;
	}


	/**
	 * Returns true if the PEM file is readable
	 *
	 * @since 4.0.0
	 * @return bool
	 */
	public function is_pem_file_readable() {

		return is_readable( $this->get_pem_file_path() );
	}


	/**
	 * Return an array of valid Global Gateway environments
	 *
	 * @since 4.0.0
	 * @return array
	 */
	protected function get_global_gateway_environments() {

		return [ self::ENVIRONMENT_PRODUCTION => __( 'Production', 'woocommerce-gateway-firstdata' ), self::ENVIRONMENT_STAGING => __( 'Staging', 'woocommerce-gateway-firstdata' ) ];
	}


}
