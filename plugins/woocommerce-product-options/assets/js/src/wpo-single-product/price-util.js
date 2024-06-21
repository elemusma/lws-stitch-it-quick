import CurrencyFactory from '@woocommerce/currency';
import { isFieldType, isCheckboxLike, isRadioLike } from './util';

/* eslint-disable no-undef */
export const calculateOptionPrice = ( field, priceType, optionPrice, productPrice, productQuantity ) => {
	let calculatedPrice = 0;

	switch ( priceType ) {
		case 'flat_fee':
			calculatedPrice = optionPrice;
			break;
		case 'percentage_inc':
			calculatedPrice = productPrice * ( optionPrice / 100 ) * productQuantity;
			break;
		case 'percentage_dec':
			calculatedPrice = -( productPrice * ( optionPrice / 100 ) ) * productQuantity;
			break;
		case 'quantity_based':
			calculatedPrice = optionPrice * productQuantity;
			break;
		case 'char_count':
			calculatedPrice = optionPrice * getCharCountElement( field ).value.trim().length * productQuantity;
			break;
		case 'price_formula':
			calculatedPrice = optionPrice * productQuantity;
			break;
	}

	return calculatedPrice;
};

const getCharCountElement = ( field ) => {
	if ( field.type === 'text' ) {
		return field.element.querySelector( 'input' );
	}

	if ( field.type === 'textarea' ) {
		return field.element.querySelector( 'textarea' );
	}
};

export const getChosenPrices = ( field ) => {
	const chosenPrices = [];

	if ( field.element.classList.contains( 'wpo-field-hide' ) ) {
		return chosenPrices;
	}

	if ( isCheckboxLike( field ) ) {
		Array.from( field.element.querySelectorAll( 'input[type="checkbox"]' ) ).forEach( ( checkbox ) => {
			if ( checkbox.checked ) {
				chosenPrices.push( {
					priceType: checkbox.dataset.priceType,
					priceAmount: parseFloat( checkbox.dataset.priceAmount ),
				} );
			}
		} );
	}

	if ( isRadioLike( field ) ) {
		Array.from( field.element.querySelectorAll( 'input[type="radio"]' ) ).forEach( ( radio ) => {
			if ( radio.checked ) {
				chosenPrices.push( {
					priceType: radio.dataset.priceType,
					priceAmount: parseFloat( radio.dataset.priceAmount ),
				} );
			}
		} );
	}

	if ( isFieldType( field, 'dropdown' ) ) {
		const selectElement = field.element.querySelector( 'select' );
		chosenPrices.push( {
			priceType: selectElement.options[ selectElement.selectedIndex ].dataset.priceType,
			priceAmount: parseFloat( selectElement.options[ selectElement.selectedIndex ].dataset.priceAmount ),
		} );
	}

	if ( isFieldType( field, 'number' ) ) {
		const inputElement = field.element.querySelector( 'input' );

		if ( inputElement.value.trim().length > 0 ) {
			chosenPrices.push( {
				priceType: inputElement.dataset.priceType,
				priceAmount: parseFloat( inputElement.dataset.priceAmount ),
			} );
		}
	}

	if ( isFieldType( field, [ 'text', 'datepicker' ] ) ) {
		const inputElement = field.element.querySelector( 'input' );

		if (
			inputElement.dataset.priceType === 'char_count' ||
			( inputElement.dataset.priceType !== 'char_count' && inputElement.value.trim().length > 0 )
		) {
			chosenPrices.push( {
				priceType: inputElement.dataset.priceType,
				priceAmount: parseFloat( inputElement.dataset.priceAmount ),
			} );
		}
	}

	if ( isFieldType( field, 'textarea' ) ) {
		const inputElement = field.element.querySelector( 'textarea' );

		if (
			inputElement.dataset.priceType === 'char_count' ||
			( inputElement.dataset.priceType !== 'char_count' && inputElement.value.trim().length > 0 )
		) {
			chosenPrices.push( {
				priceType: inputElement.dataset.priceType,
				priceAmount: parseFloat( inputElement.dataset.priceAmount ),
			} );
		}
	}

	if ( isFieldType( field, 'file_upload' ) ) {
		const inputElement = field.element.querySelector( `input[name="wpo-option[option-${ field.optionId }]"]` );
		const uploadedFiles = JSON.parse( inputElement.value );

		if ( uploadedFiles.length > 0 ) {
			chosenPrices.push( {
				priceType: inputElement.dataset.priceType,
				priceAmount: parseFloat( inputElement.dataset.priceAmount ),
			} );
		}
	}

	if ( isFieldType( field, 'customer_price' ) ) {
		const inputElement = field.element.querySelector( 'input' );
		const inputAmount = isNaN( parseFloat( inputElement.value ) ) ? 0 : parseFloat( inputElement.value );

		chosenPrices.push( {
			priceType: 'flat_fee',
			priceAmount: inputAmount,
		} );
	}

	return chosenPrices;
};

export const wcFormatPrice = ( price ) => {
	const storeCurrency = CurrencyFactory( wpoSettings.currency );

	return storeCurrency.formatAmount( price );
};

export const wcUnformatPrice = ( formattedPrice ) => {
	const curSettings = wpoSettings.currency;
	const { symbol, decimalSeparator } = curSettings;
	const symbolRegExp = new RegExp( `${ symbol }`, 'g' );
	const valueRegExp = new RegExp( `[^0-9-${ decimalSeparator }]`, 'g' );

	const tmp = document.createElement( 'DIV' );
	tmp.innerHTML = formattedPrice;
	formattedPrice = tmp.textContent || tmp.innerText || '';

	return parseFloat(
		formattedPrice
			// remove the currency symbol first so that it doesn't interfere with the value/decimal separators
			.replace( symbolRegExp, '' )
			// then remove any non-numeric characters except the decimal separator and the minus sign
			.replace( valueRegExp, '' )
			// finally replace the decimal separator (there should be only one at this point) with a dot
			.replace( decimalSeparator, '.' )
	);
};
