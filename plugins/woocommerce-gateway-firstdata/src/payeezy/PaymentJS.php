<?php
/**
 * WooCommerce First Data
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@kestrelwp.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce First Data to newer
 * versions in the future. If you wish to customize WooCommerce First Data for your
 * needs please refer to http://docs.woocommerce.com/document/firstdata/
 *
 * @author      Kestrel
 * @copyright   Copyright (c) 2013-2024, Kestrel
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

namespace Atreus\WooCommerce\First_Data\Payeezy;

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\PluginFramework\v5_11_12 as Framework;

/**
 * PaymentJS form handler.
 *
 * This class handles the payment form managed by Payment.JS after checkout, in pay page.
 *
 * @since 4.7.0
 *
 * @method \WC_Gateway_First_Data_Payeezy_Credit_Card get_gateway()
 */
class PaymentJS extends Framework\SV_WC_Payment_Gateway_Payment_Form {


	/**
	 * Gets the payment form handler class name.
	 *
	 * @since 4.7.3
	 *
	 * @return string
	 */
	protected function get_js_handler_class_name() {

		return 'WC_First_Data_Payeezy_Payment_JS_Form_Handler';
	}


	/**
	 * Adds credit card fields data as expected by Payment.JS script.
	 *
	 * @since 4.7.0
	 *
	 * @return array
	 */
	public function get_credit_card_fields() {

		return array_merge(
			[
				'card-name' => [
					'type'        => 'text',
					'label'       => esc_html__( 'Cardholder full name', 'woocommerce-gateway-firstdata' ),
					'id'          => 'wc-' . $this->get_gateway()->get_id_dasherized() . '-name',
					'name'        => 'wc-' . $this->get_gateway()->get_id_dasherized() . '-name',
					'placeholder' => '',
					'required'    => true,
					'class'       => [ 'form-row-wide' ],
					'input_class' => [],
					'value'       => '',
				],
			],
			parent::get_credit_card_fields()
		);
	}


	/**
	 * Renders the payment fields.
	 *
	 * Overridden to add the client token hidden input.
	 *
	 * @since 4.7.0
	 */
	public function render_payment_fields() {

		parent::render_payment_fields();

		?>
		<input
			type="hidden"
			name="<?php echo esc_attr( 'wc-' . $this->get_gateway()->get_id_dasherized() . '-client-token' ); ?>"
			value=""
		/>
		<?php
	}


	/**
	 * Outputs a payment form field.
	 *
	 * @since 4.7.0
	 *
	 * @param array $field payment field data
	 */
	protected function render_payment_field( $field ) {

		$field['input_class'][] = 'wc-' . $this->get_gateway()->get_id_dasherized() . '-hosted-field';
		$field['input_class'][] = 'wc-' . $this->get_gateway()->get_plugin()->get_id_dasherized() . '-hosted-field';

		?>
		<div class="form-row <?php echo implode( ' ', array_map( 'sanitize_html_class', $field['class'] ) ); ?>">

			<label for="<?php echo esc_attr( $field['id'] ) . '-wrapper'; ?>"><?php echo esc_html( $field['label'] ); if ( $field['required'] ) : ?><abbr class="required" title="required">&nbsp;*</abbr><?php endif; ?></label>

			<div
					id="<?php echo esc_attr( $field['id'] ) . '-wrapper'; ?>"
					class="<?php echo implode( ' ', array_map( 'esc_attr', $field['input_class'] ) ); ?>"
			></div>

		</div>
		<?php
	}


	/**
	 * Gets the Payment.JS script configuration data.
	 *
	 * @link https://docs.paymentjs.firstdata.com/#library
	 *
	 * @since 4.7.0
	 *
	 * @return array associative array (will be used as a JS object)
	 */
	private function get_payment_js_script_configuration() {

		$fields    = $this->get_credit_card_fields();
		$selectors = [
			'name' => 'card-name',
			'card' => 'card-number',
			'exp'  => 'card-expiry',
			'cvv'  => 'card-csc',
		];

		foreach ( $selectors as $id => $field ) {

			if ( ! empty( $fields[ $field ] ) ) {

				$selectors[ $id ] = [
					'selector'    => '#' . $fields[ $field ]['id'] . '-wrapper',
					'placeholder' => $fields[ $field ]['placeholder'],
				];
			}
		}

		/**
		 * Filters the Payment.JS configuration.
		 *
		 * @link https://docs.paymentjs.firstdata.com/#library
		 *
		 * @since 4.7.0
		 *
		 * @param array $configuration associative array
		 */
		return (array) apply_filters( 'wc_' . $this->get_gateway()->get_id() . '_payment_js_script_configuration', [
			// input fields selectors (Payment.JS will look for fields with the following data attributes)
			'fields'  => $selectors,
			// input fields styles (Payment.JS will assign these to inputs in iframes)
			'styles'  => $this->get_payment_fields_styles(),
			// input fields state classes (Payment.JS will assign these to inputs according to their state)
			'classes' => $this->get_payment_fields_css_classes(),
		] );
	}


	/**
	 * Gets Payment.JS payment fields CSS classes.
	 *
	 * This is used in the client script JS to set CSS classes for the inputs in iframes according to their current state.
	 * Third parties can filter these to alter the input styles to match their theme.
	 * @link https://docs.paymentjs.firstdata.com/#library
	 *
	 * @since 4.7.0-dev.w
	 *
	 * @return array associative array of JS states (keys) and CSS classes (values)
	 */
	private function get_payment_fields_css_classes() {

		$classes = [
			'empty'   => 'empty',
			'focus'   => 'focus',
			'invalid' => 'invalid',
			'valid'   => 'valid',
		];

		/**
		 * Filters the CSS classes for payment fields output by Payment.JS.
		 *
		 * @link https://docs.paymentjs.firstdata.com/#library
		 *
		 * @since 4.7.0
		 *
		 * @param array $classes associative array
		 */
		return (array) apply_filters( 'wc_' . $this->get_gateway()->get_id() . '_payment_js_payment_fields_css_classes', $classes );
	}


	/**
	 * Gets Payment.JS payment fields styles.
	 *
	 * This is used in the client JavaScript to set styles for the inputs in iframes.
	 * Third parties can filter these to alter the input styles to match their theme.
	 * @link https://docs.paymentjs.firstdata.com/#library
	 *
	 * @since 4.7.0
	 *
	 * @return array associative array of elements and key-value style rules
	 */
	private function get_payment_fields_styles() {

		$styles = [
			'input'       => [
				'background-color' => 'none',
				'color'            => '#43454B',
				'font-size'        => '1.5em',
			],
			'input:focus' => [], // must be present for the .focus class to be applied
			'.invalid'    => [
				'color' => '#B81C23',
			],
		];

		/**
		 * Filters the styles for payment fields output by Payment.JS.
		 *
		 * Note: there are some restrictions to the possible applicable CSS selectors.
		 * @link https://docs.paymentjs.firstdata.com/#library (see Styling Restrictions subsection)
		 *
		 * @since 4.7.0
		 *
		 * @param array $styles associative array
		 */
		return (array) apply_filters( 'wc_' . $this->get_gateway()->get_id() . '_payment_js_payment_fields_styles', $styles );
	}


	/**
	 * Renders Payment.JS scripts on the pay page.
	 *
	 * @internal
	 *
	 * @since 4.7.0
	 */
	public function render_js() {
		global $wp;

		// Even though this gateway inherits from the Direct gateway class, it's
		//  actually a pay page gateway, so make this a no-op unless on the page page
		if ( ! is_checkout_pay_page() ) {
			return;
		}

		/**
		 * Build the Payment.JS source URL.
		 *
		 * This should be https://docs.paymentjs.firstdata.com/lib/{{env}}/client-{{version}}.js
		 *
		 * @link https://docs.paymentjs.firstdata.com/#accessing
		 */
		$version        = '2.0.0';
		$client         = 'client-' . $version;
		$environment    = $this->get_gateway()->is_production_environment() ? 'prod' : 'uat';
		$first_data_url = "https://lib.paymentjs.firstdata.com/{$environment}/$client.js";
		$payment_js     = 'wc-first-data-payeezy-payment-js';

		// enqueue the remote Payment.JS script
		wp_enqueue_script( $payment_js, $first_data_url, [], $version, true );

		$payment_js_form_handler        = $payment_js . '-payment-form';
		$payment_js_form_handler_params = str_replace( '-', '_', $payment_js_form_handler );

		// enqueue our own form handler script & form stylesheet
		wp_enqueue_style( $payment_js_form_handler, $this->get_gateway()->get_plugin()->get_plugin_url() . '/assets/css/frontend/' . $payment_js_form_handler . '.min.css', [], \WC_First_Data::VERSION );
		wp_enqueue_script( $payment_js_form_handler, $this->get_gateway()->get_plugin()->get_plugin_url() . '/assets/js/frontend/' . $payment_js_form_handler . '.min.js', [ 'jquery', $payment_js, 'sv-wc-payment-gateway-payment-form-v5_11_12' ], \WC_First_Data::VERSION, true );
		wp_localize_script( $payment_js_form_handler, $payment_js_form_handler_params, [
			'ajax_url'                               => admin_url( 'admin-ajax.php' ),
			'debug_mode'                             => ! $this->get_gateway()->debug_off(),
			'payment_js_configuration'               => $this->get_payment_js_script_configuration(),
			'generate_payment_js_client_token_nonce' => wp_create_nonce( 'generate-payment-js-client-token' ),
			// try to get order ID from order-pay page
			'order_id'                               => ! empty( $wp->query_vars['order-pay'] ) ? $wp->query_vars['order-pay'] : '',
			'i18n'                                   => [
				'general_error'         => __( 'An error occurred, please try again or try an alternate form of payment.', 'woocommerce-gateway-firstdata' ),
				'form_validation_error' => __( 'One or more payments fields may be invalid or incomplete. Please review your payment information or try an alternate form of payment.', 'woocommerce-gateway-firstdata' ),
				'authorization_error'   => __( 'Could not proceed with payment. Please try again or try an alternate form of payment.', 'woocommerce-gateway-firstdata' ),
			]
		] );

		parent::render_js();
	}


}
