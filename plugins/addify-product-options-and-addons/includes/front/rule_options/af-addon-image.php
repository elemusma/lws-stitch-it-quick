<?php
defined( 'ABSPATH' ) || exit();

$counter = 1;
$args    = array(
	'post_type'   => 'af_pao_options',
	'post_status' => 'publish',
	'post_parent' => $field_id,
	'fields'      => 'ids',
	'numberposts' => -1,
	'orderby'     => 'menu_order',
	'order'       => 'ASC',
);

$options          = get_posts( $args );
$total_checkboxes = count( $options );
$req_field_value  = 'No';
if ( '1' == get_post_meta( $field_id, 'af_addon_required_field', true ) ) {
	$req_field_value = 'Yes';
}

foreach ( $options as $option_id ) {
	if ( empty( $option_id ) ) {
		continue;
	}
	$option_name        = get_post_meta( $option_id, 'af_addon_field_options_name', true );
	$price_type         = get_post_meta( $option_id, 'af_addon_field_options_price_type', true );
	$option_price       = (float) get_post_meta( $option_id, 'af_addon_field_options_price', true );
	$image              = get_post_meta( $option_id, 'af_addon_field_options_image', true );
	$addon_option_price = '';
	if ( 'free' == $price_type ) {
		$addon_option_price = $option_name;
	} elseif ( 'flat_fixed_fee' == $price_type || 'fixed_fee_based_on_quantity' == $price_type ) {
		$addon_option_price = $option_name . ' (+' . get_woocommerce_currency_symbol() . ' ' . number_format( $option_price, 2, '.', '' ) . ')';
	} elseif ( 'flat_percentage_fee' == $price_type || 'Percentage_fee_based_on_quantity' == $price_type ) {
		$addon_option_price = $option_name . ' (+' . number_format( $option_price, 2, '.', '' ) . '%)';
	}
	if ( empty( $image ) ) {
		$image = plugin_dir_url( '' ) . 'addify-product-add-ons/assets/image-none.png';
	}

	?>
	<div class="addons_div_selection">
		<img 
			src="<?php echo esc_attr( $image ); ?>" 
			style="width: 100%; height: 54px; object-fit: cover;"
		>
		<input type="radio" 
			name="af_addons_options_<?php echo esc_attr( $field_id ); ?>" 
			class="af_addon_images <?php echo esc_attr( $af_addon_front_dep_class ); ?>" 
			title="<?php echo esc_attr( $addon_option_price ); ?>" 
			data-option_name = "<?php echo esc_attr( $option_name ); ?>" 
			data-option_price="<?php echo intval( $option_price ); ?>" 
			data-price_type="<?php echo esc_attr( $price_type . '_image' ); ?>" 
			value="<?php echo esc_attr( $option_id ); ?>" 
			data-dependent_on="<?php echo esc_attr( $dependent_fields ); ?>"
			data-dependent_val="<?php echo esc_attr( $dependent_options ); ?>" 
			id="<?php echo esc_attr( $af_addon_field_id ); ?>"
			<?php if ( 1 == get_post_meta( $field_id, 'af_addon_required_field', true ) ) : ?>
				required
			<?php endif ?>
		>

	</div>
	<?php
}
