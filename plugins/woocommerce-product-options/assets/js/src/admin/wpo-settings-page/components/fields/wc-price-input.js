/**
 * WordPress dependencies.
 */
import { useState, useEffect, useRef } from '@wordpress/element';

/**
 * External dependencies.
 */
import classnames from 'classnames';

/**
 * A pricing input which is formatted according to WC store settings.
 *
 * @param {Object}   props
 * @param {boolean}  props.displayCode
 * @param {Object}   props.storeCurrency
 * @param {boolean}  props.required
 * @param {string}   props.value
 * @param {Function} props.onChange
 * @return {React.ReactElement} InputWCPrice
 */
function WCPriceInput( { required = false, storeCurrency, value, onChange = () => {} } ) {
	const currencyConfig = storeCurrency.getCurrencyConfig();
	const formattedAmount = wpoFormatDecimalString( value, storeCurrency );
	const ref = useRef( null );

	/**
	 * Track the cursor position.
	 */
	const [ cursor, setCursor ] = useState( null );

	/**
	 * Track the fcous state.
	 */
	const [ isFocused, setIsFocused ] = useState( false );

	const backdropCSSClasses = classnames( 'barn2-prefixed-input-backdrop', {
		'barn2-input-focused': isFocused,
	} );

	/**
	 * Handles the cursor position when formatting the input amount.
	 */
	useEffect( () => {
		const input = ref.current;
		if ( input ) input.setSelectionRange( cursor, cursor );
	}, [ ref, cursor, value ] );

	/**
	 * Handles the input change.
	 *
	 * @param {Event} event
	 */
	const handleChange = ( event ) => {
		let targetValue = event.target.value;

		if ( currencyConfig?.thousandSeparator?.length > 0 ) {
			const thousandRegExp = new RegExp( `\\${ currencyConfig?.thousandSeparator }`, 'gi' );
			targetValue = targetValue.replace( thousandRegExp, '' );
		}

		let targetFloat = parseFloat( targetValue );
		let sepCount = 0;

		if ( currencyConfig?.thousandSeparator?.length > 0 ) {
			while ( targetFloat >= 1000 ) {
				// count the thousand separator
				sepCount++;
				targetFloat = targetFloat / 1000;
			}
		}

		// get the thousand separator count from the previous change
		const prevCount = parseInt( event.target.dataset.prevCount ?? 0 );
		// move the cursor to the right if the number of thousand separators changed
		setCursor( event.target.selectionStart + sepCount - prevCount );
		// store the current number of thousand separators
		event.target.dataset.prevCount = sepCount;

		onChange( event );
	};

	return (
		<div className="barn2-prefixed-input-container barn2-currency-input-container">
			<span className="barn2-input-prefix">{ currencyConfig.symbol }</span>
			<input
				required={ required }
				className="barn2-prefixed-input barn2-currency-input"
				ref={ ref }
				type="text"
				onChange={ handleChange }
				onFocus={ () => setIsFocused( true ) }
				onBlur={ () => setIsFocused( false ) }
				value={ formattedAmount }
			/>
			<div className={ backdropCSSClasses }></div>
		</div>
	);
}

function wpoFormatDecimalString( number, storeCurrency ) {
	if ( number === '-' ) {
		return '-';
	}

	if ( typeof number !== 'number' ) {
		number = parseFloat( number );
	}

	if ( Number.isNaN( number ) ) {
		return '';
	}

	const currencyConfig = storeCurrency.getCurrencyConfig();

	return storeCurrency.formatAmount( number ).replace( currencyConfig?.symbol, '' );
}

export default WCPriceInput;
