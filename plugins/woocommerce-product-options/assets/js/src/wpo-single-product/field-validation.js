import { __, _n, sprintf } from '@wordpress/i18n';
import { getFieldInputType } from './util';

/**
 * Handles frontend field validation for product options in a add to cart form.
 *
 * @param {HTMLFormElement}   addToCartForm
 * @param {HTMLButtonElement} addtoCartButton
 */
const fieldValidation = ( addToCartForm, addtoCartButton = null ) => {
	const form = addToCartForm;
	const submitButton = addtoCartButton ?? form.querySelector( 'button.single_add_to_cart_button' );
	let allFields,
		checkBoxFields,
		datePickerFields,
		radioFields,
		fileUploadFields,
		selectFields,
		textFields,
		productFields;

	function isFieldRequired( field ) {
		return field.required && ! field.element.classList.contains( 'wpo-field-hide' );
	}

	/**
	 * Initialize the field validation.
	 */
	function init() {
		if ( ! ( form instanceof HTMLFormElement ) || ! ( submitButton instanceof HTMLButtonElement ) ) {
			return false;
		}

		allFields = Array.from( form.querySelectorAll( '.wpo-field' ) ).map( ( element ) => {
			return {
				element,
				inputElements: element.querySelectorAll( getFieldInputType( element.dataset.type ) ),
				required: element.classList.contains( 'wpo-field-required' ),
			};
		} );

		checkBoxFields = Array.from(
			form.querySelectorAll(
				'.wpo-field-checkbox, .wpo-field-images, .wpo-field-text_labels, .wpo-field-product[data-type="checkbox"], .wpo-field-product[data-type="image_buttons"]'
			)
		).map( ( element ) => {
			return {
				element,
				inputElements: element.querySelectorAll( 'input[type="checkbox"]' ),
				required: element.classList.contains( 'wpo-field-required' ),
				minQty: element.dataset?.minQty ? parseInt( element.dataset.minQty ) : null,
				maxQty: element.dataset?.maxQty ? parseInt( element.dataset.maxQty ) : null,
			};
		} );

		radioFields = Array.from(
			form.querySelectorAll(
				'.wpo-field-radio, .wpo-field-color_swatches, .wpo-field-product[data-type="radio"]'
			)
		).map( ( element ) => {
			return {
				element,
				inputElements: element.querySelectorAll( 'input[type="radio"]' ),
				required: element.classList.contains( 'wpo-field-required' ),
			};
		} );

		selectFields = Array.from(
			form.querySelectorAll( '.wpo-field-dropdown, .wpo-field-product[data-type="dropdown"]' )
		).map( ( element ) => {
			return {
				element,
				inputElements: element.querySelectorAll( 'select' ),
				required: element.classList.contains( 'wpo-field-required' ),
			};
		} );

		textFields = Array.from( form.querySelectorAll( '.wpo-field-text, .wpo-field-textarea' ) ).map( ( element ) => {
			const firstInput = element.querySelector( getFieldInputType( element.dataset.type ) );
			const minChar = firstInput?.minLength;
			const maxChar = firstInput?.maxLength;
			return {
				element,
				inputElements: element.querySelectorAll( getFieldInputType( element.dataset.type ) ),
				required: element.classList.contains( 'wpo-field-required' ),
				minChar,
				maxChar,
			};
		} );

		fileUploadFields = Array.from( form.querySelectorAll( '.wpo-field-file_upload' ) ).map( ( element ) => {
			return {
				element,
				inputElements: element.querySelectorAll( 'input' ),
				required: element.classList.contains( 'wpo-field-required' ),
			};
		} );

		datePickerFields = Array.from( form.querySelectorAll( '.wpo-field-datepicker' ) ).map( ( element ) => {
			return {
				element,
				inputElements: element.querySelectorAll( '.wpo-datepicker-container > input' ),
				required: element.classList.contains( 'wpo-field-required' ),
			};
		} );

		productFields = Array.from( form.querySelectorAll( '.wpo-field-product' ) ).map( ( element ) => {
			return {
				element,
				inputElements: element.querySelectorAll( getFieldInputType( element.dataset.type ) ),
				required: element.classList.contains( 'wpo-field-required' ),
				minQty: element.dataset?.minQty ? parseInt( element.dataset.minQty ) : null,
				maxQty: element.dataset?.maxQty ? parseInt( element.dataset.maxQty ) : null,
			};
		} );

		bindEvents();
	}

	/**
	 * Bind the events.
	 */
	function bindEvents() {
		checkBoxFields
			.filter( ( field ) => isFieldRequired( field ) || field.minQty !== null || field.maxQty !== null )
			.forEach( ( field ) => {
				field.inputElements.forEach( ( inputElement ) => {
					inputElement.addEventListener( 'change', () => handleCheckboxLimitsValidation( field ) );
				} );
			} );

		radioFields.forEach( ( field ) => {
			field.element.addEventListener( 'change', () => handleRadioRequiredValidation( field ) );
		} );

		fileUploadFields.forEach( ( field ) => {
			field.element.addEventListener( 'change', () => handleFileUploadRequiredValidation( field ) );
		} );

		selectFields.forEach( ( field ) => {
			field.element.addEventListener( 'change', () => handleSelectRequiredValidation( field ) );
		} );

		textFields.forEach( ( field ) => {
			field.element.addEventListener( 'change', () => handleTextRequiredValidation( field ) );
		} );

		datePickerFields.forEach( ( field ) => {
			field.element.addEventListener( 'change', () => handleDatePickerRequiredValidation( field ) );
		} );

		productFields.forEach( ( field ) => {
			field.inputElements.forEach( ( inputElement ) => {
				inputElement.addEventListener( 'change', () => handleProductRequiredValidation( field, inputElement ) );
			} );
		} );

		allFields.forEach( ( field ) => {
			field.element.addEventListener( 'change', () => reportFieldValidityInHTML( field ) );

			field.inputElements.forEach( ( inputElement ) => {
				// disable html validation tooltips.
				inputElement.addEventListener( 'invalid', ( event ) => event.preventDefault() );
			} );
		} );

		submitButton.addEventListener( 'click', ( event ) => {
			checkBoxFields.forEach( ( field ) => handleCheckboxLimitsValidation( field ) );
			radioFields.forEach( ( field ) => handleRadioRequiredValidation( field ) );
			datePickerFields.forEach( ( field ) => handleDatePickerRequiredValidation( field ) );
			fileUploadFields.forEach( ( field ) => handleFileUploadRequiredValidation( field ) );
			selectFields.forEach( ( field ) => handleSelectRequiredValidation( field ) );
			textFields.forEach( ( field ) => handleTextRequiredValidation( field ) );
			productFields.forEach( ( field ) => handleProductRequiredValidation( field ) );

			allFields.forEach( ( field ) => reportFieldValidityInHTML( field ) );

			if ( ! checkFormValidity() ) {
				const firstErrorElement = addToCartForm.querySelector( '.wpo-error-message' );
				const fieldContainer = firstErrorElement?.closest( '.wpo-field' );

				if ( fieldContainer ) {
					const offset = 45;
					const bodyRect = document.body.getBoundingClientRect().top;
					const elementRect = fieldContainer.getBoundingClientRect().top;
					const elementPosition = elementRect - bodyRect;
					const offsetPosition = elementPosition - offset;

					window.scrollTo( {
						top: offsetPosition,
						behavior: 'smooth',
					} );
				}

				event.preventDefault();
			}

			addToCartForm.reportValidity();
		} );
	}

	/**
	 * Adds or removes inline HTML error messages to fields.
	 *
	 * @param {Object} field
	 */
	function reportFieldValidityInHTML( field ) {
		const firstInput = field.inputElements.item( 0 );

		if ( ! firstInput ) {
			return;
		}

		// check for existing error message.
		const existingErrorMessage = field.element.querySelector( '.wpo-error-message' );
		existingErrorMessage?.remove();

		if ( ! checkFieldValidity( field ) && ! field.element.classList.contains( 'wpo-field-hide' ) ) {
			// create new error message element
			const errorMessage = document.createElement( 'span' );
			errorMessage.classList.add( 'wpo-error-message' );

			/**
			 * Datepicker is a hidden input and the validation message is not stored in the validationMessage property.
			 */
			if ( field.element.dataset.type === 'datepicker' ) {
				errorMessage.textContent = __( 'Please select a date.', 'woocommerce-product-options' );
			} else {
				errorMessage.textContent = firstInput.validationMessage;
			}
			// add to the field
			field.element.appendChild( errorMessage );
		} else {
			firstInput.required = false;
		}
	}

	/**
	 * Checks if a WPO option is valid.
	 *
	 * @param {Object} field
	 * @return {boolean} True if the field is valid.
	 */
	function checkFieldValidity( field ) {
		const firstInput = field.inputElements.item( 0 );

		if ( field.element.dataset.type === 'datepicker' ) {
			return isFieldRequired( field ) && firstInput.value.length === 0 ? false : true;
		}

		return firstInput.checkValidity();
	}

	/**
	 * Checks if all WPO options in the form are valid.
	 *
	 * @return {boolean} True if all fields are valid.
	 */
	function checkFormValidity() {
		return allFields.every( ( field ) => checkFieldValidity( field ) );
	}

	/**
	 * Handles minimum and maximum quantity for checkbox fields.
	 *
	 * @param {Object} field
	 * @param {Object} inputElement
	 */
	function handleCheckboxLimitsValidation( field, inputElement ) {
		const checkedInputs = Array.from( field.inputElements ).filter( ( input ) => input.checked );
		const firstInput = field.inputElements.item( 0 );
		const requiredQty = isFieldRequired( field ) ? 1 : 0;
		const minQty = field.minQty ? field.minQty : requiredQty;
		const maxQty = field.maxQty ? field.maxQty : 0;

		if ( ( isFieldRequired( field ) || checkedInputs.length > 0 ) && minQty && checkedInputs.length < minQty ) {
			const minValidationMessage = sprintf(
				/* translators: %d: minimum number of required options */
				_n(
					'Please select at least %d option.',
					'Please select at least %d options.',
					minQty,
					'woocommerce-product-options'
				),
				minQty
			);

			firstInput?.setCustomValidity( minValidationMessage );
			return true;
		} else if (
			( isFieldRequired( field ) || checkedInputs.length > 0 ) &&
			maxQty &&
			checkedInputs.length > maxQty
		) {
			const maxValidationMessage = sprintf(
				/* translators: %d: maximum number of required options */
				_n(
					'Please select no more than %d option.',
					'Please select no more than %d options.',
					field.maxQty,
					'woocommerce-product-options'
				),
				field.maxQty
			);

			firstInput?.setCustomValidity( maxValidationMessage );
			return true;
		} else if ( maxQty === 1 && checkedInputs.length > maxQty ) {
			// this makes the checkboxes work like a radio group
			if ( inputElement ) {
				field.inputElements.forEach( ( input ) => {
					input.checked = input.value === inputElement.value;
					input
						.closest( '.wpo-image-button' )
						?.querySelector( '.wpo-image-active' )
						?.classList.toggle( 'wpo-image-selected', input.checked );
				} );
			}

			return true;
		}

		firstInput?.setCustomValidity( '' );
		return false;
	}

	/**
	 * Custom required validation for radio groups.
	 *
	 * @param {Object} field
	 */
	function handleRadioRequiredValidation( field ) {
		const selectedInputs = Array.from( field.inputElements ).filter( ( input ) => input.checked );
		const firstInput = field.inputElements.item( 0 );

		if ( selectedInputs.length === 0 && isFieldRequired( field ) ) {
			firstInput?.setCustomValidity( __( 'Please select an option.', 'woocommerce-product-options' ) );
			firstInput.required = true;
			return true;
		}

		firstInput?.setCustomValidity( '' );
		firstInput.required = false;

		return false;
	}

	/**
	 * Custom required validation for selects.
	 *
	 * @param {Object} field
	 */
	function handleSelectRequiredValidation( field ) {
		const firstInput = field.inputElements.item( 0 );

		if ( firstInput.value.length === 0 && isFieldRequired( field ) ) {
			firstInput?.setCustomValidity( __( 'Please select an option.', 'woocommerce-product-options' ) );
			firstInput.required = true;
			return true;
		}

		firstInput?.setCustomValidity( '' );
		firstInput.required = false;

		return false;
	}

	/**
	 * Custom required validation for text/textarea.
	 *
	 * @param {Object} field
	 */
	function handleTextRequiredValidation( field ) {
		const firstInput = field.inputElements.item( 0 );
		const requiredChar = isFieldRequired( field ) ? 1 : 0;
		const minChar = field.minQty ? field.minQty : requiredChar;
		const maxChar = field.maxQty ? field.maxQty : 0;

		if ( isFieldRequired( field ) && firstInput.value.length === 0 ) {
			firstInput?.setCustomValidity( __( 'Please fill in this field.', 'woocommerce-product-options' ) );
			firstInput.required = true;
			return true;
		} else if ( isFieldRequired( field ) && minChar && firstInput.value.length < minChar ) {
			const minValidationMessage = sprintf(
				/* translators: %d: minimum number of required options */
				_n(
					'Please input at least %d character.',
					'Please input at least %d characters.',
					minChar,
					'woocommerce-product-options'
				),
				minChar
			);

			firstInput?.setCustomValidity( minValidationMessage );
			return true;
		} else if ( isFieldRequired( field ) && maxChar && firstInput.value.length > maxChar ) {
			const maxValidationMessage = sprintf(
				/* translators: %d: maximum number of required options */
				_n(
					'Please input no more than %d character.',
					'Please input no more than %d characters.',
					maxChar,
					'woocommerce-product-options'
				),
				maxChar
			);

			firstInput?.setCustomValidity( maxValidationMessage );
			return true;
		}

		firstInput?.setCustomValidity( '' );
		firstInput.required = false;

		return false;
	}

	/**
	 * Custom required validation for file upload.
	 *
	 * @param {Object} field
	 */
	function handleFileUploadRequiredValidation( field ) {
		const firstInput = field.inputElements.item( 0 );
		const fileList = JSON.parse( firstInput.value );

		if ( fileList.length < 1 && isFieldRequired( field ) ) {
			firstInput?.setCustomValidity( __( 'Please select a file.', 'woocommerce-product-options' ) );
			firstInput.required = true;
			return true;
		}

		firstInput?.setCustomValidity( '' );
		firstInput.valid = true;
		firstInput.required = false;

		return false;
	}

	/**
	 * Custom required validation for date picker.
	 *
	 * @param {Object} field
	 */
	function handleDatePickerRequiredValidation( field ) {
		const firstInput = field.inputElements.item( 0 );

		if ( firstInput.value.length === 0 && isFieldRequired( field ) ) {
			firstInput?.setCustomValidity( __( 'Please select a date.', 'woocommerce-product-options' ) );
			firstInput.required = true;
			return true;
		}

		firstInput?.setCustomValidity( '' );
		firstInput.required = false;

		return false;
	}

	/**
	 * Custom required validation for product groups.
	 *
	 * @param {Object} field
	 * @param {Object} inputElement
	 */
	function handleProductRequiredValidation( field, inputElement ) {
		switch ( field.element.dataset.type ) {
			case 'radio':
				field.inputElements.forEach( ( input ) => {
					input.checked = input.value === inputElement.value;
				} );
				return handleRadioRequiredValidation( field );
			case 'dropdown':
				return handleSelectRequiredValidation( field );
			case 'checkbox':
			case 'image_buttons':
			default:
				return handleCheckboxLimitsValidation( field, inputElement );
		}
	}

	return { init };
};

export default fieldValidation;
