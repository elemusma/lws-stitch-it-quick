<?php
namespace Barn2\Plugin\WC_Product_Options\Integration;

use Barn2\Plugin\WC_Product_Options\Model\Group as Group_Model;
use Barn2\Plugin\WC_Product_Options\Util\Display as Display_Util;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Registerable;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Util as Lib_Util;

/**
 * Handles the Bulk Variations integration.
 *
 * @package   Barn2\woocommerce-product-options
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Bulk_Variations implements Registerable {

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		if ( ! Lib_Util::is_barn2_plugin_active( '\Barn2\Plugin\WC_Bulk_Variations\wbv' ) ) {
			return;
		}

		add_action( 'wc_bulk_variations_before_totals_container', [ $this, 'add_product_options' ], 10, 1 );
	}

	/**
	 * Add the product options after the Bulk Variations table ouput.
	 *
	 * @param int $product_id
	 * @return string
	 */
	public function add_product_options( $product_id ) {
		$product = wc_get_product( $product_id );
		$groups  = Group_Model::get_groups_by_product( $product );

		if ( empty( $groups ) ) {
			return;
		}

		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo Display_Util::get_groups_html( $groups, $product );
		echo self::get_totals_container_html( $product );
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Retrives the totals HTML for the supplied product
	 *
	 * @param WC_Product $product
	 * @return string
	 */
	public static function get_totals_container_html( $product ) {
		$html = sprintf(
			'<div class="wpo-totals-container" data-product-price="%1$s"></div>',
			esc_attr( wc_get_price_to_display( $product, [ 'price' => $product->get_price() ] ) )
		);

		return $html;
	}
}
