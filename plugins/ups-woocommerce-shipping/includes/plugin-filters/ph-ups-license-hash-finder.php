<?php

defined('ABSPATH') || exit;

if ( !function_exists('ph_ups_fetch_license_hash_and_update_db')) {

	function ph_ups_fetch_license_hash_and_update_db($license_key) {

		$ups_account_type 	= get_option('PH_UPS_REG_ACCOUNT_TYPE');
		$ups_settings 		= get_option('woocommerce_' . WF_UPS_ID . '_settings', []);
		$debug  			= (isset($ups_settings['debug']) && !empty($ups_settings['debug']) && $ups_settings['debug'] == 'yes') ? true : false;

		if( $ups_account_type == 'UPS_DAP' ) {

			$reg_endpoint = PH_UPS_Config::PH_UPS_DAP_REG_API . "/registration?licenseKey=" . $license_key;
		} else {

			$reg_endpoint = PH_UPS_Config::PH_UPS_READY_REG_API . "/registration?licenseKey=" . $license_key;
		}

		if ( !class_exists('Ph_Ups_Auth_Handler') ) {
			include_once plugin_dir_path(__DIR__) . 'api-handler/class-ph-ups-auth-handler.php';
		}

		$auth_token = Ph_Ups_Auth_Handler::phGetAuthProviderToken('ph_iframe');

		if ( empty( $auth_token ) ) {

			Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------------- PH UPS License Hash Update - No token found -------------------------------', $debug);

			return;
		}

		$headers = [
			'Authorization'	=> "Bearer $auth_token",
		];

		$bookmark_response = Ph_Ups_Api_Invoker::phCallApi($reg_endpoint, '', [], $headers, 'GET');

		$response_code 		= wp_remote_retrieve_response_code($bookmark_response);
		$response_message 	= wp_remote_retrieve_response_message($bookmark_response);
		$response_body 		= wp_remote_retrieve_body($bookmark_response);

		if (is_wp_error($bookmark_response) && is_object($bookmark_response)) {

			$error_message = $bookmark_response->get_error_message();

			Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------------- PH UPS License Hash Update - WP Error -------------------------------', $debug);
			Ph_UPS_Woo_Shipping_Common::phAddDebugLog($error_message, $debug);
			
			return;
		}

		$response_obj 	= json_decode($response_body, true);

		Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------------- PH UPS License Hash Update - Registration Response -------------------------------', $debug);
		Ph_UPS_Woo_Shipping_Common::phAddDebugLog(print_r($response_obj, 1), $debug);

		if( $response_code == 200 && isset($response_obj['_links']) && isset($response_obj['_links']['accessKey']) ) {

			$endpoint = $response_obj['_links']['accessKey']['href'];

			$response = Ph_Ups_Api_Invoker::phCallApi($endpoint, '', [], [], 'POST');
			
			$response_code 		= wp_remote_retrieve_response_code($response);
			$response_message 	= wp_remote_retrieve_response_message($response);
			$response_body 		= wp_remote_retrieve_body($response);

			if (is_wp_error($response) && is_object($response)) {

				$error_message = $response->get_error_message();
	
				Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------------- PH UPS License Hash Update - WP Error -------------------------------', $debug);
				Ph_UPS_Woo_Shipping_Common::phAddDebugLog($error_message, $debug);
				
				return;
			}
			
			$response_obj 	= json_decode($response_body, true);

			Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------------- PH UPS License Hash Update - Registration Details -------------------------------', $debug);
			Ph_UPS_Woo_Shipping_Common::phAddDebugLog(print_r($response_obj, 1), $debug);

			if( $response_code == 200 && isset($response_obj['clientId']) ) {

				$client_id 		= $response_obj['clientId'];
				$client_secret 	= $response_obj['secret'];
				$license_hash 	= $response_obj['externalClientId'];

				$ph_client_credentials 	= base64_encode($client_id . ':' . $client_secret);

				$ups_settings['client_credentials'] 	= $ph_client_credentials;
				$ups_settings['client_license_hash'] 	= $license_hash;

				update_option('woocommerce_' . WF_UPS_ID . '_settings', $ups_settings);
			}
		}

		// API Mode
		$apiMode        = (isset($ups_settings['api_mode']) && !empty($ups_settings['api_mode']) && $ups_settings['api_mode'] == 'Live') ? 'live' : 'sandbox';

		// Delete the transient
		delete_transient('PH_UPS_INTERNAL_ENDPOINTS_' . $apiMode);
		delete_transient('PH_UPS_AUTH_PROVIDER_TOKEN');

	}
}

add_action('ph_wc_ups_plugin_license_activated', 'ph_ups_fetch_license_hash_and_update_db', 10, 1);