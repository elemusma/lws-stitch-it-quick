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
 * Global Gateway API Transaction Response Class
 *
 * Handles credit card transaction responses
 *
 * @since 4.0.0
 */
class WC_First_Data_Global_Gateway_API_Transaction_Response extends WC_First_Data_Global_Gateway_API_Response implements Framework\SV_WC_Payment_Gateway_API_Authorization_Response {


	/** CSC match */
	const CSC_MATCH = 'M';


	/**
	 * Return true if the transaction is approved
	 *
	 * @link https://support.global.com/hc/en-us/articles/204029989-First-Data-Global-Gateway-Web-Service-API-Reference-Guide-#8.3
	 *
	 * @since 4.0.0
	 * @return bool
	 */
	public function transaction_approved() {

		return 'APPROVED' === $this->r_approved;
	}


	/**
	 * Global Gateway doesn't support the concept of a held transaction
	 *
	 * @since 4.0.0
	 * @return bool
	 */
	public function transaction_held() {

		return false;
	}


	/**
	 * Return the status code for the transaction
	 *
	 * @since 4.0.0
	 * @return string
	 */
	public function get_status_code() {

		return $this->r_approved;
	}


	/**
	 * Return the status message for the transaction
	 *
	 * @since 4.0.0
	 * @return string
	 */
	public function get_status_message() {

		return $this->r_message . ' ' . $this->r_error;
	}


	/**
	 * Return the transaction ID
	 *
	 * @since 4.0.0
	 * @return string
	 */
	public function get_transaction_id() {

		return $this->r_ordernum;
	}


	/**
	 * Return the authorization code for this transaction
	 *
	 * @since 4.0.0
	 * @return string
	 */
	public function get_authorization_code() {

		$codes = explode( ':', $this->r_code );

		return isset( $codes[0] ) ? $codes[0] : null;
	}


	/**
	 * Return the reference for this transaction, as provided by the credit
	 * card processor
	 *
	 * @since 4.0.0
	 * @return mixed
	 */
	public function get_reference_number() {

		return $this->r_ref;
	}


	/**
	 * Return the server timestamp for this transction. Used to uniquely identify
	 * a specific transaction where one order number may apply to several individual
	 * transactions (e.g. a post-auth)
	 *
	 * @since 4.0.0
	 * @return string
	 */
	public function get_timestamp() {

		return $this->r_tdate;
	}


	/**
	 * Return the AVS result for this transaction
	 *
	 * @since 4.0.0
	 * @return string
	 */
	public function get_avs_result() {

		return substr( $this->r_avs, 0, 3 );
	}


	/**
	 * Return the CSC result for this transaction
	 *
	 * @since 4.0.0
	 * @return string
	 */
	public function get_csc_result() {

		return substr( $this->r_avs, 3, 1 );
	}


	/**
	 * Return the CSC match result from this transaction
	 *
	 * @since 4.0.0
	 * @return bool
	 */
	public function csc_match() {

		return $this->get_csc_result() === self::CSC_MATCH;
	}


	/**
	 * Global Gateway does not support detailed decline messages
	 *
	 * @since 4.0.0
	 * @return string
	 */
	public function get_user_message() { }


	/**
	 * Return the payment type (credit-card or echeck) for this response
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_Payment_Gateway_API_Response::get_payment_type()
	 * @return string
	 */
	public function get_payment_type() {

		return 'credit-card';
	}


}
