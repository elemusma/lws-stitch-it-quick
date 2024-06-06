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

namespace Kestrel\WooCommerce\First_Data\Clover\API\Response;

use SkyVerge\WooCommerce\PluginFramework\v5_12_4 as Framework;

defined( 'ABSPATH' ) or exit;

/**
 * The Clover API Refund (and Void) response class.
 *
 * @link https://docs.clover.com/reference/createrefund
 * @since 5.0.0
 */
class Refund extends Response {


	/**
	 * Gets the transaction ID.
	 *
	 * @see Framework\SV_WC_Payment_Gateway_API_Response::get_transaction_id()
	 * @since 5.0.0
	 *
	 * @return string|null
	 */
	public function get_transaction_id() {

		if ( $this->response_ok() ) {
			return $this->id;
		} elseif ( isset( $this->error->charge ) ) {
			return $this->error->charge;
		}
	}


	/**
	 * Determines if the transaction was approved.
	 *
	 * @see Framework\SV_WC_Payment_Gateway_API_Response::transaction_approved()
	 * @since 5.0.0
	 *
	 * @return bool
	 */
	public function transaction_approved() : bool {

		// TODO: confirm this is the correct attribute, maybe outcome:network_status or paid: true instead?
		return parent::transaction_approved() && $this->get_status_code() === 'succeeded';
	}


	/**
	 * Gets the transaction status code.
	 *
	 * @see Framework\SV_WC_Payment_Gateway_API_Response::get_status_code()
	 * @since 5.0.0
	 *
	 * @return string|null one of 'succeeded', 'processing_error', etc
	 */
	public function get_status_code() {

		if ( $this->status ) {
			return $this->status;
		} elseif ( isset( $this->error->code ) ) {
			return $this->error->code;
		}
	}


	/**
	 * Gets the transaction status message.
	 *
	 * @see Framework\SV_WC_Payment_Gateway_API_Response::get_status_message()
	 * @since 5.0.0
	 *
	 * @return null|string
	 */
	public function get_status_message() {

		$message = parent::get_status_message();

		if ( ! $message ) {
			$message = $this->get_refund_type() . ' - ' . $this->get_gateway_response_code();
		}

		return $message;
	}


	/**
	 * Get the refund type, either PREAUTH for an auth-only transaction or AUTH if the original charge was previously
	 * captured
	 *
	 * @since 5.0.0
	 *
	 * @return string|null one of 'PREAUTH', or 'AUTH'
	 */
	public function get_refund_type() {

		return isset( $this->metadata->refundType ) ? $this->metadata->refundType : null;
	}


	/**
	 * Get the auth code
	 *
	 * @since 5.0.0
	 *
	 * @return string e.g. 'OK4556'
	 */
	public function get_auth_code() {

		return isset( $this->metadata->authCode ) ? $this->metadata->authCode : null;
	}


	/**
	 * Get the gateway response code
	 *
	 * @since 5.0.0
	 *
	 * @return string e.g. "000"
	 */
	public function get_gateway_response_code() {

		return isset( $this->metadata->gatewayResponseCode ) ? $this->metadata->gatewayResponseCode : null;
	}


	/**
	 * Get the reference number
	 *
	 * @see Response::get_ref_num()
	 * @since 5.0.0
	 *
	 * @return string|null e.g. "232300500240"
	 */
	public function get_ref_num() {

		return isset( $this->metadata->refNum ) ? $this->metadata->refNum : null;
	}


}
