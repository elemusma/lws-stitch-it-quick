<?php

namespace Barn2\Plugin\WC_Product_Options;

use Barn2\Plugin\WC_Product_Options\Util\Price as Price_Util;
use Barn2\Plugin\WC_Product_Options\Util\Util;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Conditional;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Registerable;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Service;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Util as Lib_Util;

/**
 * Handles the registering of the front-end scripts and stylesheets.
 *
 * @package   Barn2\woocommerce-product-options
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Frontend_Scripts implements Service, Registerable, Conditional {

	private $plugin;

	/**
	 * Constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_required(): bool {
		return Lib_Util::is_front_end();
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ], 15 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ], 20 );
	}

	/**
	 * Register the front-end assets.
	 */
	public function register_assets(): void {
		$scripts = [
			'wpo-single-product',
			'wpo-product-table',
			'wpo-quick-view',
			'wpo-restaurant-order',
		];

		$user_locale      = preg_replace( '/_.*$/', '', get_user_locale() );
		$flatpickr_locale = file_exists( $this->plugin->get_dir_path() . "assets/js/flatpickr/l10n/$user_locale.js" ) ? $user_locale : 'default';

		$script_params = [
			'rest_url'          => esc_url_raw( rest_url( 'wc-product-options/v1' ) ),
			'rest_nonce'        => wp_create_nonce( 'wp_rest' ),
			'currency'          => Price_Util::get_currency_data(),
			'locale'            => $user_locale,

			/**
			 * Filters whether to create thumbnails for uploaded images.
			 *
			 * Example:
			 * ```
			 * add_filter( 'wc_product_options_create_upload_thumbnails', '__return_true' );
			 * ```
			 *
			 * @param bool $create_thumbnails Whether to create thumbnails for uploaded images. Default false.
			 */
			'create_thumbnails' => apply_filters( 'wc_product_options_create_upload_thumbnails', true ),
			'tax_info'          => new \WC_Tax(),
		];

		foreach ( $scripts as $script ) {
			$this->plugin->register_script(
				$script,
				"assets/js/$script.js",
				Lib_Util::get_script_dependencies( $this->plugin, "$script.js" )['dependencies']
			);

			Util::add_inline_script_params(
				$script,
				'wpoSettings',
				$script_params
			);
		}

		$this->plugin->register_script(
			'wpo-flatpickr-l10n',
			"assets/js/flatpickr/l10n/$flatpickr_locale.js",
			[]
		);

		wp_register_style(
			'wpo-frontend-fields',
			plugins_url( 'assets/css/wpo-frontend-fields.css', $this->plugin->get_file() ),
			[],
			$this->plugin->get_version()
		);
	}

	/**
	 * Enqueue the front-end assets.
	 */
	public function enqueue_assets(): void {
		if ( is_product() ) {
			wp_enqueue_script( 'wpo-single-product' );
			wp_enqueue_script( 'wpo-flatpickr-l10n' );
			wp_enqueue_style( 'wpo-frontend-fields' );
		}
	}
}
