<?php
/**
 * Handle Shipping Fee rule
 *
 * @package YayPricing\Rule\CheckoutFee
 */

namespace YAYDP\Core\Rule\Checkout_Fee;

defined( 'ABSPATH' ) || exit;

/**
 * Declare class
 */
class YAYDP_Shipping_Fee extends \YAYDP\Abstracts\YAYDP_Checkout_Fee_Rule {

	/**
	 * Calculate all possible adjustment created by the rule.
	 *
	 * @override
	 *
	 * @param \YAYDP\Core\YAYDP_Cart $cart Cart.
	 */
	public function create_possible_adjustment_from_cart( \YAYDP\Core\YAYDP_Cart $cart ) {

		if ( \YAYDP\Core\Manager\YAYDP_Exclude_Manager::check_coupon_exclusions( $this ) ) {
			return null;
		}

		if ( $this->check_conditions( $cart ) ) {
			return array(
				'rule' => $this,
			);
		}
		return null;
	}

	/**
	 * Calculate the adjustment amount based on current shipping fee
	 *
	 * @override
	 */
	public function get_adjustment_amount() {
		$pricing_type              = $this->get_pricing_type();
		$pricing_value             = $this->get_pricing_value();
		$maximum_adjustment_amount = $this->get_maximum_adjustment_amount();
		$cart_shipping_fee         = \yaydp_get_shipping_fee();
		$adjustment_amount         = \YAYDP\Helper\YAYDP_Pricing_Helper::calculate_adjustment_amount( $cart_shipping_fee, $pricing_type, $pricing_value, $maximum_adjustment_amount );
		return $adjustment_amount;
	}

	/**
	 * Calculate total discount amount per order
	 */
	public function get_total_discount_amount() {
		$adjustment_amount = $this->get_adjustment_amount();
		$pricing_type      = $this->get_pricing_type();
		$cart_shipping_fee = \yaydp_get_shipping_fee();
		if ( \yaydp_is_percentage_pricing_type( $pricing_type ) ) {
			return min( $cart_shipping_fee, $adjustment_amount );
		}
		if ( \yaydp_is_fixed_pricing_type( $pricing_type ) ) {
			return min( $cart_shipping_fee, $adjustment_amount );
		}
		return 0;
	}

	/**
	 * Add fee to the cart
	 */
	public function add_fee() {

		$discount_amount = $this->get_total_discount_amount();

		if ( empty( $discount_amount ) ) {
			return;
		}

		$fee_data = array(
			'id'     => $this->get_id(),
			'name'   => $this->get_name(),
			'amount' => \YAYDP\Helper\YAYDP_Pricing_Helper::convert_fee( - $discount_amount ),
		);
		\WC()->cart->fees_api()->add_fee( $fee_data );
		add_filter( 'woocommerce_cart_totals_get_fees_from_cart_taxes', function($taxes, $fee) use ( $fee_data ) {
			$taxable = false;
			foreach ( WC()->cart->calculate_shipping() as $shipping ) {
				if ( !empty( $shipping->get_taxes() ) ) {
					$taxable = true;
				}
			}
			if ( $fee->object->id === $fee_data['id'] ) {
				$fee->total = \wc_add_number_precision_deep( $fee_data['amount'] );
				if ( WC()->cart->display_prices_including_tax() && $taxable && ! empty( $taxes ) ) {
					foreach ($taxes as $value) {
						$fee->total += apply_filters( 'yaydp_reversed_tax', $value );
					}
				}
				return [];
			}
			return $taxes;
		}, 100, 2 );
	}

	/**
	 * Calculate all encouragements can be created by rule ( include condition encouragements )
	 *
	 * @override
	 *
	 * @param \YAYDP\Core\YAYDP_Cart $cart Cart.
	 */
	public function get_encouragements( \YAYDP\Core\YAYDP_Cart $cart ) {
		$conditions_encouragements = parent::get_conditions_encouragements( $cart );
		if ( empty( $conditions_encouragements ) ) {
			return null;
		}
		return new \YAYDP\Core\Encouragement\YAYDP_Checkout_Fee_Encouragement(
			array(
				'cart'                      => $cart,
				'rule'                      => $this,
				'conditions_encouragements' => $conditions_encouragements,
			)
		);
	}
}
