<?php

namespace Barn2\Plugin\WC_Product_Options\Admin\Wizard\Steps;

use Barn2\Plugin\WC_Product_Options\Dependencies\Setup_Wizard\Steps\Ready;

/**
 * Completed Step.
 *
 * @package   Barn2\woocommerce-product-options
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Completed extends Ready {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->set_name( esc_html__( 'Ready', 'woocommerce-product-options' ) );
		$this->set_title( esc_html__( 'Complete Setup', 'woocommerce-product-options' ) );

		$this->set_description( $this->get_custom_description() );
	}

	/**
	 * Retrieves the description.
	 *
	 * @return string
	 */
	private function get_custom_description() {
		// $product_options_page =

		return esc_html__( 'Congratulations, you have finished setting up the plugin. Now it’s time to start adding options to your products.', 'woocommerce-product-options' );
	}

}
