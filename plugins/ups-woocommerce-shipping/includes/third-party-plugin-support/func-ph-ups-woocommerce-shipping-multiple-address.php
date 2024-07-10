<?php

/**
 * This file is to support Woocommerce shipping multiple address. It will be included only when Woocommerce shipping multiple address.
 */

if (!function_exists('ph_ups_label_packages_from_wcms')) {

	/**
	 * Support for shipping multiple address. Get customized package.
	 */
	function ph_ups_label_packages_from_wcms($packages, $address, $order_id)
	{
		$wms_packages = PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, '_wcms_packages');

		if (is_array($wms_packages)) {
			return $wms_packages;
		}

		return $packages;
	}

	add_filter('wf_ups_filter_label_from_packages', 'ph_ups_label_packages_from_wcms', 10, 3);
}

// To display respective Shipping Method for Multiple Shipping Addresses in Order Shipping Addresses Meta Box
add_filter('woocommerce_order_get_items', 'ph_ups_update_shipping_method_instance_id', 10, 3);

function ph_ups_update_shipping_method_instance_id($items, $that, $type)
{
	if ($type[0] == 'shipping' && !empty($items) && is_array($items)) {

		$order_id 			= '';
		$method_index 		= 0;
		$methods 			= [];
		$shipping_methods 	= [];

		foreach ($items as $order_item_shipping) {

			if (is_object($order_item_shipping)) {

				$order_id 	= $order_item_shipping->get_order_id();
				$order_meta = $order_item_shipping->get_meta_data();

				foreach ($order_meta as $key => $meta_value) {

					$order_meta_data 		= $meta_value->get_data();

					if (is_array($order_meta_data) && !empty($order_meta_data) && $order_meta_data['key'] == '_xa_ups_method' && is_array($order_meta_data['value'])) {

						$instance_array = explode(':', $order_meta_data['value']['id']);

						if (isset($instance_array[1]) && !empty($instance_array[1])) {

							$methods[$method_index]['id'] = $order_meta_data['value']['id'];
							$methods[$method_index]['label'] = $order_meta_data['value']['id'];

							$shipping_methods 	=  array_merge($shipping_methods, $methods);

							$order_item_shipping->set_instance_id($instance_array[1]);

							$order_item_shipping->apply_changes();

							$order_item_shipping->save();
						}
					}
				}
			}
		}

		if (!empty($order_id) && !empty($shipping_methods)) {

			$new_order_total 			= 0;
			$order_total 				= 0;
			$order_subtotal 			= 0;
			$order_cart_tax				= 0;
			$order_discount_with_tax	= 0;
			$shipping_total 			= 0;
			$shipping_tax 				= 0;
			$order 						= wc_get_order($order_id);

			if (is_object($order) && !empty($order->get_user_id()) && !empty($order->get_order_key()) && $order->get_type() == 'shop_order') {

				// ph_get_meta_data() is not returning value
				// $order_shipping 			= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, '_order_shipping');
				$order_shipping 			= get_post_meta($order_id, '_order_shipping', true);

				$order_total 				= !empty($order->get_total()) ? $order->get_total() : 0;
				$order_subtotal 			= !empty($order->get_subtotal()) ? $order->get_subtotal() : 0;
				$order_cart_tax				= !empty($order->get_cart_tax()) ? $order->get_cart_tax() : 0;
				$order_discount_with_tax	= !empty($order->get_total_discount(false)) ? $order->get_total_discount(false) : 0;
				$shipping_total 			= !empty($order->get_shipping_total()) ? $order->get_shipping_total() : 0;
				$shipping_tax 				= !empty($order->get_shipping_tax()) ? $order->get_shipping_tax() : 0;
				$need_to_update  			= false;
				
				$new_order_total = round(($order_subtotal + $order_cart_tax + $shipping_total + $shipping_tax) - $order_discount_with_tax, 2);

				if (($order_total != $new_order_total) && ($order_total < $new_order_total)) {

					$need_to_update  = true;
					$order->set_total($new_order_total);
				}

				if (($order_shipping != $shipping_total) && ($order_shipping < $shipping_total)) {

					$need_to_update  = true;
					$order->set_shipping_total($shipping_total);
				}

				if ( $need_to_update ) {

					$order->save();
				}
			}
		}
	}

	return $items;
}

if (!function_exists('ph_ups_split_shipments_based_on_destination')) {

	/**
	 * Support for shipping multiple address. Get customized package.
	 */
	function ph_ups_split_shipments_based_on_destination($shipments_split_based_on_service, $order)
	{
		$shipment_data_split_based_on_destination_and_service = array();
		$i = 0;

		foreach ($shipments_split_based_on_service as $shipment_key => $shipment) {

			if (sizeof($shipment['packages']) > 1) {

				$destination_array_for_lookup = array();

				foreach ($shipment['packages'] as $package_index => $package) {

					$current_destination = implode(',', $package['destination']);

					if (in_array($current_destination, $destination_array_for_lookup)) {

						$index = array_search($current_destination, $destination_array_for_lookup);
						$shipment_data_split_based_on_destination_and_service[$index]['packages'][] = $package;
					} else {

						$shipment_data_split_based_on_destination_and_service[$i]['shipping_service'] = $shipment['shipping_service'];
						$shipment_data_split_based_on_destination_and_service[$i]['packages'][] = $package;
						$destination_array_for_lookup[$i] = $current_destination;
						$i++;
					}
				}
			} else {

				$shipment_data_split_based_on_destination_and_service[$i] = $shipment;
				$i++;
			}
		}

		return $shipment_data_split_based_on_destination_and_service;
	}

	add_filter('wf_ups_shipment_data', 'ph_ups_split_shipments_based_on_destination', 10, 2);
}


if (!function_exists('ph_ups_add_destination_to_packages')) {

	/**
	 * Support for shipping multiple address. Get customized package.
	 */
	function ph_ups_add_destination_to_packages($packages, $destination)
	{

		foreach ($packages as $package_index => &$package_value) {

			$package_value['destination'] = $destination;
		}

		return $packages;
	}

	add_filter('ph_ups_customize_package_by_desination', 'ph_ups_add_destination_to_packages', 10, 2);
}

if (!function_exists('ph_ups_get_shipping_address_from_shipment')) {

	/**
	 * Support for shipping multiple address. Get  product data.
	 */
	function ph_ups_get_shipping_address_from_shipment($address, $shipment, $ship_from_address, $order_id, $from_to)
	{
		$wms_packages = PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, '_wcms_packages');

		if (empty($wms_packages)) {
			return $address;
		}

		if ($ship_from_address == 'billing_address' && $from_to == 'from' && isset($shipment['packages'][0]['destination'])) {

			// Will take the destination address from the first package, since all package have same destination.
			$shipping_address = $shipment['packages'][0]['destination'];

			$address = array(
				'name'		=> htmlspecialchars($shipping_address['first_name']) . ' ' . htmlspecialchars($shipping_address['last_name']),
				'company' 	=> !empty($shipping_address['company']) ? htmlspecialchars($shipping_address['company']) : '-',
				'phone' 	=> $address['phone'],
				'email' 	=> htmlspecialchars($address['email']),
				'address_1'	=> htmlspecialchars($shipping_address['address_1']),
				'address_2'	=> htmlspecialchars($shipping_address['address_2']),
				'city' 		=> htmlspecialchars($shipping_address['city']),
				'state' 	=> htmlspecialchars($shipping_address['state']),
				'country' 	=> $shipping_address['country'],
				'postcode' 	=> $shipping_address['postcode'],
			);
		} elseif ($ship_from_address != 'billing_address' && $from_to == 'to' && isset($shipment['packages'][0]['destination'])) {

			// Will take the destination address from the first package, since all package have same destination.
			$shipping_address = $shipment['packages'][0]['destination'];

			$address = array(
				'name'		=> htmlspecialchars($shipping_address['first_name']) . ' ' . htmlspecialchars($shipping_address['last_name']),
				'company' 	=> !empty($shipping_address['company']) ? htmlspecialchars($shipping_address['company']) : '-',
				'phone' 	=> $address['phone'],
				'email' 	=> htmlspecialchars($address['email']),
				'address_1'	=> htmlspecialchars($shipping_address['address_1']),
				'address_2'	=> htmlspecialchars($shipping_address['address_2']),
				'city' 		=> htmlspecialchars($shipping_address['city']),
				'state' 	=> htmlspecialchars($shipping_address['state']),
				'country' 	=> $shipping_address['country'],
				'postcode' 	=> $shipping_address['postcode'],
			);
		}

		return $address;
	}

	add_filter('ph_ups_address_customization', 'ph_ups_get_shipping_address_from_shipment', 10, 5);
}


// Get correct shipping service for package
if (!function_exists('ph_ups_shipping_method_for_multiple_address')) {

	function ph_ups_shipping_method_for_multiple_address($shipping_sevice_id, $order, $package_group_key = false)
	{

		if ($package_group_key === 0 || $package_group_key != false) {

			$shipping_methods 	= $order->get_shipping_methods();
			$shipping_method_id = '';

			while ($package_group_key >= 0) {

				$shipping_method 	= array_shift($shipping_methods);
				$package_group_key--;
			}

			if (!empty($shipping_method)) {

				$shipping_method_meta 	= $shipping_method->get_meta('_xa_ups_method');
				$shipping_method_id 	= !empty($shipping_method_meta) ? $shipping_method_meta['id'] : $shipping_method['method_id'];
			}

			if (!empty($shipping_method_id) && !is_array($shipping_method_id)) {

				$method_id			= explode(':', $shipping_method_id);
				$shipping_method 	= isset($method_id[1]) ? $method_id[1] : $shipping_sevice_id;

				return $shipping_method;
			} else {

				return $shipping_sevice_id;
			}
		} else {

			return $shipping_sevice_id;
		}
	}
}

add_filter('ph_ups_modify_shipping_method_service', 'ph_ups_shipping_method_for_multiple_address', 10, 3);
