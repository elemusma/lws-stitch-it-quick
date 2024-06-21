import { getFieldInputType, isFieldType, isCheckboxLike, isRadioLike, isTextLike } from './util';
import { isAfter, isBefore, isSameDay } from 'date-fns';

const conditionalLogic = function ( addToCartForm ) {
	const form = addToCartForm;
	let fieldData;

	function init() {
		if ( ! ( form instanceof HTMLFormElement ) ) {
			return false;
		}

		fieldData = Array.from( form.querySelectorAll( '.wpo-field' ) ).map( ( field ) => {
			return {
				element: field,
				inputElements: field.querySelectorAll( getFieldInputType( field.dataset.type ) ),
				type: field.dataset?.type,
				groupId: field.dataset?.groupId ? parseInt( field.dataset.groupId ) : null,
				optionId: field.dataset?.optionId ? parseInt( field.dataset.optionId ) : null,
				clogic: field.dataset.clogic === 'true' ? true : false,
				clogicRelation: field.dataset?.clogicRelation ?? false,
				clogicVisibility: field.dataset?.clogicVisibility ?? false,
				clogicConditions: field.dataset?.clogicConditions
					? JSON.parse( field.dataset.clogicConditions )
					: false,
			};
		} );

		bindEvents();
		checkLogic();
	}

	function bindEvents() {
		// bind the listener for input changes
		fieldData.forEach( ( field ) => field?.element.addEventListener( 'change', ( e ) => checkLogic( e ) ) );
	}

	/**
	 * Checks the conditional logic for the current form values.
	 */
	function checkLogic() {
		fieldData.forEach( ( field ) => {
			if ( field.clogic && field.clogicConditions ) {
				checkForConditions( field );
			}
		} );
	}

	function checkForConditions( field ) {
		const currentValues = getFormValues();

		if ( field.clogicRelation === 'or' ) {
			// if any of the conditions are true, trigger the visibility change.
			if ( field.clogicConditions.some( ( condition ) => checkCondition( currentValues, condition ) ) ) {
				toggleVisibility( field, true );
			} else {
				toggleVisibility( field, false );
			}
		}

		if ( field.clogicRelation === 'and' ) {
			// if all of the conditions are true, trigger the visibility change.
			if ( field.clogicConditions.every( ( condition ) => checkCondition( currentValues, condition ) ) ) {
				toggleVisibility( field, true );
			} else {
				toggleVisibility( field, false );
			}
		}
	}

	/**
	 * Check a condition against the current form values.
	 *
	 * @param {Array}  formValues
	 * @param {Object} condition
	 * @return {boolean} Whether the condition is satisfied.
	 */
	function checkCondition( formValues, condition ) {
		const field = formValues.find( ( formValue ) => formValue.optionId === condition.optionID );

		if ( ! field ) {
			return false;
		}

		if ( field.values.length === 1 ) {
			if ( condition.operator === 'contains' ) {
				return condition.value === 'any' ? true : field.values[ 0 ] === condition.value;
			}

			if ( condition.operator === 'not_contains' ) {
				return condition.value === 'any' ? false : field.values[ 0 ] !== condition.value;
			}

			if ( condition.operator === 'equals' ) {
				return condition.value === 'any' ? true : field.values[ 0 ] === condition.value;
			}

			if ( condition.operator === 'not_equals' ) {
				return condition.value === 'any' ? false : field.values[ 0 ] !== condition.value;
			}

			if ( condition.operator === 'greater' ) {
				return parseFloat( field.values[ 0 ] ) > parseFloat( condition.value );
			}

			if ( condition.operator === 'less' ) {
				return parseFloat( field.values[ 0 ] ) < parseFloat( condition.value );
			}

			if ( condition.operator === 'not_empty' ) {
				return field.values[ 0 ].length > 0;
			}

			if ( condition.operator === 'empty' ) {
				return field.values[ 0 ].length === 0;
			}

			if ( condition.operator === 'date_greater' ) {
				return isAfter( new Date( field.values[ 0 ] ), new Date( condition.value ) );
			}

			if ( condition.operator === 'date_less' ) {
				return isBefore( new Date( field.values[ 0 ] ), new Date( condition.value ) );
			}

			if ( condition.operator === 'date_equals' ) {
				return isSameDay( new Date( field.values[ 0 ] ), new Date( condition.value ) );
			}

			if ( condition.operator === 'date_not_equals' ) {
				return ! isSameDay( new Date( field.values[ 0 ] ), new Date( condition.value ) );
			}
		} else {
			if ( condition.operator === 'contains' ) {
				return condition.value === 'any' && field.values.length > 0
					? true
					: field.values.includes( condition.value );
			}

			if ( condition.operator === 'not_contains' ) {
				if ( condition.value === 'any' ) {
					return field.values.length === 0;
				}

				return ! field.values.includes( condition.value );
			}

			if ( condition.operator === 'equals' ) {
				return condition.value === 'any' && field.values.length > 0
					? true
					: field.values.includes( condition.value );
			}

			if ( condition.operator === 'not_equals' ) {
				if ( condition.value === 'any' ) {
					return field.values.length === 0;
				}

				return ! field.values.includes( condition.value );
			}

			if ( condition.operator === 'empty' ) {
				return field.values.length === 0;
			}

			if ( condition.operator === 'not_empty' ) {
				return field.values.length > 0;
			}
		}

		return false;
	}

	/**
	 * Toggles field visibility based on the provided boolean.
	 *
	 * @param {Object}  field
	 * @param {boolean} passing
	 */
	function toggleVisibility( field, passing ) {
		if ( passing ) {
			if ( field.clogicVisibility === 'show' ) {
				field.element.classList.remove( 'wpo-field-hide' );
			}

			if ( field.clogicVisibility === 'hide' ) {
				field.element.classList.add( 'wpo-field-hide' );
			}
		} else {
			if ( field.clogicVisibility === 'show' ) {
				field.element.classList.add( 'wpo-field-hide' );
			}

			if ( field.clogicVisibility === 'hide' ) {
				field.element.classList.remove( 'wpo-field-hide' );
			}
		}

		form.dispatchEvent( new Event( 'wpo_run_frontend_calculation' ) );
	}

	/**
	 * Get the current input values for all fields.
	 *
	 * @return {Array} An array of objects containing the field option ID and values.
	 */
	function getFormValues() {
		const formValues = [];
		const visibleFields = fieldData.filter( ( field ) => ! field.element.classList.contains( 'wpo-field-hide' ) );

		visibleFields.forEach( ( field ) => {
			const { optionId } = field;
			const values = getInputValues( field );

			formValues.push( { optionId, values: [ ...values ] } );
		} );

		return formValues;
	}

	/**
	 * Get the current input values for a field.
	 *
	 * @param {Object} field
	 * @return {Array} An array of values.
	 */
	function getInputValues( field ) {
		let inputElements = false;

		if ( isCheckboxLike( field ) ) {
			inputElements = field.element.querySelectorAll( 'input[type="checkbox"]' );
		}

		if ( isRadioLike( field ) ) {
			inputElements = field.element.querySelectorAll( 'input[type="radio"]' );
		}

		if ( isFieldType( field, 'dropdown' ) ) {
			inputElements = field.element.querySelector( 'select' );
		}

		if ( isFieldType( field, [ 'text', 'datepicker', 'file_upload', 'customer_price', 'number' ] ) ) {
			inputElements = field.element.querySelector( 'input' );
		}

		if ( isFieldType( field, 'textarea' ) ) {
			inputElements = field.element.querySelector( 'textarea' );
		}

		let values = [];

		if ( 'file_upload' === field.type ) {
			values = JSON.parse( inputElements.value );
		} else {
			if ( inputElements instanceof NodeList ) {
				inputElements = Array.from( inputElements );
			} else {
				inputElements = [ inputElements ];
			}

			values = inputElements.map( ( inputElement ) => {
				if ( isCheckboxLike( field ) || isRadioLike( field ) ) {
					return inputElement.checked ? inputElement.value : '';
				}

				return inputElement.value;
			} );
		}

		return values.filter( Boolean );
	}

	return { init };
};

export default conditionalLogic;
