<?php
/**
 * This class represents a cart for storing and managing items
 * It provides methods for adding, removing, and updating items in the cart
 *
 * @package YayPricing\Classes
 * @since 2.4
 */

namespace YAYDP\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Declare class
 */
class YAYDP_Cart {

	/**
	 * Contains cart items
	 *
	 * @var array
	 */
	protected $items = array();

	/**
	 * Constructor
	 * If not passing items, then initialize based on current WC cart.
	 *
	 * @param array|null $items Given items.
	 */
	public function __construct( $items = null ) {
		if ( is_null( $items ) ) {
			if ( ! is_null( \WC()->cart ) && method_exists( \WC()->cart, 'get_cart' ) ) {
				$cart_items = \WC()->cart->get_cart();
			} else {
				$cart_items = array();
			}
		} else {
			$cart_items = $items;
		}

		foreach ( $cart_items as $cart_item_key => $cart_item ) {
			$this->items[ $cart_item_key ] = new \YAYDP\Core\YAYDP_Cart_Item( $cart_item );
		}
	}

	/**
	 * Retrieves all items in the yaydp cart ( exclude extra )
	 *
	 * @return array
	 */
	public function get_items() {
		return array_filter(
			$this->items,
			function( $item ) {
				return ! $item->is_extra();
			}
		);
	}

	/**
	 * Retrieves all items in the cart, including any extra items that have been added
	 *
	 * @return array
	 */
	public function get_items_include_extra() {
		return $this->items;
	}

	/**
	 * Publishes the yaydp cart to the WC Cart
	 */
	public function publish() {
		foreach ( $this->get_items_include_extra() as $item ) {
			if ( $item->is_extra() ) {
				$product             = $item->get_product();
				$product_id          = $product->get_id();
				$added_cart_item_key = \WC()->cart->add_to_cart(
					$product_id,
					$item->get_quantity(),
					null,
					null,
					array(
						'is_extra'   => true,
						'extra_data' => \yaydp_serialize_cart_data( $item->get_extra_data() ),
						'modifiers'  => \yaydp_serialize_cart_data( $item->get_modifiers() ),
						'yaydp_custom_data' => array(
							'price' => 0
						)
					)
				);
				if ( false === $added_cart_item_key ) {
					continue;
				}

				$wc_added_item = \WC()->cart->get_cart_item( $added_cart_item_key );
				$wc_added_item['data']->set_price( 0 );
				if ( \yaydp_product_pricing_is_discount_based_on_regular_price() ) {
					$wc_added_item['data']->set_regular_price( 0 );
				}
				continue;
			}
			if ( ! $item->can_modify() ) {
				continue;
			}
			$item_key            = $item->get_key();
			$new_price           = $item->get_price();
			$has_item_in_wc_cart = isset( \WC()->cart->cart_contents[ $item_key ] );
			if ( $has_item_in_wc_cart ) {
				do_action( 'yaydp_before_set_cart_item_price' );
				\WC()->cart->cart_contents[ $item_key ]['data']->set_price( $new_price );
				\WC()->cart->cart_contents[ $item_key ]['modifiers'] = \yaydp_serialize_cart_data( $item->get_modifiers() );
				\WC()->cart->cart_contents[ $item_key ]['yaydp_custom_data'] = array(
					'price' => $new_price
				);
			}
		}
	}

	/**
	 * Adds an extra item to the cart
	 *
	 * @param \WC_Product $product Product to add.
	 * @param float       $quantity Quantity of the product to add.
	 * @param array       $props Extra data of the extra item.
	 */
	public function add_free_item( $product, $quantity, $props = array() ) {
		$product_id    = $product->get_id();
		$item_data     = array(
			'key'      => null,
			'quantity' => $quantity,
			'is_extra' => true,
			'data'     => $product,
			'props'    => $props,
		);
		$new_item      = new \YAYDP\Core\YAYDP_Cart_Item( $item_data );
		$this->items[] = $new_item;
		return $new_item;
	}

	/**
	 * Adds an item to the cart
	 *
	 * @param \WC_Product $product Product to add.
	 * @param float       $quantity Quantity of the product to add.
	 */
	public function add_item( $product, $quantity ) {
		foreach ( $this->items as $item ) {
			if ( $item->is_extra() ) {
				continue;
			}
			$item_product = $item->get_product();
			if ( $product->get_id() === $item_product->get_id() ) {
				$item_quantity = $item->get_quantity();
				$item->set_quantity( $item_quantity + $quantity );
				return $item->get_key();
			}
		}
		$new_item      = new \YAYDP\Core\YAYDP_Cart_Item(
			array(
				'key'      => time(),
				'quantity' => $quantity,
				'data'     => $product,
			)
		);
		$this->items[] = $new_item;
		return $new_item->get_key();
	}

	/**
	 * Calculates the subtotal of the cart ( exclude extra item )
	 *
	 * @return float
	 */
	public function get_cart_subtotal() {
		$subtotal = 0;
		foreach ( $this->items as $item ) {
			if ( $item->is_extra() ) {
				continue;
			}
			$item_quantity = $item->get_quantity();
			$item_price    = $item->get_price();
			$subtotal     += $item_quantity * $item_price;

		}
		return $subtotal;
	}

	/**
	 * Calculates the total quantity of the cart item ( exclude extra item )
	 */
	public function get_cart_quantity() {
		$total_quantity = 0;
		foreach ( $this->items as $item ) {
			if ( $item->is_extra() ) {
				continue;
			}
			$total_quantity += $item->get_quantity();
		}
		return $total_quantity;
	}

	public function reset_modifiers() {
		foreach ( $this->items as $item ) {
			$item->clear_modifiers();
		}
	}

	public function get_cart_total() {
		$total = 0;
		foreach ( $this->items as $item ) {
			if ( $item->is_extra() ) {
				continue;
			}
			$total += $item->get_price() * $item->get_quantity();
		}
		return $total;
	}

	public function get_cart_origin_total() {
		$total = 0;
		foreach ( $this->items as $item ) {
			if ( $item->is_extra() ) {
				continue;
			}
			$total += $item->get_initial_price() * $item->get_quantity();
		}
		return $total;
	}
}
