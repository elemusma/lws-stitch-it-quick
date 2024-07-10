<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (class_exists('WC_Countries')) {
	global $woocommerce;
	$wc_countries = new WC_Countries();
}

if (WF_UPS_ADV_DEBUG_MODE == "on") { // Test mode is only for development purpose.
	$api_mode_options = array(
		'Test'		   => __('Test', 'ups-woocommerce-shipping'),
	);
} else {
	$api_mode_options = array(
		'Live'		   => __('Live', 'ups-woocommerce-shipping'),
		'Test'		   => __('Test', 'ups-woocommerce-shipping'),
	);
}

// Services to list for default domestic and international services based on registration.
if ( ! Ph_UPS_Woo_Shipping_Common::ph_is_oauth_registered_customer()) {
	$shipping_services = $this->services + PH_WC_UPS_Constants::FREIGHT_SERVICES;
} else {
	$shipping_services = $this->services;
}

$pickup_start_time_options	=	array();
foreach (range(0, 23, 0.5) as $pickup_start_time) {
	$pickup_start_time_options[(string)$pickup_start_time]	=	date("H:i", strtotime(date('Y-m-d')) + 3600 * $pickup_start_time);
}

$pickup_close_time_options	=	array();
foreach (range(0.5, 23.5, 0.5) as $pickup_close_time) {
	$pickup_close_time_options[(string)$pickup_close_time]	=	date("H:i", strtotime(date('Y-m-d')) + 3600 * $pickup_close_time);
}

$ship_from_address_options		=	apply_filters(
	'wf_filter_label_ship_from_address_options',
	array(
		'origin_address'   => __('Origin Address', 'ups-woocommerce-shipping'),
		'billing_address'  => __('Shipping Address', 'ups-woocommerce-shipping'),
	)
);

$shipping_class_option_arr = array();
$shipping_class_arr = get_terms(array('taxonomy' => 'product_shipping_class', 'hide_empty' => false));
foreach ($shipping_class_arr as $shipping_class_detail) {
	if (is_object($shipping_class_detail)) {
		$shipping_class_option_arr[$shipping_class_detail->slug] = $shipping_class_detail->name;
	}
}

$ups_settings 	= get_option('woocommerce_' . WF_UPS_ID . '_settings', null);

// Will Check Old Settings 'include_order_id' and Based on that it will set default for 'order_id_or_number_in_label' 
$default_order_id_or_number	= (isset($ups_settings['include_order_id']) && !empty($ups_settings['include_order_id']) && $ups_settings['include_order_id'] == 'yes') ? 'include_order_number' : '';
$default_invoice_commodity_value	= ( isset($ups_settings['discounted_price']) && !empty($ups_settings['discounted_price']) && $ups_settings['discounted_price'] == 'yes' ) ? 'discount_price' : 'product_price';

// General Settings.
$general_settings =  array(

	//UPS Settings Tabs.
	'tabs_wrapper'	=> array(
		'type'			=> 'ph_ups_settings_tabs',
	),
	
	// General Tab.
	'general-title'			=> array(
		'title' 		=> __('UPS Account & Address Settings', 'ups-woocommerce-shipping'),
		'type'			=> 'title',
		'description'	=> __('Obtain UPS account credentials by registering on UPS website.', 'ups-woocommerce-shipping'),
		'class'			=> 'ph_ups_general_tab',
	),
	'api_mode' 		=> array(
		'title' 		=> __('API Mode', 'ups-woocommerce-shipping'),
		'type' 			=> 'select',
		'default' 		=> 'Test',
		'class' 		=> 'wc-enhanced-select ph_ups_general_tab',
		'options' 		=> $api_mode_options,
		'description' 	=> __('Set as Test to switch to UPS api test servers. Transaction will be treated as sample transactions by UPS.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true
	),
	'debug' 		=> array(
		'title' 		=> __('Debug Mode', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'description'	=> __('Enable debug mode to show debugging information.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_general_tab',
	),
	'silent_debug' 		=> array(
		'title' 		=> __('Silent Debug Mode', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'yes',
		'description'	=> __('Enable silent debug mode to create debug information without showing debugging information on the cart/checkout.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_general_tab ph_ups_silent_debug',
	),
	'units'			=> array(
		'title'			=> __('Weight/Dimension Units', 'ups-woocommerce-shipping'),
		'type'			=> 'select',
		'description'	=> __('Switch this to metric units, if you see "This measurement system is not valid for the selected country" errors.', 'ups-woocommerce-shipping'),
		'default'		=> 'imperial',
		'class'			=> 'wc-enhanced-select ph_ups_general_tab',
		'options'		=> array(
			'imperial'		=> __('LB / IN', 'ups-woocommerce-shipping'),
			'metric'		=> __('KG / CM', 'ups-woocommerce-shipping'),
		),
		'desc_tip'		=> true,
	),
	'negotiated'	=> array(
		'title'			=> __('Negotiated Rates', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'description'	=> __('Enable this if this shipping account has negotiated rates available.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_general_tab',
	),
	'residential'	=> array(
		'title'			=> __('Residential', 'ups-woocommerce-shipping'),
		'label'			=> __('Ship to address is Residential.', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'description'	=> __('This will indicate to UPS that the receiver is always a residential address.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_general_tab',
	),
	'address_validation' => array(
		'title'			=> __('Address Classification', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'description'	=> __('Helps in classifying address as Commercial or Residential. Applicable for United States and Puert Rico. Debug details are available in WC_Logger.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'class'			=> 'ph_ups_general_tab'
	),
	'suggested_address' => array(
		'title'			=> __('Enable Address Suggestion From UPS', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'description'	=> __('Provides Address Suggestions Based On Addresses In UPS Database', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'class'			=> 'ph_ups_general_tab'
	),
	'suggested_display'   => array(
		'title'			=> __('Address Suggestion on Checkout Page', 'ups-woocommerce-shipping'),
		'type'			=> 'select',
		'default'		=> 'suggested_notice',
		'class'			=> 'wc-enhanced-select ph_ups_general_tab',
		'options'		=> array(
			'suggested_notice'   => __('Display as Notice', 'ups-woocommerce-shipping'),
			'suggested_radio'    => __('Display as Options', 'ups-woocommerce-shipping'),
		),
		'description'	=> __('Select how the address suggestion is displayed on the WooCommerce checkout page.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true
	),
	'insuredvalue'	=> array(
		'title'			=> __('Insurance Option', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'description'	=> __('Request Insurance to be included.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_general_tab',
	),
	'min_order_amount_for_insurance' 	=> array(
		'title'			=> __('Min Order Amount', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'description'	=> __('Insurance will apply only if Order subtotal amount is greater or equal to the Min Order Amount. Note - For Comparison it will take only the sum of product price i.e Order Subtotal amount. In Cart It will take Cart Subtotal Amount.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_general_tab',
	),
	'ship_from_address'   => array(
		'title'			=> __('Ship From Address Preference', 'ups-woocommerce-shipping'),
		'type'			=> 'select',
		'default'		=> 'origin_address',
		'class'			=> 'wc-enhanced-select ph_ups_general_tab',
		'options'		=> $ship_from_address_options,
		'description'	=> __('Change the preference of Ship From Address printed on the label. You can make  use of Billing Address from Order admin page, if you ship from a different location other than shipment origin address given below.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true
	),
	'ups_user_name'	=> array(
		'title'			=> __('Company Name', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'description'	=> __('Enter your company name', 'ups-woocommerce-shipping'),
		'default'		=> '',
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_general_tab',
	),
	'ups_display_name'	=> array(
		'title'			=> __('Attention Name', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'description'	=> __('Your business/attention name.', 'ups-woocommerce-shipping'),
		'default'		=> '',
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_general_tab',
	),
	'origin_addressline'  => array(
		'title'		   => __('Origin Address Line 1', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'description'	 => __('Shipping Origin Address Line 1 (Ship From Address).', 'ups-woocommerce-shipping'),
		'default'		 => '',
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_general_tab',
	),
	'origin_addressline_2'  => array(
		'title'			=> __('Origin Address Line 2', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'description'	=> __('Shipping Origin Address Line 2 (Ship From Address).', 'ups-woocommerce-shipping'),
		'default'		=> '',
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_general_tab',
	),
	'origin_city'	  	  => array(
		'title'		   => __('Origin City', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'description'	 => __('Origin City (Ship From City)', 'ups-woocommerce-shipping'),
		'default'		 => '',
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_general_tab',
	),
	'origin_country_state'	=> array(
		'type'				=> 'single_select_country',
	),
	'origin_custom_state'		=> array(
		'title'		   => __('Origin State Code', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'description'	 => __('Specify shipper state province code if state not listed with Origin Country.', 'ups-woocommerce-shipping'),
		'default'		 => '',
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_general_tab',
	),
	'origin_postcode'	 => array(
		'title'		   => __('Origin Postcode', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'description'	 => __('Ship From Zip/postcode.', 'ups-woocommerce-shipping'),
		'default'		 => '',
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_general_tab',
	),
	'phone_number'		=> array(
		'title'		   => __('Your Phone Number', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'description'	 => __('Your contact phone number.', 'ups-woocommerce-shipping'),
		'default'		 => '',
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_general_tab',
	),
	'email'		=> array(
		'title'		   => __('Your email', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'description'	 => __('Your email.', 'ups-woocommerce-shipping'),
		'default'		 => '',
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_general_tab',
	),
	'ship_from_address_different_from_shipper'	=>	array(
		'title'			=>	__('Ship From Address Different from Shipper Address', 'ups-woocommerce-shipping'),
		'label'			=>	__('Enable', 'ups-woocommerce-shipping'),
		'description'	=>	__('Shipper Address - Address to be printed on the label.<br> Ship From Address - Address from where the UPS will pickup the package (like Warehouse Address).<br>By Default Shipper address and Ship From Address are same. By enabling it, Ship From Address can be defined seperately.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'type'			=>	'checkbox',
		'default'		=>	'no',
		'class'			=> 'ph_ups_general_tab',
	),
	'ship_from_address_for_freight'	=>	array(
		'title'			=>	__('Enable for Freight Services', 'ups-woocommerce-shipping'),
		'label'			=>	__('Enable', 'ups-woocommerce-shipping'),
		'description'	=>	__(' If this option is enabled, the ‘Ship From Address’ will be considered as the ‘Origin Address’ for freight shipments.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'type'			=>	'checkbox',
		'default'		=>	'no',
		'class'			=> 'ph_ups_different_ship_from_address ph_ups_general_tab',
	),
	'ship_from_addressline'  => array(
		'title'		   => __('Ship From Address Line 1', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'description'	 => __('Ship From Address Line 1', 'ups-woocommerce-shipping'),
		'default'		 => '',
		'desc_tip'		=> true,
		'class'			=>	'ph_ups_different_ship_from_address ph_ups_general_tab'
	),
	'ship_from_addressline_2'  => array(
		'title'			=> __('Ship From Address Line 2', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'description'	=> __('Ship From Address Line 2', 'ups-woocommerce-shipping'),
		'default'		=> '',
		'desc_tip'		=> true,
		'class'			=>	'ph_ups_different_ship_from_address ph_ups_general_tab'
	),
	'ship_from_city'	  	  => array(
		'title'		   => __('Ship From City', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'description'	 => __('Ship From City', 'ups-woocommerce-shipping'),
		'default'		 => '',
		'desc_tip'		=> true,
		'class'			=>	'ph_ups_different_ship_from_address ph_ups_general_tab'
	),
	'ship_from_country_state'	=> array(
		'type'				=> 'ship_from_country_state',
	),
	'ship_from_custom_state'		=> array(
		'title'		   => __('Ship From State Code', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'description'	 => __('Specify shipper state province code if state not listed with Ship From Country.', 'ups-woocommerce-shipping'),
		'default'		 => '',
		'desc_tip'		=> true,
		'class'			=>	'ph_ups_different_ship_from_address ph_ups_general_tab'
	),
	'ship_from_postcode'	 => array(
		'title'		   => __('Ship From Postcode', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'description'	 => __('Ship From Zip/postcode.', 'ups-woocommerce-shipping'),
		'default'		 => '',
		'desc_tip'		=> true,
		'class'			=>	'ph_ups_different_ship_from_address ph_ups_general_tab'
	),
	'billing_address_as_shipper'	=>	array(
		'title'			=> __('Billing Address as Shipper Address on Label', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable (Not Applicable for Freight Shipment)', 'ups-woocommerce-shipping'),
		'description'	=> __('Billing Address will be printed on the label.<br> UPS will pickup the package from the address set under Ship From Address Preference or Ship From Address Different from Shipper Address', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'class'			=> 'ph_ups_general_tab',
	),
	'skip_products'	=> array(
		'title'			=>	__('Skip Products', 'ups-woocommerce-shipping'),
		'type'			=>	'multiselect',
		'options'		=>	$shipping_class_option_arr,
		'description'	=>	__('Skip all the products belonging to the selected Shipping Classes while fetching rates and creating Shipping Label.', 'ups-woocommerce-shipping'),
		'desc_tip'		=>	true,
		'class'			=>	'chosen_select ph_ups_general_tab',
	),
	'xa_show_all' => array(
		'title'		   => __('Show All Services in Order Page', 'ups-woocommerce-shipping'),
		'label'		   => __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		 => 'no',
		'description'	 => __('Check this option to show all services in create label drop down(UPS).', 'ups-woocommerce-shipping'),
		'desc_tip'		   => true,
		'class'			=> 'ph_ups_general_tab'
	),
	'remove_recipients_phno' => array(
		'title'		   => __('Remove Recipients Phone Number', 'ups-woocommerce-shipping'),
		'label'		   => __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		 => 'no',
		'description'	 => __('Adding Customer Phone Number is mandatory in shipping labels only for International Shipments or certain Domestic Services. Enabling this option will make sure customer phone number is not added to any other services', 'ups-woocommerce-shipping'),
		'desc_tip'		   => true,
		'class'		=> 'ph_ups_general_tab'
	),
	'shipper_release_indicator' => array(
		'title'			=> __('Display Shipper Release Indicator', 'ups-woocommerce-shipping'),
		'label' 		=> __('Enable', 'ups-woocommerce-shipping'),
		'type' 			=> 'checkbox',
		'default' 		=> 'no',
		'description' 	=> __('Enabling this option indicates that the package may be released by driver without a signature from the consignee. Only available for US/PR to US/PR packages without return service. This will be added only for Packages that do not require Signature & is not a COD shipment.', 'ups-woocommerce-shipping'),
		'desc_tip' 		=> true,
		'class'		=> 'ph_ups_general_tab'
	),
);

// Rate & Services.
$rates_and_services_settings = array(

	'rate-title'			=> array(
		'title' 		=> __('Shipping Rate Settings', 'ups-woocommerce-shipping'),
		'type'			=> 'title',
		'description'	=> __('Configure the shipping rate related settings. You can enable the desired UPS shipping services and other rate options.', 'ups-woocommerce-shipping'),
		'class'			=> 'ph_ups_rates_tab',
	),
	'enabled' 		=> array(
		'title' 		=> __('Realtime Rates', 'ups-woocommerce-shipping'),
		'type' 			=> 'checkbox',
		'label' 		=> __('Enable', 'ups-woocommerce-shipping'),
		'default'		=> 'no',
		'description'	=> __('Enable realtime rates on Cart/Checkout page.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_rates_tab',
	),
	'title' 		=> array(
		'title'			=> __('UPS Method Title', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'description'	=> __('This controls the title which the user sees during checkout.', 'ups-woocommerce-shipping'),
		'default'		=> __('UPS', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_rates_tab',
	),
	'availability' 	=> array(
		'title' 		=> __('Method Availability', 'ups-woocommerce-shipping'),
		'type' 			=> 'select',
		'default' 		=> 'all',
		'class' 		=> 'availability wc-enhanced-select ph_ups_rates_tab',
		'options' 		=> array(
			'all' 			=> __('All Countries', 'ups-woocommerce-shipping'),
			'specific' 		=> __('Specific Countries', 'ups-woocommerce-shipping'),
		),
	),
	'countries'		=> array(
		'title'			=> __('Specific Countries', 'ups-woocommerce-shipping'),
		'type' 			=> 'multiselect',
		'class' 		=> 'chosen_select ph_ups_rates_tab',
		'css' 			=> 'width: 450px;',
		'default'		=> '',
		'options'		=> $wc_countries->get_allowed_countries(),
	),
	'enable_estimated_delivery'		=> array(
		'title'			=> __('Show Estimated Delivery', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'description'	=> __('Enable it to display Estimated delivery.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_rates_tab',
	),
	'estimated_delivery_text'	=>	array(
		'title'			=>	__('Estimated Delivery Text', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'default'		=> 'Est delivery :',
		'placeholder'	=> 'Est delivery :',
		'desc_tip'		=> __('Given text will be used to show estimated delivery.', 'ups-woocommerce-shipping'),
		'class'			=> 'ph_ups_rates_tab ph_ups_est_delivery',
	),
	'cut_off_time'	=>	array(
		'title'			=>	__('Cut-Off Time', 'ups-woocommerce-shipping'),
		'type'			=>	'text',
		'default'		=>	'24:00',
		'placeholder'	=>	'24:00',
		'desc_tip'		=> __('Estimated delivery will be adjusted to the next day if any order is placed after cut off time. Use 24 hour format (Hour:Minute). Example - 23:00.', 'ups-woocommerce-shipping'),
		'class'			=> 'ph_ups_rates_tab ph_ups_est_delivery'
	),
	'ship_time_adjustment'	  => array(
		'title'		   => __('Shipping Time Adjustment', 'ups-woocommerce-shipping'),
		'label'		   => __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'number',
		'default'		 => 0,
		'desc_tip'	=> true,
		'custom_attributes' => array('min' => 0, 'step' => '1'),
		'class'			=> 'ph_ups_rates_tab ph_ups_est_delivery',
		'description'	 => __('Adjust number of days to get the estimated delivery accordingly (Numeric Only).', 'ups-woocommerce-shipping')
	),
	'ups_rate_caching' => array(
		'title' 		=> __('Shipping Rates Cache Limit', 'ups-woocommerce-shipping'),
		'desc_tip' 		=> true,
		'type' 			=> 'select',
		'options'		=>	array(
			'1'		=> __("1 Hour", 'ups-woocommerce-shipping'),
			'2'		=> __("2 Hours", 'ups-woocommerce-shipping'),
			'4'		=> __("4 Hours", 'ups-woocommerce-shipping'),
			'6'		=> __("6 Hours", 'ups-woocommerce-shipping'),
			'9'		=> __("9 Hours", 'ups-woocommerce-shipping'),
			'12'	=> __("12 Hours", 'ups-woocommerce-shipping'),
			'15'	=> __("15 Hours", 'ups-woocommerce-shipping'),
			'18'	=> __("18 Hours", 'ups-woocommerce-shipping'),
			'21'	=> __("21 Hours", 'ups-woocommerce-shipping'),
			'24'	=> __("24 Hours", 'ups-woocommerce-shipping'),
		),
		'default'		=> '24',
		'class' 		=> 'wc-enhanced-select ph_ups_rates_tab',
		'description' 	=> __('Select the Time Period you want to store the Rates.', 'ups-woocommerce-shipping'),
	),
	'pickup'  => array(
		'title'		   => __('Rates Based On Pickup Type', 'ups-woocommerce-shipping'),
		'type'			=> 'select',
		'css'			  => 'width: 250px;',
		'class'			  => 'chosen_select wc-enhanced-select ph_ups_rates_tab',
		'default'		 => '01',
		'options'		 => PH_WC_UPS_Constants::PICKUP_CODE,
	),
	'customer_classification'  => array(
		'title'		   => __('Customer Classification', 'ups-woocommerce-shipping'),
		'type'			=> 'select',
		'css'			  => 'width: 250px;',
		'class'			  => 'chosen_select wc-enhanced-select ph_ups_rates_tab',
		'default'		 => 'NA',
		'options'		 => PH_WC_UPS_Constants::CUSTOMER_CLASSIFICATION_CODE,
		'description'	 => __('Valid if origin country is US.'),
		'desc_tip'		=> true
	),

	'email_notification'  => array(
		'title'			=> __('Send email notification to', 'ups-woocommerce-shipping'),
		'type'			=> 'multiselect',
		'class'			=> 'multiselect chosen_select ph_ups_rates_tab',
		'default'		=> '',
		'options'		=> array(
			'sender'		=> __('Sender', 'ups-woocommerce-shipping'),
			'recipient'		=> __('Recipient', 'ups-woocommerce-shipping')
		),
		'description'	=> __('Choose whom to send the notification. Leave blank to not send notification.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
	),
	'email_notification_code' => array(
		'title'			=> __('Tracking Notifications', 'ups-woocommerce-shipping'),
		'type'			=> 'multiselect',
		'default'		=> 6,
		'options'		=> array(
			'2'			=> __('Return or Label Creation', 'ups-woocommerce-shipping'),
			'5'			=> __('In Transit', 'ups-woocommerce-shipping'),
			'6'			=> __('Ship', 'ups-woocommerce-shipping'),
			'7'			=> __('Exception', 'ups-woocommerce-shipping'),
			'8'			=> __('Delivery', 'ups-woocommerce-shipping')
		),
		'class'			=> 'chosen_select ph_ups_rates_tab multiselect'
	),
	'tax_indicator'	  => array(
		'title'		   => __('Tax On Rates', 'ups-woocommerce-shipping'),
		'description'	 => __('Taxes may be applicable to shipment', 'ups-woocommerce-shipping'),
		'desc_tip'		   => true,
		'type'			=> 'checkbox',
		'default'		 => 'no',
		'class'			=> 'ph_ups_rates_tab'
	),
	'ups_tradability'	  => array(
		'title'		   	=> __('Display Additional Taxes & Charges on cart page', 'ups-woocommerce-shipping'),
		'description' 	=> __('Additional Taxes & Charges will be displayed along with the shipping cost at the cart & checkout page. These charges wont be added to the cart total.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'class'			=> 'ph_ups_rates_tab'
	),
	'tradability_cart_title'	  => array(
		'title'		   	=> __('Custom Text for Additional Taxes & Charges', 'ups-woocommerce-shipping'),
		'description' 	=> __('This text will be displayed at the cart & checkout page. (max - 35 characters)', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'type'			=> 'text',
		'default'		=> __('Additional Taxes & Charges', 'ups-woocommerce-shipping'),
		'class'			=> 'ph_ups_rates_tab'
	),
	'accesspoint_locator' => array(
		'title'		   => __('UPS Access Point® Locator', 'ups-woocommerce-shipping'),
		'label'		   => __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		 => 'no',
		'class'			=> 'ph_ups_rates_tab'
	),
	'accesspoint_req_option' => array(
		'title'			=> __('Location Type', 'ups-woocommerce-shipping'),
		'type'			=> 'select',
		'default'		=> 1,
		'options'		=> array(
			'1'			=> __('Display UPS Stores', 'ups-woocommerce-shipping'),
			'64'		=> __('Display All UPS Access Point® Locations', 'ups-woocommerce-shipping')
		),
		'class'			=> 'wc-enhanced-select ph_ups_rates_tab ph_ups_accesspoint'
	),
	'accesspoint_max_limit'  => array(
		'title'			=> __('Max Location Options', 'ups-woocommerce-shipping'),
		'type'			=> 'number',
		'default'		=> '10',
		'description'	=> __('<small><i>Select the maximum number of UPS Access Point® Locations that will be displayed.</i></small>', 'ups-woocommerce-shipping'),
		'class'			=> 'ph_ups_rates_tab ph_ups_accesspoint ph_ups_accesspoint_limit',
	),
	'accesspoint_option_code' => array(
		'title'			=> __('UPS Access Point® Location Options', 'ups-woocommerce-shipping'),
		'type'			=> 'multiselect',
		'default'		=> '018',
		'desc_tip'		=> true,
		'description'	=> __('Selecte the Access Point Location Option', 'ups-woocommerce-shipping'),
		'class'			=> 'ph_ups_rates_tab ph_ups_accesspoint chosen_select',
		'css'			=> 'width: 400px;',
		'options'		=> PH_WC_UPS_Constants::ACCESSPOINT_LOCATION_OPTION,
	),
	// 'restricted_payments'	=> array(
	// 	'title'			=> __( 'Restricted Payments for Access Point® Location', 'ups-woocommerce-shipping' ),
	// 	'type' 			=> 'multiselect',
	// 	'class' 		=> 'chosen_select ph_ups_rates_tab',
	// 	'css' 			=> 'width: 400px;',
	// 	'default'		=> '',
	// 	'options'		=> $available_gateways,
	// ),
	'tin_number'  => array(
		'title'		   => __('TIN', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'placeholder'	  => 'Tax Identification Number',
		'description'	 => __('Tax Identification Number', 'ups-woocommerce-shipping'),
		'desc_tip'		   => true,
		'class'			=> 'ph_ups_rates_tab',
	),
	'recipients_tin'  => array(
		'title'			=> __('Recipient TIN', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		 => 'no',
		'description'	=> __("Recipient's Tax Identification Number", 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_rates_tab'
	),
	'offer_rates'	=> array(
		'title'			=> __('Offer Rates', 'ups-woocommerce-shipping'),
		'type'			=> 'select',
		'class'			=> 'wc-enhanced-select ph_ups_rates_tab',
		'description'	=> '<strong>' . __('Default Shipping Rates - ', 'ups-woocommerce-shipping') . '</strong>' . __('It will return shipping rates for all the valid shipping services.', 'ups-woocommerce-shipping') . '<br/><strong>' . __('Cheapest Rate - ', 'ups-woocommerce-shipping') . '</strong>' . __('It will display only the cheapest shipping rate with service name as Shipping Method Title (if given) or the default Shipping Service Name will be shown.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'default'		=> 'all',
		'options'		=> array(
			'all'			=> __('All Shipping Rates (Default)', 'ups-woocommerce-shipping'),
			'cheapest'		=> __('Cheapest Rate', 'ups-woocommerce-shipping'),
		),
	),
	'services_packaging'  => array(
		'title'		   => __('Services', 'ups-woocommerce-shipping'),
		'type'			=> 'title',
		'class'			=> 'ph_ups_rates_tab',
		'description'	 => '',
	),
	'services'			=> array(
		'type'			=> 'services'
	),
	'fallback'		=> array(
		'title'			=> __('Fallback', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'description'	=> __('If UPS returns no matching rates, offer this amount for shipping so that the user can still checkout. Leave blank to disable.', 'ups-woocommerce-shipping'),
		'default'		=> '',
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_rates_tab',
	),
	'currency_type'	=> array(
		'title'	   	=> __('Currency', 'ups-woocommerce-shipping'),
		'label'	  	=> __('Currency', 'ups-woocommerce-shipping'),
		'type'			=> 'select',
		'class'			=> 'wc-enhanced-select ph_ups_rates_tab',
		'options'	 	=> get_woocommerce_currencies(),
		'default'	 	=> get_woocommerce_currency(),
		'description' 	=> __('This currency will be used to communicate with UPS.', 'ups-woocommerce-shipping'),
	),
	'conversion_rate'	 => array(
		'title' 		  => __('Conversion Rate.', 'ups-woocommerce-shipping'),
		'type' 			  => 'text',
		'default'		 => 1,
		'description' 	  => __('Enter the conversion amount in case you have a different currency set up comparing to the currency of origin location. This amount will be multiplied with the shipping rates. Leave it empty if no conversion required.', 'ups-woocommerce-shipping'),
		'desc_tip' 		  => true,
		'class'			=> 'ph_ups_rates_tab',
	),
	'min_amount'  => array(
		'title'		   => __('Minimum Order Amount', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'placeholder'	=> wc_format_localized_price(0),
		'default'		 => '0',
		'description'	 => __('Users will need to spend this amount to get this shipping available.', 'ups-woocommerce-shipping'),
		'desc_tip'		   => true,
		'class'			=> 'ph_ups_rates_tab',
	),
	'min_weight_limit' => array(
		'title'		   => __('Minimum Weight', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'description'	 => __('Shipping Rates will be returned and Label will be created, if the total weight(After skipping the Products) is more than the Minimum Weight.', 'ups-woocommerce-shipping'),
		'desc_tip'		   => true,
		'class'			=> 'ph_ups_rates_tab'
	),
	'max_weight_limit' => array(
		'title'		   => __('Maximum Weight', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'description'	 => __('Shipping Rates will be returned and Label will be created, if the total weight(After skipping the Products) is less than the Maximum Weight.', 'ups-woocommerce-shipping'),
		'desc_tip'		   => true,
		'class'			=> 'ph_ups_rates_tab',
	)
);

// Labels.
$shipping_labels_settings = array(
		
	'label-title' => array(
		'title'			=> __('UPS Shipping Label Settings', 'ups-woocommerce-shipping'),
		'type'			=> 'title',
		'class'			=> 'ph_ups_label_tab',
		'description'	=> __('Configure the UPS shipping label related settings', 'ups-woocommerce-shipping'),
	),
	'disble_ups_print_label' => array(
		'title'			=> __('Label Printing', 'ups-woocommerce-shipping'),
		'type'			=> 'select',
		'default'		=> 'no',
		'class'			=> 'wc-enhanced-select ph_ups_label_tab',
		'options'		=> array(
			'no'			=> __('Enable', 'ups-woocommerce-shipping'),
			'yes'			=> __('Disable', 'ups-woocommerce-shipping'),
		),
	),
	'print_label_type'	=> array(
		'title'			=> __('Print Label Type', 'ups-woocommerce-shipping'),
		'type'			=> 'select',
		'default'		=> 'gif',
		'class'			=> 'wc-enhanced-select ph_ups_label_tab',
		'options'		=> array(
			'gif'			=> __('GIF', 'ups-woocommerce-shipping'),
			'png'			=> __('PNG', 'ups-woocommerce-shipping'),
			'zpl'			=> __('ZPL', 'ups-woocommerce-shipping'),
			'epl'			=> __('EPL', 'ups-woocommerce-shipping'),
		),
		'description'	=> __('Selecting PNG will enable ~4x6 dimension label. Note that an external api labelary is used. For Laser 8.5X11 please select GIF.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true
	),
	'label_format'  => array(
		'title'			=> __('Label Format', 'ups-woocommerce-shipping'),
		'type'			=> 'select',
		'default'		=> null,
		'options'		=> array(
			null				=>	__('None', 'ups-woocommerce-shipping'),
			'laser_8_5_by_11'	=>	__('Laser 8.5 X 11', 'ups-woocommerce-shipping'),
		),
		'desc_tip'	=> true,
		'description'	=> __('Selecting the label size will take affect ONLY for the Live Mode.', 'ups-woocommerce-shipping'),
		'class'			=> 'ph_ups_label_tab wc-enhanced-select',
	),
	'show_label_in_browser'  => array(
		'title'			=> __('Display Labels in Browser for Individual Order', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'description'	=> __('Enabling this will display the label in the browser instead of downloading it. Useful if your downloaded file is getting currupted because of PHP BOM (ByteOrderMark).', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_label_tab',
	),
	'rotate_label'  => array(
		'title'			=> __('Display label in Landscape mode', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'desc_tip'		=> false,
		'class'			=> 'ph_ups_label_tab',
	),
	'label_in_browser_zoom'  => array(
		'title'			=> __('Custom Scaling (%)', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'default'		=> '100',
		'description'	=> __('Provide a percentage value to scale the shipping label image based on your preference.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_label_tab',
	),
	'label_margin'  => array(
		'title'			=> __('Margin', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'number',
		'default'		=> '0',
		'description'	=> __('Applicable for all 4 sides (px).', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_label_tab ups_display_browser_options',
		'custom_attributes' => array('min' => 0, 'step' => 'any'),
	),
	'label_vertical_align'  => array(
		'title'			=> __('Vertical Alignment', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'select',
		'default'		=> 'center',
		'class'			=> 'ph_ups_label_tab ups_display_browser_options',
		'options'		=> array(
			'flex-start'	=>	__('Top', 'ups-woocommerce-shipping'),
			'center'		=>	__('Center', 'ups-woocommerce-shipping'),
			'flex-end'		=>	__('Bottom', 'ups-woocommerce-shipping'),
		),
	),
	'label_horizontal_align'  => array(
		'title'			=> __('Horizontal Alignment', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'select',
		'default'		=> 'center',
		'class'			=> 'ph_ups_label_tab ups_display_browser_options',
		'options'		=> array(
			'left'				=>	__('Left', 'ups-woocommerce-shipping'),
			'center'			=>	__('Center', 'ups-woocommerce-shipping'),
			'right'				=>	__('Right', 'ups-woocommerce-shipping'),
		),
	),
	'transportation'  => array(
		'title'            => __('Transportation', 'ups-woocommerce-shipping'),
		'type'            => 'select',
		'default'			=> 'shipper',
		'class'            => 'wc-enhanced-select ph_ups_label_tab',
		'options'        => array(
			'shipper' 	 => __('Shipper', 'ups-woocommerce-shipping'),
			'third_party' => __('Third Party', 'ups-woocommerce-shipping'),
		),
		'description'    => __('Select who will pay the Transportation Charges', 'ups-woocommerce-shipping'),
		'desc_tip'        => true,
	),
	'transport_payor_acc_no'	=> array(
		'title'		   => __('Third Party Account Number', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'default'		 => '',
		'class'			  => 'thirdparty_grp ph_ups_label_tab',
		'desc_tip'	=> true,
	),
	'transport_payor_post_code'	=> array(
		'title'		   => __('Third Party Postcode', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'default'		 => '',
		'class'			  => 'thirdparty_grp ph_ups_label_tab',
		'desc_tip'	=> true,
	),
	'transport_payor_country_code'	=> array(
		'title'		   => __('Third Party Country code', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'default'		 => '',
		'class'			  => 'thirdparty_grp ph_ups_label_tab',
		'desc_tip'	=> true,
	),
	'duties_and_taxes'  => array(
		'title'            => __('Duties And Taxes Payer', 'ups-woocommerce-shipping'),
		'type'            => 'select',
		'default'			=> 'receiver',
		'class'            => 'wc-enhanced-select ph_ups_label_tab',
		'options'        => array(
			'receiver' 	 => __('Reciever', 'ups-woocommerce-shipping'),
			'shipper' 	 => __('Shipper', 'ups-woocommerce-shipping'),
			'third_party' => __('Third Party', 'ups-woocommerce-shipping'),
		),
		'description'    => __('Select who will pay the Duties and Taxes.<br/> * Duties and Taxes Payer will be default to Shipper in case the customers select Access Point Location.', 'ups-woocommerce-shipping'),
		'desc_tip'        => true,
	),
	'shipping_payor_acc_no'	=> array(
		'title'		   => __('Third Party Account Number', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'default'		 => '',
		'class'			  => 'thirdparty_grp ph_ups_label_tab',
		'desc_tip'	=> true,
	),
	'shipping_payor_post_code'	=> array(
		'title'		   => __('Third Party Postcode', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'default'		 => '',
		'class'			  => 'thirdparty_grp ph_ups_label_tab',
		'desc_tip'	=> true,
	),
	'shipping_payor_country_code'	=> array(
		'title'		   => __('Third Party Country code', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'default'		 => '',
		'class'			  => 'thirdparty_grp ph_ups_label_tab',
		'desc_tip'	=> true,
	),
	'dangerous_goods_manifest' => array(
		'title'			=> __('Dangerous Goods Manifest', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable to print Dangerous Goods Manifest', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'class'			=> 'ph_ups_label_tab'
	),
	'dangerous_goods_signatoryinfo' => array(
		'title'			=> __('Dangerous Goods Signatory Information', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable this option to print the dangerous goods signatory information along with the shipping labels.', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'class'			=> 'ph_ups_label_tab'
	),
	'mail_innovation_type'   => array(
		'title'			=> __('Mail Innovation Packaging Type', 'ups-woocommerce-shipping'),
		'type'			=> 'select',
		'default'		=> '66',
		'class'			=> 'wc-enhanced-select ph_ups_label_tab',
		'options'		=> array(
			'57'	=> __('International Parcel: Parcels', 'ups-woocommerce-shipping'),
			'62'	=> __('Domestic Parcel < 1LBS: Irregulars', 'ups-woocommerce-shipping'),
			'63'	=> __('Domestic Parcel > 1LBS: Parcel Post', 'ups-woocommerce-shipping'),
			'64'	=> __('Domestic Parcel: BPM Parcel', 'ups-woocommerce-shipping'),
			'65'	=> __('Domestic Parcel: Media Mail', 'ups-woocommerce-shipping'),
			'66'	=> __('Flat: BPM Flat', 'ups-woocommerce-shipping'),
			'67'	=> __('Flat: Standard FLat', 'ups-woocommerce-shipping'),
		),
		'description'	=> __("Select the Packaging Type for Mail Innovation Services. For International Mail Innovations Shipments by default value will be 'Parcels'", 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
	),
	'usps_endorsement'   => array(
		'title'			=> __('USPS Endorsement for Mail Innovation', 'ups-woocommerce-shipping'),
		'type'			=> 'select',
		'default'		=> '5',
		'class'			=> 'wc-enhanced-select ph_ups_label_tab',
		'options'		=> array(
			'1'	=> __('Return Service', 'ups-woocommerce-shipping'),
			'2'	=> __('Forwarding Service', 'ups-woocommerce-shipping'),
			'3'	=> __('Address Service', 'ups-woocommerce-shipping'),
			'4'	=> __('Change Service', 'ups-woocommerce-shipping'),
			'5'	=> __('No Service', 'ups-woocommerce-shipping'),
		),
		'description'	=> __("Select the USPS Endorsement Type for Mail Innovation Services. For International Mail Innovations Shipments by default value will be 'No Service'", 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
	),
	'latin_encoding' => array(
		'title'		   => __('Enable Latin Encoding', 'ups-woocommerce-shipping'),
		'label'		   => __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		 => 'no',
		'description'	 => __('Check this option to use Latin encoding over default encoding.', 'ups-woocommerce-shipping'),
		'desc_tip'		   => true,
		'class'			=> 'ph_ups_label_tab'
	),
	'disble_shipment_tracking'   => array(
		'title'			=> __('Shipment Tracking', 'ups-woocommerce-shipping'),
		'type'			=> 'select',
		'default'		=> 'yes',
		'class'			=> 'wc-enhanced-select ph_ups_label_tab',
		'options'		=> array(
			'TrueForCustomer'	=> __('Disable for Customer', 'ups-woocommerce-shipping'),
			'False'				=> __('Enable', 'ups-woocommerce-shipping'),
			'True'				=> __('Disable', 'ups-woocommerce-shipping'),
		),
		'description'	=> __('Selecting Disable for customer will hide shipment tracking info from customer side order details page.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,

	),
	'custom_message' => array(
		'title'		   => __('Tracking Message', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'placeholder'	=>	__('Your order is shipped via UPS. To track shipment, please follow the shipment ID(s) ', 'ups-woocommerce-shipping'),
		'description'	 => __('Provide Your Tracking Message. Tracking Id(s) will be appended at the end of the tracking message.', 'ups-woocommerce-shipping'),
		'desc_tip'		   => true,
		'class'			=> 'ph_ups_label_tab'
	),
	'custom_tracking'	=> array(
		'title'		=> __('Custom Tracking', 'ups-woocommerce-shipping'),
		'label'		=> __('Enable', 'ups-woocommerce-shipping'),
		'type'		=> 'checkbox',
		'default'	=> 'no',
		'class'		=> 'ph_ups_label_tab'
	),
	'custom_tracking_url' => array(
		'title'		   	=> __('Custom Tracking URL', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'description'	=> __('Add the custom tracking URL you want to use for tracking UPS orders. Supported Tags: [TRACKING_ID].', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_label_tab'
	),
	'automate_package_generation'	  => array(
		'title'		   => __('Generate Packages Automatically After Order Received', 'ups-woocommerce-shipping'),
		'label'			  => __('Enable', 'ups-woocommerce-shipping'),
		'description'	 => __('This will generate packages automatically after order is received and payment is successful', 'ups-woocommerce-shipping'),
		'desc_tip'		   => true,
		'type'			=> 'checkbox',
		'default'		 => 'no',
		'class'			=> 'ph_ups_label_tab'
	),
	'automate_label_generation'	  => array(
		'title'		   => __('Generate Shipping Labels Automatically After Order Received', 'ups-woocommerce-shipping'),
		'label'			  => __('Enable', 'ups-woocommerce-shipping'),
		'description'	 => __('This will generate shipping labels automatically after order is received and payment is successful', 'ups-woocommerce-shipping'),
		'desc_tip'		   => true,
		'type'			=> 'checkbox',
		'default'		 => 'no',
		'class'			=> 'ph_ups_label_tab'
	),
	'automate_label_trigger' 	=> array(
		'title' 		=> __('Trigger Automatic Label Generation', 'ups-woocommerce-shipping'),
		'type'			=> 'select',
		'default'		=> 'thankyou_page',
		'class'			=> 'ph_ups_label_tab',
		'options' 		=> array(
			'thankyou_page'	=> __('Default - When the order is placed successfully', 'ups-woocommerce-shipping'),
			'payment_status' => __('When the payment is confirmed', 'ups-woocommerce-shipping'),
		),
	),
	'default_dom_service' => array(
		'title'		   => __('Default service for domestic', 'ups-woocommerce-shipping'),
		'description'	 => __('Default service for domestic label. This will consider if no UPS services selected from frond end while placing the order', 'ups-woocommerce-shipping'),
		'desc_tip'		   => true,
		'type'			=> 'select',
		'default'		 => '',
		'class'		   => 'wc-enhanced-select ph_ups_label_tab',
		'options'		  => array(
			null => __('Select', 'ups-woocommerce-shipping')
		) + $shipping_services,
	),
	'default_int_service'	=> array(
		'title'		   => __('Default service for International', 'ups-woocommerce-shipping'),
		'description'	 => __('Default service for International label. This will consider if no UPS services selected from frond end while placing the order', 'ups-woocommerce-shipping'),
		'desc_tip'		   => true,
		'type'			=> 'select',
		'class'		   => 'wc-enhanced-select ph_ups_label_tab',
		'default'		 => '',
		'options'		  => array(
			null => __('Select', 'ups-woocommerce-shipping')
		) + $shipping_services,
	),
	'allow_label_btn_on_myaccount'	  => array(
		'title'		   => __('Allow customers to print shipping label from their <br/>My Account->Orders page', 'ups-woocommerce-shipping'),
		'label'			  => __('Enable', 'ups-woocommerce-shipping'),
		'description'	 => __('A button will be available for downloading the label and printing', 'ups-woocommerce-shipping'),
		'desc_tip'		   => true,
		'type'			=> 'checkbox',
		'default'		 => 'no',
		'class'			=> 'ph_ups_label_tab'
	),
	'carbonneutral_indicator' => array(
		'title'			=> __('UPS Carbon Neutral', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'class'			=> 'ph_ups_label_tab'
	),
	'remove_special_char_product' => array(
		'title' 		=> __('Remove Special Characters from Product Name', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'class'			=> 'ph_ups_label_tab',
		'description'	=> __('While passing product details for Commercial Invoice, remove special characters from product name.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
	),
	'label_description'	=> array(
		'title'		   => __('Shipment Description For UPS Label or Commercial Invoice', 'ups-woocommerce-shipping'),
		'description'	 => __('Select how you want the shipment description on the UPS Shipping Label or Commercial Invoice. Choose from <br>1. Product Name <br>2. Product Category <br>3. Custom Description', 'ups-woocommerce-shipping'),
		'desc_tip'		   => true,
		'type'			=> 'select',
		'class'		   => 'wc-enhanced-select ph_ups_label_tab',
		'options'		  => array(
			'product_name' 			=> __('Product Name', 'ups-woocommerce-shipping'),
			'name_quantity' 		=> __('Product Name x Quantity (Only for Labels)', 'ups-woocommerce-shipping'),
			'product_desc' 			=> __('Product Description (UPS Shipping Details)', 'ups-woocommerce-shipping'),
			'product_category'		=> __('Product Category', 'ups-woocommerce-shipping'),
			'order_number'			=> __('Order Number Only', 'ups-woocommerce-shipping'),
			'custom_description' 	=> __('Custom Description', 'ups-woocommerce-shipping'),
		),
	),
	'label_custom_description'	=> array(
		'title'		   	=> __('Custom Description', 'ups-woocommerce-shipping'),
		'type'			=> 'textarea',
		'css' 			=>	'width: 400px;',
		'description'	=>	__('Enter character with length from 1 to 50 characters. If the shipment is from US to US or PR to PR maximum character limit is 35.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=>	'ph_label_custom_description ph_ups_label_tab'
	),
	'order_id_or_number_in_label'	=> array(
		'title'		   	=> __('Order details in shipment description', 'ups-woocommerce-shipping'),
		'type' 			=> 'select',
		'default'		=> $default_order_id_or_number,
		'class'		   	=> 'wc-enhanced-select ph_ups_label_tab ph_additional_shipment_description',
		'options'		=> array(
			'' 						=> __('NONE', 'ups-woocommerce-shipping'),
			'include_order_id' 		=> __('Include Order ID', 'ups-woocommerce-shipping'),
			'include_order_number'	=> __('Include Order Number', 'ups-woocommerce-shipping'),
		),
	),
	'add_product_sku' => array(
		'label'			=> __('Add Product SKU in Shipping Label<br/><small>For US Domestic Shipments only Product SKU will be added</small>', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'class'			=> 'ph_ups_label_tab ph_additional_shipment_description'
	),
	'include_in_commercial_invoice' => array(
		'label'		   => __('Include Shipment Description for Commercial Invoice as well.<br/><small><i>If disabled, Commercial Invoice will have Product Name as Default Description.</i></small>', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		 => 'no',
		'class'		=> 'ph_ups_label_tab ph_additional_shipment_description'
	),

	// Send Label via Email.
	'auto_email_label'	=> array(
		'title'				=> __('Send Shipping Label via Email', 'ups-woocommerce-shipping'),
		'type'				=> 'multiselect',
		'class'				=> 'chosen_select ph_ups_label_tab',
		'default'			=> '',
		'options'			=> apply_filters('ph_ups_option_for_automatic_label_recipient', array(
			'shipper' 			=> __('To Shipper', 'ups-woocommerce-shipping'),
			'recipient'			=> __('To Recipient', 'ups-woocommerce-shipping'),
		)),
	),
	'email_recipients'	  	=> array(
		'title'		   	=> __('Email Recipients in CC (Comma Separated)', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'class'			=> 'ph_ups_label_tab'
	),
	'email_subject'	  	=> array(
		'title'		   	=> __('Email Subject', 'ups-woocommerce-shipping'),
		'description'	=> __('Subject of Email sent for UPS Label. Supported Tags : [ORDER_NO] - Order Number.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'type'			=> 'text',
		'placeholder'	=> __('Shipment Label For Your Order', 'ups-woocommerce-shipping') . ' [ORDER_NO]',
		'class'			=> 'ph_ups_email_label_settings ph_ups_label_tab'
	),
	'email_content'	=> array(
		'title'		   	=> __('Content of Email With Label', 'ups-woocommerce-shipping'),
		'type'			=> 'textarea',
		'placeholder'	=> "<html><body>
		<div>Please Download the label</div>
		<a href='[DOWNLOAD LINK]' ><input type='button' value='Download the label here' /> </a>
		</body></html>",
		'default'		=> '',
		'css' 			=> 'width:70%;height: 150px;',
		'description'	=> __('Define your own email html here. Use the place holder tag [DOWNLOAD LINK] to get the label dowload link.<br />Supported Tags - <br />[DOWNLOAD LINK] - Label Link. <br />[ORDER NO] - Get order number. <br />[ORDER AMOUNT] - Order total Cost. <br />[PRODUCTS ID] - Comma seperated product ids in label. <br />[PRODUCTS SKU] - Comma seperated product sku in label. <br />[PRODUCTS NAME] - Comma seperated products name in label. <br />[PRODUCTS QUANTITY] - Comma seperated product quantities in label. <br />[ALL_PRODUCT INFO] - Product info in label in table form. <br />[ORDER_PRODUCTS] - Product info of order in table form.<br />[CUSTOMER EMAIL]- Customer contact Email ID. <br />[CUSTOMER NAME] - Customer Name.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_email_label_settings ph_ups_label_tab'
	),
);

// International Forms.
$international_forms_settings = array(

	'int-forms-title' => array(
		'title'			=> __('UPS International Forms Settings', 'ups-woocommerce-shipping'),
		'type'			=> 'title',
		'class'			=> 'ph_ups_int_forms_tab',
		'description'	=> __('Configure the UPS International forms related settings like Commercial Invoice, NAFTA and EEI DATA', 'ups-woocommerce-shipping'),
	),
	'commercial_invoice' => array(
		'title'		   => __('Commercial Invoice', 'ups-woocommerce-shipping'),
		'label'		   => __('Enable', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'type'			=> 'checkbox',
		'default'		 => 'no',
		'description'	 => __('On enabling this option will create commercial invoice. Applicable for International shipments only.', 'ups-woocommerce-shipping'),
		'class'			=> 'ph_ups_int_forms_tab'
	),
	'sold_to_address'	=> array(
		'title'			=> __('Consider Shipping Address as Sold to Address', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'class'			=> 'commercial_invoice_toggle ph_ups_int_forms_tab',
		'default'	 	=> 'no',
		'desc_tip'		=> true,
		'description'	=> 'Enabling this option will consider Shipping Address as the Sold To Address for International Shipments.'
	),

	// Unit of Measure on Invoice.
	'invoice_unit_of_measure'   => array(
		'title'		   	=> __( 'Unit of Measure (UOM)', 'ups-woocommerce-shipping' ),
		'type'			=> 'select',
		'class'		  	=> 'wc-enhanced-select ph_ups_int_forms_tab commercial_invoice_toggle',
		'desc_tip'		=> true,
		'description'	=> __("Choose the units you'd like to use for specifying your shipment's dimensions and weight. These units will be reflected on your commercial invoice.", 'ups-woocommerce-shipping' ),
		'default'		=> 'EA',
		'options'		=> PH_WC_UPS_Common_Utils::ph_get_translated_options(PH_WC_UPS_Constants::PH_INVOICE_UNIT_OF_MEASURES),		
	),

	'invoice_commodity_value'   => array(
		'title'		   	=> __( 'Price Value', 'ups-woocommerce-shipping' ),
		'type'			=> 'select',
		'class'		  	=> 'wc-enhanced-select ph_ups_int_forms_tab commercial_invoice_toggle',
		'desc_tip'		=> true,
		'description'	=> __('Select whether you want to display the discounted price, original product price or the declared value to be printed on the commercial invoice.', 'ups-woocommerce-shipping' ),
		'default'		=> $default_invoice_commodity_value,
		'options'		=> array(
			'discount_price' 			=> __( 'Discounted', 'ups-woocommerce-shipping'),
			'product_price' 			=> __( 'Product', 'ups-woocommerce-shipping'),
			'declared_price' 			=> __( 'Declared', 'ups-woocommerce-shipping')
		)				
	),	
	// PDS-124
	'commercial_invoice_shipping' => array(
		'title'			=> __('Shipping Charges in Commercial Invoice', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'class'			=> 'commercial_invoice_toggle ph_ups_int_forms_tab',
		'default'		=> 'no',
		'desc_tip'		=> true,
		'description'	=> 'Enabling this option will display shipping charges (if any) in Commercial Invoice.'
	),
	'declaration_statement' => array(
		'title'		   => __('Declaration Statement', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'label'		   => __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'css'			  => 'width:1000px',
		'placeholder'	  => __('Example: I hereby certify that the goods covered by this shipment qualify as originating goods for purposes of preferential tariff treatment under the NAFTA.', 'ups-woocommerce-shipping'),
		'description'	 => __('This is an optional field for the legal explanation, used by Customs, for the delivering of this shipment. It must be identical to the set of declarations actually used by Customs.', 'ups-woocommerce-shipping'),
		'class'		=> 'ph_ups_int_forms_tab'
	),
	'terms_of_shipment'	  => array(
		'title'		   => __('Terms of Sale (Incoterm)', 'ups-woocommerce-shipping'),
		'type'			   => 'select',
		'default'			=> '',
		'class'				=> 'wc-enhanced-select ph_ups_int_forms_tab',
		'options'			=> array(
			''	   		=> __('NONE', 	'ups-woocommerce-shipping'),
			'CFR'	   	=> __('Cost and Freight', 	'ups-woocommerce-shipping'),
			'CIF'	   	=> __('Cost Insurance and Freight', 	'ups-woocommerce-shipping'),
			'CIP'		=> __('Carriage and Insurance Paid', 	'ups-woocommerce-shipping'),
			'CPT'		=> __('Carriage Paid To', 	'ups-woocommerce-shipping'),
			'DAF'		=> __('Delivered at Frontier', 	'ups-woocommerce-shipping'),
			'DDP' 		=> __('Delivery Duty Paid', 	'ups-woocommerce-shipping'),
			'DDU' 		=> __('Delivery Duty Unpaid', 	'ups-woocommerce-shipping'),
			'DEQ' 		=> __('Delivered Ex Quay', 	'ups-woocommerce-shipping'),
			'DES' 		=> __('Delivered Ex Ship', 	'ups-woocommerce-shipping'),
			'EXW' 		=> __('Ex Works', 	'ups-woocommerce-shipping'),
			'FAS' 		=> __('Free Alongside Ship', 	'ups-woocommerce-shipping'),
			'FCA' 		=> __('Free Carrier', 	'ups-woocommerce-shipping'),
			'FOB' 		=> __('Free On Board', 	'ups-woocommerce-shipping'),
		),
		'description'	 => __('Indicates the rights to the seller from the buyer, internationally', 'ups-woocommerce-shipping'),
		'desc_tip'		   => true,
	),
	'reason_export'	  => array(
		'title'		   => __('Reason for Export', 'ups-woocommerce-shipping'),
		'type'			   => 'select',
		'default'			=> 0,
		'class'				=> 'wc-enhanced-select ph_ups_int_forms_tab',
		'options'			=> array(
			'none'	   	=> __('NONE', 	'ups-woocommerce-shipping'),
			'SALE'	   	=> __('SALE', 	'ups-woocommerce-shipping'),
			'GIFT'	   	=> __('GIFT', 	'ups-woocommerce-shipping'),
			'SAMPLE'		=> __('SAMPLE', 	'ups-woocommerce-shipping'),
			'RETURN'		=> __('RETURN', 	'ups-woocommerce-shipping'),
			'REPAIR'		=> __('REPAIR', 	'ups-woocommerce-shipping'),
			'INTERCOMPANYDATA' => __('INTERCOMPANYDATA', 	'ups-woocommerce-shipping'),
		),
		'description'	 => __('This may be required for customs purpose while shipping products to your customers, internationally', 'ups-woocommerce-shipping'),
		'desc_tip'		   => true,
	),
	'return_reason_export'	  => array(
		'title'		=> __('Reason for Export Returns', 'ups-woocommerce-shipping'),
		'type'		=> 'select',
		'default'	=> 'RETURN',
		'class'		=> 'wc-enhanced-select ph_ups_int_forms_tab',
		'options'	=> array(
			'none' 				=> __('NONE', 	'ups-woocommerce-shipping'),
			'SALE' 				=> __('SALE', 	'ups-woocommerce-shipping'),
			'GIFT' 				=> __('GIFT', 	'ups-woocommerce-shipping'),
			'SAMPLE'			=> __('SAMPLE', 	'ups-woocommerce-shipping'),
			'RETURN'			=> __('RETURN', 	'ups-woocommerce-shipping'),
			'REPAIR'			=> __('REPAIR', 	'ups-woocommerce-shipping'),
			'INTERCOMPANYDATA'	=> __('INTERCOMPANYDATA', 	'ups-woocommerce-shipping'),
		),
		'description' 	=> __('This may be required for customs purpose incase of return shipments.', 'ups-woocommerce-shipping'),
		'desc_tip' 		=> true,
	),
	'edi_on_label' => array(
		'title' 		=> __('EDI on Shipping Labels', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'class'			=> 'ph_ups_int_forms_tab',
		'description'	=> __('Enable this option when Shipper does not intend on supplying other self-prepared International Forms (EEI, CO, NAFTACO) to accompany the shipment.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
	),
	'nafta_co_form' => array(
		'title' 		=> __('NAFTA Certificate', 'ups-woocommerce-shipping'),
		'label'		   => __('Enable', 'ups-woocommerce-shipping'),
		'desc_tip' 		=> true,
		'type' 			=> 'checkbox',
		'description' 	=> __('Enable this option to create NORTH AMERICAN FREE TRADE AGREEMENT CERTIFICATE OF ORIGIN. Applicable for International shimpents only.', 'ups-woocommerce-shipping'),
		'class'			=> 'ph_ups_int_forms_tab'
	),
	'nafta_producer_option' => array(
		'title' 		=> __('NAFTA Producer Option', 'ups-woocommerce-shipping'),
		'desc_tip' 		=> true,
		'type' 			=> 'select',
		'options'		=>	array(
			'01'	=> __('01', 'ups-woocommerce-shipping'),
			'02'	=> __('02', 'ups-woocommerce-shipping'),
			'03'	=> __('03', 'ups-woocommerce-shipping'),
			'04'	=> __('04', 'ups-woocommerce-shipping'),
		),
		'default'		=> '02',
		'description' 	=> __('The text associated with the code will be printed in the producer section instead of producer contact information. <br/>  01 - AVAILABLE TO CUSTOMS UPON REQUEST <br/> 02 - SAME AS EXPORTER <br/> 03 - ATTACHED LIST <br/> 04 - UNKNOWN', 'ups-woocommerce-shipping'),
		'class'			=> 'ph_ups_int_forms_tab ph_ups_nafta_group'
	),
	'blanket_begin_period' => array(
		'title' 		=> __('Blanket Period Begin Date', 'ups-woocommerce-shipping'),
		'label' 		=> __('Enable', 'ups-woocommerce-shipping'),
		'desc_tip' 		=> true,
		'type' 			=> 'date',
		'css'			=> 'width:400px',
		'description' 	=> __('Begin date of the blanket period. It is the date upon which the Certificate becomes applicable to the good covered by the blanket Certificate (it may be prior to the date of signing this Certificate)', 'ups-woocommerce-shipping'),
		'class'			=> 'ph_ups_int_forms_tab ph_ups_nafta_group'
	),
	'blanket_end_period' => array(
		'title' 		=> __('Blanket Period End Date', 'ups-woocommerce-shipping'),
		'label' 		=> __('Enable', 'ups-woocommerce-shipping'),
		'desc_tip' 		=> true,
		'type' 			=> 'date',
		'css'			=> 'width:400px',
		'description' 	=> __('End Date of the blanket period. It is the date upon which the blanket period expires', 'ups-woocommerce-shipping'),
		'class'			=> 'ph_ups_int_forms_tab ph_ups_nafta_group'
	),
	'eei_data' => array(
		'title'			=> __('EEI DATA', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'type'			=> 'checkbox',
		'description'	=> __('Enable this option to create UPS EEI DATA. Applicable for International shimpents only.', 'ups-woocommerce-shipping'),
		'class'			=> 'ph_ups_int_forms_tab'
	),
	'eei_shipper_filed_option'  => array(
		'title'			=> __('Shipper Filed Option', 'ups-woocommerce-shipping'),
		'type'			=> 'select',
		'class'			=> 'wc-enhanced-select ph_ups_eei_group ph_ups_int_forms_tab',
		'options'		=> array(
			'A'			=> __('A', 'ups-woocommerce-shipping'),
			'B'			=> __('B', 'ups-woocommerce-shipping'),
			'C'			=> __('C', 'ups-woocommerce-shipping'),
		),
		'description'	=> __(" A - requires the ITN <br/> B - requires the Exemption Legend <br/> C - requires the post departure filing citation.", 'ups-woocommerce-shipping'),
		'desc_tip'		   => true,
	),
	'eei_pre_departure_itn_number'  => array(
		'title'			=> __('Pre Departure ITN Number', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'class'			=> 'ph_ups_eei_group eei_pre_departure_itn_number ph_ups_int_forms_tab',
		'description'	=> __("Input for Shipper Filed option 'A'. The format is available from AESDirect website", 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
	),
	'eei_exemption_legend'  => array(
		'title'			=> __('Exemption Legend', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'class'			=> 'ph_ups_eei_group eei_exemption_legend ph_ups_int_forms_tab',
		'description'	=> __("Input for Shipper Filed option 'B'", 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
	),
	'eei_mode_of_transport'  => array(
		'title'			=> __('Mode of Transport', 'ups-woocommerce-shipping'),
		'type'			=> 'select',
		'default'		=> 'Air',
		'class'			=> 'wc-enhanced-select ph_ups_eei_group ph_ups_int_forms_tab',
		'options'		=> array(
			'Air'							=> __('Air', 	'ups-woocommerce-shipping'),
			'AirContainerized'				=> __('Air Containerized', 	'ups-woocommerce-shipping'),
			'Auto'							=> __('Auto', 	'ups-woocommerce-shipping'),
			'FixedTransportInstallations'	=> __('Fixed Transport Installations', 	'ups-woocommerce-shipping'),
			'Mail'							=> __('Mail', 	'ups-woocommerce-shipping'),
			'PassengerHandcarried'			=> __('Passenger Handcarried', 	'ups-woocommerce-shipping'),
			'Pedestrian' 					=> __('Pedestrian', 	'ups-woocommerce-shipping'),
			'Rail'							=> __('Rail', 	'ups-woocommerce-shipping'),
			'Containerized'					=> __('Containerized', 	'ups-woocommerce-shipping'),
			'Auto'							=> __('Auto', 	'ups-woocommerce-shipping'),
			'FixedTransportInstallations'	=> __('Fixed Transport Installations', 	'ups-woocommerce-shipping'),
			'RoadOther'						=> __('Road Other', 	'ups-woocommerce-shipping'),
			'SeaBarge'						=> __('Sea Barge', 	'ups-woocommerce-shipping'),
			'SeaContainerized'				=> __('Sea Containerized', 	'ups-woocommerce-shipping'),
			'SeaNoncontainerized'			=> __('Sea Noncontainerized', 	'ups-woocommerce-shipping'),
			'Truck'							=> __('Truck', 	'ups-woocommerce-shipping'),
			'TruckContainerized'			=> __('Truck Containerized', 	'ups-woocommerce-shipping'),
		),
		'description'	=> __('Mode of transport by which the goods are exported. Only 10 Characters can appear on the form. Anything greater than 10 characters will be truncated on the form.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
	),
	'eei_parties_to_transaction'  => array(
		'title'			=> __('Parties To Transaction', 'ups-woocommerce-shipping'),
		'type'			=> 'select',
		'default'		=> 'R',
		'class'			=> 'wc-enhanced-select ph_ups_eei_group ph_ups_int_forms_tab',
		'options'		=> array(
			'R'			=> __('Related', 	'ups-woocommerce-shipping'),
			'N'			=> __('Non Related', 	'ups-woocommerce-shipping'),
		),
		'description'	=> __('Use Related, if the parties to the transaction are related. A related party is an export from a U.S. businessperson or business to a foreign business or from a U.S. business to a foreign person or business where the person has at least 10 percent of the voting shares of the business during the fiscal year.', 'ups-woocommerce-shipping'),
		'desc_tip'		   => true,
	),
	'eei_ultimate_consignee_code'	=> array(
		'title'			=> __('Ultimate Consignee Type', 'ups-woocommerce-shipping'),
		'type'			=> 'select',
		'default'		=> '',
		'class'			=> 'wc-enhanced-select ph_ups_eei_group ph_ups_int_forms_tab',
		'options'		=> array(
			''			=> __('None', 'ups-woocommerce-shipping'),
			'D'			=> __('Direct Consumer', 'ups-woocommerce-shipping'),
			'G'			=> __('Government Entity', 'ups-woocommerce-shipping'),
			'R'			=> __('Reseller', 'ups-woocommerce-shipping'),
			'O'			=> __('Other/Unknown', 'ups-woocommerce-shipping')
		),
		'description'	=> __('An ultimate consignee is the party who will be the final recipient of a shipment.', 'ups-woocommerce-shipping'),
		'desc_tip'		   => true,
	),
	'vendor_info'	=> array(
		'title'			=> __('Vendor Info', 'ups-woocommerce-shipping'),
		'label'			=> __('VCIDs are new types of VAT registration numbers that are used for verified point-of-sale VAT charges collected for lower-value goods to customers in certain </br> countries. VCIDs can speed up customs clearance and prevent unnecessary import fees.', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'description'	=> __('If enabled VCID details will be available on edit order page', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_int_forms_tab',
	),
);

// Special Services.
$special_services_settings = array(	

	'spl-services-title' => array(
		'title'			=> __('UPS Special Services', 'ups-woocommerce-shipping'),
		'type'			=> 'title',
		'class'			=> 'ph_ups_spl_services_tab',
		'description'	=> __('Configure special services related setting.', 'ups-woocommerce-shipping'),
	),
	'import_control_settings'  => array(
		'title'			=> __('UPS Import Control', 'ups-woocommerce-shipping'),
		'label'		   => __('Enable<br/><small><i>If you enable this option then shipment will be considered as import control shipment. For more details:<a href="https://www.ups.com/us/en/support/international-tools-resources/ups-import-control.page" target="_blank" >  UPS Import Control℠</a></i></small>', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'description'	=> __('UPS Import Control allows you, as the importer, to initiate UPS shipments from another country and have those shipments delivered to your business or to an alternate location.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_spl_services_tab',
	),
	'saturday_delivery'	=> array(
		'title'   		=> __('Saturday Delivery', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'class'			=> 'ph_ups_spl_services_tab',
		'description'	=> __('Saturday Delivery from UPS allows you to stretch your business week to Saturday', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'default' 		=> 'no',
	),
	'cod_enable'	=> array(
		'title'			=> __('UPS COD', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'description'	=> __('Enable this to calculate COD Rates on Cart/Checkout', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_spl_services_tab',
	),
	'eu_country_cod_type' => array(
		'title' 		=> __('COD Type', 'ups-woocommerce-shipping'),
		'desc_tip' 		=> true,
		'type' 			=> 'select',
		'options'		=>	array(
			'9'	=> __("Check / Cashier's Check / Money Order", 'ups-woocommerce-shipping'),
			'1'	=> __("Cash", 'ups-woocommerce-shipping'),
		),
		'default'		=> '9',
		'class' 		=> 'wc-enhanced-select ph_ups_spl_services_tab',
		'description' 	=> __('Collect on Delivery Type for all European Union (EU) Countries or Territories', 'ups-woocommerce-shipping'),
	),
	'ph_delivery_confirmation'   => array(
		'title'			=> __('Delivery Confirmation', 'ups-woocommerce-shipping'),
		'type'			=> 'select',
		'class'			=> 'wc-enhanced-select ph_ups_spl_services_tab',
		'description'	=> __('Appropriate signature option for your shipping service.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'default'       => 0,
		'options'	=> array(
			0	=> __('Confirmation Not Required', 'ups-woocommerce-shipping'),
			1	=> __('Confirmation Required', 'ups-woocommerce-shipping'),
			2	=> __('Confirmation With Signature', 'ups-woocommerce-shipping'),
			3	=> __('Confirmation With Adult Signature', 'ups-woocommerce-shipping')
		),
	),
	'ups_simple_rate' 	=> array(
		'title'			=> __('UPS Simple Rate', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable UPS Simple Rate', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'description'	=> __('Enable it to get UPS Simple Rates in cart/checkout pages.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_spl_services_tab',
	),
	'international_special_commodities' => array(
		'title'         => __('International Special Commodities', 'ups-woocommerce-shipping'),
		'label'         => __('Enable', 'ups-woocommerce-shipping'),
		'type'          => 'checkbox',
		'default'       => 'no',
		'description'   => __('Enabling this option indicates that the package may contain biological items or item with International Special Commodity standards.', 'ups-woocommerce-shipping'),
		'desc_tip'      => true,
		'class'     	=> 'ph_ups_spl_services_tab'
	),
	'ph_ups_restricted_article' 	=> array(
		'title'			=> __('Restricted Articles', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'class'			=> 'ph_ups_spl_services_tab ph_ups_isc_toggol',
	),
	'ph_ups_diog' 	=> array(
		'title'			=> __('Diagnostic Specimens Indicator', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'class'			=> 'ph_ups_spl_services_tab ph_restricted_article',
		'desc_tip'		=> true,
		'description'	=> __('Enable this if the package has Biological substances.', 'ups-woocommerce-shipping'),
	),
	'ph_ups_alcoholic' 	=> array(
		'title'			=> __('Alcoholic Beverages Indicator', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'class'			=> 'ph_ups_spl_services_tab ph_restricted_article',
		'desc_tip'		=> true,
		'description'	=> __('Enable this if the package contains Alcoholic Beverages.', 'ups-woocommerce-shipping'),
	),
	'ph_ups_perishable' 	=> array(
		'title'			=> __('Perishables Indicator', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'class'			=> 'ph_ups_spl_services_tab ph_restricted_article',
		'desc_tip'		=> true,
		'description'	=> __('Enable this if the package contains Perishable items.', 'ups-woocommerce-shipping'),
	),
	'ph_ups_plantsindicator' 	=> array(
		'title'			=> __('Plants Indicator', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'class'			=> 'ph_ups_spl_services_tab ph_restricted_article',
		'desc_tip'		=> true,
		'description'	=> __('Enable this if the package contains Plants.', 'ups-woocommerce-shipping'),
	),
	'ph_ups_seedsindicator' 	=> array(
		'title'			=> __('Seeds Indicator', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'class'			=> 'ph_ups_spl_services_tab ph_restricted_article',
		'desc_tip'		=> true,
		'description'	=> __('Enable this if the package contains Seeds.', 'ups-woocommerce-shipping'),
	),
	'ph_ups_specialindicator' 	=> array(
		'title'			=> __('Special Exceptions Indicator', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'class'			=> 'ph_ups_spl_services_tab ph_restricted_article',
		'desc_tip'		=> true,
		'description'	=> __('Enable this if the package contains Special Exception items.', 'ups-woocommerce-shipping'),
	),
	'ph_ups_tobaccoindicator' 	=> array(
		'title'			=> __('Tobacco Indicator', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'class'			=> 'ph_ups_spl_services_tab ph_restricted_article',
		'description'	=> __('Enable this if the package contains Tobacco.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
	),
	'ph_ups_refrigeration' 	=> array(
		'title'			=> __('Refrigeration Indicator', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'class'			=> 'ph_ups_spl_services_tab ph_ups_isc_toggol',
		'description'	=> __('Enable this if the package contains an item that needs refrigeration.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
	),
	'ph_ups_clinicaltrials' 	=> array(
		'title'			=> __('Clinical Trials Id', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'class'			=> 'ph_ups_spl_services_tab ph_ups_isc_toggol',
		'description'	=> __('Unique identifier for clinical trials', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
	),
);

// Packaging.
$packaging_settings = array(

	'packaging-title' => array(
		'title'			=> __('Package Settings', 'ups-woocommerce-shipping'),
		'type'			=> 'title',
		'class'			=> 'ph_ups_packaging_tab',
		'description'	=> __('Choose the packing options suitable for your store', 'ups-woocommerce-shipping'),
	),
	'packing_method'	  => array(
		'title'		   => __('Parcel Packing', 'ups-woocommerce-shipping'),
		'type'			=> 'select',
		'default'		 => 'weight_based',
		'class'		   => 'packing_method wc-enhanced-select ph_ups_packaging_tab',
		'options'		 => array(
			'per_item'	=> __('Default: Pack items individually', 'ups-woocommerce-shipping'),
			'box_packing' => __('Recommended: Pack into boxes with weights and dimensions', 'ups-woocommerce-shipping'),
			'weight_based' => __('Weight based: Calculate shipping on the basis of order total weight', 'ups-woocommerce-shipping'),
		),
	),
	'packing_algorithm'  			=> array(
		'title'		   			=> __('Packing Algorithm', 'ups-woocommerce-shipping'),
		'type'					=> 'select',
		'default'		 		=> 'volume_based',
		'class'		   			=> 'xa_ups_box_packing wc-enhanced-select ph_ups_packaging_tab',
		'options'		 		=> array(
			'volume_based'	   	=> __('Default: Volume Based Packing', 'ups-woocommerce-shipping'),
			'stack_first'		=> __('Stack First Packing', 'ups-woocommerce-shipping'),
			'new_algorithm'		=> __('New Algorithm(Based on Volume Used * Item Count)', 'ups-woocommerce-shipping'),
		),
	),
	'exclude_box_weight'	=> array(
		'title'   			=> __('Exclude Box Weight', 'ups-woocommerce-shipping'),
		'type'				=> 'checkbox',
		'class'				=> 'xa_ups_box_packing exclude_box_weight ph_ups_packaging_tab',
		'label'				=> __('Enabling this option will not include Box Weight', 'ups-woocommerce-shipping'),
		'default' 			=> 'no',
	),
	'stack_to_volume'	=> array(
		'title'   			=> __('Convert Stack First to Volume Based', 'ups-woocommerce-shipping'),
		'type'				=> 'checkbox',
		'class'				=> 'xa_ups_box_packing stack_to_volume ph_ups_packaging_tab',
		'label'				=> __('Automatically change packing method when the products are packed in a box and the filled up space is less less than 44% of the box volume', 'ups-woocommerce-shipping'),
		'default' 			=> 'yes',
	),

	'volumetric_weight'	=> array(
		'title'   			=> __('Enable Volumetric weight', 'ups-woocommerce-shipping'),
		'type'				=> 'checkbox',
		'class'				=> 'weight_based_option ph_ups_packaging_tab',
		'label'				=> __('This option will calculate the volumetric weight. Then a comparison is made on the total weight of cart to the volumetric weight.</br>The higher weight of the two will be sent in the request.', 'ups-woocommerce-shipping'),
		'default' 			=> 'no',
	),

	'box_max_weight'		   => array(
		'title'		   => __('Max Package Weight', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'default'		 => '10',
		'class'		   => 'weight_based_option ph_ups_packaging_tab',
		'desc_tip'	=> true,
		'description'	 => __('Maximum weight allowed for single box.', 'ups-woocommerce-shipping'),
	),
	'weight_packing_process'   => array(
		'title'		   => __('Packing Process', 'ups-woocommerce-shipping'),
		'type'			=> 'select',
		'default'		 => 'pack_descending',
		'class'		   => 'weight_based_option wc-enhanced-select ph_ups_packaging_tab',
		'options'		 => array(
			'pack_descending'	   => __('Pack heavier items first', 'ups-woocommerce-shipping'),
			'pack_ascending'		=> __('Pack lighter items first.', 'ups-woocommerce-shipping'),
			'pack_simple'			=> __('Pack purely divided by weight.', 'ups-woocommerce-shipping'),
		),
		'desc_tip'	=> true,
		'description'	 => __('Select your packing order.', 'ups-woocommerce-shipping'),
	),
	'boxes'  => array(
		'type'			=> 'box_packing'
	),
);

// Freight.
$freight_settings = array(

	'freight-title' => array(
		'title'			=> __('Freight Settings', 'ups-woocommerce-shipping'),
		'type'			=> 'title',
		'class'			=> 'ph_ups_freight_tab',
		'description'	=> __('Configure the UPS Freight related settings', 'ups-woocommerce-shipping'),
	),

	// Freight Banner.
	'ph_ups_freight_banner'=>array(
		'type'	=>'ph_ups_freight_banner'
	),

	'enable_freight' => array(
		'title'			=> __('Freight Services', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'description'	=> __('Enable Freight Services	', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_freight_tab',
	),
	'freight_payment'		=> array(
		'title'			=> __('Payment Information', 'ups-woocommerce-shipping'),
		'type'			=> 'select',
		'desc_tip'		=> true,
		'description'	=> __('Choose Freight Billing Option', 'ups-woocommerce-shipping'),
		'default'		=> '10',
		'class'			=> 'ph_ups_freight_payment wc-enhanced-select ph_ups_freight_tab',
		'options'		=> array(
			'10'		=> __('Prepaid', 'ups-woocommerce-shipping'),
			'30'		=> __('Bill to Third Party', 'ups-woocommerce-shipping'),
		),
	),
	'freight_thirdparty_contact_name'  => array(
		'title'			=> __('Contact Name', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'description'	=> __('Third Party Contact Name', 'ups-woocommerce-shipping'),
		'default'		=> '',
		'desc_tip'		=> true,
		'class'			=>	'ph_ups_freight_tab ph_ups_freight_third_party_billing'
	),
	'freight_thirdparty_addressline'  => array(
		'title'			=> __('Address Line 1', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'description'	=> __('Third Party Address Line 1', 'ups-woocommerce-shipping'),
		'default'		=> '',
		'desc_tip'		=> true,
		'class'			=>	'ph_ups_freight_tab ph_ups_freight_third_party_billing'
	),
	'freight_thirdparty_addressline_2'  => array(
		'title'			=> __('Address Line 2', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'description'	=> __('Third Party Address Line 2', 'ups-woocommerce-shipping'),
		'default'		=> '',
		'desc_tip'		=> true,
		'class'			=>	'ph_ups_freight_tab ph_ups_freight_third_party_billing'
	),
	'freight_thirdparty_city'	  	  => array(
		'title'			=> __('City', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'description'	=> __('Third Party City', 'ups-woocommerce-shipping'),
		'default'		=> '',
		'desc_tip'		=> true,
		'class'			=>	'ph_ups_freight_tab ph_ups_freight_third_party_billing'
	),
	'freight_thirdparty_country_state'	=> array(
		'type'			=> 'freight_thirdparty_country_state',
	),
	'freight_thirdparty_custom_state'		=> array(
		'title'			=> __('State Code', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'description'	=> __('Specify Third Party State Province Code if state not listed with Third Party Country.', 'ups-woocommerce-shipping'),
		'default'		=> '',
		'desc_tip'		=> true,
		'class'			=>	'ph_ups_freight_tab ph_ups_freight_third_party_billing'
	),
	'freight_thirdparty_postcode'	 => array(
		'title'			=> __('Postcode', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'description'	=> __('Third Party Zip/Postcode.', 'ups-woocommerce-shipping'),
		'default'		=> '',
		'desc_tip'		=> true,
		'class'			=>	'ph_ups_freight_tab ph_ups_freight_third_party_billing'
	),
	'freight_class'		=>	array(
		'title'				=>	__("Freight Class", "ups-woocommerce-shipping"),
		"type"				=>	"select",
		"default"			=>	"50",
		"options"			=>	array(
			"50"		=>	"Class 50",	// Fits on standard shrink-wrapped 4X4 pallet, very durable, Ref link - http://www.fmlfreight.com/freight-101/freight-classes/
			"55"		=>	"Class 55",			// Bricks, cement, mortar, hardwood flooring
			"60"		=>	"Class 60",			// Car accessories & car parts
			"65"		=>	"Class 65",			// Car accessories & car parts, bottled beverages, books in boxes
			"70"		=>	"Class 70",			// Car accessories & car parts, food items, automobile engines	
			"77.5"		=>	"Class 77.5",		// Tires, bathroom fixtures	
			"85"		=>	"Class 85",			// Crated machinery, cast iron stoves
			"92.5"		=>	"Class 92.5",		// Computers, monitors, refrigerators
			"100"		=>	"Class 100",		// boat covers, car covers, canvas, wine cases, caskets	
			"110"		=>	"Class 110",		// cabinets, framed artwork, table saw	
			"125"		=>	"Class 125",		// Small Household appliances	
			"150"		=>	"Class 150",		// Auto sheet metal parts, bookcases,	
			"175"		=>	"Class 175",		// Clothing, couches stuffed furniture	
			"200"		=>	"Class 200",		// Auto sheet metal parts, aircraft parts, aluminum table, packaged mattresses,	
			"250"		=>	"Class 250",		// Bamboo furniture, mattress and box spring, plasma TV	
			"300"		=>	"Class 300",		// wood cabinets, tables, chairs setup, model boats	
			"400"		=>	"Class 400",		// Deer antlers	
			"500"		=>	"Class 500"			// Bags of gold dust, ping pong balls	
		),
		"description"		=>	__("50 - Fits on standard shrink-wrapped 4X4 pallet, very durable (Lowest Cost).<br>55 - Bricks, cement, mortar, hardwood flooring.<br>60 - Car accessories & car parts.<br>65 - Car accessories & car parts, bottled beverages, books in boxes.<br>70 - Car accessories & car parts, food items, automobile engines.<br>77.5 - Tires, bathroom fixtures.<br>85 - Crated machinery, cast iron stoves.<br>92.5 - Computers, monitors, refrigerators.<br>100 - boat covers, car covers, canvas, wine cases, caskets.<br>110 - cabinets, framed artwork, table saw.<br>125 - Small Household appliances.<br>150 - Auto sheet metal parts, bookcases.<br>175 - Clothing, couches stuffed furniture.<br>200 - Auto sheet metal parts, aircraft parts, aluminum table, packaged mattresses.<br>250 - Bamboo furniture, mattress and box spring, plasma TV.<br>300 - wood cabinets, tables, chairs setup, model boats.<br>400 - Deer antlers.<br>500 - Bags of gold dust, ping pong balls (Highest Cost).", "ups-woocommerce-shipping"),
		"desc_tip"			=>	true,
		'class'			=> 'ph_ups_freight_tab'
	),
	'freight_packaging_type'		=>	array(
		'title'			=>	__("Freight Packaging Type", "ups-woocommerce-shipping"),
		"type"			=>	"select",
		"default"		=>	"PLT",
		"options"		=>	array(
			"BAG"	=> "Bag",
			"BAL"	=> "Bale",
			"BAR"	=> "Barrel",
			"BDL"	=> "Bundle",
			"BIN"	=> "Bin",
			"BOX"	=> "Box",
			"BSK"	=> "Basket",
			"BUN"	=> "Bunch",
			"CAB"	=> "Cabinet",
			"CAN"	=> "Can",
			"CAR"	=> "Carrier",
			"CAS"	=> "Case",
			"CBY"	=> "Carboy",
			"CON"	=> "Container",
			"CRT"	=> "Crate",
			"CSK"	=> "Cask",
			"CTN"	=> "Carton",
			"CYL"	=> "Cylinder",
			"DRM"	=> "Drum",
			"LOO"	=> "Loose",
			"OTH"	=> "Other",
			"PAL"	=> "Pail",
			"PCS"	=> "Pieces",
			"PKG"	=> "Package",
			"PLN"	=> "Pipe Line",
			"PLT"	=> "Pallet",
			"RCK"	=> "Rack",
			"REL"	=> "Reel",
			"ROL"	=> "Roll",
			"SKD"	=> "Skid",
			"SPL"	=> "Spool",
			"TBE"	=> "Tube",
			"TNK"	=> "Tank",
			"UNT"	=> "Unit",
			"VPK"	=> "Van Pack",
			"WRP"	=> "Wrapped",
		),
		"description"	=>	__("The UPS packaging type associated with the shipment. ", "ups-woocommerce-shipping"),
		"desc_tip"		=>	true,
		'class'			=> 'ph_ups_freight_tab'
	),
	'freight_holiday_pickup' 	=> array(
		'title'			=> __('Freight Holiday Pickup', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'description'	=> __('Enable it to indicate if the shipment requires a holiday pickup', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_freight_tab',
	),
	'freight_inside_pickup' 	=> array(
		'title'			=> __('Freight Inside Pickup', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'description'	=> __('Enable it to indicate if the shipment requires a inside pickup', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_freight_tab',
	),
	'freight_residential_pickup' 	=> array(
		'title'			=> __('Freight Residential Pickup', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'description'	=> __('Enable it to indicate if the shipment requires a residential pickup', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_freight_tab',
	),
	'freight_weekend_pickup' 	=> array(
		'title'			=> __('Freight Weekend Pickup', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'description'	=> __('Enable it to indicate if the shipment requires a weekend pickup', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_freight_tab',
	),
	'freight_liftgate_pickup' 	=> array(
		'title'			=> __('Freight Lift Gate Pickup', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'description'	=> __('Enable it to indicate if the shipment requires a lift gate pickup', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_freight_tab',
	),
	'freight_limitedaccess_pickup' 	=> array(
		'title'			=> __('Freight Limited Access Pickup', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'description'	=> __('Enable it to indicate if the shipment has limited access for pickup', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_freight_tab',
	),
	'freight_holiday_delivery' 	=> array(
		'title'			=> __('Freight Holiday Delivery', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'description'	=> __('Enable it to indicate that the shipment is going to be delivered on a holiday.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_freight_tab',
	),
	'freight_inside_delivery' 	=> array(
		'title'			=> __('Freight Inside Delivery', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'description'	=> __('Enable it to indicate that the shipment requires an inside delivery.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_freight_tab',
	),
	'freight_call_before_delivery' 	=> array(
		'title'			=> __('Freight Call Before Delivery', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'description'	=> __('Enable it to indicate that the shipment is going to be delivered after calling the consignee.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_freight_tab',
	),
	'freight_weekend_delivery' 	=> array(
		'title'			=> __('Freight Weekend Delivery', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'description'	=> __('Enable it to  indicate that the shipment is going to be delivered on a weekend.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_freight_tab',
	),
	'freight_liftgate_delivery' 	=> array(
		'title'			=> __('Freight Lift Gate Delivery', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'description'	=> __('Enable it to indicate that the shipment requires a lift gate.', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_freight_tab',
	),
	'freight_limitedaccess_delivery' 	=> array(
		'title'			=> __('Freight Limited Access Delivery', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'description'	=> __('Enable it to indicate that there is limited access for delivery', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_freight_tab',
	),
	'freight_pickup_inst' 	=> array(
		'title'			=> __('Freight Pickup Instructions', 'ups-woocommerce-shipping'),
		'type'			=> 'textarea',
		'css' 			=> 'width: 400px;',
		'description'	=> __('Pickup Instructions', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_freight_tab',
	),
	'freight_delivery_inst' => array(
		'title'			=> __('Freight Delivery Instructions', 'ups-woocommerce-shipping'),
		'type'			=> 'textarea',
		'css' 			=> 'width: 400px;',
		'description'	=> __('Delivery Instructions', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_freight_tab',
	),
	'enable_density_based_rating'		=> array(
		'title'			=> __('Density Based Rating (DBR)', 'ups-woocommerce-shipping'),
		'label'			=> __('Enable', 'ups-woocommerce-shipping'),
		'type'			=> 'checkbox',
		'default'		=> 'no',
		'description'	=> __('Enable this option if Density Based Rating is enabled for your UPS Account', 'ups-woocommerce-shipping'),
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_freight_tab',
	),
	'density_description'	=> array(
		'type'			=> 'title',
		'class'			=> 'density_description ph_ups_freight_tab',
		'description'	=> __('Enter Freight Package Dimensions if Weight Based Packing Method is enabled. By default the plugin will take dimensions as 47x47x47 IN or 119x119x119 CM', 'ups-woocommerce-shipping')
	),
	'density_length'	=> array(
		'title'		   	=> __('Length(DBR)', 'ups-woocommerce-shipping'),
		'type'			=> 'number',
		'description'	=> 'Length',
		'placeholder'	=> '47 IN / 119 CM',
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_freight_tab',
	),
	'density_width'	=> array(
		'title'		   	=> __('Width(DBR)', 'ups-woocommerce-shipping'),
		'type'			=> 'number',
		'description'	=> 'Width',
		'placeholder'	=> '47 IN / 119 CM',
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_freight_tab',
	),
	'density_height'	=> array(
		'title'		   	=> __('Height(DBR)', 'ups-woocommerce-shipping'),
		'type'			=> 'number',
		'description'	=> 'Height',
		'placeholder'	=> '47 IN / 119 CM',
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_freight_tab',
	),
);

// Pickup.
$pickup_settings = array(

	'pickup-title'  => array(
		'title'			=> __('UPS Pickup Settings', 'ups-woocommerce-shipping'),
		'type'			=> 'title',
		'description'	=> __('Configure the UPS Pickup related settings', 'ups-woocommerce-shipping'),
		'class'			=> 'ph_ups_pickup_tab',
	),
	'pickup_enabled'	  => array(
		'title'		   => __('Enable Pickup', 'ups-woocommerce-shipping'),
		'description'	 => __('Enable this to setup pickup request', 'ups-woocommerce-shipping'),
		'desc_tip'		   => true,
		'type'			=> 'checkbox',
		'default'		 => 'no',
		'class'			=> 'ph_ups_pickup_tab'
	),
	'pickup_start_time'		   => array(
		'title'		   => __('Pickup Start Time', 'ups-woocommerce-shipping'),
		'description'	 => __('Items will be ready for pickup by this time from shop', 'ups-woocommerce-shipping'),
		'desc_tip'		   => true,
		'type'			=> 'select',
		'class'			  => 'wf_ups_pickup_grp wc-enhanced-select ph_ups_pickup_tab',
		'default'		 => 8,
		'options'		  => $pickup_start_time_options,
	),
	'pickup_close_time'		   => array(
		'title'		   => __('Company Close Time', 'ups-woocommerce-shipping'),
		'description'	 => __('Your shop closing time. It must be greater than company open time', 'ups-woocommerce-shipping'),
		'desc_tip'		   => true,
		'type'			=> 'select',
		'class'			  => 'wf_ups_pickup_grp wc-enhanced-select ph_ups_pickup_tab',
		'default'		 => 18,
		'options'		  => $pickup_close_time_options,
	),
	'pickup_date'		   => array(
		'title'			  => __('Pick up date', 'ups-woocommerce-shipping'),
		'type'			   => 'select',
		'desc_tip'		   => true,
		'description'	 => __('Default option will pick current date. Choose \'Select working days\' to configure working days', 'ups-woocommerce-shipping'),
		'default'			=> 'current',
		'class'			  => 'wf_ups_pickup_grp wc-enhanced-select ph_ups_pickup_tab',
		'options'			=> array(
			'current'			=> __('Default', 'ups-woocommerce-shipping'),
			'specific'	   => __('Select working days', 'ups-woocommerce-shipping'),
		),
	),
	'working_days'			  => array(
		'title'			  => __('Select working days', 'ups-woocommerce-shipping'),
		'type'			   => 'multiselect',
		'desc_tip'		   => true,
		'description'	 => __('Select working days here. Selected days will be used for pickup'),
		'class'			  => 'wf_ups_pickup_grp pickup_working_days chosen_select ph_ups_pickup_tab',
		'css'				=> 'width: 450px;',
		'default'			=> array('Mon', 'Tue', 'Wed', 'Thu', 'Fri'),
		'options'			=> array('Sun' => 'Sunday', 'Mon' => 'Monday', 'Tue' => 'Tuesday', 'Wed' => 'Wednesday', 'Thu' => 'Thursday', 'Fri' => 'Friday', 'Sat' => 'Saturday'),
	),
);

// Advanced.
$advanced_settings = array(
	
	'advanced-title'  => array(
		'title'			=> __('UPS Advanced Settings', 'ups-woocommerce-shipping'),
		'type'			=> 'title',
		'description'	=> __('', 'ups-woocommerce-shipping'),
		'class'			=> 'ph_ups_advanced_settings_tab',
	),
	'change_company_name'	=> array(
		'title'				=> __('Print Customer’s Name as Company Name on Labels', 'ups-woocommerce-shipping'),
		'label'			=> __('If your customers do not enter a Company Name on checkout page, enabling this option will print the customer’s name as company name on the labels.', 'ups-woocommerce-shipping'),
		'type'				=> 'checkbox',
		'default'			=> 'no',
		'class'				=> 'ph_ups_advanced_settings_tab'
	),
	'fixed_product_price'	=> array(
		'title'				=> __('Default Product Price', 'ups-woocommerce-shipping'),
		'description'		=> __('<small>For products that are free or have a zero cost, please enter a non-zero default price here. Shipping Carriers like UPS require a non-zero value.<br>
		A non-zero product price is required to generate labels & meet the Customs compliance by displaying the price on the Commercial Invoice in case of exports.</small>', 'ups-woocommerce-shipping'),
		'type'				=> 'number',
		'custom_attributes' => array('step' => 'any', 'min' => '0'),
		'default'			=> 1,
		'class'				=> 'ph_ups_advanced_settings_tab'
	),

	// legacy Cradential Settings
	'ph_legacy_section'     => array(
		'title' => __('UPS Access Keys (Legacy) ', 'ups-woocommerce-shipping'),
		'type'  => 'title',
		'class' => 'ph_ups_advanced_settings_tab ph_ups_legacy_tab',
		'description'	=> __('This section allows existing customers to locate their older UPS Access Keys, which are no longer provided by UPS due to the implementation of the OAuth 2.0.<br><br> <b>This legacy option will be removed in future releases of the plugin. We strongly encourage all users to migrate to the OAuth 2.0 authentication method as soon as possible.</b>', 'ups-woocommerce-shipping'),
	),
	'user_id'		=> array(
		'title'			=> __('UPS User ID', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'description'	=> __('Obtained from UPS after getting an account.', 'ups-woocommerce-shipping'),
		'default'		=> '',
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_advanced_settings_tab',
	),
	'password'		=> array(
		'title'			=> __('UPS Password', 'ups-woocommerce-shipping'),
		'type'			=> 'password',
		'description'	=> __('Obtained from UPS after getting an account.', 'ups-woocommerce-shipping'),
		'default'		=> '',
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_advanced_settings_tab',
	),
	'access_key'		=> array(
		'title'			=> __('UPS Access Key', 'ups-woocommerce-shipping'),
		'type'			=> 'password',
		'description'	=> __('Obtained from UPS after getting an account.', 'ups-woocommerce-shipping'),
		'default'		=> '',
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_advanced_settings_tab',
	),
	'shipper_number' => array(
		'title'			=> __('UPS Account Number', 'ups-woocommerce-shipping'),
		'type'			=> 'text',
		'description'	=> __('Obtained from UPS after getting an account.', 'ups-woocommerce-shipping'),
		'default'		=> '',
		'desc_tip'		=> true,
		'class'			=> 'ph_ups_advanced_settings_tab',
	),
	'client_credentials'     => array(
		'title'         => __('Client Credentials', 'ups-woocommerce-shipping'),
		'type'          => 'hidden',
		'default'       => '',
		'class'         => 'ph_ups_advanced_settings_tab',
	),
	'client_license_hash'       => array(
		'title'         => __('Client License Hash', 'ups-woocommerce-shipping'),
		'type'          => 'hidden',
		'default'       => '',
		'class'         => 'ph_ups_advanced_settings_tab',
	),
);

// Help & Support
$help_and_support_settings = array(

	'help_and_support'  => array(
		'type'			=> 'help_support_section'
	),
);

// Checking if settings fields are requested from UPS Global or UPS Zone section.
if( isset( $this->instance_id ) && !empty( $this->instance_id )  ) {

	$ups_settings = array(

		'title' 		=> array(
			'title'			=> __('UPS Method Title', 'ups-woocommerce-shipping'),
			'type'			=> 'text',
			'default'		=> __('UPS Shipping', 'ups-woocommerce-shipping'),
		),
		'services_packaging'  => array(
			'title'		   => __('Services', 'ups-woocommerce-shipping'),
			'type'			=> 'title',
			'description'	 => '',
		),
		'services'			=> array(
			'type'			=> 'services'
		),
		'shipping_method_instance_id'	=> array(
			'title'			=>	__( "Add Instance ID to Shipping Method", 'ups-woocommerce-shipping' ),
			'label'   		=> __( "Enable", 'ups-woocommerce-shipping' ),
			'type'			=>	'checkbox',
			'default'		=>	'no',
			'description'	=>	__( "Enable this option to add Instance ID to the shipping method while placing an order", 'ups-woocommerce-shipping'),
			'desc_tip'		=> true,
		),
	);

} else {

	// Unsetting Display Additional Taxes & Charges on cart page and Custom Text for Additional Taxes & Charges for REST.
	if (Ph_UPS_Woo_Shipping_Common::ph_is_oauth_registered_customer()) {

		$rates_and_services_settings = PH_WC_UPS_Common_Utils::unset_settings_fields( $rates_and_services_settings, array(
			'ups_tradability',
			'tradability_cart_title'
		));
	}

	$ups_settings = array_merge(
	
		$general_settings,
		$rates_and_services_settings,
		$shipping_labels_settings,
		$international_forms_settings,
		$special_services_settings,
		$packaging_settings,
		$freight_settings,
		$pickup_settings,
		$advanced_settings,
		$help_and_support_settings
	);

}

return $ups_settings;