<?php
if (!class_exists('PH_UPS_Pickup_Admin_Rest')) {

	class PH_UPS_Pickup_Admin_Rest extends PH_Shipping_UPS_Admin_Rest {
		
		// Class Variables Declaration;
		private $debug_datas;

		var $_pickup_prn	= '_ups_pickup_prn';
		var $_pickup_date	= '_ups_pickup_date';

		public function __construct() {

			$settings_helper 	= new PH_WC_UPS_Settings_Helper();
			$this->settings  	= $settings_helper->settings;

			$this->debug_datas		= array();
		}

		public function get_pickup_creation_request($order_ids, $pickup_address = 'origin') {

			$pieces	=	$this->get_pickup_pieces($order_ids);
			$json_request = array();
			// no piece found !
			if (!$pieces)
				return false;

			$total_weight	=	0;
			$over_weight	=	'N';
			foreach ($pieces as $piece) {

				if ($piece['Weight'] > 70) {	// More than 70 lbs package considered as over weight
					$over_weight	=	'Y';
				}

				$total_weight	=	$total_weight	+	$piece['Weight'];
			}

			//JSON Request
			$json_request['PickupCreationRequest']	=	array(
				'Request'	=>	array(
					'TransactionReference'	=>	array(
						'CustomerContext'	=>	'UPS Pickup Request',
					),
				),
				'RatePickupIndicator'		=>	'N',
			);

			$json_request['PickupCreationRequest'] = array_merge($json_request['PickupCreationRequest'], $this->get_shipper_info());
			$json_request['PickupCreationRequest'] = array_merge($json_request['PickupCreationRequest'], $this->get_pickup_date_info($order_ids));

			if ($pickup_address == 'origin') {
				$json_request['PickupCreationRequest'] = array_merge($json_request['PickupCreationRequest'], $this->get_pickup_address());
			} else {
				$json_request['PickupCreationRequest'] = array_merge($json_request['PickupCreationRequest'], $this->get_pickup_address(current($order_ids)));
			}

			$json_request['PickupCreationRequest']['AlternateAddressIndicator'] = 'Y';

			$piece_json = 	array();

			foreach ($pieces as $pickup_piece) {
				$piece_json['PickupPiece'][]	= array(
					'ServiceCode'	=>	'0' . $pickup_piece['ServiceCode'],
					'Quantity'		=>	strval($pickup_piece['Quantity']),
					'DestinationCountryCode'	=>	$pickup_piece['DestinationCountryCode'],
					'ContainerCode'		=>	$pickup_piece['ContainerCode'],
				);
			}

			$json_request['PickupCreationRequest'] = array_merge($json_request['PickupCreationRequest'], $piece_json);

			$json_request['PickupCreationRequest']['TotalWeight']	= array(
				'Weight'	=> strval(round($total_weight, 1)),
				'UnitOfMeasurement'	=>	$this->settings['weight_unit'],
			);

			$json_request['PickupCreationRequest']['OverweightIndicator'] = $over_weight;
			$json_request['PickupCreationRequest']['PaymentMethod'] = '01';

			return $json_request;
		}

		public function get_shipper_info() {

			$json 	= array(
				'Shipper'	=> array(
					'Account'	=> array(
						'AccountNumber'			=>	$this->settings['shipper_number'],
						'AccountCountryCode'	=>	$this->settings['origin_country'],
					)
				)
			);

			return $json;
		}

		public function get_pickup_date_info($order_ids) {

			$timestamp				=	strtotime(date('Y-m-d')); // Timestamp of the 00:00 hr of this day		
			$pickup_ready_timestamp	=	$timestamp + $this->settings['pickup_start_time'] * 3600 * 1;
			$pickup_close_timestamp	=	$timestamp + $this->settings['pickup_close_time'] * 3600;

			if ('current' == $this->settings['pickup_date']) {

				$current_wp_time_hour_minute = current_time('H:i');

				$date 	=  date('Ymd');

				if ($current_wp_time_hour_minute > $this->settings['pickup_close_time']) {

					$pickup_date = date('Ymd', strtotime('+1 days', strtotime($date)));
				} else {

					$pickup_date = $date;
				}
			} else {

				$pickup_date = $this->get_next_working_day();
			}

			// Update pickup date for all order ids
			foreach ($order_ids as $oid) {

				PH_UPS_WC_Storage_Handler::ph_add_and_save_meta_data($oid, $this->_pickup_date, $pickup_date);
			}

			$json = array(
				'PickupDateInfo' => array(
					'CloseTime'	 => date("Hi", $pickup_close_timestamp),
					'ReadyTime'	 =>	date("Hi", $pickup_ready_timestamp),
					'PickupDate' => $pickup_date,
				)
			);

			return $json;
		}

		private function get_next_working_day() {

			$day_order = array(
				0 => 'Sun',
				1 => 'Mon',
				2 => 'Tue',
				3 => 'Wed',
				4 => 'Thu',
				5 => 'Fri',
				6 => 'Sat',
			);

			$today 				= date("D");
			$next_working_day 	= $today;
			$date 				= new DateTime();
			$today_key 			= array_search($today, $this->settings['working_days']);

			// Convert close time from H.i to H:i format to compare
			$pickup_close_time	= date("H:i", strtotime(date('Y-m-d')) + 3600 * $this->settings['pickup_close_time']);

			// Check Current Time exceeds Store Cut-off Time, if yes request on next day
			$current_wp_time_hour_minute = current_time('H:i');

			if (strtotime($current_wp_time_hour_minute) > strtotime($pickup_close_time)) {
				$today_key = '';
				$today = date("D", strtotime("+1 day"));
			}

			if (!empty($today_key) || $today_key === 0) {
				return $date->format('Ymd');
			} else {

				$today_key = array_search($today, $day_order);

				for ($i = 0; $i < 8; $i++) {

					$found_index 	= array_search($day_order[$today_key], $this->settings['working_days']);

					if (!empty($found_index) || $found_index === 0) {

						$next_working_day = $this->settings['working_days'][$found_index];
						break;
					}

					if ($today_key <= 5)  $today_key++;
					else  $today_key = 0;
				}

				$date->modify("next $next_working_day");

				return $date->format('Ymd');
			}
		}

		public function get_pickup_address($order_id = null) {

			$ship_from_address = 'origin_address';

			if (!empty($order_id)) {

				$ship_from_address 			= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, 'ph_ship_from_address');
			}

			if ($ship_from_address != 'origin_address') {

				$order = wc_get_order($order_id);

				$ups_user_name				= !empty($order->get_shipping_company()) ?  $order->get_shipping_company() : "-";
				$ups_display_name			= $order->get_shipping_first_name();
				$phone_number				= $order->get_billing_phone();
				$ups_origin_addressline		= $order->get_shipping_address_1();
				$ups_origin_addressline_2	= $order->get_shipping_address_2();
				$ups_origin_city			= $order->get_shipping_city();
				$ups_origin_postcode		= $order->get_shipping_postcode();
				$origin_state				= $order->get_shipping_state();
				$origin_country				= $order->get_shipping_country();
			} else {

				$ups_user_name					= $this->settings['ups_user_name'];
				$ups_display_name				= $this->settings['ups_display_name'];
				$phone_number 					= $this->settings['phone_number'];
				$ups_origin_addressline 		= $this->settings['origin_addressline'];
				$ups_origin_addressline_2 		= $this->settings['origin_addressline_2'];
				$ups_origin_city 				= $this->settings['origin_city'];
				$ups_origin_postcode 			= $this->settings['origin_postcode'];
				$origin_state					= $this->settings['origin_state'];
				$origin_country					= $this->settings['origin_country'];
			}

			$json = array(
				'PickupAddress' => array(
					'CompanyName' => $ups_user_name,
					'ContactName' => $ups_display_name,
					'AddressLine' => substr(($ups_origin_addressline . ' ' . $ups_origin_addressline_2), 0, 72),
					'City' => $ups_origin_city,
					'StateProvince' => $origin_state,
					'PostalCode' => $ups_origin_postcode,
					'CountryCode' => $origin_country,
					'ResidentialIndicator' => 'Y',
					'PickupPoint' => 'Lobby',
					'Phone' => array(
						'Number' => $phone_number
					)
				)
			);

			return $json;
		}

		public function get_pickup_pieces($order_ids) {

			$pickup_pieces	=	array();

			foreach ($order_ids as $order_id) {

				// Skip the order if pickup is already requested
				if ($this->is_pickup_requested($order_id)) {

					continue;
				}

				$order = wc_get_order($order_id);

				if (!($order instanceof WC_Order)) {

					wf_admin_notice::add_notice('Cannot load order.');
					return false;
				}

				$selected_service_code	= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, 'wf_ups_selected_service');
				$generated_services		= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, 'xa_ups_generated_label_services');
				$services_array 		= !empty($generated_services) ? json_decode($generated_services) : array();
				$package_data			= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, '_wf_ups_stored_packages');

				if (!isset($selected_service_code) || empty($selected_service_code)) {

					wf_admin_notice::add_notice('Order #' . $order_id . ': Label not generated yet');
					return false;
				}

				$piece_data = array();

				foreach ($package_data as $package_key => $package_group) {

					foreach ($package_group as $grp_key => $package_data) {

						$service_code 	= isset($services_array[$package_key]) && !empty($services_array[$package_key]) ? $services_array[$package_key] : $selected_service_code;
						$piece_data[]	= $this->get_order_piece_from_package($package_data, $order, $service_code);
					}
				}

				$pickup_pieces	=	array_merge($pickup_pieces, $piece_data);
			}

			return $pickup_pieces;
		}

		public function get_order_piece_from_package($package_data, $order, $service_code) {

			$piece_data	=	array();

			$piece_data['Weight']					=	$package_data['PackageWeight']['Weight'];
			$piece_data['Quantity']					=	1;
			$piece_data['DestinationCountryCode']	=	$order->get_shipping_country();
			$piece_data['ServiceCode']				=	$service_code;
			$piece_data['ContainerCode']			=	'01'; // 01 = Package, 02 = UPS Letter, 03 = Pallet

			return $piece_data;
		}

		public function request_pickup($request) {
			
			$request = wp_json_encode($request);

			// Check for active plugin license
			if (Ph_UPS_Woo_Shipping_Common::phHasActiveLicense()) {

				$api_access_details = Ph_Ups_Endpoint_Dispatcher::phGetApiAccessDetails();

				if (!$api_access_details) {
					wf_admin_notice::add_notice('Failed to get API access token. Please check WooCommerce logs for more information.');
					return false;
				}

				$endpoint = Ph_Ups_Endpoint_Dispatcher::ph_get_enpoint('shipment/pickup/requested');

				$response = Ph_Ups_Api_Invoker::phCallApi(
					PH_UPS_Config::PH_UPS_PROXY_API_BASE_URL . $endpoint,
					$api_access_details['token'],
					$request
				);

			} else {

				wf_admin_notice::add_notice('Please use a valid plugin license to continue using WooCommerce UPS Shipping Plugin with Print Label');
				return false;
			}

			if (is_wp_error($response) && is_object( $response )) {

				$error_message = $response->get_error_message();

				wf_admin_notice::add_notice('Sorry. Something went wrong: ' . $error_message);
				return false;
			}

			if ($this->settings['debug']) {
				$this->debug_datas[] = array(
					'PICKUP REQUEST' 	=> $request,
					'PICKUP RESPONSE' 	=> $response['body'],
				);

				Ph_UPS_Woo_Shipping_Common::phAddDebugLog(__('------------------------------- UPS PICKUP REQUEST -------------------------------', 'ups-woocommerce-shipping'), $this->settings['debug']);
				Ph_UPS_Woo_Shipping_Common::phAddDebugLog( $request, $this->settings['debug']);
				Ph_UPS_Woo_Shipping_Common::phAddDebugLog(__('------------------------------- UPS PICKUP RESPONSE -------------------------------', 'ups-woocommerce-shipping'), $this->settings['debug']);
				Ph_UPS_Woo_Shipping_Common::phAddDebugLog( $response['body'], $this->settings['debug']);
			}

			$response_obj = json_decode($response['body'], true);

			if (isset($response_obj['PickupCreationResponse']['Response']['ResponseStatus']['Code']) && $response_obj['PickupCreationResponse']['Response']['ResponseStatus']['Code'] == 1) {

				$data	= array(
					'PRN'	=>	(string)$response_obj['PickupCreationResponse']['PRN'],
				);

				return $data;
			} else {

				if (isset($response_obj['response']['errors'][0]['code'])) {
					$error_description	=	(string)$response_obj['response']['errors'][0]['message'];
					wf_admin_notice::add_notice($error_description);
					return false;
				}
			}
		}

		public function pickup_cancel($order_id, $pickupPrn) {

			$order = wc_get_order($order_id);

			if (!($order instanceof WC_Order)) {

				wf_admin_notice::add_notice('Cannot load order.');
				return false;
			}

			if (!$this->is_pickup_requested($order_id)) {
				wf_admin_notice::add_notice('Pickup request not found for order #' . $order_id);
				return false;
			}

			$result		=	$this->run_pickup_cancel($order_id, $pickupPrn);
			return $result;
		}

		public function run_pickup_cancel($order_id, $pickupPrn) {

			$settings_helper 	= new PH_WC_UPS_Settings_Helper();
			$this->settings  	= $settings_helper->settings;

			// Check for active plugin license
			if (Ph_UPS_Woo_Shipping_Common::phHasActiveLicense()) {
				
				$api_access_details = Ph_Ups_Endpoint_Dispatcher::phGetApiAccessDetails();

				if (!$api_access_details) {
					wf_admin_notice::add_notice('Failed to get API access token. Please check WooCommerce log for more information.');
					return false;
				}

				$endpoint = Ph_Ups_Endpoint_Dispatcher::ph_get_enpoint('shipment/pickup/cancelled');

				$headers['prn'] = $pickupPrn;

				$response = Ph_Ups_Api_Invoker::phCallApi(
					PH_UPS_Config::PH_UPS_PROXY_API_BASE_URL . $endpoint,
					$api_access_details['token'],
					[],
					$headers
				);
			
			} else {
				wf_admin_notice::add_notice('Please use a valid plugin license to continue using WooCommerce UPS Shipping Plugin with Print Label');
				return false;
			}

			if (is_wp_error($response) && is_object( $response )) {

				$error_message = $response->get_error_message();

				wf_admin_notice::add_notice('Sorry. Something went wrong: ' . $error_message);
				return false;
			}

			if ($this->settings['debug']) {
				$this->debug_datas[] = array(
					'PICKUP CANCEL REQUEST' 	=> $pickupPrn,
					'PICKUP CANCEL RESPONSE' 	=> $response['body'],
				);

				Ph_UPS_Woo_Shipping_Common::phAddDebugLog(__('------------------------------- UPS PICKUP CANCEL REQUEST -------------------------------', 'ups-woocommerce-shipping'), $this->settings['debug']);
				Ph_UPS_Woo_Shipping_Common::phAddDebugLog( __('Order Id: ' . $order_id . ' | Prn: ' . $pickupPrn, 'ups-woocommerce-shipping'), $this->settings['debug']);
				Ph_UPS_Woo_Shipping_Common::phAddDebugLog(__('------------------------------- UPS PICKUP CANCEL RESPONSE -------------------------------', 'ups-woocommerce-shipping'), $this->settings['debug']);
				Ph_UPS_Woo_Shipping_Common::phAddDebugLog( $response['body'], $this->settings['debug']);
			}

			$response_obj = json_decode($response['body'], true);
			if (isset($response_obj['PickupCancelResponse']['Response']['ResponseStatus']['Code']) && $response_obj['PickupCancelResponse']['Response']['ResponseStatus']['Code'] == 1) {
				return true;
			} else {
				if (isset($response_obj['response']['errors'][0]['code'])) {
					$error_description	=	(string)$response_obj['response']['errors'][0]['message'];
					wf_admin_notice::add_notice($error_description);
					return false;
				}
			}
		}

		public function is_pickup_requested($order_id) {
			return $this->get_pickup_no($order_id) ? true : false;
		}

		public function get_pickup_no($order_id) {
			if (empty($order_id))
				return false;

			$pickup_confirmation_number	=	PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, $this->_pickup_prn);

			return $pickup_confirmation_number;
		}

		public function get_pickup_date($order_id) {
			if (empty($order_id))
				return false;

			$pickup_date	= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, $this->_pickup_date);

			if (!empty($pickup_date)) {

				$wp_date_format = get_option('date_format');
				$pickup_date 	= date($wp_date_format, strtotime($pickup_date));
			}

			return $pickup_date;
		}

		function delete_pickup_details($order_id, $ph_metadata_handler) {
			$ph_metadata_handler->ph_delete_meta_data($this->_pickup_prn);

			return $ph_metadata_handler;
		}
	}
}
