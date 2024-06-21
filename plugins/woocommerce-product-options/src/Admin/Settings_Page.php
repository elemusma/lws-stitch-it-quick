<?php

namespace Barn2\Plugin\WC_Product_Options\Admin;

use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Conditional;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Plugin\License\License;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Plugin\Licensed_Plugin;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Registerable;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Service;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Util;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Admin\Settings_Util;

/**
 * The settings page.
 *
 * @package   Barn2\woocommerce-product-options
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Settings_Page implements Service, Registerable, Conditional {

	/**
	 * Plugin handling the page.
	 *
	 * @var Licensed_Plugin
	 */
	public $plugin;

	/**
	 * License handler.
	 *
	 * @var License
	 */
	public $license;

	/**
	 * List of settings.
	 *
	 * @var array
	 */
	public $registered_settings = [];

	/**
	 * Constructor.
	 *
	 * @param Licensed_Plugin $plugin
	 */
	public function __construct( Licensed_Plugin $plugin ) {
		$this->plugin              = $plugin;
		$this->license             = $plugin->get_license();
		$this->registered_settings = $this->get_settings_tabs();
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_required() {
		return Util::is_admin();
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$this->register_settings_tabs();

		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Retrieves the settings tab classes.
	 *
	 * @return array
	 */
	private function get_settings_tabs(): array {
		$settings_tabs = [
			Settings_Tab\Product_Options::TAB_ID => new Settings_Tab\Product_Options( $this->plugin ),
			Settings_Tab\General::TAB_ID         => new Settings_Tab\General( $this->plugin ),
		];

		return $settings_tabs;
	}

	/**
	 * Register the settings tab classes.
	 *
	 * @return void
	 */
	private function register_settings_tabs(): void {
		array_map(
			function( $setting_tab ) {
				if ( $setting_tab instanceof Registerable ) {
					$setting_tab->register();
				}
			},
			$this->registered_settings
		);
	}

	/**
	 * Enqueue reg for the settings page.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		$screen = get_current_screen();

		if ( $screen->base !== 'product_page_wpo_options' ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script( 'wpo-settings-page' );
		wp_enqueue_style( 'wpo-settings-page' );
	}

	/**
	 * Register the Settings submenu page.
	 */
	public function add_settings_page() {
		add_submenu_page(
			'edit.php?post_type=product',
			__( 'Product Options', 'woocommerce-product-options' ),
			__( 'Product Options', 'woocommerce-product-options' ),
			'manage_woocommerce',
			'wpo_options',
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Render the Settings page.
	 */
	public function render_settings_page(): void {
		$active_tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ?? 'product_options';

		?>
		<div class='woocommerce-layout__header'>
			<div class="woocommerce-layout__header-wrapper">
				<h3 class='woocommerce-layout__header-heading'>
					<?php esc_html_e( 'Product Options', 'woocommerce-product-options' ); ?>
				</h3>
				<div class="links-area">
					<?php $this->support_links(); ?>
				</div>
			</div>
		</div>

		<div class="wrap barn2-settings">

			<?php do_action( 'barn2_before_plugin_settings', $this->plugin->get_id() ); ?>

			<div class="barn2-settings-inner">

				<h2 class="nav-tab-wrapper">
					<?php
					foreach ( $this->registered_settings as $setting_tab ) {
						$active_class = $active_tab === $setting_tab::TAB_ID ? ' nav-tab-active' : '';
						?>
							<a href="<?php echo esc_url( add_query_arg( 'tab', $setting_tab::TAB_ID, $this->plugin->get_settings_page_url() ) ); ?>" class="<?php echo esc_attr( sprintf( 'nav-tab%s', $active_class ) ); ?>">
								<?php echo esc_html( $setting_tab->get_title() ); ?>
							</a>
							<?php
					}
					?>
				</h2>

				<h1></h1>

				<div class="barn2-inside-wrapper">
					<?php if ( $active_tab === 'product_options' ) : ?>
						<?php echo $this->registered_settings[ $active_tab ]->output(); //phpcs:ignore ?>
					<?php else : ?>
						<h2>
							<?php esc_html_e( 'General', 'woocommerce-product-options' ); ?>
						</h2>
						<p>
							<?php esc_html_e( 'The following options control the WooCommerce Product Options extension.', 'woocommerce-product-options' ); ?>
						</p>

						<form action="options.php" method="post">
							<?php
							settings_errors();
							settings_fields( $this->registered_settings[ $active_tab ]::OPTION_GROUP );
							do_settings_sections( $this->registered_settings[ $active_tab ]::MENU_SLUG );
							?>

							<p class="submit">
								<input name="Submit" type="submit" name="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'woocommerce-product-options' ); ?>" />
							</p>
						</form>
					<?php endif; ?>
				</div>

			</div>

			<?php do_action( 'barn2_after_plugin_settings', $this->plugin->get_id() ); ?>
		</div>
		<?php
	}

	/**
	 * Output the Barn2 Support Links.
	 */
	public function support_links(): void {
		printf(
			'<p>%s</p>',
			// phpcs:reason The output is already escaped in the Settings_Util::get_help_links() method.
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			Settings_Util::get_help_links( $this->plugin )
		);
	}
}
