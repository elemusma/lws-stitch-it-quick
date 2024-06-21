<?php
defined( 'ABSPATH' ) || exit();

$price_type = 0;
$price      = 0;

if ( '1' == get_post_meta( $field_id, 'af_addon_price_range_checkbox', true ) ) {
	$price_type = get_post_meta( $field_id, 'af_addon_field_price_type', true );
	$price      = ! empty( get_post_meta( $field_id, 'af_addon_field_price', true ) ) ? get_post_meta( $field_id, 'af_addon_field_price', true ) : 0;
}

$addon_price = '';
if ( 'flat_fixed_fee' == $price_type || 'fixed_fee_based_on_quantity' == $price_type ) {
	$addon_price = ' (+' . get_woocommerce_currency_symbol() . ' ' . number_format( $price, 2, '.', '' ) . ')';
} elseif ( 'flat_percentage_fee' == $price_type || 'Percentage_fee_based_on_quantity' == $price_type ) {
	$addon_price = ' (+' . number_format( $price, 2, '.', '' ) . '%)';
}
?>

<input type="email"
style="width: 100%; height: 40px;"
autocomplete="off"
data-dependent_on="<?php echo esc_attr( $dependent_fields ); ?>" 
data-dependent_val="<?php echo esc_attr( $dependent_options ); ?>" 
name="af_addons_options_<?php echo esc_attr( $field_id ); ?>" 
class="addon_email_option <?php echo esc_attr( $af_addon_front_dep_class ); ?>" 
id="<?php echo esc_attr( $af_addon_field_id ); ?>" 
data-price_type="<?php echo esc_attr( $price_type . '_email' ); ?>" 
data-price="<?php echo esc_attr( $price ); ?>"
<?php if ( 1 == get_post_meta( $field_id, 'af_addon_required_field', true ) ) : ?>
	required
<?php endif ?>
>