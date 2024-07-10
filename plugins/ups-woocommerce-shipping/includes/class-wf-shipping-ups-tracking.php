<?php

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

class WF_Shipping_UPS_Tracking
{
	const TRACKING_MESSAGE_KEY 		= "wfupstrackingmsg";
	const TRACK_SHIPMENT_KEY		= "wf_ups_track_shipment";
	const SHIPMENT_IDS_KEY			= "ups_shipment_ids";
	const META_BOX_TITLE		 	= "UPS Shipment Tracking";
	const SHIPPING_METHOD_ID		= WF_UPS_ID;
	const SHIPPING_METHOD_DISPLAY	= "UPS";
	const TRACKING_URL				= "http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums=";
	const TEXT_DOMAIN				= "ups-woocommerce-shipping";

	const SHIPMENT_RETURN_LABEL_DETAILS		= "ups_return_label_details_array";

	public $settings;

	public function __construct()
	{
		$settings_helper 	= new PH_WC_UPS_Settings_Helper();
		$this->settings  = $settings_helper->settings;
		
		if (is_admin()) {

			add_action('add_meta_boxes', array($this, 'ph_add_admin_tracking_metabox'), 15, 2);

			add_action('admin_notices', array($this, 'wf_admin_notice'), 15);

			// Shipment Tracking.
			add_action('woocommerce_process_shop_order_meta', array($this, 'wf_process_order_meta_fields_save'), 15, 2);
		}

		// Shipment Tracking - Customer Order Details Page.
		add_action('woocommerce_order_details_after_order_table', array($this, 'wf_display_customer_track_shipment'));

		if (isset($this->settings['disble_shipment_tracking']) && $this->settings['disble_shipment_tracking'] == 'False') {

			add_action('woocommerce_email_order_meta', array($this, 'wf_add_ups_tracking_info_to_email'), 20);
		}

		add_action('wf_add_ups_tracking_info_to_email_action', array($this, 'wf_add_ups_tracking_info_to_email'), 20);

		// Shipment Tracking - Admin end.
		if (isset($_GET[self::TRACK_SHIPMENT_KEY])) {

			add_action('init', array($this, 'wf_display_admin_track_shipment'), 15);
		}

		// To support Custom Action on Delivery Addon
		if (Ph_UPS_Woo_Shipping_Common::ph_is_oauth_registered_customer()) {
			
			if ( !class_exists('PH_Shipping_UPS_Rest_Tracking') ) {
				include_once('ups_rest/class-wf-shipping-ups-rest-tracking.php');
			}
	
			$ups_rest_tracking = new PH_Shipping_UPS_Rest_Tracking();
			add_filter('ph_get_ups_shipment_tracking_status', array($ups_rest_tracking, 'wf_ups_trackv2_response'), 10, 2);
		} else {
			add_filter('ph_get_ups_shipment_tracking_status', array($this, 'wf_ups_trackv2_response'), 10, 2);
		}

	}

	function wf_user_check()
	{
		if (is_admin()) {
			return true;
		}

		return false;
	}

	function wf_admin_notice()
	{

		if (!isset($_GET[self::TRACKING_MESSAGE_KEY]) && empty($_GET[self::TRACKING_MESSAGE_KEY])) {
			return;
		}

		$isHPOEnabled 	= class_exists('\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController') ? wc_get_container()->get(CustomOrdersTableController::class)->custom_orders_table_usage_is_enabled() : false;
		$order_id 		= $isHPOEnabled ? $_GET['id'] : $_GET['post'];
		$wftrackingmsg 	= $_GET[self::TRACKING_MESSAGE_KEY];

		switch ($wftrackingmsg) {
			case "0":
				echo '<div class="error"><p>' . self::SHIPPING_METHOD_DISPLAY . ': Sorry, Unable to proceed.</p></div>';
				break;
			case "WP_Error":
				echo "<div class='error'><p>" . self::SHIPPING_METHOD_DISPLAY . ": " . urldecode($_GET['Message']) . "</p></div>";
				break;
			case "4":
				echo '<div class="error"><p>' . self::SHIPPING_METHOD_DISPLAY . ': Unable to track the shipment. Please cross check shipment id or try after some time.</p></div>';
				break;
			case "5":
				$wftrackingmsg = PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, self::TRACKING_MESSAGE_KEY);
				echo '<div class="updated"><p>' . $wftrackingmsg . '</p></div>';
				break;
			case "6":
				echo '<div class="updated"><p>' . self::SHIPPING_METHOD_DISPLAY . ': No shipment tracking details.</p></div>';
				break;
			default:
				break;
		}
	}

	function wf_add_ups_tracking_info_to_email($order, $sent_to_admin = false, $plain_text = false)
	{

		$order_id 							= $order->get_id();
		$shipment_id_cs 					= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, self::SHIPMENT_IDS_KEY);
		$ups_return_label_details_array 	= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, self::SHIPMENT_RETURN_LABEL_DETAILS);
		$return_tracking_number 			= array();

		if (!empty($ups_return_label_details_array) && is_array($ups_return_label_details_array)) {

			foreach ($ups_return_label_details_array as $ups_return_label_details) {

				foreach ($ups_return_label_details as $key => $ups_return_label) {

					$return_tracking_number[] 	= $ups_return_label['TrackingNumber'];
				}
			}
		}

		if ($shipment_id_cs != '') {

			$shipment_ids = explode(",", $shipment_id_cs);

			if (empty($shipment_ids)) {
				return;
			}

			$custom_message 		= !empty($this->settings['custom_message']) ? __($this->settings['custom_message'], 'ups-woocommerce-shipping') : __('Your order is shipped via UPS. To track your shipment, please follow the Tracking Number(s) ', 'ups-woocommerce-shipping');
			$return_custom_message 	= !empty($this->settings['custom_message']) ? __($this->settings['custom_message'], 'ups-woocommerce-shipping') : __('In case you use the return labels, then to track the return shipments please follow the Tracking Number(s) ', 'ups-woocommerce-shipping');
			$shipping_title 		= apply_filters('wf_usp_shipment_tracking_email_shipping_title', __('Shipment Tracking Details', 'ups-woocommerce-shipping'), $order);

			echo '<h3>' . __($shipping_title, 'ups-woocommerce-shipping') . '</h3>';

			$order_notice 			=  apply_filters('wf_ups_custom_tracking_message', $custom_message, $order, get_locale());
			$return_order_number 	= '';

			foreach ($shipment_ids as $shipment_id) {

				if ($this->settings['custom_tracking'] && !empty($this->settings['custom_tracking_url'])) {

					if (strpos($this->settings['custom_tracking_url'], '[TRACKING_ID]') !== false) {
						$tracking_url = str_replace("[TRACKING_ID]", $shipment_id, $this->settings['custom_tracking_url']);
					} else {
						$tracking_url = $this->settings['custom_tracking_url'] . $shipment_id;
					}

					if (in_array($shipment_id, $return_tracking_number)) {
						$return_order_number 	.= '<a href="' . $tracking_url . '" target="_blank">' . $shipment_id . '</a>' . ' | ';
					} else {
						$order_notice 			.= '<a href="' . $tracking_url . '" target="_blank">' . $shipment_id . '</a>' . ' | ';
					}
				} else {

					if (in_array($shipment_id, $return_tracking_number)) {
						$return_order_number 	.= '<a href="' . self::TRACKING_URL . $shipment_id . '" target="_blank">' . $shipment_id . '</a>' . ' | ';
					} else {
						$order_notice 			.= '<a href="' . self::TRACKING_URL . $shipment_id . '" target="_blank">' . $shipment_id . '</a>' . ' | ';
					}
				}
			}
			//to remove the '|' from the end
			$order_notice = rtrim($order_notice, ' | ');

			echo '<p>' . __($order_notice, 'ups-woocommerce-shipping') . '</p></br>';

			if (!empty($return_order_number)) {
				$return_order_number = rtrim($return_order_number, ' | ');

				echo '<p>' . __($return_custom_message, 'ups-woocommerce-shipping') . $return_order_number . '</p></br>';
			}
		}
	}

	function wf_display_customer_track_shipment($order)
	{
		$order_id 							= $order->get_id();
		$shipment_id_cs						= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, self::SHIPMENT_IDS_KEY);
		$ups_return_label_details_array 	= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, self::SHIPMENT_RETURN_LABEL_DETAILS);
		$return_tracking_number 			= array();

		if (!empty($ups_return_label_details_array) && is_array($ups_return_label_details_array)) {
			foreach ($ups_return_label_details_array as $ups_return_label_details) {
				foreach ($ups_return_label_details as $key => $ups_return_label) {
					$return_tracking_number[] 	= $ups_return_label['TrackingNumber'];
				}
			}
		}

		if (!$this->tracking_eligibility($order, true)) {
			return;
		}

		if ($shipment_id_cs == '') {
			return;
		}

		$shipment_ids = explode(",", $shipment_id_cs);

		if (empty($shipment_ids)) {
			return;
		}

		$shipment_info = $this->get_shipment_info($order_id, $shipment_id_cs);

		if (empty($shipment_info) || false == $shipment_info) {
			return;
		}

		echo '<h3>' . __('Shipment Tracking Details', 'ups-woocommerce-shipping') . '</h3>';
		echo '<table class="shop_table wooforce_tracking_details">
			<thead>
				<tr>
					<th class="product-name">' . __('Shipment Tracking Number(s)', 'ups-woocommerce-shipping') . '</th>
					<th class="product-total">' . __('Status', 'ups-woocommerce-shipping') . '</th>
				</tr>
			</thead>
			<tfoot>';

		foreach ($shipment_info as $shipment_id => $message) {
			echo '<tr>';

			if ($this->settings['custom_tracking'] && !empty($this->settings['custom_tracking_url'])) {

				if (strpos($this->settings['custom_tracking_url'], '[TRACKING_ID]') !== false) {
					$tracking_url = str_replace("[TRACKING_ID]", $shipment_id, $this->settings['custom_tracking_url']);
				} else {
					$tracking_url = $this->settings['custom_tracking_url'] . $shipment_id;
				}

				if (in_array($shipment_id, $return_tracking_number)) {
					echo '<th scope="row">' . '<a href="' . $tracking_url . '" target="_blank">' . $shipment_id . '</a> ' . __('( Return Tracking Number )', 'ups-woocommerce-shipping') . '</th>';
				} else {
					echo '<th scope="row">' . '<a href="' . $tracking_url . '" target="_blank">' . $shipment_id . '</a></th>';
				}
			} else {

				if (in_array($shipment_id, $return_tracking_number)) {
					echo '<th scope="row">' . '<a href="' . self::TRACKING_URL . $shipment_id . '" target="_blank">' . $shipment_id . '</a> ' . __('( Return Tracking Number )', 'ups-woocommerce-shipping') . '</th>';
				} else {
					echo '<th scope="row">' . '<a href="' . self::TRACKING_URL . $shipment_id . '" target="_blank">' . $shipment_id . '</a></th>';
				}
			}

			echo '<td><span>' . __($message, 'ups-woocommerce-shipping') . '</span></td>';
			echo '</tr>';
		}
		echo '</tfoot>
		</table>';
	}

	function wf_display_admin_track_shipment()
	{
		if (!$this->wf_user_check()) {

			wp_die( esc_html__("You don't have admin privileges to view this page.", "ups-woocommerce-shipping"), '', array('back_link' => 1) );
		}

		$post_id 		= isset($_GET['post']) ? $_GET['post'] : '';
		$shipment_id_cs	= isset($_GET[self::TRACK_SHIPMENT_KEY]) ? $_GET[self::TRACK_SHIPMENT_KEY] : '';

		$admin_notice = '';
		$shipment_info = $this->get_shipment_info($post_id, $shipment_id_cs);

		foreach ($shipment_info as $shipment_id => $message) {
			$admin_notice .= '<strong>' . $shipment_id . ': </strong>' . $message . '</br>';
		}

		$wftrackingmsg = 5;
		PH_UPS_WC_Storage_Handler::ph_add_and_save_meta_data($post_id, self::TRACKING_MESSAGE_KEY, $admin_notice);

		wp_redirect(admin_url('/post.php?post=' . $post_id . '&action=edit&' . self::TRACKING_MESSAGE_KEY . '=' . $wftrackingmsg));
		exit;
	}

	function get_shipment_info($post_id, $shipment_id_cs)
	{
		return $this->wf_ups_track_shipment($post_id, $shipment_id_cs);
	}

	function wf_process_order_meta_fields_save($post_id, $post_object)
	{
		if (isset($_POST[self::SHIPMENT_IDS_KEY])) {

			$shipment_ids = $_POST[self::SHIPMENT_IDS_KEY];

			PH_UPS_WC_Storage_Handler::ph_add_and_save_meta_data($post_id, self::SHIPMENT_IDS_KEY, $shipment_ids);
		}
	}

	function ph_add_admin_tracking_metabox($postType, $postObject)
	{

		$isHPOEnabled 	= class_exists('\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController') ? wc_get_container()->get(CustomOrdersTableController::class)->custom_orders_table_usage_is_enabled() : false;
		$screenType 	= $isHPOEnabled ? wc_get_page_screen_id('shop-order') : 'shop_order';
		$order_object 	= ($postObject instanceof WP_Post) ? wc_get_order($postObject->ID) : (($postObject instanceof WC_Order) ? $postObject : '');

		if (!$this->tracking_eligibility($order_object)) {

			return;
		}

		add_meta_box(

			'PH_UPS_Tracking_Metabox',
			__(self::META_BOX_TITLE, self::TEXT_DOMAIN),
			[$this, 'ph_ups_admin_tracking_metabox'],
			$screenType,
			'side',
			'default'
		);
	}

	function ph_ups_admin_tracking_metabox($postOrOrderObject)
	{
		$order = ($postOrOrderObject instanceof WP_Post) ? wc_get_order($postOrOrderObject->ID) : $postOrOrderObject;

		if (!$order instanceof WC_Order) {

			return;
		}

		$order_id 		= $order->get_id();
		$shipment_ids 	= PH_UPS_WC_Storage_Handler::ph_get_meta_data($order_id, self::SHIPMENT_IDS_KEY);
?>

		<div class="add_label_id">

			<strong>Enter Tracking Number(s) <br /> (Comma Separated)</strong>

			<textarea rows="1" cols="25" class="input-text" id="<?php echo self::SHIPMENT_IDS_KEY; ?>" name="<?php echo self::SHIPMENT_IDS_KEY; ?>" type="text"><?php echo $shipment_ids; ?></textarea>
		</div>

		<?php
		$tracking_url = admin_url('/?post=' . ($order_id));
		?>

		<a class="button button-primary ups_shipment_tracking tips" href="<?php echo $tracking_url; ?>" data-tip="<?php _e('Save/Show Tracking Info', self::TEXT_DOMAIN); ?>"><?php _e('Save/Show Tracking Info', self::TEXT_DOMAIN); ?></a>
		<hr style="border-color:#0074a2">

		<script type="text/javascript">
			jQuery("a.ups_shipment_tracking").on("click", function() {
				location.href = this.href + '&wf_ups_track_shipment=' + jQuery('#ups_shipment_ids').val().replace(/ /g, '');
				return false;
			});
		</script>
<?php
	}

	/**
	 * Find and add additional error message.
	 *
	 * @param string
	 * @return string
	 */
	public function ph_error_notice_handle($error_code)
	{

		if (!class_exists('PH_UPS_Error_Notice_Handle')) {

			include('ph-ups-error-notice-handle.php');
		}

		$error_handel = new PH_UPS_Error_Notice_Handle();

		return $error_handel->ph_find_error_additional_info($error_code);
	}

	function wf_ups_track_shipment($post_id, $shipment_id_cs)
	{
		if (empty($post_id)) {

			$wftrackingmsg = 0;
			wp_redirect(admin_url('/post.php?post=' . $post_id . '&action=edit&' . self::TRACKING_MESSAGE_KEY . '=' . $wftrackingmsg));
			exit;
		}

		$order_object = wc_get_order($post_id);

		if (!($order_object instanceof WC_Order)) {

			return [];
		}

		if (empty($shipment_id_cs)) {

			PH_UPS_WC_Storage_Handler::ph_add_and_save_meta_data($post_id, self::SHIPMENT_IDS_KEY, $shipment_id_cs);

			$wftrackingmsg = 6;
			wp_redirect(admin_url('/post.php?post=' . $post_id . '&action=edit&' . self::TRACKING_MESSAGE_KEY . '=' . $wftrackingmsg));
			exit;
		}

		$prev_shipment_ids = PH_UPS_WC_Storage_Handler::ph_get_meta_data($post_id, self::SHIPMENT_IDS_KEY);

		if (!empty($prev_shipment_ids))	// For Different Service Tracking Numbers
		{
			$shipment_id_cs .= ',' . $prev_shipment_ids;
		}

		$shipment_ids 		= preg_split('@,@', $shipment_id_cs, -1, PREG_SPLIT_NO_EMPTY);
		$shipment_ids 		= array_unique($shipment_ids);

		$shipment_id_cs 	= implode(',', $shipment_ids);

		PH_UPS_WC_Storage_Handler::ph_add_and_save_meta_data($post_id, self::SHIPMENT_IDS_KEY, $shipment_id_cs);

		$shipment_ids 		= explode(",", $shipment_id_cs);

		if (Ph_UPS_Woo_Shipping_Common::ph_is_oauth_registered_customer()) {

			if (!class_exists('PH_Shipping_UPS_Rest_Tracking')) {
				include_once('ups_rest/class-wf-shipping-ups-rest-tracking.php');
			}

			$PH_Shipping_UPS_Rest_Tracking = new PH_Shipping_UPS_Rest_Tracking();

			$responses 			= $PH_Shipping_UPS_Rest_Tracking->wf_ups_trackv2_response($shipment_ids, $post_id);
		} else {
			$responses 			= $this->wf_ups_trackv2_response($shipment_ids, $post_id);
		}

		if (empty($responses)) {
			$wftrackingmsg = 4;
			wp_redirect(admin_url('/post.php?post=' . $post_id . '&action=edit&' . self::TRACKING_MESSAGE_KEY . '=' . $wftrackingmsg));
			exit;
		}

		$shipment_info		= array();

		if (Ph_UPS_Woo_Shipping_Common::ph_is_oauth_registered_customer()) {

			foreach ($responses as $shipment_id => $response) {
				$response_obj		= json_decode($response['body'], true);

				if (isset($response_obj['response']['errors'])) {
					$error_code 	= (string)$response_obj['response']['errors'][0]['code'];
					$error_desc 	= (string)$response_obj['response']['errors'][0]['message'];

					$additional_info = $this->ph_error_notice_handle($error_code);

					$message		= $error_desc . ' [Error Code: ' . $error_code . ']' . $additional_info;

					$shipment_info[$shipment_id] = $message;
				} else {

					foreach ($response_obj['trackResponse']['shipment'] as $trackinfo) {
						
						if ( isset($trackinfo['package']) && is_array($trackinfo['package']) ) {

							foreach ($trackinfo['package'] as $track_package) {
								if (isset($track_package['activity'][0]) && $track_package['activity'][0]['status']['description'] != '') {
									$message 	= (string)$track_package['activity'][0]['status']['description'];
									$shipment_info[(string)$track_package['trackingNumber']] = $message;
								} else if (isset($track_package['currentStatus']['description']) && $track_package['currentStatus']['description'] != '') {
									$message 	= (string)$track_package['currentStatus']['description'];
									$shipment_info[(string)$trackinfo['inquiryNumber']] = $message;
								} else {
									$message 	= 'Unable to track this number.';
									$shipment_info[$shipment_id] = $message;
								}
							}
						}
					}
				}
			}
		} else {

			foreach ($responses as $shipment_id => $response) {
				$response_obj		= simplexml_load_string($response['body']);
				$response_code 		= (string)$response_obj->Response->ResponseStatusCode;

				if ('0' == $response_code) {
					$error_code 	= (string)$response_obj->Response->Error->ErrorCode;
					$error_desc 	= (string)$response_obj->Response->Error->ErrorDescription;

					$additional_info = $this->ph_error_notice_handle($error_code);

					$message		= $error_desc . ' [Error Code: ' . $error_code . ']' . $additional_info;

					$shipment_info[$shipment_id] = $message;
				} else {

					$trackinfo			= $response_obj->Shipment;

					if (isset($trackinfo->Error)) {
						$message 		= (string)$trackinfo->Error->Description . ' [' . $trackinfo->Error->Number . ']>';
						$shipment_info[(string)$trackinfo->Package->TrackingNumber] = $message;
					} else {
						if (isset($trackinfo->Package->Activity[0]) && $trackinfo->Package->Activity[0]->Status->StatusType->Description != '') {
							$message 	= (string)$trackinfo->Package->Activity[0]->Status->StatusType->Description . '';
							$shipment_info[(string)$trackinfo->Package->TrackingNumber] = $message;
						} else if ($trackinfo->CurrentStatus->Description != '') {
							$message 	= (string)$trackinfo->CurrentStatus->Description . '';
							$shipment_info[(string)$trackinfo->InquiryNumber->Value] = $message;
						} else {
							$message 	= 'Unable to track this number.';
							$shipment_info[$shipment_id] = $message;
						}
					}
				}
			}
		}

		return $shipment_info;
	}

	function wf_ups_trackv2_response($shipment_ids, $order_id)
	{
		// Load Shipping Method Settings.
		$settings		= get_option('woocommerce_' . self::SHIPPING_METHOD_ID . '_settings', null);
		$settings		= apply_filters('ph_ups_plugin_settings', $settings, $order_id);

		$endpoint	= '';

		if ("Live" == $this->settings['api_mode']) {
			$endpoint = 'https://onlinetools.ups.com/ups.app/xml/Track';
		} else {
			$endpoint = 'https://wwwcie.ups.com/ups.app/xml/Track';
		}

		// New registration method with active plugin license key
		$isNewAndActiveRegistration = false;

		if (Ph_UPS_Woo_Shipping_Common::phIsNewRegistration()) {
			// Check for active plugin license
			if (Ph_UPS_Woo_Shipping_Common::phHasActiveLicense()) {
				
				$isNewAndActiveRegistration = true;
				$apiAccessDetails = Ph_Ups_Endpoint_Dispatcher::phGetApiAccessDetails();
				$internalEndpoints = $apiAccessDetails['internalEndpoints'];

				$endpoint = PH_UPS_Config::PH_UPS_PROXY_API_BASE_URL . $internalEndpoints['shipment/tracking']['href'];
			}
		}

		$responses = array();
		foreach ($shipment_ids as $shipment_id) {

			$request 	= $this->wf_ups_trackv2_request($shipment_id, $this->settings['user_id'], $this->settings['password'], $this->settings['access_key'], $order_id);

			//Check if new registration method
			if ($isNewAndActiveRegistration) {
				$headers = [
					"Content-Type"  => "application/vnd.ph.carrier.ups.v1+xml"
				];

				$response = Ph_Ups_Api_Invoker::phCallApi($endpoint, $apiAccessDetails['token'], $request, $headers);
			} else {

				$response	= wp_remote_post(
					$endpoint,
					array(
						'timeout'   => 70,
						'sslverify' => 0,
						'body'      => $request
					)
				);
			}

			if (is_wp_error($response)) {
				$wftrackingmsg = 'WP_Error';
				wp_redirect(admin_url('/post.php?post=' . $order_id . '&action=edit&' . self::TRACKING_MESSAGE_KEY . '=' . $wftrackingmsg . '&Message=' . urlencode($response->get_error_message())));
				exit;
			}

			$responses[$shipment_id] 	= $response;
		}

		return $responses;
	}

	function wf_ups_trackv2_request($shipment_id, $user_id, $password, $access_key, $order_id)
	{
		$xml_request = '';

		if (!Ph_UPS_Woo_Shipping_Common::ph_is_oauth_registered_customer()) {

			$xml_request 	.= '<?xml version="1.0" ?>';
			$xml_request 	.= '<AccessRequest xml:lang="en-US">';
			$xml_request 	.= '<AccessLicenseNumber>' . $access_key . '</AccessLicenseNumber>';
			$xml_request 	.= '<UserId>' . $user_id . '</UserId>';
			$xml_request 	.= '<Password>' . $password . '</Password>';
			$xml_request 	.= '</AccessRequest>';
			$xml_request 	.= '<?xml version="1.0" ?>';
		}

		$xml_request 	.= '<TrackRequest>';
		$xml_request 	.= '<Request>';
		$xml_request 	.= '<TransactionReference>';
		$xml_request 	.= '<CustomerContext>' . $order_id . '</CustomerContext>';
		$xml_request 	.= '</TransactionReference>';
		$xml_request 	.= '<RequestAction>Track</RequestAction>';
		$xml_request 	.= '</Request>';
		$xml_request 	.= '<TrackingNumber>' . $shipment_id . '</TrackingNumber>';

		// Mail Innovation Tracking ID contains all numeric characters and 26 Characters
		// ctype_digit() - Returns TRUE if every character in the string text is a decimal digit, FALSE otherwise
		if (ctype_digit($shipment_id) && strlen($shipment_id) > 18) {
			$xml_request 	.= '<IncludeMailInnovationIndicator></IncludeMailInnovationIndicator>';
		}

		$xml_request 	.= '</TrackRequest>';

		$request 		= str_replace(array("\n", "\r"), '', $xml_request);

		return $request;
	}

	function tracking_eligibility($order, $for_consumer = false)
	{
		return $this->check_ups_tracking_eligibility($order, $for_consumer);
	}

	function check_ups_tracking_eligibility($order, $for_consumer)
	{
		$eligibility = false;

		if (!$order) return false;

		if ($this->settings['disble_shipment_tracking'] != 'True') {
			if (true == $for_consumer && 'TrueForCustomer' == $this->settings['disble_shipment_tracking']) {
				$eligibility = false;
			} else {
				$eligibility = true;
			}
		}

		return $eligibility;
	}

	function wf_get_ups_shipping_service_data($order)
	{
		//TODO: Take the first shipping method. The use case of multiple shipping method for single order is not handled.

		$shipping_methods = $order->get_shipping_methods();
		if (!$shipping_methods) {
			return false;
		}

		$shipping_method = array_shift($shipping_methods);
		$shipping_service_tmp_data = explode(':', $shipping_method['method_id']);

		if ((count($shipping_service_tmp_data) < 2)) {
			return false;
		}

		$shipping_service_data['shipping_method'] 		= $shipping_service_tmp_data[0];
		$shipping_service_data['shipping_service'] 		= $shipping_service_tmp_data[1];
		$shipping_service_data['shipping_service_name']	= $shipping_method['name'];

		return $shipping_service_data;
	}
}

new WF_Shipping_UPS_Tracking();

?>