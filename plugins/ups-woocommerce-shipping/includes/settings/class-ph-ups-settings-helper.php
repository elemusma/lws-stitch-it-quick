<?php
/**
 * Settings helper for WooCommerce UPS Shipping Plugin with Print label.
 *
 * @package ups-woocommerce-shipping
 */

defined( 'ABSPATH' ) || exit;

/**
 * PH_WC_UPS_Settings_Helper Class
 */
class PH_WC_UPS_Settings_Helper {

	/**
	 * Plugin settings
	 *
	 * @var array
	 */
	public $settings;

	/**
	 * PH_WC_UPS_Settings_Helper construct
	 */
	public function __construct( $instance_id = '' ) {
		
		if( empty( $instance_id ) ) {

			$settings 			= get_option( 'woocommerce_' . WF_UPS_ID . '_settings', null );
			$ups_configuration 	= ph_wc_ups_plugin_configuration();
		} else {

			$settings			= get_option( 'woocommerce_'. PH_WC_UPS_ZONE_SHIPPING .'_'. $instance_id.'_settings', null );
			$ups_configuration 	= ph_wc_ups_shipping_zone_method_configuration();
		}

		// UPS settings specifically zones.
		if( !empty($instance_id) ) {

			$ups_zone_settings = array(

				'title'							=> isset($settings['title']) && !empty($settings['title']) ? $settings['title'] : '',
				'services' 						=> ( isset($settings['services']) && !empty($settings['services']) ) ? $settings['services'] : array(),				
				'shipping_method_instance_id'	=> ( isset( $settings['shipping_method_instance_id'] ) && ! empty( $settings['shipping_method_instance_id'] ) && 'yes' === $settings['shipping_method_instance_id'] ) ? true : false,
			);

			$this->settings = $ups_zone_settings;
			
		} else {
		
			// Will Check Old Settings 'include_order_id' and Based on that it will set default for 'order_id_or_number_in_label' 
			$default_order_id_or_number			= (isset($settings['include_order_id']) && !empty($settings['include_order_id']) && 'yes' === $settings['include_order_id'] ) ? 'include_order_number' : '';
			$default_invoice_commodity_value	= ( isset($settings['discounted_price']) && !empty($settings['discounted_price']) && 'yes' === $settings['discounted_price'] ) ? 'discount_price' : 'product_price';

			$ups_settings = array(

				'soap_available' 					=> Ph_UPS_Woo_Shipping_Common::is_soap_available() ? true : false,

				// General Settings.
				'api_mode'	  						=> isset($settings['api_mode']) ? $settings['api_mode'] : 'Test',
				'user_id'		 					=> isset($settings['user_id']) ? $settings['user_id'] : '',
				'password'							=> isset($settings['password']) ? $settings['password'] : '',
				'access_key'	  					=> isset($settings['access_key']) ? $settings['access_key'] : '',
				'shipper_number'  					=> isset($settings['shipper_number']) && !empty($settings['shipper_number']) ? $settings['shipper_number'] : '',
				'debug'	  							=> isset($settings['debug']) && 'yes' === $settings['debug'] ? true : false,
				'silent_debug'						=> isset($settings['silent_debug']) && 'yes' === $settings['silent_debug'] ? true : false,
				'units'								=> isset($settings['units']) ? $settings['units'] : 'imperial',
				'negotiated'	  					=> isset($settings['negotiated']) && 'yes' === $settings['negotiated'] ? true : false,
				'residential'						=> isset($settings['residential']) && 'yes' === $settings['residential'] ? true : false,
				'address_validation'				=> (isset($settings['address_validation']) && 'yes' === $settings['address_validation']) ? true : false,
				'suggested_address'					=> (isset($settings['suggested_address']) && !empty($settings['suggested_address']) && 'yes' === $settings['suggested_address']) ? true : false,
				'suggested_display'					=> (isset($settings['suggested_display']) && !empty($settings['suggested_display']) && $settings['suggested_display'] == 'suggested_radio') ? 'suggested_radio' : 'suggested_notice',
				'insuredvalue' 						=> isset($settings['insuredvalue']) && 'yes' === $settings['insuredvalue'] ? true : false,
				'min_order_amount_for_insurance' 	=> !empty($settings['min_order_amount_for_insurance']) ? $settings['min_order_amount_for_insurance'] : 0,
				'ship_from_address'	  				=> isset($settings['ship_from_address']) ? $settings['ship_from_address'] : 'origin_address',
				'ups_user_name'						=> isset($settings['ups_user_name']) ? $settings['ups_user_name'] : '',
				'ups_display_name'					=> isset($settings['ups_display_name']) ? $settings['ups_display_name'] : '',
				'origin_addressline' 				=> isset($settings['origin_addressline']) ? $settings['origin_addressline'] : '',
				'origin_addressline_2' 				=> isset($settings['origin_addressline_2']) ? $settings['origin_addressline_2'] : '',
				'origin_city' 						=> isset($settings['origin_city']) ? $settings['origin_city'] : '',
				'origin_country_state' 				=> isset($settings['origin_country_state']) ? $settings['origin_country_state'] : '',
				'origin_custom_state'				=> isset($settings['origin_custom_state']) ? $settings['origin_custom_state'] : '',
				'origin_postcode' 					=> isset($settings['origin_postcode']) ? $settings['origin_postcode'] : '',
				'origin_country' 					=> PH_WC_UPS_Common_Utils::ph_get_origin_country_and_state( $settings, 'country' ),
				'origin_state'   					=> PH_WC_UPS_Common_Utils::ph_get_origin_country_and_state( $settings, 'state' ),
				'phone_number' 						=> isset($settings['phone_number']) ? $settings['phone_number'] : '',
				'email'								=> isset($settings['email']) ? $settings['email'] : '',		
				
				// General Settings - Different Ship From Address.
				'ship_from_address_different_from_shipper' => (isset($settings['ship_from_address_different_from_shipper']) && !empty($settings['ship_from_address_different_from_shipper']) && 'yes' === $settings['ship_from_address_different_from_shipper']) ? true : false,
				'ship_from_addressline'				=> !empty($settings['ship_from_addressline']) ? $settings['ship_from_addressline'] : null,
				'ship_from_addressline_2'			=> isset($settings['ship_from_addressline_2']) ? $settings['ship_from_addressline_2'] : null,
				'ship_from_city'					=> !empty($settings['ship_from_city']) ? $settings['ship_from_city'] : null,
				'ship_from_country_state'			=> !empty($settings['ship_from_country_state']) ? $settings['ship_from_country_state'] : null,
				'ship_from_custom_state'			=> !empty($settings['ship_from_custom_state']) ? $settings['ship_from_custom_state'] : null,
				'ship_from_postcode' 				=> !empty($settings['ship_from_postcode']) ? $settings['ship_from_postcode'] : null,

				// General Settings - Billing Address as Shipper Address.
				'billing_address_as_shipper' 		=> (isset($settings['billing_address_as_shipper']) && !empty($settings['billing_address_as_shipper']) && 'yes' === $settings['billing_address_as_shipper']) ? true : false,
				
				'skip_products' 					=> !empty($settings['skip_products']) ? $settings['skip_products'] : array(),
				'xa_show_all'						=> (isset($settings['xa_show_all']) && 'yes' === $settings['xa_show_all']) ? true : false,
				'remove_recipients_phno'			=> (isset($settings['remove_recipients_phno']) && !empty($settings['remove_recipients_phno']) && 'yes' === $settings['remove_recipients_phno']) ? true : false,
				'shipper_release_indicator' 		=> (isset($settings['shipper_release_indicator']) && !empty($settings['shipper_release_indicator']) && 'yes' === $settings['shipper_release_indicator']) ? true : false,
				
				// Rates & Services Settings.
				
				'enabled'							=> (isset($settings['enabled']) && !empty($settings['enabled']) && 'yes' === $settings['enabled']) ? true : false,
				'title'								=> isset($settings['title']) ? $settings['title'] : $ups_configuration['method_title'],
				'cheapest_rate_title'				=> isset($settings['title']) ? $settings['title'] : null,
				'availability'						=> isset($settings['availability']) ? $settings['availability'] : 'all',
				'countries'	   						=> isset($settings['countries']) ? $settings['countries'] : array(),
				'enable_estimated_delivery'			=> (isset($settings['enable_estimated_delivery']) && 'yes' === $settings['enable_estimated_delivery']) ? true : false,
				'estimated_delivery_text'			=> !empty($settings['estimated_delivery_text']) ? $settings['estimated_delivery_text'] : null,
				'cut_off_time'						=> (isset($settings['cut_off_time']) && !empty($settings['cut_off_time'])) ? $settings['cut_off_time'] : '24:00',
				'shipTimeAdjustment' 				=> ( isset($settings['ship_time_adjustment']) && !empty($settings['ship_time_adjustment'])) ? $settings['ship_time_adjustment'] : '',
				'rate_caching' 						=> ( isset($settings['ups_rate_caching']) && !empty($settings['ups_rate_caching'])) ? $settings['ups_rate_caching'] : '24',
				'pickup'							=> isset($settings['pickup']) ? $settings['pickup'] : '01',
				'customer_classification' 			=> isset($settings['customer_classification']) ? $settings['customer_classification'] : '99',
				'email_notification'   				=> isset($settings['email_notification']) ? $settings['email_notification'] : array(),
				'email_notification_code'  			=> isset($settings['email_notification_code']) && !empty($settings['email_notification_code']) ? $settings['email_notification_code'] : array(),
				'tax_indicator'	  					=> isset($settings['tax_indicator']) && 'yes' === $settings['tax_indicator'] ? true : false,
				'ups_tradability' 					=> (( isset($settings['ups_tradability']) && !empty( $settings['ups_tradability'])) && 'yes' === $settings['ups_tradability']) ? true : false,
				'tradability_cart_title'			=> isset($settings['tradability_cart_title']) && !empty($settings['tradability_cart_title']) ? $settings['tradability_cart_title'] : 'Additional Taxes & Charges',
				'accesspoint_locator' 				=> (isset($settings['accesspoint_locator']) && 'yes' === $settings['accesspoint_locator']) ? true : false,
				'accesspoint_req_option'			=> (isset($settings['accesspoint_req_option']) && !empty($settings['accesspoint_req_option'])) ? $settings['accesspoint_req_option'] : '1',
				'accesspoint_max_limit'				=> (isset($settings['accesspoint_max_limit']) && !empty($settings['accesspoint_max_limit'])) ? $settings['accesspoint_max_limit'] : '10',
				'accesspoint_option_code'   		=> (isset($settings['accesspoint_option_code']) && !empty($settings['accesspoint_option_code'])) ? $settings['accesspoint_option_code'] : array('018'),
				'tin_number' 						=> isset($settings['tin_number']) ?  $settings['tin_number'] : '',
				'recipients_tin' 					=> (isset($settings['recipients_tin']) && !empty($settings['recipients_tin']) && 'yes' === $settings['recipients_tin']) ? true : false,
				'offer_rates'	 					=> isset($settings['offer_rates']) ? $settings['offer_rates'] : 'all',
				'fallback'		   					=> !empty($settings['fallback']) ? $settings['fallback'] : '',
				'currency_type'						=> !empty($settings['currency_type']) ? $settings['currency_type'] : get_woocommerce_currency(),
				'conversion_rate'					=> !empty($settings['conversion_rate']) ? $settings['conversion_rate'] : 1,
				'min_amount'	  			 		=> isset($settings['min_amount']) ? $settings['min_amount'] : 0,
				'min_weight_limit' 					=> !empty($settings['min_weight_limit']) ? (float) $settings['min_weight_limit'] : null,
				'max_weight_limit'					=> !empty($settings['max_weight_limit']) ? (float) $settings['max_weight_limit'] : null,
				'services' 							=> (isset($settings['services']) && !empty($settings['services'])) ? $settings['services'] : array(),

				// Shipping Labels Settings.
				'disble_ups_print_label'			=> isset($settings['disble_ups_print_label']) ? $settings['disble_ups_print_label'] : '',
				'print_label_type'	  				=> isset($settings['print_label_type']) ? $settings['print_label_type'] : 'gif',
				'label_format'						=> isset($settings['label_format']) && !empty($settings['label_format']) ? $settings['label_format'] : null,
				'show_label_in_browser'				=> (isset($settings['show_label_in_browser']) && !empty($settings['show_label_in_browser']) && 'yes' === $settings['show_label_in_browser']) ? true : false,
				'transportation' 					=> (isset($settings['transportation']) && !empty($settings['transportation'])) ? $settings['transportation']  : 'shipper',
				'transport_payor_post_code' 		=> (isset($settings['transport_payor_post_code']) && !empty($settings['transport_payor_post_code'])) ? $settings['transport_payor_post_code'] : '',
				'transport_payor_country_code' 		=> (isset($settings['transport_payor_country_code']) && !empty($settings['transport_payor_country_code'])) ? $settings['transport_payor_country_code'] : '',
				'transport_payor_acc_no' 			=> (isset($settings['transport_payor_acc_no']) && !empty($settings['transport_payor_acc_no'])) ? $settings['transport_payor_acc_no'] : '',
				'customandduties' 					=> (isset($settings['duties_and_taxes']) && !empty($settings['duties_and_taxes'])) ? $settings['duties_and_taxes']  : 'receiver',
				
				// Shipping Labels Settings - Third Party Duties And Taxes Payer Options.
				'customandduties_ac_num' 			=> isset( $settings['duties_and_taxes']) && ('third_party' === $settings['duties_and_taxes'] && isset($settings['shipping_payor_acc_no']) && !empty($settings['shipping_payor_acc_no'])) ? $settings['shipping_payor_acc_no'] : '',
				'customandduties_pcode' 			=> isset( $settings['duties_and_taxes']) && ('third_party' === $settings['duties_and_taxes'] && isset($settings['shipping_payor_post_code']) && !empty($settings['shipping_payor_post_code'])) ? $settings['shipping_payor_post_code'] : '',
				'customandduties_ccode' 			=> isset( $settings['duties_and_taxes']) && ('third_party' === $settings['duties_and_taxes'] && isset($settings['shipping_payor_country_code']) && !empty($settings['shipping_payor_country_code'])) ? $settings['shipping_payor_country_code'] : '',

				'dangerous_goods_manifest' 			=> (isset($settings['dangerous_goods_manifest']) && !empty($settings['dangerous_goods_manifest']) && 'yes' === $settings['dangerous_goods_manifest']) ? true : false,
				'dangerous_goods_signatoryinfo'		=> isset($settings['dangerous_goods_signatoryinfo']) && !empty($settings['dangerous_goods_signatoryinfo']) && 'yes' === $settings['dangerous_goods_signatoryinfo'] ? true : false,
				'mail_innovation_type'  			=> (isset($settings['mail_innovation_type']) && !empty($settings['mail_innovation_type'])) ? $settings['mail_innovation_type']  : '66',
				'usps_endorsement' 					=> (isset($settings['usps_endorsement']) && !empty($settings['usps_endorsement'])) ? $settings['usps_endorsement']  : '5',
				'enable_latin_encoding'				=> isset($settings['latin_encoding']) ? 'yes' === $settings['latin_encoding'] : false,
				'custom_message'					=> (isset($settings['custom_message']) && !empty($settings['custom_message'])) ? $settings['custom_message'] : '',
				'custom_tracking'					=> (isset($settings['custom_tracking']) && !empty($settings['custom_tracking']) && 'yes' === $settings['custom_tracking']) ? true : false,
				'custom_tracking_url'				=> (isset($settings['custom_tracking_url']) && !empty($settings['custom_tracking_url'])) ? $settings['custom_tracking_url'] : '',
				'disble_shipment_tracking'			=> isset($settings['disble_shipment_tracking']) ? $settings['disble_shipment_tracking'] : 'TrueForCustomer',
				'automate_package_generation'		=> (isset($settings['automate_package_generation']) &&  'yes' === $settings['automate_package_generation'] ) ? 'yes' : 'no',
				'automate_label_generation'			=> (isset($settings['automate_label_generation']) &&  'yes' === $settings['automate_label_generation'] ) ? 'yes' : 'no',
				'automate_label_trigger'			=> isset($settings['automate_label_trigger']) && !empty($settings['automate_label_trigger']) ? $settings['automate_label_trigger'] : 'thankyou_page',
				'default_dom_service'				=> isset($settings['default_dom_service']) && !empty($settings['default_dom_service']) ? $settings['default_dom_service'] : '',
				'default_int_service'				=> isset($settings['default_int_service']) && !empty($settings['default_int_service']) ? $settings['default_int_service'] : '',
				'allow_label_btn_on_myaccount'		=> (isset($settings['allow_label_btn_on_myaccount']) && !empty($settings['allow_label_btn_on_myaccount']) && 'yes' === $settings['allow_label_btn_on_myaccount']) ? true : false,
				'carbonneutral_indicator' 			=> (isset($settings['carbonneutral_indicator']) && !empty($settings['carbonneutral_indicator']) && 'yes' === $settings['carbonneutral_indicator']) ? true : false,
				'remove_special_char_product'		=> (isset($settings['remove_special_char_product']) && !empty($settings['remove_special_char_product']) && 'yes' === $settings['remove_special_char_product']) ? true : false,
				'label_description' 				=> (isset($settings['label_description']) && !empty($settings['label_description'])) ? $settings['label_description'] : 'product_category',
				'label_custom_description' 			=> (isset($settings['label_custom_description']) && !empty($settings['label_custom_description'])) ? $settings['label_custom_description'] : '',
				'order_id_or_number_in_label' 		=> isset($settings['order_id_or_number_in_label']) ?  $settings['order_id_or_number_in_label'] : $default_order_id_or_number,
				'add_product_sku'					=> (isset($settings['add_product_sku']) && !empty($settings['add_product_sku']) && 'yes' === $settings['add_product_sku']) ? true : false,
				'include_in_commercial_invoice'		=> (isset($settings['include_in_commercial_invoice']) && !empty($settings['include_in_commercial_invoice']) && 'yes' === $settings['include_in_commercial_invoice']) ? true : false,
				'auto_email_label'					=> (isset($settings['auto_email_label']) && !empty($settings['auto_email_label'])) ? $settings['auto_email_label'] : array(),
				'email_recipients'					=> isset($settings['email_recipients']) && !empty($settings['email_recipients']) ? $settings['email_recipients'] : '', 	
				'email_subject'						=> isset($settings['email_subject']) && !empty($settings['email_subject']) ? $settings['email_subject'] : '',
				'email_content'						=> isset( $settings['email_content'] ) && ! empty( $settings['email_content'] ) ? $settings['email_content'] : '',

				// International Forms Settings.
				'commercial_invoice'				=> isset($settings['commercial_invoice']) && !empty($settings['commercial_invoice']) && 'yes' === $settings['commercial_invoice'] ? true : false,
				'shippingAddressAsSoldTo' 			=> isset($settings['sold_to_address']) && !empty($settings['sold_to_address']) && 'yes' === $settings['sold_to_address'] ? true : false,
				'invoice_unit_of_measure' 			=> isset($settings['invoice_unit_of_measure']) ? $settings['invoice_unit_of_measure'] : 'EA',
				'discounted_price'					=> (isset($settings['discounted_price']) && !empty($settings['discounted_price']) && 'yes' === $settings['discounted_price']) ? true : false,
				'invoice_commodity_value' 			=> isset($settings['invoice_commodity_value']) ? $settings['invoice_commodity_value'] : ( $default_invoice_commodity_value ? 'discount_price' : ''),
				'commercial_invoice_shipping'		=> (isset($settings['commercial_invoice_shipping']) && !empty($settings['commercial_invoice_shipping']) && 'yes' === $settings['commercial_invoice_shipping']) ? true : false,
				'declaration_statement'				=> isset($settings['declaration_statement']) && !empty($settings['declaration_statement']) ? $settings['declaration_statement'] : '',
				'terms_of_shipment' 				=> isset($settings['terms_of_shipment']) && !empty($settings['terms_of_shipment']) ?  $settings['terms_of_shipment'] : '',
				'reason_export' 					=> isset($settings['reason_export']) ?  $settings['reason_export'] : '',
				'return_reason_export' 				=> isset($settings['return_reason_export']) && !empty($settings['return_reason_export']) ?  $settings['return_reason_export'] : 'RETURN',
				'edi_on_label'						=> (isset($settings['edi_on_label']) && !empty($settings['edi_on_label']) && 'yes' === $settings['edi_on_label']) ? true : false,
				'nafta_co_form'						=> (isset($settings['nafta_co_form']) && !empty($settings['nafta_co_form']) && 'yes' === $settings['nafta_co_form']) ? true : false,
				'nafta_producer_option'				=> (isset($settings['nafta_producer_option']) && !empty($settings['nafta_producer_option'])) ? $settings['nafta_producer_option'] : '02',
				'blanket_begin_period'				=> (isset($settings['blanket_begin_period']) && !empty($settings['blanket_begin_period'])) ? $settings['blanket_begin_period'] : '',
				'blanket_end_period'				=> (isset($settings['blanket_end_period']) && !empty($settings['blanket_end_period'])) ? $settings['blanket_end_period'] : '',
				'eei_data'							=> (isset($settings['eei_data']) && !empty($settings['eei_data']) && 'yes' === $settings['eei_data']) ? true : false,
				'eei_shipper_filed_option'			=> (isset($settings['eei_shipper_filed_option']) && !empty($settings['eei_shipper_filed_option'])) ? $settings['eei_shipper_filed_option'] : '',
				'eei_pre_departure_itn_number'		=> (isset($settings['eei_pre_departure_itn_number']) && !empty($settings['eei_pre_departure_itn_number'])) ? $settings['eei_pre_departure_itn_number'] : '',
				'eei_exemption_legend'				=> (isset($settings['eei_exemption_legend']) && !empty($settings['eei_exemption_legend'])) ? $settings['eei_exemption_legend'] : '',
				'eei_mode_of_transport'				=> (isset($settings['eei_mode_of_transport']) && !empty($settings['eei_mode_of_transport'])) ? $settings['eei_mode_of_transport'] : '',
				'eei_parties_to_transaction'		=> (isset($settings['eei_parties_to_transaction']) && !empty($settings['eei_parties_to_transaction'])) ? $settings['eei_parties_to_transaction'] : '',
				'eei_ultimate_consignee_code'		=> (isset($settings['eei_ultimate_consignee_code']) && !empty($settings['eei_ultimate_consignee_code'])) ? $settings['eei_ultimate_consignee_code'] : '',
				'vendorInfo' 			 			=> (isset($settings['vendor_info']) && 'yes' === $settings['vendor_info']) ? true : false,


				// Special Services Settings.		
				'import_control_settings' 			=> (isset($settings['import_control_settings']) && !empty($settings['import_control_settings'])) ? $settings['import_control_settings'] : '',
				'saturday_delivery' 				=> isset($settings['saturday_delivery']) && !empty($settings['saturday_delivery']) ? $settings['saturday_delivery'] : '',
				'cod' 								=> false,
				'cod_total' 						=> 0,
				'cod_enable' 						=> (isset($settings['cod_enable']) && !empty($settings['cod_enable']) && 'yes' === $settings['cod_enable']) ? true : false,
				'eu_country_cod_type' 				=> isset($settings['eu_country_cod_type']) && !empty($settings['eu_country_cod_type']) ? $settings['eu_country_cod_type'] : 9,
				'ph_delivery_confirmation' 			=> isset($settings['ph_delivery_confirmation']) && !empty($settings['ph_delivery_confirmation']) ? $settings['ph_delivery_confirmation'] : 0,
				'upsSimpleRate'						=> isset($settings['ups_simple_rate']) && 'yes' === $settings['ups_simple_rate'] ? true : false,
				'isc' 								=> ( isset( $settings['international_special_commodities']) && !empty($settings['international_special_commodities']) ) && 'yes' === $settings['international_special_commodities']  ? true : false,
				'ph_restricted_article' 			=> ((isset($settings['ph_ups_restricted_article']) && 'yes' === $settings['ph_ups_restricted_article'] ) ? true : false),
				'ph_diog' 	 						=> ((isset($settings['ph_ups_diog']) &&  'yes' === $settings['ph_ups_diog'] ) ? 'yes' : 'no'),
				'ph_alcoholic' 	 					=> ((isset($settings['ph_ups_alcoholic']) &&  'yes' === $settings['ph_ups_alcoholic'] ) ? 'yes' : 'no'),
				'ph_perishable' 	 				=> ((isset($settings['ph_ups_perishable']) &&  'yes' === $settings['ph_ups_perishable'] ) ? 'yes' : 'no'),
				'ph_plantsindicator' 				=> ((isset($settings['ph_ups_plantsindicator']) &&  'yes' === $settings['ph_ups_plantsindicator'] ) ? 'yes' : 'no'),
				'ph_seedsindicator' 	 			=> ((isset($settings['ph_ups_seedsindicator']) &&  'yes' === $settings['ph_ups_seedsindicator'] ) ? 'yes' : 'no'),
				'ph_specialindicator' 	 			=> ((isset($settings['ph_ups_specialindicator']) &&  'yes' === $settings['ph_ups_specialindicator'] ) ? 'yes' : 'no'),
				'ph_tobaccoindicator' 	 			=> ((isset($settings['ph_ups_tobaccoindicator']) &&  'yes' === $settings['ph_ups_tobaccoindicator'] ) ? 'yes' : 'no'),
				'ph_ups_refrigeration'				=> ((isset($settings['ph_ups_refrigeration']) &&  'yes' === $settings['ph_ups_refrigeration'] ) ? 'yes' : 'no'),
				'ph_ups_clinicaltrials'				=> (isset($settings['ph_ups_clinicaltrials']) && !empty($settings['ph_ups_clinicaltrials'])) ? $settings['ph_ups_clinicaltrials'] : '',

				// Packaging Settings.
				'packing_method'  					=> isset($settings['packing_method']) ? $settings['packing_method'] : 'per_item',
				'mode' 								=> isset($settings['packing_algorithm']) ? $settings['packing_algorithm'] : 'volume_based',
				'exclude_box_weight' 				=> (isset($settings['exclude_box_weight']) && 'yes' === $settings['exclude_box_weight'] ) ? true : false,
				'stack_to_volume' 					=> (isset($settings['stack_to_volume']) && 'yes' === $settings['stack_to_volume'] ) ? true : false,
				'box_max_weight'					=> 	( isset($settings['box_max_weight']) && !empty($settings['box_max_weight'])) ? $settings['box_max_weight'] : '10',
				'weight_packing_process'			=>	( isset($settings['weight_packing_process']) && !empty($settings['weight_packing_process'])) ? $settings['weight_packing_process'] : 'pack_descending',
				'boxes'		   						=> isset($settings['boxes']) ? $settings['boxes'] : array(),
				'enable_density_based_rating' 		=> (isset($settings['enable_density_based_rating']) && 'yes' === $settings['enable_density_based_rating'] ) ? true : false,
				'density_length' 					=> (isset($settings['density_length']) && !empty($settings['density_length'])) ? $settings['density_length'] : 0,
				'density_width' 					=> (isset($settings['density_width']) && !empty($settings['density_width'])) ? $settings['density_width'] : 0,
				'density_height' 					=> (isset($settings['density_height']) && !empty($settings['density_height'])) ? $settings['density_height'] : 0,

				// Advanced Settings.
				'fixedProductPrice'					=> ( isset($settings['fixed_product_price']) && !empty($settings['fixed_product_price'])) ? $settings['fixed_product_price'] : 1,	
				
				// Unknown settings
				'ups_packaging'						=> isset($settings['ups_packaging']) ? $settings['ups_packaging'] : array(),
				'service_code'						=> '',

				//Pickup Settings.
				'pickup_enabled'					=> (isset($settings['pickup_enabled']) && !empty($settings['pickup_enabled']) && 'yes' === $settings['pickup_enabled']) ? true : false,
				'pickup_start_time'					=> (isset($settings['pickup_start_time']) && !empty($settings['pickup_start_time'])) ? $settings['pickup_start_time'] : 8,
				'pickup_close_time'					=> (isset($settings['pickup_close_time']) && !empty($settings['pickup_close_time'])) ? $settings['pickup_close_time'] : 18,
				'pickup_date'						=> (isset($settings['pickup_date']) && !empty($settings['pickup_date'])) ? $settings['pickup_date'] : 'current',
				'working_days'						=> (isset($settings['working_days']) && !empty($settings['working_days'])) ? $settings['working_days'] : array(),
			);

			if ($ups_settings['enable_estimated_delivery']) {
				if (empty($ups_settings['current_wp_time'])) {
					$current_time 			= current_time('Y-m-d H:i:s');
					$current_wp_time 		= date_create($current_time);
				}
				if (empty($ups_settings['wp_date_time_format'])) {
					$wp_date_time_format 	= Ph_UPS_Woo_Shipping_Common::get_wordpress_date_format() . ' ' . Ph_UPS_Woo_Shipping_Common::get_wordpress_time_format();
				}
			}

			if (empty($ups_settings['ship_from_country_state'])) {
				$ship_from_country = $ups_settings['origin_country_state'];		// By Default Origin Country
				$ship_from_state   = $ups_settings['origin_state'];				// By Default Origin State
			} else {
				if (strstr($ups_settings['ship_from_country_state'], ':')) :
					list($ship_from_country, $ship_from_state) = explode(':', $ups_settings['ship_from_country_state']);
				else :
					$ship_from_country = $ups_settings['ship_from_country_state'];
					$ship_from_state   = '';
				endif;
			}

			if (!empty($ups_settings['conversion_rate'])) {
				$rate_conversion					= $ups_settings['conversion_rate']; // For Returned Rate Conversion to Default Currency 
				$ups_settings['conversion_rate']	= apply_filters('ph_ups_currency_conversion_rate', $ups_settings['conversion_rate'], $ups_settings['currency_type']);   // Multicurrency
			}

			if ('metric' === $ups_settings['units'] ) {
				$weight_unit 		= 'KGS';
				$dim_unit			= 'CM';
				$simpleRateBoxes	= PH_WC_UPS_Constants::UPS_SIMPLE_RATE_BOXES_IN_CMS;
				$packaging 			= PH_WC_UPS_Constants::UPS_DEFAULT_BOXES_IN_CMS;
			
			} else {
				$weight_unit 		= 'LBS';
				$dim_unit			= 'IN';
				$simpleRateBoxes 	= PH_WC_UPS_Constants::UPS_SIMPLE_RATE_BOXES_IN_INCHES;
				$packaging 			= PH_WC_UPS_Constants::UPS_DEFAULT_BOXES_IN_INCHES;
			}

			$ups_additional_settings = array(

				'current_wp_time'					=> isset( $current_wp_time ) ? $current_wp_time : '',
				'wp_date_time_format'				=> isset( $wp_date_time_format) ? $wp_date_time_format : '',
				'ship_from_country'					=> isset( $ship_from_country ) ? $ship_from_country : '',
				'ship_from_state'					=> isset( $ship_from_state ) ? $ship_from_state : '',
				'ship_from_custom_state'  			=> !empty( $settings['ship_from_custom_state']) ? $settings['ship_from_custom_state'] : $ship_from_state,
				'rate_conversion'					=> isset( $rate_conversion ) ? $rate_conversion : '',
				'weight_unit'						=> isset( $weight_unit ) ? $weight_unit : '',
				'dim_unit'							=> isset( $dim_unit ) ? $dim_unit : '',
				'uom' 								=> ( 'imperial' === $ups_settings['units'] ) ? 'LB' : 'KG',
				'simpleRateBoxes'					=> isset( $simpleRateBoxes ) ? $simpleRateBoxes : '',
				'packaging'							=> isset( $packaging ) ? $packaging : '',
			);

			$this->settings = array_merge( $ups_settings, $ups_additional_settings );
		}
	}
}