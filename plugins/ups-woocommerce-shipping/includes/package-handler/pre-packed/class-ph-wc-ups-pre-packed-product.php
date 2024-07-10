<?php
/**
 * Pre Packed Product packaging for WooCommerce UPS Shipping Plugin.
 *
 * @package ups-woocommerce-shipping
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PH_WC_UPS_Pre_Packed_Product
 *
 * Generates pre-packed product packaging for UPS Shipping.
 */
class PH_WC_UPS_Pre_Packed_Product {

	/**
	 * Settings for package generation.
	 *
	 * @var array
	 */
	public $settings;

	/**
	 * Debug mode flag.
	 *
	 * @var bool
	 */
	public $debug;

	/**
	 * Silent debug mode flag.
	 *
	 * @var bool
	 */
	public $silent_debug;
	
	/**
	 * Constructor method for PH_WC_UPS_Pre_Packed_Product Class.
	 */
	public function __construct( $settings ) {
	
		$this->settings     = $settings;

		$this->debug		= $this->settings['debug'];
		$this->silent_debug = $this->settings['silent_debug'];
	}
	
	/**
	 * Adds pre-packed products to UPS shipping requests.
	 *
	 * @param array    $pre_packed_items Array containing pre-packed product items.
	 * @param array	   $destination Destination details required to check for COD feature applicable or not.	
	 * @param WC_Order $order            Optional. The WooCommerce order object. Default is null.
	 * @param array    $params           Optional. Additional parameters. Default is an empty array.
	 * @return array Array of UPS pre packed product package requests.
	 */
	public function ph_ups_add_pre_packed_product($pre_packed_items, $destination, $order = null, $params = array())
	{
		$requests = array();
		foreach ($pre_packed_items as $item_id => $values) {
			if (!($values['quantity'] > 0 && $values['data']->needs_shipping())) {
				Ph_UPS_Woo_Shipping_Common::debug(sprintf(__('Product #%d is virtual. Skipping.', 'ups-woocommerce-shipping'), $values['data']->id), $this->debug, $this->silent_debug);

				// Add by Default
				Ph_UPS_Woo_Shipping_Common::phAddDebugLog(sprintf('Product %d is virtual. Skipping from Rate Calculation', $values['data']->id), $this->debug);

				continue;
			}

			if (!$values['data']->get_weight()) {
				Ph_UPS_Woo_Shipping_Common::debug(sprintf(__('Product #%d is missing weight. Aborting.', 'ups-woocommerce-shipping'), $values['data']->id), $this->debug, $this->silent_debug, 'error');

				// Add by Default
				Ph_UPS_Woo_Shipping_Common::phAddDebugLog(sprintf('Product %d is  missing weight. Aborting Rate Calculation', $values['data']->id), $this->debug);

				return;
			}
			$weight = wc_get_weight((!empty($values['data']->get_weight()) ? $values['data']->get_weight() : 0), $this->settings['weight_unit']);

			if ($values['data']->length && $values['data']->height && $values['data']->width && $values['data']->weight) {
				$dimensions = array(
					number_format(wc_get_dimension((float) $values['data']->length, $this->settings['dim_unit']), 2, '.', ''),
					number_format(wc_get_dimension((float) $values['data']->height, $this->settings['dim_unit']), 2, '.', ''),
					number_format(wc_get_dimension((float) $values['data']->width, $this->settings['dim_unit']), 2, '.', '')
				);
				sort($dimensions);
			} else {
				Ph_UPS_Woo_Shipping_Common::debug(sprintf(__('Product is missing dimensions. Aborting.', 'ups-woocommerce-shipping')), $this->debug, $this->silent_debug, 'error');

				// Add by Default
				Ph_UPS_Woo_Shipping_Common::phAddDebugLog(sprintf('Product %d is  missing dimensions. Aborting Rate Calculation', $values['data']->id), $this->debug);

				return;
			}

			$cart_item_qty = $values['quantity'];

			$request['Package']	=	array(
				'PackagingType'	=>	array(
					'Code'			=>	'02',
					'Description'	=>	'Package/customer supplied'
				),
				'Description'	=>	'Rate',
			);

			if ('box_packing' === $this->settings['packing_method']) {

				$request['Package']['box_name'] = "Pre-packed Product";
			}

			// Direct Delivery option
			$directdeliveryonlyindicator = PH_WC_UPS_Common_Utils::get_individual_product_meta(array($values['data']), '_wf_ups_direct_delivery');

			if (isset($_GET['dd'])) {

				PH_UPS_WC_Storage_Handler::ph_add_and_save_meta_data($order->get_id(), '_ph_ups_direct_delivery', $_GET['dd']);

				$directdeliveryonlyindicator = !empty($_GET['dd']) ? $_GET['dd'] : $directdeliveryonlyindicator;
			}

			if ($directdeliveryonlyindicator == 'yes') {
				$request['Package']['DirectDeliveryOnlyIndicator'] = $directdeliveryonlyindicator;
			}

			if ($values['data']->length && $values['data']->height && $values['data']->width) {
				$request['Package']['Dimensions']	=	array(
					'UnitOfMeasurement'	=>	array(
						'Code'	=>	$this->settings['dim_unit']
					),
					'Length'	=>	(string) round($dimensions[2], 2),
					'Width'		=>	(string) round($dimensions[1], 2),
					'Height'	=>	(string) round($dimensions[0], 2)
				);
			}
			if ((isset($params['service_code']) && $params['service_code'] == 92) || ($this->settings['service_code'] == 92)) // Surepost Less Than 1LBS
			{
				if ( 'LBS' === $this->settings['weight_unit'] ) { // make sure weight in pounds
					$weight_ozs = $weight * 16;
				} else {
					$weight_ozs = $weight * 35.274; // From KG
				}
				$request['Package']['PackageWeight']	=	array(
					'UnitOfMeasurement'	=>	array(
						'Code'	=>	'OZS'
					),
					'Weight'	=>	(string) round($weight_ozs, 2)
				);
			} else {

				// Invalid Weight Error if Weight is less than 0.05 for Estimated Delivery Option
				if ($weight < 0.05) {
					$weight = 0.05;
				}

				$request['Package']['PackageWeight']	=	array(
					'UnitOfMeasurement'	=>	array(
						'Code'	=>	$this->settings['weight_unit']
					),
					'Weight'	=>	(string) round($weight, 2)
				);
			}


			if ($this->settings['insuredvalue'] || $this->settings['cod'] || $this->settings['cod_enable']) {

				// InsuredValue
				if ($this->settings['insuredvalue']) {

					// REST doesn't support "InsuredValue" node, it's handled in REST file
					$request['Package']['PackageServiceOptions']['InsuredValue']	=	array(
						'CurrencyCode'	=>	$this->settings['currency_type'],
						'MonetaryValue'	=>	(string) round(( PH_WC_UPS_Common_Utils::ph_get_insurance_amount( $values['data'], $this->settings['fixedProductPrice'] ) * $this->settings['conversion_rate'] ), 2)
					);
				}

				//COD
				if (($this->settings['cod'] && isset($_GET['wf_ups_shipment_confirm'])) || ($this->settings['cod_enable'] && !isset($_GET['wf_ups_shipment_confirm']))) {

					if (! PH_WC_UPS_Common_Utils::is_shipment_level_cod_required($destination['country'])) {

						$cod_amount 	= !empty($values['data']->get_price()) ? $values['data']->get_price() : $this->settings['fixedProductPrice'];
						$codfundscode 	= in_array($destination['country'], array('AR', 'BR', 'CL')) ? 9 : 0;

						$request['Package']['PackageServiceOptions']['COD']	= array(
							'CODCode'		=>	3,
							'CODFundsCode'	=>	$codfundscode,
							'CODAmount'		=>	array(
								'MonetaryValue'	=>	(string) round($cod_amount, 2),
								'CurrencyCode'	=>	$this->settings['currency_type'],
							),
						);
					}
				}
			}

			// Delivery Confirmation
			if (isset($params['delivery_confirmation_applicable']) && $params['delivery_confirmation_applicable'] == true) {

				$signature_option = PH_WC_UPS_Common_Utils::get_package_signature(array($values['data']));
				$signature_option = $signature_option < $this->settings['ph_delivery_confirmation'] ? $this->settings['ph_delivery_confirmation'] : $signature_option;

				if (isset($_GET['dc'])) {

					PH_UPS_WC_Storage_Handler::ph_add_and_save_meta_data($order->get_id(), '_ph_ups_delivery_signature', $_GET['dc']);

					$signature_option = $_GET['dc'] != 4 ? $_GET['dc'] : $signature_option;
				}

				$signature_option = $signature_option == 3 ? 3 : ($signature_option > 0 ? 2 : $signature_option);

				if (isset($request['Package']['PackageServiceOptions']) && isset($request['Package']['PackageServiceOptions']['COD'])) {
					Ph_UPS_Woo_Shipping_Common::phAddDebugLog('UPS : COD Shipment. Signature will not be applicable.', $this->debug);
				}

				if (!empty($signature_option) && ($signature_option > 0) && (!isset($request['Package']['PackageServiceOptions']) || (isset($request['Package']['PackageServiceOptions']) && !isset($request['Package']['PackageServiceOptions']['COD'])))) {

					Ph_UPS_Woo_Shipping_Common::phAddDebugLog('UPS : Require Signature - ' . $signature_option, $this->debug);

					$request['Package']['PackageServiceOptions']['DeliveryConfirmation']['DCISType'] = $signature_option;
				}
			}

			if ($this->settings['isc']) {

				$refrigeratorindicator 	= 'no';
				$clinicalid 			= '';
				$clinicalvar 			= get_post_meta($values['data']->id, '_ph_ups_clinicaltrials_var', 1);
				$refrigerator_var 		= get_post_meta($values['data']->id, '_ph_ups_refrigeration_var', 1);

				if (empty($refrigerator_var) || !isset($refrigerator_var)) {

					$refrigerator 	= get_post_meta($values['data']->id, '_ph_ups_refrigeration', 1);
				} else {

					$refrigerator 	= $refrigerator_var;
				}

				if (empty($clinicalvar) || !isset($clinicalvar)) {

					$clinical 	= get_post_meta($values['data']->id, '_ph_ups_clinicaltrials', 1);
				} else {

					$clinical 	= $clinicalvar;
				}

				$refrigeratorindicator 	= ($refrigeratorindicator == 'yes') ? $refrigeratorindicator : $refrigerator;
				$clinicalid 			= (isset($clinicalid) && !empty($clinicalid)) ? $clinicalid : $clinical;

				$refrigeratorindicator = ($refrigeratorindicator == 'yes' ? 'yes' : (isset($this->settings['ph_ups_refrigeration']) && $this->settings['ph_ups_refrigeration'] == 'yes' ? 'yes' : 'no'));

				$clinicalid  = (!empty($clinicalid) ? $clinicalid  : (isset($this->settings['ph_ups_clinicaltrials']) && !empty($this->settings['ph_ups_clinicaltrials']) ? $this->settings['ph_ups_clinicaltrials'] : ''));

				if ($refrigeratorindicator == 'yes') {

					$request['Package']['PackageServiceOptions']['RefrigerationIndicator'] = '1';
				}

				if (isset($clinicalid) && !empty($clinicalid) && isset($_GET['wf_ups_shipment_confirm'])) {

					$request['Package']['PackageServiceOptions']['ClinicaltrialsID'] = $clinicalid;
				}
			}

			//Setting the product object in package request	
			$request['Package']['items'] = array($values['data']->obj);

			// Boolean to check if unit conversion is required. To support multi-vendor addon
			if (isset($params['metrics'])) {
				$request['Package']['metrics'] = $params['metrics'];
			}

			for ($i = 0; $i < $cart_item_qty; $i++)
				$requests[] = $request;
		}
		return $requests;
	}

}