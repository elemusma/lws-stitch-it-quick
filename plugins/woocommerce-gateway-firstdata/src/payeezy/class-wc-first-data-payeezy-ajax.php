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
 * Payeezy JS AJAX class.
 *
 * @since 4.1.8
 */
class WC_First_Data_Payeezy_AJAX {


	/** @var \WC_Gateway_First_Data_Payeezy the Payeezy JS gateway instance */
	protected $gateway;


	/**
	 * Constructs the class.
	 *
	 * @since 4.1.8
	 *
	 * @param \WC_Gateway_First_Data_Payeezy $gateway the gateway instance
	 */
	public function __construct( \WC_Gateway_First_Data_Payeezy $gateway ) {

		$this->gateway = $gateway;

		add_action( 'wp_ajax_wc_' . $this->get_gateway()->get_id() . '_log_js_data',        array( $this, 'log_js_data' ) );
		add_action( 'wp_ajax_nopriv_wc_' . $this->get_gateway()->get_id() . '_log_js_data', array( $this, 'log_js_data' ) );

		// generate Payment.JS client token
		add_action( 'wp_ajax_wc_first_data_payeezy_payment_js_generate_client_token',        [ $this, 'generate_payment_js_client_token' ] );
		add_action( 'wp_ajax_nopriv_wc_first_data_payeezy_payment_js_generate_client_token', [ $this, 'generate_payment_js_client_token' ] );
	}


	/**
	 * Generates a client token for Payment.JS.
	 *
	 * @internal
	 *
	 * @since 4.7.0
	 */
	public function generate_payment_js_client_token() {

		check_ajax_referer( 'generate-payment-js-client-token', 'security' );

		try {

			$response = $this->get_gateway()->get_payment_js_api()->authorize_session();

			if ( ! $response->get_client_token() ) {
				throw new Framework\SV_WC_API_Exception( 'Client token is missing' );
			}

			if ( ! $response->get_nonce() ) {
				throw new Framework\SV_WC_API_Exception( 'Nonce is missing' );
			}

			if ( ! $response->get_public_key() ) {
				throw new Framework\SV_WC_API_Exception( 'Public key is missing' );
			}

			$order_id = Framework\SV_WC_Helper::get_posted_value( 'order', 0 );
			$object   = $order_id > 0 ? wc_get_order( $order_id ) : null;

			// no order found
			if ( ! $object instanceof \WC_Order ) {

				// if some sort of ID was passed, we can stop here
				if ( $order_id ) {
					throw new Framework\SV_WC_API_Exception( 'Order not found' );
				}

				// otherwise, try and get the current customer object
				try {

					$user_id = get_current_user_id();

					if ( ! empty( $user_id ) ) {
						$object = new \WC_Customer( $user_id );
					}

				} catch ( \Exception $exception ) {}
			}

			// one final check for a real object
			if ( ! $object instanceof \WC_Data ) {
				throw new Framework\SV_WC_API_Exception( 'Session data could not be stored' );
			}

			// store session authorization data to the object for later retrieval and checks
			$object->update_meta_data( \WC_Gateway_First_Data_Payeezy_Credit_Card::PAYMENT_JS_CLIENT_TOKEN_META, wc_clean( $response->get_client_token() ) );
			$object->update_meta_data( \WC_Gateway_First_Data_Payeezy_Credit_Card::PAYMENT_JS_NONCE_META, wc_clean( $response->get_nonce() ) );

			/**
			 * Stores the payment method, so that the order can be retrieved later.
			 *
			 * @see WC_Gateway_First_Data_Payeezy_Credit_Card::get_order_from_tokenization_response()
			 */
			$object->update_meta_data( '_payment_method', $this->get_gateway()->get_id() );

			$object->save_meta_data();

			wp_send_json_success( [
				'clientToken'     => $response->get_client_token(),
				'publicKeyBase64' => $response->get_public_key(),
			] );

		} catch ( Framework\SV_WC_API_Exception $e ) {

			wp_send_json_error( sprintf(
				/* translators: Placeholder: %s - error message */
				__( 'Authorization error: %s', 'woocommerce-gateway-firstdata' ),
				$e->getMessage()
			) );
		}
	}


	/**
	 * Writes card tokenization JS request/response data to the standard debug log.
	 *
	 * @internal
	 *
	 * @since 4.1.8
	 */
	public function log_js_data() {

		check_ajax_referer( 'wc_' . $this->get_gateway()->get_id() . '_log_js_data', 'security' );

		if ( ! empty( $_REQUEST['data'] ) ) {

			$message = sprintf( "FDToken %1\$s\n%1\$s Body: ", ! empty( $_REQUEST['type'] ) ? ucfirst( $_REQUEST['type'] ) : 'Request' );

			// add the data
			$message .= print_r( $_REQUEST['data'], true );

			$this->get_gateway()->add_debug_message( $message );
		}

		wp_send_json_success();
	}


	/**
	 * Gets the gateway instance.
	 *
	 * @since 4.1.8
	 *
	 * @return \WC_Gateway_First_Data_Payeezy
	 */
	public function get_gateway() {

		return $this->gateway;
	}


}
