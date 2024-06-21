<?php
/**
 * Front class start.
 *
 * @package : Addify Product Add Ons
 */

defined( 'ABSPATH' ) || exit;

class Addify_Product_Add_Ons_Front {

	public function __construct() {

		add_action( 'wp_head', array( $this, 'af_addon_fields_styling' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'addon_enqueue_script' ) );

		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'af_addon_front_fields_show' ), 20 );

		add_action( 'woocommerce_loop_add_to_cart_link', array( $this, 'add_to_cart_button' ), 10, 2 );
	}

	public function addon_enqueue_script() {

		wp_enqueue_style( 'addon_front_css', plugins_url( 'assets/css/addify-pao-front.css', __FILE__ ), false, '1.0.0' );

		wp_enqueue_script( 'addon_front_js', plugins_url( 'assets/js/addify-pao-front.js', __FILE__ ), array( 'jquery' ), '1.0.0', false );

		wp_enqueue_style( 'select2-css', plugins_url( 'assets/css/select2.css', WC_PLUGIN_FILE ), array(), '5.7.2' );

		wp_enqueue_script( 'select2-js', plugins_url( 'assets/js/select2/select2.min.js', WC_PLUGIN_FILE ), array( 'jquery' ), '4.0.3', true );

		wp_enqueue_style( 'af_pao_font', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', false, '1.0', false );

		$af_pao_ajax_nonce = array(

			'admin_url' => admin_url( 'admin-ajax.php' ),

			'nonce'     => wp_create_nonce( '_addify_pao_nonce' ),

		);

		wp_localize_script( 'addon_front_js', 'addify_product_add_ons', $af_pao_ajax_nonce );
	}

	public function af_addon_fields_styling() {

		$class_obj = new Af_Addon_Front_Style();

		$class_obj->af_addon_front_fields_styling( get_the_ID() );

		$addon_rules = get_posts(
			array(
				'post_type'   => 'af_addon',
				'post_status' => 'publish',
				'orderby'     => 'menu_order',
				'order'       => 'ASC',
				'numberposts' => -1,
				'fields'      => 'ids',
			)
		);

		foreach ( $addon_rules as $rule_id ) {

			if ( empty( $rule_id ) ) {
				continue;
			}

			$class_obj->af_addon_front_fields_styling( $rule_id );
		}
	}

	public function af_addon_front_fields_show() {

		global $product;

		$product_id = $product->get_id();

		wp_nonce_field( 'af_addon_front_nonce', 'af_addon_front_nonce_field' );

		?>

			<div class="af_addon_field_show" >

				<?php

				af_addon_front_fields( $product_id );

				if ( '1' != get_post_meta( $product_id, 'exclude_rule_addons', true ) ) {

					$addon_rules = get_posts(
						array(
							'post_type'   => 'af_addon',
							'post_status' => 'publish',
							'orderby'     => 'menu_order',
							'order'       => 'ASC',
							'numberposts' => -1,
							'fields'      => 'ids',
						)
					);

					foreach ( $addon_rules as $rule_id ) {

						$af_addon_user_role = get_post_meta( $rule_id, 'af_addon_user_role', true );

						$af_addon_use_roles = is_user_logged_in() ? current( wp_get_current_user()->roles ) : 'guest';

						if ( ! empty( $af_addon_user_role ) && ! in_array( (string) $af_addon_use_roles, (array) $af_addon_user_role, true ) ) {

							continue;
						}

						if ( $this->af_addon_prod_check( $product_id, $rule_id ) ) {

							af_addon_front_fields( $rule_id );
						}
					}
				}

				?>

			</div>
				<input type="hidden" name="af_product_options_fields_availabel" value="yes">
		<?php

		if ( $this->is_product_has_extra_options( $product->get_id() ) ) {

			?>
			<div style="margin-bottom: 15px;">
				<p class="af_pao_real_time_product_sub_total_calculation" data-prod_title="<?php echo esc_attr( $product->get_title() ); ?>" data-prod_price="<?php echo esc_attr( $product->get_price() ); ?>" data-currency_sym="<?php echo esc_attr( get_woocommerce_currency_symbol() ); ?>"></p>
			</div>
			<table class="af_addon_total_price">
				<tbody id="addon-tbody">
					<tr>
						<td class="product-name-and-quantity">

						</td>
						<td class="product-sub-total-1st-tr">

						</td>
					</tr>
					<tr class="optn_name_price" style="display:none;">
						<td id="product_option_selected_name">
							<ul>

							</ul>
						</td>
						<td id="product_option_selected_price">
							<ul>

							</ul>
						</td>
					</tr>
				</tbody>
			</table>
			<?php

		}
	}

	public function af_addon_prod_check( $prod_id, $rule_id ) {

		$addon_selected_products = get_post_meta( $rule_id, 'af_pao_prod_search', true );
		$addon_selected_category = get_post_meta( $rule_id, 'af_pao_cat_search', true );
		$addon_selected_tags     = get_post_meta( $rule_id, 'af_pao_tag_search', true );

		if ( empty( $addon_selected_products ) && empty( $addon_selected_category ) && empty( $addon_selected_tags ) ) {
			return true;
		}
		if ( ! empty( $addon_selected_products ) ) {

			if ( in_array( (string) $prod_id, $addon_selected_products ) ) {
				return true;
			}
		}
		if ( ! empty( $addon_selected_category ) ) {

			if ( has_term( $addon_selected_category, 'product_cat', $prod_id ) ) {
				return true;
			}
		}
		if ( ! empty( $addon_selected_tags ) ) {

			if ( has_term( $addon_selected_tags, 'product_tag', $prod_id ) ) {
				return true;
			}
		}
		return false;
	}

	public function add_to_cart_button( $button, $product ) {

		$prod_id = $product->get_id();

		if ( $this->af_fields_ids_check( $prod_id ) ) {

			$button = '<a href="' . esc_url( $product->get_permalink() ) . '"> <button> Read More </button> </a>';
		}

		return $button;
	}

	public function af_fields_ids_check( $prod_id ) {

		$is_req = false;

		$addon_prod_lvl = get_posts(
			array(
				'post_type'   => 'af_pao_fields',
				'post_status' => 'publish',
				'numberposts' => -1,
				'post_parent' => $prod_id,
				'fields'      => 'ids',
				'orderby'     => 'menu_order',
				'order'       => 'ASC',
			)
		);

		if ( ! empty( $addon_prod_lvl ) ) {

			foreach ( $addon_prod_lvl as $addon_prd ) {

				if ( '1' == get_post_meta( $addon_prd, 'af_addon_required_field', true ) ) {

					$is_req = true;
				}
			}
		}

		if ( '1' == get_post_meta( $prod_id, 'exclude_rule_addons', true ) ) {

			return $is_req;
		}

		$addon_rules = get_posts(
			array(
				'post_type'   => 'af_addon',
				'post_status' => 'publish',
				'orderby'     => 'menu_order',
				'order'       => 'ASC',
				'numberposts' => -1,
				'fields'      => 'ids',
			)
		);

		foreach ( $addon_rules as $addon_rule ) {

			$rules_fields = get_posts(
				array(
					'post_type'   => 'af_pao_fields',
					'post_status' => 'publish',
					'numberposts' => -1,
					'post_parent' => $addon_rule,
					'fields'      => 'ids',
					'orderby'     => 'menu_order',
					'order'       => 'ASC',
				)
			);

			$addon_selected_products = get_post_meta( $addon_rule, 'af_pao_prod_search', true );
			$addon_selected_category = get_post_meta( $addon_rule, 'af_pao_cat_search', true );
			$addon_selected_tags     = get_post_meta( $addon_rule, 'af_pao_tag_search', true );

			if ( empty( $addon_selected_products ) && empty( $addon_selected_category ) && empty( $addon_selected_tags ) ) {

				foreach ( $rules_fields as $rule_field ) {

					if ( '1' == get_post_meta( $rule_field, 'af_addon_required_field', true ) ) {

						$is_req = true;
					}
				}
			} elseif ( $this->af_addon_prod_check( $prod_id, $addon_rule ) ) {


				foreach ( $rules_fields as $rule_field ) {

					if ( '1' == get_post_meta( $rule_field, 'af_addon_required_field', true ) ) {

						$is_req = true;

					}
				}
			}
		}

		return $is_req;
	}

	public function is_product_has_extra_options( $prod_id ) {

		$is_applicable = false;

		$addon_prod_lvl = get_posts(
			array(
				'post_type'   => 'af_pao_fields',
				'post_status' => 'publish',
				'numberposts' => -1,
				'post_parent' => $prod_id,
				'fields'      => 'ids',
				'orderby'     => 'menu_order',
				'order'       => 'ASC',
			)
		);

		if ( count( $addon_prod_lvl ) >= 1 ) {
			$is_applicable = true;
		}

		if ( '1' == get_post_meta( $prod_id, 'exclude_rule_addons', true ) ) {

			return $is_applicable;
		}

		$addon_rules = get_posts(
			array(
				'post_type'   => 'af_addon',
				'post_status' => 'publish',
				'orderby'     => 'menu_order',
				'order'       => 'ASC',
				'numberposts' => -1,
				'fields'      => 'ids',
			)
		);

		foreach ( $addon_rules as $addon_rule ) {

			$rules_fields = get_posts(
				array(
					'post_type'   => 'af_pao_fields',
					'post_status' => 'publish',
					'numberposts' => -1,
					'post_parent' => $addon_rule,
					'fields'      => 'ids',
					'orderby'     => 'menu_order',
					'order'       => 'ASC',
				)
			);

			$addon_selected_products = get_post_meta( $addon_rule, 'af_pao_prod_search', true );
			$addon_selected_category = get_post_meta( $addon_rule, 'af_pao_cat_search', true );
			$addon_selected_tags     = get_post_meta( $addon_rule, 'af_pao_tag_search', true );

			if ( empty( $addon_selected_products ) && empty( $addon_selected_category ) && empty( $addon_selected_tags ) ) {

				$is_applicable = true;
			}

			if ( $this->af_addon_prod_check( $prod_id, $addon_rule ) ) {

				$is_applicable = true;
			}
		}

		return $is_applicable;
	}
}

new Addify_Product_Add_Ons_Front();
