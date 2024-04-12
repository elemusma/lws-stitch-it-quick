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

namespace Atreus\WooCommerce\First_Data\Clover\Gateway;

use SkyVerge\WooCommerce\PluginFramework\v5_11_12 as Framework;
//use Atreus\WooCommerce\First_Data\Clover\Gateway\Credit_Card as Gateway;

defined( 'ABSPATH' ) or exit;

/**
 * Handles the custom Clover payment form.
 *
 * This overrides the framework's implementation to support Clover's hosted iframe SDK,
 * which uses iframes for the payment inputs like Braintree.
 *
 * @since 5.0.0
 */
class Payment_Form extends Framework\SV_WC_Payment_Gateway_Payment_Form {


	/** @var string JavaScript handler base class name */
	protected $js_handler_base_class_name = 'WC_First_Data_Clover_Payment_Form_Handler';

	/** @var string transaction approval card number */
	const TEST_CARD_NUMBER_APPROVAL = '4761530001111126';

	/** @var string transaction decline card number */
	const TEST_CARD_NUMBER_DECLINE = '378282246310005';


	/**
	 * Gets the JS handler class name.
	 *
	 * @since 5.0.0
	 *
	 * @return string
	 */
	protected function get_js_handler_class_name() {

		return $this->js_handler_base_class_name;
	}


	/**
	 * Renders the payment form description, overridden to add the test card numbers for easier sandbox testing.
	 *
	 * @link https://docs.clover.com/docs/test-card-numbers
	 *
	 * @see Framework\SV_WC_Payment_Gateway_Payment_Form::render_payment_form_description()
	 * @since 5.0.0
	 */
	public function render_payment_form_description() {

		parent::render_payment_form_description();

		if ( $this->get_gateway()->is_test_environment() ) : ?>

			<p><?php esc_html_e( 'Test card numbers:', 'woocommerce-gateway-firstdata' ); ?></p>

			<ul class="wc-clover-test-card-numbers" style="margin-left: 0;">
				<li><code><?php echo self::TEST_CARD_NUMBER_APPROVAL; ?></code> - <?php esc_html_e( 'Approved', 'woocommerce-gateway-firstdata' ); ?></li>
				<li><code><?php echo self::TEST_CARD_NUMBER_DECLINE; ?></code> - <?php esc_html_e( 'Declined (CSC)', 'woocommerce-gateway-firstdata' ); ?></li>
			</ul>

		<?php endif;
	}


	/**
	 * Renders the payment fields.
	 *
	 * Overridden to add the hidden fields for Custom Checkout tokenization.
	 *
	 * @see Framework\SV_WC_Payment_Gateway_Payment_Form::render_payment_fields()
	 * @since 2.0.0
	 */
	public function render_payment_fields() {

		parent::render_payment_fields();

		$hidden_fields = array(
			'js-token',
			'account-number', // this will be the first 6 and last 4 combined, which will be handled by the framework to set card type & last 4
			'exp-month',
			'exp-year'
		);

		foreach ( $hidden_fields as $field ) {
			echo '<input type="hidden" name="wc-' . $this->get_gateway()->get_id_dasherized() . '-' . sanitize_html_class( $field ) . '" />';
		}
		// TODO: any reason we can't just mark these as hidden and add to get_credit_card_fields() below?
	}


	/**
	 * Renders the payment fields.
	 *
	 * Overridden to output empty divs for each field instead of actual inputs.
	 * Clover Custom Checkout uses these to attach iframe inputs. The divs are
	 * styled like the inputs would be, and the transparent iframes sit inside.
	 *
	 * @since 2.0.0
	 *
	 * @param array $field payment field params
	 */
	public function render_payment_field( $field ) {

		$sanitized_input_classes = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $field['input_class'][0] ) ) );
		?>
		<div class="form-row <?php echo implode( ' ', array_map( 'sanitize_html_class', $field['class'] ) ); ?>">
			<label for="<?php echo esc_attr( $field['id'] ) . '-hosted'; ?>"><?php echo esc_html( $field['label'] ); if ( $field['required'] ) : ?><abbr class="required" title="required">&nbsp;*</abbr><?php endif; ?></label>
			<div id="<?php echo esc_attr( $field['id'] ) . '-hosted'; ?>"       class="<?php echo $sanitized_input_classes; ?>" data-placeholder="<?php echo isset( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : ''; ?>"></div>
			<div id="<?php echo esc_attr( $field['id'] ) . '-hosted-error'; ?>" class="<?php echo esc_attr( 'wc-' . $this->get_gateway()->get_id_dasherized() . '-hosted-input-error' ); ?>"></div>
		</div>
		<?php
	}


	/**
	 * Get the default credit card fields plus the postal code field that Clover expects
	 *
	 * @see Framework\SV_WC_Payment_Gateway_Payment_Form::get_credit_card_fields()
	 * @since 2.0.0
	 *
	 * @param array $field payment field params
	 */
	public function get_credit_card_fields() {
		$fields = parent::get_credit_card_fields();

		if ( $this->get_gateway()->avs_street_address() ) {
			// TODO: localize this for US/CA (en-CA, fr-CA)
			$fields['street-address'] = [
					'type'              => 'text',
					'label'             => esc_html__( 'Street Address', 'woocommerce-gateway-firstdata' ),
					'id'                => 'wc-' . $this->get_gateway()->get_id_dasherized() . '-street-address',
					'name'              => 'wc-' . $this->get_gateway()->get_id_dasherized() . '-street-address',
					'required'          => false,
					'class'             => [ 'form-row-wide' ],
					'input_class'       => [ 'js-sv-wc-payment-gateway-credit-card-form-input' ],
					'maxlength'         => 10,
					'custom_attributes' => [
							'autocomplete'   => 'cc-number',
							'autocorrect'    => 'no',
							'autocapitalize' => 'no',
							'spellcheck'     => 'no',
					],
			];
		}

		// TODO: localize this for US/CA (en-CA, fr-CA)
		$fields['postal-code'] = [
				'type'              => 'text',
				'label'             => esc_html__( 'Postal Code', 'woocommerce-gateway-firstdata' ),
				'id'                => 'wc-' . $this->get_gateway()->get_id_dasherized() . '-postal-code',
				'name'              => 'wc-' . $this->get_gateway()->get_id_dasherized() . '-postal-code',
				'required'          => true,
				'class'             => [ 'form-row-wide' ],
				'input_class'       => [ 'js-sv-wc-payment-gateway-credit-card-form-input' ],
				'maxlength'         => 10,
				'custom_attributes' => [
						'autocomplete'   => 'cc-number',
						'autocorrect'    => 'no',
						'autocapitalize' => 'no',
						'spellcheck'     => 'no',
				],
		];

		return $fields;
	}


	/**
	 * Merge the Clover JS vars in with the standard framework gateway JS vars
	 *
	 * @see Framework\SV_WC_Payment_Gateway_Payment_Form::get_js_handler_args()
	 * @since 5.0.0
	 *
	 * @return array
	 */
	protected function get_js_handler_args() : array {
		$args = parent::get_js_handler_args();

		$args['debug']       = $this->get_gateway()->debug_log();
		$args['styles']      = $this->get_hosted_element_styles();
		$args['publicToken'] = $this->get_gateway()->get_public_token();
		$args['locale']      = get_locale();

		/**
		 * Filters the Clover JS SDK args
		 *
		 * @since 5.0.0
		 *
		 * @param array $args JS args
		 * @param Payment_Form $form_handler payment form handler
		 */
		return apply_filters( 'wc_' . $this->get_gateway()->get_id() . '_payment_fields_js_args', $args, $this );
	}



	/**
	 * Gets the payment field styles.
	 *
	 * Their JS allows us to specify styles to render on each iframe field. This
	 * sets some common base styles for each, and allows filtering to further match
	 * other theme-specific styles.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function get_hosted_element_styles() : array {

		$styles = [
			'body' => [
				'fontSize' => '1em',
			],
			'input' => [
				'fontSize' => '1em',
			]
		];

		/**
		 * Filters the Clover payment field styles.
		 *
		 * @since 5.0.0
		 *
		 * @param array $styles payment field styles
		 * @param Payment_Form $form_handler payment form handler
		 */
		return apply_filters( 'wc_' . $this->get_gateway()->get_id() . '_payment_field_styles', $styles, $this );
	}


}
