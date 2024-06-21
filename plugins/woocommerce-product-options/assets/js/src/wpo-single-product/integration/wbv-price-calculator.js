import { isFieldType } from '../util';
import { calculateOptionPrice, getChosenPrices, wcFormatPrice } from '../price-util';

const wbvPriceCalculator = ( addToCartForm ) => {
	const form = addToCartForm;
	let wbvVariations = [];
	let fieldData = [];
	let totalPrice = 0;

	const init = () => {
		if ( ! ( form instanceof HTMLFormElement ) ) {
			return false;
		}

		if ( ! ( form.querySelector( '.wpo-totals-container' ) instanceof HTMLElement ) ) {
			return false;
		}

		wbvVariations = JSON.parse( form.dataset?.product_variations );

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

		bindEvents();
		runAllCalculations();
	};

	const bindEvents = () => {
		// listener for quantity changes
		document.querySelectorAll( 'input.wcbvp-quantity' ).forEach( ( quantityField ) => {
			const variationId = parseInt( quantityField.dataset.product_id );

			quantityField.addEventListener( 'change', ( e ) => {
				wbvVariations[
					wbvVariations.findIndex( ( variation ) => variation.variation_id === variationId )
				].quantity = parseInt( e.target.value );

				runAllCalculations();
			} );
		} );

		// listener for field input changes
		fieldData.forEach( ( field ) => {
			if ( isFieldType( field, [ 'text', 'textarea', 'customer_price' ] ) ) {
				field?.element.addEventListener( 'input', () => runAllCalculations() );
			} else {
				field?.element.addEventListener( 'change', () => runAllCalculations() );
			}
		} );

		// trigger after WBV recalculates it's internal price
		jQuery( document ).on( 'wc_bulk_variations_table_recalculate', () => runAllCalculations() );
	};

	const runAllCalculations = () => {
		// reset the subtotals
		wbvVariations.forEach( ( variation ) => {
			variation.subTotals = [];
		} );

		fieldData.forEach( ( field ) => {
			// run initial calc on all pricing fields
			const chosenPrices = getChosenPrices( field );

			chosenPrices.forEach( ( chosenPrice ) => {
				addOptionPrice( field, chosenPrice.priceAmount, chosenPrice.priceType );
			} );
		} );

		calculatePricing();
	};

	const addOptionPrice = ( field, priceAmount, priceType ) => {
		for ( const [ index, variation ] of wbvVariations.entries() ) {
			// if no qty is set, skip this variation
			if ( ! variation?.quantity || variation.quantity === 0 ) {
				continue;
			}

			const optionPrice = calculateOptionPrice(
				field,
				priceType,
				priceAmount,
				variation.display_price,
				variation.quantity
			);

			wbvVariations[ index ].subTotals.push( {
				field: field.optionId,
				price: optionPrice,
			} );
		}
	};

	const calculatePricing = () => {
		totalPrice = wbvVariations.reduce( ( total, variation ) => {
			if ( ! variation?.subTotals || ! Array.isArray( variation.subTotals ) ) {
				return total;
			}

			if ( ! variation?.quantity || variation.quantity === 0 ) {
				return total;
			}

			const optionsPrice = variation.subTotals.reduce( ( subTotal, subTotalItem ) => {
				return subTotal + subTotalItem.price;
			}, 0 );

			return total + ( variation.quantity * variation.display_price + optionsPrice );
		}, 0 );

		setPricingInHtml( wcFormatPrice( totalPrice ) );
	};

	const setPricingInHtml = ( formattedPrice ) => {
		form.querySelector( '.wcbvp_total_price bdi' ).innerHTML = formattedPrice;
	};

	return { init };
};

export default wbvPriceCalculator;
