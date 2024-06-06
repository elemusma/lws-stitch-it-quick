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

namespace Kestrel\WooCommerce\First_Data\Clover;

use SkyVerge\WooCommerce\PluginFramework\v5_12_4 as Framework;

defined( 'ABSPATH' ) or exit;

/**
 * Clover Payment Gateway Token
 *
 * @since 5.0.0
 */
class PaymentToken extends Framework\SV_WC_Payment_Gateway_Payment_Token {


	/**
	 * Gets the Clover card id
	 *
	 * @since 5.0.0
	 *
	 * @return string payment token string
	 */
	public function get_card_id() {

		return $this->data['card_id'];
	}


}
