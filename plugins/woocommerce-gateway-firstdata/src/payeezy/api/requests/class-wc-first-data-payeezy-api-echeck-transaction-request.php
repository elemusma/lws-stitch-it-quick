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
 * Handles eCheck transaction requests
 *
 * @since 4.0.0
 */
class WC_First_Data_Payeezy_API_eCheck_Transaction_Request extends WC_First_Data_Payeezy_API_Request {


	/**
	 * Create data for eCheck debit
	 *
	 * @link https://developer.payeezy.com/payeezy-api/apis/post/transactions-10
	 *
	 * @since 4.0.0
	 */
	public function create_check_debit() {

		$this->request_data = [
			'method'           => 'tele_check',
			'transaction_type' => 'purchase',
			'amount'           => round( floatval( $this->get_order()->payment_total ) * 100 ), // cents
			'currency_code'    => $this->get_order()->get_currency(),
			'tele_check'       => [
				'check_number'       => $this->get_order()->payment->check_number,
				'check_type'         => $this->get_order()->payment->check_type,
				'routing_number'     => $this->get_order()->payment->routing_number,
				'account_number'     => $this->get_order()->payment->account_number,
				'customer_id_number' => str_replace( [ '-', ' ' ], '', $this->get_order()->payment->customer_id_number ),
				'customer_id_type'   => $this->get_order()->payment->customer_id_type,
				'accountholder_name' => trim( $this->get_order()->get_formatted_billing_full_name() ),
				'client_email'       => $this->get_order()->get_billing_email(),
			],
			'billing_address'  => [
				'street'          => trim( $this->get_order()->get_billing_address_1( 'edit' ) . ' ' . $this->get_order()->get_billing_address_2( 'edit' ) ),
				'city'            => $this->get_order()->get_billing_city( 'edit' ),
				'state_province'  => Framework\SV_WC_Helper::str_truncate( $this->get_order()->get_billing_state( 'edit' ), 2 ),
				'zip_postal_code' => Framework\SV_WC_Helper::str_truncate( $this->get_order()->get_billing_postcode( 'edit' ), 5 ),
				'country'         => Framework\SV_WC_Helper::str_truncate( $this->get_order()->get_billing_country( 'edit' ), 2 ),
			],
		];
	}


}
