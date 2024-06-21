<?php
namespace Barn2\Plugin\WC_Product_Options\Fields;

/**
 * Dropdown field class.
 *
 * @package   Barn2\woocommerce-product-options
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Select extends Abstract_Field {

	protected $type = 'dropdown';

	/**
	 * Render the HTML for the field.
	 */
	public function render(): void {
		if ( ! $this->has_display_prerequisites() ) {
			return;
		}

		$this->render_field_wrap_open();

		$this->render_option_name();
		$this->render_select();
		$this->render_description();

		$this->render_field_wrap_close();
	}

	/**
	 * Render the HTML for the field input.
	 */
	private function render_select() {
		$html = sprintf(
			'<select id="%1$s" name="%2$s" %3$s placeholder="%4$s">',
			esc_attr( $this->get_input_id() ),
			esc_attr( $this->get_input_name() ),
			$this->is_required() ? esc_attr( 'required' ) : '',
			esc_attr__( 'Select an option', 'woocommerce-product-options' )
		);

		$html .= sprintf( '<option value="">%s</option>', esc_html__( 'Select an option', 'woocommerce-product-options' ) );

		foreach ( $this->option->choices as $choice ) {
			$pricing      = $this->get_choice_pricing_string( $choice );
			$data_display = $pricing !== wp_strip_all_tags( $pricing ) ? 'data-display="' . esc_attr( $pricing ) . '"' : '';
			$html        .= sprintf(
				'<option value="%1$s" %2$s %5$s %6$s>%3$s %4$s</option>',
				esc_attr( $choice['id'] ),
				selected( $choice['selected'], true, false ),
				esc_html( $choice['label'] ),
				$this->get_choice_pricing_string( $choice ),
				$this->get_choice_pricing_attributes( $choice ),
				$data_display
			);
		}

		$html .= '</select>';

		// phpcs:reason This is escaped above.
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped/
		echo $html;
	}
}
