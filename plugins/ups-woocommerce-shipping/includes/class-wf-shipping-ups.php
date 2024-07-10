<?php

include('class-wf-freight-ups.php');
include('class-ph-ups-help-and-support.php');

/**
 * WF_Shipping_UPS class.
 *
 * @extends WC_Shipping_Method
 */
class WF_Shipping_UPS extends WC_Shipping_Method
{
	public $mode = 'volume_based';
	private $endpoint = 'https://wwwcie.ups.com/ups.app/xml/Rate';
	private $freight_endpoint = 'https://wwwcie.ups.com/rest/FreightRate';
	public $vendorId = null;
	public $services;

	/**
	 * For Delivery Confirmation below array of countries will be considered as domestic, Confirmed by UPS.
	 * US to US, CA to CA, PR to PR are considered as domestic, all other shipments are international.
	 * @var array 
	 */
	public $dc_domestic_countries = array('US', 'CA', 'PR');

	private $freight_services = array(
		'308' => 'TForce Freight LTL',
		'309' => 'TForce Freight LTL - Guaranteed',
		'334' => 'TForce Freight LTL - Guaranteed A.M.',
		'349' => 'TForce Freight LTL Mexico',
	);

	public $freight_package_type_code_list = array(
		"BAG" => "Bag",
		"BAL" => "Bale",
		"BAR" => "Barrel",
		"BDL" => "Bundle",
		"BIN" => "Bin",
		"BOX" => "Box",
		"BSK" => "Basket",
		"BUN" => "Bunch",
		"CAB" => "Cabinet",
		"CAN" => "Can",
		"CAR" => "Carrier",
		"CAS" => "Case",
		"CBY" => "Carboy",
		"CON" => "Container",
		"CRT" => "Crate",
		"CSK" => "Cask",
		"CTN" => "Carton",
		"CYL" => "Cylinder",
		"DRM" => "Drum",
		"LOO" => "Loose",
		"OTH" => "Other",
		"PAL" => "Pail",
		"PCS" => "Pieces",
		"PKG" => "Package",
		"PLN" => "Pipe Line",
		"PLT" => "Pallet",
		"RCK" => "Rack",
		"REL" => "Reel",
		"ROL" => "Roll",
		"SKD" => "Skid",
		"SPL" => "Spool",
		"TBE" => "Tube",
		"TNK" => "Tank",
		"UNT" => "Unit",
		"VPK" => "Van Pack",
		"WRP" => "Wrapped",
	);
	public  $freight_package_type_code = 'PLT';
	public 	$freight_shippernumber = '';
	public 	$freight_billing_option_code = '10';
	public 	$freight_billing_option_code_list = array('10' => 'Prepaid', '30' => 'Bill to Third Party', '40' => 'Freight Collect');
	public 	$freight_handling_unit_one_type_code = 'PLT';

	private $ups_surepost_services = array(92, 93, 94, 95);

	private $cod_currency_specific_contries = array(
		'BE' => 'EUR',
		'BG' => 'EUR',
		'CZ' => 'EUR',
		'DK' => 'EUR',
		'DE' => 'EUR',
		'EE' => 'EUR',
		'IE' => 'EUR',
		'GR' => 'EUR',
		'ES' => 'EUR',
		'FR' => 'EUR',
		'HR' => 'EUR',
		'IT' => 'EUR',
		'CY' => 'EUR',
		'LV' => 'EUR',
		'LT' => 'EUR',
		'LU' => 'EUR',
		'HU' => 'EUR',
		'MT' => 'EUR',
		'NL' => 'EUR',
		'AT' => 'EUR',
		'PT' => 'EUR',
		'RO' => 'EUR',
		'SI' => 'EUR',
		'SK' => 'EUR',
		'FI' => 'EUR',
		'GB' => 'EUR',
		'PL' => 'EUR',
		'SE' => 'EUR',
	);

	// Packaging not offered at this time: 00 = UNKNOWN, 30 = Pallet, 04 = Pak
	// Code 21 = Express box is valid code, but doesn't have dimensions
	// References:
	// http://www.ups.com/content/us/en/resources/ship/packaging/supplies/envelopes.html
	// http://www.ups.com/content/us/en/resources/ship/packaging/supplies/paks.html
	// http://www.ups.com/content/us/en/resources/ship/packaging/supplies/boxes.html
	private $packaging = array(
		"A_UPS_LETTER" => array(
			"name" 	 => "UPS Letter",
			"code"	 => '01',
			"length" => "12.5",
			"width"  => "9.5",
			"height" => "0.25",
			"weight" => "0.5",
			"box_enabled" => true
		),
		"B_TUBE" => array(
			"name" 	 => "Tube",
			"code"	 => "03",
			"length" => "38",
			"width"  => "6",
			"height" => "6",
			"weight" => "100",
			"box_enabled" => true
		),
		"C_PAK" => array(
			"name" 	 => "PAK",
			"code"	 => "04",
			"length" => "17",
			"width"  => "13",
			"height" => "1",
			"weight" => "100",
			"box_enabled" => true
		),
		"D_25KG_BOX" => array(
			"name" 	 => "25KG Box",
			"code"	 => "24",
			"length" => "19.375",
			"width"  => "17.375",
			"height" => "14",
			"weight" => "25",
			"box_enabled" => true
		),
		"E_10KG_BOX" => array(
			"name" 	 => "10KG Box",
			"code"	 => "25",
			"length" => "16.5",
			"width"  => "13.25",
			"height" => "10.75",
			"weight" => "10",
			"box_enabled" => true
		),
		"F_SMALL_EXPRESS_BOX" => array(
			"name" 	 => "Small Express Box",
			"code"	 => "2a",
			"length" => "13",
			"width"  => "11",
			"height" => "2",
			"weight" => "100",
			"box_enabled" => true
		),
		"G_MEDIUM_EXPRESS_BOX" => array(
			"name" 	 => "Medium Express Box",
			"code"	 => "2b",
			"length" => "15",
			"width"  => "11",
			"height" => "3",
			"weight" => "100",
			"box_enabled" => true
		),
		"H_LARGE_EXPRESS_BOX" => array(
			"name" 	 => "Large Express Box",
			"code"	 => "2c",
			"length" => "18",
			"width"  => "13",
			"height" => "3",
			"weight" => "30",
			"box_enabled" => true
		)
	);

	/**
	 * UPS Id
	 */
	public $id;
	/**
	 * General Settings
	 */
	public $settings, $import_control_settings;
	public $api_mode, $ups_tradability, $skip_products, $tax_indicator, $negotiated, $ups_user_name, $ups_display_name, $phone_number, $residential, $isc, $soap_available, $accesspoint_locator, $address_validation, $xa_show_all, $min_order_amount_for_insurance, $enable_freight, $upsSimpleRate, $enable_density_based_rating;
	public $countries, $enabled, $title, $cheapest_rate_title, $availability, $method_title, $method_description, $rate_caching, $customer_classification, $offer_rates, $fallback, $currency_type, $conversion_rate, $min_amount, $fixedProductPrice, $insuredvalue, $hst_lc;
	/**
	 * Packing
	 */
	public $packing_method, $ups_packaging, $weight_packing_process;
	/**
	 * Order
	 */
	public $order;
	/**
	 * Est Delivery
	 */
	public $show_est_delivery, $wp_date_time_format, $estimated_delivery_text, $shipTimeAdjustment, $current_wp_time, $current_wp_time_hour_minute;
	/**
	 * Debug
	 */
	public $debug, $silent_debug;
	/**
	 * UPS Account Details Variables
	 */
	public $user_id, $access_key, $password, $shipper_number;
	/**
	 * Label Settings
	 */
	public $disble_ups_print_label, $print_label_type, $show_label_in_browser, $ship_from_address, $disble_shipment_tracking;
	/**
	 * Pickup
	 */
	public $pickup;
	/**
	 * Boxes
	 */
	public $boxes, $simpleRateBoxes;
	/**
	 * Weight and Dimensions Variables
	 */
	public $exclude_box_weight, $stack_to_volume, $density_length, $density_width, $density_height, $box_max_weight, $min_weight_limit, $max_weight_limit;

	public $eu_country_cod_type;

	/**
	 * Package
	 */
	public $current_package_items_and_quantity;
	/**
	 * Unit Variables
	 */
	public $savedMetrics, $wc_weight_unit, $units, $weight_unit, $dim_unit, $density_unit, $uom;
	/**
	 * COD Variables
	 */
	public $cod, $cod_total, $cod_enable;
	/**
	 * Address Variables
	 */
	public $freight_class, $destination, $origin_addressline, $origin_addressline_2, $origin_city, $origin_postcode, $origin_country_state, $origin_country, $ship_from_address_different_from_shipper, $ship_from_addressline, $ship_from_addressline_2, $ship_from_city, $ship_from_postcode, $ship_from_country_state, $ship_from_country, $ship_from_state, $origin_state, $ship_from_custom_state, $origin_custom_state;
	/**
	 * Freight Variables
	 */
	public $freight_weekend_pickup, $freight_holiday_pickup, $freight_inside_pickup, $freight_residential_pickup, $freight_liftgate_pickup, $freight_holiday_delivery, $freight_inside_delivery, $freight_weekend_delivery, $freight_liftgate_delivery, $freight_limitedaccess_delivery, $freight_call_before_delivery, $freight_limitedaccess_pickup, $ship_from_address_for_freight, $freight_payment_information, $freight_thirdparty_contact_name, $freight_thirdparty_addressline, $freight_thirdparty_addressline_2, $freight_thirdparty_city, $freight_thirdparty_postcode, $freight_thirdparty_state, $freight_thirdparty_country_state, $freight_thirdparty_country;
	/**
	 * Custom Fields Variables
	 */
	public $ph_delivery_confirmation, $saturday_delivery, $rate_conversion, $ph_restricted_article, $ph_diog, $ph_perishable, $ph_alcoholic, $ph_plantsindicator, $ph_seedsindicator, $ph_specialindicator, $ph_tobaccoindicator, $is_hazmat_product;
	/**
	 * Services
	 */
	public  $custom_services, $service_code, $ordered_services;
	/**
	 * Access Point
	 */
	public $ph_ups_selected_access_point_details;
	/**
	 * Delivery Confirmation
	 */
	public $international_delivery_confirmation_applicable;
	/**
	 * Pickup Variables
	 */
	public $pickup_date, $pickup_time;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct($order = null)
	{
		if ($order) {
			$this->order = $order;
		}

		$plugin_config = ph_wc_ups_plugin_configuration();

		$this->id 					= $plugin_config['id'];
		$this->method_title 		= $plugin_config['method_title'];
		$this->method_description 	= $plugin_config['method_description'];

		// WF: Load UPS Settings.
		$ups_settings 					= get_option('woocommerce_' . WF_UPS_ID . '_settings', null);

		$this->wc_weight_unit 	= get_option('woocommerce_weight_unit');
		$ups_settings			= apply_filters('ph_ups_plugin_settings', $ups_settings, $order);
		$api_mode	  		= isset($ups_settings['api_mode']) ? $ups_settings['api_mode'] : 'Test';

		if ("Live" == $api_mode) {
			$this->endpoint = 'https://onlinetools.ups.com/ups.app/xml/Rate';
			$this->freight_endpoint = 'https://onlinetools.ups.com/rest/FreightRate';
		} else {
			$this->endpoint = 'https://wwwcie.ups.com/ups.app/xml/Rate';
			$this->freight_endpoint = 'https://wwwcie.ups.com/rest/FreightRate';
		}

		$this->init();

		// Add Estimated delivery to cart rates
		if ($this->show_est_delivery) {
			add_filter('woocommerce_cart_shipping_method_full_label', array($this, 'ph_ups_add_delivery_time'), 10, 2);
		}
	}

	/**
	 * Append the estimated delivery time to the shipping method label.
	 *
	 * @param string $label The current shipping method label.
	 * @param object $method The shipping method object.
	 * @return string The modified shipping method label with the estimated delivery time appended.
	 */
	public function ph_ups_add_delivery_time($label, $method) {
		return PH_WC_UPS_Common_Utils::ph_update_delivery_time( $label, $method, $this->wp_date_time_format, $this->estimated_delivery_text );
	}

	/**
	 * init function.
	 *
	 * @access public
	 * @return void
	 */
	private function init()
	{
		global $woocommerce;

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();
		$this->settings	=	apply_filters('ph_ups_plugin_settings', $this->settings, '');

		$this->isc = isset($this->settings['international_special_commodities']) && !empty($this->settings['international_special_commodities']) && $this->settings['international_special_commodities'] == 'yes' ? true : false;

		$this->soap_available 		= Ph_UPS_Woo_Shipping_Common::is_soap_available() ? true : false;

		// Define user set variables.
		$this->mode 					= isset($this->settings['packing_algorithm']) ? $this->settings['packing_algorithm'] : 'volume_based';
		$this->exclude_box_weight 		= (isset($this->settings['exclude_box_weight']) && $this->settings['exclude_box_weight'] == 'yes') ? true : false;
		$this->stack_to_volume 			= (isset($this->settings['stack_to_volume']) && $this->settings['stack_to_volume'] == 'yes') ? true : false;
		$this->enabled					= isset($this->settings['enabled']) ? $this->settings['enabled'] : $this->enabled;
		$this->title					= isset($this->settings['title']) ? $this->settings['title'] : $this->method_title;
		$this->cheapest_rate_title		= isset($this->settings['title']) ? $this->settings['title'] : null;
		$this->availability				= isset($this->settings['availability']) ? $this->settings['availability'] : 'all';
		$this->countries	   			= isset($this->settings['countries']) ? $this->settings['countries'] : array();

		// API Settings
		$this->user_id		 		= isset($this->settings['user_id']) ? $this->settings['user_id'] : '';

		// WF: Print Label - Start
		$this->disble_ups_print_label	= isset($this->settings['disble_ups_print_label']) ? $this->settings['disble_ups_print_label'] : '';
		$this->print_label_type	  	= isset($this->settings['print_label_type']) ? $this->settings['print_label_type'] : 'gif';
		$this->show_label_in_browser	= isset($this->settings['show_label_in_browser']) ? $this->settings['show_label_in_browser'] : 'no';
		$this->ship_from_address	  	= isset($this->settings['ship_from_address']) ? $this->settings['ship_from_address'] : 'origin_address';
		$this->disble_shipment_tracking	= isset($this->settings['disble_shipment_tracking']) ? $this->settings['disble_shipment_tracking'] : 'TrueForCustomer';
		$this->api_mode	  			= isset($this->settings['api_mode']) ? $this->settings['api_mode'] : 'Test';
		$this->ups_user_name			= isset($this->settings['ups_user_name']) ? $this->settings['ups_user_name'] : '';
		$this->ups_display_name			= isset($this->settings['ups_display_name']) ? $this->settings['ups_display_name'] : '';
		$this->phone_number 			= isset($this->settings['phone_number']) ? $this->settings['phone_number'] : '';
		// WF: Print Label - End

		$this->user_id		 		= isset($this->settings['user_id']) ? $this->settings['user_id'] : '';
		$this->password				= isset($this->settings['password']) ? $this->settings['password'] : '';
		$this->access_key	  		= isset($this->settings['access_key']) ? $this->settings['access_key'] : '';
		$this->shipper_number  		= isset($this->settings['shipper_number']) ? $this->settings['shipper_number'] : '';

		$this->negotiated	  		= isset($this->settings['negotiated']) && $this->settings['negotiated'] == 'yes' ? true : false;
		$this->tax_indicator	  	= isset($this->settings['tax_indicator']) && $this->settings['tax_indicator'] == 'yes' ? true : false;
		$this->origin_addressline 	= isset($this->settings['origin_addressline']) ? $this->settings['origin_addressline'] : '';
		$this->origin_addressline_2 = isset($this->settings['origin_addressline_2']) ? $this->settings['origin_addressline_2'] : '';
		$this->origin_city 			= isset($this->settings['origin_city']) ? $this->settings['origin_city'] : '';
		$this->origin_postcode 		= isset($this->settings['origin_postcode']) ? $this->settings['origin_postcode'] : '';
		$this->origin_country_state = isset($this->settings['origin_country_state']) ? $this->settings['origin_country_state'] : '';
		$this->debug	  			= isset($this->settings['debug']) && $this->settings['debug'] == 'yes' ? true : false;
		$this->silent_debug			= isset($this->settings['silent_debug']) && $this->settings['silent_debug'] == 'yes' ? true : false;

		// Estimated delivery : Start
		$this->show_est_delivery		= (isset($this->settings['enable_estimated_delivery']) && $this->settings['enable_estimated_delivery'] == 'yes') ? true : false;
		$this->estimated_delivery_text	= !empty($this->settings['estimated_delivery_text']) ? $this->settings['estimated_delivery_text'] : null;
		$this->shipTimeAdjustment 		= (isset($this->settings['ship_time_adjustment']) && !empty($this->settings['ship_time_adjustment'])) ? $this->settings['ship_time_adjustment'] : '';

		$this->fixedProductPrice	= (isset($this->settings['fixed_product_price']) && !empty($this->settings['fixed_product_price'])) ? $this->settings['fixed_product_price'] : 1;

		if ($this->show_est_delivery) {
			if (empty($this->current_wp_time)) {
				$current_time 			= current_time('Y-m-d H:i:s');
				$this->current_wp_time 	= date_create($current_time);
			}
			if (empty($this->wp_date_time_format)) {
				$this->wp_date_time_format = Ph_UPS_Woo_Shipping_Common::get_wordpress_date_format() . ' ' . Ph_UPS_Woo_Shipping_Common::get_wordpress_time_format();
			}
		}
		// Estimated delivery : End

		$this->rate_caching 	= (isset($this->settings['ups_rate_caching']) && !empty($this->settings['ups_rate_caching'])) ? $this->settings['ups_rate_caching'] : '24';

		// Pickup and Destination
		$this->pickup			= isset($this->settings['pickup']) ? $this->settings['pickup'] : '01';
		$this->customer_classification = isset($this->settings['customer_classification']) ? $this->settings['customer_classification'] : '99';
		$this->residential		= isset($this->settings['residential']) && $this->settings['residential'] == 'yes' ? true : false;

		// Services and Packaging
		$this->offer_rates	 	= isset($this->settings['offer_rates']) ? $this->settings['offer_rates'] : 'all';
		$this->fallback		   	= !empty($this->settings['fallback']) ? $this->settings['fallback'] : '';
		$this->currency_type	= !empty($this->settings['currency_type']) ? $this->settings['currency_type'] : get_woocommerce_currency();
		$this->conversion_rate	= !empty($this->settings['conversion_rate']) ? $this->settings['conversion_rate'] : 1;
		$this->packing_method  	= isset($this->settings['packing_method']) ? $this->settings['packing_method'] : 'per_item';
		$this->ups_packaging	= isset($this->settings['ups_packaging']) ? $this->settings['ups_packaging'] : array();
		$this->custom_services  = isset($this->settings['services']) ? $this->settings['services'] : array();
		$this->boxes		   	= isset($this->settings['boxes']) ? $this->settings['boxes'] : array();
		$this->insuredvalue 	= isset($this->settings['insuredvalue']) && $this->settings['insuredvalue'] == 'yes' ? true : false;
		$this->min_order_amount_for_insurance = !empty($this->settings['min_order_amount_for_insurance']) ? $this->settings['min_order_amount_for_insurance'] : 0;
		$this->enable_freight 	= isset($this->settings['enable_freight']) && $this->settings['enable_freight'] == 'yes' ? true : false;
		$this->upsSimpleRate	= isset($this->settings['ups_simple_rate']) && $this->settings['ups_simple_rate'] == 'yes' ? true : false;

		$this->enable_density_based_rating = (isset($this->settings['enable_density_based_rating']) && $this->settings['enable_density_based_rating'] == 'yes') ? true : false;
		$this->density_length 	= (isset($this->settings['density_length']) && !empty($this->settings['density_length'])) ? $this->settings['density_length'] : 0;
		$this->density_width 	= (isset($this->settings['density_width']) && !empty($this->settings['density_width'])) ? $this->settings['density_width'] : 0;
		$this->density_height 	= (isset($this->settings['density_height']) && !empty($this->settings['density_height'])) ? $this->settings['density_height'] : 0;

		$this->freight_class	= !empty($this->settings['freight_class']) ? $this->settings['freight_class'] : 50;

		$this->box_max_weight			=	$this->get_option('box_max_weight');
		$this->weight_packing_process	=	$this->get_option('weight_packing_process');
		$this->service_code 	= '';
		$this->min_amount	   = isset($this->settings['min_amount']) ? $this->settings['min_amount'] : 0;
		// $this->ground_freight 	= isset( $this->settings['ground_freight'] ) && $this->settings['ground_freight'] == 'yes' ? true : false;

		// Units
		$this->units			= isset($this->settings['units']) ? $this->settings['units'] : 'imperial';

		$this->savedMetrics		= $this->get_option('units');

		if ($this->units == 'metric') {
			$this->weight_unit = 'KGS';
			$this->dim_unit	= 'CM';
			$this->simpleRateBoxes = PH_WC_UPS_Constants::UPS_SIMPLE_RATE_BOXES_IN_CMS;
			$this->packaging 	   = PH_WC_UPS_Constants::UPS_DEFAULT_BOXES_IN_CMS;
		} else {
			$this->weight_unit = 'LBS';
			$this->dim_unit	= 'IN';
			$this->simpleRateBoxes = PH_WC_UPS_Constants::UPS_SIMPLE_RATE_BOXES_IN_INCHES;
			$this->packaging 	   = PH_WC_UPS_Constants::UPS_DEFAULT_BOXES_IN_INCHES;
		}

		$this->uom = ($this->units == 'imperial') ? 'LB' : 'KG';

		//Advanced Settings
		$this->accesspoint_locator 	= (isset($this->settings['accesspoint_locator']) && $this->settings['accesspoint_locator'] == 'yes') ? true : false;
		$this->address_validation	= (isset($this->settings['address_validation']) && $this->settings['address_validation'] == 'yes') ? true : false;

		$this->xa_show_all		= isset($this->settings['xa_show_all']) && $this->settings['xa_show_all'] == 'yes' ? true : false;

		$this->origin_country = PH_WC_UPS_Common_Utils::ph_get_origin_country_and_state( $this->settings, 'country' );
		$this->origin_state   = PH_WC_UPS_Common_Utils::ph_get_origin_country_and_state( $this->settings, 'state' );

		// COD selected
		$this->cod 					= false;
		$this->cod_total 			= 0;
		$this->cod_enable 			= (isset($this->settings['cod_enable']) && !empty($this->settings['cod_enable']) && $this->settings['cod_enable'] == 'yes') ? true : false;
		$this->eu_country_cod_type 	= isset($this->settings['eu_country_cod_type']) && !empty($this->settings['eu_country_cod_type']) ? $this->settings['eu_country_cod_type'] : 9;
		
		$this->services = PH_WC_UPS_Common_Utils::get_services_based_on_origin( $this->origin_country );

		// Different Ship From Address
		$this->ship_from_address_different_from_shipper = !empty($this->settings['ship_from_address_different_from_shipper']) ? $this->settings['ship_from_address_different_from_shipper'] : 'no';
		$this->ship_from_addressline	= !empty($this->settings['ship_from_addressline']) ? $this->settings['ship_from_addressline'] : null;
		$this->ship_from_addressline_2	= isset($this->settings['ship_from_addressline_2']) ? $this->settings['ship_from_addressline_2'] : null;
		$this->ship_from_city			= !empty($this->settings['ship_from_city']) ? $this->settings['ship_from_city'] : null;
		$this->ship_from_postcode 		= !empty($this->settings['ship_from_postcode']) ? $this->settings['ship_from_postcode'] : null;
		$this->ship_from_country_state	= !empty($this->settings['ship_from_country_state']) ? $this->settings['ship_from_country_state'] : null;

		if (empty($this->ship_from_country_state)) {
			$this->ship_from_country = $this->origin_country_state;		// By Default Origin Country
			$this->ship_from_state   = $this->origin_state;				// By Default Origin State
		} else {
			if (strstr($this->ship_from_country_state, ':')) :
				list($this->ship_from_country, $this->ship_from_state) = explode(':', $this->ship_from_country_state);
			else :
				$this->ship_from_country = $this->ship_from_country_state;
				$this->ship_from_state   = '';
			endif;
		}

		$this->ship_from_custom_state   = !empty($this->settings['ship_from_custom_state']) ? $this->settings['ship_from_custom_state'] : $this->ship_from_state;

		$this->freight_weekend_pickup 		= (isset($this->settings['freight_weekend_pickup']) && $this->settings['freight_weekend_pickup'] == 'yes') ? true : false;
		$this->freight_holiday_pickup 		= (isset($this->settings['freight_holiday_pickup']) && $this->settings['freight_holiday_pickup'] == 'yes') ? true : false;
		$this->freight_inside_pickup 		= (isset($this->settings['freight_inside_pickup']) && $this->settings['freight_inside_pickup'] == 'yes') ? true : false;
		$this->freight_residential_pickup 	= (isset($this->settings['freight_residential_pickup']) && $this->settings['freight_residential_pickup'] == 'yes') ? true : false;
		$this->freight_liftgate_pickup 		= (isset($this->settings['freight_liftgate_pickup']) && $this->settings['freight_liftgate_pickup'] == 'yes') ? true : false;
		$this->freight_limitedaccess_pickup = (isset($this->settings['freight_limitedaccess_pickup']) && $this->settings['freight_limitedaccess_pickup'] == 'yes') ? true : false;
		$this->freight_holiday_delivery 	= (isset($this->settings['freight_holiday_delivery']) && $this->settings['freight_holiday_delivery'] == 'yes') ? true : false;
		$this->freight_inside_delivery 		= (isset($this->settings['freight_inside_delivery']) && $this->settings['freight_inside_delivery'] == 'yes') ? true : false;
		$this->freight_weekend_delivery 	= (isset($this->settings['freight_weekend_delivery']) && $this->settings['freight_weekend_delivery'] == 'yes') ? true : false;
		$this->freight_liftgate_delivery 	= (isset($this->settings['freight_liftgate_delivery']) && $this->settings['freight_liftgate_delivery'] == 'yes') ? true : false;
		$this->freight_limitedaccess_delivery = (isset($this->settings['freight_limitedaccess_delivery']) && $this->settings['freight_limitedaccess_delivery'] == 'yes') ? true : false;
		$this->freight_call_before_delivery = (isset($this->settings['freight_call_before_delivery']) && $this->settings['freight_call_before_delivery'] == 'yes') ? true : false;

		$this->ship_from_address_for_freight	=  (isset($this->settings['ship_from_address_for_freight']) && $this->settings['ship_from_address_for_freight'] == 'yes') ? true : false;

		// Third Party Freight Billing
		$this->freight_payment_information 		= isset($this->settings['freight_payment']) && !empty($this->settings['freight_payment']) ? $this->settings['freight_payment'] : '10';
		$this->freight_thirdparty_contact_name	= isset($this->settings['freight_thirdparty_contact_name']) && !empty($this->settings['freight_thirdparty_contact_name']) ? $this->settings['freight_thirdparty_contact_name'] : ' ';
		$this->freight_thirdparty_addressline 	= isset($this->settings['freight_thirdparty_addressline']) && !empty($this->settings['freight_thirdparty_addressline']) ? $this->settings['freight_thirdparty_addressline'] : ' ';
		$this->freight_thirdparty_addressline_2 = isset($this->settings['freight_thirdparty_addressline_2']) && !empty($this->settings['freight_thirdparty_addressline_2']) ? $this->settings['freight_thirdparty_addressline_2'] : ' ';
		$this->freight_thirdparty_city 			= isset($this->settings['freight_thirdparty_city']) && !empty($this->settings['freight_thirdparty_city']) ? $this->settings['freight_thirdparty_city'] : ' ';
		$this->freight_thirdparty_postcode 		= isset($this->settings['freight_thirdparty_postcode']) && !empty($this->settings['freight_thirdparty_postcode']) ? $this->settings['freight_thirdparty_postcode'] : ' ';
		$this->freight_thirdparty_country_state	= isset($this->settings['freight_thirdparty_country_state']) && !empty($this->settings['freight_thirdparty_country_state']) ? $this->settings['freight_thirdparty_country_state'] : ' ';
		$this->ups_tradability 	= ((isset($this->settings['ups_tradability']) && !empty($this->settings['ups_tradability'])) && $this->settings['ups_tradability'] == 'yes') ? true : false;

		if (empty($this->freight_thirdparty_country_state)) {
			$this->freight_thirdparty_country 	= $this->origin_country_state;		// By Default Origin Country
			$this->freight_thirdparty_state 	= $this->origin_state;				// By Default Origin State
		} else {
			if (strstr($this->freight_thirdparty_country_state, ':')) :
				list($this->freight_thirdparty_country, $this->freight_thirdparty_state) = explode(':', $this->freight_thirdparty_country_state);
			else :
				$this->freight_thirdparty_country = $this->freight_thirdparty_country_state;
				$this->freight_thirdparty_state   = '';
			endif;
		}

		$this->freight_thirdparty_state 		= isset($this->settings['freight_thirdparty_custom_state']) && !empty($this->settings['freight_thirdparty_custom_state']) ? $this->settings['freight_thirdparty_custom_state'] : $this->freight_thirdparty_state;

		$this->skip_products 	= !empty($this->settings['skip_products']) ? $this->settings['skip_products'] : array();
		$this->min_weight_limit = !empty($this->settings['min_weight_limit']) ? (float) $this->settings['min_weight_limit'] : null;
		$this->max_weight_limit	= !empty($this->settings['max_weight_limit']) ? (float) $this->settings['max_weight_limit'] : null;
		$this->ph_delivery_confirmation = isset($this->settings['ph_delivery_confirmation']) && !empty($this->settings['ph_delivery_confirmation']) ? $this->settings['ph_delivery_confirmation'] : 0;
		$this->saturday_delivery 		= isset($this->settings['saturday_delivery']) && !empty($this->settings['saturday_delivery']) ? $this->settings['saturday_delivery'] : '';
		$this->import_control_settings 	= (isset($this->settings['import_control_settings']) && !empty($this->settings['import_control_settings'])) ? $this->settings['import_control_settings'] : '';

		if (!empty($this->conversion_rate)) {
			$this->rate_conversion		= $this->conversion_rate; // For Returned Rate Conversion to Default Currency 
			$this->conversion_rate		= apply_filters('ph_ups_currency_conversion_rate', $this->conversion_rate, $this->currency_type);   // Multicurrency
		}

		add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
		add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'clear_transients'));

		if (isset($_GET['ph_ups_reset_boxes'])) {
			add_action('admin_init', array($this, 'ph_ups_reset_boxes'));
		}
		
		$this->ph_restricted_article 	= ((isset($this->settings['ph_ups_restricted_article']) && $this->settings['ph_ups_restricted_article'] == 'yes') ? true : false);
		$this->ph_diog 	 				= ((isset($this->settings['ph_ups_diog']) &&  $this->settings['ph_ups_diog'] == 'yes') ? 'yes' : 'no');
		$this->ph_perishable 	 		= ((isset($this->settings['ph_ups_perishable']) &&  $this->settings['ph_ups_perishable'] == 'yes') ? 'yes' : 'no');
		$this->ph_alcoholic 	 		= ((isset($this->settings['ph_ups_alcoholic']) &&  $this->settings['ph_ups_alcoholic'] == 'yes') ? 'yes' : 'no');
		$this->ph_plantsindicator 		= ((isset($this->settings['ph_ups_plantsindicator']) &&  $this->settings['ph_ups_plantsindicator'] == 'yes') ? 'yes' : 'no');
		$this->ph_seedsindicator 	 	= ((isset($this->settings['ph_ups_seedsindicator']) &&  $this->settings['ph_ups_seedsindicator'] == 'yes') ? 'yes' : 'no');
		$this->ph_specialindicator 	 	= ((isset($this->settings['ph_ups_specialindicator']) &&  $this->settings['ph_ups_specialindicator'] == 'yes') ? 'yes' : 'no');
		$this->ph_tobaccoindicator 	 	= ((isset($this->settings['ph_ups_tobaccoindicator']) &&  $this->settings['ph_ups_tobaccoindicator'] == 'yes') ? 'yes' : 'no');
	}

	/**
	 * clear_transients function.
	 *
	 * @access public
	 * @return void
	 */
	public function clear_transients()
	{
		global $wpdb;

		$wpdb->query("DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_ups_quote_%') OR `option_name` LIKE ('_transient_timeout_ups_quote_%')");
	}

	/**
	 * Reset boxes
	 */
	public function ph_ups_reset_boxes()
	{

		$this->update_option('boxes', []);
		$this->update_option('ups_packaging', []);

		$boxesToSave = [];

		foreach ($this->packaging as $key => $box) {
			$boxesToSave[$key] = array(
				'boxes_name'		=> $box['name'],
				'outer_length'	=> $box['length'],
				'outer_width'	=> $box['width'],
				'outer_height'	=> $box['height'],
				'inner_length'	=> $box['length'],
				'inner_width'	=> $box['width'],
				'inner_height'	=> $box['height'],
				'box_weight'  	=> 0,
				'max_weight'  	=> $box['weight'],
				'box_enabled'	=> $box['box_enabled'],
			);
		}

		foreach ($this->simpleRateBoxes as $key => $box) {

			$boxesToSave[$key] = array(
				'boxes_name'	=> $box['name'],
				'outer_length'	=> $box['length'],
				'outer_width'	=> $box['width'],
				'outer_height'	=> $box['height'],
				'inner_length'	=> $box['length'],
				'inner_width'	=> $box['width'],
				'inner_height'	=> $box['height'],
				'box_weight'  	=> $box['box_weight'],
				'max_weight'  	=> $box['max_weight'],
				'box_enabled'	=> $box['box_enabled'],
			);
		}

		$this->update_option('boxes', $boxesToSave);

		wp_redirect(admin_url('admin.php?page=wc-settings&tab=shipping&section=wf_shipping_ups'));
	}

	/**
	 * environment_check function.
	 *
	 * @access public
	 * @return void
	 */
	private function environment_check()
	{
		global $woocommerce;

		$error_message = '';

		// WF: Print Label - Start
		// Check for UPS User Name
		if (!$this->ups_user_name && $this->enabled == 'yes') {
			$error_message .= '<p>' . __('UPS is enabled, but Your Name has not been set.', 'ups-woocommerce-shipping') . '</p>';
		}
		// WF: Print Label - End

		// Check for UPS User ID
		if (!$this->user_id && $this->enabled == 'yes') {
			$error_message .= '<p>' . __('UPS is enabled, but the UPS User ID has not been set.', 'ups-woocommerce-shipping') . '</p>';
		}

		// Check for UPS Password
		if (!$this->password && $this->enabled == 'yes') {
			$error_message .= '<p>' . __('UPS is enabled, but the UPS Password has not been set.', 'ups-woocommerce-shipping') . '</p>';
		}

		// Check for UPS Access Key
		if (!$this->access_key && $this->enabled == 'yes') {
			$error_message .= '<p>' . __('UPS is enabled, but the UPS Access Key has not been set.', 'ups-woocommerce-shipping') . '</p>';
		}

		// Check for UPS Shipper Number
		if (!$this->shipper_number && $this->enabled == 'yes') {
			$error_message .= '<p>' . __('UPS is enabled, but the UPS Shipper Number has not been set.', 'ups-woocommerce-shipping') . '</p>';
		}

		// Check for Origin Postcode
		if (!$this->origin_postcode && $this->enabled == 'yes') {
			$error_message .= '<p>' . __('UPS is enabled, but the origin postcode has not been set.', 'ups-woocommerce-shipping') . '</p>';
		}

		// Check for Origin country
		if (!$this->origin_country_state && $this->enabled == 'yes') {
			$error_message .= '<p>' . __('UPS is enabled, but the origin country/state has not been set.', 'ups-woocommerce-shipping') . '</p>';
		}

		// If user has selected to pack into boxes,
		// Check if at least one UPS packaging is chosen, or a custom box is defined
		if (($this->packing_method == 'box_packing') && ($this->enabled == 'yes')) {

			if (empty($this->ups_packaging)  && empty($this->boxes)) {
				$error_message .= '<p>' . __('UPS is enabled, and Parcel Packing Method is set to \'Pack into boxes\', but no UPS Packaging is selected and there are no custom boxes defined. Items will be packed individually.', 'ups-woocommerce-shipping') . '</p>';
			}
		}

		// Check for at least one service enabled
		$ctr = 0;
		if (isset($this->custom_services) && is_array($this->custom_services)) {
			foreach ($this->custom_services as $key => $values) {
				if ($values['enabled'] == 1)
					$ctr++;
			}
		}
		if (($ctr == 0) && $this->enabled == 'yes') {
			$error_message .= '<p>' . __('UPS is enabled, but there are no services enabled.', 'ups-woocommerce-shipping') . '</p>';
		}


		if (!$error_message == '') {
			echo '<div class="error">';
			echo $error_message;
			echo '</div>';
		}
	}

	/**
	 * admin_options function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_options() {
		
		echo '<h3>' . ( ! empty( $this->method_title ) ? $this->method_title : __( 'Settings', 'ups-woocommerce-shipping' ) ) . '</h3>';
		echo ( ! empty( $this->method_description ) ) ? wpautop( $this->method_description ) : '';
		
		// Check users environment supports this method
		$this->environment_check();

		// Show settings
		?>
		<table class="form-table">
			<?php $this->generate_settings_html(); ?>
		</table>
		<?php
	}

	/**
	 *
	 * Registration Banner
	 *
	 * @access public
	 * @return void
	 */
	function generate_ups_registration_banner_html()
	{

		ob_start();

		if (!Ph_UPS_Woo_Shipping_Common::phIsNewRegistration() && empty($this->access_key)) {
?>

			<div class="ph_registration_banner">

				<div id='ph_ups_close_banner'>[X]</div>

				<div class="ph_ups_box_1">

					<img src="<?php echo plugins_url('ups-woocommerce-shipping') . '/resources/images/PH_Logo.png'; ?>">

					<h4><?php _e("WooCommerce UPS Shipping Plugin with Print Label", "ups-woocommerce-shipping"); ?></h4>

				</div>

				<div class="ph_ups_box_2">

					<ul>
						<li style='color:red;'><strong><?php _e("Don't have UPS Access Keys? <a href='" . admin_url("/admin.php?page=ph_ups_registration") . "' target='_BLANK'>Click here to Register</a>", "ups-woocommerce-shipping"); ?></strong></li>
						<li><?php _e("PluginHive, a UPS Ready Business Solutions provider, ensures a seamless transition for all merchants by utilizing UPS Ready Partner Access to create UPS Access Keys while migrating to OAuth and RESTful APIs.", "ups-woocommerce-shipping"); ?></li>
						<li></li>

					</ul>

				</div>

			</div>
		<?php
		}

		return ob_get_clean();
	}

	/**
	 *
	 * generate_single_select_country_html function
	 *
	 * @access public
	 * @return void
	 */
	function generate_single_select_country_html()
	{
		global $woocommerce;

		$this->origin_country = PH_WC_UPS_Common_Utils::ph_get_origin_country_and_state( $this->settings, 'country' );
		$this->origin_state   = PH_WC_UPS_Common_Utils::ph_get_origin_country_and_state( $this->settings, 'state' );
		
		ob_start();
		?>
		<tr valign="top" class="ph_ups_general_tab">
			<th scope="row" class="titledesc">
				<label for="origin_country"><?php _e('Origin Country', 'ups-woocommerce-shipping'); ?></label>
			</th>
			<td class="forminp"><select name="woocommerce_ups_origin_country_state" id="woocommerce_ups_origin_country_state" style="width: 250px;" data-placeholder="<?php _e('Choose a country&hellip;', 'ups-woocommerce-shipping'); ?>" title="Country" class="chosen_select">
					<?php echo $woocommerce->countries->country_dropdown_options($this->origin_country, $this->origin_state ? $this->origin_state : '*'); ?>
				</select>
			</td>
		</tr>
	<?php
		return ob_get_clean();
	}

	/**
	 *
	 * generate_ship_from_country_state_html function
	 *
	 * @access public
	 * @return void
	 */
	function generate_ship_from_country_state_html()
	{
		global $woocommerce;

		ob_start();
	?>
		<tr valign="top" class="ph_ups_general_tab">
			<th scope="row" class="titledesc">
				<label for="woocommerce_wf_shipping_ups_ship_from_country_state"><?php _e('Ship From Country', 'ups-woocommerce-shipping'); ?></label>
			</th>
			<td class="forminp ph_ups_different_ship_from_address"><select name="woocommerce_wf_shipping_ups_ship_from_country_state" id="woocommerce_wf_shipping_ups_ship_from_country_state" style="width: 250px;" data-placeholder="<?php _e('Choose a country&hellip;', 'ups-woocommerce-shipping'); ?>" title="Country" class="chosen_select">
					<?php echo $woocommerce->countries->country_dropdown_options($this->ship_from_country, $this->ship_from_state ? $this->ship_from_state : '*'); ?>
				</select>
			</td>
		</tr>
	<?php
		return ob_get_clean();
	}

	/**
	 *
	 * generate_freight_thirdparty_country_state_html function
	 *
	 * @access public
	 * @return void
	 */
	function generate_freight_thirdparty_country_state_html()
	{
		global $woocommerce;

		ob_start();
	?>
		<tr valign="top" class="ph_ups_freight_tab">
			<th scope="row" class="titledesc">
				<label for="woocommerce_wf_shipping_ups_freight_thirdparty_country_state"><?php _e('Country', 'ups-woocommerce-shipping'); ?></label>
			</th>
			<td class="forminp ph_ups_freight_third_party_billing"><select name="woocommerce_wf_shipping_ups_freight_thirdparty_country_state" id="woocommerce_wf_shipping_ups_freight_thirdparty_country_state" style="width: 250px;" data-placeholder="<?php _e('Choose a country&hellip;', 'ups-woocommerce-shipping'); ?>" title="Country" class="chosen_select">
					<?php echo $woocommerce->countries->country_dropdown_options($this->freight_thirdparty_country, $this->freight_thirdparty_state ? $this->freight_thirdparty_state : '*'); ?>
				</select>
			</td>
		</tr>
	<?php
		return ob_get_clean();
	}

	/**
	 * generate_services_html function.
	 *
	 * @access public
	 * @return void
	 */
	function generate_services_html()
	{
		ob_start();
			include 'admin-views/ph-ups-service-list-html.php';
		return ob_get_clean();
	}

	/**
	 * generate_box_packing_html function.
	 *
	 * @access public
	 * @return void
	 */
	public function generate_box_packing_html()
	{	
		$default_boxes = [];
		if ( !isset($this->settings['boxes'] ) || !is_array($this->settings['boxes'] ) ) {

			foreach( $this->packaging as $key => $box ) {

				$default_boxes[$key] = array(
					'boxes_name'	=> $box['name'],
					'outer_length'	=> $box['length'],
					'outer_width'	=> $box['width'],
					'outer_height'	=> $box['height'],
					'inner_length'	=> $box['length'],
					'inner_width'	=> $box['width'],
					'inner_height'	=> $box['height'],
					'box_weight'  	=> 0,
					'max_weight'  	=> $box['weight'],
					'box_enabled'	=> true,
				);
			}

			foreach ($this->simpleRateBoxes as $key => $box) {

				$default_boxes[$key] = array(
					'boxes_name'	=> $box['name'],
					'outer_length'	=> $box['length'],
					'outer_width'	=> $box['width'],
					'outer_height'	=> $box['height'],
					'inner_length'	=> $box['length'],
					'inner_width'	=> $box['width'],
					'inner_height'	=> $box['height'],
					'box_weight'  	=> $box['box_weight'],
					'max_weight'  	=> $box['max_weight'],
					'box_enabled'	=> $box['box_enabled'],
				);
			}
		}

		ob_start();
			include 'admin-views/ph-ups-box-packing-html.php';
		return ob_get_clean();
	}

	/**
	 * validate_single_select_country_field function.
	 *
	 * @access public
	 * @param mixed $key
	 * @return void
	 */
	public function validate_single_select_country_field($key)
	{
		if (isset($_POST['woocommerce_ups_origin_country_state']))
			return $_POST['woocommerce_ups_origin_country_state'];
		return '';
	}

	/**
	 * validate_box_packing_field function.
	 *
	 * @access public
	 * @param mixed $key
	 * @return void
	 */
	public function validate_box_packing_field($key)
	{
		$boxes = array();

		if (isset($_POST['boxes_outer_length'])) {

			$boxes_name			= $_POST['boxes_name'];
			$boxes_outer_length = $_POST['boxes_outer_length'];
			$boxes_outer_width  = $_POST['boxes_outer_width'];
			$boxes_outer_height = $_POST['boxes_outer_height'];
			$boxes_inner_length = $_POST['boxes_inner_length'];
			$boxes_inner_width  = $_POST['boxes_inner_width'];
			$boxes_inner_height = $_POST['boxes_inner_height'];
			$boxes_box_weight   = $_POST['boxes_box_weight'];
			$boxes_max_weight   = $_POST['boxes_max_weight'];
			$boxes_enabled		= isset($_POST['boxes_enabled']) ? $_POST['boxes_enabled'] : [];
			$selectedDimUnit	= isset($_POST['selected_dim_unit']) && !empty($_POST['selected_dim_unit']) ? $_POST['selected_dim_unit'] : $this->units;

			// Update the value as true if simple rate is enabled, to show the simple rate boxes.
			$this->upsSimpleRate = isset($_POST['woocommerce_wf_shipping_ups_ups_simple_rate']) ? true : false;

			if (!empty($boxes_outer_length) && sizeof($boxes_outer_length)) {

				foreach ($boxes_outer_length as $key => $value) {

					if ($boxes_outer_length[$key] && $boxes_outer_width[$key] && $boxes_outer_height[$key] && $boxes_inner_length[$key] && $boxes_inner_width[$key] && $boxes_inner_height[$key]) {

						$boxes[$key] = array(
							'boxes_name'		=> $boxes_name[$key],
							'outer_length'	=> floatval($boxes_outer_length[$key]),
							'outer_width'	=> floatval($boxes_outer_width[$key]),
							'outer_height'	=> floatval($boxes_outer_height[$key]),
							'inner_length'	=> floatval($boxes_inner_length[$key]),
							'inner_width'	=> floatval($boxes_inner_width[$key]),
							'inner_height'	=> floatval($boxes_inner_height[$key]),
							'box_weight'  	=> floatval($boxes_box_weight[$key]),
							'max_weight'  	=> floatval($boxes_max_weight[$key]),
							'box_enabled'	=> array_key_exists($key, $boxes_enabled) ? true : false,
						);
					}
				}
			}

			// Convert the box weight and dimensions if unit is changed under plugin settings while saving
			if ($selectedDimUnit != $this->savedMetrics) {

				$boxes = PH_WC_UPS_Common_Utils::ph_convert_box_measurement_units($boxes, $this->units);
			}
		}


		// Delete previous settings for UPS Packaging once updated
		if (isset($this->ups_packaging) && !empty($this->ups_packaging)) {

			delete_option('ups_packaging');
		}

		// Sort boxes based on key before saving, to maintain the order while displaying in settings.
		ksort($boxes);
		return $boxes;
	}

	/**
	 * validate_services_field function.
	 *
	 * @access public
	 * @param mixed $key
	 * @return void
	 */
	public function validate_services_field($key)
	{
		$services		 = array();
		$posted_services  = $_POST['ups_service'];

		foreach ($posted_services as $code => $settings) {

			$services[$code] = array(
				'name'			   	 => wc_clean($settings['name']),
				'order'			  	 => wc_clean($settings['order']),
				'enabled'			 => isset($settings['enabled']) ? true : false,
				'adjustment'		 => wc_clean($settings['adjustment']),
				'adjustment_percent' => str_replace('%', '', wc_clean($settings['adjustment_percent']))
			);
		}

		return $services;
	}

	public function generate_ph_ups_settings_tabs_html()
	{
		ob_start();
			include ( 'admin-views/ph-ups-settings-tabs-html.php');
		return ob_get_clean();
	}

	public function generate_help_support_section_html()
	{
		ob_start();
			include ('admin-views/ph-ups-help-and-support-html.php');
		return ob_get_clean();
	}

	/**
	 * Including Freight Banner.
	 */
	public function generate_ph_ups_freight_banner_html()
	{
		ob_start();			
			include_once('admin-views/ph-ups-freight-banner.php');	
		return ob_get_clean();
	}

	/**
	 * init_form_fields function.
	 *
	 * @access public
	 * @return void
	 */
	public function init_form_fields()
	{
		$this->services = PH_WC_UPS_Common_Utils::get_services_based_on_origin( $this->origin_country );

		$this->form_fields  = include( 'settings/data-ph-ups-settings.php' );
	}

	/**
	 * See if method is available based on the package and cart.
	 *
	 * @param array $package Shipping package.
	 * @return bool
	 */

	public function is_available($package)
	{
		if ("no" === $this->enabled) {

			Ph_UPS_Woo_Shipping_Common::phAddDebugLog('UPS : Realtime Rates is not enabled', $this->debug);
			return false;
		}

		if ('specific' === $this->availability) {
			if (is_array($this->countries) && !in_array($package['destination']['country'], $this->countries)) {

				Ph_UPS_Woo_Shipping_Common::phAddDebugLog('UPS : Method Availability for Specific Countries - ' . print_r($this->countries, 1), $this->debug);
				Ph_UPS_Woo_Shipping_Common::phAddDebugLog('UPS : Checking for Destination - ' . $package['destination']['country'] . ' Rate Calculation Aborted.', $this->debug);
				return false;
			}
		} elseif ('excluding' === $this->availability) {
			if (is_array($this->countries) && (in_array($package['destination']['country'], $this->countries) || !$package['destination']['country'])) {
				return false;
			}
		}

		$has_met_min_amount = false;

		if (!method_exists(WC()->cart, 'get_displayed_subtotal')) { // WC version below 2.6
			$total = WC()->cart->subtotal;
		} else {
			$total = WC()->cart->get_displayed_subtotal();

			if (version_compare(WC()->version, '4.4', '<')) {
				$tax_display 	= WC()->cart->tax_display_cart;
			} else {
				$tax_display 	= WC()->cart->get_tax_price_display_mode();
			}

			if ('incl' === $tax_display) {
				$total = $total - (WC()->cart->get_cart_discount_total() + WC()->cart->get_cart_discount_tax_total());
			} else {
				$total = $total - WC()->cart->get_cart_discount_total();
			}
		}
		if ($total < 0) {
			$total = 0;
		}
		if ($total >= $this->min_amount) {
			$has_met_min_amount = true;
		}
		$is_available	=	$has_met_min_amount;
		return apply_filters('woocommerce_shipping_' . $this->id . '_is_available', $is_available, $package);
	}

	/**
	 * calculate_shipping function.
	 *
	 * @access public
	 * @param mixed $package
	 * @return void
	 */
	public function calculate_shipping($package = array())
	{
		global $woocommerce;

		if ( Ph_UPS_Woo_Shipping_Common::ph_is_oauth_registered_customer() ) {

			$all_rates 		 = array();
			$settings_helper = new PH_WC_UPS_Settings_Helper();
			$this->settings  = $settings_helper->settings;

			if (!class_exists('PH_WC_UPS_Shipping_Controller')) {

				require_once PH_WC_UPS_PLUGIN_DIR_PATH . 'includes/utils/class-ph-ups-shipping-controller.php';
			}

			$shipping_controller = new PH_WC_UPS_Shipping_Controller( $this->settings );

			// Function to get rates for both zones and rest.
			$rates = $shipping_controller->ph_get_shipping_rates( $package, 'UPS' );

			// Incase of no active license or no api details or any other issues, the return value might be empty.
			if( empty($rates)) return;

			$this->current_package_items_and_quantity = $rates['current_package_items_and_quantity'];
			$this->vendorId = $rates['vendor_id'];
			
			if( !empty( $rates )) {
				$all_rates = $rates['all_rates'];
			}

		} else {

			// XML rates calculation.

			// Address Validation applicable for US and PR
			if ($this->address_validation && in_array($package['destination']['country'], array('US', 'PR')) && !is_admin() && !$this->residential) {

				require_once 'class-ph-ups-address-validation.php';

				$Ph_Ups_Address_Validation 	= new Ph_Ups_Address_Validation($package['destination'], $this->settings);
				$residential_code			= $Ph_Ups_Address_Validation->residential_check;

				// To get Address Validation Result Outside
				$residential_code 	= apply_filters('ph_ups_address_validation_result', $residential_code, $package['destination'], $this->settings);

				if ($residential_code == 2) {
					$this->residential = true;
				}
			}

			$this->ph_ups_selected_access_point_details = !empty($package['ph_ups_selected_access_point_details']) ? $package['ph_ups_selected_access_point_details'] : null;
			libxml_use_internal_errors(true);

			// Only return rates if the package has a destination including country, postcode
			if ('' == $package['destination']['country']) {
				Ph_UPS_Woo_Shipping_Common::debug(__('UPS: Country not yet supplied. Rates not requested.', 'ups-woocommerce-shipping'), $this->debug, $this->silent_debug);
				return;
			}

			if (in_array($package['destination']['country'], PH_WC_UPS_Constants::NO_POSTCODE_COUNTRY_ARRAY)) {
				if (empty($package['destination']['city'])) {
					Ph_UPS_Woo_Shipping_Common::debug(__('UPS: City not yet supplied. Rates not requested.', 'ups-woocommerce-shipping'), $this->debug, $this->silent_debug);
					return;
				}
			} else if ('' == $package['destination']['postcode']) {
				Ph_UPS_Woo_Shipping_Common::debug(__('UPS: Zip not yet supplied. Rates not requested.', 'ups-woocommerce-shipping'), $this->debug, $this->silent_debug);
				return;
			}
			// Turn off Insurance value if Cart subtotal is less than the specified amount in plugin settings
			if (isset($package['cart_subtotal']) && $package['cart_subtotal'] <= $this->min_order_amount_for_insurance) {
				$this->insuredvalue = false;
			}

			// Skip Products
			if (!empty($this->skip_products)) {
				$package = PH_WC_UPS_Common_Utils::skip_products( $package, $this->skip_products, '', $this->debug, $this->silent_debug );
				if (empty($package['contents'])) {
					return;
				}
			}

			if (!empty($this->min_weight_limit) || !empty($this->max_weight_limit)) {
				$need_shipping = PH_WC_UPS_Common_Utils::check_min_weight_and_max_weight($package, $this->min_weight_limit, $this->max_weight_limit, $this->debug, $this->silent_debug);
				if (!$need_shipping)	return;
			}

			// To Support Multi Vendor plugin
			$packages = apply_filters('wf_filter_package_address', array($package), $this->ship_from_address);

			//Woocommerce packages after dividing the products based on vendor, if vendor plugin exist
			$wc_total_packages_count 	= count($packages);
			$package_rates 				= array();
			$allPackageRateCount 		= array();

			// $packageKey is to differentiate the multiple Cart Packages
			// Usecase: Multi Vendor with Split and Sum Method

			foreach ($packages as $packageKey => $package) {

				// Check Hazardous Materials in Package
				$is_hazardous_materials = false;

				if (isset($package['contents']) && !empty($package['contents'])) {

					foreach ($package['contents'] as $key => $items) {

						if (isset($items['variation_id']) && !empty($items['variation_id']) && get_post_meta($items['variation_id'], '_ph_ups_hazardous_materials', 1) == 'yes') {

							$is_hazardous_materials = true;
							break;
						} elseif (get_post_meta($items['product_id'], '_ph_ups_hazardous_materials', 1) == 'yes') {

							$is_hazardous_materials = true;
							break;
						}
					}
				}


				// Reset Internal Rates Array after each Vendor Package Rate Calculation

				$rates = array();

				if (($this->origin_country != $package['destination']['country']) && $this->ups_tradability) {

					$calculate_lc_query_request = $this->calculate_lc_query_request($package);
					$lcr_response				= $this->get_lc_result($calculate_lc_query_request, 'Landed Cost Query');

					if (!empty($lcr_response) && isset($lcr_response->QueryResponse) && !empty($lcr_response->QueryResponse)) {

						$transaction_digest 	= isset($lcr_response->QueryResponse->TransactionDigest) && !empty($lcr_response->QueryResponse->TransactionDigest) ? $lcr_response->QueryResponse->TransactionDigest : '';

						if (isset($transaction_digest) && !empty($transaction_digest)) {

							$calculate_lc_estimate_request	= $this->calculate_lc_estimate_request($transaction_digest);

							$lcr_response = $this->get_lc_result($calculate_lc_estimate_request, 'Landed Cost Estimate');
						}
					}

					if (!empty($lcr_response) && isset($lcr_response->EstimateResponse) && !empty($lcr_response->EstimateResponse)) {

						$total_landed_cost  = $lcr_response->EstimateResponse->ShipmentEstimate->TotalLandedCost;

						if (WC() != null && WC()->session != null) {

							WC()->session->set('ph_ups_total_landed_cost', $total_landed_cost);
						}
					}
				} else {

					if (WC() != null && WC()->session != null) {

						WC()->session->set('ph_ups_total_landed_cost', '');
					}
				}

				$package	= apply_filters('wf_customize_package_on_cart_and_checkout', $package);	// Customize the packages if cart contains bundled products
				// To pass the product info with rates meta data
				foreach ($package['contents'] as $product) {
					$product_id = !empty($product['variation_id']) ? $product['variation_id'] : $product['product_id'];
					$this->current_package_items_and_quantity[$product_id] = $product['quantity'];
				}

				$this->vendorId = !empty($package['vendorID']) ? $package['vendorID'] : null;

				$package_params	=	array();
				//US to US and PR, CA to CA , PR to US or PR are domestic remaining all pairs are international
				if ((($this->origin_country == $package['destination']['country']) && in_array($this->origin_country, $this->dc_domestic_countries)) || (($this->origin_country == 'US' || $this->origin_country == 'PR') && ($package['destination']['country'] == 'US' || $package['destination']['country'] == 'PR'))) {
					$package_params['delivery_confirmation_applicable']	=	true;
				} else {
					$this->international_delivery_confirmation_applicable = true;
				}
				
				$package_requests		= $this->get_package_requests($package, $package_params);

				$indexKey 				= 0;
				$maxIndex 				= 50;
				$packageCount 			= 0;
				$new_package_requests	= array();

				foreach ($package_requests as $key => $value) {

					$packageCount++;

					if ($packageCount <= $maxIndex) {

						$new_package_requests[$indexKey][] = $value;
					} else {

						$packageCount = 1;
						$indexKey++;
						$new_package_requests[$indexKey][] = $value;
					}
				}

				$internal_package_count = !empty($new_package_requests) && is_array($new_package_requests) ? count($new_package_requests) : 0;
				$single_package 		= true;

				if (!empty($new_package_requests)) {

					foreach ($new_package_requests as $key => $newPackageRequest) {

						// To get rate for services like ups ground, 3 day select etc.
						$rate_requests 	= $this->get_rate_requests($newPackageRequest, $package);
						$rate_response 	= $this->process_result($this->get_result($rate_requests, '', $key), '', $rate_requests);

						if (!empty($rate_response)) {
							$rates[$key]['general'][] =  $rate_response;
						}
						// End of get rates for services like ups ground, 3 day select etc.

						//For Worldwide Express Freight Service
						if (isset($this->custom_services[96]['enabled']) && $this->custom_services[96]['enabled']) {

							$rate_requests 		= $this->get_rate_requests($newPackageRequest, $package, 'Pallet', 96);
							$rates[$key][96][] 	= $this->process_result($this->get_result($rate_requests, 'WWE Freight', $key), '', $rate_requests);
						}

						// GFP request
						if (isset($this->settings['services']['US48']['enabled']) && $this->settings['services']['US48']['enabled']) {

							if (!$is_hazardous_materials) {

								if ($this->soap_available) {

									$rate_requests	= $this->get_rate_requests_gfp($newPackageRequest, $package);
									$rates[$key]['US48'][]	= $this->process_result_gfp($this->get_result_gfp($rate_requests, 'UPS GFP', $key));
								} else {

									Ph_UPS_Woo_Shipping_Common::debug("UPS Ground with Freight Rate Request Failed. SoapClient is not enabled for your website. Ground with Freight Service requires SoapClient to be enabled to fetch rates. Contact your Hosting Provider to enable SoapClient and try again.", $this->debug, $this->silent_debug);

									Ph_UPS_Woo_Shipping_Common::phAddDebugLog("UPS Ground with Freight Rate Request Failed. SoapClient is not enabled for your website. Ground with Freight Service requires SoapClient to be enabled to fetch rates. Contact your Hosting Provider to enable SoapClient and try again.", $this->debug);
								}
							} else {

								Ph_UPS_Woo_Shipping_Common::debug("HazMat Product can not be shipped using UPS Ground with Freight Pricing.", $this->debug, $this->silent_debug);

								Ph_UPS_Woo_Shipping_Common::phAddDebugLog("HazMat Product can not be shipped using UPS Ground with Freight Pricing.", $this->debug);
							}
						}

						// For Freight services 308, 309, 334, 349
						if ($this->enable_freight) {

							$freight_ups = new wf_freight_ups($this);

							foreach ($this->freight_services as $service_code => $value) {

								if (!empty($this->settings['services'][$service_code]['enabled'])) {

									Ph_UPS_Woo_Shipping_Common::debug("UPS FREIGHT SERVICE START: $service_code", $this->debug, $this->silent_debug);

									$freight_rate 			= array();
									$cost 					= 0;
									$freight_rate_requests 	= $freight_ups->get_rate_request($package, $service_code, $newPackageRequest);

									// Freight rate request
									// foreach( $package_requests as $package_key => $package_request) {
									// Freight rate response for individual packages request
									$freight_rate_response = $this->process_result($this->get_result($freight_rate_requests, 'freight', $key), 'json');

									if (!empty($freight_rate_response[WF_UPS_ID . ":$service_code"]['cost'])) {

										// Cost of freight packages till now processed for individual freight service
										$cost += $freight_rate_response[WF_UPS_ID . ":$service_code"]['cost'];
										$freight_rate_response[WF_UPS_ID . ":$service_code"]['cost'] = $cost;
										$freight_rate = $freight_rate_response;
									} else {
										// If no response comes for any packages then we won't show the response for that Freight service
										$freight_rate = array();
										Ph_UPS_Woo_Shipping_Common::debug("UPS FREIGHT SERVICE RESPONSE FAILED FOR SOME PACKAGES : $service_code", $this->debug, $this->silent_debug);
										break;
									}
									// }

									Ph_UPS_Woo_Shipping_Common::debug("UPS FREIGHT SERVICE END : $service_code", $this->debug, $this->silent_debug);

									// If rate comes for freight sevices then merge it in rates array
									if (!empty($freight_rate)) {
										$rates[$key][$service_code][] = $freight_rate;
									}
								}
							}
						}
						// End code for Freight services 308, 309, 334, 349

						$surepostPackageCount	= !empty($newPackageRequest) && is_array($newPackageRequest) ? count($newPackageRequest) : 1;
						$originCountryState		= isset($this->settings['origin_country_state']) ? $this->settings['origin_country_state'] : '';
						$originCountryState		= current(explode(':', $originCountryState));
						$originCountry 			= isset($package['origin']['country']) ? $package['origin']['country'] : $originCountryState;

						// UPS Simple Rate
						$currentPackage		= current($newPackageRequest);
						$packageWeight 		= $currentPackage['Package']['PackageWeight']['Weight'];
						$isSimpleRateBox	= (isset($currentPackage['Package']['BoxCode']) && array_key_exists($currentPackage['Package']['BoxCode'], $this->simpleRateBoxes)) ? true : false;

						if ($surepostPackageCount == 1 && $single_package && ($originCountry == 'US')) {

							// UPS Simplerate
							if ($this->upsSimpleRate && $isSimpleRateBox && $packageWeight <= 50) {

								$rate_requests	= $this->get_rate_requests($newPackageRequest, $package, 'simple_rate');
								$rates[$key]['simple'][] 	= $this->process_result($this->get_result($rate_requests, 'simple rate', $key), '', $rate_requests);

								foreach ($rates[$key]['simple'][0] as $rates_key => $value) {

									if (isset($rates[$key]['general'][0][$rates_key])) {
										unset($rates[$key]['general'][0][$rates_key]);
									}
								}
							} else {

								if ($this->upsSimpleRate) {

									Ph_UPS_Woo_Shipping_Common::debug("UPS Simple Rate Request Aborted.<br/>UPS Simple Rate is a single package service. Please make sure:<br/><ul><li>Simple Rate Box(es) are enabled in the plugin Packaging settings</li><li>Total order weight should not exceed 50 lbs or 22.6 kg (as supported by UPS)</li></ul>", $this->debug, $this->silent_debug);

									Ph_UPS_Woo_Shipping_Common::phAddDebugLog("UPS Simple Rate Request Aborted. UPS Simple Rate is a single package service. Please make sure:Simple Rate Box(es) are enabled in the plugin Packaging settings, Total order weight should not exceed 50 lbs or 22.6 kg (as supported by UPS)", $this->debug);
								}
							}


							// Surepost, 1 is Commercial Address
							$surepost_check = 0;

							if (!class_exists('Ph_Ups_Address_Validation')) {
								require_once 'class-ph-ups-address-validation.php';
							}

							if (isset($Ph_Ups_Address_Validation) && ($Ph_Ups_Address_Validation instanceof Ph_Ups_Address_Validation)) {

								$surepost_check				= $Ph_Ups_Address_Validation->residential_check;
							} elseif (class_exists('Ph_Ups_Address_Validation') && in_array($package['destination']['country'], array('US', 'PR'))) {

								// Will check the address is Residential or not, SurePost only for residential
								$Ph_Ups_Address_Validation 	= new Ph_Ups_Address_Validation($package['destination'], $this->settings);
								$surepost_check				= $Ph_Ups_Address_Validation->residential_check;
							}

							$surepost_check 	= apply_filters('ph_ups_update_surepost_address_validation_result', $surepost_check, $package['destination'], $package);

							if ($surepost_check != 1) {

								foreach ($this->ups_surepost_services as $service_code) {

									if (empty($this->custom_services[$service_code]['enabled']) || ($this->custom_services[$service_code]['enabled'] != 1)) {
										//It will be not set for European origin address
										continue;
									}

									$rate_requests			= $this->get_rate_requests($newPackageRequest, $package, 'surepost', $service_code);
									$rate_response			= $this->process_result($this->get_result($rate_requests, 'surepost', $key), '', $rate_requests);

									if (!empty($rate_response)) {

										$rates[$key][$service_code][]	= $rate_response;
									}
								}
							} else {
								Ph_UPS_Woo_Shipping_Common::debug("UPS SurePost Rate Request Aborted. Entered Address is Commercial.", $this->debug, $this->silent_debug);

								Ph_UPS_Woo_Shipping_Common::phAddDebugLog("UPS SurePost Rate Request Aborted. Entered Address is Commercial.", $this->debug);
							}
						} else {

							$single_package = false;

							Ph_UPS_Woo_Shipping_Common::debug("UPS SurePost/Simple Rate Request Aborted. Only single-piece shipments is allowed. Request contains $surepostPackageCount Packages.", $this->debug, $this->silent_debug);

							Ph_UPS_Woo_Shipping_Common::phAddDebugLog("UPS SurePost/Simple Rate Request Aborted. Only single-piece shipments is allowed. Request contains $surepostPackageCount Packages.", $this->debug);
						}

						// Saturday Delivery Request
						if (isset($this->saturday_delivery) && $this->saturday_delivery == 'yes') {

							$rate_requests 		= $this->get_rate_requests($newPackageRequest, $package, 'saturday');
							$rates[$key]['saturday'][] 	= $this->process_result($this->get_result($rate_requests, 'Saturday Delivery', $key), '', $rate_requests);

							// Similar Services are Unsetting
							foreach ($rates[$key]['saturday'][0] as $rates_key => $value) {

								if (isset($rates[$key]['general'][0][$rates_key])) {

									unset($rates[$key]['general'][0][$rates_key]);
								}
							}
						}
					}
				}

				// Handle Rates for Internal Packages, Add Rates from all the Packages and build Final Rate Array
				// If any of the Internal Package missing any Shipping Rate, unset the Shipping Rate from the Final Rate Array
				// Usecase: More than 50 Packages generated (Packing Algorithm) from Cart Packages - Pack Item Indivisually with 120 Quantity of product.

				if (!empty($rates)) {

					foreach ($rates as $key => $value) {

						// $rate_type will be general, freight ( 308, 309, 334, 349 ), US48, 96, SurePost

						foreach ($value as $rate_type => $all_packages_rates) {

							// Build Final Rate Array for each Cart Packages

							if (!isset($package_rates[$rate_type])) {

								$package_rates[$rate_type] 				= array();

								// Add $packageKey in Final Rate Array to check rates are returned for all Cart Packages

								$package_rates[$rate_type][$packageKey] = array();
							}

							// Build Internal Package Rate Count for all the Services

							if (!isset($allPackageRateCount[$rate_type])) {

								$allPackageRateCount[$rate_type] 				= array();

								// Add $packageKey in Internal Package Rate Count Array 
								// To check rates are returned for all the Internal Packages within a Cart Package

								$allPackageRateCount[$rate_type][$packageKey] 	= array();
							}

							$calculatedRates = current($all_packages_rates);

							if (is_array($calculatedRates) || is_object($calculatedRates)) {

								foreach ($calculatedRates as $ups_sevice => $package_rate) {

									// Keep the Count of each UPS Shipping Service returned for all the Internal Packages

									if (!isset($allPackageRateCount[$rate_type][$packageKey][$ups_sevice])) {

										$allPackageRateCount[$rate_type][$packageKey][$ups_sevice] = 1;
									}

									// If: Push the Shipping Rate array for the initial Internal Package to the Final Rate Array
									// Else: Add the Shipping Rate Cost to the Final Rate Array for each additional Internal Package
									// Increacse the Internal Package Rate Count as well

									if (!isset($package_rates[$rate_type][$packageKey][$ups_sevice])) {

										$package_rates[$rate_type][$packageKey][$ups_sevice] = array();
										$package_rates[$rate_type][$packageKey][$ups_sevice] = $package_rate;
									} else {

										$package_rates[$rate_type][$packageKey][$ups_sevice]['cost'] = (float) $package_rates[$rate_type][$packageKey][$ups_sevice]['cost'] + (float) $package_rate['cost'];
										$allPackageRateCount[$rate_type][$packageKey][$ups_sevice]++;
									}
								}
							}
						}
					}

					// If all the Internal Package Rates were not returned then Unset that Shipping Rate
					// This unsetting is only for Internal Package Rates of respectve Cart Packages

					if (!empty($allPackageRateCount)) {

						foreach ($allPackageRateCount as $rateType => $rateCount) {

							if ( isset($rateCount[$packageKey]) && is_array($rateCount[$packageKey]) ) {

								foreach ($rateCount[$packageKey] as $rateId => $count) {

									if (isset($package_rates[$rateType]) && isset($package_rates[$rateType][$packageKey]) && isset($package_rates[$rateType][$packageKey][$rateId]) && $internal_package_count != $count) {

										$serviceName 	= $package_rates[$rateType][$packageKey][$rateId]['label'];

										Ph_UPS_Woo_Shipping_Common::phAddDebugLog("$serviceName is removed from Shipping Rates. Total $internal_package_count Package Set(s) were requested for Rates. Rates returned only for $count Package Set(s). One Package Set contains maximum of 50 Packages.", $this->debug);

										unset($package_rates[$rateType][$packageKey][$rateId]);
									}
								}
							}
						}
					}
				}
			}

			$rates 		= $package_rates;
			$all_rates 	= array();

			// Handle Rates for Multi Cart Packages, Check all cart packages returned rates.
			// Filter Common Shipping Methods and conbine the Shipping Cost and Display
			// Usecase: Multi Vendor with Split and Sum Method

			if (!empty($rates)) {

				foreach ($rates as $rate_type => $all_packages_rates) {

					// For every woocommerce package there must be response, so number of woocommerce package and UPS response must be equal

					if (count($rates[$rate_type]) == $wc_total_packages_count) {

						// UPS services keys in rate response

						$ups_found_services_keys = array_keys(current($all_packages_rates));

						foreach ($ups_found_services_keys as $ups_sevice) {

							$count = 0;

							foreach ($all_packages_rates as $package_rates) {

								if (!empty($package_rates[$ups_sevice])) {

									if (empty($all_rates[$ups_sevice])) {

										$all_rates[$ups_sevice] = $package_rates[$ups_sevice];
									} else {

										$all_rates[$ups_sevice]['cost'] = (float) $all_rates[$ups_sevice]['cost'] + (float) $package_rates[$ups_sevice]['cost'];
									}

									$count++;
								}
							}

							// If number of package requests not equal to number of response for any particular service

							if ($count != $wc_total_packages_count) {
								unset($all_rates[$ups_sevice]);
							}
						}
					}
				}
			}
		}

		$this->xa_add_rates($all_rates);
	}
	// End of Calculate Shipping function
	
	/**
	 * xa_add_rates function.
	 *
	 * Adds UPS shipping rates to the WooCommerce shipping method.
	 *
	 * @param array $rates The array of UPS shipping rates.
	 * @return void
	 */
	function xa_add_rates($rates)
	{
		if (!empty($rates)) {

			if ('all' == $this->offer_rates) {

				uasort($rates, array('Ph_UPS_Woo_Shipping_Common', 'sort_rates'));
				foreach ($rates as $key => $rate) {

					$this->add_rate($rate);
				}
			} else {

				$cheapest_rate = '';

				foreach ($rates as $key => $rate) {
					if (!$cheapest_rate || ($cheapest_rate['cost'] > $rate['cost'] && !empty($rate['cost'])))
						$cheapest_rate = $rate;
				}
				// If cheapest only without actual service name i.e Service name has to be override with method title
				if (!empty($this->cheapest_rate_title)) {
					$cheapest_rate['label'] = $this->cheapest_rate_title;
				}
				$this->add_rate($cheapest_rate);
			}
			// Fallback
		} elseif ($this->fallback) {
			$this->add_rate(array(
				'id' 	=> $this->id . '_fallback',
				'label' => $this->title,
				'cost' 	=> $this->fallback,
				'sort'  => 0,
				'meta_data'	=> array(
					'_xa_ups_method'	=>	array(
						'id'			=>	$this->id . '_fallback',	// Rate id will be in format WF_UPS_ID:service_id ex for ground wf_shipping_ups:03
						'method_title'	=>	$this->title,
						'items'			=>	isset($this->current_package_items_and_quantity) ? $this->current_package_items_and_quantity : array(),
					),
					'VendorId'			=> !empty($this->vendorId) ? $this->vendorId : null,
				)
			));
			Ph_UPS_Woo_Shipping_Common::debug(__('UPS: Using Fallback setting.', 'ups-woocommerce-shipping'), $this->debug, $this->silent_debug);
		}
	}

	public function calculate_lc_query_request($package)
	{

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
				$unit_price  		= !empty($product['data']->get_price()) ? $product['data']->get_price() : $this->fixedProductPrice;
				$unit_weight 		= round(wc_get_weight((!empty($product['data']->get_weight()) ? $product['data']->get_weight() : 0), $this->weight_unit, 4));
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
							'UnitCode' => $this->uom
						)
					)
				);
			}
		}
		$request['QueryRequest'] = $queryrequest;
		return $request;
	}

	public function calculate_lc_estimate_request($transaction_digest)
	{

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

	//Landed Cost Result
	public function get_lc_result($request, $request_type)
	{
		// $ups_response 		= null;
		$ups_response 		= [];
		$exceptionMessage   = '';
		$ups_settings 		= get_option('woocommerce_' . WF_UPS_ID . '_settings', null);

		$ups_settings['boxes']	= null;

		update_option('woocommerce_' . WF_UPS_ID . '_settings', $ups_settings);
		$api_mode	  		= isset($ups_settings['api_mode']) ? $ups_settings['api_mode'] : 'Test';
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
		
		if (!Ph_UPS_Woo_Shipping_Common::ph_is_oauth_registered_customer()) {

			$client->__setSoapHeaders($header);
		}

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

			$xml_response = $client->__getLastResponse();

			Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------ UPS ' . strtoupper($request_type) . ' REQUEST ------------------------', $this->debug);
			Ph_UPS_Woo_Shipping_Common::phAddDebugLog(htmlspecialchars($client->__getLastRequest()), $this->debug);
			Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------ UPS ' . strtoupper($request_type) . ' RESPONSE ------------------------', $this->debug);
			Ph_UPS_Woo_Shipping_Common::phAddDebugLog( !empty( $xml_response ) ? htmlspecialchars( $xml_response ) : null, $this->debug);

			if (!empty($exceptionMessage)) {

				Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------ UPS ' . strtoupper($request_type) . ' EXCEPTION MESSAGE ------------------------', $this->debug);
				Ph_UPS_Woo_Shipping_Common::phAddDebugLog(htmlspecialchars($exceptionMessage), $this->debug);
			}
		}
		return $ups_response;
	}

	/**
	 * get_rate_requests_gfp
	 *
	 * Get rate requests for ground freight
	 * @access private
	 * @return array of strings - XML
	 *
	 */
	public function  get_rate_requests_gfp($package_requests, $package, $request_type = '', $service_code = '')
	{
		global $woocommerce;

		$customer = $woocommerce->customer;

		$package_requests_to_append	= $package_requests;
		$rate_request_data			=	array(
			'user_id'					=>	$this->user_id,
			'password'					=>	str_replace('&', '&amp;', $this->password), // Ampersand will break XML doc, so replace with encoded version.
			'access_key'				=>	$this->access_key,
			'shipper_number'			=>	$this->shipper_number,
			'origin_addressline'		=>	$this->origin_addressline,
			'origin_addressline_2'		=>	$this->origin_addressline_2,
			'origin_postcode'			=>	$this->origin_postcode,
			'origin_city'				=>	$this->origin_city,
			'origin_state'				=>	$this->origin_state,
			'origin_country'			=>	$this->origin_country,
			'ship_from_addressline'		=>	$this->ship_from_addressline,
			'ship_from_addressline_2'	=>	$this->ship_from_addressline_2,
			'ship_from_postcode'		=>	$this->ship_from_postcode,
			'ship_from_city'			=>	$this->ship_from_city,
			'ship_from_state'			=>	$this->ship_from_state,
			'ship_from_country'			=>	$this->ship_from_country,
		);

		$rate_request_data	=	apply_filters('wf_ups_rate_request_data', $rate_request_data, $package, $package_requests);

		$request['RateRequest'] = array();

		$request['RateRequest']['Request'] = array(

			'TransactionReference' => array(
				'CustomerContext' => 'Rating and Service'
			),
			'RequestAction' => 'Rate',
			'RequestOption' => 'Rate'
		);

		$request['RateRequest']['PickupType'] = array(
			'Code' => $this->pickup,
			'Description' => PH_WC_UPS_Constants::PICKUP_CODE[$this->pickup]
		);

		$request['RateRequest']['Shipment'] = array(
			'FRSPaymentInformation' => array(
				'Type' => array(
					'Code' => '01'
				),
				'AccountNumber' => $this->shipper_number
			),
			'Description' => 'WooCommerce GFP Rate Request',
		);

		$originAddress = empty($rate_request_data['origin_addressline_2']) ? $rate_request_data['origin_addressline'] : array($rate_request_data['origin_addressline'], $rate_request_data['origin_addressline_2']);

		$request['RateRequest']['Shipment']['Shipper'] = array(
			'Address' => array(
				'AddressLine' => $originAddress,
				'CountryCode' => $rate_request_data['origin_country'],
			),
			'ShipperNumber' => $rate_request_data['shipper_number'],
		);

		$request['RateRequest']['Shipment']['Shipper']['Address'] = array_merge($request['RateRequest']['Shipment']['Shipper']['Address'], PH_WC_UPS_Common_Utils::ph_get_postcode_city_in_array($rate_request_data['origin_country'], $rate_request_data['origin_city'], $rate_request_data['origin_postcode']));

		if (!empty($rate_request_data['origin_state'])) {
			$request['RateRequest']['Shipment']['Shipper']['Address']['StateProvinceCode'] = $rate_request_data['origin_state'];
		}

		$destination_city 		= htmlspecialchars(strtoupper($package['destination']['city']));
		$destination_country 	= "";

		if (("PR" == $package['destination']['state']) && ("US" == $package['destination']['country'])) {
			$destination_country = "PR";
		} else {
			$destination_country = $package['destination']['country'];
		}

		$request['RateRequest']['Shipment']['ShipTo']['Address'] = array();

		$address = '';

		if (!empty($package['destination']['address_1'])) {

			$address = htmlspecialchars($package['destination']['address_1']);

			if (isset($package['destination']['address_2']) && !empty($package['destination']['address_2'])) {

				$address = $address . ' ' . htmlspecialchars($package['destination']['address_2']);
			}
		} elseif (!empty($package['destination']['address'])) {

			$address = htmlspecialchars($package['destination']['address']);
		}

		if (!empty($address)) {

			$request['RateRequest']['Shipment']['ShipTo']['Address']['AddressLine'] = $address;
		}

		$request['RateRequest']['Shipment']['ShipTo']['Address']['StateProvinceCode'] = htmlspecialchars($package['destination']['state']);

		$request['RateRequest']['Shipment']['ShipTo']['Address'] = array_merge($request['RateRequest']['Shipment']['ShipTo']['Address'], PH_WC_UPS_Common_Utils::ph_get_postcode_city_in_array($destination_country, $destination_city, $package['destination']['postcode']));
		$request['RateRequest']['Shipment']['ShipTo']['Address']['CountryCode'] = $destination_country;
		$request['RateRequest']['Shipment']['ShipTo']['Address']['CountryCode'] = $destination_country;
		if ($this->residential) {
			$request['RateRequest']['Shipment']['ShipTo']['Address']['ResidentialAddressIndicator'] = 1;
		}

		// If ShipFrom address is different.
		if ($this->ship_from_address_different_from_shipper == 'yes' && !empty($rate_request_data['ship_from_addressline'])) {

			$fromAddress = empty($rate_request_data['ship_from_addressline_2']) ? $rate_request_data['ship_from_addressline'] : array($rate_request_data['ship_from_addressline'], $rate_request_data['ship_from_addressline_2']);

			$request['RateRequest']['Shipment']['ShipFrom'] = array(
				'Address' => array(
					'AddressLine' => $fromAddress,
					'CountryCode' => $rate_request_data['ship_from_country'],
				),
			);

			$request['RateRequest']['Shipment']['ShipFrom']['Address'] = array_merge($request['RateRequest']['Shipment']['ShipFrom']['Address'], PH_WC_UPS_Common_Utils::ph_get_postcode_city_in_array($rate_request_data['ship_from_country'], $rate_request_data['ship_from_city'], $rate_request_data['ship_from_postcode']));

			if (!empty($rate_request_data['ship_from_state'])) {
				$request['RateRequest']['Shipment']['ShipFrom']['Address']['StateProvinceCode'] = $rate_request_data['ship_from_state'];
			}
		} else {

			$fromAddress = empty($rate_request_data['origin_addressline_2']) ? $rate_request_data['origin_addressline'] : array($rate_request_data['origin_addressline'], $rate_request_data['origin_addressline_2']);

			$request['RateRequest']['Shipment']['ShipFrom'] = array(
				'Address' => array(
					'AddressLine' => $fromAddress,
					'CountryCode' => $rate_request_data['origin_country'],
				),
			);

			$request['RateRequest']['Shipment']['ShipFrom']['Address'] = array_merge($request['RateRequest']['Shipment']['ShipFrom']['Address'], PH_WC_UPS_Common_Utils::ph_get_postcode_city_in_array($rate_request_data['origin_country'], $rate_request_data['origin_city'], $rate_request_data['origin_postcode']));

			if ($rate_request_data['origin_state']) {
				$request['RateRequest']['Shipment']['ShipFrom']['Address']['StateProvinceCode'] = $rate_request_data['origin_state'];
			}
		}

		$request['RateRequest']['Shipment']['Service'] = array('Code' => '03');
		$total_item_count = 0;

		if (isset($package['contents']) && !empty($package['contents'])) {

			foreach ($package['contents'] as $product) {
				$total_item_count += $product['quantity'];
			}
		}

		$request['RateRequest']['Shipment']['NumOfPieces'] = $total_item_count;

		// packages
		$total_package_weight = 0;
		$total_packags = array();
		foreach ($package_requests_to_append as $key => $package_request) {

			$package_request = $this->ph_ups_convert_weight_dimension_based_on_vendor($package_request);

			$total_package_weight += $package_request['Package']['PackageWeight']['Weight'];
			$package_request['Package']['PackageWeight'] = PH_WC_UPS_Common_Utils::copyArray($package_request['Package']['PackageWeight']);
			$package_request['Package']['Commodity']['FreightClass'] = $this->freight_class;
			$package_request['Package']['PackagingType']['Code'] = "02";
			// Setting Length, Width and Height for weight based packing.
			if (!isset($package_request['Package']['Dimensions']) || !empty($package_request['Package']['Dimensions'])) {
				unset($package_request['Package']['Dimensions']);
			}

			//PDS-87
			if (isset($package_request['Package']['PackageServiceOptions'])) {

				if (isset($package_request['Package']['PackageServiceOptions']['InsuredValue'])) {

					unset($package_request['Package']['PackageServiceOptions']['InsuredValue']);
				}

				if (isset($package_request['Package']['PackageServiceOptions']['DeliveryConfirmation'])) {

					$package_request['Package']['PackageServiceOptions']['DeliveryConfirmation'] = PH_WC_UPS_Common_Utils::copyArray($package_request['Package']['PackageServiceOptions']['DeliveryConfirmation']);
				}
			}

			if (isset($package_request['Package']['items'])) {
				unset($package_request['Package']['items']);		//Not required further
			}

			$total_packags[] = $package_request['Package'];
		}

		$request['RateRequest']['Shipment']['Package'] = $total_packags;

		$request['RateRequest']['Shipment']['ShipmentRatingOptions'] = array();
		if ($this->negotiated) {
			$request['RateRequest']['Shipment']['ShipmentRatingOptions']['NegotiatedRatesIndicator'] = 1;
		}
		$request['RateRequest']['Shipment']['ShipmentRatingOptions']['FRSShipmentIndicator'] = 1;

		if ($this->tax_indicator) {
			$request['RateRequest']['Shipment']['TaxInformationIndicator'] = 1;
		}

		$request['RateRequest']['Shipment']['DeliveryTimeInformation'] = array(

			'PackageBillType' => '03',
		);

		if ($this->show_est_delivery && !empty($this->settings['cut_off_time']) && $this->settings['cut_off_time'] != '24:00') {
			$timestamp 							= clone $this->current_wp_time;
			$this->current_wp_time_hour_minute 	= current_time('H:i');
			if ($this->current_wp_time_hour_minute > $this->settings['cut_off_time']) {
				$timestamp->modify('+1 days');
				$this->pickup_date = $timestamp->format('Ymd');
				$this->pickup_time = '0800';
			} else {
				$this->pickup_date = date('Ymd');
				$this->pickup_time = $timestamp->format('Hi');
			}

			// Adjust estimated delivery based on Ship time adjsutment settings
			if (!empty($this->shipTimeAdjustment)) {

				$dateObj = new DateTime($this->pickup_date);
				$dateObj->modify("+ $this->shipTimeAdjustment days");
				$this->pickup_date = $dateObj->format('Ymd');
			}

			$request['RateRequest']['Shipment']['DeliveryTimeInformation']['Pickup'] = array(
				'Date' => $this->pickup_date,
				'Time' => $this->pickup_time,
			);
		}

		$request['RateRequest']['Shipment']['ShipmentTotalWeight'] = array(

			'UnitOfMeasurement' => array(
				'Code'	=> $this->weight_unit
			),
			'Weight' => $total_package_weight
		);

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
			$request['RateRequest']['Shipment']['FreightShipmentInformation'] = array(

				'FreightDensityInfo' => array(

					'HandlingUnits' => array(

						'Quantity' 	=> 1,
						'Type'		=> array(

							'Code'			=> 'PLT',
							'Description'	=> 'Density'
						),
						'Dimensions' => array(

							'UnitOfMeasurement'	=> array('Code'	=> $this->density_unit),
							'Description'		=> "Dimension unit",
							'Length'			=> $this->density_length,
							'Width'				=> $this->density_width,
							'Height'			=> $this->density_height
						)
					),
					'Description'	=> "density rating",
				),
				'DensityEligibleIndicator'	=> 1,
			);
		}

		return apply_filters('ph_ups_rate_request_gfp', $request, $package);
	}

	//function to get result for GFP
	public function get_result_gfp($request, $request_type = '', $key = '', $orderId = '')
	{
		$ups_response = null;
		$key++;
		$ups_settings		= get_option('woocommerce_' . WF_UPS_ID . '_settings', null);
		$api_mode	  		= isset($ups_settings['api_mode']) ? $ups_settings['api_mode'] : 'Test';
		$header = new stdClass();
		$header->UsernameToken = new stdClass();
		$header->UsernameToken->Username = $this->user_id;
		$header->UsernameToken->Password = $this->password;
		$header->ServiceAccessToken = new stdClass();
		$header->ServiceAccessToken->AccessLicenseNumber = $this->access_key;

		$header = new SoapHeader('http://www.ups.com/XMLSchema/XOLTWS/UPSS/v1.0', 'UPSSecurity', $header, false);

		$wsdl = plugin_dir_path(dirname(__FILE__)) . 'wsdl/' . $api_mode . '/RateWS.wsdl';

		$client = $this->wf_create_soap_client($wsdl, array(
			'trace' =>	true,
			// 'uri'      => "https://wwwcie.ups.com/webservices/Rate",
			// 'location'      => "https://wwwcie.ups.com/webservices/Rate",
			'cache_wsdl' => 0
		));

		$client->__setSoapHeaders($header);


		try {

			$ups_response = $client->ProcessRate($request['RateRequest']);
			$request = $client->__getLastRequest();
			$response = $client->__getLastResponse();
		} catch (\SoapFault $fault) {
		}

		if ($this->debug) {

			$orderId = !empty($orderId) ? '#' . $orderId : '';

			Ph_UPS_Woo_Shipping_Common::debug("UPS GFP REQUEST [ Package Set: " . $key . " | Max Packages: 50 ] <pre>" . print_r(htmlspecialchars($client->__getLastRequest()), true) . '</pre>', $this->debug, $this->silent_debug);
			Ph_UPS_Woo_Shipping_Common::debug("UPS GFP RESPONSE [ Package Set: " . $key . "	| Max Packages: 50 ] <pre>" . print_r(htmlspecialchars($client->__getLastResponse()), true) . '</pre>', $this->debug, $this->silent_debug);

			Ph_UPS_Woo_Shipping_Common::phAddDebugLog("------------------------ UPS GFP REQUEST [ Package Set: $key | Max Packages: 50 ] $orderId ------------------------", $this->debug);
			Ph_UPS_Woo_Shipping_Common::phAddDebugLog(htmlspecialchars($client->__getLastRequest()), $this->debug);
			Ph_UPS_Woo_Shipping_Common::phAddDebugLog("------------------------ UPS GFP RESPONSE [ Package Set: $key | Max Packages: 50 ] $orderId ------------------------", $this->debug);
			Ph_UPS_Woo_Shipping_Common::phAddDebugLog(htmlspecialchars($client->__getLastResponse()), $this->debug);
		}

		return $ups_response;
	}

	public function process_result_gfp($gfp_response, $type = '', $package = array())
	{

		$rates = array();
		if (!empty($gfp_response)) {

			$gfp_response = isset($gfp_response->RatedShipment) ? $gfp_response->RatedShipment : '';	// Normal rates : freight rates

			$gfp_response = apply_filters('ph_ups_gfp_rate_adjustment', $gfp_response, $package); // Additional cost Adjustment for Ground with Freight Services

			$code = 'US48';
			$service_name = $this->services[$code];
			if ($this->negotiated && isset($gfp_response->NegotiatedRateCharges->TotalCharge->MonetaryValue)) {
				$rate_cost = (float) $gfp_response->NegotiatedRateCharges->TotalCharge->MonetaryValue;
			} else {
				$rate_cost = (float) $gfp_response->TotalCharges->MonetaryValue;
			}


			$rate_id	 = $this->id . ':' . $code;

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

			$rate_cost =  $this->ph_ups_get_shipping_cost_after_conversion($rate_cost);

			$rates[$rate_id] = array(
				'id' 	=> $rate_id,
				'label' => $rate_name,
				'cost' 	=> $rate_cost,
				'sort'  => $sort,
				'meta_data'	=> array(
					'_xa_ups_method'	=>	array(
						'id'			=>	$rate_id,	// Rate id will be in format WF_UPS_ID:service_id ex for ground wf_shipping_ups:03
						'method_title'	=>	$rate_name,
						'items'			=>	isset($this->current_package_items_and_quantity) ? $this->current_package_items_and_quantity : array(),
					),
					'VendorId'			=> !empty($this->vendorId) ? $this->vendorId : null,
				)
			);
		}
		return $rates;
	}

	/**
	 * get_rate_requests
	 *
	 * Get rate requests for all
	 * @access private
	 * @return array of strings - XML
	 *
	 */
	public function  get_rate_requests($package_requests, $package, $request_type = '', $service_code = '', $international_delivery_confirmation_applicable = false)
	{
		global $woocommerce;

		$this->international_delivery_confirmation_applicable = $international_delivery_confirmation_applicable;

		if (isset($_GET['wf_ups_generate_packages_rates'])) {

			$order_id 	= base64_decode($_GET['wf_ups_generate_packages_rates']);
		} else if (isset($_GET['wf_ups_shipment_confirm'])) {

			$query_string 	= explode( '|', base64_decode($_GET['wf_ups_shipment_confirm']) );
			$order_id		= end( $query_string );
		}

		$package_requests_to_append	= $package_requests;

		$rate_request_data	=	array(
			'user_id'					=>	$this->user_id,
			'password'					=>	str_replace('&', '&amp;', $this->password), // Ampersand will break XML doc, so replace with encoded version.
			'access_key'				=>	$this->access_key,
			'shipper_number'			=>	$this->shipper_number,
			'origin_addressline'		=>	$this->origin_addressline,
			'origin_addressline_2'		=>	$this->origin_addressline_2,
			'origin_postcode'			=>	$this->origin_postcode,
			'origin_city'				=>	$this->origin_city,
			'origin_state'				=>	$this->origin_state,
			'origin_country'			=>	$this->origin_country,
			'ship_from_addressline'		=>	$this->ship_from_addressline,
			'ship_from_addressline_2'	=>	$this->ship_from_addressline_2,
			'ship_from_postcode'		=>	$this->ship_from_postcode,
			'ship_from_city'			=>	$this->ship_from_city,
			'ship_from_state'			=>	$this->ship_from_state,
			'ship_from_country'			=>	$this->ship_from_country,

			// If Import Control is enabled then also Shipper address will not change.
			'shipper_addressline'		=>	$this->origin_addressline,
			'shipper_addressline_2'		=>	$this->origin_addressline_2,
			'shipper_postcode'			=>	$this->origin_postcode,
			'shipper_city'				=>	$this->origin_city,
			'shipper_state'				=>	$this->origin_state,
			'shipper_country'			=>	$this->origin_country,
		);

		// Checking Import Control selected in edit order page
		if (isset($_GET['impc'])) {

			$import_control = $_GET['impc'] ? $_GET['impc'] : '';

			PH_UPS_WC_Storage_Handler::ph_add_and_save_meta_data($order_id, '_ph_ups_import_control', $_GET['impc']);
		}

		if ((isset($import_control) && $import_control == 'true') || $this->import_control_settings == 'yes') {

			$rate_request_data['origin_addressline'] 	 = $package['destination']['address'];
			$rate_request_data['origin_addressline_2'] 	 = '';
			$rate_request_data['origin_postcode'] 		 = $package['destination']['postcode'];
			$rate_request_data['origin_city'] 			 = $package['destination']['city'];
			$rate_request_data['origin_state'] 			 = $package['destination']['state'];
			$rate_request_data['origin_country'] 		 = $package['destination']['country'];

			$package['destination']['address']  = $this->origin_addressline;
			$package['destination']['country']  = $this->origin_country;
			$package['destination']['state'] 	 = $this->origin_state;
			$package['destination']['postcode'] = $this->origin_postcode;
			$package['destination']['city'] 	 = $this->origin_city;

			if ($this->ship_from_address_different_from_shipper == 'yes') {

				$rate_request_data['ship_from_addressline']  = $package['destination']['address'];
				$rate_request_data['ship_from_addressline_2'] = '';
				$rate_request_data['ship_from_postcode'] 	 = $package['destination']['postcode'];
				$rate_request_data['ship_from_city'] 		 = $package['destination']['city'];
				$rate_request_data['ship_from_state'] 		 = $package['destination']['state'];
				$rate_request_data['ship_from_country'] 	 = $package['destination']['country'];

				$package['destination']['address']  = $this->ship_from_addressline;
				$package['destination']['country']  = $this->ship_from_country;
				$package['destination']['state'] 	 = $this->ship_from_state;
				$package['destination']['postcode'] = $this->ship_from_postcode;
				$package['destination']['city'] 	 = $this->ship_from_city;
			}
		}

		$rate_request_data	=	apply_filters('wf_ups_rate_request_data', $rate_request_data, $package, $package_requests);

		$this->is_hazmat_product 	= false;

		$request = '';

		// Security Header

		if (!Ph_UPS_Woo_Shipping_Common::ph_is_oauth_registered_customer()) {

			$request .= "<?xml version=\"1.0\" ?>" . "\n";
			$request .= "<AccessRequest xml:lang='en-US'>" . "\n";
			$request .= "	<AccessLicenseNumber>" . $rate_request_data['access_key'] . "</AccessLicenseNumber>" . "\n";
			$request .= "	<UserId>" . $rate_request_data['user_id'] . "</UserId>" . "\n";
			$request .= "	<Password>" . $rate_request_data['password'] . "</Password>" . "\n";
			$request .= "</AccessRequest>" . "\n";
			$request .= "<?xml version=\"1.0\" ?>" . "\n";
		}

		$request .= "<RatingServiceSelectionRequest>" . "\n";
		$request .= "	<Request>" . "\n";
		$request .= "	<TransactionReference>" . "\n";
		$request .= "		<CustomerContext>Rating and Service</CustomerContext>" . "\n";
		$request .= "		<XpciVersion>1.0</XpciVersion>" . "\n";
		$request .= "	</TransactionReference>" . "\n";
		$request .= "	<RequestAction>Rate</RequestAction>" . "\n";

		// For Estimated delivery, Estimated delivery not available for Surepost confirmed by UPS
		if ($this->show_est_delivery && $request_type != 'surepost') {
			$requestOption = empty($service_code) ? 'Shoptimeintransit' : 'Ratetimeintransit';
		} else {
			$requestOption = empty($service_code) ? 'Shop' : 'Rate';
		}
		$request .= "	<RequestOption>$requestOption</RequestOption>" . "\n";
		$request .= "	</Request>" . "\n";
		$request .= "	<PickupType>" . "\n";
		$request .= "		<Code>" . $this->pickup . "</Code>" . "\n";
		$request .= "		<Description>" . PH_WC_UPS_Constants::PICKUP_CODE[$this->pickup] . "</Description>" . "\n";
		$request .= "	</PickupType>" . "\n";

		//Accroding to the documentaion CustomerClassification will not work for non-us county. But UPS team confirmed this will for any country.
		// if ( 'US' == $rate_request_data['origin_country']) {
		if ($this->negotiated) {
			$request .= "	<CustomerClassification>" . "\n";
			$request .= "		<Code>" . "00" . "</Code>" . "\n";
			$request .= "	</CustomerClassification>" . "\n";
		} elseif (!empty($this->customer_classification) && $this->customer_classification != 'NA') {
			$request .= "	<CustomerClassification>" . "\n";
			$request .= "		<Code>" . $this->customer_classification . "</Code>" . "\n";
			$request .= "	</CustomerClassification>" . "\n";
		}
		// }

		// Shipment information
		$request .= "	<Shipment>" . "\n";

		if ($this->accesspoint_locator) {
			$access_point_node = $this->get_acccesspoint_rate_request( $this->ph_ups_selected_access_point_details );
			if (!empty($access_point_node)) { // Access Point Addresses Are All Commercial
				$this->residential	=	false;
				$request .= $access_point_node;
			}
		}

		$request .= "		<Description>WooCommerce Rate Request</Description>" . "\n";
		$request .= "		<Shipper>" . "\n";
		$request .= "			<ShipperNumber>" . $rate_request_data['shipper_number'] . "</ShipperNumber>" . "\n";
		$request .= "			<Address>" . "\n";
		$request .= "				<AddressLine1>" . $rate_request_data['shipper_addressline'] . "</AddressLine1>" . "\n";

		if (!empty($rate_request_data['shipper_addressline_2'])) {

			$request .= "				<AddressLine2>" . $rate_request_data['shipper_addressline_2'] . "</AddressLine2>" . "\n";
		}

		$request .= PH_WC_UPS_Common_Utils::wf_get_postcode_city($rate_request_data['shipper_country'], $rate_request_data['shipper_city'], $rate_request_data['shipper_postcode']);

		if (!empty($rate_request_data['shipper_state'])) {
			$request .= "			<StateProvinceCode>" . $rate_request_data['shipper_state'] . "</StateProvinceCode>\n";
		}

		$request .= "				<CountryCode>" . $rate_request_data['shipper_country'] . "</CountryCode>" . "\n";
		$request .= "			</Address>" . "\n";
		$request .= "		</Shipper>" . "\n";
		$request .= "		<ShipTo>" . "\n";
		$request .= "			<Address>" . "\n";

		// Residential address Validation done by API automatically if address_1 is available.
		$address = '';

		if (!empty($package['destination']['address_1'])) {

			$address = htmlspecialchars($package['destination']['address_1']);

			if (isset($package['destination']['address_2']) && !empty($package['destination']['address_2'])) {

				$address = $address . ' ' . htmlspecialchars($package['destination']['address_2']);
			}
		} elseif (!empty($package['destination']['address'])) {

			$address = htmlspecialchars($package['destination']['address']);
		}

		if (!empty($address)) {

			$request .= "				<AddressLine1>" . $address . "</AddressLine1>" . "\n";
		}

		$request .= "				<StateProvinceCode>" . htmlspecialchars($package['destination']['state']) . "</StateProvinceCode>" . "\n";

		$destination_city = htmlspecialchars(strtoupper($package['destination']['city']));
		$destination_country = "";
		if (("PR" == $package['destination']['state']) && ("US" == $package['destination']['country'])) {
			$destination_country = "PR";
		} else {
			$destination_country = $package['destination']['country'];
		}

		//$request .= "				<PostalCode>" . $package['destination']['postcode'] . "</PostalCode>" . "\n";
		$request .= PH_WC_UPS_Common_Utils::wf_get_postcode_city($destination_country, $destination_city, $package['destination']['postcode']);
		$request .= "				<CountryCode>" . $destination_country . "</CountryCode>" . "\n";

		if ($this->residential) {
			$request .= "				<ResidentialAddressIndicator></ResidentialAddressIndicator>" . "\n";
		}
		$request .= "			</Address>" . "\n";
		$request .= "		</ShipTo>" . "\n";

		// If ShipFrom address is different.
		if ($this->ship_from_address_different_from_shipper == 'yes' && !empty($rate_request_data['ship_from_addressline'])) {
			$request .= "		<ShipFrom>" . "\n";
			$request .= "			<Address>" . "\n";
			$request .= "				<AddressLine1>" . $rate_request_data['ship_from_addressline'] . "</AddressLine1>" . "\n";

			if (!empty($rate_request_data['ship_from_addressline_2'])) {

				$request .= "			<AddressLine2>" . $rate_request_data['ship_from_addressline_2'] . "</AddressLine2>" . "\n";
			}

			$request .= PH_WC_UPS_Common_Utils::wf_get_postcode_city($rate_request_data['ship_from_country'], $rate_request_data['ship_from_city'], $rate_request_data['ship_from_postcode']);

			if (!empty($rate_request_data['ship_from_state'])) {
				$request .= "			<StateProvinceCode>" . $rate_request_data['ship_from_state'] . "</StateProvinceCode>\n";
			}

			$request .= "				<CountryCode>" . $rate_request_data['ship_from_country'] . "</CountryCode>" . "\n";
			$request .= "			</Address>" . "\n";
			$request .= "		</ShipFrom>" . "\n";
		} else {
			$request .= "		<ShipFrom>" . "\n";
			$request .= "			<Address>" . "\n";
			$request .= "				<AddressLine1>" . $rate_request_data['origin_addressline'] . "</AddressLine1>" . "\n";

			if (!empty($rate_request_data['origin_addressline_2'])) {

				$request .= "			<AddressLine2>" . $rate_request_data['origin_addressline_2'] . "</AddressLine2>" . "\n";
			}

			$request .= PH_WC_UPS_Common_Utils::wf_get_postcode_city($rate_request_data['origin_country'], $rate_request_data['origin_city'], $rate_request_data['origin_postcode']);

			if (!empty($rate_request_data['origin_state'])) {
				$request .= "			<StateProvinceCode>" . $rate_request_data['origin_state'] . "</StateProvinceCode>\n";
			}
			$request .= "				<CountryCode>" . $rate_request_data['origin_country'] . "</CountryCode>" . "\n";
			$request .= "			</Address>" . "\n";
			$request .= "		</ShipFrom>" . "\n";
		}

		//For Worldwide Express Freight Service
		if ($request_type == 'Pallet' && $service_code == 96 && isset($package['contents']) && is_array($package['contents'])) {
			$total_item_count = 0;
			foreach ($package['contents'] as $product) {
				$total_item_count += $product['quantity'];
			}
			$request .= "	<NumOfPieces>" . $total_item_count . "</NumOfPieces>" . "\n";
		}

		if (!empty($service_code)) {
			$request .= "		<Service>" . "\n";
			$request .= "			<Code>" . PH_WC_UPS_Common_Utils::get_service_code_for_country($service_code, $rate_request_data['origin_country']) . "</Code>" . "\n";
			$request .= "		</Service>" . "\n";
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
				if ($this->isc) {

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
			$ph_hazmat_details = [];
			$this->is_hazmat_product = false;

			if (isset($package_request['Package']) && isset($package_request['Package']['items']) && !empty($package_request['Package']['items'])) {

				foreach ($package_request['Package']['items'] as $key => $value) {

					$cod_amount = $cod_amount + (!empty($value->get_price()) ? $value->get_price() : $this->fixedProductPrice);

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

						$hazmat_details_arr = [];
						$hazmat_details_arr['ChemicalRecordIdentifier'] = !empty($hazmat_settings['_ph_ups_record_number']) ? $hazmat_settings['_ph_ups_record_number'] : '';
						$hazmat_details_arr['ClassDivisionNumber'] = !empty($hazmat_settings['_ph_ups_class_division_no']) ? $hazmat_settings['_ph_ups_class_division_no'] : '';
						$hazmat_details_arr['IDNumber'] = !empty($hazmat_settings['_ph_ups_commodity_id']) ? $hazmat_settings['_ph_ups_commodity_id'] : '';
						$hazmat_details_arr['TransportationMode'] = $hazmat_settings['_ph_ups_hm_transportaion_mode'];
						$hazmat_details_arr['RegulationSet'] = $hazmat_settings['_ph_ups_hm_regulations'];
						$hazmat_details_arr['PackagingGroupType'] = !empty($hazmat_settings['_ph_ups_package_group_type']) ? $hazmat_settings['_ph_ups_package_group_type'] : '';
						$hazmat_details_arr['PackagingInstructionCode'] = !empty($hazmat_settings['_ph_ups_package_instruction_code']) ? $hazmat_settings['_ph_ups_package_instruction_code'] : '';
						$hazmat_details_arr['Quantity'] = round($value->get_weight(), 1);
						$hazmat_details_arr['UOM'] = ($this->uom == 'LB') ? 'pound' : 'kg';
						$hazmat_details_arr['ProperShippingName'] = !empty($hazmat_settings['_ph_ups_shipping_name']) ? $hazmat_settings['_ph_ups_shipping_name'] : '';
						$hazmat_details_arr['TechnicalName'] = !empty($hazmat_settings['_ph_ups_technical_name']) ? $hazmat_settings['_ph_ups_technical_name'] : '';
						$hazmat_details_arr['AdditionalDescription'] = !empty($hazmat_settings['_ph_ups_additional_description']) ? $hazmat_settings['_ph_ups_additional_description'] : '';
						$hazmat_details_arr['PackagingType'] = !empty($hazmat_settings['_ph_ups_package_type']) ? $hazmat_settings['_ph_ups_package_type'] : '';
						$hazmat_details_arr['PackagingTypeQuantity'] = 1;
						$hazmat_details_arr['CommodityRegulatedLevelCode'] = $hazmat_settings['_ph_ups_hm_commodity'];
						$hazmat_details_arr['EmergencyPhone'] = $this->phone_number;
						$hazmat_details_arr['EmergencyContact'] = $this->ups_display_name;
						
						$ph_already_added_hazmat = false;

						if ( isset($ph_hazmat_details) && is_array($ph_hazmat_details) ) {

							foreach($ph_hazmat_details as $key => $value ) {

								if ( $hazmat_details_arr['RegulationSet'] == $value['HazMatChemicalRecord']['RegulationSet'] && $hazmat_details_arr['IDNumber'] == $value['HazMatChemicalRecord']['IDNumber'] && $hazmat_details_arr['PackagingType'] == $value['HazMatChemicalRecord']['PackagingType'] ) {

									$ph_hazmat_details[$key]['HazMatChemicalRecord']['Quantity'] += $hazmat_details_arr['Quantity'];

									$ph_hazmat_details[$key]['HazMatChemicalRecord']['PackagingTypeQuantity'] = 1;

									$ph_already_added_hazmat = true;
									break;
								}
							}
						}

						if ( !$ph_already_added_hazmat ) {

							$ph_hazmat_details[] = array('HazMatChemicalRecord' => $hazmat_details_arr);
						}
					}
				}
			}

			$total_package_weight += $package_request['Package']['PackageWeight']['Weight'];

			$package_request = $this->ph_ups_convert_weight_dimension_based_on_vendor($package_request);

			if ($request_type == 'surepost') {
				unset($package_request['Package']['PackageServiceOptions']['InsuredValue']);
				if ($service_code == 92) {
					$package_request = PH_WC_UPS_Common_Utils::convert_weight($package_request, $this->weight_unit, $service_code);
				}
			}

			//For Worldwide Express Freight Service
			if ($request_type == "Pallet") {
				$package_request['Package']['PackagingType']['Code'] = 30;
				// Setting Length, Width and Height for weight based packing.
				if (empty($package_request['Package']['Dimensions'])) {

					$package_request['Package']['Dimensions'] = array(
						'UnitOfMeasurement' => array(
							'Code'  => ($package_request['Package']['PackageWeight']['UnitOfMeasurement']['Code'] == 'LBS') ? 'IN' : 'CM',
						),
						'Length'    => ($package_request['Package']['PackageWeight']['UnitOfMeasurement']['Code'] == 'LBS') ? 47 : 119,
						'Width'	    => ($package_request['Package']['PackageWeight']['UnitOfMeasurement']['Code'] == 'LBS') ? 47 : 119,
						'Height'    => ($package_request['Package']['PackageWeight']['UnitOfMeasurement']['Code'] == 'LBS') ? 47 : 119
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


			// To Set deliveryconfirmation at shipment level if shipment is international or outside of $this->dc_domestic_countries
			if (is_admin() && !isset($_GET['sig'])) {

				$usCountry 	= array('US', 'PR');
				$origin 	= $this->origin_country;
				$dest 		= $package['destination']['country'];

				if ( isset($package_request['Package']) && isset($package_request['Package']['items'])) {

					// To Set deliveryconfirmation at package level
					if ( (( $origin == $dest && in_array($origin, array('US','PR','CA'))) || (in_array($origin, $usCountry) && in_array($dest, $usCountry))) ) {

						$shipment_delivery_confirmation = PH_WC_UPS_Common_Utils::get_package_signature($package_request['Package']['items']);
					
						$shipment_delivery_confirmation = $shipment_delivery_confirmation != 0 ? $shipment_delivery_confirmation : $this->ph_delivery_confirmation;
						
						$shipment_delivery_confirmation = isset($_GET['sig']) && $_GET['sig'] != 4 && $_GET['sig'] != 0 ? $_GET['sig'] : $shipment_delivery_confirmation;

						if ( !empty($shipment_delivery_confirmation)) {

							$shipment_delivery_confirmation = $shipment_delivery_confirmation < 3 ? 2 : $shipment_delivery_confirmation;

							$package_request['Package']['PackageServiceOptions']['DeliveryConfirmation'] = array('DCISType' => $shipment_delivery_confirmation);
						}
					} else {

						// To Set deliveryconfirmation at shipment level
						$shipment_delivery_confirmation = PH_WC_UPS_Common_Utils::get_package_signature($package_request['Package']['items']);
						$shipment_delivery_confirmation = $shipment_delivery_confirmation < $this->ph_delivery_confirmation ? $this->ph_delivery_confirmation : $shipment_delivery_confirmation;

						$delivery_confirmation = (isset($delivery_confirmation) && $delivery_confirmation >= $shipment_delivery_confirmation) ? $delivery_confirmation : $shipment_delivery_confirmation;
					}
				} else {

					// For Manual Package Domestic
					if ( isset($package_request['Package']['PackageServiceOptions']['DeliveryConfirmation']) ) {

						if ( isset($_GET['sig']) && $_GET['sig'] != 0) {

							$ph_sig_value = $_GET['sig'] == 4 ? $this->ph_delivery_confirmation : $_GET['sig'];

							if ( !empty($ph_sig_value) ) {

								$ph_sig_value = $ph_sig_value < 3 ? 2 : $ph_sig_value;

								$package_request['Package']['PackageServiceOptions']['DeliveryConfirmation'] = array('DCISType' => $ph_sig_value);
							}
						}
					}

					// For Manual Package Int
					if ( $origin != $dest && !(in_array($origin, $usCountry) && in_array($dest, $usCountry)) ) {

						if ( isset($_GET['sig']) && $_GET['sig'] != 0) {

							$ph_sig_value = $_GET['sig'] == 4 ? $this->ph_delivery_confirmation : $_GET['sig'];

							if ( !empty($ph_sig_value) ) {

								$delivery_confirmation = $ph_sig_value;
							}
						}
					}
				}
			} else if (isset($this->international_delivery_confirmation_applicable) && $this->international_delivery_confirmation_applicable) {

				$shipment_delivery_confirmation = PH_WC_UPS_Common_Utils::get_package_signature($package_request['Package']['items']);
				$shipment_delivery_confirmation = $shipment_delivery_confirmation < $this->ph_delivery_confirmation ? $this->ph_delivery_confirmation : $shipment_delivery_confirmation;

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
				$hazmat_array['Package']['PackageServiceOptions']['HazMat']['HazMatChemicalRecord']	=	array_merge(array('multi_node' => 1), array_values($ph_hazmat_details));
				$package_request = array_merge_recursive($package_request, $hazmat_array);
			}

			//For Package level COD in Edit Order Page.
			if ((isset($ups_cod) && !empty($ups_cod) && $ups_cod == 'true') && isset($_GET['wf_ups_generate_packages_rates'])) {

				$destination = isset($this->destination['country']) && !empty($this->destination['country']) ? $this->destination['country'] : $package['destination']['country'];

				if (! PH_WC_UPS_Common_Utils::is_shipment_level_cod_required($destination)) {

					$codfundscode = in_array($destination, array('AR', 'BR', 'CL')) ? 9 : 0;

					$cod_array['Package']['PackageServiceOptions']['COD']	=	array(
						'CODCode'		=>	3,
						'CODFundsCode'	=>	$codfundscode,
						'CODAmount'		=>	array(
							'MonetaryValue'	=>	(string) round($cod_amount, 2),
						),
					);

					$package_request = array_merge_recursive($package_request, $cod_array);
				}
			}

			$request .= $this->wf_array_to_xml($package_request);
		}
		// negotiated rates flag
		if ($this->negotiated) {
			$request .= "		<RateInformation>" . "\n";
			$request .= "			<NegotiatedRatesIndicator />" . "\n";
			$request .= "		</RateInformation>" . "\n";
		}

		if ($this->tax_indicator) {
			$request .= "		<TaxInformationIndicator/>" . "\n";
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


		if ((isset($delivery_confirmation) && !empty($delivery_confirmation)) || ($request_type == 'saturday') || ($this->isc) || ($this->cod_enable) || (isset($ups_cod) && $ups_cod == 'true') || (isset($direct_delivery_only) && $direct_delivery_only == 'yes')) {

			$request .= "<ShipmentServiceOptions>";
		}

		// Set deliveryconfirmation at shipment level for international shipment
		if (isset($delivery_confirmation) && !empty($delivery_confirmation)) {

			$delivery_confirmation = ($delivery_confirmation == 3) ? 2 : 1;

			$request .= "			<DeliveryConfirmation>"
				. "<DCISType>$delivery_confirmation</DCISType>"
				. "</DeliveryConfirmation>" . "\n";
		}

		if ((isset($import_control) && !empty($import_control) && $import_control == 'true') || $this->import_control_settings == 'yes') {

			$request .= "<ImportControl><Code>02</Code></ImportControl>";
		}

		if ($request_type == 'saturday') {

			$request .= "<SaturdayDeliveryIndicator></SaturdayDeliveryIndicator>";
		}

		if (isset($direct_delivery_only) && !empty($direct_delivery_only) && $direct_delivery_only == 'yes') {

			$request .= "<DirectDeliveryOnlyIndicator>$direct_delivery_only</DirectDeliveryOnlyIndicator>";
		}

		if ($this->isc) {

			if ($add_global_restricted_article && $this->ph_restricted_article) {

				$alcoholicbeveragesindicator 	= ($alcoholicbeveragesindicator == 'yes') ? $alcoholicbeveragesindicator : $this->ph_alcoholic;
				$diagnosticspecimensindicator 	= ($diagnosticspecimensindicator == 'yes') ? $diagnosticspecimensindicator : $this->ph_diog;
				$perishablesindicator 			= ($perishablesindicator == 'yes') ? $perishablesindicator : $this->ph_perishable;
				$plantsindicator 				= ($plantsindicator == 'yes') ? $plantsindicator : $this->ph_plantsindicator;
				$seedsindicator 				= ($seedsindicator == 'yes') ? $seedsindicator : $this->ph_seedsindicator;
				$specialexceptionsindicator 	= ($specialexceptionsindicator == 'yes') ? $specialexceptionsindicator : $this->ph_specialindicator;
				$tobaccoindicator 				= ($tobaccoindicator == 'yes') ? $tobaccoindicator : $this->ph_tobaccoindicator;
			}

			$request .= "\n  <RestrictedArticles>" . "\n";

			if ($alcoholicbeveragesindicator == 'yes') {

				$request .= "<AlcoholicBeveragesIndicator></AlcoholicBeveragesIndicator>";
			}

			if ($diagnosticspecimensindicator == 'yes') {

				$request .= "<DiagnosticSpecimensIndicator></DiagnosticSpecimensIndicator>";
			}

			if ($perishablesindicator == 'yes') {

				$request .= "<PerishablesIndicator></PerishablesIndicator>";
			}

			if ($plantsindicator == 'yes') {

				$request .= "<PlantsIndicator></PlantsIndicator>";
			}

			if ($seedsindicator == 'yes') {

				$request .= "<SeedsIndicator></SeedsIndicator>";
			}

			if ($specialexceptionsindicator == 'yes') {

				$request .= "<SpecialExceptionsIndicator></SpecialExceptionsIndicator>";
			}

			if ($tobaccoindicator == 'yes') {

				$request .= "<TobaccoIndicator></TobaccoIndicator>";
			}

			$request .= "  </RestrictedArticles>" . "\n";
		}

		if (($this->cod_enable && !isset($ups_cod))  || (isset($ups_cod) && !empty($ups_cod) && $ups_cod == 'true')) {

			$destination = isset($this->destination['country']) && !empty($this->destination['country']) ? $this->destination['country'] : $package['destination']['country'];

			if ( PH_WC_UPS_Common_Utils::is_shipment_level_cod_required($destination)) {

				$cod_amount = isset($package['cart_subtotal']) && !empty($package['cart_subtotal']) ? $package['cart_subtotal'] : $package['contents_cost'];
				// 1 for Cash, 9 for Cheque, 1 is available for all the countries
				$codfundscode = in_array($destination, array('RU', 'AE')) ? 1 : $this->eu_country_cod_type;

				$request .= "<COD><CODCode>3</CODCode><CODFundsCode>" . $codfundscode . "</CODFundsCode><CODAmount><MonetaryValue>" . $cod_amount . "</MonetaryValue></CODAmount></COD>";
			}
		}

		if ((isset($delivery_confirmation) && !empty($delivery_confirmation)) || ($request_type == 'saturday') || ($this->isc) || ($this->cod_enable) || (isset($ups_cod) && $ups_cod == 'true') || (isset($direct_delivery_only) && $direct_delivery_only == 'yes')) {

			$request .= "</ShipmentServiceOptions>";
		}

		// Required for estimated delivery
		if ($this->show_est_delivery) {

			//cuttoff time- PDS-80
			if (!empty($this->settings['cut_off_time']) && $this->settings['cut_off_time'] != '24:00') {

				$timestamp = clone $this->current_wp_time;
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
			if (!empty($this->shipTimeAdjustment)) {

				$dateObj = new DateTime($this->pickup_date);
				$dateObj->modify("+ $this->shipTimeAdjustment days");
				$this->pickup_date = $dateObj->format('Ymd');
			}

			$request .= "\n<DeliveryTimeInformation><PackageBillType>03</PackageBillType><Pickup><Date>" . $this->pickup_date . "</Date><Time>" . $this->pickup_time . "</Time></Pickup></DeliveryTimeInformation>\n";
			$request .= "\n<ShipmentTotalWeight>
						<UnitOfMeasurement><Code>" . $this->weight_unit . "</Code></UnitOfMeasurement>
						<Weight>$total_package_weight</Weight>
						</ShipmentTotalWeight>\n";

			if ($this->ship_from_country != $package['destination']['country']) {

				if (empty($package['contents_cost']) && isset($package['cart_subtotal'])) {

					$package['contents_cost'] = $package['cart_subtotal'];
				}

				$invoiceTotal  = round(($package['contents_cost'] / (float)$this->conversion_rate), 2);

				// Invoice Line Total amount for the shipment.
				// Valid values are from 1 to 99999999
				if ($invoiceTotal < 1) {
					$invoiceTotal 	= 1;
				}

				$request .= "\n<InvoiceLineTotal>
											<CurrencyCode>" . $this->currency_type . "</CurrencyCode>
											<MonetaryValue>" . $invoiceTotal . "</MonetaryValue>
										</InvoiceLineTotal>\n";
			}
		}

		$request .= "	</Shipment>" . "\n";
		$request .= "</RatingServiceSelectionRequest>" . "\n";

		if ($this->is_hazmat_product) {
			$exploded_request = explode("</TransactionReference>", $request);
			$request  = $exploded_request[0] . "</TransactionReference>";
			$request .= "\n    <SubVersion>1701</SubVersion>";
			$request .= $exploded_request[1];
		}

		return apply_filters('wf_ups_rate_request', $request, $package);
	}

	public function get_result($request, $request_type = '', $key = '', $orderId = '')
	{
		$ups_response = null;

		$send_request		   	= str_replace(array("\n", "\r"), '', $request);
		$transient			  	= 'ups_quote_' . md5($request);
		$cached_response		= get_transient($transient);
		$transient_time 		= ((int) $this->rate_caching) * 60 * 60;
		$key++;

		if ($cached_response === false || apply_filters('ph_use_cached_response', false, $cached_response)) {

			if ($request_type == 'freight') {

				$response = wp_remote_post(
					$this->freight_endpoint,
					array(
						'timeout'   => 70,
						'body'	  => $send_request
					)
				);
			} else {

				$response = wp_remote_post(
					$this->endpoint,
					array(
						'timeout'   => 70,
						'body'	  => $send_request
					)
				);
			}

			if (is_wp_error($response)) {
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
			Ph_UPS_Woo_Shipping_Common::debug('UPS ' . strtoupper($request_type) . ' REQUEST XML [ Package Set: ' . $key . $displayCount . ' | Max Packages: 50 ] <pre>' . print_r(htmlspecialchars($send_request), true) . '</pre>', $this->debug, $this->silent_debug);
			Ph_UPS_Woo_Shipping_Common::debug('UPS ' . strtoupper($request_type) . ' RESPONSE XML [ Package Set: ' . $key . $displayCount . ' | Max Packages: 50 ] <pre>' . print_r(htmlspecialchars($ups_response), true) . '</pre>', $this->debug, $this->silent_debug);

			$orderId = !empty($orderId) ? '#' . $orderId : '';

			Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------ UPS ' . strtoupper($request_type) . ' REQUEST [ Package Set: ' . $key . $displayCount . ' | Max Packages: 50 ] ' . $orderId . '------------------------', $this->debug);
			Ph_UPS_Woo_Shipping_Common::phAddDebugLog(htmlspecialchars($send_request), $this->debug);
			Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------ UPS ' . strtoupper($request_type) . ' RESPONSE [ Package Set: ' . $key . $displayCount . ' | Max Packages: 50 ] ' . $orderId . '------------------------', $this->debug);
			Ph_UPS_Woo_Shipping_Common::phAddDebugLog(htmlspecialchars($ups_response), $this->debug);

			if ($cached_response !== false) {
				Ph_UPS_Woo_Shipping_Common::phAddDebugLog(__('Above Response is cached Response.', 'ups-woocommerce-shipping'), $this->debug);
			}
		}

		return $ups_response;
	}

	public function process_result($ups_response, $type = '', $package = array())
	{
		//for freight response
		if ($type == 'json') {
			$xml = json_decode($ups_response);
		} else {

			libxml_use_internal_errors(true);

			$xml = simplexml_load_string(preg_replace('/<\?xml.*\?>/', '', $ups_response));
		}

		if (!$xml) {
			Ph_UPS_Woo_Shipping_Common::debug(__('Failed loading XML', 'ups-woocommerce-shipping'), $this->debug, $this->silent_debug, 'error');
			return;
		}
		$rates = array();
		if ((property_exists($xml, 'Response') && $xml->Response->ResponseStatusCode == 1)  || ($type == 'json' && !property_exists($xml, 'Fault'))) {

			$xml = apply_filters('wf_ups_rate', $xml, $package);
			$xml_response = isset($xml->RatedShipment) ? $xml->RatedShipment : $xml;	// Normal rates : freight rates
			foreach ($xml_response as $response) {
				$code = (string)$response->Service->Code;

				if (!empty($this->custom_services[$code]) && $this->custom_services[$code]['enabled'] != 1) {		// For Freight service code custom services won't be set
					continue;
				}

				if (in_array("$code", array_keys($this->freight_services)) && property_exists($xml, 'FreightRateResponse')) {
					$service_name = $this->freight_services[$code];
					$rate_cost = (float) $xml->FreightRateResponse->TotalShipmentCharge->MonetaryValue;
				} else {
					$service_name = $this->services[$code];
					if ($this->negotiated && isset($response->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue)) {
						if (property_exists($response->NegotiatedRates->NetSummaryCharges, 'TotalChargesWithTaxes')) {
							$rate_cost = (float) $response->NegotiatedRates->NetSummaryCharges->TotalChargesWithTaxes->MonetaryValue;
						} else {
							$rate_cost = (float) $response->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue;
						}
					} else {
						if (property_exists($response, 'TotalChargesWithTaxes')) {
							$rate_cost = (float) $response->TotalChargesWithTaxes->MonetaryValue;
						} else {
							$rate_cost = (float) $response->TotalCharges->MonetaryValue;
						}
					}
				}


				$rate_id	 = $this->id . ':' . $code;

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

				$rate_cost =  $this->ph_ups_get_shipping_cost_after_conversion($rate_cost);

				$rates[$rate_id] = array(
					'id' 	=> $rate_id,
					'label' => $rate_name,
					'cost' 	=> $rate_cost,
					'sort'  => $sort,
					'meta_data'	=> array(
						'_xa_ups_method'	=>	array(
							'id'			=>	$rate_id,	// Rate id will be in format WF_UPS_ID:service_id ex for ground wf_shipping_ups:03
							'method_title'	=>	$rate_name,
							'items'			=>	isset($this->current_package_items_and_quantity) ? $this->current_package_items_and_quantity : array(),
						),
						'VendorId'			=> !empty($this->vendorId) ? $this->vendorId : null,
					)
				);

				// Set Estimated delivery in rates meta data
				if ($this->show_est_delivery) {
					$estimated_delivery = null;
					// Estimated delivery for freight
					if ($type == 'json' && isset($response->TimeInTransit->DaysInTransit)) {
						$days_in_transit 	= (string) $response->TimeInTransit->DaysInTransit;
						$current_time 		= clone $this->current_wp_time;
						if (!empty($days_in_transit))	$estimated_delivery = $current_time->modify("+$days_in_transit days");
					} // Estimated delivery for normal services
					elseif (!empty($response->TimeInTransit->ServiceSummary->EstimatedArrival->Arrival)) {
						$estimated_delivery_date = $response->TimeInTransit->ServiceSummary->EstimatedArrival->Arrival->Date; // Format YYYYMMDD, i.e Ymd
						$estimated_delivery_time = $response->TimeInTransit->ServiceSummary->EstimatedArrival->Arrival->Time; // Format His
						$estimated_delivery = date_create_from_format("Ymj His", $estimated_delivery_date . ' ' . $estimated_delivery_time);
					}

					if (!empty($estimated_delivery)) {
						if (empty($this->wp_date_time_format)) {
							$this->wp_date_time_format = Ph_UPS_Woo_Shipping_Common::get_wordpress_date_format() . ' ' . Ph_UPS_Woo_Shipping_Common::get_wordpress_time_format();
						}

						$rates[$rate_id]['meta_data']['ups_delivery_time'] = apply_filters('ph_ups_estimated_delivery_customization', $estimated_delivery);
						if ($estimated_delivery instanceof DateTime) {

							if(!is_admin()) {
								$rates[$rate_id]['label']	= apply_filters('ph_ups_estimated_delivery_html', $rates[$rate_id]['label'], $this->estimated_delivery_text, $estimated_delivery);
							}
							$rates[$rate_id]['meta_data']['Estimated Delivery'] = $estimated_delivery->format($this->wp_date_time_format);
						}
					}
				}
			}
		}

		return $rates;
	}

	private function wf_create_soap_client($wsdl, $options = ['trace' => true])
	{

		$soapclient = new SoapClient($wsdl, $options);
		return $soapclient;
	}

	/**
	 * Create Debug Request or response.
	 * @param $data mixed Xml or JSON request or response.
	 * @param $type string Rate request or Response.
	 * @param $request_type mixed Request type whether freight or surepost or normal request.
	 */
	public function create_debug_request_or_response($data, $type = '', $request_type = null)
	{
		$debug_data = null;
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
					$data_arr = explode("<RatingServiceSelectionRequest>", $data);
					if (!empty($data_arr[1])) {
						$request_data = self::convert_xml_to_array("<RatingServiceSelectionRequest>" . $data_arr[1]);
						if (!empty($request_data)) {
							$debug_data = array(
								'Ship From Address'	=>	$request_data['Shipment']['ShipFrom']['Address'],
								'Ship To Address'	=>	$request_data['Shipment']['ShipTo']['Address'],
							);
							$packages = isset($request_data['Shipment']['Package']) ? $request_data['Shipment']['Package'] : '';
							// Handle Single Package
							if (isset($request_data['Shipment']['Package']['PackageWeight'])) {
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
					$response_arr = self::convert_xml_to_array($data);
					if (!empty($response_arr['Response']['Error'])) {
						$debug_data = $response_arr['Response']['Error'];
					} elseif (!empty($response_arr['RatedShipment'])) {
						$response_rate_arr = isset($response_arr['RatedShipment']['Service']) ? array($response_arr['RatedShipment']) : $response_arr['RatedShipment'];
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
	 * Convert XML to Array.
	 * @param $data string XML data.
	 * @return array Data as Array.
	 */
	public static function convert_xml_to_array($data)
	{
		libxml_use_internal_errors(true);

		$data = simplexml_load_string($data);
		$data = json_encode($data);
		$data = json_decode($data, TRUE);
		return $data;
	}

	/**
	 * Get Shipping Cost after Conversion.
	 * @param int $rate_cost
	 * @return int $rate_cost
	 */
	public function ph_ups_get_shipping_cost_after_conversion($rate_cost)
	{
		$this->rate_conversion = apply_filters('xa_conversion_rate', $this->rate_conversion, (isset($xml->RatedShipment[0]->TotalCharges->CurrencyCode) ? (string)$xml->RatedShipment[0]->TotalCharges->CurrencyCode : null));

		$this->rate_conversion = apply_filters('ph_vendor_conversion_rate', $this->rate_conversion, $this->vendorId, 'ups');

		$rate_cost *=  ((!empty($this->rate_conversion) && $this->rate_conversion > 0) ? $this->rate_conversion : 1);

		return $rate_cost;
	}

	/**
	 * wf_get_api_rate_box_data function.
	 *
	 * @access public
	 * @return requests
	 */
	public function wf_get_api_rate_box_data( $package, $packing_method, $params = array())
	{
		$this->packing_method	= $packing_method;
		$requests 				= $this->get_package_requests($package, $params);
		return $requests;
	}

	/**
	 * wf_set_cod_details function.
	 *
	 * Sets Cash On Delivery (COD) details for an order.
	 *
	 * @param object $order The order object.
	 * @return void
	 */
	public function wf_set_cod_details($order)
	{
		if ($order->get_id()) {

			$this->cod 			= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order->get_id(), '_wf_ups_cod');
			$this->cod_total 	= $order->get_total();
		}
	}

	/**
	 * wf_set_service_code function.
	 *
	 * Sets the service code for UPS shipping.
	 *
	 * @param int $service_code The service code.
	 * @return void
	 */
	public function wf_set_service_code($service_code)
	{
		$this->service_code = $service_code;
	}

	/**
	 * wf_array_to_xml function.
	 *
	 * Converts an array to XML format.
	 *
	 * @param array $tags The array to convert.
	 * @param bool $full_xml Whether to include the XML declaration (optional).
	 * @return string The XML string.
	 */
	public function wf_array_to_xml($tags, $full_xml = false)
	{ //$full_xml true will contain <?xml version
		$xml_str	=	'';
		foreach ($tags as $tag_name	=> $tag) {
			$out	=	'';
			try {
				$xml = new SimpleXMLElement('<' . $tag_name . '/>');

				if (is_array($tag)) {
					$this->array2XML($xml, $tag);

					if (!$full_xml) {

						if (function_exists('dom_import_simplexml')) {
							$dom	=	dom_import_simplexml($xml);
							$out .= $dom->ownerDocument->saveXML($dom->ownerDocument->documentElement);
						} else {
							Ph_UPS_Woo_Shipping_Common::debug(__('The DOMElement class is not enabled for your site, so UPS Packages are not created. Please contact your Hosting Provider to enable DOMElement class for your site and try again', 'ups-woocommerce-shipping'), $this->debug, $this->silent_debug);
						}
					} else {
						$out .= $xml->saveXML();
					}
				} else {
					$out .= $tag;
				}
			} catch (Exception $e) {
				// Do nothing
			}
			$xml_str .= $out;
		}
		// echo preg_replace('<[\/]*item[0-9]>', '', $xml_str);
		return $xml_str;
	}

	/**
	 * array2XML function.
	 *
	 * Converts a nested array to XML format.
	 *
	 * @param object $obj The SimpleXMLElement object.
	 * @param array $array The array to convert.
	 * @return void
	 */
	public function array2XML($obj, $array)
	{
		foreach ($array as $key => $value) {
			if (is_numeric($key))
				$key = 'item' . $key;

			if (is_array($value)) {
				if (!array_key_exists('multi_node', $value)) {
					$node = $obj->addChild($key);
					$this->array2XML($node, $value);
				} else {
					unset($value['multi_node']);
					foreach ($value as $node_value) {
						$this->array2XML($obj, $node_value);
					}
				}
			} else {
				$obj->addChild($key, $value);
			}
		}
	}

	public function get_acccesspoint_rate_request( $ph_ups_selected_access_point_details )
	{
		//Getting accesspoint address details
		$access_request = '';
		$shipping_accesspoint = PH_WC_UPS_Common_Utils::wf_get_accesspoint_datas( $ph_ups_selected_access_point_details );
		if (!empty($shipping_accesspoint) && is_string($shipping_accesspoint)) {
			$decoded_accesspoint = json_decode($shipping_accesspoint);
			if (isset($decoded_accesspoint->AddressKeyFormat)) {

				$accesspoint_addressline	= $decoded_accesspoint->AddressKeyFormat->AddressLine;
				$accesspoint_city			= (property_exists($decoded_accesspoint->AddressKeyFormat, 'PoliticalDivision2')) ? $decoded_accesspoint->AddressKeyFormat->PoliticalDivision2 : '';
				$accesspoint_state			= (property_exists($decoded_accesspoint->AddressKeyFormat, 'PoliticalDivision1')) ? $decoded_accesspoint->AddressKeyFormat->PoliticalDivision1 : '';
				$accesspoint_postalcode		= $decoded_accesspoint->AddressKeyFormat->PostcodePrimaryLow;
				$accesspoint_country		= $decoded_accesspoint->AddressKeyFormat->CountryCode;

				$access_request .= "		<ShipmentIndicationType>" . "\n";
				$access_request .=	"			<Code>01</Code>" . "\n";
				$access_request .=	"		</ShipmentIndicationType>" . "\n";
				$access_request .= "		<AlternateDeliveryAddress>" . "\n";
				$access_request .= "			<Address>" . "\n";
				$access_request .= "				<AddressLine1>" . $accesspoint_addressline . "</AddressLine1>" . "\n";
				$access_request .= "				<City>" . $accesspoint_city . "</City>" . "\n";
				$access_request .= "				<StateProvinceCode>" . $accesspoint_state . "</StateProvinceCode>" . "\n";
				$access_request .= "				<PostalCode>" . $accesspoint_postalcode . "</PostalCode>" . "\n";
				$access_request .= "				<CountryCode>" . $accesspoint_country . "</CountryCode>" . "\n";
				$access_request .= "			</Address>" . "\n";
				$access_request .= "		</AlternateDeliveryAddress>" . "\n";
			}
		}

		return $access_request;
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
					$this->weight_unit = 'KGS';
					$package['Package']['PackageWeight']['UnitOfMeasurement']['Code']	= 'KGS';
					$package['Package']['PackageWeight']['Weight']	= round(wc_get_weight($package['Package']['PackageWeight']['Weight'], 'KGS', 'lbs'), 2);
				}
			} else {

				if (isset($package['Package']['Dimensions']) && !empty($package['Package']['Dimensions']) && $package['Package']['Dimensions']['UnitOfMeasurement']['Code'] != 'IN' && $this->units == 'metric') {
					$package['Package']['Dimensions']['UnitOfMeasurement']['Code'] = 'IN';
					$package['Package']['Dimensions']['Length'] = round(wc_get_dimension($package['Package']['Dimensions']['Length'], 'IN', 'cm'), 2);
					$package['Package']['Dimensions']['Width']	= round(wc_get_dimension($package['Package']['Dimensions']['Width'], 'IN', 'cm'), 2);
					$package['Package']['Dimensions']['Height']	= round(wc_get_dimension($package['Package']['Dimensions']['Height'], 'IN', 'cm'), 2);
				}

				if ($package['Package']['PackageWeight']['UnitOfMeasurement']['Code'] != 'LBS' && $this->units == 'metric') {
					$this->weight_unit = 'LBS';
					$package['Package']['PackageWeight']['UnitOfMeasurement']['Code']	= 'LBS';
					$package['Package']['PackageWeight']['Weight']	= round(wc_get_weight($package['Package']['PackageWeight']['Weight'], 'LBS', 'kg'), 2);
				}
			}

			// Unset metrics
			unset($package['Package']['metrics']);
		}
		return $package;
	}
	
	// Can be removed once XML is removed.
	/**
	 * get_package_requests
	 *
	 * @access private
	 * @return array
	 */
	private function get_package_requests($package, $params = array())
	{
		if (empty($package['contents']) && class_exists('wf_admin_notice')) {
			wf_admin_notice::add_notice(__("UPS - Something wrong with products associated with order, or no products associated with order.", "ups-woocommerce-shipping"), 'error');
			return false;
		}
		// Choose selected packing
		switch ($this->packing_method) {
			case 'box_packing':
				$requests = $this->box_shipping($package, $params);
				break;
			case 'weight_based':
				$requests = $this->weight_based_shipping($package, $params);
				break;
			case 'per_item':
			default:
				$requests = $this->per_item_shipping($package, $params);
				break;
		}

		if (empty($requests))	$requests = array();

		$request_before_resetting_min_weight = $requests;
		// check for Minimum weight required by UPS
		$requests = $this->ups_minimum_weight_required($requests);
		return apply_filters('ph_ups_generated_packages', $requests, $package, $request_before_resetting_min_weight);
	}

	// Can be removed once XML is removed.
	/**
	 * Minimum Weight Required by UPS.
	 * @param array $ups_packages UPS packages generated by packaging Algorithms
	 * @return array UPS packages
	 */
	public function ups_minimum_weight_required($ups_packages)
	{

		switch ($this->origin_country) {
			case 'IL':
				$min_weight = 0.5;
				break;
			default:
				$min_weight = 0.0001;
		}

		foreach ($ups_packages as &$ups_package) {
			if ((float) $ups_package['Package']['PackageWeight']['Weight'] < $min_weight) {
				if ($this->debug) {
					$this->debug(sprintf(__("Package Weight has been reset to Minimum Weight. [ Actual Weight - %lf Minimum Weight - %lf ]", 'ups-woocommerce-shipping'), $ups_package['Package']['PackageWeight']['Weight'], $min_weight));
				}

				// Add by Default
				$this->diagnostic_report(sprintf('Package Weight has been reset to Minimum Weight. [ Actual Weight - %lf Minimum Weight - %lf ]', $ups_package['Package']['PackageWeight']['Weight'], $min_weight));

				$ups_package['Package']['PackageWeight']['Weight'] = $min_weight;
			}
		}
		return $ups_packages;
	}

	// Can be removed once XML is removed.
	/**
	 * per_item_shipping function.
	 *
	 * @access private
	 * @param mixed $package
	 * @return mixed $requests - an array of XML strings
	 */
	private function per_item_shipping($package, $params = array())
	{
		global $woocommerce;

		$requests = array();
		$refrigeratorindicator = 'no';
		$ctr = 0;
		$this->destination = $package['destination'];

		foreach ($package['contents'] as $item_id => $values) {

			// To support WPGlobalCart Plugin
			do_action('ph_ups_package_contents_loop_start', $values, $this->settings);

			$values = apply_filters('ph_ups_package_contents', $values, $this->settings);

			$values['data'] = Ph_UPS_Woo_Shipping_Common::wf_load_product($values['data']);
			$ctr++;

			$additional_products = apply_filters('xa_ups_alter_products_list', array($values));	// To support product addon

			foreach ($additional_products as $values) {

				$skip_product = apply_filters('wf_shipping_skip_product', false, $values, $package['contents']);
				if ($skip_product) {
					continue;
				}

				if (!($values['quantity'] > 0 && $values['data']->needs_shipping())) {
					$this->debug(sprintf(__('Product #%d is virtual. Skipping.', 'ups-woocommerce-shipping'), $values['data']->id));

					// Add by Default
					$this->diagnostic_report(sprintf('Product #%d is virtual. Skipping from Rate Calculation', $values['data']->id));

					continue;
				}

				if (!$values['data']->get_weight()) {
					$this->debug(sprintf(__('Product #%d is missing weight. Aborting.', 'ups-woocommerce-shipping'), $values['data']->id), 'error');

					// Add by Default
					$this->diagnostic_report(sprintf('Product #%d is missing weight. Aborting Rate Calculation', $values['data']->id));

					return;
				}

				// get package weight
				$weight = wc_get_weight((!empty($values['data']->get_weight()) ? $values['data']->get_weight() : 0), $this->weight_unit);
				//$weight = apply_filters('wf_ups_filter_product_weight', $weight, $package, $item_id );

				// get package dimensions
				if ($values['data']->length && $values['data']->height && $values['data']->width) {

					$dimensions = array(
						number_format(wc_get_dimension((float) $values['data']->length, $this->dim_unit), 2, '.', ''),
						number_format(wc_get_dimension((float) $values['data']->height, $this->dim_unit), 2, '.', ''),
						number_format(wc_get_dimension((float) $values['data']->width, $this->dim_unit), 2, '.', '')
					);
					sort($dimensions);
				}
				if (isset($dimensions)) {
					foreach ($dimensions as $key => $dimension) {	//ensure the dimensions aren't zero
						if ($dimension <= 0) {
							$dimensions[$key] = 0.01;
						}
					}
				}

				// get quantity in cart
				$cart_item_qty = $values['quantity'];
				// get weight, or 1 if less than 1 lbs.
				// $_weight = ( floor( $weight ) < 1 ) ? 1 : $weight;

				$request['Package']	=	array(
					'PackagingType'	=>	array(
						'Code'			=>	'02',
						'Description'	=>	'Package/customer supplied'
					),
					'Description'	=>	'Rate',
				);

				if ($values['data']->length && $values['data']->height && $values['data']->width) {
					$request['Package']['Dimensions']	=	array(
						'UnitOfMeasurement'	=>	array(
							'Code'	=>	$this->dim_unit
						),
						'Length'	=>	(string) round($dimensions[2], 2),
						'Width'		=>	(string) round($dimensions[1], 2),
						'Height'	=>	(string) round($dimensions[0], 2)
					);
				}
				if ((isset($params['service_code']) && $params['service_code'] == 92) || ($this->service_code == 92)) // Surepost Less Than 1LBS
				{
					if ($this->weight_unit == 'LBS') { // make sure weight in pounds
						$weight_ozs = $weight * 16;
					} else {
						$weight_ozs = $weight * 35.274; // From KG
					}
					$request['Package']['PackageWeight']	=	array(
						'UnitOfMeasurement'	=>	array(
							'Code'	=>	'OZS'
						),
						'Weight'	=>	(string) round($weight_ozs, 2),
					);
				} else {

					// Invalid Weight Error if Weight is less than 0.05 for Estimated Delivery Option
					if ($weight < 0.05) {
						$weight = 0.05;
					}

					$request['Package']['PackageWeight']	=	array(
						'UnitOfMeasurement'	=>	array(
							'Code'	=>	$this->weight_unit
						),
						'Weight'	=>	(string) round($weight, 2),
					);
				}


				if ($this->insuredvalue || $this->cod || $this->cod_enable) {

					// InsuredValue
					if ($this->insuredvalue) {

						// REST doesn't support "InsuredValue" node, it's handled in REST file
						$request['Package']['PackageServiceOptions']['InsuredValue']	=	array(
							'CurrencyCode'	=>	$this->get_ups_currency(),
							'MonetaryValue'	=>	(string) round(( PH_WC_UPS_Common_Utils::ph_get_insurance_amount($values['data'], $this->fixedProductPrice) / $this->conversion_rate), 2)
						);
					}

					//Cod
					if (($this->cod && isset($_GET['wf_ups_shipment_confirm'])) || ($this->cod_enable && !isset($_GET['wf_ups_shipment_confirm']))) {

						if (! PH_WC_UPS_Common_Utils::is_shipment_level_cod_required($this->destination['country'])) {

							$cod_amount 	= !empty($values['data']->get_price()) ? $values['data']->get_price() : $this->fixedProductPrice;
							$codfundscode 	= in_array($this->destination['country'], array('AR', 'BR', 'CL')) ? 9 : 0;

							$request['Package']['PackageServiceOptions']['COD']	=	array(
								'CODCode'		=>	3,
								'CODFundsCode'	=>	$codfundscode,
								'CODAmount'		=>	array(
									'MonetaryValue'	=>	(string) round($cod_amount, 2),
									'CurrencyCode'	=>	$this->get_ups_currency(),
								),
							);
						}
					}
				}

				if ($this->isc) {

					$refrigeratorindicator 	= 'no';
					$clinicalid 			= '';
					$clinicalvar 			= get_post_meta($values['data']->id, '_ph_ups_clinicaltrials_var', 1);
					$refrigerator_var 		= get_post_meta($values['data']->id, '_ph_ups_refrigeration_var', 1);

					if (empty($refrigerator_var) || !isset($refrigerator_var)) {

						$refrigerator 	= get_post_meta($values['data']->id, '_ph_ups_refrigeration', 1);
					} else {

						$refrigerator 	= $refrigerator_var;
					}

					if (empty($clinicalvar) || !isset($clinicalvar)) {

						$clinical 	= get_post_meta($values['data']->id, '_ph_ups_clinicaltrials', 1);
					} else {

						$clinical 	= $clinicalvar;
					}

					$refrigeratorindicator  = ($refrigeratorindicator == 'yes') ? $refrigeratorindicator : $refrigerator;
					$clinicalid 			= (isset($clinicalid) && !empty($clinicalid)) ? $clinicalid : $clinical;

					$refrigeratorindicator = ($refrigeratorindicator == 'yes' ? 'yes' : (isset($this->settings['ph_ups_refrigeration']) && $this->settings['ph_ups_refrigeration'] == 'yes' ? 'yes' : 'no'));

					$clinicalid  = (!empty($clinicalid) ? $clinicalid  : (isset($this->settings['ph_ups_clinicaltrials']) && !empty($this->settings['ph_ups_clinicaltrials']) ? $this->settings['ph_ups_clinicaltrials'] : ''));

					if ($refrigeratorindicator == 'yes') {

						$request['Package']['PackageServiceOptions']['RefrigerationIndicator'] = '1';
					}

					if (isset($clinicalid) && !empty($clinicalid) && isset($_GET['wf_ups_shipment_confirm'])) {

						$request['Package']['PackageServiceOptions']['ClinicaltrialsID'] = $clinicalid;
					}
				}

				//Adding all the items to the stored packages
				$request['Package']['items'] = array($values['data']->obj);

				// Direct Delivery option
				$directdeliveryonlyindicator = PH_WC_UPS_Common_Utils::get_individual_product_meta(array($values['data']), '_wf_ups_direct_delivery');

				if (isset($_GET['dd'])) {

					PH_UPS_WC_Storage_Handler::ph_add_and_save_meta_data($this->order->get_id(), '_ph_ups_direct_delivery', $_GET['dd']);

					$directdeliveryonlyindicator = !empty($_GET['dd']) ? $_GET['dd'] : $directdeliveryonlyindicator;
				}

				if ($directdeliveryonlyindicator == 'yes') {
					$request['Package']['DirectDeliveryOnlyIndicator'] = $directdeliveryonlyindicator;
				}

				// Delivery Confirmation
				if (isset($params['delivery_confirmation_applicable']) && $params['delivery_confirmation_applicable'] == true) {

					$signature_option = PH_WC_UPS_Common_Utils::get_package_signature(array($values['data']));
					$signature_option = $signature_option < $this->ph_delivery_confirmation ? $this->ph_delivery_confirmation : $signature_option;

					if (isset($_GET['dc'])) {

						PH_UPS_WC_Storage_Handler::ph_add_and_save_meta_data($this->order->get_id(), '_ph_ups_delivery_signature', $_GET['dc']);

						$signature_option = $_GET['dc'] != 4 ? $_GET['dc'] : $signature_option;
					}

					$signature_option = $signature_option == 3 ? 3 : ($signature_option > 0 ? 2 : $signature_option);

					if (isset($request['Package']['PackageServiceOptions']) && isset($request['Package']['PackageServiceOptions']['COD'])) {

						$this->diagnostic_report('UPS : COD Shipment. Signature will not be applicable.');
					}

					if (!empty($signature_option) && ($signature_option > 0) && (!isset($request['Package']['PackageServiceOptions']) || (isset($request['Package']['PackageServiceOptions']) && !isset($request['Package']['PackageServiceOptions']['COD'])))) {

						$this->diagnostic_report('UPS : Require Signature - ' . $signature_option);
						$request['Package']['PackageServiceOptions']['DeliveryConfirmation']['DCISType'] = $signature_option;
					}
				}

				// Boolean to check if unit conversion is required. To support multi-vendor addon
				if (isset($package['metrics'])) {
					$request['Package']['metrics'] = $package['metrics'] ? true : false;
				}

				for ($i = 0; $i < $cart_item_qty; $i++)
					$requests[] = $request;
			}

			// To support WPGlobalCart Plugin
			do_action('ph_ups_package_contents_loop_end', $values, $this->settings);
		}

		return $requests;
	}

	// Can be removed once XML is removed.
	/**
	 * box_shipping function.
	 *
	 * @access private
	 * @param mixed $package
	 * @return void
	 */
	private function box_shipping($package, $params = array())
	{

		global $woocommerce;
		$pre_packed_contents = array();
		$requests = array();

		if (!class_exists('PH_UPS_Boxpack')) {
			include_once 'package-handler/box-pack/class-wf-packing.php';
		}
		if (!class_exists('PH_UPS_Boxpack_Stack')) {
			include_once 'package-handler/box-pack/class-wf-packing-stack.php';
		}

		volume_based:
		if (isset($this->mode) && $this->mode == 'stack_first') {
			$boxpack = new PH_UPS_Boxpack_Stack();
		} else {
			$boxpack = new PH_UPS_Boxpack($this->mode, $this->exclude_box_weight);
		}

		if ( ((isset($package['destination']['country']) && $this->origin_country == $package['destination']['country']) || $this->origin_country != 'US') && (isset($this->boxes['E_10KG_BOX']) || isset($this->boxes['D_25KG_BOX']) ) ) {

			unset($this->boxes['E_10KG_BOX']);
			unset($this->boxes['D_25KG_BOX']);
		}

		// Define boxes
		if (!empty($this->boxes)) {

			foreach ($this->boxes as $key => $box) {

				// Skip if box is not enabled in settings.
				if (!$box['box_enabled'] || (!$this->upsSimpleRate && $box['box_enabled'] && array_key_exists($key, PH_WC_UPS_Constants::SIMPLE_RATE_BOX_CODES))) {
					continue;
				}

				$boxId = '';

				// Get the actual Box ID
				if (array_key_exists($key, $this->packaging)) {

					$boxId = $this->packaging[$key]['code'];
				}

				if (array_key_exists($key, PH_WC_UPS_Constants::SIMPLE_RATE_BOX_CODES)) {
					$boxId = $key;
				}

				$newbox = $boxpack->add_box($box['outer_length'], $box['outer_width'], $box['outer_height'], $box['box_weight']);
				$newbox->set_inner_dimensions($box['inner_length'], $box['inner_width'], $box['inner_height']);

				if ($box['max_weight']) {
					$newbox->set_max_weight($box['max_weight']);
				}

				$newbox->set_id($boxId);

				if (isset($box['boxes_name']) && !empty($box['boxes_name'])) {

					$newbox->set_box_name($box['boxes_name']);
				} else {

					$newbox->set_box_name('Custom Box');
				}


				if (isset($this->mode) && $this->mode == 'stack_first') {

					$newbox = $boxpack->add_box($box['outer_height'], $box['outer_width'], $box['outer_length'], $box['box_weight']);
					$newbox->set_inner_dimensions($box['inner_height'], $box['inner_width'], $box['inner_length']);

					if ($box['max_weight']) {
						$newbox->set_max_weight($box['max_weight']);
					}

					$newbox->set_id($boxId);

					if (isset($box['boxes_name']) && !empty($box['boxes_name'])) {

						$newbox->set_box_name($box['boxes_name']);
					} else {

						$newbox->set_box_name('Custom Box');
					}
				}
			}
		}

		// Add items
		$ctr 					= 0;
		$pre_packed_contents 	= [];
		$this->destination 		= $package['destination'];

		if (isset($package['contents'])) {
			foreach ($package['contents'] as $item_id => $values) {

				// To support WPGlobalCart Plugin
				do_action('ph_ups_package_contents_loop_start', $values, $this->settings);

				$values = apply_filters('ph_ups_package_contents', $values, $this->settings);

				$values['data'] = Ph_UPS_Woo_Shipping_Common::wf_load_product($values['data']);

				$ctr++;

				$additional_products = apply_filters('xa_ups_alter_products_list', array($values));	// To support product addon

				foreach ($additional_products as $values) {
					$skip_product = apply_filters('wf_shipping_skip_product', false, $values, $package['contents']);
					if ($skip_product) {
						continue;
					}

					if (!($values['quantity'] > 0 && $values['data']->needs_shipping())) {
						$this->debug(sprintf(__('Product #%d is virtual. Skipping.', 'ups-woocommerce-shipping'), $values['data']->id));

						// Add by Default
						$this->diagnostic_report(sprintf('Product #%d is virtual. Skipping from Rate Calculation', $values['data']->id));

						continue;
					}

					$pre_packed = get_post_meta($values['data']->id, '_wf_pre_packed_product_var', 1);

					if (empty($pre_packed) || $pre_packed == 'no') {
						$parent_product_id = wp_get_post_parent_id($values['data']->id);
						$pre_packed = get_post_meta(!empty($parent_product_id) ? $parent_product_id : $values['data']->id, '_wf_pre_packed_product', 1);
					}

					$pre_packed = apply_filters('wf_ups_is_pre_packed', $pre_packed, $values);

					if (!empty($pre_packed) && $pre_packed == 'yes') {
						$pre_packed_contents[] = $values;
						$this->debug(sprintf(__('Pre Packed product. Skipping the product # %d', 'ups-woocommerce-shipping'), $values['data']->id));

						// Add by Default
						$this->diagnostic_report(sprintf('Pre Packed product. Skipping the product %d from Box Packing Algorithm', $values['data']->id));

						continue;
					}

					if ($values['data']->length && $values['data']->height && $values['data']->width && $values['data']->weight) {

						$dimensions = array($values['data']->length, $values['data']->width, $values['data']->height);

						for ($i = 0; $i < $values['quantity']; $i++) {

							$boxpack->add_item(
								number_format(wc_get_dimension((float) $dimensions[0], $this->dim_unit), 6, '.', ''),
								number_format(wc_get_dimension((float) $dimensions[1], $this->dim_unit), 6, '.', ''),
								number_format(wc_get_dimension((float) $dimensions[2], $this->dim_unit), 6, '.', ''),
								number_format(wc_get_weight((!empty($values['data']->get_weight()) ? $values['data']->get_weight() : 0), $this->weight_unit), 6, '.', ''),
								PH_WC_UPS_Common_Utils::ph_get_insurance_amount($values['data'], $this->fixedProductPrice),
								$values['data'] // Adding Item as meta
							);
						}
					} else {
						$this->debug(sprintf(__('UPS Parcel Packing Method is set to Pack into Boxes. Product #%d is missing dimensions. Aborting.', 'ups-woocommerce-shipping'), $ctr), 'error');

						// Add by Default
						$this->diagnostic_report(sprintf('UPS Parcel Packing Method is set to Pack into Boxes. Product #%d is missing dimensions. Aborting Rate Calulation.', $values['data']->id));

						return;
					}
				}

				// To support WPGlobalCart Plugin
				do_action('ph_ups_package_contents_loop_end', $values, $this->settings);
			}
		} else {
			wf_admin_notice::add_notice('No package found. Your product may be missing weight/length/width/height');

			// Add by Default
			$this->diagnostic_report('No package found. Your product may be missing weight/length/width/height');
		}
		// Pack it
		$boxpack->pack();

		// Get packages
		$box_packages 	= $boxpack->get_packages();
		$stop_fallback 	= apply_filters('xa_ups_stop_fallback_from_stack_first_to_vol_based', false);

		if (isset($this->mode) && $this->mode == 'stack_first' && !$stop_fallback && $this->stack_to_volume) {

			foreach ($box_packages as $key => $box_package) {

				$box_volume 					= $box_package->length * $box_package->width * $box_package->height;
				$box_used_volume 				= isset($box_package->volume) && !empty($box_package->volume) ? $box_package->volume : 1;
				$box_used_volume_percentage 	= ($box_used_volume * 100) / $box_volume;

				if (isset($box_used_volume_percentage) && $box_used_volume_percentage < 44) {

					$this->mode = 'volume_based';

					$this->debug('(FALLBACK) : Stack First Option changed to Volume Based');

					// Add by Default
					$this->diagnostic_report('(FALLBACK) : Stack First Method changed to Volume Based. Reason: Selected Box Volume % used is less than 44%');

					goto volume_based;
					break;
				}
			}
		}

		$ctr = 0;

		$standard_boxes_without_dimensions = array('01', '24', '25');

		foreach ($box_packages as $key => $box_package) {
			$ctr++;

			// if( $this->debug ) {

			// 	$this->debug( "Box Packing Result: PACKAGE " . $ctr . " (" . $key . ")\n<pre>" . print_r( $box_package,true ) . "</pre>", 'error' );
			// }

			$weight	 = $box_package->weight;
			$dimensions = array($box_package->length, $box_package->width, $box_package->height);


			$boxCode = $box_package->id;

			// UPS packaging type select, If not present set as custom box
			if (!isset($box_package->id) || empty($box_package->id) || !array_key_exists($box_package->id, PH_WC_UPS_Constants::PACKAGING_SELECT)) {
				$box_package->id = '02';
			}

			sort($dimensions);
			// get weight, or 1 if less than 1 lbs.
			// $_weight = ( floor( $weight ) < 1 ) ? 1 : $weight;

			$box_name = isset($box_package->box_name) && !empty($box_package->box_name) ? $box_package->box_name : '';

			$request['Package']	=	array(
				'PackagingType'	=>	array(
					'Code'				=>	$box_package->id,
					'Description'	=>	'Package/customer supplied'
				),
				'Description'	=> 'Rate',
				'BoxCode' 		=> $boxCode,
				'box_name'		=> $box_name,
			);

			// Dimensions Mismatch error will come for some Default Boxes
			if (!in_array($box_package->id, $standard_boxes_without_dimensions)) {

				$request['Package']['Dimensions'] = array(

					'UnitOfMeasurement'	=>	array(
						'Code'	=>	$this->dim_unit,
					),
					'Length'	=>	(string) round($dimensions[2], 2),
					'Width'		=>	(string) round($dimensions[1], 2),
					'Height'	=>	(string) round($dimensions[0], 2)
				);
			}

			// Getting packed items
			$packed_items	=	array();
			if (!empty($box_package->packed) && is_array($box_package->packed)) {

				foreach ($box_package->packed as $item) {
					$item_product	=	$item->meta;
					$packed_items[] = $item_product;
				}
			}

			if ($this->isc || $this->cod_enable || $this->cod) {

				$refrigeratorindicator  = 'no';
				$clinicalid 			= '';
				$cod_amount = 0;

				foreach ($packed_items as $key => $value) {

					if ($this->isc) {

						$clinicalvar            = get_post_meta($value->id, '_ph_ups_clinicaltrials_var', 1);
						$refrigerator_var       = get_post_meta($value->id, '_ph_ups_refrigeration_var', 1);

						if (empty($refrigerator_var) || !isset($refrigerator_var)) {

							$refrigerator 	= get_post_meta($value->id, '_ph_ups_refrigeration', 1);
						} else {

							$refrigerator 	= $refrigerator_var;
						}

						if (empty($clinicalvar) || !isset($clinicalvar)) {

							$clinical 	= get_post_meta($value->id, '_ph_ups_clinicaltrials', 1);
						} else {

							$clinical 	= $clinicalvar;
						}

						$refrigeratorindicator  = ($refrigeratorindicator == 'yes') ? $refrigeratorindicator : $refrigerator;
						$clinicalid 			= (isset($clinicalid) && !empty($clinicalid)) ? $clinicalid : $clinical;
					}

					if ($this->cod_enable || $this->cod) {

						$cod_amount = $cod_amount + (!empty($value->get_price()) ? $value->get_price() : $this->fixedProductPrice);
					}
				}

				if ($this->isc) {

					$refrigeratorindicator = ($refrigeratorindicator == 'yes' ? 'yes' : (isset($this->settings['ph_ups_refrigeration']) && $this->settings['ph_ups_refrigeration'] == 'yes' ? 'yes' : 'no'));

					$clinicalid  = (!empty($clinicalid) ? $clinicalid  : (isset($this->settings['ph_ups_clinicaltrials']) && !empty($this->settings['ph_ups_clinicaltrials']) ? $this->settings['ph_ups_clinicaltrials'] : ''));

					if ($refrigeratorindicator == 'yes') {

						$request['Package']['PackageServiceOptions']['RefrigerationIndicator'] = '1';
					}

					if (isset($clinicalid) && !empty($clinicalid) && isset($_GET['wf_ups_shipment_confirm'])) {

						$request['Package']['PackageServiceOptions']['ClinicaltrialsID'] = $clinicalid;
					}
				}
			}

			if ((isset($params['service_code']) && $params['service_code'] == 92) || ($this->service_code == 92)) // Surepost Less Than 1LBS
			{
				if ($this->weight_unit == 'LBS') { // make sure weight in pounds
					$weight_ozs = $weight * 16;
				} else {
					$weight_ozs = $weight * 35.274; // From KG
				}

				$request['Package']['PackageWeight']	=	array(
					'UnitOfMeasurement'	=>	array(
						'Code'	=>	'OZS'
					),
					'Weight'	=>	(string) round($weight_ozs, 2)
				);
			} else {

				// Invalid Weight Error if Weight is less than 0.05 for Estimated Delivery Option
				if ($weight < 0.05) {
					$weight = 0.05;
				}

				$request['Package']['PackageWeight']	=	array(
					'UnitOfMeasurement'	=>	array(
						'Code'	=>	$this->weight_unit
					),
					'Weight'	=>	(string) round($weight, 2)
				);
			}

			if ($this->insuredvalue || $this->cod || $this->cod_enable) {

				// InsuredValue
				if ($this->insuredvalue) {
					// REST doesn't support "InsuredValue" node, it's handled in REST file
					$request['Package']['PackageServiceOptions']['InsuredValue']	=	array(
						'CurrencyCode'	=>	$this->get_ups_currency(),
						'MonetaryValue'	=>	(string)round(($box_package->value / $this->conversion_rate), 2)
					);
				}

				//COD
				if (($this->cod && isset($_GET['wf_ups_shipment_confirm'])) || ($this->cod_enable && !isset($_GET['wf_ups_shipment_confirm']))) {

					if (!PH_WC_UPS_Common_Utils::is_shipment_level_cod_required($this->destination['country'])) {

						$codfundscode = in_array($this->destination['country'], array('AR', 'BR', 'CL')) ? 9 : 0;

						$request['Package']['PackageServiceOptions']['COD']	=	array(
							'CODCode'		=>	3,
							'CODFundsCode'	=>	$codfundscode,
							'CODAmount'		=>	array(
								'MonetaryValue'	=>	(string) round($cod_amount, 2),
								'CurrencyCode'	=>	$this->get_ups_currency(),
							),
						);
					}
				}
			}

			//Adding all the items to the stored packages
			if (isset($box_package->unpacked) && $box_package->unpacked && isset($box_package->obj)) {
				$request['Package']['items'] = array($box_package->obj);
			} else {
				$request['Package']['items'] = $packed_items;
			}
			// Direct Delivery option
			$directdeliveryonlyindicator = !empty($packed_items) ? PH_WC_UPS_Common_Utils::get_individual_product_meta($packed_items, '_wf_ups_direct_delivery') : PH_WC_UPS_Common_Utils::get_individual_product_meta(array($box_package), '_wf_ups_direct_delivery'); // else part is for unpacked item

			if (isset($_GET['dd'])) {

				PH_UPS_WC_Storage_Handler::ph_add_and_save_meta_data($this->order->get_id(), '_ph_ups_direct_delivery', $_GET['dd']);

				$directdeliveryonlyindicator = !empty($_GET['dd']) ? $_GET['dd'] : $directdeliveryonlyindicator;
			}

			if ($directdeliveryonlyindicator == 'yes') {
				$request['Package']['DirectDeliveryOnlyIndicator'] = $directdeliveryonlyindicator;
			}

			// Delivery Confirmation
			if (isset($params['delivery_confirmation_applicable']) && $params['delivery_confirmation_applicable'] == true) {

				$signature_option = PH_WC_UPS_Common_Utils::get_package_signature($request['Package']['items']);	//Works on both packed and unpacked items
				$signature_option = $signature_option < $this->ph_delivery_confirmation ? $this->ph_delivery_confirmation : $signature_option;

				if (isset($_GET['dc'])) {

					PH_UPS_WC_Storage_Handler::ph_add_and_save_meta_data($this->order->get_id(), '_ph_ups_delivery_signature', $_GET['dc']);

					$signature_option = $_GET['dc'] != 4 ? $_GET['dc'] : $signature_option;
				}

				$signature_option = $signature_option == 3 ? 3 : ($signature_option > 0 ? 2 : $signature_option);

				if (isset($request['Package']['PackageServiceOptions']) && isset($request['Package']['PackageServiceOptions']['COD'])) {

					$this->diagnostic_report('UPS : COD Shipment. Signature will not be applicable.');
				}

				if (!empty($signature_option) && ($signature_option > 0) && (!isset($request['Package']['PackageServiceOptions']) || (isset($request['Package']['PackageServiceOptions']) && !isset($request['Package']['PackageServiceOptions']['COD'])))) {

					$this->diagnostic_report('UPS : Require Signature - ' . $signature_option);

					$request['Package']['PackageServiceOptions']['DeliveryConfirmation']['DCISType'] = $signature_option;
				}
			}

			// Boolean to check if unit conversion is required. To support multi-vendor addon
			if (isset($package['metrics'])) {
				$request['Package']['metrics'] = $package['metrics'] ? true : false;
			}

			$requests[] = $request;
		}
		//add pre packed item with the package
		if (!empty($pre_packed_contents)) {

			// Boolean to check if unit conversion is required. To support multi-vendor addon
			if (isset($package['metrics'])) {
				$params['metrics'] = $package['metrics'] ? true : false;
			}

			$prepacked_requests = $this->wf_ups_add_pre_packed_product($pre_packed_contents, $params);
			if (is_array($prepacked_requests)) {
				$requests = array_merge($requests, $prepacked_requests);
			}
		}

		return $requests;
	}

	// Can be removed once XML is removed.
	/**
	 * weight_based_shipping function.
	 *
	 * @access private
	 * @param mixed $package
	 * @return void
	 */
	private function weight_based_shipping($package, $params = array())
	{

		// Tempprary variable to support metrics - multivendor addon, because $package will be overwritten
		$actualPackage = $package;

		global $woocommerce;
		$pre_packed_contents = array();
		if (!class_exists('WeightPack')) {
			include_once 'package-handler/weight-pack/class-wf-weight-packing.php';
		}
		$weight_pack = new WeightPack($this->weight_packing_process);
		$weight_pack->set_max_weight($this->box_max_weight);

		$package_total_weight = 0;
		$insured_value = 0;

		$requests = array();
		$ctr = 0;
		$this->destination = $package['destination'];

		foreach ($package['contents'] as $item_id => $values) {

			// To support WPGlobalCart Plugin
			do_action('ph_ups_package_contents_loop_start', $values, $this->settings);

			$values = apply_filters('ph_ups_package_contents', $values, $this->settings);

			$values['data'] = Ph_UPS_Woo_Shipping_Common::wf_load_product($values['data']);
			$ctr++;

			$additional_products = apply_filters('xa_ups_alter_products_list', array($values));	// To support product addon
			foreach ($additional_products as $values) {
				$skip_product = apply_filters('wf_shipping_skip_product', false, $values, $package['contents']);
				if ($skip_product) {
					continue;
				}

				if (!($values['quantity'] > 0 && $values['data']->needs_shipping())) {
					$this->debug(sprintf(__('Product # %d is virtual. Skipping.', 'ups-woocommerce-shipping'), $values['data']->id));

					// Add by Default
					$this->diagnostic_report(sprintf('Product # %d is virtual. Skipping from Rate Calculation.', $values['data']->id));

					continue;
				}

				if (!$values['data']->get_weight()) {
					$this->debug(sprintf(__('Product # %d is missing weight. Aborting.', 'ups-woocommerce-shipping'), $values['data']->id), 'error');

					// Add by Default
					$this->diagnostic_report(sprintf('Product # %d is missing weight. Aborting Rate Calculation.', $values['data']->id));

					return;
				}

				$pre_packed = get_post_meta($values['data']->id, '_wf_pre_packed_product_var', 1);

				if (empty($pre_packed) || $pre_packed == 'no') {
					$parent_product_id = wp_get_post_parent_id($values['data']->id);
					$pre_packed = get_post_meta(!empty($parent_product_id) ? $parent_product_id : $values['data']->id, '_wf_pre_packed_product', 1);
				}

				$pre_packed = apply_filters('wf_ups_is_pre_packed', $pre_packed, $values);

				if (!empty($pre_packed) && $pre_packed == 'yes') {
					$pre_packed_contents[] = $values;
					$this->debug(sprintf(__('Pre Packed product. Skipping the product # %d', 'ups-woocommerce-shipping'), $values['data']->id));

					// Add by Default
					$this->diagnostic_report(sprintf('Pre Packed product. Skipping the product %d from Weight Packing Algorithm', $values['data']->id));

					continue;
				}

				$product_weight = $this->xa_get_volumatric_products_weight($values['data']);
				$weight_pack->add_item(wc_get_weight($product_weight, $this->weight_unit), $values['data'], $values['quantity']);
			}

			// To support WPGlobalCart Plugin
			do_action('ph_ups_package_contents_loop_end', $values, $this->settings);
		}

		$pack	=	$weight_pack->pack_items();
		$errors	=	$pack->get_errors();
		if (!empty($errors)) {
			//do nothing
			return;
		} else {
			$boxes		=	$pack->get_packed_boxes();
			$unpacked_items	=	$pack->get_unpacked_items();

			$insured_value			=	0;

			if (isset($this->order)) {
				$order_total	=	$this->order->get_total();
			}


			$packages		=	array_merge($boxes,	$unpacked_items); // merge items if unpacked are allowed
			$package_count	=	sizeof($packages);

			// get all items to pass if item info in box is not distinguished
			$packable_items	=	$weight_pack->get_packable_items();
			$all_items		=	array();
			if (is_array($packable_items)) {
				foreach ($packable_items as $packable_item) {
					$all_items[]	=	$packable_item['data'];
				}
			}

			foreach ($packages as $package) {

				$packed_products 		= array();
				$insured_value  		= 0;
				$refrigeratorindicator	= 'no';
				$clinicalid 			= '';
				$cod_amount 			= 0;

				if (!empty($package['items'])) {

					foreach ($package['items'] as $item) {

						if ($this->insuredvalue) {

							$insured_value 	= $insured_value + PH_WC_UPS_Common_Utils::ph_get_insurance_amount($item, $this->fixedProductPrice);
						}

						if ($this->isc) {

							$clinicalvar            = get_post_meta($item->id, '_ph_ups_clinicaltrials_var', 1);
							$refrigerator_var       = get_post_meta($item->id, '_ph_ups_refrigeration_var', 1);

							if (empty($refrigerator_var) || !isset($refrigerator_var)) {

								$refrigerator 	= get_post_meta($item->id, '_ph_ups_refrigeration', 1);
							} else {

								$refrigerator 	= $refrigerator_var;
							}

							if (empty($clinicalvar) || !isset($clinicalvar)) {

								$clinical 	= get_post_meta($item->id, '_ph_ups_clinicaltrials', 1);
							} else {

								$clinical 	= $clinicalvar;
							}

							$refrigeratorindicator  = ($refrigeratorindicator == 'yes') ? $refrigeratorindicator : $refrigerator;
							$clinicalid 			= (isset($clinicalid) && !empty($clinicalid)) ? $clinicalid : $clinical;
						}

						if ($this->cod_enable || $this->cod) {

							$cod_amount = $cod_amount + (!empty($item->get_price()) ? $item->get_price() : $this->fixedProductPrice);
						}
					}
				} elseif (isset($order_total) && $package_count) {

					$insured_value	=	$order_total / $package_count;

					if ($this->cod_enable || $this->cod) {

						$cod_amount = $order_total / $package_count;
					}
				}

				$packed_products	=	isset($package['items']) ? $package['items'] : $all_items;
				// Creating package request
				$package_total_weight	=	$package['weight'];

				$request['Package']	=	array(
					'PackagingType'	=>	array(
						'Code'			=>	'02',
						'Description'	=>	'Package/customer supplied',
					),
					'Description'	=>	'Rate',
				);

				if ((isset($params['service_code']) && $params['service_code'] == 92) || ($this->service_code == 92)) { // Surepost Less Than 1LBS
					if ($this->weight_unit == 'LBS') { // make sure weight in pounds
						$weight_ozs = $package_total_weight * 16;
					} else {
						$weight_ozs = $package_total_weight * 35.274; // From KG
					}

					$request['Package']['PackageWeight']	=	array(
						'UnitOfMeasurement'	=>	array(
							'Code'	=>	'OZS'
						),
						'Weight'	=>	(string) round($weight_ozs, 2)
					);
				} else {

					// Invalid Weight Error if Weight less is than 0.05 for Estimated Delivery Option
					if ($package_total_weight < 0.05) {
						$package_total_weight = 0.05;
					}

					$request['Package']['PackageWeight']	=	array(
						'UnitOfMeasurement'	=>	array(
							'Code'	=>	$this->weight_unit
						),
						'Weight'	=>	(string) round($package_total_weight, 2)
					);
				}

				// InsuredValue
				if ($this->insuredvalue) {
					// REST doesn't support "InsuredValue" node, it's handled in REST file
					$request['Package']['PackageServiceOptions']['InsuredValue']	=	array(
						'CurrencyCode'	=>	$this->get_ups_currency(),
						'MonetaryValue'	=>	(string) round(($insured_value / $this->conversion_rate), 2),
					);
				}

				if ($this->isc) {

					$refrigeratorindicator = ($refrigeratorindicator == 'yes' ? 'yes' : (isset($this->settings['ph_ups_refrigeration']) && $this->settings['ph_ups_refrigeration'] == 'yes' ? 'yes' : 'no'));

					$clinicalid  = (!empty($clinicalid) ? $clinicalid  : (isset($this->settings['ph_ups_clinicaltrials']) && !empty($this->settings['ph_ups_clinicaltrials']) ? $this->settings['ph_ups_clinicaltrials'] : ''));


					if ($refrigeratorindicator == 'yes') {

						$request['Package']['PackageServiceOptions']['RefrigerationIndicator'] = '1';
					}

					if (isset($clinicalid) && !empty($clinicalid) && isset($_GET['wf_ups_shipment_confirm'])) {

						$request['Package']['PackageServiceOptions']['ClinicaltrialsID'] = $clinicalid;
					}
				}

				if (($this->cod && isset($_GET['wf_ups_shipment_confirm'])) || ($this->cod_enable && !isset($_GET['wf_ups_shipment_confirm']))) {

					if (! PH_WC_UPS_Common_Utils::is_shipment_level_cod_required($this->destination['country'])) {

						$codfundscode = in_array($this->destination['country'], array('AR', 'BR', 'CL')) ? 9 : 0;

						$request['Package']['PackageServiceOptions']['COD']	=	array(

							'CODCode'		=>	3,
							'CODFundsCode'	=>	$codfundscode,
							'CODAmount'		=>	array(
								'MonetaryValue'	=>	(string) round($cod_amount, 2),
								'CurrencyCode'	=>	$this->get_ups_currency(),
							),
						);
					}
				}

				// Direct Delivery option
				$directdeliveryonlyindicator = PH_WC_UPS_Common_Utils::get_individual_product_meta($packed_products, '_wf_ups_direct_delivery');

				if (isset($_GET['dd'])) {

					PH_UPS_WC_Storage_Handler::ph_add_and_save_meta_data($this->order->get_id(), '_ph_ups_direct_delivery', $_GET['dd']);

					$directdeliveryonlyindicator = !empty($_GET['dd']) ? $_GET['dd'] : $directdeliveryonlyindicator;
				}

				if ($directdeliveryonlyindicator == 'yes') {
					$request['Package']['DirectDeliveryOnlyIndicator'] = $directdeliveryonlyindicator;
				}

				// Delivery Confirmation
				if (isset($params['delivery_confirmation_applicable']) && $params['delivery_confirmation_applicable'] == true) {

					$signature_option = PH_WC_UPS_Common_Utils::get_package_signature($packed_products);
					$signature_option = $signature_option < $this->ph_delivery_confirmation ? $this->ph_delivery_confirmation : $signature_option;

					if (isset($_GET['dc'])) {

						PH_UPS_WC_Storage_Handler::ph_add_and_save_meta_data($this->order->get_id(), '_ph_ups_delivery_signature', $_GET['dc']);

						$signature_option = $_GET['dc'] != 4 ? $_GET['dc'] : $signature_option;
					}

					$signature_option = $signature_option == 3 ? 3 : ($signature_option > 0 ? 2 : $signature_option);

					if (isset($request['Package']['PackageServiceOptions']) && isset($request['Package']['PackageServiceOptions']['COD'])) {
						$this->diagnostic_report('UPS : COD Shipment. Signature will not be applicable.');
					}

					if (!empty($signature_option) && ($signature_option > 0) && (!isset($request['Package']['PackageServiceOptions']) || (isset($request['Package']['PackageServiceOptions']) && !isset($request['Package']['PackageServiceOptions']['COD'])))) {

						$this->diagnostic_report('UPS : Require Signature - ' . $signature_option);

						$request['Package']['PackageServiceOptions']['DeliveryConfirmation']['DCISType'] = $signature_option;
					}
				}

				$request['Package']['items'] = $package['items'];	    //Required for numofpieces in case of worldwidefreight

				// Boolean to check if unit conversion is required. To support multi-vendor addon
				if (isset($actualPackage['metrics'])) {
					$request['Package']['metrics'] = $actualPackage['metrics'] ? true : false;
				}

				$requests[] = $request;
			}
		}
		//add pre packed item with the package
		if (!empty($pre_packed_contents)) {

			// Boolean to check if unit conversion is required. To support multi-vendor addon
			if (isset($actualPackage['metrics'])) {
				$params['metrics'] = $actualPackage['metrics'] ? true : false;
			}

			$prepacked_requests = $this->wf_ups_add_pre_packed_product($pre_packed_contents, $params);
			if (is_array($prepacked_requests)) {
				$requests = array_merge($requests, $prepacked_requests);
			}
		}
		return $requests;
	}

	// Can be removed once XML is removed.
	/**
	 * Get Volumetric weight .
	 * @param object wf_product | wc_product object .
	 * @return float Volumetric weight if it is higher than product weight else actual product weight.
	 */
	private function xa_get_volumatric_products_weight($values)
	{

		if (!empty($this->settings['volumetric_weight']) && $this->settings['volumetric_weight'] == 'yes') {

			$length = wc_get_dimension((float) $values->get_length(), 'cm');
			$width 	= wc_get_dimension((float) $values->get_width(), 'cm');
			$height = wc_get_dimension((float) $values->get_height(), 'cm');
			if ($length != 0 && $width != 0 && $height != 0) {
				$volumetric_weight = $length * $width * $height /  5000; // Divide by 5000 as per fedex standard
			}
		}

		$weight = !empty($values->get_weight()) ? $values->get_weight() : 0;

		if (!empty($volumetric_weight)) {
			$volumetric_weight = wc_get_weight($volumetric_weight, $this->wc_weight_unit, 'kg');
			if ($volumetric_weight > $weight) {
				$weight = $volumetric_weight;
			}
		}
		return $weight;
	}

	// Can be removed once XML is removed.
	/*
	 * function to create package for pre packed items
	 *
	 * @ since 3.3.1
	 * @ access private
	 * @ params pre_packed_items
	 * @ return requests
	 */
	private function wf_ups_add_pre_packed_product($pre_packed_items, $params = array())
	{
		$requests = array();
		foreach ($pre_packed_items as $item_id => $values) {
			if (!($values['quantity'] > 0 && $values['data']->needs_shipping())) {
				$this->debug(sprintf(__('Product #%d is virtual. Skipping.', 'ups-woocommerce-shipping'), $values['data']->id));

				// Add by Default
				$this->diagnostic_report(sprintf('Product %d is virtual. Skipping from Rate Calculation', $values['data']->id));

				continue;
			}

			if (!$values['data']->get_weight()) {
				$this->debug(sprintf(__('Product #%d is missing weight. Aborting.', 'ups-woocommerce-shipping'), $values['data']->id), 'error');

				// Add by Default
				$this->diagnostic_report(sprintf('Product %d is  missing weight. Aborting Rate Calculation', $values['data']->id));

				return;
			}
			$weight = wc_get_weight((!empty($values['data']->get_weight()) ? $values['data']->get_weight() : 0), $this->weight_unit);

			if ($values['data']->length && $values['data']->height && $values['data']->width && $values['data']->weight) {
				$dimensions = array(
					number_format(wc_get_dimension((float) $values['data']->length, $this->dim_unit), 2, '.', ''),
					number_format(wc_get_dimension((float) $values['data']->height, $this->dim_unit), 2, '.', ''),
					number_format(wc_get_dimension((float) $values['data']->width, $this->dim_unit), 2, '.', '')
				);
				sort($dimensions);
			} else {
				$this->debug(sprintf(__('Product is missing dimensions. Aborting.', 'ups-woocommerce-shipping')), 'error');

				// Add by Default
				$this->diagnostic_report(sprintf('Product %d is  missing dimensions. Aborting Rate Calculation', $values['data']->id));

				return;
			}

			$cart_item_qty = $values['quantity'];

			$request['Package']	=	array(
				'PackagingType'	=>	array(
					'Code'			=>	'02',
					'Description'	=>	'Package/customer supplied'
				),
				'Description'	=>	'Rate',
			);

			if ($this->packing_method == 'box_packing') {

				$request['Package']['box_name'] = "Pre-packed Product";
			}

			// Direct Delivery option
			$directdeliveryonlyindicator = PH_WC_UPS_Common_Utils::get_individual_product_meta(array($values['data']), '_wf_ups_direct_delivery');

			if (isset($_GET['dd'])) {

				PH_UPS_WC_Storage_Handler::ph_add_and_save_meta_data($this->order->get_id(), '_ph_ups_direct_delivery', $_GET['dd']);

				$directdeliveryonlyindicator = !empty($_GET['dd']) ? $_GET['dd'] : $directdeliveryonlyindicator;
			}

			if ($directdeliveryonlyindicator == 'yes') {
				$request['Package']['DirectDeliveryOnlyIndicator'] = $directdeliveryonlyindicator;
			}

			if ($values['data']->length && $values['data']->height && $values['data']->width) {
				$request['Package']['Dimensions']	=	array(
					'UnitOfMeasurement'	=>	array(
						'Code'	=>	$this->dim_unit
					),
					'Length'	=>	(string) round($dimensions[2], 2),
					'Width'		=>	(string) round($dimensions[1], 2),
					'Height'	=>	(string) round($dimensions[0], 2)
				);
			}
			if ((isset($params['service_code']) && $params['service_code'] == 92) || ($this->service_code == 92)) // Surepost Less Than 1LBS
			{
				if ($this->weight_unit == 'LBS') { // make sure weight in pounds
					$weight_ozs = $weight * 16;
				} else {
					$weight_ozs = $weight * 35.274; // From KG
				}
				$request['Package']['PackageWeight']	=	array(
					'UnitOfMeasurement'	=>	array(
						'Code'	=>	'OZS'
					),
					'Weight'	=>	(string) round($weight_ozs, 2)
				);
			} else {

				// Invalid Weight Error if Weight is less than 0.05 for Estimated Delivery Option
				if ($weight < 0.05) {
					$weight = 0.05;
				}

				$request['Package']['PackageWeight']	=	array(
					'UnitOfMeasurement'	=>	array(
						'Code'	=>	$this->weight_unit
					),
					'Weight'	=>	(string) round($weight, 2)
				);
			}


			if ($this->insuredvalue || $this->cod || $this->cod_enable) {

				// InsuredValue
				if ($this->insuredvalue) {
					// REST doesn't support "InsuredValue" node, it's handled in REST file
					$request['Package']['PackageServiceOptions']['InsuredValue']	=	array(
						'CurrencyCode'	=>	$this->get_ups_currency(),
						'MonetaryValue'	=>	(string) round((PH_WC_UPS_Common_Utils::ph_get_insurance_amount($values['data'], $this->fixedProductPrice) * $this->conversion_rate), 2)
					);
				}

				//COD
				if (($this->cod && isset($_GET['wf_ups_shipment_confirm'])) || ($this->cod_enable && !isset($_GET['wf_ups_shipment_confirm']))) {

					if (! PH_WC_UPS_Common_Utils::is_shipment_level_cod_required($this->destination['country'])) {

						$cod_amount 	= !empty($values['data']->get_price()) ? $values['data']->get_price() : $this->fixedProductPrice;
						$codfundscode 	= in_array($this->destination['country'], array('AR', 'BR', 'CL')) ? 9 : 0;

						$request['Package']['PackageServiceOptions']['COD']	= array(
							'CODCode'		=>	3,
							'CODFundsCode'	=>	$codfundscode,
							'CODAmount'		=>	array(
								'MonetaryValue'	=>	(string) round($cod_amount, 2),
								'CurrencyCode'	=>	$this->get_ups_currency(),
							),
						);
					}
				}
			}

			// Delivery Confirmation
			if (isset($params['delivery_confirmation_applicable']) && $params['delivery_confirmation_applicable'] == true) {

				$signature_option = PH_WC_UPS_Common_Utils::get_package_signature(array($values['data']));
				$signature_option = $signature_option < $this->ph_delivery_confirmation ? $this->ph_delivery_confirmation : $signature_option;

				if (isset($_GET['dc'])) {

					PH_UPS_WC_Storage_Handler::ph_add_and_save_meta_data($this->order->get_id(), '_ph_ups_delivery_signature', $_GET['dc']);

					$signature_option = $_GET['dc'] != 4 ? $_GET['dc'] : $signature_option;
				}

				$signature_option = $signature_option == 3 ? 3 : ($signature_option > 0 ? 2 : $signature_option);

				if (isset($request['Package']['PackageServiceOptions']) && isset($request['Package']['PackageServiceOptions']['COD'])) {
					$this->diagnostic_report('UPS : COD Shipment. Signature will not be applicable.');
				}

				if (!empty($signature_option) && ($signature_option > 0) && (!isset($request['Package']['PackageServiceOptions']) || (isset($request['Package']['PackageServiceOptions']) && !isset($request['Package']['PackageServiceOptions']['COD'])))) {

					$this->diagnostic_report('UPS : Require Signature - ' . $signature_option);

					$request['Package']['PackageServiceOptions']['DeliveryConfirmation']['DCISType'] = $signature_option;
				}
			}

			if ($this->isc) {

				$refrigeratorindicator 	= 'no';
				$clinicalid 			= '';
				$clinicalvar 			= get_post_meta($values['data']->id, '_ph_ups_clinicaltrials_var', 1);
				$refrigerator_var 		= get_post_meta($values['data']->id, '_ph_ups_refrigeration_var', 1);

				if (empty($refrigerator_var) || !isset($refrigerator_var)) {

					$refrigerator 	= get_post_meta($values['data']->id, '_ph_ups_refrigeration', 1);
				} else {

					$refrigerator 	= $refrigerator_var;
				}

				if (empty($clinicalvar) || !isset($clinicalvar)) {

					$clinical 	= get_post_meta($values['data']->id, '_ph_ups_clinicaltrials', 1);
				} else {

					$clinical 	= $clinicalvar;
				}

				$refrigeratorindicator 	= ($refrigeratorindicator == 'yes') ? $refrigeratorindicator : $refrigerator;
				$clinicalid 			= (isset($clinicalid) && !empty($clinicalid)) ? $clinicalid : $clinical;

				$refrigeratorindicator = ($refrigeratorindicator == 'yes' ? 'yes' : (isset($this->settings['ph_ups_refrigeration']) && $this->settings['ph_ups_refrigeration'] == 'yes' ? 'yes' : 'no'));

				$clinicalid  = (!empty($clinicalid) ? $clinicalid  : (isset($this->settings['ph_ups_clinicaltrials']) && !empty($this->settings['ph_ups_clinicaltrials']) ? $this->settings['ph_ups_clinicaltrials'] : ''));

				if ($refrigeratorindicator == 'yes') {

					$request['Package']['PackageServiceOptions']['RefrigerationIndicator'] = '1';
				}

				if (isset($clinicalid) && !empty($clinicalid) && isset($_GET['wf_ups_shipment_confirm'])) {

					$request['Package']['PackageServiceOptions']['ClinicaltrialsID'] = $clinicalid;
				}
			}

			//Setting the product object in package request	
			$request['Package']['items'] = array($values['data']->obj);

			// Boolean to check if unit conversion is required. To support multi-vendor addon
			if (isset($params['metrics'])) {
				$request['Package']['metrics'] = $params['metrics'];
			}

			for ($i = 0; $i < $cart_item_qty; $i++)
				$requests[] = $request;
		}
		return $requests;
	}

	// Can be removed once XML is removed.
	public function get_ups_currency()
	{
		return $this->currency_type;
	}

	// Can be removed once XML is removed.
	/**
	 * Output a message or error
	 * @param  string $message
	 * @param  string $type
	 */
	public function debug($message, $type = 'notice')
	{
		// Hard coding to 'notice' as recently noticed 'error' is breaking with wc_add_notice.
		$type = 'notice';
		if ($this->debug && !is_admin() && !$this->silent_debug) { //WF: do not call wc_add_notice from admin.
			if (version_compare(WOOCOMMERCE_VERSION, '2.1', '>=')) {
				wc_add_notice($message, $type);
			} else {
				global $woocommerce;
				$woocommerce->add_message($message);
			}
		}
	}

	// Can be removed once XML is removed.
	public function diagnostic_report($data)
	{

		if (function_exists("wc_get_logger") && $this->debug) {

			$log = wc_get_logger();
			$log->debug(($data) . PHP_EOL . PHP_EOL, array('source' => PH_UPS_DEBUG_LOG_FILE_NAME));
		}
	}

}