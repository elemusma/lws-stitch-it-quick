<?php

if( ! function_exists('ph_ups_get_woocommerce_multicurrency_conversion_rate') ) {
	function ph_ups_get_woocommerce_multicurrency_conversion_rate($conversion_rate) {

		$ups_settings 			= get_option( 'woocommerce_'.WF_UPS_ID.'_settings', null );
		$ups_currency_type 		= !empty( $ups_settings['currency_type'] ) ? $ups_settings['currency_type'] : get_woocommerce_currency();
		$wc_store_currency		= get_woocommerce_currency();
		$woocommerce_currency_conversion_rate = get_option('woocommerce_multicurrency_rates');

		if( !empty($woocommerce_currency_conversion_rate) && isset($woocommerce_currency_conversion_rate[$wc_store_currency]) )
		{
			$ups_currency_rate = $woocommerce_currency_conversion_rate[$ups_currency_type];

			$new_currency_conversion_rate = $woocommerce_currency_conversion_rate[$wc_store_currency];

			$conversion_rate = round( ($new_currency_conversion_rate / $ups_currency_rate) , 3);
		}

		return $conversion_rate;
	}
}

add_filter( 'xa_conversion_rate', 'ph_ups_get_woocommerce_multicurrency_conversion_rate' ,12,2);

