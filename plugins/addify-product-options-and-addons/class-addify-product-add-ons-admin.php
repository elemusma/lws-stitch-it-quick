<?php
/**
 * Admin class start.
 *
 * @package : Addify Product Add Ons
 */

defined( 'ABSPATH' ) || exit;

/** Admin class. **/
class Addify_Product_Add_Ons_Admin {

	/** Constructor. **/
	public function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'af_pao_enqueue_script' ) );

		add_action( 'woocommerce_after_order_itemmeta', array( $this, 'af_pao_show_option_data_in_order_detail' ), 10, 3 );

		add_action( 'wp_loaded', array( $this, 'af_pao_export_csv' ) );
	}

	/** Enqueue scripts and css. **/
	public function af_pao_enqueue_script() {

		wp_enqueue_media();

		wp_enqueue_style( 'pao_admin_css', plugins_url( 'assets/css/addify-pao-admin.css', __FILE__ ), false, '1.0.0' );

		wp_enqueue_script( 'pao_admin_js', plugins_url( 'assets/js/addify-pao-admin.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );

		wp_enqueue_style( 'select2-css', plugins_url( 'assets/css/select2.css', WC_PLUGIN_FILE ), array(), '5.7.2' );

		wp_enqueue_script( 'select2-js', plugins_url( 'assets/js/select2/select2.min.js', WC_PLUGIN_FILE ), array( 'jquery' ), '4.0.3', true );

		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_script( 'wp-color-picker' );

		wp_enqueue_style( 'af_pao_font', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', false, '1.0', false );

		$af_pao_ajax_nonce = array(
			'admin_url' => admin_url( 'admin-ajax.php' ),
			'nonce'     => wp_create_nonce( '_addify_pao_nonce' ),
		);
		wp_localize_script( 'pao_admin_js', 'addify_product_add_ons', $af_pao_ajax_nonce );
	}

	/**
	 * Show in order detail page.
	 *
	 * @param int $item_id order item id.
	 * @param int $item order item object.
	 * @param int $order order object.
	 */
	public function af_pao_show_option_data_in_order_detail( $item_id, $item, $order ) {

		foreach ( $item->get_meta_data() as $item_data ) {

			$item_data_array = $item_data->get_data();
			if ( in_array( 'selected_item_post_id', $item_data_array ) && is_array( $item_data_array['value'] ) ) {
				$get_added_data = $item_data_array['value'];

				foreach ( $get_added_data as $option_field_id_array ) {

					foreach ( $option_field_id_array as $option_field_id_array ) {

						$pao_price = '';

						if ( in_array( 'field_id', array_keys( $option_field_id_array ) ) ) {
							$field_id_value = $option_field_id_array['field_id'];
							$field_type     = $option_field_id_array['field_type'];

							$field_title = get_post_meta( $field_id_value, 'af_addon_field_title', true );

							if ( $field_title ) {
								?>
								<br>
								<b> <?php echo esc_attr( $field_title . ':' ); ?> </b>
								<br>
								<?php
							}

							$field_type = $option_field_id_array['field_type'];

							if ( 'multi_select' == $field_type || 'check_boxes' == $field_type ) {

								$get_multi_select_val = (array) $option_field_id_array['value'];

								foreach ( $get_multi_select_val as $option_id_value ) {

									$option_name  = get_post_meta( $option_id_value, 'af_addon_field_options_name', true );
									$option_price = (float) get_post_meta( $option_id_value, 'af_addon_field_options_price', true );
									$price_type   = get_post_meta( $option_id_value, 'af_addon_field_options_price_type', true );

									if ( 'free' == $price_type ) {

										$pao_price = $option_name;
									} elseif ( 'flat_fixed_fee' == $price_type || 'fixed_fee_based_on_quantity' == $price_type ) {

										$pao_price = $option_name . ' (+' . get_woocommerce_currency_symbol() . ' ' . number_format( $option_price, 2, '.', '' ) . ')';
									} elseif ( 'flat_percentage_fee' == $price_type || 'Percentage_fee_based_on_quantity' == $price_type ) {

										$pao_price = $option_name . ' (+' . number_format( $option_price, 2, '.', '' ) . '%)';
									}

									echo esc_attr( $pao_price );

									if ( end( $get_multi_select_val ) != $option_id_value ) {
										echo '<br>';
									}
								}
							} else {

								$field_id = $option_field_id_array['field_id'];

								if ( 'file_upload' == $option_field_id_array['field_type'] ) {

									$upload = wp_upload_dir();
									$url    = $upload['baseurl'] . '/addify-product-addons/uploaded-files/' . $option_field_id_array['value'];
									?>
									<a target="_blank" href="<?php echo esc_attr( $url ); ?>">
										<i class="fa fa-eye">
											<?php echo esc_attr( $option_field_id_array['value'] ); ?>
										</i>
									</a>
									<?php
								}

								if ( 'drop_down' == $field_type || 'image' == $field_type || 'image_swatcher' == $field_type || 'color_swatcher' == $field_type || 'radio' == $field_type ) {

									$option_value = $option_field_id_array['value'];
									$option_name  = get_post_meta( $option_value, 'af_addon_field_options_name', true );
									$option_price = (float) get_post_meta( $option_value, 'af_addon_field_options_price', true );
									$price_type   = get_post_meta( $option_value, 'af_addon_field_options_price_type', true );
									if ( 'free' == $price_type ) {

										$pao_price = $option_name;
									} elseif ( 'flat_fixed_fee' == $price_type || 'fixed_fee_based_on_quantity' == $price_type ) {

										$pao_price = $option_name . ' (+' . get_woocommerce_currency_symbol() . ' ' . number_format( $option_price, 2, '.', '' ) . ')';
									} elseif ( 'flat_percentage_fee' == $price_type || 'Percentage_fee_based_on_quantity' == $price_type ) {

										$pao_price = $option_name . ' (+' . number_format( $option_price, 2, '.', '' ) . '%)';
									}

									echo esc_attr( $pao_price );

								} else {

									$option_price = (float) get_post_meta( $field_id, 'af_addon_field_price', true );
									$price_type   = get_post_meta( $field_id, 'af_addon_field_price_type', true );
									if ( 'flat_fixed_fee' == $price_type || 'fixed_fee_based_on_quantity' == $price_type ) {

										$pao_price = ' (+' . get_woocommerce_currency_symbol() . ' ' . number_format( $option_price, 2, '.', '' ) . ')';
									} elseif ( 'flat_percentage_fee' == $price_type || 'Percentage_fee_based_on_quantity' == $price_type ) {

										$pao_price = ' (+' . number_format( $option_price, 2, '.', '' ) . '%)';
									}
									if ( 'file_upload' != $option_field_id_array['field_type'] ) {
										?>
										<b><?php echo esc_attr( $option_field_id_array['value'] . ' ' . $pao_price ); ?></b>
										<?php
									} else {
										?>
										<b><?php echo esc_attr( $pao_price ); ?></b>
										<?php
									}
								}

								$pao_price = '';
							}
						}
					}
				}
			}
		}
	}

	/** CSV Import Export. **/
	public function af_pao_export_csv() {

		include AFPAO_PLUGIN_DIR . 'includes/admin/csv/class-af-addon-csv-import-export.php';

		$class = new Af_Addon_Csv_Import_Export();

		if ( ! empty( $_FILES['af_addon_import_file'] ) && isset( $_POST['af_addon_done_import_button'] ) && isset( $_POST['for_import_current_post_id'] ) ) {

			$nonce = isset( $_POST['csv_form_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['csv_form_nonce_field'] ) ) : 0;

			if ( ! wp_verify_nonce( $nonce, 'csv_form_nonce' ) ) {
				die( 'Failed security check' );
			}

			$class->af_addon_import_csv( $_POST, $_FILES );
		}

		if ( isset( $_POST['af_addon_export_button'] ) && isset( $_POST['Export_data_of_current_rule'] ) ) {

			$nonce = isset( $_POST['csv_export_form_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['csv_export_form_nonce_field'] ) ) : 0;
			if ( ! wp_verify_nonce( $nonce, 'csv_export_form_nonce' ) ) {
				die( 'Failed security check' );
			}

			$class->af_addon_export_csv( $_POST );
		}
	}
}

new Addify_Product_Add_Ons_Admin();
