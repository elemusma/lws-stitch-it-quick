<?php
defined( 'ABSPATH' ) || exit();

$counter = 1;
$args    = array(

	'post_type'   => 'af_pao_options',
	'post_status' => 'publish',
	'post_parent' => $field_id,
	'numberposts' => -1,
	'fields'      => 'ids',
	'orderby'     => 'menu_order',
	'order'       => 'ASC',
);

$options          = get_posts( $args );
$total_checkboxes = count( $options );
?>

	<?php

	foreach ( $options as $option_id ) {
		if ( empty( $option_id ) ) {
			continue;
		}
		$option_name  = get_post_meta( $option_id, 'af_addon_field_options_name', true );
		$price_type   = get_post_meta( $option_id, 'af_addon_field_options_price_type', true );
		$option_price = (float) get_post_meta( $option_id, 'af_addon_field_options_price', true );
		?>
		<input type="checkbox" 
			name="af_addons_options_<?php echo esc_attr( $field_id ); ?>[]" 
			class="af_pao_options_checkbox af_pao_options_checkbox_<?php echo esc_attr( $field_id ); ?> <?php echo esc_attr( $af_addon_front_dep_class ); ?>" 
			value="<?php echo esc_attr( $option_id ); ?>" 
			data-dependent_on="<?php echo esc_attr( $dependent_fields ); ?>" 
			data-dependent_val="<?php echo esc_attr( $dependent_options ); ?>"
			data-option_name="<?php echo esc_attr( $option_name ); ?>" 
			data-total_checkboxes="<?php echo esc_attr( $total_checkboxes ); ?>" 
			data-option_price="<?php echo esc_attr( $option_price ); ?>" 
			data-price_type="<?php echo esc_attr( $price_type . '_checkbox' ); ?>" 
			data-field_id="<?php echo esc_attr( $field_id ); ?>" 
			id="<?php echo esc_attr( $af_addon_field_id ); ?>"
			<?php if ( 1 == get_post_meta( $field_id, 'af_addon_required_field', true ) ) : ?>
				required
				data-required='required'
			<?php endif ?>
			data-is_field_req="<?php echo esc_attr( get_post_meta( $field_id, 'af_addon_required_field', true ) ); ?>"
		>
		<?php
		$addon_option_price = '';
		if ( 'free' == $price_type ) {
			$addon_option_price = $option_name;
		} elseif ( 'flat_fixed_fee' == $price_type || 'fixed_fee_based_on_quantity' == $price_type ) {
			$addon_option_price = $option_name . ' (+' . get_woocommerce_currency_symbol() . ' ' . number_format( $option_price, 2, '.', '' ) . ')';
		} elseif ( 'flat_percentage_fee' == $price_type || 'Percentage_fee_based_on_quantity' == $price_type ) {
			$addon_option_price = $option_name . ' (+' . number_format( $option_price, 2, '.', '' ) . '%)';
		}
		?>
		<label class="af_addon_option_font_<?php echo esc_attr( $rule_id ); ?>"><?php echo esc_attr( ' ' . $addon_option_price ); ?></label>
		<?php
		if ( end( $options ) != $option_id ) {
			echo '<br>';
		}
	}
