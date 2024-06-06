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
 * Payeezy API Transaction Response Class
 *
 * Handles credit card and eCheck transaction responses
 *
 * @since 4.0.0
 */
class WC_First_Data_Payeezy_API_Tokenize_Credit_Card_Response {


	public function __construct( \WC_Order $order ) {

		$this->order = $order;
	}


	/**
	 * Return true if the transaction is approved
	 *
	 * @since 4.0.0
	 * @return bool
	 */
	public function transaction_approved() {

		return true;
	}


	/**
	 * Payeezy doesn't support the concept of a held transaction
	 *
	 * @since 4.0.0
	 * @return bool
	 */
	public function transaction_held() {

		return false;
	}


	/**
	 * Return the status code for the transaction, this is either the bank
	 * response code or exact response code (for transactions with gateway errors)
	 *
	 * @since 4.0.0
	 * @return string
	 */
	public function get_status_code() {

		return 'success';
	}


	/**
	 * Return the status message for the transaction, this is either the bank
	 * response message or exact response message (for transactions with gateway errors)
	 *
	 * @since 4.0.0
	 * @return string
	 */
	public function get_status_message() {

		return 'success';
	}



	/**
	 * Return the transaction ID
	 *
	 * @since 4.0.0
	 * @return string
	 */
	public function get_transaction_id() { }


	/**
	 * Payeezy does not return an authorization code
	 *
	 * @since 4.0.0
	 * @return null
	 */
	public function get_authorization_code() { }


	/**
	 * Returns the payment token, if requested, for the transaction
	 *
	 * @since 4.0.0
	 * @return Framework\SV_WC_Payment_Gateway_Payment_Token
	 */
	public function get_payment_token() {

		return new Framework\SV_WC_Payment_Gateway_Payment_Token( $this->get_order()->payment->token, array(
			'type'      => 'credit_card',
			'last_four' => $this->get_order()->payment->last_four,
			'card_type' => $this->get_order()->payment->card_type,
			'exp_month' => $this->get_order()->payment->exp_month,
			'exp_year'  => $this->get_order()->payment->exp_year,
		) );
	}


	protected function get_order() {
		return $this->order;
	}


}
