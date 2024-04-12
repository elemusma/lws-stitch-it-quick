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

use SkyVerge\WooCommerce\PluginFramework\v5_11_12 as Framework;

/**
 * Payeezy eCheck Class
 *
 * Handles eCheck specific functionality
 *
 * @since 4.0.0
 */
class WC_Gateway_First_Data_Payeezy_eCheck extends WC_Gateway_First_Data_Payeezy {


	/**
	 * Setup the gateway
	 *
	 * @since 4.0.0
	 */
	public function __construct() {

		parent::__construct(
			\WC_First_Data::PAYEEZY_ECHECK_GATEWAY_ID,
			wc_first_data(),
			array(
				'method_title'       => __( 'Payeezy TeleCheck', 'woocommerce-gateway-firstdata' ),
				'method_description' => __( 'Allow customers to securely pay using their checking or savings account via Payeezy.', 'woocommerce-gateway-firstdata' ),
				'supports'           => array(
					self::FEATURE_PRODUCTS,
					self::FEATURE_PAYMENT_FORM,
					self::FEATURE_DETAILED_CUSTOMER_DECLINE_MESSAGES,
				),
				'payment_type'       => self::PAYMENT_TYPE_ECHECK,
				'environments'       => $this->get_payeezy_environments(),
				'shared_settings'    => $this->shared_settings_names,
			)
		);

		// add check number and check type fields to payment form
		add_filter( 'wc_' . $this->get_id() . '_payment_form_default_echeck_fields', array( $this, 'add_payment_form_fields' ), 5 );

		// add the authorization language required for Telecheck after the payment form description
		add_filter( 'wc_' . $this->get_id() . '_payment_form_description', array( $this, 'add_authorization_language' ), 5 );
	}


	/** Payment Form methods **************************************************/


	/**
	 * Add check number and check type inputs to the payment form
	 *
	 * @since 4.0.0
	 * @param array $fields
	 * @return array
	 */
	public function add_payment_form_fields( $fields ) {

		// remove account type field
		unset( $fields['account-type'] );

		// add check number
		$fields['check-number'] = array(
			'type'              => 'text',
			'label'             => __( 'Check Number', 'woocommerce-gateway-firstdata' ),
			'id'                => 'wc-' . $this->get_id_dasherized() . '-check-number',
			'name'              => 'wc-' . $this->get_id_dasherized() . '-check-number',
			'required'          => true,
			'class'             => array( 'form-row-first' ),
			'input_class'       => array( 'wc-' . $this->get_id_dasherized() . '-check-number' ),
			'maxlength'         => 30,
			'custom_attributes' => array( 'autocomplete' => 'off' ),
			'value'             => $this->is_test_environment() ? '1234' : '',
		);

		// add check type
		$fields['check-type'] = array(
			'type'              => 'select',
			'label'             => __( 'Check Type', 'woocommerce-gateway-firstdata' ),
			'id'                => 'wc-' . $this->get_id_dasherized() . '-check-type',
			'name'              => 'wc-' . $this->get_id_dasherized() . '-check-type',
			'required'          => true,
			'class'             => array( 'form-row-last' ),
			'input_class'       => array( 'wc-' . $this->get_id_dasherized() . '-check-type' ),
			'options'           => array(
				'personal'  => _x( 'Personal', 'check type', 'woocommerce-gateway-firstdata' ),
				'corporate' => _x( 'Corporate', 'check type', 'woocommerce-gateway-firstdata' ),
			),
			'maxlength'         => 30,
			'custom_attributes' => array(),
			'value'             => 'personal',
		);

		// add check type
		$fields['customer-id-type'] = array(
			'type'              => 'select',
			'label'             => __( 'ID Type', 'woocommerce-gateway-firstdata' ),
			'id'                => 'wc-' . $this->get_id_dasherized() . '-customer-id-type',
			'name'              => 'wc-' . $this->get_id_dasherized() . '-customer-id-type',
			'required'          => true,
			'class'             => array( 'form-row-first' ),
			'input_class'       => array( 'wc-' . $this->get_id_dasherized() . '-customer-id-type' ),
			'options'           => array(
				'0'  => _x( 'Driver\'s License', 'Identification type', 'woocommerce-gateway-firstdata' ),
				'1'  => _x( 'Social Security',   'Identification type', 'woocommerce-gateway-firstdata' ),
				'2'  => _x( 'Tax ID',            'Identification type', 'woocommerce-gateway-firstdata' ),
				'3'  => _x( 'Military ID',       'Identification type', 'woocommerce-gateway-firstdata' ),
			),
			'value' => '0',
		);

		// add customer ID number
		$fields['customer-id-number'] = array(
			'type'              => 'text',
			'label'             => __( 'ID Number', 'woocommerce-gateway-firstdata' ),
			'id'                => 'wc-' . $this->get_id_dasherized() . '-customer-id-number',
			'name'              => 'wc-' . $this->get_id_dasherized() . '-customer-id-number',
			'required'          => true,
			'class'             => array( 'form-row-last' ),
			'input_class'       => array( 'wc-' . $this->get_id_dasherized() . '-customer-id-number' ),
			'custom_attributes' => array( 'autocomplete' => 'off' ),
			'value'             => $this->is_test_environment() ? 'W1234' : '',
		);

		return $fields;
	}


	/**
	 * Add the required "TeleCheck Internet Check Acceptance Authorization Language"
	 * after the standard eCheck payment form description
	 *
	 * @link https://support.payeezy.com/hc/en-us/articles/204059459-What-is-TeleCheck-And-How-It-Is-Used-Within-The-Payeezy-Gateway
	 * @link https://support.payeezy.com/hc/en-us/articles/205315027
	 *
	 * @since 4.0.0
	 * @param string $description default description HTML
	 * @return string
	 */
	public function add_authorization_language( $description ) {

		ob_start();
		?>
		<p style="font-size: 10px;">By entering my account number below and clicking <strong>Place Order</strong>, I authorize my payment to be processed as an
			electronic funds transfer or draft drawn from my account.  If my full order is not available at the same time,
			I authorize partial debits to my account, not to exceed the total authorized amount.  The partial debits will take place upon
			each shipment of partial goods for amounts corresponding to the value of each shipment.  If any of my payments are returned unpaid,
			I authorize you or your service provider to collect the payment and my state’s return item fee for each payment by electronic
			fund transfer(s) or draft(s) drawn from my account.
			<a href="http://www.firstdata.com/support/TeleCheck_returned_check/returned_check_fees.htm" target="_blank">Click here to view your state’s returned item fee and other costs associated with the transaction.</a>
			If this payment is from a corporate account, I make these authorizations as an authorized corporate representative and agree
			that the entity will be bound by the NACHA operating rules.  This authorization shall remain in full force and effect until revoked.
			Any payment authorized for today’s date will initiate at end-of-business, Central Time.  I understand that for orders shipped in full,
			my account will be debited  within 1-2 banking days from this Authorization.  In the event payments are authorized for initiation at a
			future date, those payments may be revoked by providing written notice to this merchant at the contact information found on this
			merchant's website and providing the merchant reasonable opportunity to act upon it.</p>
		<p style="font-size: 10px;">Please use your browser’s print feature to print this disclosure for your records.</p>
		<?php
		return $description . ob_get_clean();
	}


	/**
	 * Return the default values for this payment method, used to pre-fill
	 * a valid test account number when in testing mode
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_Payment_Gateway::get_payment_method_defaults()
	 * @return array
	 */
	public function get_payment_method_defaults() {

		$defaults = parent::get_payment_method_defaults();

		if ( $this->is_test_environment() ) {

			$defaults['routing-number'] = '121000248';
			$defaults['account-number'] = '8675309';
		}

		return $defaults;
	}


	/**
	 * Validates the eCheck payment fields.
	 *
	 * @since 4.1.8
	 * @param bool $is_valid whether the payment fields are valid
	 * @return bool
	 */
	protected function validate_check_fields( $is_valid ) {

		$check_number = Framework\SV_WC_Helper::get_posted_value( 'wc-' . $this->get_id_dasherized() . '-check-number' );
		$id_number    = Framework\SV_WC_Helper::get_posted_value( 'wc-' . $this->get_id_dasherized() . '-customer-id-number' );

		// check number is always required
		if ( empty( $check_number ) ) {

			Framework\SV_WC_Helper::wc_add_notice( esc_html__( 'Check Number is missing', 'woocommerce-gateway-firstdata' ), 'error' );
			$is_valid = false;
		}

		// ID number is always required
		if ( empty( $id_number ) ) {

			Framework\SV_WC_Helper::wc_add_notice( esc_html__( 'ID number is missing', 'woocommerce-gateway-firstdata' ), 'error' );
			$is_valid = false;

		} elseif ( ! preg_match( '/^[a-zA-Z0-9 -]+$/', $id_number ) ) {

			Framework\SV_WC_Helper::wc_add_notice( esc_html__( 'ID number is invalid', 'woocommerce-gateway-firstdata' ), 'error' );
			$is_valid = false;
		}

		return parent::validate_check_fields( $is_valid );
	}


	/** Admin methods *********************************************************/


	/**
	 * Get the default payment method title
	 *
	 * @since 4.0.0
	 * @return string payment method title to show on checkout
	 */
	protected function get_default_title() {

		return __( 'TeleCheck', 'woocommerce-gateway-firstdata' );
	}


	/** Gateway methods *******************************************************/


	/**
	 * Get the order, adds Payeezy Telecheck specific info to the order:
	 *
	 * + payment->check_type - either "P" (personal check) or "C" (corporate check)
	 *
	 * @since 4.0.0
	 * @see Framework\SV_WC_Payment_Gateway_Direct::get_order()
	 * @param int|\WC_Order $order_id
	 * @return \WC_Order
	 */
	public function get_order( $order_id ) {

		$order = parent::get_order( $order_id );

		$order->payment->check_type = ( 'corporate' === Framework\SV_WC_Helper::get_posted_value( 'wc-' . $this->get_id_dasherized() . '-check-type' ) ) ? 'C' : 'P';

		$order->payment->customer_id_number = Framework\SV_WC_Helper::get_posted_value( 'wc-' . $this->get_id_dasherized() . '-customer-id-number' );
		$order->payment->customer_id_type   = Framework\SV_WC_Helper::get_posted_value( 'wc-' . $this->get_id_dasherized() . '-customer-id-type' );

		return $order;
	}


}
