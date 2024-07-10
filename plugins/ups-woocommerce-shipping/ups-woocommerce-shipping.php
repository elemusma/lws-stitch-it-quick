<?php
/*
	Plugin Name: WooCommerce UPS Shipping Plugin with Print Label
	Plugin URI: https://www.pluginhive.com/product/woocommerce-ups-shipping-plugin-with-print-label/
	Description: Lets you completely automate UPS shipping. Display live shipping rates on WooCommerce Cart & Checkout pages, Pay Postage, Print Labels Manually or in Bulk, Track shipments, and more.
	Version: 6.1.2
	Author: PluginHive
	Author URI: https://www.pluginhive.com/about/
	WC requires at least: 3.0.0
	Text Domain: ups-woocommerce-shipping
	WC tested up to: 9.0.2
	Requires Plugins: woocommerce
*/

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

// Define PH_UPS_PLUGIN_VERSION
if (!defined('PH_UPS_PLUGIN_VERSION')) {
	define('PH_UPS_PLUGIN_VERSION', '6.1.2');
}

// Define UPS Global ID
if (!defined('WF_UPS_ID')) {

	define("WF_UPS_ID", "wf_shipping_ups");
}

// Define UPS Zone ID.
if ( ! defined( 'PH_WC_UPS_ZONE_SHIPPING' ) ) {

	define( 'PH_WC_UPS_ZONE_SHIPPING', 'ph_ups_shipping' );
}

// Define plugin directory path.
if( !defined( 'PH_WC_UPS_PLUGIN_DIR_PATH' )) {
	
	define( 'PH_WC_UPS_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ));
}

// Include Common Class
if (!class_exists('Ph_UPS_Woo_Shipping_Common')) {

	require_once 'class-ph-ups-woo-shipping-common.php';
}

// Include API Manager
if (!class_exists('PH_UPS_API_Manager')) {

	include_once('ph-api-manager/ph_api_manager_ups.php');
}

// UPS Config
include_once('ph-ups-config.php');

if (class_exists('PH_UPS_API_Manager')) {

	$ph_ups_api_obj = new PH_UPS_API_Manager(__FILE__, '', PH_UPS_PLUGIN_VERSION, 'plugin', PH_UPS_Config::PH_UPS_API_MANAGER_ENDPOINT, 'UPS', 'ups-woocommerce-shipping');
}

// Define PH_UPS_DEBUG_LOG_FILE_NAME
if (!defined( 'PH_UPS_DEBUG_LOG_FILE_NAME' )) {

	define( 'PH_UPS_DEBUG_LOG_FILE_NAME', 'PluginHive-UPS-Error-Debug-Log' );
}

// Constants.
if (!class_exists('PH_WC_UPS_Constants')) {

	include_once 'includes/constants/class-ph-ups-constants.php';
}

// Include Common Utils Class
if (!class_exists('PH_WC_UPS_Common_Utils')) {

	require_once PH_WC_UPS_PLUGIN_DIR_PATH . 'includes/utils/class-ph-ups-common-utils.php';
}

// Include API Invoker.
if (!class_exists('Ph_Ups_Api_Invoker')) {

	require_once 'includes/api-handler/class-ph-ups-api-invoker.php';
}

// Include Endpoint Dispatcher.
if (!class_exists('Ph_Ups_Endpoint_Dispatcher')) {

	require_once 'includes/api-handler/class-ph-ups-endpoint-dispatcher.php';
}

function ph_ups_pre_activation_check()
{
	//check if basic version is there
	if (is_plugin_active('ups-woocommerce-shipping-method/ups-woocommerce-shipping.php')) {

		deactivate_plugins(basename(__FILE__));

		wp_die(__("Oops! You tried installing the premium version without deactivating and deleting the basic version. Kindly deactivate and delete UPS(Basic) Woocommerce Extension and then try again", "ups-woocommerce-shipping"), "", array('back_link' => 1));
	}
}

register_activation_hook(__FILE__, 'ph_ups_pre_activation_check');

// Required functions
if (!class_exists('PH_UPS_Dependencies')) {

	require_once('dependency/class-ph-ups-dependencies.php');
}

// WC active check
if (!PH_UPS_Dependencies::ph_is_woocommerce_active()) {

	return;
}

if (!defined('WF_UPS_ADV_DEBUG_MODE')) {

	define("WF_UPS_ADV_DEBUG_MODE", "off"); // Turn 'on' for demo/test sites.
}

if (!defined('PH_UPS_LABEL_MIGRATION_OPTION')) {

	define("PH_UPS_LABEL_MIGRATION_OPTION", "ph_ups_post_table_label_migrated");
}

// Define PH_UPS_BANNER_OPTION_ID
if (!defined('PH_UPS_BANNER_OPTION_ID')) {

	define("PH_UPS_BANNER_OPTION_ID", "ph_ups_display_rest_api_banner_6_0");
}

// WooCommerce HPOS Compatibility Declaration
add_action('before_woocommerce_init', function () {

	if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {

		$ph_wp_postameta_label_migrated = get_option(PH_UPS_LABEL_MIGRATION_OPTION, false);

		if ($ph_wp_postameta_label_migrated) {

			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
		} else {

			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, false);
		}
	}
});

/**
 * WC_UPS class
 */
if (!class_exists('UPS_WooCommerce_Shipping')) {

	class UPS_WooCommerce_Shipping
	{
		/**
		 * Constructor
		 */
		public function __construct()
		{
			$this->ph_init();

			add_action('init', array($this, 'init'));
			add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'ph_ups_plugin_action_links'));
			add_action('woocommerce_shipping_init', array($this, 'ph_ups_shipping_init'));
			add_filter('woocommerce_shipping_methods', array($this, 'ph_ups_add_method'));
			add_action('admin_enqueue_scripts', array($this, 'ph_ups_scripts'));

			// Closing Banner
			add_action('wp_ajax_ph_ups_closing_rest_api_banner', array('Ph_UPS_Woo_Shipping_Common', 'ph_ups_close_rest_api_info_banner'));

			// Showing Banner
			add_action('admin_notices', array('Ph_UPS_Woo_Shipping_Common', 'ph_rest_api_updation_banner'));
		}

		public function ph_init()
		{

			if (is_admin()) {

				// Product Level Fields
				include_once('includes/class-wf-ups-admin-options.php');
			}
		}

		public function init()
		{
			include_once 'includes/plugin-filters/ph-ups-license-hash-finder.php';

			// Storage Handler.
			if (!class_exists('PH_UPS_WC_Storage_Handler')) {

				include_once 'class-ph-ups-wc-storage-handler.php';
			}

			// Post Table Label Migration.
			if (!class_exists('PH_UPS_WP_Post_Table_Label_Migration')) {

				include_once 'migration/ph-ups-post-table-label-migration.php';
			}

			// Settings Helper.
			if( !class_exists( 'PH_WC_UPS_Settings_Helper' )) {

				include_once 'includes/settings/class-ph-ups-settings-helper.php';
			}

			// Package Generator.
			if( !class_exists('PH_WC_UPS_Package_Generator')) {

				include_once 'includes/package-handler/class-ph-ups-package-generator.php';
			}

			// To support third party plugins
			$this->third_party_plugin_support();

			include('includes/wf-automatic-label-generation.php');

			if (!class_exists('wf_order')) {

				include_once 'includes/class-wf-legacy.php';
			}
			// Add Notice Class
			include_once('includes/class-wf-admin-notice.php');
			// WF Print Shipping Label.
			include_once('includes/class-wf-shipping-ups-admin.php');

			include_once('includes/class-wf-ups-accesspoint-locator.php');

			include_once('includes/class-ph-ups-custom-fields.php');

			if (is_admin()) {

				//include pickup functionality
				include_once('includes/class-wf-ups-pickup-admin.php');

				// UPS Document Upload
				include_once('includes/class-ph-ups-document-upload.php');

				// UPS Registration
				include_once('includes/registration/class-ph-ups-registration-menu.php');
			}

			include_once('includes/registration/class-ph-ups-registration-admin-ajax.php');
			
			// Localisation
			load_plugin_textdomain('ups-woocommerce-shipping', false, dirname(plugin_basename(__FILE__)) . '/i18n/');
		}

		/**
		 * It will decide that which third party plugin support file has to be included depending on active plugins.
		 */
		public function third_party_plugin_support()
		{

			// To Support Woocommerce Bundle Product Plugin
			if (PH_UPS_Dependencies::ph_plugin_active_check('woocommerce-product-bundles/woocommerce-product-bundles.php')) {
				require_once 'includes/third-party-plugin-support/func-ph-ups-woocommerce-bundle-product-support.php';
			}
			// For WooCommerce Composite Product plugin
			if (PH_UPS_Dependencies::ph_plugin_active_check('woocommerce-composite-products/woocommerce-composite-products.php')) {
				require_once 'includes/third-party-plugin-support/func-ph-ups-woocommerce-composite-product-support.php';
			}
			// For WooCommerce Shipping Multiple Address
			if (
				PH_UPS_Dependencies::ph_plugin_active_check('woocommerce-shipping-multiple-addresses/woocommerce-shipping-multiple-address.php') ||
				PH_UPS_Dependencies::ph_plugin_active_check('woocommerce-shipping-multiple-addresses/woocommerce-shipping-multiple-addresses.php')
			) {
				require_once 'includes/third-party-plugin-support/func-ph-ups-woocommerce-shipping-multiple-address.php';
			}
			// For Multicurrency for woocommerce
			if (
				PH_UPS_Dependencies::ph_plugin_active_check('woo-multi-currency/woo-multi-currency.php') ||
				PH_UPS_Dependencies::ph_plugin_active_check('woo-multi-currency-pro/woo-multi-currency-pro.php')
			) {
				require_once 'includes/third-party-plugin-support/func-ph-ups-multicurrency-for-woocommerce.php';
			}
			// For WooCommerce subscriptions Plugin
			if (PH_UPS_Dependencies::ph_plugin_active_check('woocommerce-subscriptions/woocommerce-subscriptions.php')) {
				require_once 'includes/third-party-plugin-support/func-ph-ups-woocommerce-subscription.php';
			}
			// For WooCommerce Multi-Currency
			if (PH_UPS_Dependencies::ph_plugin_active_check('woocommerce-multicurrency/woocommerce-multicurrency.php')) {
				require_once 'includes/third-party-plugin-support/func-ph-ups-woocommerce-multicurrency.php';
			}
			// For WooCommerce Mix and Match Products
			if (PH_UPS_Dependencies::ph_plugin_active_check('woocommerce-mix-and-match-products/woocommerce-mix-and-match-products.php')) {
				require_once 'includes/third-party-plugin-support/func-ph-ups-woocommerce-mix-and-match.php';
			}
			// For WooCommerce Blocks cart/checkout
			require_once 'includes/third-party-plugin-support/func-ph-ups-woocommerce-blocks.php';

			// For custom code snippet - Replace Company Name
			require_once 'includes/custom-code-snippets/func-ph-ups-change-company-name.php';
		}

		/**
		 * Plugin page links
		 */
		public function ph_ups_plugin_action_links($links)
		{
			$plugin_links = array(
				'<a href="' . admin_url('admin.php?page=wc-settings&tab=shipping&section=wf_shipping_ups') . '">' . __('Settings', 'ups-woocommerce-shipping') . '</a>',
				'<a href="https://www.pluginhive.com/knowledge-base/category/woocommerce-ups-shipping-plugin-with-print-label/" target="_blank">' . __('Documentation', 'ups-woocommerce-shipping') . '</a>',
				'<a href="https://www.pluginhive.com/support/" target="_blank">' . __('Support', 'ups-woocommerce-shipping') . '</a>'
			);
			return array_merge($plugin_links, $links);
		}

		/**
		 * UPS Init Function.
		 *
		 * @access public
		 * @return void
		 */
		function ph_ups_shipping_init()
		{
			if( ! class_exists( 'WF_Shipping_UPS' )) {
				include_once('includes/class-wf-shipping-ups.php');
			}

			if ( Ph_UPS_Woo_Shipping_Common::ph_is_oauth_registered_customer() ) {

				if ( ! class_exists( 'PH_WC_UPS_Shipping_Zone_Method' ) ) {
					include_once( 'includes/ups-shipping-zones/class-ph-wc-ups-shipping-zone-method.php' );
				}
			}
		}

		/**
		 * PH UPS Add Method function.
		 *
		 * @access public
		 * @param mixed $methods
		 * @return mixed $methods
		 */
		function ph_ups_add_method($methods)
		{
			// This method will be added under Shipping independent of Zone
			$methods[] = 'WF_Shipping_UPS';
                     
			if( Ph_UPS_Woo_Shipping_Common::ph_is_oauth_registered_customer() ) {
				
				// This method will be added under Zone Shipping Methods
				$methods[PH_WC_UPS_ZONE_SHIPPING] = PH_WC_UPS_Shipping_Zone_Method::class;
			}

			return $methods;
		}

		/**
		 * wc_ups_scripts function.
		 *
		 * @access public
		 * @return void
		 */
		function ph_ups_scripts()
		{

			if (is_admin() && !did_action('wp_enqueue_media')) {
				wp_enqueue_media();
			}
			
			$ups_settings 	= get_option('woocommerce_' . WF_UPS_ID . '_settings', null);

			$ph_new_customer = false;

			if ( $ups_settings === null || Ph_UPS_Woo_Shipping_Common::ph_is_oauth_registered_customer() || Ph_UPS_Woo_Shipping_Common::phIsNewRegistration() ) {
			
				$ph_new_customer = true;
			}

			$data = array(
				'ph_new_customer'	=> $ph_new_customer,
			);

			wp_enqueue_script('jquery-ui-sortable');
			// wp_enqueue_script( 'wf-ups-common-script', plugins_url( '/resources/js/wf_common.js', __FILE__ ), array( 'jquery' ) );
			wp_enqueue_script('wf-ups-script', plugins_url('/resources/js/wf_ups.js', __FILE__), array('jquery'), PH_UPS_PLUGIN_VERSION);

			wp_enqueue_script('wf-ups-product-script', plugins_url('/resources/js/ph_ups_product_settings.js', __FILE__), array('jquery'), PH_UPS_PLUGIN_VERSION);
			
			if (is_admin()) {
				wp_localize_script('wf-ups-script', 'Ph_woocommerce_plugin_status', $data);
			}

			wp_enqueue_style('ph-ups-common-style', plugins_url('/resources/css/wf_common_style.css', __FILE__));

			// if (is_admin() && isset($_GET['page']) && ($_GET['page'] == 'ph_ups_registration')) {
			if (isset($_GET['page']) && ($_GET['page'] == 'ph_ups_registration')) {

				if (!class_exists('Ph_Ups_Auth_Handler')) {
					include_once 'includes/api-handler/class-ph-ups-auth-handler.php';
				}
		
				$auth_token				= Ph_Ups_Auth_Handler::phGetAuthProviderToken('ph_iframe');
				$product_order_api_key	= get_option('ph_client_ups_product_order_api_key');
		
				$dap_url = PH_UPS_Config::PH_UPS_DAP_REG_API . "/registration?licenseKey=$product_order_api_key";
				$ready_url = PH_UPS_Config::PH_UPS_READY_REG_API . "/registration?licenseKey=$product_order_api_key";
		
				$headers = [
					'Content-Type' => 'application/vnd.phive.external.carrier.v2+json',
					'Accept'		=> 'application/vnd.phive.external.carrier.v2+json',
					'env'   		=> PH_UPS_Config::PH_UPS_PROXY_ENV,
					'Authorization'	=> "Bearer $auth_token",
				];

				wp_enqueue_script('ph_ups_registration', plugins_url('/resources/js/ph_ups_registration.js', __FILE__), array('jquery'), PH_UPS_PLUGIN_VERSION);
				wp_localize_script(
					'ph_ups_registration',
					'ph_ups_registration_js',
					array(
						'ajaxurl' 				=> admin_url('admin-ajax.php'),
						'carrier_dap_reg_api_url'	=> $dap_url,
						'carrier_ready_reg_api_url'	=> $ready_url,
						'api_headers'			=> $headers
					)
				);
			}
		}
	}

	if (!class_exists('Ph_Ups_Db_Migration')) {
		require_once "migration/ph-ups-box-migration.php";
	}

	// Migrate box settings
	new Ph_Ups_Db_Migration();

	$wc_active = PH_UPS_Dependencies::ph_is_woocommerce_active();

	if ( $wc_active ) {

		if (!function_exists('ph_wc_ups_plugin_configuration')) {

			function ph_wc_ups_plugin_configuration() {

				return array(
					'id' 					=> WF_UPS_ID,
					'method_title' 			=> __("UPS", 'ups-woocommerce-shipping' ),
					'method_description' 	=> __("WooCommerce UPS Shipping Plugin with Print Label by PluginHive", 'ups-woocommerce-shipping' )
				);	
			}
		}
			
		if (!function_exists('ph_wc_ups_shipping_zone_method_configuration')) {
		
			function ph_wc_ups_shipping_zone_method_configuration() {

				return array(
					'id' 					=> PH_WC_UPS_ZONE_SHIPPING,
					'method_title' 			=> __("UPS Shipping", 'ups-woocommerce-shipping' ),
					'method_description' 	=> __("Please configure your services for calculating rates", 'ups-woocommerce-shipping' )
				);		
			}
		}
	}
}

new UPS_WooCommerce_Shipping();

/* Add a new country to countries list */
if (!function_exists('ph_add_puert_rico_country')) {

	function ph_add_puert_rico_country($country)
	{
		$country["PR"] = 'Puerto Rico';

		return $country;
	}

	add_filter('woocommerce_countries', 'ph_add_puert_rico_country', 10, 1);
}
