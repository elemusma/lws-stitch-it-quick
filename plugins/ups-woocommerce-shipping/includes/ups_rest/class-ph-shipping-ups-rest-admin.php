<?php

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

class PH_Shipping_UPS_Admin_Rest {

	private static $wc_version;

	/**
	 * General Variables 
	 */
	public $settings, $wcsups_rest, $debug;
	/**
	 * Auto label generation Variables
	 */
	public $auto_label_generation, $auto_label_services;
	/**
	 * Custom Fields Variables
	 */
	public $is_hazmat_product;

	/**
	 * Constructor for the class PH_Shipping_UPS_Admin_Rest.
	 */
	public function __construct() {

		$this->init();
	}

	/**
	 * Initializes the UPS shipping method.
	 */
	private function init() {

		if (empty(self::$wc_version))	self::$wc_version = WC()->version;

		if (!class_exists('PH_Shipping_UPS_Rest')) {
			include_once plugin_dir_path(dirname(__FILE__)) . 'ups_rest/class-ph-shipping-ups-rest.php';
		}

		add_filter('wf_ups_filter_label_packages', array($this, 'manual_packages'), 10, 2);
	}

	public function ph_editable_access_point_location($order) {

		$order_id 				= $order->get_id();
		$ph_metadata_handler	= new PH_UPS_WC_Storage_Handler($order);
		$access_point_location 	= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, '_shipping_accesspoint');
		$accesspoint_locators 	= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, '_ph_accesspoint_location');
		$selected_accesspoint_locator = '';

		if( empty( $this->settings )) {
			$settings_helper 	= new PH_WC_UPS_Settings_Helper();
			$this->settings  	= $settings_helper->settings;
		}

		$this->wcsups_rest  = new PH_Shipping_UPS_Rest( $order );
		$this->debug		= $this->settings['debug'];
		
		if (!empty($access_point_location)) {

			// Older plugin version - $access_point_location will be of JSON type
			// From plugin version 4.2.7 - $access_point_location will be locator_id string
			$decoded_accesspoint_location = json_decode($access_point_location, true);

			// For supporting previous versions of plugin
			if (empty($accesspoint_locators) && !empty($access_point_location)) {

				$decoded_order_formatted_accesspoint = $access_point_location;
			} else {

				if (is_array($decoded_accesspoint_location)) {

					$decoded_accesspoint_location['LocationID'] = is_array($decoded_accesspoint_location['LocationID']) ? $decoded_accesspoint_location['LocationID'] : array($decoded_accesspoint_location['LocationID']);

					$access_point_location = implode('', $decoded_accesspoint_location['LocationID']);
				}

				if (is_array($accesspoint_locators)) {

					foreach ($accesspoint_locators as $locator_id => $locator) {

						if ($locator_id == $access_point_location) {

							$decoded_order_formatted_accesspoint = $locator;
							break;
						}
					}
				} else {

					$decoded_order_formatted_accesspoint = $accesspoint_locators;
				}
			}

			$decoded_order_formatted_accesspoint = json_decode($decoded_order_formatted_accesspoint);

			$accesspoint_name = (isset($decoded_order_formatted_accesspoint->AddressKeyFormat->ConsigneeName)) ? $decoded_order_formatted_accesspoint->AddressKeyFormat->ConsigneeName : '';
			$accesspoint_address = (isset($decoded_order_formatted_accesspoint->AddressKeyFormat->AddressLine)) ? $decoded_order_formatted_accesspoint->AddressKeyFormat->AddressLine : '';
			$accesspoint_city = (isset($decoded_order_formatted_accesspoint->AddressKeyFormat->PoliticalDivision2)) ? $decoded_order_formatted_accesspoint->AddressKeyFormat->PoliticalDivision2 : '';
			$accesspoint_state = (isset($decoded_order_formatted_accesspoint->AddressKeyFormat->PoliticalDivision1)) ? $decoded_order_formatted_accesspoint->AddressKeyFormat->PoliticalDivision1 : '';
			$accesspoint_country = (isset($decoded_order_formatted_accesspoint->AddressKeyFormat->CountryCode)) ? $decoded_order_formatted_accesspoint->AddressKeyFormat->CountryCode : '';
			$accesspoint_postcode = (isset($decoded_order_formatted_accesspoint->AddressKeyFormat->PostcodePrimaryLow)) ? $decoded_order_formatted_accesspoint->AddressKeyFormat->PostcodePrimaryLow : '';

			$ph_metadata_handler->ph_update_meta_data('_ph_accesspoint_name', $accesspoint_name);
			$ph_metadata_handler->ph_update_meta_data('_ph_accesspoint_address', $accesspoint_address);
			$ph_metadata_handler->ph_update_meta_data('_ph_accesspoint_city', $accesspoint_city);
			$ph_metadata_handler->ph_update_meta_data('_ph_accesspoint_statecode', $accesspoint_state);
			$ph_metadata_handler->ph_update_meta_data('_ph_accesspoint_countrycode', $accesspoint_country);
			$ph_metadata_handler->ph_update_meta_data('_ph_accesspoint_postcode', $accesspoint_postcode);

			$order_shipping_accesspoint	=	substr($accesspoint_name . ', ' . $accesspoint_address . ', ' . $accesspoint_city . ', ' . $accesspoint_postcode, 0, 70);

			// Saving the selected accesspoint details in meta
			$ph_metadata_handler->ph_update_meta_data('_ph_selected_accesspoint_detail', $order_shipping_accesspoint);


			$selected_accesspoint_locator = isset($decoded_order_formatted_accesspoint->LocationID) ? $decoded_order_formatted_accesspoint->LocationID : '';
		}

		// Load Shipping Method Settings.
		$this->settings			= apply_filters('ph_ups_plugin_settings', $this->settings, $order);

		$response 				= null;
		$jsonRequest			= array();
		$json_option_code 		= array();

		$shipping_address 		= $order->get_shipping_address_1();
		$shipping_city 			= $order->get_shipping_city();
		$shipping_postalcode 	= $order->get_shipping_postcode();
		$shipping_state 		= $order->get_shipping_state();
		$shipping_country 		= $order->get_shipping_country();

		if (empty($shipping_country)) {

			return;
		}

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

		//@note Phone Number not found

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
							'Code'			 => $json_option_code,
						),
					),
					'MaximumListSize'  		 => $this->settings['accesspoint_max_limit'],
					'SearchRadius'			 => '50',
				),
			),
		);

		$jsonRequest 		= apply_filters('ph_ups_access_point_xml_request', $jsonRequest, $this->settings);
		$jsonRequest		= wp_json_encode(Ph_UPS_Woo_Shipping_Common::convert_array_values_to_strings($jsonRequest), JSON_UNESCAPED_SLASHES);
		$transient			= 'ph_ups_access_point' . md5($jsonRequest);
		$cached_response	= get_transient($transient);
		$response			= $cached_response;

		if (empty($cached_response)) {

			// Check for active plugin license
			if (Ph_UPS_Woo_Shipping_Common::phHasActiveLicense()) {
				$api_access_details	= Ph_Ups_Endpoint_Dispatcher::phGetApiAccessDetails();

				if (!$api_access_details) {
					return [];
				}

				$endpoint			= Ph_Ups_Endpoint_Dispatcher::ph_get_enpoint('access-point');

				$response = Ph_Ups_Api_Invoker::phCallApi(
					PH_UPS_Config::PH_UPS_PROXY_API_BASE_URL . $endpoint,
					$api_access_details['token'],
					$jsonRequest
				);

			} else {

				Ph_UPS_Woo_Shipping_Common::phAddDebugLog('Please use a valid plugin license to continue using WooCommerce UPS Shipping Plugin with Print Label', $this->debug);
				return [];
			}
			
			// Handle WP Error
			if (is_wp_error($response) && is_object($response) ) {
				$wp_error_message = 'Error Message : ' . $response->get_error_message();
			}

			if (!empty($wp_error_message)) {
				return array();
			}
		}
		
		if (!empty($cached_response)) {

			Ph_UPS_Woo_Shipping_Common::phAddDebugLog("-------------------- UPS Access Point Details #$order_id ---------------- Using Cached Response", $this->debug);
		}
		Ph_UPS_Woo_Shipping_Common::phAddDebugLog("-------------------- UPS Access Point Request #$order_id ----------------", $this->debug);
		Ph_UPS_Woo_Shipping_Common::phAddDebugLog($jsonRequest, $this->debug);
		Ph_UPS_Woo_Shipping_Common::phAddDebugLog("-------------------- UPS Access Point Response #$order_id ----------------", $this->debug);
		Ph_UPS_Woo_Shipping_Common::phAddDebugLog(!empty($wp_error_message) ? $wp_error_message : $response['body'], $this->debug);
		

		$locators 		= array();
		$drop_locations = array();

		$json = json_decode($response['body'], true);

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

			// Updating all the locator details in meta
			$ph_metadata_handler->ph_update_meta_data('_ph_accesspoint_location', $all_locators);
		}

		$locator = '<div class="edit_address form-field form-field-wide"><strong>UPS Access Point® Locator:</strong><select id="shipping_accesspoint" name="shipping_accesspoint" class="select">';
		$locator .=	"<option value=''>" . __('Select Access Point Location', 'ups-woocommerce-shipping') . "</option>";

		if (!empty($locators)) {

			foreach ($locators as $locator_id => $access_point_locator) {

				// Since XML orders have objects
				if (!empty($selected_accesspoint_locator) && is_object($selected_accesspoint_locator)) {
					$selected_accesspoint_locator = json_decode(json_encode($selected_accesspoint_locator), true);
				}

				$selected_accesspoint_locator = is_array($selected_accesspoint_locator) ? $selected_accesspoint_locator : array($selected_accesspoint_locator);

				if (!empty($selected_accesspoint_locator) && in_array($locator_id, $selected_accesspoint_locator)) {

					$locator .= "<option selected='selected' value='" . $locator_id . "'>" . __($access_point_locator, 'ups-woocommerce-shipping') . "</option>";
				} else {

					$locator .= "<option value='" . $locator_id . "'>" . __($access_point_locator, 'ups-woocommerce-shipping') . "</option>";
				}
			}
		}

		$locator .=	'</select></div>';
		$array['#shipping_accesspoint'] = $locator;

		$ph_metadata_handler->ph_save_meta_data();

		echo $array['#shipping_accesspoint'];
	}

	/**
	 * Get the shop address for shipping label.
	 *
	 * @param WC_Order $order The WooCommerce order object.
	 * @return array The shop address details.
	 */
	private function get_shop_address( $order ) {

		$shipper_phone_number 	= isset($this->settings['phone_number']) ? $this->settings['phone_number'] : '';
		$attention_name 		= isset($this->settings['ups_display_name']) ? preg_replace("/&#?[a-z0-9]+;/i", "", $this->settings['ups_display_name']) : '-';
		$company_name			= isset($this->settings['ups_user_name']) ? preg_replace("/&#?[a-z0-9]+;/i", "", $this->settings['ups_user_name']) : '-';

		//Address standard followed in all xadapter plugins. 
		$from_address = array(

			'name'		=> $attention_name,
			'company' 	=> $company_name,
			'phone' 	=> (strlen($shipper_phone_number) < 10) ? '0000000000' :  $shipper_phone_number,
			'email' 	=> $this->settings['email'],
			'address_1' => $this->settings['origin_addressline'],
			'address_2' => $this->settings['origin_addressline_2'],
			'city' 		=> $this->settings['origin_city'],
			'state' 	=> $this->settings['origin_state'],
			'country' 	=> $this->settings['origin_country'],
			'postcode' 	=> $this->settings['origin_postcode'],
		);

		//Filter for shipping common addon
		return apply_filters('wf_filter_label_from_address', $from_address, $this->wf_create_package($order));
	}

	private function get_order_address( $order ) {
		//Address standard followed in all xadapter plugins. 
		$billing_address 	= $order->get_address('billing');
		$shipping_address 	= $order->get_address('shipping');

		// Handle the address line one greater than 35 char(UPS Limit)
		$address_line_1_arr	= self::divide_sentence_based_on_char_length($shipping_address['address_1'], 35);
		$address_line_1 	= array_shift($address_line_1_arr);	// Address Line 1

		// Address Line 2
		if (!empty($address_line_1_arr)) {

			$address_line_2 = array_shift($address_line_1_arr);

			if (empty($address_line_1_arr)) {

				$address_line_2 = substr($address_line_2 . ' ' . $shipping_address['address_2'], 0, 35);
			}
		} else {

			$address_line_2 = substr($shipping_address['address_2'], 0, 35);
		}

		$phonenummeta 	= method_exists($order, 'get_shipping_phone') ? $order->get_shipping_phone() : '';
		$phonenum 		= !empty($phonenummeta) ? $phonenummeta : $billing_address['phone'];
		$phone_number 	= (strlen($phonenum) > 15) ? str_replace(' ', '', $phonenum) : $phonenum;

		return array(

			'name'		=> $shipping_address['first_name'] . ' ' . $shipping_address['last_name'],
			'company' 	=> !empty($shipping_address['company']) ? $shipping_address['company'] : '-',
			'phone' 	=> $phone_number,
			'email' 	=> htmlspecialchars($billing_address['email']),
			'address_1'	=> $address_line_1,
			'address_2'	=> $address_line_2,
			'city' 		=> $shipping_address['city'],
			'state' 	=> htmlspecialchars($shipping_address['state']),
			'country' 	=> $shipping_address['country'],
			'postcode' 	=> $shipping_address['postcode'],
		);
	}

	/**
	 * Get the String divided into multiple sentence based on Character Length of sentence.
	 * @param $string String String or Sentence on which the Divide has to be applied.
	 * @param $length Length for the new String.
	 * @return array Array of string or sentence of given length
	 */
	public static function divide_sentence_based_on_char_length($string, $length) {
		if (strlen($string) <= $length) {

			return array($string);
		} else {

			$words_instring = explode(' ', $string);
			$i = 0;
			foreach ($words_instring as $word) {
				$word = substr($word, 0, $length);			// To handle the word of length longer than given length
				if (!empty($new_string[$i])) {
					$new_length = strlen($new_string[$i] . ' ' . $word);
					if ($new_length <= $length) {
						$new_string[$i] .= ' ' . $word;
					} else {
						$new_string[++$i] = $word;
					}
				} else {
					$new_string[$i] = $word;
				}
			}
			return $new_string;
		}
	}

	private function get_billing_address($order) {

		$billing_address 	= $order->get_address('billing');

		return array(
			'name'		=> $billing_address['first_name'] . ' ' . $billing_address['last_name'],
			'company' 	=> !empty($billing_address['company']) ? $billing_address['company'] : '-',
			'phone' 	=> (strlen($billing_address['phone']) > 15) ? str_replace(' ', '', $billing_address['phone']) : $billing_address['phone'],
			'email' 	=> htmlspecialchars($billing_address['email']),
			'address_1'	=> $billing_address['address_1'],
			'address_2'	=> $billing_address['address_2'],
			'city' 		=> $billing_address['city'],
			'state' 	=> htmlspecialchars($billing_address['state']),
			'country' 	=> $billing_address['country'],
			'postcode' 	=> $billing_address['postcode'],
		);
	}

	/**
	 * GFP Request Builder
	 * 
	 * @param object $order PH Order Object
	 * @param array $shipment Shipment
	 * @return array Shipment Request
	 */
	function wf_ups_shipment_confirmrequest_GFP($order, $shipment = array(), $return_label = false) {
		global $post;

		$order_id 				= $order->get_id();
		$order_object			= wc_get_order($order->get_id());
		$ph_metadata_handler	= new PH_UPS_WC_Storage_Handler($order);
		$ups_settings 			= get_option('woocommerce_' . WF_UPS_ID . '_settings', null);

		// Apply filter on settings data
		$ups_settings	=	apply_filters('wf_ups_confirm_shipment_settings', $ups_settings, $order); //For previous version compatibility.
		$ups_settings	=	apply_filters('ph_ups_plugin_settings', $ups_settings, $order);

		$this->ship_from_address_different_from_shipper = !empty($ups_settings['ship_from_address_different_from_shipper']) ? $ups_settings['ship_from_address_different_from_shipper'] : 'no';

		// Define user set variables
		$ups_enabled					= isset($ups_settings['enabled']) ? $ups_settings['enabled'] : '';
		$ups_title						= isset($ups_settings['title']) ? $ups_settings['title'] : 'UPS';
		$ups_availability    			= isset($ups_settings['availability']) ? $ups_settings['availability'] : 'all';
		$ups_countries       			= isset($ups_settings['countries']) ? $ups_settings['countries'] : array();
		// WF: Print Label Settings.
		$print_label_type     			= isset($ups_settings['print_label_type']) ? $ups_settings['print_label_type'] : 'gif';
		$ship_from_address      		= isset($ups_settings['ship_from_address']) ? $ups_settings['ship_from_address'] : 'origin_address';

		$temp = PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, '_ph_ups_shipfrom_address_preference');

		if (isset($_GET['sfap']) && !empty($_GET['sfap'])) {

			$ship_from_address  = $_GET['sfap'];

			$ph_metadata_handler->ph_update_meta_data('_ph_ups_shipfrom_address_preference', $_GET['sfap']);
		} elseif (!empty($temp)) {

			$ship_from_address  = $temp;
		}

		$shipper_email	 				= isset($ups_settings['email']) ? $ups_settings['email'] : '';
		$ups_user_id         			= isset($ups_settings['user_id']) ? $ups_settings['user_id'] : '';
		$ups_password        			= isset($ups_settings['password']) ? $ups_settings['password'] : '';
		$ups_access_key      			= isset($ups_settings['access_key']) ? $ups_settings['access_key'] : '';
		$ups_shipper_number  			= isset($ups_settings['shipper_number']) ? $ups_settings['shipper_number'] : '';
		$ups_negotiated      			= isset($ups_settings['negotiated']) && $ups_settings['negotiated'] == 'yes' ? true : false;

		$this->accesspoint_locator 	= (isset($this->settings['accesspoint_locator']) && $this->settings['accesspoint_locator'] == 'yes') ? true : false;

		$cod						= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, '_wf_ups_cod');
		$sat_delivery				= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, '_wf_ups_sat_delivery');
		$order_total				= $order->get_total();
		$order_sub_total			= (float) is_object($order_object) ? $order_object->get_subtotal() : 0;
		$min_order_amount_for_insurance = !empty($ups_settings['min_order_amount_for_insurance']) ? $ups_settings['min_order_amount_for_insurance'] : 0;
		$order_currency				= $order->get_currency();

		$commercial_invoice		        = isset($ups_settings['commercial_invoice']) && $ups_settings['commercial_invoice'] == 'yes' ? true : false;

		$billing_address_preference = $this->get_product_address_preference($order, $ups_settings, false);

		if ('billing_address' == $ship_from_address && $billing_address_preference) {
			$from_address 	= $this->get_order_address($order_object);
			$to_address 	= $this->get_shop_address($order);
		} else {
			$from_address 	= $this->get_shop_address($order);
			$to_address 	= $this->get_order_address($order_object);
		}

		$shipping_service_data	= $this->wf_get_shipping_service_data($order);
		$shipping_method		= $shipping_service_data['shipping_method'];
		$shipping_service		= $shipping_service_data['shipping_service'];
		$shipping_service_name	= $shipping_service_data['shipping_service_name'];

		// Delivery confirmation available at package level only for domestic shipments.
		if (($from_address['country'] == $to_address['country']) && in_array($from_address['country'], PH_WC_UPS_Constants::DC_DOMESTIC_COUNTRIES)) {

			$ship_options['delivery_confirmation_applicable']	= true;
			$ship_options['international_delivery_confirmation_applicable']	= false;
		} else {

			$ship_options['international_delivery_confirmation_applicable']	= true;
		}

		$package_data = $this->wf_get_package_data($order, $ship_options, $to_address);

		if (empty($package_data)) {

			$stored_package = PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, '_wf_ups_stored_packages');

			if (!isset($stored_package[0]))
				$stored_package = array($stored_package);

			if (is_array($stored_package)) {

				$package_data = $stored_package;
			} else {

				return false;
			}
		}

		$package_data		=	apply_filters('wf_ups_filter_label_packages', $package_data, $order);

		$ph_metadata_handler->ph_update_meta_data('_wf_ups_stored_packages', $package_data);

		$shipment_requests	= '';
		$all_var 			= get_defined_vars();

		if (is_array($shipment)) {

			$contextvalue 	= apply_filters('ph_ups_update_customer_context_value', $order_id);
			$from_address 	= apply_filters('ph_ups_address_customization', $from_address, $shipment, $ship_from_address, $order->get_id(), 'from'); // Support for shipping multiple address
			$to_address 	= apply_filters('ph_ups_address_customization', $to_address, $shipment, $ship_from_address, $order->get_id(), 'to'); // Support for shipping multiple address
			$directdeliveryonlyindicator = null;

			$request_arr	=	array();

			// Taking Confirm Shipment Data Into Array for Better Processing and Filtering
			$request_arr = array();

			//@note <RequestAction>ShipConfirm</RequestAction> node not present.
			$request_arr = array(
				'ShipmentRequest'	=> array(
					'Request'		=> array(
						'RequestOption'		=>	'nonvalidate',
						'TransactionReference'	=> array(
							'CustomerContext'	=> $contextvalue,
						),
					)
				)
			);

			// Request for access point, not required for return label, confirmed by UPS
			// Access Point Addresses Are All Commercial So Overridding ResidentialAddress Condition
			if ($this->accesspoint_locator) {

				$access_point_node	=	$this->get_confirm_shipment_accesspoint_request($order);

				if (!empty($access_point_node)) {

					$this->residential			= false;
					$request_arr['ShipmentRequest']['Shipment'] 	= array_merge($access_point_node);
				}
			}

			$request_arr['ShipmentRequest']['Shipment']['Description']	= $this->wf_get_shipment_description($order, $shipment);

			if ($this->billing_address_as_shipper) {

				$billing_address 	= $order->get_address('billing');

				$shipper_address_1  = substr($billing_address['address_1'], 0, 34);
				$shipper_address_2  = substr($billing_address['address_2'], 0, 34);
				$shipper_address 	= empty($shipper_address_2) ? $shipper_address_1 : array($shipper_address_1, $shipper_address_2);

				$billing_as_shipper =  array(

					'name'		=> $billing_address['first_name'] . ' ' . $billing_address['last_name'],
					'company' 	=> !empty($billing_address['company']) ? $billing_address['company'] : '-',
					'phone' 	=> (strlen($billing_address['phone']) > 15) ? str_replace(' ', '', $billing_address['phone']) : $billing_address['phone'],
					'email' 	=> htmlspecialchars($billing_address['email']),
					'address'	=> $billing_address['address_1'] . ' ' . $billing_address['address_2'],
					'city' 		=> $billing_address['city'],
					'state' 	=> htmlspecialchars($billing_address['state']),
					'country' 	=> $billing_address['country'],
					'postcode' 	=> $billing_address['postcode'],
				);

				$request_arr['ShipmentRequest']['Shipment']['Shipper']	=	array(

					'Name'			=>	substr($billing_as_shipper['company'], 0, 34),
					'AttentionName'	=>	substr($billing_as_shipper['name'], 0, 34),
					'Phone'			=>	array(
						'Number'		=> preg_replace("/[^0-9]/", "", $billing_as_shipper['phone']),
					),
					'EMailAddress'	=>	$billing_as_shipper['email'],
					'ShipperNumber'	=>	$ups_shipper_number,
					'Address'		=>	array(
						'AddressLine'		=>	$shipper_address,
						'City'				=>	substr($billing_as_shipper['city'], 0, 29),
						'StateProvinceCode'	=>	strlen($billing_as_shipper['state']) < 6 ? $billing_as_shipper['state'] : '',
						'CountryCode'		=>	$billing_as_shipper['country'],
						'PostalCode'		=>	$billing_as_shipper['postcode'],
					),
				);

				$shipfrom_address_1  	= substr($from_address['address_1'], 0, 34);
				$shipfrom_address_2  	= substr($from_address['address_2'], 0, 34);
				$shipfrom_address 		= empty($shipfrom_address_2) ? $shipfrom_address_1 : array($shipfrom_address_1, $shipfrom_address_2);

				$request_arr['ShipmentRequest']['Shipment']['ShipFrom'] = array(

					'AttentionName'		=>	substr($from_address['name'], 0, 34),
					'Phone'				=>	array(
						'Number'		=> 	preg_replace("/[^0-9]/", "", $from_address['phone']),
					),
					'Name'				=>	substr($from_address['company'], 0, 34),
					'Address'			=>	array(
						'AddressLine'		=>	$shipfrom_address,
						'City'				=>	substr($from_address['city'], 0, 29),
						'StateProvinceCode'	=>	strlen($from_address['state']) < 6 ? $from_address['state'] : '',
						'CountryCode'		=>	$from_address['country'],
						'PostalCode'		=>	$from_address['postcode'],
					),
				);
			} else {

				$shipper_address_1  	= substr($from_address['address_1'], 0, 34);
				$shipper_address_2  	= substr($from_address['address_2'], 0, 34);
				$shipper_address 		= empty($shipper_address_2) ? $shipper_address_1 : array($shipper_address_1, $shipper_address_2);

				$request_arr['ShipmentRequest']['Shipment']['Shipper']	=	array(

					'Name'			=>	substr($from_address['company'], 0, 34),
					'AttentionName'	=>	substr($from_address['name'], 0, 34),
					'Phone'			=>	array(

						'Number'		=> preg_replace("/[^0-9]/", "", $from_address['phone']),
					),
					'EMailAddress'	=>	$from_address['email'],
					'ShipperNumber'	=>	$ups_shipper_number,
					'Address'		=>	array(

						'AddressLine'		=>	$shipper_address,
						'City'				=>	substr($from_address['city'], 0, 29),
						'StateProvinceCode'	=>	strlen($from_address['state']) < 6 ? $from_address['state'] : '',
						'CountryCode'		=>	$from_address['country'],
						'PostalCode'		=>	$from_address['postcode'],
					),
				);
			}

			if ('' == trim($to_address['company'])) {

				$to_address['company'] = '-';
			}

			$request_arr['ShipmentRequest']['Shipment']['ShipTo']	=	array(

				'Name'			=>	substr($to_address['company'], 0, 34),
				'AttentionName'	=>	substr($to_address['name'], 0, 34),
				'Phone'			=>	array(

					'Number'		=> preg_replace("/[^0-9]/", "", $to_address['phone']),
				),
				'EMailAddress'	=>	$to_address['email'],
				'Address'		=>	array(

					'AddressLine'		=>	substr($to_address['address_1'], 0, 34),
					'AddressLine2'		=>	substr($to_address['address_2'], 0, 34),
					'City'				=>	substr($to_address['city'], 0, 29),
					'CountryCode'		=>	$to_address['country'],
					'PostalCode'		=>	$to_address['postcode'],
				)
			);

			// State Code valid for certain countries only
			if (in_array($to_address['country'], PH_WC_UPS_Constants::COUNTRIES_WITH_STATECODES)) {

				$request_arr['ShipmentRequest']['Shipment']['ShipTo']['Address']['StateProvinceCode']	=	strlen($to_address['state']) < 6 ? $to_address['state'] : '';
			}

			// GFP Service Code
			$selected_shipment_service = '03';

			if ($this->remove_recipients_phno && !in_array($selected_shipment_service, PH_WC_UPS_Constants::PHONE_NUMBER_SERVICES) && $from_address['country'] == $to_address['country']) {

				if (isset($request_arr['ShipmentRequest']['Shipment']['ShipTo']['Phone']['Number'])) {

					unset($request_arr['ShipmentRequest']['Shipment']['ShipTo']['Phone']['Number']);
				}
			}

			// Negotiated Rates Flag
			if ($ups_negotiated) {

				$request_arr['ShipmentRequest']['Shipment']['ShipmentRatingOptions']['NegotiatedRatesIndicator']	=	'';
			}

			$request_arr['ShipmentRequest']['Shipment']['ShipmentRatingOptions']['FRSShipmentIndicator'] = '';
			$request_arr['ShipmentRequest']['Shipment']['FRSPaymentInformation']['Type']['Code'] = '01';
			$request_arr['ShipmentRequest']['Shipment']['FRSPaymentInformation']['AccountNumber'] = $ups_shipper_number;

			//@note Added ResidentialAddressIndicator as ResidentialAddress was not present
			if ($this->settings['residential']) {
				$request_arr['ShipmentRequest']['Shipment']['ShipTo']['Address']['ResidentialAddressIndicator'] = '';
			}

			$request_arr['ShipmentRequest']['Shipment']['Service']	=	array(
				'Code'			=>	"03",
				'Description'	=>	"GFP",
			);

			// Save service id, Required for pickup 
			$ph_metadata_handler->ph_update_meta_data('wf_ups_selected_service', $shipment['shipping_service']);

			foreach ($shipment['packages'] as $package) {

				if (isset($package['destination'])) {

					unset($package['destination']);
				}

				// Get direct delivery option from package to set in order level
				if (empty($directdeliveryonlyindicator) && !empty($package['Package']['DirectDeliveryOnlyIndicator'])) {

					$directdeliveryonlyindicator = $package['Package']['DirectDeliveryOnlyIndicator'];
				}

				// Unset DirectDeliveryOnlyIndicator, it is not applicable at package level
				if (isset($package['Package']['DirectDeliveryOnlyIndicator'])) {

					unset($package['Package']['DirectDeliveryOnlyIndicator']);
				}

				// Contains product which are being packed together
				$items_in_packages[] 	= isset($package['Package']['items']) ? $package['Package']['items'] : null;
				$product_data 			= array();

				if (isset($package['Package']['items'])) {

					foreach ($package['Package']['items'] as $item) {

						$product_id = $item->get_id();

						if (isset($product_data[$product_id])) {

							$product_data[$product_id] += 1;
						} else {

							$product_data[$product_id] = 1;
						}
					}
				}

				unset($package['Package']['items']);

				$package['Package']['Packaging']['Code'] 			= "02";
				$package['Package']['Commodity']['FreightClass'] 	= "50";

				if (!isset($package['Package']['Dimensions']) || !empty($package['Package']['Dimensions'])) {

					unset($package['Package']['Dimensions']);
				}

				//@note Removing 'PackagingType', 'BoxCode' and 'box_name' node 
				if (!empty($package['Package']['PackagingType']) || isset($package['Package']['BoxCode']) || isset($package['Package']['box_name'])) {
					unset($package['Package']['PackagingType']);
					unset($package['Package']['box_name']);
					unset($package['Package']['BoxCode']);
				}

				$request_arr['ShipmentRequest']['Shipment']['Package'][] = $package['Package'];
			}

			$shipmentServiceOptions = array();

			if ($this->ship_from_address_different_from_shipper == 'yes') {

				$different_ship_from_address = $this->get_ship_from_address($ups_settings);

				$shipfrom_address_1  	= substr($different_ship_from_address['Address']['AddressLine1'], 0, 34);
				$shipfrom_address_2  	= isset($different_ship_from_address['Address']['AddressLine2']) ? substr($from_address['address_2'], 0, 34) : '';
				$shipfrom_address 		= empty($shipfrom_address_2) ? $shipfrom_address_1 : array($shipfrom_address_1, $shipfrom_address_2);

				// Node differs in case of Ground With Freight
				$ship_from_different_address = array(

					'Name'			=> $different_ship_from_address['CompanyName'],
					'AttentionName'	=> $different_ship_from_address['AttentionName'],
					'Address'		=>	array(

						'AddressLine'	=>	$shipfrom_address,
						'City'			=>	$different_ship_from_address['Address']['City'],
						'PostalCode'	=>	$different_ship_from_address['Address']['PostalCode'],
						'CountryCode'	=>	$different_ship_from_address['Address']['CountryCode'],
					),
				);

				if (isset($different_ship_from_address['Address']['StateProvinceCode'])) {

					$ship_from_different_address['Address']['StateProvinceCode'] = strlen($different_ship_from_address['Address']['StateProvinceCode']) < 6 ? $different_ship_from_address['Address']['StateProvinceCode'] : '';
				}

				if (!empty($ship_from_address))	$request_arr['Shipment']['ShipFrom'] = $ship_from_different_address;
			}

			if ($sat_delivery) {

				$shipmentServiceOptions['SaturdayDelivery']	=	'';
			}

			if ($this->wcsups_rest->settings['cod']) {

				// Shipment Level COD
				if (PH_WC_UPS_Common_Utils::is_shipment_level_cod_required($to_address['country'])) {

					$codfundscode = in_array($to_address['country'], array('RU', 'AE')) ? 1 : $this->eu_country_cod_type;	// 1 for Cash, 9 for Cheque, 1 is available for all the countries
					$cod_value = $this->wcsups_rest->settings['cod_total'];


					//@note Removed it since 'CODCode' is not present in doc. Also check if value is correct
					$shipmentServiceOptions['COD']	=	array(
						'CODFundsCode'	=>	$codfundscode,
						'CODAmount'		=>	array(
							'CurrencyCode'	=>	$order_currency,
							'MonetaryValue'	=>	(string) $cod_value,
						),
					);
				}
			}

			if ($this->tin_number) {

				$request_arr['ShipmentRequest']['Shipment']['Shipper']['TaxIdentificationNumber'] = $this->tin_number;
			}

			if ((!empty($this->email_notification)) && ((isset($this->email_notification_code)) && (array_intersect($this->email_notification_code, array('6', '7', '8'))))) {

				$emails = array();

				foreach ($this->email_notification as $notifier) {

					switch ($notifier) {

						case 'recipient':
							array_push($emails, array('EMailAddress' => $order->get_billing_email()));
							break;

						case 'sender':
							array_push($emails, array('EMailAddress' => $shipper_email));
							break;
					}
				}

				if (!empty($emails)) {

					$email_notification_code = array_intersect($this->email_notification_code, array('6', '7', '8'));
					$email_address 			 = $emails;


					foreach ($email_notification_code as $code) {

						$notification[] = array(

							'NotificationCode' 	=> $code,
							'EMail' 			=> array(

								'EMailAddress' 				=> $email_address,
								'UndeliverableEMailAddress' => $shipper_email,
								'FromEMailAddress' 			=> $shipper_email
							),
						);
					}

					if (isset($request_arr['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['Notification'])) {
						$request_arr['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['Notification'] = array_merge($request_arr['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['Notification'], $notification);
					} else {
						$shipmentServiceOptions['Notification'] = array_merge($notification);
					}
				}
			}

			// Set Direct delivery in the actual request
			if (!empty($directdeliveryonlyindicator)) {

				$shipmentServiceOptions['DirectDeliveryOnlyIndicator'] = $directdeliveryonlyindicator;
			}

			if (sizeof($shipmentServiceOptions)) {
				$request_arr['ShipmentRequest']['Shipment']['ShipmentServiceOptions']	=	empty($request_arr['ShipmentRequest']['Shipment']['ShipmentServiceOptions']) ? $shipmentServiceOptions : array_merge($request_arr['ShipmentRequest']['Shipment']['ShipmentServiceOptions'], $shipmentServiceOptions);
			}

			$this->density_unit 	= $this->dim_unit;
			$this->density_length 	= $this->density_length;
			$this->density_width 	= $this->density_width;
			$this->density_height 	= $this->density_height;

			if ($this->density_length == 0) {

				$this->density_length = ($this->density_unit == 'IN') ? 10 : 26;
			}

			if ($this->density_width == 0) {

				$this->density_width = ($this->density_unit == 'IN') ? 10 : 26;
			}

			if ($this->density_height == 0) {

				$this->density_height = ($this->density_unit == 'IN') ? 10 : 26;
			}

			if ($this->enable_density_based_rating) {

				$request_arr['ShipmentRequest']['Shipment']['FreightShipmentInformation'] = array(

					'FreightDensityInfo' => array(

						'HandlingUnits' => array(

							'Quantity' => 1,
							'Type' => array(

								'Code' => 'PLT',
								'Description' => 'Density'
							),
							'Dimensions' => array(

								'UnitOfMeasurement' => array(

									'Code'	=> $this->density_unit,
									'Description' => "Dimension unit",
								),
								'Length' => $this->density_length,
								'Width' => $this->density_width,
								'Height' => $this->density_height
							)
						),
					),
					'DensityEligibleIndicator' => 1,
				);
			}

			//@note Commented the below line as the node 'LabelPrintMethod' is not present.
			// $request_arr['ShipmentRequest']['LabelSpecification']['LabelPrintMethod']	= $this->get_code_from_label_type($print_label_type);

			$request_arr['ShipmentRequest']['LabelSpecification']['HTTPUserAgent']		= 'Mozilla/4.5';

			if ('zpl' == $print_label_type || 'epl' == $print_label_type || 'png' == $print_label_type) {

				$request_arr['ShipmentRequest']['LabelSpecification']['LabelStockSize']	=	array('Height' => '6', 'Width' => '4');
			}

			$request_arr['ShipmentRequest']['LabelSpecification']['LabelImageFormat']	=	$this->get_code_from_label_type($print_label_type);

			$request_arr	=	apply_filters('wf_ups_shipment_confirm_request_data', $request_arr, $order);

			$shipment_requests 	=	apply_filters('wf_ups_shipment_confirm_request', $request_arr, $order, $shipment, $return_label);

			// Converting all values in jsonRequest_arr array to String
			$convert_values_to_string = Ph_UPS_Woo_Shipping_Common::convert_array_values_to_strings($shipment_requests);
			$encode_jsonRequest = wp_json_encode($convert_values_to_string, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
		}

		$ph_metadata_handler->ph_save_meta_data();

		return $shipment_requests;
	}

	function wf_ups_shipment_confirmrequest($order, $return_label = false) {

		global $post;

		$is_not_forward 		= true;
		$order_id 				= $order->get_id();
		$order_object			= wc_get_order($order_id);
		$ph_metadata_handler	= new PH_UPS_WC_Storage_Handler($order_object);
		$label_type 			= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, '_ph_ups_shipment_label_type');
		$shipment_terms   		= '';

		if (!empty($label_type) && $label_type == 'forward') {

			//For Display Purpose while using PH UPS Return Label Type addon 
			$ph_metadata_handler->ph_update_meta_data('_ph_ups_shipment_label_type_as_forward', $label_type);
			$is_not_forward = false;
		}

		// Apply filter on settings data
		$this->settings	= apply_filters('wf_ups_confirm_shipment_settings', $this->settings, $order_object); // For previous version compatibility.
		$this->settings	= apply_filters('ph_ups_plugin_settings', $this->settings, $order_object);

		$temp = PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, '_ph_ups_shipfrom_address_preference');

		if (isset($_GET['sfap']) && !empty($_GET['sfap'])) {

			$this->settings['ship_from_address']  = $_GET['sfap'];

			$ph_metadata_handler->ph_update_meta_data('_ph_ups_shipfrom_address_preference', $_GET['sfap']);
		} elseif (!empty($temp)) {

			$this->settings['ship_from_address']  = $temp;
		}

		$this->is_hazmat_product 	= false;

		$cod						= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, '_wf_ups_cod');
		$sat_delivery				= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, '_wf_ups_sat_delivery');
		$order_total				= $order_object->get_total();
		$order_sub_total			= (float) is_object($order_object) ? $order_object->get_subtotal() : 0;
		$order_currency				= $order_object->get_currency();
		
		$import_control  			= 'false';

		// For bulk pickup to work with both origin and shipping address
		$ph_metadata_handler->ph_update_meta_data('ph_ship_from_address', $this->settings['ship_from_address']);

		if (isset($_GET['ic'])) {

			// UPS Import Control Indicator
			$import_control = isset($_GET['ic']) ? $_GET['ic'] : '';
		} else if (isset($import_control_settings) && $import_control_settings) {

			$import_control = 'true';
		}

		$recipients_tin 		= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, 'ph_ups_shipping_tax_id_num');
		$shipto_recipients_tin 	= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, 'ph_ups_ship_to_tax_id_num');

		// Array to pass options like return label on the fly
		$ship_options = array('return_label' => $return_label);

		$billing_address_preference = $this->get_product_address_preference($order_object, $this->settings, $return_label);

		if ('billing_address' == $this->settings['ship_from_address'] && $billing_address_preference) {

			$from_address 	= $this->get_order_address($order_object);
			$to_address 	= $this->get_shop_address($order_object);
		} else {

			$from_address 	= $this->get_shop_address($order_object);
			$to_address 	= $this->get_order_address($order_object);
		}

		if (isset($_GET['ShipmentTerms'])) {

			$shipment_terms = isset($_GET['ShipmentTerms']) ? $_GET['ShipmentTerms'] : '';
		} elseif (!empty($this->settings['terms_of_shipment'])) {

			$shipment_terms = apply_filters('ph_ups_shipment_terms', $this->settings['terms_of_shipment'], $to_address, $order_object);
		} else {

			// Else condition is to handle when shipment terms under plugin settings is set as None and automatic label generation is enabled
			// If UPS Shipment Terms addon is enabled shipment term from the matched rule will be considered
			$shipment_terms = apply_filters('ph_ups_shipment_terms', $this->settings['terms_of_shipment'], $to_address, $order_object);
		}

		if ($this->settings['address_validation'] && in_array($to_address['country'], array('US', 'PR'))) {

			if (!class_exists('Ph_Ups_Address_Validation')) {

				require_once 'class-ph-ups-rest-address-validation.php';
			}

			if ($return_label) {

				$Ph_Ups_Address_Validation_Rest 	= new Ph_Ups_Address_Validation_Rest($from_address, $this->settings);
				$residential_code					= $Ph_Ups_Address_Validation_Rest->residential_check;
			} else {

				$Ph_Ups_Address_Validation_Rest 	= new Ph_Ups_Address_Validation_Rest($to_address, $this->settings);
				$residential_code					= $Ph_Ups_Address_Validation_Rest->residential_check;
			}

			if ( 2 == $residential_code) {

				$this->settings['residential'] = true;
			}
		}

		$shipping_service_data	= $this->wf_get_shipping_service_data($order_object);
		$shipping_method		= $shipping_service_data['shipping_method'];
		$shipping_service		= $shipping_service_data['shipping_service'];
		$shipping_service_name	= $shipping_service_data['shipping_service_name'];

		// Delivery confirmation available at package level only for domestic shipments.
		if (($from_address['country'] == $to_address['country']) && in_array($from_address['country'], PH_WC_UPS_Constants::DC_DOMESTIC_COUNTRIES)) {

			$ship_options['delivery_confirmation_applicable']	= true;
			$ship_options['international_delivery_confirmation_applicable']	= false;
		} else {

			$ship_options['international_delivery_confirmation_applicable']	= true;
		}

		$package_data = array();

		if (!$return_label) {

			$package_data = $this->wf_get_package_data($order_object, $ship_options, $to_address);
		}

		if (empty($package_data)) {

			$stored_package = PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, '_wf_ups_stored_packages');

			if (!isset($stored_package[0])) {

				$stored_package = array($stored_package);
			}

			if (is_array($stored_package)) {

				$package_data = $stored_package;
			} else {

				return false;
			}
		}

		$package_data		=	apply_filters('wf_ups_filter_label_packages', $package_data, $order_object);

		$ph_metadata_handler->ph_update_meta_data('_wf_ups_stored_packages', $package_data);

		$shipments          =   $this->split_shipment_by_services($package_data, $order_object, $return_label);

		// Filter to break shipments further, with other business logics, like multi vendor ,Support for shipping multiple address
		$shipments			=	apply_filters('wf_ups_shipment_data', $shipments, $order_object);

		$shipment_requests	= array();
		$all_var 			= get_defined_vars();

		if (is_array($shipments)) {

			$service_index	= 0;
			$str 			= isset($_GET['service']) ? str_replace('\"', '', str_replace(']', '', str_replace('[', '', $_GET['service']))) : '';

			// $str will be empty for Automatic Label Generation 
			if (empty($str) && isset($this->auto_label_generation) && $this->auto_label_generation) {

				// auto_label_services will have the service value from order or from default service setting
				$svc_code 	= $this->auto_label_services;
			} else {
				$svc_code 	= explode(',', $str);
			}

			$include_return	= isset($_GET['rt_service']) ? str_replace('\"', '', str_replace(']', '', str_replace('[', '', $_GET['rt_service']))) : '';

			if (!empty($include_return)) {

				$return_svc_code 		= explode(',', $include_return);
			}

			foreach ($shipments as $shipment) {
				// Support for shipping multiple address
				$from_address = apply_filters('ph_ups_address_customization', $from_address, $shipment, $this->settings['ship_from_address'], $order_id, 'from');
				$to_address = apply_filters('ph_ups_address_customization', $to_address, $shipment, $this->settings['ship_from_address'], $order_id, 'to');

				$directdeliveryonlyindicator 	= null;
				$alcoholicbeveragesindicator	= 'no';
				$diagnosticspecimensindicator	= 'no';
				$perishablesindicator			= 'no';
				$plantsindicator				= 'no';
				$seedsindicator					= 'no';
				$specialexceptionsindicator		= 'no';
				$tobaccoindicator				= 'no';
				$this->is_hazmat_product 	    = false;

				$shipping_service = $svc_code[$service_index];

				if (in_array($shipping_service, array_keys(PH_WC_UPS_Constants::FREIGHT_SERVICES)) || in_array($shipment['shipping_service'], array_keys(PH_WC_UPS_Constants::FREIGHT_SERVICES))) {

					// Freight services are not supported with REST API
					WC_Admin_Meta_Boxes::add_error(sprintf(__('Order #(%s) is skipped from label generation - LTL Frieght services are not supported', 'ups-woocommerce-shipping'), $order_id));

				} else if ($shipment['shipping_service'] == 'US48') {

					$shipment_requests[] = array(

						"service" => 'GFP',
						"request" => $this->wf_ups_shipment_confirmrequest_GFP($order_object, $shipment, $return_label)
					);
				} else {

					$contextvalue 	= apply_filters('ph_ups_update_customer_context_value', $order_id);

					$jsonRequest_arr 	=   array();

					//@note RequestAction is missing in doc

					// Forming JSON Request
					$jsonRequest_arr = array(
						'ShipmentRequest'	=> array(
							'Request'		=> array(
								'TransactionReference'	=>	array(
									'CustomerContext'	=>	strval($contextvalue),
								),
								'RequestOption'			=> 'nonvalidate',
							)
						)
					);

					// Request for access point, not required for return label, confirmed by UPS
					// Access Point Addresses Are All Commercial So Overridding ResidentialAddress Condition
					if ( $this->settings['accesspoint_locator'] && (!$is_not_forward || !$return_label)) {

						$json_access_point_node	=	$this->get_confirm_shipment_accesspoint_request($order_object);

						if (!empty($json_access_point_node)) {

							$this->settings['residential']	=	false;
							$jsonRequest_arr['ShipmentRequest']['Shipment'] = array_merge($json_access_point_node);
						}
					}

					$jsonRequest_arr['ShipmentRequest']['Shipment']['Description']	= $this->wf_get_shipment_description($order_object, $shipment);

					if ($return_label) {
						$jsonRequest_arr['ShipmentRequest']['Shipment']['ReturnService']	= array('Code'	=>	9);
					}

					// ReferenceNumber Valid if the origin/destination pair is not US/US or PR/PR
					if ($from_address['country'] != $to_address['country'] || !in_array($from_address['country'], array('US', 'PR'))) {

						if ($this->settings['order_id_or_number_in_label'] == 'include_order_number') {

							// Add a third argument to just return the Order Number without any additional text
							$order_id_or_number	= Ph_UPS_Woo_Shipping_Common::getOrderIdOrNumber($order_object, 'include_order_number', true);
						} else {

							// Add a third argument to just return the Order Id without any additional text
							$order_id_or_number	= Ph_UPS_Woo_Shipping_Common::getOrderIdOrNumber($order_object, 'include_order_id', true);
						}

						$jsonRequest_arr['ShipmentRequest']['Shipment']['ReferenceNumber']	=	array(
							'Code'	=>	'PO',
							'Value'	=>	$order_id_or_number,
						);
					}

					$mrn_number = PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, '_ph_ups_export_compliance');

					if (!empty($mrn_number)) {

						if (isset($mrn_number)) {
							$jsonRequest_arr['ShipmentRequest']['Shipment']['MovementReferenceNumber'] = $mrn_number;
						}
					}

					if (in_array($from_address['country'], array('US')) &&  in_array($to_address['country'], array('PR', 'CA'))) {

						if ($order_total < 1) {
							$order_total = 1;
						}

						$jsonRequest_arr['ShipmentRequest']['Shipment']['InvoiceLineTotal']['CurrencyCode'] = $order_currency;
						$jsonRequest_arr['ShipmentRequest']['Shipment']['InvoiceLineTotal']['MonetaryValue'] = (int)$order_total;

						// For Return Shipment check condition in reverse order
					} else if (($is_not_forward && $return_label) && in_array($to_address['country'], array('US')) &&  in_array($from_address['country'], array('PR', 'CA'))) {

						if ($order_total < 1) {
							$order_total = 1;
						}

						$jsonRequest_arr['ShipmentRequest']['Shipment']['InvoiceLineTotal']['CurrencyCode'] = $order_currency;
						$jsonRequest_arr['ShipmentRequest']['Shipment']['InvoiceLineTotal']['MonetaryValue'] = (int)$order_total;
					}

					$is_gfp_shipment	= isset($_GET['is_gfp_shipment']) ? $_GET['is_gfp_shipment'] : '';

					if ($is_gfp_shipment == 'true') {
						$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipmentRatingOptions']['FRSShipmentIndicator'] = '';
						$jsonRequest_arr['ShipmentRequest']['Shipment']['FRSPaymentInformation']['Type']['Code'] = '01';
						$jsonRequest_arr['ShipmentRequest']['Shipment']['FRSPaymentInformation']['AccountNumber'] = $this->settings['shipper_number'];
					}

					if ($this->settings['billing_address_as_shipper'] && (!$is_not_forward || !$return_label)) {

						$billing_address 	= $order_object->get_address('billing');

						$billing_as_shipper =  array(
							'name'		=> $billing_address['first_name'] . ' ' . $billing_address['last_name'],
							'company' 	=> !empty($billing_address['company']) ? $billing_address['company'] : '-',
							'phone' 	=> (strlen($billing_address['phone']) > 15) ? str_replace(' ', '', $billing_address['phone']) : $billing_address['phone'],
							'email' 	=> htmlspecialchars($billing_address['email']),
							'address'	=> $billing_address['address_1'],
							'city' 		=> $billing_address['city'],
							'state' 	=> htmlspecialchars($billing_address['state']),
							'country' 	=> $billing_address['country'],
							'postcode' 	=> $billing_address['postcode'],
						);

						// JSON Request
						$jsonRequest_arr['ShipmentRequest']['Shipment']['Shipper']	=	array(
							'Name'			=>	substr($billing_as_shipper['company'], 0, 34),
							'AttentionName'	=>	substr($billing_as_shipper['name'], 0, 34),
							'Phone'			=>	array(
								'Number'	=>	preg_replace("/[^0-9]/", "", $billing_as_shipper['phone']),
							),
							'EMailAddress'	=>	$billing_as_shipper['email'],
							'ShipperNumber'	=>	$this->settings['shipper_number'],
							'Address'		=>	array(
								'AddressLine'		=>	array(
									substr($billing_as_shipper['address'], 0, 34),
								),
								'City'				=>	substr($billing_as_shipper['city'], 0, 29),
								'StateProvinceCode'	=>	strlen($billing_as_shipper['state']) < 6 ? $billing_as_shipper['state'] : '',
								'CountryCode'		=>	$billing_as_shipper['country'],
								'PostalCode'		=>	$billing_as_shipper['postcode'],
							),
						);

						if (!empty($billing_address['address_2'])) {
							$jsonRequest_arr['ShipmentRequest']['Shipment']['Shipper']['Address']['AddressLine'][1] = substr($billing_address['address_2'], 0, 34);
						}

						// JSON Request
						$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipFrom'] = array(
							'AttentionName'		=>	substr($from_address['name'], 0, 34),
							'Name'				=>	substr($from_address['company'], 0, 34),
							'Phone'				=>	array(
								'Number'		=>	preg_replace("/[^0-9]/", "", $from_address['phone']),
							),
							'Address'			=>	array(
								'AddressLine'		=>	array(
									substr($from_address['address_1'], 0, 34),
								),
								'City'				=>	substr($from_address['city'], 0, 29),
								'StateProvinceCode'	=>	strlen($from_address['state']) < 6 ? $from_address['state'] : '',
								'CountryCode'		=>	$from_address['country'],
								'PostalCode'		=>	$from_address['postcode'],
							),
							'TaxIdentificationNumber'	=> $this->settings['tin_number'],
						);

						if (!empty($from_address['address_2'])) {
							$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipFrom']['Address']['AddressLine'][1] = substr($from_address['address_2'], 0, 34);
						}
					} else {

						// JSON Request
						$jsonRequest_arr['ShipmentRequest']['Shipment']['Shipper']	=	array(
							'Name'			=>	substr($from_address['company'], 0, 34),
							'AttentionName'	=>	substr($from_address['name'], 0, 34),
							'Phone'			=>	array(
								'Number'	=>	preg_replace("/[^0-9]/", "", $from_address['phone']),
							),
							'EMailAddress'	=>	$from_address['email'],
							'ShipperNumber'	=>	$this->settings['shipper_number'],
							'Address'		=>	array(
								'AddressLine'		=>	array(
									substr($from_address['address_1'], 0, 34),
								),
								'City'				=>	substr($from_address['city'], 0, 29),
								'StateProvinceCode'	=>	strlen($from_address['state']) < 6 ? $from_address['state'] : '',
								'CountryCode'		=>	$from_address['country'],
								'PostalCode'		=>	$from_address['postcode'],
							),
						);

						if (!empty($from_address['address_2'])) {
							$jsonRequest_arr['ShipmentRequest']['Shipment']['Shipper']['Address']['AddressLine'][1] = substr($from_address['address_2'], 0, 34);
						}
					}

					if ($this->settings['eei_data'] && (!$is_not_forward || !$return_label)) {

						// JSON Request
						$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipFrom'] = array(
							'AttentionName'		=>	substr($from_address['name'], 0, 34),
							'Phone'				=>	array(
								'Number'		=>	preg_replace("/[^0-9]/", "", $from_address['phone']),
							),
							'Name'				=>	substr($from_address['company'], 0, 34),
							'Address'			=>	array(
								'AddressLine'		=>	array(
									substr($from_address['address_1'], 0, 34),
								),
								'City'				=>	substr($from_address['city'], 0, 29),
								'StateProvinceCode'	=>	strlen($from_address['state']) < 6 ? $from_address['state'] : '',
								'CountryCode'		=>	$from_address['country'],
								'PostalCode'		=>	$from_address['postcode'],
							),
							'TaxIdentificationNumber'	=> $this->settings['tin_number'],
							'TaxIDType'					=>	array(
								'Code'	=>	'EIN',
							),
						);

						if (!empty($from_address['address_2'])) {
							$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipFrom']['Address']['AddressLine'][1] = substr($from_address['address_2'], 0, 34);
						}
					}

					if ($this->settings['vendorInfo'] && (isset($_GET['vci']) && isset($_GET['ct'])) && (!$is_not_forward || !$return_label)) {

						$ph_metadata_handler->ph_update_meta_data('_ph_ups_vcid_number', $_GET['vci']);
						$ph_metadata_handler->ph_update_meta_data('_ph_ups_vcid_consignee', $_GET['ct']);

						$vendorCollectIDTypeCode = '';

						if (isset($to_address['country']) && in_array($to_address['country'], PH_WC_UPS_Constants::EU_ARRAY) && $to_address['country'] != 'GB') {

							// IOSS (Import One Stop Shop) Number is used when shipping to EU destinations or Northern Ireland.
							$vendorCollectIDTypeCode = '0356';
						} elseif (isset($to_address['country']) && $to_address['country'] == 'GB') {

							// HMRC (Her Majesty’s Revenue and Customs) Number is used when shipping to the United Kingdom.
							$vendorCollectIDTypeCode = '0358';
						} elseif (isset($to_address['country']) && $to_address['country'] == 'NO') {

							// VOEC (VAT On E-Commerce) Number is used when shipping to Norway.
							$vendorCollectIDTypeCode = '0357';
						}

						// ShipFrom is there in request no need to add again. 
						if (!isset($jsonRequest_arr['ShipmentRequest']['Shipment']['ShipFrom']) || empty($jsonRequest_arr['ShipmentRequest']['Shipment']['ShipFrom'])) {

							// JSON Request
							$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipFrom'] = array(
								'AttentionName'		=>	substr($from_address['name'], 0, 34),
								'Phone'				=>	array(
									'Number'		=>	preg_replace("/[^0-9]/", "", $from_address['phone']),
								),
								'Name'				=>	substr($from_address['company'], 0, 34),
								'Address'			=>	array(
									'AddressLine'		=>	array(
										substr($from_address['address_1'], 0, 34),
									),
									'City'				=>	substr($from_address['city'], 0, 29),
									'StateProvinceCode'	=>	strlen($from_address['state']) < 6 ? $from_address['state'] : '',
									'CountryCode'		=>	$from_address['country'],
									'PostalCode'		=>	$from_address['postcode'],
								),
							);
						}

						$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipFrom']['VendorInfo'] = array(
							'VendorCollectIDTypeCode' => $vendorCollectIDTypeCode,
							'VendorCollectIDNumber'   => isset($_GET['vci']) && !empty($_GET['vci']) ? $_GET['vci'] : '',
							'ConsigneeType' 		  => isset($_GET['ct']) && !empty($_GET['ct']) ? $_GET['ct'] : 'NA',
						);

						if (!empty($from_address['address_2'])) {
							$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipFrom']['Address']['AddressLine'][1] = substr($from_address['address_2'], 0, 34);
						}
					}

					if ($return_label) {

						// JSONRequest
						$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipTo']	=	array(
							'Name'			=>	substr($from_address['company'], 0, 34),
							'AttentionName'	=>	substr($from_address['name'], 0, 34),
							'Phone'	=>	array(
								'Number'	=>	preg_replace("/[^0-9]/", "", $from_address['phone']),
							),
							'EMailAddress'	=>	$from_address['email'],
						);
						$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipTo']['Address'] = $this->get_ship_to_address_in_return_label($this->settings, $from_address);

						if ($this->settings['tin_number']) {
							$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipTo']['TaxIdentificationNumber'] = $this->settings['tin_number'];
						}

						//@note ResidentialAddress not found in doc

						if ($this->settings['residential']) {
							$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipTo']['Address']['ResidentialAddressIndicator'] = '';
						}
					} else {

						if ('' == trim($to_address['company'])) {
							$to_address['company'] = '-';
						}

						$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipTo']	=	array(
							'Name'			=>	substr($to_address['company'], 0, 34),
							'AttentionName'	=>	substr($to_address['name'], 0, 34),
							'Phone'			=>	array(
								'Number'	=>	preg_replace("/[^0-9]/", "", $to_address['phone']),
							),
							'EMailAddress'	=>	$to_address['email'],
							'Address'		=>	array(
								'AddressLine'		=>	array(
									substr($to_address['address_1'], 0, 34),
								),
								'City'				=>	substr($to_address['city'], 0, 29),
								'CountryCode'		=>	$to_address['country'],
								'PostalCode'		=>	$to_address['postcode'],
							)
						);

						if (!empty(substr($to_address['address_2'], 0, 34))) {
							$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipTo']['Address']['AddressLine'][1] = substr($to_address['address_2'], 0, 34);
						}

						// State Code valid for certain countries only
						if (in_array($to_address['country'], PH_WC_UPS_Constants::COUNTRIES_WITH_STATECODES)) {
							$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipTo']['Address']['StateProvinceCode']	=	strlen($to_address['state']) < 6 ? $to_address['state'] : '';
						}

						$selected_shipment_service = $this->get_service_code_for_country($shipment['shipping_service'], $from_address['country']);

						if ($this->settings['remove_recipients_phno'] && !in_array($selected_shipment_service, PH_WC_UPS_Constants::PHONE_NUMBER_SERVICES) && $from_address['country'] == $to_address['country']) {

							if (isset($jsonRequest_arr['ShipmentRequest']['Shipment']['ShipTo']['Phone']['Number'])) {
								unset($jsonRequest_arr['ShipmentRequest']['Shipment']['ShipTo']['Phone']['Number']);
							}
						}

						if ($this->settings['recipients_tin']) {
							$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipTo']['TaxIdentificationNumber'] = $shipto_recipients_tin;
						}

						//@note ResidentialAddress not found in doc

						if ($this->settings['residential']) {
							$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipTo']['Address']['ResidentialAddressIndicator'] = '';
						}
					}

					if ($return_label) {

						if (!empty($_GET['return_label_service'])) {

							$jsonRequest_arr['ShipmentRequest']['Shipment']['Service']	=	array(
								'Code'			=>	$_GET['return_label_service'],
								'Description'	=>	PH_WC_UPS_Constants::UPS_SERVICE_CODES['US'][$_GET['return_label_service']],
							);
						} else {

							$jsonRequest_arr['ShipmentRequest']['Shipment']['Service']	=	array(
								'Code'			=>	$return_svc_code[$service_index],
								'Description'	=>	PH_WC_UPS_Constants::UPS_SERVICE_CODES['US'][$return_svc_code[$service_index]],
							);
						}
					} else {

						$jsonRequest_arr['ShipmentRequest']['Shipment']['Service']	=	array(
							'Code'			=>	$this->get_service_code_for_country($shipment['shipping_service'], $from_address['country']),
							'Description'	=> ($this->get_service_code_for_country($shipment['shipping_service'], $from_address['country']) == 96) ? 'WorldWide Express Freight' : $shipping_service_name,
						);
					}

					// Save service id, Required for pickup
					$ph_metadata_handler->ph_update_meta_data('wf_ups_selected_service', $shipment['shipping_service']);

					$paymentinformation = array();

					$modified_ups_shipper_number = apply_filters('ph_replace_carrier_account_number', $this->settings['shipper_number'], $order_id);

					if (!empty($modified_ups_shipper_number) && $modified_ups_shipper_number != $this->settings['shipper_number']) {

						$billing_address 	= $order->get_address('billing');

						$paymentinformation[]	= array(

							'Type'			=>	'01',
							'BillReceiver' 	=> array(

								'AccountNumber' => $modified_ups_shipper_number,
								'Address'		=> array(

									'PostalCode'	=> $billing_address['postcode']
								),
							),

						);
					} else if ($this->settings['transportation'] == 'shipper') {

						$paymentinformation[]	= array(

							'Type'			=>	'01',
							'BillShipper' 	=> array(

								'AccountNumber' => $this->settings['shipper_number'],
							),

						);
					} else if ($this->settings['transportation'] == 'third_party') {

						$paymentinformation[]	= array(

							'Type'				=>	'01',
							'BillThirdParty' 	=> array(

								'AccountNumber' => $this->settings['transport_payor_acc_no'],
								'Address'       => array(

									'PostalCode'  => $this->settings['transport_payor_post_code'],
									'CountryCode' => $this->settings['transport_payor_country_code'],
								),
							),

						);
					}

					$custom_and_duties = array(
						'type' 			=> $this->settings['customandduties'],
						'post_code' 	=> $this->settings['customandduties_pcode'],
						'account_num' 	=> $this->settings['customandduties_ac_num'],
						'country_code' 	=> $this->settings['customandduties_ccode']
					);

					// When UPS Shipment Terms addon is enabled Duties And Taxes Payer from the matched rule will be considered
					$custom_and_duties = apply_filters('ph_ups_custom_and_duties', $custom_and_duties, $to_address, $order);

					if ($custom_and_duties['type'] == 'shipper') {

						Ph_UPS_Woo_Shipping_Common::phAddDebugLog('UPS: Duties And Taxes Paid by Shipper', $this->debug);

						$paymentinformation[]	= array(

							'Type'		  =>	'02',
							'BillShipper' => 	array(

								'AccountNumber' => $this->settings['shipper_number'],
							),
						);
					} else if ($custom_and_duties['type'] == 'third_party') {

						Ph_UPS_Woo_Shipping_Common::phAddDebugLog('UPS: Duties And Taxes Paid by Third Party', $this->debug);

						$paymentinformation[]	= array(
							
							'Type'			=>	'02',
							'BillThirdParty' 		=> array(

								'AccountNumber' 	=> $custom_and_duties['account_num'],
								'Address' 			=> array(

									'PostalCode'  	=> $custom_and_duties['post_code'],
									'CountryCode' 	=> $custom_and_duties['country_code'],
								),
							),
						);
					} else {

						Ph_UPS_Woo_Shipping_Common::phAddDebugLog('UPS: Duties And Taxes Paid by Receiver', $this->debug);
					}

					$jsonRequest_arr['ShipmentRequest']['Shipment']['PaymentInformation']['ShipmentCharge']	=	$paymentinformation;

					if (isset($paymentinformation)) {
						unset($paymentinformation);
					}

					// For check at Global
					$add_global_restricted_article = false;

					$numofpieces = 0;	//For Worldwide Express Freight Service
					$hazmat_package_id = 0;

					foreach ($shipment['packages'] as $package_key => $package) {

						// Add Simple Rate node if item is packed in Simple Rate Box
						if (isset($package['Package']['BoxCode'])) {

							$boxCode = $package['Package']['BoxCode'];

							if (array_key_exists($boxCode, PH_WC_UPS_Constants::SIMPLE_RATE_BOX_CODES)) {
								$package['Package']['SimpleRate']['Code'] = PH_WC_UPS_Constants::SIMPLE_RATE_BOX_CODES[$boxCode];
							}

							// Unset BoxCode once its passed as SimpleRate Code
							unset($package['Package']['BoxCode']);
						}

						if (isset($package['destination'])) {

							unset($package['destination']);
						}

						// @note InsuredValue node not present. Only DeclaredValue is there.

						// InsuredValue should not send with Sure post
						if (
							($this->wf_is_surepost($shipment['shipping_service']) || $this->settings['min_order_amount_for_insurance'] > $order_sub_total)
							&& isset($package['Package']['PackageServiceOptions'])
							&& isset($package['Package']['PackageServiceOptions']['InsuredValue'])
						) {
							unset($package['Package']['PackageServiceOptions']['InsuredValue']);
						}

						// InsuredValue in REST becomes DeclaredValue
						if ( isset($package['Package']['PackageServiceOptions']['InsuredValue']) ) {

							$package['Package']['PackageServiceOptions']['DeclaredValue'] = $package['Package']['PackageServiceOptions']['InsuredValue'];

							unset($package['Package']['PackageServiceOptions']['InsuredValue']);
						}

						// To Set Delivery Confirmation at shipment level for international shipment
						if ($ship_options['international_delivery_confirmation_applicable'] && (!$is_not_forward || !$return_label)) {

							if (isset($package['Package']['items'])) {

								$shipment_delivery_confirmation = PH_WC_UPS_Common_Utils::get_package_signature($package['Package']['items']);
								$shipment_delivery_confirmation = $shipment_delivery_confirmation < $this->settings['ph_delivery_confirmation'] ? $this->settings['ph_delivery_confirmation'] : $shipment_delivery_confirmation;

								if (isset($_GET['dc'])) {

									$ph_metadata_handler->ph_update_meta_data('_ph_ups_delivery_signature', $_GET['dc']);

									$shipment_delivery_confirmation = $_GET['dc'] != 4 ? $_GET['dc'] : $shipment_delivery_confirmation;
								}

								$delivery_confirmation = (isset($delivery_confirmation) && $delivery_confirmation >= $shipment_delivery_confirmation) ? $delivery_confirmation : $shipment_delivery_confirmation;
							}
						}

						// Get direct delivery option from package to set in order level
						if (empty($directdeliveryonlyindicator) && !empty($package['Package']['DirectDeliveryOnlyIndicator'])) {

							$directdeliveryonlyindicator = $package['Package']['DirectDeliveryOnlyIndicator'];
						}

						// Unset DirectDeliveryOnlyIndicator, it is not applicable at package level
						if (isset($package['Package']['DirectDeliveryOnlyIndicator'])) {

							unset($package['Package']['DirectDeliveryOnlyIndicator']);
						}

						//For Worldwide Express Freight Service
						if ($shipment['shipping_service'] == 96) {

							$package['Package']['PackagingType']['Code'] = 30;

							if (isset($package['Package']['items'])) {

								$numofpieces    += count($package['Package']['items']);
							}
						}

						// Reseting box for return label
						// '25KG Box' and '10KG Box' will not support for return label
						if ( $return_label && isset($package['Package']['PackagingType']['Code']) && in_array($package['Package']['PackagingType']['Code'],array(24, 25)) ) {

							$package['Package']['PackagingType']['Code'] = 2;
						}


						// Remove Delivery Confirmation for Return Label as UPS doesn't support it
						if (($is_not_forward && $return_label) && isset($package['Package']['PackageServiceOptions']) && isset($package['Package']['PackageServiceOptions']['DeliveryConfirmation'])) {

							unset($package['Package']['PackageServiceOptions']['DeliveryConfirmation']);
						}

						// Remove COD for Return Label as UPS doesn't support it
						if (($is_not_forward && $return_label) && isset($package['Package']['PackageServiceOptions']) && isset($package['Package']['PackageServiceOptions']['COD'])) {

							unset($package['Package']['PackageServiceOptions']['COD']);
						}

						if (in_array($shipment['shipping_service'], array('M4', 'M5', 'M6'))) {

							$package_description = array(
								'57'	=> 'Parcels',
								'62'	=> 'Irregulars',
								'63'	=> 'Parcel Post',
								'64'	=> 'BPM Parcel',
								'65'	=> 'Media Mail',
								'66'	=> 'BPM Flat',
								'67'	=> 'Standard FLat',
							);

							// For International Shipment only supported Packaging Type - 57
							if ($shipment['shipping_service'] == 'M5' || $shipment['shipping_service'] == 'M6') {

								$this->settings['mail_innovation_type'] 	= '57';
							}

							$package['Package']['PackagingType']['Code'] 		= $this->settings['mail_innovation_type'];
							$package['Package']['PackagingType']['Description'] = $package_description[$this->settings['mail_innovation_type']];

							if ($this->settings['mail_innovation_type'] == '62' || $this->settings['mail_innovation_type'] == '67' && isset($package['Package']['PackageWeight'])) {

								$weight 	= $package['Package']['PackageWeight']['Weight'];

								if ($this->settings['weight_unit'] == 'LBS') {
									// From LBS to ounces
									$weight	=	$weight * 16;
								} else {
									// From KGS to ounces
									$weight	=	$weight * 35.274;
								}

								$package['Package']['PackageWeight']['Weight']				= (string) round($weight, 2);
								$package['Package']['PackageWeight']['UnitOfMeasurement']	= array('Code' => 'OZS');
							}

							if (isset($package['Package']['PackageServiceOptions'])) {

								unset($package['Package']['PackageServiceOptions']);
							}
						}

						//Package level Label description for US to US shipments
						if ($this->settings['add_product_sku'] || ($from_address['country'] == $to_address['country'] && in_array($from_address['country'], array('US', 'PR')))) {

							$description_value = $this->wf_get_shipment_description($order, $package, true);

							$description_value = (strlen($description_value) >= 35) ? substr($description_value, 0, 32) . '...' : $description_value;

							if (isset($package['Package'])) {

								$package['Package']['ReferenceNumber'] 	= array();
								$package['Package']['ReferenceNumber']	= array(
									'Code'	=>	'01',
									'Value'	=>	$description_value,
								);
							}
						}

						// Contains product which are being packed together
						$items_in_packages[] = isset($package['Package']['items']) ? $package['Package']['items'] : null;

						$product_data = array();

						if (isset($package['Package']['items'])) {

							foreach ($package['Package']['items'] as $item) {

								$product_id = 0;

								if (is_object($item)) {

									$product_id = $item->get_id();

									if (!empty($product_id)) {

										$product_data[$product_id]['parent_id'] = (WC()->version > '2.7') ? $item->get_parent_id() : (isset($item->parent->id) ? $item->parent->id : 0);

										if (isset($product_data[$product_id]['quantity'])) {

											$product_data[$product_id]['quantity'] += 1;
										} else {

											$product_data[$product_id]['quantity'] = 1;
										}

										$product_data[$product_id]['weight'] = $item->get_weight();
									}
								}
							}
						}

						foreach ($product_data as $product_id => $product_detail) {

							$hazmat_product 	= 'no';
							$restricted_product = 'no';

							$hazmat_product 	= get_post_meta($product_id, '_ph_ups_hazardous_materials', 1);
							$hazmat_settings 	= get_post_meta($product_id, '_ph_ups_hazardous_settings', 1);

							if ($hazmat_product != 'yes' && !empty($product_detail['parent_id'])) {

								$hazmat_product 	= get_post_meta($product_detail['parent_id'], '_ph_ups_hazardous_materials', 1);
								$hazmat_settings 	= get_post_meta($product_detail['parent_id'], '_ph_ups_hazardous_settings', 1);
							}

							if (!empty($product_detail['parent_id'])) {

								$restricted_product = get_post_meta($product_detail['parent_id'], '_ph_ups_restricted_article', 1);
								$restrictedarticle 	= get_post_meta($product_detail['parent_id'], '_ph_ups_restricted_settings', 1);
							}

							if ($restricted_product != 'yes') {

								$restricted_product = get_post_meta($product_id, '_ph_ups_restricted_article', 1);
								$restrictedarticle 	= get_post_meta($product_id, '_ph_ups_restricted_settings', 1);
							}

							$transportationmode = array(
								'01' => 'Highway',
								'02' => 'Ground',
								'03' => 'PAX',
								'04' => 'CAO',
							);

							if ($this->settings['isc'] && isset($restrictedarticle) && !empty($restrictedarticle)  && ($restricted_product == 'yes')) {

								$alcoholicbeveragesindicator 	= ($alcoholicbeveragesindicator == 'yes') ? $alcoholicbeveragesindicator : $restrictedarticle['_ph_ups_alcoholic'];
								$diagnosticspecimensindicator 	= ($diagnosticspecimensindicator == 'yes') ? $diagnosticspecimensindicator : $restrictedarticle['_ph_ups_diog'];
								$perishablesindicator 			= ($perishablesindicator == 'yes') ? $perishablesindicator : $restrictedarticle['_ph_ups_perishable'];
								$plantsindicator 				= ($plantsindicator == 'yes') ? $plantsindicator : $restrictedarticle['_ph_ups_plantsindicator'];
								$seedsindicator 				= ($seedsindicator == 'yes') ? $seedsindicator : $restrictedarticle['_ph_ups_seedsindicator'];
								$specialexceptionsindicator 	= ($specialexceptionsindicator == 'yes') ? $specialexceptionsindicator : $restrictedarticle['_ph_ups_specialindicator'];
								$tobaccoindicator 				= ($tobaccoindicator == 'yes') ? $tobaccoindicator : $restrictedarticle['_ph_ups_tobaccoindicator'];
							}

							if (empty($restricted_product) || $restricted_product == 'no') {

								$add_global_restricted_article = true;
							}

							if ($hazmat_product == 'yes') {

								$this->is_hazmat_product = true;

								if (array_key_exists($hazmat_settings['_ph_ups_hm_transportaion_mode'], $transportationmode)) {

									$mode = $transportationmode[$hazmat_settings['_ph_ups_hm_transportaion_mode']];
								}

								$req['ChemicalRecordIdentifier'] = !empty($hazmat_settings['_ph_ups_record_number']) ? $hazmat_settings['_ph_ups_record_number'] : ' ';
								$req['ClassDivisionNumber'] = !empty($hazmat_settings['_ph_ups_class_division_no']) ? $hazmat_settings['_ph_ups_class_division_no'] : ' ';
								$req['IDNumber'] = !empty($hazmat_settings['_ph_ups_commodity_id']) ? $hazmat_settings['_ph_ups_commodity_id'] : ' ';
								$req['TransportationMode'] = $mode;
								$req['RegulationSet'] = $hazmat_settings['_ph_ups_hm_regulations'];
								$req['PackagingGroupType'] = !empty($hazmat_settings['_ph_ups_package_group_type']) ? $hazmat_settings['_ph_ups_package_group_type'] : ' ';
								$req['Quantity'] = round($product_detail['weight'], 1);
								$req['UOM'] = ($this->settings['uom'] == 'LB') ? 'pound' : 'kg';
								$req['ProperShippingName'] = !empty($hazmat_settings['_ph_ups_shipping_name']) ? $hazmat_settings['_ph_ups_shipping_name'] : ' ';
								$req['PackagingInstructionCode'] = !empty($hazmat_settings['_ph_ups_package_instruction_code']) ? $hazmat_settings['_ph_ups_package_instruction_code'] : '';
								$req['TechnicalName'] = !empty($hazmat_settings['_ph_ups_technical_name']) ? $hazmat_settings['_ph_ups_technical_name'] : ' ';
								$req['AdditionalDescription'] = !empty($hazmat_settings['_ph_ups_additional_description']) ? $hazmat_settings['_ph_ups_additional_description'] : ' ';
								$req['PackagingType'] = !empty($hazmat_settings['_ph_ups_package_type']) ? $hazmat_settings['_ph_ups_package_type'] : ' ';
								$req['PackagingTypeQuantity'] = $product_detail['quantity'];
								$req['CommodityRegulatedLevelCode'] = $hazmat_settings['_ph_ups_hm_commodity'];
								$req['EmergencyPhone'] = $this->settings['phone_number'];
								$req['EmergencyContact'] = $this->settings['ups_display_name'];

								$ph_already_added_hazmat = false;

								if ( isset($new_req_arr) && is_array($new_req_arr) ) {

									foreach($new_req_arr['HazMat'] as $key => $value ) {

										if ( $req['RegulationSet'] == $value['RegulationSet'] && $req['IDNumber'] == $value['IDNumber'] && $req['PackagingType'] == $value['PackagingType'] ) {

											$total_qty = (($new_req_arr['HazMat'][$key]['Quantity'] * $new_req_arr['HazMat'][$key]['PackagingTypeQuantity']) + ($req['Quantity'] * $req['PackagingTypeQuantity']));

											$new_req_arr['HazMat'][$key]['Quantity'] = $total_qty;

											$new_req_arr['HazMat'][$key]['PackagingTypeQuantity'] = 1;
											$ph_already_added_hazmat = true;
											break;
										}
									}
								}

								if ( !$ph_already_added_hazmat ) {

									$new_req_arr['HazMat'][] = $req;
								}
							}
						}

						if (isset($package['Package']['items'])) {
							unset($package['Package']['items']);
						}

						if ($this->is_hazmat_product && isset($new_req_arr)) {

							$hazmat_package_id += 1;
							$hazmat_array['Package']['PackageServiceOptions']	= $new_req_arr;
							$hazmat_array['Package']['PackageServiceOptions']['PackageIdentifier'] = $hazmat_package_id;
							$package = array_merge_recursive($package, $hazmat_array);

							unset($new_req_arr);
						}

						// Converting weight and dimensions based on Vendor origin address
						if (isset($package['Package']['metrics'])) {

							if ($package['Package']['metrics']) {

								if (isset($package['Package']['Dimensions']) && !empty($package['Package']['Dimensions']) && $package['Package']['Dimensions']['UnitOfMeasurement']['Code'] != 'CM') {

									$package['Package']['Dimensions']['UnitOfMeasurement']['Code'] = 'CM';
									$this->settings['dim_unit'] = 'CM';
									$package['Package']['Dimensions']['Length'] = round(wc_get_dimension($package['Package']['Dimensions']['Length'], 'CM', 'in'), 2);
									$package['Package']['Dimensions']['Width']	= round(wc_get_dimension($package['Package']['Dimensions']['Width'], 'CM', 'in'), 2);
									$package['Package']['Dimensions']['Height']	= round(wc_get_dimension($package['Package']['Dimensions']['Height'], 'CM', 'in'), 2);
								}

								if ($package['Package']['PackageWeight']['UnitOfMeasurement']['Code'] != 'KGS') {

									$package['Package']['PackageWeight']['UnitOfMeasurement']['Code']	= 'KGS';
									$this->settings['weight_unit'] = 'KGS';
									$package['Package']['PackageWeight']['Weight']	= round(wc_get_weight($package['Package']['PackageWeight']['Weight'], 'KGS', 'lbs'), 2);
								}
							} else {

								if (isset($package['Package']['Dimensions']) && !empty($package['Package']['Dimensions']) && $package['Package']['Dimensions']['UnitOfMeasurement']['Code'] != 'IN' && $this->settings['units'] == 'metric') {

									$package['Package']['Dimensions']['UnitOfMeasurement']['Code'] = 'IN';
									$this->settings['dim_unit'] = 'IN';
									$package['Package']['Dimensions']['Length'] = round(wc_get_dimension($package['Package']['Dimensions']['Length'], 'IN', 'cm'), 2);
									$package['Package']['Dimensions']['Width']	= round(wc_get_dimension($package['Package']['Dimensions']['Width'], 'IN', 'cm'), 2);
									$package['Package']['Dimensions']['Height']	= round(wc_get_dimension($package['Package']['Dimensions']['Height'], 'IN', 'cm'), 2);
								}

								if ($package['Package']['PackageWeight']['UnitOfMeasurement']['Code'] != 'LBS' && $this->settings['units'] == 'metric') {

									$package['Package']['PackageWeight']['UnitOfMeasurement']['Code']	= 'LBS';
									$this->settings['weight_unit'] = 'LBS';
									$package['Package']['PackageWeight']['Weight']	= round(wc_get_weight($package['Package']['PackageWeight']['Weight'], 'LBS', 'kg'), 2);
								}
							}

							// Unset metrics
							unset($package['Package']['metrics']);
						}

						//@note Changing 'PackagingType' to 'Packaging' node and removing 'box_name' node 
						if (!empty($package['Package']['PackagingType']) || isset($package['Package']['box_name'])) {
							$package['Package']['Packaging'] = $package['Package']['PackagingType'];
							unset($package['Package']['PackagingType']);
							unset($package['Package']['box_name']);
						}

						$jsonRequest_arr['ShipmentRequest']['Shipment']['Package'][] = apply_filters('ph_ups_shipment_confirm_packages', $package['Package'], $shipment['packages'][$package_key], $order_id);
					}

					if ($this->settings['shipper_release_indicator'] && (!$is_not_forward || !$return_label) && isset($jsonRequest_arr['ShipmentRequest']['Shipment']['Package']) && is_array($jsonRequest_arr['ShipmentRequest']['Shipment']['Package'])) {

						$shipper_release_indicator 	= false;
						$shipper_release_countries 	= array('US', 'PR');

						if (in_array($from_address['country'], $shipper_release_countries) && in_array($to_address['country'], $shipper_release_countries) && $from_address['country'] == $to_address['country']) {

							foreach ($jsonRequest_arr['ShipmentRequest']['Shipment']['Package'] as $key => $package_items) {
								if (
									!empty($package_items) &&
									is_array($package_items) &&
									isset($package_items['PackageServiceOptions']) &&
									(isset($package_items['PackageServiceOptions']['DeliveryConfirmation']) || isset($package_items['PackageServiceOptions']['COD']))
								) {

									if ($this->debug) {

										if (isset($package_items['PackageServiceOptions']['COD'])) {

											Ph_UPS_Woo_Shipping_Common::phAddDebugLog('UPS: Shipper Release Indicator is not added because it is COD Shipment', $this->debug);
										} else if (isset($package_items['PackageServiceOptions']['DeliveryConfirmation'])) {

											Ph_UPS_Woo_Shipping_Common::phAddDebugLog('UPS: Shipper Release Indicator is not added because Signature required for this Package', $this->debug);
										}
									}

									$shipper_release_indicator 	= false;
								} else {
									$shipper_release_indicator 	= true;
								}

								if ($shipper_release_indicator && isset($package_items) && !empty($package_items) && is_array($package_items)) {
									$jsonRequest_arr['ShipmentRequest']['Shipment']['Package'][$key]['PackageServiceOptions']['ShipperReleaseIndicator'] = '';
								}
							}
						}
					}

					$json_shipmentServiceOptions = array();

					// Set delivery confirmation at shipment level for international shipment
					// UPS doesn't support Delivery Confirmation for Return Label
					if (isset($delivery_confirmation) &&  !empty($delivery_confirmation) && (!$is_not_forward || !$return_label)) {

						if (!isset($_GET['dc']) || $_GET['dc'] == 4) {

							$signature_required = $delivery_confirmation;
						} else {
		
							$signature_required = $_GET['dc'];
						}

						$signature_required = $signature_required == 3 ? 2 : ($signature_required > 0 ? 1: '');

						
						if ( !empty($signature_required) && !(in_array($from_address['country'], array('US', 'PR')) && in_array($to_address['country'], array('US', 'PR'))) && !($from_address['country'] == $to_address['country'] && $from_address['country'] == 'CA') ) {
							
							$json_shipmentServiceOptions['DeliveryConfirmation']['DCISType'] = $signature_required;
						}
					}

					if ($this->settings['isc']) {

						if ($add_global_restricted_article && $this->settings['ph_restricted_article']) {

							$alcoholicbeveragesindicator 	= ($alcoholicbeveragesindicator == 'yes') ? $alcoholicbeveragesindicator : $this->settings['ph_alcoholic'];
							$diagnosticspecimensindicator 	= ($diagnosticspecimensindicator == 'yes') ? $diagnosticspecimensindicator : $this->settings['ph_diog'];
							$perishablesindicator 			= ($perishablesindicator == 'yes') ? $perishablesindicator : $this->settings['ph_perishable'];
							$plantsindicator 				= ($plantsindicator == 'yes') ? $plantsindicator : $this->settings['ph_plantsindicator'];
							$seedsindicator 				= ($seedsindicator == 'yes') ? $seedsindicator : $this->settings['ph_seedsindicator'];
							$specialexceptionsindicator 	= ($specialexceptionsindicator == 'yes') ? $specialexceptionsindicator : $this->settings['ph_specialindicator'];
							$tobaccoindicator 				= ($tobaccoindicator == 'yes') ? $tobaccoindicator : $this->settings['ph_tobaccoindicator'];
						}

						if ($alcoholicbeveragesindicator == 'yes') {

							$json_shipmentServiceOptions['RestrictedArticles']['AlcoholicBeveragesIndicator'] = 1;
						}

						if ($diagnosticspecimensindicator == 'yes') {

							$json_shipmentServiceOptions['RestrictedArticles']['DiagnosticSpecimensIndicator'] = 1;
						}

						if ($perishablesindicator == 'yes') {

							$json_shipmentServiceOptions['RestrictedArticles']['PerishablesIndicator'] = 1;
						}

						if ($plantsindicator == 'yes') {

							$json_shipmentServiceOptions['RestrictedArticles']['PlantsIndicator'] = 1;
						}

						if ($seedsindicator == 'yes') {

							$json_shipmentServiceOptions['RestrictedArticles']['SeedsIndicator'] = 1;
						}

						if ($specialexceptionsindicator == 'yes') {

							$json_shipmentServiceOptions['RestrictedArticles']['SpecialExceptionsIndicator'] = 1;
						}

						if ($tobaccoindicator == 'yes') {

							$json_shipmentServiceOptions['RestrictedArticles']['TobaccoIndicator'] = 1;
						}
					}

					//For Worldwide Express Freight Service
					if ($shipment['shipping_service'] == 96) {
						$jsonRequest_arr['ShipmentRequest']['Shipment']['NumOfPiecesInShipment'] = $numofpieces;
					}

					// Negotiated Rates Flag
					if ($this->settings['negotiated']) {
						$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipmentRatingOptions']['NegotiatedRatesIndicator']	= '';
					}

					// For return label, Ship From address will be set as Shipping Address of order.
					if ($return_label) {

						$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipFrom']	=	array(

							'Name'			=>	substr($to_address['company'], 0, 34),
							'AttentionName'	=>	substr($to_address['name'], 0, 34),
							'Address'		=>	array(
								'AddressLine'	=> 	array(
									substr($to_address['address_1'], 0, 34),
								),
								'City'			=>	substr($to_address['city'], 0, 29),
								'PostalCode'	=>	$to_address['postcode'],
								'CountryCode'	=>	$to_address['country'],
							),
						);

						// State Code valid for certain countries only
						if (in_array($to_address['country'], PH_WC_UPS_Constants::COUNTRIES_WITH_STATECODES)) {
							$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipFrom']['Address']['StateProvinceCode']	=	strlen($to_address['state']) < 6 ? $to_address['state'] : '';
						}

						if ($this->settings['recipients_tin']) {
							$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipFrom']['TaxIdentificationNumber'] = $shipto_recipients_tin;
						}

						if (!empty($to_address['address_2'])) {
							$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipFrom']['Address']['AddressLine'][1]	=	substr($to_address['address_2'], 0, 34);
						}
					} else {

						if ($this->settings['ship_from_address_different_from_shipper']) {

							$different_ship_from_address = $this->get_ship_from_address($this->settings);

							if (!empty( $this->settings['ship_from_address'] )) {
								$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipFrom'] = $different_ship_from_address;
							}
						}
					}

					//@note 'SaturdayDelivery' not found

					if ($sat_delivery) {
						$json_shipmentServiceOptions['SaturdayDeliveryIndicator'] = '';
					}

					// PDS-79
					// Import control is used to support ship from out side country
					if ($ship_options['international_delivery_confirmation_applicable'] && $import_control == 'true') {

						$json_shipmentServiceOptions['ImportControlIndicator'] =	'';
						$json_shipmentServiceOptions['LabelMethod']	= array(
							'Code' => '05',
						);
					}

					//Its a UPS service supporting nature
					if ($this->settings['carbonneutral_indicator']) {
						$json_shipmentServiceOptions['UPScarbonneutralIndicator'] =	'';
					}

					//PDS-129
					if ($this->is_hazmat_product) {

						$jsonRequest_arr['ShipmentRequest']['Shipment']['DGSignatoryInfo']  = array(
							'Name' => $from_address['name'],
							'Title' => $from_address['company'],
							'Place' => $from_address['city'],
							'Date' => date('Ymd'),
						);
					}

					if (in_array($jsonRequest_arr['ShipmentRequest']['Shipment']['Service']['Code'], array('M6', 'M5', 'M4'))) {

						// For International Shipment only supported USPS Endorsement Value - 5 (No Service)
						if ($shipment['shipping_service'] == 'M5' || $shipment['shipping_service'] == 'M6') {
							$this->settings['usps_endorsement'] = '5';
						}

						$jsonRequest_arr['ShipmentRequest']['Shipment']['USPSEndorsement'] = $this->settings['usps_endorsement'];
						$jsonRequest_arr['ShipmentRequest']['Shipment']['PackageID'] = $order->get_id();

						if (isset($jsonRequest_arr['ShipmentRequest']['Shipment']['ReferenceNumber'])) {
							unset($jsonRequest_arr['ShipmentRequest']['Shipment']['ReferenceNumber']);
						}
					}

					// Commercial Invoice is available only for international shipments
					if ($this->settings['commercial_invoice'] && ($from_address['country'] != $to_address['country']) && !(in_array($from_address['country'], PH_WC_UPS_Constants::EU_ARRAY) && in_array($to_address['country'], PH_WC_UPS_Constants::EU_ARRAY))) {

						if ($is_not_forward && $return_label) {

							if (array_key_exists($from_address['country'], PH_WC_UPS_Constants::SATELLITE_COUNTRIES)) {

								$soldToCountry 	= PH_WC_UPS_Constants::SATELLITE_COUNTRIES[$from_address['country']];
							} else {
								$soldToCountry 	= $from_address['country'];
							}

							$soldToPhone	=	(strlen($from_address['phone']) < 10) ? '0000000000' : $from_address['phone'];
							$company_name	=	substr($from_address['company'], 0, 34);

							$json_sold_to_arr	=	array(
								'Name'	=>	!empty($company_name) ? $company_name : '-',
								'AttentionName'	=>	substr($from_address['name'], 0, 34),
								'Phone'			=>	array(
									'Number'	=>	preg_replace("/[^0-9]/", "", $from_address['phone']),
								),
								'Address'		=>	array(
									'AddressLine'		=>	array(
										substr($from_address['address_1'], 0, 34),
									),
									'City'				=>	substr($from_address['city'], 0, 29),
									'CountryCode'		=>	$soldToCountry,
									'PostalCode'		=>	preg_replace("/[^A-Z0-9]/", "", $from_address['postcode']),
								),
							);

							if (!empty($from_address['state'])) {
								$json_sold_to_arr['Address']['StateProvinceCode'] = strlen($from_address['state']) < 6 ? $from_address['state'] : '';
							}

							if ($this->settings['tin_number']) {
								$json_sold_to_arr['TaxIdentificationNumber'] = $this->settings['tin_number'];
							}

							if (!empty($from_address['address_2'])) {
								$json_sold_to_arr['Address']['AddressLine'][1] = substr($from_address['address_2'], 0, 34);
							}
						} else {

							// Shipping Address will be considered as the Sold To address if shippingAddressAsSoldTo is enabled else Sold To address should be Billing Address
							$shippingAsSoldToIsEnabled = (isset($_GET['soldTo']) && ($_GET['soldTo'] == 'true')) ? true : false;
							$billing_address = $shippingAsSoldToIsEnabled ? $to_address : $this->get_billing_address($order);

							$billing_address = apply_filters('ph_ups_modify_sold_to_address', $billing_address, $to_address['country']);

							$ph_metadata_handler->ph_update_meta_data('_ph_ups_shipping_address_as_sold_to', $shippingAsSoldToIsEnabled);

							$soldToPhone	= (strlen($billing_address['phone']) < 10) ? '0000000000' : $billing_address['phone'];
							$company_name	= substr($billing_address['company'], 0, 34);

							if (array_key_exists($billing_address['country'], PH_WC_UPS_Constants::SATELLITE_COUNTRIES)) {

								$soldToCountry 	= PH_WC_UPS_Constants::SATELLITE_COUNTRIES[$billing_address['country']];
							} else {
								$soldToCountry 	= $billing_address['country'];
							}

							$json_sold_to_arr	=	array(
								'Name'	=>	!empty($company_name) ? $company_name : '-',
								'AttentionName'	=>	substr($billing_address['name'], 0, 34),
								'Phone'			=>	array(
									'Number'	=>	preg_replace("/[^0-9]/", "", $billing_address['phone']),
								),
								'Address'		=>	array(
									'AddressLine'	=>	array(
										substr($billing_address['address_1'], 0, 34),
									),
									'City'			=>	substr($billing_address['city'], 0, 29),
									'CountryCode'	=>	$soldToCountry,
									'PostalCode'	=>	preg_replace("/[^A-Z0-9]/", "", $billing_address['postcode']),
								),
							);

							// State Code valid for certain countries only
							if (in_array($billing_address['country'], PH_WC_UPS_Constants::COUNTRIES_WITH_STATECODES)) {
								$json_sold_to_arr['Address']['StateProvinceCode']	=	strlen($billing_address['state']) < 6 ? $billing_address['state'] : '';
							}

							if ($this->settings['recipients_tin']) {
								$json_sold_to_arr['TaxIdentificationNumber'] = $recipients_tin;
							}

							if (!empty($billing_address['address_2'])) {
								$json_sold_to_arr['Address']['AddressLine'][1] = substr($billing_address['address_2'], 0, 34);
							}
						}

						//@note SoldTo not found outside InternationalForms line in ( $request_arr['Shipment']['SoldTo'] 	= $sold_to_arr; )
						//@note	So added them to InternationalForms where the node is actually present.

						$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['InternationalForms']['Contacts']['SoldTo'] = $json_sold_to_arr;
						$invoice_products					= array();
						$total_item_cost 					= 0;

						if (!empty($order_object) && is_a($order_object, 'WC_Order')) {

							// To support Mix and Match Product
							do_action('ph_ups_before_get_items_from_order', $order_object);

							$order_items = $order_object->get_items();

							if (!empty($order_items)) {

								foreach ($order_items as  $item_key => $item_values) {

									$orderItemId 		= $item_values->get_id();
									$refundedItemCount	= $order_object->get_qty_refunded_for_item($orderItemId);

									$orderItemQty 		= $item_values->get_quantity() + $refundedItemCount;

									if ($orderItemQty <= 0) {

										continue;
									}

									$total_item_cost += $item_values->get_total();
								}
							}

							// To support Mix and Match Product
							do_action('ph_ups_after_get_items_from_order', $order_object);

							$eei_base_currency 		= "USD";
							$wc_conversion_rate 	= get_option('woocommerce_multicurrency_rates');

							if ($order_currency != $eei_base_currency && !empty($wc_conversion_rate) && is_array($wc_conversion_rate)) {

								$eei_currency_rate 		= $wc_conversion_rate[$eei_base_currency];
								$order_currency_rate 	= $wc_conversion_rate[$order_currency];

								$conversion_rate 		= $eei_currency_rate / $order_currency_rate;
								$total_item_cost 		*= $conversion_rate;
							}
						}

						$ship_from_country 		= $from_address['country'];
						$ship_to_country 		= $to_address['country'];

						$invoice_products 		= $this->get_ups_invoice_products($shipment, $from_address['country'], $order_object, $return_label, $ship_to_country, $total_item_cost);

						// Support for shipping multiple address
						$invoice_products = apply_filters('ph_ups_shipment_confirm_request_customize_product_details', $invoice_products, $shipment, $this->settings['weight_unit'], $from_address['country']);

						$billing_address = $this->get_billing_address($order_object);

						$billing_address = apply_filters('ph_ups_modify_sold_to_address', $billing_address, $ship_to_country);

						$billing_address = apply_filters('ph_ups_address_customization', $billing_address, $shipment, $this->settings['ship_from_address'], $order_id, 'to');

						$json_shipmentServiceOptions['InternationalForms']	=	array(

							'InvoiceNumber'			=>	'',
							'InvoiceDate'			=>	date("Ymd"),
							'PurchaseOrderNumber'	=>	$order->get_order_number(),
							'Contacts'				=>	array(
								'SoldTo'	=>	array(
									'Name'						=>	substr($billing_address['company'], 0, 34),
									'AttentionName'				=>	substr($billing_address['name'], 0, 34),
									'TaxIdentificationNumber'	=>	$recipients_tin,
									'Phone'						=>	array(
										'Number'	=>	$soldToPhone,
									),
									'Address'					=>	array(
										'AddressLine'	=>	substr($billing_address['address_1'] . ' ' . $billing_address['address_2'], 0, 34),
										'City'			=>	substr($billing_address['city'], 0, 29),
										'PostalCode'	=>	preg_replace("/[^A-Z0-9]/", "", $billing_address['postcode']),
										'CountryCode'	=>	$billing_address['country'],
									)
								)
							),
							'ExportDate'			=>	date('Ymd'),
							'ExportingCarrier'		=>	'UPS',
							'ReasonForExport'		=>	'SALE',
							'CurrencyCode'			=>	$order_currency,
						);

						//PDS-124
						if ($this->settings['commercial_invoice_shipping']) {

							$shipping_total 	= round($order_object->get_shipping_total(), 2);

							$shipping_total 	= apply_filters('ph_ups_freight_rate_based_on_country', $shipping_total, $to_address['country']);

							$json_shipmentServiceOptions['InternationalForms']['FreightCharges']['MonetaryValue'] = $shipping_total;
						}

						if (!$this->settings['edi_on_label']) {
							$json_shipmentServiceOptions['InternationalForms']['AdditionalDocumentIndicator'] = '1';
						}

						$form_types = array();

						// 01 - Commercial Invoice
						$form_types[] = '01';

						// Pre uploaded documents
						$documentDetails = PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, '_ph_ups_upload_document_details');

						if (!empty($documentDetails)) {

							$json_uploadedDocIds = array();

							foreach ($documentDetails as $documentDetail) {
								if (isset($documentDetail['documentID']) && !empty($documentDetail['documentID']) && !isset($documentDetail['isDeleted'])) {
									$json_uploadedDocIds[] = $documentDetail['documentID'];
								}
							}

							if (!empty($json_uploadedDocIds)) {

								// Customer generated form
								$form_types[] = '07';

								$json_shipmentServiceOptions['InternationalForms']['UserCreatedForm']['DocumentID'] = $json_uploadedDocIds;
							}
						}

						if ($this->settings['nafta_co_form'] && (!$is_not_forward || !$return_label) && isset(PH_WC_UPS_Constants::NAFTA_SUPPORTED_COUNTRIES[$ship_from_country]) && in_array($ship_to_country, PH_WC_UPS_Constants::NAFTA_SUPPORTED_COUNTRIES[$ship_from_country])) {

							// 04 - NAFTA
							$form_types[] = '04';

							$json_shipmentServiceOptions['InternationalForms']['AdditionalDocumentIndicator'] = '1';
							$json_shipmentServiceOptions['InternationalForms']['Contacts']['Producer'] = array(

								'Option' 		=> $this->settings['nafta_producer_option'],
								'CompanyName' 	=> substr($from_address['company'], 0, 34),
								'TaxIdentificationNumber' => $this->settings['tin_number'],
								'Address'		=> array(
									'AddressLine'	=>	array(
										substr($from_address['address_1'], 0, 34),
									),
									'City'			=>	substr($from_address['city'], 0, 29),
									'PostalCode'	=>	$from_address['postcode'],
									'CountryCode'	=>	$from_address['country'],
								),
							);

							if (!empty(substr($from_address['address_2'], 0, 34))) {
								$json_shipmentServiceOptions['InternationalForms']['Contacts']['Producer']['Address']['AddressLine'][1] = substr($from_address['address_2'], 0, 34);
							}

							$blanket_begin_period 	= !empty($this->settings['blanket_begin_period']) ? str_replace('-', '', $this->settings['blanket_begin_period']) : '';
							$blanket_end_period 	= !empty($this->settings['blanket_end_period']) ? str_replace('-', '', $this->settings['blanket_end_period']) : '';

							$json_shipmentServiceOptions['InternationalForms']['BlanketPeriod'] = array(

								'BeginDate'	=> $blanket_begin_period,
								'EndDate'	=> $blanket_end_period,
							);

						}

						if ($this->settings['eei_data'] && (!$is_not_forward || !$return_label) && $total_item_cost >= 2500 && in_array($ship_from_country, array('US', 'PR'))) {

							// 11 - EEI
							$form_types[] = '11';

							$json_shipmentServiceOptions['InternationalForms']['AdditionalDocumentIndicator'] = '1';
							$json_shipmentServiceOptions['InternationalForms']['EEIFilingOption'] = array(

								// 1 - Shipper filed, 2 - AES Direct, 3 - UPS filed
								'Code'			=>	'1',
								'ShipperFiled'	=>	array(
									'Code'		=> $this->settings['eei_shipper_filed_option'],
								),
							);

							// $_GET['itn'] & $_GET['exl'], from edit order page. ITN is unique for each shipment. An ITN cannot be re-used.
							if (isset($_GET['itn']) && !empty($_GET['itn']) && $_GET['itn'] != 'undefined') {

								$this->settings['eei_pre_departure_itn_number'] = $_GET['itn'];

								$ph_metadata_handler->ph_update_meta_data('_ph_ups_eei_itn_number', $_GET['itn']);
							}

							if (isset($_GET['exl']) && !empty($_GET['exl']) && $_GET['exl'] != 'undefined') {

								$this->settings['eei_exemption_legend'] = $_GET['exl'];

								$ph_metadata_handler->ph_update_meta_data('_ph_ups_eei_exemption_legend', $_GET['exl']);
							}

							if ($this->settings['eei_shipper_filed_option'] == 'A') {

								$json_shipmentServiceOptions['InternationalForms']['EEIFilingOption']['ShipperFiled']['PreDepartureITNNumber'] 	= $this->settings['eei_pre_departure_itn_number'];
							} else if ($this->settings['eei_shipper_filed_option'] == 'B') {

								$json_shipmentServiceOptions['InternationalForms']['EEIFilingOption']['ShipperFiled']['ExemptionLegend'] 		= $this->settings['eei_exemption_legend'];
							} else {

								$json_shipmentServiceOptions['InternationalForms']['EEIFilingOption']['ShipperFiled']['EEIShipmentReferenceNumber'] = $order_object->get_order_number();
							}

							$json_shipmentServiceOptions['InternationalForms']['Contacts']['ForwardAgent'] = array(

								'CompanyName'				=>	substr($from_address['company'], 0, 34),
								'TaxIdentificationNumber'	=>	$this->settings['tin_number'],
								'Address'					=>	array(
									'AddressLine'			=>	array(
										substr($from_address['address_1'], 0, 34),
									),
									'City'						=>	substr($from_address['city'], 0, 29),
									'StateProvinceCode'			=>	strlen($from_address['state']) < 6 ? $from_address['state'] : '',
									'PostalCode'				=>	$from_address['postcode'],
									'CountryCode'				=>	$from_address['country'],
								)
							);

							if (!empty(substr($from_address['address_2'], 0, 34))) {
								$json_shipmentServiceOptions['InternationalForms']['Contacts']['ForwardAgent']['Address']['AddressLine'][1] = substr($from_address['address_2'], 0, 34);
							}

							$json_shipmentServiceOptions['InternationalForms']['Contacts']['UltimateConsignee'] = array(
								'CompanyName' 			=>	substr($to_address['company'], 0, 34),
								'Address'				=>	array(
									'AddressLine'			=>	array(
										substr($to_address['address_1'], 0, 34),
									),
									'City'					=>	substr($to_address['city'], 0, 29),
									'StateProvinceCode'		=>	strlen($to_address['state']) < 6 ? $to_address['state'] : '',
									'PostalCode'			=>	$to_address['postcode'],
									'CountryCode'			=>	$to_address['country'],
								),
							);

							if (!empty(substr($to_address['address_2'], 0, 34))) {
								$json_shipmentServiceOptions['InternationalForms']['Contacts']['UltimateConsignee']['Address']['AddressLine'][1] = substr($to_address['address_2'], 0, 34);
							}

							// Consider Ultimate consignee type set under Edit Order Page
							if (isset($_GET['uct']) && $_GET['uct'] != 'undefined') {
								$this->settings['eei_ultimate_consignee_code'] = $_GET['uct'];
								$ph_metadata_handler->ph_update_meta_data('_ph_ups_eei_ultimate_consignee_type', $this->settings['eei_ultimate_consignee_code']);
							}

							if (!empty($this->settings['eei_ultimate_consignee_code']) && ($this->settings['eei_ultimate_consignee_code'] != 'none')) {
								$json_shipmentServiceOptions['InternationalForms']['Contacts']['UltimateConsignee']['UltimateConsigneeType']['Code'] = $this->settings['eei_ultimate_consignee_code'];
							}

							$json_shipmentServiceOptions['InternationalForms']['InBondCode'] 			= '70';
							$json_shipmentServiceOptions['InternationalForms']['PointOfOrigin'] 			= $from_address['state'];
							$json_shipmentServiceOptions['InternationalForms']['PointOfOriginType'] 		= 'S';		// State Postal Code
							$json_shipmentServiceOptions['InternationalForms']['ModeOfTransport'] 		= $this->settings['eei_mode_of_transport'];
							$json_shipmentServiceOptions['InternationalForms']['PartiesToTransaction'] 	= $this->settings['eei_parties_to_transaction'];
						}

						$json_shipmentServiceOptions['InternationalForms']['FormType']	=	$form_types;

						if ($is_not_forward && $return_label) {

							$json_shipmentServiceOptions['InternationalForms']['Contacts']['SoldTo'] = array(
								'Name'						=> substr($from_address['company'], 0, 34),
								'AttentionName'				=> substr($from_address['name'], 0, 34),
								'TaxIdentificationNumber'	=> $this->settings['tin_number'],
								'Phone'						=>	array(
									'Number'		=> $soldToPhone,
								),
								'Address'					=>	array(
									'AddressLine'	=>	substr($from_address['address_1'] . ' ' . $from_address['address_2'], 0, 34),
									'City'			=>	substr($from_address['city'], 0, 29),
									'PostalCode'	=>	$from_address['postcode'],
									'CountryCode'	=>	$from_address['country'],
								)
							);
						}

						$declaration_statement = $this->settings['declaration_statement'];

						$declaration_statement = apply_filters('ph_ups_declaration_statement_based_on_country', $declaration_statement, $to_address['country']);

						if (!empty($declaration_statement)) {
							$json_shipmentServiceOptions['InternationalForms']['DeclarationStatement'] = $declaration_statement;
						}

						if (!$is_not_forward || !$return_label) {

							if (!empty($this->settings['reason_export'])  && $this->settings['reason_export'] != 'none') {
								$json_shipmentServiceOptions['InternationalForms']['ReasonForExport']	=	$this->settings['reason_export'];
							}
						} else {

							if (!empty($this->settings['return_reason_export'])  && $this->settings['return_reason_export'] != 'none') {
								$json_shipmentServiceOptions['InternationalForms']['ReasonForExport']	=	$this->settings['return_reason_export'];
							}
						}

						if (isset($shipment_terms) && !empty($shipment_terms)) {
							$json_shipmentServiceOptions['InternationalForms']['TermsOfShipment']	=	$shipment_terms;
						}

						if (($is_not_forward && $return_label) && in_array($from_address['country'], PH_WC_UPS_Constants::COUNTRIES_WITH_STATECODES)) {
							$json_shipmentServiceOptions['InternationalForms']['Contacts']['SoldTo']['Address']['StateProvinceCode']	= strlen($from_address['state']) < 6 ? $from_address['state'] : '';
						} elseif ((!$is_not_forward || !$return_label) && in_array($billing_address['country'], PH_WC_UPS_Constants::COUNTRIES_WITH_STATECODES)) {
							$json_shipmentServiceOptions['InternationalForms']['Contacts']['SoldTo']['Address']['StateProvinceCode']	= strlen($billing_address['state']) < 6 ? $billing_address['state'] : '';
						}

						$json_shipmentServiceOptions['InternationalForms']['Product']	=	$invoice_products;
					}

					if ($this->wcsups_rest->settings['cod'] && (!$is_not_forward || !$return_label)) {

						// Shipment Level COD
						if (PH_WC_UPS_Common_Utils::is_shipment_level_cod_required($to_address['country'])) {

							// 1 for Cash, 9 for Cheque, 1 is available for all the countries
							$codfundscode 	= in_array($to_address['country'], array('RU', 'AE')) ? 1 : $this->settings['eu_country_cod_type'];
							$cod_value 		= $this->wcsups_rest->settings['cod_total'];

							//@note CODCode not available in doc ( Not added )
							$json_shipmentServiceOptions['COD'] = array(
								'CODFundsCode'	=>	$codfundscode,
								'CODAmount'		=>	array(
									//@note Added 'CurrencyCode' (mandatory). Check if $order_currency is the correct value
									'CurrencyCode'	=>	$order_currency,
									'MonetaryValue'	=>	$cod_value,
								),
							);
						}
					}

					if ($this->settings['tin_number']) {
						$jsonRequest_arr['ShipmentRequest']['Shipment']['Shipper']['TaxIdentificationNumber'] = $this->settings['tin_number'];
					}

					// PDS-79
					if ($ship_options['international_delivery_confirmation_applicable'] && $import_control == 'true') {

						$ship = $from_address;
						$dest = $to_address;

						if ($this->settings['ship_from_address'] == 'billing_address') {

							$ship = $to_address;
							$dest = $from_address;
						}

						$jsonRequest_arr['ShipmentRequest']['Shipment']['Shipper']	=	array(
							'AttentionName'		=>	substr($ship['name'], 0, 34),
							'Name'				=>	substr($ship['company'], 0, 34),
							'Phone'				=>	array(
								'Number'		=>	preg_replace("/[^0-9]/", "", $ship['phone']),
							),
							'EMailAddress'	    =>	$ship['email'],
							'ShipperNumber'		=>	$this->settings['shipper_number'],
							'Address'			=>	array(
								'AddressLine'		=>	array(
									substr($ship['address_1'], 0, 34),
								),
								'City'				=>	substr($ship['city'], 0, 29),
								'StateProvinceCode'	=>	strlen($ship['state']) < 6 ? $ship['state'] : '',
								'CountryCode'		=>	$ship['country'],
								'PostalCode'		=>	$ship['postcode'],
							),
						);

						if (!empty(substr($to_address['address_2'], 0, 34))) {
							$jsonRequest_arr['ShipmentRequest']['Shipment']['Shipper']['Address']['AddressLine'][1] = substr($to_address['address_2'], 0, 34);
						}

						if ($this->settings['tin_number']) {
							$jsonRequest_arr['ShipmentRequest']['Shipment']['Shipper']['TaxIdentificationNumber'] = $this->settings['tin_number'];
						}

						$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipFrom'] = array(
							'Name'			=>	substr($dest['company'], 0, 34),
							'AttentionName'	=>	substr($dest['name'], 0, 34),
							'Phone'			=>	array(
								'Number'	=>	preg_replace("/[^0-9]/", "", $dest['phone']),
							),
							'EMailAddress'	=>	$dest['email'],
							'Address'		=>	array(
								'AddressLine'		=>	array(
									substr($dest['address_1'], 0, 34),
								),
								'City'				=>	substr($dest['city'], 0, 29),
								'CountryCode'		=>	$dest['country'],
								'StateProvinceCode'	=>	strlen($dest['state']) < 6 ? $dest['state'] : '',
								'PostalCode'		=>	$dest['postcode'],
							)
						);

						if ($this->settings['recipients_tin']) {
							$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipFrom']['TaxIdentificationNumber'] = $shipto_recipients_tin;
						}

						if (!empty($dest['address_2'])) {
							$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipFrom']['Address']['AddressLine'][1] = substr($dest['address_2'], 0, 34);
						}

						$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipTo']	=	array(
							'Name'			=>	substr($ship['company'], 0, 34),
							'AttentionName'	=>	substr($ship['name'], 0, 34),
							'Phone'			=>	array(
								'Number'	=>	preg_replace("/[^0-9]/", "", $ship['phone']),
							),
							'EMailAddress'	=>	$ship['email'],
							'Address'		=>	array(
								'AddressLine'		=>	array(
									substr($ship['address_1'], 0, 34),
								),
								'City'				=>	substr($ship['city'], 0, 29),
								'CountryCode'		=>	$ship['country'],
								'PostalCode'		=>	$ship['postcode'],
							)
						);

						if (!empty(substr($ship['address_2'], 0, 34))) {
							$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipTo']['Address']['AddressLine'][1] = substr($ship['address_2'], 0, 34);
						}

						// State Code valid for certain countries only
						if (in_array($ship['country'], PH_WC_UPS_Constants::COUNTRIES_WITH_STATECODES)) {
							$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipTo']['Address']['StateProvinceCode']	=	strlen($ship['state']) < 6 ? $ship['state'] : '';
						}

						$selected_shipment_service = $this->get_service_code_for_country($shipment['shipping_service'], $from_address['country']);

						if ($this->settings['remove_recipients_phno'] && !in_array($selected_shipment_service, PH_WC_UPS_Constants::PHONE_NUMBER_SERVICES) && $from_address['country'] == $ship['country']) {

							if (isset($jsonRequest_arr['ShipmentRequest']['Shipment']['ShipTo']['Phone']['Number'])) {

								unset($jsonRequest_arr['ShipmentRequest']['Shipment']['ShipTo']['Phone']['Number']);
							}
						}

						if ($this->settings['tin_number']) {
							$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipTo']['TaxIdentificationNumber'] = $this->settings['tin_number'];
						}

						$jsonRequest_arr['ShipmentRequest']['Shipment']['InternationalForms']['Contacts']['SoldTo']	=	array(
							'Name'			=>	substr($ship['company'], 0, 34),
							'AttentionName'	=>	substr($ship['name'], 0, 34),
							'Phone'			=>	array(
								'Number'	=>	preg_replace("/[^0-9]/", "", $ship['phone']),
							),
							'Address'		=>	array(
								'AddressLine'		=>	array(
									substr($ship['address_1'], 0, 34),
								),
								'City'				=>	substr($ship['city'], 0, 29),
								'CountryCode'		=>	$ship['country'],
								'StateProvinceCode' => strlen($ship['state']) < 6 ? $ship['state'] : '',
								'PostalCode'		=>	preg_replace("/[^A-Z0-9]/", "", $ship['postcode']),
							),
						);

						// State Code valid for certain countries only
						if (in_array($ship['country'], PH_WC_UPS_Constants::COUNTRIES_WITH_STATECODES)) {
							$jsonRequest_arr['ShipmentRequest']['Shipment']['InternationalForms']['Contacts']['SoldTo']['Address']['StateProvinceCode']	= strlen($ship['state']) < 6 ? $ship['state'] : '';
						}

						if ($this->settings['tin_number']) {
							$jsonRequest_arr['ShipmentRequest']['Shipment']['InternationalForms']['Contacts']['SoldTo']['TaxIdentificationNumber'] = $this->settings['tin_number'];
						}

						if (!empty($ship['address_2'])) {
							$jsonRequest_arr['ShipmentRequest']['Shipment']['InternationalForms']['Contacts']['SoldTo']['Address']['AddressLine'][1] = substr($ship['address_2'], 0, 34);
						}

						if (in_array($dest['country'], array('US')) &&  in_array($ship['country'], array('PR', 'CA'))) {

							if ($order_total < 1) {
								$order_total = 1;
							}

							$jsonRequest_arr['ShipmentRequest']['Shipment']['InvoiceLineTotal']['CurrencyCode'] 	= $order_currency;
							$jsonRequest_arr['ShipmentRequest']['Shipment']['InvoiceLineTotal']['MonetaryValue'] 	= (int)$order_total;
							// For Return Shipment check condition in reverse order
						} else if (($is_not_forward && $return_label) && in_array($ship['country'], array('US')) &&  in_array($dest['country'], array('PR', 'CA'))) {

							if ($order_total < 1) {
								$order_total = 1;
							}

							$jsonRequest_arr['ShipmentRequest']['Shipment']['InvoiceLineTotal']['CurrencyCode'] 	= $order_currency;
							$jsonRequest_arr['ShipmentRequest']['Shipment']['InvoiceLineTotal']['MonetaryValue'] 	= (int)$order_total;
						}
					}

					if (!empty($this->settings['email_notification'])) {

						$emails = array();
						foreach ($this->settings['email_notification'] as $notifier) {
							switch ($notifier) {
									// Case 0 and 1 for backward compatibility, remove it after few version release 3.9.16.3
								case 'recipient':
								case 1:
									array_push($emails, array('EMailAddress' => $order->get_billing_email()));
									break;
									//sender
								case 'sender':
								case 0:
									array_push($emails, array('EMailAddress' => $this->settings['email']));
									break;
							}
						}

						if (!empty($emails)) {

							$email_notification_code = array('6');

							if (isset($this->settings['email_notification_code']) && !empty($this->settings['email_notification_code'])) {

								// 'Return or Label Creation' and 'In Transist' Only Support for Return Label and Import Control
								if ((array_intersect($this->settings['email_notification_code'], array('5', '2'))) && (($ship_options['international_delivery_confirmation_applicable'] && $import_control == 'true') || ($return_label))) {
									// 'In Transist' Only Support for Return Label
									if ($return_label) {

										$email_notification_code = array_intersect($this->settings['email_notification_code'], array('5', '2'));
									} else {

										$email_notification_code = array('2');
									}
								} else if (array_intersect($this->settings['email_notification_code'], array('6', '7', '8')) && (($import_control != 'true') && (!$return_label))) {

									$email_notification_code = array_intersect($this->settings['email_notification_code'], array('6', '7', '8'));
								}
							}

							$emailAddresses = array();
							foreach ($emails as $emailNode) {
								if (isset($emailNode['EMailAddress']) && !empty($emailNode['EMailAddress'])) {
									$emailAddresses[] = $emailNode['EMailAddress'];
								}
							}

							foreach ($email_notification_code as $key => $code) {

								$json_notification[] = array(

									'NotificationCode' 	=> $code,
									'EMail' 			=> array(
										'EMailAddress' 		=> $emailAddresses,
										'FromEMailAddress' 	=> $this->settings['email']
									),
								);

								// There can be only one UndeliverableEMailAddress for each shipment with Quantum View Shipment Notifications.
								if (count($json_notification) == 1) {
									$json_notification[0]['EMail']['UndeliverableEMailAddress'] = $this->settings['email'];
								}
							}
							
							if (isset($jsonRequest_arr['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['Notification'])) {
								$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['Notification'] = array_merge($jsonRequest_arr['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['Notification'], $json_notification);
							} else {
								$json_shipmentServiceOptions['Notification'] = array_merge($json_notification);
							}
						}
					}

					// Set Direct delivery in the actual request
					if (!empty($directdeliveryonlyindicator)) {
						$json_shipmentServiceOptions['DirectDeliveryOnlyIndicator'] = $directdeliveryonlyindicator;
					}

					if (sizeof($json_shipmentServiceOptions)) {
						$jsonRequest_arr['ShipmentRequest']['Shipment']['ShipmentServiceOptions']	=	empty($jsonRequest_arr['ShipmentRequest']['Shipment']['ShipmentServiceOptions']) ? $json_shipmentServiceOptions : array_merge($jsonRequest_arr['ShipmentRequest']['Shipment']['ShipmentServiceOptions'],  $json_shipmentServiceOptions);
					}

					//@note LabelPrintMethod not available in doc
					$jsonRequest_arr['ShipmentRequest']['LabelSpecification']['LabelImageFormat']	=	$this->get_code_from_label_type($this->settings['print_label_type']);
					$jsonRequest_arr['ShipmentRequest']['LabelSpecification']['HTTPUserAgent']		=	'Mozilla/4.5';

					if ('zpl' == $this->settings['print_label_type'] || 'epl' == $this->settings['print_label_type'] || 'png' == $this->settings['print_label_type']) {
						$jsonRequest_arr['ShipmentRequest']['LabelSpecification']['LabelStockSize']	=	array('Height' => '6', 'Width' => '4');
					}

					$jsonRequest_arr['ShipmentRequest']['LabelSpecification']['LabelImageFormat']	=	$this->get_code_from_label_type($this->settings['print_label_type']);

					if ($is_not_forward && $return_label) {

						$include_return_weight		= isset($_GET['rt_weight']) ? str_replace('\"', '', str_replace(']', '', str_replace('[', '', $_GET['rt_weight']))) : '';
						$include_return_length		= isset($_GET['rt_length']) ? str_replace('\"', '', str_replace(']', '', str_replace('[', '', $_GET['rt_length']))) : '';
						$include_return_width		= isset($_GET['rt_width']) ? str_replace('\"', '', str_replace(']', '', str_replace('[', '', $_GET['rt_width']))) : '';
						$include_return_height		= isset($_GET['rt_height']) ? str_replace('\"', '', str_replace(']', '', str_replace('[', '', $_GET['rt_height']))) : '';
						$include_return_insurance	= isset($_GET['rt_insurance']) ? str_replace('\"', '', str_replace(']', '', str_replace('[', '', $_GET['rt_insurance']))) : '';

						if (!empty($include_return_weight)) {
							$return_weight	= explode(',', $include_return_weight);
						}

						if (!empty($include_return_length) && !empty($include_return_width) && !empty($include_return_height)) {
							$return_length	= explode(',', $include_return_length);
							$return_width	= explode(',', $include_return_width);
							$return_height	= explode(',', $include_return_height);
						}

						if (!empty($include_return_insurance)) {
							$return_insurance	= explode(',', $include_return_insurance);
						}

						if (!empty($return_weight)) {

							for ($i = 0; $i < count($return_weight); $i++) {

								if (empty($return_weight[$i]))
									continue;

								$jsonRequest_arr['ShipmentRequest']['Shipment']['Package'][$i]	=	array(
									'Packaging'		=>	array(
										'Code'				=>	'02',
										'Description'		=>	'Package/customer supplied'
									),
									'Description'	=> 'Rate',
									'PackageWeight' => array(
										'UnitOfMeasurement'	=>	array(
											'Code'	=>	$this->settings['weight_unit'],
										),
										'Weight'	=>	(string) round($return_weight[$i], 2)
									),
								);

								if (!empty($return_length[$i]) && !empty($return_width[$i]) && !empty($return_height[$i])) {

									$jsonRequest_arr['ShipmentRequest']['Shipment']['Package'][$i]['Dimensions'] = array(

										'UnitOfMeasurement'	=>	array(
											'Code'	=>	$this->settings['dim_unit'],
										),
										'Length'	=>	(string) round($return_length[$i], 2),
										'Width'		=>	(string) round($return_width[$i], 2),
										'Height'	=>	(string) round($return_height[$i], 2),
									);
								}

								if (!empty($return_insurance[$i])) {

									//@note InsuredValue not present in doc. Since 'DeclaredValue' was present which seemed similar. Added it

									$jsonRequest_arr['ShipmentRequest']['Shipment']['Package'][$i]['PackageServiceOptions'] = array(

										'DeclaredValue'	=> array(
											'CurrencyCode'	=> $this->wcsups_rest->get_ups_currency(),
											'MonetaryValue'	=> $return_insurance[$i],
										)
									);
								}
							}
						}
					}

					$jsonRequest_arr	=	apply_filters('wf_ups_shipment_confirm_request_data', $jsonRequest_arr, $order_object);

					if ($this->is_hazmat_product) {
						$jsonRequest_arr['ShipmentRequest']['Request']['SubVersion'] = '1701';
					}

					$jsonRequest_arr	=	apply_filters('wf_ups_shipment_confirm_request', $jsonRequest_arr, $order_object, $shipment, $return_label);


					$shipment_requests[] = wp_json_encode($jsonRequest_arr);
				}

				$service_index++;
			}
		}

		$ph_metadata_handler->ph_save_meta_data();

		return $shipment_requests;
	}

	/*
		* Return products contains in the individual shipment
		*/
	function get_ups_invoice_products($shipment, $from_address, $order, $return_label = false, $ship_to_country = '', $total_item_cost = 0) {

		$product_details 	= array();
		$ship_from_country 	= $from_address;
		$order_item 		= array();
		$export_info_array	= array(
			"LC", "LV", "SS", "MS", "GS", "DP", "HR", "UG", "IC", "SC", "DD", "HH", "SR", "TE", "TL", "IS", "CR", "GP", "RJ", "TP", "IP", "IR", "DB", "CH", "RS", "OS",
		);

		if (!empty($order)) {

			// To support Mix and Match Product
			do_action('ph_ups_before_get_items_from_order', $order);

			$orderItems = $order->get_items();

			foreach ($orderItems as $orderItem) {

				$orderItemId 		= $orderItem->get_id();
				$refundedItemCount	= $order->get_qty_refunded_for_item($orderItemId);

				$orderItemQty 		= $orderItem->get_quantity() + $refundedItemCount;

				if ($orderItemQty <= 0) {

					continue;
				}

				$orderItem->set_quantity($orderItemQty);

				$item_id 				= $orderItem['variation_id'] ? $orderItem['variation_id'] : $orderItem['product_id'];
				$order_item[$item_id] 	= $orderItem;
			}

			// To support Mix and Match Product
			do_action('ph_ups_after_get_items_from_order', $order);
		}

		foreach ($shipment['packages'] as $package_key => $package) {

			if (isset($package['Package']['items'])) {

				foreach ($package['Package']['items'] as $item_index => $item) {

					$product_id 	= (wp_get_post_parent_id($item->get_id()) == 0) ? $item->get_id() : wp_get_post_parent_id($item->get_id());
					$item_id 		= $item->get_id();
					$product_data 	= (is_a($item, 'WC_Product') || is_a($item, 'wf_product')) ? $item : wc_get_product($item_id);

					$product_weight = $product_data->get_weight();

					if (empty($product_weight)) {

						$product_weight = 0;
					}

					if (isset($product_details[$item_id])) {

						$product_unit_weight	= wc_get_weight($product_weight, $this->settings['weight_unit']);
						$product_details[$item_id]['Unit']['Number'] += 1;
						$product_line_weight	= ($product_unit_weight	*	$product_details[$item_id]['Unit']['Number']);
						$product_line_weight	= round($product_line_weight, 1); // UPS supports only single value after decimal point
						$product_details[$item_id]['ProductWeight']['Weight'] = $product_line_weight;

						if ($this->settings['eei_data'] && !$return_label && $total_item_cost >= 2500 && in_array($ship_from_country, array('US', 'PR'))) {

							$product_details[$item_id]['ScheduleB']['Quantity'] = $product_details[$item_id]['Unit']['Number'];
							$product_details[$item_id]['SEDTotalValue'] 		= round($product_details[$item_id]['Unit']['Value'] * $product_details[$item_id]['Unit']['Number'], 2);
						}
					} else {

						// Include only those products which require shipping
						if ((is_a($product_data, 'WC_Product') || is_a($product_data, 'wf_product')) && $product_data->needs_shipping()) {

							$product_unit_weight	= wc_get_weight($product_weight, $this->settings['weight_unit']);
							$product_quantity		= 1;
							$product_line_weight	= $product_unit_weight	*	$product_quantity;
							$product_line_weight	= round($product_line_weight, 1); // UPS supports only single value after decimal point
							$hst 					= get_post_meta($item_id, '_ph_ups_hst_var', true);

							if (empty($hst)) {

								$hst 				= get_post_meta($product_id, '_wf_ups_hst', true);
							}

							$product_title 			= ($product_id != $item_id) ? strip_tags($product_data->get_formatted_name()) : $product_data->get_title();

							if ($this->settings['remove_special_char_product']) {

								$product_title 	= preg_replace('/[^A-Za-z0-9-()# ]/', '', $product_title);
								$product_title 	= htmlspecialchars($product_title);
							}

							$product_title 			= (strlen($product_title) >= 35) ? substr($product_title, 0, 32) . '...' : $product_title;

							if ( $this->settings['invoice_commodity_value'] == 'discount_price' ) {

								$product_price = isset($order_item[$item_id]) ? ($order_item[$item_id]->get_total() / $order_item[$item_id]->get_quantity()) : $product_data->get_price();
							} else if ( $this->settings['invoice_commodity_value'] == 'declared_price' ) {

								$custom_declared_value 	= get_post_meta($item_id, '_ph_ups_custom_declared_value_var', true);

								if (empty($custom_declared_value)) {

									$custom_declared_value	= get_post_meta($product_id, '_wf_ups_custom_declared_value', true);
								}

								$product_price 	= !empty($custom_declared_value) ? $custom_declared_value : $product_data->get_price();
							} else {

								$product_price 	= $product_data->get_price();
							}

							// Use fixed price form plugin settings for the product if product price is zero
							$product_price = ($product_price == 0) ? $this->settings['fixedProductPrice'] : $product_price;

							$countryofmanufacture 	= get_post_meta($product_id, '_ph_ups_manufacture_country', true);

							if (empty($countryofmanufacture)) {
								$countryofmanufacture = $from_address;
							}

							$product_details[$item_id] = array(
								'Unit'			=>	array(
									'Number'	=>	$product_quantity,
									'UnitOfMeasurement'	=>	array('Code'	=>	$this->settings['invoice_unit_of_measure']),
									'Value'		=>	round($product_price, 2),
								),
								'OriginCountryCode'	=>	$countryofmanufacture,
								'NumberOfPackagesPerCommodity'	=>	'1',
								'ProductWeight'	=>	array(
									'UnitOfMeasurement'	=>	array(
										'Code'	=> $this->settings['weight_unit'],
									),
									'Weight'			=>	$product_line_weight,
								),
								'CommodityCode' => $hst,
							);

							// Pass multi node if description exceeds 35 characters
							$invoiceDescription = $this->ph_get_commercial_invoice_description($product_id, $product_title, $item_id);
							$product_details[$item_id]['Description'] = $invoiceDescription;

							if ($this->settings['nafta_co_form'] && !$return_label && isset(PH_WC_UPS_Constants::NAFTA_SUPPORTED_COUNTRIES[$ship_from_country]) && in_array($ship_to_country, PH_WC_UPS_Constants::NAFTA_SUPPORTED_COUNTRIES[$ship_from_country])) {

								$net_cost_code 			= get_post_meta($product_id, '_ph_net_cost_code', true);
								$preference_criteria 	= get_post_meta($product_id, '_ph_preference_criteria', true);
								$producer_info 			= get_post_meta($product_id, '_ph_producer_info', true);

								$begin_date = !empty($this->settings['blanket_begin_period']) ? str_replace('-', '', $this->settings['blanket_begin_period']) : '';
								$end_date	= !empty($this->settings['blanket_end_period']) ? str_replace('-', '', $this->settings['blanket_end_period']) : '';

								$product_details[$item_id]['NetCostCode'] = !empty($net_cost_code) ? $net_cost_code : 'NC';
								$product_details[$item_id]['NetCostDateRange'] = array(
									'BeginDate'	=> $begin_date,
									'EndDate'	=> $end_date,
								);

								$product_details[$item_id]['PreferenceCriteria'] = !empty($preference_criteria) ? $preference_criteria : 'A';
								$product_details[$item_id]['ProducerInfo'] = !empty($producer_info) ? $producer_info : 'Yes';
							}

							if ($this->settings['eei_data'] && !$return_label && $total_item_cost >= 2500 && in_array($ship_from_country, array('US', 'PR'))) {

								$export_type		= get_post_meta($product_id, '_ph_eei_export_type', true);
								$export_info		= get_post_meta($product_id, '_ph_eei_export_information', true);
								$scheduleB_num		= get_post_meta($product_id, '_ph_eei_schedule_b_number', true);
								$unit_of_measure	= get_post_meta($product_id, '_ph_eei_unit_of_measure', true);

								$product_details[$item_id]['ScheduleB'] = array(
									'Number'			=> $scheduleB_num,
									'Quantity'			=> $product_quantity,
									'UnitOfMeasurement'	=>	array(
										'Code'	=> !empty($unit_of_measure) ? $unit_of_measure : 'X',
									),
								);

								$product_details[$item_id]['ExportType'] 		= !empty($export_type) ? $export_type : 'D';
								$product_details[$item_id]['SEDTotalValue'] 	= round($product_price * $product_quantity, 2);

								if (!empty($export_info) && in_array($export_info, $export_info_array)) {
									$product_details[$item_id]['EEIInformation'] 		= array(
										'ExportInformation'			=> $export_info,
									);
								}

								if ($this->settings['eei_shipper_filed_option'] == 'A') {
									$itar_exemption_number		= get_post_meta($product_id, '_ph_eei_itar_exemption_number', true);
									$ddtc_information_uom		= get_post_meta($product_id, '_ph_eei_ddtc_information_uom', true);
									$acm_number					= get_post_meta($product_id, '_ph_eei_acm_number', true);

									$product_details[$item_id]['EEIInformation']['License']['LicenseLineValue'] 			= round($product_price);
									$product_details[$item_id]['EEIInformation']['DDTCInformation']['ITARExemptionNumber'] 	= $itar_exemption_number;
									$product_details[$item_id]['EEIInformation']['DDTCInformation']['UnitOfMeasurement']['Code'] = $ddtc_information_uom;
									$product_details[$item_id]['EEIInformation']['DDTCInformation']['ACMNumber'] 			= $acm_number;
								}
							}

							$product_details[$item_id] = apply_filters('wf_ups_shipment_confirm_request_product_details', $product_details[$item_id], $product_data);
						}
					}
				}
			}
		}

		$invoice_products 	= array();

		if (!empty($product_details)) {

			// To support Bookings Plugin with UPS Compatibility
			$product_details = apply_filters('ph_ups_shipment_confirm_final_product_details', $product_details, $shipment, $order, $from_address, $ship_to_country, $return_label, $total_item_cost, $this);


			foreach ($product_details as $product_id => $product) {
				$invoice_products[] = $product;
			}
		} else {
			if (!empty($order)) {

				// To support Mix and Match Product
				do_action('ph_ups_before_get_items_from_order', $order);

				$orderItems = $order->get_items();

				foreach ($orderItems as $orderItem) {

					$orderItemId 		= $orderItem->get_id();
					$refundedItemCount	= $order->get_qty_refunded_for_item($orderItemId);

					$orderItemQty 		= $orderItem->get_quantity() + $refundedItemCount;

					if ($orderItemQty <= 0) {

						continue;
					}

					$orderItem->set_quantity($orderItemQty);
					
					$item_id 			= $orderItem['variation_id'] ? $orderItem['variation_id'] : $orderItem['product_id'];
					$product_data 		= wc_get_product($item_id);

					$product_weight = $product_data->get_weight();

					if (empty($product_weight)) {

						$product_weight = 0;
					}

					// Include only those products which require shipping
					if (is_a($product_data, 'WC_Product') && $product_data->needs_shipping()) {

						$product_unit_weight	= wc_get_weight($product_weight, $this->settings['weight_unit']);
						$product_quantity		= $orderItem->get_quantity();
						$product_line_weight	= ($product_unit_weight	*	$product_quantity);
						$product_line_weight 	= round($product_line_weight, 1); // UPS supports only single value after decimal point
						$hst 					= get_post_meta($orderItem['product_id'], '_wf_ups_hst', true);
						$product_id 			= isset($orderItem['product_id']) ?  $orderItem['product_id'] : $item_id;
						$product_title 			= ($product_id != $item_id) ? strip_tags($product_data->get_formatted_name()) : $product_data->get_title();
						$hst 					= get_post_meta($item_id, '_ph_ups_hst_var', true);

						if (empty($hst)) {

							$hst 				= get_post_meta($product_id, '_wf_ups_hst', true);
						}

						if ($this->settings['remove_special_char_product']) {

							$product_title 	= preg_replace('/[^A-Za-z0-9-()# ]/', '', $product_title);
							$product_title 	= htmlspecialchars($product_title);
						}

						$product_title 			= (strlen($product_title) >= 35) ? substr($product_title, 0, 32) . '...' : $product_title;

						if ( $this->settings['invoice_commodity_value'] == 'discount_price' ) {

							$product_price = isset($order_item[$item_id]) ? ($order_item[$item_id]->get_total() / $order_item[$item_id]->get_quantity()) : $product_data->get_price();
						} else if ( $this->settings['invoice_commodity_value'] == 'declared_price' ) {

							$custom_declared_value 	= get_post_meta($item_id, '_ph_ups_custom_declared_value_var', true);

							if (empty($custom_declared_value)) {

								$custom_declared_value	= get_post_meta($product_id, '_wf_ups_custom_declared_value', true);
							}

							$product_price 	= !empty($custom_declared_value) ? $custom_declared_value : $product_data->get_price();
						} else {

							$product_price 	= $product_data->get_price();
						}

						// Use fixed price form plugin settings for the product if product price is zero
						$product_price = ($product_price == 0) ? $this->settings['fixedProductPrice'] : $product_price;

						$countryofmanufacture 	= get_post_meta($product_id, '_ph_ups_manufacture_country', true);

						if (empty($countryofmanufacture)) {
							$countryofmanufacture = $from_address;
						}

						$product_details = array(
							'Unit'			=>	array(
								'Number'	=>	$product_quantity,
								'UnitOfMeasurement'	=>	array('Code'	=>	$this->settings['invoice_unit_of_measure']),
								'Value'		=>	round($product_price, 2),
							),
							'OriginCountryCode'	=>	$countryofmanufacture,
							'NumberOfPackagesPerCommodity'	=>	'1',
							'ProductWeight'	=>	array(
								'UnitOfMeasurement'	=>	array(
									'Code'	=> $this->settings['weight_unit'],
								),
								'Weight'			=>	$product_line_weight,
							),
							'CommodityCode' => $hst,
						);

						// Pass multi node if description exceeds 35 characters
						$invoiceDescription = $this->ph_get_commercial_invoice_description($product_id, $product_title, $item_id);
						$product_details['Description'] = $invoiceDescription;

						if ($this->settings['nafta_co_form'] && !$return_label && isset(PH_WC_UPS_Constants::NAFTA_SUPPORTED_COUNTRIES[$ship_from_country]) && in_array($ship_to_country, PH_WC_UPS_Constants::NAFTA_SUPPORTED_COUNTRIES[$ship_from_country])) {
							$net_cost_code 			= get_post_meta($product_id, '_ph_net_cost_code', true);
							$preference_criteria 	= get_post_meta($product_id, '_ph_preference_criteria', true);
							$producer_info 			= get_post_meta($product_id, '_ph_producer_info', true);

							$begin_date = !empty($this->settings['blanket_begin_period']) ? str_replace('-', '', $this->settings['blanket_begin_period']) : '';
							$end_date	= !empty($this->settings['blanket_end_period']) ? str_replace('-', '', $this->settings['blanket_end_period']) : '';

							$product_details['NetCostCode'] = !empty($net_cost_code) ? $net_cost_code : 'NC';
							$product_details['NetCostDateRange'] = array(
								'BeginDate'	=> $begin_date,
								'EndDate'	=> $end_date,
							);
							$product_details['PreferenceCriteria'] = !empty($preference_criteria) ? $preference_criteria : 'A';
							$product_details['ProducerInfo'] = !empty($producer_info) ? $producer_info : 'Yes';
						}

						if ($this->settings['eei_data'] && !$return_label && $total_item_cost >= 2500 && in_array($ship_from_country, array('US', 'PR'))) {
							$export_type		= get_post_meta($product_id, '_ph_eei_export_type', true);
							$export_info		= get_post_meta($product_id, '_ph_eei_export_information', true);
							$scheduleB_num		= get_post_meta($product_id, '_ph_eei_schedule_b_number', true);
							$unit_of_measure	= get_post_meta($product_id, '_ph_eei_unit_of_measure', true);

							$product_details['ScheduleB'] = array(
								'Number'			=> $scheduleB_num,
								'Quantity'			=> $product_quantity,
								'UnitOfMeasurement'	=>	array(
									'Code'	=> !empty($unit_of_measure) ? $unit_of_measure : 'X',
								),
							);

							$product_details['ExportType'] 		= !empty($export_type) ? $export_type : 'D';
							$product_details['SEDTotalValue'] 	= round(((!empty($product_data->get_price()) ? $product_data->get_price() : $this->settings['fixedProductPrice']) / $this->wcsups_rest->settings['conversion_rate']) * $product_quantity, 2);

							if (!empty($export_info) && in_array($export_info, $export_info_array)) {
								$product_details['EEIInformation'] 		= array(
									'ExportInformation'			=> $export_info,
								);
							}

							if ($this->settings['eei_shipper_filed_option'] == 'A') {
								$itar_exemption_number		= get_post_meta($product_id, '_ph_eei_itar_exemption_number', true);
								$ddtc_information_uom		= get_post_meta($product_id, '_ph_eei_ddtc_information_uom', true);
								$acm_number					= get_post_meta($product_id, '_ph_eei_acm_number', true);

								$product_details['EEIInformation']['License']['LicenseLineValue'] 				= round(((!empty($product_data->get_price()) ? $product_data->get_price() : $this->settings['fixedProductPrice']) / $this->wcsups_rest->settings['conversion_rate']));
								$product_details['EEIInformation']['DDTCInformation']['ITARExemptionNumber'] 	= $itar_exemption_number;
								$product_details['EEIInformation']['DDTCInformation']['UnitOfMeasurement']['Code'] = $ddtc_information_uom;
								$product_details['EEIInformation']['DDTCInformation']['ACMNumber'] 				= $acm_number;
							}
						}

						$invoice_products[] = apply_filters('wf_ups_shipment_confirm_request_product_details', $product_details, $product_data);
					}
				}

				// To support Mix and Match Product
				do_action('ph_ups_after_get_items_from_order', $order);
			}
		}

		return apply_filters('ph_ups_shipment_invoice_product_details', $invoice_products, $shipment, $order, $from_address, $ship_to_country, $return_label, $total_item_cost, $this);
	}


	/**
	 * Get Ship From Address.
	 */
	private function get_ship_from_address($settings) {

		$json_ship_from_address = array();

		if (!empty($this->settings['ship_from_addressline'])) {

			$ship_from_country_state = $this->settings['ship_from_country_state'];

			if (strstr($ship_from_country_state, ':')) :
				list($ship_from_country, $ship_from_state) = explode(':', $ship_from_country_state);
			else :
				$ship_from_country = $ship_from_country_state;
				$ship_from_state   = '';
			endif;

			$ship_from_custom_state = !empty($this->settings['ship_from_custom_state']) ? $this->settings['ship_from_custom_state'] : $ship_from_state;
			$attention_name 		= !empty($this->settings['ups_display_name']) ? preg_replace("/&#?[a-z0-9]+;/i", "", $this->settings['ups_display_name']) : '-';
			$company_name 			= isset($this->settings['ups_user_name']) ? preg_replace("/&#?[a-z0-9]+;/i", "", $this->settings['ups_user_name']) : '-';

			$json_ship_from_address = array(
				'Name'			=>	substr($company_name, 0, 34),
				'AttentionName'	=>	substr($attention_name, 0, 34),
				'Address'		=>	array(
					'AddressLine'	=>	array(
						substr($this->settings['ship_from_addressline'], 0, 34),
					),
					'City'			=>	substr($this->settings['ship_from_city'], 0, 29),
					'PostalCode'	=>	$this->settings['ship_from_postcode'],
					'CountryCode'	=>	$ship_from_country,
				),
			);

			if (!empty($ship_from_custom_state)) {
				$json_ship_from_address['Address']['StateProvinceCode'] = strlen($ship_from_custom_state) < 6 ? $ship_from_custom_state : '';
			}

			if (isset($this->settings['ship_from_addressline_2']) && !empty($this->settings['ship_from_addressline_2'])) {
				$json_ship_from_address['Address']['AddressLine'][1] = substr($this->settings['ship_from_addressline_2'], 0, 34);
			}
		}
		return $json_ship_from_address;
	}

	/**
	 * Get Ship To Address for Return Label.
	 */
	private function get_ship_to_address_in_return_label($settings, $from_address = array()) {

		$json_ship_to_address = array();

		if ($this->settings['ship_from_address_different_from_shipper']) {

			$ship_from_country_state = $this->settings['ship_from_country_state'];
			if (strstr($ship_from_country_state, ':')) :
				list($ship_from_country, $ship_from_state) = explode(':', $ship_from_country_state);
			else :
				$ship_from_country = $ship_from_country_state;
				$ship_from_state   = '';
			endif;

			$ship_from_custom_state   = !empty($this->settings['ship_from_custom_state']) ? $this->settings['ship_from_custom_state'] : $ship_from_state;

			$json_ship_to_address = array(
				'AddressLine'		=>	array(
					substr($this->settings['ship_from_addressline'], 0, 34),
				),
				'City'				=>	substr($this->settings['ship_from_city'], 0, 29),
				'CountryCode'		=>	$ship_from_country,
				'PostalCode'		=>	$this->settings['ship_from_postcode'],
			);

			if (!empty($ship_from_custom_state)) {
				$json_ship_to_address['StateProvinceCode']	=	strlen($ship_from_custom_state) < 6 ? $ship_from_custom_state : '';
			}

			if (isset($this->settings['ship_from_addressline_2']) && !empty($this->settings['ship_from_addressline_2'])) {
				$json_ship_to_address['AddressLine'][1] = substr($this->settings['ship_from_addressline_2'], 0, 34);
			}
		} else {

			$json_ship_to_address = array(
				'AddressLine'		=>	array(
					substr($from_address['address_1'], 0, 34),
				),
				'City'				=>	substr($from_address['city'], 0, 29),
				'StateProvinceCode'	=>	strlen($from_address['state']) < 6 ? $from_address['state'] : '',
				'CountryCode'		=>	$from_address['country'],
				'PostalCode'		=>	$from_address['postcode'],
			);

			if (isset($from_address['address_2']) && !empty($from_address['address_2'])) {
				$json_ship_to_address['AddressLine'][1] = substr($from_address['address_2'], 0, 34);
			}
		}
		return $json_ship_to_address;
	}


	private function wf_is_surepost($shipping_method) {
		return in_array($shipping_method, PH_WC_UPS_Constants::UPS_SUREPOST_SERVICES);
	}

	private function get_service_code_for_country($service, $country) {
		$service_for_country = array(
			'CA' => array(
				'07' => '01', // for Canada serivce code of 'UPS Express(07)' is '01'
			),
		);

		if (array_key_exists($country, $service_for_country)) {

			return isset($service_for_country[$country][$service]) ? $service_for_country[$country][$service] : $service;
		}

		return $service;
	}

	private function ph_get_accesspoint_data($order_details) {

		$order_details	= wc_get_order($order_details->get_id());
		$address_field 	= is_object($order_details) ? $order_details->get_meta('_shipping_accesspoint') : '';

		return json_decode($address_field);
	}

	public function get_confirm_shipment_accesspoint_request($order_details) {
		$accesspoint 			= $this->ph_get_accesspoint_data($order_details);
		$order_id 				= $order_details->get_id();
		$accesspoint_locators 	= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, '_ph_accesspoint_location');

		// If $accesspoint contains locator_id only, we loop through locators to find selected locator
		if (!is_object($accesspoint) && !empty($accesspoint)) {

			$decoded_accesspoint = json_decode($accesspoint, true);

			if (is_array($decoded_accesspoint))
				$accesspoint = implode('', $decoded_accesspoint['LocationID']);


			if (is_array($accesspoint_locators)) {

				foreach ($accesspoint_locators as $locator_id => $locator) {

					if ($locator_id == $accesspoint) {
						$accesspoint = $locator;
						break;
					}
				}
			}

			$accesspoint = json_decode($accesspoint);
		}

		$json_confirm_accesspoint_request = array();

		if (isset($accesspoint->AddressKeyFormat)) {

			$access_point_consignee		= $accesspoint->AddressKeyFormat->ConsigneeName;
			$access_point_addressline	= $accesspoint->AddressKeyFormat->AddressLine;
			$access_point_city			= isset($accesspoint->AddressKeyFormat->PoliticalDivision2) ? $accesspoint->AddressKeyFormat->PoliticalDivision2 : '';
			$access_point_state			= isset($accesspoint->AddressKeyFormat->PoliticalDivision1) ? $accesspoint->AddressKeyFormat->PoliticalDivision1 : '';
			$access_point_postalcode	= $accesspoint->AddressKeyFormat->PostcodePrimaryLow;
			$access_point_country		= $accesspoint->AddressKeyFormat->CountryCode;
			$access_point_id 			= '';

			if (!empty($accesspoint->AccessPointInformation->PublicAccessPointID)) {

				$access_point_id			= $accesspoint->AccessPointInformation->PublicAccessPointID;
			}


			if (strlen($access_point_addressline) > 35) {

				$address_line_1		= null;
				$address_line_2		= null;
				$temp_address		= null;
				$new_address		= explode(' ', $access_point_addressline);

				foreach ($new_address as $word) {

					$temp_address = $temp_address . ' ' . $word;

					if (empty($address_line_2) && strlen($temp_address) <= 35) {

						$address_line_1 = $address_line_1 . ' ' . $word;
					} else {

						$address_line_2	= $address_line_2 . ' ' . $word;
					}

					$temp_address = empty($address_line_2) ? $address_line_1 : $address_line_2;
				}
			}

			$access_point_consignee 	= preg_replace('/[^A-Za-z0-9-()#, ]/', '', $access_point_consignee);

			//JSON Request
			$json_confirm_accesspoint_request	=	array(

				'ShipmentIndicationType'	=>	array('Code' => '01'),
				'AlternateDeliveryAddress'	=>	array(
					'Name'				=>	$access_point_consignee,
					'AttentionName'		=>	$access_point_consignee,
					'UPSAccessPointID'	=>	$access_point_id,
					'Address'			=>	array(
						'AddressLine'		=> array(
							!empty($address_line_1) ? $address_line_1 : $access_point_addressline,
							!empty($address_line_2) ? $address_line_2 : '-',
						),
						'City'				=>	$access_point_city,
						'StateProvinceCode'	=>	strlen($access_point_state) < 6 ? $access_point_state : '',
						'PostalCode'		=>	$access_point_postalcode,
						'CountryCode'		=>	$access_point_country,
					),
				),
			);

			$json_accesspoint_notifications[] = array(

				'NotificationCode' => '012',
				'EMail' => array(
					'EMailAddress' => $order_details->get_billing_email(),
				),
				'Locale' => array(
					'Language' => 'ENG',
					'Dialect' => 'US',
				),
			);

			$json_accesspoint_notifications[] = array(

				'NotificationCode' => '013',
				'EMail' => array(
					'EMailAddress' => $order_details->get_billing_email(),
				),
				'Locale' => array(
					'Language' => 'ENG',
					'Dialect' => 'US',
				),
			);

			$json_confirm_accesspoint_request['ShipmentServiceOptions']['Notification'] =	array_merge($json_accesspoint_notifications);
		}

		return $json_confirm_accesspoint_request;
	}

	private function get_code_from_label_type($label_type) {
		switch ($label_type) {
			case 'zpl':
				$code_val = 'ZPL';
				break;
			case 'epl':
				$code_val = 'EPL';
				break;
			case 'png':
				$code_val = 'PNG';
				break;
			default:
				$code_val = 'GIF';
				break;
		}
		return array('Code' => $code_val);
	}

	/**
	 * Shipment Description / Reference Number Details
	 * 
	 * @param  object $order
	 * @param  array $shipment
	 * @param  bool $package
	 * @return string Shipment Description
	 */
	private function wf_get_shipment_description($order, $shipment, $package = false) {

		// To support Mix and Match Product
		do_action('ph_ups_before_get_items_from_order', $order);

		// Return only the Order Number
		if ($this->settings['label_description'] == 'order_number') {

			return Ph_UPS_Woo_Shipping_Common::getOrderIdOrNumber($order, 'include_order_number');
		}

		$order_items 	= $order->get_items();

		$shipment_description 	= '';
		$categories 			= '';
		$shipment_products 		= array();
		$shipment_qty 			= array();

		if ($package) {

			if (isset($shipment['Package']) && isset($shipment['Package']['items'])) {

				foreach ($shipment['Package']['items'] as $item) {

					if ($item instanceof wf_product || $item instanceof WC_Product_Simple || $item instanceof WC_Product_Variation) {

						$item_id 				= ($item->get_parent_id() == 0) ? $item->get_id() : $item->get_parent_id();
						$shipment_products[] 	= $item_id;

						if (isset($shipment_qty[$item_id]) && !empty($shipment_qty[$item_id])) {

							$shipment_qty[$item_id]++;
						} else {
							$shipment_qty[$item_id] = 1;
						}
					}
				}
			}
		} else {

			if (isset($shipment['packages']) && is_array($shipment['packages']) && count($shipment['packages']) == 1) {

				$package = $shipment['packages'][0];

				if (isset($package['Package']) && isset($package['Package']['items'])) {

					foreach ($package['Package']['items'] as $item) {

						if ($item instanceof wf_product || $item instanceof WC_Product_Simple || $item instanceof WC_Product_Variation) {

							$item_id 				= ($item->get_parent_id() == 0) ? $item->get_id() : $item->get_parent_id();
							$shipment_products[] 	= $item_id;

							if (isset($shipment_qty[$item_id]) && !empty($shipment_qty[$item_id])) {

								$shipment_qty[$item_id]++;
							} else {
								$shipment_qty[$item_id] = 1;
							}
						}
					}
				}
			}
		}

		if ($package && $this->settings['add_product_sku']) {

			if ($this->settings['order_id_or_number_in_label'] == 'include_order_number') {

				// Append a hyphen at the end to separate the Order Number from the additional description
				$shipment_description	= Ph_UPS_Woo_Shipping_Common::getOrderIdOrNumber($order, 'include_order_number') . ' - ';
			} elseif ($this->settings['order_id_or_number_in_label'] == 'include_order_id') {

				// Append a hyphen at the end to separate the Order Id from the additional description
				$shipment_description	= Ph_UPS_Woo_Shipping_Common::getOrderIdOrNumber($order, 'include_order_id') . ' - ';
			}

			if (is_array($order_items) && count($order_items)) {

				$product_sku = '';

				foreach ($order_items as $order_item) {

					$orderItemId 		= $order_item->get_id();
					$refundedItemCount	= $order->get_qty_refunded_for_item($orderItemId);

					$orderItemQty 		= $order_item->get_quantity() + $refundedItemCount;

					if ($orderItemQty <= 0) {

						continue;
					}

					$product = $this->get_product_from_order_item($order_item);

					if (is_a($product, 'WC_Product') && $product->needs_shipping()) {

						$product_id = ($product->get_parent_id() == 0) ? $product->get_id() : $product->get_parent_id();

						if (empty($shipment_products) || in_array($product_id, $shipment_products)) {

							$product_sku 	.= $product->get_sku() . ', ';
						}
					}
				}

				$shipment_description .= rtrim($product_sku, ', ');
			}

			if (!empty($shipment_description)) {

				if ($this->settings['remove_special_char_product']) {

					$shipment_description 	= preg_replace('/[^A-Za-z0-9-()# ]/', '', $shipment_description);
					$shipment_description 	= htmlspecialchars($shipment_description);
				}

				$shipment_description = apply_filters('ph_ups_alter_shipment_desription', $shipment_description);
				$shipment_description = (strlen($shipment_description) >= 50) ? substr($shipment_description, 0, 45) . '...' : $shipment_description;

				return $shipment_description;
			}

			$shipment_description = '';
		}

		if ($this->settings['order_id_or_number_in_label'] == 'include_order_number') {

			// Append a hyphen at the end to separate the Order Number from the additional description
			$shipment_description	= Ph_UPS_Woo_Shipping_Common::getOrderIdOrNumber($order, 'include_order_number') . ' - ';
		} elseif ($this->settings['order_id_or_number_in_label'] == 'include_order_id') {

			// Append a hyphen at the end to separate the Order Id from the additional description
			$shipment_description	= Ph_UPS_Woo_Shipping_Common::getOrderIdOrNumber($order, 'include_order_id') . ' - ';
		}

		if (is_array($order_items) && count($order_items)) {

			$product_categories	= '';

			foreach ($order_items as $order_item) {

				$orderItemId 		= $order_item->get_id();
				$refundedItemCount	= $order->get_qty_refunded_for_item($orderItemId);
				$orderItemQty 		= $order_item->get_quantity() + $refundedItemCount;

				if ($orderItemQty <= 0) {

					continue;
				}

				$product = $this->get_product_from_order_item($order_item);

				if (is_a($product, 'WC_Product') && $product->needs_shipping()) {

					$product_id = ($product->get_parent_id() == 0) ? $product->get_id() : $product->get_parent_id();

					if (empty($shipment_products) || in_array($product_id, $shipment_products)) {
						$product_categories	.= (string) strip_tags(wc_get_product_category_list($product_id, ', ', '', '')) . ', ';
					}
				}
			}

			$cat_array = array_unique(explode(', ', rtrim($product_categories, ', ')));

			if (($key = array_search('Uncategorized', $cat_array)) !== false) {
				unset($cat_array[$key]);
			}

			$categories = implode(', ', $cat_array);
		}

		if ($this->settings['label_description'] == 'custom_description' && !empty($this->settings['label_custom_description'])) {

			$shipment_description .= strip_tags($this->settings['label_custom_description']);
		} elseif (($this->settings['label_description'] == 'product_category' || $this->settings['label_description'] == 'custom_description') && !empty($categories)) {

			$shipment_description .=	$categories;
		} else {

			if (is_array($order_items) && count($order_items)) {

				$product_names 	= '';
				$product_descs 	= '';
				$product_qty 	= '';
				$name_qty 		= '';

				foreach ($order_items as $order_item) {

					$orderItemId 		= $order_item->get_id();
					$refundedItemCount	= $order->get_qty_refunded_for_item($orderItemId);

					$orderItemQty 		= $order_item->get_quantity() + $refundedItemCount;

					if ($orderItemQty <= 0) {

						continue;
					}

					$product 		= $this->get_product_from_order_item($order_item);
					$product_qty 	= $orderItemQty;

					if (is_a($product, 'WC_Product') && $product->needs_shipping()) {

						$product_id = ($product->get_parent_id() == 0) ? $product->get_id() : $product->get_parent_id();
						$var_id 	= $product->get_id();

						if (empty($shipment_products) || in_array($product_id, $shipment_products)) {

							if (!empty($shipment_qty) && isset($shipment_qty[$product_id]) && $shipment_qty[$product_id] < $product_qty) {

								$product_qty 	= $shipment_qty[$product_id];
							}

							$product_names 	.= strip_tags($product->get_formatted_name()) . ', ';
							$name_qty 		.= strip_tags($product->get_formatted_name()) . 'x' . $product_qty . ', ';
							$pdt_description = get_post_meta($var_id, 'ph_ups_invoice_desc_var', true);

							if (empty($pdt_description)) {

								$pdt_description = get_post_meta($product_id, 'ph_ups_invoice_desc', true);
							}

							if (!empty($pdt_description)) {

								// Meta Value will contain special characters as HTML entities
								// Convert special HTML entities back to characters
								$product_descs 	.= htmlspecialchars_decode($pdt_description) . ', ';
							}
						}
					}
				}

				if ($this->settings['label_description'] == 'name_quantity' || empty($name_qty)) {

					$shipment_description .= rtrim($name_qty, ', ');
				} else if ($this->settings['label_description'] == 'product_name' || empty($product_descs)) {

					$shipment_description .= rtrim($product_names, ', ');
				} else {

					$shipment_description .= rtrim($product_descs, ', ');
				}
			}
		}

		//PDS-153
		if ($this->settings['remove_special_char_product']) {

			$shipment_description 	= preg_replace('/[^A-Za-z0-9-()# ]/', '', $shipment_description);
			$shipment_description 	= htmlspecialchars($shipment_description);
		}

		$shipment_description = apply_filters('wf_ups_alter_shipment_desription', $shipment_description);
		$shipment_description = (strlen($shipment_description) >= 50) ? substr($shipment_description, 0, 45) . '...' : $shipment_description;

		// To support Mix and Match Product
		do_action('ph_ups_after_get_items_from_order', $order);

		return $shipment_description;
	}

	private function ph_get_commercial_invoice_description($product_id, $invoice_default, $var_id) {

		$invoice_description 	= '';
		$pro_description 		= get_post_meta($var_id, 'ph_ups_invoice_desc_var', true);

		if (empty($pro_description)) {

			$pro_description = get_post_meta($product_id, 'ph_ups_invoice_desc', true);
		}

		if (isset($pro_description) && !empty($pro_description)) {

			// Meta Value will contain special characters as HTML entities
			// Convert special HTML entities back to characters 
			$invoice_description = htmlspecialchars_decode($pro_description);

			//PDS-153
			if ($this->settings['remove_special_char_product']) {

				$invoice_description 	= preg_replace('/[^A-Za-z0-9-()# ]/', '', $invoice_description);
				$invoice_description 	= htmlspecialchars($invoice_description);
			}

			// Split into multiple discription if length is exceeds 35 characters
			$invoice_description = (strlen($invoice_description) > 35) ? $this->ph_get_splitted_description($invoice_description) : $invoice_description;

			return $invoice_description;
		}

		if ($this->settings['include_in_commercial_invoice'] || $this->settings['label_description'] == 'product_name' || $this->settings['label_description'] == 'order_number') {

			return $invoice_default;
		}

		$invoice_description =	'';
		$categories = '';

		$product_categories	= (string) strip_tags(wc_get_product_category_list($product_id, ', ', '', '')) . ', ';
		$cat_array = array_unique(explode(', ', rtrim($product_categories, ', ')));
		if (($key = array_search('Uncategorized', $cat_array)) !== false) {
			unset($cat_array[$key]);
		}

		$categories = implode(', ', $cat_array);

		if ($this->settings['label_description'] == 'custom_description' && !empty($this->settings['label_custom_description'])) {

			$invoice_description = strip_tags($this->settings['label_custom_description']);
		} elseif (($this->settings['label_description'] == 'product_category' || $this->settings['label_description'] == 'custom_description') && !empty($categories)) {

			$invoice_description =	$categories;
		} else {
			$invoice_description =	$invoice_default;
		}

		$invoice_description = apply_filters('ph_ups_alter_invoice_desription', $invoice_description);

		//PDS-153
		if ($this->settings['remove_special_char_product']) {

			$invoice_description 	= preg_replace('/[^A-Za-z0-9-()# ]/', '', $invoice_description);
			$invoice_description 	= htmlspecialchars($invoice_description);
		}

		// Split into multiple discription if length is exceeds 35 characters
		$invoice_description = (strlen($invoice_description) > 35) ? $this->ph_get_splitted_description($invoice_description) : $invoice_description;

		return $invoice_description;
	}

	/**
	 * Split product description for shipment
	 *
	 * @param mixed $invoice_description
	 * @return mixed $multiLineDesc
	 */
	public function ph_get_splitted_description($invoice_description) {

		// Splitting description by 35 characters
		$multiLineDesc 			= [];
		$splittedDescription	= str_split($invoice_description, 35);

		foreach ($splittedDescription as $key => $description) {
			$multiLineDesc[] = $description;

			if ($key == 2)
				break;
		}

		return $multiLineDesc;
	}

	/**
	 * Get Product from Order Line Item.
	 * @param array|object $line_item Array in less than woocommerce 3.0 else Object
	 * @return object WC_Product|null|false
	 */
	public function get_product_from_order_item($line_item) {
		$product = $line_item->get_product();

		return $product;
	}

	function wf_get_package_data($order, $ship_options = array(), $to_address = array()) {

		if( empty( $this->settings )) {
			$settings_helper 	= new PH_WC_UPS_Settings_Helper();
			$this->settings  	= $settings_helper->settings;
		}

		$this->wcsups_rest  = new PH_Shipping_UPS_Rest( $order );
		$this->debug		= $this->settings['debug'];

		$packages	= $this->wf_create_package($order, $to_address);
		$order_id 	= $order->get_id();

		// If return label is printing, cod can't be applied
		if (!isset($ship_options['return_label']) || !$ship_options['return_label']) {

			$this->wcsups_rest->ph_set_cod_details($order);
		}

		$order = wc_get_order($order_id);
		// Set Insurance value false
		$order_subtotal = is_object($order) ? $order->get_subtotal() : 0;

		if (isset($this->settings['min_order_amount_for_insurance']) && $order_subtotal < $this->settings['min_order_amount_for_insurance']) {

			$this->wcsups_rest->settings['insuredvalue'] = false;
		}

		$service_code = PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, 'wf_ups_selected_service');

		if ($service_code) {

			$this->wcsups_rest->ph_set_service_code($service_code);

			// Insurance value doesn't work with sure post services
			if (in_array($service_code, array(92, 93, 94, 95))) {

				$this->wcsups_rest->settings['insuredvalue'] = false;
			}
		}

		$package_params	=	array();

		if (isset($ship_options['delivery_confirmation_applicable'])) {

			$package_params['delivery_confirmation_applicable']	=	$ship_options['delivery_confirmation_applicable'];
		}

		$packing_method  = isset($this->settings['packing_method']) ? $this->settings['packing_method'] : 'per_item';
		$package_data    = array();

		foreach ($packages as $key => $package) {

			// Filter to customize the package, for example to support bundle product
			$package 				= apply_filters('wf_customize_package_on_generate_label', $package, $order_id);
			$temp_package_data 		= $this->wcsups_rest->ph_get_api_rate_box_data( $package, $packing_method, $package_params );

			// Support for woocommerce multi shipping address
			$temp_package_data		=	apply_filters('ph_ups_customize_package_by_desination', $temp_package_data, $package['destination']);

			if (is_array($temp_package_data)) {

				$package_data = array_merge($package_data, $temp_package_data);
			}
		}

		return $package_data;
	}

	function wf_create_package($order, $to_address = array()) {

		// To support Mix and Match Product
		do_action('ph_ups_before_get_items_from_order', $order);

		$orderItems 		= $order->get_items();
		$items 				= array();
		$order_id 			= $order->get_id();
		$label_generation	= 'Label Generation';

		foreach ($orderItems as $orderItem) {

			$orderItemId 		= $orderItem->get_id();
			$refundedItemCount	= $order->get_qty_refunded_for_item($orderItemId);
			$item_id 			= $orderItem['variation_id'] ? $orderItem['variation_id'] : $orderItem['product_id'];
			$orderItemQty 		= $orderItem['qty'] + $refundedItemCount;

			if ($orderItemQty <= 0) {

				continue;
			}

			if (empty($items[$item_id])) {

				// $product_data 		= wc_get_product( $item_id );
				$product_data 		= $this->get_product_from_order_item($orderItem);

				if (empty($product_data)) {

					$deleted_products[] = $orderItem->get_name();
					continue;
				}

				if ($product_data->needs_shipping()) {

					$items[$item_id] 	= array('data' => $product_data, 'quantity' => $orderItemQty);
				}
			} else {

				// If a product is in bundle product and it's also ordered individually in same order
				$items[$item_id]['quantity'] += $orderItemQty;
			}
		}

		// To support Mix and Match Product
		do_action('ph_ups_after_get_items_from_order', $order);

		if (!empty($deleted_products) && class_exists('WC_Admin_Meta_Boxes')) {

			WC_Admin_Meta_Boxes::add_error(__("UPS Warning! One or more Ordered Products have been deleted from the Order. Please check these Products- ", 'ups-woocommerce-shipping') . implode(',', $deleted_products) . '.');
		}

		//If no items exist in order $items won't be set
		$package['contents'] = isset($items) ? apply_filters('xa_ups_get_customized_package_items_from_order', $items, $order) : array();

		$package['destination'] = array(
			'country' 	=> !empty($to_address) ? $to_address['country'] : $order->get_shipping_country(),
			'state' 	=> !empty($to_address) ? $to_address['state'] : $order->get_shipping_state(),
			'postcode' 	=> !empty($to_address) ? $to_address['postcode'] : $order->get_shipping_postcode(),
			'city' 		=> !empty($to_address) ? $to_address['city'] : $order->get_shipping_city(),
			'address' 	=> !empty($to_address) ? $to_address['address_1'] : $order->get_shipping_address_1(),
			'address_2'	=> !empty($to_address) ? $to_address['address_2'] : $order->get_shipping_address_2()
		);

		// Skip Products
		if (!empty($this->settings['skip_products'])) {

			$package = PH_WC_UPS_Common_Utils::skip_products( $package, $this->settings['skip_products'], $label_generation, $this->debug);

			if (empty($package['contents'])) {

				return array();
			}
		}

		// Check for Minimum weight and maximum weight
		if (!empty($this->settings['min_weight_limit']) || !empty($this->settings['max_weight_limit'])) {

			$need_shipping = PH_WC_UPS_Common_Utils::check_min_weight_and_max_weight($package, $this->settings['min_weight_limit'], $this->settings['max_weight_limit'], $label_generation, $this->debug);

			if (!$need_shipping)	return array();
		}

		$ship_from_address  = $this->settings['ship_from_address'];

		$temp = PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, '_ph_ups_shipfrom_address_preference');

		if (isset($_GET['sfap']) && !empty($_GET['sfap'])) {

			$ship_from_address  = $_GET['sfap'];

			PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, '_ph_ups_shipfrom_address_preference', $_GET['sfap']);
		} elseif (!empty($temp)) {

			$ship_from_address  = $temp;
		}

		$packages	= apply_filters('wf_ups_filter_label_from_packages', array($package), $ship_from_address, $order_id);

		return $packages;
	}

	function ph_ups_generate_packages($ups_settings, $order_id = '', $auto_generate = false) {

		// Manual Package Generation
		if (empty($order_id)) {

			$query_string 		= explode('|', base64_decode($_GET['phupsgp']));
			$order_id 			= $query_string[1];
		}

		$order_object			= wc_get_order($order_id);
		$ph_metadata_handler	= new PH_UPS_WC_Storage_Handler($order_object);
		$order_items 			= '';

		//Setting to default			
		$ph_metadata_handler->ph_update_meta_data('_ph_ups_delivery_signature', 4);
		$ph_metadata_handler->ph_update_meta_data('_ph_ups_direct_delivery', '');
		$ph_metadata_handler->ph_update_meta_data('_ph_ups_direct_delivery', '');
		$ph_metadata_handler->ph_update_meta_data('_ph_ups_import_control', '');
		$ph_metadata_handler->ph_update_meta_data('_ph_ups_cod', '');
		$ph_metadata_handler->ph_update_meta_data('_ph_ups_eei_itn_number', '');
		$ph_metadata_handler->ph_update_meta_data('_ph_ups_eei_exemption_legend', '');
		$ph_metadata_handler->ph_update_meta_data('_ph_ups_vcid_number', '');
		$ph_metadata_handler->ph_update_meta_data('_ph_ups_vcid_consignee', '');
		$ph_metadata_handler->ph_update_meta_data('_ph_ups_eei_ultimate_consignee_type', '');

		//Shipfrom address preference set to default
		$ph_metadata_handler->ph_update_meta_data('_ph_ups_shipfrom_address_preference', '');
		$ph_metadata_handler->ph_update_meta_data('_ph_ups_shipment_label_type_as_forward', '');

		if ($order_object instanceof WC_Order) {

			// To support Mix and Match Product
			do_action('ph_ups_before_get_items_from_order', $order_object);

			$order_items		=	$order_object->get_items();
		}

		if (empty($order_items) && class_exists('WC_Admin_Meta_Boxes') && (is_admin() || !$auto_generate)) {

			WC_Admin_Meta_Boxes::add_error(__('UPS - No product Found.'));
			wp_redirect(admin_url('/post.php?post=' . $order_id . '&action=edit'));
			exit();
		}

		// To support Mix and Match Product
		do_action('ph_ups_after_get_items_from_order', $order_object);

		$fromCountry 	= $ups_settings['origin_country'];
		$toCountry 		= $order_object->get_shipping_country();
		$ship_options 	= [];

		// Delivery confirmation available at package level only for domestic shipments
		if (($fromCountry == $toCountry) && in_array($fromCountry, PH_WC_UPS_Constants::DC_DOMESTIC_COUNTRIES)) {

			$ship_options['delivery_confirmation_applicable']	= true;
			$ship_options['international_delivery_confirmation_applicable']	= false;
		} else {
			$ship_options['international_delivery_confirmation_applicable']	= true;
		}

		$package_data		=	$this->wf_get_package_data($order_object, $ship_options);

		if (empty($package_data)) {

			$package['Package']['PackagingType'] = array(
				'Code' => '02',
				'Description' => 'Package/customer supplied'
			);

			$package['Package']	=	array(
				'PackagingType'	=>	array(
					'Code'				=>	'02',
					'Description'	=>	'Package/customer supplied'
				),
				'Description'	=> 'Rate',
				'Dimensions'	=>	array(
					'UnitOfMeasurement'	=>	array(
						'Code'	=>	$ups_settings['dim_unit'],
					),
					'Length'	=>	0,
					'Width'		=>	0,
					'Height'	=>	0
				),
				'PackageWeight' => array(
					'UnitOfMeasurement'	=>	array(
						'Code'	=>	$ups_settings['weight_unit'],
					),
					'Weight'	=>	0
				),
				'PackageServiceOptions' => array(
					'InsuredValue'	=> array(
						'CurrencyCode'	=> $this->wcsups_rest->get_ups_currency(),
						'MonetaryValue'	=> 0
					)
				)
			);

			$ph_metadata_handler->ph_update_meta_data('_wf_ups_stored_packages', array($package));

			$package_data = $package;
		} else {

			$ph_metadata_handler->ph_update_meta_data('_wf_ups_stored_packages', $package_data);

			foreach ($package_data as $key => $value) {

				$ph_metadata_handler->ph_update_meta_data('_ph_ups_package_delivery_signature' . $key, '');
			}
		}

		//Generate label mannually when Automatic label generation is active
		if (!isset($_GET['phupsgp'])) {

			do_action('wf_after_package_generation', $order_id, $package_data);
		}

		$ph_metadata_handler->ph_save_meta_data();

		// Redirect Only if headers has not been already sent
		if (!headers_sent() && (is_admin() || !$auto_generate)) {

			if (isset($_GET['phupsgp'])) {

				wp_redirect(admin_url('/post.php?post=' . $order_id . '&action=edit#PH_UPS_Metabox'));
				exit;
			}

			return;
		}
	}

	/**
	 * Find and add additional error message.
	 *
	 * @param string
	 * @return string
	 */
	public function ph_error_notice_handle($error_code) {

		if (!class_exists('PH_UPS_Error_Notice_Handle')) {
			include(plugin_dir_path(dirname(__FILE__)) . 'ph-ups-error-notice-handle.php');
		}

		$error_handel = new PH_UPS_Error_Notice_Handle();

		return $error_handel->ph_find_error_additional_info($error_code);
	}

	function wf_ups_shipment_confirm($order_id = '', $auto_generate = false, $user_check = null) {

		if (!$this->wf_user_check($user_check)) {

			wp_die( esc_html__("You don't have admin privileges to view this page.", "ups-woocommerce-shipping"), '', array('back_link' => 1) );
		}

		// Check for active plugin license
		if (Ph_UPS_Woo_Shipping_Common::phHasActiveLicense()) {

			$api_access_details	= Ph_Ups_Endpoint_Dispatcher::phGetApiAccessDetails();

			if (!$api_access_details) {

				wp_die( esc_html__('Failed to get API access token', 'ups-woocommerce-shipping'), '', array('back_link' => 1));

			} 
		} else {
			
			wp_die( esc_html__('Please use a valid plugin license to continue using WooCommerce UPS Shipping Plugin with Print Label', 'ups-woocommerce-shipping'), '', array('back_link' => 1));
		}

		// Manual Label Generation
		if (empty($order_id)) {

			$query_string 	= explode('|', base64_decode($_GET['wf_ups_shipment_confirm']));
			$order_id 		= $query_string[1];
		}

		$order_object			= wc_get_order($order_id);
		$ph_metadata_handler	= new PH_UPS_WC_Storage_Handler($order_object);

		// Stop Label generation if label has been already generated
		$old_label_details = PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, 'ups_rest_created_shipments_details_array');

		if (!empty($old_label_details)) {

			WC_Admin_Meta_Boxes::add_error(__("UPS Label has been already generated.", 'ups-woocommerce-shipping'));
			exit;
		}

		$ups_settings 	= get_option('woocommerce_' . WF_UPS_ID . '_settings', null);

		if( empty( $this->settings )) {
			$settings_helper 	= new PH_WC_UPS_Settings_Helper();
			$this->settings  	= $settings_helper->settings;
		}
		
		$this->wcsups_rest  = new PH_Shipping_UPS_Rest();
		$this->debug		= $this->settings['debug'];

		$ups_settings 	= apply_filters('ph_ups_plugin_settings', $ups_settings, $order_id);

		$wf_ups_selected_service = isset($_GET['wf_ups_selected_service']) ? $_GET['wf_ups_selected_service'] : null;

		$ph_metadata_handler->ph_update_meta_data('wf_ups_selected_service', $wf_ups_selected_service);

		$cod				= isset($_GET['cod']) ? $_GET['cod'] : '';
		$sat_delivery		= isset($_GET['sat_delivery']) ? $_GET['sat_delivery'] : '';
		$is_return_label	= isset($_GET['is_return_label']) ? $_GET['is_return_label'] : '';
		
		$order_payment_method 	= $order_object->get_payment_method();

		if ($cod != 'true' && $order_payment_method == 'cod' && $auto_generate) {

			$cod = 'true';
		}

		if ($cod == 'true') {

			$ph_metadata_handler->ph_update_meta_data('_wf_ups_cod', true);
		} else {

			$ph_metadata_handler->ph_delete_meta_data('_wf_ups_cod');
		}

		if ($sat_delivery == 'true') {

			$ph_metadata_handler->ph_update_meta_data('_wf_ups_sat_delivery', true);
		} else {

			$ph_metadata_handler->ph_delete_meta_data('_wf_ups_sat_delivery');
		}


		if ($is_return_label == 'true') {

			$ups_return = true;
		} else {

			$ups_return = false;
		}

		if (isset($_GET['ups_export_compliance'])) {

			$ph_metadata_handler->ph_update_meta_data('_ph_ups_export_compliance', $_GET['ups_export_compliance']);
		} else {

			$ph_metadata_handler->ph_delete_meta_data('_ph_ups_export_compliance');
		}

		if (isset($_GET['ups_recipient_tin'])) {

			$ph_metadata_handler->ph_update_meta_data('ph_ups_shipping_tax_id_num', $_GET['ups_recipient_tin']);
		} else {

			$ph_metadata_handler->ph_delete_meta_data('ph_ups_shipping_tax_id_num');
		}

		if (isset($_GET['ups_shipto_recipient_tin'])) {

			$ph_metadata_handler->ph_update_meta_data('ph_ups_ship_to_tax_id_num', $_GET['ups_shipto_recipient_tin']);
		} else {

			$ph_metadata_handler->ph_delete_meta_data('ph_ups_ship_to_tax_id_num');
		}

		// Added a save meta after updating all the meta keys as it was missing.
		$ph_metadata_handler->ph_save_meta_data();

		$requests 	= $this->wf_ups_shipment_confirmrequest($order_object);
		$created_shipments_details_array 	= array();
		$created_shipments_json_request 	= array();
		$return_package_index 				= 0;

		foreach ($requests as $request) {

			if ($this->debug && !is_array($request)) {

				$request = wp_json_encode(Ph_UPS_Woo_Shipping_Common::convert_array_values_to_strings(json_decode($request,true)), JSON_UNESCAPED_SLASHES);

				echo '<div style="background: #eee;overflow: auto;padding: 10px;margin: 10px;">SHIPMENT CONFIRM REQUEST: ';
				echo '<xmp>' . $request . '</xmp></div>';

				// Dokan vendor dashboard order
				if (isset($_GET) && isset($_GET['dokan_dashboard']) && !empty($_GET['dokan_dashboard'])) {

					Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------ UPS LABEL CONFIRM REQUEST - DOKAN DASHBOARD ORDER ------------------------', $this->debug);
				} else {

					Ph_UPS_Woo_Shipping_Common::phAddDebugLog("------------------------ UPS LABEL CONFIRM REQUEST #$order_id ------------------------", $this->debug);
				}

				Ph_UPS_Woo_Shipping_Common::phAddDebugLog($request, $this->debug);
			}

			// Due to some error and request not available, But the error is not catched
			if (!$request && (is_admin() || !$auto_generate)) {

				wf_admin_notice::add_notice('Sorry. Something went wrong: please turn on debug mode to investigate more.');
				$this->wf_redirect(admin_url('/post.php?post=' . $order_id . '&action=edit'));
				exit;
			}

			$api_access_details = Ph_Ups_Endpoint_Dispatcher::phGetApiAccessDetails();
			$endpoint = PH_UPS_Config::PH_UPS_PROXY_API_BASE_URL . Ph_Ups_Endpoint_Dispatcher::ph_get_enpoint('shipment/confirmed');

			if (!is_array($request) && json_decode($request) !== null && $request == 'freight') {

				$response = wp_remote_post(
					$freight_endpoint,
					array(
						'timeout'   => 70,
						'body'      => $xml_request
					)
				);

			} elseif (is_array($request) && isset($request['service']) && $request['service'] == 'GFP') {

				// Creating array from JSON will create empty array for null values, replace with null
				if (isset($request['ShipmentRequest']['Shipment']['ShipmentRatingOptions'])) {

					if (isset($request['ShipmentRequest']['Shipment']['ShipmentRatingOptions']['NegotiatedRatesIndicator'])) {

						$request['ShipmentRequest']['Shipment']['ShipmentRatingOptions']['NegotiatedRatesIndicator'] = '';
					}

					if (isset($request['ShipmentRequest']['Shipment']['ShipmentRatingOptions']['FRSShipmentIndicator'])) {

						$request['ShipmentRequest']['Shipment']['ShipmentRatingOptions']['FRSShipmentIndicator'] = '';
					}
				}

				$response 		= '';
				$error_desc 	= '';

				$request_arr = wp_json_encode(Ph_UPS_Woo_Shipping_Common::convert_array_values_to_strings($request['request']), JSON_UNESCAPED_SLASHES);

				$response = Ph_Ups_Api_Invoker::phCallApi($endpoint, $api_access_details['token'], $request_arr);

				if ($this->debug) {

					echo '<div style="background: #eee;overflow: auto;padding: 10px;margin: 10px;">SHIPMENT CONFIRM REQUEST: ';
					echo '<xmp>' . print_r($request_arr, 1) . '</xmp></div>';
					echo '<div style="background:#ccc;background: #ccc;overflow: auto;padding: 10px;margin: 10px 10px 50px 10px;">SHIPMENT CONFIRM RESPONSE: ';
					echo '<xmp>' . print_r($response['body'], 1) . '</xmp></div>';

					// Dokan vendor dashboard order
					if (isset($_GET) && isset($_GET['dokan_dashboard']) && !empty($_GET['dokan_dashboard'])) {

						Ph_UPS_Woo_Shipping_Common::phAddDebugLog(__('------------------------UPS GFP Request - DOKAN DASHBOARD ORDER -------------------------------', 'ups-woocommerce-shipping'), $this->debug);
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog(htmlspecialchars(wp_json_encode($request)), $this->debug);
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog(__('------------------------UPS GFP Response - DOKAN DASHBOARD ORDER -------------------------------', 'ups-woocommerce-shipping'), $this->debug);
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog(htmlspecialchars($response['body']), $this->debug);


						Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------ UPS GFP LABEL CONFIRM REQUEST - DOKAN DASHBOARD ORDER ------------------------', $this->debug);
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog(htmlspecialchars(wp_json_encode($request)), $this->debug);
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------ UPS GFP LABEL CONFIRM RESPONSE - DOKAN DASHBOARD ORDER ------------------------', $this->debug);
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog(htmlspecialchars($response['body']), $this->debug);
					} else {

						Ph_UPS_Woo_Shipping_Common::phAddDebugLog(__("------------------------UPS GFP Request #$order_id -------------------------------", 'ups-woocommerce-shipping'), $this->debug);
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog(htmlspecialchars(wp_json_encode($request)), $this->debug);
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog(__("------------------------UPS GFP Response #$order_id -------------------------------", 'ups-woocommerce-shipping'), $this->debug);
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog(htmlspecialchars($response['body']), $this->debug);

						Ph_UPS_Woo_Shipping_Common::phAddDebugLog("------------------------ UPS GFP LABEL CONFIRM REQUEST #$order_id ------------------------", $this->debug);
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog(htmlspecialchars(wp_json_encode($request)), $this->debug);
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog("------------------------ UPS GFP LABEL CONFIRM RESPONSE #$order_id ------------------------", $this->debug);
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog(htmlspecialchars($response['body']), $this->debug);
					}
				}

				$created_shipments_details = array();
				$response = json_decode($response['body']);

				if (
					!empty($response)
					&& isset($response->ShipmentResponse->Response)
					&& isset($response->ShipmentResponse->Response->ResponseStatus)
					&& isset($response->ShipmentResponse->Response->ResponseStatus->Description)
					&& $response->ShipmentResponse->Response->ResponseStatus->Description == 'Success'
				) {

					$shipment_id 									= (string)$response->ShipmentResponse->ShipmentResults->ShipmentIdentificationNumber;
					// $created_shipments_details["ShipmentDigest"] 	= (string)$response->ShipmentResponse->ShipmentResults->ShipmentDigest;

					$created_shipments_details = json_decode($response['body'], true);

					// Only for single packages.
					$created_shipments_details = $this->normalize_if_single_package($created_shipments_details);
					$created_shipments_details['GFP'] 				= true;

					$created_shipments_details_array[$shipment_id] 	= $created_shipments_details;
					$created_shipments_json_request[$shipment_id] 	= $request;
				} else {

					if (is_admin() || !$auto_generate) {

						$error_desc = $response->response->errors[0]->message;

						wf_admin_notice::add_notice($error_desc);

						$this->wf_redirect(admin_url('/post.php?post=' . $order_id . '&action=edit'));
						exit;
					}
				}

				continue;
			} else {

				$request_arr = wp_json_encode(Ph_UPS_Woo_Shipping_Common::convert_array_values_to_strings(json_decode($request, true)), JSON_UNESCAPED_SLASHES);

				$response = Ph_Ups_Api_Invoker::phCallApi($endpoint, $api_access_details['token'], $request_arr);

			}

			if ( $this->debug && is_array($response)) {

				echo '<div style="background:#ccc;background: #ccc;overflow: auto;padding: 10px;margin: 10px 10px 50px 10px;">SHIPMENT CONFIRM RESPONSE: ';
				echo '<xmp>' . print_r($response['body'], 1) . '</xmp></div>';

				// Dokan vendor dashboard order
				if (isset($_GET) && isset($_GET['dokan_dashboard']) && !empty($_GET['dokan_dashboard'])) {

					Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------ UPS SHIPMENT LABEL CONFIRM RESPONSE - DOKAN DASHBOARD ORDER ------------------------', $this->debug);
				} else {

					Ph_UPS_Woo_Shipping_Common::phAddDebugLog("------------------------ UPS SHIPMENT LABEL CONFIRM RESPONSE #$order_id ------------------------", $this->debug);
				}

				Ph_UPS_Woo_Shipping_Common::phAddDebugLog($response['body'], $this->debug);
			}

			if (is_wp_error($response)) {

				$error_message = $response->get_error_message();

				if (is_admin() || !$auto_generate) {

					wf_admin_notice::add_notice('Sorry. Something went wrong: ' . $error_message);

					$this->wf_redirect(admin_url('/post.php?post=' . $order_id . '&action=edit'));
					exit;
				}
			}

			$req_arr = array();

			if (!is_array($request)) {

				$req_arr = json_decode($request);
			}

			// For Freight Shipments  as it is JSON not Array
			if (!is_array($request) && isset($req_arr->FreightShipRequest)) {

				try {

					$var = json_decode($response['body']);

					if (!empty($var->Fault)) {

						Ph_UPS_Woo_Shipping_Common::phAddDebugLog("------------------------ UPS FREIGHT LABEL ERROR #$order_id ------------------------", $this->debug);

						if (is_array($var->Fault->detail->Errors->ErrorDetail)) {

							foreach ($var->Fault->detail->Errors->ErrorDetail as $index => $error_details) {

								WC_Admin_Meta_Boxes::add_error($error_details->PrimaryErrorCode->Description);

								Ph_UPS_Woo_Shipping_Common::phAddDebugLog($error_details->PrimaryErrorCode->Description, $this->debug);
							}
						} else {

							WC_Admin_Meta_Boxes::add_error($var->Fault->detail->Errors->ErrorDetail->PrimaryErrorCode->Description);

							Ph_UPS_Woo_Shipping_Common::phAddDebugLog($var->Fault->detail->Errors->ErrorDetail->PrimaryErrorCode->Description, $this->debug);
						}

						if (is_admin() || !$auto_generate) {

							$this->wf_redirect(admin_url('/post.php?post=' . $order_id . '&action=edit'));
							exit;
						}
					}
				} catch (Exception $e) {

					if (is_admin() || !$auto_generate) {

						$this->wf_redirect(admin_url('/post.php?post=' . $order_id . '&action=edit'));
						exit;
					}
				}

				$created_shipments_details 	= array();
				$shipment_id 				= (string)$var->FreightShipResponse->ShipmentResults->ShipmentNumber;

				$created_shipments_details["ShipmentDigest"] 	= (string)$var->FreightShipResponse->ShipmentResults->ShipmentNumber;
				$created_shipments_details["BOLID"] 			= (string)$var->FreightShipResponse->ShipmentResults->BOLID;
				$created_shipments_details["type"] 				= "freight";

				try {

					$img = '';

					if (
						isset($var->FreightShipResponse->ShipmentResults->Documents)
						&& isset($var->FreightShipResponse->ShipmentResults->Documents->Image)
						&& isset($var->FreightShipResponse->ShipmentResults->Documents->Image->GraphicImage)
					) {

						$img = (string)$var->FreightShipResponse->ShipmentResults->Documents->Image->GraphicImage;
					}
				} catch (Exception $ex) {

					$img = '';
				}

				$created_shipments_details_array[$shipment_id] = $created_shipments_details;

				$ph_metadata_handler = $this->wf_ups_freight_accept_shipment($img, $shipment_id, $created_shipments_details["BOLID"], $order_id, $ph_metadata_handler);
			} else {

				// 403 Access Forbidden
				if (!empty($response['response']['errors'][0]['code']) && $response['response']['errors'][0]['code'] == 403) {

					if (is_admin() || !$auto_generate) {

						wf_admin_notice::add_notice($response['response']['errors'][0]['message'] . " You don't have permission to access https://wwwcie.ups.com/api/shipments/v1/ship on this server [Error Code: 403]");

						$this->wf_redirect(admin_url('/post.php?post=' . $order_id . '&action=edit'));
						exit;
					}
				}

				$response_obj = json_decode($response['body']);

				$error_response_code = (string)!empty($response_obj->response->errors) ? $response_obj->response->errors[0]->code : '';

				if ($error_response_code) {

					$error_code = (string)$response_obj->response->errors[0]->code;
					$error_desc = (string)$response_obj->response->errors[0]->message;

					$additional_info = $this->ph_error_notice_handle($error_code);

					Ph_UPS_Woo_Shipping_Common::phAddDebugLog("------------------------ UPS SHIPMENT LABEL ERROR #$order_id ------------------------", $this->debug);
					Ph_UPS_Woo_Shipping_Common::phAddDebugLog($error_desc . strip_tags($additional_info), $this->debug);

					if (is_admin() || !$auto_generate) {

						wf_admin_notice::add_notice($error_desc . ' [Error Code: ' . $error_code . ']' . $additional_info);

						$this->wf_redirect(admin_url('/post.php?post=' . $order_id . '&action=edit'));
						exit;
					}
				}

				$created_shipments_details 	= array();
				$shipment_id 				= isset($response_obj->ShipmentResponse->ShipmentResults->ShipmentIdentificationNumber) ? (string)$response_obj->ShipmentResponse->ShipmentResults->ShipmentIdentificationNumber : '';

				if (!empty($shipment_id)) {

					//@note "ShipmentDigest" Node not present
					// $created_shipments_details["ShipmentDigest"] 	= (string)$response_obj->ShipmentDigest;

					$created_shipments_details = json_decode($response['body'], true);

					// Only for single packages.
					$created_shipments_details = $this->normalize_if_single_package($created_shipments_details);

					$created_shipments_details_array[$shipment_id] 	= $created_shipments_details;
					$created_shipments_json_request[$shipment_id] 	= $request;

					//@note Need to handle the 'Insured Value' part ( There is no node for it in REQUEST) 

					// Add the Insurance in Order Note
					// if (strstr($request, 'InsuredValue') != false) {

					// 	$request = explode('</AccessRequest>', $request);
					// 	$xml = simplexml_load_string($request[1]);

					// 	$insuredvalue = "";

					// 	if (
					// 		isset($xml->Shipment->Package->PackageServiceOptions)
					// 		&& isset($xml->Shipment->Package->PackageServiceOptions->InsuredValue)
					// 		&& isset($xml->Shipment->Package->PackageServiceOptions->InsuredValue->MonetaryValue)
					// 	) {

					// 		$insuredvalue = (string)$xml->Shipment->Package->PackageServiceOptions->InsuredValue->MonetaryValue;
					// 	}

					// 	if (!empty($insuredvalue)) {

					// 		$order_object->add_order_note(__("UPS Package with Tracking Id #$shipment_id is Insured.", "ups-woocommerce-shipping"));
					// 	}
					// }

					// Creating Return Label
					if ($ups_return) {

						$return_label = $this->wf_ups_return_shipment_confirm($shipment_id, $return_package_index);

						if (!empty($return_label)) {

							$created_shipments_details_array[$shipment_id]['return'] = $return_label;
						}
					}
				}
			}

			$return_package_index++;
		}

		//@note Not able to update REQUEST array as it is beyond the size of key
		$ph_metadata_handler->ph_update_meta_data('ups_rest_created_shipments_details_array', $created_shipments_details_array);
		
		// Saving individually to support sending the label to the vendor via email.(Multi Vendor Addon)
		PH_UPS_WC_Storage_Handler::ph_add_and_save_meta_data($order_id, 'ups_created_shipments_json_request_array', $created_shipments_json_request);

		//@note Forming Shipment response needed for metabox after confirm shipment. Since REST doesn't have 'SHIPMENT DIGEST'
		$ups_created_shipments_details_array = $this->create_rest_shipment_response($created_shipments_details_array);
		$ph_metadata_handler->ph_update_meta_data('ups_rest_label_details_array', $ups_created_shipments_details_array);

		//@note
		$ph_metadata_handler = $this->ups_accept_shipment($order_id, $ph_metadata_handler, $created_shipments_details_array);
		$ph_metadata_handler->ph_save_meta_data();

		if (is_admin() || !$auto_generate) {
			$this->wf_redirect(admin_url('/post.php?post=' . $order_id . '&action=edit'));
			exit;
		}
	}

	function wf_ups_freight_accept_shipment($img, $shipment_id, $BOLID, $order_id, $ph_metadata_handler) {
		// Since their is no accept shipment method for freigth we will skip it 
		$ups_label_details["TrackingNumber"]		= $BOLID;
		$ups_label_details["Code"] 					= "PDF";
		$ups_label_details["GraphicImage"] 			= $img;
		$ups_label_details["Type"] 					= "FREIGHT";
		$ups_label_details_array[$shipment_id][]	= $ups_label_details;

		do_action('wf_label_generated_successfully', $shipment_id, $order_id, $ups_label_details["Code"], "0", $ups_label_details["TrackingNumber"], $ups_label_details);

		$old_ups_label_details_array 	= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, 'ups_rest_created_shipments_details_array');

		if (empty($old_ups_label_details_array)) {

			$old_ups_label_details_array = $ups_label_details_array;
		} else {

			$old_ups_label_details_array[$shipment_id][] = $ups_label_details;
		}

		$ph_metadata_handler->ph_update_meta_data('ups_rest_created_shipments_details_array', $old_ups_label_details_array);

		wf_admin_notice::add_notice('Order #' . $order_id . ': Shipment accepted successfully. Labels are ready for printing.', 'notice');

		return $ph_metadata_handler;
	}

	function wf_ups_return_shipment_confirm($parent_shipment_id, $return_package_index) {

		if (!$this->wf_user_check()) {

			wp_die( esc_html__("You don't have admin privileges to view this page.", "ups-woocommerce-shipping"), '', array('back_link' => 1) );
		}

		// Check for active plugin license
		if (Ph_UPS_Woo_Shipping_Common::phHasActiveLicense()) {

			$api_access_details	= Ph_Ups_Endpoint_Dispatcher::phGetApiAccessDetails();

			if (!$api_access_details) {

				wp_die( esc_html__('Failed to get API access token', 'ups-woocommerce-shipping'), '', array('back_link' => 1));

			} 
		} else {
			
			wp_die( esc_html__('Please use a valid plugin license to continue using WooCommerce UPS Shipping Plugin with Print Label', 'ups-woocommerce-shipping'), '', array('back_link' => 1));
		}

		$query_string 	= isset($_GET['wf_ups_shipment_confirm']) ? explode('|', base64_decode($_GET['wf_ups_shipment_confirm'])) : '';

		// xa_generate_return_label is set when return label is generated after generating the label, contains order id
		$order_id 		= !empty($_GET['xa_generate_return_label']) ? $_GET['xa_generate_return_label'] : $query_string[1];
		$order_object	= wc_get_order($order_id);

		// true for return label, false for general shipment, default is false	
		$requests 		= $this->wf_ups_shipment_confirmrequest($order_object, true);

		if (!$requests) return;

		if ("Live" == $this->settings['api_mode']) {

			$endpoint = 'https://onlinetools.ups.com/ups.app/xml/ShipConfirm';
		} else {

			$endpoint = 'https://wwwcie.ups.com/api/shipments/v1/ship?additionaladdressvalidation=string';
		}

		$created_shipments_details_array = array();

		foreach ($requests as $key => $request) {

			if ($key !== $return_package_index) continue;

			if ($this->debug) {

				$request = wp_json_encode(Ph_UPS_Woo_Shipping_Common::convert_array_values_to_strings(json_decode($request,true)), JSON_UNESCAPED_SLASHES);

				echo '<div style="background: #eee;overflow: auto;padding: 10px;margin: 10px;">RETURN SHIPMENT CONFIRM REQUEST: ';

				echo '<xmp>' . print_r($request, 1) . '</xmp></div>';

				// Dokan vendor dashboard order
				if (isset($_GET) && isset($_GET['dokan_dashboard']) && !empty($_GET['dokan_dashboard'])) {

					Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------ UPS RETURN SHIPMENT CONFIRM REQUEST - DOKAN DASHBOARD ORDER ------------------------', $this->debug);
				} else {

					Ph_UPS_Woo_Shipping_Common::phAddDebugLog("------------------------ UPS RETURN SHIPMENT CONFIRM REQUEST #$order_id ------------------------", $this->debug);
				}

				Ph_UPS_Woo_Shipping_Common::phAddDebugLog($request, $this->debug);
			}

			$request_arr = wp_json_encode(Ph_UPS_Woo_Shipping_Common::convert_array_values_to_strings(json_decode($request, true)), JSON_UNESCAPED_UNICODE);

			$api_access_details = Ph_Ups_Endpoint_Dispatcher::phGetApiAccessDetails();
			$endpoint = Ph_Ups_Endpoint_Dispatcher::ph_get_enpoint('shipment/confirmed');

			$response = Ph_Ups_Api_Invoker::phCallApi(
				PH_UPS_Config::PH_UPS_PROXY_API_BASE_URL . $endpoint,
				$api_access_details['token'],
				$request_arr
			);

			if ($this->debug) {

				echo '<div style="background:#ccc;background: #ccc;overflow: auto;padding: 10px;margin: 10px 10px 50px 10px;">RETURN SHIPMENT CONFIRM RESPONSE: ';
				echo '<xmp>' . print_r($response['body'], 1) . '</xmp></div>';

				// Dokan vendor dashboard order
				if (isset($_GET) && isset($_GET['dokan_dashboard']) && !empty($_GET['dokan_dashboard'])) {

					Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------ UPS RETURN SHIPMENT CONFIRM RESPONSE - DOKAN DASHBOARD ORDER ------------------------', $this->debug);
				} else {

					Ph_UPS_Woo_Shipping_Common::phAddDebugLog("------------------------ UPS RETURN SHIPMENT CONFIRM RESPONSE #$order_id ------------------------", $this->debug);
				}

				Ph_UPS_Woo_Shipping_Common::phAddDebugLog($response['body'], $this->debug);
			}

			if (is_wp_error($response) && is_object($response)) {

				$error_message = $response->get_error_message();
				$error_message = 'Return Label - ' . $error_message;

				wf_admin_notice::add_notice('Sorry. Something went wrong: ' . $error_message);

				$this->wf_redirect(admin_url('/post.php?post=' . $order_id . '&action=edit'));
				exit;
			}

			$response_obj 	= json_decode($response['body'], true);

			if (isset($response_obj['response']['errors'])) {

				$error_code = (string)$response_obj['response']['errors'][0]['code'];
				$error_desc = (string)$response_obj['response']['errors'][0]['message'];

				$additional_info = $this->ph_error_notice_handle($error_code);

				if ($this->debug) {

					Ph_UPS_Woo_Shipping_Common::phAddDebugLog("------------------------ UPS RETURN SHIPMENT ERROR #$order_id ------------------------", $this->debug);
					Ph_UPS_Woo_Shipping_Common::phAddDebugLog($error_desc . $additional_info, $this->debug);
				}

				$error_desc = 'Return Label - ' . $error_desc;

				wf_admin_notice::add_notice($error_desc . ' [Error Code: ' . $error_code . ']' . $additional_info);

				$this->wf_redirect(admin_url('/post.php?post=' . $order_id . '&action=edit'));

				exit;
			}

			$created_shipments_details = array();

			// Only for single packages.
			$created_shipments_details = $this->normalize_if_single_package($response_obj);

			$shipment_id = (string)$response_obj['ShipmentResponse']['ShipmentResults']['ShipmentIdentificationNumber'];
			$created_shipments_details = array($shipment_id => $created_shipments_details);
			$created_shipments_details_array = $created_shipments_details;
		}

		return $created_shipments_details_array;
	}

	private function wf_redirect($url = '') {
		if (!$url) {

			return false;
		}

		if (!$this->debug) {

			wp_redirect($url);
		}

		exit();
	}

	function wf_ups_return_shipment_accept($order_id, $shipment_data) {

		if (!$this->wf_user_check()) {

			wp_die( esc_html__("You don't have admin privileges to view this page.", "ups-woocommerce-shipping"), '', array('back_link' => 1) );
		}

		// Check for active plugin license
		if (Ph_UPS_Woo_Shipping_Common::phHasActiveLicense()) {

			$api_access_details	= Ph_Ups_Endpoint_Dispatcher::phGetApiAccessDetails();

			if (!$api_access_details) {

				wp_die( esc_html__('Failed to get API access token', 'ups-woocommerce-shipping'), '', array('back_link' => 1));

			} 
		} else {
			
			wp_die( esc_html__('Please use a valid plugin license to continue using WooCommerce UPS Shipping Plugin with Print Label', 'ups-woocommerce-shipping'), '', array('back_link' => 1));
		}

		// Load UPS Settings.
		$ups_settings				= apply_filters('ph_ups_plugin_settings', $this->settings, $order_id);

		$order_object			= wc_get_order($order_id);
		$ph_metadata_handler	= new PH_UPS_WC_Storage_Handler($order_object);

		foreach ($shipment_data as $shipment_id => $created_shipments_details) {

			$package_results 	= $created_shipments_details['ShipmentResponse']['ShipmentResults']['PackageResults'];
			$shipment_id_cs 	= '';

			// Labels for each package.
			$ups_label_details_array = PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, 'ups_return_label_details_array');

			if (empty($ups_label_details_array)) {

				$ups_label_details_array = array();
			}

			if (isset($created_shipments_details['ShipmentResponse']['ShipmentResults']['Form']['Image'])) {

				$international_forms[$shipment_id]	= array(

					'ImageFormat'	=>	(string)$created_shipments_details['ShipmentResponse']['ShipmentResults']['Form']['Image']['ImageFormat']['Code'],
					'GraphicImage'	=>	(string)$created_shipments_details['ShipmentResponse']['ShipmentResults']['Form']['Image']['GraphicImage'],
				);
			}

			$index = 0;

			foreach ($package_results as $package_result) {
				$ups_label_details["TrackingNumber"]	= (string)$package_result['TrackingNumber'];
				$ups_label_details["Code"] 				= (string)$package_result['ShippingLabel']['ImageFormat']['Code'];
				$ups_label_details["GraphicImage"] 		= (string)$package_result['ShippingLabel']['GraphicImage'];

				if (!empty($package_result['ShippingLabel']['HTMLImage'])) {

					$ups_label_details["HTMLImage"] 	= (string)$package_result['ShippingLabel']['HTMLImage'];
				}

				$ups_label_details_array[$shipment_id][] = $ups_label_details;
				$shipment_id_cs 						.= $ups_label_details["TrackingNumber"] . ',';

				do_action('wf_label_generated_successfully',  $shipment_id, $order_id, $ups_label_details["Code"], $index, $ups_label_details["TrackingNumber"], $ups_label_details, true);

				$index++;
			}

			$shipment_id_cs = rtrim($shipment_id_cs, ',');

			if (empty($ups_label_details_array)) {

				wf_admin_notice::add_notice('UPS: Sorry, An unexpected error occurred while creating return label.');
				return false;
			} else {

				$ph_metadata_handler->ph_update_meta_data('ups_return_label_details_array', $ups_label_details_array);

				if (isset($international_forms)) {

					$ph_metadata_handler->ph_update_meta_data('ups_return_commercial_invoice_details', $international_forms);
				}

				
				return $shipment_id_cs;
			}
			
			$ph_metadata_handler->ph_save_meta_data();
			break; // Only one return shipment is allowed
			return false;
		}
	}

	function wf_ups_void_shipment() {

		if (!$this->wf_user_check()) {

			wp_die( esc_html__("You don't have admin privileges to view this page.", "ups-woocommerce-shipping"), '', array('back_link' => 1) );
		}

		// Check for active plugin license
		if (Ph_UPS_Woo_Shipping_Common::phHasActiveLicense()) {

			$api_access_details	= Ph_Ups_Endpoint_Dispatcher::phGetApiAccessDetails();

			if (!$api_access_details) {

				wp_die( esc_html__('Failed to get API access token', 'ups-woocommerce-shipping'), '', array('back_link' => 1));

			} 
		} else {
			
			wp_die( esc_html__('Please use a valid plugin license to continue using WooCommerce UPS Shipping Plugin with Print Label', 'ups-woocommerce-shipping'), '', array('back_link' => 1));
		}

		$query_string				= explode('|', base64_decode($_GET['wf_ups_void_shipment']));
		$order_id 					= $query_string[0];
		$order_object				= wc_get_order($order_id);
		$ph_metadata_handler		= new PH_UPS_WC_Storage_Handler($order_object);
		$ups_label_details_array 	= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, 'ups_rest_label_details_array');

		if (empty($ups_label_details_array)) {
			$ups_label_details_array 	= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, 'ups_label_details_array');
		}

		// Load UPS Settings.
		$ups_settings 		= get_option('woocommerce_' . WF_UPS_ID . '_settings', null);
		$ups_settings		= apply_filters('ph_ups_plugin_settings', $ups_settings, $order_id);

		// API Settings
		$api_mode		    = isset($ups_settings['api_mode']) ? $ups_settings['api_mode'] : 'Test';
		$this->debug		= isset($ups_settings['debug']) && 'yes' === $ups_settings['debug'] ? true : false;

		$client_side_reset 			= false;

		if (isset($_GET['client_reset'])) {

			$client_side_reset = true;
		}

		if (!empty($ups_label_details_array) && !$client_side_reset) {

			foreach ($ups_label_details_array as $shipmentId => $ups_label_detail_arr) {

				if (isset($ups_label_detail_arr[0]['GFP']) && $ups_label_detail_arr[0]['GFP']) {

					$contextvalue = apply_filters('ph_ups_update_customer_context_value', $order_id);

					foreach ($ups_label_detail_arr as $ups_label_details) {

						$tracking_numbers_arr[] = $ups_label_details['TrackingNumber'];
					}

					$tracking_numbers_string = '["' . implode('","', $tracking_numbers_arr) . '"]';

					$api_access_details = Ph_Ups_Endpoint_Dispatcher::phGetApiAccessDetails();

					$endpoint = Ph_Ups_Endpoint_Dispatcher::ph_get_enpoint('shipment/cancel');

					$body = [
						'shipmentId' => $shipmentId
					];

					$response = Ph_Ups_Api_Invoker::phCallApi(
						PH_UPS_Config::PH_UPS_PROXY_API_BASE_URL . $endpoint,
						$api_access_details['token'],
						$body
					);

					if(is_wp_error($response) && is_object($response)) {
						$error_message = $response->get_error_message();
					}

					// Redirect to Dokan dashboard when void shipment is done from Dokan dashboard
					if (isset($_GET) && isset($_GET['dokan_dashboard'])) {

						$dashboardId 	= dokan_get_option('dashboard', 'dokan_pages');
						$url 			= esc_url(get_permalink($dashboardId) . 'orders/');
						$dokanUrl		= html_entity_decode(esc_url(wp_nonce_url(add_query_arg(array('order_id' => $order_id, 'void_error' => true), $url), 'dokan_view_order')));
						$log 			= wc_get_logger();

						Ph_UPS_Woo_Shipping_Common::phAddDebugLog(__("------------------------UPS GFP Void Request - DOKAN DASHBOARD ORDER -------------------------------", 'ups-woocommerce-shipping'), $this->debug);
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog($endpoint, $this->debug);
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog(__("------------------------UPS GFP Void Response - DOKAN DASHBOARD ORDER -------------------------------", 'ups-woocommerce-shipping'), $this->debug);
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog($response, $this->debug);

						wp_redirect($dokanUrl);
						exit;
					} else {

						$message 			= '<strong>' . $error_message . ' </strong>';

						$current_page_uri	= $_SERVER['REQUEST_URI'];
						$href_url 			= $current_page_uri . '&client_reset';

						$message .= 'Please contact UPS to void/cancel this shipment. <br/>';
						$message .= 'If you have already cancelled this shipment by calling UPS customer care, and you would like to create shipment again then click <a class="button button-primary tips" href="' . $href_url . '" data-tip="Client Side Reset">Client Side Reset</a>';
						$message .= '<p style="color:red"><strong>Note: </strong>Previous shipment details and label will be removed from Order page.</p>';

						if ("Test" == $this->settings['api_mode']) {

							$message .= "<strong>Also, noticed that you have enabled 'Test' mode.<br/>Please note that void is not possible in 'Test' mode, as there is no real shipment is created with UPS. </strong><br/>";
						}

						wf_admin_notice::add_notice($message);

						Ph_UPS_Woo_Shipping_Common::phAddDebugLog(__("------------------------UPS GFP Void Request -------------------------------", 'ups-woocommerce-shipping'), $this->debug);
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog($endpoint, $this->debug);
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog(__("------------------------UPS GFP Void Response -------------------------------", 'ups-woocommerce-shipping'), $this->debug);
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog($response, $this->debug);

						wp_redirect(admin_url('/post.php?post=' . $order_id . '&action=edit'));
						exit;
					}

					if (isset($_GET) && isset($_GET['dokan_dashboard'])) {

						Ph_UPS_Woo_Shipping_Common::phAddDebugLog(__("------------------------UPS GFP Void Request - DOKAN DASHBOARD ORDER -------------------------------", 'ups-woocommerce-shipping'), $this->debug);
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog($endpoint, $this->debug);

						Ph_UPS_Woo_Shipping_Common::phAddDebugLog(__("------------------------UPS GFP Void Response - DOKAN DASHBOARD ORDER -------------------------------", 'ups-woocommerce-shipping'), $this->debug);
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog($response, $this->debug);
					} else {

						Ph_UPS_Woo_Shipping_Common::phAddDebugLog(__("------------------------UPS GFP Void Request for the Order #" . $order_id . "-------------------------------", 'ups-woocommerce-shipping'), $this->debug);
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog($endpoint, $this->debug);

						Ph_UPS_Woo_Shipping_Common::phAddDebugLog(__("------------------------UPS GFP Void Response for the Order #" . $order_id . "-------------------------------", 'ups-woocommerce-shipping'), $this->debug);
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog($response, $this->debug);
					}

					continue;
				} else {

					$contextvalue = apply_filters('ph_ups_update_customer_context_value', $order_id);

					foreach ($ups_label_detail_arr as $ups_label_details) {

						$tracking_numbers_arr[] = $ups_label_details['TrackingNumber'];
					}

					$tracking_numbers_string = '["' . implode('","', $tracking_numbers_arr) . '"]';

					//@note not sure what to do here with the below line of code

					// To support Vendor Addon
					// $xml_request	= apply_filters('xa_ups_void_shipment_xml_request', $xml_request, $shipmentId, $order_id);


					$api_access_details = Ph_Ups_Endpoint_Dispatcher::phGetApiAccessDetails();

					$endpoint = Ph_Ups_Endpoint_Dispatcher::ph_get_enpoint('shipment/cancelled');

					$body = [
						'shipmentId' => $shipmentId
					];

					$response = Ph_Ups_Api_Invoker::phCallApi(
						PH_UPS_Config::PH_UPS_PROXY_API_BASE_URL . $endpoint,
						$api_access_details['token'],
						json_encode($body)
					);


					if (isset($_GET) && isset($_GET['dokan_dashboard'])) {

						Ph_UPS_Woo_Shipping_Common::phAddDebugLog(__("------------------------Void Request - DOKAN DASHBOARD ORDER -------------------------------", 'ups-woocommerce-shipping'), $this->debug);
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog($body, $this->debug);

						Ph_UPS_Woo_Shipping_Common::phAddDebugLog(__("------------------------Void Response - DOKAN DASHBOARD ORDER -------------------------------", 'ups-woocommerce-shipping'), $this->debug);
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog($response, $this->debug);
					} else {

						Ph_UPS_Woo_Shipping_Common::phAddDebugLog(__("------------------------Void Request #$order_id -------------------------------", 'ups-woocommerce-shipping'), $this->debug);
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog($endpoint . $tracking_numbers_string, $this->debug);

						Ph_UPS_Woo_Shipping_Common::phAddDebugLog(__("------------------------Void Response #$order_id -------------------------------", 'ups-woocommerce-shipping'), $this->debug);
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog(is_array($response['body']) ? json_encode($response['body']) : $response['body'], $this->debug);
					}

					// In case of any issues with remote post.
					if (is_wp_error($response) && is_object($response)) {

						$error_message = $response->get_error_message(); 

						// Redirect to Dokan dashboard when void shipment is done from Dokan dashboard
						if (isset($_GET) && isset($_GET['dokan_dashboard'])) {

							$dashboardId 	= dokan_get_option('dashboard', 'dokan_pages');
							$url 			= esc_url(get_permalink($dashboardId) . 'orders/');
							$dokanUrl		= html_entity_decode(esc_url(wp_nonce_url(add_query_arg(array('order_id' => $order_id, 'void_error' => true), $url), 'dokan_view_order')));

							wp_redirect($dokanUrl);
							exit;
						} else {

							wf_admin_notice::add_notice('Sorry. Something went wrong: ' . $error_message);
							wp_redirect(admin_url('/post.php?post=' . $order_id . '&action=edit'));
							exit;
						}
					}

					$response_obj 	= json_decode($response['body'], true);

					// It is an error response
					if (isset($response_obj['response']['errors'])) {

						// Redirect to Dokan dashboard when void shipment is done from Dokan dashboard
						if (isset($_GET) && isset($_GET['dokan_dashboard'])) {

							$dashboardId 	= dokan_get_option('dashboard', 'dokan_pages');
							$url 			= esc_url(get_permalink($dashboardId) . 'orders/');
							$dokanUrl		= html_entity_decode(esc_url(wp_nonce_url(add_query_arg(array('order_id' => $order_id, 'void_error' => true), $url), 'dokan_view_order')));

							wp_redirect($dokanUrl);
							exit;
						} else {

							$error_code = (string)$response_obj['response']['errors'][0]['code'];
							$error_desc = (string)$response_obj['response']['errors'][0]['message'];

							$additional_info = $this->ph_error_notice_handle($error_code);

							$message 			= '<strong>' . $error_desc . ' [Error Code: ' . $error_code . ']' . '. </strong>' . $additional_info;
							$current_page_uri	= $_SERVER['REQUEST_URI'];
							$href_url 			= $current_page_uri . '&client_reset';

							$message .= 'Please contact UPS to void/cancel this shipment. <br/>';
							$message .= 'If you have already cancelled this shipment by calling UPS customer care, and you would like to create shipment again then click <a class="button button-primary tips" href="' . $href_url . '" data-tip="Client Side Reset">Client Side Reset</a>';
							$message .= '<p style="color:red"><strong>Note: </strong>Previous shipment details and label will be removed from Order page.</p>';

							if ("Test" == $api_mode) {

								$message .= "<strong>Also, noticed that you have enabled 'Test' mode.<br/>Please note that void is not possible in 'Test' mode, as there is no real shipment is created with UPS. </strong><br/>";
							}

							wf_admin_notice::add_notice($message);
							wp_redirect(admin_url('/post.php?post=' . $order_id . '&action=edit'));
							exit;
						}
					}

					$ph_metadata_handler = $this->wf_ups_void_return_shipment($order_id, $shipmentId, $ph_metadata_handler);
				}
			}
		}

		$empty_array = array();

		$ph_metadata_handler->ph_update_meta_data('ups_rest_created_shipments_details_array', $empty_array);
		$ph_metadata_handler->ph_update_meta_data('ups_rest_label_details_array', $empty_array);

		// Deleting old meta so that lables can be created with REST
		$ph_metadata_handler->ph_update_meta_data('ups_created_shipments_details_array', $empty_array);
		$ph_metadata_handler->ph_update_meta_data('ups_label_details_array', $empty_array);

		$ph_metadata_handler->ph_update_meta_data('ups_commercial_invoice_details', $empty_array);
		$ph_metadata_handler->ph_delete_meta_data('ups_dangerous_goods_image');
		$ph_metadata_handler->ph_update_meta_data('ph_ups_dangerous_goods_image', $empty_array);
		$ph_metadata_handler->ph_update_meta_data('ups_control_log_receipt', $empty_array);
		$ph_metadata_handler->ph_update_meta_data('ph_ups_dangerous_goods_manifest_data', $empty_array);
		$ph_metadata_handler->ph_update_meta_data('ph_ups_dangerous_goods_manifest_required', $empty_array);
		$ph_metadata_handler->ph_update_meta_data('wf_ups_selected_service', '');
		$ph_metadata_handler->ph_delete_meta_data('ups_shipment_ids');
		$ph_metadata_handler->ph_update_meta_data('ups_return_label_details_array', $empty_array);
		$ph_metadata_handler->ph_delete_meta_data('ups_return_shipment_details', $empty_array);
		$ph_metadata_handler->ph_update_meta_data('_ph_ups_delivery_signature', 4);
		$ph_metadata_handler->ph_update_meta_data('_ph_ups_direct_delivery', '');
		$ph_metadata_handler->ph_update_meta_data('_ph_ups_saturday_delivery', '');
		$ph_metadata_handler->ph_update_meta_data('_ph_ups_import_control', '');
		$ph_metadata_handler->ph_update_meta_data('_ph_ups_cod', '');
		$ph_metadata_handler->ph_update_meta_data('_ph_ups_eei_itn_number', '');
		$ph_metadata_handler->ph_update_meta_data('_ph_ups_eei_exemption_legend', '');
		$ph_metadata_handler->ph_update_meta_data('_ph_ups_vcid_number', '');
		$ph_metadata_handler->ph_update_meta_data('_ph_ups_vcid_consignee', '');
		$ph_metadata_handler->ph_update_meta_data('_ph_ups_eei_ultimate_consignee_type', '');
		$ph_metadata_handler->ph_update_meta_data('_ph_ups_shipping_address_as_sold_to', '');

		// Shipfrom address preference set to default
		$ph_metadata_handler->ph_update_meta_data('_ph_ups_shipfrom_address_preference', '');
		$ph_metadata_handler->ph_update_meta_data('_ph_ups_shipment_label_type_as_forward', '');

		$ph_metadata_handler->ph_save_meta_data();

		// Reset of stored meta elements done. Back to admin order page. 
		if ($client_side_reset) {

			// Redirect to Dokan vendor dashboard if reset by vendor
			if (isset($_GET) && isset($_GET['dokan_dashboard'])) {

				$dashboardId 	= dokan_get_option('dashboard', 'dokan_pages');
				$url 			= esc_url(get_permalink($dashboardId) . 'orders/');
				$dokanUrl		= html_entity_decode(esc_url(wp_nonce_url(add_query_arg(array('order_id' => $order_id), $url), 'dokan_view_order')));

				wp_redirect($dokanUrl);
				exit;
			} else {

				wf_admin_notice::add_notice('UPS: Client side reset of labels and shipment completed. You can re-initiate shipment now.', 'notice');

				wp_redirect(admin_url('/post.php?post=' . $order_id . '&action=edit'));
				exit;
			}
		}

		// Redirect to Dokan vendor dashboard if dahsboard order
		if (isset($_GET) && isset($_GET['dokan_dashboard'])) {

			$dashboardId 	= dokan_get_option('dashboard', 'dokan_pages');
			$url 			= esc_url(get_permalink($dashboardId) . 'orders/');
			$dokanUrl		= html_entity_decode(esc_url(wp_nonce_url(add_query_arg(array('order_id' => $order_id), $url), 'dokan_view_order')));

			wp_redirect($dokanUrl);
			exit;
		} else {

			wf_admin_notice::add_notice('UPS: Cancellation of shipment completed successfully. You can re-initiate shipment.', 'notice');

			wp_redirect(admin_url('/post.php?post=' . $order_id . '&action=edit'));
			exit;
		}
	}

	function wf_ups_void_return_shipment($order_id, $shipmentId, $ph_metadata_handler) {

		$ups_created_shipments_details_array = PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, 'ups_rest_created_shipments_details_array');

		if (is_array($ups_created_shipments_details_array) && isset($ups_created_shipments_details_array[$shipmentId]['return'])) {

			$return_shipment_id = current(array_keys($ups_created_shipments_details_array[$shipmentId]['return']));

			if ($return_shipment_id) {

				// Load UPS Settings
				$ups_settings 		= get_option('woocommerce_' . WF_UPS_ID . '_settings', null);
				$ups_settings		= apply_filters('ph_ups_plugin_settings', $ups_settings, $order_id);
				
				// API Settings
				$api_mode		    = isset($ups_settings['api_mode']) ? $ups_settings['api_mode'] : 'Test';

				$ups_return_label_details_array 	= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, 'ups_return_label_details_array');

				$contextvalue 	= apply_filters('ph_ups_update_customer_context_value', $order_id);

				if (!empty($ups_return_label_details_array) && $return_shipment_id) {

					foreach ($ups_return_label_details_array[$return_shipment_id] as $ups_return_label_details) {

						$tracking_numbers_arr[] = $ups_return_label_details['TrackingNumber'];
					}

					// Check for active plugin license
					if (Ph_UPS_Woo_Shipping_Common::phHasActiveLicense()) {

						$api_access_details = Ph_Ups_Endpoint_Dispatcher::phGetApiAccessDetails();
						
						if (!$api_access_details) {

							wf_admin_notice::add_notice('Failed to get API access token');
							return $ph_metadata_handler;
						}

						$endpoint = Ph_Ups_Endpoint_Dispatcher::ph_get_enpoint('shipment/cancelled');

						$body = wp_json_encode(
							array(
								'shipmentId'	=> $return_shipment_id
							)
						);

						$response = Ph_Ups_Api_Invoker::phCallApi(
							PH_UPS_Config::PH_UPS_PROXY_API_BASE_URL . $endpoint,
							$api_access_details['token'],
							$body
						);
	
					} else {

						wf_admin_notice::add_notice('Please use a valid plugin license to continue using WooCommerce UPS Shipping Plugin with Print Label');
						return $ph_metadata_handler;
					}

					// In case of any issues with remote post.
					if (is_wp_error($response) && is_object($response)) {

						$error_message = $response->get_error_message();

						wf_admin_notice::add_notice('Sorry. Something went wrong: ' . $error_message);
						return $ph_metadata_handler;
					}

					$response_obj = json_decode($response['body'], true);

					// It is an error response
					if (isset($response_obj['response']['errors'])) {

						$error_code = (string)$response_obj['response']['errors'][0]['code'];
						$error_desc = (string)$response_obj['response']['errors'][0]['message'];

						$additional_info = $this->ph_error_notice_handle($error_code);

						$message = '<strong>' . $error_desc . ' [Error Code: ' . $error_code . ']' . '. </strong>' . $additional_info;

						$current_page_uri	= $_SERVER['REQUEST_URI'];
						$href_url 			= $current_page_uri . '&client_reset';

						$message .= 'Please contact UPS to void/cancel this shipment. <br/>';
						$message .= 'If you have already cancelled this shipment by calling UPS customer care, and you would like to create shipment again then click <a class="button button-primary tips" href="' . $href_url . '" data-tip="Client Side Reset">Client Side Reset</a>';
						$message .= '<p style="color:red"><strong>Note: </strong>Previous shipment details and label will be removed from Order page.</p>';

						if ("Test" == $api_mode) {
							$message .= "<strong>Also, noticed that you have enabled 'Test' mode.<br/>Please note that void is not possible in 'Test' mode, as there is no real shipment is created with UPS. </strong><br/>";
						}

						wf_admin_notice::add_notice($message);
						return $ph_metadata_handler;
					}
				}

				$empty_array = array();

				$ph_metadata_handler->ph_update_meta_data('ups_return_label_details_array', $empty_array);
			}
		}

		return $ph_metadata_handler;
	}

	function wf_user_check($auto_generate = null) {
		$current_minute = (int)date('i');

		if (!empty($auto_generate) && ($auto_generate == md5($current_minute) || $auto_generate == md5($current_minute + 1))) {

			return true;
		}

		if (is_admin()) {

			return true;
		}

		return false;
	}

	function wf_get_shipping_service_data($order) {

		// TODO: Take the first shipping method. The use case of multiple shipping method for single order is not handled.

		$order_id 					= $order->get_id();
		$shipping_methods 			= $order->get_shipping_methods();
		$wf_ups_selected_service 	= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, 'wf_ups_selected_service');
		$shipping_service_tmp_data 	= array();

		if( empty( $this->settings )) {
			$settings_helper 	= new PH_WC_UPS_Settings_Helper();
			$this->settings  	= $settings_helper->settings;
		}
		
		if (!$shipping_methods) {

			$return_array = apply_filters('ph_shipping_method_array_filter', false, $order, PH_WC_UPS_Constants::UPS_SERVICE_CODES['US'], $this->settings, $this->settings['origin_country']);

			if ($return_array) {

				return $return_array;
			}
		}

		if (!empty($shipping_methods) && is_array($shipping_methods)) {

			$shipping_method			= array_shift($shipping_methods);

			if (self::$wc_version >= '3.0.0') $shipping_method_ups_meta 	= $shipping_method->get_meta('_xa_ups_method');

			$selected_service 			= !empty($shipping_method_ups_meta) ? $shipping_method_ups_meta['id'] : $shipping_method['method_id'];
			$shipping_service_tmp_data	= explode(':', $selected_service);
		}

		// If already tried to generate the label with any service
		if ('' != $wf_ups_selected_service) {

			$shipping_service_data['shipping_method'] 		= WF_UPS_ID;
			$shipping_service_data['shipping_service'] 		= $wf_ups_selected_service;
			$shipping_service_data['shipping_service_name']	= isset(PH_WC_UPS_Constants::UPS_SERVICE_CODES['US'][$wf_ups_selected_service]) ? PH_WC_UPS_Constants::UPS_SERVICE_CODES['US'][$wf_ups_selected_service] : '';

			// Customer Selected Service if UPS
		} elseif (!empty($shipping_service_tmp_data) && ( WF_UPS_ID || PH_WC_UPS_ZONE_SHIPPING ) == $shipping_service_tmp_data[0] && isset($shipping_service_tmp_data[1])) {

			$shipping_service_data = array(
				'shipping_method'		=>	WF_UPS_ID == $shipping_service_tmp_data[0] ? WF_UPS_ID : PH_WC_UPS_ZONE_SHIPPING,
				'shipping_service_name'	=>	WF_UPS_ID == $shipping_service_tmp_data[0] ? WF_UPS_ID : PH_WC_UPS_ZONE_SHIPPING,
				'shipping_service'		=>	$shipping_service_tmp_data[1],
			);

		} elseif ($this->is_domestic($order) && !empty($this->settings['default_dom_service'])) {

			$service_code = $this->settings['default_dom_service'];

			$shipping_service_data = array(
				'shipping_method' 		=> WF_UPS_ID,
				'shipping_service' 		=> $service_code,
				'shipping_service_name'	=> isset(PH_WC_UPS_Constants::UPS_SERVICE_CODES['US'][$service_code]) ? PH_WC_UPS_Constants::UPS_SERVICE_CODES['US'][$service_code] : '',
			);
		} elseif (!$this->is_domestic($order) && !empty($this->settings['default_int_service'])) {

			$service_code = $this->settings['default_int_service'];

			$shipping_service_data = array(
				'shipping_method' 		=> WF_UPS_ID,
				'shipping_service' 		=> $service_code,
				'shipping_service_name'	=> isset(PH_WC_UPS_Constants::UPS_SERVICE_CODES['US'][$service_code]) ? PH_WC_UPS_Constants::UPS_SERVICE_CODES['US'][$service_code] : '',
			);
		} else {

			$shipping_service_data['shipping_method'] 		= WF_UPS_ID;
			$shipping_service_data['shipping_service'] 		= '';
			$shipping_service_data['shipping_service_name']	= '';
		}

		return $shipping_service_data;
	}

	/**
	 * Check if the shipping address of an order is domestic.
	 *
	 * @param WC_Order $order The WooCommerce order object.
	 * @return bool True if the shipping address is domestic, false otherwise.
	 */
	private function is_domestic($order) {
		return ($order->get_shipping_country() == $this->settings['origin_country']);
	}

	public function get_dimension_from_package($package) {

		$dimensions	=	array(
			'Length'		=>	null,
			'Width'			=>	null,
			'Height'		=>	null,
			'Weight'		=>	null,
			'InsuredValue'	=>	null,
		);

		if (!isset($package['Package'])) {

			return $dimensions;
		}
		if (isset($package['Package']['Dimensions'])) {

			$dimensions['Length']	=	(string) round($package['Package']['Dimensions']['Length'], 2);
			$dimensions['Width']	=	(string) round($package['Package']['Dimensions']['Width'], 2);
			$dimensions['Height']	=	(string) round($package['Package']['Dimensions']['Height'], 2);
		}

		$weight		=	$package['Package']['PackageWeight']['Weight'];

		if ($package['Package']['PackageWeight']['UnitOfMeasurement']['Code'] == 'OZS') {

			// Make weight in pounds
			if ($this->settings['weight_unit'] == 'LBS') {

				$weight	=	$weight / 16;

				// To KG
			} else {
				$weight	=	$weight / 35.274;
			}
		}

		// PackageServiceOptions
		if (isset($package['Package']['PackageServiceOptions']['InsuredValue'])) {

			$dimensions['InsuredValue']	=	$package['Package']['PackageServiceOptions']['InsuredValue']['MonetaryValue'];
		}

		$dimensions['Weight']	=	(string) round($weight, 2);

		return $dimensions;
	}

	public function manual_packages($packages, $order) {

		if( empty( $this->settings )) {
			$settings_helper 	= new PH_WC_UPS_Settings_Helper();
			$this->settings  	= $settings_helper->settings;
		}

		$this->wcsups_rest  = new PH_Shipping_UPS_Rest( $order );
		$this->debug		= $this->settings['debug'];

		if (isset($_GET["package_key"])) {

			$package_indexes	= json_decode(stripslashes(html_entity_decode($_GET["package_key"])));

			if (!empty($package_indexes) && is_array($package_indexes)) {

				$final_packages = [];

				foreach ($package_indexes as $packages_index) {

					if (isset($packages[$packages_index])) {

						$final_packages[] = $packages[$packages_index];
					}
				}

				$packages = $final_packages;
			}
		}

		if (!isset($_GET['weight'])) {

			return $packages;
		}

		$order_id 		= $order->get_id();
		$length_arr		= json_decode(stripslashes(html_entity_decode($_GET["length"])));
		$width_arr		= json_decode(stripslashes(html_entity_decode($_GET["width"])));
		$height_arr		= json_decode(stripslashes(html_entity_decode($_GET["height"])));
		$weight_arr		= json_decode(stripslashes(html_entity_decode($_GET["weight"])));
		$insurance_arr	= json_decode(stripslashes(html_entity_decode($_GET["insurance"])));
		$service_arr	= json_decode(stripslashes(html_entity_decode($_GET["service"])));

		$no_of_package_entered	=	count($weight_arr);
		$no_of_packages			=	count($packages);

		// Populate extra packages, if entered manual values
		if ($no_of_package_entered > $no_of_packages) {

			// Get first package to clone default data
			$package_clone	= current($packages);
			$package_unit 	= (isset($package_clone['Package']) && isset($package_clone['Package']['PackageWeight']) && isset($package_clone['Package']['PackageWeight']['UnitOfMeasurement'])) ? $package_clone['Package']['PackageWeight']['UnitOfMeasurement']['Code'] : $this->settings['weight_unit'];

			for ($i = $no_of_packages; $i < $no_of_package_entered; $i++) {

				$packages[$i]	=	array(
					'Package'	=>	array(
						'PackagingType'	=>	array(
							'Code'	=>	'02',
							'Description'	=>	'Package/customer supplied',
						),
						'Description'	=>	'Rate',
						'PackageWeight'	=>	array(
							'UnitOfMeasurement'	=>	array(
								'Code'	=>	$package_unit,
							),
							'Weight'	=> '',
						),
					),
				);
			}
		}

		// Overridding package values
		foreach ($packages as $key => $package) {

			if (!isset($packages[$key]['Package']['Dimensions']) && isset($length_arr[$key]) && $length_arr[$key] !== "") {

				$packages[$key]['Package']['Dimensions'] = array();
				$packages[$key]['Package']['Dimensions']['UnitOfMeasurement']['Code'] = $this->settings['dim_unit'];
			}
			// If not available in GET then don't overwrite.
			if (isset($length_arr[$key]) && $length_arr[$key] !== "") {
				$packages[$key]['Package']['Dimensions']['Length']	=	(string) round($length_arr[$key], 2);
			}
			// If not available in GET then don't overwrite.
			if (isset($width_arr[$key]) && $width_arr[$key] !== "") {
				$packages[$key]['Package']['Dimensions']['Width']	=	(string) round($width_arr[$key], 2);
			}
			// If not available in GET then don't overwrite.
			if (isset($height_arr[$key]) && $height_arr[$key] !== "") {
				$packages[$key]['Package']['Dimensions']['Height']	=	(string) round($height_arr[$key], 2);
			}

			// If not available in GET then don't overwrite.
			if (isset($weight_arr[$key])) {

				$weight	=	isset($weight_arr[$key]) && !empty($weight_arr[$key]) ? $weight_arr[$key] : 0;

				// Surepost Less Than 1 LBS
				if (isset($service_arr[$key]) && $service_arr[$key] == 92) {
					$packages[$key]['Package']['PackageWeight']['UnitOfMeasurement']['Code'] =	'OZS';
				}

				if ($packages[$key]['Package']['PackageWeight']['UnitOfMeasurement']['Code'] == 'OZS') {

					// Make sure weight from pounds to ounces
					if ($this->settings['weight_unit'] == 'LBS') {

						$weight	=	$weight * 16;
					} else {
						// From KG to ounces
						$weight	=	$weight * 35.274;
					}
				}

				$packages[$key]['Package']['PackageWeight']['Weight']	=	(string) round($weight, 2);
			}

			// If not available in GET then don't overwrite.
			if (isset($insurance_arr[$key]) && $insurance_arr[$key] !== "") {

				if (!isset($packages[$key]['Package']['PackageServiceOptions'])) {
					$packages[$key]['Package']['PackageServiceOptions'] = array();
				}

				$packages[$key]['Package']['PackageServiceOptions']['InsuredValue'] = array();

				$packages[$key]['Package']['PackageServiceOptions']['InsuredValue']	= array(
					'CurrencyCode'	=>	$this->wcsups_rest->get_ups_currency(),
					'MonetaryValue'	=>	round($insurance_arr[$key], 2),
				);
			}

			$ship_from_address          = isset($this->settings['ship_from_address']) ? $this->settings['ship_from_address'] : 'origin_address';
			
			$billing_address_preference = $this->get_product_address_preference($order, $this->settings, false);

			if ( $ship_from_address == 'billing_address' && $billing_address_preference) {

				$from_address 	= $this->get_order_address($order);
				$to_address 	= $this->get_shop_address($order);
			} else {

				$from_address 	= $this->get_shop_address($order);
				$to_address 	= $this->get_order_address($order);
			}


			$edit_order_sig = json_decode(stripslashes(html_entity_decode($_GET['dc'])));
			$sig = 0;

			if ( !empty($edit_order_sig) ) {

				if( isset($package['Package']['items']) && !empty( $package['Package']['items'])) {
					
				$package_signature = isset($package['Package']['items']) ? PH_WC_UPS_Common_Utils::get_package_signature($package['Package']['items']) : '';
						
					if ( $edit_order_sig == 4) {
						$sig = $this->settings['ph_delivery_confirmation'] > $package_signature ? $this->settings['ph_delivery_confirmation'] : $package_signature;
					} else {
						$sig = $edit_order_sig;
					}
			
				} else {
			
					if ( $edit_order_sig == 4) {
						$sig = $this->settings['ph_delivery_confirmation'];
					} else {
						$sig = $edit_order_sig;
					}
			
				}
			
			
			}

			$sig = $sig == 3 ? 3 : ($sig > 0 ? 2 : 0);

			if ( !empty($sig) && (( $from_address['country'] == $to_address['country'] && in_array($from_address['country'], array('US','PR','CA'))) || (in_array($from_address['country'], array('US', 'PR')) && in_array($to_address['country'], array('US', 'PR')))) ) {

				$packages[$key]['Package']['PackageServiceOptions']['DeliveryConfirmation'] = array(
					'DCISType' => $sig
				);
			}
		}

		PH_UPS_WC_Storage_Handler::ph_add_and_save_meta_data($order_id, '_wf_ups_stored_packages', $packages);

		return $packages;
	}


	function split_shipment_by_services($ship_packages, $order, $return_label = false) {

		$shipments	=	array();

		// Check Ground with Freight Services Selected
		$is_service_code_US48 	= false;

		$order_id = $order->get_id();

		if (!isset($_GET['service'])) {

			if (isset($this->auto_label_generation) && $this->auto_label_generation && !empty($this->auto_label_services)) {

				foreach ($this->auto_label_services as $count => $service_code) {

					if (isset($ship_packages[$count])) {

						$shipment_arr[$service_code][]	=	$ship_packages[$count];
					}
				}

				foreach ($shipment_arr as $service_code => $packages) {

					if ($service_code == 'US48') {

						$is_service_code_US48 	= true;
					}

					$shipments[]	=	array(
						'shipping_service'	=>	$service_code,
						'packages'			=>	$packages,
					);
				}
			} else {

				$shipping_service_data	= $this->wf_get_shipping_service_data($order);
				$default_service_type 	= $shipping_service_data['shipping_service'];

				$default_service_code = '["' . $default_service_type . '"]';

				PH_UPS_WC_Storage_Handler::ph_add_and_save_meta_data($order_id, 'xa_ups_generated_label_services', $default_service_code);

				if ($default_service_type == 'US48') {

					$is_service_code_US48 	= true;
				}

				$shipments[]	=	array(
					'shipping_service'	=>	$default_service_type,
					'packages'			=>	$ship_packages,
				);
			}
		} else {

			// Services for return label if label has been generated previously
			if (!empty($_GET['xa_generate_return_label'])) {

				$service_arr = json_decode(stripslashes(html_entity_decode(base64_decode($_GET["rt_service"]))));

				// Services for label
			} else {

				$service_arr 	= json_decode(stripslashes(html_entity_decode($_GET["service"])));

				PH_UPS_WC_Storage_Handler::ph_add_and_save_meta_data($order_id, 'xa_ups_generated_label_services', $_GET["service"]);

				// Services for return label if it is being generated at the time of label creation only
				if ($return_label) {

					$service_arr 	= json_decode(stripslashes(html_entity_decode($_GET["rt_service"])));
				}
			}

			foreach ($service_arr as $count => $service_code) {

				if (isset($ship_packages[$count])) {

					$shipment_arr[$service_code][]	=	$ship_packages[$count];
				}
			}

			foreach ($shipment_arr as $service_code => $packages) {

				if ($service_code == 'US48') {

					$is_service_code_US48 	= true;
				}

				$shipments[]	=	array(
					'shipping_service'	=>	$service_code,
					'packages'			=>	$packages,
				);
			}
		}

		// Check Hazardous Materials in Package
		$is_hazardous_materials = false;

		foreach ($ship_packages as $count => $value) {

			foreach ($value as $packages_key => $package) {

				if (isset($package['items']) && !empty($package['items'])) {

					foreach ($package['items'] as $key => $items) {

						// Product Meta
						if (get_post_meta($items->get_id(), '_ph_ups_hazardous_materials', 1) == 'yes') {

							$is_hazardous_materials = true;
							break;
						}
					}

					if ($is_hazardous_materials) {

						break;
					}
				}
			}

			if ($is_hazardous_materials) {

				break;
			}
		}

		if ($is_hazardous_materials && $is_service_code_US48) {

			if (is_admin() || !$this->auto_label_generation) {

				wf_admin_notice::add_notice('HazMat Product can not be shipped using UPS Ground with Freight Pricing. Please select a valid service and try again.');

				wp_redirect(admin_url('/post.php?post=' . $order_id . '&action=edit'));
				exit;
			} else {

				Ph_UPS_Woo_Shipping_Common::phAddDebugLog("Automatic Label Generation Stopped #$order_id - HazMat Product can not be shipped using UPS Ground with Freight Pricing.", $this->debug);
				return;
			}
		}

		return $shipments;
	}

	/**
	 * Void Shipment - Bulk Actions
	 * 
	 * @param int $order_id
	 * 
	 * @return bool
	 */
	function ups_void_shipment($order_id) {

		$ups_label_details_array	=	$this->get_order_label_details($order_id);

		if (!$ups_label_details_array) {

			wf_admin_notice::add_notice('Order #' . $order_id . ': Shipment is not available.');
			return false;
		}

		// Check for active plugin license
		if (!Ph_UPS_Woo_Shipping_Common::phHasActiveLicense()) {

			wf_admin_notice::add_notice('Please use a valid plugin license to continue using WooCommerce UPS Shipping Plugin with Print Label');
			return false;
		}
		
		$ups_settings 		= get_option('woocommerce_' . WF_UPS_ID . '_settings', null);

		// Load UPS Settings
		$ups_settings			= apply_filters('ph_ups_plugin_settings', $ups_settings, $order_id);
		
		// API Settings
		$api_mode		      	= isset($ups_settings['api_mode']) ? $ups_settings['api_mode'] : 'Test';

		$order_object			= wc_get_order($order_id);
		$ph_metadata_handler	= new PH_UPS_WC_Storage_Handler($order_object);

		foreach ($ups_label_details_array as $shipmentId => $ups_label_detail_arr) {

			foreach ($ups_label_detail_arr as $ups_label_details) {

				$tracking_numbers_arr[] = $ups_label_details['TrackingNumber'];
			}

			$api_access_details = Ph_Ups_Endpoint_Dispatcher::phGetApiAccessDetails();
			$endpoint = Ph_Ups_Endpoint_Dispatcher::ph_get_enpoint('shipment/cancelled');

			$body = wp_json_encode(array(
				'shipmentId'	=> $shipmentId
			));

			$response = Ph_Ups_Api_Invoker::phCallApi(
				PH_UPS_Config::PH_UPS_PROXY_API_BASE_URL . $endpoint,
				$api_access_details['token'],
				$body
			);

			// In case of any issues with remote post.
			if (is_wp_error($response) && is_object($response)) {

				$error_message = $response->get_error_message();

				wf_admin_notice::add_notice('Order #' . $order_id . ': Sorry. Something went wrong: ' . $error_message);
				continue;
			}

			$response_obj 	= json_decode($response['body'], true);

			// It is an error response.
			if (isset($response_obj['response']['errors'])) {

				$error_code = (string)$response_obj['response']['errors'][0]['code'];
				$error_desc = (string)$response_obj['response']['errors'][0]['message'];

				$additional_info = $this->ph_error_notice_handle($error_code);

				$message = '<strong>' . $error_desc . ' [Error Code: ' . $error_code . ']' . '. </strong>' . $additional_info;

				$void_shipment_url = admin_url('/?wf_ups_void_shipment=' . base64_encode($order_id) . '&client_reset');
				$message .= 'Please contact UPS to void/cancel this shipment. <br/>';

				// For bulk void shipment we are clearing the data autometically

				$message .= 'If you have already cancelled this shipment by calling UPS customer care, and you would like to create shipment again then click <a class="button button-primary tips" href="' . $void_shipment_url . '" data-tip="Client Side Reset">Client Side Reset</a>';
				$message .= '<p style="color:red"><strong>Note: </strong>Previous shipment details and label will be removed from Order page.</p>';

				if ("Test" == $api_mode) {
					$message .= "<strong>Also, noticed that you have enabled 'Test' mode.<br/>Please note that void is not possible in 'Test' mode, as there is no real shipment is created with UPS. </strong><br/>";
				}

				wf_admin_notice::add_notice('Order #' . $order_id . ': ' . $message);
				return false;
			}

			$ph_metadata_handler = $this->wf_ups_void_return_shipment($order_id, $shipmentId, $ph_metadata_handler);
		}

		$ph_metadata_handler->ph_delete_meta_data('ups_rest_created_shipments_details_array');
		$ph_metadata_handler->ph_delete_meta_data('ups_rest_label_details_array');
		$ph_metadata_handler->ph_delete_meta_data('ups_commercial_invoice_details');
		$ph_metadata_handler->ph_delete_meta_data('ups_dangerous_goods_image');
		$ph_metadata_handler->ph_delete_meta_data('ph_ups_dangerous_goods_image');
		$ph_metadata_handler->ph_delete_meta_data('wf_ups_selected_service');
		$ph_metadata_handler->ph_delete_meta_data('ups_return_shipment_details');
		$ph_metadata_handler->ph_save_meta_data();

		wf_admin_notice::add_notice('Order #' . $order_id . ': Cancellation of shipment completed successfully. You can re-initiate shipment.', 'notice');

		return true;
	}

	function get_order_label_details($order_id) {

		$ups_label_details_array	= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, 'ups_rest_label_details_array');

		if (!empty($ups_label_details_array) && is_array($ups_label_details_array)) {

			return $ups_label_details_array;
		}

		return false;
	}

	/**
	 * Confirm Shipment - Bulk Actions
	 * 
	 * @param int $order_id
	 * 
	 * @return bool
	 */
	function ups_confirm_shipment($order_id) {

		// Check if shipment created already
		if ($this->get_order_label_details($order_id)) {

			wf_admin_notice::add_notice('Order #' . $order_id . ': Shipment is already created.', 'warning');
			return false;
		}

		if( empty( $this->settings )) {
			$settings_helper 	= new PH_WC_UPS_Settings_Helper();
			$this->settings  	= $settings_helper->settings;
		}

		$this->debug		= $this->settings['debug'];

		$order_object		= wc_get_order($order_id);
		$ph_metadata_handler = new PH_UPS_WC_Storage_Handler($order_object);
		$requests 			= $this->wf_ups_shipment_confirmrequest($order_object);

		$created_shipments_details_array 	= array();

		// Check for active plugin license
		if (Ph_UPS_Woo_Shipping_Common::phHasActiveLicense()) {

			$api_access_details 			= Ph_Ups_Endpoint_Dispatcher::phGetApiAccessDetails();

			if (!$api_access_details) {

				wf_admin_notice::add_notice('Failed to get API access token');
				return false;
			}

			$endpoint = Ph_Ups_Endpoint_Dispatcher::ph_get_enpoint('shipment/confirmed');
			$freight_endpoint 	= Ph_Ups_Endpoint_Dispatcher::ph_get_enpoint('shipment/freight');
		
		} else {

			wf_admin_notice::add_notice('Please use a valid plugin license to continue using WooCommerce UPS Shipping Plugin with Print Label');
			return false;
		}

		foreach ($requests as $request) {

			$xml_request = 'not freight';

			if (!is_array($request) && json_decode($request) !== null &&  $xml_request == 'freight') {

				$response = wp_remote_post(
					$freight_endpoint,
					array(
						'timeout'   => 70,
						'body'      => $xml_request
					)
				);
			} else if (is_array($request) && isset($request['service']) && $request['service'] == 'GFP') {

				// Creating array from JSON will create empty array for null values, replace with null
				if (isset($request['ShipmentRequest']['Shipment']['ShipmentRatingOptions'])) {

					if (isset($request['ShipmentRequest']['Shipment']['ShipmentRatingOptions']['NegotiatedRatesIndicator'])) {

						$request['ShipmentRequest']['Shipment']['ShipmentRatingOptions']['NegotiatedRatesIndicator'] = '';
					}

					if (isset($request['ShipmentRequest']['Shipment']['ShipmentRatingOptions']['FRSShipmentIndicator'])) {

						$request['ShipmentRequest']['Shipment']['ShipmentRatingOptions']['FRSShipmentIndicator'] = '';
					}
				}

				$request_arr = wp_json_encode(Ph_UPS_Woo_Shipping_Common::convert_array_values_to_strings($request['request']));

				$response = Ph_Ups_Api_Invoker::phCallApi(
					$endpoint,
					$api_access_details['token'],
					$request_arr
				);

			} else {

				$request_arr = wp_json_encode(Ph_UPS_Woo_Shipping_Common::convert_array_values_to_strings(json_decode($request, true)), JSON_UNESCAPED_SLASHES);

				$response = Ph_Ups_Api_Invoker::phCallApi(
					PH_UPS_Config::PH_UPS_PROXY_API_BASE_URL . $endpoint,
					$api_access_details['token'],
					$request_arr
				);
			}

			if (is_wp_error($response) && is_object($response)) {

				$error_message = $response->get_error_message();

				wf_admin_notice::add_notice('Order #' . $order_id . ': Sorry. Something went wrong: ' . $error_message);
				return false;
			}

			$req_arr = array();

			if (!is_array($request)) {

				$req_arr = json_decode($request);
			}

			if (
				!is_array($request)
				&& isset($req_arr->FreightShipRequest)
				&& isset($req_arr->FreightShipRequest->Shipment->Service->Code)
				&& in_array($req_arr->FreightShipRequest->Shipment->Service->Code, array_keys(PH_WC_UPS_Constants::FREIGHT_SERVICES))
			) {
				// For Freight Shipments  as it is JSON not Array
				try {

					$var = json_decode($response['body']);
					$pdf = $var->FreightShipResponse->ShipmentResults->Documents->Image->GraphicImage;
				} catch (Exception $e) {

					$this->wf_redirect(admin_url('/post.php?post=' . $order_id . '&action=edit'));
					exit;
				}

				$created_shipments_details = array();
				$shipment_id = (string)$var->FreightShipResponse->ShipmentResults->ShipmentNumber;

				$created_shipments_details["ShipmentDigest"] 	= (string)$var->FreightShipResponse->ShipmentResults->ShipmentNumber;
				$created_shipments_details["BOLID"] 			= (string)$var->FreightShipResponse->ShipmentResults->BOLID;
				$created_shipments_details["type"] 				= "freight";

				try {
					$img = (string)$var->FreightShipResponse->ShipmentResults->Documents->Image->GraphicImage;
				} catch (Exception $ex) {
					$img = '';
				}

				$created_shipments_details_array[$shipment_id] = $created_shipments_details;

				$ph_metadata_handler = $this->wf_ups_freight_accept_shipment($img, $shipment_id, $created_shipments_details["BOLID"], $order_id, $ph_metadata_handler);
			} else if (is_array($request) && isset($request['service']) && $request['service'] == 'GFP') {

				$created_shipments_details = array();

				$response_obj = json_decode($response['body']);

				$error_response_code = (string)!empty($response_obj->response->errors) ? $response_obj->response->errors[0]->code : '';

				if ($error_response_code) {

					$error_code = (string)$response_obj->response->errors[0]->code;
					$error_desc = (string)$response_obj->response->errors[0]->message;

					$additional_info = $this->ph_error_notice_handle($error_code);

					wf_admin_notice::add_notice('Order #' . $order_id . ': ' . $error_desc . ' [Error Code: ' . $error_code . ']' . $additional_info);

					return false;
				}

				$created_shipments_details = array();
				$shipment_id 				= isset($response_obj->ShipmentResponse->ShipmentResults->ShipmentIdentificationNumber) ? (string)$response_obj->ShipmentResponse->ShipmentResults->ShipmentIdentificationNumber : '';

				if (!empty($shipment_id)) {

					$created_shipments_details = json_decode($response['body'], true);

					// Only for single packages.
					$created_shipments_details = $this->normalize_if_single_package($created_shipments_details);
					$created_shipments_details['GFP']				= true;
					$created_shipments_details_array[$shipment_id] 	= $created_shipments_details;
				}
			} else {

				$response_obj = json_decode($response['body']);

				$error_response_code = (string)!empty($response_obj->response->errors) ? $response_obj->response->errors[0]->code : '';

				if ($error_response_code) {

					$error_code = (string)$response_obj->response->errors[0]->code;
					$error_desc = (string)$response_obj->response->errors[0]->message;

					$additional_info = $this->ph_error_notice_handle($error_code);

					wf_admin_notice::add_notice('Order #' . $order_id . ': ' . $error_desc . ' [Error Code: ' . $error_code . ']' . $additional_info);

					return false;
				}

				$created_shipments_details = array();
				$shipment_id 				= isset($response_obj->ShipmentResponse->ShipmentResults->ShipmentIdentificationNumber) ? (string)$response_obj->ShipmentResponse->ShipmentResults->ShipmentIdentificationNumber : '';

				if (!empty($shipment_id)) {

					$created_shipments_details = json_decode($response['body'], true);

					// Only for single packages.
					$created_shipments_details = $this->normalize_if_single_package($created_shipments_details);

					$created_shipments_details_array[$shipment_id] 	= $created_shipments_details;
				}
			}
		}

		//@note Forming Shipment response needed for metabox. Since REST doesn't have 'SHIPMENT DIGEST'
		$ups_created_shipments_details_array = $this->create_rest_shipment_response($created_shipments_details_array);

		$ph_metadata_handler->ph_update_meta_data('ups_rest_created_shipments_details_array', $created_shipments_details_array);

		$ph_metadata_handler->ph_save_meta_data();

		return true;
	}

	function ups_accept_shipment($order_id, $ph_metadata_handler = '', $shipment_details = []) {

		if (empty($ph_metadata_handler)) {

			$order_object			= wc_get_order($order_id);
			$ph_metadata_handler	= new PH_UPS_WC_Storage_Handler($order_object);
		}

		$created_shipments_details_array	= !empty($shipment_details) ? $shipment_details : PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, 'ups_rest_created_shipments_details_array');

		if (empty($created_shipments_details_array) && !is_array($created_shipments_details_array)) {

			return $ph_metadata_handler;
		}

		// Load UPS Settings.
		$ups_settings				= apply_filters('ph_ups_plugin_settings', $this->settings, $order_id);
		$ups_label_details_array 	= array();

		foreach ($created_shipments_details_array as $shipment_id	=>	$created_shipments_details) {

			if (isset($created_shipments_details['type']) && $created_shipments_details['type'] == 'freight') {

				continue;
			}

			if (isset($created_shipments_details['GFP']) && $created_shipments_details['GFP']) {
				
				$index 				= 0;
				$shipment_id_cs 	= '';
				$package_results 	= $created_shipments_details['ShipmentResponse']['ShipmentResults']['PackageResults'];

				// Save shimpment date and time in meta to use for document upload
				$ph_metadata_handler->ph_update_meta_data('_ups_shipment_date_time_stamp', date('Y-m-d-H.i.s'));

				if (!empty($package_results) && is_array($package_results)) {

					foreach ($package_results as $key => $package_result) {

						$ups_label_details						= array();
						$ups_label_details["TrackingNumber"]	= (string)$package_result['TrackingNumber'];
						$ups_label_details["Code"] 				= (string)$package_result['ShippingLabel']['ImageFormat']['Code'];
						$ups_label_details["GraphicImage"] 		= (string)$package_result['ShippingLabel']['GraphicImage'];

						if (isset($package_result['ShippingLabel']['HTMLImage'])) {

							$ups_label_details["HTMLImage"] 			= (string)$package_result['ShippingLabel']['HTMLImage'];
						}

						$ups_label_details["GFP"]					= true;
						$ups_label_details_array[$shipment_id][]	= $ups_label_details;
						$shipment_id_cs 							.= $ups_label_details["TrackingNumber"] . ',';

						do_action('wf_label_generated_successfully', $shipment_id, $order_id, $ups_label_details["Code"], (string)$index, $ups_label_details["TrackingNumber"], $ups_label_details);
					}

				} else {

					$ups_label_details						= array();
					$shipment_id_cs 						= '';
					$ups_label_details["TrackingNumber"]	= (string)$package_results['TrackingNumber'];
					$ups_label_details["Code"] 				= (string)$package_results['ShippingLabel']['ImageFormat']['Code'];
					$ups_label_details["GraphicImage"] 		= (string)$package_results['ShippingLabel']['GraphicImage'];

					if (isset($package_result['ShippingLabel']['HTMLImage'])) {

						$ups_label_details["HTMLImage"] 			= (string)$package_results['ShippingLabel']['HTMLImage'];
					}

					$ups_label_details["GFP"]					= true;
					$ups_label_details_array[$shipment_id][]	= $ups_label_details;
					$shipment_id_cs 							.= $ups_label_details["TrackingNumber"] . ',';

					do_action('wf_label_generated_successfully', $shipment_id, $order_id, $ups_label_details["Code"], (string)$index, $ups_label_details["TrackingNumber"], $ups_label_details);
				}
			} else {

				$response_obj = $created_shipments_details;

				// Save shimpment date and time in meta to use for document upload
				$ph_metadata_handler->ph_update_meta_data('_ups_shipment_date_time_stamp', date('Y-m-d-H.i.s'));

				$package_results 			= $response_obj['ShipmentResponse']['ShipmentResults']['PackageResults'];
				$ups_label_details			= array();
				$shipment_id_cs 			= '';

				if (isset($response_obj['ShipmentResponse']['ShipmentResults']['Form']['Image'])) {

					$international_forms[$shipment_id]	=	array(

						'ImageFormat'	=>	(string)$response_obj['ShipmentResponse']['ShipmentResults']['Form']['Image']['ImageFormat']['Code'],
						'GraphicImage'	=>	(string)$response_obj['ShipmentResponse']['ShipmentResults']['Form']['Image']['GraphicImage'],
					);
				}

				if (isset($response_obj['ShipmentResponse']['ShipmentResults']) && isset($response_obj['ShipmentResponse']['ShipmentResults']['DGPaperImage'])) {

					$DGPaper_image[$shipment_id]	=	array(

						'DGPaperImage'	=>	(string) ( is_array($response_obj['ShipmentResponse']['ShipmentResults']['DGPaperImage']) ? current($response_obj['ShipmentResponse']['ShipmentResults']['DGPaperImage']) : $response_obj['ShipmentResponse']['ShipmentResults']['DGPaperImage']),
					);
				}

				// Labels for each package.

				$index = 0;
				foreach ($package_results as $package_result) {

					$trackingNum 								= (string)$package_result['TrackingNumber'];
					$ups_label_details["TrackingNumber"]		= (isset($package_result['USPSPICNumber']) && ctype_digit($trackingNum)) ? (string) $package_result['USPSPICNumber'] : $trackingNum;
					$ups_label_details["Code"] 					= (string)$package_result['ShippingLabel']['ImageFormat']['Code'];
					$ups_label_details["GraphicImage"] 			= (string)$package_result['ShippingLabel']['GraphicImage'];

					if (!empty($package_result['ShippingLabel']['HTMLImage'])) {

						$ups_label_details["HTMLImage"] 		= (string)$package_result['ShippingLabel']['HTMLImage'];
					}
					$ups_label_details_array[$shipment_id][]	= $ups_label_details;
					$shipment_id_cs 							.= $ups_label_details["TrackingNumber"] . ',';

					do_action('wf_label_generated_successfully', $shipment_id, $order_id, $ups_label_details["Code"], (string)$index, $ups_label_details["TrackingNumber"], $ups_label_details);

					$index = $index + 1;
				}
			}

			$shipment_id_cs = rtrim($shipment_id_cs, ',');

			if (empty($ups_label_details_array)) {

				wf_admin_notice::add_notice('Order #' . $order_id . ': Sorry, An unexpected error occurred.');

				return $ph_metadata_handler;
			} else {

				$old_ups_label_details_array     = PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, 'ups_rest_label_details_array');

				if (empty($old_ups_label_details_array)) {

					$old_ups_label_details_array = $ups_label_details_array;
				} else {

					foreach ($ups_label_details_array as $shipment_id => $ups_label_details) {

						$old_ups_label_details_array[$shipment_id] = $ups_label_details;
					}
				}

				$ph_metadata_handler->ph_update_meta_data('ups_rest_label_details_array', $old_ups_label_details_array);


				if ($this->settings['dangerous_goods_manifest']) {

					$ph_metadata_handler = $this->ph_create_dangerous_goods_manifest($order_id, $ph_metadata_handler, $created_shipments_details_array);
				}

				if (isset($international_forms)) {

					$ph_metadata_handler->ph_update_meta_data('ups_commercial_invoice_details', $international_forms);
				}

				if (isset($DGPaper_image)) {

					// Update in custom meta table
					$ph_metadata_handler->ph_update_meta_data('ph_ups_dangerous_goods_image', $DGPaper_image);
				}

				// Creating return label
				if (isset($created_shipments_details['return']) && $created_shipments_details['return']) {

					$return_label_ids = $this->wf_ups_return_shipment_accept($order_id, $created_shipments_details['return']);

					if ($return_label_ids && $shipment_id_cs) {

						$shipment_id_cs = $shipment_id_cs . ',' . $return_label_ids;
					}
				}
			}

			if (isset($response_obj['ShipmentResponse']['ShipmentResults']['ControlLogReceipt']['ImageFormat']['Code'])) {

				$control_log_image_format = $response_obj['ShipmentResponse']['ShipmentResults']['ControlLogReceipt']['ImageFormat']['Code'];

				if ($control_log_image_format == "HTML") {

					$control_log_receipt[$shipment_id] = base64_decode($response_obj['ShipmentResponse']['ShipmentResults']['ControlLogReceipt']['GraphicImage']);

					$ph_metadata_handler->ph_update_meta_data('ups_control_log_receipt', $control_log_receipt);
				}
			}

			if ('True' != $this->settings['disble_shipment_tracking']) {

				// To support UPS Integration with Shipment Tracking
				do_action('ph_ups_shipment_tracking_detail_ids', $shipment_id_cs, $order_id);

				// Update Tracking Info
				$ups_tarcking	=	new WF_Shipping_UPS_Tracking();

				$ups_tarcking->get_shipment_info($order_id, $shipment_id_cs);
			}

			wf_admin_notice::add_notice('Order #' . $order_id . ': Shipment accepted successfully. Labels are ready for printing.', 'notice');
		}

		return $ph_metadata_handler;
	}

	public function ph_create_dangerous_goods_manifest($order_id, $ph_metadata_handler, $created_shipments_details_array) {

		$ups_label_details_array 			= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, 'ups_rest_label_details_array');
		$packages 							= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, '_wf_ups_stored_packages');

		$hazmat_products = array();
		$hazmat_package  = array();

		if (!empty($ups_label_details_array) && is_array($ups_label_details_array)) {

			foreach ($created_shipments_details_array as $shipmentId => $created_shipments_details) {

				$hazmat_products = array();

				if (!empty($ups_label_details_array[$shipmentId])) {

					foreach ($ups_label_details_array[$shipmentId] as $ups_label_details) {

						$tracking_number 	= isset($ups_label_details["TrackingNumber"]) ? $ups_label_details["TrackingNumber"] : '';

						if (is_array($packages)) {

							$package = array_shift($packages);

							$first_item_in_package = (isset($package['Package']['items']) && is_array($package['Package']['items'])) ? current($package['Package']['items']) : null;

							if (!empty($first_item_in_package)) {

								foreach ($package['Package']['items'] as $product) {

									$product_id 		= $product->get_id();
									$product_weight 	= wc_get_weight((!empty($product->get_weight()) ? $product->get_weight() : 0), $this->settings['weight_unit']);
									$hazmat_product 	= 'no';

									$product_var_id = '';

									if ($product->get_parent_id()) {
										$parent_id 	= $product->get_parent_id();
										$product_var_id = $product->get_id();
									} else {
										$parent_id 	= $product->get_id();
									}

									if (!empty($product_var_id)) {

										$hazmat_product 	= get_post_meta($product_var_id, '_ph_ups_hazardous_materials', 1);
										$hazmat_settings 	= get_post_meta($product_var_id, '_ph_ups_hazardous_settings', 1);
									}

									if ($hazmat_product != 'yes' && !empty($product_id)) {

										$hazmat_product 	= get_post_meta($product_id, '_ph_ups_hazardous_materials', 1);
										$hazmat_settings 	= get_post_meta($product_id, '_ph_ups_hazardous_settings', 1);
									}

									if ($hazmat_product == 'yes' && !empty($hazmat_settings) && is_array($hazmat_settings)) {

										if (isset($hazmat_products[$product_id])) {

											$hazmat_products[$product_id]['quantity']++;

											if ($hazmat_products[$product_id]['trackingNumber'] != $tracking_number) {

												$hazmat_products[$product_id]['trackingNumber'] 	.= ', ' . $tracking_number;
											}
											continue;
										}

										$transportationmode = array(
											'01' => 'Highway',
											'02' => 'Ground',
											'03' => 'PAX',
											'04' => 'CAO',
										);

										if (isset($hazmat_settings['_ph_ups_hm_transportaion_mode']) && array_key_exists($hazmat_settings['_ph_ups_hm_transportaion_mode'], $transportationmode)) {
											$mode = $transportationmode[$hazmat_settings['_ph_ups_hm_transportaion_mode']];
										}

										$idNumber 				= !empty($hazmat_settings['_ph_ups_commodity_id']) ? $hazmat_settings['_ph_ups_commodity_id'] : '';
										$properShippingName 	= !empty($hazmat_settings['_ph_ups_shipping_name']) ? $hazmat_settings['_ph_ups_shipping_name'] : '';
										$classDivisionNumber 	= !empty($hazmat_settings['_ph_ups_class_division_no']) ? $hazmat_settings['_ph_ups_class_division_no'] : '';
										$packagingGroupType 	= !empty($hazmat_settings['_ph_ups_package_group_type']) ? $hazmat_settings['_ph_ups_package_group_type'] : '';
										$packagingInstructionCode = !empty($hazmat_settings['_ph_ups_package_instruction_code']) ? $hazmat_settings['_ph_ups_package_instruction_code'] : '';
										$packagingType 			= !empty($hazmat_settings['_ph_ups_package_type']) ? $hazmat_settings['_ph_ups_package_type'] : '';
										$regulationSet 			= !empty($hazmat_settings['_ph_ups_hm_regulations']) ? $hazmat_settings['_ph_ups_hm_regulations'] : '';
										$transportationMode 	= $mode;
										$uom 					= ('LB' == $this->settings['uom']) ? 'pound' : 'kg';

										$hazmat_products[$product_id] = array(
											'productName'			=> $product->get_name(),
											'productWeight'			=> $product_weight,
											'trackingNumber'		=> $tracking_number,
											'commodityId'			=> $idNumber,
											'properShippingName'	=> $properShippingName,
											'classDivisionNumber'	=> $classDivisionNumber,
											'packagingGroupType'	=> $packagingGroupType,
											'packagingInstructionCode' => $packagingInstructionCode,
											'packagingType'			=> $packagingType,
											'regulationSet'			=> $regulationSet,
											'transportationMode'	=> $transportationMode,
											'uom'					=> $uom,
											'quantity' 				=> 1,
										);
									}
								}
							}
						}
					}
				}
				$hazmat_package[$shipmentId] = $hazmat_products;
			}
		}

		if (!empty($hazmat_package)) {

			$ph_metadata_handler->ph_update_meta_data('ph_ups_dangerous_goods_manifest_required', true);
			$ph_metadata_handler->ph_update_meta_data('ph_ups_dangerous_goods_manifest_data', $hazmat_package);
		}

		return $ph_metadata_handler;
	}

	/**
	 *  Generate return label if label has been created previously
	 */
	public function xa_generate_return_label() {

		$order_id 				= $_GET['xa_generate_return_label'];
		$order_object			= wc_get_order($order_id);
		$return_package_index 	= 0;
		$shipment_id_cs 		= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, 'ups_shipment_ids');
		$shipments 				= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, 'ups_rest_created_shipments_details_array');
		
		if( empty( $this->settings )) {
			$settings_helper 	= new PH_WC_UPS_Settings_Helper();
			$this->settings  	= $settings_helper->settings;
		}
		
		$this->wcsups_rest  = new PH_Shipping_UPS_Rest( $order_object );
		$this->debug		= $this->settings['debug'];

		// To support return label generation for orders 
		if (empty($shipments)) {
			$shipments 	= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, 'ups_created_shipments_details_array');
		}

		$ph_metadata_handler	= new PH_UPS_WC_Storage_Handler($order_object);

		if (!empty($shipments)) {
			// Confirm return shipment
			foreach ($shipments as $shipment_id => $shipment) {

				$return_label = $this->wf_ups_return_shipment_confirm($shipment_id, $return_package_index);

				if (!empty($return_label)) {
					$created_shipments_details_array[$shipment_id]['return'] = $return_label;
				}

				$return_package_index++;
			}

			$ph_metadata_handler->ph_update_meta_data('ups_rest_created_shipments_details_array', $created_shipments_details_array);

			// Accept Return Shipment
			foreach ($created_shipments_details_array as $shipment_id => $created_shipments_details) {

				if (!empty($created_shipments_details['return'])) {

					$return_label_ids = $this->wf_ups_return_shipment_accept($order_id, $created_shipments_details['return']);

					if ($return_label_ids) {

						$shipment_id_cs = $shipment_id_cs . ',' . $return_label_ids;
					}
				}
			}

			// To support UPS Integration with Shipment Tracking
			do_action('ph_ups_shipment_tracking_detail_ids', $shipment_id_cs, $order_id);

			// Update tracking info
			$ups_tarcking	=	new WF_Shipping_UPS_Tracking();

			$ups_tarcking->get_shipment_info($order_id, $shipment_id_cs);

			if ($this->debug) {

				exit();
			}
		}

		wp_redirect(admin_url('/post.php?post=' . $order_id . '&action=edit#PH_UPS_Metabox'));
	}

	// Check for any Product has Origin Address Preference, If 'yes' use Origin Address irrespective of Product and Settings
	public function get_product_address_preference($order, $ups_settings, $return_label = false) {

		$billing_address 	= true;

		if ($order instanceof WC_Order) {

			// To support Mix and Match Product
			do_action('ph_ups_before_get_items_from_order', $order);

			$order_items = $order->get_items();

			if (!empty($order_items)) {

				foreach ($order_items as  $item_key => $item_values) {

					$orderItemId 		= $item_values->get_id();
					$refundedItemCount	= $order->get_qty_refunded_for_item($orderItemId);

					$orderItemQty 		= $item_values->get_quantity() + $refundedItemCount;

					if ($orderItemQty <= 0) {

						continue;
					}

					$order_item_id = $item_values->get_variation_id();

					$product_id  = wp_get_post_parent_id($order_item_id);

					if (empty($product_id)) {

						$product_id = $item_values->get_product_id();
					}

					$default_to_origin  = get_post_meta($product_id, '_ph_ups_product_address_preference', 1);

					if ($default_to_origin == 'yes') {

						$billing_address = false;
						break;
					}
				}
			}

			// To support Mix and Match Product
			do_action('ph_ups_after_get_items_from_order', $order);
		}

		return $billing_address;
	}

	// Automatic Package Generation
	public function ph_ups_auto_generate_packages($order_id, $ups_settings, $minute = '') {

		// Check current time (minute) in Thank You Page for Automatic Package generation
		if (!$this->wf_user_check($minute)) {
			return;
		}

		$order_id 	= base64_decode($order_id);
		$order 		= wc_get_order($order_id);

		if (!($order instanceof WC_Order)) return;

		$this->ph_ups_generate_packages($ups_settings, $order_id, true);
	}

	// Automatic Label Generation
	public function ph_ups_auto_create_shipment($order_id, $ups_settings, $weight_arr, $length_arr, $width_arr, $height_arr, $service_arr, $insurance, $minute = '') {

		// Check current time (minute) in Thank You Page for Automatic Label generation
		$allowed_user = $this->wf_user_check($minute);

		if (!$allowed_user) {
			return;
		}

		$order 	= wc_get_order($order_id);
		$debug 	= ($bool = $ups_settings['debug']) && $bool == 'yes' ? true : false;

		if (!($order instanceof WC_Order)) return;

		$shipment_ids = PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, 'ups_rest_created_shipments_details_array');

		if (empty($shipment_ids)) {

			if (!empty($service_arr) && is_array($service_arr)) {
				$this->auto_label_generation 	= true;
				$this->auto_label_services 		= $service_arr;
			}

			$this->wf_ups_shipment_confirm($order_id, true, $minute);
		} else {

			if ($debug) {
				_e('UPS label generation Suspended. Label has been already generated.', 'ups-woocommerce-shipping');
			}
			if (class_exists('WC_Admin_Meta_Boxes')) {
				WC_Admin_Meta_Boxes::add_error('UPS label generation Suspended. Label has been already generated.', 'ups-woocommerce-shipping');
			}
		}
	}

	/**
	 * Check if the shipment details contain a single package, and normalize the structure if needed.
	 *
	 * @param array $created_shipments_details The shipment details to check.
	 *
	 * @return array The modified normalized package results structure.
	 */
	public function normalize_if_single_package($created_shipments_details) {

		$package_results = isset($created_shipments_details['ShipmentResponse']['ShipmentResults']['PackageResults']) ? $created_shipments_details['ShipmentResponse']['ShipmentResults']['PackageResults'] : $created_shipments_details;
		foreach ($package_results as $package_result) {
			if (!is_array($package_result)) {
				$package_results = array(0 => $package_results);
				$created_shipments_details['ShipmentResponse']['ShipmentResults']['PackageResults'] = $package_results;
				break;
			}
		}
		return $created_shipments_details;
	}

	/**
	 * Create a REST shipment response array.
	 *
	 * @param array $ups_label_details_array Array of UPS label details.
	 *
	 * @return array The UPS REST Shipment Response.
	 */
	public function create_rest_shipment_response($ups_label_details_array, $return_label = '') {

		$ups_rest_response = array();
		$key = 0;

		if (!empty($ups_label_details_array)) {
			foreach ($ups_label_details_array as $shipmentId => $created_shipment_details) {

				if (is_array($created_shipment_details) && isset($created_shipment_details['ShipmentResponse']['ShipmentResults']['PackageResults'])) {
					foreach ($created_shipment_details['ShipmentResponse']['ShipmentResults']['PackageResults'] as $package_result) {

						$tracking_number = isset($package_result['TrackingNumber']) ? $package_result['TrackingNumber'] : '';
						$code = isset($package_result['ShippingLabel']['ImageFormat']['Code']) ? $package_result['ShippingLabel']['ImageFormat']['Code'] : '';
						$graphic_img = isset($package_result['ShippingLabel']['GraphicImage']) ? $package_result['ShippingLabel']['GraphicImage'] : '';
						$html_img = isset($package_result['ShippingLabel']['HTMLImage']) ? $package_result['ShippingLabel']['HTMLImage'] : '';

						$ups_rest_response = array_merge($ups_rest_response, array(
							$key =>
							array(
								'TrackingNumber' => $tracking_number,
								'Code'			 => $code,
								'GraphicImage'	 => $graphic_img,
								'HTMLImage'		 =>	$html_img,
							),
						));
						$key = $key + 1;
					}
				}

				$ups_rest_response = array($shipmentId => $ups_rest_response);

				if ('return' === $return_label) {
					$ups_rest_response = array(
						$shipmentId => array('return' => $ups_rest_response),
					);
				}
				break;
			}
		}
		return $ups_rest_response;
	}
}
