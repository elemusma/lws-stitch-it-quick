<?php

if (!class_exists('PH_UPS_AccessPoint_Locator_Rest')) {

	class PH_UPS_AccessPoint_Locator_Rest {

		// Class Variables Declaration
		public $settings;
		public $endpoint;

		public function __construct() {

			$settings_helper 	= new PH_WC_UPS_Settings_Helper();
			$this->settings  	= $settings_helper->settings;

			if ("Live" === $this->settings['api_mode']) {
				$this->endpoint = 'https://onlinetools.ups.com/ups.app/xml/Locator';
			} else {
				$this->endpoint = 'https://wwwcie.ups.com/api/locations/v1/search/availabilities/64?Locale=en_US';
			}
		}

		private function wf_get_accesspoint_datas($order_details = null) {

			if (!empty($order_details)) {

				$address_field = $order_details->get_meta('_shipping_accesspoint');

				return json_decode($address_field);
			} else {

				return WC()->session->get('ph_ups_selected_access_point_details');
			}
		}

		public function update_access_point_select_options($array) {

			$response = null;
			$shipping_address = WC()->customer->get_shipping_address();
			$shipping_city = WC()->customer->get_shipping_city();
			$shipping_postalcode = WC()->customer->get_shipping_postcode();
			$shipping_state = WC()->customer->get_shipping_state();
			$shipping_country = WC()->customer->get_shipping_country();

			if (empty($shipping_country)) {
				return;
			}

			$jsonRequest = array();
			$json_option_code = array();

			foreach ($this->settings['accesspoint_option_code'] as $code) {

				if ($code == '014') {
					foreach (PH_WC_UPS_Constants::UPS_SERVICE_PROVIDER_CODE as $service_provider_code) {
						$json_option_code['OptionCode'] = array(
							'Code'	   => $service_provider_code,
						);
					}
				} else {
					$json_option_code['OptionCode'] = array(
						'Code'		  => $code,
					);
				}
			}

			//@note Phone number node missing 

			// JSON Request
			$jsonRequest = array(
				'LocatorRequest' => array(
					'Request' => array(
						'RequestAction' 		 => 'Locator',
						'RequestOption' 		 => $this->settings['accesspoint_req_option'],
					),
					'OriginAddress' => array(
						'AddressKeyFormat' => array(
							'ConsigneeName' 	 => 'yes',
							'AddressLine' 		 => $shipping_address,
							'PoliticalDivision2' => $shipping_city,
							'PoliticalDivision1' => $shipping_state,
							'PostcodePrimaryLow' => $shipping_postalcode,
							'CountryCode' 	     => $shipping_country,
						),
					),
					'Translate' => array(
						'Locale' 				 => 'en_US',
					),
					'UnitOfMeasurement' => array(
						'Code' 					 => 'MI',
					),
					'LocationSearchCriteria' => array(
						'SearchOption' => array(
							'OptionType' => array(
								'Code' 			 => '01',
							),
							'OptionCode' => array(
								'Code'			 => $json_option_code
							),
						),
						'MaximumListSize'  		 => $this->settings['accesspoint_max_limit'],
						'SearchRadius'			 => '50',
					),
				),
			);

			$jsonRequest 		= apply_filters('ph_ups_access_point_json_request', $jsonRequest, $this->settings);
			$jsonRequest		= wp_json_encode(Ph_UPS_Woo_Shipping_Common::convert_array_values_to_strings($jsonRequest), JSON_UNESCAPED_SLASHES);
			$transient			= 'ph_ups_access_point' . md5($jsonRequest);
			$cached_response	= get_transient($transient);
			$response			= $cached_response;

			$api_access_details = Ph_Ups_Endpoint_Dispatcher::phGetApiAccessDetails();

			if (empty($cached_response)) {

				$endpoint = Ph_Ups_Endpoint_Dispatcher::ph_get_enpoint('access-point');

				$response = Ph_Ups_Api_Invoker::phCallApi(
					PH_UPS_Config::PH_UPS_PROXY_API_BASE_URL . $endpoint,
					$api_access_details['token'],
					$jsonRequest
				);

				// Handle WP Error
				if (is_wp_error($response) && is_object($response)) {
					$wp_error_message = 'Error Message : ' . $response->get_error_message();
				}

				if (!empty($wp_error_message)) {
					return $array;
				}
			}

			$json = json_decode($response['body'], true);

			if (!empty($cached_response)) {

				Ph_UPS_Woo_Shipping_Common::phAddDebugLog("---------------------------------------- UPS Access Point Details - Using Cached Response ----------------------------------------", $this->settings['debug'] );
			}	
			
			Ph_UPS_Woo_Shipping_Common::phAddDebugLog("---------------------------------------- UPS Access Point Request ----------------------------------------", $this->settings['debug'] );
			Ph_UPS_Woo_Shipping_Common::phAddDebugLog( $jsonRequest, $this->settings['debug'] );
			
			Ph_UPS_Woo_Shipping_Common::phAddDebugLog("---------------------------------------- UPS Access Point Response ----------------------------------------", $this->settings['debug'] );
			Ph_UPS_Woo_Shipping_Common::phAddDebugLog( !empty($wp_error_message) ? $wp_error_message : $response['body'], $this->settings['debug'] );
			

			$locators = array();
			$drop_locations = array();

			if (isset($json['LocatorResponse']['SearchResults']['DropLocation'])) {

				$drop_locations = ($json['LocatorResponse']['SearchResults']['DropLocation']);

				if (empty($cached_response))	set_transient($transient, $response, 7200);
			}

			if (!empty($drop_locations)) {

				foreach ($drop_locations as $locator_id => $drop_location) {

					$locator_id				= (string)$drop_location['LocationID'];
					$locator_consignee_name	= substr((string)$drop_location['AddressKeyFormat']['ConsigneeName'] . ', ' . (string)$drop_location['AddressKeyFormat']['AddressLine'] . ', ' . (string)$drop_location['AddressKeyFormat']['PoliticalDivision2'] . ', ' . (string)$drop_location['AddressKeyFormat']['PostcodePrimaryLow'], 0, 70);

					$drop_location_data							=	array();
					$drop_location_data['LocationID']			=	$drop_location['LocationID'];
					$drop_location_data['AddressKeyFormat']		=	$drop_location['AddressKeyFormat'];
					$drop_location_data['AccessPointInformation'] =	$drop_location['AccessPointInformation'];
					$locator_full_address[$locator_consignee_name] = json_encode($drop_location_data);
					$locators[$locator_id] = $locator_consignee_name;

					$all_locators[$locator_id]					= json_encode($drop_location_data);
				}
				// Saving all the accesspoint locators in session
				WC()->session->set('ph_ups_access_point_details', $all_locators);
			}

			$locator = '<select id="shipping_accesspoint" name="shipping_accesspoint" class="select" data-select2="1">';
			$locator .=	"<option value=''>" . __('Select UPS Access Point® Location', 'ups-woocommerce-shipping') . "</option>";

			if (!empty($locators)) {

				foreach ($locators as $locator_id => $access_point_locator) {

					$updated_accesspoint = $this->wf_get_accesspoint_datas();

					$decoded_selected_accesspoint = (isset($updated_accesspoint) && is_string($updated_accesspoint)) ? json_decode($updated_accesspoint) : '';
					$consignee_name = (isset($decoded_selected_accesspoint['AddressKeyFormat']['ConsigneeName'])) ? $decoded_selected_accesspoint['AddressKeyFormat']['ConsigneeName'] : '';
					$address_line 	= (isset($decoded_selected_accesspoint['AddressKeyFormat']['AddressLine)']))   ? $decoded_selected_accesspoint['AddressKeyFormat']['AddressLine'] : '';
					$political_division_2 = (isset($decoded_selected_accesspoint['AddressKeyFormat']['PoliticalDivision2']))   ? $decoded_selected_accesspoint['AddressKeyFormat']['PoliticalDivision2'] : '';
					$post_code = (isset($decoded_selected_accesspoint['AddressKeyFormat']['PostcodePrimaryLow']))   ? $decoded_selected_accesspoint['AddressKeyFormat']['PostcodePrimaryLow'] : '';

					$selected_accesspoint_locator	=	substr($consignee_name . ', ' . $address_line . ', ' . $political_division_2 . ', ' . $post_code, 0, 70);

					$selected_accesspoint_locator	= isset($decoded_selected_accesspoint->LocationID) ? $decoded_selected_accesspoint->LocationID : '';

					if (!empty($selected_accesspoint_locator) && in_array($locator_id, $selected_accesspoint_locator)) {

						$locator .= "<option selected='selected' value='" . $locator_id . "'>" . $access_point_locator . "</option>";
					} else {

						$locator .= "<option value='" . $locator_id . "'>" . $access_point_locator . "</option>";
					}
				}
			} else {

				$this->wf_update_accesspoint_datas();
			}

			$locator .=	'</select>';
			$array['#shipping_accesspoint'] = $locator;
			return $array;
		}

		private function wf_update_accesspoint_datas($value = '') {
			WC()->session->set('ph_ups_selected_access_point_details', $value);
		}
	}
}
