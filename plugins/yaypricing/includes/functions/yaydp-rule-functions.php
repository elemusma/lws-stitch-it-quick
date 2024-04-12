<?php
/**
 * YayPricing functions for rule
 *
 * Declare global functions
 *
 * @package YayPricing\Functions
 */

if ( ! function_exists( 'yaydp_is_product_pricing' ) ) {

	/**
	 * Check whether rule is Product Pricing.
	 *
	 * @param object $rule Checking rule.
	 */
	function yaydp_is_product_pricing( $rule ) {
		return $rule instanceof YAYDP\Abstracts\YAYDP_Product_Pricing_Rule;
	}
}

if ( ! function_exists( 'yaydp_is_simple_adjustment' ) ) {

	/**
	 * Check whether rule is Simple Adjustment.
	 *
	 * @param object $rule Checking rule.
	 */
	function yaydp_is_simple_adjustment( $rule ) {
		return $rule instanceof YAYDP\Core\Rule\Product_Pricing\YAYDP_Simple_Adjustment;
	}
}

if ( ! function_exists( 'yaydp_is_bulk_pricing' ) ) {

	/**
	 * Check whether rule is Bulk Pricing.
	 *
	 * @param object $rule Checking rule.
	 */
	function yaydp_is_bulk_pricing( $rule ) {
		return $rule instanceof YAYDP\Core\Rule\Product_Pricing\YAYDP_Bulk_Pricing;
	}
}

if ( ! function_exists( 'yaydp_is_bogo' ) ) {

	/**
	 * Check whether rule is BOGO.
	 *
	 * @param object $rule Checking rule.
	 */
	function yaydp_is_bogo( $rule ) {
		return $rule instanceof YAYDP\Core\Rule\Product_Pricing\YAYDP_Bogo;
	}
}

if ( ! function_exists( 'yaydp_is_buy_x_get_y' ) ) {

	/**
	 * Check whether rule is Buy X Get Y.
	 *
	 * @param object $rule Checking rule.
	 */
	function yaydp_is_buy_x_get_y( $rule ) {
		return $rule instanceof YAYDP\Core\Rule\Product_Pricing\YAYDP_Buy_X_Get_Y;
	}
}

if ( ! function_exists( 'yaydp_is_cart_discount' ) ) {

	/**
	 * Check whether rule is Cart Discount.
	 *
	 * @param object $rule Checking rule.
	 */
	function yaydp_is_cart_discount( $rule ) {
		return $rule instanceof YAYDP\Abstracts\YAYDP_Cart_Discount_Rule;
	}
}

if ( ! function_exists( 'yaydp_is_checkout_fee' ) ) {

	/**
	 * Check whether rule is Checkout Fee.
	 *
	 * @param object $rule Checking rule.
	 */
	function yaydp_is_checkout_fee( $rule ) {
		return $rule instanceof YAYDP\Abstracts\YAYDP_Checkout_Fee_Rule;
	}
}

if ( ! function_exists( 'yaydp_is_percentage_pricing_type' ) ) {

	/**
	 * Check whether pricing type is percentage.
	 *
	 * @param string $type Checking type.
	 */
	function yaydp_is_percentage_pricing_type( $type = 'fixed_discount' ) {
		return false !== strpos( $type, 'percent' );
	}
}

if ( ! function_exists( 'yaydp_is_flat_pricing_type' ) ) {

	/**
	 * Check whether pricing type is flat.
	 *
	 * @param string $type Checking type.
	 */
	function yaydp_is_flat_pricing_type( $type = 'fixed_discount' ) {
		return 'flat_price' === $type;
	}
}

if ( ! function_exists( 'yaydp_is_fixed_pricing_type' ) ) {

	/**
	 * Check whether pricing type is fixed.
	 *
	 * @param string $type Checking type.
	 */
	function yaydp_is_fixed_pricing_type( $type = 'fixed_discount' ) {
		return false !== strpos( $type, 'fixed' );
	}
}

if ( ! function_exists( 'yaydp_get_formatted_discount_value' ) ) {

	/**
	 * Format pricing value.
	 * If add % if is percentage.
	 * WC format if is fixed.
	 *
	 * @param float  $value Pricing value.
	 * @param string $type Pricing type.
	 */
	function yaydp_get_formatted_pricing_value( $value, $type = 'fixed_discount' ) {
		return \yaydp_is_percentage_pricing_type( $type ) ? "$value%" : \wc_price( $value );
	}
}

if ( ! function_exists( 'yaydp_is_fixed_product_pricing_type' ) ) {

	/**
	 * Check whether pricing type is fixed product.
	 *
	 * @param string $type Checking type.
	 */
	function yaydp_is_fixed_product_pricing_type( $type = 'fixed_discount' ) {
		return false !== strpos( $type, 'fixed_product' );
	}
}
