<?php

class PH_Shipping_UPS_Rest_Tracking {

	const TRACKING_MESSAGE_KEY = "wfupstrackingmsg";

	/**
	 * Handles tracking of UPS REST shipments.
	 *
	 * @param array $shipment_ids Array of shipment IDs to be tracked.
	 * @param int $order_id The ID of the WooCommerce order being processed.
	 * @return array|false Array of responses for each shipment ID, or false on failure.
	 */
	function wf_ups_trackv2_response($shipment_ids, $order_id) {

		// Check for active plugin license
		if (Ph_UPS_Woo_Shipping_Common::phHasActiveLicense()) {

			$api_access_details = Ph_Ups_Endpoint_Dispatcher::phGetApiAccessDetails();

			if (!$api_access_details) {
				wf_admin_notice::add_notice('Failed to get API access token. Please check WooCommerce logs for more information.');
				return false;
			}

			$endpoint = Ph_Ups_Endpoint_Dispatcher::ph_get_enpoint('shipment/tracking');

		} else {

			wf_admin_notice::add_notice('Please use a valid plugin license to continue using WooCommerce UPS Shipping Plugin with Print Label');
			return false;
		}

		$responses = array();

		foreach ($shipment_ids as $shipment_id) {

			$request 	= $this->wf_ups_trackv2_request();

			$headers = array(
				'transId'			=> 'string',
				'transactionSrc'	=> 'testing'
			);

			$body = wp_json_encode(
				array(
					'shipmentId'	=> $shipment_id
				)
			);

			$response = Ph_Ups_Api_Invoker::phCallApi(
				PH_UPS_Config::PH_UPS_PROXY_API_BASE_URL . $endpoint . $request,
				$api_access_details['token'],
				$body,
				$headers
			);

			if (is_wp_error($response) && is_object($response)) {
				$wftrackingmsg = 'WP_Error';
				wp_redirect(admin_url('/post.php?post=' . $order_id . '&action=edit&' . self::TRACKING_MESSAGE_KEY . '=' . $wftrackingmsg . '&Message=' . urlencode($response->get_error_message())));
				exit;
			}

			$responses[$shipment_id] 	= $response;
		}

		return $responses;
	}

	/**
	 * Prepares the request parameters for the UPS tracking API call.
	 *
	 * @return string The query string for the UPS tracking API request.
	 */
	function wf_ups_trackv2_request() {
		$queryParams = array();

		$queryParams = array(
			'locale'			=>	'en_US',
			'returnSignature'	=> 	false,
			'returnMilestones'	=> 	false,
			'returnPOD'			=> 	false
		);

		$queryString = '?' . http_build_query($queryParams);

		return $queryString;
	}
}

?>