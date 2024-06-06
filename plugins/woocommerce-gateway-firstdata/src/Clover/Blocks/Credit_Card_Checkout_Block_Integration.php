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
 * to woosupport@Kestrel.io so we can send you a copy immediately.
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

namespace Kestrel\WooCommerce\First_Data\Clover\Blocks;

use Kestrel\WooCommerce\First_Data\Clover\Gateway\Credit_Card;
use SkyVerge\WooCommerce\PluginFramework\v5_12_4\Payment_Gateway\Blocks\Gateway_Checkout_Block_Integration;
use SkyVerge\WooCommerce\PluginFramework\v5_12_4\SV_WC_Payment_Gateway_Helper;
use SkyVerge\WooCommerce\PluginFramework\v5_12_4\SV_WC_Payment_Gateway;
use Kestrel\WooCommerce\First_Data\Clover\Gateway\Payment_Form;
use WC_HTTPS;

/**
 * Checkout block integration for the {@see Credit_Card} gateway.
 *
 * @since 5.2.0
 *
 * @property Credit_Card $gateway the gateway instance
 */
class Credit_Card_Checkout_Block_Integration extends Gateway_Checkout_Block_Integration {


	/**
	 * Gets the main script handle.
	 *
	 * @since 5.2.0
	 *
	 * @return string
	 */
	protected function get_main_script_handle() : string {

		/**
		 * Filters the block main script handle.
		 *
		 * @since 5.2.0
		 *
		 * @param string $handle
		 * @param Gateway_Checkout_Block_Integration $integration
		 */
		return (string) apply_filters( 'wc_' . $this->get_id() . '_'. $this->block_name . '_block_handle', sprintf(
			'wc-%s-%s-block',
			$this->gateway->get_id_dasherized(),
			$this->block_name
		), $this );
	}


	/**
	 * Gets the main script URL.
	 *
	 * @since 5.2.0
	 *
	 * @return string
	 */
	protected function get_main_script_url() : string {

		/**
		 * Filters the block main script URL.
		 *
		 * @since 5.2.0
		 *
		 * @param string $url
		 * @param Credit_Card_Checkout_Block_Integration $integration
		 */
		return (string) apply_filters( 'wc_' . $this->get_id() . '_' . $this->block_name . '_block_script_url', sprintf(
			'%s/wc-%s-%s-block.js',
			$this->plugin->get_plugin_url() . '/assets/js/blocks',
			$this->gateway->get_id_dasherized(),
			$this->block_name
		), $this );
	}


	/**
	 * Gets the main script stylesheet URL.
	 *
	 * @since 5.2.0
	 *
	 * @return string
	 *
	 */
	protected function get_main_script_stylesheet_url() : string {

		/**
		 * Filters the block main script stylesheet URL.
		 *
		 * @since 5.2.0
		 *
		 * @param string $url
		 * @param Credit_Card_Checkout_Block_Integration $integration
		 */
		return (string) apply_filters( 'wc_' . $this->get_id() . '_' . $this->block_name . '_block_stylesheet_url', sprintf(
			'%s/wc-%s-%s-block.css',
			$this->plugin->get_plugin_url() . '/assets/css/blocks',
			$this->gateway->get_id_dasherized(),
			$this->block_name
		), $this );
	}


	/**
	 * Gets a list of gateway logos as icon image URLs.
	 *
	 * If the gateway has a specific icon, it will return that item only.
	 * Otherwise, it will return a list of icon URLs for each card type supported by the gateway.
	 *
	 * @since 5.2.0
	 *
	 * @see Gateway_Checkout_Block_Integration::get_gateway_icons()
	 *
	 * @return array<string, string>
	 */
	protected function get_gateway_icons() : array {

		$icons = [];

		if ( $this->gateway->icon ) {

			$icons = [
				[
					'alt' => $this->gateway->get_method_title(),
					'src' => $this->gateway->icon,
					'id'  => $this->gateway->get_id(),
				]
			];

		} elseif ( $this->gateway->is_echeck_gateway() ) {

			$icons = [
				[
					'alt' => __( 'eCheck', 'woocommerce' ),
					'src' => $this->gateway->get_payment_method_image_url( 'echeck' ),
					'id'  => 'echeck',
				]
			];

		} elseif ( $card_types = $this->get_enabled_card_types() ) {

			foreach ( $card_types as $card_type ) {

				$card_type = SV_WC_Payment_Gateway_Helper::normalize_card_type( $card_type );
				$card_name = SV_WC_Payment_Gateway_Helper::payment_type_to_name( $card_type );

				if ( $url = $this->gateway->get_payment_method_image_url( $card_type ) ) {
					$icons[] = [
						'alt' => $card_name,
						'src' => WC_HTTPS::force_https_url( $url ),
						'id'  => $card_type,
					];
				}
			}
		}

		return $icons;
	}


	/**
	 * Adds payment method data.
	 *
	 * @see Gateway_Checkout_Block_Integration::get_payment_method_data()
	 *
	 * @internal
	 *
	 * @since 5.2.0
	 *
	 * @param array<string, mixed> $payment_method_data
	 * @param Credit_Card $gateway
	 * @return array<string, mixed>
	 */
	public function add_payment_method_data( array $payment_method_data, SV_WC_Payment_Gateway $gateway ) : array {

		return array_merge( [
			'hosted_field_styles'        => $gateway->get_hosted_element_styles(),
			'public_token'               => $gateway->get_public_token(),
			'locale'                     => get_locale(),
			'test_card_number_approval'  => Payment_Form::TEST_CARD_NUMBER_APPROVAL,
			'test_card_number_decline'   => Payment_Form::TEST_CARD_NUMBER_DECLINE,
		], $payment_method_data );
	}


	/**
	 * Returns any gateway flags from configuration (boolean values only).
	 *
	 * @since 5.2.0
	 * @see Gateway_Checkout_Block_Integration::get_gateway_flags()
	 *
	 * @return array<string, bool>
	 */
	protected function get_gateway_flags() : array {

		$gateway_flags = parent::get_gateway_flags();
		$gateway_flags['avs_street_address'] = $this->gateway->avs_street_address();

		return $gateway_flags;
	}


}
