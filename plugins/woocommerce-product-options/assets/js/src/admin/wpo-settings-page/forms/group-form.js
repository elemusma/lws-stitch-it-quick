/**
 * WordPress dependencies
 */
import { useState, useEffect, useMemo } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { Spinner, Dashicon } from '@wordpress/components';

/**
 * External dependencies
 */
import { ListTable, RadioControl, CheckboxControl, Button } from '@barn2plugins/components';
import { useForm, useFieldArray, FormProvider, Controller } from 'react-hook-form';
import { useMultipleAdminNotifications } from '@barn2plugins/react-helpers';

/**
 * Internal dependencies
 */
import ProductSelect from '../components/fields/product-select';
import CategorySelect from '../components/fields/category-select';
import FormRow from '../components/tables/form-row';
import OptionForm from '../forms/option-form';
import OptionNameCell from '../components/tables/option-name-cell';
import TypeCell from '../components/tables/type-cell';
import DeleteModal from '../components/tables/delete-modal';
import WCTableTooltip from '../components/wc-table-tooltip';
import { useCreateGroup, useUpdateGroup, useDuplicateGroup } from '../hooks/groups';
import { useGroupOptions } from '../hooks/options';
import { useNavigate } from 'react-router-dom';

/**
 * Handles the display of the group editor form.
 *
 * @param {Object}  props
 * @param {boolean} props.editMode
 * @param {Object}  props.group
 */
const GroupForm = ( { editMode, group } ) => {
	const { setNotification, clearNotifications, clearNotification } = useMultipleAdminNotifications();
	const updateGroup = useUpdateGroup();
	const createGroup = useCreateGroup();
	const duplicateGroup = useDuplicateGroup();
	const [ editRow, setEditRow ] = useState( null );
	const [ editOpen, setEditOpen ] = useState( false );
	const [ deleteRow, setDeleteRow ] = useState( {} );
	const [ duplicating, setDuplicating ] = useState( false );

	const navigate = useNavigate();

	const defaultValues = editMode
		? group
		: {
				id: 0,
				menu_order: 0,
				display_name: 0,
				visibility: 'global',
				options: [],
		  };

	const formMethods = useForm( { defaultValues } );
	const visibilityInput = formMethods.watch( 'visibility' );

	const groupOptionsQuery = useGroupOptions( defaultValues.id );

	const confirmDeleteMessage = deleteRow?.name
		? sprintf(
				/* translators: %s: Option group name */
				__( 'Are you sure you want to delete the "%s" option?', 'woocommerce-product-options' ),
				deleteRow?.name
		  )
		: __( 'Are you sure you want to delete this option?', 'woocommerce-product-options' );

	const optionsFieldArray = useFieldArray( {
		control: formMethods.control,
		name: 'options',
	} );

	const watchOptionsField = formMethods.watch( 'options' );

	useEffect( () => {
		if ( editMode === 'duplicate' && ! duplicating && formMethods.getValues( 'id' ) !== 0 ) {
			async function getDuplicateGroup() {
				setDuplicating( true );
				const result = await duplicateGroup.mutateAsync( group );

				if ( result?.group_id && result.group_id !== 0 ) {
					navigate( `/edit/${ result.group_id }` );
				}
				setDuplicating( false );
			}

			group.name += ' ' + __( 'Copy', 'woocommerce-product-options' );
			formMethods.setValue( 'id', 0 );
			formMethods.setValue( 'name', group.name );
			getDuplicateGroup();
		}
	}, [ editMode, group ] );

	useEffect( () => {
		if ( groupOptionsQuery.isFetched ) {
			optionsFieldArray.remove();

			formMethods.setValue( 'options', groupOptionsQuery.data );
		}
	}, [ groupOptionsQuery.data ] );

	useEffect( () => {
		if ( group ) {
			formMethods.reset( group );

			optionsFieldArray.remove();
			formMethods.setValue( 'options', groupOptionsQuery.data );
		}
	}, [ group ] );

	/**
	 * Columns for the table. Refetched every time the editRow changes.
	 *
	 * @return {Array}
	 */
	const columns = useMemo(
		() => [
			{
				Header: __( 'Name', 'woocommerce-product-options' ),
				accessor: 'name',
				Cell: ( table ) => (
					<OptionNameCell
						table={ table }
						onClick={ ( event, rowIndex ) => closeOrSwitchInlineEdit( rowIndex ) }
					/>
				),
			},
			{
				Header: __( 'Type', 'woocommerce-product-options' ),
				accessor: 'type',
				Cell: ( table ) => <TypeCell table={ table } />,
			},
		],
		[ editRow, groupOptionsQuery.data ]
	);

	/**
	 * Add a new option on button click.
	 *
	 * @param {Function} e
	 */
	const addOption = ( e ) => {
		e.preventDefault();

		optionsFieldArray.append( {
			id: 0,
			group_id: defaultValues.id,
			name: '',
			type: 'checkbox',
			display_name: 1,
			required: 0,
			choices: null,
			settings: null,
			conditional_logic: null,
		} );

		setEditRow( watchOptionsField?.length ?? 1 );
		setEditOpen( true );
	};

	/**
	 * Close or switch the inline edit state.
	 *
	 * @param {number} rowIndex
	 */
	const closeOrSwitchInlineEdit = ( rowIndex ) => {
		if ( ! editOpen ) {
			setEditRow( rowIndex );

			setEditOpen( true );
		} else if ( editRow === rowIndex ) {
			setEditRow( null );
			setEditOpen( false );
		} else {
			setEditRow( rowIndex );
		}
	};

	/**
	 * Duplicate an option.
	 *
	 * @param {number} rowIndex
	 */
	const duplicateOption = ( rowIndex ) => {
		const option = watchOptionsField[ rowIndex ];

		optionsFieldArray.insert( rowIndex + 1, {
			...option,
			...{
				id: 0,
				name: option.name + ' ' + __( 'Copy', 'woocommerce-product-options' ),
			},
		} );
	};

	/**
	 * Reorder the options in the table.
	 *
	 * @param {Array} reOrderedOptions
	 */
	const handleReOrder = ( reOrderedOptions ) => {
		setEditRow( null );
		setEditOpen( false );

		const updatedMenuOrder = reOrderedOptions.map( ( option, index ) => {
			return {
				...option,
				...{ menu_order: index },
			};
		} );

		formMethods.setValue( 'options', updatedMenuOrder );
	};

	/**
	 * Delete an option.
	 *
	 * @param {Object} row
	 */
	const handleDelete = async ( row ) => {
		optionsFieldArray.remove( row.rowIndex );
		setDeleteRow( {} );
	};

	/**
	 * Clean options for saving
	 *
	 * @param {Object} option
	 */
	const cleanOptionData = ( option ) => {
		// remove null values
		Object.keys( option ).forEach( ( key ) => {
			if ( option[ key ] === null ) {
				delete option[ key ];
			}
		} );

		let cleanData = option;

		if ( option?.conditional_logic ) {
			const cleanConditions = option.conditional_logic.conditions.filter( ( condition ) => {
				const selectedOption = watchOptionsField.find(
					( watchOption ) => watchOption.id === condition.optionID
				);
				return selectedOption && condition.optionID && condition.optionType && condition.operator;
			} );

			cleanData = {
				...cleanData,
				conditional_logic: { ...option.conditional_logic, conditions: cleanConditions },
			};
		}

		if ( option?.choices ) {
			// take only the first choice for certain types of options
			if ( [ 'text', 'textarea', 'file_upload', 'customer_price' ].includes( option.type ) ) {
				cleanData = { ...cleanData, choices: [ option.choices[ 0 ] ] };
			}

			// remove choices for the price formula field type
			if ( option.type === 'price_formula' ) {
				cleanData = { ...cleanData, choices: [] };
			}

			// remove color and media from non-relevant choices
			cleanData = {
				...cleanData,
				choices: option.choices.map( ( choice ) => {
					if ( option.type !== 'color_swatches' ) {
						delete choice.color;
					}

					if ( option.type !== 'images' ) {
						delete choice.media;
					}

					return choice;
				} ),
			};
		}

		return cleanData;
	};

	/**
	 * Update or create the group via ajax.
	 *
	 * @param {Object} data
	 */
	const onSubmit = async ( data ) => {
		formMethods.clearErrors();

		// remove null values
		Object.keys( data ).forEach( ( key ) => {
			if ( data[ key ] === null ) {
				delete data[ key ];
			}
		} );

		// remove product and cat if global
		if ( data.visibility === 'global' ) {
			data.products = [];
			data.categories = [];
		}

		// clean options
		if ( data?.options ) {
			data.options = data.options.map( ( option ) => cleanOptionData( option ) );
		}

		clearNotifications();

		if ( data ) {
			if ( data?.visibility === 'specific' && ! data?.products.length && ! data?.categories.length ) {
				setNotification(
					'error',
					__( 'Please select one or more products or categories.', 'woocommerce-product-options' ),
					false,
					true
				);

				setTimeout( () => {
					window.scrollTo( {
						top: 0,
						behavior: 'smooth',
					} );
				}, 1 );	

				return false;
			}
		}

		if ( editMode === 'edit' ) {
			updateGroup.mutate( data );
		} else {
			const result = await createGroup.mutateAsync( data );

			if ( result?.group_id && result.group_id !== 0 ) {
				navigate( `/edit/${ result.group_id }` );
			}
		}

		setTimeout( () => {
			window.scrollTo( {
				top: 0,
				behavior: 'smooth',
			} );
		}, 1 );
	};

	/**
	 * Handles the form error display.
	 *
	 * @param {Object} errors
	 */
	const onError = ( errors ) => {
		Object.keys( errors ).forEach( ( key ) => {
			setNotification( 'error', errors[ key ].message, false, true );
		} );

		setTimeout( () => {
			window.scrollTo( {
				top: 0,
				behavior: 'smooth',
			} );
		}, 1 );
	};

	return (
		<>
			<FormProvider { ...formMethods }>
				<form id="post" onSubmit={ formMethods.handleSubmit( onSubmit, onError ) }>
					<table className="form-table">
						<tbody>
							<FormRow
								name="name"
								className={ 'group-name-row' }
								label={ __( 'Group name', 'woocommerce-product-options' ) }
								tooltip={ __( 'Enter a name for the option group.', 'woocommerce-product-options' ) }
							>
								<input
									required
									id="name"
									type="text"
									className="regular-input"
									{ ...formMethods.register( 'name', {
										required: __(
											'Option group must be given a name.',
											'woocommerce-product-options'
										),
									} ) }
								/>
								<Controller
									control={ formMethods.control }
									name={ 'display_name' }
									render={ ( { field } ) => (
										<CheckboxControl
											label={ __( 'Display', 'woocommerce-product-options' ) }
											checked={ [ '1', 1, true ].includes( field?.value ) }
											onChange={ ( value ) => field.onChange( value ) }
											isClassicStyle={ true }
										/>
									) }
								/>
							</FormRow>

							<FormRow name={ 'visibility' } label={ __( 'Visibility', 'woocommerce-product-options' ) }>
								<Controller
									control={ formMethods.control }
									name={ 'visibility' }
									render={ ( { field } ) => (
										<RadioControl
											selected={ field?.value ?? 'global' }
											options={ [
												{
													label: __(
														'Display globally on all products',
														'woocommerce-product-options'
													),
													value: 'global',
												},
												{
													label: __(
														'Show on specific categories or products',
														'woocommerce-product-options'
													),
													value: 'specific',
												},
											] }
											onChange={ ( value ) => field.onChange( value ) }
										/>
									) }
								/>
							</FormRow>

							{ visibilityInput === 'specific' && (
								<FormRow
									name={ 'inclusions' }
									label={ __( 'Inclusions', 'woocommerce-product-options' ) }
								>
									<div className="wpo-split-row">
										<Controller
											control={ formMethods.control }
											name={ 'products' }
											render={ ( { field } ) => (
												<ProductSelect
													prefilled={ group?.products ?? false }
													onChange={ ( value ) => field.onChange( value ) }
													value={ field?.value ?? [] }
												/>
											) }
										/>

										<Controller
											control={ formMethods.control }
											name={ 'categories' }
											render={ ( { field } ) => (
												<CategorySelect
													prefilled={ group?.categories ?? false }
													onChange={ ( value ) => field.onChange( value ) }
													value={ field?.value ?? [] }
												/>
											) }
										/>
									</div>
								</FormRow>
							) }

							<FormRow name={ 'exclusions' } label={ __( 'Exclusions', 'woocommerce-product-options' ) }>
								<div className="wpo-split-row">
									<Controller
										control={ formMethods.control }
										defaultValue={ [] }
										name={ 'exclude_products' }
										render={ ( { field } ) => (
											<ProductSelect
												prefilled={ group?.exclude_products ?? false }
												onChange={ ( value ) => field.onChange( value ) }
												value={ field?.value ?? [] }
											/>
										) }
									/>

									<Controller
										control={ formMethods.control }
										name={ 'exclude_categories' }
										render={ ( { field } ) => (
											<CategorySelect
												prefilled={ group?.exclude_categories ?? false }
												onChange={ ( value ) => field.onChange( value ) }
												value={ field?.value ?? [] }
											/>
										) }
									/>
								</div>
							</FormRow>

							<FormRow name={ 'options' } label={ __( 'Options', 'woocommerce-product-options' ) }>
								<ListTable
									className="wpo-options-table"
									columns={ columns }
									data={ Array.isArray( watchOptionsField ) ? watchOptionsField : [] }
									fetchData={ () => {} }
									loading={ false }
									compTableLoading={ <Spinner /> }
									compEmptyTable={
										groupOptionsQuery.isFetching ? (
											<Spinner />
										) : (
											<div>{ __( 'No options found.', 'woocommerce-product-options' ) }</div>
										)
									}
									compDraggableHeader={
										<WCTableTooltip
											tooltip={ __( 'Drag to reorder', 'woocommerce-product-options' ) }
										/>
									}
									compDraggableIcon={
										<Dashicon className={ 'barn2-list-table-drag' } icon={ 'menu' } />
									}
									compFooter={ () => (
										<>
											<Button onClick={ addOption }>
												{ __( 'Add option', 'woocommerce-product-options' ) }
											</Button>
										</>
									) }
									rowActionsAccessor={ 'name' }
									rowActions={ ( index ) => [
										{
											title:
												index === editRow
													? __( 'Close option', 'woocommerce-product-options' )
													: __( 'Edit option', 'woocommerce-product-options' ),
											isTrash: false,
											onClick: ( row, fetchData, rowIndex ) =>
												closeOrSwitchInlineEdit( rowIndex ),
										},
										{
											title:
												index !== editRow
													? __( 'Duplicate', 'woocommerce-product-options' )
													: '',
											isTrash: false,
											onClick: ( row, fetchData, rowIndex ) => duplicateOption( rowIndex ),
										},
										{
											title: __( 'Delete', 'woocommerce-product-options' ),
											isTrash: true,
											onClick: ( row, fetchData, rowIndex ) =>
												setDeleteRow( { ...row, ...{ rowIndex } } ),
										},
									] }
									isInlineEdit={ editOpen ? editRow : null }
									compInlineEdit={ ( row, index ) => {
										return (
											<OptionForm index={ index } option={ row } formMethods={ formMethods } />
										);
									} }
									draggable={ true }
									onDragEnd={ handleReOrder }
								/>

								<DeleteModal
									row={ deleteRow }
									title={ __( 'Delete option', 'woocommerce-product-options' ) }
									confirmMessage={ confirmDeleteMessage }
									onModalDelete={ handleDelete }
									onModalClose={ () => setDeleteRow( {} ) }
								/>
							</FormRow>
						</tbody>
					</table>

					<input type={ 'hidden' } { ...formMethods.register( 'id' ) } />
					<input type={ 'hidden' } { ...formMethods.register( 'menu_order' ) } />

					<div className={ 'submit-area' }>
						<input
							type={ 'submit' }
							className={ 'button-primary' }
							value={ __( 'Save changes', 'woocommerce-product-options' ) }
							disabled={
								! formMethods.formState.isDirty ||
								updateGroup.isLoading ||
								createGroup.isLoading ||
								duplicateGroup.isLoading
							}
						/>
						{ ( updateGroup.isLoading || createGroup.isLoading || duplicateGroup.isLoading ) && (
							<Spinner />
						) }
					</div>
				</form>
			</FormProvider>
		</>
	);
};

export default GroupForm;
