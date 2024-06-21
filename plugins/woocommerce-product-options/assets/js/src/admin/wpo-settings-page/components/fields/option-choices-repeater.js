/**
 * WordPress dependencies.
 */
import { useState, useEffect, useMemo } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { FormToggle, Dashicon } from '@wordpress/components';

/**
 * WooCommerce dependencies.
 */
import CurrencyFactory from '@woocommerce/currency';

/**
 * External dependencies.
 */
import { Button, Popover } from '@barn2plugins/components';
import { nanoid } from 'nanoid';
import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd';
import { usePreviousDifferent } from 'rooks';
import classnames from 'classnames';

/**
 * Internal dependencies.
 */
import WCPriceInput from './wc-price-input';
import WCPercentageInput from './wc-percentage-input';
import WCTableTooltip from '../wc-table-tooltip';
import ColorSwatchButton from '../color-swatch-button';
import ImageButton from '../image-button';
import { toNumber } from '../../util';

const OptionChoicesRepeater = ( { optionType, singleChoice = false, maxQty, value, onChange = () => {} } ) => {
	const storeCurrency = useMemo( () => CurrencyFactory( wpoSettings.currency ), [ wpoSettings.currency ] );
	const previousType = usePreviousDifferent( optionType );

	const containerClasses = classnames( 'option-setting-repeater wpo-choices-repeater', {
		'wpo-choices-is-customer-price': optionType === 'customer_price',
	} );

	/**
	 * Wholesale Pricing.
	 */
	const hasWholesaleRoles = wpoSettings?.isWholesaleProActive;

	const defaultChoice = {
		id: nanoid(),
		label: '',
		price_type: 'no_cost',
		pricing: false,
		selected: false,
		color: '#000000',
		media: null,
		wholesale: {},
	};

	const pricingComponents = {
		no_cost: {
			component: InputDisabled,
		},
		flat_fee: {
			component: WCPriceInput,
			props: {
				storeCurrency,
			},
		},
		quantity_based: {
			component: WCPriceInput,
			props: {
				storeCurrency,
			},
		},
		percentage_inc: {
			component: WCPercentageInput,
			props: {
				storeCurrency,
			},
		},
		percentage_dec: {
			component: WCPercentageInput,
			props: {
				storeCurrency,
				max: 100,
			},
		},
		char_count: {
			component: WCPriceInput,
			props: {
				storeCurrency,
			},
		},
	};

	const [ choices, setChoices ] = useState( [ defaultChoice ] );

	/**
	 * Adds a new empty choice to the list.
	 *
	 * @param {Event} event
	 */
	const addChoice = ( event ) => {
		event.preventDefault();

		setChoices( ( prevChoices ) => [ ...prevChoices, defaultChoice ] );
	};

	/**
	 * Removes a choice from the list.
	 *
	 * @param {string} choiceId
	 */
	const removeChoice = ( choiceId ) => {
		const changedChoices = choices.filter( ( choice ) => choice.id !== choiceId );

		if ( changedChoices.length === 0 ) {
			changedChoices.push( defaultChoice );
		}

		setChoices( changedChoices );
		onChange( changedChoices );
	};

	/**
	 * Removes a choice from the list.
	 *
	 * @param {string} choiceId
	 */
	const cloneChoice = ( choiceId ) => {
		const clonedIndex = choices.findIndex( ( choice ) => choice.id === choiceId );

		if ( clonedIndex >= 0 ) {
			const match = choices[ clonedIndex ]?.label?.match( / \(copy( *)(\d*)\)/ );
			let copyNumber = '';
			let label = choices[ clonedIndex ].label;

			if ( match ) {
				label = label.replace( match[ 0 ], '' );

				if ( match[ 2 ] ) {
					copyNumber = ` ${ parseInt( match[ 2 ] ) + 1 }`;
				} else {
					copyNumber = ' 2';
				}
			}

			const clonedChoice = {
				...choices[ clonedIndex ],
				id: nanoid(),
				label: label + sprintf( ' (%1$s%2$s)', __( 'copy', 'woocommerce-product-options' ), copyNumber ),
				selected: false,
			};

			const changedChoices = [ ...choices ]
				.splice( 0, clonedIndex + 1 )
				.concat( clonedChoice, [ ...choices ].splice( clonedIndex + 1 ) );

			setChoices( changedChoices );
			onChange( changedChoices );
		}
	};

	/**
	 * Handle a value change for a choice.
	 *
	 * @param {any}    changeValue
	 * @param {string} key
	 * @param {string} choiceId
	 */
	const handleChoiceChange = ( changeValue, key, choiceId ) => {
		const newChoices = choices.map( ( choice ) => {
			if ( choice.id !== choiceId ) {
				return choice;
			}

			if ( key === 'price_type' && changeValue === 'no_cost' ) {
				return { ...choice, ...{ [ key ]: changeValue, pricing: false } };
			}

			if ( [ 'pricing', 'wholesale' ].includes( key ) ) {
				return { ...choice, [ key ]: toNumber( changeValue ) };
			}

			return { ...choice, [ key ]: changeValue };
		} );

		setChoices( newChoices );
		onChange( newChoices );
	};

	const numberToString = ( numberValue ) => {
		if ( Object.entries( numberValue )?.[0]?.[0].indexOf( 'wcwp_') === 0 ) {
			// this is a wholesale role pricing object
			const wwpPricing = Object.entries( numberValue );
			const formattedWwpPricing = wwpPricing.map( ( [ key, value ] ) => {
				const stringNumber = storeCurrency.formatAmount( value ).replace( storeCurrency?.getCurrencyConfig()?.symbol, '' );
				return [ key, stringNumber ];
			} );

			return Object.fromEntries( formattedWwpPricing );
		}

		return storeCurrency.formatDecimalString( numberValue );
	};

	/**
	 * Handles the selected toggle.
	 *
	 * Checkboxes can have multiple toggles active.
	 *
	 * @param {boolean} selected
	 * @param {string}  choiceId
	 */
	const handleSelectedChange = ( selected, choiceId ) => {
		const newChoices = choices.map( ( choice ) => ( choice.id === choiceId ? { ...choice, selected } : choice ) );

		if (
			( ! [ 'checkbox', 'images', 'text_labels' ].includes( optionType ) && selected ) ||
			( [ 'checkbox', 'images', 'text_labels' ].includes( optionType ) && parseInt( maxQty ) === 1 && selected )
		) {
			newChoices.forEach( ( choice ) => {
				if ( choice.id !== choiceId ) {
					choice.selected = false;
				}
			} );
		}

		setChoices( newChoices );
		onChange( newChoices );
	};
	/**
	 * Handles the drag and drop of choices.
	 *
	 * @param {Array} result
	 */
	const onDragEnd = ( result ) => {
		// dropped outside the list
		if ( ! result.destination ) {
			return;
		}

		const reorderedChoices = choices;
		const [ moved ] = reorderedChoices.splice( result.source.index, 1 );

		reorderedChoices.splice( result.destination.index, 0, moved );

		setChoices( reorderedChoices );
	};

	/**
	 * Keep only the first choice if the type is a single choice field.
	 */
	useEffect( () => {
		if ( ! value ) {
			setChoices( [ defaultChoice ] );
		}

		if ( value.length > 0 ) {
			setChoices( singleChoice ? [ value[ 0 ] ] : value );
		}
	}, [ value, singleChoice ] );

	/**
	 * Remove char_count pricing if the option type does not support it
	 */
	useEffect( () => {
		if ( [ 'text', 'text_area' ].includes( previousType ) && choices[ 0 ].price_type === 'char_count' ) {
			setChoices( [ { ...choices[ 0 ], ...{ price_type: 'no_cost', pricing: false } } ] );
		}
	}, [ previousType ] );

	/**
	 * If the maxQty is 1, disable the selected toggle on choices after the first one.
	 */
	useEffect( () => {
		const selectedChoices = choices.filter( ( choice ) => choice.selected );

		if ( parseInt( maxQty ) === 1 && selectedChoices.length > 1 ) {
			const newChoices = choices.map( ( choice ) => {
				if ( choice.selected && choice.id !== selectedChoices[ 0 ].id ) {
					return { ...choice, selected: false };
				}

				return choice;
			} );

			setChoices( newChoices );
			onChange( newChoices );
		}
	}, [ maxQty ] );

	return (
		<>
			<table className={ containerClasses }>
				<thead className="choice-headers">
					<tr>
						{ ! singleChoice && (
							<th className="option-setting-repeater-draggable-col" colSpan={ 1 }>
								{ ' ' }
								<WCTableTooltip
									tooltip={ __( 'Drag to reorder', 'woocommerce-product-options' ) }
								/>{ ' ' }
							</th>
						) }
						<th colSpan={ 1 }>
							{ __( 'Label', 'woocommerce-product-options' ) }
							<WCTableTooltip
								tooltip={ __(
									'The label that appears alongside the option.',
									'woocommerce-product-options'
								) }
							/>
						</th>
						{ optionType === 'images' && (
							<th className={ 'option-choices-repeater-image-col' } colSpan={ 1 }>
								{ __( 'Images', 'woocommerce-product-options' ) }
							</th>
						) }
						{ optionType === 'color_swatches' && (
							<th className={ 'option-choices-repeater-color-col' } colSpan={ 1 }>
								{ __( 'Color', 'woocommerce-product-options' ) }
							</th>
						) }

						{ optionType !== 'customer_price' && (
							<>
								<th colSpan={ 1 }>{ __( 'Price Type', 'woocommerce-product-options' ) }</th>
								<th colSpan={ 1 }>{ __( 'Pricing', 'woocommerce-product-options' ) }</th>
							</>
						) }

						{ hasWholesaleRoles && (
							<th className={ 'option-choices-repeater-wholesale-col' } colSpan={ 1 }>
								{ __( 'Wholesale', 'woocommerce-product-options' ) }
							</th>
						) }

						{ ! singleChoice && (
							<>
								<th className={ 'option-choices-repeater-selected-col' } colSpan={ 1 }>
									{ __( 'Selected', 'woocommerce-product-options' ) }
									<WCTableTooltip
										tooltip={ __(
											'If you set a default option then this will be pre-selected on the product page.'
										) }
									/>
								</th>
								<th className={ 'option-setting-repeater-remove-col' } colSpan={ 1 }></th>
							</>
						) }
						{ ! singleChoice && <th className={ 'option-setting-repeater-clone-col' } colSpan={ 1 }></th> }
					</tr>
				</thead>
				<DragDropContext onDragEnd={ onDragEnd }>
					<Droppable droppableId="droppable">
						{ ( droppableProvided, droppableSnapshot ) => (
							<tbody
								{ ...droppableProvided.droppableProps }
								ref={ droppableProvided.innerRef }
								style={ getListStyle( droppableSnapshot.isDraggingOver ) }
							>
								{ choices.map( ( choice, index ) => {
									const PricingComponent = pricingComponents[ choice.price_type ].component;
									const pricingComponentProps = pricingComponents[ choice.price_type ].props;

									const wholesalePopoverClass = classnames( 'wpo-wholesale-popover-icon', {
										'is-empty':
											! choice?.wholesale ||
											Object.values( choice.wholesale ).every( ( x ) => x === null || x === '' ),
									} );

									return (
										<Draggable key={ choice.id } draggableId={ `${ choice.id }` } index={ index }>
											{ ( draggableProvided, draggableSnapshot ) => (
												<tr
													ref={ draggableProvided.innerRef }
													{ ...draggableProvided.draggableProps }
													className="wpo-choice"
													style={ getItemStyle(
														draggableSnapshot.isDragging,
														draggableProvided.draggableProps.style
													) }
												>
													{ ! singleChoice && (
														<td
															{ ...draggableProvided.dragHandleProps }
															className="drag-handle-wrap"
														>
															<Dashicon icon={ 'menu' } />
														</td>
													) }

													<td colSpan={ 1 } className="label-wrap">
														<input
															required
															type="text"
															value={ choice?.label ?? defaultChoice.label }
															onChange={ ( event ) =>
																handleChoiceChange(
																	event.target.value,
																	'label',
																	choice.id
																)
															}
														/>
													</td>

													{ optionType === 'images' && (
														<td colSpan={ 1 } className="image-wrap">
															<ImageButton
																onChange={ ( imageId ) =>
																	handleChoiceChange( imageId, 'media', choice.id )
																}
																imageId={ choice?.media ?? null }
															/>
														</td>
													) }

													{ optionType === 'color_swatches' && (
														<td colSpan={ 1 } className="color-wrap">
															<ColorSwatchButton
																onChange={ ( color ) =>
																	handleChoiceChange( color, 'color', choice.id )
																}
																color={ choice?.color ?? '#000000' }
															/>
														</td>
													) }

													{ optionType !== 'customer_price' && (
														<td colSpan={ 1 } className="price_type-wrap">
															<select
																value={ choice?.price_type ?? defaultChoice.price_type }
																onChange={ ( event ) =>
																	handleChoiceChange(
																		event.target.value,
																		'price_type',
																		choice.id
																	)
																}
															>
																<option value={ 'no_cost' }>
																	{ __( 'No cost', 'woocommerce-product-options' ) }
																</option>
																<option value={ 'flat_fee' }>
																	{ __( 'Flat fee', 'woocommerce-product-options' ) }
																</option>
																<option value={ 'quantity_based' }>
																	{ __(
																		'Quantity-based fee',
																		'woocommerce-product-options'
																	) }
																</option>
																<option value={ 'percentage_inc' }>
																	{ __(
																		'Percentage increase',
																		'woocommerce-product-options'
																	) }
																</option>
																<option value={ 'percentage_dec' }>
																	{ __(
																		'Percentage decrease',
																		'woocommerce-product-options'
																	) }
																</option>
																{ [ 'text', 'textarea' ].includes( optionType ) && (
																	<option value={ 'char_count' }>
																		{ __(
																			'Character count',
																			'woocommerce-product-options'
																		) }
																	</option>
																) }
															</select>
														</td>
													) }

													{ optionType !== 'customer_price' && (
														<td colSpan={ 1 } className="pricing-wrap">
															<PricingComponent
																{ ...pricingComponentProps }
																onChange={ ( event ) =>
																	handleChoiceChange(
																		event.target.value,
																		'pricing',
																		choice.id
																	)
																}
																value={ choice?.pricing ?? defaultChoice.pricing }
															/>
														</td>
													) }

													{ optionType !== 'customer_price' && hasWholesaleRoles && (
														<td colSpan={ 1 } className="wholesale-wrap">
															{ ! [ 'char_count', 'no_cost' ].includes(
																choice.price_type
															) && (
																<Popover
																	content={
																		<WholesaleRolePricing
																			choice={ choice }
																			value={ choice?.wholesale ??
																				defaultChoice.wholesale
																			}
																			onChange={ ( wholesalePricing ) => {
																				handleChoiceChange(
																					wholesalePricing,
																					'wholesale',
																					choice.id
																				);
																			} }
																		/>
																	}
																>
																	<div
																		className={ wholesalePopoverClass }
																		style={ { display: 'inline' } }
																	>
																		<svg
																			xmlns="http://www.w3.org/2000/svg"
																			viewBox="0 0 512 512"
																			width="24"
																			height="24"
																			fill="#e2e4e7"
																		>
																			<path d="M471 261.4 260.9 49.8l-1.5-1.5h-.4c-8.3-7.9-17.9-12-29.9-12.3l-99.7-3.7-4.4-.2c-11.2.2-22.2 4.5-30.7 13.1L45.1 94.3c-9 9-13.1 20.9-13.1 32.7v.1l.3 4.2 6.7 97.3v2.1c1 8.7 4.5 17.3 10.4 24.4l5.5 5.4 206.3 208.8 3.1 3.1c11.9 10.5 30 10 41.3-1.4L471 304.4c11.8-11.8 12-31.1 0-43zM144 192c-26.5 0-48-21.5-48-48s21.5-48 48-48 48 21.5 48 48-21.5 48-48 48z" />
																		</svg>
																	</div>
																</Popover>
															) }
														</td>
													) }

													{ ! singleChoice && (
														<>
															<td colSpan={ 1 } className="selected-wrap">
																<FormToggle
																	checked={
																		choice?.selected ?? defaultChoice.selected
																	}
																	onChange={ () => {
																		handleSelectedChange(
																			! choice.selected,
																			choice.id
																		);
																	} }
																/>
															</td>
															<td colSpan={ 1 } className="trash-wrap">
																<Button
																	className="wpo-option-setting-repeater-remove"
																	disabled={ false }
																	title={ __(
																		'Remove this choice',
																		'woocommerce-product-options'
																	) }
																	onClick={ () => removeChoice( choice.id ) }
																>
																	<svg
																		xmlns="http://www.w3.org/2000/svg"
																		viewBox="-2 -2 24 24"
																		width="24"
																		height="24"
																		aria-hidden="true"
																		focusable="false"
																		style={ { fill: 'currentColor' } }
																	>
																		<path d="M4 9h12v2H4V9z"></path>
																	</svg>
																</Button>
															</td>
														</>
													) }

													{ ! singleChoice && (
														<td colSpan={ 1 } className="clone-wrap">
															<Button
																className="wpo-option-setting-repeater-clone"
																disabled={ false }
																title={ __(
																	'Duplicate this choice',
																	'woocommerce-product-options'
																) }
																onClick={ () => cloneChoice( choice.id ) }
															>
																<svg
																	xmlns="http://www.w3.org/2000/svg"
																	viewBox="-10 -20 130 160"
																	width="24"
																	height="24"
																	aria-hidden="true"
																	focusable="false"
																	style={ { fill: 'currentColor' } }
																>
																	<path d="M89.62,13.96v7.73h12.19h0.01v0.02c3.85,0.01,7.34,1.57,9.86,4.1c2.5,2.51,4.06,5.98,4.07,9.82h0.02v0.02 v73.27v0.01h-0.02c-0.01,3.84-1.57,7.33-4.1,9.86c-2.51,2.5-5.98,4.06-9.82,4.07v0.02h-0.02h-61.7H40.1v-0.02 c-3.84-0.01-7.34-1.57-9.86-4.1c-2.5-2.51-4.06-5.98-4.07-9.82h-0.02v-0.02V92.51H13.96h-0.01v-0.02c-3.84-0.01-7.34-1.57-9.86-4.1 c-2.5-2.51-4.06-5.98-4.07-9.82H0v-0.02V13.96v-0.01h0.02c0.01-3.85,1.58-7.34,4.1-9.86c2.51-2.5,5.98-4.06,9.82-4.07V0h0.02h61.7 h0.01v0.02c3.85,0.01,7.34,1.57,9.86,4.1c2.5,2.51,4.06,5.98,4.07,9.82h0.02V13.96L89.62,13.96z M79.04,21.69v-7.73v-0.02h0.02 c0-0.91-0.39-1.75-1.01-2.37c-0.61-0.61-1.46-1-2.37-1v0.02h-0.01h-61.7h-0.02v-0.02c-0.91,0-1.75,0.39-2.37,1.01 c-0.61,0.61-1,1.46-1,2.37h0.02v0.01v64.59v0.02h-0.02c0,0.91,0.39,1.75,1.01,2.37c0.61,0.61,1.46,1,2.37,1v-0.02h0.01h12.19V35.65 v-0.01h0.02c0.01-3.85,1.58-7.34,4.1-9.86c2.51-2.5,5.98-4.06,9.82-4.07v-0.02h0.02H79.04L79.04,21.69z M105.18,108.92V35.65v-0.02 h0.02c0-0.91-0.39-1.75-1.01-2.37c-0.61-0.61-1.46-1-2.37-1v0.02h-0.01h-61.7h-0.02v-0.02c-0.91,0-1.75,0.39-2.37,1.01 c-0.61,0.61-1,1.46-1,2.37h0.02v0.01v73.27v0.02h-0.02c0,0.91,0.39,1.75,1.01,2.37c0.61,0.61,1.46,1,2.37,1v-0.02h0.01h61.7h0.02 v0.02c0.91,0,1.75-0.39,2.37-1.01c0.61-0.61,1-1.46,1-2.37h-0.02V108.92L105.18,108.92z"></path>
																</svg>
															</Button>
														</td>
													) }
												</tr>
											) }
										</Draggable>
									);
								} ) }
								{ droppableProvided.placeholder }
							</tbody>
						) }
					</Droppable>
				</DragDropContext>
			</table>

			{ ! singleChoice && (
				<Button
					className="wpo-option-setting-repeater-add"
					onClick={ addChoice }
					disabled={ false }
					title={ __(
						'Add a choice',
						'woocommerce-product-options'
					) }
				>
					<svg
						xmlns="http://www.w3.org/2000/svg"
						viewBox="0 0 24 24"
						width="24"
						height="24"
						aria-hidden="true"
						focusable="false"
						style={ { fill: 'currentColor' } }
					>
						<path d="M18 11.2h-5.2V6h-1.6v5.2H6v1.6h5.2V18h1.6v-5.2H18z"></path>
					</svg>
				</Button>
			) }
		</>
	);
};

const InputPercentage = ( props ) => {
	const [ isFocused, setIsFocused ] = useState( false );

	const backdropCSSClasses = classnames( 'barn2-prefixed-input-backdrop', {
		'barn2-input-focused': isFocused,
	} );

	const wpoFormatDecimalString = ( number, currencyConfig ) => {
		if ( typeof number !== 'number' ) {
			if ( typeof number === 'string' ) {
				if ( currencyConfig?.thousandSeparator?.length > 0 ) {
					const thousandRegExp = new RegExp( `\\${ currencyConfig?.thousandSeparator }`, 'gi' );
					number = number.replace( thousandRegExp, '' );
				}
				if ( currencyConfig?.decimalSeparator?.length > 0 ) {
					const decimalRegExp = new RegExp( `\\${ currencyConfig?.decimalSeparator }`, 'gi' );
					number = number.replace( decimalRegExp, '.' );
				}
			}

			number = parseFloat( number );
		}

		if ( Number.isNaN( number ) ) {
			return '';
		}

		return number.toFixed( currencyConfig.precision ).replace( '.', currencyConfig.decimalSeparator );
	};

	const formattedAmount = wpoFormatDecimalString( props.value, wpoSettings.currency );

	return (
		<div className="barn2-prefixed-input-container">
			<span className="barn2-input-prefix">{ '%' }</span>
			<input
				className="barn2-prefixed-input barn2-percentage-input"
				type="number"
				onFocus={ () => setIsFocused( true ) }
				onBlur={ () => setIsFocused( false ) }
				value={ formattedAmount }
				{ ...props }
			/>
			<div className={ backdropCSSClasses }></div>
		</div>
	);
};

const InputDisabled = () => <input type="text" disabled />;

const WholesaleRolePricing = ( { choice, value, onChange = () => {} } ) => {
	const storeCurrency = useMemo( () => CurrencyFactory( wpoSettings.currency ), [ wpoSettings.currency ] );

	const pricingComponents = {
		flat_fee: {
			component: WCPriceInput,
			props: {
				storeCurrency,
			},
		},
		quantity_based: {
			component: WCPriceInput,
			props: {
				storeCurrency,
			},
		},
		percentage_inc: {
			component: WCPercentageInput,
			props: {
				storeCurrency,
			},
		},
		percentage_dec: {
			component: WCPercentageInput,
			props: {
				storeCurrency: {
					...storeCurrency,
					max: 100,
				},
			},
		},
		char_count: {
			component: WCPriceInput,
			props: {
				storeCurrency,
			},
		},
	};

	const wholesaleRoles = Object.values( wpoSettings.wholesaleRoles );
	const [ wholesalePricing, setWholesalePricing ] = useState( value ?? {} );

	const PricingComponent = pricingComponents[ choice.price_type ].component;
	const pricingComponentProps = pricingComponents[ choice.price_type ].props;

	const infoMessage = useMemo( () => {
		switch ( choice.price_type ) {
			case 'flat_fee':
			case 'quantity_based':
				return __( 'Set specific pricing for your wholesale roles.', 'woocommerce-product-options' );
			case 'percentage_inc':
			case 'percentage_dec':
				return __( 'Set specific percentages for your wholesale roles.', 'woocommerce-product-options' );
			default:
				return null;
		}
	}, [ choice ] );

	const renderWholesalePricingInputs = () => {
		return (
			<div className="wpo-wholesale-pricing">
				{ wholesaleRoles.map( ( role ) => {
					return (
						<div className="wpo-wholesale-pricing-role" key={ `wpo-${ role.name }` }>
							<label htmlFor={ role.name }>
								<strong>{ role.label }</strong>
							</label>
							<PricingComponent
								{ ...pricingComponentProps }
								onChange={ ( event ) => handleWholeSalePricingChange( role.name, event.target.value ) }
								value={ wholesalePricing?.[ role.name ] ?? '' }
							/>
						</div>
					);
				} ) }
			</div>
		);
	};

	const handleWholeSalePricingChange = ( role, amount ) => {
		setWholesalePricing( { ...wholesalePricing, [ role ]: toNumber( amount ) } );
	};

	useEffect( () => {
		onChange( wholesalePricing );
	}, [ wholesalePricing ] );

	return (
		<>
			<span>{ infoMessage }</span>
			{ renderWholesalePricingInputs() }
		</>
	);
};

const getItemStyle = ( isDragging, draggableStyle ) => ( {
	// display: 'flex',
	userSelect: 'none',
	padding: '8px 0',
	margin: `0 0 8px 0`,

	...draggableStyle,
} );

const getListStyle = ( isDraggingOver ) => ( {
	// borderColor: isDraggingOver ? '#2271b1' : '#e2e4e7',
	padding: '8px',
	// width: 250,
} );

export default OptionChoicesRepeater;
