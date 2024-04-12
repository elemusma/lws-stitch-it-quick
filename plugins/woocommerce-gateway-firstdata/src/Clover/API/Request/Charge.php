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

namespace Atreus\WooCommerce\First_Data\Clover\API\Request;

use SkyVerge\WooCommerce\PluginFramework\v5_11_12 as Framework;

defined( 'ABSPATH' ) or exit;

/**
 * Charge request class.
 *
 * @link https://docs.clover.com/reference/createcharge
 * @since 2.0.0
 */
class Charge extends Request {


	/** @var string the request path */
	const REQUEST_PATH = 'v1/charges';

	/**
	 * Sets the credit card authorization transaction data
	 *
	 * @since 5.0.0
	 *
	 * @param \WC_Order $order order object
	 * @param bool $is_customer_initiated true if customer initiated, false otherwise (merchant initiated)
	 */
	public function set_auth_data( \WC_Order $order, bool $is_customer_initiated ) {
		$data = $this->get_transaction_data( $order, $is_customer_initiated );
		$data['capture'] = false;
		$this->prepare_request( $order, $data );
	}


	/**
	 * Sets the credit card charge transaction data.
	 *
	 * @since 5.0.0
	 *
	 * @param \WC_Order $order order object
	 * @param bool $is_customer_initiated true if customer initiated, false otherwise (merchant initiated)
	 */
	public function set_charge_data( \WC_Order $order, bool $is_customer_initiated ) {
		$data = $this->get_transaction_data( $order, $is_customer_initiated );
		$data['capture'] = true;
		$this->prepare_request( $order, $data );
	}


	/**
	 * Prepare the request
	 *
	 * @param \WC_Order $order order object
	 * @param bool $is_customer_initiated true if customer initiated, false otherwise (merchant initiated)
	 *
	 */
	protected function prepare_request( \WC_Order $order, array $data ) {

		$this->order = $order;
		$this->path  = self::REQUEST_PATH;
		$this->data  = $data;
	}


	/**
	 * Sets the credit card charge transaction data.
	 *
	 * @since 5.0.0
	 *
	 * @param \WC_Order $order order object
	 * @param bool $is_customer_initiated true if customer initiated, false otherwise (merchant initiated)
	 * @return array transaction data
	 */
	protected function get_transaction_data( \WC_Order $order, bool $is_customer_initiated ) : array {

		// available but not implemented:
		// partial_redemption, soft descriptor, level2/level3 data, tip_amount
		$data = [
			'amount'                => $this->to_cents( $order->payment_total ),
			'currency'              => strtolower( $order->get_currency() ),
			'ecomind'               => 'ecom',
			'external_reference_id' => $this->get_order_number( $order ),
		];

		/**
		 * Enable the Clover receipt email.
		 *
		 * @since 5.1.0
		 *
		 * @param boolean $enable_receipt_email defaults to `false`
		 * @param \WC_Order $order order object
	 	 * @param bool $is_customer_initiated true if customer initiated, false otherwise (merchant initiated)
		 * @param \Charge $this Charge request instance
		 */
		$enable_receipt_email = apply_filters( 'wc_first_data_clover_enable_receipt_email', false, $order, $is_customer_initiated, $this );

		if ( $enable_receipt_email ) {
			$data['receipt_email'] = $order->get_billing_email();
		}

		if ( isset( $order->payment->token ) ) {
			// paying with a saved card (card might be new and being saved right now, or might have been previously saved)
			$data['source'] = $order->payment->token;

			// when transacting with a multi-pay token the stored_credentials block should be supplied
			//   sequence should always be SUBSEQUENT (otherwise you get a "CVV could not be verified" response, at least in test)
			//   is_scheduled should always be false
			$data['stored_credentials'] = [
				'sequence'     => 'SUBSEQUENT',
				'is_scheduled' => false,
				'initiator'    => $is_customer_initiated ? 'CARDHOLDER' : 'MERCHANT',
			];
		} else {
			// paying with a non-saved card
			$data['source'] = $order->payment->js_token;
		}

		return $data;
	}
}
