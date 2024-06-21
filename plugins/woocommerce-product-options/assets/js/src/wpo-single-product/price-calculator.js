/* eslint-disable no-undef */
import { calculateOptionPrice, getChosenPrices, wcFormatPrice, wcUnformatPrice } from './price-util';
import { isFieldType } from './util';

// eslint-disable-next-line import/no-extraneous-dependencies
import { transliterate } from 'transliteration';

/**
 * WooCommerce dependencies.
 */

import Formula from 'fparser';

const priceCalculator = ( addToCartForm, isIntegration = false ) => {
	const form = addToCartForm;
	const mainPriceContainer = form
		?.closest( '.product,.product+.child' )
		?.querySelector(
			'.entry-summary .price, .woocommerce.product div>.wp-block-woocommerce-product-price .wc-block-components-product-price, .elementor-widget-woocommerce-product-price .price'
		);
	const originalPriceHtml = mainPriceContainer ? mainPriceContainer.innerHTML : null;
	let productPrice;
	let productQuantity;
	let totalsContainer;
	let fieldData = [];
	let formulaFields = [];
	let numberFields = [];
	let subTotals = [];

	const init = () => {
		if ( ! ( form instanceof HTMLFormElement ) ) {
			return false;
		}

		if ( ! ( form.querySelector( '.wpo-totals-container' ) instanceof HTMLElement ) ) {
			return false;
		}

		totalsContainer = form.querySelector( '.wpo-totals-container' );
		productPrice = parseFloat( form.querySelector( '.wpo-totals-container' ).dataset.productPrice );
		productQuantity = parseInt( form.querySelector( 'input.qty' )?.value ?? 1 );

		fieldData = Array.from( form.querySelectorAll( '.wpo-field' ) )
			.map( ( field ) => {
				return {
					element: field,
					type: field.dataset?.type,
					groupId: field.dataset?.groupId ? parseInt( field.dataset.groupId ) : null,
					optionId: field.dataset?.optionId ? parseInt( field.dataset.optionId ) : null,
					pricing: field.dataset?.pricing === 'true' ? true : false,
				};
			} )
			.filter( ( field ) => field.pricing );

		formulaFields = Array.from( form.querySelectorAll( '.wpo-field' ) )
			.filter( ( element ) => element.dataset.type === 'price_formula' )
			.map( ( field ) => {
				const inputElement = field.querySelector( 'input' );

				return {
					element: field,
					type: field.dataset?.type,
					groupId: field.dataset?.groupId ? parseInt( field.dataset.groupId ) : null,
					optionId: field.dataset?.optionId ? parseInt( field.dataset.optionId ) : null,
					formula: inputElement.dataset?.priceFormula ? inputElement.dataset.priceFormula : null,
					formulaVariables: inputElement.dataset?.priceFormulaVariables
						? JSON.parse( inputElement.dataset.priceFormulaVariables )
						: null,
				};
			} );

		numberFields = Array.from( form.querySelectorAll( '.wpo-field' ) )
			.filter( ( element ) => element.dataset.type === 'number' )
			.map( ( field ) => {
				return {
					element: field,
					inputElement: field.querySelector( 'input' ),
					type: field.dataset?.type,
					groupId: field.dataset?.groupId ? parseInt( field.dataset.groupId ) : null,
					optionId: field.dataset?.optionId ? parseInt( field.dataset.optionId ) : null,
				};
			} );

		bindEvents();
		runAllCalculations();
	};

	const bindEvents = () => {
		// listener for quantity changes
		form.querySelector( 'input.qty' ).addEventListener( 'change', ( e ) => {
			productQuantity = e.target.value;
			runAllCalculations();
		} );

		// if this WRO then set the modal button price
		if ( isIntegration === 'wro' && window.wcRestaurantProductModal ) {
			window.wcRestaurantProductModal.getModalElement().on( 'wro:modal:change_quantity', ( e ) => {
				const qty = e.target.querySelector( 'input.qty' );
				productQuantity = qty ? parseInt( qty.value ) : 1;
				runAllCalculations();
			} );
		}

		form.addEventListener( 'wpo_run_frontend_calculation', () => runAllCalculations() );

		// listener for field input changes
		fieldData.forEach( ( field ) => {
			if ( isFieldType( field, [ 'text', 'textarea', 'customer_price', 'number' ] ) ) {
				field?.element.addEventListener( 'input', () => runAllCalculations() );
			} else {
				field?.element.addEventListener( 'change', () => runAllCalculations() );
			}
		} );

		// listener for formula field input changes
		numberFields.forEach( ( field ) => {
			field?.inputElement.addEventListener( 'input', () => runAllCalculations() );
		} );

		// listener for WC variation changes
		jQuery( document ).on( 'found_variation', form, function ( event, variation ) {
			if ( form !== event.target ) {
				return;
			}

			totalsContainer.dataset.productPrice = variation.display_price;
			productPrice = variation.display_price;
			runAllCalculations();
		} );
	};

	/**
	 * Runs all the calculations.
	 */
	const runAllCalculations = () => {
		// reset the subtotals
		subTotals = [];

		fieldData.forEach( ( field ) => {
			// run initial calc on all pricing fields
			const chosenPrices = getChosenPrices( field );

			chosenPrices.forEach( ( chosenPrice ) => {
				addOptionPrice( field, chosenPrice.priceAmount, chosenPrice.priceType );
			} );
		} );

		runFormulaCalculations();
		calculatePricing();
	};

	/**
	 * Runs calculations for any formula fields.
	 */
	const runFormulaCalculations = () => {
		formulaFields.forEach( ( field ) => {
			if ( field.element.classList.contains( 'wpo-field-hide' ) ) {
				return;
			}

			let parser;

			try {
				parser = new Formula( transliterate( field.formula ) );
			} catch ( e ) {
				return;
			}

			const variables = parser.getVariables();

			// validate the variables in the formula against the available options
			const validVariables = variables.filter( ( variable ) => {
				return field.formulaVariables.findIndex( ( structuredVar ) => structuredVar.name === variable ) > -1;
			} );

			// Check we have the data we need to run the calculation
			if ( validVariables.length !== variables.length ) {
				return;
			}

			// create a variable object for the parser
			const fparseVariableObject = field.formulaVariables.reduce( ( accumulator, variable ) => {
				if ( variable.type === 'number_option' ) {
					const numberField = form.querySelector(
						`.wpo-field:not(.wpo-field-hide)[data-option-id="${ variable.id }"]`
					);
					const numberInput = numberField?.querySelector( 'input' );

					if ( numberInput?.checkValidity() ) {
						return { ...accumulator, [ variable.name ]: parseFloat( numberInput.value ) };
					}
				}

				if ( variable.type === 'product' ) {
					switch ( variable.name ) {
						case 'product_price':
							return { ...accumulator, [ variable.name ]: parseFloat( productPrice ) };
						case 'product_quantity':
							return { ...accumulator, [ variable.name ]: parseFloat( productQuantity ) };
					}
				}

				return { ...accumulator, [ variable.name ]: false };
			}, {} );

			const result = parser.evaluate( fparseVariableObject );

			if ( ! isNaN( result ) ) {
				addOptionPrice( field, result, 'price_formula' );
			}
		} );
	};

	/**
	 * Add the price of an option to the subtotals array
	 *
	 * @param {Object} field
	 * @param {number} priceAmount
	 * @param {string} priceType
	 */
	const addOptionPrice = ( field, priceAmount, priceType ) => {
		const optionPrice = calculateOptionPrice( field, priceType, priceAmount, productPrice, productQuantity, form );

		// update the subtotal array
		subTotals.push( {
			field: field.optionId,
			price: optionPrice,
		} );

		field.element.dataset.optionPrice = optionPrice;
	};

	/**
	 * Adds up all the option prices and sets the total price.
	 */
	const calculatePricing = () => {
		const optionsPrice = subTotals.reduce( ( total, subTotal ) => {
			return total + subTotal.price;
		}, 0 );

		let totalPrice =
			totalsContainer?.dataset?.excludeProductPrice === 'true'
				? optionsPrice
				: productQuantity * productPrice + optionsPrice;

		// total price is min 0
		totalPrice = totalPrice < 0 ? 0 : totalPrice;

		setPricingInHtml( totalPrice, optionsPrice );
	};

	/**
	 * Sets the pricing in the HTML.
	 *
	 * @param {number} totalPrice
	 * @param {number} optionsPrice
	 * @return {void}
	 */
	const setPricingInHtml = ( totalPrice, optionsPrice ) => {
		let subscriptionDetails = null;
		let variationPriceContainer = null;

		const formattedPrice = wcFormatPrice( totalPrice );

		// hide the WPO total if there is no priced options
		if ( optionsPrice === 0 && productQuantity === 1 ) {
			totalsContainer.classList.add( 'wpo-totals-hidden' );
		} else {
			totalsContainer.classList.remove( 'wpo-totals-hidden' );
		}

		// update the WPO total, which takes into account the product quantity
		form.querySelector( '.wpo-totals-container .wpo-price' ).innerHTML = formattedPrice;

		// once the WPO total is updated, we can get the price for the single item
		totalPrice = totalPrice / productQuantity;

		// if this is WRO then set the modal button price
		if ( isIntegration === 'wro' && window.wcRestaurantProductModal ) {
			// `totalPrice` already takes into account the product quantity
			// We need to divide by the quantity to get the price per item
			// so that the modal button price is correct.
			window.wcRestaurantProductModal.setPrice( totalPrice );
		}

		// check if we have a variation price container
		if ( form.classList.contains( 'variations_form' ) ) {
			variationPriceContainer = form.querySelector( '.woocommerce-variation-price .price' );
		}

		// determine if we're dealing with a simple or variation price container
		const priceContainer = variationPriceContainer ? variationPriceContainer : mainPriceContainer;

		if ( ! priceContainer ) {
			return;
		}

		// store the subscription details if we have them
		if ( priceContainer?.querySelector( '.subscription-details' ) ) {
			subscriptionDetails = priceContainer.querySelector( '.subscription-details' ).innerHTML;
		}

		// check if we're excluding the product price and it the price has not changed
		const excludeProductPrice = totalsContainer?.dataset?.excludeProductPrice === 'true';

		if ( excludeProductPrice && totalPrice === 0.0 ) {
			priceContainer.innerHTML = originalPriceHtml;
			return;
		}

		// check if the price has not changed
		if ( totalPrice === productPrice && ! variationPriceContainer && ! excludeProductPrice ) {
			priceContainer.innerHTML = originalPriceHtml;
			return;
		}

		let updatedHtml = `<span class="woocommerce-Price-amount amount"><bdi>	${ wcFormatPrice(
			totalPrice
		) }</bdi></span>`;

		// maybe add the subscription details
		updatedHtml += subscriptionDetails ? `<span class="subscription-details">${ subscriptionDetails }</span>` : '';

		priceContainer.innerHTML = updatedHtml;
	};

	return { init };
};

export default priceCalculator;
