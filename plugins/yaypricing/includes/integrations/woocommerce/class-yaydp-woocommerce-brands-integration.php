<?php
/**
 * Handles the integration of WooCommerce WooCommerce Brands plugin with our system
 *
 * @package YayPricing\Integrations
 */

namespace YAYDP\Integrations\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Declare class
 */
class YAYDP_WooCommerce_Brands_Integration {
	use \YAYDP\Traits\YAYDP_Singleton;

	private $taxonomy = 'product_brand';

	/**
	 * Constructor
	 */
	protected function __construct() {
		if ( ! class_exists( 'WC_Brands' ) ) {
			return;
		}
		add_action( 'init', array( $this, 'initialize_wc_brands_integration' ), 100 );
	}

	/**
	 * Initialize callback
	 */
	public function initialize_wc_brands_integration() {
		if ( ! class_exists( 'WC_Brands' ) ) {
			return;
		}
		add_filter( 'yaydp_admin_product_filters', array( $this, 'admin_product_filters' ) );
		add_filter( 'yaydp_admin_extra_localize_data', array( $this, 'taxonomies_localize_data' ) );

		$wc_brands_taxonomy = $this->taxonomy;
		add_filter( "yaydp_admin_custom_filter_{$wc_brands_taxonomy}_result", array( __CLASS__, 'get_filter_result' ), 10, 5 );
		add_filter( "yaydp_get_matching_products_by_{$wc_brands_taxonomy}", array( __CLASS__, 'get_matching_products' ), 10, 4 );
		add_filter( "yaydp_check_condition_by_{$wc_brands_taxonomy}", array( __CLASS__, 'check_condition' ), 10, 3 );
	}

	/**
	 * Add filter to current product filters
	 *
	 * @param array $filters Given filters.
	 *
	 * @return array
	 */
	public function admin_product_filters( $filters ) {
		$wc_brands_filter_array = array(
			array(
			'value'   		    => $this->taxonomy,
			'label'            => 'WooCommerce Brands',
			'comparations'     => array(
					array(
						'value' => 'in_list',
						'label' => 'In list',
					),
					array(
						'value' => 'not_in_list',
						'label' => 'Not in list',
					),
				),
			)
		);
		return array_merge( $filters, $wc_brands_filter_array );
	}

	/**
	 * Add CPT UI taxonomies data to current localize data
	 *
	 * @param array $data Localize data.
	 *
	 * @return array
	 */
	public function taxonomies_localize_data( $data ) {
		return array();
	}

	/**
	 * Alter search filter result
	 *
	 * @param array  $result Search result.
	 * @param string $filter_name Name of the filter.
	 * @param string $search Search text.
	 * @param int    $page Current page.
	 * @param int    $limit Limit result to get.
	 *
	 * @return array
	 */
	public static function get_filter_result( $result, $filter_name, $search = '', $page = 1, $limit = YAYDP_SEARCH_LIMIT ) {
		$offset = ( $page - 1 ) * $limit;
		$args   = array(
			'number'     => $limit + 1,
			'offset'     => $offset,
			'order'      => 'ASC',
			'orderby'    => 'name',
			'taxonomy'   => $filter_name,
			'name__like' => $search,
		);

		$categories = array_map(
			function( $item ) {
				$parent_label = '';
				$cat = $item;
				while ( ! empty( $cat->parent ) ) {
					$parent = get_term( $cat->parent );
					$parent_label .= $parent->name . ' ⇒ ';
					$cat = $parent;
				}
				return array(
					'id'   => $item->term_id,
					'name' => $parent_label . $item->name,
					'slug' => $item->slug,
				);
			},
			\array_values( \get_categories( $args ) )
		);

		return $categories;

	}

	/**
	 * Alter matching products result
	 *
	 * @param array  $products Result.
	 * @param string $type  Custom Post Type taxonomy name.
	 * @param string $value  Custom Post Type term value.
	 * @param string $comparation Comparison operation.
	 *
	 * @return array
	 */
	public static function get_matching_products( $products, $type, $value, $comparation ) {
		if ( empty( $value ) ) {
			return array();
		}
		$args     = array(
			'limit'     => -1,
			'order'     => 'ASC',
			'orderby'   => 'title',
			'tax_query' => array(
				array(
					'taxonomy' => $type,
					'terms'    => $value,
					'operator' => 'in_list' === $comparation ? 'IN' : 'NOT_IN',
				),
			),
		);
		$products = \wc_get_products( $args );
		return $products;
	}

	/**
	 * Alter check condition result
	 *
	 * @param array       $result Result.
	 * @param \WC_Product $product  Given product.
	 * @param array       $filter Checking filter.
	 *
	 * @return bool
	 */
	public static function check_condition( $result, $product, $filter ) {
		$list_id         = \YAYDP\Helper\YAYDP_Helper::map_filter_value( $filter );
		$product_cats    = \get_the_terms( $product->get_id(), $filter['type'] );
		$product_cat_ids = array_map(
			function( $item ) {
				return $item->term_id;
			},
			$product_cats ? $product_cats : array()
		);
		$in_list         = false;
		foreach ( $product_cat_ids as $cat_id ) {
			if ( \in_array( $cat_id, $list_id ) ) {
				$in_list = true;
				break;
			}
			$cat_parents      = get_ancestors( $cat_id, $filter['type'] );
			$intersection_ids = array_intersect( $cat_parents, $list_id );
			if ( ! empty( $intersection_ids ) ) {
				$in_list = true;
				break;
			}
		}
		$parent_id = $product->get_parent_id();
		if ( ! empty( $parent_id ) && ! $in_list ) {
			$in_list = self::check_condition( false, \wc_get_product( $parent_id ), $filter );
		}
		return 'in_list' === $filter['comparation'] ? $in_list : ! $in_list;
	}
}