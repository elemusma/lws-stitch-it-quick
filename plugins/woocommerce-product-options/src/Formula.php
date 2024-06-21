<?php

namespace Barn2\Plugin\WC_Product_Options;

use Barn2\Plugin\WC_Product_Options\Model\Option;

/**
 * Model for handling price formula data.
 *
 * @package   Barn2\woocommerce-product-options
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Formula {

	/**
	 * The option ID.
	 *
	 * @var int
	 */
	private $option_id;

	/**
	 * The option settings.
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * The formula data.
	 *
	 * @var array
	 */
	private $data;

	/**
	 * Whether to exclude the product price from the formula.
	 *
	 * @var bool
	 */
	private $exclude_product_price;

	/**
	 * Constructor.
	 *
	 * @param Option $option
	 * @throws \InvalidArgumentException If the option is not an Option or is not a price formula.
	 */
	public function __construct( $option ) {
		if ( ! $option instanceof Option ) {
			throw new \InvalidArgumentException( 'Invalid option' );
		}

		if ( $option['type'] !== 'price_formula' ) {
			throw new \InvalidArgumentException( 'Invalid option type' );
		}

		$this->option_id             = $option->id;
		$this->settings              = $option->settings;
		$this->data                  = $option->settings['formula'] ?? [];
		$this->exclude_product_price = $option->settings['exclude_product_price'] ?? false;

		$this->data['validationError'] = $this->data['validationError'] ?? false;
	}

	/**
	 * Determines whether the current data is sufficient to evaluate the formula.
	 *
	 * @return bool
	 */
	public function check_validity() {
		if ( $this->data['validationError'] || empty( $this->data['formula'] ) || empty( $this->data['variables'] ) || empty( $this->data['expression'] ) ) {
			$this->set_valid( false );
			return false;
		}

		return true;
	}

	/**
	 * Updates a variable name in the formula.
	 *
	 * @param int $option_id
	 * @param string $name
	 */
	public function update_variable_name( $option_id, $name ) {
		// find object by key value in array
		$key = array_search( $option_id, array_column( $this->data['variables'], 'id' ), true );

		if ( $key !== false ) {
			$old_name = $this->data['variables'][ $key ]['name'];

			$this->data['variables'][ $key ]['name'] = $name;
			$this->data['expression']                = str_replace( $old_name, $name, $this->data['expression'] );
			$this->data['formula']                   = str_replace( $old_name, $name, $this->data['formula'] );
		}
	}

	/**
	 * Saves the formula data to the database.
	 */
	public function save() {
		$this->settings['formula'] = $this->data;
		Option::where( [ 'id' => $this->option_id ] )->update( [ 'settings' => $this->settings ] );
	}

	/**
	 * Sets the price formula for the field.
	 *
	 * @param string $formula
	 */
	public function set_formula( string $formula ): void {
		$this->data['formula'] = $formula;
	}

	/**
	 * Sets the expression for the executor.
	 *
	 * @param string $expression
	 */
	public function set_expression( string $expression ): void {
		$this->data['expression'] = $expression;
	}

	/**
	 * Sets the price formula variables for the field.
	 *
	 * @param array $variables
	 */
	public function set_variables( array $variables ): void {
		$this->data['variables'] = $variables;
	}

	/**
	 * Sets the valid status of the formula.
	 *
	 * @param bool $valid
	 */
	public function set_valid( bool $valid ): void {
		$this->data['valid'] = $valid;
	}

	/**
	 * Gets the price formula for the field.
	 *
	 * @return string
	 */
	public function get_formula(): string {
		return $this->data['formula'] ?? '';
	}

	/**
	 * Gets the expression for the executor.
	 *
	 * @return string
	 */
	public function get_expression(): string {
		return $this->data['expression'] ?? '';
	}

	/**
	 * Gets the price formula variables for the field.
	 *
	 * @return array
	 */
	public function get_variables(): array {
		return $this->data['variables'] ?? [];
	}

	/**
	 * Gets the price formula variables for the field.
	 *
	 * @return string
	 */
	public function get_valid(): bool {
		return $this->data['valid'] ?? false;
	}

	/**
	 * Whether the product product price should be excluded because of the formula.
	 */
	public function exclude_product_price(): bool {
		return $this->exclude_product_price;
	}
}
