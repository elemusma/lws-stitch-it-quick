<?php
/*
 Plugin name: Inventory Sync for WooCommerce
 Description: Allows to synchronize the stock quantity of the products with the same SKUs between two WooCommerce stores.
 Author: Misha Rudrastyh
 Author URI: https://rudrastyh.com
 Version: 1.2
 License: GPL v2 or later
 License URI: http://www.gnu.org/licenses/gpl-2.0.html

 Copyright 2023-2024 Misha Rudrastyh ( https://rudrastyh.com )

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
 the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if( ! class_exists( 'ISFW_Product_Sync' ) ) {

	class ISFW_Product_Sync{


		function __construct() {

			// order created
			add_action( 'woocommerce_reduce_order_stock', array( $this, 'order_sync' ) );
			// order cancelled
			add_action( 'woocommerce_restore_order_stock', array( $this, 'order_sync' ) );
			// product saved
			add_action( 'save_post', array( __CLASS__, 'product_update' ), 25, 2 );

			// settings pages
			add_filter( 'woocommerce_get_settings_products', array( $this, 'output_settings' ), 25, 2 );
			add_filter( 'woocommerce_admin_settings_sanitize_option_isfw_store_url', 'sanitize_url' );
			add_filter( 'woocommerce_admin_settings_sanitize_option_isfw_application_password', array( $this, 'sanitize_pwd' ) );
			add_filter( 'option_isfw_application_password', array( $this, 'esc_pwd' ) );

		}




		public static function is_excluded( $product_or_variation ) {

			if( 'external' === $product_or_variation->get_type() ) {
				return true;
			}

			return false;

		}


		public function order_sync( $order ) {

			$items = $order->get_items( array( 'line_item' ) );
			if( ! $items ) {
				return;
			}


			$items = $this->format_order_items( $items );
			$this->sync( $items );

		}


		public static function product_update( $product_id, $post ) {

			if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			if( 'product' !== $post->post_type && 'product_variation' !== $post->post_type ) {
				return;
			}

			if( ! function_exists( 'wc_get_product' ) ) {
				return;
			}

			$product = wc_get_product( $product_id );

			if( ! $product ) { // null or false
				return;
			}

			self::product_sync( $product );

		}


		public static function product_sync( $product ) {

			if( ! is_a( $product, 'WC_Product' ) && ! is_a( $product, 'WC_Product_Variation' ) ) {
				return;
			}

			if( self::is_excluded( $product ) ) {
				return;
			}

			$items = self::format_product( $product );
			self::sync( $items );

		}


		/*
		 * Formatting functions
		 */
		private function format_order_items( $items ) {

			/*
				Array(
					products => array( product_id => data, ... ),
					variations => array( product_id => array( variation_id => data, ... ) )
				)
			*/
			$return = array(
				'products' => array(),
				'variations' => array(),
			);

			foreach( $items as $item ) { // both products and variations

				// WC_Product or WC_Product_Variation
				$product = $item->get_product();
				if( ! $product ) {
					continue;
				}

				if( $this->is_excluded( $product ) ) {
					continue;
				}

				if( $parent_id = $product->get_parent_id() ) {
					// variation
					$return[ 'variations' ][ $parent_id ][] = $this->product_data( $product );
				} else {
					// not variation
					$return[ 'products' ][] = $this->product_data( $product );
				}

			}

			return $return;

		}


		public static function format_product( $product ) {

			$return = array(
				'products' => array(),
				'variations' => array(),
			);

			// for variable products we add variations only, it will save us 1 requests I guess
			if( $product->is_type( 'variable' ) ) {

				if( $variation_ids = $product->get_children() ) {
					$parent_id = $product->get_id();
					foreach( $variation_ids as $variation_id ) {
						$variation = wc_get_product_object( 'variation', $variation_id );
						if( ! $variation ) {
							continue;
						}
						$return[ 'variations' ][ $parent_id ][] = self::product_data( $variation );
					}
				}

			} else {

				$return[ 'products' ][] = self::product_data( $product );

			}

			return $return;

		}


		public static function product_data( $product ) {
			return array(
				'id' => self::get_id_by_sku( get_post_meta( $product->get_id(), '_sku', true ) ),
				'manage_stock' => $product->get_manage_stock(),
				'stock_status' => $product->get_stock_status(),
				'stock_quantity' => $product->get_stock_quantity(),
			);
		}

		public static function get_id_by_sku( $sku ) {

			$url = esc_url( get_option( 'isfw_store_url' ) );
			$login = get_option( 'isfw_username' );
			$pwd = get_option( 'isfw_application_password' );

			// if something is not here, exit on that
			if( ! $url || ! $login || ! $pwd ) {
				return;
			}

			$request = wp_remote_get(
				add_query_arg( array( 'sku' => $sku ), "{$url}/wp-json/wc/v3/products" ),
				array(
					'headers' => array(
						'Authorization' => 'Basic ' . base64_encode( "{$login}:{$pwd}" )
					)
				)
			);

			if( 'OK' !== wp_remote_retrieve_response_message( $request ) ) {
				return 0;
			}

			$products = json_decode( wp_remote_retrieve_body( $request ) );
			if( ! $products ) {
				return 0;
			}

			$p = reset( $products );
			return $p->id;

		}


		/*
		 * Sync functions
		 */
		public static function sync( $items ) {

			$url = esc_url( get_option( 'isfw_store_url' ) );
			$login = get_option( 'isfw_username' );
			$pwd = get_option( 'isfw_application_password' );

			// if something is not here, exit on that
			if( ! $url || ! $login || ! $pwd ) {
				return;
			}

			// let's generate batch requests for products first
			if( $items[ 'products' ] ) {

				// let's check how many elements are in array
				if( count( $items[ 'products' ] ) > 1 ) {
					// create and run batch here
					wp_remote_request(
						"{$url}/wp-json/wc/v3/products/batch",
						array(
							'method' => 'POST',
							'headers' => array(
								'Authorization' => 'Basic ' . base64_encode( "$login:$pwd" )
							),
							'body' => array(
								'update' => $items[ 'products' ]
							)
						)
					);
				} else {

					// great now we have a product and we have to update its stock!
					$product_id = $items[ 'products' ][0][ 'id' ];
					unset( $items[ 'products' ][0][ 'id' ] );

					wp_remote_request(
						"{$url}/wp-json/wc/v3/products/{$product_id}",
						array(
							'method' => 'PUT',
							'headers' => array(
								'Authorization' => 'Basic ' . base64_encode( "$login:$pwd" )
							),
							'body' => $items[ 'products' ][0]
						)
					);

				}

			}


			// the same for variations
			if( $items[ 'variations' ] ) {

				foreach( $items[ 'variations' ] as $parent_id => $variations ) {

					if( count( $variations ) > 1 ) {

						wp_remote_request(
							"{$url}/wp-json/wc/v3/products/{$parent_id}/variations/batch",
							array(
								'method' => 'POST',
								'headers' => array(
									'Authorization' => 'Basic ' . base64_encode( "$login:$pwd" )
								),
								'body' => array(
									'update' => $variations
								)
							)
						);

					} else {

						// great now we have a product and we have to update its stock!
						$variation_id = $variations[0][ 'id' ];
						unset( $variations[0][ 'id' ] );

						wp_remote_request(
							"{$url}/wp-json/wc/v3/products/{$parent_id}/variations/{$variation_id}",
							array(
								'method' => 'POST',
								'headers' => array(
									'Authorization' => 'Basic ' . base64_encode( "$login:$pwd" )
								),
								'body' => $variations[0]
							)
						);

					}

				} // foreach loop for every variation set

			}

		}


		/*
		 * Settings page
		 */
		public function output_settings( $settings, $current_section ) {

			if( 'inventory' !== $current_section ) {
				return $settings;
			}

			$settings[] = array(
				'name' => __( 'Inventory Sync', 'rudr-simple-inventory-sync' ),
				'type' => 'title',
				'desc' => __( 'Provide the store details you would like to sync the inventory with.', 'rudr-simple-inventory-sync' ),
			);

			$settings[] = array(
				'name'     => __( 'Store URL', 'rudr-simple-inventory-sync' ),
				'id'       => 'isfw_store_url',
				'desc_tip' => __( 'Please note, that you need to provide a store URL starting with https:// unless you are using the plugin on your localhost.', 'rudr-simple-inventory-sync' ),
				'type'     => 'text',
			);

			$settings[] = array(
				'name'     => __( 'Username', 'rudr-simple-inventory-sync' ),
				'id'       => 'isfw_username',
				'type'     => 'text',
			);

			$settings[] = array(
				'name'     => __( 'Application password', 'rudr-simple-inventory-sync' ),
				'id'       => 'isfw_application_password',
				'desc_tip' => __( 'You can create an application password in Users > Profile', 'rudr-simple-inventory-sync' ),
				'type'     => 'text',
			);

			$settings[] = array(
				'type' => 'sectionend',
			);

			return $settings;

		}

		public function esc_pwd( $application_password ) {

			if( function_exists( 'get_current_screen' ) && ( $screen = get_current_screen() ) && 'woocommerce_page_wc-settings' === $screen->id ) {
				$application_password = esc_attr( substr_replace( $application_password, '*****', - ( strlen( $application_password ) - 9 ) ) );
			}

			return $application_password;

		}

		public function sanitize_pwd( $application_password ) {

			if( preg_match("/\*\*$/", $application_password ) ) {
				return null;
			}
			return $application_password;

		}

	}

	new ISFW_Product_Sync();

}
