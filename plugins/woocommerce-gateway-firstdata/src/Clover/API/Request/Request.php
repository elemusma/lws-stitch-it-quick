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

namespace Kestrel\WooCommerce\First_Data\Clover\API\Request;

use SkyVerge\WooCommerce\PluginFramework\v5_12_4 as Framework;


defined( 'ABSPATH' ) or exit;

/**
 * The Base request class.
 *
 * @since 5.0.0
 *
 * @link
 */
abstract class Request extends Framework\SV_WC_API_JSON_Request implements Framework\SV_WC_Payment_Gateway_API_Request {


	/** @var \WC_Order $order */
	protected $order;


	/**
	 * Gets the request data.
	 *
	 * @see Framework\SV_WC_API_JSON_Request::get_data()
	 * @since 5.0.0
	 *
	 * @return array
	 */
	public function get_data() : array {

		/**
		 * Filters the Clover API request data.
		 *
		 * @since 5.0.0
		 *
		 * @param array $data request data
		 * @param \WC_Order $order order object
		 */
		$this->data = (array) apply_filters( 'wc_first_data_clover_credit_card_api_request_data', $this->data, $this->order );

		return $this->data;
	}


	/**
	 * Returns the order number, used in the charge/refund endpoints, truncated to 12 chars
	 *
	 * @since 5.0.0
	 *
	 * @param \WC_Order $order
	 *
	 * @return string
	 */
	protected function get_order_number( \WC_Order $order ) : string {

		return preg_replace( '/[^a-zA-Z0-9]/', '', Framework\SV_WC_Helper::str_truncate( $order->get_order_number(), 12, '' ) );
	}


	/**
	 * Convert a string dollar amount into cents, avoiding floating point math nonsense
	 *
	 * TODO: should this be a framework helper method?
	 *
	 * @since 5.0.0
	 *
	 * @param string $amount
	 *
	 * @return int
	 */
	protected function to_cents( string $amount ) : int {

		return (int) (string) ( (float) preg_replace( "/[^0-9.]/", '', $amount ) * 100 );
	}


}
