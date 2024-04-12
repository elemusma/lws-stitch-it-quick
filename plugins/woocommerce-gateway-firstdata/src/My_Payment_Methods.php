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
 * The My Payment Methods handler class.
 *
 * @since 4.7.3
 */
class My_Payment_Methods extends Framework\SV_WC_Payment_Gateway_My_Payment_Methods {


	/**
	 * Maybe enqueues the styles and scripts.
	 *
	 * Only enqueue if there are payment methods to manage.
	 *
	 * @since 4.7.3
	 */
	public function maybe_enqueue_styles_scripts() {

		parent::maybe_enqueue_styles_scripts();

		if ( $this->has_tokens ) {
			wp_enqueue_script( 'wc-first-data-my-payment-methods', $this->get_plugin()->get_plugin_url() . '/assets/js/frontend/wc-first-data-my-payment-methods.min.js', [ 'sv-wc-payment-gateway-my-payment-methods-v5_11_12' ], $this->get_plugin()->get_version() );
		}
	}


	/**
	 * Gets the JS handler class name.
	 *
	 * @since 4.7.3
	 *
	 * @return string
	 */
	protected function get_js_handler_class_name() {

		return 'WC_First_Data_My_Payment_Methods_Handler';
	}


}
