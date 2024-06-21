<?php
defined( 'ABSPATH' ) || exit();
?>

<select class="af_addon_multi_select <?php echo esc_attr( $af_addon_front_dep_class ); ?>" name="af_addons_options_<?php echo esc_attr( $field_id ); ?>[]" multiple="multiple" placeholder='Select an option' data-dependent_on="<?php echo esc_attr( $dependent_fields ); ?>" id="<?php echo esc_attr( $af_addon_field_id ); ?>" data-dependent_val="<?php echo esc_attr( $dependent_options ); ?>" style="height: 40px !important; width: 100% !important;">
	<?php
	$args = array(
		'post_type'   => 'af_pao_options',
		'post_status' => 'publish',
		'post_parent' => $field_id,
		'numberposts' => -1,
		'fields'      => 'ids',
		'orderby'     => 'menu_order',
		'order'       => 'ASC',
	);

	$options = get_posts( $args );

	foreach ( $options as $option_id ) {
		if ( empty( $option_id ) ) {
			continue;
		}
		$option_name  = get_post_meta( $option_id, 'af_addon_field_options_name', true );
		$price_type   = get_post_meta( $option_id, 'af_addon_field_options_price_type', true );
		$option_price = (float) get_post_meta( $option_id, 'af_addon_field_options_price', true );
		?>
		<option 
			value="<?php echo esc_attr( $option_id ); ?>" 
			data-option_name="<?php echo esc_attr( $option_name ); ?>" 
			data-price_type="<?php echo esc_attr( $price_type . '_multi_selecter' ); ?>" 
			data-option_price="<?php echo esc_attr( $option_price ); ?>">
			<?php
			$addon_option_price = '';
			if ( 'free' == $price_type ) {
				$addon_option_price = $option_name;
			} elseif ( 'flat_fixed_fee' == $price_type || 'fixed_fee_based_on_quantity' == $price_type ) {
				$addon_option_price = $option_name . ' (+' . get_woocommerce_currency_symbol() . ' ' . number_format( $option_price, 2, '.', '' ) . ')';
			} elseif ( 'flat_percentage_fee' == $price_type || 'Percentage_fee_based_on_quantity' == $price_type ) {
				$addon_option_price = $option_name . ' (+' . number_format( $option_price, 2, '.', '' ) . '%)';
			}

			echo esc_attr( ' ' . $addon_option_price );
			?>
		</option>
		<?php
	}
	?>
</select>
