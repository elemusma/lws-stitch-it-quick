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

namespace Kestrel\WooCommerce\First_Data\Payeezy\API\Response\PaymentJS;

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\PluginFramework\v5_12_4 as Framework;
use Kestrel\WooCommerce\First_Data\Payeezy\API\Response;

/**
 * Payeezy Payment.JS API Create Payment Token webhook response.
 *
 * @link https://docs.paymentjs.firstdata.com/#webhook
 *
 * @since 4.7.0
 */
class Create_Payment_Token extends Framework\SV_WC_API_JSON_Response implements Framework\SV_WC_Payment_Gateway_API_Create_Payment_Token_Response {


	/**
	 * Gets the payment token.
	 *
	 * @since 4.7.0
	 *
	 * @return string
	 */
	public function get_payment_token() {

		return isset( $this->response_data->card->token ) ? $this->response_data->card->token : '';
	}


	/**
	 * Determines whether the transaction was approved.
	 *
	 * @since 4.7.0
	 *
	 * @return bool
	 */
	public function transaction_approved() {

		return ! $this->transaction_held();
	}


	/**
	 * Determines whether the transaction was held.
	 *
	 * @since 4.7.0
	 *
	 * @return bool
	 */
	public function transaction_held() {

		return ! empty( $this->response_data->error );
	}


	/**
	 * Gets the transaction ID.
	 *
	 * @since 4.7.0
	 *
	 * @return string
	 */
	public function get_transaction_id() {

		return isset( $this->response_data->gatewayRefId ) ? $this->response_data->gatewayRefId : '';
	}


	/**
	 * Gets the cardholder name.
	 *
	 * @since 4.7.0
	 *
	 * @return string
	 */
	public function get_account_name() {

		return isset( $this->response_data->card->name ) ? (string) $this->response_data->card->name : '';
	}


	/**
	 * Gets the masked account number.
	 *
	 * @since 4.7.0
	 *
	 * @return string
	 */
	public function get_account_number() {

		return isset( $this->response_data->card->masked ) ? (string) $this->response_data->card->masked : '';
	}


	/**
	 * Gets the card's last four digits.
	 *
	 * @since 4.7.0
	 *
	 * @return string
	 */
	public function get_last_four() {

		return isset( $this->response_data->card->last4 ) ? (string) $this->response_data->card->last4 : '';
	}


	/**
	 * Get's the card's expiration month.
	 *
	 * @since 4.7.0
	 *
	 * @return string
	 */
	public function get_expiry_month() {

		return isset( $this->response_data->card->exp->month ) ? (string) $this->response_data->card->exp->month : '';
	}


	/**
	 * Gets the card's expiration year.
	 *
	 * @since 4.7.0
	 *
	 * @return string
	 */
	public function get_expiry_year() {

		return isset( $this->response_data->card->exp->year ) ? (string) $this->response_data->card->exp->year : '';
	}


	/**
	 * Gets the card type.
	 *
	 * @since 4.7.0
	 *
	 * @return string
	 */
	public function get_type() {

		$card_type = isset( $this->response_data->card->brand ) ? (string) $this->response_data->card->brand : '';
		$card_type = Framework\SV_WC_Payment_Gateway_Helper::normalize_card_type( $card_type );

		return 'american-express' === $card_type ? Framework\SV_WC_Payment_Gateway_Helper::CARD_TYPE_AMEX : $card_type;
	}


	/**
	 * Gets the payment type.
	 *
	 * @since 4.7.0
	 *
	 * @return string 'credit-card'
	 */
	public function get_payment_type() {

		return \WC_Gateway_First_Data_Payeezy_Credit_Card::PAYMENT_TYPE_CREDIT_CARD;
	}


	/**
	 * Gets the response status code.
	 *
	 * @since 4.7.0
	 *
	 * @return string
	 */
	public function get_status_code() {

		return isset( $this->response_data->reason ) && is_string( $this->response_data->reason ) ? strtoupper( $this->response_data->reason ) : '';
	}


	/**
	 * Gets a message for the user.
	 *
	 * @since 4.7.0
	 *
	 * @return string
	 */
	public function get_status_message() {

		$reason = $this->get_status_code();

		switch ( $reason ) {

			case 'BAD_REQUEST' :
				$message = __( 'The request body is missing or incorrect for endpoint', 'woocommerce-gateway-firstdata' );
			break;
			case 'DECRYPTION_ERROR' :
				$message = __( 'Failed to decrypt card data', 'woocommerce-gateway-firstdata' );
			break;
			case 'INVALID_GATEWAY_CREDENTIALS' :
				$message = __( 'Gateway credentials failed', 'woocommerce-gateway-firstdata' );
				break;
			case 'JSON_ERROR' :
				$message = __( 'The request body is either not valid JSON or larger than 2kb', 'woocommerce-gateway-firstdata' );
			break;
			case 'KEY_NOT_FOUND' :
				$message = __( 'No available key found', 'woocommerce-gateway-firstdata' );
			break;
			case 'MISSING_CVV' :
				$message = __( 'Zero dollar auth requires cvv in form data', 'woocommerce-gateway-firstdata' );
			break;
			case 'NETWORK' :
				$message = __( 'Gateway connection error', 'woocommerce-gateway-firstdata' );
			break;
			case 'REJECTED' :
				$message = __( 'The request was rejected by the gateway', 'woocommerce-gateway-firstdata' );
			break;
			case 'SESSION_CONSUMED' :
				$message = __( 'Session completed in another request', 'woocommerce-gateway-firstdata' );
			break;
			case 'SESSION_INSERT' :
				$message = __( 'Failed to store session data', 'woocommerce-gateway-firstdata' );
			break;
			case 'SESSION_INVALID' :
				$message = __( 'Failed to match clientToken with valid record; can occur during deployment', 'woocommerce-gateway-firstdata' );
			break;
			case 'UNEXPECTED_RESPONSE' :
				$message = __( 'The gateway did not respond with the expected data', 'woocommerce-gateway-firstdata' );
			break;
			case 'UNKNOWN' :
				$message = __( 'Unknown error', 'woocommerce-gateway-firstdata' );
			break;
			default:
				$message = '';
			break;
		}

		return $message;
	}


	/**
	 * Gets the user-facing message.
	 *
	 * Payment.JS doesn't return any status codes that can be translated to user action.
	 *
	 * @since 4.7.0
	 *
	 * @return string
	 */
	public function get_user_message() {

		return '';
	}


}
