<?php
/**
 * Common utils for WooCommerce UPS Shipping Plugin with Print Label.
 *
 * @package ups-woocommerce-shipping
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'PH_WC_UPS_Common_Utils' ) ) {
	/**
	 * Common Utils Class.
	 */
	class PH_WC_UPS_Common_Utils {

		/**
		 * Updates the delivery time in the shipping method label in checkout.
		 *
		 * @param string $label The current shipping method label.
		 * @param object $method The shipping method object.
		 * @param string $wp_date_time_format The WordPress date-time format.
		 * @param string $estimated_delivery_text The text for estimated delivery.
		 * @return string The modified shipping method label with the estimated delivery time appended.
		 */
		public static function ph_update_delivery_time( $label, $method, $wp_date_time_format, $estimated_delivery_text ) {
			global $wp_version;

			// Older versoin of WC is not supporting get_meta_data() on method.
			if ( ! is_object( $method ) || ! method_exists( $method, 'get_meta_data' ) ) {
				return $label;
			}

			// Set default format if not provided
  			$wp_date_time_format = $wp_date_time_format ?: Ph_UPS_Woo_Shipping_Common::get_wordpress_date_format() . ' ' . Ph_UPS_Woo_Shipping_Common::get_wordpress_time_format();

			$shipping_rate_meta_data_arr = $method->get_meta_data();
			$est_delivery_text           = ! empty( $estimated_delivery_text ) ? $estimated_delivery_text : __( 'Est delivery', 'ups-woocommerce-shipping' );
			
			if ( ! empty( $shipping_rate_meta_data_arr['ups_delivery_time'] ) && strpos( $label, $est_delivery_text ) === false ) {

				$est_delivery   = $shipping_rate_meta_data_arr['ups_delivery_time'];
				$formatted_date = date_format( $est_delivery, $wp_date_time_format );

				if ( version_compare( $wp_version, '5.3', '>=' ) ) {

					$zone = date_default_timezone_get() ? new DateTimeZone( date_default_timezone_get() ) : $zone = new DateTimeZone( 'UTC' );

					if ( strtotime( $formatted_date ) ) {
						$formatted_date = wp_date( $wp_date_time_format, strtotime( $formatted_date ), $zone );
					}
				} elseif ( strtotime( $formatted_date ) ) {
						$formatted_date = date_i18n( $wp_date_time_format, strtotime( $formatted_date ) );
				}

				$est_delivery_text = $estimated_delivery_text ?: __( 'Est delivery:', 'ups-woocommerce-shipping' );
				$est_delivery_html = '<br /><small>' . $est_delivery_text . ' ' . $formatted_date . '</small>';

				$est_delivery_html = apply_filters( 'wf_ups_estimated_delivery', $est_delivery_html, $est_delivery, $method );
				// Avoid multiple
				if ( strstr( $label, $formatted_date ) === false ) {
					$label .= $est_delivery_html;
				}
			}
			return $label;
		}

		/**
		 * Get options array with translated values.
		 *
		 * @param array $options_array The options array to be translated.
		 * @return array The translated options array.
		 */
		public static function ph_get_translated_options( array $options_array ) {
			$options = array();

			foreach ( $options_array as $key => $value ) {
				$options[ $key ] = __( $value, 'ups-woocommerce-shipping' ); //phpcs:ignore
			}

			return $options;
		}

		/**
		 * Unset specified settings fields from a settings tab array if they are set.
		 *
		 * @param array $settings_tab An associative array representing the settings tab.
		 * @param array $settings_field_arr An array of settings field keys to unset.
		 * @return array The modified settings tab array with specified fields unset.
		 */
		public static function unset_settings_fields( $settings_tab, $settings_field_arr ) {

			// Check if $settings_field_arr is an array.
			if( !is_array( $settings_field_arr )) return;

			// Loop through each key in $settings_field_arr
			foreach ( $settings_field_arr as $settings_field ) {
				// Check if the key exists in $settings_tab and unset it if it does
				if ( isset( $settings_tab[ $settings_field ] ) ) {
					unset( $settings_tab[ $settings_field ] );
				}
			}

			return $settings_tab;
		}

		/**
		 * Convert box dimensions and weight based on plugin settings
		 *
		 * @param array $boxes
		 * @return array $boxes
		 */
		public static function ph_convert_box_measurement_units( $boxes, $units ) {
			$dimToUnit      = '';
			$dimFromUnit    = '';
			$weightToUnit   = '';
			$weightFromUnit = '';

			if ( $units == 'imperial' ) {

				$dimToUnit      = 'cm';
				$dimFromUnit    = 'in';
				$weightToUnit   = 'kg';
				$weightFromUnit = 'lbs';
			} else {
				$dimToUnit      = 'in';
				$dimFromUnit    = 'cm';
				$weightToUnit   = 'lbs';
				$weightFromUnit = 'kg';
			}

			foreach ( $boxes as $key => $box ) {

				$boxes[ $key ]['outer_length'] = round( wc_get_dimension( $box['outer_length'], $dimToUnit, $dimFromUnit ), 2 );
				$boxes[ $key ]['outer_width']  = round( wc_get_dimension( $box['outer_width'], $dimToUnit, $dimFromUnit ), 2 );
				$boxes[ $key ]['outer_height'] = round( wc_get_dimension( $box['outer_height'], $dimToUnit, $dimFromUnit ), 2 );
				$boxes[ $key ]['inner_length'] = round( wc_get_dimension( $box['inner_length'], $dimToUnit, $dimFromUnit ), 2 );
				$boxes[ $key ]['inner_width']  = round( wc_get_dimension( $box['inner_width'], $dimToUnit, $dimFromUnit ), 2 );
				$boxes[ $key ]['inner_height'] = round( wc_get_dimension( $box['inner_height'], $dimToUnit, $dimFromUnit ), 2 );
				$boxes[ $key ]['box_weight']   = round( wc_get_weight( $box['box_weight'], $weightToUnit, $weightFromUnit ), 2 );
				$boxes[ $key ]['max_weight']   = round( wc_get_weight( $box['max_weight'], $weightToUnit, $weightFromUnit ), 2 );
			}

			return $boxes;
		}

		/**
		 * Skips products from the shipping package based on shipping class.
		 *
		 * @param array  $package         The shipping package containing line items.
		 * @param array  $skip_products   An array of shipping class names to skip.
		 * @param string $invoker         An identifier for the caller (e.g., shipping method name).
		 * @param bool   $debug           Whether to enable debug mode.
		 * @param bool   $silent_debug    Whether to suppress debug output.
		 * @return array                  The modified shipping package after skipping products.
		 */
		public static function skip_products( $package, $skip_products, $invoker = '', $debug = false, $silent_debug = false ) {
			$skipped_products = null;

			foreach ( $package['contents'] as $line_item_key => $line_item ) {

				$line_item_shipping_class = $line_item['data']->get_shipping_class();

				if ( in_array( $line_item_shipping_class, $skip_products ) ) {

					if( 'Label Generation' === $invoker) {
						$skipped_products[] = $line_item['data']->get_id();
					} else {
						$skipped_products[] = ! empty( $line_item['variation_id'] ) ? $line_item['variation_id'] : $line_item['product_id'];
					}
					unset( $package['contents'][ $line_item_key ] );
				}
			}
			if ( ! empty( $skipped_products ) ) {
				$skipped_products = implode( ', ', $skipped_products );

				if ( 'Label Generation' === $invoker && $debug ) {
					if ( class_exists( 'WC_Admin_Notices' ) ) {
						WC_Admin_Notices::add_custom_notice( 'ups_skipped_products', __( 'UPS : Skipped Products Id - ', 'ups-woocommerce-shipping' ) . $skipped_products . ' .' );
					}
				} else {
					Ph_UPS_Woo_Shipping_Common::debug( __( 'UPS ' . $invoker . ' : Skipped Products Id - ', 'ups-woocommerce-shipping' ) . $skipped_products . ' .', $debug, $silent_debug );
				}
			}

			if ( ! empty( $skipped_products ) ) {

				Ph_UPS_Woo_Shipping_Common::phAddDebugLog( 'UPS ' . $invoker . ' : Skipped Product Id(s)', $debug );
				Ph_UPS_Woo_Shipping_Common::phAddDebugLog( print_r( $skipped_products, 1 ), $debug );
			}

			return $package;
		}

		/**
		 * Retrieves the origin country or state based on settings.
		 *
		 * @param array  $settings          The settings array containing 'origin_country_state'.
		 * @param string $state_or_country  The type of information to retrieve ('country' or 'state').
		 * @return string                   The origin country or state.
		 */
		public static function ph_get_origin_country_and_state( $settings, $state_or_country ) {
			$origin_country_state = isset( $settings['origin_country_state'] ) ? $settings['origin_country_state'] : '';

			if (strstr($origin_country_state, ':') !== false) {

				list($origin_country, $origin_state) = explode(':', $origin_country_state);
			} else {

				$origin_country = $origin_country_state;
				$origin_state = '';
			}

			return 'country' === $state_or_country ? $origin_country : $origin_state;
		}

		/**
		 * Checks if the package weight is within the specified minimum and maximum weight limits.
		 *
		 * @param array  $package           The shipping package containing line items.
		 * @param float  $min_weight_limit  The minimum weight limit for the package (optional).
		 * @param float  $max_weight_limit  The maximum weight limit for the package (optional).
		 * @param string $invoker           An identifier for the caller (e.g., shipping method name).
		 * @param bool   $debug             Whether to enable debug mode.
		 * @param bool   $silent_debug      Whether to suppress debug output.
		 * @return bool                      True if the package weight is within the limits, false otherwise.
		 */
		public static function check_min_weight_and_max_weight( $package, $min_weight_limit = null, $max_weight_limit = null, $invoker = '', $debug = false, $silent_debug = false ) {
			$package_weight = 0;
			foreach ( $package['contents'] as $line_item ) {

				$quantity        = isset( $line_item['quantity'] ) ? $line_item['quantity'] : 1;
				$package_weight += (float) ( ( ! empty( $line_item['data']->get_weight() ) ? $line_item['data']->get_weight() : 0 ) * $quantity );
			}
			if ( $package_weight < $min_weight_limit || ( ! empty( $max_weight_limit ) && $package_weight > $max_weight_limit ) ) {

				if ( 'Label Generation' === $invoker ) {

					if ( class_exists( 'WC_Admin_Notices' ) ) {
						WC_Admin_Notices::add_custom_notice( 'ups_package_weight_not_in_range', __( 'UPS Package Generation stopped. - Package Weight is not in range of Minimum and Maximum Weight Limit (Check UPS Plugin Settings).', 'ups-woocommerce-shipping' ) );
					}

					Ph_UPS_Woo_Shipping_Common::phAddDebugLog( 'UPS Package Generation stopped. - Package Weight is not in range of Minimum and Maximum Weight Limit (Check UPS Plugin Settings).', $debug );

				} else {

					Ph_UPS_Woo_Shipping_Common::debug( __( 'UPS Shipping' . $invoker . ' Calculation Skipped - Package Weight is not in range of Minimum and Maximum Weight Limit (Check UPS Plugin Settings).', 'ups-woocommerce-shipping' ), $debug, $silent_debug );
					// Add by default
					Ph_UPS_Woo_Shipping_Common::phAddDebugLog( 'UPS Shipping' . $invoker . 'Calculation Skipped - Package Weight is not in range of Minimum and Maximum Weight Limit (Check UPS Plugin Settings)', $debug );
				}

				return false;
			}
			return true;
		}

		/**
		 * Calculate the insurance amount for a product.
		 *
		 * @param WC_Product|int $product            The product object or ID.
		 * @param float          $fixed_product_price The fixed product price.
		 * @return float                              The insurance amount.
		 */
		public static function ph_get_insurance_amount( $product, $fixed_product_price ) {

			$product   	= wc_get_product( $product->get_id() );
			$parent_id 	= is_object( $product ) ? $product->get_parent_id() : 0;
			$product_id = $product->get_id();

			if ( $product->is_type( 'variation' ) ) {

				$insured_price = get_post_meta( $product_id, '_ph_ups_custom_declared_value_var', true );
				$meta_exists   = metadata_exists( 'post', $product_id, '_ph_ups_custom_declared_value_var' );

				if ( empty( $insured_price ) ) {
					$insured_price = get_post_meta( $parent_id, '_wf_ups_custom_declared_value', true );
					$meta_exists   = metadata_exists( 'post', $parent_id, '_wf_ups_custom_declared_value' );
				}
			} else {

				$insured_price = get_post_meta( $product_id, '_wf_ups_custom_declared_value', true );
				$meta_exists   = metadata_exists( 'post', $product_id, '_wf_ups_custom_declared_value' );
			}

			return (float) ($meta_exists && is_numeric($insured_price) ? $insured_price : (!empty($product->get_price()) ? $product->get_price() : $fixed_product_price));
		}

		/**
		 * Retrieve access point data based on the provided details or order.
		 *
		 * @param mixed $ph_ups_selected_access_point_details The selected access point details.
		 * @param mixed $order_details Optional. The order details to retrieve the access point data from. Defaults to an empty string.
		 * @return mixed|string|null The access point data if found, otherwise null.
		 */
		public static function wf_get_accesspoint_datas( $ph_ups_selected_access_point_details, $order_details = '' ) {
			// For getting the rates in backend
			if ( is_admin() && isset( $_GET['wf_ups_generate_packages_rates'] ) ) {
				$order_id      = base64_decode( $_GET['wf_ups_generate_packages_rates'] );
				$order_details = new WC_Order( $order_id );
			} else {
				return;
			}

			if ( empty( $order_details ) ) {
				return $ph_ups_selected_access_point_details;
			}
				
			$address_field = $order_details->get_meta( '_shipping_accesspoint' );
			return stripslashes( $address_field );
		}

				/**
				 * Retrieves UPS services based on the origin country.
				 *
				 * @param string $origin_country The origin country code for which to retrieve services.
				 * @return array                 An array of UPS services available for the specified origin country.
				 */
		public static function get_services_based_on_origin( $origin_country ) {

			// Show services based on origin country
			$mappedCountry        = array_key_exists( $origin_country, PH_WC_UPS_Constants::UPS_COUNTRY_SERVICE_MAPPER ) ? PH_WC_UPS_Constants::UPS_COUNTRY_SERVICE_MAPPER[ $origin_country ] : '';
			$services             = array_key_exists( $mappedCountry, PH_WC_UPS_Constants::UPS_SERVICE_CODES ) ? PH_WC_UPS_Constants::UPS_SERVICE_CODES[ $mappedCountry ] : PH_WC_UPS_Constants::UPS_SERVICE_CODES['US'];

			return $services;
		}

		public static function get_service_code_for_country( $service, $country ) {
			$service_for_country = array(
				'CA' => array(
					'07' => '01', // for Canada serivce code of 'UPS Express(07)' is '01'
					'65' => '13', // Saver
				),
			);
			if ( array_key_exists( $country, $service_for_country ) ) {
				return isset( $service_for_country[ $country ][ $service ] ) ? $service_for_country[ $country ][ $service ] : $service;
			}
			return $service;
		}

		public static function wf_get_postcode_city( $country, $city, $postcode ) {
			$request_part = '';
			if ( in_array( $country, PH_WC_UPS_Constants::NO_POSTCODE_COUNTRY_ARRAY ) && ! empty( $city ) ) {
				$request_part = '<City>' . $city . '</City>' . "\n";
			} elseif ( empty( $city ) ) {
				$request_part = '<PostalCode>' . $postcode . '</PostalCode>' . "\n";
			} else {
				$request_part  = ' <City>' . $city . '</City>' . "\n";
				$request_part .= '<PostalCode>' . $postcode . '</PostalCode>' . "\n";
			}

			return $request_part;
		}

		public static function ph_get_postcode_city_in_array( $country, $city, $postcode ) {
			$request_part = array();
			if ( in_array( $country, PH_WC_UPS_Constants::NO_POSTCODE_COUNTRY_ARRAY ) && ! empty( $city ) ) {
				$request_part['City'] = $city;
			} elseif ( empty( $city ) ) {
				$request_part['PostalCode'] = $postcode;
			} else {
				$request_part['City']       = $city;
				$request_part['PostalCode'] = $postcode;
			}

			return $request_part;
		}

		/**
		 * Get UPS package weight converted for rate request for service 92
		 *
		 * @param $package_request array UPS package request array
		 * @return $service_code array UPS Package request
		 */
		public static function convert_weight( $package_request, $weight_unit, $service_code = null ) {
			if ( $service_code = 92 ) { // Surepost Less Than 1 LBS
				if ( $weight_unit == 'LBS' ) { // make sure weight in pounds
					$weight_ozs = (float) $package_request['Package']['PackageWeight']['Weight'] * 16;
				} else {
					$weight_ozs = (float) $package_request['Package']['PackageWeight']['Weight'] * 35.274; // From KG
				}

				$package_request['Package']['PackageWeight'] = array(
					'UnitOfMeasurement' => array(
						'Code' => 'OZS',
					),
					'Weight'            => (string) round( $weight_ozs, 2 ),
				);
			}
			return $package_request;
		}

		/**
		 * Creates a deep copy of an array.
		 *
		 * @param array $source The source array to copy.
		 * @return array A new array that is a deep copy of the source array.
		 */
		public static function copyArray( $source ) {
			$result = array();

			foreach ( $source as $key => $item ) {
				$result[ $key ] = ( is_array( $item ) ? self::copyArray( $item ) : $item );
			}

			return $result;
		}

		/**
		 * Get the highest delivery confirmation signature option among the given products.
		 *
		 * @param array $products An array of product objects.
		 * @return int The highest delivery confirmation signature option.
		 */
		public static function get_package_signature( $products ) {
			$higher_signature_option = 0;
			foreach ( $products as $product ) {

				$product = wc_get_product( $product->get_id() );

				if ( empty( $product ) && !( $product instanceof WC_Product ) ) {
					continue;
				}

				$parent_id  = is_object( $product ) ? $product->get_parent_id() : 0;
				$product_id = ! empty( $parent_id ) ? $parent_id : $product->get_id();

				$wf_dcis_type = get_post_meta( $product_id, '_wf_ups_deliveryconfirmation', true );
				if ( empty( $wf_dcis_type ) || ! is_numeric( $wf_dcis_type ) ) {
					$wf_dcis_type = 0;
				}

				if ( $wf_dcis_type > $higher_signature_option ) {
					$higher_signature_option = $wf_dcis_type;
				}
				
			}
			return $higher_signature_option;
		}

		/**
		 * Check whether Shipment Level COD is required or not.
		 *
		 * @param string $country_code
		 * @return bool True if Shipment Level COD is required else false.
		 */
		public static function is_shipment_level_cod_required( $country_code ) {
			if ( ! $country_code ) {
				return false;
			}

			return in_array( $country_code, PH_WC_UPS_Constants::COUNTRIES );
		}

		/**
		 * Get product meta data for single occurance in request
		 *
		 * @param array|object $products array of wf_product object
		 * @param string       $option
		 * @return mixed Return option value
		 */
		public static function get_individual_product_meta( $products, $option = '' ) {
			$meta_result = '';
			foreach ( $products as $product ) {
				if ( empty( $meta_result ) ) {
					$meta_result = ! empty( $product->obj ) ? $product->obj->get_meta( $option ) : '';   // $product->obj actual product
				}
			}

			return $meta_result;
		}
	}
}
