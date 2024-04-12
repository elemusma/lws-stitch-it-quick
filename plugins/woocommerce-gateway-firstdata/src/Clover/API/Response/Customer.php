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

namespace Atreus\WooCommerce\First_Data\Clover\API\Response;

use SkyVerge\WooCommerce\PluginFramework\v5_11_12 as Framework;

defined( 'ABSPATH' ) or exit;

/**
 * The Clover API Customer response class: create customer with saved card, add
 * saved card to customer, revoke saved card.
 *
 * TODO: consider splitting out the revoke card response, which is on the Customer endpoint, but a very different / simpler response {JS: 2022-11-21}
 *
 * @link https://docs.clover.com/reference/createcustomer
 * @link https://docs.clover.com/reference/updatecustomer
 * @link https://docs.clover.com/reference/revokecard
 *
 * @since 5.0.0
 */
class Customer extends Response implements Framework\SV_WC_Payment_Gateway_API_Customer_Response, Framework\SV_WC_Payment_Gateway_API_Create_Payment_Token_Response {

	/** @var Framework\SV_WC_Payment_Gateway_Payment_Token $payment_token the payment token */
	protected $payment_token;


	/**
	 * Gets the transaction status code.
	 *
	 * @see Framework\SV_WC_Payment_Gateway_API_Response::get_status_code()
	 * @since 5.0.0
	 *
	 * @return string|null one of null, 'email_invalid', etc
	 */
	public function get_status_code() {

		// Clover doesn't have a success code for the customer endpoint
		if ( isset( $this->error->code ) ) {
			return $this->error->code;
		}
	}


	/**
	 * Set the payment token on this response object
	 *
	 * @since 5.0.0
	 * @param Framework\SV_WC_Payment_Gateway_Payment_Token $payment_token the payment token to set
	 * @return Customer this object
	 */
	public function set_payment_token( $payment_token ) {
		$this->payment_token = $payment_token;

		return $this;
	}


	/**
	 * Returns the customer ID for a successful customer create/update response
	 *
	 * @see Framework\SV_WC_Payment_Gateway_API_Customer_Response::get_customer_id()
	 * @since 5.0.0
	 *
	 * @return null|string customer ID returned by the gateway
	 */
	public function get_customer_id() {
		if ( $this->object == 'customer' ) {
			return $this->id;
		}
	}


	/**
	 * Returns the card ID, which can be used to susequently revoke the card
	 *
	 * @return string|null the card ID
	 */
	public function get_card_id() {

		if ( isset( $this->sources->data ) ) {
			return $this->sources->data[0];
		}
	}


	/**
	 * Return the payment token for a tokenization transaction.
	 *
	 * @see Framework\SV_WC_Payment_Gateway_API_Create_Payment_Token_Response::get_payment_token()
	 * @since 5.0.0
	 *
	 * @return null|Framework\SV_WC_Payment_Gateway_Payment_Token
	 */
	public function get_payment_token() {

		return $this->payment_token;
	}


	/**
	 * Clover Customer responses don't have a transaction ID
	 *
	 * @see Framework\SV_WC_Payment_Gateway_API_Response::get_transaction_id()
	 * @since 5.0.0
	 *
	 * @return null
	 */
	public function get_transaction_id() { return null; }


}
