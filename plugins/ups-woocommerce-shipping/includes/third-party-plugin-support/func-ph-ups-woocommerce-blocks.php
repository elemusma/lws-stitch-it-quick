<?php

use Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils;

if( ! function_exists('ph_ups_get_estimated_delivery_html') ) {

	function ph_ups_get_estimated_delivery_html($label, $estimated_delivery_text, $estimated_delivery) {

		// In case of classic cart and block checkout or vice versa
		if ( (is_cart() && !CartCheckoutUtils::is_cart_block_default()) || (is_checkout() && !CartCheckoutUtils::is_checkout_block_default()) ) {
			return $label;
		}

		global $wp_version;

		$wp_date_time_format = Ph_UPS_Woo_Shipping_Common::get_wordpress_date_format().' '.Ph_UPS_Woo_Shipping_Common::get_wordpress_time_format();

		$formatted_date = $estimated_delivery->format($wp_date_time_format);


		if ( version_compare( $wp_version, '5.3', '>=' ) ) {

			if (date_default_timezone_get()) {

				$zone 		= new DateTimeZone(date_default_timezone_get());

			}else{

				$zone 		= new DateTimeZone('UTC');
			}
			
			if( strtotime($formatted_date) ) {

				$formatted_date = wp_date( $wp_date_time_format, strtotime($formatted_date), $zone );
			}

		}else{

			if( strtotime($formatted_date) ) {

				$formatted_date = date_i18n( $wp_date_time_format, strtotime($formatted_date) );

			}

		}

		if( ! empty($estimated_delivery_text) )
			$est_delivery_html 	= " (".$estimated_delivery_text. $formatted_date.')</small>';
		else
			$est_delivery_html 	= "<br /><small>".__('Est delivery:', 'ups-woocommerce-shipping')." ". $formatted_date.'</small>';

		$label .= $est_delivery_html;

		return $label;
	}
}
add_filter( 'ph_ups_estimated_delivery_html', 'ph_ups_get_estimated_delivery_html' ,10,3);