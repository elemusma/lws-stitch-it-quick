jQuery(document).ready(function () {

	// UPS Shipping Details Toggle
	jQuery('.ph_ups_other_details').next('.ph_ups_hide_show_product_fields').hide();
	jQuery('.ph_ups_other_details').click(function (event) {
		event.stopImmediatePropagation();
		jQuery('.ups_toggle_symbol').toggleClass('ups_toggle_symbol_click');
		jQuery(this).next('.ph_ups_hide_show_product_fields').toggle();
	});

	// UPS Shipping Details Toggle - Variation Level
	jQuery(document).on('click', '.ph_ups_var_other_details', function (e) {
		e.stopImmediatePropagation();
		jQuery(this).find('.ups_var_toggle_symbol').toggleClass('ups_var_toggle_symbol_click');
		jQuery(this).next('.ph_ups_hide_show_var_product_fields').toggle();
	});

	// Toggle Hazardous Materials

	ph_ups_toggle_restricted_materials();
	jQuery('#_ph_ups_restricted_article').change(function () {
		ph_ups_toggle_restricted_materials();
	});

	jQuery(document).on('click', '.woocommerce_variation', function () {
		ph_ups_toggle_var_restricted_materials_on_load(this);
	});

	jQuery(document).on('change', 'input.ph_ups_variation_restricted_product', function () {
		ph_ups_toggle_var_restricted_materials(this);
	});


	ph_ups_toggle_hazardous_materials();
	jQuery('#_ph_ups_hazardous_materials').change(function () {
		ph_ups_toggle_hazardous_materials();
	});

	// Toggle Hazardous Materials - Variation Level - By Default
	jQuery(document).on('click', '.woocommerce_variation', function () {
		ph_ups_toggle_var_hazardous_materials_on_load(this);
	});

	// Toggle Hazardous Materials - Variation Level - On Click
	jQuery(document).on('change', 'input.ph_ups_variation_hazmat_product', function () {
		ph_ups_toggle_var_hazardous_materials(this);
	});

	// End of Toggle Hazardous Materials

	jQuery('.ph_label_custom_description').attr({ 'maxlength': 50, 'rows': 2 });
	jQuery('.thirdparty_grp').attr({ 'maxlength': 50, 'rows': 2 });

	// Country of Manufacturer
	jQuery('#_ph_ups_manufacture_country').attr({ 'maxlength': 2 });

	// EEI Product Level Fields
	jQuery('#_ph_eei_export_information').attr({ 'maxlength': 2 });
	jQuery('#_ph_eei_schedule_b_number').attr({ 'maxlength': 10 });
	jQuery('#_ph_eei_unit_of_measure').attr({ 'maxlength': 3 });

	function ph_ups_toggle_restricted_materials() {
		if (jQuery("#_ph_ups_restricted_article").is(":checked")) {
			jQuery(".ph_ups_restricted_article").show();
		}
		else {
			jQuery(".ph_ups_restricted_article").hide();
		}
	}
	function ph_ups_toggle_var_restricted_materials_on_load(e) {
		if (jQuery(e).find(".ph_ups_variation_restricted_product").is(':checked')) {
			jQuery(e).find(".ph_ups_var_restricted_article").show();
		} else {
			jQuery(e).find(".ph_ups_var_restricted_article").hide();
		}
	}

	/**
	 * Toggle Hazardous Materials Settings - Variation Level - Onclick
	**/
	function ph_ups_toggle_var_restricted_materials(e) {
		if (jQuery(e).is(':checked')) {
			jQuery(e).closest('.woocommerce_variation').find(".ph_ups_var_restricted_article").show();
		} else {
			jQuery(e).closest('.woocommerce_variation').find(".ph_ups_var_restricted_article").hide();
		}
	}

	/**
	 * Toggle Hazardous Materials Settings
	**/
	function ph_ups_toggle_hazardous_materials() {
		if (jQuery("#_ph_ups_hazardous_materials").is(":checked")) {
			jQuery(".ph_ups_hazardous_materials").show();
		}
		else {
			jQuery(".ph_ups_hazardous_materials").hide();
		}
	}

	/**
	 * Toggle Hazardous Materials Settings - Variation Level - Onload
	**/
	function ph_ups_toggle_var_hazardous_materials_on_load(e) {
		if (jQuery(e).find(".ph_ups_variation_hazmat_product").is(':checked')) {
			jQuery(e).find(".ph_ups_var_hazardous_materials").show();
		} else {
			jQuery(e).find(".ph_ups_var_hazardous_materials").hide();
		}
	}

	/**
	 * Toggle Hazardous Materials Settings - Variation Level - Onclick
	**/
	function ph_ups_toggle_var_hazardous_materials(e) {
		if (jQuery(e).is(':checked')) {
			jQuery(e).closest('.woocommerce_variation').find(".ph_ups_var_hazardous_materials").show();
		} else {
			jQuery(e).closest('.woocommerce_variation').find(".ph_ups_var_hazardous_materials").hide();
		}
	}

});