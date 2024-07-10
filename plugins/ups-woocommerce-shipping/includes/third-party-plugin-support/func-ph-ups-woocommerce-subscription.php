<?php
if( ! function_exists('ph_ups_subscription_default_shipping_method') ) {
	function ph_ups_subscription_default_shipping_method( $default_bool, $order, $ups_services, $settings, $origin_country ) {
        
        //Check if the order contains Woocommerce-subscription product
		$items = $order->get_items();

		$is_subscription = false;
		$shippable = false;
		foreach ( $items as $item ) {
			$product_id = $item->get_product_id();
			$_product = wc_get_product( $product_id );

			if( $_product->needs_shipping()) {
				$shippable = true;
			}

			if( $_product->is_type( 'subscription' ) || $_product->is_type( 'variable-subscription' ) ){
				$is_subscription = true;
			}
		}

		if ( !$is_subscription || !$shippable ) {
			return $default_bool;
		}	

        //check if domestic or international
        $is_domestic = false;
        if($order->get_shipping_country() == $origin_country){
            $is_domestic = true;
        }


        if ( $is_domestic && !empty($settings['default_dom_service']) ){
            $service_code = $settings['default_dom_service'];
            $shipping_service_data = array(
                'shipping_method' 		=> WF_UPS_ID,
                'shipping_service' 		=> $service_code,
                'shipping_service_name'	=> isset( $ups_services[$service_code] ) ? $ups_services[$service_code] : '',
            );
        }elseif ( !$is_domestic && !empty($settings['default_int_service']) ){
            $service_code = $settings['default_int_service'];
            $shipping_service_data = array(
                'shipping_method' 		=> WF_UPS_ID,
                'shipping_service' 		=> $service_code,
                'shipping_service_name'	=> isset( $ups_services[$service_code] ) ? $ups_services[$service_code] : '',
            );
        }
        else
        {
            return $default_bool;
        }

        return $shipping_service_data;
    }
}

add_filter( 'ph_shipping_method_array_filter', 'ph_ups_subscription_default_shipping_method', 10, 5 );