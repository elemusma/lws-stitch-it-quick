<?php

namespace Barn2\Plugin\WC_Product_Options\Admin\Wizard\Steps;

use Barn2\Plugin\WC_Product_Options\Dependencies\Setup_Wizard\Steps\Cross_Selling;

/**
 * Upsell Step.
 *
 * @package   Barn2\woocommerce-product-options
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Upsell extends Cross_Selling {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->set_name( esc_html__( 'More', 'woocommerce-product-options' ) );
		$this->set_title( esc_html__( 'Extra features', 'woocommerce-product-options' ) );
		$this->set_description( __( 'Enhance your store with these fantastic plugins from Barn2.', 'woocommerce-product-options' ) );
	}
}
