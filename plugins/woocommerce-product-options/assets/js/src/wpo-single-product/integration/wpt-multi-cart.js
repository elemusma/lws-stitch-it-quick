import { getFieldInputType, isCheckboxLike, isRadioLike, isFieldType } from '../util';

const wptMultiCart = ( addToCartForm ) => {
	const form = addToCartForm;
	const multiCartSelectedCheckbox = form.nextElementSibling.querySelector( 'input[name="product_ids[]"]' );
	let modified = false;
	let allFields;

	function init() {
		if ( ! ( form instanceof HTMLFormElement ) ) {
			return false;
		}

		allFields = Array.from( form.querySelectorAll( '.wpo-field' ) ).map( ( element ) => {
			return {
				element,
				type: element.dataset?.type ?? null,
				optionId: element.dataset?.optionId ? parseInt( element.dataset.optionId ) : null,
				inputElements: element.querySelectorAll( getFieldInputType( element.dataset.type ) ),
			};
		} );

		allFields.forEach( ( field ) => {
			field.inputElements.forEach( ( inputElement ) => {
				updateMultiHiddenField( inputElement.value, field );

				inputElement.addEventListener( 'change', maybeCheckMultiCartRow );
			} );
		} );

		multiCartSelectedCheckbox.addEventListener( 'change', maybeResetModified );

		bindEvents();
	}

	function bindEvents() {
		allFields.forEach( ( field ) => {
			field.inputElements.forEach( ( inputElement ) => {
				inputElement.addEventListener( 'change', ( e ) => {
					updateMultiHiddenField( e.target.value, field );
				} );
			} );
		} );
	}

	/**
	 * Checks the multicheck row checkbox if a product option is selected.
	 */
	function maybeCheckMultiCartRow() {
		if ( ! multiCartSelectedCheckbox ) {
			return;
		}

		if ( multiCartSelectedCheckbox && ! modified ) {
			multiCartSelectedCheckbox.checked = true;
			modified = true;
		}
	}

	function maybeResetModified( event ) {
		if ( ! event.target.checked ) {
			modified = false;
		}
	}

	function updateMultiHiddenField( value, field ) {
		if ( ! field ) {
			return;
		}

		// check if we have multi cart check
		const multiCheck = form.nextElementSibling;

		if ( ! multiCheck ) {
			return;
		}

		// Find the multi-cart input which corresponds to the changed cart input
		let multiCartSelector = isCheckboxLike( field )
			? `input[data-input-name="wpo-option[option-${ field.optionId }][]"]`
			: `input[data-input-name="wpo-option[option-${ field.optionId }]"]`;

		if ( field.element.dataset?.parentType === 'product' ) {
			multiCartSelector = `input[data-input-name*="wpo-option[option-${ field.optionId }]"]`;
		}

		const multiCartInput = multiCheck.querySelector( multiCartSelector );

		if ( ! multiCartInput ) {
			return;
		}

		if ( isCheckboxLike( field ) || field.element.dataset?.parentType === 'product' ) {
			const checkedValues = Array.from( field.inputElements )
				.filter( ( input ) => input.checked )
				.map( ( input ) => input.value );

			multiCartInput.value = checkedValues;
		} else if ( isRadioLike( field ) ) {
			const checkedValue = Array.from( field.inputElements ).find( ( input ) => input.checked );

			multiCartInput.value = checkedValue?.value;
		} else {
			multiCartInput.value = value;
		}
	}

	return { init };
};

export default wptMultiCart;
