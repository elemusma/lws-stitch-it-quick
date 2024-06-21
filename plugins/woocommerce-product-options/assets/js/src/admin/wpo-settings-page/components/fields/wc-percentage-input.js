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
function WCPercentageInput( { required = false, storeCurrency, value, max = false, onChange = () => {} } ) {
	const currencyConfig = storeCurrency.getCurrencyConfig();
	const formattedAmount = wpoFormatDecimalString( value, { ...currencyConfig, max } );
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
		setCursor( event.target.selectionStart );
		onChange( event );
	};

	return (
		<div className="barn2-prefixed-input-container">
			<span className="barn2-input-prefix">{ '%' }</span>
			<input
				required={ required }
				className="barn2-prefixed-input barn2-percentage-input"
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

function wpoFormatDecimalString( number, currencyConfig ) {
	if ( typeof number !== 'number' ) {
		number = parseFloat( number );

		if ( currencyConfig.max ) {
			number = Math.min( number, currencyConfig.max );
		}
	}

	if ( Number.isNaN( number ) ) {
		return '';
	}

	return number.toFixed( currencyConfig.precision ).replace( '.', currencyConfig.decimalSeparator );
}

export default WCPercentageInput;
