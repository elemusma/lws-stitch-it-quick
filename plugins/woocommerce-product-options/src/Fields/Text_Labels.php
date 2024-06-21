<?php
namespace Barn2\Plugin\WC_Product_Options\Fields;

/**
 * Radios field class.
 *
 * @package   Barn2\woocommerce-product-options
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Text_Labels extends Checkboxes {

	protected $type = 'text_labels';

	/**
	 * Whether the field supports multiple values (e.g checkboxes).
	 *
	 * @var bool
	 */
	protected $stores_multiple_values = true;

	/**
	 * Render the HTML for the field.
	 */
	public function render(): void {
		if ( ! $this->has_display_prerequisites() ) {
			return;
		}

		$this->render_field_wrap_open();

		$this->render_option_name();
		$this->render_labels();
		$this->render_description();

		$this->render_field_wrap_close();
	}

	/**
	 * Render the HTML for the field checkboxes.
	 */
	private function render_labels(): void {
		$html = '<div class="wpo-text-labels">';

		foreach ( $this->option->choices as $index => $choice ) {
			$html .= sprintf(
				'<label class="wpo-text-label"><input type="checkbox" id="%1$s" name="%2$s[]" value="%3$s" %4$s %7$s><span class="wpo-text-label-inner">%5$s %6$s</span></label>',
				esc_attr( sprintf( '%1$s-%2$s', $this->get_input_id(), $index ) ),
				esc_attr( $this->get_input_name() ),
				esc_attr( $choice['id'] ),
				checked( $choice['selected'], true, false ),
				esc_html( $choice['label'] ),
				$this->get_choice_pricing_string( $choice ),
				$this->get_choice_pricing_attributes( $choice )
			);
		}

		$html .= '</div>';

		// phpcs:reason This is escaped above.
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $html;
	}
}
