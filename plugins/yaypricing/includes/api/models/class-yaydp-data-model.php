<?php
/**
 * This class represents the model for the YAYDP Data
 *
 * @package YayPricing\Models
 */

namespace YAYDP\API\Models;

/**
 * Declare class
 */
class YAYDP_Data_Model {

	/**
	 * Retrieves products in database by search query
	 *
	 * @param string $search Search name.
	 * @param number $page Current page.
	 * @param number $limit Limit to get.
	 */
	public static function get_products( $search = '', $page = 1, $limit = YAYDP_SEARCH_LIMIT ) {
		$offset   = ( $page - 1 ) * $limit;
		$args     = array(
			'limit'   => $limit + 1,
			'offset'  => $offset,
			's'       => $search,
			'order'   => 'ASC',
			'orderby' => 'title',
		);
		$products = array_map(
			function( $item ) {
				return array(
					'id'   => $item->get_id(),
					'name' => $item->get_name(),
					'slug' => $item->get_slug(),
				);
			},
			\wc_get_products( $args )
		);
		return $products;
	}

	/**
	 * Retrieves product variations in database by search query
	 *
	 * @param string $search Search name.
	 * @param number $page Current page.
	 * @param number $limit Limit to get.
	 */
	public static function get_variations( $search = '', $page = 1, $limit = YAYDP_SEARCH_LIMIT ) {
		$offset = ( $page - 1 ) * $limit;
		$args   = array(
			'post_type'      => array( 'product_variation' ),
			'posts_per_page' => $limit + 1,
			'offset'         => $offset,
			's'              => $search,
			'order'          => 'ASC',
			'orderby'        => 'title',
		);

		$query_response = new \WP_Query( $args );

		$query_variations = $query_response->have_posts() ? $query_response->posts : array();

		$variations = array_map(
			function( $item ) {
				return array(
					'id'   => $item->ID,
					'name' => $item->post_title,
					'slug' => $item->slug,
				);
			},
			$query_variations
		);
		return $variations;
	}

	/**
	 * Retrieves product categories in database by search query
	 *
	 * @param string $search Search name.
	 * @param number $page Current page.
	 * @param number $limit Limit to get.
	 */
	public static function get_categories( $search = '', $page = 1, $limit = YAYDP_SEARCH_LIMIT ) {
		$offset = ( $page - 1 ) * $limit;
		$args   = array(
			'number'     => $limit + 1,
			'offset'     => $offset,
			'order'      => 'ASC',
			'orderby'    => 'name',
			'taxonomy'   => 'product_cat',
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
	 * Retrieves product tags in database by search query
	 *
	 * @param string $search Search name.
	 * @param number $page Current page.
	 * @param number $limit Limit to get.
	 */
	public static function get_tags( $search = '', $page = 1, $limit = YAYDP_SEARCH_LIMIT ) {
		$offset = ( $page - 1 ) * $limit;
		$args   = array(
			'number'     => $limit + 1,
			'offset'     => $offset,
			'order'      => 'ASC',
			'orderby'    => 'name',
			'taxonomy'   => 'product_tag',
			'name__like' => $search,
		);

		$tags = array_map(
			function( $item ) {
				return array(
					'id'   => $item->term_id,
					'name' => $item->name,
					'slug' => $item->slug,
				);
			},
			\array_values( \get_categories( $args ) )
		);
		return $tags;
	}

	/**
	 * Retrieves customer roles in database by search query
	 *
	 * @param string $search Search name.
	 * @param number $page Current page.
	 * @param number $limit Limit to get.
	 */
	public static function get_customer_roles( $search = '', $page = 1, $limit = YAYDP_SEARCH_LIMIT ) {
		global $wp_roles;

		$offset = ( $page - 1 ) * $limit;

		$list  = $wp_roles->get_names();
		$roles = \array_filter(
			array_keys( $list ? $list : array() ),
			function( $slug ) use ( $list, $search ) {
				if ( ! empty( $search ) ) {
					return false !== strpos( strtolower( $list[ $slug ] ), strtolower( $search ) );
				}
				return true;
			}
		);
		$roles = \array_map(
			function( $slug ) use ( $list ) {
				return array(
					'id'   => $slug,
					'name' => $list[ $slug ],
				);
			},
			$roles
		);

		return array_slice( $roles, $offset, $limit );
	}

	/**
	 * Retrieves customer list in database by search query
	 *
	 * @param string $search Search name.
	 * @param number $page Current page.
	 * @param number $limit Limit to get.
	 */
	public static function get_customers( $search = '', $page = 1, $limit = YAYDP_SEARCH_LIMIT ) {
		global $wpdb;
		$offset        = ( $page - 1 ) * $limit;
		$query_results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT SQL_CALC_FOUND_ROWS {$wpdb->prefix}users.ID as id, {$wpdb->prefix}users.user_email
				FROM {$wpdb->prefix}users INNER JOIN {$wpdb->prefix}usermeta ON ( {$wpdb->prefix}users.ID = {$wpdb->prefix}usermeta.user_id )
				WHERE 1=1 AND (
				{$wpdb->prefix}usermeta.meta_key = 'first_name' AND {$wpdb->prefix}usermeta.meta_value LIKE %s
				OR {$wpdb->prefix}usermeta.meta_key = 'last_name' AND {$wpdb->prefix}usermeta.meta_value LIKE %s
				OR {$wpdb->prefix}users.user_email LIKE %s
				)
				LIMIT %d OFFSET %d
				",
				'%' . $wpdb->esc_like( $search ) . '%',
				'%' . $wpdb->esc_like( $search ) . '%',
				'%' . $wpdb->esc_like( $search ) . '%',
				$limit,
				$offset
			)
		);
		$customers     = array();
		if ( ! empty( $query_results ) ) {
			$customers = \array_map(
				function( $item ) {
					$first_name = get_user_meta( $item->id, 'first_name', true );
					$last_name = get_user_meta( $item->id, 'last_name', true );
					return array(
						'id'         => $item->id,
						'email'      => $item->user_email,
						'first_name' => $first_name,
						'last_name'  => $last_name,
						'label' => $first_name . $last_name . "(#" . $item->user_email . ")"
					);
				},
				$query_results
			);
		}

		return $customers;
	}

	/**
	 * Retrieves all regions and its country in database by search query
	 *
	 * @param string $search Search name.
	 * @param number $page Current page.
	 * @param number $limit Limit to get.
	 */
	public static function get_shipping_regions( $search = '', $page = 1, $limit = YAYDP_SEARCH_LIMIT ) {
		$shipping_continents = \WC()->countries->get_shipping_continents();
		$allowed_countries   = \WC()->countries->get_shipping_countries();
		$regions             = \array_map(
			function( $continent_slug ) use ( $shipping_continents, $allowed_countries ) {
				$continent = $shipping_continents[ $continent_slug ];
				$countries = array_intersect( array_keys( $allowed_countries ? $allowed_countries : array() ), $continent['countries'] );
				return array(
					'continent_slug' => $continent_slug,
					'continent_name' => $continent['name'],
					'countries'      => \array_map(
						function( $country_code ) use ( $allowed_countries ) {
							$country_states = \WC()->countries->get_states( $country_code );
							return array(
								'country_code' => $country_code,
								'country_name' => $allowed_countries[ $country_code ],
								'states'       => array_map(
									function( $state_code ) use ( $country_states ) {
										return array(
											'state_code' => $state_code,
											'state_name' => $country_states[ $state_code ],
										);
									},
									array_keys( $country_states ? $country_states : array() )
								),
							);
						},
						array_values( $countries ? $countries : array() )
					),
				);
			},
			array_keys( $shipping_continents ? $shipping_continents : array() )
		);
		return $regions;
	}

	/**
	 * Retrieves all payment methods in database by search query
	 *
	 * @param string $search Search name.
	 * @param number $page Current page.
	 * @param number $limit Limit to get.
	 */
	public static function get_payment_methods( $search = '', $page = 1, $limit = YAYDP_SEARCH_LIMIT ) {

		$offset = ( $page - 1 ) * $limit;

		$payment_gateways = \WC()->payment_gateways->payment_gateways();
		$payment_methods  = array_map(
			function( $id ) use ( $payment_gateways ) {
				$method = $payment_gateways[ $id ];
				return array(
					'id'      => $id,
					'name'    => ! empty( $method->method_title ) ? $method->method_title : $method->title,
					'enabled' => 'yes' === $method->enabled,
				);
			},
			array_keys( $payment_gateways ? $payment_gateways : array() )
		);

		$payment_methods = array_filter(
			$payment_methods,
			function( $method ) use ( $search ) {
				if ( ! empty( $search ) ) {
					return false !== strpos( strtolower( $method['name'] ), strtolower( $search ) );
				}
				return true;
			}
		);

		return array_slice( $payment_methods, $offset, $limit );
	}

	/**
	 * Retrieves all coupons in database by search query
	 *
	 * @since 2.0
	 *
	 * @param string $search Search name.
	 * @param number $page Current page.
	 * @param number $limit Limit to get.
	 */
	public static function get_coupons( $search = '', $page = 1, $limit = YAYDP_SEARCH_LIMIT ) {

		$offset = ( $page - 1 ) * $limit;

		$coupon_posts = get_posts(
			array(
				'posts_per_page' => $limit + 1,
				'offset'         => $offset,
				's'              => $search,
				'orderby'        => 'name',
				'order'          => 'asc',
				'post_type'      => 'shop_coupon',
				'post_status'    => 'publish',
			)
		);

		$coupons = array_map(
			function( $post ) {
				return array(
					'id'   => $post->ID,
					'name' => $post->post_title,
				);
			},
			$coupon_posts
		);

		return $coupons;
	}

	/**
	 * Retrieves product categories in database by search query
	 *
	 * @param string $search Search name.
	 * @param number $page Current page.
	 * @param number $limit Limit to get.
	 */
	public static function get_attributes( $search = '', $page = 1, $limit = YAYDP_SEARCH_LIMIT ) {
		$offset = ( $page - 1 ) * $limit;

		$taxonomy_names = \wc_get_attribute_taxonomy_names();

		$taxonomy_with_label = [];

		foreach ($taxonomy_names as $taxonomy_name) {
			$taxonomy_with_label[$taxonomy_name] = \wc_attribute_label( $taxonomy_name );
		}

		$args   = array(
			'number'     => $limit + 1,
			'offset'     => $offset,
			'order'      => 'ASC',
			'orderby'    => 'name',
			'taxonomy'   => $taxonomy_names,
			'name__like' => $search,
		);

		$categories = array_map(
			function( $item ) use ( $taxonomy_with_label ) {
				$parent_label = '';
				$cat = $item;
				while ( ! empty( $cat->parent ) ) {
					$parent = get_term( $cat->parent );
					$parent_label .= $parent->name . ' ⇒ ';
					$cat = $parent;
				}
				$taxonomy_label = isset( $taxonomy_with_label[ $item->taxonomy ] ) ? $taxonomy_with_label[ $item->taxonomy ] : '';
				return array(
					'id'   => $item->term_id,
					'name' => $taxonomy_label . ': ' . $parent_label . $item->name,
					'slug' => $item->slug,
				);
			},
			\array_values( \get_categories( $args ) )
		);
		return $categories;
	}
}
