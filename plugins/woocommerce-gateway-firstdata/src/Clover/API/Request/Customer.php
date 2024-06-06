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
 * Customer request class.
 *
 * @link https://docs.clover.com/reference/createcustomer
 * @link https://docs.clover.com/reference/updatecustomer
 * @link https://docs.clover.com/reference/revokecard
 * @since 2.0.0
 */
class Customer extends Request {


	/**
	 * Create a customer with associated saved card
	 *
	 * @since 5.0.0
	 *
	 * @param \WC_Order $order order object
	 */
	public function create_customer_with_saved_card( \WC_Order $order ) {

		$this->order = $order;

		$this->path = 'v1/customers';

		// available but not implemented:
		// shipping->carrier, shipping->tracking_number
		$this->data = [
			'ecomind'   => 'ecom',
			'email'     => $order->get_billing_email(),
			'firstName' => $order->get_billing_first_name(),
			'lastName'  => $order->get_billing_last_name(),
			'source'    => $order->payment->js_token,
			'phone'     => $order->get_billing_phone(),
			'shipping' => [
				'address' => [
					'city'        => $order->get_shipping_city(),
					'country'     => $order->get_shipping_country(),
					'line1'       => $order->get_shipping_address_1(),
					'line2'       => $order->get_shipping_address_2(),
					'postal_code' => $order->get_shipping_postcode(),
					'state'       => $order->get_shipping_state(),
				],
				'name'  => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
				'phone' => $order->get_shipping_phone(),
			],
		];
	}


	/**
	 * Update a customer with a new saved card
	 *
	 * @since 5.0.0
	 *
	 * @param \WC_Order $order order object
	 */
	public function update_customer_with_saved_card( \WC_Order $order ) {

		$this->order = $order;

		$this->path = "v1/customers/{$order->customer_id}";

		$this->method = 'PUT';

		// available but not implemented:
		// ecomind, firstName, lastName, name, phone, shipping (object)
		$this->data = [
			'source' => $order->payment->js_token,
			'email'  => $order->get_billing_email(),
		];
	}


	/**
	 * Remove a saved card from a customer
	 *
	 * TODO: a drawback of the Payment Gateway Framework is that the token is
	 * removed locally before it's deleted remotely, which means that if the
	 * remote request fails due to network timeout, or maintenance, or rate
	 * limiting, etc, we "successfully" delete the local token while the
	 * remote token remains in existence. {JS: 2022-11-21}
	 *
	 * @since 5.0.0
	 *
	 * @param string $card_id remote card ID
	 * @param string $customer_id unique customer ID
	 */
	public function revoke_card( $card_id, $customer_id ) {

		$this->path = "v1/customers/{$customer_id}/sources/{$card_id}";

		$this->method = 'DELETE';
	}


}
