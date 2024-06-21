<?php
/**
 * YITH Product Bundles compatibility.
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\ProductAddons
 */

defined( 'YITH_WCPB_PREMIUM' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WAPO_Product_Bundles_Compatibility' ) ) {
    /**
     * Compatibility Class
     *
     * @class   YITH_WAPO_Product_Bundles_Compatibility
     * @since   4.2.1
     */
    class YITH_WAPO_Product_Bundles_Compatibility {

        /**
         * Single instance of the class
         *
         * @var YITH_WAPO_Product_Bundles_Compatibility
         */
        protected static $instance;

        /**
         * Returns single instance of the class
         *
         * @return YITH_WAPO_Product_Bundles_Compatibility
         */
        public static function get_instance() {
            return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
        }

        /**
         * YITH_WAPO_Product_Bundles_Compatibility constructor
         */
        private function __construct() {
            add_filter( 'yith_wapo_display_edit_product_link', array( $this, 'display_edit_product_link' ), 10, 2 );
        }

        /**
         * Display or not the edit product link depending on product bundles.
         *
         * @param boolean $value The boolean value.
         * @param array $cart_item The cart item array.
         * @return false|mixed
         */
        public function display_edit_product_link( $value, $cart_item ) {

            $is_bundled = isset( $cart_item['bundled_by'] ) && isset( $cart_item['bundled_item_id'] );

            return $is_bundled ? false : $value;

        }

    }
}
