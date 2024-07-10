<?php
/**
 * UPS constants for WooCommerce UPS Shipping Plugin.
 *
 * @package ups-wocommerce-shipping
 */

defined( 'ABSPATH' ) || exit;

/**
 * PH_WC_UPS_Constants
 */
class PH_WC_UPS_Constants {

	/**
	* For Delivery Confirmation below array of countries will be considered as domestic, Confirmed by UPS.
	* US to US, CA to CA, PR to PR are considered as domestic, all other shipments are international.
	* @var array 
	*/
	public const DC_DOMESTIC_COUNTRIES = array('US', 'CA', 'PR');

	// European countries.
	public const EU_ARRAY = array('BE', 'BG', 'CZ', 'DK', 'DE', 'EE', 'IE', 'GR', 'ES', 'FR', 'HR', 'IT', 'CY', 'LV', 'LT', 'LU', 'HU', 'MT', 'NL', 'AT', 'PT', 'RO', 'SI', 'SK', 'FI', 'PL', 'SE');
	

	public const PHONE_NUMBER_SERVICES = array('01', '13', '14');

	// UPS Accesspoint Location Options Code.
	public const UPS_SERVICE_PROVIDER_CODE = array(
		"014",
		"015",
		"016"
	);

	public const COUNTRIES_WITH_STATECODES = array('US', 'CA', 'IE');

	public const NO_POSTCODE_COUNTRY_ARRAY = array(

		'AE', 'AF', 'AG', 'AI', 'AL', 'AN', 'AO', 'AW', 'BB', 'BF', 'BH', 'BI', 'BJ', 'BM', 'BO', 'BS', 'BT', 'BW', 'BZ', 'CD', 'CF', 'CG', 'CI', 'CK', 'CL', 'CM', 'CR', 'CV', 'DJ', 'DM', 'DO', 'EC', 'EG', 'ER', 'ET', 'FJ', 'FK', 'GA', 'GD', 'GH', 'GI', 'GM', 'GN', 'GQ', 'GT', 'GW', 'GY', 'HK', 'HN', 'HT', 'IE', 'IQ', 'IR', 'JM', 'JO', 'KE', 'KH', 'KI', 'KM', 'KN', 'KP', 'KW', 'KY', 'LA', 'LB', 'LC', 'LR', 'LS', 'LY', 'ML', 'MM', 'MO', 'MR', 'MS', 'MT', 'MU', 'MW', 'MZ', 'NA', 'NE', 'NG', 'NI', 'NP', 'NR', 'NU', 'OM', 'PA', 'PE', 'PF', 'PY', 'QA', 'RW', 'SB', 'SC', 'SD', 'SL', 'SN', 'SO', 'SR', 'SS', 'ST', 'SV', 'SY', 'TC', 'TD', 'TG', 'TL', 'TO', 'TT', 'TV', 'TZ', 'UG', 'VC', 'VE', 'VG', 'VU', 'WS', 'XA', 'XB', 'XC', 'XE', 'XL', 'XM', 'XN', 'XS', 'YE', 'ZM', 'ZW'
	);

	public const UPS_SUREPOST_SERVICES = array( 92, 93, 94, 95 );

	public const UPS_SERVICE_CODES = array(

		'US' => array(
			// Domestic
			"12" => "UPS 3 Day Select®",
			"03" => "UPS® Ground",
			"02" => "UPS 2nd Day Air®",
			"59" => "UPS 2nd Day Air A.M.®",
			"01" => "UPS Next Day Air®",
			"13" => "UPS Next Day Air Saver®",
			"14" => "UPS Next Day Air® Early",
			"74" => "UPS Express® 12:00",		// Germany Domestic
	
			// International
			"11" => "UPS® Standard",
			"07" => "UPS Worldwide Express™",
			"54" => "UPS Worldwide Express Plus™",
			"08" => "UPS Worldwide Expedited",
			"65" => "UPS Worldwide Saver",
	
			// SurePost
			"92" =>	"UPS SurePost® (USPS) < 1lb",
			"93" =>	"UPS SurePost® (USPS) > 1lb",
			"94" =>	"UPS SurePost® (USPS) BPM",
			"95" =>	"UPS SurePost® (USPS) Media",
	
			//New Services
			"M2" => "UPS First Class Mail",
			"M3" => "UPS Priority Mail",
			"M4" => "UPS Expedited Mail Innovations ",
			"M5" => "UPS Priority Mail Innovations ",
			"M6" => "UPS Economy Mail Innovations ",
			"70" => "UPS Access Point® Economy ",
			"71" => "UPS Worldwide Express Freight Midday",
			"96" => "UPS Worldwide Express Freight",
	
			"US48" => "Ground with Freight",
		),
	
		// Shipments Originating in the European Union.
		'EU' => array(
			"07" => "UPS Express",
			"08" => "UPS Expedited",
			"11" => "UPS® Standard",
			"54" => "UPS Worldwide Express Plus™",
			"65" => "UPS Worldwide Saver®",
			"70" => "UPS Access Point® Economy",
			"74" => "UPS Express® 12:00",
		),
	
		// Services for United Kingdom. Also stays the same for 'GB'. 
		'UK' => array(
			"07" => "UPS Express",
			"08" => "UPS Expedited",
			"11" => "UPS® Standard",
			"54" => "UPS Worldwide Express Plus™",
			"65" => "UPS Worldwide Express Saver®",
			"70" => "UPS Access Point® Economy",
		),
	
		// Services for Poland.
		'PL' => array(
			"07" => "UPS Express",
			"08" => "UPS Expedited",
			"11" => "UPS® Standard",
			"54" => "UPS Express Plus®",
			"65" => "UPS Express Saver",
			"82" => "UPS Today Standard",
			"83" => "UPS Today Dedicated Courier",
			"85" => "UPS Today Express",
			"86" => "UPS Today Express Saver",
			"70" => "UPS Access Point® Economy",
		),
	
		// Services for Canada Origination.
		'CA' => array(
			"01" =>	"UPS Express",
			"02" => "UPS Expedited",
			"07" =>	"UPS Worldwide Express™",
			"08" =>	"UPS Worldwide Expedited®",
			"11" =>	"UPS® Standard",
			"12" => "UPS 3 Day Select®",			// For CA and US48.
			"13" => "UPS Express Saver",
			"14" =>	"UPS Express Early",
			"54" => "UPS Worldwide Express Plus™",	//UPS Express Early for CA and US48.
			"65" => "UPS Express Saver",
			"70" =>	"UPS Access Point® Economy",
		),
	);

	public const UPS_COUNTRY_SERVICE_MAPPER = array(

		'PL'    => 'PL',
		'CA'    => 'CA',
		'GB'    => 'UK',
		'UK'    => 'UK',
		'US'    => 'US',

		// Specifically for Europe.
		'BE'    => 'EU',
		'BG'    => 'EU',
		'CZ'    => 'EU',
		'DK'    => 'EU',
		'DE'    => 'EU',
		'EE'    => 'EU',
		'IE'    => 'EU',
		'GR'    => 'EU',
		'ES'    => 'EU',
		'FR'    => 'EU',
		'HR'    => 'EU',
		'IT'    => 'EU',
		'CY'    => 'EU',
		'LV'    => 'EU',
		'LT'    => 'EU',
		'LU'    => 'EU',
		'HU'    => 'EU',
		'MT'    => 'EU',
		'NL'    => 'EU',
		'AT'    => 'EU',
		'PT'    => 'EU',
		'RO'    => 'EU',
		'SI'    => 'EU',
		'SK'    => 'EU',
		'FI'    => 'EU',
		'SE'    => 'EU',
		'PL'    => 'EU',
	);

	public const FREIGHT_SERVICES = array(

		'308' => 'TForce Freight LTL',
		'309' => 'TForce Freight LTL - Guaranteed',
		'334' => 'TForce Freight LTL - Guaranteed A.M.',
		'349' => 'TForce Freight LTL Mexico',
	);

	public const PICKUP_CODE = array(

		'01' => "Daily Pickup",
		'03' => "Customer Counter",
		'06' => "One Time Pickup",
		'07' => "On Call Air",
		'19' => "Letter Center",
		'20' => "Air Service Center",
	);

	public const CUSTOMER_CLASSIFICATION_CODE = array(

		'NA' => "Default",
		'00' => "Rates Associated with Shipper Number",
		'01' => "Daily Rates",
		'04' => "Retail Rates",
		'05' => "Regional Rates",
		'06' => "General List Rates",
		'53' => "Standard List Rates",
	);

	public const ACCESSPOINT_LOCATION_OPTION = array(

		"000" => "All Location Types",
		"001" => "UPS Customer Center",
		"002" => "The UPS Store",
		"003" => "UPS Drop Box",
		"004" => "Authorized Shipping Outlet",
		"005" => "Mail Boxes Etc.",
		"007" => "UPS Alliance",
		"014" => "UPS Authorized Service Providers",
		"018" => "UPS Access Point",
		"019" => "UPS Access Point Locker",
	);

	public const SATELLITE_COUNTRIES = array(

		'E2'	=> 	'BQ',
		'S1'	=> 	'BQ',
		'IC'	=> 	'ES',
		'XC'	=> 	'ES',
		'XL'	=> 	'ES',
		'AX'	=> 	'FI',
		'KO'	=> 	'FM',
		'PO'	=> 	'FM',
		'TU'	=> 	'FM',
		'YA'	=> 	'FM',
		'EN'	=> 	'GB',
		'NB'	=> 	'GB',
		'SF'	=> 	'GB',
		'WL'	=> 	'GB',
		'SW'	=> 	'KN',
		'RT'	=> 	'MP',
		'SP'	=> 	'MP',
		'TI'	=> 	'MP',
		'HO'	=> 	'NL',
		'TA'	=> 	'PF',
		'A2'	=> 	'PT',
		'M3'	=> 	'PT',
		'UI'	=> 	'VC',
		'VR'	=> 	'VG',
		'ZZ'	=> 	'VG',
		'C3'	=> 	'VI',
		'UV'	=> 	'VI',
		'VL'	=> 	'VI',
	);

	// NAFTA Origin Destination Pair
	public const NAFTA_SUPPORTED_COUNTRIES = array(

		'US' => array(
			'CA',
			'MX',
		),
		'CA' => array(
			'US',
			'PR',
			'MX',
		),
		'PR' => array(
			'CA',
			'MX',
		),
	);

	public const SIMPLE_RATE_BOX_CODES = array(

		'XS'    => 'XS',
		'XS:2'  => 'XS',
		'XS:3'  => 'XS',
		// Small Boxes
		'S'     => 'S',
		'S:2'   => 'S',
		'S:3'   => 'S',
		// Medium Boxes
		'M'     => 'M',
		'M:2'   => 'M',
		'M:3'   => 'M',
		// Large Boxes
		'L'     => 'L',
		'L:2'   => 'L',
		'L:3'   => 'L',
		// Extra Large Boxes
		'XL'    => 'XL',
		'XL:2'  => 'XL',
		'XL:3'  => 'XL'
	);

	public const PACKAGING_SELECT = array(
		
		"01" => "UPS Letter",
		"03" => "Tube",
		"04" => "PAK",
		"24" => "25KG Box",
		"25" => "10KG Box",
		"2a" => "Small Express Box",
		"2b" => "Medium Express Box",
		"2c" => "Large Express Box",
	);

	public const UPS_SIMPLE_RATE_BOXES_IN_INCHES = [

		 // Extra Small Boxes
		 'XS'    => [
			'name'      => 'Extra Small Box',
			'code'      => 'XS',
			'length'    => 4,
			'width'     => 4,
			'height'    => 4,
			'box_weight' => 0,
			'max_weight' => 50,
			'box_enabled'   => true
		],
		'XS:2'  => [
			'name'      => 'Extra Small Box',
			'code'      => 'XS:2',
			'length'    => 6,
			'width'     => 4,
			'height'    => 4,
			'box_weight' => 0,
			'max_weight' => 50,
			'box_enabled'   => true
		],
		'XS:3'  => [
			'name'      => 'Extra Small Box',
			'code'      => 'XS:3',
			'length'    => 8,
			'width'     => 6,
			'height'    => 2,
			'box_weight' => 0,
			'max_weight' => 50,
			'box_enabled'   => true
		],
	
		// Small Boxes
		'S'     => [
			'name'      => 'Small Box',
			'code'      => 'S',
			'length'    => 6,
			'width'     => 6,
			'height'    => 6,
			'box_weight' => 0,
			'max_weight' => 50,
			'box_enabled'   => true
		],
		'S:2'   => [
			'name'      => 'Small Box',
			'code'      => 'S:2',
			'length'    => 8,
			'width'     => 6,
			'height'    => 5,
			'box_weight' => 0,
			'max_weight' => 50,
			'box_enabled'   => true
		],
		'S:3'   => [
			'name'      => 'Small Box',
			'code'      => 'S:3',
			'length'    => 12,
			'width'     => 9,
			'height'    => 2,
			'box_weight' => 0,
			'max_weight' => 50,
			'box_enabled'   => true
		],
	
		// Medium Boxes
		'M'     => [
			'name'      => 'Medium Box',
			'code'      => 'M',
			'length'    => 8,
			'width'     => 8,
			'height'    => 8,
			'box_weight' => 0,
			'max_weight' => 50,
			'box_enabled'   => true
		],
		'M:2'   => [
			'name'      => 'Medium Box',
			'code'      => 'M:2',
			'length'    => 12,
			'width'     => 9,
			'height'    => 6,
			'box_weight' => 0,
			'max_weight' => 50,
			'box_enabled'   => true
		],
		'M:3'   => [
			'name'      => 'Medium Box',
			'code'      => 'M:3',
			'length'    => 13,
			'width'     => 11,
			'height'    => 2,
			'box_weight' => 0,
			'max_weight' => 50,
			'box_enabled'   => true
		],
	
		// Large Boxes
		'L'     => [
			'name'      => 'Large Box',
			'code'      => 'L',
			'length'    => 10,
			'width'     => 10,
			'height'    => 10,
			'box_weight' => 0,
			'max_weight' => 50,
			'box_enabled'   => true
		],
		'L:2'    => [
			'name'      => 'Large Box',
			'code'      => 'L:2',
			'length'    => 12,
			'width'     => 12,
			'height'    => 7,
			'box_weight' => 0,
			'max_weight' => 50,
			'box_enabled'   => true
		],
		'L:3'   => [
			'name'      => 'Large Box',
			'code'      => 'L:3',
			'length'    => 15,
			'width'     => 11,
			'height'    => 6,
			'box_weight' => 0,
			'max_weight' => 50,
			'box_enabled'   => true
		],
	
		// Extra Large Boxes
		'XL'    => [
			'name'      => 'Extra Large Box',
			'code'      => 'XL',
			'length'    => 12,
			'width'     => 12,
			'height'    => 12,
			'box_weight' => 0,
			'max_weight' => 50,
			'box_enabled'   => true
		],
		'XL:2'  => [
			'name'      => 'Extra Large Box',
			'code'      => 'XL:2',
			'length'    => 16,
			'width'     => 12,
			'height'    => 9,
			'box_weight' => 0,
			'max_weight' => 50,
			'box_enabled'   => true
		],
		'XL:3'  => [
			'name'      => 'Extra Large Box',
			'code'      => 'XL:3',
			'length'    => 18,
			'width'     => 12,
			'height'    => 6,
			'box_weight' => 0,
			'max_weight' => 50,
			'box_enabled'   => true
		],
	];

	public const UPS_SIMPLE_RATE_BOXES_IN_CMS = [

		// Extra Small Boxes
		'XS'    => [
			'name'      => 'Extra Small Box',
			'code'      => 'XS',
			'length'    => 10.16,
			'width'     => 10.16,
			'height'    => 10.16,
			'box_weight'=> 0,
			'max_weight'=> 22.67,
			'box_enabled'   => true
		],
		'XS:2'  => [
			'name'      => 'Extra Small Box',
			'code'      => 'XS:2',
			'length'    => 15.24,
			'width'     => 10.16,
			'height'    => 10.16,
			'box_weight'=> 0,
			'max_weight'=> 22.67,
			'box_enabled'   => true
		],
		'XS:3'  => [
			'name'      => 'Extra Small Box',
			'code'      => 'XS:3',
			'length'    => 20.32,
			'width'     => 15.24,
			'height'    => 5.08,
			'box_weight'=> 0,
			'max_weight'=> 22.67,
			'box_enabled'   => true
		],
	
		// Small Boxes
		'S'     => [
			'name'      => 'Small Box',
			'code'      => 'S',
			'length'    => 15.24,
			'width'     => 15.24,
			'height'    => 15.24,
			'box_weight'=> 0,
			'max_weight'=> 22.67,
			'box_enabled'   => true
		],
		'S:2'   => [
			'name'      => 'Small Box',
			'code'      => 'S:2',
			'length'    => 20.32,
			'width'     => 15.24,
			'height'    => 12.7,
			'box_weight'=> 0,
			'max_weight'=> 22.67,
			'box_enabled'   => true
		],
		'S:3'   => [
			'name'      => 'Small Box',
			'code'      => 'S:3',
			'length'    => 30.48,
			'width'     => 22.86,
			'height'    => 5.08,
			'box_weight'=> 0,
			'max_weight'=> 22.67,
			'box_enabled'   => true
		],
	
		// Medium Boxes
		'M'     => [
			'name'      => 'Medium Box',
			'code'      => 'M',
			'length'    => 20.32,
			'width'     => 20.32,
			'height'    => 20.32,
			'box_weight'=> 0,
			'max_weight'=> 22.67,
			'box_enabled'   => true
		],
		'M:2'   => [
			'name'      => 'Medium Box',
			'code'      => 'M:2',
			'length'    => 30.48,
			'width'     => 22.86,
			'height'    => 15.24,
			'box_weight'=> 0,
			'max_weight'=> 22.67,
			'box_enabled'   => true
		],
		'M:3'   => [
			'name'      => 'Medium Box',
			'code'      => 'M:3',
			'length'    => 33.02,
			'width'     => 27.94,
			'height'    => 5.08,
			'box_weight'=> 0,
			'max_weight'=> 22.67,
			'box_enabled'   => true
		],
	
		// Large Boxes
		'L'     => [
			'name'      => 'Large Box',
			'code'      => 'L',
			'length'    => 25.4,
			'width'     => 25.4,
			'height'    => 25.4,
			'box_weight'=> 0,
			'max_weight'=> 22.67,
			'box_enabled'   => true
		],
		'L:2'    => [
			'name'      => 'Large Box',
			'code'      => 'L:2',
			'length'    => 30.48,
			'width'     => 30.48,
			'height'    => 17.78,
			'box_weight'=> 0,
			'max_weight'=> 22.67,
			'box_enabled'   => true
		],
		'L:3'   => [
			'name'      => 'Large Box',
			'code'      => 'L:3',
			'length'    => 38.1,
			'width'     => 27.94,
			'height'    => 15.24,
			'box_weight'=> 0,
			'max_weight'=> 22.67,
			'box_enabled'   => true
		],
	
		// Extra Large Boxes
		'XL'    => [
			'name'      => 'Extra Large Box',
			'code'      => 'XL',
			'length'    => 30.48,
			'width'     => 30.48,
			'height'    => 30.48,
			'box_weight'=> 0,
			'max_weight'=> 22.67,
			'box_enabled'   => true
		],
		'XL:2'  => [
			'name'      => 'Extra Large Box',
			'code'      => 'XL:2',
			'length'    => 40.64,
			'width'     => 30.48,
			'height'    => 22.86,
			'box_weight'=> 0,
			'max_weight'=> 22.67,
			'box_enabled'   => true
		],
		'XL:3'  => [
			'name'      => 'Extra Large Box',
			'code'      => 'XL:3',
			'length'    => 45.72,
			'width'     => 30.48,
			'height'    => 15.24,
			'box_weight'=> 0,
			'max_weight'=> 22.67,
			'box_enabled'   => true
		],		
	
	];

	public const UPS_DEFAULT_BOXES_IN_INCHES = array(

		"A_UPS_LETTER" => array(
			"name" 	 => "UPS Letter",
			"code"	 => '01',
			"length" => "12.5",
			"width"  => "9.5",
			"height" => "0.25",
			"weight" => "0.5",
			"box_enabled"=> true
		),
		"B_TUBE" => array(
				"name" 	 => "Tube",
				"code"	 => "03",
				"length" => "38",
				"width"  => "6",
				"height" => "6",
				"weight" => "100",
				"box_enabled"=> true
			),
		"C_PAK" => array(
				"name" 	 => "PAK",
				"code"	 => "04",	
				"length" => "17",
				"width"  => "13",
				"height" => "1",
				"weight" => "100",
				"box_enabled"=> true
			),
		"D_25KG_BOX" => array(
				"name" 	 => "25KG Box",
				"code"	 => "24",
				"length" => "19.375",
				"width"  => "17.375",
				"height" => "14",
				"weight" => "55",
				"box_enabled"=> true
			),
		"E_10KG_BOX" => array(
				"name" 	 => "10KG Box",
				"code"	 => "25",
				"length" => "16.5",
				"width"  => "13.25",
				"height" => "10.75",
				"weight" => "22",
				"box_enabled"=> true
			),
		"F_SMALL_EXPRESS_BOX" => array(
				"name" 	 => "Small Express Box",
				"code"	 => "2a",
				"length" => "13",
				"width"  => "11",
				"height" => "2",
				"weight" => "100",
				"box_enabled"=> true
			),
		"G_MEDIUM_EXPRESS_BOX" => array(
				"name" 	 => "Medium Express Box",
				"code"	 => "2b",
				"length" => "15",
				"width"  => "11",
				"height" => "3",
				"weight" => "100",
				"box_enabled"=> true
			),
		"H_LARGE_EXPRESS_BOX" => array(
				"name" 	 => "Large Express Box",
				"code"	 => "2c",
				"length" => "18",
				"width"  => "13",
				"height" => "3",
				"weight" => "30",
				"box_enabled"=> true
		)
	);

	public const UPS_DEFAULT_BOXES_IN_CMS = [

		"A_UPS_LETTER" => [
			"name" 	 => "UPS Letter",
			"code"	 => '01',
			"length" => "31.75",
			"width"  => "24.13",
			"height" => "0.635",
			"weight" => "0.226796",
			"box_enabled"=> true
		],
		"B_TUBE" => [
				"name" 	 => "Tube",
				"code"	 => "03",
				"length" => "96.52",
				"width"  => "15.24",
				"height" => "15.24",
				"weight" => "100",
				"box_enabled"=> true
		],
		"C_PAK" => [
				"name" 	 => "PAK",
				"code"	 => "04",	
				"length" => "43.18",
				"width"  => "33.02",
				"height" => "2.54",
				"weight" => "45.3592",
				"box_enabled"=> true
		],
		"D_25KG_BOX" => [
				"name" 	 => "25KG Box",
				"code"	 => "24",
				"length" => "49.2125",
				"width"  => "44.1325",
				"height" => "35.56",
				"weight" => "25",
				"box_enabled"=> true
		],
		"E_10KG_BOX" => [
				"name" 	 => "10KG Box",
				"code"	 => "25",
				"length" => "41.91",
				"width"  => "33.655",
				"height" => "27.305",
				"weight" => "10",
				"box_enabled"=> true
		],
		"F_SMALL_EXPRESS_BOX" => [
				"name" 	 => "Small Express Box",
				"code"	 => "2a",
				"length" => "33.02",
				"width"  => "27.94",
				"height" => "5.08",
				"weight" => "45.3592",
				"box_enabled"=> true
		],
		"G_MEDIUM_EXPRESS_BOX" => [
				"name" 	 => "Medium Express Box",
				"code"	 => "2b",
				"length" => "38.1",
				"width"  => "27.94",
				"height" => "7.62",
				"weight" => "45.3592",
				"box_enabled"=> true
		],
		"H_LARGE_EXPRESS_BOX" => [
				"name" 	 => "Large Express Box",
				"code"	 => "2c",
				"length" => "45.72",
				"width"  => "33.02",
				"height" => "7.62",
				"weight" => "13.6078",
				"box_enabled"=> true
			]
	];

	// Supported unit of measurements for invoice
	public const PH_INVOICE_UNIT_OF_MEASURES = array(

		"BG" 	=> "Bag",
		"BA" 	=> "Barrel",
		"BT" 	=> "Bolt",
		"BOX" 	=> "Box",
		"BH" 	=> "Bunch",
		"BE" 	=> "Bundle",
		"BU" 	=> "Butt",
		"CI" 	=> "Canister",
		"CT" 	=> "Carton",
		"CS" 	=> "Case",
		"CM" 	=> "Centimeter",
		"CON" 	=> "Container",
		"CR" 	=> "Crate",
		"CY" 	=> "Cylinder",
		"DOZ" 	=> "Dozen",
		"NMB" 	=> "Each/Number (Canadian)",
		"EA" 	=> "Each/Number (Non-Canadian)",
		"EN" 	=> "Envelope",
		"FT" 	=> "Foot",
		"KG" 	=> "Kilogram",
		"KGS" 	=> "Kilograms",
		"L" 	=> "Liter",
		"M" 	=> "Meter",
		"PK" 	=> "Package",
		"PA" 	=> "Packet",
		"PAR" 	=> "Pair",
		"PRS" 	=> "Pairs",
		"PAL" 	=> "Pallet",
		"PC" 	=> "Piece",
		"PCS" 	=> "Pieces",
		"LB" 	=> "Pound",
		"PF" 	=> "Proof Liter",
		"ROL" 	=> "Roll",
		"SET" 	=> "Set",
		"SME" 	=> "Square Meter",
		"SYD" 	=> "Square Yard",
		"TU" 	=> "Tube",
		"YD" 	=> "Yard",
	);

	// Latin characters corresponding to the Cyrillic character list in $cyrillic_characters
	public const  LATIN_CHARACTERS = [

		'a', 'b', 'v', 'g', 'd', 'e', 'io', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p',
		'r', 's', 't', 'u', 'f', 'h', 'ts', 'ch', 'sh', 'sht', 'a', 'i', 'y', 'e', 'yu', 'ya', 'No.',
		'A', 'B', 'V', 'G', 'D', 'E', 'Io', 'Zh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P',
		'R', 'S', 'T', 'U', 'F', 'H', 'Ts', 'Ch', 'Sh', 'Sht', 'A', 'I', 'Y', 'e', 'Yu', 'Ya', 'No.',
	];

	public const CYRILLIC_CHARACTERS = [

		'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п',
		'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', 'Nº',
		'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П',
		'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', '№',
	];

	// United Arab Emirates, Russia, European Countries
	public const COUNTRIES = array(
		
		'AE',
		'RU',
		'UA',
		'FR',
		'ES',
		'SE',
		'NO',
		'DE',
		'FI',
		'PL',
		'IT',
		'UK',
		'GB',
		'RO',
		'BY',
		'EL',
		'BG',
		'IS',
		'HU',
		'PT',
		'AZ',
		'AT',
		'CZ',
		'RS',
		'IE',
		'GE',
		'LT',
		'LV',
		'HR',
		'BA',
		'SK',
		'EE',
		'DK',
		'CH',
		'NL',
		'MD',
		'BE',
		'AL',
		'MK',
		'TR',
		'SI',
		'ME',
		'XK',
		'LU',
		'MT',
		'LI',
	);
}