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
 * Payeezy API Transaction Request Class
 *
 * Handles credit card transaction requests
 *
 * @since 4.0.0
 */
class WC_First_Data_Payeezy_API_Credit_Card_Transaction_Request extends WC_First_Data_Payeezy_API_Request {


	/** transaction type used for regular purchases */
	const TRANSACTION_TYPE_PURCHASE = 'purchase';

	/** transaction type used for pre-authorized (but not captured) purchases */
	const TRANSACTION_TYPE_AUTHORIZATION = 'authorize';

	/** transaction type used to capture a previously authorized transaction */
	const TRANSACTION_TYPE_CAPTURE = 'capture';

	/** transaction type used for void a previously authorized (but not captured) charge */
	const TRANSACTION_TYPE_VOID = 'void';

	/** transaction type used for refunding a previously auth/captured charge */
	const TRANSACTION_TYPE_REFUND = 'refund';


	/**
	 * Create data for credit card charge
	 *
	 * @link https://developer.payeezy.com/payeezy-api/apis/post/transactions-3
	 * @link https://developer.payeezy.com/payeezy-api/apis/post/transactions-4
	 *
	 * @since 4.0.0
	 */
	public function create_credit_card_charge() {

		$this->create_transaction( self::TRANSACTION_TYPE_PURCHASE );
	}


	/**
	 * Create data for credit card authorization
	 *
	 * @link https://developer.payeezy.com/payeezy-api/apis/post/transactions-3
	 * @link https://developer.payeezy.com/payeezy-api/apis/post/transactions-4
	 *
	 * @since 4.0.0
	 */
	public function create_credit_card_authorization() {

		$this->create_transaction( self::TRANSACTION_TYPE_AUTHORIZATION );
	}


	/**
	 * Create data for credit card capture
	 *
	 * @link https://developer.payeezy.com/payeezy-api/apis/post/transactions/%7Bid%7D-0
	 *
	 * @since 4.0.0
	 */
	public function create_credit_card_capture() {

		$this->path = 'transactions/' . $this->get_order()->capture->trans_id;

		$this->request_data = [
			'merchant_ref'     => $this->get_order()->capture->description,
			'transaction_tag'  => $this->get_order()->capture->transaction_tag,
			'transaction_type' => self::TRANSACTION_TYPE_CAPTURE,
			'method'           => 'credit_card',
			'amount'           => round( floatval( $this->get_order()->capture->amount ) * 100 ), // cents
			'currency_code'    => $this->get_order()->get_currency(),
		];
	}


	/**
	 * Create data for refunding a transaction
	 *
	 * @link https://developer.payeezy.com/payeezy-api/apis/post/transactions/%7Bid%7D-0
	 *
	 * @since 4.0.0
	 */
	public function create_refund() {

		$this->path = 'transactions/' . $this->get_order()->refund->trans_id;

		$this->request_data = [
			'merchant_ref'     => $this->get_order()->refund->reason,
			'transaction_tag'  => $this->get_order()->refund->transaction_tag,
			'transaction_type' => self::TRANSACTION_TYPE_REFUND,
			'method'           => 'credit_card',
			'amount'           => round( floatval( $this->get_order()->refund->amount ) * 100 ), // cents
			'currency_code'    => $this->get_order()->get_currency(),
		];
	}


	/**
	 * Create data for voiding a transaction
	 *
	 * @link https://developer.payeezy.com/payeezy-api/apis/post/transactions/%7Bid%7D-0
	 *
	 * @since 4.0.0
	 */
	public function create_void() {

		$this->path = 'transactions/' . $this->get_order()->refund->trans_id;

		$this->request_data = [
			'merchant_ref'     => $this->get_order()->refund->reason,
			'transaction_tag'  => $this->get_order()->refund->transaction_tag,
			'transaction_type' => self::TRANSACTION_TYPE_VOID,
			'method'           => 'credit_card',
			'amount'           => round( floatval( $this->get_order()->refund->amount ) * 100 ), // cents
			'currency_code'    => $this->get_order()->get_currency(),
		];
	}


	/**
	 * Create purchase/authorize transaction data
	 *
	 * @since 4.0.0
	 * @param string $type type of transaction
	 */
	protected function create_transaction( $type ) {

		// set common transaction request data
		$this->request_data = [
			'merchant_ref'     => $this->get_order()->description,
			'method'           => 'token',
			'transaction_type' => $type,
			'amount'           => round( floatval( $this->get_order()->payment_total ) * 100 ), // cents
			'currency_code'    => $this->get_order()->get_currency(),
			'token'            => $this->get_token_data(),
			'billing_address'  => $this->get_billing_address(),
			'soft_descriptors' => array_filter( $this->get_order()->payment->soft_descriptors, 'strlen' ),
			'level2'           => [ 'tax1_amount' => $this->get_order()->get_total_tax() ],
		];
	}


	/**
	 * Return the token data for the transaction
	 *
	 * @since 4.0.0
	 * @return array
	 */
	protected function get_token_data() {

		return [
			'token_type' => 'FDToken',
			'token_data' => [
				'type'            => $this->get_order()->payment->full_type,
				'value'           => $this->get_order()->payment->token,
				'cardholder_name' => trim( $this->get_order()->get_formatted_billing_full_name() ),
				'exp_date'        => $this->get_order()->payment->exp_month . substr( $this->get_order()->payment->exp_year, - 2 ),
			],
		];
	}


	/**
	 * Return the billing address info required for AVS processing on the transaction
	 *
	 * @since 4.0.0
	 * @return array
	 */
	protected function get_billing_address() {

		return [
			'street'          => trim( $this->get_order()->get_billing_address_1( 'edit' ) . ' ' . $this->get_order()->get_billing_address_2( 'edit' ) ),
			'city'            => $this->get_order()->get_billing_city( 'edit' ),
			'state_province'  => Framework\SV_WC_Helper::str_truncate( $this->get_order()->get_billing_state( 'edit' ), 2 ),
			'country_code'    => Framework\SV_WC_Helper::str_truncate( $this->get_order()->get_billing_country( 'edit' ), 2 ),
			'zip_postal_code' => Framework\SV_WC_Helper::str_truncate( $this->get_order()->get_billing_postcode( 'edit' ), 5 ),
			'email'           => $this->get_order()->get_billing_email( 'edit' ),
			'phone'           => [
				'type'   => 'D',
				'number' => Framework\SV_WC_Helper::str_truncate( preg_replace( '/\D/', '', $this->get_order()->get_billing_phone( 'edit' ) ), 14 ),
			],
		];
	}


}
