<?php
/**
 * Plugin Name:  Product Options and Fields
 * Plugin URI:   https://woocommerce.com/products/addify-product-options-and-addons/
 * Description:  Add extra options and add-ons to let your customers personalize products while placing an order.
 * Version:      1.3.1
 * Author:       Addify
 * Developed By: Addify
 * Author URI:   https://woocommerce.com/vendor/addify/
 * Support:      https://woocommerce.com/vendor/addify/
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path:  /languages
 * Text Domain : addify_pao
 * WC requires at least: 3.0.9
 * WC tested up to: 8.*.*
 * Woo: 18734001818589:619c7dea53da0483187d310cff7647ad
 * Requires plugins: woocommerce
 *
 * @package : Addify Product Add Ons
 */

defined( 'ABSPATH' ) || exit();

class Addify_Product_Add_Ons_Main {

	public function __construct() {

		$this->afpao_apbgp_global_constents_vars();

		add_action( 'init', array( $this, 'af_po_admin_init' ) );

		add_action( 'plugins_loaded', array( $this, 'af_addon_init' ) );

		add_action( 'before_woocommerce_init', array( $this, 'af_po_h_o_p_s_compatibility' ) );
	}

	public function af_po_admin_init() {

		$this->af_pao_custom_post_type();

		add_action( 'wp_loaded', array( $this, 'afpao_accm_init' ) );

		include AFPAO_PLUGIN_DIR . 'includes/front/class-af-addon-front-style.php';

		include AFPAO_PLUGIN_DIR . 'includes/front/af-addon-front-fields.php';

		include AFPAO_PLUGIN_DIR . 'includes/admin/class-af-addon-ajax.php';

		if ( is_admin() ) {

			include AFPAO_PLUGIN_DIR . 'class-addify-product-add-ons-admin.php';

			include AFPAO_PLUGIN_DIR . 'includes/admin/class-af-addon-variation.php';

			include AFPAO_PLUGIN_DIR . 'includes/admin/class-af-addon-product.php';

			include AFPAO_PLUGIN_DIR . 'includes/admin/class-af-addon-rule.php';

		} else {

			include AFPAO_PLUGIN_DIR . 'includes/front/class-af-addon-validation.php';

			include AFPAO_PLUGIN_DIR . 'includes/front/class-af-addon-front-cart.php';

			include AFPAO_PLUGIN_DIR . 'class-addify-product-add-ons-front.php';
		}
	}

	public function af_addon_init() {

		// Check the installation of WooCommerce module if it is not a multi site.
		if ( ! is_multisite() ) {

			if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {

				add_action( 'admin_notices', array( $this, 'af_addon_check_wocommerce' ) );
			}
		}
	}

	public function af_addon_check_wocommerce() {

		deactivate_plugins( __FILE__ );

		?>

		<div id="message" class="error">
		
			<p>
		
				<strong> 
		
					<?php esc_html_e( 'Product Options and Fields plugin is inactive. WooCommerce plugin must be active in order to activate it.', 'addify_pao' ); ?>
		
				</strong>
		
			</p>
		
		</div>
		
		<?php
	}

	public function af_po_h_o_p_s_compatibility() {

		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {

			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}

	/**
	 * Path define function starts.
	 */
	public function afpao_apbgp_global_constents_vars() {

		if ( ! defined( 'AFPAO_URL' ) ) {

			define( 'AFPAO_URL', plugin_dir_url( __FILE__ ) );
		}

		if ( ! defined( 'AFPAO_BASENAME' ) ) {

			define( 'AFPAO_BASENAME', plugin_basename( __FILE__ ) );
		}

		if ( ! defined( 'AFPAO_PLUGIN_DIR' ) ) {

			define( 'AFPAO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		$upload_dir = wp_upload_dir();

		$upload_path = $upload_dir['basedir'] . '/addify-product-addons/';

		if ( ! is_dir( $upload_path ) ) {

			mkdir( $upload_path );
		}

		if ( ! defined( 'AFPAO_MEDIA_PATH' ) ) {

			define( 'AFPAO_MEDIA_PATH', $upload_path );
		}

		$upload_url = $upload_dir['baseurl'] . '/addify-product-addons/';

		if ( ! defined( 'AFPAO_MEDIA_URL' ) ) {

			define( 'AFPAO_MEDIA_URL', $upload_url );
		}
	}
	/**
	 * Define class.
	 */
	public function afpao_accm_init() {

		if ( function_exists( 'load_plugin_textdomain' ) ) {

			load_plugin_textdomain( 'addify_pao', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}
	}

	public function af_pao_custom_post_type() {

		$labels = array(
			'name'           => __( 'Product Options', 'addify_pao' ),
			'singular_name'  => __( 'Product Option', 'addify_pao' ),
			'menu_name'      => __( 'Product Options', 'addify_pao' ),
			'add_new'        => __( 'Add New Product Option', 'addify_pao' ),
			'name_admin_bar' => __( 'Product Options', 'addify_pao' ),
			'edit_item'      => __( 'Edit Product Option', 'addify_pao' ),
			'view_item'      => __( 'View Product Option', 'addify_pao' ),
			'all_items'      => __( 'Product Options', 'addify_pao' ),
			'search_items'   => __( 'Search Product Option', 'addify_pao' ),
			'not_found'      => __( 'No Product Options found', 'addify_pao' ),
			'attributes'     => __( 'Rule Priority', 'addify_pao' ),
		);

		$args = array(
			'supports'            => array( 'title', 'page-attributes' ),
			'labels'              => $labels,
			'description'         => __( 'Product Options and Fields', 'addify_pao' ),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=product',
			'exclude_from_search' => true,
			'capability_type'     => 'post',
			'query_var'           => true,
			'menu_icon'           => plugins_url( '/assets/addify-logo.png', __FILE__ ),
			'rewrite'             => array( 'slug' => 'af_addon' ),
			'hierarchical'        => false,
		);

		register_post_type( 'af_addon', $args );

		$labels = array(
			'name'           => __( 'Add options', 'addify_pao' ),
			'singular_name'  => __( 'Add options', 'addify_pao' ),
			'menu_name'      => __( 'Add options', 'addify_pao' ),
			'add_new'        => __( 'Add new option', 'addify_pao' ),
			'name_admin_bar' => __( 'Product options', 'addify_pao' ),
			'edit_item'      => __( 'Edit option', 'addify_pao' ),
			'view_item'      => __( 'View option', 'addify_pao' ),
			'all_items'      => __( 'Product add options', 'addify_pao' ),
			'search_items'   => __( 'Search options', 'addify_pao' ),
			'not_found'      => __( 'No options found', 'addify_pao' ),
		);

		$args = array(
			'supports'            => array( 'title', 'page-attributes', 'thumbnail' ),
			'labels'              => $labels,
			'description'         => __( 'Add Option in addon fields.', 'addify_pao' ),
			'public'              => false,
			'show_ui'             => false,
			'show_in_menu'        => false,
			'exclude_from_search' => true,
			'capability_type'     => 'post',
			'query_var'           => true,
			'menu_icon'           => plugins_url( '/assets/addify-logo.png', __FILE__ ),
			'rewrite'             => array(
				'slug' => 'af_pao_options',
			),
			'hierarchical'        => false,
		);

		register_post_type( 'af_pao_options', $args );

		$labels = array(
			'name'           => __( 'Add fields', 'addify_pao' ),
			'singular_name'  => __( 'Add fields', 'addify_pao' ),
			'menu_name'      => __( 'Add fields', 'addify_pao' ),
			'add_new'        => __( 'Add New add field', 'addify_pao' ),
			'name_admin_bar' => __( 'Product add fields', 'addify_pao' ),
			'edit_item'      => __( 'Edit add fields', 'addify_pao' ),
			'view_item'      => __( 'View add fields', 'addify_pao' ),
			'all_items'      => __( 'Product add fields', 'addify_pao' ),
			'search_items'   => __( 'Search add fields', 'addify_pao' ),
			'not_found'      => __( 'No add fields found', 'addify_pao' ),
		);

		$args = array(
			'supports'            => array( 'title', 'page-attributes' ),
			'labels'              => $labels,
			'description'         => __( 'Custom Add-Fields', 'addify_pao' ),
			'public'              => false,
			'show_ui'             => false,
			'show_in_menu'        => false,
			'exclude_from_search' => true,
			'capability_type'     => 'post',
			'query_var'           => true,
			'menu_icon'           => plugins_url( '/assets/addify-logo.png', __FILE__ ),
			'rewrite'             => array(
				'slug' => 'af_pao_fields',
			),
			'hierarchical'        => false,
		);

		register_post_type( 'af_pao_fields', $args );
	}
}

new Addify_Product_Add_Ons_Main();
