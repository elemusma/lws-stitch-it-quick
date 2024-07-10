<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

if (!class_exists('Ph_UPS_Woo_Shipping_Common')) {

	/**
	 * Common Class.
	 */
	class Ph_UPS_Woo_Shipping_Common
	{
		
		/**
		 * Wordpress date format.
		 */
		public static $wp_date_format;

		/**
		 * Wordpress date format.
		 */
		public static $wp_time_format;

		/**
		 * Get Wordpress Date format.
		 * 
		 * @return string Wordpress Date format.
		 */
		public static function get_wordpress_date_format()
		{
			if (empty(self::$wp_date_format)) {

				self::$wp_date_format = get_option('date_format');
			}

			return self::$wp_date_format;
		}

		/**
		 * Get Wordpress Time format.
		 * 
		 * @return string Wordpress Time format.
		 */
		public static function get_wordpress_time_format()
		{
			if (empty(self::$wp_time_format)) {

				self::$wp_time_format = get_option('time_format');
			}

			return self::$wp_time_format;
		}

		/**
		 * Get the Order Id/Num
		 * Used for Shipment Description & Reference Number
		 * 
		 * @param  object $orderObject
		 * @param  string $condition
		 * @param  bool $type
		 * @return string Order Id/Num
		 */
		public static function getOrderIdOrNumber($orderObject, $condition = '', $exclucdeText = false)
		{
			/* Info: $exclucdeText will be sent as true when this function should return only Order Id/Number */

			$orderIdOrNum = '';

			// Return Order Id when Condition is not provided
			if( empty($condition) ) {

				return $orderObject->get_id();
			}

			// Get the Value based on Condition
			if ($condition == 'include_order_number') {

				$orderIdOrNum = $orderObject->get_order_number();
			} else if ($condition == 'include_order_id') {

				$orderIdOrNum = $orderObject->get_id();
			}

			// Check for Adding Prefix
			if ($exclucdeText) {

				return $orderIdOrNum;

			} else {

				$orderIdOrNum = 'Order #' . $orderIdOrNum;
			}

			return $orderIdOrNum;
		}

		/**
		 * Check for new registration method
		 */
		public static function phIsNewRegistration()
		{
			$isRegisteredUser = get_option('ph_ups_registered_user', false);

			return $isRegisteredUser;

		}

		/**
		 * Check if current user has active license
		 */
		public static function phHasActiveLicense()
		{
			$phLicenseActivationStatus = get_option('wc_am_client_ups_woocommerce_shipping_activated');
			$phLicenseActivationStatus = $phLicenseActivationStatus == 'Activated' ? true : false;			
			return $phLicenseActivationStatus;
		}

		/**
		 * Adds debug log entries.
		 *
		 * @param mixed $data       The data to be logged.
		 * @param bool  $debugMode  Whether to enable debug mode.
		 * @return void
		 */
		public static function phAddDebugLog($data, $debugMode = false)
		{
			if (function_exists("wc_get_logger") && $debugMode) {

				$log = wc_get_logger();

				if ($data instanceof WP_Error) {
					
                    $data = $data->get_error_message();
                } else if (is_object($data)) {
					
					// JSON encode the object and log it
					$data = json_encode($data);
				}

				$log->debug(($data) . PHP_EOL . PHP_EOL, array('source' => PH_UPS_DEBUG_LOG_FILE_NAME));
			}
		}

		/**
		 * Prepare params for proxy call
		 *
		 * @param array $apiAccessDetails
		 * @param string $type
		 * @return arrya $proxyParams
		 */
		public static function phGetProxyParams($apiAccessDetails, $type = '')
		{
			$proxyParams = [];
			$endpointKey = '';

			$upsSettings    = get_option('woocommerce_' . WF_UPS_ID . '_settings', null);
			$phUpsClientLicenseHash = isset($upsSettings['client_license_hash']) && !empty($upsSettings['client_license_hash']) ? $upsSettings['client_license_hash'] : null;

			$token = $apiAccessDetails['token'];

			$headers = [
				'Authorization: Bearer ' . $token,
				'Content-Type: application/vnd.ph.carrier.ups.v1+xml',
				'x-license-key-id: ' . $phUpsClientLicenseHash
			];

			$headers = implode("\r\n", $headers);

			$options = [
				'stream_context' => stream_context_create([
					'http' => [
						'header' => $headers
					],
				]),
				'trace' => true
			];

			switch($type)
			{
				case 'Landed Cost Estimate' :
					$endpointKey = 'shipment/landed-cost/estimation';
					break;
				case 'Landed Cost Query' :
					$endpointKey = 'shipment/landed-cost/calculation';
					break;
				case 'UPS GFP' :
					$endpointKey = 'ground-with-freight/rates';
					break;
				case 'gfp_shipment' :
					$endpointKey = 'shipment/ground-with-freight/confirmed';
					break;
				case 'gfp_shipment_accept' :
					$endpointKey = 'shipment/ground-with-freight/accepted';
					break;
				case 'gfp_cancel_shipment' :
					$endpointKey = 'shipment/ground-with-freight/cancelled';
					break;
				case 'upload_doc' :
					$endpointKey = 'shipment/document-paperless/documents';
					break;
				case 'push_to_repository' :
					$endpointKey = 'shipment/document-paperless/repository/image';
					break;
			}

			$proxyParams = [
				'options'	=> $options,
				'endpoint'	=> PH_UPS_Config::PH_UPS_PROXY_API_BASE_URL . $apiAccessDetails['internalEndpoints'][$endpointKey]['href']
			];

			return $proxyParams;
		}

		/**
		 * Recursively converts values in an array to strings.
		 *
		 * @param array $request_array The array to be processed.
		 *
		 * @return array The array with values converted to strings.
		 */
		public static function convert_array_values_to_strings($request_array) {
			foreach ($request_array as &$value) {
				if (is_array($value)) {
					$value = self::convert_array_values_to_strings($value); // Recursively convert nested arrays
				} else {
					$value = strval($value); // Convert non-array values to strings
				}
			}
			return $request_array;
		}

		/**
		 * Sorts an array of elements based on a custom "sort" key.
		 *
		 * @param array $a The first element to compare.
		 * @param array $b The second element to compare.
		 * @return int Returns 0 if the elements are equal, -1 if the first element's "sort" key is less than the second, or 1 otherwise.
		 *
		 */
		public static function sort_rates($a, $b)
		{
			if (isset($a['sort']) && isset($b['sort']) && ($a['sort'] == $b['sort'])) return 0;
			return (isset($a['sort']) && isset($b['sort']) && ($a['sort'] < $b['sort'])) ? -1 : 1;
		}

		/**
		 * Loads a WC_Product object or a legacy wf_product object.
		 *
		 * @param mixed $product The product ID, post object, or existing WC_Product object.
		 * @return WC_Product|wf_product|false Returns a WC_Product object on success, a legacy wf_product object
		 *                                      if WC_Product is unavailable, or false if the product cannot be loaded.
		 */
		public static function wf_load_product( $product )
		{
			if (!class_exists('wf_product')) {
				include_once( 'includes/class-wf-legacy.php');
			}
			if (!$product) {
				return false;
			}
			return new wf_product($product);
		}

		/**
		 * Logs a debug message.
		 *
		 * @param string $message      The debug message to log.
		 * @param bool   $debug        Whether to display the debug message as a notice.
		 * @param bool   $silent_debug Whether to suppress the debug message.
		 * @param string $type         The type of notice to display (e.g., 'notice', 'error').
		 *                             Defaults to 'notice'.
		 * @return void
		 */
		public static function debug($message, $debug = false, $silent_debug = false, $type = 'notice')
		{
			// Hard coding to 'notice' as recently noticed 'error' is breaking with wc_add_notice.
			$type = 'notice';
			if ( $debug && function_exists( 'wc_add_notice' ) && ! $silent_debug ) {
				wc_add_notice( $message, $type );
			}
		}

		/**
		 * Check if the current customer has registred using OAuth
		 *
		 * @return bool
		 */
		public static function ph_is_oauth_registered_customer()
		{
			return get_option('PH_UPS_OAUTH_REGISTERED_CUSTOMER', false);
		}

		/**
		 * Get UPS account registration type for OAuth registration
		 */
		public static function ph_get_ups_reg_account_type()
		{
			return get_option('PH_UPS_REG_ACCOUNT_TYPE', '');
		}

		/**
		 * Checks if the SOAP extension is loaded.
		 *
		 * @return bool True if the SOAP extension is loaded, false otherwise.
		 */
		public static function is_soap_available()
		{
			return extension_loaded('soap');
		}

		/**
		 * Build and return UPS registration URL for UPS_READY or UPS_DAP
		 *
		 * @param string $auth_token
		 * @param string $product_order_api_key
		 * @param string $type
		 * @return string $url
		 */
		public static function ph_get_ups_reg_url($auth_token, $product_order_api_key, $type)
		{
			if ($type == 'UPS_READY') {
				$client_id 		= PH_UPS_Config::PH_UPS_READY_CLIENT_ID;
				$redirect_uri 	= PH_UPS_Config::PH_UPS_READY_REDIRECT_URL;
			} else {

				$client_id 		= PH_UPS_Config::PH_UPS_DAP_CLIENT_ID;
				$redirect_uri 	= PH_UPS_Config::PH_UPS_DAP_REDIRECT_URL;
			}

			$redirect_uri_params = [
				'client_id'		=> $client_id,
				'redirect_uri'	=> $redirect_uri,
				'response_type'	=> 'code',
				'scope'			=> 'read',
				'type'			=> 'ups_com_api'
			];

			$redirect_uri_params = http_build_query($redirect_uri_params);

			$redirect_uri = PH_UPS_Config::PH_UPS_LASSO_URI . '?' . $redirect_uri_params;

			$query_params = [
				'token'				=> $auth_token,
				'licenseKey'		=> $product_order_api_key,
				'carrier'			=> $type,
				'connectionMode'	=> PH_UPS_Config::PH_UPS_PROXY_ENV,
				'redirectUrl'		=> $redirect_uri
			];

			$query_params = http_build_query($query_params);

			$url = PH_UPS_Config::PH_UPS_REGISTRATION_UI_URL . '?' . $query_params;

			return $url;
		}

		/**
		 * Banner For Rest API Update.
		 */
		public static function ph_rest_api_updation_banner(){

            $ph_display_banner      = get_option(PH_UPS_BANNER_OPTION_ID, false);

			if ( !$ph_display_banner ) {
				?>	
				<div class="notice ph-ups-notice-banner">
					
					<h3><strong>&#x1F69A;&#x1F504; WooCommerce UPS Shipping Plugin with Print Label - v.6.0.0</strong></h3>
					<p>
						<?php echo __("We are excited to announce the migration of UPS Shipping Plugin to the UPS REST API in this version (v.6.0.0) for enhanced security.<br/>Migration to REST APIs will ensure uninterrupted UPS service for your WooCommerce store as UPS will be phasing out their existing APIs by June 2024.","ups-woocommerce-shipping") ?>
					
						<br><br><?php echo __("Please note that certain features will be affected during this transition:", "ups-woocommerce-shipping") ?>
						<ol>
							<li><?php echo __("Void Shipment","ups-woocommerce-shipping") ?></li>
						</ol>
						<?php echo __("Rest assured, label printing for orders generated before the migration will continue seamlessly.","ups-woocommerce-shipping") ?>
						
						<br><br><?php echo __("Migration Steps [To Be Completed By 3rd June, 2024]:","ups-woocommerce-shipping") ?>
						<ol>
							<li><?php echo __("For Customers with Legacy Credentials (with UPS Access Keys):","ups-woocommerce-shipping") ?>
								<ol>
									<li type="circle"><?php echo __("Void the shipments that need to be voided in your WooCommerce store","ups-woocommerce-shipping") ?></li>
									<li type="circle"><?php echo __("Deactivate the plugin license, and then reactivate it.","ups-woocommerce-shipping") ?></li>
									<li type="circle"><?php echo __("Proceed to the UPS Registration menu & complete the REST migration process by clicking 'Login with UPS Ready' option.","ups-woocommerce-shipping") ?></li>
								</ol>
							</li>
							<li><?php echo __("For Customers with Existing Registration:","ups-woocommerce-shipping") ?>
								<ol>
									<li type="circle"><?php echo __("Choose the “Reregistration” option on the registration page.","ups-woocommerce-shipping") ?></li>
									<li type="circle"><?php echo __("Follow the new REST API registration flow by clicking on the 'Login with UPS READY' option.","ups-woocommerce-shipping") ?></li>
								</ol>
							</li>
						</ol>
						<?php echo __("Team PluginHive thanks you for your cooperation during this migration process.<br>If you have any questions or concerns, please don't hesitate to reach out to our support team.","ups-woocommerce-shipping") ?>
						<p><?php _e('Read More - <a href="https://www.pluginhive.com/knowledge-base/connect-ups-with-woocommerce/" target="_blank">How to Connect UPS Account to WooCommerce?</a>', 'ups-woocommerce-shipping'); ?></p>
					</p>

					<button class="ph-ups-close-rest-api-banner ph-ups-close-notice"><?php _e('Close', 'ups-woocommerce-shipping') ?></button>
					<button class="ph-ups-contact-us"><a href="https://www.pluginhive.com/support/" target='_blank'><?php _e('Contact Us', 'ups-woocommerce-shipping') ?></a></button>
				</div>
				<?php
			}
		}

		/**
		 * Banner option value update.
		 */
		public static function ph_ups_close_rest_api_info_banner() {

			update_option(PH_UPS_BANNER_OPTION_ID, true);
			
			wp_die(json_encode(true));
		}
	}
}
