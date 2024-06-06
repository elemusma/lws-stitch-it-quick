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

namespace Kestrel\WooCommerce\First_Data\Payeezy\API\Request\PaymentJS;

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\PluginFramework\v5_12_4 as Framework;
use Kestrel\WooCommerce\First_Data\Payeezy\API\Request as Request;

/**
 * Payeezy API Transaction Request Class
 *
 * Handles credit card transaction requests
 *
 * @since 4.0.0
 */
class Authorize_Session extends Request\PaymentJS {


	/**
	 * Request constructor.
	 *
	 * Sets the path to "merchant/authorization".
	 *
	 * @since 4.7.0
	 */
	public function __construct() {

		parent::__construct();

		$this->path = 'merchant/authorize-session';
	}


	/**
	 * Sets request data to authorize a session.
	 *
	 * @since 4.7.0
	 */
	public function authorize_session() {

		$this->request_data = [
			'gateway'         => 'PAYEEZY',
			'apiKey'          => $this->get_gateway()->get_api_key(),
			'apiSecret'       => $this->get_gateway()->get_api_secret(),
			'authToken'       => $this->get_gateway()->get_merchant_token(),
			'transarmorToken' => $this->get_gateway()->get_transarmor_token(),
			'zeroDollarAuth'  => $this->get_gateway()->perform_pre_auth(),
		];
	}


}
