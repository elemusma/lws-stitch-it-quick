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
 * Payeezy Gateway API Transaction Request Class
 *
 * Handles credit card and eCheck transaction requests
 *
 * @since 4.0.0
 */
class WC_First_Data_Payeezy_Gateway_API_Transaction_Request extends WC_First_Data_Payeezy_Gateway_API_Request {


	/** transaction type used for regular purchases */
	const TRANSACTION_TYPE_PURCHASE = '00';

	/** tranasction type used for pre-authorized (but not captured) purchases */
	const TRANSACTION_TYPE_PRE_AUTHORIZATION = '01';

	/** transaction type used to perform a zero dollar pre-authorization for the purposes of tokenizing a credit card */
	const TRANSACTION_TYPE_PRE_AUTHORIZATION_ONLY = '05';

	/** transaction type used to capture a previously authorized transaction */
	const TRANSACTION_TYPE_TAGGED_PRE_AUTHORIZATION_COMPLETION = '32';

	/** transaction type used for void a previously authorized (but not captured) charge */
	const TRANSACTION_TYPE_TAGGED_VOID = '33';

	/** transaction type used for refunding a previously auth/captured charge */
	const TRANSACTION_TYPE_TAGGED_REFUND = '34';

	/** CVV2 value provided by the cardholder */
	const CVD_PROVIDED_BY_CARDHOLDER = '1';

	/** day phone number type */
	const PHONE_NUMBER_TYPE_DAY = 'D';

	/** indicates transaction was SSL secured */
	const ECI_CHANNEL_ENCRYPTED_TRANSACTION = '7';

	/** indicates transaction was _not_ SSL secured */
	const ECI_NON_SECURE_ELECTRONIC_COMMERCE_TRANSACTION = '8';


	/**
	 * Create data for credit card charge
	 *
	 * @since 4.0.0
	 */
	public function create_credit_card_charge() {

		$this->create_transaction( self::TRANSACTION_TYPE_PURCHASE );
	}


	/**
	 * Create data for credit card authorization
	 *
	 * @since 4.0.0
	 */
	public function create_credit_card_authorization() {

		$this->create_transaction( self::TRANSACTION_TYPE_PRE_AUTHORIZATION );
	}


	/**
	 * Create data for eCheck debit
	 *
	 * @since 4.0.0
	 */
	public function create_check_debit() {

		$this->create_transaction( self::TRANSACTION_TYPE_PURCHASE );
	}


	/**
	 * Create data for credit card capture
	 *
	 * @since 4.0.0
	 */
	public function create_credit_card_capture() {

		$this->create_transaction( self::TRANSACTION_TYPE_TAGGED_PRE_AUTHORIZATION_COMPLETION );
	}


	/**
	 * Create data for refunding a transaction
	 *
	 * @since 4.0.0
	 */
	public function create_refund() {

		$this->create_transaction( self::TRANSACTION_TYPE_TAGGED_REFUND );
	}


	/**
	 * Create data for voiding a transaction
	 *
	 * @since 4.0.0
	 */
	public function create_void() {

		$this->create_transaction( self::TRANSACTION_TYPE_TAGGED_VOID );
	}


	/**
	 * Create data for tokenizing a credit card
	 *
	 * @since 4.0.0
	 */
	public function create_tokenize_payment_method() {

		$this->create_transaction( self::TRANSACTION_TYPE_PRE_AUTHORIZATION_ONLY );
	}


	/**
	 * Create transaction data
	 *
	 * @since 4.0.0
	 * @param string $type type of transaction
	 */
	protected function create_transaction( $type ) {

		// set common transaction request data
		$this->request_data = array(
			'transaction_type' => $type,
			'reference_no'     => $this->get_reference_no(),
			'customer_ref'     => $this->get_order()->get_user_id() > 0 ? (string) $this->get_order()->get_user_id() : null,
			'reference_3'      => $this->get_order()->get_id( 'edit' ),
			'client_ip'        => $this->get_order()->get_customer_ip_address( 'edit' ),
			'client_email'     => $this->get_order()->get_billing_email( 'edit' ),
			'currency_code'    => $this->get_order()->get_currency(),
			'ecommerce_flag'   => $this->get_ecommerce_flag(),
		);

		// set transaction type specific data
		switch ( $type ) {

			// purchases
			case self::TRANSACTION_TYPE_PURCHASE:
			case self::TRANSACTION_TYPE_PRE_AUTHORIZATION:

				if ( 'credit_card' === $this->get_order()->payment->type ) {
					$this->set_credit_card_data( $type );
				} else {
					$this->set_echeck_data();
				}
			break;

			// tokenization
			case self::TRANSACTION_TYPE_PRE_AUTHORIZATION_ONLY:

				$this->set_credit_card_data( $type );
			break;

			// capture previously pre-auth'd charge
			case self::TRANSACTION_TYPE_TAGGED_PRE_AUTHORIZATION_COMPLETION:

				$this->set_credit_card_capture_data();
			break;

			// refund or void
			case self::TRANSACTION_TYPE_TAGGED_REFUND:
			case self::TRANSACTION_TYPE_TAGGED_VOID:

				$this->set_refund_or_void_data( $type );
			break;
		}
	}


	/**
	 * Set required data for a credit card transaction:
	 *
	 * + address data
	 * + level 2/3 processing data
	 * + soft descriptors (if enabled/provided)
	 * + card info (number, exp date, etc)
	 *
	 * @since 4.0.0
	 * @param string $transaction_type
	 */
	protected function set_credit_card_data( $transaction_type ) {

		// set address data for AVS filtering
		$this->set_address_data();

		// set level 2/3 processing data
		$this->set_level_2_3_data();

		// set soft descriptors for purchase transactions, when set by the admin
		if ( $transaction_type !== self::TRANSACTION_TYPE_PRE_AUTHORIZATION_ONLY && $this->get_order()->payment->has_soft_descriptors ) {
			$this->set_soft_descriptors();
		}

		// common data for all credit card transactions
		$data = [
			'amount'             => $this->get_order()->payment_total,
			'cardholder_name'    => Framework\SV_WC_Helper::str_to_ascii( $this->get_order()->get_formatted_billing_full_name() ),
			'cc_expiry'          => $this->get_order()->payment->exp_month . substr( $this->get_order()->payment->exp_year, -2 ),
			'partial_redemption' => $this->get_order()->payment->partial_redemption,
		];

		if ( empty( $this->get_order()->payment->token ) ) {

			// regular purchase, or $0 pre-authorization, add CC number
			$data['cc_number'] = $this->get_order()->payment->account_number;

			// and CSC, if present
			if ( ! empty( $this->get_order()->payment->csc ) ) {
				$data['cvd_code']         = $this->get_order()->payment->csc;
				$data['cvd_presence_ind'] = self::CVD_PROVIDED_BY_CARDHOLDER;
			}

		} else {

			// tokenized purchase, add token and credit card full type
			$data['transarmor_token'] = (string) $this->get_order()->payment->token; // string cast is intentional, otherwise Payeezy returns an error
			$data['credit_card_type'] = $this->get_order()->payment->full_type;
		}

		$this->request_data = array_merge( $this->request_data, $data );
	}


	/**
	 * Set required data for an eCheck transaction:
	 *
	 * + address data
	 * + check data (number, type, account number, etc)
	 *
	 * @since 4.0.0
	 */
	protected function set_echeck_data() {

		// set address data
		$this->set_address_data();

		$this->request_data = array_merge( $this->request_data, array(
			'amount'             => $this->get_order()->payment_total,
			'check_number'       => $this->get_order()->payment->check_number,
			'check_type'         => $this->get_order()->payment->check_type,
			'account_number'     => $this->get_order()->payment->account_number,
			'bank_id'            => $this->get_order()->payment->routing_number,
			'cardholder_name'    => Framework\SV_WC_Helper::str_truncate( Framework\SV_WC_Helper::str_to_ascii( $this->get_order()->get_formatted_billing_full_name() ), 30, '' ),
			'customer_id_number' => str_replace( array( '-', ' ' ), '', $this->get_order()->payment->customer_id_number ),
			'customer_id_type'   => $this->get_order()->payment->customer_id_type,
		) );
	}


	/**
	 * Set address data for a transaction. This is used for both credit cards
	 * and eChecks.
	 *
	 * @since 4.0.0
	 */
	protected function set_address_data() {

		$this->request_data = array_merge( $this->request_data, [
			'zip_code' => $this->get_order()->get_billing_postcode( 'edit' ),
			'address'  => [
				'address1'     => Framework\SV_WC_Helper::str_truncate( $this->get_order()->get_billing_address_1( 'edit' ), 30 ),
				'address2'     => Framework\SV_WC_Helper::str_truncate( $this->get_order()->get_billing_address_2( 'edit' ), 28 ),
				'city'         => Framework\SV_WC_Helper::str_truncate( $this->get_order()->get_billing_city( 'edit' ), 20 ),
				'state'        => Framework\SV_WC_Helper::str_truncate( $this->get_order()->get_billing_state( 'edit' ), 2 ),
				'zip'          => Framework\SV_WC_Helper::str_truncate( $this->get_order()->get_billing_postcode( 'edit' ), 10 ),
				'country_code' => Framework\SV_WC_Helper::str_truncate( $this->get_order()->get_billing_country( 'edit' ), 2 ),
				'phone_number' => Framework\SV_WC_Helper::str_truncate( preg_replace( '/\D/', '', $this->get_order()->get_billing_phone( 'edit' ) ), 14 ),
				'phone_type'   => 'D',
			],
		] );
	}


	/**
	 * Set level II/III processing data for the transaction
	 *
	 * @link https://support.payeezy.com/hc/en-us/articles/204029989-First-Data-Payeezy-Gateway-Web-Service-API-Reference-Guide-#4.2
	 *
	 * @since 4.0.0
	 */
	protected function set_level_2_3_data() {

		$tax_total = Framework\SV_WC_Helper::number_format( $this->get_order()->get_total_tax() );

		// level II
		$this->request_data['tax1_amount'] = $tax_total;

		// level III
		/* note level 3 is not supported yet because it *requires* each line item to have an associated
		commodity code, which the admin must enter from a list of hundreds of types (see https://support.payeezy.com/attachments/token/djgxolkycohpwho/?name=commodity_codes.pdf)
		and we've not had enough requests to build the UI required to make this possible. If a customer _really_ needs
		this, they can filter the request data and set the level 3 data manually @MR 2016-02-09
		-------
		$this->request_data['level3'] = array(
			'tax_amount' => $tax_total,
			'discount_amount' => Framework\SV_WC_Helper::number_format( $this->get_order()->get_total_discount() ),
			'freight_amount' => Framework\SV_WC_Helper::number_format( $this->get_order()->get_total_shipping() ),
			'ship_to_address' => array(
				'address1' => Framework\SV_WC_Helper::str_truncate( strtoupper( $this->get_order()->shipping_address_1 ), 28 ),
				'city'     => Framework\SV_WC_Helper::str_truncate( strtoupper( $this->get_order()->shipping_city ), 20 ),
				'state'    => Framework\SV_WC_Helper::str_truncate( strtoupper( $this->get_order()->shipping_state ), 2 ),
				'zip'      => Framework\SV_WC_Helper::str_truncate( $this->get_order()->shipping_postcode, 10 ),
				'country'  => in_array( $this->get_order()->shipping_country, array( 'US, CA', 'GB', 'UK', ) ) ? $this->get_order()->shipping_country : ' ',
				'email'    => Framework\SV_WC_Helper::str_truncate( $this->get_order()->billing_email, 50 ),
				'name'     => Framework\SV_WC_Helper::str_truncate( ( $this->get_order()->get_formatted_billing_full_name() ), 28 ),
				'line_items' => $this->get_level_3_line_items(),
			),
		);*/
	}


	/**
	 * Set soft descriptors for the credit card transaction. Note these are sanitized
	 * in the gateway class to remove non-ASCII characters and adhere to (known)
	 * character limits
	 *
	 * @since 4.0.0
	 */
	protected function set_soft_descriptors() {

		$this->request_data['soft_descriptor'] = array_filter( $this->get_order()->payment->soft_descriptors, 'strlen' );
	}


	/**
	 * Set required data for a credit card capture
	 *
	 * @since 4.0.0
	 */
	protected function set_credit_card_capture_data() {

		$this->request_data = array(
			'transaction_type'  => self::TRANSACTION_TYPE_TAGGED_PRE_AUTHORIZATION_COMPLETION,
			'transaction_tag'   => $this->get_order()->capture->trans_id,
			'authorization_num' => $this->get_order()->capture->auth_num,
			'amount'            => $this->get_order()->capture->amount,
		);
	}


	/**
	 * Set required data for a transaction refund or void
	 *
	 * @since 4.0.0
	 * @param string $transaction_type, refund or void type
	 */
	protected function set_refund_or_void_data( $transaction_type ) {

		$this->request_data = array(
			'transaction_type'  => $transaction_type,
			'transaction_tag'   => $this->get_order()->refund->trans_id,
			'authorization_num' => $this->get_order()->refund->auth_num,
			'amount'            => $this->get_order()->refund->amount,
		);
	}


	/**
	 * Get the reference number for the transaction. Payeezy gateway restricts
	 * various characters for US/non-US transactions so they're removed here.
	 *
	 * @since 4.0.0
	 * @return string
	 */
	protected function get_reference_no() {

		if ( 'US' === $this->get_order()->get_billing_country( 'edit' ) ) {

			$find = array( '|', '^', '%', '\\', '/' );

		} else {

			$find = array( '^', '\\', '[', ']', '~', '`' );
		}

		return str_replace( $find, '', $this->get_order()->get_order_number() );
	}


	/**
	 * Returns the appropriate ecommerce flag for the transaction request
	 *
	 * @link https://firstdata.zendesk.com/entries/21531261-ecommerce-flag-values
	 *
	 * @since 4.0.0
	 * @return string
	 */
	protected function get_ecommerce_flag() {

		return is_ssl() ? self::ECI_CHANNEL_ENCRYPTED_TRANSACTION : self::ECI_NON_SECURE_ELECTRONIC_COMMERCE_TRANSACTION;
	}


}
