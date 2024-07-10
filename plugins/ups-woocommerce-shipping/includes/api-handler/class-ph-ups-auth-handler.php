<?php

if (!defined('ABSPATH')) {
	exit;
}

class Ph_Ups_Auth_Handler {
	/**
	 * Get Auth provider token
	 *
	 * @param string $invoker
	 * @return string $token
	 */
	public static function phGetAuthProviderToken($invoker = '') {

		$token          = '';
		$upsSettings    = get_option('woocommerce_' . WF_UPS_ID . '_settings', null);
		$debug          = (isset($upsSettings['debug']) && !empty($upsSettings['debug']) && $upsSettings['debug'] == 'yes') ? true : false;

		$headers = [
			"Content-Type" => "application/json",
		];

		$body = [
			'subject' => 'PH_UPS_PLUGIN',
		];

		$body = wp_json_encode($body);

		if ($invoker == 'ph_iframe') {
			// staging
			$authorization = PH_UPS_Config::PH_UPS_AUTH_PROVIDER_UI_CREDENTIAL;
			$headers['Authorization'] = "Basic $authorization";
		} else {

			$phUPSClientCredentials     = isset($upsSettings['client_credentials']) ? $upsSettings['client_credentials'] : null;
			$headers['Authorization']   = "Basic $phUPSClientCredentials";
		}

		$result = Ph_Ups_Api_Invoker::phCallApi(
			PH_UPS_Config::PH_UPS_AUTH_PROVIDER_TOKEN,
			'',
			$body,
			$headers,
			'POST',
			'auth_token'
		);

		if (!empty($result) && is_array($result) && isset($result['response'])) {

			if (isset($result['response']['code']) && $result['response']['code'] == 200 && isset($result['body'])) {
				$result = json_decode($result['body']);

				// Update access token in transient
				if (isset($result->accessToken) && !empty($result->accessToken)) {

					$token = $result->accessToken;

					// Do not cache Iframe token
					if ($invoker != 'ph_iframe') {
						set_transient('PH_UPS_AUTH_PROVIDER_TOKEN', $token, 1800);
					}
				}

				// Update refresh token in transient
				if (isset($result->refreshToken) && !empty($result->refreshToken)) {
					set_transient('PH_UPS_AUTH_PROVIDER_REFRESH_TOKEN', $result->refreshToken, 1800);
				}
			} else {

				Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------------- Failed to get Authentication Token -------------------------------', $debug);
				Ph_UPS_Woo_Shipping_Common::phAddDebugLog($result['response']['message'], $debug);
			}
		} else {

			Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------------- Failed to get Authentication Token -------------------------------', $debug);
			Ph_UPS_Woo_Shipping_Common::phAddDebugLog(print_r($result, 1), $debug);
		}

		return $token;
	}
}
