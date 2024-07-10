<?php

/**
 * PH_Shipping_UPS class.
 *
 * @extends WC_Shipping_Method
 */
class PH_Shipping_UPS_Rest extends WC_Shipping_Method {

	public $vendorId = null;
	private $services;

	/**
	 * UPS Id
	 */
	public $id;
	/**
	 * General Settings
	 */
	public $settings, $instance_settings;
	/**
	 * Order
	 */
	public $order;
	/**
	 * Debug
	 */
	public $debug, $silent_debug;
	/**
	 * Address Variables
	 */
	public $destination;
	/**
	 * Package
	 */
	public $current_package_items_and_quantity;
	/**
	 * Unit Variables
	 */
	public $wc_weight_unit;
	/**
	 * Custom Fields Variables
	 */
	public $custom_services, $is_hazmat_product;
	/**
	 * Access Point
	 */
	public $ph_ups_selected_access_point_details;
	/**
	 * Est Delivery
	 */
	public $current_wp_time_hour_minute;
	/**
	 * Delivery Confirmation
	 */
	public $international_delivery_confirmation_applicable;
	/**
	 * Pickup Variables
	 */
	public $pickup_date, $pickup_time;

	/**
	 * Request option
	 */
	public $request_option;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct( $order = null, $instance_id = '' ) {

		if ($order) {
			$this->order = $order;
		}

		$this->instance_id = $instance_id;

		if( empty($this->instance_id) ) {
			$this->id		 =	WF_UPS_ID;
		} else {
			$this->id		 =	PH_WC_UPS_ZONE_SHIPPING;
		}

		// WF: Load UPS Settings.
		$settings 		 = get_option('woocommerce_' . WF_UPS_ID . '_settings', null);
		$settings		 = apply_filters('ph_ups_plugin_settings', $settings, $order);
		
		$settings_helper = new PH_WC_UPS_Settings_Helper();
		$this->settings  = $settings_helper->settings;

		if( !empty($this->instance_id) ) {

			$settings_helper = new PH_WC_UPS_Settings_Helper( $this->instance_id );
			$this->instance_settings  = $settings_helper->settings;
		}

		$this->init();
	}

	/**
	 * init function.
	 *
	 * @access public
	 * @return void
	 */
	private function init() {
		global $woocommerce;

		$this->services = PH_WC_UPS_Common_Utils::get_services_based_on_origin( $this->settings['origin_country'] );

		$this->custom_services = isset($this->instance_settings['services']) ? $this->instance_settings['services'] : $this->settings['services'];

		$this->debug 	 	= $this->settings['debug'];
		$this->silent_debug = $this->settings['silent_debug'];
	}

	public function calculate_lc_query_request($package) {

		//create soap request
		$action['RequestAction'] = 'LandedCost';
		$request['Request'] 	 = $action;
		$product 				 = [];

		$queryrequest = array(
			'Shipment' => array(
				'OriginCountryCode' 			=> $this->origin_country,
				'OriginStateProvinceCode' 		=> $this->origin_state,
				'DestinationCountryCode' 		=> $package['destination']['country'],
				'DestinationStateProvinceCode' 	=> $package['destination']['state'],
				'TransportationMode' 			=> '1',
				'ResultCurrencyCode' 			=> get_woocommerce_currency(),
			)
		);
		if (isset($package['contents']) && !empty($package['contents'])) {

			foreach ($package['contents'] as $product) {

				$total_item_count   = $product['quantity'];
				$unit_price  		= !empty($product['data']->get_price()) ? $product['data']->get_price() : $this->settings['fixedProductPrice'];
				$unit_weight 		= round(wc_get_weight((!empty($product['data']->get_weight()) ? $product['data']->get_weight() : 0), $this->settings['weight_unit'], 4));
				$product_id 		= isset($product['variation_id']) && !empty($product['variation_id']) ? $product['variation_id'] : $product['product_id'];
				$hst 				= get_post_meta($product_id, '_ph_ups_hst_var', true);

				if (empty($hst)) {

					$hst 	= get_post_meta($product_id, '_wf_ups_hst', true);
				}

				$this->hst_lc[] 	= $hst;

				$queryrequest['Shipment']['Product'][] = array(
					'TariffInfo' => array(
						'TariffCode' => $hst,
					),

					'Quantity' => array(
						'Value' => $total_item_count
					),

					'UnitPrice' => array(
						'MonetaryValue' => $unit_price,
						'CurrencyCode'  => get_woocommerce_currency(),
					),
					'Weight' => array(
						'Value' 		=> $unit_weight,
						'UnitOfMeasure' => array(
							'UnitCode' => $this->settings['uom']
						)
					)
				);
			}
		}
		$request['QueryRequest'] = $queryrequest;
		return $request;
	}

	public function calculate_lc_estimate_request($transaction_digest) {

		$action['RequestAction'] = 'LandedCost';
		$request['Request'] 	 = $action;
		$estimaterequest 		 = [];
		$estimaterequest['Shipment']['Product'] = [];

		foreach ($this->hst_lc as $hst) {

			$estimaterequest['Shipment']['Product'][] = array(
				'TariffCode' => $hst,
			);
		}

		$estimaterequest['TransactionDigest'] 	= $transaction_digest;
		$request['EstimateRequest'] 			= $estimaterequest;
		return $request;
	}

	/**
	 * Processes the UPS API response to extract shipping rates.
	 *
	 * @param string $ups_response The response from the UPS API, expected to be a JSON string.
	 * @param string $type Optional. The type of response being processed. Defaults to an empty string.
	 * @param array $items_and_quantities Optional. An array of items and their quantities for the shipment. Defaults to an empty array.
	 * @param int|null $vendorId Optional. The vendor ID associated with the shipment. Defaults to null.
	 * @param array $package Optional. An array representing the package details. Defaults to an empty array.
	 * @param string $zone_id Optional. The shipping zone ID. Defaults to an empty string.
	 *
	 * @return array An array of shipping rates, each containing rate details including ID, label, cost, sort order, and metadata.
	 */
	public function process_result($ups_response, $type = '', $items_and_quantities = array(), $vendorId = null, $package = array()) {

		$json = !empty( $ups_response ) ? json_decode($ups_response) : $ups_response;

		if (!$json) {
			Ph_UPS_Woo_Shipping_Common::debug(__('Failed loading REST', 'ups-woocommerce-shipping'), $this->debug, $this->silent_debug, 'error');
			return;
		}

		$rates = array();
		if ((property_exists($json, 'RateResponse') && $json->RateResponse->Response->ResponseStatus->Code == 1)  || ($type == 'json' && !property_exists($json, 'Fault'))) {

			$json = apply_filters('wf_ups_rate', $json, $package);
			$json_response = isset($json->RateResponse->RatedShipment) ? $json->RateResponse->RatedShipment : $json;

			if (is_object($json_response) && isset($json_response->Service->Code)) {
				$json_response = array($json_response);
			}

			foreach ($json_response as $response) {

				$code = (string)$response->Service->Code;

				if (!empty($this->custom_services[$code]) && $this->custom_services[$code]['enabled'] != 1) {		// For Freight service code custom services won't be set
					continue;
				}

				$service_name = isset($this->services[$code]) ? $this->services[$code] : '';
				
				if ($this->settings['negotiated'] && isset($response->NegotiatedRateCharges->TotalCharge->MonetaryValue)) {
					if (property_exists($response->NegotiatedRateCharges, 'TotalChargesWithTaxes')) {
						$rate_cost = (float) $response->NegotiatedRateCharges->TotalChargesWithTaxes->MonetaryValue;
					} else {
						$rate_cost = (float) $response->NegotiatedRateCharges->TotalCharge->MonetaryValue;
					}
				} else {
					if (property_exists($response, 'TotalChargesWithTaxes')) {
						$rate_cost = (float) $response->TotalChargesWithTaxes->MonetaryValue;
					} else {
						$rate_cost = (float) $response->TotalCharges->MonetaryValue;
					}
				}

				if( !empty( $this->instance_id ) ) {

					if( isset($this->instance_settings['shipping_method_instance_id']) && !empty( $this->instance_settings['shipping_method_instance_id']) ) {	
						$rate_id 	 = $this->id . ':' . $code . ':' . $this->instance_id;
					} else {
						$rate_id	 = $this->id . ':' . $code;
					}
				} else {

					$rate_id = $this->id . ':' . $code;
				}
				
				$rate_name   = $service_name;

				// Name adjustment
				if (!empty($this->custom_services[$code]['name']))
					$rate_name = $this->custom_services[$code]['name'];
				// Cost adjustment %, don't apply on order page rates
				if (!empty($this->custom_services[$code]['adjustment_percent']) && !isset($_GET['wf_ups_generate_packages_rates']))
					$rate_cost = $rate_cost + ($rate_cost * (floatval($this->custom_services[$code]['adjustment_percent']) / 100));
				// Cost adjustment, don't apply on order page rates
				if (!empty($this->custom_services[$code]['adjustment']) && !isset($_GET['wf_ups_generate_packages_rates']))
					$rate_cost = $rate_cost + floatval($this->custom_services[$code]['adjustment']);

				// Sort
				if (isset($this->custom_services[$code]['order'])) {
					$sort = $this->custom_services[$code]['order'];
				} else {
					$sort = 999;
				}

				$rate_cost =  $this->ph_ups_get_shipping_cost_after_conversion($rate_cost, $response);

				$rates[$rate_id] = array(
					'id' 	=> $rate_id,
					'label' => $rate_name,
					'cost' 	=> $rate_cost,
					'sort'  => $sort,
					'meta_data'	=> array(
						'_xa_ups_method'	=>	array(
							'id'			=>	$rate_id,	// Rate id will be in format WF_UPS_ID:service_id ex for ground wf_shipping_ups:03
							'method_title'	=>	$rate_name,
							'items'			=>	isset($items_and_quantities) ? $items_and_quantities : array(), // This is a parameter value from xml file
						),
						'VendorId'			=> !empty($vendorId) ? $vendorId : null,
					)
				);

				// Set Estimated delivery in rates meta data
				if ($this->settings['enable_estimated_delivery']) {
					
					$estimated_delivery = null;

					// Estimated delivery for freight
					if ($type == 'json' && isset($response->TimeInTransit->DaysInTransit)) {
						$days_in_transit 	= (string) $response->TimeInTransit->DaysInTransit;
						$current_time 		= clone $this->settings['current_wp_time'];
						if (!empty($days_in_transit))	$estimated_delivery = $current_time->modify("+$days_in_transit days");
					} // Estimated delivery for normal services
					elseif (!empty($response->TimeInTransit->ServiceSummary->EstimatedArrival->Arrival)) {
						$estimated_delivery_date = $response->TimeInTransit->ServiceSummary->EstimatedArrival->Arrival->Date; // Format YYYYMMDD, i.e Ymd
						$estimated_delivery_time = $response->TimeInTransit->ServiceSummary->EstimatedArrival->Arrival->Time; // Format His
						$estimated_delivery = date_create_from_format("Ymj His", $estimated_delivery_date . ' ' . $estimated_delivery_time);
					}

					if (!empty($estimated_delivery)) {
						if (empty($this->settings['wp_date_time_format'])) {
							$this->settings['wp_date_time_format'] = Ph_UPS_Woo_Shipping_Common::get_wordpress_date_format() . ' ' . Ph_UPS_Woo_Shipping_Common::get_wordpress_time_format();
						}

						$rates[$rate_id]['meta_data']['ups_delivery_time'] = apply_filters('ph_ups_estimated_delivery_customization', $estimated_delivery);
						if ($estimated_delivery instanceof DateTime) {

							if(!is_admin()) {
								$rates[$rate_id]['label']	= apply_filters('ph_ups_estimated_delivery_html', $rates[$rate_id]['label'], $this->settings['estimated_delivery_text'], $estimated_delivery);
							}
							$rates[$rate_id]['meta_data']['Estimated Delivery'] = $estimated_delivery->format($this->settings['wp_date_time_format']);
						}
					}
				}
			}
		}

		return $rates;
	}

	/**
	 * Get Shipping Cost after Conversion.
	 * @param int $rate_cost
	 * @return int $rate_cost
	 */
	public function ph_ups_get_shipping_cost_after_conversion($rate_cost, $json_response) {

		$rate_conversion = apply_filters('xa_conversion_rate', $this->settings['rate_conversion'], (isset( $json_response->TotalCharges->CurrencyCode ) ? (string)$json_response->TotalCharges->CurrencyCode : null));

		$rate_conversion = apply_filters('ph_vendor_conversion_rate', $this->settings['rate_conversion'], $this->vendorId, 'ups');

		$rate_cost *=  ((!empty($rate_conversion) && $rate_conversion > 0) ? $rate_conversion : 1);
		
		return $rate_cost;
	}

	// public function process_result_gfp($gfp_response, $items_and_quantities = array(), $vendorId = null, $type = '', $package = array()) {

	// 	$gfp_response = is_array($gfp_response) ? json_decode($gfp_response['body']) : '';

	// 	$rates = array();
	// 	if (!empty($gfp_response)) {

	// 		$gfp_response = isset($gfp_response->RateResponse->RatedShipment) ? $gfp_response->RateResponse->RatedShipment : '';	// Normal rates : freight rates

	// 		$gfp_response = apply_filters('ph_ups_gfp_rate_adjustment', $gfp_response, $package); // Additional cost Adjustment for Ground with Freight Services

	// 		$code = 'US48';
	// 		$service_name = $this->services[$code];
	// 		if ($this->negotiated && isset($gfp_response->RateResponse->RatedShipment->NegotiatedRateCharges->TotalCharge->MonetaryValue)) {
	// 			$rate_cost = (float) $gfp_response->RateResponse->RatedShipment->NegotiatedRateCharges->TotalCharge->MonetaryValue;
	// 		} else {
	// 			$rate_cost = (float) $gfp_response->RateResponse->RatedShipment->TotalCharges->MonetaryValue;
	// 		}


	// 		$rate_id	 = $this->id . ':' . $code;

	// 		$rate_name   = $service_name;
	// 		// Name adjustment
	// 		if (!empty($this->custom_services[$code]['name']))
	// 			$rate_name = $this->custom_services[$code]['name'];

	// 		// Cost adjustment %, don't apply on order page rates
	// 		if (!empty($this->custom_services[$code]['adjustment_percent']) && !isset($_GET['wf_ups_generate_packages_rates']))
	// 			$rate_cost = $rate_cost + ($rate_cost * (floatval($this->custom_services[$code]['adjustment_percent']) / 100));
	// 		// Cost adjustment, don't apply on order page rates
	// 		if (!empty($this->custom_services[$code]['adjustment']) && !isset($_GET['wf_ups_generate_packages_rates']))
	// 			$rate_cost = $rate_cost + floatval($this->custom_services[$code]['adjustment']);

	// 		// Sort
	// 		if (isset($this->custom_services[$code]['order'])) {
	// 			$sort = $this->custom_services[$code]['order'];
	// 		} else {
	// 			$sort = 999;
	// 		}

	// 		$rate_cost =  $this->ph_ups_get_shipping_cost_after_conversion($rate_cost);

	// 		$rates[$rate_id] = array(
	// 			'id' 	=> $rate_id,
	// 			'label' => $rate_name,
	// 			'cost' 	=> $rate_cost,
	// 			'sort'  => $sort,
	// 			'meta_data'	=> array(
	// 				'_xa_ups_method'	=>	array(
	// 					'id'			=>	$rate_id,	// Rate id will be in format WF_UPS_ID:service_id ex for ground wf_shipping_ups:03
	// 					'method_title'	=>	$rate_name,
	// 					'items'			=>	isset($items_and_quantities) ? $items_and_quantities : array(),
	// 				),
	// 				'VendorId'			=> !empty($vendorId) ? $vendorId : null,
	// 			)
	// 		);
	// 	}
	// 	return $rates;
	// }

	//function to get result for GFP
	// public function get_result_gfp($request, $request_type = '', $key = '', $orderId = '') {
	// 	$ups_response = null;
	// 	$send_request		   	= wp_json_encode(Ph_UPS_Woo_Shipping_Common::convert_array_values_to_strings($request), JSON_UNESCAPED_SLASHES);
	// 	$key++;

	// 	$headers = array(
	// 		// 'Authorization'	=>	BEARER_TOKEN,
	// 		'Content-Type'	=>	'application/json',
	// 		'transId'		=>	'string',
	// 		'transactionSrc' =>	'testing'
	// 	);

	// 	$freight_endpoint = ''; //@note GFP is not available within proxy

	// 	$ups_response = Ph_Ups_Api_Invoker::phCallApi($freight_endpoint, '', $send_request, $headers);

	// 	if (is_wp_error($ups_response) && is_object($ups_response)) {
	// 		$error_string = $ups_response->get_error_message();
	// 		$this->debug('UPS GFP REQUEST FAILED: <pre>' . print_r(htmlspecialchars($error_string), true) . '</pre>');
	// 	} else {

	// 		if ($this->debug) {
	
	// 			$orderId = !empty($orderId) ? '#' . $orderId : '';
	
	// 			$this->debug("UPS GFP REQUEST [ Package Set: " . $key . " | Max Packages: 50 ] <pre>" . print_r($send_request, true) . '</pre>');
	// 			$this->debug("UPS GFP RESPONSE [ Package Set: " . $key . "	| Max Packages: 50 ] <pre>" . print_r($ups_response, true) . '</pre>');
	
	// 			$this->diagnostic_report("------------------------ UPS GFP REQUEST [ Package Set: $key | Max Packages: 50 ] $orderId ------------------------");
	// 			$this->diagnostic_report($send_request);
	// 			$this->diagnostic_report("------------------------ UPS GFP RESPONSE [ Package Set: $key | Max Packages: 50 ] $orderId ------------------------");
	// 			$this->diagnostic_report($ups_response);
	// 		}
	// 	}


	// 	return $ups_response;
	// }


	//Landed Cost Result
	public function get_lc_result($request, $request_type) {
		// $ups_response 		= null;
		$ups_response 		= [];
		$exceptionMessage   = '';
		$api_mode	  		= isset($this->settings['api_mode']) ? $this->settings['api_mode'] : 'Test';
		$header 			= new stdClass();
		$header->UserId 	= $this->user_id;
		$header->Password 	= $this->password;
		$header->AccessLicenseNumber = $this->access_key;

		$wsdl = plugin_dir_path(dirname(__FILE__)) . 'wsdl/' . $api_mode . '/tradability/LandedCost.wsdl';

		//Check if new registration method
		if (Ph_UPS_Woo_Shipping_Common::phIsNewRegistration()) {
			//Check for active license
			if (!Ph_UPS_Woo_Shipping_Common::phHasActiveLicense()) {
				Ph_UPS_Woo_Shipping_Common::phAddDebugLog("------------------------------- UPS Landed Cost -------------------------------", $this->debug);
				Ph_UPS_Woo_Shipping_Common::phAddDebugLog("Please use a valid plugin license to continue using WooCommerce UPS Shipping Plugin with Print Label", $this->debug);
				return [];
			} else {

				$apiAccessDetails = Ph_Ups_Endpoint_Dispatcher::phGetApiAccessDetails();

				if (!$apiAccessDetails) {
					return false;
				}

				$proxyParams = Ph_UPS_Woo_Shipping_Common::phGetProxyParams($apiAccessDetails, $request_type);

				$client = $this->wf_create_soap_client($wsdl, $proxyParams['options']);

				// Updating the SOAP location to Proxy server
				$client->__setLocation($proxyParams['endpoint']);
			}
		} else {

			$client = $this->wf_create_soap_client($wsdl);
		}


		$header = new SoapHeader('http://www.ups.com/schema/xpci/1.0/auth', 'AccessRequest', $header, false);
		$client->__setSoapHeaders($header);

		try {

			$ups_response = $client->ProcessLCRequest($request);
		} catch (\SoapFault $fault) {

			$exceptionMessage  = 'An exception has been raised as a result of client data.';

			if (!empty($fault) && !empty($fault->detail) && !empty($fault->detail->Errors->ErrorDetail) && !empty($fault->detail->Errors->ErrorDetail->PrimaryErrorCode)) {

				$exceptionMessage  = isset($fault->detail->Errors->ErrorDetail->PrimaryErrorCode->Description) && !empty($fault->detail->Errors->ErrorDetail->PrimaryErrorCode->Description) ? $fault->detail->Errors->ErrorDetail->PrimaryErrorCode->Description : 'An exception has been raised as a result of client data.';
			}

			if (WC() != null && WC()->session != null) {

				WC()->session->set('ph_ups_total_landed_cost', '');
			}
		}

		if ($this->debug) {

			Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------ UPS ' . strtoupper($request_type) . ' REQUEST ------------------------', $this->debug);
			Ph_UPS_Woo_Shipping_Common::phAddDebugLog(htmlspecialchars($client->__getLastRequest()), $this->debug);
			Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------ UPS ' . strtoupper($request_type) . ' RESPONSE ------------------------', $this->debug);
			Ph_UPS_Woo_Shipping_Common::phAddDebugLog(htmlspecialchars($client->__getLastResponse()), $this->debug);

			if (!empty($exceptionMessage)) {

				Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------ UPS ' . strtoupper($request_type) . ' EXCEPTION MESSAGE ------------------------', $this->debug);
				Ph_UPS_Woo_Shipping_Common::phAddDebugLog(htmlspecialchars($exceptionMessage), $this->debug);
			}
		}
		return $ups_response;
	}

	/**
	 * Retrieves the UPS API response and handles caching.
	 *
	 * @param array $request The request data to be sent to the UPS API.
	 * @param string $request_type Optional. The type of request being processed. Defaults to an empty string.
	 * @param string $key Optional. A key used to track the request. Defaults to an empty string.
	 * @param string $orderId Optional. The order ID for which the request is made. Defaults to an empty string.
	 *
	 * @return string|null The response from the UPS API, or null if an error occurred.
	 */
	public function get_result($request, $request_type = '', $key = '', $orderId = '') {
		$ups_response = null;

		$send_request		   	= wp_json_encode(Ph_UPS_Woo_Shipping_Common::convert_array_values_to_strings($request), JSON_UNESCAPED_SLASHES);
		$transient			  	= 'ups_quote_' . md5($send_request);
		$cached_response		= get_transient($transient);
		$transient_time 		= ((int) $this->settings['rate_caching']) * 60 * 60;
		$key++;

		if ( $cached_response === false || apply_filters('ph_use_cached_response', false, $cached_response)) {

			$api_access_details = Ph_Ups_Endpoint_Dispatcher::phGetApiAccessDetails();

			$internal_endpoints = $api_access_details['internalEndpoints'];

			$request_option_proxy_mapping = array(
				'Shop'				=> 'account/rates/time-in-transit',
				'Shoptimeintransit'	=> 'account/rates',
				'Rate'				=> 'server/rates',
				'Ratetimeintransit'	=> 'server/rates/time-in-transit'
			);

			$endpoint = PH_UPS_Config::PH_UPS_PROXY_API_BASE_URL . $internal_endpoints[$request_option_proxy_mapping[$this->request_option]]['href'];

			$response = Ph_Ups_Api_Invoker::phCallApi(
				$endpoint,
				$api_access_details['token'],
				$send_request
			);

			if (is_wp_error($response) && is_object($response)) {
				$error_string = $response->get_error_message();
				Ph_UPS_Woo_Shipping_Common::debug('UPS REQUEST FAILED: <pre>' . print_r(htmlspecialchars($error_string), true) . '</pre>', $this->debug, $this->silent_debug);
			} elseif (!empty($response['body'])) {
				$ups_response = $response['body'];
				set_transient($transient, $response['body'], $transient_time);
			}
		} else {
			Ph_UPS_Woo_Shipping_Common::debug(__('UPS: Using cached response.', 'ups-woocommerce-shipping'), $this->debug, $this->silent_debug);
			$ups_response = $cached_response;
		}

		if ($this->debug) {
			$debug_request_to_display = $this->create_debug_request_or_response($request, 'rate_request', $request_type);

			$packageCount 	= isset($debug_request_to_display['Packages']) && !empty($debug_request_to_display['Packages']) ? count($debug_request_to_display['Packages']) : '';
			$displayCount 	= !empty($packageCount) ? " | Requested Packages: " . $packageCount : '';

			Ph_UPS_Woo_Shipping_Common::debug("UPS " . strtoupper($request_type) . " REQUEST [ Package Set: " . $key . $displayCount . " | Max Packages: 50 ] <pre>" . print_r($debug_request_to_display, true) . '</pre>', $this->debug, $this->silent_debug);
			$debug_response_to_display = $this->create_debug_request_or_response($ups_response, 'rate_response', $request_type);

			Ph_UPS_Woo_Shipping_Common::debug("UPS " . strtoupper($request_type) . " RESPONSE [ Package Set: " . $key . $displayCount . " | Max Packages: 50 ] <pre>" . print_r($debug_response_to_display, true) . '</pre>', $this->debug, $this->silent_debug);
			Ph_UPS_Woo_Shipping_Common::debug('UPS ' . strtoupper($request_type) . ' REQUEST JSON [ Package Set: ' . $key . $displayCount . ' | Max Packages: 50 ] <pre>' . print_r($send_request, true) . '</pre>', $this->debug, $this->silent_debug);
			Ph_UPS_Woo_Shipping_Common::debug('UPS ' . strtoupper($request_type) . ' RESPONSE JSON [ Package Set: ' . $key . $displayCount . ' | Max Packages: 50 ] <pre>' . print_r($ups_response, true) . '</pre>', $this->debug, $this->silent_debug);

			$orderId = !empty($orderId) ? '#' . $orderId : '';

			Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------ UPS ' . strtoupper($request_type) . ' REQUEST [ Package Set: ' . $key . $displayCount . ' | Max Packages: 50 ] ' . $orderId . '------------------------', $this->debug);
			Ph_UPS_Woo_Shipping_Common::phAddDebugLog($send_request, $this->debug);
			Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------ UPS ' . strtoupper($request_type) . ' RESPONSE [ Package Set: ' . $key . $displayCount . ' | Max Packages: 50 ] ' . $orderId . '------------------------', $this->debug);
			Ph_UPS_Woo_Shipping_Common::phAddDebugLog($ups_response, $this->debug);
		}

		if (is_admin()) {

			Ph_UPS_Woo_Shipping_Common::phAddDebugLog(__('------------------------UPS Rate Request for the Order #' . $orderId . '-------------------------------', 'ups-woocommerce-shipping'), $this->debug);
			Ph_UPS_Woo_Shipping_Common::phAddDebugLog(htmlspecialchars($send_request), $this->debug);

			Ph_UPS_Woo_Shipping_Common::phAddDebugLog(__('------------------------UPS Rate Response for the Order #' . $orderId . '-------------------------------', 'ups-woocommerce-shipping'), $this->debug);
			Ph_UPS_Woo_Shipping_Common::phAddDebugLog(htmlspecialchars($ups_response), $this->debug);

			if ($cached_response !== false) {
				Ph_UPS_Woo_Shipping_Common::phAddDebugLog(__('Above Response is cached Response.', 'ups-woocommerce-shipping'), $this->debug);
			}
		}

		return $ups_response;
	}

	/**
	 * Create Debug Request or response.
	 * @param $data mixed Xml or JSON request or response.
	 * @param $type string Rate request or Response.
	 * @param $request_type mixed Request type whether freight or surepost or normal request.
	 */
	public function create_debug_request_or_response($data, $type = '', $request_type = null) {
		$debug_data = null;

		if( null === $data) return;
		
		switch ($type) {
			case 'rate_request':
				// Freight Request
				if ($request_type == 'freight') {
					$request_data = json_decode($data, true);
					$debug_data = array(
						'Ship From Address'	=>	$request_data['FreightRateRequest']['ShipFrom']['Address'],
						'Ship To Address'	=>	$request_data['FreightRateRequest']['ShipTo']['Address'],
					);
					$packages = $request_data['FreightRateRequest']['Commodity'];
					foreach ($packages as $package) {
						if (!empty($package['Dimensions'])) {
							$debug_data['Packages'][] = array(
								'Weight'	=>	array(
									'Value'		=>	$package['Weight']['Value'],
									'Unit'		=>	$package['Weight']['UnitOfMeasurement']['Code'],
								),
								'Dimensions'	=>	array(
									'Length'	=>	$package['Dimensions']['Length'],
									'Width'		=>	$package['Dimensions']['Width'],
									'Height'	=>	$package['Dimensions']['Height'],
									'Unit'		=>	$package['Dimensions']['UnitOfMeasurement']['Code'],
								),
							);
						} else {
							$debug_data['Packages'][] = array(
								'Weight'	=>	array(
									'Value'		=>	$package['Weight']['UnitOfMeasurement']['Code'],
									'Unit'		=>	$package['Weight']['Value'],
								),
							);
						}
					}
				}
				// Other request type
				else {
					if (!empty($data)) {
						$debug_data = array(
							'Ship From Address'	=>	isset($data['RateRequest']['Shipment']['ShipFrom']['Address']['AddressLine']) ? $data['RateRequest']['Shipment']['ShipFrom']['Address']['AddressLine'] : '',
							'Ship To Address'	=>	isset($data['RateRequest']['Shipment']['ShipTo']['Address']['AddressLine']) ? $data['RateRequest']['Shipment']['ShipTo']['Address']['AddressLine'] : '',
						);
						$packages = isset($data['RateRequest']['Shipment']['Package']) ? $data['RateRequest']['Shipment']['Package'] : '';
						// Handle Single Package
						if (isset($data['RateRequest']['Shipment']['Package']['PackageWeight'])) {
							$packages = array($packages);
						}

						if (!empty($packages) && is_array($packages)) {

							foreach ($packages as $package) {
								if (!empty($package['Dimensions'])) {
									$debug_data['Packages'][] = array(
										'Weight'	=>	array(
											'Value'		=>	$package['PackageWeight']['Weight'],
											'Unit'		=>	$package['PackageWeight']['UnitOfMeasurement']['Code'],
										),
										'Dimensions'	=>	array(
											'Length'	=>	$package['Dimensions']['Length'],
											'Width'		=>	$package['Dimensions']['Width'],
											'Height'	=>	$package['Dimensions']['Height'],
											'Unit'		=>	$package['Dimensions']['UnitOfMeasurement']['Code'],
										),
									);
								} else {
									$debug_data['Packages'][] = array(
										'Weight'	=>	array(
											'Value'		=>	$package['PackageWeight']['UnitOfMeasurement']['Code'],
											'Unit'		=>	$package['PackageWeight']['Weight'],
										),
									);
								}
							}
						}
					}
				}

				break;
			case 'rate_response':
				if ($request_type == 'freight') {
					$response_arr = json_decode($data, true);
					if (!empty($response_arr['Fault'])) {
						$debug_data = $response_arr['Fault'];
					} elseif (!empty($response_arr['FreightRateResponse'])) {
						$debug_data = array(
							'Service'			=>	$response_arr['FreightRateResponse']['Service']['Code'],
							'Shipping Cost'		=>	$response_arr['FreightRateResponse']['TotalShipmentCharge']['MonetaryValue'],
							'Currency Code'		=>	$response_arr['FreightRateResponse']['TotalShipmentCharge']['CurrencyCode'],
						);
					}
				} else {

					$response_arr = json_decode($data, true);
					if (!empty($response_arr['response']['errors'])) {
						$debug_data = $response_arr['response']['errors'];
					} elseif (!empty($response_arr['RateResponse']['RatedShipment'])) {
						$response_rate_arr = isset($response_arr['RateResponse']['RatedShipment']['Service']) ? array($response_arr['RateResponse']['RatedShipment']) : $response_arr['RateResponse']['RatedShipment'];
						foreach ($response_rate_arr as $rate_details) {
							$debug_data[] = array(
								'Service'		=>	$rate_details['Service']['Code'],
								'Shipping Cost'	=>	$rate_details['TotalCharges']['MonetaryValue'],
								'Currency Code'	=>	$rate_details['TotalCharges']['CurrencyCode'],
							);
						}
					}
				}
				break;
			default:
				break;
		}

		return $debug_data;
	}

	/**
	 * get_rate_requests_gfp
	 *
	 * Get rate requests for ground freight
	 * @access private
	 * @return array of strings - XML
	 *
	 */
	// public function  get_rate_requests_gfp($package_requests, $package, $request_type = '', $service_code = '') {
	// 	global $woocommerce;

	// 	$customer = $woocommerce->customer;

	// 	$package_requests_to_append	= $package_requests;
	// 	$rate_request_data			=	array(
	// 		'user_id'					=>	$this->user_id,
	// 		'password'					=>	str_replace('&', '&amp;', $this->password), // Ampersand will break XML doc, so replace with encoded version.
	// 		'access_key'				=>	$this->access_key,
	// 		'shipper_number'			=>	$this->shipper_number,
	// 		'origin_addressline'		=>	$this->origin_addressline,
	// 		'origin_addressline_2'		=>	$this->origin_addressline_2,
	// 		'origin_postcode'			=>	$this->origin_postcode,
	// 		'origin_city'				=>	$this->origin_city,
	// 		'origin_state'				=>	$this->origin_state,
	// 		'origin_country'			=>	$this->origin_country,
	// 		'ship_from_addressline'		=>	$this->ship_from_addressline,
	// 		'ship_from_addressline_2'	=>	$this->ship_from_addressline_2,
	// 		'ship_from_postcode'		=>	$this->ship_from_postcode,
	// 		'ship_from_city'			=>	$this->ship_from_city,
	// 		'ship_from_state'			=>	$this->ship_from_state,
	// 		'ship_from_country'			=>	$this->ship_from_country,
	// 	);

	// 	$rate_request_data	=	apply_filters('wf_ups_rate_request_data', $rate_request_data, $package, $package_requests);

	// 	$request['RateRequest'] = array();
	// 	$json_request = array();

	// 	$request['RateRequest']['Request'] = array(

	// 		'TransactionReference' => array(
	// 			'CustomerContext' => 'Rating and Service'
	// 		),
	// 		//@note Removed 'RequestAction' node as it was not present in doc
	// 		'RequestOption' => 'Rate'
	// 	);

	// 	$request['RateRequest']['PickupType'] = array(
	// 		'Code' => $this->pickup,
	// 		'Description' => $this->pickup_code[$this->pickup]
	// 	);

	// 	$request['RateRequest']['Shipment'] = array(
	// 		'Description' => 'WooCommerce GFP Rate Request',
	// 		'FRSPaymentInformation' => array(
	// 			'Type' => array(
	// 				'Code' => '01'
	// 			),
	// 			'AccountNumber' => $this->shipper_number
	// 		),
	// 	);

	// 	$originAddress = empty($rate_request_data['origin_addressline_2']) ? $rate_request_data['origin_addressline'] : array($rate_request_data['origin_addressline'], $rate_request_data['origin_addressline_2']);

	// 	$request['RateRequest']['Shipment']['Shipper'] = array(
	// 		'Address' => array(
	// 			'AddressLine' => $originAddress,
	// 			'CountryCode' => $rate_request_data['origin_country'],
	// 		),
	// 		'ShipperNumber' => $rate_request_data['shipper_number'],
	// 	);

	// 	$request['RateRequest']['Shipment']['Shipper']['Address'] = array_merge($request['RateRequest']['Shipment']['Shipper']['Address'], $this->ph_get_postcode_city_in_array($rate_request_data['origin_country'], $rate_request_data['origin_city'], $rate_request_data['origin_postcode']));

	// 	if (!empty($rate_request_data['origin_state'])) {
	// 		$request['RateRequest']['Shipment']['Shipper']['Address']['StateProvinceCode'] = $rate_request_data['origin_state'];
	// 	}

	// 	$destination_city 		= strtoupper($package['destination']['city']);
	// 	$destination_country 	= "";

	// 	if (("PR" == $package['destination']['state']) && ("US" == $package['destination']['country'])) {
	// 		$destination_country = "PR";
	// 	} else {
	// 		$destination_country = $package['destination']['country'];
	// 	}

	// 	$request['RateRequest']['Shipment']['ShipTo']['Address'] = array();

	// 	$address = '';

	// 	if (!empty($package['destination']['address_1'])) {

	// 		$address = $package['destination']['address_1'];

	// 		if (isset($package['destination']['address_2']) && !empty($package['destination']['address_2'])) {

	// 			$address = $address . ' ' . $package['destination']['address_2'];
	// 		}
	// 	} elseif (!empty($package['destination']['address'])) {

	// 		$address = $package['destination']['address'];
	// 	}

	// 	if (!empty($address)) {

	// 		$request['RateRequest']['Shipment']['ShipTo']['Address']['AddressLine'] = $address;
	// 	}

	// 	$request['RateRequest']['Shipment']['ShipTo']['Address']['StateProvinceCode'] = htmlspecialchars($package['destination']['state']);

	// 	$request['RateRequest']['Shipment']['ShipTo']['Address'] = array_merge($request['RateRequest']['Shipment']['ShipTo']['Address'], $this->ph_get_postcode_city_in_array($destination_country, $destination_city, $package['destination']['postcode']));
	// 	$request['RateRequest']['Shipment']['ShipTo']['Address']['CountryCode'] = $destination_country;
	// 	$request['RateRequest']['Shipment']['ShipTo']['Address']['CountryCode'] = $destination_country;
	// 	if ($this->residential) {
	// 		$request['RateRequest']['Shipment']['ShipTo']['Address']['ResidentialAddressIndicator'] = 1;
	// 	}

	// 	// If ShipFrom address is different.
	// 	if ($this->ship_from_address_different_from_shipper == 'yes' && !empty($rate_request_data['ship_from_addressline'])) {

	// 		$fromAddress = empty($rate_request_data['ship_from_addressline_2']) ? $rate_request_data['ship_from_addressline'] : array($rate_request_data['ship_from_addressline'], $rate_request_data['ship_from_addressline_2']);

	// 		$request['RateRequest']['Shipment']['ShipFrom'] = array(
	// 			'Address' => array(
	// 				'AddressLine' => $fromAddress,
	// 				'CountryCode' => $rate_request_data['ship_from_country'],
	// 			),
	// 		);

	// 		$request['RateRequest']['Shipment']['ShipFrom']['Address'] = array_merge($request['RateRequest']['Shipment']['ShipFrom']['Address'], $this->ph_get_postcode_city_in_array($rate_request_data['ship_from_country'], $rate_request_data['ship_from_city'], $rate_request_data['ship_from_postcode']));

	// 		if (!empty($rate_request_data['ship_from_state'])) {
	// 			$request['RateRequest']['Shipment']['ShipFrom']['Address']['StateProvinceCode'] = $rate_request_data['ship_from_state'];
	// 		}
	// 	} else {

	// 		$fromAddress = empty($rate_request_data['origin_addressline_2']) ? $rate_request_data['origin_addressline'] : array($rate_request_data['origin_addressline'], $rate_request_data['origin_addressline_2']);

	// 		$request['RateRequest']['Shipment']['ShipFrom'] = array(
	// 			'Address' => array(
	// 				'AddressLine' => $fromAddress,
	// 				'CountryCode' => $rate_request_data['origin_country'],
	// 			),
	// 		);

	// 		$request['RateRequest']['Shipment']['ShipFrom']['Address'] = array_merge($request['RateRequest']['Shipment']['ShipFrom']['Address'], $this->ph_get_postcode_city_in_array($rate_request_data['origin_country'], $rate_request_data['origin_city'], $rate_request_data['origin_postcode']));

	// 		if ($rate_request_data['origin_state']) {
	// 			$request['RateRequest']['Shipment']['ShipFrom']['Address']['StateProvinceCode'] = $rate_request_data['origin_state'];
	// 		}
	// 	}

	// 	$request['RateRequest']['Shipment']['Service'] = array('Code' => '03');

	// 	$total_item_count = 0;

	// 	if (isset($package['contents']) && !empty($package['contents'])) {

	// 		foreach ($package['contents'] as $product) {
	// 			$total_item_count += $product['quantity'];
	// 		}
	// 	}

	// 	$request['RateRequest']['Shipment']['NumOfPieces'] = $total_item_count;

	// 	// packages
	// 	$total_package_weight = 0;
	// 	$total_packags = array();
	// 	foreach ($package_requests_to_append as $key => $package_request) {

	// 		$package_request = $this->ph_ups_convert_weight_dimension_based_on_vendor($package_request);

	// 		$total_package_weight += $package_request['Package']['PackageWeight']['Weight'];
	// 		$package_request['Package']['PackageWeight'] = $this->copyArray($package_request['Package']['PackageWeight']);
	// 		$package_request['Package']['Commodity']['FreightClass'] = $this->freight_class;
	// 		$package_request['Package']['PackagingType']['Code'] = "02";
	// 		// Setting Length, Width and Height for weight based packing.
	// 		if (!isset($package_request['Package']['Dimensions']) || !empty($package_request['Package']['Dimensions'])) {
	// 			unset($package_request['Package']['Dimensions']);
	// 		}

	// 		//PDS-87
	// 		if (isset($package_request['Package']['PackageServiceOptions'])) {

	// 			if (isset($package_request['Package']['PackageServiceOptions']['InsuredValue'])) {

	// 				unset($package_request['Package']['PackageServiceOptions']['InsuredValue']);
	// 			}

	// 			if (isset($package_request['Package']['PackageServiceOptions']['DeliveryConfirmation'])) {

	// 				$package_request['Package']['PackageServiceOptions']['DeliveryConfirmation'] = $this->copyArray($package_request['Package']['PackageServiceOptions']['DeliveryConfirmation']);
	// 			}
	// 		}

	// 		if (isset($package_request['Package']['items'])) {
	// 			unset($package_request['Package']['items']);		//Not required further
	// 		}

	// 		$total_packags[] = $package_request['Package'];
	// 	}

	// 	$request['RateRequest']['Shipment']['Package'] = $total_packags;

	// 	$request['RateRequest']['Shipment']['ShipmentRatingOptions'] = array();
	// 	if ($this->negotiated) {
	// 		$request['RateRequest']['Shipment']['ShipmentRatingOptions']['NegotiatedRatesIndicator'] = '1';
	// 	}
	// 	$request['RateRequest']['Shipment']['ShipmentRatingOptions']['FRSShipmentIndicator'] = '1';

	// 	if ($this->tax_indicator) {
	// 		$request['RateRequest']['Shipment']['TaxInformationIndicator'] = '1';
	// 	}

	// 	$request['RateRequest']['Shipment']['DeliveryTimeInformation'] = array(

	// 		'PackageBillType' => '03',
	// 	);

	// 	if ($this->show_est_delivery && !empty($this->settings['cut_off_time']) && $this->settings['cut_off_time'] != '24:00') {
	// 		$timestamp 							= clone $this->current_wp_time;
	// 		$this->current_wp_time_hour_minute 	= current_time('H:i');
	// 		if ($this->current_wp_time_hour_minute > $this->settings['cut_off_time']) {
	// 			$timestamp->modify('+1 days');
	// 			$this->pickup_date = $timestamp->format('Ymd');
	// 			$this->pickup_time = '0800';
	// 		} else {
	// 			$this->pickup_date = date('Ymd');
	// 			$this->pickup_time = $timestamp->format('Hi');
	// 		}

	// 		// Adjust estimated delivery based on Ship time adjsutment settings
	// 		if (!empty($this->shipTimeAdjustment)) {

	// 			$dateObj = new DateTime($this->pickup_date);
	// 			$dateObj->modify("+ $this->shipTimeAdjustment days");
	// 			$this->pickup_date = $dateObj->format('Ymd');
	// 		}

	// 		$request['RateRequest']['Shipment']['DeliveryTimeInformation']['Pickup'] = array(
	// 			'Date' => $this->pickup_date,
	// 			'Time' => $this->pickup_time,
	// 		);
	// 	}

	// 	$request['RateRequest']['Shipment']['ShipmentTotalWeight'] = array(

	// 		'UnitOfMeasurement' => array(
	// 			'Code'	=> $this->weight_unit
	// 		),
	// 		'Weight' => $total_package_weight
	// 	);

	// 	$this->density_unit 	= $this->dim_unit;
	// 	$this->density_length 	= $this->density_length;
	// 	$this->density_width 	= $this->density_width;
	// 	$this->density_height 	= $this->density_height;

	// 	if ($this->density_length == 0) {
	// 		$this->density_length = ($this->density_unit == 'IN') ? 10 : 26;
	// 	}

	// 	if ($this->density_width == 0) {
	// 		$this->density_width = ($this->density_unit == 'IN') ? 10 : 26;
	// 	}

	// 	if ($this->density_height == 0) {
	// 		$this->density_height = ($this->density_unit == 'IN') ? 10 : 26;
	// 	}
	// 	if ($this->enable_density_based_rating) {
	// 		$request['RateRequest']['Shipment']['FreightShipmentInformation'] = array(

	// 			'FreightDensityInfo' => array(

	// 				'HandlingUnits' => array(

	// 					'Quantity' 	=> 1,
	// 					'Type'		=> array(

	// 						'Code'			=> 'PLT',
	// 						'Description'	=> 'Density'
	// 					),
	// 					'Dimensions' => array(

	// 						'UnitOfMeasurement'	=> array(
	// 							'Code'	=> $this->density_unit,
	// 							'Description'		=> "Dimension unit",
	// 						),
	// 						'Length'			=> $this->density_length,
	// 						'Width'				=> $this->density_width,
	// 						'Height'			=> $this->density_height
	// 					)
	// 				),
	// 				'Description'	=> "density rating",
	// 			),
	// 			'DensityEligibleIndicator'	=> 1,
	// 		);
	// 	}

	// 	return apply_filters('ph_ups_rate_request_gfp', $request, $package);
	// }

	/**
	 * Generates rate requests for UPS based on provided package details.
	 *
	 * @param array $package_requests The array of package requests that will be populated with new rate requests.
	 * @param array $package The details of the package for which the rate request is being generated.
	 * @param string $request_type Optional. The type of request being processed. Defaults to an empty string.
	 * @param string $service_code Optional. The UPS service code to use for the rate request. Defaults to an empty string.
	 * @param bool $international_delivery_confirmation_applicable Optional. Whether international delivery confirmation is applicable. Defaults to an empty string.
	 *
	 * @return array The array of package requests, including the newly added rate requests.
	 */
	public function  get_rate_requests($package_requests, $package, $request_type = '', $service_code = '', $international_delivery_confirmation_applicable = '') {
		global $woocommerce;

		if (isset($_GET['wf_ups_generate_packages_rates'])) {

			$order_id 	= base64_decode($_GET['wf_ups_generate_packages_rates']);
		} else if (isset($_GET['wf_ups_shipment_confirm'])) {

			$query_string 	= explode( '|', base64_decode($_GET['wf_ups_shipment_confirm']) );
			$order_id		= end( $query_string );
		}

		$this->ph_ups_selected_access_point_details = !empty($package['ph_ups_selected_access_point_details']) ? $package['ph_ups_selected_access_point_details'] : null;

		$package_requests_to_append	= $package_requests;

		$rate_request_data	=	array(
			'user_id'					=>	$this->settings['user_id'],
			'password'					=>	str_replace('&', '&amp;', $this->settings['password']), // Ampersand will break XML doc, so replace with encoded version.
			'access_key'				=>	$this->settings['access_key'],
			'shipper_number'			=>	$this->settings['shipper_number'],
			'origin_addressline'		=>	$this->settings['origin_addressline'],
			'origin_addressline_2'		=>	$this->settings['origin_addressline_2'],
			'origin_postcode'			=>	$this->settings['origin_postcode'],
			'origin_city'				=>	$this->settings['origin_city'],
			'origin_state'				=>	$this->settings['origin_state'],
			'origin_country'			=>	$this->settings['origin_country'],
			'ship_from_addressline'		=>	$this->settings['ship_from_addressline'],
			'ship_from_addressline_2'	=>	$this->settings['ship_from_addressline_2'],
			'ship_from_postcode'		=>	$this->settings['ship_from_postcode'],
			'ship_from_city'			=>	$this->settings['ship_from_city'],
			'ship_from_state'			=>	$this->settings['ship_from_state'],
			'ship_from_country'			=>	$this->settings['ship_from_country'],

			// If Import Control is enabled then also Shipper address will not change.
			'shipper_addressline'		=>	$this->settings['origin_addressline'],
			'shipper_addressline_2'		=>	$this->settings['origin_addressline_2'],
			'shipper_postcode'			=>	$this->settings['origin_postcode'],
			'shipper_city'				=>	$this->settings['origin_city'],
			'shipper_state'				=>	$this->settings['origin_state'],
			'shipper_country'			=>	$this->settings['origin_country'],
		);

		// Checking Import Control selected in edit order page
		if (isset($_GET['impc'])) {

			$import_control = $_GET['impc'] ? $_GET['impc'] : '';

			PH_UPS_WC_Storage_Handler::ph_add_and_save_meta_data($order_id, '_ph_ups_import_control', $_GET['impc']);
		}

		if ((isset($import_control) && 'true' == $import_control) || 'yes' === $this->settings['import_control_settings'] ) {

			$rate_request_data['origin_addressline'] 	 = $package['destination']['address'];
			$rate_request_data['origin_addressline_2'] 	 = '';
			$rate_request_data['origin_postcode'] 		 = $package['destination']['postcode'];
			$rate_request_data['origin_city'] 			 = $package['destination']['city'];
			$rate_request_data['origin_state'] 			 = $package['destination']['state'];
			$rate_request_data['origin_country'] 		 = $package['destination']['country'];

			$package['destination']['address']   = $this->settings['origin_addressline'];
			$package['destination']['country']   = $this->settings['origin_country'];
			$package['destination']['state'] 	 = $this->settings['origin_state'];
			$package['destination']['postcode']  = $this->settings['origin_postcode'];
			$package['destination']['city'] 	 = $this->settings['origin_city'];

			if ( $this->settings['ship_from_address_different_from_shipper']) {

				$rate_request_data['ship_from_addressline']  = $package['destination']['address'];
				$rate_request_data['ship_from_addressline_2'] = '';
				$rate_request_data['ship_from_postcode'] 	 = $package['destination']['postcode'];
				$rate_request_data['ship_from_city'] 		 = $package['destination']['city'];
				$rate_request_data['ship_from_state'] 		 = $package['destination']['state'];
				$rate_request_data['ship_from_country'] 	 = $package['destination']['country'];

				$package['destination']['address']   = $this->settings['ship_from_addressline'];
				$package['destination']['country']   = $this->settings['ship_from_country'];
				$package['destination']['state'] 	 = $this->settings['ship_from_state'];
				$package['destination']['postcode']  = $this->settings['ship_from_postcode'];
				$package['destination']['city'] 	 = $this->settings['ship_from_city'];
			}
		}

		$rate_request_data	=	apply_filters('wf_ups_rate_request_data', $rate_request_data, $package, $package_requests);

		$this->is_hazmat_product 	= false;

		// For Estimated delivery, Estimated delivery not available for Surepost confirmed by UPS
		if ($this->settings['enable_estimated_delivery'] && $request_type != 'surepost') {
			$this->request_option = empty($service_code) ? 'Shoptimeintransit' : 'Ratetimeintransit';
		} else {
			$this->request_option = empty($service_code) ? 'Shop' : 'Rate';
		}

		$jsonRequest['RateRequest'] = array();

		$jsonRequest['RateRequest']['Request'] = array(

			'TransactionReference' => array(
				'CustomerContext' => 'Rating and Service'
			),
			//@note Removed 'RequestAction' node as it was not present in doc
			'RequestOption' => $this->request_option
		);

		$jsonRequest['RateRequest']['PickupType'] = array(
			'Code' => $this->settings['pickup'],
			'Description' => PH_WC_UPS_Constants::PICKUP_CODE[$this->settings['pickup']]
		);

		//Accroding to the documentaion CustomerClassification will not work for non-us county. But UPS team confirmed this will for any country.
		// if ( 'US' == $rate_request_data['origin_country']) {
		if ($this->settings['negotiated']) {

			$jsonRequest['RateRequest']['CustomerClassification']['Code'] = '00';
		} elseif (!empty($this->settings['customer_classification']) && 'NA' != $this->settings['customer_classification']) {

			$jsonRequest['RateRequest']['CustomerClassification']['Code'] = $this->settings['customer_classification'];
		}
		// }

		//@note AccessPoint needs to be handled with request.

		$jsonRequest['RateRequest']['Shipment'] = array();

		if ($this->settings['accesspoint_locator']) {

			$access_point_node = $this->get_acccesspoint_rate_request( $this->ph_ups_selected_access_point_details );
			if (!empty($access_point_node)) { // Access Point Addresses Are All Commercial
				$this->settings['residential']	=	false;

				$jsonRequest['RateRequest']['Shipment'] = array_merge($jsonRequest['RateRequest']['Shipment'], $access_point_node);
			}
		}

		//@note Description node not present in doc. Check XML Request

		$shipperAddress = empty($rate_request_data['shipper_addressline_2']) ? $rate_request_data['shipper_addressline'] : array($rate_request_data['shipper_addressline'], $rate_request_data['shipper_addressline_2']);

		$jsonRequest['RateRequest']['Shipment']['Shipper'] = array(
			'ShipperNumber' => $rate_request_data['shipper_number'],
			'Address' => array(
				'AddressLine' => $shipperAddress,
			)
		);

		$jsonRequest['RateRequest']['Shipment']['Shipper']['Address'] = array_merge($jsonRequest['RateRequest']['Shipment']['Shipper']['Address'], PH_WC_UPS_Common_Utils::ph_get_postcode_city_in_array($rate_request_data['shipper_country'], $rate_request_data['shipper_city'], $rate_request_data['shipper_postcode']));
		if (!empty($rate_request_data['shipper_state'])) {
			$jsonRequest['RateRequest']['Shipment']['Shipper']['Address']['StateProvinceCode'] = $rate_request_data['shipper_state'];
		}

		$jsonRequest['RateRequest']['Shipment']['Shipper']['Address']['CountryCode'] = $rate_request_data['shipper_country'];

		// Residential address Validation done by API automatically if address_1 is available.
		$address = '';

		if (!empty($package['destination']['address_1'])) {

			$address = $package['destination']['address_1'];

			if (isset($package['destination']['address_2']) && !empty($package['destination']['address_2'])) {

				$address = $address . ' ' . $package['destination']['address_2'];
			}
		} elseif (!empty($package['destination']['address'])) {

			$address = $package['destination']['address'];
		}

		if (!empty($address)) {

			$jsonRequest['RateRequest']['Shipment']['ShipTo']['Address']['AddressLine'] = $address;
		}

		$destination_city = strtoupper($package['destination']['city']);
		$destination_country = "";
		if (("PR" == $package['destination']['state']) && ("US" == $package['destination']['country'])) {
			$destination_country = "PR";
		} else {
			$destination_country = $package['destination']['country'];
		}

		if (isset($jsonRequest['RateRequest']['Shipment']['ShipTo']['Address'])) {
			$jsonRequest['RateRequest']['Shipment']['ShipTo']['Address'] = array_merge($jsonRequest['RateRequest']['Shipment']['ShipTo']['Address'], PH_WC_UPS_Common_Utils::ph_get_postcode_city_in_array($destination_country, $destination_city, $package['destination']['postcode']));
		} else {
			$jsonRequest['RateRequest']['Shipment']['ShipTo']['Address'] = PH_WC_UPS_Common_Utils::ph_get_postcode_city_in_array($destination_country, $destination_city, $package['destination']['postcode']);
		}
		
		$jsonRequest['RateRequest']['Shipment']['ShipTo']['Address']['StateProvinceCode'] = htmlspecialchars($package['destination']['state']);

		$jsonRequest['RateRequest']['Shipment']['ShipTo']['Address']['CountryCode'] = $destination_country;

		if ($this->settings['residential']) {
			$jsonRequest['RateRequest']['Shipment']['ShipTo']['Address']['ResidentialAddressIndicator'] = 1;
		}

		// If ShipFrom address is different.
		if ( $this->settings['ship_from_address_different_from_shipper'] && !empty($rate_request_data['ship_from_addressline'])) {

			$shipFromAddress = empty($rate_request_data['ship_from_addressline_2']) ? $rate_request_data['ship_from_addressline'] : array($rate_request_data['ship_from_addressline'], $rate_request_data['ship_from_addressline_2']);

			$jsonRequest['RateRequest']['Shipment']['ShipFrom'] = array(
				'Address' => array(
					'AddressLine' => $shipFromAddress,
				)
			);

			if (isset($jsonRequest['RateRequest']['Shipment']['ShipFrom']['Address'])) {
				$jsonRequest['RateRequest']['Shipment']['ShipFrom']['Address'] = array_merge($jsonRequest['RateRequest']['Shipment']['ShipFrom']['Address'], PH_WC_UPS_Common_Utils::ph_get_postcode_city_in_array($rate_request_data['ship_from_country'], $rate_request_data['ship_from_city'], $rate_request_data['ship_from_postcode']));
			} else {
				$jsonRequest['RateRequest']['Shipment']['ShipFrom']['Address'] = PH_WC_UPS_Common_Utils::ph_get_postcode_city_in_array($rate_request_data['ship_from_country'], $rate_request_data['ship_from_city'], $rate_request_data['ship_from_postcode']);
			}

			if (!empty($rate_request_data['ship_from_state'])) {
				$jsonRequest['RateRequest']['Shipment']['ShipFrom']['Address']['StateProvinceCode'] = $rate_request_data['ship_from_state'];
			}

			$jsonRequest['RateRequest']['Shipment']['ShipFrom']['Address']['CountryCode'] = $rate_request_data['ship_from_country'];
		} else {

			$originAddress = empty($rate_request_data['origin_addressline_2']) ? $rate_request_data['origin_addressline'] : array($rate_request_data['origin_addressline'], $rate_request_data['origin_addressline_2']);

			$jsonRequest['RateRequest']['Shipment']['ShipFrom'] = array(
				'Address' => array(
					'AddressLine' => $originAddress,
				)
			);

			if (isset($jsonRequest['RateRequest']['Shipment']['ShipFrom']['Address'])) {
				$jsonRequest['RateRequest']['Shipment']['ShipFrom']['Address'] = array_merge($jsonRequest['RateRequest']['Shipment']['ShipFrom']['Address'], PH_WC_UPS_Common_Utils::ph_get_postcode_city_in_array($rate_request_data['origin_country'], $rate_request_data['origin_city'], $rate_request_data['origin_postcode']));
			} else {
				$jsonRequest['RateRequest']['Shipment']['ShipFrom']['Address'] = PH_WC_UPS_Common_Utils::ph_get_postcode_city_in_array($rate_request_data['origin_country'], $rate_request_data['origin_city'], $rate_request_data['origin_postcode']);
			} 

			if (!empty($rate_request_data['origin_state'])) {
				$jsonRequest['RateRequest']['Shipment']['ShipFrom']['Address']['StateProvinceCode'] = $rate_request_data['origin_state'];
			}

			$jsonRequest['RateRequest']['Shipment']['ShipFrom']['Address']['CountryCode'] = $rate_request_data['origin_country'];
		}

		//For Worldwide Express Freight Service
		if ($request_type == 'Pallet' && $service_code == 96 && isset($package['contents']) && is_array($package['contents'])) {
			$total_item_count = 0;
			foreach ($package['contents'] as $product) {
				$total_item_count += $product['quantity'];
			}
			$jsonRequest['RateRequest']['Shipment']['NumOfPieces'] = $total_item_count;
		}

		if (!empty($service_code)) {
			$jsonRequest['RateRequest']['Shipment']['Service']['Code'] = PH_WC_UPS_Common_Utils::get_service_code_for_country($service_code, $rate_request_data['origin_country']);
		}

		// Hazmat Materials & ISC
		$id = 0;

		$alcoholicbeveragesindicator 	= 'no';
		$diagnosticspecimensindicator 	= 'no';
		$perishablesindicator 			= 'no';
		$plantsindicator 				= 'no';
		$seedsindicator 				= 'no';
		$specialexceptionsindicator 	= 'no';
		$tobaccoindicator 				= 'no';

		// For check at Global
		$add_global_restricted_article 	= false;

		if (isset($package['contents'])) {

			foreach ($package['contents'] as $product) {

				$product_id 		= (isset($product['product_id'])) ? $product['product_id'] : '';
				$product_var_id 	= (isset($product['variation_id'])) ? $product['variation_id'] : '';

				// Restricted Articles
				if ($this->settings['isc']) {

					$restricted_product  = 'no';

					if (!empty($product_id)) {
						$restricted_product 	= get_post_meta($product_id, '_ph_ups_restricted_article', 1);
						$restrictedarticle 		= get_post_meta($product_id, '_ph_ups_restricted_settings', 1);
					}

					if (!empty($product_var_id) && $restricted_product != 'yes') {
						$restricted_product = get_post_meta($product_var_id, '_ph_ups_restricted_article', 1);
						$restrictedarticle 	= get_post_meta($product_var_id, '_ph_ups_restricted_settings', 1);
					}

					if (empty($restricted_product) || $restricted_product == 'no') {

						$add_global_restricted_article = true;
					}

					if ($restricted_product == 'yes' && isset($restrictedarticle) && !empty($restrictedarticle)) {

						$alcoholicbeveragesindicator 	= ($alcoholicbeveragesindicator == 'yes') ? $alcoholicbeveragesindicator : $restrictedarticle['_ph_ups_alcoholic'];
						$diagnosticspecimensindicator 	= ($diagnosticspecimensindicator == 'yes') ? $diagnosticspecimensindicator : $restrictedarticle['_ph_ups_diog'];
						$perishablesindicator 			= ($perishablesindicator == 'yes') ? $perishablesindicator : $restrictedarticle['_ph_ups_perishable'];
						$plantsindicator 				= ($plantsindicator == 'yes') ? $plantsindicator : $restrictedarticle['_ph_ups_plantsindicator'];
						$seedsindicator 				= ($seedsindicator == 'yes') ? $seedsindicator : $restrictedarticle['_ph_ups_seedsindicator'];
						$specialexceptionsindicator 	= ($specialexceptionsindicator == 'yes') ? $specialexceptionsindicator : $restrictedarticle['_ph_ups_specialindicator'];
						$tobaccoindicator 				= ($tobaccoindicator == 'yes') ? $tobaccoindicator : $restrictedarticle['_ph_ups_tobaccoindicator'];
					}
				}
			}
		}

		// Checking COD selected in edit order page
		if (isset($_GET['cod'])) {

			$ups_cod  = $_GET['cod'] ? $_GET['cod'] : '';

			PH_UPS_WC_Storage_Handler::ph_add_and_save_meta_data($order_id, '_ph_ups_cod', $_GET['cod']);
		}

		// packages
		$hazmat_package_identifier = 1;
		$total_package_weight = 0;

		foreach ($package_requests_to_append as $key => $package_request) {

			//For Package level COD in Edit Order Page.
			$cod_amount = 0;
			$this->is_hazmat_product = false;
			$ph_hazmat_details = [];

			if (isset($package_request['Package']) && isset($package_request['Package']['items']) && !empty($package_request['Package']['items'])) {

				foreach ($package_request['Package']['items'] as $key => $value) {

					$cod_amount = $cod_amount + (!empty($value->get_price()) ? $value->get_price() : $this->settings['fixedProductPrice']);

					$product_var_id = '';

					if ($value->get_parent_id()) {
						$product_id 	= $value->get_parent_id();
						$product_var_id = $value->get_id();
					} else {
						$product_id 	= $value->get_id();
					}
					$hazmat_product 	= 'no';

					if (!empty($product_var_id)) {

						$hazmat_product 	= get_post_meta($product_var_id, '_ph_ups_hazardous_materials', 1);
						$hazmat_settings 	= get_post_meta($product_var_id, '_ph_ups_hazardous_settings', 1);
					}

					if ($hazmat_product != 'yes' && !empty($product_id)) {

						$hazmat_product 	= get_post_meta($product_id, '_ph_ups_hazardous_materials', 1);
						$hazmat_settings 	= get_post_meta($product_id, '_ph_ups_hazardous_settings', 1);
					}

					if ($hazmat_product == 'yes') {

						$this->is_hazmat_product = true;

						$product_id = $value->get_id();

						if (isset($ph_hazmat_details[$product_id])) {

							$ph_hazmat_details[$product_id]['PackagingTypeQuantity'] += 1;
						} else {

							$hazmat_details_arr = [];
							$hazmat_details_arr['ChemicalRecordIdentifier'] = !empty($hazmat_settings['_ph_ups_record_number']) ? $hazmat_settings['_ph_ups_record_number'] : '';
							$hazmat_details_arr['ClassDivisionNumber'] = !empty($hazmat_settings['_ph_ups_class_division_no']) ? $hazmat_settings['_ph_ups_class_division_no'] : '';
							$hazmat_details_arr['IDNumber'] = !empty($hazmat_settings['_ph_ups_commodity_id']) ? $hazmat_settings['_ph_ups_commodity_id'] : '';
							$hazmat_details_arr['TransportationMode'] = $hazmat_settings['_ph_ups_hm_transportaion_mode'];
							$hazmat_details_arr['RegulationSet'] = $hazmat_settings['_ph_ups_hm_regulations'];
							$hazmat_details_arr['PackagingGroupType'] = !empty($hazmat_settings['_ph_ups_package_group_type']) ? $hazmat_settings['_ph_ups_package_group_type'] : '';
							$hazmat_details_arr['PackagingInstructionCode'] = !empty($hazmat_settings['_ph_ups_package_instruction_code']) ? $hazmat_settings['_ph_ups_package_instruction_code'] : '';
							$hazmat_details_arr['Quantity'] = round($value->get_weight(), 1);
							$hazmat_details_arr['UOM'] = ( 'LB' === $this->settings['uom']) ? 'pound' : 'kg';
							$hazmat_details_arr['ProperShippingName'] = !empty($hazmat_settings['_ph_ups_shipping_name']) ? $hazmat_settings['_ph_ups_shipping_name'] : '';
							$hazmat_details_arr['TechnicalName'] = !empty($hazmat_settings['_ph_ups_technical_name']) ? $hazmat_settings['_ph_ups_technical_name'] : '';
							$hazmat_details_arr['AdditionalDescription'] = !empty($hazmat_settings['_ph_ups_additional_description']) ? $hazmat_settings['_ph_ups_additional_description'] : '';
							$hazmat_details_arr['PackagingType'] = !empty($hazmat_settings['_ph_ups_package_type']) ? $hazmat_settings['_ph_ups_package_type'] : '';
							$hazmat_details_arr['PackagingTypeQuantity'] = 1;
							$hazmat_details_arr['CommodityRegulatedLevelCode'] = $hazmat_settings['_ph_ups_hm_commodity'];
							$hazmat_details_arr['EmergencyPhone'] = $this->settings['phone_number'];
							$hazmat_details_arr['EmergencyContact'] = $this->settings['ups_display_name'];

							$ph_hazmat_details[$product_id] = $hazmat_details_arr;
						}
					}
				}
			}

			$total_package_weight += $package_request['Package']['PackageWeight']['Weight'];

			$package_request = $this->ph_ups_convert_weight_dimension_based_on_vendor($package_request);

			// InsuredValue in REST becomes DeclaredValue
			if ( isset($package_request['Package']['PackageServiceOptions']['InsuredValue']) ) {

				if ( $request_type != 'surepost' ) {

					$package_request['Package']['PackageServiceOptions']['DeclaredValue'] = $package_request['Package']['PackageServiceOptions']['InsuredValue'];
				}

				unset($package_request['Package']['PackageServiceOptions']['InsuredValue']);
			}

			// Converted Weight in rate request for service 92
            if ( $request_type == 'surepost' && $service_code == 92 ) {
				
                $package_request = PH_WC_UPS_Common_Utils::convert_weight($package_request, $this->settings['weight_unit'], $service_code);
            }

			//For Worldwide Express Freight Service
			if ($request_type == "Pallet") {
				$package_request['Package']['PackagingType']['Code'] = 30;
				// Setting Length, Width and Height for weight based packing.
				if (empty($package_request['Package']['Dimensions'])) {

					$package_request['Package']['Dimensions'] = array(

						//@note Removed Width node from UnitOfMeasurement and added as a separate node.

						'UnitOfMeasurement' => array(
							'Code'  		=> ($package_request['Package']['PackageWeight']['UnitOfMeasurement']['Code'] == 'LBS') ? 'IN' : 'CM',
						),
						'Length'    => ($package_request['Package']['PackageWeight']['UnitOfMeasurement']['Code'] == 'LBS') ? 47 : 119,
						'Height'    => ($package_request['Package']['PackageWeight']['UnitOfMeasurement']['Code'] == 'LBS') ? 47 : 119,
						'Width'	    => ($package_request['Package']['PackageWeight']['UnitOfMeasurement']['Code'] == 'LBS') ? 47 : 119
					);
				}
			}

			// Add Simple Rate box code in request
			if (isset($package_request['Package']['BoxCode']) && $request_type == 'simple_rate') {
				$package_request['Package']['SimpleRate']['Code'] = current(explode(':', $package_request['Package']['BoxCode']));
			}

			// Unset BoxCode once its passed as SimpleRate Code
			if (isset($package_request['Package']['BoxCode'])) {
				unset($package_request['Package']['BoxCode']);
			}


			// To Set deliveryconfirmation at shipment level if shipment is international or outside of PH_WC_UPS_Constants::DC_DOMESTIC_COUNTRIES.
			if (is_admin() && !isset($_GET['sig'])) {

				$usCountry 	= array('US', 'PR');
				$origin 	= $this->settings['origin_country'];
				$dest 		= $package['destination']['country'];

				if ( isset($package_request['Package']) && isset($package_request['Package']['items'])) {

					// To Set deliveryconfirmation at package level
					if ( (( $origin == $dest && in_array($origin, array('US','PR','CA'))) || (in_array($origin, $usCountry) && in_array($dest, $usCountry))) ) {

						$shipment_delivery_confirmation = PH_WC_UPS_Common_Utils::get_package_signature($package_request['Package']['items']);
					
						$shipment_delivery_confirmation = $shipment_delivery_confirmation != 0 ? $shipment_delivery_confirmation : $this->settings['ph_delivery_confirmation'];
						
						$shipment_delivery_confirmation = isset($_GET['sig']) && $_GET['sig'] != 4 && $_GET['sig'] != 0 ? $_GET['sig'] : $shipment_delivery_confirmation;

						if ( !empty($shipment_delivery_confirmation)) {

							$shipment_delivery_confirmation = $shipment_delivery_confirmation < 3 ? 2 : $shipment_delivery_confirmation;

							$package_request['Package']['PackageServiceOptions']['DeliveryConfirmation'] = array('DCISType' => $shipment_delivery_confirmation);
						}
					} else {

						// To Set deliveryconfirmation at shipment level
						$shipment_delivery_confirmation = PH_WC_UPS_Common_Utils::get_package_signature($package_request['Package']['items']);
						$shipment_delivery_confirmation = $shipment_delivery_confirmation < $this->settings['ph_delivery_confirmation'] ? $this->settings['ph_delivery_confirmation'] : $shipment_delivery_confirmation;

						$delivery_confirmation = (isset($delivery_confirmation) && $delivery_confirmation >= $shipment_delivery_confirmation) ? $delivery_confirmation : $shipment_delivery_confirmation;
					}
				} else {

					// For Manual Package Domestic
					if ( isset($package_request['Package']['PackageServiceOptions']['DeliveryConfirmation']) ) {

						if ( isset($_GET['sig']) && $_GET['sig'] != 0) {

							$ph_sig_value = $_GET['sig'] == 4 ? $this->settings['ph_delivery_confirmation'] : $_GET['sig'];

							if ( !empty($ph_sig_value) ) {

								$ph_sig_value = $ph_sig_value < 3 ? 2 : $ph_sig_value;

								$package_request['Package']['PackageServiceOptions']['DeliveryConfirmation'] = array('DCISType' => $ph_sig_value);
							}
						}
					}

					// For Manual Package Int
					if ( $origin != $dest && !(in_array($origin, $usCountry) && in_array($dest, $usCountry)) ) {

						if ( isset($_GET['sig']) && $_GET['sig'] != 0) {

							$ph_sig_value = $_GET['sig'] == 4 ? $this->settings['ph_delivery_confirmation'] : $_GET['sig'];

							if ( !empty($ph_sig_value) ) {

								$delivery_confirmation = $ph_sig_value;
							}
						}
					}
				}
			} else if (isset($international_delivery_confirmation_applicable) && $international_delivery_confirmation_applicable) {

				$shipment_delivery_confirmation = PH_WC_UPS_Common_Utils::get_package_signature($package_request['Package']['items']);
				$shipment_delivery_confirmation = $shipment_delivery_confirmation < $this->settings['ph_delivery_confirmation'] ? $this->settings['ph_delivery_confirmation'] : $shipment_delivery_confirmation;

				$delivery_confirmation = (isset($delivery_confirmation) && $delivery_confirmation >= $shipment_delivery_confirmation) ? $delivery_confirmation : $shipment_delivery_confirmation;
			}

			//Not required further
			if (isset($package_request['Package']['items'])) {

				unset($package_request['Package']['items']);
			}

			//Not required further
			if (isset($package_request['Package']['DirectDeliveryOnlyIndicator'])) {

				$direct_delivery_only = $package_request['Package']['DirectDeliveryOnlyIndicator'];
				unset($package_request['Package']['DirectDeliveryOnlyIndicator']);
			}

			if ($this->is_hazmat_product) {

				$hazmat_array['Package']['PackageServiceOptions']['HazMat']['PackageIdentifier'] = $hazmat_package_identifier++;
				$hazmat_array['Package']['PackageServiceOptions']['HazMat']['HazMatChemicalRecord']	=	array_values($ph_hazmat_details);
				$package_request = array_merge_recursive($package_request, $hazmat_array);
			}

			//For Package level COD in Edit Order Page.
			if ((isset($ups_cod) && !empty($ups_cod) && $ups_cod == 'true') && isset($_GET['wf_ups_generate_packages_rates'])) {

				$destination = isset($this->destination['country']) && !empty($this->destination['country']) ? $this->destination['country'] : $package['destination']['country'];

				if (! PH_WC_UPS_Common_Utils::is_shipment_level_cod_required($destination)) {

					$codfundscode = in_array($destination, array('AR', 'BR', 'CL')) ? 9 : 0;

					//@note Added CurrencyCode ( mandatory )
					//@note Commented 'CODCode' since the node is not present

					$cod_array['Package']['PackageServiceOptions']['COD']	=	array(
						// 'CODCode'		=>	3,
						'CODFundsCode'	=>	$codfundscode,
						'CODAmount'		=>	array(
							'CurrencyCode'	=> $this->settings['currency_type'],
							'MonetaryValue'	=>	(string) round($cod_amount, 2),
						),
					);

					$package_request = array_merge_recursive($package_request, $cod_array);
				}
			}

			//@note Removing box_name node from $package_request as it is not required
			if (isset($package_request['Package']['box_name'])) {
				unset($package_request['Package']['box_name']);
			}

			$jsonRequest['RateRequest']['Shipment']['Package'][] = $package_request['Package'];
		}

		// negotiated rates flag
		if ($this->settings['negotiated']) {
			$jsonRequest['RateRequest']['Shipment']['ShipmentRatingOptions']['NegotiatedRatesIndicator'] = 1;
		}

		if ($this->settings['tax_indicator']) {
			$jsonRequest['RateRequest']['Shipment']['TaxInformationIndicator'] = 1;
		}

		//Checking if delivery confirmation changed in edit order page
		if (isset($_GET['sig']) && isset($delivery_confirmation)) {

			PH_UPS_WC_Storage_Handler::ph_add_and_save_meta_data($order_id, '_ph_ups_delivery_signature', $_GET['sig']);

			$delivery_confirmation = ($_GET['sig'] != 4) ? $_GET['sig'] : $delivery_confirmation;
		}

		//Checking Direct Delivery Only selected in edit order page
		if (isset($_GET['ddo']) && !empty($_GET['ddo'])) {

			$direct_delivery_only = $_GET['ddo'];

			PH_UPS_WC_Storage_Handler::ph_add_and_save_meta_data($order_id, '_ph_ups_direct_delivery', $_GET['ddo']);

		}

		$delivery_confirmation = (isset($delivery_confirmation) && ($delivery_confirmation != 0)) ? $delivery_confirmation : '';

		// Set deliveryconfirmation at shipment level for international shipment
		if (isset($delivery_confirmation) && !empty($delivery_confirmation)) {
			$delivery_confirmation = ($delivery_confirmation == 3) ? 2 : 1;
			$jsonRequest['RateRequest']['Shipment']['ShipmentServiceOptions']['DeliveryConfirmation']['DCISType'] = $delivery_confirmation;
		}

		if ((isset($import_control) && !empty($import_control) && $import_control == 'true') || $this->settings['import_control_settings'] == 'yes') {
			$jsonRequest['RateRequest']['Shipment']['ShipmentServiceOptions']['ImportControl']['Code'] = '02';
		}

		if ($request_type == 'saturday') {
			$jsonRequest['RateRequest']['Shipment']['ShipmentServiceOptions']['SaturdayDeliveryIndicator'] = 1;
		}

		if (isset($direct_delivery_only) && !empty($direct_delivery_only) && $direct_delivery_only == 'yes') {
			$jsonRequest['RateRequest']['Shipment']['ShipmentServiceOptions']['DirectDeliveryOnlyIndicator'] = 1;
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
				$jsonRequest['RateRequest']['Shipment']['ShipmentServiceOptions']['RestrictedArticles']['AlcoholicBeveragesIndicator'] = 1;
			}

			if ($diagnosticspecimensindicator == 'yes') {
				$jsonRequest['RateRequest']['Shipment']['ShipmentServiceOptions']['RestrictedArticles']['DiagnosticSpecimensIndicator'] = 1;
			}

			if ($perishablesindicator == 'yes') {
				$jsonRequest['RateRequest']['Shipment']['ShipmentServiceOptions']['RestrictedArticles']['PerishablesIndicator'] = 1;
			}

			if ($plantsindicator == 'yes') {
				$jsonRequest['RateRequest']['Shipment']['ShipmentServiceOptions']['RestrictedArticles']['PlantsIndicator'] = 1;
			}

			if ($seedsindicator == 'yes') {
				$jsonRequest['RateRequest']['Shipment']['ShipmentServiceOptions']['RestrictedArticles']['SeedsIndicator'] = 1;
			}

			if ($specialexceptionsindicator == 'yes') {
				$jsonRequest['RateRequest']['Shipment']['ShipmentServiceOptions']['RestrictedArticles']['SpecialExceptionsIndicator'] = 1;
			}

			if ($tobaccoindicator == 'yes') {
				$jsonRequest['RateRequest']['Shipment']['ShipmentServiceOptions']['RestrictedArticles']['TobaccoIndicator'] = 1;
			}
		}

		if (($this->settings['cod_enable'] && !isset($ups_cod))  || (isset($ups_cod) && !empty($ups_cod) && $ups_cod == 'true')) {

			$destination = isset($this->destination['country']) && !empty($this->destination['country']) ? $this->destination['country'] : $package['destination']['country'];

			if ( PH_WC_UPS_Common_Utils::is_shipment_level_cod_required($destination)) {

				$cod_amount = isset($package['cart_subtotal']) && !empty($package['cart_subtotal']) ? $package['cart_subtotal'] : $package['contents_cost'];
				// 1 for Cash, 9 for Cheque, 1 is available for all the countries
				$codfundscode = in_array($destination, array('RU', 'AE')) ? 1 : $this->settings['eu_country_cod_type'];

				//@note Added CurrencyCode and its value ( mandatory )
				//@note Commented 'CODCode' since the node is not present

				$jsonRequest['RateRequest']['Shipment']['ShipmentServiceOptions']['COD'] = array(
					'CODFundsCode' => $codfundscode,
					'CODAmount'    => array(
						'CurrencyCode'	=> $this->settings['currency_type'],
						'MonetaryValue' => $cod_amount,
					),
				);
			}
		}

		// Required for estimated delivery
		if ($this->settings['enable_estimated_delivery']) {

			//cuttoff time- PDS-80
			if (!empty($this->settings['cut_off_time']) && $this->settings['cut_off_time'] != '24:00') {

				$timestamp = clone $this->settings['current_wp_time'];
				$this->current_wp_time_hour_minute = current_time('H:i');

				if ($this->current_wp_time_hour_minute > $this->settings['cut_off_time']) {

					$timestamp->modify('+1 days');
					$this->pickup_date = $timestamp->format('Ymd');
					$this->pickup_time = '0800';
				} else {
					$this->pickup_date = date('Ymd');
					$this->pickup_time = $timestamp->format('Hi');
				}
			} else {
				$this->pickup_date = date('Ymd');
				$this->pickup_time = current_time('Hi');
			}

			// Adjust estimated delivery based on Ship time adjsutment settings
			if (!empty($this->settings['shipTimeAdjustment'])) {
				
				$dateObj = new DateTime($this->pickup_date);
				$dateObj->modify("+ {$this->settings['shipTimeAdjustment']} days");
				$this->pickup_date = $dateObj->format('Ymd');
			}

			$jsonRequest['RateRequest']['Shipment']['DeliveryTimeInformation'] = array(
				'PackageBillType' => '03',
				'Pickup' 		  => array(
					'Date'		  => $this->pickup_date,
					'Time'		  => $this->pickup_time,
				),
			);

			//@note Added Description to UnitOfMeasurement ( mandatory )
			$jsonRequest['RateRequest']['Shipment']['ShipmentTotalWeight'] = array(
				'UnitOfMeasurement' => array(
					'Code'			=> $this->settings['weight_unit'],
					'Description'	=>	'Mass Unit'
				),
				'Weight'			=> $total_package_weight,
			);

			if ($this->settings['ship_from_country'] != $package['destination']['country']) {

				if (empty($package['contents_cost']) && isset($package['cart_subtotal'])) {

					$package['contents_cost'] = $package['cart_subtotal'];
				}

				$invoiceTotal  = round(($package['contents_cost'] / (float)$this->settings['conversion_rate']), 2);

				// Invoice Line Total amount for the shipment.
				// Valid values are from 1 to 99999999
				if ($invoiceTotal < 1) {
					$invoiceTotal 	= 1;
				}

				$jsonRequest['RateRequest']['Shipment']['InvoiceLineTotal'] = array(
					'CurrencyCode'	 => $this->settings['currency_type'],
					'MonetaryValue'	 => $invoiceTotal,
				);
			}
		}

		if ($this->is_hazmat_product) {
			$jsonRequest['RateRequest']['Request']['SubVersion'] = '1701';
		}

		return apply_filters('wf_ups_rate_request', $jsonRequest, $package);
	}

	/**
	 * Generates a rate request for a UPS Access Point.
	 *
	 * @param mixed $ph_ups_selected_access_point_details The details of the selected UPS Access Point.
	 *        This parameter should contain necessary information to retrieve the access point address.
	 *
	 * @return array The constructed rate request array containing the address and shipment indication type.
	 *         If the provided details are invalid or insufficient, an empty array is returned.
	 */
	public function get_acccesspoint_rate_request( $ph_ups_selected_access_point_details ) {
		//Getting accesspoint address details
		$access_request = array();
		$shipping_accesspoint = PH_WC_UPS_Common_Utils::wf_get_accesspoint_datas( $ph_ups_selected_access_point_details );

		if (!empty($shipping_accesspoint) && is_string($shipping_accesspoint)) {
			$decoded_accesspoint = json_decode($shipping_accesspoint);
			if (isset($decoded_accesspoint->AddressKeyFormat)) {

				$accesspoint_addressline	= $decoded_accesspoint->AddressKeyFormat->AddressLine;
				$accesspoint_city			= (property_exists($decoded_accesspoint->AddressKeyFormat, 'PoliticalDivision2')) ? $decoded_accesspoint->AddressKeyFormat->PoliticalDivision2 : '';
				$accesspoint_state			= (property_exists($decoded_accesspoint->AddressKeyFormat, 'PoliticalDivision1')) ? $decoded_accesspoint->AddressKeyFormat->PoliticalDivision1 : '';
				$accesspoint_postalcode		= $decoded_accesspoint->AddressKeyFormat->PostcodePrimaryLow;
				$accesspoint_country		= $decoded_accesspoint->AddressKeyFormat->CountryCode;

				$access_request['ShipmentIndicationType']['Code'] = '01';

				$access_request['AlternateDeliveryAddress']['Address'] = array(
					'AddressLine' 			=> $accesspoint_addressline,
					'City'					=> $accesspoint_city,
					'StateProvinceCode'		=> $accesspoint_state,
					'PostalCode'			=> $accesspoint_postalcode,
					'CountryCode'			=> $accesspoint_country
				);
			}
		}

		return $access_request;
	}

	/**
	 * ph_get_api_rate_box_data function.
	 *
	 * Retrieves API rate box data for shipping.
	 *
	 * @param array $package The package data.
	 * @param string $packing_method The packing method.
	 * @param array $params Additional parameters (optional).
	 * @return array The array of API rate box data requests.
	 */
	public function ph_get_api_rate_box_data( $package, $packing_method, $params = array())
	{
		$this->settings['packing_method'] = $packing_method;
		$package_generator 		= new PH_WC_UPS_Package_Generator( $this->settings );
		$requests 				= $package_generator->get_package_requests($package, $this->order, $params);

		return $requests;
	}

	/**
	 * ph_set_cod_details function.
	 *
	 * Sets Cash On Delivery (COD) details for an order.
	 *
	 * @param object $order The order object.
	 * @return void
	 */
	public function ph_set_cod_details( $order )
	{
		if ($order->get_id()) {

			$this->settings['cod'] 			= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order->get_id(), '_wf_ups_cod');
			$this->settings['cod_total'] 	= $order->get_total();
		}
	}

	/**
	 * ph_set_service_code function.
	 *
	 * Sets the service code for UPS shipping.
	 *
	 * @param int $service_code The service code.
	 * @return void
	 */
	public function ph_set_service_code( $service_code )
	{
		$this->settings['service_code'] = $service_code;
	}

	/**
	 * Retrieves the currency type setting for UPS.
	 * 
	 * @return string The currency type configured for UPS.
	 */
	public function get_ups_currency() {
		return $this->settings['currency_type'];
	}

	/**
	 * Convert weight and dimension based on vendor origin address
	 *
	 * @param array $package
	 * @return array $package
	 */
	public function ph_ups_convert_weight_dimension_based_on_vendor($package) {

		// Converting weight and dimensions based on Vendor origin address
		if (isset($package['Package']['metrics'])) {

			if ($package['Package']['metrics']) {

				if (isset($package['Package']['Dimensions']) && !empty($package['Package']['Dimensions']) && $package['Package']['Dimensions']['UnitOfMeasurement']['Code'] != 'CM') {
					$package['Package']['Dimensions']['UnitOfMeasurement']['Code'] = 'CM';
					$package['Package']['Dimensions']['Length'] = round(wc_get_dimension($package['Package']['Dimensions']['Length'], 'CM', 'in'), 2);
					$package['Package']['Dimensions']['Width']	= round(wc_get_dimension($package['Package']['Dimensions']['Width'], 'CM', 'in'), 2);
					$package['Package']['Dimensions']['Height']	= round(wc_get_dimension($package['Package']['Dimensions']['Height'], 'CM', 'in'), 2);
				}

				if ($package['Package']['PackageWeight']['UnitOfMeasurement']['Code'] != 'KGS') {
					$this->settings['weight_unit'] = 'KGS';
					$package['Package']['PackageWeight']['UnitOfMeasurement']['Code']	= 'KGS';
					$package['Package']['PackageWeight']['Weight']	= round(wc_get_weight($package['Package']['PackageWeight']['Weight'], 'KGS', 'lbs'), 2);
				}
			} else {

				if (isset($package['Package']['Dimensions']) && !empty($package['Package']['Dimensions']) && $package['Package']['Dimensions']['UnitOfMeasurement']['Code'] != 'IN' && $this->settings['units'] == 'metric') {
					$package['Package']['Dimensions']['UnitOfMeasurement']['Code'] = 'IN';
					$package['Package']['Dimensions']['Length'] = round(wc_get_dimension($package['Package']['Dimensions']['Length'], 'IN', 'cm'), 2);
					$package['Package']['Dimensions']['Width']	= round(wc_get_dimension($package['Package']['Dimensions']['Width'], 'IN', 'cm'), 2);
					$package['Package']['Dimensions']['Height']	= round(wc_get_dimension($package['Package']['Dimensions']['Height'], 'IN', 'cm'), 2);
				}

				if ($package['Package']['PackageWeight']['UnitOfMeasurement']['Code'] != 'LBS' && $this->settings['units'] == 'metric') {
					$this->settings['weight_unit'] = 'LBS';
					$package['Package']['PackageWeight']['UnitOfMeasurement']['Code']	= 'LBS';
					$package['Package']['PackageWeight']['Weight']	= round(wc_get_weight($package['Package']['PackageWeight']['Weight'], 'LBS', 'kg'), 2);
				}
			}

			// Unset metrics
			unset($package['Package']['metrics']);
		}
		return $package;
	}
}
