<?php
/**
 * Class handles the integration of external services with our application
 *
 * @package YayPricing\Integrations
 */

namespace YAYDP;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Declare class
 */
class YAYDP_Integrations {

	/**
	 * Constructor
	 */
	public function __construct() {
		\YAYDP\Integrations\YayCommerce\YAYDP_YayCurrency_Integration::get_instance();
		\YAYDP\Integrations\WebDevStudios\YAYDP_CPT_UI_Integration::get_instance();
		\YAYDP\Integrations\YITH\YAYDP_YITH_WC_Brands_Integration::get_instance();
		\YAYDP\Integrations\YITH\YAYDP_YITH_Gift_Card_Integration::get_instance();
		\YAYDP\Integrations\YITH\YAYDP_YITH_Product_Add_On_Integration::get_instance();
		\YAYDP\Integrations\VillaTheme\YAYDP_CURCY_Integration::get_instance();
		\YAYDP\Integrations\Themes\YAYDP_Astra_Theme_Integration::get_instance();
		\YAYDP\Integrations\Aelia\YAYDP_Aelia_Currency_Integration::get_instance();
		\YAYDP\Integrations\WooCommerce\YAYDP_WooCommerce_Subscriptions_Integration::get_instance();
		\YAYDP\Integrations\WooCommerce\YAYDP_WooCommerce_Brands_Integration::get_instance();
		\YAYDP\Integrations\Ademti\YAYDP_WC_Google_Product_Feed_Integration::get_instance();
		\YAYDP\Integrations\MarketPress\YAYDP_B2B_Market_Integration::get_instance();
		\YAYDP\Integrations\WooCommerce\YAYDP_WC_Listing_Ads_Integration::get_instance();
		\YAYDP\Integrations\LiteSpeed\YAYDP_LiteSpeed_Cache_Integration::get_instance();
	}

}

new YAYDP_Integrations();
