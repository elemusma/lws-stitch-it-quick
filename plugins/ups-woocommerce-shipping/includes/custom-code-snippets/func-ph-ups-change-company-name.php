<?php

if (!function_exists('ph_ups_change_company_name')) {

    /**
     * Replace Company Name with Attention Name in Shipment confirm requests
     */
    function ph_ups_change_company_name($toAddress, $shipment, $shipFrom, $orderId, $to)
    {
        $upsSettings = get_option('woocommerce_wf_shipping_ups_settings', null);
        
        $changeCompanyName = (isset($upsSettings['change_company_name']) && $upsSettings['change_company_name'] == 'yes') ? true : false;

        if ($changeCompanyName && $to == 'to' && ( empty($toAddress['company']) || $toAddress['company'] == '-' )) {
            
            $toAddress['company'] = isset($toAddress['name']) ? $toAddress['name'] : '-';
        
        } else if ($changeCompanyName && $to == 'billing' && ( empty($toAddress['CompanyName']) || $toAddress['CompanyName'] == '-' )) {
            
            $toAddress['CompanyName'] = isset($toAddress['AttentionName']) ? $toAddress['AttentionName'] : '-';
        }

        return $toAddress;
    }
}
add_filter('ph_ups_address_customization', 'ph_ups_change_company_name', 10, 5);
