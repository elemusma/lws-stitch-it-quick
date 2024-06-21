<?php

namespace Barn2\Plugin\WC_Product_Options;

use Barn2\Plugin\WC_Product_Options\Admin\Wizard\Setup_Wizard;
use Barn2\Plugin\WC_Product_Options\Dependencies\Sematico\FluentQuery\DatabaseCapsule;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Registerable;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Translatable;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Service_Provider;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Plugin\Premium_Plugin;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Plugin\Licensed_Plugin;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Util as Lib_Util;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Admin\Notices;

/**
 * The main plugin class. Responsible for setting up to core plugin services.
 *
 * @package   Barn2\woocommerce-product-options
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Plugin extends Premium_Plugin implements Licensed_Plugin, Registerable, Translatable, Service_Provider {
	const NAME        = 'WooCommerce Product Options';
	const ITEM_ID     = 461766;
	const META_PREFIX = 'wpo_';

	/**
	 * The notices object
	 *
	 * @var Notices
	 */
	private $notices;

	/**
	 * Constructs and initalizes the main plugin class.
	 *
	 * @param string $file    The root plugin __FILE__
	 * @param string $version The current plugin version
	 */
	public function __construct( $file = null, $version = '1.0' ) {
		parent::__construct(
			[
				'name'               => self::NAME,
				'item_id'            => self::ITEM_ID,
				'version'            => $version,
				'file'               => $file,
				'is_woocommerce'     => true,
				'settings_path'      => 'edit.php?post_type=product&page=wpo_options',
				'documentation_path' => 'kb-categories/product-options-kb/',
			]
		);

		$this->add_service( 'plugin_setup', new Plugin_Setup( $this->get_file(), $this ), true );
		$this->add_service( 'db', new DatabaseCapsule(), true );
		$this->get_service( 'db' )->boot();
	}

	/**
	 * Registers the plugin with WordPress.
	 */
	public function register(): void {
		parent::register();

		$this->notices = new Notices();

		if ( is_admin() ) {
			$this->notices->boot();
		}

		add_action( 'plugins_loaded', [ $this, 'add_services' ] );

		add_action( 'init', [ $this, 'register_services' ] );
		add_action( 'init', [ $this, 'load_textdomain' ], 5 );
	}

	/**
	 * Maybe bootup plugin.
	 */
	public function maybe_load_plugin(): void {
		if ( ! $this->check_wp_requirements() ) {
			return;
		}

		// Don't load anything if WooCommerce not active.
		if ( ! $this->check_wc_requirements() ) {
			return;
		}
	}

	/**
	 * Setup the plugin services
	 */
	public function add_services() {
		if ( ! $this->check_wp_requirements() ) {
			return;
		}

		// Don't load anything if WooCommerce not active.
		if ( ! $this->check_wc_requirements() ) {
			return;
		}

		$this->add_service( 'setup_wizard', new Setup_Wizard( $this ) );
		$this->add_service( 'admin_controller', new Admin\Admin_Controller( $this ) );
		$this->add_service( 'rest_controller', new Rest\Rest_Controller() );

		if ( $this->has_valid_license() ) {
			$this->add_service( 'frontend_scripts', new Frontend_Scripts( $this ) );
			$this->add_service( 'upload_directory', new Upload_Directory() );
			$this->add_service( 'file_cleanup', new File_Cleanup( $this ) );
			$this->add_service( 'handlers/single_product', new Handlers\Single_Product() );
			$this->add_service( 'handlers/add_to_cart', new Handlers\Add_To_Cart() );
			$this->add_service( 'handlers/item_data', new Handlers\Item_Data() );
			$this->add_service( 'handlers/cart', new Handlers\Cart() );
			$this->add_service( 'integration/wro', new Integration\Restaurant_Ordering() );
			$this->add_service( 'integration/wpt', new Integration\Product_Table() );
			$this->add_service( 'integration/wqv', new Integration\Quick_View_Pro() );
			$this->add_service( 'integration/wbv', new Integration\Bulk_Variations() );
			$this->add_service( 'integration/wwp', new Integration\Wholesale_Pro() );
			$this->add_service( 'integration/aelia', new Integration\Aelia_Currency_Switcher() );
			$this->add_service( 'integration/wc_wpml', new Integration\WooCommerce_Multilingual() );
			$this->add_service( 'integration/wc_subscriptions', new Integration\WooCommerce_Subscriptions() );
			$this->add_service( 'integration/theme_compat', new Integration\Theme_Compat() );
		}
	}

	/**
	 * Check all the requirements are met
	 *
	 * @return bool
	 */
	public function check_all_requirements(): bool {
		return $this->check_wp_requirements() && $this->check_wc_requirements();
	}

	/**
	 * Check the WP Requirements are met
	 *
	 * @return bool
	 */
	public function check_wp_requirements(): bool {
		global $wp_version;

		if ( version_compare( $wp_version, '5.2', '<' ) ) {
			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				// translators: %s: Plugin name.
				wp_die( esc_html__( 'The %s plugin requires WordPress 5.2 or greater. Please update your WordPress installation first.', 'woocommerce-product-options' ), esc_html( self::NAME ) );
			}

			if ( is_admin() ) {
				$can_update_core = current_user_can( 'update_core' );

				$this->notices->add(
					'wpo_invalid_wp_version',
					'',
					sprintf(
					/* translators: %1$s: Plugin name. %2$s: Update Core <a> tag open. %3$s: <a> tag close.  */
						__( 'The %1$s plugin requires WordPress 5.2 or greater. Please %2$supdate%3$s your WordPress installation.', 'woocommerce-product-options' ),
						'<strong>' . self::NAME . '</strong>',
						( $can_update_core ? sprintf( '<a href="%s">', esc_url( self_admin_url( 'update-core.php' ) ) ) : '' ),
						( $can_update_core ? '</a>' : '' )
					),
					[
						'type'       => 'error',
						'capability' => 'install_plugins',
						'screens'    => [ 'plugins', 'woocommerce_page_wc-settings' ]
					]
				);
			}
			return false;
		}

		return true;
	}

	/**
	 * Check the WooCommerce requirements are met.
	 *
	 * @return bool
	 */
	public function check_wc_requirements(): bool {
		if ( ! class_exists( 'WooCommerce' ) ) {
			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				wp_die( esc_html__( 'Please install WooCommerce in order to use WooCommerce Product Options.', 'woocommerce-product-options' ) );
			}

			if ( is_admin() ) {
				$this->notices->add(
					'wpo_woocommerce_missing',
					'',
					/* translators: %1$s: Install WooCommerce <a> tag open. %2$s: <a> tag close.  */
					sprintf( __( 'Please %1$sinstall WooCommerce%2$s in order to use WooCommerce Product Options.', 'woocommerce-product-options' ), Lib_Util::format_link_open( 'https://woocommerce.com/', true ), '</a>' ),
					[
						'type'       => 'error',
						'capability' => 'install_plugins',
						'screens'    => [ 'plugins' ],
					]
				);
			}

			return false;
		}

		global $woocommerce;

		if ( version_compare( $woocommerce->version, '5.9', '<' ) ) {
			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				// translators: %s: Plugin name.
				wp_die( esc_html__( 'The %s plugin requires WooCommerce 5.9 or greater. Please update your WooCommerce setup first.', 'woocommerce-product-options' ), esc_html( self::NAME ) );
			}

			if ( is_admin() ) {
				$this->notices->add(
					'wpo_invalid_wc_version',
					'',
					/* translators: %1$s: Plugin name. */
					sprintf( __( 'The %1$s plugin requires WooCommerce 5.9 or greater. Please update your WooCommerce setup first.', 'woocommerce-product-options' ), self::NAME ),
					[
						'type'       => 'error',
						'capability' => 'install_plugins',
						'screens'    => [ 'plugins', 'woocommerce_page_wc-settings' ],
					]
				);
			}

			return false;
		}

		return true;
	}

	/**
	 * Load the language file
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain( 'woocommerce-product-options', false, dirname( $this->get_basename() ) . '/languages' );
	}
}
