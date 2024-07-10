<?php

if (!defined('ABSPATH'))	exit;

if (!class_exists('Ph_Ups_Address_Validation_Rest')) {
	class Ph_Ups_Address_Validation_Rest {

		// Class Variables Declaration
		public $residential_check = 0;
		public $destination;
		public $settings;
		public $debug;
		public $wc_logger;

		/**
		 * Constructor
		 */
		public function __construct($destination = array(), $settings = array()) {
			$this->destination	= $destination;
			$this->settings		= $settings;
			$this->init();
		}

		/**
		 * Init
		 */
		public function init() {

			if ($this->settings['debug'])
				$this->wc_logger = wc_get_logger();

			$json_request				= $this->get_address_validation_request();
			$json_response				= $this->get_address_validation_response($json_request);
			$matched_addresses			= $this->process_response($json_response);
			$this->residential_check	= $this->process_response_for_residential_commercial($json_response);
		}

		/**
		 * Get Address Validation Request as JSON.
		 * @return string JSON Request.
		 */
		public function get_address_validation_request() {

			$jsonRequest = array();
			$address1 = isset($this->destination['address_1']) ? $this->destination['address_1'] : (isset($this->destination['address']) ? $this->destination['address'] : '');

			if (isset($this->destination['address_2']) && !empty($this->destination['address_2'])) {

				$address1 .= ' ' . $this->destination['address_2'];
			}

			// JSON Request
			$jsonRequest['XAVRequest'] = array(
				'Request'		   => array(
					'TransactionReference' 	=> array(
						'CustomerContext'	=> '** UPS Address Validation **'
					),
					'RequestOption'			=> '3',
				),
				'AddressKeyFormat' => array(
					'AddressLine' 			=> array(
						$address1,
					),
					'PoliticalDivision2'	=> $this->destination['city'],
					'PoliticalDivision1'	=> $this->destination['state'],
					'PostcodePrimaryLow'	=> $this->destination['postcode'],
					'CountryCode'			=> $this->destination['country'],
				),
			);

			return $jsonRequest;
		}

		/**
		 * Get Address Validation Response.
		 * @param string $request JSON request.
		 * @return mixed( bool | string ) Return false on error or JSON Response.
		 */
		public function get_address_validation_response($request) {

			$result = [];
			$send_request = wp_json_encode($request, JSON_UNESCAPED_SLASHES);

			$api_access_details = Ph_Ups_Endpoint_Dispatcher::phGetApiAccessDetails();
			$endpoint = Ph_Ups_Endpoint_Dispatcher::ph_get_enpoint('validated-address');


			$result = Ph_Ups_Api_Invoker::phCallApi(
				PH_UPS_Config::PH_UPS_PROXY_API_BASE_URL . $endpoint,
				$api_access_details['token'],
				$send_request
			);

			// Handle WP Error
			if (!is_wp_error($result))
				$response_body = isset($result['body']) ? $result['body'] : '';
			else
				$error_message = $result->get_error_message();

			// Log the details
			if ($this->settings['debug']) {

				$this->wc_logger->debug("-------------------- UPS Address Validation Request --------------------" . PHP_EOL . json_encode($request) . PHP_EOL, array('source' => 'PluginHive-UPS-Error-Debug-Log'));

				if (!empty($error_message)) {

					$this->wc_logger->alert("-------------------- UPS Address Validation Response Error --------------------" . PHP_EOL . $error_message . PHP_EOL, array('source' => 'PluginHive-UPS-Error-Debug-Log'));

					return false;
				} else {

					$this->wc_logger->debug("-------------------- UPS Address Validation Response --------------------" . PHP_EOL . $response_body . PHP_EOL, array('source' => 'PluginHive-UPS-Error-Debug-Log'));
				}
			}

			return !empty($error_message) ? false : $response_body;
		}

		/**
		 * Process the JSON response of Address Validation.
		 * @param string $json_response JSON Response.
		 * @return 
		 */
		public function process_response( $json_response ) {

			$json_response = json_decode($json_response, true);
			$response = false;

			//@note Check below lines of code. Not sure about what to do here

			if( ! $json_response ) {
				if( $this->settings['debug'] ) {
					$error_message = "Failed loading JSON : ".print_r( $json_response, true ).PHP_EOL;
					foreach(libxml_get_errors() as $error) {
						$error_message = $error_message . $error->message . PHP_EOL;
					}
					$this->wc_logger->alert( "-------------------- UPS Address Validation Response XML Error --------------------". PHP_EOL . $error_message . PHP_EOL , array( 'source' => 'PluginHive-UPS-Error-Debug-Log' ) );
				}
			}

			// Match Found
			elseif( isset($json_response['XAVResponse']['ValidAddressIndicator']) ){
				$response = $json_response['XAVResponse']['Candidate'];
				$suggested_address = null;
			} 

			elseif( isset($json_response['XAVResponse']['AmbiguousAddressIndicator']) ){
				$response = $json_response['XAVResponse']['Candidate']; 
			}

			elseif( isset($json_response['XAVResponse']['NoCandidatesIndicator']) ) {
				if( $this->settings['debug'] )	$this->wc_logger->alert( "-------------------- UPS Address Validation Response Message --------------------". PHP_EOL . "No matching Address found." . PHP_EOL , array( 'source' => 'PluginHive-UPS-Error-Debug-Log' ) );
			}

			$suggested_option 	= array();
			
			// Show the Suggested address
			if( $response && !is_admin() ) {
				
				$addressLine = isset($this->destination['address_1']) ? $this->destination['address_1'] : ( isset($this->destination['address']) ? $this->destination['address'] : '' );
				
				// With REST API Multiple Addresses are returned hence taking the first one
				$response = isset($response['AddressKeyFormat']) ? $response : ( is_array($response) ? current($response) : $response );

				if( $addressLine != $response['AddressKeyFormat']['AddressLine'] || $this->destination['city'] != $response['AddressKeyFormat']['PoliticalDivision2'] || $this->destination['state'] != $response['AddressKeyFormat']['PoliticalDivision1'] ) {

					if( is_array($response['AddressKeyFormat']['AddressLine']) && isset($response['AddressKeyFormat']['AddressLine'][0]) ) {

						$street_address = $response['AddressKeyFormat']['AddressLine'][0];

						$address_1 		= $street_address;
						$address_2 		= '';

						if( isset($response['AddressKeyFormat']['AddressLine'][1]) ) {

							$street_address .= ', '.$response['AddressKeyFormat']['AddressLine'][1];
							$address_2 		 = $response['AddressKeyFormat']['AddressLine'][1];
						}
					} else {

						$street_address = $response['AddressKeyFormat']['AddressLine'];
						$address_1 		= $street_address;
						$address_2 		= '';
					}
					
					$message = __( 'Suggested Address -', 'ups-woocommerce-shipping' ).'<br/>';
					$message .= __( 'Street Address: ', 'ups-woocommerce-shipping' ).$street_address.'<br/>';
					$message .= __( 'City: ', 'ups-woocommerce-shipping').$response['AddressKeyFormat']['PoliticalDivision2'].'<br/>';
					$message .= __( 'State: ', 'ups-woocommerce-shipping' ). WC()->countries->states['US'][$response['AddressKeyFormat']['PoliticalDivision1']] .'<br/>';
					$message .= __( 'PostCode: ', 'ups-woocommerce-shipping').$response['AddressKeyFormat']['PostcodePrimaryLow'].(isset( $response['AddressKeyFormat']['PostcodeExtendedLow'] ) ? '-' . $response['AddressKeyFormat']['PostcodeExtendedLow'] : '').'<br/>';
					$message .= __( 'Country: ', 'ups-woocommerce-shipping'). WC()->countries->countries[$response['AddressKeyFormat']['CountryCode']];
					
					$message = apply_filters( 'ph_ups_address_validation_message', $message, $response );
					
					$s_address 	= array(
						'country' 		=> $response['AddressKeyFormat']['CountryCode'],
						'state' 		=> $response['AddressKeyFormat']['PoliticalDivision1'],
						'postcode' 		=> $response['AddressKeyFormat']['PostcodePrimaryLow'],
						'city' 			=> $response['AddressKeyFormat']['PoliticalDivision2'],
						'address' 		=> $address_1,
						'address_1' 	=> $address_1,
						'address_2' 	=> $address_2,
					);

					$suggested_option = array(
						'checkout_address' 		=> $this->destination,
						'suggested_address' 	=> $s_address,  
					);

					if( ! empty($message) && $this->settings['suggested_address'] && 'suggested_notice' == $this->settings['suggested_display'] ) {
						
						wc_clear_notices();
						wc_add_notice( $message );
					}
				}
			}

			if( WC() != null && WC()->session != null ){
				
				WC()->session->set('ph_ups_suggested_address_on_checkout', $suggested_option);
			}

			return $response;
		}
		/**
		 * Process the JSON response of Address Validation.
		 * @param string $json_response JSON Response.
		 * @return 
		 */
		public function process_response_for_residential_commercial($json_response) {
			
			$response = json_decode($json_response, true);

			if (!$response) {
				return 0;
			}
			// Match Found
			elseif (isset($response['XAVResponse']['Candidate']) && isset($response['XAVResponse']['Candidate']['AddressClassification'])) {

				$response = $response['XAVResponse']['Candidate']['AddressClassification'];
			} elseif (isset($response['XAVResponse']['AddressClassification'])) {

				$response = $response['XAVResponse']['AddressClassification'];
			}

			// Show the Suggested address
			if ($response  && is_array($response)) {
				$response = isset($response['Code']) ? $response['Code'] : 0;
			}
			return $response;
		}
	}
}
