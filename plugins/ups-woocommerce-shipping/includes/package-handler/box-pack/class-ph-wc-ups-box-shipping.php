<?php
/**
 * Box Shipping for WooCommerce UPS Shipping Plugin.
 *
 * @package ups-woocommerce-shipping
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PH_WC_UPS_Box_Shipping
 *
 * Generates box packages for UPS Shipping.
 */
class PH_WC_UPS_Box_Shipping {

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
	 * Constructor method for PH_WC_UPS_Box_Shipping Class.
	 */
	public function __construct( $settings ) {

		$this->settings     = $settings;

		$this->debug		= $this->settings['debug'];
		$this->silent_debug = $this->settings['silent_debug'];
	}
	
	/**
	 * Perform box shipping calculation.
	 *
	 * @param array $package The package details.
	 * @param WC_Order|null $order The WooCommerce order object.
	 * @param array $params Additional parameters for shipping calculation.
	 * @return array An array of box packing shipping requests.
	 */
	public function box_shipping($package, $order = null, $params = array())
	{
		global $woocommerce;
		$pre_packed_contents = array();
		$requests = array();

		if (!class_exists('PH_UPS_Boxpack')) {
			include_once 'class-wf-packing.php';
		}
		if (!class_exists('PH_UPS_Boxpack_Stack')) {
			include_once 'class-wf-packing-stack.php';
		}

		volume_based:
		if (isset($this->settings['mode']) && $this->settings['mode'] == 'stack_first') {
			$boxpack = new PH_UPS_Boxpack_Stack();
		} else {
			$boxpack = new PH_UPS_Boxpack($this->settings['mode'], $this->settings['exclude_box_weight']);
		}

		if ( ((isset($package['destination']['country']) && $this->settings['origin_country'] == $package['destination']['country']) || $this->settings['origin_country'] != 'US') && (isset($this->settings['boxes']['E_10KG_BOX']) || isset($this->settings['boxes']['D_25KG_BOX']) ) ) {

			unset($this->settings['boxes']['E_10KG_BOX']);
			unset($this->settings['boxes']['D_25KG_BOX']);
		}

		// Define boxes
		if (!empty($this->settings['boxes'])) {

			foreach ($this->settings['boxes'] as $key => $box) {

				// Skip if box is not enabled in settings.
				if (!$box['box_enabled'] || (!$this->settings['upsSimpleRate'] && $box['box_enabled'] && array_key_exists($key, PH_WC_UPS_Constants::SIMPLE_RATE_BOX_CODES ))) {
					continue;
				}

				$boxId = '';

				// Get the actual Box ID
				if (array_key_exists($key, $this->settings['packaging'])) {

					$boxId = $this->settings['packaging'][$key]['code'];
				}

				if (array_key_exists($key, PH_WC_UPS_Constants::SIMPLE_RATE_BOX_CODES)) {
					$boxId = $key;
				}

				$newbox = $boxpack->add_box($box['outer_length'], $box['outer_width'], $box['outer_height'], $box['box_weight']);
				$newbox->set_inner_dimensions($box['inner_length'], $box['inner_width'], $box['inner_height']);

				if ($box['max_weight']) {
					$newbox->set_max_weight($box['max_weight']);
				}

				$newbox->set_id($boxId);

				if (isset($box['boxes_name']) && !empty($box['boxes_name'])) {

					$newbox->set_box_name($box['boxes_name']);
				} else {

					$newbox->set_box_name('Custom Box');
				}


				if (isset($this->settings['mode']) && 'stack_first' === $this->settings['mode']) {

					$newbox = $boxpack->add_box($box['outer_height'], $box['outer_width'], $box['outer_length'], $box['box_weight']);
					$newbox->set_inner_dimensions($box['inner_height'], $box['inner_width'], $box['inner_length']);

					if ($box['max_weight']) {
						$newbox->set_max_weight($box['max_weight']);
					}

					$newbox->set_id($boxId);

					if (isset($box['boxes_name']) && !empty($box['boxes_name'])) {

						$newbox->set_box_name($box['boxes_name']);
					} else {

						$newbox->set_box_name('Custom Box');
					}
				}
			}
		}

		// Add items
		$ctr 					= 0;
		$pre_packed_contents 	= [];
		$this->destination 		= $package['destination'];

		if (isset($package['contents'])) {
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
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog(sprintf('Pre Packed product. Skipping the product %d from Box Packing Algorithm', $values['data']->id), $this->debug);

						continue;
					}

					if ($values['data']->length && $values['data']->height && $values['data']->width && $values['data']->weight) {

						$dimensions = array($values['data']->length, $values['data']->width, $values['data']->height);

						for ($i = 0; $i < $values['quantity']; $i++) {

							$boxpack->add_item(
								number_format(wc_get_dimension((float) $dimensions[0], $this->settings['dim_unit']), 6, '.', ''),
								number_format(wc_get_dimension((float) $dimensions[1], $this->settings['dim_unit']), 6, '.', ''),
								number_format(wc_get_dimension((float) $dimensions[2], $this->settings['dim_unit']), 6, '.', ''),
								number_format(wc_get_weight((!empty($values['data']->get_weight()) ? $values['data']->get_weight() : 0), $this->settings['weight_unit']), 6, '.', ''),
								PH_WC_UPS_Common_Utils::ph_get_insurance_amount($values['data'], $this->settings['fixedProductPrice']),
								$values['data'] // Adding Item as meta
							);
						}
					} else {
						Ph_UPS_Woo_Shipping_Common::debug(sprintf(__('UPS Parcel Packing Method is set to Pack into Boxes. Product #%d is missing dimensions. Aborting.', 'ups-woocommerce-shipping'), $ctr), $this->debug, $this->silent_debug, 'error');

						// Add by Default
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog(sprintf('UPS Parcel Packing Method is set to Pack into Boxes. Product #%d is missing dimensions. Aborting Rate Calulation.', $values['data']->id), $this->debug);

						return;
					}
				}

				// To support WPGlobalCart Plugin
				do_action('ph_ups_package_contents_loop_end', $values, $this->settings);
			}
		} else {
			wf_admin_notice::add_notice('No package found. Your product may be missing weight/length/width/height');

			// Add by Default
			Ph_UPS_Woo_Shipping_Common::phAddDebugLog('No package found. Your product may be missing weight/length/width/height', $this->debug);
		}
		// Pack it
		$boxpack->pack();

		// Get packages
		$box_packages 	= $boxpack->get_packages();
		$stop_fallback 	= apply_filters('xa_ups_stop_fallback_from_stack_first_to_vol_based', false);




		if (isset($this->settings['mode']) && 'stack_first' === $this->settings['mode'] && !$stop_fallback && $this->settings['stack_to_volume']) {

			foreach ($box_packages as $key => $box_package) {

				$box_volume 					= $box_package->length * $box_package->width * $box_package->height;
				$box_used_volume 				= isset($box_package->volume) && !empty($box_package->volume) ? $box_package->volume : 1;
				$box_used_volume_percentage 	= ($box_used_volume * 100) / $box_volume;

				if (isset($box_used_volume_percentage) && $box_used_volume_percentage < 44) {

					$this->settings['mode'] = 'volume_based';

					Ph_UPS_Woo_Shipping_Common::debug('(FALLBACK) : Stack First Option changed to Volume Based', $this->debug, $this->silent_debug);

					// Add by Default
					Ph_UPS_Woo_Shipping_Common::phAddDebugLog('(FALLBACK) : Stack First Method changed to Volume Based. Reason: Selected Box Volume % used is less than 44%', $this->debug);

					goto volume_based;
					break;
				}
			}
		}

		$ctr = 0;

		$standard_boxes_without_dimensions = array('01', '24', '25');

		foreach ($box_packages as $key => $box_package) {
			$ctr++;

			// if( $this->debug ) {

			// 	Ph_UPS_Woo_Shipping_Common::debug( "Box Packing Result: PACKAGE " . $ctr . " (" . $key . ")\n<pre>" . print_r( $box_package,true ) . "</pre>", $this->debug, $this->silent_debug, 'error');
			// }

			$weight	 = $box_package->weight;
			$dimensions = array($box_package->length, $box_package->width, $box_package->height);


			$boxCode = $box_package->id;

			// UPS packaging type select, If not present set as custom box
			if (!isset($box_package->id) || empty($box_package->id) || !array_key_exists($box_package->id, PH_WC_UPS_Constants::PACKAGING_SELECT )) {
				$box_package->id = '02';
			}

			sort($dimensions);
			// get weight, or 1 if less than 1 lbs.
			// $_weight = ( floor( $weight ) < 1 ) ? 1 : $weight;

			$box_name = isset($box_package->box_name) && !empty($box_package->box_name) ? $box_package->box_name : '';

			$request['Package']	=	array(
				'PackagingType'	=>	array(
					'Code'				=>	$box_package->id,
					'Description'	=>	'Package/customer supplied'
				),
				'Description'	=> 'Rate',
				'BoxCode' 		=> $boxCode,
				'box_name'		=> $box_name,
			);

			// Dimensions Mismatch error will come for some Default Boxes
			if (!in_array($box_package->id, $standard_boxes_without_dimensions)) {

				$request['Package']['Dimensions'] = array(

					'UnitOfMeasurement'	=>	array(
						'Code'	=>	$this->settings['dim_unit'],
					),
					'Length'	=>	(string) round($dimensions[2], 2),
					'Width'		=>	(string) round($dimensions[1], 2),
					'Height'	=>	(string) round($dimensions[0], 2)
				);
			}

			// Getting packed items
			$packed_items	=	array();
			if (!empty($box_package->packed) && is_array($box_package->packed)) {

				foreach ($box_package->packed as $item) {
					$item_product	=	$item->meta;
					$packed_items[] = $item_product;
				}
			}

			if ($this->settings['isc'] || $this->settings['cod_enable'] || $this->settings['cod']) {

				$refrigeratorindicator  = 'no';
				$clinicalid 			= '';
				$cod_amount = 0;

				foreach ($packed_items as $key => $value) {

					if ($this->settings['isc']) {

						$clinicalvar            = get_post_meta($value->id, '_ph_ups_clinicaltrials_var', 1);
						$refrigerator_var       = get_post_meta($value->id, '_ph_ups_refrigeration_var', 1);

						if (empty($refrigerator_var) || !isset($refrigerator_var)) {

							$refrigerator 	= get_post_meta($value->id, '_ph_ups_refrigeration', 1);
						} else {

							$refrigerator 	= $refrigerator_var;
						}

						if (empty($clinicalvar) || !isset($clinicalvar)) {

							$clinical 	= get_post_meta($value->id, '_ph_ups_clinicaltrials', 1);
						} else {

							$clinical 	= $clinicalvar;
						}

						$refrigeratorindicator  = ($refrigeratorindicator == 'yes') ? $refrigeratorindicator : $refrigerator;
						$clinicalid 			= (isset($clinicalid) && !empty($clinicalid)) ? $clinicalid : $clinical;
					}

					if ($this->settings['cod_enable'] || $this->settings['cod']) {

						$cod_amount = $cod_amount + (!empty($value->get_price()) ? $value->get_price() : $this->settings['fixedProductPrice']);
					}
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
			}

			if ((isset($params['service_code']) && $params['service_code'] == 92) || ($this->settings['service_code'] == 92)) // Surepost Less Than 1LBS
			{
				if ('LBS' === $this->settings['weight_unit']) { // make sure weight in pounds
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
						'MonetaryValue'	=>	(string)round(($box_package->value / $this->settings['conversion_rate']), 2)
					);
				}

				//COD
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
			}

			//Adding all the items to the stored packages
			if (isset($box_package->unpacked) && $box_package->unpacked && isset($box_package->obj)) {
				$request['Package']['items'] = array($box_package->obj);
			} else {
				$request['Package']['items'] = $packed_items;
			}
			// Direct Delivery option
			$directdeliveryonlyindicator = !empty($packed_items) ? PH_WC_UPS_Common_Utils::get_individual_product_meta($packed_items, '_wf_ups_direct_delivery') : PH_WC_UPS_Common_Utils::get_individual_product_meta(array($box_package), '_wf_ups_direct_delivery'); // else part is for unpacked item

			if (isset($_GET['dd'])) {

				PH_UPS_WC_Storage_Handler::ph_add_and_save_meta_data($order->get_id(), '_ph_ups_direct_delivery', $_GET['dd']);

				$directdeliveryonlyindicator = !empty($_GET['dd']) ? $_GET['dd'] : $directdeliveryonlyindicator;
			}

			if ($directdeliveryonlyindicator == 'yes') {
				$request['Package']['DirectDeliveryOnlyIndicator'] = $directdeliveryonlyindicator;
			}

			// Delivery Confirmation
			if (isset($params['delivery_confirmation_applicable']) && $params['delivery_confirmation_applicable'] == true) {

				$signature_option = PH_WC_UPS_Common_Utils::get_package_signature($request['Package']['items']);	//Works on both packed and unpacked items
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

			$requests[] = $request;
		}
		//add pre packed item with the package
		if (!empty($pre_packed_contents)) {

			// Boolean to check if unit conversion is required. To support multi-vendor addon
			if (isset($package['metrics'])) {
				$params['metrics'] = $package['metrics'] ? true : false;
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

}