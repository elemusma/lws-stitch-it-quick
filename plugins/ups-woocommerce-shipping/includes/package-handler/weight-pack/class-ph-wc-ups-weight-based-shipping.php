<?php
/**
 * Weight Based Shipping for WooCommerce UPS Shipping Plugin.
 *
 * @package ups-woocommerce-shipping
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PH_WC_UPS_Weight_Based_Shipping
 *
 * Generates box packages for UPS Shipping.
 */
class PH_WC_UPS_Weight_Based_Shipping {

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
	 * Constructor method for PH_WC_UPS_Weight_Based_Shipping Class.
	 */
	public function __construct( $settings ) {
	
		$this->settings     = $settings;

		$this->debug		= $this->settings['debug'];
		$this->silent_debug = $this->settings['silent_debug'];
	}
	
	/**
	 * weight_based_shipping function.
	 *
	 * @access private
	 * @param mixed $package
	 * @return void
	 */
	public function weight_based_shipping($package, $order, $params = array())
	{
		// Tempprary variable to support metrics - multivendor addon, because $package will be overwritten
		$actualPackage = $package;

		global $woocommerce;
		$pre_packed_contents = array();

		if (!class_exists('WeightPack')) {
			include_once 'class-wf-weight-packing.php';
		}

		$weight_pack = new WeightPack($this->settings['weight_packing_process']);
		$weight_pack->set_max_weight($this->settings['box_max_weight']);

		$package_total_weight = 0;
		$insured_value = 0;

		$requests = array();
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
					Ph_UPS_Woo_Shipping_Common::debug(sprintf(__('Product # %d is virtual. Skipping.', 'ups-woocommerce-shipping'), $values['data']->id), $this->debug, $this->silent_debug);

					// Add by Default
					Ph_UPS_Woo_Shipping_Common::phAddDebugLog(sprintf('Product # %d is virtual. Skipping from Rate Calculation.', $values['data']->id), $this->debug);

					continue;
				}

				if (!$values['data']->get_weight()) {
					Ph_UPS_Woo_Shipping_Common::debug(sprintf(__('Product # %d is missing weight. Aborting.', 'ups-woocommerce-shipping'), $values['data']->id), $this->debug, $this->silent_debug, 'error');

					// Add by Default
					Ph_UPS_Woo_Shipping_Common::phAddDebugLog(sprintf('Product # %d is missing weight. Aborting Rate Calculation.', $values['data']->id), $this->debug);

					return;
				}

				$pre_packed = get_post_meta($values['data']->id, '_wf_pre_packed_product_var', 1);

				if (empty($pre_packed) || $pre_packed == 'no') {
					$parent_product_id = wp_get_post_parent_id($values['data']->id);
					$pre_packed = get_post_meta(!empty($parent_product_id) ? $parent_product_id : $values['data']->id, '_wf_pre_packed_product', 1);
				}

				$pre_packed = apply_filters('wf_ups_is_pre_packed', $pre_packed, $values);

				if (!empty($pre_packed) && $pre_packed == 'yes') {
					$pre_packed_contents[] = $values;
					Ph_UPS_Woo_Shipping_Common::debug(sprintf(__('Pre Packed product. Skipping the product # %d', 'ups-woocommerce-shipping'), $values['data']->id), $this->debug, $this->silent_debug);

					// Add by Default
					Ph_UPS_Woo_Shipping_Common::phAddDebugLog(sprintf('Pre Packed product. Skipping the product %d from Weight Packing Algorithm', $values['data']->id), $this->debug);

					continue;
				}

				$product_weight = $this->xa_get_volumatric_products_weight($values['data']);
				$weight_pack->add_item(wc_get_weight($product_weight, $this->settings['weight_unit']), $values['data'], $values['quantity']);
			}

			// To support WPGlobalCart Plugin
			do_action('ph_ups_package_contents_loop_end', $values, $this->settings);
		}

		$pack	=	$weight_pack->pack_items();
		$errors	=	$pack->get_errors();
		if (!empty($errors)) {
			//do nothing
			return;
		} else {
			$boxes		=	$pack->get_packed_boxes();
			$unpacked_items	=	$pack->get_unpacked_items();

			$insured_value			=	0;

			if ( !empty($order) ) {
				$order_total	=	$order->get_total();
			}

			$packages		=	array_merge($boxes,	$unpacked_items); // merge items if unpacked are allowed
			$package_count	=	sizeof($packages);

			// get all items to pass if item info in box is not distinguished
			$packable_items	=	$weight_pack->get_packable_items();
			$all_items		=	array();
			if (is_array($packable_items)) {
				foreach ($packable_items as $packable_item) {
					$all_items[]	=	$packable_item['data'];
				}
			}

			foreach ($packages as $package) {

				$packed_products 		= array();
				$insured_value  		= 0;
				$refrigeratorindicator	= 'no';
				$clinicalid 			= '';
				$cod_amount 			= 0;

				if (!empty($package['items'])) {

					foreach ($package['items'] as $item) {

						if ($this->settings['insuredvalue']) {

							$insured_value 	= $insured_value + PH_WC_UPS_Common_Utils::ph_get_insurance_amount($item, $this->settings['fixedProductPrice']);
						}

						if ($this->settings['isc']) {

							$clinicalvar            = get_post_meta($item->id, '_ph_ups_clinicaltrials_var', 1);
							$refrigerator_var       = get_post_meta($item->id, '_ph_ups_refrigeration_var', 1);

							if (empty($refrigerator_var) || !isset($refrigerator_var)) {

								$refrigerator 	= get_post_meta($item->id, '_ph_ups_refrigeration', 1);
							} else {

								$refrigerator 	= $refrigerator_var;
							}

							if (empty($clinicalvar) || !isset($clinicalvar)) {

								$clinical 	= get_post_meta($item->id, '_ph_ups_clinicaltrials', 1);
							} else {

								$clinical 	= $clinicalvar;
							}

							$refrigeratorindicator  = ($refrigeratorindicator == 'yes') ? $refrigeratorindicator : $refrigerator;
							$clinicalid 			= (isset($clinicalid) && !empty($clinicalid)) ? $clinicalid : $clinical;
						}

						if ($this->settings['cod_enable'] || $this->settings['cod']) {

							$cod_amount = $cod_amount + (!empty($item->get_price()) ? $item->get_price() : $this->settings['fixedProductPrice']);
						}
					}
				} elseif (isset($order_total) && $package_count) {

					$insured_value	=	$order_total / $package_count;

					if ($this->settings['cod_enable'] || $this->settings['cod']) {

						$cod_amount = $order_total / $package_count;
					}
				}

				$packed_products	=	isset($package['items']) ? $package['items'] : $all_items;
				// Creating package request
				$package_total_weight	=	$package['weight'];

				$request['Package']	=	array(
					'PackagingType'	=>	array(
						'Code'			=>	'02',
						'Description'	=>	'Package/customer supplied',
					),
					'Description'	=>	'Rate',
				);

				if ((isset($params['service_code']) && 92 == $params['service_code']) || (92 == $this->settings['service_code'])) { // Surepost Less Than 1LBS
					if ( 'LBS' === $this->settings['weight_unit'] ) { // make sure weight in pounds
						$weight_ozs = $package_total_weight * 16;
					} else {
						$weight_ozs = $package_total_weight * 35.274; // From KG
					}

					$request['Package']['PackageWeight']	=	array(
						'UnitOfMeasurement'	=>	array(
							'Code'	=>	'OZS'
						),
						'Weight'	=>	(string) round($weight_ozs, 2)
					);
				} else {

					// Invalid Weight Error if Weight less is than 0.05 for Estimated Delivery Option
					if ($package_total_weight < 0.05) {
						$package_total_weight = 0.05;
					}

					$request['Package']['PackageWeight']	=	array(
						'UnitOfMeasurement'	=>	array(
							'Code'	=>	$this->settings['weight_unit']
						),
						'Weight'	=>	(string) round($package_total_weight, 2)
					);
				}

				// InsuredValue
				if ($this->settings['insuredvalue']) {
					
					// REST doesn't support "InsuredValue" node, it's handled in REST file
					$request['Package']['PackageServiceOptions']['InsuredValue']	=	array(
						'CurrencyCode'	=>	$this->settings['currency_type'],
						'MonetaryValue'	=>	(string) round(($insured_value / $this->settings['conversion_rate']), 2),
					);
				}

				if ($this->settings['isc']) {

					$refrigeratorindicator = ($refrigeratorindicator == 'yes' ? 'yes' : (isset($this->settings['ph_ups_refrigeration']) && $this->settings['ph_ups_refrigeration'] == 'yes' ? 'yes' : 'no'));

					$clinicalid  = (!empty($clinicalid) ? $clinicalid  : (isset($this->settings['ph_ups_clinicaltrials']) && !empty($this->settings['ph_ups_clinicaltrials']) ? $this->settings['ph_ups_clinicaltrials'] : ''));


					if ($refrigeratorindicator == 'yes') {

						$request['Package']['PackageServiceOptions']['RefrigerationIndicator'] = '1';
					}

					if (isset($clinicalid) && !empty($clinicalid) && isset($_GET['wf_ups_shipment_confirm'])) {

						$request['Package']['PackageServiceOptions']['ClinicaltrialsID'] = $clinicalid;
					}
				}

				if (($this->settings['cod'] && isset($_GET['wf_ups_shipment_confirm'])) || ($this->settings['cod_enable'] && !isset($_GET['wf_ups_shipment_confirm']))) {

					if (! PH_WC_UPS_Common_Utils::is_shipment_level_cod_required($this->destination['country'])) {

						$codfundscode = in_array($this->destination['country'], array('AR', 'BR', 'CL')) ? 9 : 0;

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

				// Direct Delivery option
				$directdeliveryonlyindicator = PH_WC_UPS_Common_Utils::get_individual_product_meta($packed_products, '_wf_ups_direct_delivery');

				if (isset($_GET['dd'])) {

					PH_UPS_WC_Storage_Handler::ph_add_and_save_meta_data($order->get_id(), '_ph_ups_direct_delivery', $_GET['dd']);

					$directdeliveryonlyindicator = !empty($_GET['dd']) ? $_GET['dd'] : $directdeliveryonlyindicator;
				}

				if ($directdeliveryonlyindicator == 'yes') {
					$request['Package']['DirectDeliveryOnlyIndicator'] = $directdeliveryonlyindicator;
				}

				// Delivery Confirmation
				if (isset($params['delivery_confirmation_applicable']) && $params['delivery_confirmation_applicable'] == true) {

					$signature_option = PH_WC_UPS_Common_Utils::get_package_signature($packed_products);
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

				$request['Package']['items'] = $package['items'];	    //Required for numofpieces in case of worldwidefreight

				// Boolean to check if unit conversion is required. To support multi-vendor addon
				if (isset($actualPackage['metrics'])) {
					$request['Package']['metrics'] = $actualPackage['metrics'] ? true : false;
				}

				$requests[] = $request;
			}
		}
		//add pre packed item with the package
		if (!empty($pre_packed_contents)) {

			// Boolean to check if unit conversion is required. To support multi-vendor addon
			if (isset($actualPackage['metrics'])) {
				$params['metrics'] = $actualPackage['metrics'] ? true : false;
			}

			if( !class_exists( 'PH_WC_UPS_Pre_Packed_Product' )) {
				include_once( PH_WC_UPS_PLUGIN_DIR_PATH . 'includes/package-handler/pre-packed/class-ph-wc-ups-pre-packed-product.php');
			}

			$pre_packed_obj = new PH_WC_UPS_Pre_Packed_Product( $this->settings );
			$prepacked_requests = $pre_packed_obj->ph_ups_add_pre_packed_product($pre_packed_contents, $this->destination, $order, $params);

			if (is_array($prepacked_requests)) {
				$requests = array_merge($requests, $prepacked_requests);
			}
		}
		return $requests;
	}

	/**
	 * Get Volumetric weight .
	 * @param object wf_product | wc_product object .
	 * @return float Volumetric weight if it is higher than product weight else actual product weight.
	 */
	private function xa_get_volumatric_products_weight($values)
	{
		$wc_weight_unit 	= get_option('woocommerce_weight_unit');

		if (!empty($this->settings['volumetric_weight']) && $this->settings['volumetric_weight'] == 'yes') {

			$length = wc_get_dimension((float) $values->get_length(), 'cm');
			$width 	= wc_get_dimension((float) $values->get_width(), 'cm');
			$height = wc_get_dimension((float) $values->get_height(), 'cm');
			if ($length != 0 && $width != 0 && $height != 0) {
				$volumetric_weight = $length * $width * $height /  5000; // Divide by 5000 as per fedex standard
			}
		}

		$weight = !empty($values->get_weight()) ? $values->get_weight() : 0;

		if (!empty($volumetric_weight)) {
			$volumetric_weight = wc_get_weight($volumetric_weight, $wc_weight_unit, 'kg');
			if ($volumetric_weight > $weight) {
				$weight = $volumetric_weight;
			}
		}
		return $weight;
	}

}