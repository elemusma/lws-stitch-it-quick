<?php
namespace Barn2\Plugin\WC_Product_Options\Handlers;

use Barn2\Plugin\WC_Product_Options\Model\Option as Option_Model;
use Barn2\Plugin\WC_Product_Options\Util\Price as Price_Util;
use Barn2\Plugin\WC_Product_Options\Util\Util;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Registerable;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Service;

/**
 * Cart Handler
 *
 * @package   Barn2\woocommerce-product-options
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Cart implements Registerable, Service {

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		add_action( 'woocommerce_before_calculate_totals', [ $this, 'option_price_calculation' ], 11, 1 );
		add_action( 'woocommerce_before_mini_cart', [ $this, 'force_mini_cart_calculation' ], 1 );
	}

	/**
	 * Calculates the product addon pricing in the cart.
	 *
	 * @param WC_Cart $cart
	 */
	public function option_price_calculation( $cart ) {

		foreach ( $cart->get_cart_contents() as $cart_item ) {

			if ( ! isset( $cart_item['wpo_options'] ) ) {
				continue;
			}

			$options_data = $cart_item['wpo_options'];
			$product      = $cart_item['data'];

			if ( ! Util::is_allowed_product_type( $product->get_type() ) ) {
				continue;
			}

			/**
			 * Filters whether to allow product option price calculation on a product.
			 *
			 * @param bool $enable Whether to allow product option price calculation on a product.
			 * @param \WC_Product $product The product which is being calculated.
			 * @param array|null $cart_item The cart item if this is calculated in the cart.
			 */
			$calculation_enabled = apply_filters( 'wc_product_options_enable_price_calculation', true, $product, $cart_item );

			if ( ! $calculation_enabled ) {
				continue;
			}

			if ( isset( $cart_item['wholesale_pro']['is_wholesale_price'] ) && $cart_item['wholesale_pro']['is_wholesale_price'] === true ) {
				$product_price = $product->get_price();
			} else {
				$product_price = $product->is_on_sale() ? $product->get_sale_price() : $product->get_regular_price();
			}

			$options_price = $this->calculate_options_price( $product, $options_data, $cart_item['quantity'], $product_price );

			$final_price = Option_Model::get_product_price_exclusion_status( $product ) ? $options_price : (float) $product_price + $options_price;

			$final_price = max( 0, $final_price );

			$product->set_price( $final_price );
		}
	}

	/**
	 * Workaround - https://github.com/woocommerce/woocommerce/issues/26422
	 */
	public function force_mini_cart_calculation() {
		if ( is_cart() || is_checkout() || ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
			return;
		}

		if ( ! defined( 'WOOCOMMERCE_CART' ) ) {
			define( 'WOOCOMMERCE_CART', true );
		}

		WC()->cart->calculate_totals();
	}


	/**
	 * Calculates the price of the product options.
	 *
	 * @param WC_Product $product
	 * @param array $options_data
	 * @param int $quantity
	 * @param float $product_price
	 * @return float
	 */
	private function calculate_options_price( $product, $options_data, $quantity, $product_price ): float {
		$options_price = 0;

		foreach ( $options_data as $item_data ) {
			if ( ! isset( $item_data['choice_data'] ) ) {
				continue;
			}

			$price_change = 0;

			foreach ( $item_data['choice_data'] as $choice_data ) {
				if ( ! isset( $choice_data['pricing'] ) ) {
					continue;
				}

				$price_change += Price_Util::calculate_option_cart_price( $choice_data['pricing'], $product, $quantity, $product_price );
			}

			$options_price += $price_change;
		}

		return $options_price;
	}
}
