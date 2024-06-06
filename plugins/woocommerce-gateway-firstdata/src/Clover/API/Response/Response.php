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
 * The Clover API base response class.
 *
 * @since 5.0.0
 */
abstract class Response extends Framework\SV_WC_API_JSON_Response implements Framework\SV_WC_Payment_Gateway_API_Response {

	/** @var string response code */
	protected $response_code;


	/**
	 * Construct the response.
	 *
	 * @since 5.0.0
	 * @param string $raw_response_json
	 * @param string $response_code the HTTP response code from the server response
	 */
	public function __construct( string $raw_response_json, string $response_code ) {

		$this->response_code = $response_code;

		parent::__construct( $raw_response_json );
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

		return $this->response_ok();
	}


	/**
	 * Determines if the transaction was held.
	 *
	 * @see Framework\SV_WC_Payment_Gateway_API_Response::transaction_held()
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function transaction_held() : bool {

		// TODO: does Clover have a notion of a held transaction status? Maybe check the free WC plugin? {JS: 2022-11-20}
		return false;
	}


	/**
	 * Gets the transaction status message.
	 *
	 * @see Framework\SV_WC_Payment_Gateway_API_Response::get_status_message()
	 * @since 2.0.0
	 *
	 * @return string|null
	 */
	public function get_status_message() {

		$message = '';

		if ( isset( $this->error->code ) ) {

			$error_type = ! empty( $this->error->type ) ? "{$this->error->type} : {$this->error->code}" : $this->error->code;
			$message    = sprintf( '[%1$s] %2$s', $error_type, $this->error->message );

			if ( isset( $this->error->declineCode ) ) {
				$message .= " ({$this->error->declineCode})";
			}
		}

		return $message;
	}


	/**
	 * Gets a customer-friendly error message if available.
	 *
	 * @see Framework\SV_WC_Payment_Gateway_API_Response::get_user_message()
	 * @since 5.0.0
	 *
	 * @return string
	 */
	public function get_user_message() : string {

		// TODO: we can also consider mapping $this->error->code to message, as done in "Clover Payments for WooCommerce", if needed {JS: 2022-11-20}
		if ( isset( $this->error->message ) ) {
			return $this->error->message;
		} else {
			return '';
		}
	}


	/**
	 * Gets the payment type.
	 *
	 * @see Framework\SV_WC_Payment_Gateway_API_Response::get_payment_type()
	 * @since 5.0.0
	 *
	 * @return string
	 */
	public function get_payment_type() : string {

		return Framework\SV_WC_Payment_Gateway::PAYMENT_TYPE_CREDIT_CARD;
	}


	/**
	 * Returns true if the response was 200 OK
	 *
	 * @since 5.0.0
	 */
	protected function response_ok() : bool {

		return (int) $this->response_code === 200;
	}


}
