<?php
namespace Barn2\Plugin\WC_Product_Options\Fields;

use Barn2\Plugin\WC_Product_Options\Util\Util;
use Barn2\Plugin\WC_Product_Options\Util\Conditional_Logic as Conditional_Logic_Util;
use WP_Error;

/**
 * Text input field class.
 *
 * @package   Barn2\woocommerce-product-options
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class File extends Abstract_Field {

	protected $type = 'file_upload';

	protected $has_user_value           = true;
	protected $supports_multiple_values = true;

	protected $used_settings = [
		'file_upload_size',
		'file_upload_allowed_types',
		'file_upload_items_max',
	];

	protected $file_upload_size;
	protected $file_upload_allowed_types;
	protected $file_upload_items_max;

	/**
	 * Sanitizes the field value.
	 *
	 * @param string $serialized_values The field value.
	 * @return array The sanitized field value.
	 */
	public function sanitize( $serialized_values ) {
		$values           = json_decode( $serialized_values, true );
		$sanitized_values = [];

		if ( is_null( $values ) ) {
			return $values;
		}

		foreach ( $values as $value ) {
			$sanitized_values[] = esc_url_raw( $value );
		}

		return $sanitized_values;
	}

	/**
	 * Validate the field value.
	 *
	 * Further validation on the file is done in the upload handler.
	 *
	 * @param string $serialized_values
	 * @return WP_Error|true
	 */
	public function validate( $serialized_values, $option_data ) {
		$values = json_decode( $serialized_values, true );

		if ( $this->is_required() && empty( $values ) && ! Conditional_Logic_Util::is_field_hidden( $this, $option_data ) ) {
			/* translators: %s: Option name */
			return new WP_Error(
				'wpo-validation-error',
				esc_html(
					sprintf(
						/* translators: %1: Option name %2: Product name */
						__( '"%1$s" is a required field for "%2$s".', 'woocommerce-product-options' ),
						$this->option->name,
						$this->product->get_name()
					)
				)
			);
		}

		if ( ! empty( $this->file_upload_items_max ) && count( $values ) > absint( $this->file_upload_items_max ) ) {
			/* translators: %s: Option name */
			return new WP_Error(
				'wpo-validation-error',
				esc_html(
					sprintf(
						/* translators: %1: Maxmimum number of files %2: Option name %3: Product name */
						__( 'You can only upload up to %1$d files(s) for "%2$s" on "%3$s".', 'woocommerce-product-options' ),
						$this->file_upload_items_max,
						$this->option->name,
						$this->product->get_name()
					)
				)
			);
		}

		return true;
	}

	/**
	 * Retrieves the cart item data for the selected value(s) of the field.
	 *
	 * @param mixed       $values
	 * @param WC_Product $product
	 * @param int $quantity
	 * @param array $options
	 * @return array
	 */
	public function get_cart_item_data( $values, $product, $quantity, $options ): ?array {

		if ( empty( $values ) ) {
			return null;
		}

		if ( Conditional_Logic_Util::is_field_hidden( $this, $options ) ) {
			return null;
		}

		$item_data = [
			'name'        => $this->option->name,
			'type'        => $this->option->type,
			'option_id'   => $this->option->id,
			'group_id'    => $this->option->group_id,
			'value'       => $values,
			'choice_data' => [
				[
					'label' => $this->get_uploaded_files_label( $values ),
				]
			]
		];

		$choice = $this->get_choice_for_value( $values );

		if ( ! $choice ) {
			return null;
		}

		if ( $choice['pricing'] ) {
			// Add the price and price type to the item data.
			$item_data['choice_data'][0]['pricing']['type']   = $choice['price_type'];
			$item_data['choice_data'][0]['pricing']['amount'] = (float) $choice['pricing'];
		}

		return $item_data;
	}

	/**
	 * Render the HTML for the field.
	 */
	public function render(): void {
		if ( ! $this->has_display_prerequisites() ) {
			return;
		}

		$this->render_field_wrap_open();

		$this->render_option_name();
		$this->render_dropzone();
		$this->render_description();

		$this->render_field_wrap_close();
	}

	/**
	 * Render the HTML for the field input.
	 */
	private function render_dropzone() {
		$html = sprintf(
			'<label for="%4$s">%1$s %2$s</label>
			<div class="wpo-file-dropzone dropzone" %3$s>%6$s</div>
			%7$s
			<input type="type" id="%4$s" class="wpo-incognito-input" name="%8$s" value="%9$s" %5$s />',
			$this->get_label(),
			$this->get_choice_pricing_string(),
			$this->get_file_upload_attributes(),
			esc_attr( $this->get_input_id() ),
			$this->get_choice_pricing_attributes(),
			$this->get_dropzone_button(),
			$this->get_dropzone_preview_template(),
			$this->get_input_name(),
			wp_json_encode( [] )
		);

		// phpcs:reason This is escaped above.
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $html;
	}

	/**
	 * Get the label for the field.
	 *
	 * @return string
	 */
	private function get_label(): string {
		if ( $this->option->choices[0]['label'] === '' ) {
			return '';
		}

		$label = esc_html( $this->option->choices[0]['label'] );

		if ( ! $this->option->display_name && $this->is_required() ) {
			$label .= '<span class="wpo-field-required-symbol">*</span>';
		}

		return $label;
	}

	/**
	 * Gets the custom dropzone button.
	 */
	private function get_dropzone_button(): string {
		$template = sprintf(
			'<div class="dz-default dz-message">
				<button class="dz-button" type="button">
					<span class="dz-button-icon">
					<svg xmlns="http://www.w3.org/2000/svg" height="40" width="40"><path d="M10.625 32.5q-3.333 0-5.729-2.375T2.5 24.375q0-3.083 2.042-5.417 2.041-2.333 5.041-2.666.709-3.834 3.646-6.313Q16.167 7.5 20.042 7.5q4.458 0 7.5 3.167 3.041 3.166 3.041 7.625v1.333h.5q2.709 0 4.563 1.854 1.854 1.854 1.854 4.563 0 2.666-1.875 4.562Q33.75 32.5 31.083 32.5h-9.541q-1.042 0-1.813-.771-.771-.771-.771-1.854V19.708L15.5 23.167 14 21.708l6-6.041 6 6.041-1.5 1.459-3.458-3.459v10.167q0 .208.166.375.167.167.334.167h9.5q1.833 0 3.104-1.271 1.271-1.271 1.271-3.104 0-1.792-1.271-3.063-1.271-1.271-3.104-1.271H28.5v-3.416q0-3.584-2.479-6.125-2.479-2.542-6.063-2.542-3.583 0-6.062 2.542-2.479 2.541-2.479 6.125h-.875q-2.417 0-4.188 1.75t-1.771 4.333q0 2.458 1.771 4.25 1.771 1.792 4.271 1.792h5V32.5ZM20 21.042Z"/></svg>				</span>
				<span class="dz-button-label">%s</span>
				</button>
			</div>',
			esc_html__( 'Drop files here to upload', 'woocommerce-product-options' )
		);

		return $template;
	}

	/**
	 * Returns the custom preview template for dropzone.js
	 */
	private function get_dropzone_preview_template(): string {
		/**
		 * Filter the dropzone preview template.
		 *
		 * @param string $template The template.
		 * @param string $id       The field ID.
		 * @param self   $this     The field.
		 */
		$template = apply_filters(
			'wc_product_options_dropzone_preview_template',
			sprintf(
				'<div id="%s-dropzone-template" class="wpo-dropzone-preview">
					<div class="dz-preview dz-file-preview well" id="dz-preview-template">
						<div class="dz-details">
								<div class="dz-filename"><span data-dz-name></span></div>
								<div class="dz-size" data-dz-size></div>
						</div>
						<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
						<div class="dz-error-message"><span data-dz-errormessage></span></div>
					</div>
				</div>',
				esc_attr( $this->get_input_id() )
			),
			$this->get_input_id(),
			$this
		);

		return $template;
	}

	/**
	 * Gets the character limits for the field. (Text and Textarea)
	 */
	private function get_file_upload_attributes(): string {
		$attributes = [];

		$attributes = array_merge(
			$attributes,
			[
				'data-max-filesize' => empty( $this->file_upload_size ) ? number_format( wp_max_upload_size() / 1048576 ) : $this->file_upload_size,
				'data-file-types'   => $this->get_allowed_files_mime_string()
			]
		);

		if ( ! empty( $this->file_upload_items_max ) ) {
			$attributes = array_merge(
				$attributes,
				[
					'data-max-files' => $this->file_upload_items_max,
				]
			);
		}

		$attribute_string = Util::get_html_attribute_string( $attributes );

		return $attribute_string;
	}

	/**
	 * Generates a mime type string for dropzone.js
	 */
	private function get_allowed_files_mime_string(): string {
		$default_extensions = [ 'jpg', 'jpeg', 'jpe', 'png', 'docx', 'xlsx', 'pptx', 'pdf' ];

		if ( empty( $this->file_upload_allowed_types ) ) {
			$cleaned_extensions = $default_extensions;
		} else {
			$cleaned_extensions = [];

			foreach ( $this->file_upload_allowed_types as $extensions ) {
				$extensions = stripos( $extensions, '|' ) === false ? [ $extensions ] : explode( '|', $extensions );

				$cleaned_extensions = array_merge( $cleaned_extensions, $extensions );
			}
		}

		$prefixed_extensions = array_map(
			function ( $extension ) {
				return '.' . $extension;
			},
			$cleaned_extensions
		);

		return implode( ',', $prefixed_extensions );
	}

	/**
	 * Get uploaded file linked label.
	 *
	 * @param array $values
	 * @return string
	 */
	private function get_uploaded_files_label( array $values ): string {
		$html = '<ul style="margin:0; list-style-type: none;">';

		foreach ( $values as $value ) {
			$html .= sprintf( '<li><a href="%1$s" target="_blank">%2$s</a></li>', esc_attr( esc_url( $value ) ), esc_html( basename( $value ) ) );
		}

		$html .= '</ul>';

		return $html;
	}
}
