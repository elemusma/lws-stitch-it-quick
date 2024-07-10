<?php

defined('ABSPATH') || exit;

class Ph_Ups_Endpoint_Dispatcher
{
    /**
     * Fetch internal API endpoints from proxy server
     *
     * @param string $authProviderToken
     * @return array $endpoints
     */
    public static function phGetInternalEndpoints($authProviderToken)
    {
        $result     = [];
        $endpoints  = [];

        $upsSettings    = get_option('woocommerce_' . WF_UPS_ID . '_settings', null);
        $debug          = (isset($upsSettings['debug']) && !empty($upsSettings['debug']) && $upsSettings['debug'] == 'yes') ? true : false;
        $apiMode        = (isset($upsSettings['api_mode']) && !empty($upsSettings['api_mode']) && $upsSettings['api_mode'] == 'Live') ? 'live' : 'sandbox';

        $carrier_slug = Ph_UPS_Woo_Shipping_Common::ph_is_oauth_registered_customer() ? 'ups-rest' : 'ups';

        if (empty(get_transient('PH_UPS_INTERNAL_ENDPOINTS_' . $apiMode))) {

            $result = Ph_Ups_Api_Invoker::phCallApi(PH_UPS_Config::PH_UPS_PROXY_API_BASE_URL . PH_UPS_Config::PH_UPS_CARRIER_ENDPOINT . $carrier_slug, $authProviderToken, [], [], 'GET');

            if (!empty($result) && is_array($result) && isset($result['response'])) {

                if (isset($result['response']['code']) && $result['response']['code'] == 200 && isset($result['body'])) {

                    $result = json_decode($result['body'], 1);
                    $endpoints = $result['_links'];

                    // Update the endpoints in transient
                    set_transient('PH_UPS_INTERNAL_ENDPOINTS_' . $apiMode, $endpoints, 1800);

                    Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------------- PH UPS Set Internal Endpoints -------------------------------', $debug);
                    Ph_UPS_Woo_Shipping_Common::phAddDebugLog( print_r($endpoints, true), $debug);

                } else {

                    Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------------- Failed to get Internal Endpoints -------------------------------', $debug);
                    Ph_UPS_Woo_Shipping_Common::phAddDebugLog($result['response']['message'], $debug);
                }
            } else {

                Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------------- Failed to get Internal Endpoints -------------------------------', $debug);
                Ph_UPS_Woo_Shipping_Common::phAddDebugLog($result, $debug);
            }
        }

        return $endpoints;
    }

    /**
     * Retrieve Auth Provider access token and the internal API endpoints
     *
     * @return array $apiAccessDetails
     */
    public static function phGetApiAccessDetails()
    {
        $upsSettings = get_option('woocommerce_' . WF_UPS_ID . '_settings', null);

        $apiMode = (isset($upsSettings['api_mode']) && !empty($upsSettings['api_mode']) && $upsSettings['api_mode'] == 'Live') ? 'live' : 'sandbox';
        $debug   = (isset($upsSettings['debug']) && !empty($upsSettings['debug']) && $upsSettings['debug'] == 'yes') ? true : false;

        $authProviderToken = get_transient('PH_UPS_AUTH_PROVIDER_TOKEN');
        $internalEndpoints = get_transient('PH_UPS_INTERNAL_ENDPOINTS_' . $apiMode);

        Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------------- PH UPS Get Internal Endpoints -------------------------------', $debug);
        Ph_UPS_Woo_Shipping_Common::phAddDebugLog( print_r($internalEndpoints, true), $debug);

        if (empty($authProviderToken)) {
            include_once('class-ph-ups-auth-handler.php');

            $authProviderToken = Ph_Ups_Auth_Handler::phGetAuthProviderToken();
        }

        if (!empty($authProviderToken) && empty($internalEndpoints)) {

            $internalEndpoints = Ph_Ups_Endpoint_Dispatcher::phGetInternalEndpoints($authProviderToken);
        }

        if(empty($authProviderToken) || empty($internalEndpoints))
        {
            return false;
        }

        $apiAccessDetails = [
            'token'             => $authProviderToken,
            'internalEndpoints' => $internalEndpoints,
        ];

        return $apiAccessDetails;
    }

    /**
     * Return the proxy endpoint for the given proxy service
     *
     * @param string $service
     * @return string 
     */
    public static function ph_get_enpoint($service = '') {

        $api_access_details = self::phGetApiAccessDetails();

        $internal_endpoints = isset($api_access_details['internalEndpoints']) ? $api_access_details['internalEndpoints'] : '';

        return isset($internal_endpoints[$service]['href']) ? $internal_endpoints[$service]['href'] : '';
    }
}
