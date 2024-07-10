<?php
/**
 * Shipping zone method for WooCommerce UPS Shipping Plugin with Print Label.
 *
 * @package ups-woocommerce-shipping
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class PH_WC_UPS_Shipping_Zone_Method extending WC_Shipping_Method
 */
class PH_WC_UPS_Shipping_Zone_Method extends WC_Shipping_Method {

	/**
	 * Vendor Id
	 */
	public $vendor_id = null;

	/**
	 * General
	 */
	public $settings, $debug;

	/**
	 * Location variables
	 */
	public $origin_country_state, $origin_country, $origin_state;

	/**
	 * Ship from location variables
	 */
	public $ship_from_country_state, $ship_from_country, $ship_from_state;

	/**
	 * Services
	 */
	public $custom_services, $service_code, $ordered_services, $services = array();

	/**
	 * Packing
	 */
	public $boxes, $ups_packaging, $units, $dim_unit, $weight_unit, $upsSimpleRate, $simpleRateBoxes, $packaging;

	/**
	 * Estimated delivery variables
	 */
	public $enable_estimated_delivery, $estimated_delivery_text, $wp_date_time_format;

	/**
	 * Other variables
	 */
	public $current_package_items_and_quantity, $international_delivery_confirmation_applicable, $ph_ups_selected_access_point_details;

	/**
	 * Constructor for the shipping method class.
	 *
	 * @param int   $instance_id The instance ID for the shipping method. Default is 0.
	 * @param mixed $order       The order object or null. Default is null.
	 */
	public function __construct( $instance_id = 0, $order = null ) {

		$plugin_config = ph_wc_ups_shipping_zone_method_configuration();

		$this->id                 = $plugin_config['id'];
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( $plugin_config['method_title'], 'ups-woocommerce-shipping' );
		$this->method_description = __( $plugin_config['method_description'], 'ups-woocommerce-shipping' );

		$this->supports = array(
			'shipping-zones',
			'instance-settings',
			// 'instance-settings-modal',   // For Pop-up Modal
		);

		// To avoid conflict with third-party plugins
		if (!class_exists('PH_WC_UPS_Settings_Helper')) {

			require_once PH_WC_UPS_PLUGIN_DIR_PATH . 'includes/settings/class-ph-ups-settings-helper.php';
		}

		$settings_helper = new PH_WC_UPS_Settings_Helper();
		$this->settings  = $settings_helper->settings;

		$this->init();
	}

	/**
	 * init function.
	 *
	 * @access public
	 * @return void
	 */
	function init() {

		$this->init_form_fields();
		$this->init_instance_settings();

		$this->title 			= isset( $this->instance_settings['title'] ) && ! empty( $this->instance_settings['title'] ) ? $this->instance_settings['title'] : $this->method_title;
		$this->custom_services  = isset($this->instance_settings['services']) ? $this->instance_settings['services'] : array();
		
		// Save settings in admin.
		add_action( 'woocommerce_update_options_shipping_' . $this->id . '_' . $this->instance_id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * UPS Shipping Zone form fields.
	 */
	function init_form_fields() {

		$this->origin_country = PH_WC_UPS_Common_Utils::ph_get_origin_country_and_state( $this->settings, 'country' );
		$this->services 	  = PH_WC_UPS_Common_Utils::get_services_based_on_origin( $this->origin_country );

		$this->instance_form_fields = include PH_WC_UPS_PLUGIN_DIR_PATH . 'includes/settings/data-ph-ups-settings.php';
	}

	function admin_options() {
		?>
		<h2><?php _e( $this->method_title, 'ups-woocommerce-shipping' ); ?></h2>
		<?php echo $this->method_description; ?>
		<div class="clear"></div>
		<br/>
		<table class="form-table">
			<?php $this->generate_settings_html( $this->instance_form_fields ); ?>
		</table>
		<?php
	}

	function generate_services_html() {

		ob_start();
			include PH_WC_UPS_PLUGIN_DIR_PATH . 'includes/admin-views/ph-ups-service-list-html.php';
		return ob_get_clean();
	}

	/**
	 * validate_services_field function.
	 *
	 * @access public
	 * @param mixed $key
	 * @return void
	 */
	public function validate_services_field( $key ) {
		$services        = array();
		$posted_services = $_POST['ups_service'];

		foreach ( $posted_services as $code => $settings ) {

			$services[ $code ] = array(
				'name'               => wc_clean( $settings['name'] ),
				'order'              => wc_clean( $settings['order'] ),
				'enabled'            => isset( $settings['enabled'] ) ? true : false,
				'adjustment'         => wc_clean( $settings['adjustment'] ),
				'adjustment_percent' => str_replace( '%', '', wc_clean( $settings['adjustment_percent'] ) ),
			);
		}

		return $services;
	}

	/**
	 * Calculating rates based on matching zones.
	 */
	public function calculate_shipping( $package = array() ) {
		global $woocommerce;

		if (!class_exists('PH_WC_UPS_Shipping_Controller')) {

			require_once PH_WC_UPS_PLUGIN_DIR_PATH . 'includes/utils/class-ph-ups-shipping-controller.php';
		}

		$shipping_controller = new PH_WC_UPS_Shipping_Controller( $this->settings );

		// Function to get rates for both zones and rest.
		$rates = $shipping_controller->ph_get_shipping_rates( $package, 'UPS Shipping Zone', $this->instance_id );

		// Incase of no active license or no api details or any other issues, the return value might be empty.
		if( empty($rates)) return;

		$this->current_package_items_and_quantity = $rates['current_package_items_and_quantity'];
		$this->vendor_id = $rates['vendor_id'];

		$this->ph_add_rates( $rates['all_rates'] );
	}

	/**
	 * ph_add_rates function.
	 *
	 * Adds UPS shipping rates to the WooCommerce shipping method.
	 *
	 * @param array $rates The array of UPS shipping rates.
	 * @return void
	 */
	function ph_add_rates( $rates ) {
		if ( ! empty( $rates ) ) {

			if ( 'all' == $this->settings['offer_rates'] ) {

				uasort( $rates, array( 'Ph_UPS_Woo_Shipping_Common', 'sort_rates' ) );
				foreach ( $rates as $key => $rate ) {

					$this->add_rate( $rate );
				}
			} else {

				$cheapest_rate = '';

				foreach ( $rates as $key => $rate ) {
					if ( ! $cheapest_rate || ( $cheapest_rate['cost'] > $rate['cost'] && ! empty( $rate['cost'] ) ) ) {
						$cheapest_rate = $rate;
					}
				}
				// If cheapest only without actual service name i.e Service name has to be override with method title
				if ( ! empty( $this->settings['cheapest_rate_title'] ) ) {
					$cheapest_rate['label'] = $this->settings['cheapest_rate_title'];
				}
				$this->add_rate( $cheapest_rate );
			}
			// Fallback
		} elseif ( $this->settings['fallback'] ) {
			$this->add_rate(
				array(
					'id'        => $this->id . '_fallback',
					'label'     => $this->title,
					'cost'      => $this->settings['fallback'],
					'sort'      => 0,
					'meta_data' => array(
						'_xa_ups_method' => array(
							'id'           => $this->id . '_fallback',    // Rate id will be in format PH_WC_UPS_ZONE_SHIPPING:service_id:instance_id ex for ground ph_ups_shipping:03:31
							'method_title' => $this->title,
							'items'        => isset( $this->current_package_items_and_quantity ) ? $this->current_package_items_and_quantity : array(),
						),
						'VendorId'       => ! empty( $this->vendor_id ) ? $this->vendor_id : null,
					),
				)
			);
			Ph_UPS_Woo_Shipping_Common::debug( __( 'UPS Shipping Zone : Using Fallback setting.', 'ups-woocommerce-shipping' ), $this->debug );
		}
	}
}
