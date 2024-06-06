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
 * The Clover API Charge / Capture response class.
 *
 * TODO: from the Clover docs: If the customer-provided postal code does not match the cardholder's code, the response includes "address_zip_check": "failed". Your app should automatically refund the payment in this case using the Create a refund or Return an order endpoint.
 * TODO: from the clover docs: The address_line1_check may return a false negative result due to factors like how the address was formatted in the request and how the address is formatted in the issuer's system. If address_line1_check is the only one that fails, your app may advise the merchant to take action and verify the validity of the transaction. In general, a payment should not be automatically refunded based on this check alone.
 * https://docs.clover.com/docs/confirming-customer-information-with-ecommerce-fraud-tools#recommendations-for-avs-checks
 *
 * TODO: from the Clover docs: if the customer's PIN does not match the PIN stored with the issue, the response includes `"cvc_check": "failed". Your app should automatically refund the payment in this case using the Create a refund or Return an order endpoint.
 * https://docs.clover.com/docs/confirming-customer-information-with-ecommerce-fraud-tools#recommendations-for-cvc-checks
 *
 * @link https://docs.clover.com/reference/createcharge
 * @link https://docs.clover.com/reference/capturecharge
 *
 * @since 5.0.0
 */
class Charge extends Response implements Framework\SV_WC_Payment_Gateway_API_Authorization_Response {


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
	 * Get the reference number
	 *
	 * @since 5.0.0
	 *
	 * @return string|null
	 */
	public function get_ref_num() {

		return $this->ref_num;
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

		// TODO: confirm this is the correct attribute, maybe outcome: network_status or paid: true or status: succeeded (like refunds) instead?
		return parent::transaction_approved() && $this->get_status_code() === 'authorized';
	}


	/**
	 * Gets the transaction status code.
	 *
	 * @see Framework\SV_WC_Payment_Gateway_API_Response::get_status_code()
	 * @since 2.0.0
	 *
	 * @return string|null one of 'authorized', 'processing_error', 'card_declined', etc
	 */
	public function get_status_code() {

		if ( isset( $this->outcome->type ) ) {
			return $this->outcome->type;
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
	 * @return string|null
	 */
	public function get_status_message() {

		$message = parent::get_status_message();

		if ( ! $message && isset( $this->outcome->network_status ) ) {
			$message = $this->outcome->network_status;
		}

		return $message;
	}


	/**
	 * Gets the transaction authorization code.
	 *
	 * @see Framework\SV_WC_Payment_Gateway_API_Authorization_Response::get_authorization_code()
	 * @since 5.0.0
	 *
	 * @return string|null
	 */
	public function get_authorization_code() {

		return $this->auth_code;
	}


	/**
	 * Determines if the AVS was a match.
	 *
	 * @since 5.0.0
	 *
	 * @return bool
	 */
	public function avs_match() : bool {

		return $this->avs_address_line1_check() && $this->avs_zip_check();
	}


	/**
	 * Gets the transaction AVS result code.
	 *
	 * Clover doesn't return AVS result codes, only whether address line 1 / ZIP checks passed.
	 *
	 * @see Framework\SV_WC_Payment_Gateway_API_Authorization_Response::get_avs_result()
	 * @since 5.0.0
	 *
	 * @return string
	 */
	public function get_avs_result() : string {

		return '';
	}

	/**
	 * @return bool
	 */
	public function avs_address_line1_check() : bool {

		// address_line1_check can be one of: pass, failed, unavailable, unchecked
		return isset( $this->source->address_line1_check ) && $this->source->address_line1_check === 'pass';
	}

	/**
	 * @return bool
	 */
	public function avs_zip_check(): bool {

		// address_zip_check can be one of: pass, failed, unavailable, unchecked
		return isset( $this->source->address_zip_check ) && $this->source->address_zip_check === 'pass';
	}


	/**
	 * Gets the transaction CSC validation result.
	 *
	 * Clover does not return CSC validation codes, only the result of the check.
	 *
	 * @see Framework\SV_WC_Payment_Gateway_API_Authorization_Response::get_csc_result()
	 * @since 5.0.0
	 *
	 * @return string|null
	 */
	public function get_csc_result() : string {

		return '';
	}


	/**
	 * Determines if the CSC was a match.
	 *
	 * @see Framework\SV_WC_Payment_Gateway_API_Authorization_Response::csc_match()
	 * @since 5.0.0
	 *
	 * @return bool
	 */
	public function csc_match() : bool {

		// cvc_check can be one of: pass, failed, unavailable, unchecked
		return isset( $this->source->cvc_check ) && $this->source->cvc_check === 'pass';
	}


}
