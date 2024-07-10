<?php

if (!defined('ABSPATH')) {
    exit;
}

class Ph_Ups_Api_Invoker
{
    /**
     * Call API
     *
     * @param string $endpoint
     * @param string token
     * @param array $body
     * @param string $method
     * @param string $type
     * @return array $result
     */
    public static function phCallApi($endpoint, $token = '', $body = [], $headers = [], $method = 'POST', $type = '')
    {
        $args   = [];
        $result = [];
        $upsSettings = get_option('woocommerce_' . WF_UPS_ID . '_settings', null);
        $debug  = (isset($upsSettings['debug']) && !empty($upsSettings['debug']) && $upsSettings['debug'] == 'yes') ? true : false;

        if (!empty($token)) {
            $headers['Authorization'] = "Bearer $token";

            if ($type != 'auth_token') {

                $apiMode                    = (isset($upsSettings['api_mode']) && !empty($upsSettings['api_mode']) && $upsSettings['api_mode'] == 'Live') ? 'live' : 'sandbox';
                $phUPSClientLicenseHash     = isset($upsSettings['client_license_hash']) ? $upsSettings['client_license_hash'] : null;

                $headers['x-license-key-id']    = $phUPSClientLicenseHash;

                if (Ph_UPS_Woo_Shipping_Common::ph_is_oauth_registered_customer() && !empty(Ph_UPS_Woo_Shipping_Common::ph_get_ups_reg_account_type())) {

                    $headers['x-carrier-registration-type'] = Ph_UPS_Woo_Shipping_Common::ph_get_ups_reg_account_type();
                    $headers['Content-Type'] = "application/vnd.ph.carrier.ups.v2+json";
                }

                $headers['env']                 = $apiMode;
            }
        }

        if (!empty($headers)) {
            $args['headers'] = $headers;
        }

        if (!empty($body)) {
            $args['body'] = $body;
        }

        $args['timeout'] = 30;
        $args['method']  = $method;

        try {

            $result = wp_remote_request($endpoint, $args);

            return $result;
        } catch (Exception $e) {

            Ph_UPS_Woo_Shipping_Common::phAddDebugLog('------------------------------- PH UPS API Invoker Exception -------------------------------', $debug);
            Ph_UPS_Woo_Shipping_Common::phAddDebugLog($e->getMessage(), $debug);
        }

        return $result;
    }
}
