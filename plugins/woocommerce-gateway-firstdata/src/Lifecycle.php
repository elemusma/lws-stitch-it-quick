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

namespace Atreus\WooCommerce\First_Data;

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\PluginFramework\v5_11_12 as Framework;

/**
 * Plugin lifecycle handler.
 *
 * @since 4.4.0
 *
 * @method \WC_First_Data get_plugin()
 */
class Lifecycle extends Framework\Plugin\Lifecycle {


	/**
	 * Runs every time. Used since the activation hook is not executed when updating a plugin.
	 *
	 * @since 4.4.0
	 */
	protected function install() {

		update_option( 'wc_first_data_active_gateway', \WC_First_Data::CLOVER_CREDIT_CARD_GATEWAY_ID );

		// handle upgrades from pre v4.0.0 versions, as the plugin ID changed then
		// and the upgrade routine won't be triggered automatically
		if ( $old_version = get_option( 'wc_firstdata_version' ) ) {

			$this->upgrade( $old_version );
		}
	}


	/**
	 * Runs when the plugin version number changes.
	 *
	 * @since 4.4.0
	 *
	 * @param string $installed_version the currently installed version
	 */
	protected function upgrade( $installed_version ) {

		// upgrade to v3.0.0
		$settings = get_option( 'woocommerce_firstdata-global-gateway_settings' );

		if ( ! $installed_version && $settings ) {
			// upgrading from the pre-rewrite version, need to adjust the settings array

			if ( isset( $settings['pemfile'] ) ) {
				// Global Gateway: the new global gateway id is firstdata-global-gateway, so
				//  we'll make that change and set the Global Gateway as the active version
				//  for a seamless "upgrade" from the previous standalone Global Gateway plugin

				// sandbox -> environment
				if ( isset( $settings['sandbox'] ) && 'yes' === $settings['sandbox'] ) {
					$settings['environment'] = 'sandbox';
				} else {
					$settings['environment'] = 'production';
				}
				unset( $settings['sandbox'] );

				// rename the settings option
				delete_option( 'woocommerce_firstdata-global-gateway_settings' );
				update_option( 'woocommerce_firstdata_settings', $settings );

				// Make the Global Gateway version active
				update_option( 'wc_firstdata_gateway', 'WC_Gateway_FirstData_Global_Gateway' );

			} else {
				// GGe4

				// logger -> debug_mode
				if ( ! isset( $settings['logger'] ) || 'no' === $settings['logger'] ) {
					$settings['debug_mode'] = 'off';
				} elseif ( isset( $settings['logger'] ) && 'yes' === $settings['logger'] ) {
					$settings['debug_mode'] = 'log';
				}
				unset( $settings['logger'] );

				// set demo fields
				if ( isset( $settings['environment'] ) && 'demo' === $settings['environment'] ) {
					$settings['demo_gateway_id']       = $settings['gateway_id'];
					$settings['demo_gateway_password'] = $settings['gateway_password'];

					$settings['gateway_id']       = '';
					$settings['gateway_password'] = '';
				}

				// set the updated options array
				update_option( 'woocommerce_firstdata_settings', $settings );
			}
		}

		// upgrade to v3.1.1
		if ( -1 === version_compare( $installed_version, '3.1.1' ) && $settings ) {

			// standardize transaction type setting: '00' => 'purchase', '01' => 'authorization'
			if ( isset( $settings['transaction_type'] ) ) {
				if ( '01' == $settings['transaction_type'] ) {
					$settings['transaction_type'] = 'authorization';
				} else {
					$settings['transaction_type'] = 'charge';
				}
			}

			// set the updated options array
			update_option( 'woocommerce_firstdata_settings', $settings );

		}


		// upgrade to v4.0.0
		if ( version_compare( $installed_version, '4.0.0', '<' ) ) {
			global $wpdb;
			$meta_table = Framework\SV_WC_Plugin_Compatibility::is_hpos_enabled() ? OrdersTableDataStore::get_meta_table_name() : $wpdb->postmeta;

			$this->get_plugin()->log( 'Starting upgrade to v4.0.0' );

			if ( 'WC_Gateway_FirstData_Global_Gateway' === get_option( 'wc_firstdata_gateway' ) ) {

				/** Upgrade Global Gateway */
				$this->get_plugin()->log( 'Starting Global Gateway upgrade.' );


				// update switcher option
				update_option( 'wc_first_data_active_gateway', \WC_First_Data::GLOBAL_GATEWAY_ID );
				delete_option( 'wc_firstdata_gateway' );

				$old_settings = get_option( 'woocommerce_firstdata-global-gateway_settings' );

				if ( $old_settings ) {

					$new_settings = [
						'enabled'               => ( isset( $old_settings['enabled'] ) && 'yes' === $old_settings['enabled'] ) ? 'yes' : 'no',
						'title'                 => ( ! empty( $old_settings['title'] ) ) ? $old_settings['title'] : __( 'Credit Card', 'woocommerce-gateway-firstdata' ),
						'description'           => ( ! empty( $old_settings['description'] ) ) ? $old_settings['description'] : __( 'Pay securely using your credit card.', 'woocommerce-gateway-firstdata' ),
						'enable_csc'            => 'yes',
						'transaction_type'      => 'charge',
						'card_types'            => [ 'VISA', 'MC', 'AMEX', 'DISC' ],
						'debug_mode'            => 'off',
						'environment'           => ( isset( $old_settings['environment'] ) && 'sandbox' === $old_settings['environment'] ) ? 'staging' : 'production',
						'store_number'          => ( ! empty( $old_settings['storenum'] ) ) ? $old_settings['storenum'] : '',
						'pem_file_path'         => ( ! empty( $old_settings['pemfile'] ) ) ? $old_settings['pemfile'] : '',
						'staging_store_number'  => ( isset( $old_settings['environment'] ) && 'sandbox' === $old_settings['environment'] && ! empty( $old_settings['storenum'] ) ) ? $old_settings['storenum'] : '',
						'staging_pem_file_path' => ( isset( $old_settings['environment'] ) && 'sandbox' === $old_settings['environment'] && ! empty( $old_settings['pemfile'] ) ) ? $old_settings['pemfile'] : '',
					];

					// save new settings, remove old ones
					update_option( 'woocommerce_first_data_global_gateway_settings', $new_settings );
					delete_option( 'woocommerce_firstdata-global-gateway_settings' );

					$this->get_plugin()->log( 'Settings upgraded.' );
				}

			} else {

				/** Upgrade GGe4/Payeezy Gateway */
				$this->get_plugin()->log( 'Starting Payeezy Gateway upgrade.' );

				// remote old switcher option
				delete_option( 'wc_firstdata_gateway' );

				$old_settings = get_option( 'woocommerce_firstdata_settings' );

				if ( $old_settings ) {

					$new_settings = [
						'enabled'                               => ( isset( $old_settings['enabled'] ) && 'yes' === $old_settings['enabled'] ) ? 'yes' : 'no',
						'title'                                 => ( ! empty( $old_settings['title'] ) ) ? $old_settings['title'] : __( 'Credit Card', 'woocommerce-gateway-firstdata' ),
						'description'                           => ( ! empty( $old_settings['description'] ) ) ? $old_settings['description'] : __( 'Pay securely using your credit card.', 'woocommerce-gateway-firstdata' ),
						'enable_csc'                            => 'yes', // old version required it by default, with no option to disable
						'transaction_type'                      => ( isset( $old_settings['transaction_type'] ) && 'authorization' === $old_settings['transaction_type'] ) ? 'authorization' : 'charge',
						'partial_redemption'                    => ( isset( $old_settings['partial_redemption'] ) && 'yes' === $old_settings['partial_redemption'] ) ? 'yes' : 'no',
						'card_types'                            => ( isset( $old_settings['card_types'] ) && is_array( $old_settings['card_types'] ) ) ? $old_settings['card_types'] : [ 'VISA', 'MC', 'AMEX', 'DISC', ],
						'tokenization'                          => ( isset( $old_settings['tokenization'] ) && 'yes' === $old_settings['tokenization'] ) ? 'yes' : 'no',
						'enable_customer_decline_messages'      => 'no',
						'debug_mode'                            => ( ! empty( $old_settings['debug_mode'] ) ) ? $old_settings['debug_mode'] : 'off',
						'environment'                           => ( isset( $old_settings['environment'] ) && 'demo' === $old_settings['environment'] ) ? 'demo' : 'production',
						'inherit_settings'                      => 'no',
						'gateway_id'                            => ( ! empty( $old_settings['gateway_id'] ) ) ? $old_settings['gateway_id'] : '',
						'gateway_password'                      => ( ! empty( $old_settings['gateway_password'] ) ) ? $old_settings['gateway_password'] : '',
						'key_id'                                => ( ! empty( $old_settings['key_id'] ) ) ? $old_settings['key_id'] : '',
						'hmac_key'                              => ( ! empty( $old_settings['hmac_key'] ) ) ? $old_settings['hmac_key'] : '',
						'demo_gateway_id'                       => ( ! empty( $old_settings['demo_gateway_id'] ) ) ? $old_settings['demo_gateway_id'] : '',
						'demo_gateway_password'                 => ( ! empty( $old_settings['demo_gateway_password'] ) ) ? $old_settings['demo_gateway_password'] : '',
						'demo_key_id'                           => ( ! empty( $old_settings['demo_key_id'] ) ) ? $old_settings['demo_key_id'] : '',
						'demo_hmac_key'                         => ( ! empty( $old_settings['demo_hmac_key'] ) ) ? $old_settings['demo_hmac_key'] : '',
						'soft_descriptors_enabled'              => ( isset( $old_settings['soft_descriptors_enabled'] ) && 'yes' === $old_settings['soft_descriptors_enabled'] ) ? 'yes' : 'no',
						'soft_descriptor_dba_name'              => ( ! empty( $old_settings['soft_descriptor_dba_name'] ) ) ? $old_settings['soft_descriptor_dba_name'] : '',
						'soft_descriptor_street'                => ( ! empty( $old_settings['soft_descriptor_street'] ) ) ? $old_settings['soft_descriptor_street'] : '',
						'soft_descriptor_city'                  => ( ! empty( $old_settings['soft_descriptor_city'] ) ) ? $old_settings['soft_descriptor_city'] : '',
						'soft_descriptor_region'                => ( ! empty( $old_settings['soft_descriptor_region'] ) ) ? $old_settings['soft_descriptor_region'] : '',
						'soft_descriptor_postal_code'           => ( ! empty( $old_settings['soft_descriptor_postal_code'] ) ) ? $old_settings['soft_descriptor_postal_code'] : '',
						'soft_descriptor_country_code'          => ( ! empty( $old_settings['soft_descriptor_country_code'] ) ) ? $old_settings['soft_descriptor_country_code'] : '',
						'soft_descriptor_mid'                   => ( ! empty( $old_settings['soft_descriptor_mid'] ) ) ? $old_settings['soft_descriptor_mid'] : '',
						'soft_descriptor_mcc'                   => ( ! empty( $old_settings['soft_descriptor_mcc'] ) ) ? $old_settings['soft_descriptor_mcc'] : '',
						'soft_descriptor_merchant_contact_info' => ( ! empty( $old_settings['soft_descriptor_merchant_contact_info'] ) ) ? $old_settings['soft_descriptor_merchant_contact_info'] : '',
					];

					// save new settings, remove old ones
					update_option( 'woocommerce_first_data_payeezy_gateway_credit_card_settings', $new_settings );
					delete_option( 'woocommerce_firstdata_settings' );

					$this->get_plugin()->log( 'Settings upgraded.' );
				}

				/** Update meta values for order payment method & recurring payment method */

				// meta key: _payment_method
				// old value: firstdata
				// new value: first_data_payeezy_gateway_credit_card
				$rows = $wpdb->update( $meta_table, [ 'meta_value' => 'first_data_payeezy_gateway_credit_card' ], [ 'meta_key' => '_payment_method', 'meta_value' => 'firstdata' ] );

				$this->get_plugin()->log( sprintf( '%d orders updated for payment method meta', $rows ) );

				// meta key: _recurring_payment_method
				// old value: firstdata
				// new value: first_data_payeezy_gateway_credit_card
				$rows = $wpdb->update( $meta_table, [ 'meta_value' => 'first_data_payeezy_gateway_credit_card' ], [ 'meta_key' => '_recurring_payment_method', 'meta_value' => 'firstdata' ] );

				$this->get_plugin()->log( sprintf( '%d orders updated for recurring payment method meta', $rows ) );

				/** Convert tokens stored in legacy format to framework payment token format */

				$this->get_plugin()->log( 'Starting legacy token upgrade.' );

				$user_ids = $wpdb->get_col( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = '_wc_firstdata_credit_card_tokens'" );

				if ( $user_ids ) {

					// iterate through each user with tokens
					foreach ( $user_ids as $user_id ) {

						$old_tokens = get_user_meta( $user_id, '_wc_firstdata_credit_card_tokens', true );

						$new_tokens = [];

						// iterate through each token
						foreach ( $old_tokens as $token_id => $token ) {

							// sanity check
							if ( ! $token_id || empty( $token ) ) {
								continue;
							}

							// parse expiry date
							if ( ! empty( $token['exp_date'] ) && 4 === strlen( $token['exp_date'] ) ) {
								$exp_month = substr( $token['exp_date'], 0, 2 );
								$exp_year  = substr( $token['exp_date'], 2, 2 );
							} else {
								$exp_month = $exp_year = '';
							}

							// parse card type
							switch ( $token['type'] ) {
								case 'Visa':             $card_type = 'visa';     break;
								case 'American Express': $card_type = 'amex';     break;
								case 'Mastercard':       $card_type = 'mc';       break;
								case 'Discover':         $card_type = 'discover'; break;
								case 'Diners Club':      $card_type = 'diners';   break;
								case 'JCB':              $card_type = 'jcb';      break;
								default:                 $card_type = '';
							}

							// setup new token
							$new_tokens[ $token_id ] = [
								'type'      => 'credit_card',
								'last_four' => ! empty( $token['last_four'] ) ? $token['last_four'] : '',
								'card_type' => $card_type,
								'exp_month' => $exp_month,
								'exp_year'  => $exp_year,
								'default'   => ( ! empty( $token['active'] ) && $token['active'] ),
							];
						}

						// save new tokens
						if ( ! empty( $new_tokens ) ) {
							update_user_meta( $user_id, '_wc_first_data_payeezy_gateway_credit_card_payment_tokens', $new_tokens );
						}

						// save the legacy tokens in case we need them later
						// TODO: the legacy tokens can be removed in future version, say September 2016 @MR 2016-02-10
						update_user_meta( $user_id, '_wc_first_data_payeezy_gateway_legacy_payment_tokens', $old_tokens );
						delete_user_meta( $user_id, '_wc_firstdata_credit_card_tokens' );

						$this->get_plugin()->log( sprintf( 'Converted legacy payment tokens for user ID: %d', absint( $user_id ) ) ) ;
					}

					$this->get_plugin()->log( 'Completed legacy payment token upgrade.' );
				}
			}

			$this->get_plugin()->log( 'Completed upgrade for v4.0.0' );
		}

		// upgrade to v4.7.0
		if ( version_compare( $installed_version, '4.7.0', '<' ) ) {

			// set a flag to display the legacy Payeezy.JS settings for existing users
			update_option( 'wc_first_data_payeezy_display_payeezy_js_settings', 'yes' );
		}

		// TODO: update these routines to the method handling of the latest FW {CW 2020-02-13}
	}

}
