<?php
/**
 * Per item packaging for WooCommerce UPS Shipping Plugin.
 *
 * @package ups-woocommerce-shipping
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PH_WC_UPS_Per_Item_Shipping
 *
 * Generates per-item packaging for UPS Shipping.
 */
class PH_WC_UPS_Per_Item_Shipping {

	/**
	 * Settings for package generation.
	 *
	 * @var array
	 */
	public $settings;

	/**
	 * Destination details.
	 *
	 * @var string
	 */
	public $destination;

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
	 * Constructor method for PH_WC_UPS_Per_Item_Shipping Class.
	 */
	public function __construct( $settings ) {
	
		$this->settings     = $settings;

		$this->debug		= $this->settings['debug'];
		$this->silent_debug = $this->settings['silent_debug'];
	}
	
	/**
	 * Calculate shipping rates for each item in the package.
	 *
	 * @param array    $package An array containing information about the package, including its contents and destination.
	 * @param WC_Order $order   (Optional) The WooCommerce order associated with the package.
	 * @param array    $params  (Optional) Additional parameters for customizing the shipping calculation.
	 * @return array An array of individual item packaging.
	 */
	public function per_item_shipping($package, $order = null, $params = array())
	{
		global $woocommerce;

		$requests = array();
		$refrigeratorindicator = 'no';
		$ctr = 0;
		$this->destination = $package['destination'];

		foreach ($package['contents'] as $item_id => $values) {

			// To support WPGlobalCart Plugin
			do_action('ph_ups_package_contents_loop_start', $values, $this->settings);

			$values = apply_filters('ph_ups_package_contents', $values, $this->settings);

			$values['data'] = Ph_UPS_Woo_Shipping_Common::wf_load_product($values['data']);
			$ctr++;

			$additional_products = apply_filters('xa_ups_alter_products_list', array($values));	// To support product addon

			foreach ($additional_products as $values) {

				$skip_product = apply_filters('wf_shipping_skip_product', false, $values, $package['contents']);
				if ($skip_product) {
					continue;
				}

				if (!($values['quantity'] > 0 && $values['data']->needs_shipping())) {
					Ph_UPS_Woo_Shipping_Common::debug(sprintf(__('Product #%d is virtual. Skipping.', 'ups-woocommerce-shipping'), $values['data']->id), $this->debug, $this->silent_debug);

					// Add by Default
					Ph_UPS_Woo_Shipping_Common::phAddDebugLog(sprintf('Product #%d is virtual. Skipping from Rate Calculation', $values['data']->id), $this->debug);

					continue;
				}

				if (!$values['data']->get_weight()) {
					Ph_UPS_Woo_Shipping_Common::debug(sprintf(__('Product #%d is missing weight. Aborting.', 'ups-woocommerce-shipping'), $values['data']->id), $this->debug, $this->silent_debug, 'error');

					// Add by Default
					Ph_UPS_Woo_Shipping_Common::phAddDebugLog(sprintf('Product #%d is missing weight. Aborting Rate Calculation', $values['data']->id), $this->debug);

					return;
				}

				// get package weight
				$weight = wc_get_weight((!empty($values['data']->get_weight()) ? $values['data']->get_weight() : 0), $this->settings['weight_unit']);
				//$weight = apply_filters('wf_ups_filter_product_weight', $weight, $package, $item_id );

				// get package dimensions
				if ($values['data']->length && $values['data']->height && $values['data']->width) {

					$dimensions = array(
						number_format(wc_get_dimension((float) $values['data']->length, $this->settings['dim_unit']), 2, '.', ''),
						number_format(wc_get_dimension((float) $values['data']->height, $this->settings['dim_unit']), 2, '.', ''),
						number_format(wc_get_dimension((float) $values['data']->width, $this->settings['dim_unit']), 2, '.', '')
					);
					sort($dimensions);
				}
				if (isset($dimensions)) {
					foreach ($dimensions as $key => $dimension) {	//ensure the dimensions aren't zero
						if ($dimension <= 0) {
							$dimensions[$key] = 0.01;
						}
					}
				}

				// get quantity in cart
				$cart_item_qty = $values['quantity'];
				// get weight, or 1 if less than 1 lbs.
				// $_weight = ( floor( $weight ) < 1 ) ? 1 : $weight;

				$request['Package']	=	array(
					'PackagingType'	=>	array(
						'Code'			=>	'02',
						'Description'	=>	'Package/customer supplied'
					),
					'Description'	=>	'Rate',
				);

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
						'Weight'	=>	(string) round($weight_ozs, 2),
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
						'Weight'	=>	(string) round($weight, 2),
					);
				}


				if ($this->settings['insuredvalue'] || $this->settings['cod'] || $this->settings['cod_enable']) {

					// InsuredValue
					if ($this->settings['insuredvalue']) {

						// REST doesn't support "InsuredValue" node, it's handled in REST file
						$request['Package']['PackageServiceOptions']['InsuredValue']	=	array(
							'CurrencyCode'	=>	$this->settings['currency_type'],
							'MonetaryValue'	=>	(string) round(( PH_WC_UPS_Common_Utils::ph_get_insurance_amount($values['data'], $this->settings['fixedProductPrice']) / $this->settings['conversion_rate']), 2)
						);
					}

					//Cod
					if (($this->settings['cod'] && isset($_GET['wf_ups_shipment_confirm'])) || ($this->settings['cod_enable'] && !isset($_GET['wf_ups_shipment_confirm']))) {

						if (! PH_WC_UPS_Common_Utils::is_shipment_level_cod_required($this->destination['country'])) {

							$cod_amount 	= !empty($values['data']->get_price()) ? $values['data']->get_price() : $this->settings['fixedProductPrice'];
							$codfundscode 	= in_array($this->destination['country'], array('AR', 'BR', 'CL')) ? 9 : 0;

							$request['Package']['PackageServiceOptions']['COD']	=	array(
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

					$refrigeratorindicator  = ($refrigeratorindicator == 'yes') ? $refrigeratorindicator : $refrigerator;
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

				//Adding all the items to the stored packages
				$request['Package']['items'] = array($values['data']->obj);

				// Direct Delivery option
				$directdeliveryonlyindicator = PH_WC_UPS_Common_Utils::get_individual_product_meta(array($values['data']), '_wf_ups_direct_delivery');

				if (isset($_GET['dd'])) {

					PH_UPS_WC_Storage_Handler::ph_add_and_save_meta_data($order, '_ph_ups_direct_delivery', $_GET['dd']);

					$directdeliveryonlyindicator = !empty($_GET['dd']) ? $_GET['dd'] : $directdeliveryonlyindicator;
				}

				if ($directdeliveryonlyindicator == 'yes') {
					$request['Package']['DirectDeliveryOnlyIndicator'] = $directdeliveryonlyindicator;
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

				// Boolean to check if unit conversion is required. To support multi-vendor addon
				if (isset($package['metrics'])) {
					$request['Package']['metrics'] = $package['metrics'] ? true : false;
				}

				for ($i = 0; $i < $cart_item_qty; $i++)
					$requests[] = $request;
			}

			// To support WPGlobalCart Plugin
			do_action('ph_ups_package_contents_loop_end', $values, $this->settings);
		}

		return $requests;
	}

}