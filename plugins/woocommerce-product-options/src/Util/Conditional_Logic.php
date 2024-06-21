<?php

namespace Barn2\Plugin\WC_Product_Options\Util;

use Barn2\Plugin\WC_Product_Options\Model\Option as Option_Model;
use Barn2\Plugin\WC_Product_Options\Fields;
use DateTime;

/**
 * Conditional logic utilities.
 *
 * @package   Barn2\woocommerce-product-options
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
final class Conditional_Logic {

	/**
	 * Determines if a field is hidden by conditional logic.
	 *
	 * @param Fields\Abstract_Field $field
	 * @param array $option_data
	 */
	public static function is_field_hidden( $field, $option_data, $recursive_field = null ): bool {
		if ( ! $field->has_conditional_logic() ) {
			return false;
		}

		$passing = self::check_for_conditions( $field, $option_data, $recursive_field );

		$config = $field->get_conditional_logic_config();

		if ( $config->visibility === 'show' ) {
			return ! $passing;
		}

		if ( $config->visibility === 'hide' ) {
			return $passing;
		}

		return false;
	}

	/**
	 * Determines if the fields conditions are met.
	 *
	 * @param Abstract_Field $field
	 * @param array $option_data
	 */
	private static function check_for_conditions( $field, $option_data, $recursive_field ): bool {
		$config     = $field->get_conditional_logic_config();
		$product    = $field->get_product();
		$conditions = $config->conditions;

		if ( $recursive_field ) {
			$conditions = array_filter(
				$conditions,
				function ( $condition ) use ( $recursive_field ) {
					return $condition['optionID'] !== (int) $recursive_field->get_id();
				}
			);
		}

		if ( $config->relation === 'or' ) {
			// pass if any of the conditions are true.
			$matches = array_filter(
				$conditions,
				function ( $condition ) use ( $option_data, $field, $product ) {
					return self::check_condition( $option_data, (object) $condition, $field, $product );
				}
			);

			return count( $matches ) > 0;
		}

		if ( $config->relation === 'and' ) {
			// pass if all conditions are true.
			$matches = array_reduce(
				$conditions,
				function ( $acc, $condition ) use ( $option_data, $field, $product ) {
					return $acc && self::check_condition( $option_data, (object) $condition, $field, $product );
				},
				true
			);

			return ! ! $matches;
		}
	}

	/**
	 * Check a single condition against the current form values.
	 *
	 * @param array $option_data
	 * @param object $condition
	 * @param WC_Product $product
	 * @return bool Whether the condition is satisfied.
	 */
	private static function check_condition( $option_data, $condition, $recursive_field, $product ): bool {
		$key = "option-$condition->optionID";

		if ( ! isset( $option_data[ $key ] ) ) {
			return (
				$condition->operator === 'empty' ||
				$condition->operator === 'not_contains' ||
				$condition->operator === 'not_equals'
			);
		}

		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$option = Option_Model::find( $condition->optionID );

		if ( ! $option || ! $option instanceof Option_Model ) {
			return false;
		}

		$class = Util::get_field_class( $option->type );

		if ( ! class_exists( $class ) ) {
			return false;
		}

		$field = new $class( $option, $product );

		// check if the option itself isn't hidden from higher up in the conditional logic.
		if ( self::is_field_hidden( $field, $option_data, $recursive_field ) ) {
			return false;
		}

		$option_values = self::maybe_json_decode( $option_data[ $key ] );

		if ( ! is_array( $option_values ) ) {
			$option_values = [ $option_values ];
		}

		if ( isset( $option_values['products'] ) ) {
			if ( ! is_array( $option_values['products'] ) ) {
				$option_values['products'] = [ $option_values['products'] ];
			} else {
				$option_values = array_reduce(
					array_values( $option_values['products'] ),
					function ( $acc, $product ) {
						return array_merge( $acc, array_values( $product ) );
					},
					[]
				);
			}
		}

		if ( count( $option_values ) === 1 ) {
			if ( $condition->operator === 'contains' ) {
				return $condition->value === 'any' ? true : $option_values[0] === $condition->value;
			}

			if ( $condition->operator === 'not_contains' ) {
				return $condition->value === 'any' ? false : $option_values[0] !== $condition->value;
			}

			if ( $condition->operator === 'equals' ) {
				return $condition->value === 'any' ? true : $option_values[0] === $condition->value;
			}

			if ( $condition->operator === 'not_equals' ) {
				return $condition->value === 'any' ? false : $option_values[0] !== $condition->value;
			}

			if ( $condition->operator === 'greater' ) {
				return (float) $option_values[0] > (float) $condition->value;
			}

			if ( $condition->operator === 'less' ) {
				return (float) $option_values[0] < (float) $condition->value;
			}

			if ( $condition->operator === 'not_empty' ) {
				return strlen( $option_values[0] ) > 0;
			}

			if ( $condition->operator === 'empty' ) {
				return strlen( $option_values[0] ) === 0;
			}

			if ( in_array( $condition->operator, [ 'date_greater', 'date_less', 'date_equals', 'date_not_equals' ], true ) ) {
				$field_date     = new DateTime( $option_values[0] );
				$condition_date = new DateTime( $condition->value );

				switch ( $condition->operator ) {
					case 'date_greater':
						return $field_date->format( 'U' ) > $condition_date->format( 'U' );
					case 'date_less':
						return $field_date->format( 'U' ) < $condition_date->format( 'U' );
					case 'date_equals':
						return $field_date->format( 'Y-m-d' ) === $condition_date->format( 'Y-m-d' );
					case 'date_not_equals':
						return $field_date->format( 'Y-m-d' ) !== $condition_date->format( 'Y-m-d' );
				}
			}
		} else {
			if ( $condition->operator === 'contains' ) {
				return $condition->value === 'any' && count( $option_values ) > 0
					? true
					: in_array( $condition->value, $option_values, true );
			}

			if ( $condition->operator === 'not_contains' ) {
				if ( $condition->value === 'any' ) {
					return count( $option_values ) === 0;
				}

				return ! in_array( $condition->value, $option_values, true );
			}

			if ( $condition->operator === 'equals' ) {
				return $condition->value === 'any' && count( $option_values ) > 0
					? true
					: in_array( $condition->value, $option_values, true );
			}

			if ( $condition->operator === 'not_equals' ) {
				if ( $condition->value === 'any' ) {
					return count( $option_values ) === 0;
				}

				return ! in_array( $condition->value, $option_values, true );
			}

			if ( $condition->operator === 'empty' ) {
				return count( $option_values ) === 0;
			}

			if ( $condition->operator === 'not_empty' ) {
				return count( $option_values ) > 0;
			}
		}
	}

	public static function maybe_json_decode( $json_value ) {
		if ( is_string( $json_value ) && is_array( json_decode( $json_value, true ) ) ) {
			$json_value = json_decode( $json_value, true );
		}

		return $json_value;
	}

}
