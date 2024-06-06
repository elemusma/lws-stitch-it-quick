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
 * Payeezy Gateway API Transaction Response Class
 *
 * Handles credit card and eCheck transaction responses
 *
 * @since 4.0.0
 */
class WC_First_Data_Payeezy_Gateway_API_Transaction_Response extends WC_First_Data_Payeezy_Gateway_API_Response implements Framework\SV_WC_Payment_Gateway_API_Authorization_Response, Framework\SV_WC_Payment_Gateway_API_Create_Payment_Token_Response {


	/** CSC match result value */
	const CSC_MATCH = 'M';


	/**
	 * Return true if the transaction is approved
	 *
	 * @link https://support.payeezy.com/hc/en-us/articles/204029989-First-Data-Payeezy-Gateway-Web-Service-API-Reference-Guide-#8.3
	 *
	 * @since 4.0.0
	 * @return bool
	 */
	public function transaction_approved() {

		return 1 === $this->transaction_approved && 0 === $this->transaction_error;
	}


	/**
	 * Payeezy Gateway doesn't support the concept of a held transaction
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

		return $this->get_status_info()->code;
	}


	/**
	 * Return the status message for the transaction, this is either the bank
	 * response message or exact response message (for transactions with gateway errors)
	 *
	 * @since 4.0.0
	 * @return string
	 */
	public function get_status_message() {

		return $this->get_status_info()->message;
	}


	/**
	 * Helper to return the status code and message for a transaction
	 *
	 * @since 4.0.0
	 * @return \stdClass
	 */
	protected function get_status_info() {

		$status = new stdClass();

		if ( $this->transaction_approved() ) {

			$status->code    = $this->bank_resp_code;
			$status->message = $this->bank_message;

		} else {

			if ( $this->has_gateway_error() ) {

				$status->code    = $this->exact_resp_code;
				$status->message = $this->exact_message;

			} else {

				$status->code    = $this->bank_resp_code;
				$status->message = $this->bank_message;
			}

		}

		return $status;
	}


	/**
	 * Returns true if the transaction has a gateway error, which indicates
	 * the bank response code/message will not be present.
	 *
	 * @since 4.0.0
	 * @return bool
	 */
	public function has_gateway_error() {

		return 1 === $this->transaction_error;
	}


	/**
	 * Return the transaction ID
	 *
	 * @since 4.0.0
	 * @return string
	 */
	public function get_transaction_id() {

		return $this->transaction_tag;
	}


	/**
	 * Return the authorization code for this transaction
	 *
	 * @since 4.0.0
	 * @return string
	 */
	public function get_authorization_code() {

		return $this->authorization_num;
	}


	/**
	 * Return the sequence number for this transaction
	 *
	 * @since 4.0.0
	 * @return mixed
	 */
	public function get_sequence_no() {

		return $this->sequence_no;
	}


	/**
	 * Return the AVS result for this transaction
	 *
	 * @since 4.0.0
	 * @return string
	 */
	public function get_avs_result() {

		return $this->avs;
	}


	/**
	 * Return the CSC result for this transaction
	 *
	 * @since 4.0.0
	 * @return string
	 */
	public function get_csc_result() {

		return $this->cvv2;
	}


	/**
	 * Returns true if the transaction resulted in a CSC match
	 *
	 * @since 4.0.0
	 * @return bool
	 */
	public function csc_match() {

		return $this->get_csc_result() === self::CSC_MATCH;
	}


	/**
	 * Returns the payment token, if requested, for the transaction
	 *
	 * @since 4.0.0
	 * @return Framework\SV_WC_Payment_Gateway_Payment_Token
	 * @throws Framework\SV_WC_Payment_Gateway_Exception
	 */
	public function get_payment_token() {

		if ( ! $token = $this->get_transarmor_token() ) {
			throw new Framework\SV_WC_Payment_Gateway_Exception( __( 'Required TransArmor token not received. Please double-check your Payeezy Gateway configuration.', 'woocommerce-gateway-firstdata' ) );
		}

		return new Framework\SV_WC_Payment_Gateway_Payment_Token( $this->get_transarmor_token(), [
			'type'      => 'credit_card',
			'last_four' => $this->get_request()->get_order()->payment->last_four,
			'card_type' => $this->get_request()->get_order()->payment->card_type,
			'exp_month' => $this->get_request()->get_order()->payment->exp_month,
			'exp_year'  => $this->get_request()->get_order()->payment->exp_year,
		] );
	}


	/**
	 * Return the TransArmor token for the transaction, if present
	 *
	 * @since 4.0.0
	 * @return string|null string if present, null otherwise
	 */
	public function get_transarmor_token() {

		return $this->transarmor_token;
	}


	/**
	 * Return a message suitable for display to the user
	 *
	 * @since 4.0.0
	 * @return string
	 */
	public function get_user_message() {

		$helper     = new Framework\SV_WC_Payment_Gateway_API_Response_Message_Helper();
		$message_id = $this->get_user_message_id();

		$message = $helper->get_user_message( $message_id );

		/**
		 * First Data Payeezy Gateway Response User Message Filter.
		 *
		 * Allow actors to filter the response message shown to users.
		 *
		 * @since 4.0.0
		 * @param string $message message shown to user
		 * @param string $message_id message ID used with payment gateway response message helper class
		 * @param \WC_First_Data_Payeezy_Gateway_API_Transaction_Response $this response class instance
		 */
		return apply_filters( 'wc_first_data_payeezy_gateway_response_user_message', $message, $message_id, $this );
	}


	/**
	 * Helper to return the user message ID given the response code from Payeezy
	 * Gateway
	 *
	 * @link https://support.payeezy.com/hc/en-us/articles/203730509-First-Data-Global-Gateway-e4-Bank-Response-Codes
	 * @link https://support.payeezy.com/hc/en-us/articles/203730499-eCommerce-Response-Codes-ETG-e4-Transaction-Gateway-Codes-
	 *
	 * @since 4.0.0
	 * @return string
	 */
	protected function get_user_message_id() {

		$codes = [
			'22'  => 'card_number_invalid',
			'25'  => 'card_expiry_invalid',
			'08'  => 'csc_mismatch',
			'44'  => 'avs_mismatch',
			'F1'  => 'avs_mismatch',
			'F2'  => 'card_declined',
			'201' => 'card_number_invalid',
			'302' => 'insufficient_funds',
			'303' => 'card_declined',
			'304' => 'card_number_type_invalid',
			'401' => 'card_declined',
			'402' => 'card_declined',
			'501' => 'card_declined',
			'502' => 'decline',
			'503' => 'csc_mismatch',
			'505' => 'decline',
			'508' => 'decline',
			'509' => 'insufficient_funds',
			'510' => 'insufficient_funds',
			'521' => 'insufficient_funds',
			'522' => 'card_expired',
			'530' => 'card_declined',
			'531' => 'csc_mismatch',
			'750' => 'bank_aba_invalid',
			'751' => 'bank_aba_invalid',
			'787' => 'decline',
			'811' => 'csc_mismatch',
			'903' => 'card_expiry_invalid',
		];

		return isset( $codes[ $this->get_status_code() ] ) ? $codes[ $this->get_status_code() ] : 'error';
	}


	/**
	 * Return the payment type (credit-card or echeck) for this response
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_Payment_Gateway_API_Response::get_payment_type()
	 * @return string
	 */
	public function get_payment_type() {

		return 'credit_card' === $this->get_request()->get_order()->payment->type ? 'credit-card' : 'echeck';
	}


}
