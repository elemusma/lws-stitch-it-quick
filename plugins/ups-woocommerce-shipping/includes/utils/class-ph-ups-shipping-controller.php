<?php
/**
 * Shipping Controller for WooCommerce UPS Shipping Plugin with Print Label.
 *
 * @package ups-woocommerce-shipping
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'PH_WC_UPS_Shipping_Controller' ) ) {

	/**
	 * Common Utils Class.
	 */
	class PH_WC_UPS_Shipping_Controller {

		/**
		 * Vendor Id
		 */
		public $vendor_id = null;

		/**
		 * General variables
		 */
		public $settings, $instance_settings, $debug;

		/**
		 * Other variables
		 */
		public $current_package_items_and_quantity, $international_delivery_confirmation_applicable, $ph_ups_selected_access_point_details;

		/**
		 * Common Utils Class Constructor.
		 */
		public function __construct( $settings ) {

			$this->settings = $settings;
		}


		/**
		 * ph_get_shipping_rates function.
		 *
		 * Returns UPS shipping rates.
		 *
		 * @param array $package packages present in the checkout.
		 * @param string $invoker Invoking from UPS or UPS Shipping Zone.
		 * @return array $instance_id Instance_id (Optional).
		 */
		public function ph_get_shipping_rates( $package, $invoker, $instance_id = '' ) {

			// New registration method with active plugin license key
			$is_new_and_active_reg = false;
			$api_access_details 		= false;
			$this->debug 				= $this->settings['debug'];
			
			// Check if instance_id is present and add services chosen from it.
			if( $instance_id ) {

				$this->instance_settings 	= get_option( 'woocommerce_'. PH_WC_UPS_ZONE_SHIPPING .'_'. $instance_id.'_settings', null );
				$this->settings['services'] = isset( $this->instance_settings['services'] ) && !empty( $this->instance_settings['services'] ) ? $this->instance_settings['services'] : array();
			}

			//Check if new registration method
			if (Ph_UPS_Woo_Shipping_Common::phIsNewRegistration()) {
				// Check for active plugin license
				if (!Ph_UPS_Woo_Shipping_Common::phHasActiveLicense()) {

					if( 'UPS Shipping Zone' === $invoker) {
						
						Ph_UPS_Woo_Shipping_Common::phAddDebugLog('Please use a valid plugin license to continue using WooCommerce UPS Shipping Method based on matching zones.', $this->debug);
					} else {

						Ph_UPS_Woo_Shipping_Common::phAddDebugLog('Please use a valid plugin license to continue using WooCommerce UPS Shipping Plugin with Print Label', $this->debug);
					}
					return;

				} else {

					$is_new_and_active_reg = true;

					$api_access_details = Ph_Ups_Endpoint_Dispatcher::phGetApiAccessDetails();

					// Proceed with calculate shipping only if api details are available
					if (!$api_access_details)
						return;
				}
			}

			// Address Validation applicable for US and PR
			if ( $this->settings['address_validation'] && in_array( $package['destination']['country'], array( 'US', 'PR' ) ) && ! is_admin() && ! $this->settings['residential'] ) {

				require_once PH_WC_UPS_PLUGIN_DIR_PATH . 'includes/ups_rest/class-ph-ups-rest-address-validation.php';

				$Ph_Ups_Address_Validation_Rest = new Ph_Ups_Address_Validation_Rest( $package['destination'], $this->settings );
				$residential_code               = $Ph_Ups_Address_Validation_Rest->residential_check;

				// To get Address Validation Result Outside
				$residential_code = apply_filters( 'ph_ups_zone_rate_address_validation_result', $residential_code, $package['destination'], $this->settings );

				if ( $residential_code == 2 ) {
					$this->settings['residential'] = true;
				}
			}

			$this->ph_ups_selected_access_point_details = ! empty( $package['ph_ups_selected_access_point_details'] ) ? $package['ph_ups_selected_access_point_details'] : null;

			// Only return rates if the package has a destination including country, postcode
			if ( '' == $package['destination']['country'] ) {
				Ph_UPS_Woo_Shipping_Common::debug( __( $invoker . ': Country not yet supplied. Rates not requested.', 'ups-woocommerce-shipping' ), $this->debug );
				return;
			}

			if ( in_array( $package['destination']['country'], PH_WC_UPS_Constants::NO_POSTCODE_COUNTRY_ARRAY ) ) {
				if ( empty( $package['destination']['city'] ) ) {
					Ph_UPS_Woo_Shipping_Common::debug( __( $invoker . ': City not yet supplied. Rates not requested.', 'ups-woocommerce-shipping' ), $this->debug );
					return;
				}
			} elseif ( '' == $package['destination']['postcode'] ) {
				Ph_UPS_Woo_Shipping_Common::debug( __( $invoker . ': Zip not yet supplied. Rates not requested.', 'ups-woocommerce-shipping' ), $this->debug );
				return;
			}
			// Turn off Insurance value if Cart subtotal is less than the specified amount in plugin settings
			if ( isset( $package['cart_subtotal'] ) && $package['cart_subtotal'] <= $this->settings['min_order_amount_for_insurance'] ) {
				$this->settings['insuredvalue'] = false;
			}

			// Skip Products
			if ( ! empty( $this->settings['skip_products'] ) ) {
				$package = PH_WC_UPS_Common_Utils::skip_products( $package, $this->settings['skip_products'], 'Zone Rate', $this->debug, $this->settings['silent_debug'] );
				if ( empty( $package['contents'] ) ) {
					return;
				}
			}

			if ( ! empty( $this->settings['min_weight_limit'] ) || ! empty( $this->settings['max_weight_limit'] ) ) {
				$need_shipping = PH_WC_UPS_Common_Utils::check_min_weight_and_max_weight( $package, $this->settings['min_weight_limit'], $this->settings['max_weight_limit'], 'Zone Rate', $this->debug );
				if ( ! $need_shipping ) {
					return;
				}
			}

			// To Support Multi Vendor plugin
			$packages = apply_filters( 'ph_wc_filter_package_address', array( $package ), $this->settings['ship_from_address'] );

			// Woocommerce packages after dividing the products based on vendor, if vendor plugin exist
			$wc_total_packages_count = count( $packages );
			$package_rates           = array();
			$allPackageRateCount     = array();

			foreach ( $packages as $packageKey => $package ) {

				// Check Hazardous Materials in Package
				$is_hazardous_materials = false;

				if ( isset( $package['contents'] ) && ! empty( $package['contents'] ) ) {

					foreach ( $package['contents'] as $key => $items ) {

						if ( isset( $items['variation_id'] ) && ! empty( $items['variation_id'] ) && get_post_meta( $items['variation_id'], '_ph_ups_hazardous_materials', 1 ) == 'yes' ) {

							$is_hazardous_materials = true;
							break;
						} elseif ( get_post_meta( $items['product_id'], '_ph_ups_hazardous_materials', 1 ) == 'yes' ) {

							$is_hazardous_materials = true;
							break;
						}
					}
				}

				// Reset Internal Rates Array after each Vendor Package Rate Calculation

				$rates = array();

				$package = apply_filters( 'ph_wc_customize_package_on_cart_and_checkout', $package );  // Customize the packages if cart contains bundled products
				// To pass the product info with rates meta data
				foreach ( $package['contents'] as $product ) {
					$product_id = ! empty( $product['variation_id'] ) ? $product['variation_id'] : $product['product_id'];
					$this->current_package_items_and_quantity[ $product_id ] = $product['quantity'];
				}

				$this->vendor_id = ! empty( $package['vendorID'] ) ? $package['vendorID'] : null;

				$package_params = array();
				// US to US and PR, CA to CA , PR to US or PR are domestic remaining all pairs are international
				if ( ( ( $this->settings['origin_country'] == $package['destination']['country'] ) && in_array( $this->settings['origin_country'], PH_WC_UPS_Constants::DC_DOMESTIC_COUNTRIES ) )
					|| ( ( $this->settings['origin_country'] == 'US' || $this->settings['origin_country'] == 'PR' ) && ( $package['destination']['country'] == 'US' || $package['destination']['country'] == 'PR' ) ) ) {

					$package_params['delivery_confirmation_applicable'] = true;
				} else {
					$this->international_delivery_confirmation_applicable = true;
				}

				$package_generator = new PH_WC_UPS_Package_Generator( $this->settings );
				$package_requests  = $package_generator->get_package_requests( $package, null, $package_params );

				$indexKey             = 0;
				$maxIndex             = 50;
				$packageCount         = 0;
				$new_package_requests = array();

				foreach ( $package_requests as $key => $value ) {

					++$packageCount;

					if ( $packageCount <= $maxIndex ) {

						$new_package_requests[ $indexKey ][] = $value;
					} else {

						$packageCount = 1;
						++$indexKey;
						$new_package_requests[ $indexKey ][] = $value;
					}
				}

				$internal_package_count = ! empty( $new_package_requests ) && is_array( $new_package_requests ) ? count( $new_package_requests ) : 0;
				$single_package         = true;

				if ( ! empty( $new_package_requests ) ) {

					foreach ( $new_package_requests as $key => $newPackageRequest ) {

						if ( ! class_exists( 'PH_Shipping_UPS_Rest' ) ) {
							include_once PH_WC_UPS_PLUGIN_DIR_PATH . 'includes/ups_rest/class-ph-shipping-ups-rest.php';
						}

						$ups_rest = new PH_Shipping_UPS_Rest( null, $instance_id );

						// To get rate for services like ups ground, 3 day select etc.
						$rate_requests = $ups_rest->get_rate_requests( $newPackageRequest, $package, '', '', $this->international_delivery_confirmation_applicable );
						$rate_response = $ups_rest->process_result( $ups_rest->get_result( $rate_requests, '', $key, '' ), '', $this->current_package_items_and_quantity, $this->vendor_id, $rate_requests );

						if ( ! empty( $rate_response ) ) {
							$rates[ $key ]['general'][] = $rate_response;
						}

						// End of get rates for services like ups ground, 3 day select etc.

						// For Worldwide Express Freight Service
						if ( isset( $this->settings['services'][96]['enabled'] ) && $this->settings['services'][96]['enabled'] ) {

							$rate_requests       = $ups_rest->get_rate_requests( $newPackageRequest, $package, 'Pallet', 96, $this->international_delivery_confirmation_applicable );
							$rates[ $key ][96][] = $ups_rest->process_result( $ups_rest->get_result( $rate_requests, 'WWE Freight', $key, '' ), '', $this->current_package_items_and_quantity, $this->vendor_id, $rate_requests );
						}

						// GFP Rate Request
						// if (isset($this->settings['services']['US48']['enabled']) && $this->settings['services']['US48']['enabled']) {

							// if (!$is_hazardous_materials) {

							// $rate_requests  = $ups_rest->get_rate_requests_gfp($newPackageRequest, $package);
							// $rates[$key]['US48'][]  = $ups_rest->process_result_gfp($ups_rest_rates->get_result_gfp($rate_requests, 'UPS GFP', $key, ''), $this->current_package_items_and_quantity, $this->vendor_id );
							// } else {

							// Ph_UPS_Woo_Shipping_Common::debug( $invoker . " - HazMat Product can not be shipped using UPS Ground with Freight Pricing.", $this->debug);

							// Ph_UPS_Woo_Shipping_Common::phAddDebugLog( $invoker . " - HazMat Product can not be shipped using UPS Ground with Freight Pricing.", $this->debug);
							// }
						// }

						$surepostPackageCount = ! empty( $newPackageRequest ) && is_array( $newPackageRequest ) ? count( $newPackageRequest ) : 1;
						$originCountryState   = isset( $this->settings['origin_country_state'] ) ? $this->settings['origin_country_state'] : '';
						$originCountryState   = current( explode( ':', $originCountryState ) );
						$originCountry        = isset( $package['origin']['country'] ) ? $package['origin']['country'] : $originCountryState;

						// UPS Simple Rate
						$currentPackage  = current( $newPackageRequest );
						$packageWeight   = $currentPackage['Package']['PackageWeight']['Weight'];
						$isSimpleRateBox = ( isset( $currentPackage['Package']['BoxCode'] ) && array_key_exists( $currentPackage['Package']['BoxCode'], $this->settings['simpleRateBoxes'] ) ) ? true : false;

						if ( $surepostPackageCount == 1 && $single_package && ( $originCountry == 'US' ) ) {

							// UPS Simplerate
							if ( $this->settings['upsSimpleRate'] && $isSimpleRateBox && $packageWeight <= 50 ) {

								$rate_requests             = $ups_rest->get_rate_requests( $newPackageRequest, $package, 'simple_rate', '', $this->international_delivery_confirmation_applicable );
								$rates[ $key ]['simple'][] = $ups_rest->process_result( $ups_rest->get_result( $rate_requests, 'simple rate', $key, '', false, array() ), '', $this->current_package_items_and_quantity, $this->vendor_id, $rate_requests );

								foreach ( $rates[ $key ]['simple'][0] as $rates_key => $value ) {

									if ( isset( $rates[ $key ]['general'][0][ $rates_key ] ) ) {
										unset( $rates[ $key ]['general'][0][ $rates_key ] );
									}
								}
							} elseif ( $this->settings['upsSimpleRate'] ) {

									Ph_UPS_Woo_Shipping_Common::debug( $invoker . ' - Simple Rate Request Aborted.<br/>UPS Simple Rate is a single package service. Please make sure:<br/><ul><li>Simple Rate Box(es) are enabled in the plugin Packaging settings</li><li>Total order weight should not exceed 50 lbs or 22.6 kg (as supported by UPS)</li></ul>', $this->debug );

									Ph_UPS_Woo_Shipping_Common::phAddDebugLog( $invoker . ' - Simple Rate Request Aborted. UPS Simple Rate is a single package service. Please make sure:Simple Rate Box(es) are enabled in the plugin Packaging settings, Total order weight should not exceed 50 lbs or 22.6 kg (as supported by UPS)', $this->debug );
							}

							// Surepost, 1 is Commercial Address
							$surepost_check = 0;

							if ( $this->settings['address_validation'] ) {

								if ( ! class_exists( 'Ph_Ups_Address_Validation_Rest' ) ) {
									require_once PH_WC_UPS_PLUGIN_DIR_PATH . 'includes/ups_rest/class-ph-ups-rest-address-validation.php';
								}

								if ( isset( $Ph_Ups_Address_Validation_Rest ) && ( $Ph_Ups_Address_Validation_Rest instanceof Ph_Ups_Address_Validation_Rest ) ) {

									$surepost_check = $Ph_Ups_Address_Validation_Rest->residential_check;

								} elseif ( class_exists( 'Ph_Ups_Address_Validation_Rest' ) && in_array( $package['destination']['country'], array( 'US', 'PR' ) ) ) {

									// Will check the address is Residential or not, SurePost only for residential
									$Ph_Ups_Address_Validation_Rest = new Ph_Ups_Address_Validation_Rest( $package['destination'], $this->settings );
									$surepost_check                 = $Ph_Ups_Address_Validation_Rest->residential_check;

								}
							}

							$surepost_check = apply_filters( 'ph_ups_zone_update_surepost_address_validation_result', $surepost_check, $package['destination'], $package );

							if ( $surepost_check != 1 ) {

								foreach ( PH_WC_UPS_Constants::UPS_SUREPOST_SERVICES as $service_code ) {

									if ( empty( $this->settings['services'][ $service_code ]['enabled'] ) || ( $this->settings['services'][ $service_code ]['enabled'] != 1 ) ) {
										// It will be not set for European origin address
										continue;
									}

									$rate_requests = $ups_rest->get_rate_requests( $newPackageRequest, $package, 'surepost', $service_code, $this->international_delivery_confirmation_applicable );
									$rate_response = $ups_rest->process_result( $ups_rest->get_result( $rate_requests, 'surepost', $key, '', false, array() ), '', $this->current_package_items_and_quantity, $this->vendor_id, $rate_requests );

									if ( ! empty( $rate_response ) ) {

										$rates[ $key ][ $service_code ][] = $rate_response;
									}
								}
							} else {
								Ph_UPS_Woo_Shipping_Common::debug( $invoker . ' - SurePost Rate Request Aborted. Entered Address is Commercial.', $this->debug );

								Ph_UPS_Woo_Shipping_Common::phAddDebugLog( $invoker . ' - SurePost Rate Request Aborted. Entered Address is Commercial', $this->debug );
							}
						} else {

							$single_package = false;

							Ph_UPS_Woo_Shipping_Common::debug( $invoker . " - SurePost/Simple Rate Request Aborted. Only single-piece shipments is allowed. Request contains .$surepostPackageCount Packages.", $this->debug );

							Ph_UPS_Woo_Shipping_Common::phAddDebugLog( $invoker . " - SurePost/Simple Rate Request Aborted. Only single-piece shipments is allowed. Request contains $surepostPackageCount Packages.", $this->debug );
						}

						// Saturday Delivery Request
						if ( isset( $this->settings['saturday_delivery'] ) && 'yes' === $this->settings['saturday_delivery'] ) {

							$rate_requests               = $ups_rest->get_rate_requests( $newPackageRequest, $package, 'saturday', '', $this->international_delivery_confirmation_applicable );
							$rates[ $key ]['saturday'][] = $ups_rest->process_result( $ups_rest->get_result( $rate_requests, 'Saturday Delivery', $key, '', false, array() ), '', $this->current_package_items_and_quantity, $this->vendor_id, $rate_requests );

							// Similar Services are Unsetting
							foreach ( $rates[ $key ]['saturday'][0] as $rates_key => $value ) {

								if ( isset( $rates[ $key ]['general'][0][ $rates_key ] ) ) {

									unset( $rates[ $key ]['general'][0][ $rates_key ] );
								}
							}
						}
					}

					// Handle Rates for Internal Packages, Add Rates from all the Packages and build Final Rate Array
					// If any of the Internal Package missing any Shipping Rate, unset the Shipping Rate from the Final Rate Array
					// Usecase: More than 50 Packages generated (Packing Algorithm) from Cart Packages - Pack Item Indivisually with 120 Quantity of product.

					if ( ! empty( $rates ) ) {

						foreach ( $rates as $key => $value ) {

							// $rate_type will be general, freight ( 308, 309, 334, 349 ), US48, 96, SurePost

							foreach ( $value as $rate_type => $all_packages_rates ) {

								// Build Final Rate Array for each Cart Packages

								if ( ! isset( $package_rates[ $rate_type ] ) ) {

									$package_rates[ $rate_type ] = array();

									// Add $packageKey in Final Rate Array to check rates are returned for all Cart Packages

									$package_rates[ $rate_type ][ $packageKey ] = array();
								}

								// Build Internal Package Rate Count for all the Services

								if ( ! isset( $allPackageRateCount[ $rate_type ] ) ) {

									$allPackageRateCount[ $rate_type ] = array();

									// Add $packageKey in Internal Package Rate Count Array
									// To check rates are returned for all the Internal Packages within a Cart Package

									$allPackageRateCount[ $rate_type ][ $packageKey ] = array();
								}

								$calculatedRates = current( $all_packages_rates );

								if ( is_array( $calculatedRates ) || is_object( $calculatedRates ) ) {

									foreach ( $calculatedRates as $ups_sevice => $package_rate ) {

										// Keep the Count of each UPS Shipping Service returned for all the Internal Packages

										if ( ! isset( $allPackageRateCount[ $rate_type ][ $packageKey ][ $ups_sevice ] ) ) {

											$allPackageRateCount[ $rate_type ][ $packageKey ][ $ups_sevice ] = 1;
										}

										// If: Push the Shipping Rate array for the initial Internal Package to the Final Rate Array
										// Else: Add the Shipping Rate Cost to the Final Rate Array for each additional Internal Package
										// Increacse the Internal Package Rate Count as well

										if ( ! isset( $package_rates[ $rate_type ][ $packageKey ][ $ups_sevice ] ) ) {

											$package_rates[ $rate_type ][ $packageKey ][ $ups_sevice ] = array();
											$package_rates[ $rate_type ][ $packageKey ][ $ups_sevice ] = $package_rate;
										} else {

											$package_rates[ $rate_type ][ $packageKey ][ $ups_sevice ]['cost'] = (float) $package_rates[ $rate_type ][ $packageKey ][ $ups_sevice ]['cost'] + (float) $package_rate['cost'];
											++$allPackageRateCount[ $rate_type ][ $packageKey ][ $ups_sevice ];
										}
									}
								}
							}
						}

						// If all the Internal Package Rates were not returned then Unset that Shipping Rate
						// This unsetting is only for Internal Package Rates of respectve Cart Packages

						if ( ! empty( $allPackageRateCount ) ) {

							foreach ( $allPackageRateCount as $rateType => $rateCount ) {

								foreach ( $rateCount[ $packageKey ] as $rateId => $count ) {

									if ( isset( $package_rates[ $rateType ] ) && isset( $package_rates[ $rateType ][ $packageKey ] ) && isset( $package_rates[ $rateType ][ $packageKey ][ $rateId ] ) && $internal_package_count != $count ) {

										$serviceName = $package_rates[ $rateType ][ $packageKey ][ $rateId ]['label'];

										Ph_UPS_Woo_Shipping_Common::phAddDebugLog( $invoker . " - $serviceName is removed from Shipping Rates. Total $internal_package_count Package Set(s) were requested for Rates. Rates returned only for $count Package Set(s). One Package Set contains maximum of 50 Packages." );

										unset( $package_rates[ $rateType ][ $packageKey ][ $rateId ] );
									}
								}
							}
						}
					}
				}
			}

			$rates     = $package_rates;
			$all_rates = array();

			// Handle Rates for Multi Cart Packages, Check all cart packages returned rates.
			// Filter Common Shipping Methods and conbine the Shipping Cost and Display
			// Usecase: Multi Vendor with Split and Sum Method

			if ( ! empty( $rates ) ) {

				foreach ( $rates as $rate_type => $all_packages_rates ) {

					// For every woocommerce package there must be response, so number of woocommerce package and UPS response must be equal

					if ( count( $rates[ $rate_type ] ) == $wc_total_packages_count ) {

						// UPS services keys in rate response

						$ups_found_services_keys = array_keys( current( $all_packages_rates ) );

						foreach ( $ups_found_services_keys as $ups_sevice ) {

							$count = 0;

							foreach ( $all_packages_rates as $package_rates ) {

								if ( ! empty( $package_rates[ $ups_sevice ] ) ) {

									if ( empty( $all_rates[ $ups_sevice ] ) ) {

										$all_rates[ $ups_sevice ] = $package_rates[ $ups_sevice ];
									} else {

										$all_rates[ $ups_sevice ]['cost'] = (float) $all_rates[ $ups_sevice ]['cost'] + (float) $package_rates[ $ups_sevice ]['cost'];
									}

									++$count;
								}
							}

							// If number of package requests not equal to number of response for any particular service

							if ( $count != $wc_total_packages_count ) {
								unset( $all_rates[ $ups_sevice ] );
							}
						}
					}
				}
			}

			$rates_arr = array(
				'all_rates' 							=> $all_rates,
				'current_package_items_and_quantity'	=> $this->current_package_items_and_quantity,
				'vendor_id'								=> $this->vendor_id
			);

			return $rates_arr;
		}

	}
}