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

namespace Atreus\WooCommerce\First_Data\Clover;

use SkyVerge\WooCommerce\PluginFramework\v5_11_12 as Framework;

defined( 'ABSPATH' ) or exit;


/**
 * Handle the payment tokenization related functionality.
 *
 * @since 5.0.0
 */
class Payment_Tokens_Handler extends Framework\SV_WC_Payment_Gateway_Payment_Tokens_Handler {


	/**
	 * Builds the token object.
	 *
	 * @since 5.0.0
	 * @see Framework\SV_WC_Payment_Gateway_Payment_Tokens_Handler::payment_token_deleted()
	 *
	 * @param string $token payment token
	 * @param \WC_Payment_Token|array $data {
	 *     Payment token data.
	 *
	 *     @type bool   $default   Optional. Indicates this is the default payment token
	 *     @type string $type      Payment type. Either 'credit_card' or 'check'
	 *     @type string $last_four Last four digits of account number
	 *     @type string $card_type Credit card type (`visa`, `mc`, `amex`, `disc`, `diners`, `jcb`) or `echeck`
	 *     @type string $exp_month Optional. Expiration month (credit card only)
	 *     @type string $exp_year  Optional. Expiration year (credit card only)
	 * }
	 * @return SV_WC_Payment_Gateway_Payment_Token payment token
	 */
	public function build_token( $token, $data ) {

		return new PaymentToken( $token, $data );
	}


	/**
	 * Deletes a credit card token from user meta
	 *
	 * Note: the whole reason this method is duplicated is just so that we can redefine and call our own private remove_token_from_gateway() {JS: 2022-11-20}
	 *
	 * @since 5.0.0
	 * @see Framework\SV_WC_Payment_Gateway_Payment_Tokens_Handler::payment_token_deleted()
	 *
	 * @param int $user_id user identifier
	 * @param SV_WC_Payment_Gateway_Payment_Token|string $token the payment token to delete
	 * @param string|null $environment_id optional environment id, defaults to plugin current environment
	 * @return bool|int false if not deleted, updated user meta ID if deleted
	 */
	public function remove_token( $user_id, $token, $environment_id = null ) {

		// default to current environment
		if ( is_null( $environment_id ) ) {
			$environment_id = $this->get_environment_id();
		}

		// unknown token?
		if ( ! $this->user_has_token( $user_id, $token, $environment_id ) ) {
			return false;
		}

		// get the payment token object as needed
		if ( ! is_object( $token ) ) {
			$token = $this->get_token( $user_id, $token, $environment_id );
		}

		// for direct gateways that allow it, attempt to delete the token from the endpoint
		if ( $this->get_gateway()->get_api()->supports_remove_tokenized_payment_method() ) {

			if ( ! $this->remove_token_from_gateway( $user_id, $environment_id, $token ) ) {
				return false;
			}
		}

		return $this->delete_token( $user_id, $token );
	}


	/**
	 * Deletes remote token data and legacy token data when the corresponding core token is deleted.
	 *
	 * Note: the whole reason this method is duplicated is just so that we can redefine and call our own private remove_token_from_gateway() {JS: 2022-11-20}
	 *
	 * @internal
	 *
	 * @since 5.0.0
	 * @see Framework\SV_WC_Payment_Gateway_Payment_Tokens_Handlerpayment_token_deleted()
	 *
	 * @param int $token_id the ID of a core token
	 * @param \WC_Payment_Token $core_token the core token object
	 */
	public function payment_token_deleted( $token_id, $core_token ) {

		if ( $this->get_gateway()->get_id() === $core_token->get_gateway_id() ) {

			$token = $this->build_token( $core_token->get_token(), $core_token );

			$user_id        = $token->get_user_id();
			$environment_id = $token->get_environment();

			// for direct gateways that allow it, attempt to delete the token from the endpoint
			if ( ! $this->get_gateway()->get_api()->supports_remove_tokenized_payment_method() || $this->remove_token_from_gateway( $user_id, $environment_id, $token ) ) {

				// clear tokens transient
				$this->clear_transient( $user_id );

				// delete token from local cache
				unset( $this->tokens[ $environment_id ][ $user_id ][ $token->get_id() ] );

				// delete the legacy token data now that the token has been removed
				$this->delete_legacy_token( $user_id, $token, $environment_id );
			}
		}
	}



	/**
	 * Removes a tokenized payment method using the gateway's API.
	 *
	 * Note: The whole reason this method is overridden is just so that we can pass the full
	 * token object into the remove_tokenized_payment_method() rather than the token string only
	 * It's really unfortunate a) that the framework doesn't already do this, and b) that this
	 * method happens to be declared private meaning the two preceeding methods had to also
	 * be copied in. The primary issue with Clover is that the identifier for the token that's
	 * used to issue issue a charge is different from the ID that's used to delete the token
	 * over the API (card_id) {JS: 2022-11-20}
	 *
	 * Returns true if the token's local data should be removed.
	 *
	 * @since 5.0.0
	 * @see Framework\SV_WC_Payment_Gateway_Payment_Tokens_Handler::remove_token_from_gateway()
	 *
	 * @param int $user_id user identifier
	 * @param string $environment_id environment id
	 * @param SV_WC_Payment_Gateway_Payment_Token $token the payment token to remove
	 * @return bool
	 */
	private function remove_token_from_gateway( $user_id, $environment_id, $token ) {

		// remove a token's local data unless an exception occurs or we choose to keep loca data based on the API response
		$remove_local_data = true;

		try {

			$response = $this->get_gateway()->get_api()->remove_tokenized_payment_method( $token, $this->get_gateway()->get_customer_id( $user_id, array( 'environment_id' => $environment_id ) ) );

			if ( ! $response->transaction_approved() && ! $this->should_delete_token( $token, $response ) ) {
				$remove_local_data = false;
			}

		} catch( SV_WC_Plugin_Exception $e ) {

			if ( $this->get_gateway()->debug_log() ) {
				$this->get_gateway()->get_plugin()->log( $e->getMessage(), $this->get_gateway()->get_id() );
			}

			$remove_local_data = false;
		}

		return $remove_local_data;
	}
}
