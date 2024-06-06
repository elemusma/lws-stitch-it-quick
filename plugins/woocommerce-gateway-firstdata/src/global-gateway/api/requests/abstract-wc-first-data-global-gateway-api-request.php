<?php
/**
 * WooCommerce First Data
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@kestrelwp.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce First Data to newer
 * versions in the future. If you wish to customize WooCommerce First Data for your
 * needs please refer to http://docs.woocommerce.com/document/firstdata/
 *
 * @author      Kestrel
 * @copyright   Copyright (c) 2013-2024, Kestrel
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\PluginFramework\v5_12_4 as Framework;

/**
 * Global Gateway API Request Base Class
 *
 * Handles common functionality for request classes
 *
 * @since 4.0.0
 */
abstract class WC_First_Data_Global_Gateway_API_Request extends Framework\SV_WC_API_XML_Request implements Framework\SV_WC_Payment_Gateway_API_Request {


	/** XML document root element name */
	const ROOT_XML_ELEMENT = 'order';

	/** @var \WC_Order associated with this request */
	protected $order;

	/** @var string store number */
	protected $store_number;

	/** @var array request data */
	protected $request_data;


	/**
	 * Setup class
	 *
	 * @since 4.0.0
	 * @param \WC_Order $order order object
	 * @param string $store_number registered store number
	 */
	public function __construct( \WC_Order $order, $store_number ) {

		$this->order        = $order;
		$this->store_number = $store_number;
	}


	/**
	 * Gets the request query params.
	 *
	 * @since 4.4.0
	 *
	 * @return array
	 */
	public function get_params() {

		return $this->params;
	}


	/**
	 * Return the request data, filtered and stripped of empty/null values
	 *
	 * @since 4.4.0
	 *
	 * @return array
	 */
	public function get_data() {

		$this->request_data = parent::get_data();

		// set required credentials
		$this->request_data['merchantinfo'] = [
			'configfile' => $this->store_number,
		];

		/**
		 * Global Gateway Request Data Filter.
		 *
		 * Allow actors to modify the request data before it's sent to Global Gateway.
		 *
		 * @since 4.4.0
		 * @param array $request_data request data
		 * @param \WC_First_Data_Global_Gateway_API_Request request instance
		 */
		$this->request_data = apply_filters( 'wc_first_data_global_gateway_request_data', $this->request_data, $this );

		// remove empty (null or blank string) data from request data
		$this->remove_empty_data();

		return [ self::ROOT_XML_ELEMENT => $this->request_data ];
	}


	/**
	 * Remove null or blank string values from the request data (up to 2 levels deep)
	 *
	 * @since 4.0.0
	 */
	protected function remove_empty_data() {

		foreach ( (array) $this->request_data as $key => $value ) {

			if ( is_array( $value ) ) {

				// remove empty arrays
				if ( empty( $value ) ) {

					unset( $this->request_data[ $key ] );

				} else {

					foreach ( $value as $inner_key => $inner_value ) {

						if ( is_null( $inner_value ) || '' === $inner_value ) {
							unset( $this->request_data[ $key ][ $inner_key ] );
						}
					}
				}

			} else {

				if ( is_null( $value ) || '' === $value ) {
					unset( $this->request_data[ $key ] );
				}
			}
		}
	}


	/**
	 * Returns the string representation of this request with any and all
	 * sensitive elements masked or removed
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_API_Request::to_string_safe()
	 * @return string the request, safe for logging/displaying
	 */
	public function to_string_safe() {

		$string = $this->to_string();

		// store number
		if ( preg_match( '/<configfile>(\w+)<\/configfile>/', $string, $matches ) ) {
			$string = preg_replace( '/<configfile>(\w+)<\/configfile>/', '<configfile>' . str_repeat( '*', strlen( $matches[1] ) ) . '</configfile>', $string );
		}

		// card number
		if ( preg_match( '/<cardnumber>(\d+)<\/cardnumber>/', $string, $matches ) ) {
			$string = preg_replace( '/<cardnumber>\d+<\/cardnumber>/', '<cardnumber>' . substr( $matches[1], 0, 1 ) . str_repeat( '*', strlen( $matches[1] ) - 5 ) . substr( $matches[1], - 4 ) . '</cardnumber>', $string );
		}

		// real CSC code
		$string = preg_replace( '/<cvmvalue>\d+<\/cvmvalue>/', '<cvmvalue>***</cvmvalue>', $string );

		return $this->prettify_xml( $string );
	}


	/**
	 * Return the root element for the XML document
	 *
	 * @since 4.0.0
	 * @return string
	 */
	protected function get_root_element() {

		return self::ROOT_XML_ELEMENT;
	}


	/**
	 * Return the order associated with this request
	 *
	 * @since 4.0.0
	 * @return \WC_Order
	 */
	public function get_order() {

		return $this->order;
	}


}
