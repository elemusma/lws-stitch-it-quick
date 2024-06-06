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
use Kestrel\WooCommerce\First_Data\Clover\Blocks\Gateway_Blocks_Handler;

/**
 * The main class for the First Data Payeezy Gateway.  This class handles all the
 * non-gateway tasks such as verifying dependencies are met, loading the text
 * domain, etc.
 *
 * @since 2.0.0
 */
#[\AllowDynamicProperties]
class WC_First_Data extends Framework\SV_WC_Payment_Gateway_Plugin {


	/** version number */
	const VERSION = '5.2.0';

	/** @var \WC_First_Data single instance of this plugin */
	protected static $instance;

	/** the plugin identifier */
	const PLUGIN_ID = 'first_data';

	/** global gateway class name */
	const GLOBAL_GATEWAY_CLASS_NAME = 'WC_Gateway_First_Data_Global_Gateway';

	/** global gateway ID */
	const GLOBAL_GATEWAY_ID = 'first_data_global_gateway';

	/** payeezy gateway credit card class name */
	const PAYEEZY_GATEWAY_CREDIT_CARD_CLASS_NAME = 'WC_Gateway_First_Data_Payeezy_Gateway_Credit_Card';

	/** payeezy gateway credit card ID */
	const PAYEEZY_GATEWAY_CREDIT_CARD_ID = 'first_data_payeezy_gateway_credit_card';

	/** payeezy gateway echeck class name */
	const PAYEEZY_GATEWAY_ECHECK_CLASS_NAME = 'WC_Gateway_First_Data_Payeezy_Gateway_eCheck';

	/** payeezy gateway echeck ID */
	const PAYEEZY_GATEWAY_ECHECK_ID = 'first_data_payeezy_gateway_echeck';

	/** payeezy gateway credit card class name */
	const PAYEEZY_CREDIT_CARD_CLASS_NAME = 'WC_Gateway_First_Data_Payeezy_Credit_Card';

	/** payeezy gateway ID */
	const PAYEEZY_CREDIT_CARD_GATEWAY_ID = 'first_data_payeezy_credit_card';

	/** payeezy gateway echeck class name */
	const PAYEEZY_ECHECK_CLASS_NAME = 'WC_Gateway_First_Data_Payeezy_eCheck';

	/** payeezy gateway echeck ID */
	const PAYEEZY_ECHECK_GATEWAY_ID = 'first_data_payeezy_echeck';

	/** @var string Clover credit card gateway class */
	const CLOVER_CREDIT_CARD_CLASS_NAME = '\\Kestrel\\WooCommerce\\First_Data\\Clover\\Gateway\\Credit_Card';

	/** @var string Clover credit card gateway ID */
	const CLOVER_CREDIT_CARD_GATEWAY_ID = 'first_data_clover_credit_card';

	/** @var \WC_First_Data_Payeezy_AJAX the Payeezy JS AJAX instance */
	protected $payeezy_ajax_instance;


	/**
	 * Setup main plugin class
	 *
	 * @since 3.0
	 * @see Framework\SV_WC_Plugin::__construct()
	 */
	public function __construct() {

		parent::__construct(
			self::PLUGIN_ID,
			self::VERSION,
			[
				'text_domain'        => 'woocommerce-gateway-firstdata',
				'gateways'           => $this->get_active_gateways(),
				'supported_features' => $this->get_active_gateway_supported_features(),
				'require_ssl'        => true,
				'supports'           => $this->get_active_gateway_features(),
				'dependencies'       => [
					'php_extensions' => $this->get_active_gateway_dependencies(),
				],
			]
		);

		// include required files
		$this->includes();

		// handle switching between the included gateways
		if ( is_admin() && ! wp_doing_ajax() ) {

			// add a JS confirmation when changing to the Payeezy JS gateway
			add_action( 'pre_current_active_plugins', [ $this, 'add_change_gateway_js' ] );

			add_action( 'admin_action_wc_first_data_change_gateway', [ $this, 'change_gateway' ] );
		}
	}


	/**
	 * Include required files
	 *
	 * @since 4.0.0
	 */
	public function includes() {

		if ( $this->is_global_gateway_active() ) {

			$files = [ 'global-gateway/class-wc-gateway-first-data-global-gateway.php' ];

		} elseif ( $this->is_payeezy_gateway_active() ) {

			$files = [
				'payeezy-gateway/abstract-wc-gateway-first-data-payeezy-gateway.php',
				'payeezy-gateway/class-wc-gateway-first-data-payeezy-gateway-credit-card.php',
				'payeezy-gateway/class-wc-gateway-first-data-payeezy-gateway-echeck.php',
				'payeezy-gateway/Payment_Form.php',
				'payeezy-gateway/Capture.php',
			];

		} elseif ( $this->is_payeezy_active() ) {

			$files = [
				'payeezy/abstract-wc-gateway-first-data-payeezy.php',
				'payeezy/class-wc-gateway-first-data-payeezy-credit-card.php',
				'payeezy/class-wc-gateway-first-data-payeezy-echeck.php',
				'payeezy/class-wc-first-data-payeezy-ajax.php',
				'payeezy/Capture.php',
				'payeezy/api/responses/PaymentJS/Create_Payment_Token.php',
			];

		} elseif ( $this->is_clover_active() ) {

			$files = [
				'Clover/Gateway/Credit_Card.php',
				'Clover/Gateway/Payment_Form.php',
				'Clover/Payment_Token.php',
				'Clover/Payment_Tokens_Handler.php',
				'Clover/API.php',
				'Clover/API/Request/Request.php',
				'Clover/API/Request/Charge.php',
				'Clover/API/Request/Customer.php',
				'Clover/API/Request/Capture.php',
				'Clover/API/Request/Refund.php',
				'Clover/API/Response/Response.php',
				'Clover/API/Response/Charge.php',
				'Clover/API/Response/Customer.php',
				'Clover/API/Response/Refund.php',
			];

		}

		foreach ( $files as $file_path ) {
			require_once( $this->get_plugin_path() . '/src/' . $file_path );
		}
	}


	/**
	 * Gets the My Payment Methods handler instance.
	 *
	 * @since 4.7.3
	 *
	 * @return \Kestrel\WooCommerce\First_Data\My_Payment_Methods
	 */
	protected function get_my_payment_methods_instance() {

		require_once( $this->get_plugin_path() . '/src/My_Payment_Methods.php' );

		return new \Kestrel\WooCommerce\First_Data\My_Payment_Methods( $this );
	}


	/**
	 * Initializes the plugin.
	 *
	 * @since 4.4.0
	 */
	public function init_plugin() {

		parent::init_plugin();

		if ( class_exists( 'WC_First_Data_Payeezy_AJAX' ) && ( $gateway = $this->get_gateway( self::PAYEEZY_CREDIT_CARD_GATEWAY_ID ) ) ) {
			$this->payeezy_ajax_instance = new \WC_First_Data_Payeezy_AJAX( $gateway );
		}
	}


	/**
	 * Gets the Payeezy JS AJAX handler instance.
	 *
	 * @since 4.1.8
	 * @return \WC_First_Data_Payeezy_AJAX|null
	 */
	public function get_payeezy_ajax_instance() {

		return $this->payeezy_ajax_instance;
	}


	/**
	 * Determines if TLS v1.2 is required for API requests.
	 *
	 * @since 4.7.3
	 *
	 * @return bool
	 */
	public function require_tls_1_2() {

		return true;
	}


	/**
	 * Return deprecated/removed hooks
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_Plugin::get_deprecated_hooks()
	 * @return array
	 */
	protected function get_deprecated_hooks() {

		// hooks removed in 4.0.0
		$payeezy_gateway_v4_0_hooks = [
			'wc_gateway_firstdata_is_available' => [
				'version'     => '4.0.0',
				'removed'     => true,
				'replacement' => 'wc_gateway_first_data_payeezy_gateway_credit_card_is_available',
			],
			'wc_firstdata_api_timeout'          => [
				'version'     => '4.0.0',
				'removed'     => true,
				'replacement' => 'wc_first_data_payeezy_gateway_credit_card_http_request_args',
			],
			'wc_firstdata_icon'                 => [
				'version'     => '4.0.0',
				'removed'     => true,
				'replacement' => 'wc_first_data_payeezy_gateway_credit_card_icon',
			],
			'wc_firstdata_card_types'           => [
				'version'     => '4.0.0',
				'removed'     => true,
				'replacement' => 'wc_first_data_payeezy_gateway_credit_card_available_card_types',
			],
			'wc_first_data_validate_fields'     => [
				'version'     => '4.0.0',
				'removed'     => true,
				'replacement' => 'wc_payment_gateway_first_data_payeezy_gateway_credit_card_validate_credit_card_fields',
			],
			'wc_firstdata_manage_my_cards'      => [
				'version'     => '4.0.0',
				'removed'     => true,
				'replacement' => 'wc_first_data_payeezy_gateway_credit_card_manage_payment_methods_text',
			],
			'wc_firstdata_tokenize_card_text'   => [
				'version'     => '4.0.0',
				'removed'     => true,
				'replacement' => 'wc_first_data_payeezy_gateway_credit_card_tokenize_payment_method_text',
			],
		];

		return $this->is_payeezy_gateway_active() ? $payeezy_gateway_v4_0_hooks : [];
	}


	/** Gateway methods ******************************************************/


	/**
	 * Returns the supported features for the active gateway.
	 *
	 * @since 5.2.0
	 *
	 * @return array
	 */
	protected function get_active_gateway_supported_features() : array {

		$supported_features = [
			'hpos'   => true,
			'blocks' => [
				'cart'     => false,
				'checkout' => false,
			],
		];

		if ( $this->is_clover_active() ) {
			$supported_features['blocks']['cart']     = true;
			$supported_features['blocks']['checkout'] = true;
		}

		return $supported_features;
	}


	/**
	 * Builds the blocks handler instance.
	 *
	 * @since 5.2.0
	 *
	 * @return void
	 */
	protected function init_blocks_handler() : void {

		require_once( $this->get_framework_path() . '/Blocks/Blocks_Handler.php' );
		require_once( $this->get_framework_path() . '/payment-gateway/Blocks/Gateway_Blocks_Handler.php' );
		require_once( $this->get_plugin_path() . '/src/Clover/Blocks/Gateway_Blocks_Handler.php' );

		$this->blocks_handler = new Gateway_Blocks_Handler( $this );
	}


	/**
	 * Return the supported features for the active gateway
	 *
	 * @since 4.0.0
	 * @return array
	 */
	protected function get_active_gateway_features() {

		if ( $this->is_global_gateway_active() ) {

			return [
				self::FEATURE_CAPTURE_CHARGE,
			];

		} else {

			return [
				self::FEATURE_CAPTURE_CHARGE,
				self::FEATURE_MY_PAYMENT_METHODS,
			];
		}
	}


	/**
	 * Return the required dependencies for the active gateway
	 *
	 * @since 4.0.0
	 * @return array
	 */
	protected function get_active_gateway_dependencies() {

		return $this->is_global_gateway_active() ? [ 'SimpleXML', 'xmlwriter', 'dom' ] : [ 'json' ];
	}


	/**
	 * Return the activated gateways, either the legacy Global Gateway, Payeezy
	 * Gateway, Payeezy API, or Clover
	 *
	 * @since 4.0.0
	 * @return array
	 */
	protected function get_active_gateways() {

		$gateways = [];

		if ( $this->is_global_gateway_active() ) {

			$gateways = [
				self::GLOBAL_GATEWAY_ID => self::GLOBAL_GATEWAY_CLASS_NAME,
			];

		} elseif ( $this->is_payeezy_gateway_active() ) {

			$gateways = [
				self::PAYEEZY_GATEWAY_CREDIT_CARD_ID => self::PAYEEZY_GATEWAY_CREDIT_CARD_CLASS_NAME,
				self::PAYEEZY_GATEWAY_ECHECK_ID      => self::PAYEEZY_GATEWAY_ECHECK_CLASS_NAME,
			];

		} elseif ( $this->is_payeezy_active() ) {

			$gateways = [
				self::PAYEEZY_CREDIT_CARD_GATEWAY_ID => self::PAYEEZY_CREDIT_CARD_CLASS_NAME,
				self::PAYEEZY_ECHECK_GATEWAY_ID      => self::PAYEEZY_ECHECK_CLASS_NAME,
			];

		} elseif ( $this->is_clover_active() ){

			$gateways = [
				self::CLOVER_CREDIT_CARD_GATEWAY_ID => self::CLOVER_CREDIT_CARD_CLASS_NAME,
			];
		}

		return $gateways;
	}


	/**
	 * Return the active gateway ID
	 *
	 * @since 4.0.0
	 * @return string
	 */
	public function get_active_gateway() {

		return get_option( 'wc_first_data_active_gateway', self::CLOVER_CREDIT_CARD_GATEWAY_ID );
	}


	/**
	 * Returns true if legacy global gateway is active
	 *
	 * @since 4.0.0
	 * @return bool
	 */
	public function is_global_gateway_active() {

		return self::GLOBAL_GATEWAY_ID === $this->get_active_gateway();
	}


	/**
	 * Returns true if Payeezy Gateway is active
	 *
	 * @since 4.0.0
	 * @return bool
	 */
	public function is_payeezy_gateway_active() {

		return self::PAYEEZY_GATEWAY_CREDIT_CARD_ID === $this->get_active_gateway();
	}


	/**
	 * Returns true if Payeezy is active
	 *
	 * @since 4.0.0
	 * @return bool
	 */
	public function is_payeezy_active() {

		return self::PAYEEZY_CREDIT_CARD_GATEWAY_ID === $this->get_active_gateway();
	}


	/**
	 * Returns true if Clover is active
	 *
	 * @since 5.0.0
	 * @return bool
	 */
	public function is_clover_active() : bool {

		return self::CLOVER_CREDIT_CARD_GATEWAY_ID === $this->get_active_gateway();
	}


	/**
	 * Return the plugin action links.  This will only be called if the plugin
	 * is active.
	 *
	 * @since 3.2.0
	 * @param array $actions associative array of action names to anchor tags
	 * @return array associative array of plugin action links
	 */
	public function plugin_action_links( $actions ) {

		$custom_actions = [];

		$gateways = [
			self::GLOBAL_GATEWAY_ID,
			self::PAYEEZY_GATEWAY_CREDIT_CARD_ID,
			self::PAYEEZY_CREDIT_CARD_GATEWAY_ID,
			self::CLOVER_CREDIT_CARD_GATEWAY_ID,
		];

		// use <gateway> links
		foreach ( $gateways as $gateway ) {
			$custom_actions["change_gateway_{$gateway}"] = $this->get_change_gateway_link( $gateway );
		}

		unset( $custom_actions[ 'change_gateway_' . $this->get_active_gateway() ] );

		// add custom links to the front
		return array_merge( $custom_actions, Framework\SV_WC_Plugin::plugin_action_links( $actions ) );
	}


	/**
	 * Return the link for changing the active gateway
	 *
	 * @since 4.0.0
	 * @param string $gateway gateway ID
	 * @return string
	 */
	protected function get_change_gateway_link( $gateway ) {

		$params = [
			'action'  => 'wc_first_data_change_gateway',
			'gateway' => $gateway,
		];

		$url = wp_nonce_url( add_query_arg( $params, 'admin.php' ), $this->get_file() );

		switch ( $gateway ) {

			case self::GLOBAL_GATEWAY_ID:
				$gateway_name = esc_html__( 'Use Global Gateway', 'woocommerce-gateway-firstdata' );
				break;

			case self::PAYEEZY_GATEWAY_CREDIT_CARD_ID:
				$gateway_name = esc_html__( 'Use Payeezy Gateway', 'woocommerce-gateway-firstdata' );
				break;

			case self::PAYEEZY_CREDIT_CARD_GATEWAY_ID:
				$gateway_name = esc_html__( 'Use Payeezy JS', 'woocommerce-gateway-firstdata' );
				break;

			case self::CLOVER_CREDIT_CARD_GATEWAY_ID:
				$gateway_name = esc_html__( 'Use Clover', 'woocommerce-gateway-firstdata' );
				break;
		}

		return sprintf( '<a href="%1$s" title="%2$s">%2$s</a>', esc_url( $url ), $gateway_name );
	}


	/**
	 * Adds a JS confirmation when changing to the Payeezy JS gateway.
	 *
	 * @since 4.1.9
	 */
	public function add_change_gateway_js() {

		ob_start();

		?>
		( function( $ ) {

			$( document ).on( 'click', '.change_gateway_<?php echo esc_js( self::PAYEEZY_CREDIT_CARD_GATEWAY_ID ); ?>', function( e ) {

			var message = '<?php echo esc_js( __( 'This will enable the Payeezy JS gateway. You don\'t need to switch to this if you\'re using Payeezy Global Gateway e4.', 'woocommerce-gateway-firstdata' ) ); ?>';

			if ( ! confirm( message ) ) {
				e.preventDefault();
			}

			} );

		} ) ( jQuery );
		<?php

		wc_enqueue_js( ob_get_clean() );
	}


	/**
	 * Handles switching activated gateways from First Data Global Gateway and
	 * Payeezy Gateway/Payeezy, and vice-versa
	 *
	 * @since 3.0.0
	 */
	public function change_gateway() {

		// security check
		if ( ! wp_verify_nonce( $_GET['_wpnonce'], $this->get_file() ) || ! current_user_can( 'manage_woocommerce' ) ) {
			wp_redirect( wp_get_referer() );
			exit;
		}

		$valid_gateways = [
			self::GLOBAL_GATEWAY_ID,
			self::PAYEEZY_GATEWAY_CREDIT_CARD_ID,
			self::PAYEEZY_CREDIT_CARD_GATEWAY_ID,
			self::CLOVER_CREDIT_CARD_GATEWAY_ID,
		];

		if ( empty( $_GET['gateway'] ) || ! in_array( $_GET['gateway'], $valid_gateways, true ) ) {
			wp_redirect( wp_get_referer() );
			exit;
		}

		// switch the gateway
		update_option( 'wc_first_data_active_gateway', $_GET['gateway'] );

		$return_url = add_query_arg( [ 'gateway_switched' => 1 ], 'plugins.php' );

		// back to whence we came
		wp_redirect( $return_url );
		exit;
	}


	/** Admin methods *********************************************************/


	/**
	 * Adds a notice when gateways are switched.
	 *
	 * @since 3.4.2
	 */
	public function add_admin_notices() {

		parent::add_admin_notices();

		// show a notice when switching between the gateways
		$this->add_gateway_switch_admin_notice();
	}


	/**
	 * Adds admin notices that are delayed under gateway settings can be loaded
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_Payment_Gateway_Plugin::add_delayed_admin_notices()
	 */
	public function add_delayed_admin_notices() {

		parent::add_delayed_admin_notices();

		$this->add_settings_admin_notices();
	}


	/**
	 * Render a notice when switching between the gateways
	 *
	 * @since 3.4.2
	 */
	private function add_gateway_switch_admin_notice() {

		if ( isset( $_GET['gateway_switched'] ) ) {

			if ( $this->is_global_gateway_active() ) {
				$message = __( 'First Data Global Gateway is now active.', 'woocommerce-gateway-firstdata' );

			} elseif ( $this->is_payeezy_gateway_active() ) {
				$message = __( 'First Data Payeezy Gateway is now active.', 'woocommerce-gateway-firstdata' );

			} elseif ( $this->is_payeezy_active() ) {
				$message = __( 'First Data Payeezy is now active.', 'woocommerce-gateway-firstdata' );

			} elseif ( $this->is_clover_active() ) {
				$message = __( 'Clover by First Data is now active.', 'woocommerce-gateway-firstdata' );
			}

			$this->get_admin_notice_handler()->add_admin_notice( $message, 'gateway-switched', [ 'dismissible' => false ] );
		}
	}


	/**
	 * Render settings-related admin notices, currently:
	 *
	 * + Global Gateway is PEM file readable
	 * + Payeezy Gateway Key ID/HMAC Key settings required
	 *
	 * @since 3.4.2
	 */
	private function add_settings_admin_notices() {

		if ( $this->is_global_gateway_active() ) {

			// check if the PEM file path entered is readable and render a notice if not
			if ( $this->is_payment_gateway_configuration_page( self::GLOBAL_GATEWAY_CLASS_NAME ) ) {

				$global_gateway_settings = $this->get_gateway_settings( self::GLOBAL_GATEWAY_ID );

				// check after store number and PEM file path have been entered
				if ( ! empty( $global_gateway_settings['store_number'] ) && ! empty( $global_gateway_settings['pem_file_path'] ) &&
				     'production' === $global_gateway_settings['environment'] && ! is_readable( $global_gateway_settings['pem_file_path'] ) ) {
					$message = sprintf( __( '%1$sWooCommerce First Data Global Gateway requires additional configuration!%2$s The path entered for the First Data PEM file is either invalid or unreadable. Please ask your hosting provider for assistance with the correct file path. Need help? %3$sRead the documentation%4$s.', 'woocommerce-gateway-first-data' ),
						'<strong>', '</strong>',
						'<a href="http://docs.woocommerce.com/document/firstdata">', '</a>'
					);

					$this->get_admin_notice_handler()->add_admin_notice( $message, 'pem-file-path', [ 'dismissible' => false, 'notice_class' => 'error' ] );
				}
			}

		} elseif ( $this->is_payeezy_gateway_active() ) {

			// payeezy/payeezy gateway notices
			$payeezy_gateway_settings = $this->get_gateway_settings( self::PAYEEZY_GATEWAY_CREDIT_CARD_ID );

			// TODO: prevent this from showing when in demo mode
			if ( ! empty( $payeezy_gateway_settings['gateway_id'] ) && ! empty( $payeezy_gateway_settings['gateway_password'] ) &&
			     ( empty( $payeezy_gateway_settings['key_id'] ) || empty( $payeezy_gateway_settings['hmac_key'] ) ) ) {

				$message = sprintf( __( '%1$sWooCommerce First Data Payeezy Gateway requires additional configuration!%2$s You must %3$sconfigure the Key ID and HMAC Key settings%4$s for transaction security. %5$sRead the documentation%6$s to learn how.', 'woocommerce-gateway-first-data' ),
					'<strong>', '</strong>',
					'<a href="' . esc_url( $this->get_settings_url( self::PAYEEZY_GATEWAY_CREDIT_CARD_ID ) ) . '">', '</a>',
					'<a href="http://docs.woocommerce.com/document/firstdata#api-security">', '</a>'
				);

				$this->get_admin_notice_handler()->add_admin_notice( $message, 'key-hmac-upgrade', [ 'dismissible' => false, 'notice_class' => 'error' ] );
			}

		} elseif ( $this->is_payeezy_active() && wc_string_to_bool( get_option( 'wc_first_data_payeezy_display_payeezy_js_settings' ) ) ) {

			$gateway = $this->get_gateway();

			if ( $gateway instanceof \WC_Gateway_First_Data_Payeezy_Credit_Card && $gateway->is_enabled() && ! $gateway->is_payment_js_configured() ) {

				$payment_js_doc = sprintf( '%s#paymentjs-update', $this->get_documentation_url( 'first-data-payeezy' ) );

				$this->get_admin_notice_handler()->add_admin_notice(
					sprintf(
					/* translations: Placeholders: %1$s - opening <a> HTML link tag, %2$s - closing </a> HTML link tag, %3$s - opening <a> HTML link tag, %4$s - closing </a> HTML link tag */
						__( 'Heads up! First Data Payeezy now requires processing payments through Payment.JS. Please %1$sread the updated documentation%2$s and %3$sconfigure Payment.JS%4$s.', 'woocommerce-gateway-firstdata' ),
						'<a href="' . esc_url( $payment_js_doc ) . '">', '</a>',
						'<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . $gateway->get_id() ) ) . '">', '</a>'
					),
					'payment-js-disabled',
					[
						'notice_class' => 'notice-warning',
						'dismissible'  => false,
					]
				);
			}
		}
	}


	/** Helper methods ******************************************************/


	/**
	 * Main First Data Instance, ensures only one instance is/can be loaded
	 *
	 * @since 3.6.0
	 * @see wc_firstdata()
	 * @return \WC_First_Data
	 */
	public static function instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Gets the plugin documentation URL.
	 *
	 * @since 3.2
	 *
	 * @param $gateway string optional, to retrieve a documentation page of a specific gateway
	 * @return string documentation URL
	 */
	public function get_documentation_url( $gateway = '' ) {

		switch ( $gateway ) {
			case 'first-data-payeezy' :
				$url = 'https://docs.woocommerce.com/document/woocommerce-first-data-payeezy/';
				break;
			case 'first-data-payeezy-gateway' :
				$url = 'https://docs.woocommerce.com/document/woocommerce-first-data-payeezy-gateway/';
				break;
			case 'first-data-global-gateway' :
				$url = 'https://docs.woocommerce.com/document/woocommerce-first-data-global-gateway/';
				break;
			case 'first-data-clover':
				$url = 'https://woocommerce.com/document/clover/';
				break;
			default :
				$url = 'https://woocommerce.com/document/clover/';
				break;
		}

		return $url;
	}


	/**
	 * Gets the plugin support URL
	 *
	 * @since 3.7.0
	 * @see Framework\SV_WC_Plugin::get_support_url()
	 * @return string
	 */
	public function get_support_url() {

		return 'https://woocommerce.com/my-account/marketplace-ticket-form/';
	}


	/**
	 * Returns the plugin name, localized
	 *
	 * @since 3.2
	 * @see Framework\SV_WC_Payment_Gateway::get_plugin_name()
	 * @return string the plugin name
	 */
	public function get_plugin_name() {

		if ( $this->is_clover_active() ) {
			return __( 'Clover by First Data', 'woocommerce-gateway-firstdata' );
		} else {
			return __( 'WooCommerce First Data', 'woocommerce-gateway-firstdata' );
		}
	}


	/**
	 * Initializes the lifecycle handler.
	 *
	 * @since 4.4.0
	 */
	protected function init_lifecycle_handler() {

		require_once( $this->get_plugin_path() . '/src/Lifecycle.php' );

		$this->lifecycle_handler = new \Kestrel\WooCommerce\First_Data\Lifecycle( $this );
	}


	/**
	 * Gets the plugin file and directory name.
	 *
	 * @since 4.4.0
	 *
	 * @return string
	 */
	public function get_plugin_file() {

		$slug = dirname( plugin_basename( $this->get_file() ) );

		return trailingslashit( $slug ) . 'woocommerce-gateway-first-data.php';
	}


	/**
	 * Returns __FILE__
	 *
	 * @since 3.2
	 * @return string the full path and filename of the plugin file
	 */
	protected function get_file() {

		return __FILE__;
	}


}


/**
 * Returns the One True Instance of First Data Payeezy Gateway.
 *
 * @since 4.4.0
 *
 * @return WC_First_Data
 */
function wc_first_data() {

	return \WC_First_Data::instance();
}
