<?php

if( ! function_exists('ph_ups_get_currency_conversion_rate') ) {
	function ph_ups_get_currency_conversion_rate($conversion_rate,$currency_type) {
		$wc_store_currency		= get_woocommerce_currency();
		$currency_params 		= get_option( 'woo_multi_currency_params', array() );
		if(!isset($currency_params['currency_default']))
        {
            return $conversion_rate;
        }
		$wc_default_currency 	= $currency_params['currency_default'];
		if( isset($currency_params['currency']) )
		{
			foreach ($currency_params['currency'] as $key => $currency) {
				$currency_conversion[$currency] =  $currency_params['currency_rate'][$key];
			}
		}

		if( $wc_store_currency!=$wc_default_currency && !empty($currency_conversion[$wc_store_currency]) )
		{
			$conversion_rate = round( ($conversion_rate * $currency_conversion[$wc_store_currency]), 2);
		}
		
		return $conversion_rate;
	}

}
add_filter( 'ph_ups_currency_conversion_rate', 'ph_ups_get_currency_conversion_rate' ,10,2);
