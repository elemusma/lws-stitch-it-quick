<?php

if( ! defined('ABSPATH') )	exit;

if( ! class_exists('Ph_Ups_Address_Validation') ) {
	class Ph_Ups_Address_Validation {
		
		// Class Variables Declaration
		public $residential_check=0;
		public $destination;
		public $settings;
		public $debug;
		public $suggested_address;
		public $suggested_display;
		public $wc_logger;

		/**
		 * Constructor
		 */
		public function __construct( $destination = array(), $settings = array() ) {
			$this->destination	= $destination;
			$this->settings		= $settings;
			$this->init();
		}

		/**
		 * Init
		 */
		public function init() {
			$this->debug		= ( ! empty($this->settings['debug']) && $this->settings['debug'] == 'yes' ) ? true : false;

			$this->suggested_address	= ( isset($this->settings['suggested_address']) && !empty($this->settings['suggested_address']) && $this->settings['suggested_address'] == 'yes' ) ? true : false;
			$this->suggested_display	= ( isset($this->settings['suggested_display']) && !empty($this->settings['suggested_display']) && $this->settings['suggested_display'] == 'suggested_radio' ) ? 'suggested_radio' : 'suggested_notice';
			
			if( $this->debug )
				$this->wc_logger = wc_get_logger();
			// $this->
			$xml_request			= $this->get_address_validation_request();
			$xml_response			= $this->get_address_validation_response($xml_request);
			$matched_addresses		= $this->process_response( $xml_response );
			$this->residential_check		= $this->process_response_for_residential_commercial( $xml_response );
		}

		/**
		 * Get Address Validation Request as Xml.
		 * @return string XML Request.
		 */
		public function get_address_validation_request() {

			$address1 = isset($this->destination['address_1'])?$this->destination['address_1']:(isset($this->destination['address'])?$this->destination['address']:'');

			if( isset($this->destination['address_2']) && !empty($this->destination['address_2']) ) {

				$address1 .= ' '.$this->destination['address_2'];
			}

			$request = '<?xml version="1.0" ?>
							<AccessRequest xml:lang="en-US">
								<AccessLicenseNumber>'. $this->settings['access_key'] .'</AccessLicenseNumber>
								<UserId>'. $this->settings['user_id'] .'</UserId>
								<Password>'. $this->settings['password'] .'</Password>
							</AccessRequest>
						<?xml version="1.0" ?>
						<AddressValidationRequest xml:lang="en-US">
							<Request>
								<TransactionReference>
									<CustomerContext>** UPS Address Validation **</CustomerContext>
								</TransactionReference>
								<RequestAction>XAV</RequestAction>
								<RequestOption>3</RequestOption>
							</Request>';

			$request .=		'
								
								<AddressKeyFormat>
								<AddressLine>'.$address1.'</AddressLine>
								
								<PoliticalDivision2>'. $this->destination['city'] .'</PoliticalDivision2>
								<PoliticalDivision1>'. $this->destination['state'] .'</PoliticalDivision1>
								<PostcodePrimaryLow>'. $this->destination['postcode'] .'</PostcodePrimaryLow>
								
								<CountryCode>'. $this->destination['country'] .'</CountryCode>
							</AddressKeyFormat>
						</AddressValidationRequest>';
			return $request;
		}

		/**
		 * Get Address Validation Response.
		 * @param string $request XML request.
		 * @return mixed( bool | string ) Return false on error or Xml Response.
		 */
		public function get_address_validation_response( $request ) {

			$result = [];

			//Check if new registration method
			if (Ph_UPS_Woo_Shipping_Common::phIsNewRegistration()) {
				// Check for active plugin license
				if (Ph_UPS_Woo_Shipping_Common::phHasActiveLicense()) {
					$apiAccessDetails = Ph_Ups_Endpoint_Dispatcher::phGetApiAccessDetails();
					if(!$apiAccessDetails)
					{
						return false;
					}

					$internalEndpoints = isset($apiAccessDetails['internalEndpoints']) && !empty($apiAccessDetails['internalEndpoints']) ? $apiAccessDetails['internalEndpoints'] : [];
					$addressValidationEndpoint = PH_UPS_Config::PH_UPS_PROXY_API_BASE_URL . $internalEndpoints['validated-address']['href'];

					$headers = [
						"Content-Type"  => "application/vnd.ph.carrier.ups.v1+xml"
					];

					$result = Ph_Ups_Api_Invoker::phCallApi($addressValidationEndpoint, $apiAccessDetails['token'], $request, $headers);
				} else {
					Ph_UPS_Woo_Shipping_Common::phAddDebugLog('Please use a valid plugin license to continue using WooCommerce UPS Shipping Plugin with Print Label', $this->debug);
					return false;
				}
			} else {

				$result = wp_remote_post("https://onlinetools.ups.com/ups.app/xml/XAV", array(
					'body'		=>	$request,
					'timeout'	=>	20
				));
			}

			// Handle WP Error
			if( ! is_wp_error($result) )
				$response_body = isset($result['body']) ? $result['body'] : '';
			else
				$error_message = $result->get_error_message();

			// Log the details
			if( $this->debug ) {

				$this->wc_logger->debug( "-------------------- UPS Address Validation Request --------------------". PHP_EOL . $request . PHP_EOL , array( 'source' => PH_UPS_DEBUG_LOG_FILE_NAME ) );

				if( ! empty($error_message) ) {

					$this->wc_logger->alert( "-------------------- UPS Address Validation Response Error --------------------". PHP_EOL . $error_message . PHP_EOL , array( 'source' => PH_UPS_DEBUG_LOG_FILE_NAME ) );

					return false;
				
				} else {

					$this->wc_logger->debug( "-------------------- UPS Address Validation Response --------------------". PHP_EOL . $response_body . PHP_EOL , array( 'source' => PH_UPS_DEBUG_LOG_FILE_NAME ) );
				}
			}

			return ! empty($error_message) ? false : $response_body;
		}

		/**
		 * Process the XML response of Address Validation.
		 * @param string $xml_response Xml Response.
		 * @return 
		 */
		public function process_response( $xml_response ) {
			$response = false;
			libxml_use_internal_errors(true);
			$xml = simplexml_load_string($xml_response);
			if( ! $xml ) {
				if( $this->debug ) {
					$error_message = "Failed loading XML : ".print_r( $xml_response, true ).PHP_EOL;
					foreach(libxml_get_errors() as $error) {
						$error_message = $error_message . $error->message . PHP_EOL;
					}
					$this->wc_logger->alert( "-------------------- UPS Address Validation Response XML Error --------------------". PHP_EOL . $error_message . PHP_EOL , array( 'source' => PH_UPS_DEBUG_LOG_FILE_NAME ) );
				}
			}
			// Match Found
			elseif( isset($xml->{'ValidAddressIndicator'}) ){
				$response = (array) $xml->{'AddressKeyFormat'};
				$suggested_address = null;
			}
			elseif( isset($xml->{'AmbiguousAddressIndicator'}) ){
				$response = (array) $xml->{'AddressKeyFormat'};
			}
			elseif( isset($xml->{'NoCandidatesIndicator'}) ) {
				if( $this->debug )	$this->wc_logger->alert( "-------------------- UPS Address Validation Response Message --------------------". PHP_EOL . "No matching Address found." . PHP_EOL , array( 'source' => PH_UPS_DEBUG_LOG_FILE_NAME ) );
			}

			$suggested_option 	= array();
			
			// Show the Suggested address
			if( $response && !is_admin() ) {

				$addressLine = isset($this->destination['address_1']) ? $this->destination['address_1'] : ( isset($this->destination['address']) ? $this->destination['address'] : '' );
				
				if( $addressLine != $response['AddressLine'] || $this->destination['city'] != $response['PoliticalDivision2'] || $this->destination['state'] != $response['PoliticalDivision1'] ) {

					if( is_array($response['AddressLine']) && isset($response['AddressLine'][0]) ) {

						$street_address = $response['AddressLine'][0];

						$address_1 		= $street_address;
						$address_2 		= '';

						if( isset($response['AddressLine'][1]) ) {

							$street_address .= ', '.$response['AddressLine'][1];
							$address_2 		 = $response['AddressLine'][1];
						}
					} else {

						$street_address = $response['AddressLine'];
						$address_1 		= $street_address;
						$address_2 		= '';
					}

					$ph_states = WC()->countries->states[$response['CountryCode']];
					
					$message = __( 'Suggested Address -', 'ups-woocommerce-shipping' ).'<br/>';
					$message .= __( 'Street Address: ', 'ups-woocommerce-shipping' ).$street_address.'<br/>';
					$message .= __( 'City: ', 'ups-woocommerce-shipping').$response['PoliticalDivision2'].'<br/>';
					$message .= __( 'State: ', 'ups-woocommerce-shipping' ). (isset($ph_states[$response['PoliticalDivision1']]) ? $ph_states[$response['PoliticalDivision1']] : $response['PoliticalDivision1']).'<br/>';
					$message .= __( 'PostCode: ', 'ups-woocommerce-shipping').$response['PostcodePrimaryLow'].(isset( $response['PostcodeExtendedLow'] ) ? '-' . $response['PostcodeExtendedLow'] : '').'<br/>';
					$message .= __( 'Country: ', 'ups-woocommerce-shipping'). WC()->countries->countries[$response['CountryCode']];
					
					$message = apply_filters( 'ph_ups_address_validation_message', $message, $response );
					
					$s_address 	= array(
						'country' 		=> $response['CountryCode'],
						'state' 		=> $response['PoliticalDivision1'],
						'postcode' 		=> $response['PostcodePrimaryLow'],
						'city' 			=> $response['PoliticalDivision2'],
						'address' 		=> $address_1,
						'address_1' 	=> $address_1,
						'address_2' 	=> $address_2,
					);

					$suggested_option = array(
						'checkout_address' 		=> $this->destination,
						'suggested_address' 	=> $s_address,  
					);

					if( ! empty($message) && $this->suggested_address && $this->suggested_display == 'suggested_notice' ) {
						
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
		 * Process the XML response of Address Validation.
		 * @param string $xml_response Xml Response.
		 * @return 
		 */
		public function process_response_for_residential_commercial( $xml_response ) {
			$response = false;
			libxml_use_internal_errors(true);
			$xml = simplexml_load_string($xml_response);
			if( ! $xml ) {
				return 0;
			}
			// Match Found
			elseif( isset($xml->{'AddressKeyFormat'}) && isset($xml->{'AddressKeyFormat'}->{'AddressClassification'}) ){

				$response = (array) $xml->{'AddressKeyFormat'}->{'AddressClassification'};

			}elseif( isset($xml->{'AddressClassification'})  ){

				$response = (array) $xml->{'AddressClassification'};
				
			}
			
			// Show the Suggested address
			if( $response  && is_array($response)) {
				$response=isset($response['Code'])?$response['Code']:0;
			}
			return $response;
		}
	}
}