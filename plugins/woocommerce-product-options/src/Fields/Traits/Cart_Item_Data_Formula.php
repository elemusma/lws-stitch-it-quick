<?php

namespace Barn2\Plugin\WC_Product_Options\Fields\Traits;

use Barn2\Plugin\WC_Product_Options\Util\Util;
use Barn2\Plugin\WC_Product_Options\Util\Conditional_Logic as Conditional_Logic_Util;
use Barn2\Plugin\WC_Product_Options\Model\Option as Option_Model;
use Barn2\Plugin\WC_Product_Options\Dependencies\NXP\MathExecutor;
use Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

trait Cart_Item_Data_Formula {

	/**
	 * Retrieves the cart item data for the selected value(s) of the field.
	 *
	 * @param mixed       $value
	 * @param WC_Product $product
	 * @param int $quantity
	 * @param array $options
	 * @return array
	 */
	public function get_cart_item_data( $value, $product, $quantity, $options ): ?array {
		if ( ! $this->formula->check_validity() ) {
			return [];
		}

		if ( Conditional_Logic_Util::is_field_hidden( $this, $options ) ) {
			return [];
		}

		$result = $this->evaluate_formula( $options, $quantity );

		if ( is_null( $result ) ) {
			return [];
		}

		return [
			'name'        => $this->option->name,
			'type'        => $this->option->type,
			'option_id'   => $this->option->id,
			'group_id'    => $this->option->group_id,
			'value'       => $value,
			'choice_data' => [
				[
					'label'   => '',
					'pricing' => [
						'type'   => 'price_formula',
						'amount' => $result,
					]
				]
			]
		];
	}

	/**
	 * Create a new MathExecutor instance and add custom functions.
	 *
	 * @return MathExecutor
	 */
	public function get_math_executor() {
		$executor = new MathExecutor();

		$default_custom_functions = [
			'sign' => function ( $number ) {
				return $number <=> 0;
			},
		];

		$custom_functions = array_merge( $default_custom_functions, apply_filters( 'wc_product_options_formula_custom_functions', [] ) );

		foreach ( $custom_functions as $function_name => $function ) {
			$executor->addFunction( $function_name, $function );
		}

		return $executor;
	}

	/**
	 * Evaluates the formula for the field.
	 *
	 * @param array $options
	 * @param int $quantity
	 * @return float|null
	 */
	public function evaluate_formula( array $options, int $quantity ): ?float {
		// retrieve options which match formula vars
		$executor = $this->get_math_executor();

		// validate that supplied formula variables are valid
		$variable_values   = [];
		$formula_variables = $this->formula->get_variables();
		$expression        = $this->formula->get_expression();

		foreach ( $formula_variables as $formula_variable ) {
			if ( $formula_variable['type'] === 'number_option' && $option = $this->is_valid_option_variable( $formula_variable, $options ) ) {
				$variable = $this->get_option_variable_value( $option, $options );
			} elseif ( $formula_variable['type'] === 'product' ) {
				$variable = $this->get_product_variable_value( $formula_variable['id'], $quantity );
			}

			// can't find variable value, return null
			if ( ! isset( $variable ) ) {
				return null;
			}

			$variable_values[ $formula_variable['name'] ] = $variable;
		}

		// remove empty variables, but not 0 or 0.00
		$variable_values = array_filter(
			$variable_values,
			function( $value ) {
				return $value !== '' && ! is_null( $value );
			}
		);

		// check we have values for all variables
		if ( count( $variable_values ) !== count( $formula_variables ) ) {
			return null;
		}

		// remove spaces in variable names
		$executor_variables = array_combine(
			array_map(
				function( $key ) {
					return str_replace( ' ', '', $key );
				},
				array_keys( $variable_values )
			),
			$variable_values
		);

		try {
			// set the variables for the expression evaluator
			foreach ( $executor_variables as $variable => $value ) {
				$executor->setVar( $variable, $value );
			}

			$result = $executor->execute( $expression );
		} catch ( Exception $e ) {
			$result = null;
		}

		return $result;
	}

	/**
	 * Checks if the supplied option variable is valid.
	 *
	 * @param array $formula_variable
	 * @param array $options
	 * @throws Exception If the option variable is not valid.
	 * @return object|null
	 */
	private function is_valid_option_variable( $formula_variable, $options ): ?object {
		try {
			$option = Option_Model::findOrFail( $formula_variable['id'] );

			if ( $option->type !== 'number' ) {
				throw new Exception( 'Option is not a number type' );
			}

			if ( ! isset( $options[ "option-$option->id" ] ) ) {
				throw new Exception( 'Option is not present in options array' );
			}

			return $option;
		} catch ( ModelNotFoundException $exception ) {
			return null;
		} catch ( Exception $exception ) {
			return null;
		}
	}


	/**
	 * Checks if the formula is valid.
	 *
	 * @param object $option
	 * @param array $options
	 * @return float|null
	 */
	private function get_option_variable_value( object $option, array $options ): ?float {
		if ( ! isset( $options[ "option-$option->id" ] ) ) {
			return null;
		}

		$field_class  = Util::get_field_class( $option->type );
		$field_object = new $field_class( $option, $this->product );

		$is_field_hidden = Conditional_Logic_Util::is_field_hidden( $field_object, $options );
		$sanitized_value = $is_field_hidden ? '0' : $field_object->sanitize( $options[ "option-$option->id" ] );

		if ( $sanitized_value !== '0' && empty( $sanitized_value ) ) {
			return null;
		}

		return $sanitized_value;
	}

	/**
	 * Get the values for product based variables.
	 *
	 * @param string $id
	 * @param int $quantity
	 * @return float
	 */
	private function get_product_variable_value( string $id, int $quantity ): ?float {
		switch ( $id ) {
			case 'product_price':
				return $this->product->get_price();
			case 'product_quantity':
				return $quantity;
			case 'default':
				return null;
		}
	}
}
