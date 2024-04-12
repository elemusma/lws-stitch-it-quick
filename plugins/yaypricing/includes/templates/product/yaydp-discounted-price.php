<?php
/**
 * The Template for displaying discounted price, replace the origin product price if product has discount
 *
 * @package YayPricing\Templates
 *
 * @param $min_discounted_price
 * @param $max_discounted_price
 * @param $product
 * @param $show_discounted_with_regular_price
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Note: min and max passed in maybe is the same, so in this case just take 1.
$discounted_prices = array_unique( array( $min_discounted_price, $max_discounted_price ) );

// Not process if not have discount.
if ( empty( $discounted_prices ) ) {
	return '';
}

\yaydp_sort_array( $discounted_prices );
$discounted_prices = array_map(
	function( $price ) use ( $product ) {
		$price = \wc_get_price_to_display($product, ['price' => $price]);
		$price = \YAYDP\Helper\YAYDP_Pricing_Helper::convert_price( $price ); // Maybe converted by other currency plugin.
		return \wc_price( $price );
	},
	$discounted_prices
);

$is_variable_or_grouped_product = \yaydp_is_variable_product( $product ) || \yaydp_is_grouped_product( $product );
?>

<div class="price yaydp-discounted-price">
	<?php if ( $show_discounted_with_regular_price ) : // Show origin prices. ?>
		<div class="yaydp-original-prices">
			<del>
				<?php
				if ( $is_variable_or_grouped_product ) { // Variable product process.
					$min_price              = \yaydp_get_variable_product_min_price( $product );
					$max_price              = \yaydp_get_variable_product_max_price( $product );
					$product_min_max_prices = array_map(
						function( $price ) use ( $product )  {
							$price = \wc_get_price_to_display($product, ['price' => $price]);
							$price = \YAYDP\Helper\YAYDP_Pricing_Helper::convert_price( $price ); // Maybe converted by other currency plugin.
							return \wc_price( $price );
						},
						array_unique( array( $min_price, $max_price ) ) // Return 1 price if min = max.
					);
					echo wp_kses_post( implode( ' - ', $product_min_max_prices ) );
				} else {
					$product_price = \YAYDP\Helper\YAYDP_Pricing_Helper::get_product_price( $product );
					$product_price = \wc_get_price_to_display($product, ['price' => $product_price]);
					$product_price = \YAYDP\Helper\YAYDP_Pricing_Helper::convert_price( $product_price );
					echo wp_kses_post( \wc_price( $product_price ) );
				}
				?>
			</del>
		</div>
	<?php endif; ?>
	<div class="yaydp-calculated-prices">
	<?php echo wp_kses_post( implode( ' - ', $discounted_prices ) ); ?>
	</div>
</div>
