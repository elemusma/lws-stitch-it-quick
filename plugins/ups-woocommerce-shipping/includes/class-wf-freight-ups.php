<?php
Class wf_freight_ups{
	
	// Class Variables Declaration
	private $parent;
	private $show_est_delivery;
	private $density_unit;
	private $density_length;
	private $density_width;
	private $density_height;
	private $units;
	private $origin_country;
	private $origin_state;
	
	function __construct($parent)
	{
		$this->parent=$parent;
		$this->show_est_delivery = isset($this->parent->show_est_delivery)?$this->parent->show_est_delivery:'';

		if( isset($this->parent->enable_density_based_rating) && $this->parent->enable_density_based_rating )
		{
			$this->density_unit 	= $this->parent->dim_unit;
			$this->density_length 	= $this->parent->density_length;
			$this->density_width 	= $this->parent->density_width;
			$this->density_height 	= $this->parent->density_height;

			if( $this->density_length == 0 )
			{
				$this->density_length = ( $this->density_unit == 'IN' ) ? 47 : 119;
			}
			
			if( $this->density_width == 0 )
			{
				$this->density_width = ( $this->density_unit == 'IN' ) ? 47 : 119;
			}
			
			if( $this->density_height == 0 )
			{
				$this->density_height = ( $this->density_unit == 'IN' ) ? 47 : 119;
			}			
		}
	}
	
	/**
	 * 
	 * @param array $package Calculate_shipping packages
	 * @param int $code Service Code
	 * @param array $ups_packages Array of ups packages after packing algorithms has been applied
	 * @return array Freight rate requests
	 */
	function get_rate_request( $package, $code, $ups_packages )
	{
		
		$json_req=array();
		$commodity = $this->get_commodity_from_packages( $ups_packages );
		
		$json_req['UPSSecurity'] = array(
				"UsernameToken"	=>	array(
						"Username"	=>	$this->parent->user_id,
						"Password"	=>	str_replace( '&', '&amp;', $this->parent->password )
				),
				"ServiceAccessToken"	=>	array(
						"AccessLicenseNumber"	=>	$this->parent->access_key
				),
		);

		if( $this->parent->freight_payment_information == '10' )
		{
			$payer_name 		= !empty($this->parent->ups_display_name) ? $this->parent->ups_display_name : "Shipper";
			$payor_addressline  = empty($this->parent->origin_addressline_2) ? $this->parent->origin_addressline : array($this->parent->origin_addressline,$this->parent->origin_addressline_2);
			$payor_city 		= $this->parent->origin_city;
			$payor_state 		= $this->parent->origin_state;
			$payor_postcode 	= $this->parent->origin_postcode;
			$payor_countrycode 	= $this->parent->origin_country;
		}else{
			$payer_name 		= $this->parent->freight_thirdparty_contact_name;
			$payor_addressline  = empty($this->parent->freight_thirdparty_addressline_2) ? $this->parent->freight_thirdparty_addressline : array($this->parent->freight_thirdparty_addressline,$this->parent->freight_thirdparty_addressline_2);
			$payor_city 		= $this->parent->freight_thirdparty_city;
			$payor_state 		= $this->parent->freight_thirdparty_state;
			$payor_postcode 	= $this->parent->freight_thirdparty_postcode;
			$payor_countrycode 	= $this->parent->freight_thirdparty_country;
		}

		$address = '';

		if ( !empty($package['destination']['address_1']) ) {

			$address = $package['destination']['address_1'];

			if( isset($package['destination']['address_2']) && !empty($package['destination']['address_2']) ) {

				$address = $address.' '.htmlspecialchars($package['destination']['address_2']);
			}

		} elseif ( !empty($package['destination']['address']) ) {

			$address = $package['destination']['address'];
		}

		$destination_city 		= strtoupper( $package['destination']['city'] );
		$destination_country 	= "";

		if ( ( "PR" == $package['destination']['state'] ) && ( "US" == $package['destination']['country'] ) ) {		
				$destination_country = "PR";
		} else {
				$destination_country = $package['destination']['country'];
		}

		if ( $this->parent->ship_from_address_different_from_shipper == 'yes' && $this->parent->ship_from_address_for_freight ) {

			$shipfrom			= !isset( $this->parent->ship_from_addressline_2 ) || empty( $this->parent->ship_from_addressline_2 ) ? ( isset( $this->parent->ship_from_addressline ) ? $this->parent->ship_from_addressline : '' ) : array( $this->parent->ship_from_addressline, $this->parent->ship_from_addressline_2 );
			$origin_city		= isset( $this->parent->ship_from_city ) && !empty( $this->parent->ship_from_city ) ? $this->parent->ship_from_city : '';
			$origin_state		= isset( $this->parent->ship_from_state ) && !empty( $this->parent->ship_from_state ) ? $this->parent->ship_from_state : '';
			$origin_postcode	= isset( $this->parent->ship_from_postcode ) && !empty( $this->parent->ship_from_postcode ) ? $this->parent->ship_from_postcode : '';
			$origin_country		= isset( $this->parent->ship_from_country ) && !empty( $this->parent->ship_from_country ) ? $this->parent->ship_from_country : '';
		} else {

			$shipfrom			= empty( $this->parent->origin_addressline_2 ) ? $this->parent->origin_addressline : array( $this->parent->origin_addressline, $this->parent->origin_addressline_2 );
			$origin_city		= $this->parent->origin_city;
			$origin_state		= $this->parent->origin_state;
			$origin_postcode	= $this->parent->origin_postcode;
			$origin_country		= $this->parent->origin_country;
		}

		$json_req['FreightRateRequest']	=	array(
				"Request"	=>	array(
						"RequestOption"=>"1",
						"TransactionReference"=>array("TransactionIdentifier"=>"Freight Rate Request")
				),
				"ShipFrom"	=>	array(
						"Name"		=>$this->parent->ups_display_name,
						"Address"	=>array(
								"AddressLine"		=> $shipfrom,
								"City"				=> $origin_city,
								"StateProvinceCode"	=> $origin_state,
								"PostalCode"		=> $origin_postcode,
								"CountryCode"		=> $origin_country,
						),                                                            
				),
			   "ShipperNumber"	=>	$this->parent->shipper_number,
			   "ShipTo"	=>	array(
						"Name"=>"Receipient",
						"Address"=>array(
								"AddressLine"		=> $address,
								"City"				=> $destination_city,
								"StateProvinceCode"	=> $package['destination']['state'],
								"PostalCode"		=> $package['destination']['postcode'],
								"CountryCode"		=> $destination_country,
						),
				),
			   "PaymentInformation"=>array(
						"Payer"=>array(
								"Name"		=> $payer_name,
								"Address"	=> array(
										"AddressLine"		=> $payor_addressline,
										"City"				=> $payor_city,
										"StateProvinceCode"	=> $payor_state,
										"PostalCode"		=> $payor_postcode,
										"CountryCode"		=> $payor_countrycode,
								),
								"ShipperNumber"	=> $this->parent->shipper_number,
						),
						"ShipmentBillingOption"	=> array(
								"Code"	=> (string)$this->parent->freight_payment_information,
						)
				),
			   "Service"	=> array(
						"Code"	=> "$code"
				),
			   // "ShipmentRatingOptions"=>array("NegotiatedRatesIndicator"=>"0"),		// Node doesn't exist 
			   "HandlingUnitOne"	=> array(
						"Quantity"	=> "1",
						"Type"		=> array(
								"Code"	=> (string)$this->parent->freight_handling_unit_one_type_code
						)
				),
				 "Commodity"=> $commodity,
		);

		if( $this->parent->enable_density_based_rating )
		{
			if( ! empty($ups_packages[0]['Package']['Dimensions']) ) {
				$this->density_unit 	= $ups_packages[0]['Package']['Dimensions']['UnitOfMeasurement']['Code'];
				$this->density_length 	= round($ups_packages[0]['Package']['Dimensions']['Length'], 2);
				$this->density_width 	= round($ups_packages[0]['Package']['Dimensions']['Width'], 2);
				$this->density_height 	= round($ups_packages[0]['Package']['Dimensions']['Height'], 2);
			}

			$json_req['FreightRateRequest']['DensityEligibleIndicator'] = "";
			$json_req['FreightRateRequest']['HandlingUnits'] = array(
				"Quantity"		=> "1",
				"Type"			=> array(
					"Code"		=> (string)$this->parent->freight_handling_unit_one_type_code
				),
				"Dimensions"	=>array(
					"UnitOfMeasurement"	=> array(
						"Code"	=> (string) $this->density_unit,
					),
					'Length'	=> (string) $this->density_length,
					'Width'		=> (string) $this->density_width,
					'Height'	=> (string) $this->density_height,
				),
				
			);
		}

		// To get Estimated delivery
		if($this->show_est_delivery) {
			$json_req['FreightRateRequest']['TimeInTransitIndicator'] = "";
		}

		if (( isset($this->parent->freight_holiday_pickup) && $this->parent->freight_holiday_pickup ) || 
			( isset($this->parent->freight_inside_pickup) && $this->parent->freight_inside_pickup ) || 
			( isset($this->parent->freight_residential_pickup) && $this->parent->freight_residential_pickup) || 
			( isset($this->parent->freight_weekend_pickup) && $this->parent->freight_weekend_pickup ) || 
			( isset($this->parent->freight_liftgate_pickup) && $this->parent->freight_liftgate_pickup ) || 
			( isset($this->parent->freight_limitedaccess_pickup) && $this->parent->freight_limitedaccess_pickup )) {
			
			$json_req['FreightRateRequest']['ShipmentServiceOptions']['PickupOptions'] = array();

			if( $this->parent->freight_holiday_pickup ) {
				$json_req['FreightRateRequest']['ShipmentServiceOptions']['PickupOptions']['HolidayPickupIndicator']="";
			}

			if( $this->parent->freight_inside_pickup ) {
				$json_req['FreightRateRequest']['ShipmentServiceOptions']['PickupOptions']['InsidePickupIndicator']="";
			}

			if( $this->parent->freight_residential_pickup ) {
				$json_req['FreightRateRequest']['ShipmentServiceOptions']['PickupOptions']['ResidentialPickupIndicator']="";
			}

			if( $this->parent->freight_weekend_pickup ) {
				$json_req['FreightRateRequest']['ShipmentServiceOptions']['PickupOptions']['WeekendPickupIndicator']="";
			}

			if( $this->parent->freight_liftgate_pickup ) {
				$json_req['FreightRateRequest']['ShipmentServiceOptions']['PickupOptions']['LiftGateRequiredIndicator']="";
			}

			if( $this->parent->freight_limitedaccess_pickup ) {
				$json_req['FreightRateRequest']['ShipmentServiceOptions']['PickupOptions']['LimitedAccessPickupIndicator']="";
			}
		}

		if (( isset($this->parent->freight_holiday_delivery) && $this->parent->freight_holiday_delivery ) || 
			( isset($this->parent->freight_inside_delivery) && $this->parent->freight_inside_delivery ) || 
			( isset($this->parent->freight_weekend_delivery) && $this->parent->freight_weekend_delivery) || 
			( isset($this->parent->freight_liftgate_delivery) && $this->parent->freight_liftgate_delivery ) || 
			( isset($this->parent->freight_limitedaccess_delivery) && $this->parent->freight_limitedaccess_delivery ) || 
			( isset($this->parent->freight_call_before_delivery) && $this->parent->freight_call_before_delivery )){

			$json_req['FreightRateRequest']['ShipmentServiceOptions']['DeliveryOptions'] = array();

			if( $this->parent->freight_holiday_delivery ) {
				$json_req['FreightRateRequest']['ShipmentServiceOptions']['DeliveryOptions']['HolidayDeliveryIndicator']="";
			}

			if( $this->parent->freight_inside_delivery ) {
				$json_req['FreightRateRequest']['ShipmentServiceOptions']['DeliveryOptions']['InsideDeliveryIndicator']="";
			}

			if( $this->parent->freight_weekend_delivery ) {
				$json_req['FreightRateRequest']['ShipmentServiceOptions']['DeliveryOptions']['WeekendDeliveryIndicator']="";
			}

			if( $this->parent->freight_liftgate_delivery ) {
				$json_req['FreightRateRequest']['ShipmentServiceOptions']['DeliveryOptions']['LiftGateRequiredIndicator']="";
			}

			if( $this->parent->freight_limitedaccess_delivery ) {
				$json_req['FreightRateRequest']['ShipmentServiceOptions']['DeliveryOptions']['LimitedAccessDeliveryIndicator']="";
			}

			if( $this->parent->freight_call_before_delivery ) {
				$json_req['FreightRateRequest']['ShipmentServiceOptions']['DeliveryOptions']['CallBeforeDeliveryIndicator']="";
			}
		}

		if( $this->parent->residential ) {

			$json_req['FreightRateRequest']['ShipTo']['Address']['ResidentialAddressIndicator'] = "";
			$json_req['FreightRateRequest']['ShipmentServiceOptions']['DeliveryOptions']['ResidentialDeliveryIndicator'] = "";
		}

		$json_req  = apply_filters( 'ph_ups_freight_rate_request', $json_req, $package, $ups_packages );
		$requests = json_encode($json_req);
		return $requests;
	}
	
	/**
	 * Get the Commodities from UPS packages .
	 * @param array $package UPS packages
	 * @return array Array of commodity for every packages.
	 */
	function get_commodity_from_packages( $ups_packages) {

	    $commodities = array();
		
		if( is_array($ups_packages) ) {
			$weight=0;
			foreach( $ups_packages as $key => $package ) {
				if( ! empty($package['Package']['PackageWeight']) ) {
					$weight = round($package['Package']['PackageWeight']['Weight'], 2);
				}
				

			if( ! empty($weight) ) {
				$commodity	=	array(
					'Description'	=>'Freight',
					'Weight'	=>array(
						'Value'				=> (string) $weight,
						'UnitOfMeasurement'	=> array('Code'=>(string) $ups_packages[$key]['Package']['PackageWeight']['UnitOfMeasurement']['Code'] )
					),
				);
				
				if( ! empty($ups_packages[$key]['Package']['Dimensions']) ) {
					$commodity['Dimensions']	=	array(
						'UnitOfMeasurement'	=>	array(
							'Code'	=>	(string)$ups_packages[$key]['Package']['Dimensions']['UnitOfMeasurement']['Code']
						),
						'Length'	=>	(string) round($ups_packages[$key]['Package']['Dimensions']['Length'], 2),
						'Width'		=>	(string) round($ups_packages[$key]['Package']['Dimensions']['Width'], 2),
						'Height'	=>	(string) round($ups_packages[$key]['Package']['Dimensions']['Height'], 2)
					);
				}
				$commodity['NumberOfPieces']	= "1";
				$commodity['PackagingType']		= array(
					'Code'=>(string)$this->parent->freight_package_type_code
				);
				$commodity['FreightClass']		= (string)$this->parent->freight_class;

					$commodities[]			= $commodity;					//package_key	=> corresponding Commodity
			}
				}
			}
		return $commodities;
	}
	
	/**
	 * Get the Freight Confirm Shipment request.
	 * @param array $shipment_package_data Shipment package
	 * @return json Freight request for confirm shipment 
	 */
	public function create_shipment_request( $shipment_package_data, $order_id = '' )
	{
		$json_req = array();

		if( isset($_GET['wf_ups_shipment_confirm']) ) {

			$query_string		= explode( '|', base64_decode($_GET['wf_ups_shipment_confirm']) );
			$order_id			= end( $query_string );
			$auto_bulk_label 	= false;
		} else {
			$auto_bulk_label 	= true;
		}

		$order			= wc_get_order($order_id);

		if( $this->parent->settings['ship_from_address'] == 'origin_address') {
			$from_address	= $this->get_shop_address();
			$to_address		= $this->get_order_address($order);
		}
		else {
			$from_address	= $this->get_order_address($order);
			$to_address		= $this->get_shop_address();
		}

		$billing_option 	= isset( $this->parent->settings['freight_payment'] ) && !empty( $this->parent->settings['freight_payment'] ) ? $this->parent->settings['freight_payment'] : '10';

		if( $billing_option == '10' )
		{
			$payor_address 		= $this->get_shop_address();
		}else{
			$payor_address 		= $this->get_payor_address();
		}

		$shipping_service		= in_array( $shipment_package_data['shipping_service'], array( 308, 309, 334, 349 )) ? $shipment_package_data['shipping_service'] : null ;
		$shipping_service_name	= $this->parent->freight_services[$shipping_service];

		$json_req['UPSSecurity']=array(
			"UsernameToken"=>array(
				"Username"=>$this->parent->settings['user_id'],
				"Password"=>str_replace( '&', '&amp;', $this->parent->settings['password'] )
			),
			"ServiceAccessToken"=>array(
				"AccessLicenseNumber"=>$this->parent->settings['access_key']
			),

		);

		$shipper_address_1  = substr( htmlspecialchars($from_address['address_1']), 0, 34 );
		$shipper_address_2  = substr( htmlspecialchars($from_address['address_2']), 0, 34 );
		$shipfrom 			= empty($shipper_address_2) ? $shipper_address_1 : array($shipper_address_1,$shipper_address_2);

		$shipto_address_1  	= substr( htmlspecialchars($to_address['address_1']), 0, 34 );
		$shipto_address_2  	= substr( htmlspecialchars($to_address['address_2']), 0, 34 );
		$shipto 			= empty($shipto_address_2) ? $shipto_address_1 : array($shipto_address_1,$shipto_address_2);

		$payor_address_1  	= substr( htmlspecialchars($payor_address['address_1']), 0, 34 );
		$payor_address_2  	= substr( htmlspecialchars($payor_address['address_2']), 0, 34 );
		$payor 				= empty($payor_address_2) ? $payor_address_1 : array($payor_address_1,$payor_address_2);

		$json_req['FreightShipRequest']	= array(
			"Request"	=> array(
				"RequestOption"=>"1",
				"TransactionReference"=>array("TransactionIdentifier"=>"Freight Shipment Request")
			),
			"Shipment"	=> array(
				"ShipFrom"=>array(
					"Name"=>htmlspecialchars( $from_address['company'] ),
					'Address'		=>	array(
						'AddressLine'		=>	$shipfrom,
						'City'				=>	$from_address['city'],
						'StateProvinceCode'	=>	$from_address['state'],
						'PostalCode'		=>	$from_address['postcode'],
						'CountryCode'		=>	$from_address['country'],
					),
					'AttentionName'	=>	htmlspecialchars( $from_address['name'] ),
					'Phone'=>array(
						'Number'	=>	(strlen($from_address['phone']) < 10) ? '0000000000' :  htmlspecialchars( $from_address['phone'] )
					),
				),
				"ShipperNumber"	=> $this->parent->settings['shipper_number'],
				"ShipTo"	=> array(
					'Name'	=>	htmlspecialchars( $to_address['company'] ),
					'Address'		=>	array(
						'AddressLine'		=>	$shipto,
						'City'				=>	$to_address['city'],
						'StateProvinceCode'=>	$to_address['state'],
						'PostalCode'		=>	$to_address['postcode'],
						'CountryCode'		=>	$to_address['country'],
					),
					'AttentionName'	=>	htmlspecialchars( $to_address['name'] ),												
					'Phone'=>array(
						'Number'	=>	(strlen( $to_address['phone'] ) < 10) ? '0000000000' : htmlspecialchars( $to_address['phone'] )
					),
				),
				"PaymentInformation"=>array(
					"Payer"=>array(
						'Name'			=>	htmlspecialchars( $payor_address['name'] ),
						'Address'		=>	array(
							'AddressLine'			=>	$payor,
							'City'					=>	$payor_address['city'],
							'StateProvinceCode'		=>	$payor_address['state'],
							'PostalCode'			=>	$payor_address['postcode'],
							'CountryCode'			=>	$payor_address['country'],
						),
						'ShipperNumber'	=>	$this->parent->settings['shipper_number'],
						'AttentionName'	=>	htmlspecialchars( $this->parent->settings['ups_display_name'] ),
						'Phone'=>array(
							'Number'	=>	$payor_address['phone'],
						),
					),
					"ShipmentBillingOption"=>array(
						"Code"	=> $billing_option,
					)
				),
				"Service"=>array(
					'Code'			=>	"$shipping_service",
					'Description'	=>	htmlspecialchars( $shipping_service_name ),	
				),
				"HandlingUnitOne"=>array(
					"Quantity"	=> "1",
					"Type"		=> array(
						"Code"	=> "PLT"
					)
				),
				"Commodity"	=> array(),
				"Reference"	=> array(
					"Number"	=> array(
								"Code" 		=> "28", 		// Reference Number Code
								"Value"		=> (string) $order_id,
							),
				),
				"Documents"	=> array(
					"Image"	=> array(
						"Type"	=> array(
							"Code"	=> "20"
						),
						"Format"	=> array(
							"Code"	=> "01"
						)
					)
				)
			),
		);

		if( isset($this->parent->settings['enable_density_based_rating']) && $this->parent->settings['enable_density_based_rating'] == 'yes' )
		{

			$this->units	= isset( $this->parent->settings['units'] ) ? $this->parent->settings['units'] : 'imperial';

			if ( $this->units == 'metric' ) {
				$this->density_unit	= 'CM';
			} else {
				$this->density_unit	= 'IN';
			}

			$this->density_length 	= ( isset( $this->parent->settings['density_length'] ) && !empty( $this->parent->settings['density_length'] ) ) ? $this->parent->settings['density_length'] : 0;
			$this->density_width 	= ( isset( $this->parent->settings['density_width'] ) && !empty( $this->parent->settings['density_width'] ) ) ? $this->parent->settings['density_width'] : 0;
			$this->density_height 	= ( isset( $this->parent->settings['density_height'] ) && !empty( $this->parent->settings['density_height'] ) ) ? $this->parent->settings['density_height'] : 0;

			if( $this->density_length == 0 )
			{
				$this->density_length = ( $this->density_unit == 'IN' ) ? 47 : 119;
			}
			
			if( $this->density_width == 0 )
			{
				$this->density_width = ( $this->density_unit == 'IN' ) ? 47 : 119;
			}
			
			if( $this->density_height == 0 )
			{
				$this->density_height = ( $this->density_unit == 'IN' ) ? 47 : 119;
			}	

			if( ! empty($shipment_package_data['packages'][0]['Package']['Dimensions']) ) {
				$this->density_unit 	= $shipment_package_data['packages'][0]['Package']['Dimensions']['UnitOfMeasurement']['Code'];
				$this->density_length 	= round($shipment_package_data['packages'][0]['Package']['Dimensions']['Length'], 2);
				$this->density_width 	= round($shipment_package_data['packages'][0]['Package']['Dimensions']['Width'], 2);
				$this->density_height 	= round($shipment_package_data['packages'][0]['Package']['Dimensions']['Height'], 2);
			}

			$json_req['FreightShipRequest']['Shipment']['DensityEligibleIndicator'] = "";
			$json_req['FreightShipRequest']['Shipment']['HandlingUnits'] = array(
				"Quantity"		=> "1",
				"Type"			=> array(
					"Code"		=> "PLT",
				),
				"Dimensions"	=>array(
					"UnitOfMeasurement"	=> array(
						"Code"	=> (string) $this->density_unit,
					),
					'Length'	=> (string) $this->density_length,
					'Width'		=> (string) $this->density_width,
					'Height'	=> (string) $this->density_height,
				),
				
			);
		}

		$commodities=array();
		$index=0;
		foreach( $shipment_package_data['packages'] as $package )
		{
				$package 		= $package['Package'];
				$description 	= '';

				if( isset($package['items']) && !empty($package['items']) ) {

					$product_array 	= array();

					foreach ($package['items'] as $product) {
						
						$product_id 	= $product->get_id();

						if( isset($product_array[$product_id]) ) {

							$product_array[$product_id]['quantity']++;
						}else{

							$product_array[$product_id] = array(
								'name'		=> $product->get_name(),
								'quantity'	=> 1,
							);
						}
					}

					if(!empty($product_array)) {

						foreach ($product_array as $id => $data) {
							
							$description .= $data['name']." x ".$data['quantity'].', ';
						}

						$description = rtrim($description, ', ');

					}else{

						$description 	= "Freight Package";
					}
				}else{

					$description 	= "Freight Package";
				}

				// Description length is 755
				$description = ( strlen( $description ) >= 755 ) ? substr( $description, 0, 750 ).'...' : $description;

				$commodity	=	array(
					'Description'	=> $description,
					'Weight'		=> array(
						'Value'				=> (string) round($package['PackageWeight']['Weight'], 2),
						'UnitOfMeasurement'	=> array(
							'Code'	=>	(string) $package['PackageWeight']['UnitOfMeasurement']['Code'])
					),
				);
				
				try{
					if(isset($package['Dimensions']['UnitOfMeasurement']) && isset($package['Dimensions']['UnitOfMeasurement']['Code']) )
					{
						$unit=$package['Dimensions']['UnitOfMeasurement']['Code'];
					}else
					{
						$unit="IN";
					}

					$commodity['Dimensions']    =    array(
						'UnitOfMeasurement'    =>    array(
							'Code'    =>    (string) $unit
						),
						'Length'    =>    (string) ( isset($package['Dimensions']['Length']) ? round($package['Dimensions']['Length'], 2) : "0" ),
						'Width'     =>    (string) ( isset($package['Dimensions']['Width']) ? round($package['Dimensions']['Width'], 2) : "0" ),
						'Height'    =>    (string) ( isset($package['Dimensions']['Height']) ? round($package['Dimensions']['Height'], 2) : "0" ),
					);
				}catch(Exception $ex){    }					

				$commodity['NumberOfPieces']	= "1";
				$commodity['PackagingType']		= array('Code'=>"PLT");
				$commodity['FreightClass']		= "50";

				if( isset($_GET['FreightPackagingType']) && !empty($_GET['FreightPackagingType']) ) {

					$commodity['PackagingType']		= array('Code'=>$_GET['FreightPackagingType']);

				} else if ($auto_bulk_label) {

					$commodity['PackagingType'] 	= array('Code'=>$this->parent->freight_packaging_type);
				}
				
				if( isset($_GET['FreightClass']) && !empty($_GET['FreightClass']) ) {

					$commodity['FreightClass']	= $_GET['FreightClass'];

				} else if ($auto_bulk_label) {

					$commodity['FreightClass']	= $this->parent->freight_class;
				}

				$commodities[]=$commodity;			

		}
					
		$json_req['FreightShipRequest']['Shipment']['Commodity'] 				= $commodities;
		$json_req['FreightShipRequest']['Shipment']['TimeInTransitIndicator']	= "";
		$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']	= array();

		if( isset($_GET['PickupInstructions']) && !empty($_GET['PickupInstructions']) ) {

			$json_req['FreightShipRequest']['Shipment']['PickupInstructions'] = $_GET['PickupInstructions'];

		} else if ( $auto_bulk_label && !empty($this->parent->freight_pickup_inst) ) {

			$json_req['FreightShipRequest']['Shipment']['PickupInstructions'] = $this->parent->freight_pickup_inst;
		}

		if( isset($_GET['PickupInstructions']) && !empty($_GET['PickupInstructions']) ) {

			$json_req['FreightShipRequest']['Shipment']['DeliveryInstructions ']=$_GET['DeliveryInstructions'];

		} else if ( $auto_bulk_label && !empty($this->parent->freight_delivery_inst) ) {

			$json_req['FreightShipRequest']['Shipment']['DeliveryInstructions'] = $this->parent->freight_delivery_inst;
		}

		if(	(isset($_GET['HolidayPickupIndicator']) && $_GET['HolidayPickupIndicator']==="true") ||
			(isset($_GET['InsidePickupIndicator']) && $_GET['InsidePickupIndicator']==="true")  ||
			(isset($_GET['ResidentialPickupIndicator']) && $_GET['ResidentialPickupIndicator']==="true")  ||
			(isset($_GET['WeekendPickupIndicator']) && $_GET['WeekendPickupIndicator']==="true")  ||
			(isset($_GET['LiftGateRequiredIndicator']) && $_GET['LiftGateRequiredIndicator']==="true")  ||
			(isset($_GET['LimitedAccessPickupIndicator'])  && $_GET['LimitedAccessPickupIndicator']==="true") )
		{

			$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['PickupOptions']=array();

			if( isset($_GET['HolidayPickupIndicator']) && $_GET['HolidayPickupIndicator']==="true" ) {
				$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['PickupOptions']['HolidayPickupIndicator']="";
			}

			if(isset($_GET['InsidePickupIndicator']) && $_GET['InsidePickupIndicator']==="true" ) {
				$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['PickupOptions']['InsidePickupIndicator']="";
			}

			if(isset($_GET['ResidentialPickupIndicator']) && $_GET['ResidentialPickupIndicator']==="true" ) {
				$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['PickupOptions']['ResidentialPickupIndicator']="";
			}

			if(isset($_GET['WeekendPickupIndicator']) && $_GET['WeekendPickupIndicator']==="true" ) {
				$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['PickupOptions']['WeekendPickupIndicator']="";
			}

			if(isset($_GET['LiftGateRequiredIndicator']) && $_GET['LiftGateRequiredIndicator']==="true"  ) {
				$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['PickupOptions']['LiftGateRequiredIndicator']="";
			}

			if(isset($_GET['LimitedAccessPickupIndicator']) && $_GET['LimitedAccessPickupIndicator']==="true"  ) {
				$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['PickupOptions']['LimitedAccessPickupIndicator']="";
			}

		} else if ($auto_bulk_label) {

			$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['PickupOptions']=array();

			if( $this->parent->freight_holiday_pickup ) {
				$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['PickupOptions']['HolidayPickupIndicator']="";
			}

			if( $this->parent->freight_inside_pickup ) {
				$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['PickupOptions']['InsidePickupIndicator']="";
			}

			if( $this->parent->freight_residential_pickup ) {
				$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['PickupOptions']['ResidentialPickupIndicator']="";
			}

			if( $this->parent->freight_weekend_pickup ) {
				$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['PickupOptions']['WeekendPickupIndicator']="";
			}

			if( $this->parent->freight_liftgate_pickup ) {
				$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['PickupOptions']['LiftGateRequiredIndicator']="";
			}

			if( $this->parent->freight_limitedaccess_pickup ) {
				$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['PickupOptions']['LimitedAccessPickupIndicator']="";
			}
		}

		if(	(isset($_GET['HolidayDeliveryIndicator']) && $_GET['HolidayDeliveryIndicator']==="true") ||
			(isset($_GET['InsideDeliveryIndicator']) && $_GET['InsideDeliveryIndicator']==="true")  ||
			(isset($_GET['WeekendDeliveryIndicator']) && $_GET['WeekendDeliveryIndicator']==="true")  ||
			(isset($_GET['LiftGateDeliveryIndicator']) && $_GET['LiftGateDeliveryIndicator']==="true")  ||
			(isset($_GET['LimitedAccessDeliveryIndicator']) && $_GET['LimitedAccessDeliveryIndicator']==="true")  ||
			(isset($_GET['CallBeforeDeliveryIndicator'])  && $_GET['CallBeforeDeliveryIndicator']==="true") )
		{

			$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['DeliveryOptions']=array();

			if( isset($_GET['HolidayDeliveryIndicator']) && $_GET['HolidayDeliveryIndicator']==="true" ) {
				$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['DeliveryOptions']['HolidayDeliveryIndicator']="";
			}

			if(isset($_GET['InsideDeliveryIndicator']) && $_GET['InsideDeliveryIndicator']==="true" ) {
				$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['DeliveryOptions']['InsideDeliveryIndicator']="";
			}

			if(isset($_GET['WeekendDeliveryIndicator']) && $_GET['WeekendDeliveryIndicator']==="true" ) {
				$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['DeliveryOptions']['WeekendDeliveryIndicator']="";
			}

			if(isset($_GET['LiftGateDeliveryIndicator']) && $_GET['LiftGateDeliveryIndicator']==="true" ) {
				$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['DeliveryOptions']['LiftGateRequiredIndicator']="";
			}

			if(isset($_GET['LimitedAccessDeliveryIndicator']) && $_GET['LimitedAccessDeliveryIndicator']==="true"  ) {
				$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['DeliveryOptions']['LimitedAccessDeliveryIndicator']="";
			}

			if(isset($_GET['CallBeforeDeliveryIndicator']) && $_GET['CallBeforeDeliveryIndicator']==="true"  ) {
				$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['DeliveryOptions']['CallBeforeDeliveryIndicator']="";
			}

		} else if ($auto_bulk_label) {

			$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['DeliveryOptions']=array();

			if( $this->parent->freight_holiday_delivery ) {
				$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['DeliveryOptions']['HolidayDeliveryIndicator']="";
			}

			if( $this->parent->freight_inside_delivery ) {
				$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['DeliveryOptions']['InsideDeliveryIndicator']="";
			}

			if( $this->parent->freight_weekend_delivery ) {
				$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['DeliveryOptions']['WeekendDeliveryIndicator']="";
			}

			if( $this->parent->freight_liftgate_delivery ) {
				$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['DeliveryOptions']['LiftGateRequiredIndicator']="";
			}

			if( $this->parent->freight_limitedaccess_delivery ) {
				$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['DeliveryOptions']['LimitedAccessDeliveryIndicator']="";
			}

			if( $this->parent->freight_call_before_delivery ) {
				$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['DeliveryOptions']['CallBeforeDeliveryIndicator']="";
			}
		}

		if( $this->parent->residential ) {

			$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['DeliveryOptions']['ResidentialDeliveryIndicator'] = "";
		}

		$json_req = apply_filters( 'ph_ups_freight_create_shipment_request', $json_req, $to_address );
		return json_encode($json_req);	
	}
	
	private function get_order_address( $order ){
		//Address standard followed in all xadapter plugins. 
		$company = $order->get_shipping_company();
		return array(
			'name'		=> $order->get_shipping_first_name().' '.$order->get_shipping_last_name(),
			'company' 	=> !empty($company) ? $company : $order->get_shipping_first_name().' '.$order->get_shipping_last_name(),
			'phone' 	=> $order->get_billing_phone(),
			'email' 	=> $order->get_billing_email(),
			'address_1'	=> $order->get_shipping_address_1(),
			'address_2'	=> $order->get_shipping_address_2(),
			'city' 		=> $order->get_shipping_city(),
			'state' 	=> $order->get_shipping_state(),
			'country' 	=> $order->get_shipping_country(),
			'postcode' 	=> $order->get_shipping_postcode(),
		);
	}
	
	private function get_shop_address( ) {

		$ups_settings			= $this->parent->settings;
		$shipper_phone_number 	= isset( $ups_settings['phone_number'] ) ? $ups_settings['phone_number'] : '';
		
		if ( $this->parent->ship_from_address_different_from_shipper == 'yes' && $this->parent->ship_from_address_for_freight ) {


			$country_with_state = explode( ':', $ups_settings['ship_from_country_state'] );
			$origin_country		= current( $country_with_state );
			$origin_state		= ! empty( end( $country_with_state ) ) ? end( $country_with_state ) : $ups_settings['ship_from_custom_state'];

			$address_line_1		=$ups_settings['ship_from_addressline'];
			$address_line_2		=$ups_settings['ship_from_addressline_2']; 
			$origin_city		=$ups_settings['ship_from_city']; 
			$origin_postcode	=$ups_settings['ship_from_postcode']; 
		} else {

			$country_with_state		= explode( ':', $ups_settings['origin_country_state'] );
			$this->origin_country	= current( $country_with_state );
			$origin_state 			= end( $country_with_state );
			$this->origin_state		= ! empty( $origin_state ) ? $origin_state : $ups_settings['origin_custom_state'];

			$address_line_1		= $ups_settings['origin_addressline']; 
			$address_line_2		= $ups_settings['origin_addressline_2'];
			$origin_city		= $ups_settings['origin_city'];
			$origin_state		= $this->origin_state;
			$origin_postcode	= $ups_settings['origin_postcode'];
			$origin_country		= $this->origin_country;
		}

		//Address standard followed in all xadapter plugins. 
		return array(
			'name'		=> isset( $ups_settings['ups_display_name'] ) ? $ups_settings['ups_display_name'] : '-',
			'company' 	=> isset( $ups_settings['ups_user_name'] ) ? $ups_settings['ups_user_name'] : '-',
			'phone' 	=> (strlen($shipper_phone_number) < 10) ? '0000000000' :  htmlspecialchars( $shipper_phone_number ),
			'email' 	=> isset( $ups_settings['email'] ) ? $ups_settings['email'] : '',

			'address_1' => $address_line_1,
			'address_2' => $address_line_2,
			'city' 		=> $origin_city,
			'state' 	=> $origin_state,
			'country' 	=> $origin_country,
			'postcode' 	=> $origin_postcode,
		);
	}

	private function get_payor_address( ){

		$ups_settings			= $this->parent->settings;

		$country_with_state			= isset( $ups_settings['freight_thirdparty_country_state'] ) && !empty( $ups_settings['freight_thirdparty_country_state'] ) ? explode( ':', $ups_settings['freight_thirdparty_country_state'] ) : ' ';
		$freight_billing_country	= is_array($country_with_state) ? current( $country_with_state ) : ' ';
		$origin_state 				= is_array($country_with_state) ? end($country_with_state) : ' ';
		$custom_state 				= isset( $ups_settings['freight_thirdparty_custom_state'] ) && !empty( $ups_settings['freight_thirdparty_custom_state'] ) ? $ups_settings['freight_thirdparty_custom_state'] : ' ';
		$freight_billing_state		= !empty($origin_state) ? $origin_state : $custom_state;
		$phone_number 				= isset( $ups_settings['phone_number'] ) ? $ups_settings['phone_number'] : '';
		
		return array(
			'name'		=> isset( $ups_settings['freight_thirdparty_contact_name'] ) ? $ups_settings['freight_thirdparty_contact_name'] : '-',
			'phone' 	=> (strlen($phone_number) < 10) ? '0000000000' :  htmlspecialchars( $phone_number ),

			'address_1' => isset( $ups_settings['freight_thirdparty_addressline'] ) ? $ups_settings['freight_thirdparty_addressline'] : '',
			'address_2' => isset( $ups_settings['freight_thirdparty_addressline_2'] ) ? $ups_settings['freight_thirdparty_addressline_2'] : '',
			'city' 		=> isset( $ups_settings['freight_thirdparty_city'] ) ? $ups_settings['freight_thirdparty_city'] : ' ',
			'state' 	=> $freight_billing_state,
			'country' 	=> $freight_billing_country,
			'postcode' 	=> isset( $ups_settings['freight_thirdparty_postcode'] ) ? $ups_settings['freight_thirdparty_postcode'] : '',
			
		);
	}
}