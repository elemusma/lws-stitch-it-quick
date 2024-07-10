<?php

defined('ABSPATH') || exit();

class PH_UPS_Registration_Admin_Ajax {

	/**
	 * PH_UPS_Registration_Admin_Ajax constructor
	 */
	public function __construct() {

		add_action('wp_ajax_ph_ups_update_registration_data', array($this, 'ph_ups_update_registration_data'));

		// Delete the Current Registration
		add_action('wp_ajax_ph_ups_delete_and_register', array($this, 'ph_ups_delete_and_register'));
	}

	/**
	 * Update registration data in the DB
	 */
	function ph_ups_update_registration_data() {

		$clientId 		= isset($_POST['clientId']) ? $_POST['clientId'] : '';
		$clientSecret 	= isset($_POST['clientSecret']) ? $_POST['clientSecret'] : '';
		$licenseHash 	= isset($_POST['licenseHash']) ? $_POST['licenseHash'] : '';
		$shipper_number = isset($_POST['accountNumber']) ? $_POST['accountNumber'] : '';
		$account_type	= isset($_POST['upsRegAccountType']) ? $_POST['upsRegAccountType'] : '';

		$phClientCredentials 	= base64_encode($clientId . ':' . $clientSecret);
		$upsSettings 			= get_option('woocommerce_' . WF_UPS_ID . '_settings', []);
		$debug  				= (isset($upsSettings['debug']) && !empty($upsSettings['debug']) && $upsSettings['debug'] == 'yes') ? true : false;

		$upsSettings['user_id'] 			= "phiveUser";
		$upsSettings['password'] 			= "V0UDstWPY4nu5w=";
		$upsSettings['access_key'] 			= "PO4QDH9DL8WZOPC5";

		// Shipper number will be available for OAuth registration (UPS_READY & UPS_DAP)
		$upsSettings['shipper_number'] 		= !empty($shipper_number) ? $shipper_number : "PHIVEAB1234AB1234AB111";

		$upsSettings['client_credentials'] 	= $phClientCredentials;
		$upsSettings['client_license_hash'] = $licenseHash;

		if (!empty($shipper_number)) {
			update_option('PH_UPS_OAUTH_REGISTERED_CUSTOMER', true);
			update_option('PH_UPS_REG_ACCOUNT_TYPE', $account_type);
			update_option('PH_UPS_REGISTRATION_DATE', date('Y-m-d H:i'));
		}

		update_option('ph_ups_registered_user', true);

		Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------------- PH UPS Registration Successful -------------------------------', $debug);
		Ph_UPS_Woo_Shipping_Common::phAddDebugLog( "Account Number: " . $shipper_number, $debug);
		Ph_UPS_Woo_Shipping_Common::phAddDebugLog( "Account Type: " . $account_type, $debug);
		Ph_UPS_Woo_Shipping_Common::phAddDebugLog( "Registration Date: " . date('Y-m-d H:i'), $debug);

		update_option('woocommerce_' . WF_UPS_ID . '_settings', $upsSettings);

		$response = array("status" => 1, "error" => 0, "data" => array(), "message" => 'Success');

		echo json_encode($response);
		wp_die();
	}

	/**
	 * Remove Account & Re-register
	 */
	function ph_ups_delete_and_register() {

		$ups_account_type 		= get_option('PH_UPS_REG_ACCOUNT_TYPE');
		$product_order_api_key 	= get_option('ph_client_ups_product_order_api_key');

		$upsSettings 	= get_option('woocommerce_' . WF_UPS_ID . '_settings', []);
		
		$apiMode        = (isset($upsSettings['api_mode']) && !empty($upsSettings['api_mode']) && $upsSettings['api_mode'] == 'Live') ? 'live' : 'sandbox';

		// Delete the transient
		delete_transient('PH_UPS_INTERNAL_ENDPOINTS_' . $apiMode);
		delete_transient('PH_UPS_AUTH_PROVIDER_TOKEN');
		
		if (!empty($ups_account_type)) {

			if (!class_exists('Ph_Ups_Auth_Handler')) {

				include_once(plugin_dir_path(__DIR__) . "api-handler/class-ph-ups-auth-handler.php");
			}

			$debug  		= (isset($upsSettings['debug']) && !empty($upsSettings['debug']) && $upsSettings['debug'] == 'yes') ? true : false;
			$auth_token		= Ph_Ups_Auth_Handler::phGetAuthProviderToken('ph_iframe');

			if (empty($auth_token)) {
				
				$response = array("status" => 0, "error" => 1, "data" => array(), "message" => __("Authorization Token not found! Please contact PluginHive Support.", "ups-woocommerce-shipping"));
				
				echo json_encode($response);
				wp_die();
			}

			$headers = [
				'Content-Type' => 'application/vnd.phive.external.carrier.v2+json',
				'Accept'		=> 'application/vnd.phive.external.carrier.v2+json',
				'Authorization'	=> "Bearer $auth_token",
			];

			if ($ups_account_type == 'UPS_READY') {

				$ready_url 	= PH_UPS_Config::PH_UPS_READY_REG_API . "/registration?licenseKey=$product_order_api_key";

				$ready_response 	= Ph_Ups_Api_Invoker::phCallApi($ready_url, '', [], $headers, 'DELETE');

				$response_code 		= wp_remote_retrieve_response_code($ready_response);
				$response_message 	= wp_remote_retrieve_response_message($ready_response);
				$response_body 		= wp_remote_retrieve_body($ready_response);

				Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------------- PH UPS Delete Account - UPS Ready -------------------------------', $debug);
				Ph_UPS_Woo_Shipping_Common::phAddDebugLog($response_body, $debug);

				if ($response_code == 204) {

					$upsSettings['client_credentials'] 	= null;
					$upsSettings['client_license_hash'] = null;

					update_option('PH_UPS_OAUTH_REGISTERED_CUSTOMER', false);
					update_option('ph_ups_registered_user', false);

					update_option('woocommerce_' . WF_UPS_ID . '_settings', $upsSettings);
					update_option('PH_UPS_REG_ACCOUNT_TYPE', '');
					update_option('PH_UPS_REGISTRATION_DATE', '');

					$response = array("status" => 1, "error" => 0, "data" => array(), "message" => 'Existing UPS Account removed. Continue to re-register.');
				} else {

					$response = array("status" => 0, "error" => 1, "data" => array(), "message" => $response_message);
				}
			} else {

				$dap_profile 	= PH_UPS_Config::UPS_DAP_REGISTRATION_API_PROFILE . "/registration?licenseKey=$product_order_api_key";
				$dap_token 		= PH_UPS_Config::UPS_DAP_REGISTRATION_API_TOKEN . "/registration?licenseKey=$product_order_api_key";
				$dap_account 	= PH_UPS_Config::UPS_DAP_REGISTRATION_API_ACCOUNT . "/registration?licenseKey=$product_order_api_key";
				$dap_promo 		= PH_UPS_Config::UPS_DAP_REGISTRATION_API_PROMO . "/registration?licenseKey=$product_order_api_key";

				// Delete DAP Promo.
				$dap_response 	= Ph_Ups_Api_Invoker::phCallApi($dap_promo, '', [], $headers, 'DELETE');

				$response_code 		= wp_remote_retrieve_response_code($dap_response);
				$response_message 	= wp_remote_retrieve_response_message($dap_response);
				$response_body 		= wp_remote_retrieve_body($dap_response);

				Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------------- PH UPS Delete Account - UPS DAP Promo -------------------------------', $debug);
				Ph_UPS_Woo_Shipping_Common::phAddDebugLog($response_body, $debug);

				if ($response_code == 204) {

					$upsSettings['client_credentials'] 	= null;
					$upsSettings['client_license_hash'] = null;

					update_option('PH_UPS_OAUTH_REGISTERED_CUSTOMER', false);
					update_option('ph_ups_registered_user', false);

					update_option('woocommerce_' . WF_UPS_ID . '_settings', $upsSettings);
					update_option('PH_UPS_REG_ACCOUNT_TYPE', '');
					update_option('PH_UPS_REGISTRATION_DATE', '');

					$response = array("status" => 1, "error" => 0, "data" => array(), "message" => 'Existing UPS Account removed. Continue to re-register.');

					// Delete DAP Account.
					$dap_response 	= Ph_Ups_Api_Invoker::phCallApi($dap_account, '', [], $headers, 'DELETE');

					$response_code 		= wp_remote_retrieve_response_code($dap_response);
					$response_body 		= wp_remote_retrieve_body($dap_response);

					Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------------- PH UPS Delete Account - UPS DAP Account -------------------------------', $debug);
					Ph_UPS_Woo_Shipping_Common::phAddDebugLog($response_body, $debug);

					if ($response_code == 204) {

						// Delete DAP Token.
						$dap_response 	= Ph_Ups_Api_Invoker::phCallApi($dap_token, '', [], $headers, 'DELETE');

						$response_code 		= wp_remote_retrieve_response_code($dap_response);
						$response_body 		= wp_remote_retrieve_body($dap_response);

						Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------------- PH UPS Delete Account - UPS DAP Token -------------------------------', $debug);
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog($response_body, $debug);

						if ($response_code == 204) {

							// Delete DAP Profile.
							$dap_response 	= Ph_Ups_Api_Invoker::phCallApi($dap_profile, '', [], $headers, 'DELETE');

							$response_code 		= wp_remote_retrieve_response_code($dap_response);
							$response_body 		= wp_remote_retrieve_body($dap_response);

							Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------------- PH UPS Delete Account - UPS DAP Profile -------------------------------', $debug);
							Ph_UPS_Woo_Shipping_Common::phAddDebugLog($response_body, $debug);
						}
					}
				} else {

					$response = array("status" => 0, "error" => 1, "data" => array(), "message" => $response_message);
				}
			}
		} else {

			$response = array("status" => 0, "error" => 1, "data" => array(), "message" => __("UPS Program and Account number combination not found! Please contact PluginHive Support.", "ups-woocommerce-shipping"));
		}

		echo json_encode($response);
		wp_die();
	}
}

new PH_UPS_Registration_Admin_Ajax();
