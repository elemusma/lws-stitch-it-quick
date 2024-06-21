/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';

// eslint-disable-next-line import/no-extraneous-dependencies
import { transliterate } from 'transliteration';

/**
 * External dependencies.
 */
import Formula from 'fparser';
import { Button } from '@barn2plugins/components';

const PriceFormula = ( { formMethods, onChange = () => {}, value } ) => {
	const [ validationError, setValidationError ] = useState( value?.validationError || false );
	const [ formula, setFormula ] = useState( value?.formula || '' );
	const [ expression, setExpression ] = useState( value?.expression || '' );
	const [ formulaVariables, setFormulaVariables ] = useState( value?.variables || [] );

	/**
	 * Track the cursor position.
	 */
	const [ cursor, setCursor ] = useState( null );

	/**
	 * Track the selection range.
	 */
	const [ selection, setSelection ] = useState( null );

	const watchOptionsField = formMethods.watch( 'options' );

	const hasDirtyNumberFields = watchOptionsField?.some( ( option ) => {
		return option.type === 'number' && option.id === 0;
	} );

	/**
	 * Insert a variable or operator into the formula.
	 *
	 * @param {string} variable
	 */
	const insertVariable = ( variable ) => {
		if ( cursor !== null ) {
			const formulaParts = formula.split( '' );

			formulaParts.splice( cursor, 0, `${ variable }` );

			setFormula( formulaParts.join( '' ) );
			setCursor( cursor + variable.length );
		} else if ( selection !== null ) {
			const formulaParts = formula.split( '' );

			// remove the selected text and replace with variable
			formulaParts.splice( selection.start, selection.end - selection.start, `${ variable }` );

			setFormula( formulaParts.join( '' ) );
			setCursor( selection.start + variable.length );
		} else {
			setFormula( `${ formula }${ variable }` );
		}
	};

	/**
	 * Validate the formula and save any variables used.
	 */
	const validateFormula = () => {
		try {
			if ( formula.length === 0 ) {
				setValidationError( false );
				setFormulaVariables( [] );
				setExpression( '' );
				return;
			}

			const extractedVariables = extractVariables( formula );
			const availableVariables = extractedVariables.map( ( variable ) => {
				return {
					option: variable.replace( /[\[\]]/g, '' ),
					variableName: transliterate( variable ),
				};
			} );
			const transliteratedFormula = transliterate( formula );
			const parser = new Formula( transliteratedFormula );
			const variables = parser.getVariables();

			if ( variables.length > 0 && variables.length === availableVariables.length ) {
				// retrieve the value references for each variable
				const formattedVariables = variables.map( ( variable ) => {
					if ( Number.isInteger( parseInt( variable.split( '' )[ 0 ] ) ) ) {
						throw new Error(
							sprintf(
								// translators: %s is the variable name
								__(
									'Variable names cannot start with a digit: please change the name of option [%s].',
									'woocommerce-product-options'
								),
								variable
							)
						);
					}

					const numberVariable = watchOptionsField.find( ( option ) => {
						return (
							transliterate( option.name.replace( /[\s]+/g, '' ) ).toLowerCase() ===
								variable.toLowerCase() && option.type === 'number'
						);
					} );

					if ( numberVariable ) {
						return {
							name: variable,
							id: numberVariable.id,
							type: 'number_option',
						};
					}

					const productVariable = productVariables.find( ( productVar ) => productVar.id === variable );

					if ( productVariable ) {
						return {
							name: variable,
							id: productVariable.id,
							type: 'product',
						};
					}

					if ( ! numberVariable || ! productVariable ) {
						const anyVariable = watchOptionsField.find( ( option ) => {
							return (
								transliterate( option.name.replace( /[\s]+/g, '' ) ).toLowerCase() ===
								variable.toLowerCase()
							);
						} );

						if ( anyVariable ) {
							throw new Error(
								sprintf(
									// translators: %s is the variable name
									__(
										'Variable [%s] does not correspond to a Number option.',
										'woocommerce-product-options'
									),
									variable
								)
							);
						} else {
							throw new Error(
								sprintf(
									// translators: %s is the variable name
									__(
										'Variable [%s] does not correspond to any option in this group.',
										'woocommerce-product-options'
									),
									variable
								)
							);
						}
					}
				} );

				setValidationError( false );
				setFormulaVariables( formattedVariables );
				setExpression( parser.getExpressionString() );
			}
		} catch ( error ) {
			if ( error?.message === 'Could not parse formula: Syntax error.' ) {
				error.message = __(
					'The formula is not complete or contains a syntax error.',
					'woocommerce-product-options'
				);
			}
			setValidationError( error?.message || __( 'Invalid formula', 'woocommerce-product-options' ) );
			setFormulaVariables( [] );
			setExpression( '' );
		}
	};

	const extractVariables = ( formula ) => {
		// matches variables in user formula including [ and ]
		const regExp = /\[([^\]]+)\]/g;
		const matches = formula.match( regExp );

		// remove duplicate variables
		const uniqueMatches = matches.filter( ( match, index ) => matches.indexOf( match ) === index );

		return uniqueMatches;
	};

	/**
	 * Handles the formula input change.
	 *
	 * @param {Event} event
	 */
	const onTextareaChange = ( event ) => {
		setCursor( event.target.selectionStart );
		setFormula( event.target.value );
	};

	/**
	 * Renders the available options as buttons.
	 *
	 * @return {JSX.Element} The buttons.
	 */
	const renderAvailableOptions = () => {
		const numberOptions = watchOptionsField.filter( ( option ) => option.type === 'number' && option.id !== 0 );

		const numberButtons = numberOptions.map( ( option ) => {
			return (
				<Button key={ option.id } value={ option.id } onClick={ () => insertVariable( `[${ option.name }]` ) }>
					{ option.name }
				</Button>
			);
		} );

		const productButtons = productVariables.map( ( option ) => {
			return (
				<Button key={ option.id } value={ option.id } onClick={ () => insertVariable( `[${ option.id }]` ) }>
					{ option.name }
				</Button>
			);
		} );

		return [ ...productButtons, ...numberButtons ];
	};

	/**
	 * Renders the available operators as buttons.
	 *
	 * @return {JSX.Element} The operators.
	 */
	const renderAvailableOperators = () => {
		return availableOperators.map( ( operator ) => (
			<Button
				key={ operator.value }
				value={ operator.value }
				onClick={ () => insertVariable( ` ${ operator.value } ` ) }
			>
				{ operator.label }
			</Button>
		) );
	};
	/**
	 * Track the selection start and end.
	 *
	 * @param {Event} event
	 */
	const handleSelection = ( event ) => {
		const { selectionStart, selectionEnd } = event.target;
		setCursor( selectionStart === selectionEnd ? selectionStart : null );
		setSelection( selectionStart !== selectionEnd ? { start: selectionStart, end: selectionEnd } : null );
	};

	useEffect( () => {
		if ( formula.length > 0 && validationError !== false ) {
			validateFormula();
		}
	}, [] );

	/**
	 * Validate the formula when the formula changes.
	 */
	useEffect( () => {
		validateFormula();
	}, [ formula ] );

	/**
	 * Trigger the onChange callback when the formula properties change.
	 */
	useEffect( () => {
		onChange( { formula, expression, validationError, variables: formulaVariables } );
	}, [ formula, validationError, expression, formulaVariables ] );

	return (
		<div className="wpo-price-formula">
			<div className="wpo-price-formula-textarea-wrap">
				<textarea
					name="wpo_price_formula"
					value={ formula }
					placeholder={ '([field1] * [field2]) - [product_price]' }
					onChange={ onTextareaChange }
					onSelect={ handleSelection }
					onKeyUp={ validateFormula }
					onBlur={ validateFormula }
				/>
				{ validationError !== false && <p className="wpo-price-formula-error">{ validationError }</p> }
			</div>

			<div className="wpo-price-formula-controls">
				{ hasDirtyNumberFields && (
					<p className="wpo-clogic-unsaved-options-warning">
						{ __(
							'You need to save number options before using them in a price formula. Click the "Save changes" button below and then continue.'
						) }
					</p>
				) }
				<div className="wpo-price-formula-available-options">{ renderAvailableOptions() }</div>
				<div className="wpo-price-formula-operators">{ renderAvailableOperators() }</div>
			</div>
		</div>
	);
};

const productVariables = [
	{
		name: __( 'Product price', 'woocommerce-product-options' ),
		id: 'product_price',
		type: 'product',
	},
	// {
	// 	name: __( 'Product quantity', 'woocommerce-product-options' ),
	// 	id: 'product_quantity',
	// 	type: 'product',
	// },
];

const availableOperators = [
	{
		label: __( '+', 'woocommerce-product-options' ),
		value: '+',
	},
	{
		label: __( '-', 'woocommerce-product-options' ),
		value: '-',
	},
	{
		label: __( '*', 'woocommerce-product-options' ),
		value: '*',
	},
	{
		label: __( '/', 'woocommerce-product-options' ),
		value: '/',
	},
	{
		label: __( '(', 'woocommerce-product-options' ),
		value: '(',
	},
	{
		label: __( ')', 'woocommerce-product-options' ),
		value: ')',
	},
];

export default PriceFormula;
