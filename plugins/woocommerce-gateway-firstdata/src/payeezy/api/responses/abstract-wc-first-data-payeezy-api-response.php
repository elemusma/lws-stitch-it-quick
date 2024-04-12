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

use SkyVerge\WooCommerce\PluginFramework\v5_11_12 as Framework;

/**
 * Payeezy API Response Base Class.
 *
 * Handles common functionality for response classes.
 *
 * @since 4.0.0
 */
abstract class WC_First_Data_Payeezy_API_Response extends Framework\SV_WC_API_JSON_Response implements Framework\SV_WC_Payment_Gateway_API_Response {


	/**
	 * Setup the class
	 *
	 * @since 4.0.0
	 * @param \WC_First_Data_Payeezy_API_Credit_Card_Transaction_Request|\WC_First_Data_Payeezy_API_eCheck_Transaction_Request $request
	 * @param string $raw_json
	 */
	public function __construct( $request, $raw_json ) {

		parent::__construct( $raw_json );

		$this->request = $request;
	}


	/**
	 * Returns the string representation of this response with any and all
	 * sensitive elements masked or removed
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_API_Response::to_string_safe()
	 * @return string the response, safe for logging/displaying
	 */
	public function to_string_safe() {

		// mask the TeleCheck customer ID number
		$this->raw_response_json = preg_replace( '/"customer_id_number":"([-\w]*)"/', '"customer_id_number":"*"', $this->raw_response_json );

		return $this->raw_response_json;
	}


	/**
	 * Return the request that triggered this response
	 *
	 * @since 4.0.0
	 * @return \WC_First_Data_Payeezy_API_Credit_Card_Transaction_Request|\WC_First_Data_Payeezy_API_eCheck_Transaction_Request
	 */
	public function get_request() {

		return $this->request;
	}


}
