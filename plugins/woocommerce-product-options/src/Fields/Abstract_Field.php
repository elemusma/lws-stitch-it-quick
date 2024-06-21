<?php

namespace Barn2\Plugin\WC_Product_Options\Fields;

use Barn2\Plugin\WC_Product_Options\Fields\Traits\Cart_Item_Data;
use Barn2\Plugin\WC_Product_Options\Util\Price as Price_Util;
use Barn2\Plugin\WC_Product_Options\Util\Conditional_Logic as Conditional_Logic_Util;
use Barn2\Plugin\WC_Product_Options\Util\Util;
use WP_Error;
use WC_Product;

defined( 'ABSPATH' ) || exit;
/**
 * Abstract field class.
 *
 * @package   Barn2\woocommerce-product-options
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
#[\AllowDynamicProperties]
abstract class Abstract_Field {

	use Cart_Item_Data;

	protected $type;
	protected $option;
	protected $product;

	/**
	 * Whether the field has a user-defined value.
	 *
	 * @var bool
	 */
	protected $has_user_value = false;

	/**
	 * Whether the field has conditional logic.
	 *
	 * @var bool
	 */
	protected $has_conditional_logic = false;

	/**
	 * Whether the field supports multiple values (e.g checkboxes, radios, select).
	 *
	 * @var bool
	 */
	protected $supports_multiple_values = true;

	/**
	 * Whether the field can store multiple value selections (e.g checkboxes).
	 *
	 * @var bool
	 */
	protected $stores_multiple_values = false;


	/**
	 * Whether the field is for display only.
	 *
	 * @var bool
	 */
	protected $is_display_field = false;

	/**
	 * An array of settings being used by the field.
	 *
	 * @var array
	 */
	protected $used_settings = [];

	/**
	 * The choice quantity setting.
	 *
	 * @var array
	 */
	protected $choice_qty;

	/**
	 * The choice character setting.
	 *
	 * @var array
	 */
	protected $choice_char;

	/**
	 * Constructor.
	 *
	 * @param mixed $option
	 * @param WC_Product $product
	 */
	public function __construct( $option, $product ) {
		$this->option  = $option;
		$this->product = $product;

		$used_settings = array_unique(
			array_merge(
				$this->used_settings,
				[
					'choice_qty',
					'choice_char',
				]
			)
		);

		if ( $this->option->settings ) {
			foreach ( $used_settings as $used_setting ) {
				$this->{$used_setting} = $this->get_setting( $used_setting );
			}
		}

		$this->init_conditional_logic();
	}

	/**
	 * The set magic method.
	 *
	 * This is defined and empty to allow the use of dynamic properties.
	 *
	 * @see https://www.php.net/manual/en/language.oop5.properties.php#language.oop5.properties.dynamic-properties
	 *
	 * @param  string $name
	 * @param  mixed $value
	 * @return void
	 */
	public function __set( $name, $value ): void {}

	/**
	 * Determine whether the field has conditional logic.
	 */
	private function init_conditional_logic() {
		$this->has_conditional_logic = ! is_null( $this->option->conditional_logic ) && ! empty( $this->option->conditional_logic['conditions'] );
	}

	/**
	 * Get the array with the field settings.
	 *
	 * @return array|null
	 */
	public function get_settings() {
		/**
		 * Filters the array with the field settings.
		 *
		 * @param array $settings The array with the field settings.
		 * @param Abstract_Field $field The field object.
		 * @param WC_Product $product The product object.
		 */
		return apply_filters( 'wc_product_options_get_settings', $this->option->settings, $this, $this->get_product() );
	}

	/**
	 * Get the value of a specific field setting.
	 *
	 * @param  string $setting_name The key of the setting to retrieve.
	 * @return string|array|null
	 */
	public function get_setting( $setting_name ) {
		$settings = $this->get_settings();

		if ( ! isset( $settings[ $setting_name ] ) ) {
			return null;
		}

		/**
		 * Filters the value of a specific field setting.
		 *
		 * @param string|array $value The value of the setting being filtered.
		 * @param string $setting_name The key of the setting being filtered.
		 * @param Abstract_Field $field The field object.
		 * @param WC_Product $product The product object.
		 */
		$value = apply_filters( 'wc_product_options_get_setting', $settings[ $setting_name ], $setting_name, $this, $this->get_product() );

		/**
		 * Filters the value of a specific field setting.
		 *
		 * The variable portion of the filter name refers to the setting name.
		 *
		 * @param string|array $value The value of the setting being filtered.
		 * @param Abstract_Field $field The field object.
		 * @param WC_Product $product The product object.
		 */
		return apply_filters( "wc_product_options_get_{$setting_name}_setting", $value, $this, $this->get_product() );
	}

	/**
	 * Validate the filed value.
	 *
	 * @param mixed $value
	 * @param array $option_data
	 * @return WP_Error|true
	 */
	public function validate( $value, $option_data ) {
		// wpt and the serializeObject plugin pass through display fields.
		if ( $this->is_display_field ) {
			return true;
		}

		if ( $this->is_required() && empty( $value ) && ! Conditional_Logic_Util::is_field_hidden( $this, $option_data ) ) {
			/* translators: %1$s: Option name %2$s: Product name*/
			return new WP_Error( 'wpo-validation-error', esc_html( sprintf( __( '"%1$s" is a required field for "%2$s".', 'woocommerce-product-options' ), $this->option->name, $this->product->get_name() ) ) );
		}

		$values = is_array( $value ) ? $value : [ $value ];

		foreach ( $values as $choice ) {
			$key = $this->has_user_value ? 0 : array_search( $choice, array_column( $this->option->choices, 'id' ), true );

			if ( ! $this->has_user_value && ! isset( $this->option->choices[ $key ] ) ) {
				/* translators: %1$s: Option name %2$s: Product name*/
				return new WP_Error( 'wpo-validation-error', esc_html( sprintf( __( 'Invalid option choice for "%1$s" on "%2$s".', 'woocommerce-product-options' ), $this->option->name, $this->product->get_name() ) ) );
			}
		}

		return true;
	}

	/**
	 * Sanitize the field input value.
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public function sanitize( $value ) {
		return sanitize_text_field( $value );
	}

	/**
	 * Retrieves the choice from the option DB based on the user value provided.
	 *
	 * @param mixed $value
	 * @return array|null
	 */
	protected function get_choice_for_value( $value ): ?array {
		$key = $this->has_user_value ? 0 : array_search( $value, array_column( $this->option->choices, 'id' ), true );

		if ( $key === false || ! isset( $this->option->choices[ $key ] ) ) {
			return null;
		}

		return $this->option->choices[ $key ];
	}

	/**
	 * Determines whether the option has enough data to display.
	 *
	 * @return bool
	 */
	protected function has_display_prerequisites(): bool {
		if ( $this->is_display_field ) {
			return true;
		}

		if ( $this->option->type !== 'price_formula' && is_null( $this->option->choices ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Render the HTML for the field.
	 */
	public function render(): void {}

	/**
	 * Renders the field wrap.
	 */
	protected function render_field_wrap_open(): void {		
		/**
		 * Fires before the field wrap is rendered.
		 *
		 * @param Abstract_Field $field The current field object.
		 * @param WC_Product $product The current product object.
		 */
		do_action( 'wc_product_options_before_field_wrap', $this, $this->get_product() );

		/**
		 * Fires before the field wrap is rendered.
		 *
		 * The variable part of the hook name refers to the field type (i.e. `$field->option->type`)
		 *
		 * @param Abstract_Field $field The current field object.
		 * @param WC_Product $product The current product object.
		 */
		do_action( "wc_product_options_before_{$this->option->type}_field_wrap", $this, $this->get_product() );

		// phpcs:reason The attributes are escaped in the get_field_attributes() method.
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		printf( '<div class="%1$s" %2$s>', esc_attr( $this->get_field_class() ), $this->get_field_attributes() );

		/**
		 * Fires before the field is rendered.
		 *
		 * @param Abstract_Field $field The current field object.
		 * @param WC_Product $product The current product object.
		 */
		do_action( 'wc_product_options_before_field', $this, $this->get_product() );

		/**
		 * Fires before the field is rendered.
		 *
		 * The variable part of the hook name refers to the field type (i.e. `$field->option->type`)
		 *
		 * @param Abstract_Field $field The current field object.
		 * @param WC_Product $product The current product object.
		 */
		do_action( "wc_product_options_before_{$this->option->type}_field", $this, $this->get_product() );
	}

	/**
	 * Renders the field wrap closing tag.
	 */
	protected function render_field_wrap_close(): void {
		/**
		 * Fires after the field is rendered.
		 *
		 * @param Abstract_Field $field The current field object.
		 * @param WC_Product $product The current product object.
		 */
		do_action( 'wc_product_options_after_field', $this, $this->get_product() );

		/**
		 * Fires after the field is rendered.
		 *
		 * The variable part of the hook name refers to the field type (i.e. `$field->option->type`)
		 *
		 * @param Abstract_Field $field The current field object.
		 * @param WC_Product $product The current product object.
		 */
		do_action( "wc_product_options_after_{$this->option->type}_field", $this, $this->get_product() );

		print( '</div>' );

		/**
		 * Fires after the field wrap is rendered.
		 *
		 * @param Abstract_Field $field The current field object.
		 * @param WC_Product $product The current product object.
		 */
		do_action( 'wc_product_options_after_field_wrap', $this, $this->get_product() );

		/**
		 * Fires after the field wrap is rendered.
		 *
		 * The variable part of the hook name refers to the field type (i.e. `$field->option->type`)
		 *
		 * @param Abstract_Field $field The current field object.
		 * @param WC_Product $product The current product object.
		 */
		do_action( "wc_product_options_after_{$this->option->type}_field_wrap", $this, $this->get_product() );
	}

	/**
	 * Render the HTML for the field label.
	 */
	protected function render_option_name(): void {
		if ( $this->option->display_name ) {
			printf(
				'<p class="wpo-option-name">%1$s%2$s</p>',
				esc_html( $this->option->name ),
				$this->is_required() ? '<span class="wpo-field-required-symbol">*</span>' : ''
			);
		}
	}

	/**
	 * Render the HTML for the field description.
	 */
	protected function render_description(): void {
		if ( ! empty( $this->option->description ) ) {
			printf( '<p class="wpo-field-description">%s</p>', esc_html( $this->option->description ) );
		}
	}

	/**
	 * Gets the name attribute for the field input.
	 *
	 * @return string
	 */
	protected function get_input_name(): string {
		return sprintf( 'wpo-option[option-%d]', $this->option->id );
	}

	/**
	 * Gets the id attribute for the field input.
	 *
	 * @return string
	 */
	protected function get_input_id(): string {
		return sprintf( 'wpo-option-%d-%d-%d', $this->option->group_id, $this->option->id, $this->product->get_id() );
	}

	/**
	 * Gets the CSS class for the field.
	 *
	 * @return string
	 */
	protected function get_field_class(): string {
		$classes = [ 'wpo-field', 'wpo-field-%s' ];

		if ( $this->has_conditional_logic && $this->option->conditional_logic['visibility'] === 'show' ) {
			$classes[] = 'wpo-field-hide';
		}

		if ( $this->is_required() ) {
			$classes[] = 'wpo-field-required';
		}

		if ( ! $this->option->display_name && $this->has_user_value ) {
			$classes[] = 'wpo-label-is-option-name';
		}

		/**
		 * Filters the CSS classes for the field.
		 *
		 * @param string $class_string
		 * @param Abstract_Field $field
		 */
		return apply_filters( 'wc_product_options_field_css_class', sprintf( implode( ' ', $classes ), $this->option->type ), $this );
	}

	/**
	 * Gets the data attributes to handle conditional logic visibilities.
	 *
	 * @return string
	 */
	protected function get_field_attributes(): string {
		$attributes = [
			'data-type'      => $this->type,
			'data-group-id'  => $this->option->group_id,
			'data-option-id' => $this->option->id,
			'data-clogic'    => $this->has_conditional_logic ? 'true' : 'false',
		];

		if ( $this->has_conditional_logic ) {
			$attributes = array_merge(
				$attributes,
				[
					'data-clogic-relation'   => $this->option->conditional_logic['relation'],
					'data-clogic-visibility' => $this->option->conditional_logic['visibility'],
					'data-clogic-conditions' => wp_json_encode( $this->option->conditional_logic['conditions'] ),
				]
			);
		}

		if ( $this->stores_multiple_values && $this->choice_qty ) {
			$min = $this->choice_qty['min'] ?? '';

			if ( $min !== '' ) {
				$attributes['data-min-qty'] = $min;
			}

			$max = $this->choice_qty['max'] ?? '';

			if ( $max !== '' ) {
				$attributes['data-max-qty'] = $max;
			}
		}

		if ( ! $this->is_display_field ) {
			$attributes = array_merge( $attributes, $this->get_pricing_attributes() );
		}

		$attribute_string = Util::get_html_attribute_string( $attributes );

		return $attribute_string;
	}

	/**
	 * Gets the label for choice quantity limits if applicable.
	 */
	protected function render_choice_quantity_limits_label(): void {
		if ( ! $this->stores_multiple_values || ! $this->choice_qty ) {
			return;
		}

		$limits = [];

		$min = $this->choice_qty['min'] ?? '';

		if ( $min !== '' ) {
			$limits[] = sprintf( 'Minimum %d', esc_html( $min ) );
		}

		$max = $this->choice_qty['max'] ?? '';

		if ( $max !== '' ) {
			$limits[] = sprintf( 'Maximum %d', esc_html( $max ) );
		}

		if ( empty( $limits ) ) {
			return;
		}

		printf( '<span class="wpo-info-label">Quantities: %s.</span>', esc_html( implode( ', ', $limits ) ) );
	}

	/**
	 * Gets the label for choice character limits if applicable.
	 */
	protected function render_choice_character_limits_label(): void {
		if ( ! $this->has_user_value || ! $this->choice_char ) {
			return;
		}

		$limits = [];

		$min = $this->choice_char['min'] ?? '';

		if ( $min !== '' ) {
			$limits[] = sprintf( 'Minimum %d', esc_html( $min ) );
		}

		$max = $this->choice_char['max'] ?? '';

		if ( $max !== '' ) {
			$limits[] = sprintf( 'Maximum %d', esc_html( $max ) );
		}

		if ( empty( $limits ) ) {
			return;
		}

		printf( '<span class="wpo-info-label">Characters: %s.</span>', esc_html( implode( ', ', $limits ) ) );
	}

	/**
	 * Retrieves top level pricing attributes for the field.
	 *
	 * @return array
	 */
	protected function get_pricing_attributes(): array {
		$has_pricing   = false;
		$default_price = 0;

		if ( empty( $this->option->choices ) || $this->option->type === 'product' ) {
			return [];
		}

		foreach ( $this->option->choices as $choice ) {
			if ( $choice['price_type'] === 'no_cost' ) {
				continue;
			}

			$has_pricing = true;

			if ( $choice['selected'] && $this->supports_multiple_values ) {
				$default_price += $choice['pricing'];
			}

			if ( ! $this->supports_multiple_values ) {
				$default_price += $choice['pricing'];
			}
		}

		$attributes = [
			'data-pricing'      => $has_pricing ? 'true' : 'false',
			'data-option-price' => (string) $default_price ?? '0',
		];

		return $attributes;
	}

	/**
	 * Gets the data attributes to handle JS pricing calculation.
	 *
	 * @param array $choice
	 * @return string
	 */
	protected function get_choice_pricing_attributes( array $choice = [] ): string {
		$choice = empty( $choice ) ? $this->option->choices[0] : $choice;

		$attributes = [
			'data-price-type' => $choice['price_type'],
		];

		$choice_price = Price_Util::wholesale_user_has_choice_pricing( $choice ) ?: $choice['pricing'];

		if ( in_array( $choice['price_type'], [ 'percentage_inc', 'percentage_dec' ], true ) ) {
			$attributes['data-price-amount'] = ! empty( $choice_price ) ? $choice_price : '0';
		} else {
			$attributes['data-price-amount'] = ! empty( $choice_price ) ? Price_Util::get_choice_display_price( $this->product, $choice_price, null ) : '0';
		}

		$attribute_string = Util::get_html_attribute_string( $attributes );

		return $attribute_string;
	}

	/**
	 * Retrieves the pricing string for a choice
	 *
	 * @param array $choice
	 */
	protected function get_choice_pricing_string( array $choice = [] ): string {
		$currency_data = Price_Util::get_currency_data();
		$price_string  = '';
		$choice        = empty( $choice ) ? $this->option->choices[0] : $choice;
		$choice_price  = Price_Util::wholesale_user_has_choice_pricing( $choice ) ?: $choice['pricing'];
		$space         = str_contains( get_option( 'woocommerce_currency_pos' ), 'space' ) ? '&nbsp;' : '';

		switch ( $choice['price_type'] ) {
			case 'no_cost':
				return '';
			case 'flat_fee':
				/* translators: %s: Option choice price flat fee */
				$price_string = sprintf( esc_html_x( '(%s)', 'flat fee price', 'woocommerce-product-options' ), Price_Util::get_price_html( Price_Util::get_choice_display_price( $this->product, $choice_price, null ) ) );
				break;
			case 'percentage_inc':
				/* translators: %1$s: A space or an empty string, %2$s: Option choice percentage increase */
				$price_string = sprintf( esc_html__( '(+%1$s%2$s%1$s%%)', 'woocommerce-product-options' ), $space, number_format( $choice_price, $currency_data['precision'], $currency_data['decimalSeparator'], $currency_data['thousandSeparator'] ) );
				break;
			case 'percentage_dec':
				/* translators: %1$s: A space or an empty string, %2$s: Option choice percentage decrease */
				$price_string = sprintf( esc_html__( '(-%1$s%2$s%1$s%%)', 'woocommerce-product-options' ), $space, number_format( $choice_price, $currency_data['precision'], $currency_data['decimalSeparator'], $currency_data['thousandSeparator'] ) );
				break;
			case 'quantity_based':
				/* translators: %s: Option choice quantity bsed price */
				$price_string = sprintf( esc_html_x( '(%s)', 'quantity based price', 'woocommerce-product-options' ), Price_Util::get_price_html( Price_Util::get_choice_display_price( $this->product, $choice_price, null ) ) );
				break;
			case 'char_count':
				/* translators: %s: Option choice price per character */
				$price_string = sprintf( esc_html__( '(%s per character)', 'woocommerce-product-options' ), Price_Util::get_price_html( Price_Util::get_choice_display_price( $this->product, $choice_price, null ) ) );
				break;
			default:
				return '';
		}

		if ( ! empty( $price_string ) ) {
			return sprintf( '<span class="price wpo-price-container">%s</span>', $price_string );
		}
	}

	/**
	 * Get the field conditional logic config.
	 *
	 * @return object|null
	 */
	public function get_conditional_logic_config(): ?object {
		if ( ! $this->has_conditional_logic ) {
			return null;
		}

		return (object) $this->option->conditional_logic;
	}

	/**
	 * Get the field type.
	 *
	 * @return string
	 */
	public function get_type(): string {
		return $this->option->type;
	}

	/**
	 * Get the field id.
	 *
	 * @return string
	 */
	public function get_id(): string {
		return $this->option->id;
	}

	/**
	 * Get the product for the field.
	 *
	 * @return WC_Product
	 */
	public function get_product(): WC_Product {
		return $this->product;
	}

	/**
	 * Whether the field has a user inputted value.
	 *
	 * @return bool
	 */
	public function has_user_value(): bool {
		return $this->has_user_value;
	}

	/**
	 * Whether the field has conditional logic.
	 *
	 * @return bool
	 */
	public function has_conditional_logic(): bool {
		return $this->has_conditional_logic;
	}

	/**
	 * Whether the field stores multiple values.
	 *
	 * @return bool
	 */
	public function stores_multiple_values(): bool {
		return $this->stores_multiple_values;
	}

	/**
	 * Whether the field is required.
	 *
	 * @return boolean
	 */
	public function is_required() {
		return ! empty( $this->option->required );
	}
}
