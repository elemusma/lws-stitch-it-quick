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

namespace Kestrel\WooCommerce\First_Data\Payeezy_Gateway;

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\PluginFramework\v5_12_4 as Framework;

/**
 * The payment form handler class.
 *
 * @since 4.7.3
 */
class Payment_Form extends Framework\SV_WC_Payment_Gateway_Payment_Form {


	/**
	 * Adds the action & filter hooks.
	 *
	 * @since 4.7.3
	 */
	protected function add_hooks() {

		// enqueues the payment form assets
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );

		parent::add_hooks();
	}


	/**
	 * Enqueues the payment form assets.
	 *
	 * @since 4.7.3
	 */
	public function enqueue_assets() {

		$plugin = $this->get_gateway()->get_plugin();

		wp_enqueue_script(
			'wc-first-data-payeezy-gateway-payment-form',
			$plugin->get_plugin_url() . '/assets/js/frontend/wc-first-data-payeezy-gateway-payment-form.min.js',
			[ 'sv-wc-payment-gateway-payment-form-v5_12_4' ],
			$plugin->get_version()
		);
	}


	/**
	 * Gets the payment form JS handler name.
	 *
	 * @since 4.7.3
	 *
	 * @return string
	 */
	protected function get_js_handler_class_name() {

		return 'WC_First_Data_Payeezy_Gateway_Payment_Form_Handler';
	}


}
