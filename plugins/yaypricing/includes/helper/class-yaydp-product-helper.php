<?php
/**
 * YayPricing product helper
 *
 * @package YayPricing\Helper
 */

namespace YAYDP\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Declare class
 */
class YAYDP_Product_Helper {

	/**
	 * Check if the given product match with the filter
	 *
	 * @param \WC_Product $product Given product.
	 * @param array       $filter The product id filter.
	 *
	 * @return boolean
	 */
	public static function check_product( $product, $filter ) {
		$product_ids       = array();
		$product_parent_id = $product->get_parent_id();
		$product_ids[]     = $product->get_id();
		if ( ! empty( $product_parent_id ) ) {
			$product_ids[] = $product_parent_id;
		}
		$list_id         = \YAYDP\Helper\YAYDP_Helper::map_filter_value( $filter );
		$array_intersect = array_intersect( $list_id, $product_ids );
		return 'in_list' === $filter['comparation'] ? ! empty( $array_intersect ) : empty( $array_intersect );
	}

	/**
	 * Check if the given product match with the given variation filter
	 *
	 * @param \WC_Product $product Given product.
	 * @param array       $filter The variation filter.
	 *
	 * @return boolean
	 */
	public static function check_product_variation( $product, $filter ) {
		$list_variation_id = \YAYDP\Helper\YAYDP_Helper::map_filter_value( $filter );
		$product_ids       = array( $product->get_id() );
		if ( \yaydp_is_variable_product( $product ) || \yaydp_is_grouped_product( $product ) ) {
			$product_ids = array_merge( $product_ids, $product->get_children() );
		}
		$array_intersect = array_intersect( $product_ids, $list_variation_id );
		return 'in_list' === $filter['comparation'] ? ! empty( $array_intersect ) : empty( $array_intersect );
	}

	/**
	 * Check if the given product match with the given category filter
	 *
	 * @param \WC_Product $product Given product.
	 * @param array       $filter The category filter.
	 *
	 * @return boolean
	 */
	public static function check_category( $product, $filter ) {
		$list_category_id = \YAYDP\Helper\YAYDP_Helper::map_filter_value( $filter );
		$product_cats     = self::get_product_cats( $product );
		$array_intersect  = array_intersect( $product_cats, $list_category_id );
		return 'in_list' === $filter['comparation'] ? ! empty( $array_intersect ) : empty( $array_intersect );
	}

	/**
	 * Check if the given product match with the given tag filter
	 *
	 * @param \WC_Product $product Given product.
	 * @param array       $filter The tag filter.
	 *
	 * @return boolean
	 */
	public static function check_tag( $product, $filter ) {
		$list_category_id = \YAYDP\Helper\YAYDP_Helper::map_filter_value( $filter );
		$product_tags     = self::get_product_tags( $product );
		$array_intersect  = array_intersect( $product_tags, $list_category_id );
		return 'in_list' === $filter['comparation'] ? ! empty( $array_intersect ) : empty( $array_intersect );
	}

	/**
	 * Check if the given product match with the given price filter
	 *
	 * @param \WC_Product $product Given product.
	 * @param array       $filter The price filter.
	 *
	 * @return boolean
	 */
	public static function check_price( $product, $filter ) {
		$product_price = (float) \YAYDP\Helper\YAYDP_Pricing_Helper::get_product_price( $product );
		$check         = \yaydp_compare_numeric( $product_price, $filter['value'], $filter['comparation'] );
		return $check;
	}

	/**
	 * Check if the given product match with the given stock filter
	 *
	 * @param \WC_Product $product Given product.
	 * @param array       $filter The stock filter.
	 *
	 * @return boolean
	 */
	public static function check_stock( $product, $filter ) {
		$stock_quantity = \yaydp_get_stock_quantity( $product );
		return \yaydp_compare_numeric( $stock_quantity, $filter['value'], $filter['comparation'] );
	}

	/**
	 * Retrieves all categories associated with a given product, including parent categories
	 *
	 * @since 2.2
	 * @param \WC_Product $product given product.
	 *
	 * @return array
	 */
	public static function get_product_cats( $product ) {
		$result          = array();
		$product_cats    = \get_the_terms( $product->get_id(), 'product_cat' );
		$product_cat_ids = array_map(
			function( $item ) {
				return $item->term_id;
			},
			$product_cats ? $product_cats : array()
		);
		foreach ( $product_cat_ids as $cat_id ) {
			$result[]    = $cat_id;
			$cat_parents = get_ancestors( $cat_id, 'product_cat' );
			$result      = array_merge( $result, $cat_parents );
		}
		$product_parent_id = $product->get_parent_id();
		if ( ! empty( $product_parent_id ) ) {
			$parent_product = \wc_get_product( $product_parent_id );
			$result         = array_merge( $result, self::get_product_cats( $parent_product ) );
		}
		return array_unique( $result );
	}

	/**
	 * Retrieves all tags associated with a given product, including parent tags
	 *
	 * @since 2.2
	 * @param \WC_Product $product given product.
	 *
	 * @return array
	 */
	public static function get_product_tags( $product ) {
		$result          = array();
		$product_cats    = \get_the_terms( $product->get_id(), 'product_tag' );
		$product_cat_ids = array_map(
			function( $item ) {
				return $item->term_id;
			},
			$product_cats ? $product_cats : array()
		);
		foreach ( $product_cat_ids as $cat_id ) {
			$result[]    = $cat_id;
			$cat_parents = get_ancestors( $cat_id, 'product_tag' );
			$result      = array_merge( $result, $cat_parents );
		}
		$product_parent_id = $product->get_parent_id();
		if ( ! empty( $product_parent_id ) ) {
			$parent_product = \wc_get_product( $product_parent_id );
			$result         = array_merge( $result, self::get_product_tags( $parent_product ) );
		}
		return array_unique( $result );
	}

	/**
	 * Check if the given product match with the given attribute filter
	 *
	 * @param \WC_Product $product Given product.
	 * @param array       $filter The attributes filter.
	 *
	 * @return boolean
	 */
	public static function check_attribute( $product, $filter, $item_key = null ) {
		$list_attribute_id = \YAYDP\Helper\YAYDP_Helper::map_filter_value( $filter );
		$list_attributes = [];
		foreach ($list_attribute_id as $attribute_id) {
			$term = get_term( $attribute_id );
			$list_attributes[] = array(
				'taxonomy' => $term->taxonomy,
				'attribute' => $term->slug
			);
		}
		$product_attributes       = $product->get_attributes();
		
		// TODO: process for variable product

		$in_list = false;

		foreach ($list_attributes as $attribute_information) {
			foreach ($product_attributes as $taxonomy => $attribute) {
				if ( $attribute_information['taxonomy'] === $taxonomy && $attribute_information['attribute'] === $attribute ) {
					$in_list = true;
					break 2;
				}
			}
		}

		if ( ! is_null( $item_key ) ) {
			foreach (\WC()->cart->get_cart() as $cart_item) {
				if ( $cart_item['key'] === $item_key && ! empty( $cart_item['variation'] ) ) {
					foreach ($list_attributes as $attribute_information) {
						foreach ($cart_item['variation'] as $taxonomy => $variation) {
							if ( "attribute_" . $attribute_information['taxonomy'] === $taxonomy && $attribute_information['attribute'] === $variation ) {
								$in_list = true;
								break 2;
							}
						}
					}
				}
			}
		}

		return 'in_list' === $filter['comparation'] ? $in_list : ! $in_list;
	}
}
