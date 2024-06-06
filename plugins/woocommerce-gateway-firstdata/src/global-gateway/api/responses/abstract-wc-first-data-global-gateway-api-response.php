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

use SkyVerge\WooCommerce\PluginFramework\v5_12_4 as Framework;

/**
 * Global Gateway API Request Base Class
 *
 * Handles common functionality for request classes
 *
 * @since 4.0.0
 */
abstract class WC_First_Data_Global_Gateway_API_Response extends Framework\SV_WC_API_XML_Response implements Framework\SV_WC_Payment_Gateway_API_Response {


	/** @var \WC_First_Data_Global_Gateway_API_Transaction_Request */
	protected $request;


	/**
	 * Setup the class
	 *
	 * @since 4.0.0
	 * @param string $raw_xml RAW XML response
	 */
	public function __construct( $raw_xml ) {

		// response doesn't include a root element, set one so it can be parsed by SimpleXML
		$raw_xml = '<response>' . $raw_xml . '</response>';

		parent::__construct( $raw_xml );
	}


}
