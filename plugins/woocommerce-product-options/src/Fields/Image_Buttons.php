<?php
namespace Barn2\Plugin\WC_Product_Options\Fields;

use WC_Product;

/**
 * Radios field class.
 *
 * @package   Barn2\woocommerce-product-options
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Image_Buttons extends Checkboxes {

	protected $type        = 'image_buttons';
	private $default_width = 118;

	/**
	 * Whether the field supports multiple values (e.g checkboxes).
	 *
	 * @var bool
	 */
	protected $stores_multiple_values = true;

	protected $used_settings = [
		'label_position',
		'display_label',
		'button_width',
		'show_in_product_gallery',
	];

	protected $label_position;
	protected $display_label;
	protected $button_width;
	protected $show_in_product_gallery;

	/**
	 * Render the HTML for the field.
	 */
	public function render(): void {
		if ( ! $this->has_display_prerequisites() ) {
			return;
		}

		$this->render_field_wrap_open();

		$this->render_option_name();
		$this->render_image_buttons();
		$this->render_description();

		$this->render_field_wrap_close();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function render_field_wrap_close(): void {
		printf(
			'<style>div[data-group-id="%1$d"][data-option-id="%2$d"] .wpo-image-buttons{--wpo-image-buttons-width: %3$dpx;}</style></div>',
			esc_attr( $this->option->group_id ),
			esc_attr( $this->option->id ),
			esc_attr( $this->get_button_width() )
		);
	}


	/**
	 * Render the HTML for the field checkboxes.
	 */
	private function render_image_buttons(): void {
		$image_missing = array_reduce(
			$this->option->choices,
			function( $carry, $choice ) {
				return $carry || ! isset( $choice['media'] );
			},
			false
		);

		if ( $image_missing ) {
			$this->display_label  = true;
			$this->label_position = 'below';
		}

		if ( ! $this->display_label ) {
			$this->label_position = 'below';
		}

		$html = sprintf(
			'<div class="wpo-image-buttons %s">',
			sprintf(
				'wpo-image-buttons-%s',
				$this->label_position ?? 'full'
			)
		);

		foreach ( $this->option->choices as $index => $choice ) {

			if ( ! isset( $choice['media'] ) ) {
				$choice['media'] = get_option( 'woocommerce_placeholder_image', 0 );
			}

			$caption = $this->get_figcaption( $choice );

			$html .= sprintf(
				'<label class="wpo-image-button" title="%6$s" %12$s>
					<input type="checkbox" id="%1$s" name="%2$s[]" value="%3$s" %4$s %7$s>
					<figure class="%9$s">
						<div class="wpo-image-active">%8$s</div>
						<img src="%5$s" srcset="%10$s" sizes="%11$s" loading="lazy" alt="" />
						%13$s
					</figure>
				</label>',
				esc_attr( sprintf( '%1$s-%2$s', $this->get_input_id(), $index ) ),
				esc_attr( $this->get_input_name() ),
				esc_attr( $choice['id'] ),
				checked( $choice['selected'], true, false ),
				$this->get_image_thumbnail_url( $choice['media'], 'thumbnail' ),
				preg_replace( '/\s+/', ' ', wp_strip_all_tags( $caption ) ),
				$this->get_choice_pricing_attributes( $choice ),
				$this->get_deselect_svg(),
				$this->get_image_wrap_class( $choice ),
				$this->get_image_srcset( $choice['media'] ),
				$this->get_attachment_image_sizes( $choice['media'] ),
				$this->get_image_data( $choice['media'] ),
				$caption
			);
		}

		$html .= '</div>';

		// phpcs:reason This is escaped above.
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $html;
	}

	/**
	 * Return the HTML markup of the figcaption element for the image button.
	 *
	 * @param  array $choice
	 * @return string
	 */
	private function get_figcaption( $choice ) {
		$label = $this->display_label || ! isset( $choice['media'] ) ? esc_attr( $choice['label'] ) : '';
		$label = $label ? sprintf( '<span class="wpo-image-label">%1$s</span>', esc_html( $label ) ) : '';
		$price = $this->get_choice_pricing_string( $choice );

		if ( empty( $label ) && empty( $price ) ) {
			return '';
		}

		return sprintf(
			'<figcaption class="wpo-image-text">
				%1$s
				%2$s
			</figcaption>',
			$label,
			$this->get_choice_pricing_string( $choice )
		);
	}

	/**
	 * Get the class for the image wrap.
	 *
	 * @param array $choice The choice.
	 * @return string The class.
	 */
	private function get_image_wrap_class( $choice ): string {
		$class = 'wpo-image-wrap';

		if ( isset( $choice['media'] ) && empty( $this->display_label ) && empty( $choice['pricing'] ) ) {
			$class .= ' no-label';
		}

		return esc_attr( $class );
	}

	/**
	 * SVG for deselecting an image button.
	 *
	 * @return string
	 */
	private function get_deselect_svg(): string {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" class="toggle-cross" aria-hidden="true" focusable="false"><path d="M12 13.06l3.712 3.713 1.061-1.06L13.061 12l3.712-3.712-1.06-1.06L12 10.938 8.288 7.227l-1.061 1.06L10.939 12l-3.712 3.712 1.06 1.061L12 13.061z"></path></svg>';
	}

	/**
	 * Retreives the 'full' size image from WordPress.
	 *
	 * @param int $image_id
	 * @return string
	 */
	private function get_image_thumbnail_url( $image_id, $size = 'full' ): string {
		if ( ! is_numeric( $image_id ) ) {
			return '';
		}

		$image = wp_get_attachment_image_src( $image_id, $size );

		return $image[0] ?? '';
	}

	/**
	 * Retreives the thumbnail used in the Woo gallery.
	 *
	 * @param int $image_id
	 * @return string
	 */
	private function get_woo_gallery_thumbnail_url( $image_id ): string {
		if ( ! is_numeric( $image_id ) ) {
			return '';
		}

		if ( ! $this->is_update_main_image_enable() ) {
			return '';
		}

		$gallery_thumbnail      = wc_get_image_size( 'gallery_thumbnail' );
		$gallery_thumbnail_size = apply_filters( 'woocommerce_gallery_thumbnail_size', [ $gallery_thumbnail['width'], $gallery_thumbnail['height'] ] );
		$image                  = wp_get_attachment_image_src( $image_id, $gallery_thumbnail_size );

		return $image[0];
	}

	/**
	 * Get the srcset attribute for the image.
	 *
	 * @param int $image_id
	 * @return string
	 */
	private function get_image_srcset( $image_id ): string {
		if ( ! is_numeric( $image_id ) ) {
			return '';
		}

		return wp_get_attachment_image_srcset( $image_id, [ 118, 118 ] );
	}

	/**
	 * Get the width of the image buttons.
	 *
	 * @since 1.6.4
	 * @return string
	 */
	private function get_button_width() {
		return esc_attr( $this->button_width ?: $this->default_width );
	}

	/**
	 * Get the `sizes` attribute for the image element.
	 *
	 * @since 1.6.4
	 * @param  string|int $attachment_id The ID of the image attachment.
	 * @return string
	 */
	private function get_attachment_image_sizes( $attachment_id ) {
		$attachment = wp_get_attachment_image_src( $attachment_id, 'full' );

		if ( ! $attachment ) {
			return '';
		}

		return sprintf(
			'(max-width: 480px) 480px, %1$dpx',
			$this->get_button_width()
		);
	}

	/**
	 * Return the `data-image` attribute of the image button.
	 *
	 * The attribute contains the image data in JSON format
	 * as returned by the `wc_get_product_attachment_props` function.
	 *
	 * @since 1.6.4
	 * @param  string|int $attachment_id The ID of the image attachment.
	 * @return string
	 */
	private function get_image_data( $attachment_id ) {
		if ( ! $this->is_update_main_image_enable() ) {
			return '';
		}

		return sprintf(
			'data-image="%1$s"',
			htmlspecialchars( wp_json_encode( wc_get_product_attachment_props( $attachment_id ) ) )
		);
	}

	/**
	 * Whether the option has the "Update main image" setting enabled.
	 *
	 * @since 1.6.4
	 * @return bool
	 */
	private function is_update_main_image_enable() {
		/**
		 * Filter whether the option has the "Update main image" setting enabled.
		 *
		 * Thanks to this filter, the "Update main image" can be forced to be enabled/disabled site-wide
		 * or on specific products without any need to manually change any of the existing options.
		 * 
		 * Example:
		 * ```
		 * function my_wc_product_options_is_update_main_image_enabled( $is_enabled, $option, $product ) {
		 *     if ( $product->get_id() === 123 ) {
		 * 	       return true;
		 *     }
		 * 
		 *     return $is_enabled;
		 * }
		 * ```
		 *
		 * @param Image_Buttons $option The current option object.
		 * @param WC_Product $product The current product object.
		 */
		return apply_filters( 'wc_product_options_is_update_main_image_enabled', $this->show_in_product_gallery, $this, $this->get_product() );
	}
}
