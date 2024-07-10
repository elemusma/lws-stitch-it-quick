/********************************************************************************************************************************************/
/********************************************************* General Settings *****************************************************************/
/********************************************************************************************************************************************/

jQuery(document).ready(function () {

	/*************************************************** Services Drag and Drop Feature **************************************************************/

	// Services Ordering
	jQuery('.ups_services tbody').sortable({
		items: 'tr',
		cursor: 'move',
		axis: 'y',
		handle: '.sort',
		scrollSensitivity: 40,
		forcePlaceholderSize: true,
		helper: 'clone',
		opacity: 0.65,
		placeholder: 'wc-metabox-sortable-placeholder',
		start: function(event, ui) {
			ui.item.css('baclbsround-color', '#f6f6f6');
		},
		stop: function(event, ui) {
			ui.item.removeAttr('style');
			ups_services_row_indexes();
		}
	});

	/*************************************************** Order Level Settings **************************************************************/

	jQuery("#generate_return_label").click(function () {
		service = jQuery("#return_label_service").val();
		if (service == '') {
			alert("Select service");
			return false;
		}
		else {
			href = jQuery(this).attr('href');
			href = href + "&return_label_service=" + service;
			jQuery(this).attr('href', href);
		}
		return true;
	});

	jQuery('#ph_ups_choose_upload_document').on('click', function (event) {

		orderId = jQuery('#order_id').val();
		docType = jQuery('#ph_user_created_form_doc_type').val();

		file_frame = wp.media.frames.file_frame = wp.media({
			title: 'Select a Document to Upload',
			button: {
				text: 'Use this document',
			},
			multiple: false
		});

		file_frame.on('select', function () {

			// Make the div elements unclickable and show loading icon
			jQuery("#PhUpsDocUploadMetabox").css("pointer-events", "none");
			jQuery("#PhUpsDocUploadMetabox").css("opacity", '0.5');
			jQuery("#ph-loading-spinner").addClass("spinner");
			jQuery("#ph-loading-spinner").addClass("is-active");

			//For single document selection
			attachment = file_frame.state().get('selection').first().toJSON();

			var data = {
				'action': 'ph_ups_upload_document',
				'attachment': attachment,
				'docType': docType,
				'orderId': orderId
			};

			jQuery.post(ajaxurl, data, function (response) {

				response = JSON.parse(response);

				if (response.success == true) {

					jQuery("#ph-loading-spinner").removeClass("spinner, is-active");
					location.reload();

				} else {

					jQuery("#ph-loading-spinner").removeClass("spinner, is-active");
					location.reload();

				}
			});
		});

		file_frame.open();
	});

	// Bulk Action
	jQuery("#doaction, #doaction2").click(function() {

		selected = jQuery(this).closest("form").find("select[name^='action']").val();

		if ( selected == 'ups_generate_label' ) {

			phUPSDisableClick();		
		}
	});

	// Migration Banner
	jQuery('.ph-ups-view-progress').on('click', function (event) {
		
		jQuery(".ph-ups-progress-details").toggle();
		jQuery(".ph-ups-view-symbol").toggleClass("ph-ups-view-symbol-toggle");
	});


	jQuery('.ph-ups-close-migration-banner').off('click').on('click', function(e) {

		var data = {
			'action': 'ph_ups_closing_migration_banner',
		};
		
		// Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {

			response = JSON.parse(response);

			if( response ){

				setTimeout(function () {
					location.reload();
				}, 1000);
			}
		});
	
	});

	// On Close Rest API Banner
	jQuery('.ph-ups-close-rest-api-banner').on('click', function() {

		var data = {
			'action': 'ph_ups_closing_rest_api_banner',
		};
		
		jQuery.post(ajaxurl, data, function(response) {

			response = JSON.parse(response);

			if( response ){

				setTimeout(function () {
					location.reload();
				}, 1000);
			}
		});
	});

	// Services checkbox to enable and disable all services at once.
	jQuery(document).ready(function() {

		if (jQuery('.checkBoxClass:checked').length == jQuery('.checkBoxClass').length) {
			jQuery("#upsCheckAll").prop("checked", true);
		}
		jQuery("#upsCheckAll").click(function() {
			jQuery(".checkBoxClass").prop('checked', jQuery(this).prop('checked'));
		});

		jQuery(".checkBoxClass").change(function() {
			if (!jQuery(this).prop("checked")) {
				jQuery("#upsCheckAll").prop("checked", false);
			}
			if (jQuery('.checkBoxClass:checked').length == jQuery('.checkBoxClass').length) {
				jQuery("#upsCheckAll").prop("checked", true);
			}
		});
	});

	/*************************************************** Common JS for both Global and Zone **************************************************************/

	// Check to add js according to the location.
	if ( window.location.href.indexOf( 'wf_shipping_ups' ) !== -1 )  {	
		// Define the ID.
		WOO_PREFIX = '#woocommerce_wf_shipping_ups_';
		
	} else {
		return;
	}

	ph_ups_show_selected_tab(jQuery(".tab_general"), "general");

	jQuery('#ph_ups_close_banner').on('click', function(){

		jQuery('.ph_registration_banner').fadeOut('slow', function(){
			
			jQuery('.ph_registration_banner').remove();
		});
	}); 


	jQuery(".tab_general").on("click", function () {
		return ph_ups_show_selected_tab(jQuery(this), "general");
	});
	jQuery(".tab_rates").on("click", function () {
		return ph_ups_show_selected_tab(jQuery(this), "rates");
	});
	jQuery(".tab_labels").on("click", function () {
		return ph_ups_show_selected_tab(jQuery(this), "label");
	});
	jQuery(".tab_int_forms").on("click", function () {
		return ph_ups_show_selected_tab(jQuery(this), "int_forms");
	});
	jQuery(".tab_spl_services").on("click", function () {
		return ph_ups_show_selected_tab(jQuery(this), "spl_services");
	});
	jQuery(".tab_packaging").on("click", function () {
		return ph_ups_show_selected_tab(jQuery(this), "packaging");
	});
	jQuery(".tab_freight").on("click", function () {
		return ph_ups_show_selected_tab(jQuery(this), "freight");
	});
	jQuery(".tab_pickup").on("click", function () {
		return ph_ups_show_selected_tab(jQuery(this), "pickup");
	});
	jQuery(".tab_advanced_settings").on("click", function () {
		return ph_ups_show_selected_tab(jQuery(this), "advanced_settings");
	});
	jQuery(".tab_help").on("click", function () {
		return ph_ups_show_selected_tab(jQuery(this), "help");
	});

	function ph_ups_show_selected_tab($element, $tab) {
		jQuery(".ph-ups-tabs").removeClass("nav-tab-active");
		$element.addClass("nav-tab-active");

		jQuery(".ph_ups_rates_tab").closest("tr,h3").hide();
		jQuery(".ph_ups_rates_tab").next("p").hide();

		jQuery(".ph_ups_general_tab").closest("tr,h3").hide();
		jQuery(".ph_ups_general_tab").next("p").hide();

		jQuery(".ph_ups_label_tab").closest("tr,h3").hide();
		jQuery(".ph_ups_label_tab").next("p").hide();

		jQuery(".ph_ups_int_forms_tab").closest("tr,h3").hide();
		jQuery(".ph_ups_int_forms_tab").next("p").hide();

		jQuery(".ph_ups_spl_services_tab").closest("tr,h3").hide();
		jQuery(".ph_ups_spl_services_tab").next("p").hide();

		jQuery(".ph_ups_packaging_tab").closest("tr,h3").hide();
		jQuery(".ph_ups_packaging_tab").next("p").hide();

		jQuery(".ph_ups_freight_tab").closest("tr,h3").hide();
		jQuery(".ph_ups_freight_tab").next("p").hide();
		jQuery('.ph-ups-freight-banner-section').hide();

		jQuery(".ph_ups_pickup_tab").closest("tr,h3").hide();
		jQuery(".ph_ups_pickup_tab").next("p").hide();

		jQuery(".ph_ups_advanced_settings_tab").closest("tr,h3").hide();
		jQuery(".ph_ups_advanced_settings_tab").next("p").hide();

		jQuery(".ph_ups_help_tab").closest("tr,h3").hide();
		jQuery(".ph_ups_help_tab").next("p").hide();

		jQuery(".ph_ups_" + $tab + "_tab").closest("tr,h3").show();
		jQuery(".ph_ups_" + $tab + "_tab").next("p").show();

		if ($tab == 'general') {
			ph_ups_address_validation_options(); 
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'insuredvalue', WOO_PREFIX + 'min_order_amount_for_insurance');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'ship_from_address_different_from_shipper', '.ph_ups_different_ship_from_address');
			ph_ups_silent_debug_option();

			ph_ship_from_address_for_freight();
		}

		if ($tab == 'rates') {
			ph_ups_load_availability_options();
			ph_ups_tradability_cart_title();
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_estimated_delivery', '.ph_ups_est_delivery');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'accesspoint_locator', '.ph_ups_accesspoint');
			ph_ups_show_max_limit_error();
			ph_ups_email_notification_code_trigger();
		}

		if ($tab == 'label') {
			xa_ups_duties_payer_options();
			ph_ups_transportation_options();
			ph_toggle_ups_label_size();
			ph_ups_toggle_label_format();
			ph_ups_custom_description_for_label();
			ph_toggle_additional_description();
			ph_ups_toggle_label_email_settings();
			ph_ups_toggle_based_automate_pakage_generation();
			ph_toggle_ups_label_zoom_factor();
			ph_toggle_ups_custom_tracking();
			ph_automate_label_generation_trigger();
		}

		if ($tab == 'int_forms') {
			ph_ups_eei_options();
			ph_ups_load_shipper_filed_options();
			ph_ups_toggle_nafta_certificate_options();

			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'commercial_invoice', WOO_PREFIX + 'nafta_co_form');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'commercial_invoice', WOO_PREFIX + 'eei_data');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'commercial_invoice', WOO_PREFIX + 'declaration_statement');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'commercial_invoice', WOO_PREFIX + 'commercial_invoice_shipping');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'commercial_invoice', WOO_PREFIX + 'discounted_price');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'commercial_invoice', WOO_PREFIX + 'terms_of_shipment');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'commercial_invoice', WOO_PREFIX + 'reason_export');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'commercial_invoice', WOO_PREFIX + 'return_reason_export');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'commercial_invoice', WOO_PREFIX + 'edi_on_label');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'commercial_invoice', WOO_PREFIX + 'invoice_commodity_value');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'commercial_invoice', WOO_PREFIX + 'invoice_unit_of_measure');
		}

		if ($tab == 'spl_services') {

			if (jQuery(WOO_PREFIX + 'international_special_commodities').is(':checked')) {

				jQuery('.ph_ups_isc_toggol').closest('tr').show();
			} else {
				jQuery('.ph_ups_isc_toggol').closest('tr').hide();
			}

			ph_ups_toggle_based_on_restricted_article();
		}

		if ($tab == 'packaging') {
			wf_load_packing_method_options();
			ph_toggle_box_packing_options_based_on_algorithms();
		}

		if ($tab == 'freight') {
			
			jQuery('.ph-ups-freight-banner-section').show();

			ph_ups_load_third_party_billing_address();
			ph_ups_toggle_density_description();
			ph_ups_toggle_density_dimensions();

			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_class');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_packaging_type');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_holiday_pickup');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_inside_pickup');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_residential_pickup');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_weekend_pickup');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_liftgate_pickup');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_limitedaccess_pickup');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_holiday_delivery');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_inside_delivery');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_call_before_delivery');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_weekend_delivery');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_liftgate_delivery');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_limitedaccess_delivery');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_pickup_inst');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_delivery_inst');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_payment');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'enable_density_based_rating');

			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_density_based_rating', WOO_PREFIX + 'density_length');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_density_based_rating', WOO_PREFIX + 'density_width');
			ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_density_based_rating', WOO_PREFIX + 'density_height');
		}

		if ( $tab == 'advanced_settings' ) {
			
			jQuery('.ph_ups_legacy_tab').next('p').hide();

			jQuery('.ph_ups_legacy_tab').removeClass('ph_ups_legacy_tab_click').addClass('ph_ups_legacy_tab');

			jQuery(WOO_PREFIX + 'client_credentials').closest('tr').hide();
			jQuery(WOO_PREFIX + 'client_license_hash').closest('tr').hide();

			jQuery(WOO_PREFIX + 'user_id').closest('tr').hide();
			jQuery(WOO_PREFIX + 'password').closest('tr').hide();
			jQuery(WOO_PREFIX + 'access_key').closest('tr').hide();
			jQuery(WOO_PREFIX + 'shipper_number').closest('tr').hide();

			var phUPSClientCredentials = jQuery(WOO_PREFIX + 'client_credentials').val();
			var phUPSClientLicenseHash = jQuery(WOO_PREFIX + 'client_license_hash').val();

			if ( Ph_woocommerce_plugin_status.ph_new_customer || (phUPSClientCredentials != 'undefined' && phUPSClientCredentials != '') && (phUPSClientLicenseHash != 'undefined' && phUPSClientLicenseHash != '') ) {

				jQuery(WOO_PREFIX + 'ph_legacy_section').hide();
			}
		}

		if ($tab == 'pickup') {
			wf_ups_load_pickup_options();
		}

		if ($tab == 'help') {
			jQuery(".woocommerce-save-button").hide();
		} else {
			jQuery(".woocommerce-save-button").show();
		}

		return false;
	}

	/********************************************* General Settings *************************************************/

	// Silent Debug mode
	jQuery(WOO_PREFIX + 'debug').click(function () {
		ph_ups_silent_debug_option();
	});

	// Toggle Address Validation
	jQuery(WOO_PREFIX + 'residential').click(function () {
		ph_ups_address_validation_options();
	});

	// Toggle Address Suggestion
	jQuery(WOO_PREFIX + 'address_validation').click(function () {
		ph_ups_address_validation_options();
	});

	jQuery(WOO_PREFIX + 'suggested_address').click(function () {
		ph_ups_address_validation_options();
	});

	// Toggle Minimum Insurance amount
	jQuery(WOO_PREFIX + 'insuredvalue').click(function () {
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'insuredvalue', WOO_PREFIX + 'min_order_amount_for_insurance');
	});

	// Toggle Ship From Address Different from Shipper Address
	jQuery(WOO_PREFIX + 'ship_from_address_different_from_shipper').click(function () {
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'ship_from_address_different_from_shipper', '.ph_ups_different_ship_from_address');

		ph_ship_from_address_for_freight();
	});

	/********************************************* Rates Settings ***************************************************/

	// Toggle Estimated delivery related data
	jQuery(WOO_PREFIX + 'enable_estimated_delivery').click(function () {
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_estimated_delivery', '.ph_ups_est_delivery');
	});

	// Toggle Access Point Locator related data
	jQuery(WOO_PREFIX + 'accesspoint_locator').click(function () {
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'accesspoint_locator', '.ph_ups_accesspoint');
	});

	// Toggle Availability
	jQuery(WOO_PREFIX + 'availability').change(function () {
		ph_ups_load_availability_options();
	});

	jQuery(WOO_PREFIX + 'ups_tradability').click(function () {
		ph_ups_tradability_cart_title();
	});

	jQuery('.ph_ups_accesspoint_limit').attr({
		"max": 50,
		"min": 1
	});

	jQuery(WOO_PREFIX + 'accesspoint_max_limit').change(function (e) {
		ph_ups_show_max_limit_error();
	});

	jQuery(WOO_PREFIX + 'email_notification').change(function (e) {
		ph_ups_email_notification_code_trigger();
	});

	/********************************************* Label Settings ***************************************************/

	// Toggle Label Size
	jQuery(WOO_PREFIX + 'show_label_in_browser').click(function () {
		ph_toggle_ups_label_size();
		ph_toggle_ups_label_zoom_factor();
	});

	// Automate Package Generation Check box checked
	jQuery(WOO_PREFIX + 'automate_package_generation').click(function () {
		ph_ups_toggle_based_automate_pakage_generation();
		ph_automate_label_generation_trigger();
	});

	// Toggle Automatic Label Generation
	jQuery(WOO_PREFIX + 'automate_label_generation').click(function () {
		ph_automate_label_generation_trigger();
	});

	// Toggle Label Format based on Print Label Type option and Display Label in Browser.
	jQuery(WOO_PREFIX + 'print_label_type').change(function () {
		ph_ups_toggle_label_format();
		ph_toggle_ups_label_size();
		ph_toggle_ups_label_zoom_factor();
	});

	//Toggle Email Settings
	jQuery(WOO_PREFIX + 'auto_email_label').change(function () {
		ph_ups_toggle_label_email_settings();
	});

	// Custom Description For Label & Additional Description
	jQuery(WOO_PREFIX + 'label_description').change(function () {
		ph_ups_custom_description_for_label();
		ph_toggle_additional_description();
	});

	jQuery(WOO_PREFIX + 'duties_and_taxes').change(function () {
		xa_ups_duties_payer_options()
	});

	jQuery(WOO_PREFIX + 'transportation').change(function () {
		ph_ups_transportation_options()
	});

	// Toggle Custom Tracking
	jQuery(WOO_PREFIX + 'custom_tracking').click(function () {
		ph_toggle_ups_custom_tracking();
	});

	/********************************************* Int Fomrs Settings ************************************************/

	// Toggle NAFTA Certificate for Commercial Invoice
	jQuery(WOO_PREFIX + 'commercial_invoice').click(function () {
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'commercial_invoice', WOO_PREFIX + 'nafta_co_form');
	});

	jQuery(WOO_PREFIX + 'commercial_invoice').click(function () {
		ph_ups_toggle_nafta_certificate_options();
	});

	// Toggle Producer Option & Blanket Period for NAFTA Certificate
	jQuery(WOO_PREFIX + 'nafta_co_form').click(function () {
		ph_ups_toggle_nafta_certificate_options();
	});

	// Toggle EEI Data for Commercial Invoice
	jQuery(WOO_PREFIX + 'commercial_invoice').click(function () {
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'commercial_invoice', WOO_PREFIX + 'eei_data');
	});

	jQuery(WOO_PREFIX + 'commercial_invoice').click(function () {
		ph_ups_eei_options();
		ph_ups_load_shipper_filed_options();
	});

	jQuery(WOO_PREFIX + 'eei_data').click(function () {
		ph_ups_eei_options();
		ph_ups_load_shipper_filed_options();
	});

	jQuery(WOO_PREFIX + 'eei_shipper_filed_option').change(function () {
		ph_ups_load_shipper_filed_options();
	});

	// Toggle declaration Statement for Commercial Invoice
	jQuery(WOO_PREFIX + 'commercial_invoice').click(function () {
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'commercial_invoice', WOO_PREFIX + 'declaration_statement');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'commercial_invoice', WOO_PREFIX + 'commercial_invoice_shipping');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'commercial_invoice', WOO_PREFIX + 'discounted_price');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'commercial_invoice', WOO_PREFIX + 'terms_of_shipment');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'commercial_invoice', WOO_PREFIX + 'reason_export');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'commercial_invoice', WOO_PREFIX + 'return_reason_export');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'commercial_invoice', WOO_PREFIX + 'edi_on_label');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'commercial_invoice', WOO_PREFIX + 'invoice_commodity_value');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'commercial_invoice', WOO_PREFIX + 'invoice_unit_of_measure');
	});

	/********************************************* Special Services ************************************************/

	jQuery(WOO_PREFIX + 'international_special_commodities').click(function () {

		ph_ups_toggle_based_on_restricted_article();

		if (jQuery(WOO_PREFIX + 'international_special_commodities').is(':checked')) {

			jQuery('.ph_ups_isc_toggol').closest('tr').show();
		} else {
			jQuery('.ph_ups_isc_toggol').closest('tr').hide();
		}
	});

	jQuery(WOO_PREFIX + 'ph_ups_restricted_article').click(function () {

		ph_ups_toggle_based_on_restricted_article();
	});

	/********************************************* Packaging Settings ************************************************/

	// Toggle Packing Methods
	jQuery('.packing_method').change(function () {
		wf_load_packing_method_options();
		ph_toggle_box_packing_options_based_on_algorithms();
	});

	// Toggle Exclude Box Weight
	jQuery(WOO_PREFIX + 'packing_algorithm').change(function () {
		ph_toggle_box_packing_options_based_on_algorithms();
	});

	jQuery(window).load(function() {

		jQuery('.ups_boxes .insert').click(function() {
			var $tbody = jQuery('.ups_boxes').find('tbody');
			var size = $tbody.find('tr').size();
			size += 1;
			var code = '<tr class="new">\
									<td style="padding-left: 20px;" class="check-column"><input type="checkbox" /></td>\
									<td><input type="text" size="8" name="boxes_name[' + size + ']" /></td>\
									<td><input type="text" size="5" name="boxes_outer_length[' + size + ']" /></td>\
									<td><input type="text" size="5" name="boxes_outer_width[' + size + ']" /></td>\
									<td><input type="text" size="5" name="boxes_outer_height[' + size + ']" /></td>\
									<td><input type="text" size="5" name="boxes_inner_length[' + size + ']" /></td>\
									<td><input type="text" size="5" name="boxes_inner_width[' + size + ']" /></td>\
									<td><input type="text" size="5" name="boxes_inner_height[' + size + ']" /></td>\
									<td><input type="text" size="5" name="boxes_box_weight[' + size + ']" /></td>\
									<td><input type="text" size="5" name="boxes_max_weight[' + size + ']" /></td>\
									<td class="check-column"><input type="checkbox" name="boxes_enabled[' + size + ']"/></td>\
								</tr>';

			$tbody.append(code);

			return false;
		});

		jQuery('.ups_boxes .remove').click(function() {
			var $tbody = jQuery('.ups_boxes').find('tbody');

			$tbody.find('.check-column input:checked').each(function() {
				jQuery(this).closest('tr').hide().find('input').val('');
			});

			return false;
		});

	});

	/********************************************* Freight Settings ************************************************/

	// Toggle UPS Freight Class Settings
	jQuery(WOO_PREFIX + 'enable_freight').click(function () {
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_class');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_packaging_type');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_holiday_pickup');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_inside_pickup');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_residential_pickup');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_weekend_pickup');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_liftgate_pickup');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_limitedaccess_pickup');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_holiday_delivery');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_inside_delivery');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_call_before_delivery');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_weekend_delivery');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_liftgate_delivery');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_limitedaccess_delivery');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_pickup_inst');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_delivery_inst');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'freight_payment');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', '.ph_ups_freight_third_party_billing');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'enable_density_based_rating');
		ph_ups_load_third_party_billing_address();
	});

	jQuery(WOO_PREFIX + 'freight_payment').change(function () {
		ph_ups_load_third_party_billing_address();
	});

	jQuery(WOO_PREFIX + 'enable_density_based_rating').click(function () {
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_density_based_rating', WOO_PREFIX + 'density_length');
	});

	jQuery(WOO_PREFIX + 'enable_density_based_rating').click(function () {
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_density_based_rating', WOO_PREFIX + 'density_width');
	});

	jQuery(WOO_PREFIX + 'enable_density_based_rating').click(function () {
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_density_based_rating', WOO_PREFIX + 'density_height');
	});

	jQuery(WOO_PREFIX + 'enable_freight').click(function () {
		if (!jQuery(WOO_PREFIX + 'enable_density_based_rating').is(':checked') || !jQuery(this).is(':checked')) {
			jQuery(WOO_PREFIX + 'density_description').next('p').hide();
		}
		else {
			jQuery(WOO_PREFIX + 'density_description').next('p').show();
		}
	});

	jQuery(WOO_PREFIX + 'enable_density_based_rating').click(function () {
		if (!jQuery(this).is(':checked')) {
			jQuery(WOO_PREFIX + 'density_description').next('p').hide();
		}
		else {
			jQuery(WOO_PREFIX + 'density_description').next('p').show();
		}
	});

	jQuery(WOO_PREFIX + 'enable_freight').click(function () {
		ph_ups_toggle_density_dimensions();
	});

	jQuery(WOO_PREFIX + 'enable_density_based_rating').click(function () {
		ph_ups_toggle_density_dimensions();
	});

	/********************************************* Pickup Settings ***************************************************/

	// Toggle pickup options
	jQuery(WOO_PREFIX + 'pickup_enabled').click(function () {
		wf_ups_load_pickup_options();
	});

	// Toggle working days
	jQuery(WOO_PREFIX + 'pickup_date').change(function () {
		wf_ups_load_working_days();
	});

	/********************************************* Advanced Settings ***************************************************/

	// Legacy settings tab
	jQuery('.ph_ups_legacy_tab').click(function(event){
		event.stopImmediatePropagation();
		jQuery('.ph_ups_legacy_tab').toggleClass('ph_ups_legacy_tab_click');
		jQuery(WOO_PREFIX + 'user_id').closest('tr').toggle();
		jQuery(WOO_PREFIX + 'password').closest('tr').toggle();
		jQuery(WOO_PREFIX + 'access_key').closest('tr').toggle();
		jQuery(WOO_PREFIX + 'shipper_number').closest('tr').toggle();
		jQuery(this).next('p').toggle();
	});

	/********************************************* Help & Support Send Report Settings ************************************************/

	jQuery('#ph_ups_ticket_number').keyup(function () {
		jQuery('#ph_ups_ticket_number').removeClass('required_field');
		jQuery('.ph_ups_ticket_number_error').hide();
	});

	jQuery("#ph_ups_consent").click(function () {
		jQuery('#ph_ups_consent').removeClass('required_field');
		jQuery('.ph_ups_consent_error').hide();
	});

	jQuery("#ph_ups_submit_ticket").click(function () {

		jQuery('.ph_error_message').remove();

		var required = false;
		var ticket_num = jQuery('#ph_ups_ticket_number').val();
		var consent = jQuery('#ph_ups_consent').is(':checked');

		if (!ticket_num) {
			jQuery('#ph_ups_ticket_number').addClass('required_field');
			jQuery('.ph_ups_ticket_number_error').show();
			required = true;
		}

		if (!consent) {
			jQuery('#ph_ups_consent').addClass('required_field');
			jQuery('.ph_ups_consent_error').show();
			required = true;
		}

		if (required) {
			return false;
		}
		// Change Text and Disable the Button
		jQuery("#ph_ups_submit_ticket").prop("value", "Please Wait...");
		jQuery("#ph_ups_submit_ticket").attr('disabled', 'disabled');

		let key_data = {
			action: 'ph_ups_get_ups_log_data',
		}

		jQuery.post(ajaxurl, key_data, function (result, status) {

			try {

				let response = JSON.parse(result);

				if (response.status == true) {

					let key_data = {
						action: 'ph_ups_submit_support_ticket',
						ticket_num: ticket_num,
						log_file: response.file_path
					}

					jQuery.post(ajaxurl, key_data, function (result, status) {

						let response2 = JSON.parse(result);

						if (response2.status == true) {
							message = "<b>Diagnostic report sent successfully.</b> PluginHive Support Team will contact you shortly via email."
							jQuery(".ph_ups_help_table").after("<p style='color:green;' class='ph_error_message'>" + message + "</p>");

							// Add original text and enable the button
							jQuery("#ph_ups_submit_ticket").prop("value", "Send Report");
							jQuery("#ph_ups_submit_ticket").removeAttr("disabled");
						} else {

							// Add original text and enable the button
							jQuery("#ph_ups_submit_ticket").prop("value", "Send Report");
							jQuery("#ph_ups_submit_ticket").removeAttr("disabled");
						}

					});

				} else {
					message = response.message;
					jQuery(".ph_ups_help_table").after("<p style='color:red;' class='ph_error_message'>" + message + "</p>");

					// Add original text and enable the button
					jQuery("#ph_ups_submit_ticket").prop("value", "Send Report");
					jQuery("#ph_ups_submit_ticket").removeAttr("disabled");
				}

			} catch (err) {
				alert(err.message);

				// Add original text and enable the button
				jQuery("#ph_ups_submit_ticket").prop("value", "Send Report");
				jQuery("#ph_ups_submit_ticket").removeAttr("disabled");
			}

		});
	});

	/*************************************************** Order Level Settings **************************************************************/

	// Set the selected weight/dimension unit in a hidden field to convert while saving the settings
	jQuery(WOO_PREFIX + 'units').on('change', function () {

		jQuery('#selected_dim_unit').val(jQuery(WOO_PREFIX + 'units').val());

	});	
});

/********************************************************************************************************************************************/
/******************************************************* Required Functions **************************************************************/
/********************************************************************************************************************************************/

function ups_services_row_indexes() {
	jQuery('.ups_services tbody tr').each(function(index, el) {
		jQuery('input.order', el).val(parseInt(jQuery(el).index('.ups_services tr')));
	});
};

function ph_ship_from_address_for_freight() {

	if (jQuery(WOO_PREFIX + "enable_freight").is(':checked') && jQuery(WOO_PREFIX + "ship_from_address_different_from_shipper").is(':checked')) {

		jQuery(WOO_PREFIX + "ship_from_address_for_freight").closest('tr').show();
	} else {

		jQuery(WOO_PREFIX + "ship_from_address_for_freight").closest('tr').hide();
	}
}

function ph_ups_toggle_based_on_restricted_article() {

	if (jQuery(WOO_PREFIX + 'international_special_commodities').is(':checked') && jQuery(WOO_PREFIX + 'ph_ups_restricted_article').is(':checked')) {

		jQuery('.ph_restricted_article').closest('tr').show();
	} else {

		jQuery('.ph_restricted_article').closest('tr').hide();
	}
}

function ph_ups_show_max_limit_error() {
	jQuery('.ph_ups_max_limit_error').hide();
	val = jQuery(WOO_PREFIX + 'accesspoint_max_limit').val();
	if (val < 1 || val > 50) {
		jQuery(WOO_PREFIX + 'accesspoint_max_limit').after("<p style='color:red' class='ph_ups_max_limit_error'>Please enter a valid value. (Valid Values : 1-50)</p>");
	}
}

// Toggle based on checkbox status
function ph_ups_toggle_based_on_checkbox_status(tocheck, to_toggle) {
	if (!jQuery(tocheck).is(':checked')) {
		jQuery(to_toggle).closest('tr').hide();
	}
	else {
		jQuery(to_toggle).closest('tr').show();
	}
}

// Silent debug
function ph_ups_silent_debug_option() {

	var checked = jQuery(WOO_PREFIX + 'debug').is(":checked");

	if (checked) {

		jQuery('.ph_ups_silent_debug').closest('tr').show();

	} else {

		jQuery('.ph_ups_silent_debug').closest('tr').hide();

	}
}

function ph_ups_address_validation_options() {

	if (jQuery(WOO_PREFIX + 'residential').is(':checked')) {

		jQuery(WOO_PREFIX + 'address_validation').closest('tr').hide();
		jQuery(WOO_PREFIX + 'suggested_address').closest('tr').hide();
		jQuery(WOO_PREFIX + 'suggested_display').closest('tr').hide();
	} else {

		jQuery(WOO_PREFIX + 'address_validation').closest('tr').show();

		if (jQuery(WOO_PREFIX + 'address_validation').is(':checked')) {

			jQuery(WOO_PREFIX + 'suggested_address').closest('tr').show();

			if (jQuery(WOO_PREFIX + 'suggested_address').is(':checked')) {

				jQuery(WOO_PREFIX + 'suggested_display').closest('tr').show();

			} else {

				jQuery(WOO_PREFIX + 'suggested_display').closest('tr').hide();

			}
		} else {

			jQuery(WOO_PREFIX + 'suggested_address').closest('tr').hide();
			jQuery(WOO_PREFIX + 'suggested_display').closest('tr').hide();

		}

	}
}

function ph_ups_load_availability_options() {
	available = jQuery(WOO_PREFIX + 'availability');
	if (available.val() == 'all') {
		jQuery(WOO_PREFIX + 'countries').closest('tr').hide();
	} else {
		jQuery(WOO_PREFIX + 'countries').closest('tr').show();
	}
}

/**
 * Toggle Tradability Cart Title
 */
function ph_ups_tradability_cart_title() {

	if (jQuery(WOO_PREFIX + 'ups_tradability').is(':checked')) {
		jQuery(WOO_PREFIX + "tradability_cart_title").closest('tr').show();
	}
	else {
		jQuery(WOO_PREFIX + "tradability_cart_title").closest('tr').hide();
	}
}

function ph_ups_email_notification_code_trigger() {

	var test = jQuery(WOO_PREFIX + 'email_notification').val();

	if (test && test.length) {

		jQuery(WOO_PREFIX + "email_notification_code").closest('tr').show();
	}
	else {

		jQuery(WOO_PREFIX + "email_notification_code").closest('tr').hide();
	}
}

/**
 * Toggle Label Size option.
 */
function ph_toggle_ups_label_size() {

	if (jQuery(WOO_PREFIX + "print_label_type").val() == 'gif' || jQuery(WOO_PREFIX + "print_label_type").val() == 'png') {

		jQuery(WOO_PREFIX + "show_label_in_browser").closest('tr').show();
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'show_label_in_browser', WOO_PREFIX + 'label_in_browser_zoom');

	} else {

		jQuery(WOO_PREFIX + "show_label_in_browser").closest('tr').hide();
		jQuery(WOO_PREFIX + "label_in_browser_zoom").closest('tr').hide();
	}
}

/**
 * Toggle Label Zoom Factor
 */
function ph_toggle_ups_label_zoom_factor() {

	var labelInBrowser = jQuery(WOO_PREFIX + 'show_label_in_browser').is(':checked');
	var labelType = jQuery(WOO_PREFIX + "print_label_type").val();

	if (labelInBrowser && (labelType == 'gif' || labelType == 'png')) {

		jQuery(WOO_PREFIX + "label_in_browser_zoom").closest('tr').show();
		jQuery(WOO_PREFIX + "rotate_label").closest('tr').show();
		jQuery(".ups_display_browser_options").closest('tr').show();

	} else {
		jQuery(WOO_PREFIX + "label_in_browser_zoom").closest('tr').hide();
		jQuery(WOO_PREFIX + "rotate_label").closest('tr').hide();
		jQuery(".ups_display_browser_options").closest('tr').hide();
	}
}

/**
 * Toggle Custom Tracking
 */
function ph_toggle_ups_custom_tracking() {

	if (jQuery(WOO_PREFIX + 'custom_tracking').is(':checked')) {
		jQuery(WOO_PREFIX + "custom_tracking_url").closest('tr').show();
	}
	else {
		jQuery(WOO_PREFIX + "custom_tracking_url").closest('tr').hide();
	}
}

/**
 * Toggle Automatic Label Generation
 */
function ph_automate_label_generation_trigger() {

	let checked1 = jQuery(WOO_PREFIX + 'automate_label_generation').is(":checked");
	let checked2 = jQuery(WOO_PREFIX + 'automate_package_generation').is(":checked");
	if (checked1 && checked2) {
		jQuery(WOO_PREFIX + 'automate_label_trigger').closest('tr').show();
	} else {
		jQuery(WOO_PREFIX + 'automate_label_trigger').closest('tr').hide();
	}
}

/**
 * Toggle Label Format based on Print Label Type option and Display Label in Browser.
 */
function ph_ups_toggle_label_format() {

	if (jQuery(WOO_PREFIX + "print_label_type").val() == 'gif' || jQuery(WOO_PREFIX + "print_label_type").val() == 'png') {

		jQuery(WOO_PREFIX + "label_format").closest('tr').show();

	} else {
		jQuery(WOO_PREFIX + "label_format").closest('tr').hide();
	}
}

/**
 * Toggle UPS Label Email Settings.
 */
function ph_ups_toggle_label_email_settings() {

	let sendLabelTO = jQuery(WOO_PREFIX + "auto_email_label").val();

	if (sendLabelTO == '') {

		jQuery(".ph_ups_email_label_settings").closest('tr').hide();
		jQuery(WOO_PREFIX + "email_recipients").closest('tr').hide();

	} else {

		jQuery(".ph_ups_email_label_settings").closest('tr').show();

		if (jQuery.isArray(sendLabelTO) && sendLabelTO.includes("shipper")) {

			jQuery(WOO_PREFIX + "email_recipients").closest('tr').show();

		} else {

			jQuery(WOO_PREFIX + "email_recipients").closest('tr').hide();
		}
	}
}

/**
 * Custom Description For Label
*/
function ph_ups_custom_description_for_label() {

	if (jQuery(WOO_PREFIX + "label_description").val() == 'custom_description') {

		jQuery(WOO_PREFIX + "label_custom_description").prop("required", true).closest('tr').show();

	} else {

		jQuery(WOO_PREFIX + "label_custom_description").prop("required", false).closest('tr').hide();
	}
}

/**
 * Additional Shipment Description
*/
function ph_toggle_additional_description() {

	if (jQuery(WOO_PREFIX + "label_description").val() == 'order_number') {

		jQuery(".ph_additional_shipment_description").closest('tr').hide();
	} else {

		jQuery(".ph_additional_shipment_description").closest('tr').show();
	}
}

/**
 *  Automate Package Generation Check box checked
**/
function ph_ups_toggle_based_automate_pakage_generation() {

	if (jQuery(WOO_PREFIX + 'automate_package_generation').is(':checked')) {
		jQuery(WOO_PREFIX + "automate_label_generation").closest('tr').show();
	}
	else {
		jQuery(WOO_PREFIX + "automate_label_generation").closest('tr').hide();
		jQuery(WOO_PREFIX + 'automate_label_generation').removeAttr('checked');
	}

}

function ph_ups_toggle_nafta_certificate_options() {
	let check1 = jQuery(WOO_PREFIX + 'commercial_invoice').is(':checked');
	let check2 = jQuery(WOO_PREFIX + 'nafta_co_form').is(':checked')

	if (check1 && check2) {
		jQuery('.ph_ups_nafta_group').closest('tr').show();
	}
	else {
		jQuery('.ph_ups_nafta_group').closest('tr').hide();
	}
}

function ph_ups_eei_options() {
	let check1 = jQuery(WOO_PREFIX + 'commercial_invoice').is(':checked');
	let check2 = jQuery(WOO_PREFIX + 'eei_data').is(':checked')

	if (check1 && check2) {
		jQuery('.ph_ups_eei_group').closest('tr').show();
	}
	else {
		jQuery('.ph_ups_eei_group').closest('tr').hide();
	}
}

function ph_ups_load_shipper_filed_options() {

	var eei_filed_option = jQuery(WOO_PREFIX + 'eei_shipper_filed_option').val();
	var invoice_enabled = jQuery(WOO_PREFIX + 'commercial_invoice').is(":checked");
	var eei_enabled = jQuery(WOO_PREFIX + 'eei_data').is(":checked");

	if (invoice_enabled && eei_enabled) {
		if (eei_filed_option == 'A') {

			jQuery('.eei_pre_departure_itn_number').closest('tr').show();
			jQuery('.eei_exemption_legend').closest('tr').hide();

		} else if (eei_filed_option == 'B') {

			jQuery('.eei_pre_departure_itn_number').closest('tr').hide();
			jQuery('.eei_exemption_legend').closest('tr').show();

		} else {

			jQuery('.eei_pre_departure_itn_number').closest('tr').hide();
			jQuery('.eei_exemption_legend').closest('tr').hide();

		}
	}

}

function wf_load_packing_method_options() {
	pack_method = jQuery('.packing_method').val();

	jQuery('#packing_options').hide();
	jQuery('.weight_based_option').closest('tr').hide();
	jQuery('.xa_ups_box_packing').closest('tr').hide();

	switch (pack_method) {

		case 'box_packing':
			jQuery('.xa_ups_box_packing').closest('tr').show();
			jQuery('#packing_options').show();
			break;

		case 'weight_based':
			jQuery('.weight_based_option').closest('tr').show();
			break;

		case 'per_item':

		default:
			break;
	}
}

function ph_toggle_box_packing_options_based_on_algorithms() {

	pack_method = jQuery('.packing_method').val();
	packing_algorithm = jQuery(WOO_PREFIX + 'packing_algorithm').val();

	if (packing_algorithm == 'volume_based' && pack_method == 'box_packing') {

		jQuery('.exclude_box_weight').closest('tr').show();
	} else {

		jQuery('.exclude_box_weight').closest('tr').hide();
	}

	if (packing_algorithm == 'stack_first' && pack_method == 'box_packing') {

		jQuery('.stack_to_volume').closest('tr').show();
	} else {

		jQuery('.stack_to_volume').closest('tr').hide();
	}
}

function ph_ups_load_third_party_billing_address() {
	var freight_payment = jQuery(WOO_PREFIX + 'freight_payment').val();
	var freight_enabled = jQuery(WOO_PREFIX + 'enable_freight').is(":checked");

	if (!freight_enabled || freight_payment != '30') {
		jQuery('.ph_ups_freight_third_party_billing').closest('tr').hide();
	} else {
		jQuery('.ph_ups_freight_third_party_billing').closest('tr').show();
	}
}

function ph_ups_toggle_density_description() {
	if (!jQuery(WOO_PREFIX + 'enable_density_based_rating').is(':checked') || !jQuery(WOO_PREFIX + 'enable_freight').is(':checked')) {
		jQuery(WOO_PREFIX + 'density_description').next('p').hide();
	}
	else {
		jQuery(WOO_PREFIX + 'density_description').next('p').show();
	}
}

function ph_ups_toggle_density_dimensions() {
	if (!jQuery(WOO_PREFIX + 'enable_freight').is(':checked')) {
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'density_length');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'density_width');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_freight', WOO_PREFIX + 'density_height');
	}
	else if (!jQuery(WOO_PREFIX + 'enable_density_based_rating').is(':checked')) {
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_density_based_rating', WOO_PREFIX + 'density_length');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_density_based_rating', WOO_PREFIX + 'density_width');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_density_based_rating', WOO_PREFIX + 'density_height');
	}
	else {
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_density_based_rating', WOO_PREFIX + 'density_length');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_density_based_rating', WOO_PREFIX + 'density_width');
		ph_ups_toggle_based_on_checkbox_status(WOO_PREFIX + 'enable_density_based_rating', WOO_PREFIX + 'density_height');
	}
}

function wf_ups_load_pickup_options() {
	var checked = jQuery(WOO_PREFIX + 'pickup_enabled').is(":checked");
	if (checked) {
		jQuery('.wf_ups_pickup_grp').closest('tr').show();
	} else {
		jQuery('.wf_ups_pickup_grp').closest('tr').hide();
	}
	wf_ups_load_working_days();
}

function wf_ups_load_working_days() {

	var pickup_date = jQuery(WOO_PREFIX + 'pickup_date').val();
	var checked = jQuery(WOO_PREFIX + 'pickup_enabled').is(":checked");

	if (!checked || pickup_date != 'specific') {
		jQuery('.pickup_working_days').closest('tr').hide();
	} else {
		jQuery('.pickup_working_days').closest('tr').show();
	}
}

/**
 * Duties and taxes select box
**/
function xa_ups_duties_payer_options() {

	val = jQuery(WOO_PREFIX + "duties_and_taxes").val();
	if (val == 'third_party') {

		jQuery(WOO_PREFIX + "shipping_payor_acc_no").closest('tr').show();
		jQuery(WOO_PREFIX + "shipping_payor_post_code").closest('tr').show();
		jQuery(WOO_PREFIX + "shipping_payor_country_code").closest('tr').show();

	} else {

		jQuery(WOO_PREFIX + "shipping_payor_acc_no").closest('tr').hide();
		jQuery(WOO_PREFIX + "shipping_payor_post_code").closest('tr').hide();
		jQuery(WOO_PREFIX + "shipping_payor_country_code").closest('tr').hide();
	}
}

function ph_ups_transportation_options() {

	val = jQuery(WOO_PREFIX + "transportation").val();

	if (val == 'third_party') {

		jQuery(WOO_PREFIX + "transport_payor_acc_no").closest('tr').show();
		jQuery(WOO_PREFIX + "transport_payor_post_code").closest('tr').show();
		jQuery(WOO_PREFIX + "transport_payor_country_code").closest('tr').show();

	} else {

		jQuery(WOO_PREFIX + "transport_payor_acc_no").closest('tr').hide();
		jQuery(WOO_PREFIX + "transport_payor_post_code").closest('tr').hide();
		jQuery(WOO_PREFIX + "transport_payor_country_code").closest('tr').hide();
	}
}