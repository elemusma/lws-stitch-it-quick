<?php

namespace Barn2\Plugin\WC_Product_Options;

use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Registerable;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Service;
use Automattic\WooCommerce\Utilities\OrderUtil;

/**
 * A scheduled task to periodically check for unused uploaded files.
 *
 * @package   Barn2\woocommerce-product-options
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class File_Cleanup implements Registerable, Service {

	private $plugin_file;

	/**
	 * Constructor
	 *
	 * @param mixed $plugin_file
	 */
	public function __construct( $plugin ) {
		$this->plugin_file = $plugin->get_file();

		// check for action scheduler in WC
		if ( ! class_exists( 'ActionScheduler' ) ) {
			require_once WC_ABSPATH . 'includes/libraries/action-scheduler/action-scheduler.php';
		}
	}

	/**
	 * Register hooks and filters.
	 */
	public function register() {
		add_action( 'init', [ $this, 'schedule' ] );
	}

	/**
	 * Retrieve all orders with uploaded files and compare to existing unassociated files.
	 */
	public function run() {
		$this->remove_unused_files();
	}

	/**
	 * Schedules the event.
	 */
	public function schedule() {
		add_action( $this->get_cron_hook(), [ $this, 'run' ] );

		if ( ! as_has_scheduled_action( $this->get_cron_hook(), null, '' ) ) {
			as_schedule_recurring_action( time(), $this->get_interval(), $this->get_cron_hook(), [], '' );
		}

		// Un-schedule the event on plugin deactivation.
		register_deactivation_hook( $this->plugin_file, [ $this, 'unschedule' ] );
	}

	/**
	 * Unschedule the event.
	 */
	public function unschedule() {
		if ( as_has_scheduled_action( $this->get_cron_hook(), null, '' ) ) {
			as_unschedule_all_actions( $this->get_cron_hook(), [], '' );
		}
	}

	/**
	 * Retrieves all files associated with orders.
	 *
	 * @return array
	 */
	private function get_order_associated_files() {
		global $wpdb;

		$files = [];

		if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
			// HPOS usage is enabled.
			$query_result = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT meta_value FROM {$wpdb->prefix}wc_orders_meta WHERE meta_key = %s",
					'_wpo_files'
				),
				ARRAY_N
			);
		} else {
			// Classic postmeta query
			$query_result = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key = %s",
					'_wpo_files'
				),
				ARRAY_N
			);
		}

		if ( $query_result ) {
			foreach ( $query_result as $file_array ) {
				$parsed_files = maybe_unserialize( $file_array[0] );

				if ( is_array( $parsed_files ) ) {
					$files = array_merge( $files, $parsed_files );
				}
			}
		}

		return $files;
	}

	/**
	 * Compare the files in the order meta to the files in the uploads directory.
	 *
	 * @return void
	 */
	private function remove_unused_files() {
		$files       = get_option( 'wpo_unlinked_files', [] );
		$order_files = $this->get_order_associated_files();
		$upload_dir  = wp_upload_dir();

		// run an extra check against the files linked to orders
		$unlinked_files = array_diff( $files, $order_files );

		if ( empty( $unlinked_files ) ) {
			return;
		}

		/**
		 * Filter the file expiry time.
		 *
		 * @param int $file_expiry The time in seconds after which a file is considered expired.
		 */
		$file_expiry = apply_filters( 'wc_product_options_file_expiry_time', WEEK_IN_SECONDS );

		// check if file is older than `$file_expiry` seconds (detault 1 week)
		foreach ( $unlinked_files as $file ) {
			// convert url to path basedir with baseurl
			$file_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $file );

			if ( time() > ( filemtime( $file_path ) + $file_expiry ) ) {
				unlink( $file_path );

				// remove folder if empty
				$folder = dirname( $file_path );

				if ( ! glob( $folder . '/*' ) ) {
					rmdir( $folder );
				}

				// remove from unlinked files
				$files = array_diff( $files, [ $file ] );

				update_option( 'wpo_unlinked_files', $files );
			}
		}
	}

	/**
	 * Get the name of the scheduled task.
	 *
	 * @return string
	 */
	protected function get_cron_hook(): string {
		return 'wpo-file-cleanup';
	}

	/**
	 * Get the interval at which the task should run.
	 *
	 * @return string
	 */
	protected function get_interval(): string {
		return DAY_IN_SECONDS;
	}
}
