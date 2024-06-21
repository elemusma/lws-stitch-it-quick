<?php

namespace Barn2\Plugin\WC_Product_Options\Util;

use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Util as Lib_Util;
use Barn2\Plugin\WC_Wholesale_Pro\Util as Wholesale_Util;

/**
 * Pricing utilities.
 *
 * @package   Barn2\woocommerce-product-options
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
final class Price {

	/**
	 * Get currency data about the store currency.
	 * For use in JS.
	 *
	 * @return array
	 */
	public static function get_currency_data(): array {
		$currency = get_woocommerce_currency();

		return [
			'code'              => $currency,
			'precision'         => wc_get_price_decimals(),
			'symbol'            => html_entity_decode( get_woocommerce_currency_symbol( $currency ) ),
			'symbolPosition'    => get_option( 'woocommerce_currency_pos' ),
			'decimalSeparator'  => wc_get_price_decimal_separator(),
			'thousandSeparator' => wc_get_price_thousand_separator(),
			'priceFormat'       => html_entity_decode( get_woocommerce_price_format() ),
		];
	}

	/**
	 * Get a formatted price with currency symbol for display.
	 *
	 * We don't use wc_price for option choices because it is subject
	 * to interference by HTMLElement class targeting from third party themes and plugins.
	 *
	 * @param float $price The price you want to format for display.
	 * @return string $price_html
	 */
	public static function get_price_html( float $price ): string {
		$currency_pos         = get_option( 'woocommerce_currency_pos' );
		/**
		 * Filter the character used as a plus sign.
		 *
		 * @param string $sign The plus sign.
		 * @param float $price The price.
		 * @param string $currency_pos The currency position.
		 */
		$sign                 = apply_filters( 'wc_product_options_plus_sign', '+', $price, $currency_pos );
		$currency_placeholder = '%1$s';
		$price_placeholder    = '%2$s';

		// if the currency symbol is on the left and the number is negative, put the negative symbol before the currency symbol.
		if ( $price < 0 ) {
			$price = abs( $price );
			/**
			 * Filter the character used as a minus sign.
			 *
			 * @param string $sign The minus sign.
			 * @param float $price The price.
			 * @param string $currency_pos The currency position.
			 */
			$sign = apply_filters( 'wc_product_options_minus_sign', '-', $price, $currency_pos );
		}

		if ( str_contains( $currency_pos, 'space' ) ) {
			$sign .= '&nbsp;';
		}

		if ( str_contains( $currency_pos, 'left' ) ) {
			$currency_placeholder = "$sign$currency_placeholder";
		} else {
			$price_placeholder = "$sign$price_placeholder";
		}

		$price_string = str_replace( [ '%1$s', '%2$s' ], [ "<span class=\"wpo-currency\">$currency_placeholder</span>", "<span class=\"wpo-price\">$price_placeholder</span>" ], get_woocommerce_price_format() );

		$price_html = sprintf(
			$price_string,
			get_woocommerce_currency_symbol(),
			self::get_formatted_price( $price )
		);

		return $price_html;
	}

	/**
	 * Get a formatted price for display.
	 *
	 * @param float $price
	 * @return string
	 */
	public static function get_formatted_price( float $price ): string {
		$decimal_seperator  = wc_get_price_decimal_separator();
		$thousand_separator = wc_get_price_thousand_separator();
		$decimals           = wc_get_price_decimals();

		return number_format( $price, $decimals, $decimal_seperator, $thousand_separator );
	}

	/**
	 * Calculates the display price for a chosen option choice.
	 *
	 * @param array $price_data
	 * @param WC_Product $product
	 * @param int $quantity
	 * @param string|null $display_area
	 * @param float|null $product_price
	 * @return float
	 */
	public static function calculate_option_display_price( $price_data, $product, $quantity = 1, $display_area = null, $product_price = null ): float {

		if ( ! isset( $price_data['type'] ) ) {
			return 0;
		}

		if ( ! in_array( $price_data['type'], [ 'flat_fee', 'percentage_inc', 'percentage_dec', 'quantity_based', 'char_count', 'price_formula' ], true ) ) {
			return 0;
		}

		if ( $product_price === null ) {
			$product_price = $product->get_price();
		}

		$price_amount = self::get_choice_display_price( $product, $price_data['amount'], $display_area );

		switch ( $price_data['type'] ) {
			case 'flat_fee':
				$option_price = $price_amount / $quantity;
				break;
			case 'percentage_inc':
				$option_price = $product_price * ( $price_data['amount'] / 100 );
				break;
			case 'percentage_dec':
				$option_price = - ( $product_price * ( $price_data['amount'] / 100 ) );
				break;
			case 'quantity_based':
			case 'price_formula':
				$option_price = $price_amount;
				break;
			case 'char_count':
				$option_price = $price_amount * $price_data['char_count'];
				break;
			default:
				$option_price = 0;
				break;
		}

		return $option_price;
	}


	/**
	 * Calculates the price for a chosen option choice.
	 *
	 * @param array $price_data
	 * @param WC_Product $product
	 * @param int $quantity
	 * @param float $product_price
	 * @return float
	 */
	public static function calculate_option_cart_price( $price_data, $product, $quantity = 1, $product_price = 0.0 ): float {

		if ( ! isset( $price_data['type'] ) ) {
			return 0;
		}

		if ( ! in_array( $price_data['type'], [ 'flat_fee', 'percentage_inc', 'percentage_dec', 'quantity_based', 'char_count', 'price_formula' ], true ) ) {
			return 0;
		}

		if ( $product_price === null ) {
			$product_price = (float) $product->get_price();
		}

		switch ( $price_data['type'] ) {
			case 'flat_fee':
				$option_price = $price_data['amount'] / $quantity;
				break;
			case 'percentage_inc':
				$option_price = $product_price * ( $price_data['amount'] / 100 );
				break;
			case 'percentage_dec':
				$option_price = - ( $product_price * ( $price_data['amount'] / 100 ) );
				break;
			case 'quantity_based':
			case 'price_formula':
				$option_price = $price_data['amount'];
				break;
			case 'char_count':
				$option_price = $price_data['amount'] * $price_data['char_count'];
				break;

			default:
				$option_price = 0;
				break;
		}

		/**
		 * Filter the price of an option choice in the cart.
		 *
		 * @param float $option_price
		 * @param WC_Product $product
		 * @param array $price_data
		 */
		return apply_filters( 'wc_product_options_cart_price', $option_price, $product, $price_data );
	}

	/**
	 * Get display price for a an option choice.
	 *
	 * @param WC_Product $product
	 * @param float $choice_price
	 * @param string|null $display_area
	 * @return float|string $price
	 */
	public static function get_choice_display_price( $product, $choice_price, $display_area = null ) {
		if ( ! wc_tax_enabled() ) {
			/**
			 * Filter the price of an option choice.
			 *
			 * @param float $choice_price
			 * @param WC_Product $product
			 * @param float $choice_price
			 */
			return apply_filters( 'wc_product_options_choice_label_price', $choice_price, $product, $choice_price );
		}

		if ( is_null( $display_area ) ) {
			$display_on = is_cart() || is_checkout() ? 'cart' : 'shop';
		} elseif ( in_array( $display_area, [ 'cart', 'shop' ], true ) ) {
			$display_on = $display_area;
		} else {
			return apply_filters( 'wc_product_options_choice_label_price', $choice_price, $product, $choice_price );
		}

		$tax_display = get_option( "woocommerce_tax_display_{$display_on}" );

		if ( ! empty( WC()->customer ) && WC()->customer->get_is_vat_exempt() && wc_prices_include_tax() ) {
			$tax_display = 'excl';
		}

		return apply_filters( 'wc_product_options_choice_label_price', self::get_price_with_applicable_tax( $product, $choice_price, $tax_display ), $product, $choice_price );
	}

	/**
	 * Get price based on tax settings.
	 *
	 * @param WC_Product $product
	 * @param float $price
	 * @param bool $tax_display
	 * @return float|string Empty string if price cannot be calculated.
	 */
	public static function get_price_with_applicable_tax( $product, $price, $tax_display ) {
		// check if price is negative and set a marker to return the negative price later
		// wc_get_price_including_tax() and wc_get_price_excluding_tax() will return a positive value
		$negative_price = false;

		if ( $price < 0 ) {
			$negative_price = true;
			$price          = abs( floatval( $price ) );
		}

		if ( $tax_display === 'incl' ) {
			$price = wc_get_price_including_tax(
				$product,
				[
					'qty'   => 1,
					'price' => $price
				]
			);
		}

		if ( $tax_display === 'excl' ) {
			$price = wc_get_price_excluding_tax(
				$product,
				[
					'qty'   => 1,
					'price' => $price
				]
			);
		}

		// if the price was negative, return it as negative
		if ( $negative_price ) {
			$price = - $price;
		}

		return $price;
	}

	/**
	 * Determine if the current user has a wholesale role with set choice pricing for the given choice.
	 *
	 * @param array $choice
	 * @return bool|float
	 */
	public static function wholesale_user_has_choice_pricing( $choice ) {
		if ( ! Lib_Util::is_barn2_plugin_active( '\Barn2\Plugin\WC_Wholesale_Pro\woocommerce_wholesale_pro' ) ) {
			return false;
		}

		$wholesale_role = Wholesale_Util::get_current_user_wholesale_role_object();

		if ( ! $wholesale_role ) {
			return false;
		}

		if ( ! isset( $choice['wholesale'][ $wholesale_role->get_name() ] ) ) {
			return false;
		}

		if ( ! is_numeric( $choice['wholesale'][ $wholesale_role->get_name() ] ) ) {
			return false;
		}

		return $choice['wholesale'][ $wholesale_role->get_name() ];
	}
}
