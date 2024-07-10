<?php
/**
 * PH UPS Dependency Checker
 */
class PH_UPS_Dependencies {

	private static $active_plugins;

	public static function init() {

		self::$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() )
			self::$active_plugins = array_merge( self::$active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
	}

	public static function ph_is_woocommerce_active() {

		if ( ! self::$active_plugins ) self::init();

		return in_array( 'woocommerce/woocommerce.php', self::$active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', self::$active_plugins );
	}

	public static function ph_plugin_active_check( $plugin_path ) {

		if ( ! self::$active_plugins ) self::init();

		return in_array( $plugin_path, self::$active_plugins ) || array_key_exists( $plugin_path, self::$active_plugins );
	} 
}