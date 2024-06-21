<?php

namespace Barn2\Plugin\WC_Product_Options\Rest\Routes;

use Barn2\Plugin\WC_Product_Options\Model\Option as Option_Model;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Rest\Base_Route;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Rest\Route;
use WP_Error;
use WP_REST_Response;
use WP_REST_Server;

/**
 * REST controller for the uploading files from the frontend.
 *
 * @package   Barn2\woocommerce-product-options
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class File_Upload extends Base_Route implements Route {

	protected $rest_base = 'file-upload';

	/**
	 * Register the REST routes.
	 */
	public function register_routes() {

		// CREATE.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create' ],
					'permission_callback' => [ $this, 'permission_callback' ]
				]
			]
		);
	}

	/**
	 * Upload file.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function create( $request ) {
		$option_id = $request->get_param( 'option_id' );
		$files     = $request->get_file_params();

		$file_data = $files['file'];

		if ( ! $option_id ) {
			return new WP_Error( 'wpo_file_upload_error', __( 'Insufficient data supplied.', 'woocommerce-product-options' ) );
		}

		if ( ! $file_data ) {
			return new WP_Error( 'wpo_file_upload_error', __( 'Insufficient data supplied.', 'woocommerce-product-options' ) );
		}

		$option = Option_Model::where( 'id', $option_id )->first();

		if ( ! $option ) {
			return new WP_Error( 'wpo_file_upload_error', __( 'Insufficient data supplied', 'woocommerce-product-options' ) );
		}

		if ( $option->type !== 'file_upload' ) {
			return new WP_Error( 'wpo_file_upload_error', __( 'Insufficient data supplied', 'woocommerce-product-options' ) );
		}

		if ( ! $this->is_allowed_file_type( $file_data, $option ) ) {
			return new WP_Error( 'wpo_file_upload_error', __( 'File type is not allowed.', 'woocommerce-product-options' ) );
		}

		// Include filesystem functions to get access to wp_handle_upload().
		require_once ABSPATH . 'wp-admin/includes/file.php';

		add_filter( 'upload_dir', [ $this, 'custom_upload_dir' ] );

		$file = wp_handle_upload( $file_data, [ 'test_form' => false ] );

		if ( ! $file || isset( $file['error'] ) ) {
			return new WP_Error(
				'rest_upload_unknown_error',
				$file,
				[ 'status' => 500 ]
			);
		}

		remove_filter( 'upload_dir', [ $this, 'custom_upload_dir' ] );

		// add path to unlinked files option
		$unlinked_files = get_option( 'wpo_unlinked_files', [] );

		if ( ! in_array( $file, $unlinked_files, true ) ) {
			$unlinked_files[] = $file['url'];
			update_option( 'wpo_unlinked_files', $unlinked_files );
		}

		return new WP_REST_Response( $file, 200 );
	}

	/**
	 * Custom upload directory.
	 *
	 * @param array $path_data
	 * @return array $path_data
	 */
	public function custom_upload_dir( $path_data ): array {

		// require wc-cart-functions for session shutdown
		require_once WC_ABSPATH . 'includes/wc-cart-functions.php';
		require_once WC_ABSPATH . 'includes/wc-notice-functions.php';

		if ( null === WC()->session ) {
			$session_class = apply_filters( 'woocommerce_session_handler', 'WC_Session_Handler' );

			// Prefix session class with global namespace if not already namespaced
			if ( false === strpos( $session_class, '\\' ) ) {
				$session_class = '\\' . $session_class;
			}

			WC()->session = new $session_class();
			WC()->session->init();
		}

		$wpo_dir     = '/wpo-uploads';
		$session_dir = hash( 'md5', WC()->session->get_customer_id() );

		$path_data['path']   = $path_data['basedir'] . $wpo_dir . '/' . $session_dir;
		$path_data['url']    = $path_data['baseurl'] . $wpo_dir . '/' . $session_dir;
		$path_data['subdir'] = '';

		return $path_data;
	}

	/**
	 * Check if the file extension is allowed for the option.
	 *
	 * @param array $file_data
	 * @param Option_Model $option
	 */
	private function is_allowed_file_type( $file_data, $option ) {
		$allowed = false;
		if ( ! isset( $option->settings ) || ! isset( $option->settings['file_upload_allowed_types'] ) || empty( $option->settings['file_upload_allowed_types'] ) ) {
			$allowed_extensions = [ 'jpg|jpeg|jpe', 'png', 'docx', 'xlsx', 'pptx', 'pdf' ];
		} else {
			$allowed_extensions = $option->settings['file_upload_allowed_types'];
		}

		$mime_type_map = get_allowed_mime_types();

		foreach ( $allowed_extensions as $extension ) {
			$mime_type = isset( $mime_type_map[ $extension ] ) ? $mime_type_map[ $extension ] : null;

			if ( is_null( $mime_type ) ) {
				continue;
			}

			if ( $file_data['type'] === $mime_type ) {
				$allowed = true;
				break;
			}
		}

		return $allowed;
	}

	/**
	 * Permission callback.
	 *
	 * @return bool
	 */
	public function permission_callback() {
		return true;
	}
}
