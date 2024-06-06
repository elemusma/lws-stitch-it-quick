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


use SkyVerge\WooCommerce\PluginFramework\v5_12_4 as Framework;

/**
 * WooCommerce Blocks handler.
 *
 * @since 5.2.0
 */
class Gateway_Blocks_Handler extends Framework\Payment_Gateway\Blocks\Gateway_Blocks_Handler {


	/**
	 * Determines if a page contains a checkout shortcode.
	 * Overridden to provide FSE-theme support.
	 * TODO: update the SV Framework {JS - 2024-05-14}
	 *
	 * @since 5.2.0
	 * @see Framework\Payment_Gateway\Blocks\Gateway_Blocks_Handler::page_contains_checkout_shortcode()
	 *
	 * @param int|string|WP_Post $page
	 * @return bool
	 */
	public static function page_contains_checkout_shortcode( $page ) : bool {

		// check for shortcode in a non-FSE theme or shortcode in FSE-theme
		return static::page_contains_shortcode( '[woocommerce_checkout]', $page ) || static::page_contains_shortcode( '<!-- wp:woocommerce/classic-shortcode {"shortcode":"checkout"} /-->', $page );
	}


	/**
	 * Adds admin notices pertaining the blocks integration.
	 *
	 * @internal overrides the default notices to encourage switching to Clover, the only gateway supporting blocks
	 *
	 * @since 5.2.0
	 * @see Framework\Payment_Gateway\Blocks\Gateway_Blocks_Handler::add_admin_notices()
	 */
	public function add_admin_notices() : void {

		$admin_notice_handler = $this->plugin->get_admin_notice_handler();

		if ( static::is_checkout_block_in_use() ) {

			if ( ! $this->is_checkout_block_compatible() ) {

				$url = get_edit_post_link( wc_get_page_id( 'checkout' ) );
				$cta = '<a href="' . esc_url( $url ) .'" id="' . esc_attr( sprintf( '%s-restore-cart-shortcode', $this->plugin->get_id() ) ) . '" class="button button-primary">' . _x( 'Edit the Checkout Page', 'Button label', 'woocommerce-plugin-framework' ) . '</a>';

				$admin_notice_handler->add_admin_notice(
					sprintf(
						/* translators: Context: WordPress blocks and shortcodes. Placeholders: %1$s - Plugin name, %2$s - opening HTML <a> tag, %3$s - closing HTML </a> tag, %4$s - opening HTML <a> tag, %5$s - `[woocommerce_checkout]` shortcode tag, %6$s - closing HTML </a> tag */
						__( '%1$s is not yet compatible with the Checkout block. We recommend %2$sfollowing this guide%3$s to revert to the %4$s%5$s shortcode%6$s, or switch to the Clover gateway which is compatible.', 'woocommerce-plugin-framework' ),
						'<strong>' . $this->plugin->get_gateway()->get_method_title() . '</strong>',
						'<a href="https://woo.com/document/cart-checkout-blocks-status/#section-6" target="_blank">',
						'</a>',
						'<a href="https://woo.com/document/woocommerce-shortcodes/#checkout" target="_blank">',
						'<code>[woocommerce_checkout]</code>',
						'</a>',
					) . '<br><br>' . $cta,
					sprintf( '%s-checkout-block-not-compatible', $this->plugin->get_id_dasherized() ),
					[
						'notice_class'            => 'notice-error',
						'always_show_on_settings' => false,
					]
				);

			}
		}

		if ( static::is_cart_block_in_use() ) {

			if ( ! $this->is_cart_block_compatible() ) {

				$url = get_edit_post_link( wc_get_page_id( 'cart' ) );
				$cta = '<a href="' . esc_url( $url ) . '" id="' . esc_attr( sprintf( '%s-restore-cart-shortcode', $this->plugin->get_id() ) ) . '" class="button button-primary">' . _x( 'Edit the Cart Page', 'Button label', 'woocommerce-plugin-framework' ) . '</a>';

				$admin_notice_handler->add_admin_notice(
					sprintf(
						/* translators: Context: WordPress blocks and shortcodes. Placeholders: %1$s - Plugin name, %2$s - opening HTML <a> tag, %3$s - closing HTML </a> tag, %4$s - opening HTML <a> tag, %5$s - `[woocommerce_cart]` shortcode tag, %6$s - closing HTML </a> tag */
						__( '%1$s is not yet compatible with the Cart block. We recommend %2$sfollowing this guide%3$s to revert to the %4$s%5$s shortcode%6$s, or switch to the Clover gateway which is compatible.', 'woocommerce-plugin-framework' ),
						'<strong>' . $this->plugin->get_gateway()->get_method_title() . '</strong>',
						'<a href="https://woo.com/document/cart-checkout-blocks-status/#section-6" target="_blank">',
						'</a>',
						'<a href="https://woo.com/document/woocommerce-shortcodes/#cart" target="_blank">',
						'<code>[woocommerce_cart]</code>',
						'</a>',
					) . '<br><br>' . $cta,
					sprintf( '%s-cart-block-not-compatible', $this->plugin->get_id_dasherized() ),
					[
						'notice_class'            => 'notice-error',
						'always_show_on_settings' => false,
					]
				);

			}
		}
	}


}
