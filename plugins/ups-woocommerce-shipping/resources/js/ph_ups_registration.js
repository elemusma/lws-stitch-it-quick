jQuery(document).ready(function () {

	var isAlreadyClickedMigrationConsent = false;
	var isAlreadyClickedRegistrationConsent = false;
	let intervalId = null;
	let upsRegType = '';

	// Handle regsitration button click
	jQuery('#ph-ups-ready-btn, #ph-ups-dap-btn').on('click', function (e) {

		// Disabling both buttons as the registration process is started.
		jQuery('#ph-ups-ready-btn, #ph-ups-dap-btn').prop('disabled', true);

		upsRegType = jQuery(this).attr('data-reg-type');

		// Call the Auth API every 5 seconds to retrieve credentials for registered user
		intervalId = setInterval(ph_check_and_retrieve_access_key, 5000);

		// Open the UPS Registration flow in new tab
		window.open(jQuery(this).attr('data-ups-reg-url'));

	});

	ph_disable_confirmation_button();

	jQuery("#ph_ups_account_migration_form").on("click", function () {

		if (isAlreadyClickedMigrationConsent == false) {

			isAlreadyClickedMigrationConsent = true;

			return true;
		}

		if (isAlreadyClickedMigrationConsent) {

			jQuery(this).attr('disabled', 'disabled').css({ "cursor": "not-allowed" });
		}
	});

	jQuery("#ph_ups_registration_consent_form").on("click", function () {

		if (isAlreadyClickedRegistrationConsent == false) {

			isAlreadyClickedRegistrationConsent = true;

			return true;
		}

		if (isAlreadyClickedRegistrationConsent) {

			jQuery(this).attr('disabled', 'disabled').css({ "cursor": "not-allowed" });
		}
	});

	jQuery("#ph_ups_registration_agreement").on("click", function () {

		ph_disable_confirmation_button();
	});

	jQuery("#ph_ups_re_registration").on("click", function (e) {

		confirmation = prompt('Please enter "YES" to confirm:');

		if (confirmation === null || confirmation != 'YES') {

			alert("Please enter a correct value");
			e.preventDefault();
		}
	});

	jQuery("#ph_ups_rest_re_registration").on("click", function (e) {

		confirmation = prompt('Please enter "YES" to confirm:');

		jQuery("#ph_ups_rest_re_registration").attr('disabled', 'disabled').css({ "cursor": "not-allowed" });
		jQuery(".phReRegistration").css({ "opacity": 0.5 });

		if (confirmation == 'YES') {

			let data = {
				action: 'ph_ups_delete_and_register',
			};
			
			jQuery.post(ph_ups_registration_js.ajaxurl, data, function (result) {
				
				let response = JSON.parse(result);

				if( response.status ) {

					alert(response.message);

					location.reload();

				} else {

					alert(response.message);

					jQuery("#ph_ups_rest_re_registration").removeAttr("disabled").css({ "cursor": "pointer" });
					jQuery(".phReRegistration").css({ "opacity": 1 });
				}
				
			});
		} else {
			alert("Please enter a correct value!!!");

			jQuery("#ph_ups_rest_re_registration").removeAttr("disabled").css({ "cursor": "pointer" });
			jQuery(".phReRegistration").css({ "opacity": 1 });
			
			e.preventDefault();
		}
	});

	/**
	 * Call API to get the auth details
	 */
	async function ph_check_and_retrieve_access_key() {

		const api_headers = ph_ups_registration_js.api_headers;

		let endpoint = ph_ups_registration_js.carrier_ready_reg_api_url;
		
		if ( upsRegType == 'UPS_DAP' ) {
			endpoint = ph_ups_registration_js.carrier_dap_reg_api_url;
		}

		const response = await fetch(endpoint, {
			headers: api_headers
		});

		const data = await response.json();

		const url = data?._links?.accessKey?.href;
		const accountNumber = data?._embedded?.registration[0]?.accountDetails?.accountNumber;

		if (!url) {
			return;
		}

		const accessKeyResponse = await fetch(url, {
			method: 'POST'
		});

		const apiAccessKeyData = await accessKeyResponse.json();

		if (!apiAccessKeyData?.clientId) {
			return;
		}

		const reg_data = {
			action: 'ph_ups_update_registration_data',
			clientId: apiAccessKeyData?.clientId,
			clientSecret: apiAccessKeyData?.secret,
			licenseHash: apiAccessKeyData?.externalClientId,
			accountNumber: accountNumber,
			upsRegAccountType: upsRegType,
		};

		jQuery.post(ph_ups_registration_js.ajaxurl, reg_data, function (result) {
			const response = JSON.parse(result);
			if (response.status) {
				clearInterval(intervalId);
				location.reload();
			}
		});
	}

});

function ph_disable_confirmation_button() {

	if (jQuery('#ph_ups_registration_agreement').is(':checked')) {

		jQuery("#ph_ups_registration_consent_form").removeAttr("disabled").css({ "cursor": "pointer" });
	} else {

		jQuery("#ph_ups_registration_consent_form").attr('disabled', 'disabled').css({ "cursor": "not-allowed" });
	}
}