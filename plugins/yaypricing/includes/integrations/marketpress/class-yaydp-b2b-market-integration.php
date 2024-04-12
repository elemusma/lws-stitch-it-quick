<?php
/**
 * Handles the integration of YayCurrency plugin with our system
 *
 * @package YayPricing\Integrations
 */

namespace YAYDP\Integrations\MarketPress;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Declare class
 */
class YAYDP_B2B_Market_Integration {
	use \YAYDP\Traits\YAYDP_Singleton;

	/**
	 * Constructor
	 */
	protected function __construct() {
		if ( class_exists( 'BM_Price' ) ) {
			add_filter('bm_recalculate_prices_set_item_price', function( $res, $product, $item ) {
				if ( function_exists( 'yaydp_unserialize_cart_data' ) && isset( $item['modifiers'] ) ) {
					$modifiers = \yaydp_unserialize_cart_data( $item['modifiers'] );
					if ( ! empty( $modifiers ) ) {
						$res = false;
					}
				}
				return $res;
			}, 100, 3);
		}
	}

}
