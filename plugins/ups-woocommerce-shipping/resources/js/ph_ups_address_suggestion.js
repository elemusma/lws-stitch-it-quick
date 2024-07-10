var suggestion 	= false;

// PDS-3
jQuery(document).ready(function ($) {

	jQuery("body").on("click", "#place_order", function (e) {

		jQuery('#customer_details').before('<div class="blockUI blockOverlay ph-loading-address-suggestion" style="z-index: 1000; border: none; margin: 0px; padding: 0px; width: 100%; height: 100%; top: 0px; left: 0px; background: rgb(255, 255, 255); opacity: 0.6; cursor: default; position: absolute;"></div>');

		if( jQuery('input[name="ph_which_to_use"]').length != 0 )
		{
			jQuery('.ph-loading-address-suggestion').remove();

			jQuery('.checkout').submit();
			return true;
		}

		// Ajax call to get Data from PHP
		
		jQuery.ajax({

			url: ph_ajax_object.ajax_url,
			data: {
				action: 'ph_get_address_validation_result',
			},
			method : 'POST',
			success: function (result, status) {

				result = JSON.parse(result);

				if ( result == null || (Array.isArray(result) && result.length == 0) ) {
					
					jQuery('.ph-loading-address-suggestion').remove();

					jQuery('.checkout').submit();
					return true;
				}
				
				let checkout 		= result.checkout_address;
				let suggested 		= result.suggested_address;
				var is_addr_same 	= false;

				if (checkout.address_1?.toLowerCase() == suggested.address_1?.toLowerCase() && 
					checkout.city?.toLowerCase() == suggested.city?.toLowerCase() && 
					(checkout.state != null && suggested.state != null && checkout.state?.toLowerCase() == suggested.state?.toLowerCase()) && 
					checkout.country?.toLowerCase() == suggested.country?.toLowerCase() && 
					checkout.postcode == suggested.postcode) {

					is_addr_same = true;
				}

				let street1, street2, city, state, postcode, country = '';

				street1 	= jQuery("#ph_addr_orig_addr1").val();
				street2 	= jQuery("#ph_addr_orig_addr2").val();
				city 		= jQuery("#ph_addr_orig_city").val();
				state 		= jQuery("#ph_addr_orig_state").val();
				postcode 	= jQuery("#ph_addr_orig_zip").val();
				country 	= jQuery("#ph_addr_orig_country").val();

				if ( street1 != null ) { street1 = street1?.toLowerCase(); }

				if( street2 != null ) { street2 = street2?.toLowerCase(); }

				if( city != null ) { city = city?.toLowerCase() }
				
				if( country != null ) { country = country?.toLowerCase() }
				
				if ( checkout.address_1?.toLowerCase() !== street1 || 
					 checkout.address_2?.toLowerCase() !== street2 || 
					 checkout.city?.toLowerCase() !== city || ( 
					 checkout.state != null && state != null && 
					 checkout.state?.toLowerCase() !== state?.toLowerCase() ) || 
					 checkout.country?.toLowerCase() !== country || 
					 checkout.postcode !== postcode ) {
					
					suggestion  	= false;	
					is_addr_same 	= false;
				}

				if (is_addr_same == true) {

					jQuery('.checkout').submit();

				} else {

					if (suggestion) {

						jQuery('.checkout').submit();

					} else {

						suggestion = true;
						jQuery('#ph_addr_radio').empty();
						jQuery('#ph_suggested_address').empty();
						jQuery('html, body').animate({ scrollTop: jQuery('#customer_details').offset().top }, 'fast');					

						// Append new Div to add Suggested Address
						jQuery('#customer_details').prepend('<div id="ph_suggested_address"></div>');

						addr = ((suggested.address_1 == "") ? "" : suggested.address_1 + ", ");
						addr += ((suggested.city == "") ? "" : suggested.city + ", ");
						addr += ((suggested.state == "") ? "" : suggested.state + ", ");
						addr += ((suggested.postcode == "") ? "" : suggested.postcode);

						jQuery('#ph_suggested_address').prepend('<div class="ph-addr-radio">');
						jQuery('#ph_suggested_address').prepend('<input type="radio" name="ph_which_to_use" id="ph_radio_obj" value="obj"><b> ' + 'Use Suggested Address: ' + ' </b>' + addr + '');
						jQuery('#ph_suggested_address').prepend('</div>');

						// Add Suggested Address in hidden fields
						jQuery('#ph_addr_radio').append("<div style='display: hidden;'>");
						jQuery('#ph_addr_radio').append("<input type='hidden' name='ph_addr_corrected_obj_addr1' id='ph_addr_corrected_obj_addr1' value='" + suggested.address_1 + "'>");
						jQuery('#ph_addr_radio').append("<input type='hidden' name='ph_addr_corrected_obj_addr2' id='ph_addr_corrected_obj_addr2' value='" + suggested.address_2 + "'>");
						jQuery('#ph_addr_radio').append("<input type='hidden' name='ph_addr_corrected_obj_city'' id='ph_addr_corrected_obj_city' value='" + suggested.city + "'>");
						jQuery('#ph_addr_radio').append("<input type='hidden' name='ph_addr_corrected_obj_state' id='ph_addr_corrected_obj_state' value='" + suggested.state + "'>");
						jQuery('#ph_addr_radio').append("<input type='hidden' name='ph_addr_corrected_obj_zip' id='ph_addr_corrected_obj_zip' value='" + suggested.postcode + "'>");
						jQuery('#ph_addr_radio').append("</div>");

						addr = ((checkout.address_1 == "") ? "" : checkout.address_1 + ", ");
						addr += ((checkout.address_2 == "") ? "" : checkout.address_2 + ", ");
						addr += ((checkout.city == "") ? "" : checkout.city + ", ");
						addr += ((checkout.state == "") ? "" : checkout.state + ", ");
						addr += ((checkout.postcode == "") ? "" : checkout.postcode);

						jQuery('#ph_suggested_address').prepend('<div class="ph-addr-radio">');
						jQuery('#ph_suggested_address').prepend('<input type="radio" name="ph_which_to_use" id="ph_radio_orig" value="orig" checked><b> ' + 'Use Original Address: ' + ' </b>' + addr + '');
						jQuery('#ph_suggested_address').prepend('</div>');
						jQuery('#ph_suggested_address').prepend('<b>Please check the address before proceeding. If the address is correct use original address, or, use UPS suggested address.</b><br><br>');

						// Add Checkout Address in hidden fields
						jQuery('#ph_addr_radio').append("<div style='display: hidden;'>");
						jQuery('#ph_addr_radio').append("<input type='hidden' name='ph_addr_orig_addr1' id='ph_addr_orig_addr1' value='" + checkout.address_1 + "'>");
						jQuery('#ph_addr_radio').append("<input type='hidden' name='ph_addr_orig_addr2' id='ph_addr_orig_addr2' value='" + checkout.address_2 + "'>");
						jQuery('#ph_addr_radio').append("<input type='hidden' name='ph_addr_orig_city'' id='ph_addr_orig_city' value='" + checkout.city + "'>");
						if (checkout.state != null) {

							jQuery('#ph_addr_radio').append("<input type='hidden' name='ph_addr_orig_state' id='ph_addr_orig_state' value='" + checkout.state + "'>");
						}
						jQuery('#ph_addr_radio').append("<input type='hidden' name='ph_addr_orig_zip' id='ph_addr_orig_zip' value='" + checkout.postcode + "'>");
						jQuery('#ph_addr_radio').append("<input type='hidden' name='ph_addr_orig_country' id='ph_addr_orig_country' value='" + checkout.country + "'>");
						jQuery('#ph_addr_radio').append("</div>");

						jQuery("#place_order").removeProp("disabled");

						// Update Address Fields on radio button change
						jQuery('input[type=radio][name=ph_which_to_use]').change(function () {
							ph_radio_changed(this);
						});
					}
				}
			},
		});
		
		jQuery('.ph-loading-address-suggestion').remove();
		return false;
	});

	//Handle the radio button change
	function ph_radio_changed(item) {

		//lets copy the data into the appropriate fields
		if (item.value == 'orig') {

			//go with orig values
			addr1 = jQuery('#ph_addr_orig_addr1').val();
			addr2 = jQuery('#ph_addr_orig_addr2').val();
			city = jQuery('#ph_addr_orig_city').val();
			state = jQuery('#ph_addr_orig_state').val();
			zip = jQuery('#ph_addr_orig_zip').val();

		} else {

			//it is one of the corrected fields
			addr1 = jQuery('#ph_addr_corrected_obj_addr1').val();
			addr2 = jQuery('#ph_addr_corrected_obj_addr2').val();
			city = jQuery('#ph_addr_corrected_obj_city').val();
			state = jQuery('#ph_addr_corrected_obj_state').val();
			zip = jQuery('#ph_addr_corrected_obj_zip').val();

		}

		if (jQuery('input[name=ship_to_different_address]').is(':checked')) {

			//shipping to different addr
			jQuery('#shipping_address_1').val(addr1);
			jQuery('#shipping_address_2').val(addr2);
			jQuery('#shipping_city').val(city);
			jQuery('#shipping_state').val(state);
			jQuery('#shipping_postcode').val(zip);

		} else {

			//shipping to billing
			jQuery('#billing_address_1').val(addr1);
			jQuery('#billing_address_2').val(addr2);
			jQuery('#billing_city').val(city);
			jQuery('#billing_state').val(state);
			jQuery('#billing_postcode').val(zip);

			//always update the ship to in case they select it!
			jQuery('#shipping_address_1').val(addr1);
			jQuery('#shipping_address_2').val(addr2);
			jQuery('#shipping_city').val(city);
			jQuery('#shipping_state').val(state);
			jQuery('#shipping_postcode').val(zip);

		}

		//update checkout section when checkbox selected
		jQuery('body').trigger('update_checkout');
	}
});