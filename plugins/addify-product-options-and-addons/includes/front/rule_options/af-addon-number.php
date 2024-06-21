<?php
defined( 'ABSPATH' ) || exit();

$min_limit_range = 0;
$max_limit_range = 0;
if ( '1' == get_post_meta( $field_id, 'af_addon_limit_range_checkbox', true ) ) {
	$min_limit_range = get_post_meta( $field_id, 'af_addon_min_limit_range', true );
	$max_limit_range = get_post_meta( $field_id, 'af_addon_max_limit_range', true );
}

$addon_price = 0;
$price_type  = 0;
$price       = 0;

if ( '1' == get_post_meta( $field_id, 'af_addon_price_range_checkbox', true ) ) {
	$price_type = get_post_meta( $field_id, 'af_addon_field_price_type', true );
	$price      = ! empty( get_post_meta( $field_id, 'af_addon_field_price', true ) ) ? get_post_meta( $field_id, 'af_addon_field_price', true ) : 0;
}

if ( 'flat_fixed_fee' == $price_type || 'fixed_fee_based_on_quantity' == $price_type ) {
	$addon_price = ' (+' . get_woocommerce_currency_symbol() . ' ' . number_format( $price, 2, '.', '' ) . ')';
} elseif ( 'flat_percentage_fee' == $price_type || 'Percentage_fee_based_on_quantity' == $price_type ) {
	$addon_price = ' (+' . number_format( $price, 2, '.', '' ) . '%)';
}
?>

<input type="number" style="width: 100%; height: 40px;"
name="af_addons_options_<?php echo esc_attr( $field_id ); ?>" 
data-dependent_on="<?php echo esc_attr( $dependent_fields ); ?>" 
data-dependent_val="<?php echo esc_attr( $dependent_options ); ?>"
class="addon_front_number <?php echo esc_attr( $af_addon_front_dep_class ); ?>" 
id="<?php echo esc_attr( $af_addon_field_id ); ?>" 
<?php
if ( ! empty( $min_limit_range ) ) {
	?>
	min='<?php echo esc_attr( $min_limit_range ); ?>'
	<?php
} else {
	?>
	min='1'
	<?php
}
?>
<?php if ( ! empty( $max_limit_range ) ) : ?>
	max='<?php echo esc_attr( $max_limit_range ); ?>'
<?php endif ?>
data-price_type="<?php echo esc_attr( $price_type . '_number' ); ?>" 
data-price="<?php echo esc_attr( $price ); ?>"
<?php if ( 1 == get_post_meta( $field_id, 'af_addon_required_field', true ) ) : ?>
	required
<?php endif ?>
>
<?php
