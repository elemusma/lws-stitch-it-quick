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

namespace Kestrel\WooCommerce\First_Data\Payeezy\API\Request;

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\PluginFramework\v5_12_4 as Framework;

/**
 * Payeezy Payment.JS API Request Base Class.
 *
 * Handles common functionality for request classes.
 *
 * @since 4.7.0
 */
abstract class PaymentJS extends Framework\SV_WC_API_JSON_Request implements Framework\SV_WC_Payment_Gateway_API_Request {


	/** @var array request data */
	protected $request_data;


	/**
	 * Sets up the request.
	 *
	 * @since 4.7.0
	 */
	public function __construct() {

		// request defaults
		$this->method = 'POST';
		$this->path   = '/';
	}


	/**
	 * Gets the Payeezy gateway.
	 *
	 * @since 4.7.0
	 *
	 * @return \WC_Gateway_First_Data_Payeezy
	 */
	protected function get_gateway() {

		return wc_first_data()->get_gateway();
	}


	/**
	 * Gets the request data.
	 *
	 * @since 4.7.0
	 *
	 * @return array
	 */
	public function get_data() {

		return $this->request_data;
	}


	/**
	 * Gets the string representation of the request.
	 *
	 * Omits all sensitive elements, which are masked or removed.
	 *
	 * @since 4.7.0
	 *
	 * @return string
	 */
	public function to_string_safe() {

		$data = $this->to_string();

		$sensitive_data = [
			$this->get_gateway()->get_api_key(),
			$this->get_gateway()->get_api_secret(),
			$this->get_gateway()->get_merchant_token(),
			$this->get_gateway()->get_transarmor_token(),
		];

		foreach ( $sensitive_data as $value ) {
			$data = str_replace( $value, str_repeat( '*', strlen( $value ) ), $data );
		}

		return $data;
	}


}
