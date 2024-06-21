/**
 * WordPress dependencies
 */
import { useState, useEffect } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { FormToggle } from '@wordpress/components';

/**
 * External dependencies
 */
import { RadioControl, CheckboxControl, SelectControl } from '@barn2plugins/components';
import { Controller } from 'react-hook-form';

/**
 * Internal dependencies
 */
import { optionTypes } from '../config';
import OptionFormRow from '../components/tables/option-form-row';
import OptionTypeSelector from '../components/fields/option-type-selector';
import OptionChoicesRepeater from '../components/fields/option-choices-repeater';
import ProductsRepeater from '../components/fields/products-repeater';
import DynamicProducts from '../components/fields/dynamic-products';
import ConditionalLogicRepeater from '../components/fields/conditional-logic-repeater';
import RichText from '../components/fields/rich-text';
import FileTypeSelect from '../components/fields/file-type-select';
import PriceFormula from '../components/fields/price-formula';
import DaySelect from '../components/fields/day-select';
import DateFormat from '../components/fields/date-format';
import { removeUnnecessarySettings, hasAdvancedSettings } from '../util';

const OptionForm = ( { formMethods, index, option } ) => {
	// const [ productImageButtonAttributes, setProductImageButtonAttributes ] = useState( false );
	const id = formMethods.watch( `options.${ index }.id` );
	const optionType = formMethods.watch( `options.${ index }.type` );
	const choices = formMethods.watch( `options.${ index }.choices` );
	const displayLabel = formMethods.watch( `options.${ index }.settings.display_label` );
	const singleChoice = [ 'text', 'textarea', 'number', 'file_upload', 'customer_price', 'datepicker' ].includes(
		optionType
	);
	const productDisplayStyle = formMethods.watch( `options.${ index }.settings.product_display_style` );
	const quantityLimited =
		[ 'checkbox', 'images', 'text_labels' ].includes( optionType ) ||
		( optionType === 'product' &&
			[ 'checkbox', 'image_buttons' ].includes( productDisplayStyle ) );
	const minQtyLimit = formMethods.watch( `options.${ index }.settings.choice_qty.min` );
	const maxQtyLimit = formMethods.watch( `options.${ index }.settings.choice_qty.max` );
	const numberType = formMethods.watch( `options.${ index }.settings.number_type` );
	const productSelection = formMethods.watch( `options.${ index }.settings.product_selection` );
	const dynamicProductsLimit = formMethods.watch( `options.${ index }.settings.dynamic_products.limit` );
	const displayProductsAsImageButtons =
		optionType === 'product' && productDisplayStyle === 'image_buttons';

	const [ advancedSettings, setAdvancedSettings ] = useState( hasAdvancedSettings( option, optionType ) );

	const getChoicesLabel = () => {
		if ( optionType === 'customer_price' ) {
			return __( 'Label', 'woocommerce-product-options' );
		}

		if ( singleChoice ) {
			return __( 'Choice', 'woocommerce-product-options' );
		}

		return __( 'Choices', 'woocommerce-product-options' );
	};

	const getProductsLength = () => {
		if ( productSelection === 'dynamic' ) {
			return dynamicProductsLimit;
		}

		const products = [];

		const manualProducts = option.settings.manual_products;

		manualProducts?.forEach( ( product ) => {
			if ( product.type === 'simple' ) {
				products.push( product.product_id );
			} else if ( product?.variations?.length ) {
				products.push( ...product.variations.map( ( variation ) => variation.id ) );
			}
		} );

		return products?.length;
	};

	const getChoicesLength = () => {
		if ( optionType === 'product' ) {
			return getProductsLength();
		}

		return choices?.length;
	};

	// set default values for nested settings
	useEffect( () => {

		if ( optionType !== 'datepicker' ) {
			return;
		}

		if ( ! option?.settings?.datepicker?.date_format ) {
			formMethods.setValue( `options.${ index }.settings.datepicker.date_format`, 'F j, Y' );
		}

		if ( ! option?.settings?.datepicker?.min_time ) {
			formMethods.setValue( `options.${ index }.settings.datepicker.min_time`, '00:00' );
		}

		if ( ! option?.settings?.datepicker?.max_time ) {
			formMethods.setValue( `options.${ index }.settings.datepicker.max_time`, '23:59' );
		}

		if ( ! option?.settings?.datepicker?.minute_increment ) {
			formMethods.setValue( `options.${ index }.settings.datepicker.minute_increment`, 15 );
		}

		if ( ! option?.settings?.datepicker?.hour_increment ) {
			formMethods.setValue( `options.${ index }.settings.datepicker.hour_increment`, 1 );
		}
	}, [ optionType ] );

	useEffect( () => {
		if ( optionType !== 'product' ) {
			if ( option?.settings?.product_display_style ) {
				formMethods.setValue( `options.${ index }.settings.product_display_style`, '' )
			}
			return;
		}

		if ( ! option?.settings?.product_display_style ) {
			formMethods.setValue( `options.${ index }.settings.product_display_style`, 'image_buttons' );
		}

	}, [ optionType ] );

	// disable product image button attributes if option type is not products
	// useEffect( () => {
	// 	setProductImageButtonAttributes( optionType === 'product' && option.settings.product_display_style === 'image_buttons' );
	// }, [ optionType ] );

	useEffect( () => {
		if ( optionType !== 'images' && ( optionType !== 'product' || productDisplayStyle !== 'image_buttons' ) ) {
			return;
		}

		if ( ! option?.settings?.button_width ) {
			formMethods.setValue( `options.${ index }.settings.button_width`, 118 );
		}
	}, [ optionType, productDisplayStyle ] );

	return (
		<>
			<table className="option-form-table widefat fixed">
				<tbody>
					<OptionFormRow
						name={ `options.${ index }.name` }
						className={ 'option-name-row' }
						label={ __( 'Option name', 'woocommerce-product-options' ) }
						tooltip={ __(
							'Enter a name, such as “Pizza topping”. You can choose whether or not to display this above the choice(s).'
						) }
					>
						<input
							// eslint-disable-next-line jsx-a11y/no-autofocus
							autoFocus
							required
							id="name"
							type="text"
							className="regular-input"
							{ ...formMethods.register( `options.${ index }.name`, {
								required: true,
							} ) }
						/>

						{ ! [ 'html', 'wysiwyg' ].includes( optionType ) && (
							<Controller
								control={ formMethods.control }
								name={ `options.${ index }.display_name` }
								render={ ( { field } ) => (
									<CheckboxControl
										label={ __( 'Display', 'woocommerce-product-options' ) }
										checked={ [ '1', 1, true ].includes( field?.value ) }
										onChange={ ( value ) => field.onChange( value ) }
										isClassicStyle={ true }
									/>
								) }
							/>
						) }
					</OptionFormRow>

					<OptionFormRow
						name={ `options.${ index }.type` }
						label={ <>{ __( 'Type', 'woocommerce-product-options' ) } </> }
					>
						<Controller
							control={ formMethods.control }
							name={ `options.${ index }.type` }
							rules={ { required: true } }
							render={ ( { field } ) => (
								<OptionTypeSelector
									onChange={ ( value ) => {
										removeUnnecessarySettings( option, value );
										setAdvancedSettings( hasAdvancedSettings( option, value ) );
										field.onChange( value );
									} }
									selected={ field?.value ?? null }
								/>
							) }
						/>
					</OptionFormRow>

					{ ! [ 'html', 'wysiwyg', 'price_formula', 'product' ].includes( optionType ) && (
						<OptionFormRow
							name={ `options.${ index }.choices` }
							label={ getChoicesLabel() }
							tooltip={
								optionType === 'customer_price'
									? __(
											'The label that appears alongside the option.',
											'woocommerce-product-options'
									  )
									: ''
							}
						>
							<Controller
								control={ formMethods.control }
								name={ `options.${ index }.choices` }
								rules={ { required: true } }
								render={ ( { field } ) => (
									<OptionChoicesRepeater
										optionType={ optionType }
										singleChoice={ singleChoice }
										maxQty={ maxQtyLimit }
										onChange={ ( value ) => field.onChange( value ) }
										value={ field?.value ?? [] }
									/>
								) }
							/>
						</OptionFormRow>
					) }

					{ optionType === 'html' && (
						<OptionFormRow
							name={ `options.${ index }.settings.html` }
							label={ __( 'Static content', 'woocommerce-product-options' ) }
						>
							<textarea
								className="html-textarea"
								rows="10"
								{ ...formMethods.register( `options.${ index }.settings.html` ) }
							/>
						</OptionFormRow>
					) }

					{ optionType === 'wysiwyg' && (
						<OptionFormRow
							name={ `options.${ index }.settings.html` }
							label={ __( 'Static content', 'woocommerce-product-options' ) }
						>
							<Controller
								control={ formMethods.control }
								name={ `options.${ index }.settings.html` }
								render={ ( { field } ) => (
									<RichText
										onChange={ ( value ) => field.onChange( value ) }
										value={ field?.value ?? '' }
									/>
								) }
							/>
						</OptionFormRow>
					) }

					{ optionType === 'datepicker' && (
						<>
							<OptionFormRow
								name="settings[datepicker][enable_time]"
								label={ __( 'Selection options', 'woocommerce-product-options' ) }
							>
								<Controller
									control={ formMethods.control }
									name={ `options.${ index }.settings.datepicker.disable_past_dates` }
									render={ ( { field } ) => (
										<CheckboxControl
											label={ __( 'Disable past dates', 'woocommerce-product-options' ) }
											checked={ [ '1', 1, true ].includes( field?.value ) }
											onChange={ ( changeValue ) => field.onChange( changeValue ) }
											isClassicStyle
										/>
									) }
								/>
								<Controller
									control={ formMethods.control }
									name={ `options.${ index }.settings.datepicker.disable_future_dates` }
									render={ ( { field } ) => (
										<CheckboxControl
											label={ __( 'Disable future dates', 'woocommerce-product-options' ) }
											checked={ [ '1', 1, true ].includes( field?.value ) }
											onChange={ ( changeValue ) => field.onChange( changeValue ) }
											isClassicStyle
										/>
									) }
								/>
								<Controller
									control={ formMethods.control }
									name={ `options.${ index }.settings.datepicker.disable_today` }
									render={ ( { field } ) => (
										<CheckboxControl
											label={ __( 'Disable today', 'woocommerce-product-options' ) }
											checked={ [ '1', 1, true ].includes( field?.value ) }
											onChange={ ( changeValue ) => field.onChange( changeValue ) }
											isClassicStyle
										/>
									) }
								/>
								<Controller
									control={ formMethods.control }
									name={ `options.${ index }.settings.datepicker.enable_time` }
									render={ ( { field } ) => (
										<CheckboxControl
											label={ __( 'Enable time', 'woocommerce-product-options' ) }
											checked={ [ '1', 1, true ].includes( field?.value ) }
											onChange={ ( changeValue ) => field.onChange( changeValue ) }
											isClassicStyle
										/>
									) }
								/>
							</OptionFormRow>
							<OptionFormRow
								name="settings[datepicker][disable_days]"
								label={ __( 'Disable days', 'woocommerce-product-options' ) }
								tooltip={ __(
									'Select the days of the week to disable.',
									'woocommerce-product-options'
								) }
							>
								<Controller
									control={ formMethods.control }
									name={ `options.${ index }.settings.datepicker.disable_days` }
									render={ ( { field } ) => (
										<DaySelect
											onChange={ ( changeValue ) => field.onChange( changeValue ) }
											value={ field.value }
										/>
									) }
								/>
							</OptionFormRow>
						</>
					) }

					{ optionType === 'price_formula' && (
						<>
							<OptionFormRow
								name={ `options.${ index }.settings.formula` }
								label={ __( 'Formula', 'woocommerce-product-options' ) }
							>
								<Controller
									control={ formMethods.control }
									name={ `options.${ index }.settings.formula` }
									render={ ( { field } ) => (
										<PriceFormula
											onChange={ ( value ) => field.onChange( value ) }
											value={ field?.value ?? '' }
											formMethods={ formMethods }
										/>
									) }
								/>
							</OptionFormRow>

							<OptionFormRow
								name={ `options.${ index }.settings.price_suffix` }
								label={ __( 'Price display suffix', 'woocommerce-product-options' ) }
								tooltip={ __(
									'Define text to be displayed after the product price. E.g. "per meter".',
									'woocommerce-product-options'
								) }
							>
								<input
									type="text"
									className="regular-input"
									{ ...formMethods.register( `options.${ index }.settings.price_suffix` ) }
								/>
							</OptionFormRow>

							<OptionFormRow
								name={ `options.${ index }.settings.exclude_product_price` }
								label={ __( 'Ignore main product price', 'woocommerce-product-options' ) }
								tooltip={ __(
									'Enable this to prevent the main product price from being included in the total product price. E.g. if the main product price is $20 to indicate that a product is priced by the meter, enable this option to prevent $20 from being added to the calculated price.',
									'woocommerce-product-options'
								) }
							>
								<Controller
									control={ formMethods.control }
									name={ `options.${ index }.settings.exclude_product_price` }
									render={ ( { field } ) => (
										<CheckboxControl
											checked={ [ '1', 1, true ].includes( field?.value ) }
											onChange={ ( changeValue ) => field.onChange( changeValue ) }
											isClassicStyle
										/>
									) }
								/>
							</OptionFormRow>
						</>
					) }

					{ optionType === 'product' && (
						<>
							<OptionFormRow
								name={ `options.${ index }.settings.product_selection` }
								label={ __( 'Product selection' ) }
								tooltip={ __(
									'You can either choose individual products to display as options, or select them dynamically based on criteria such as their category.'
								) }
							>
								<Controller
									control={ formMethods.control }
									name={ `options.${ index }.settings.product_selection` }
									render={ ( { field } ) => (
										<RadioControl
											selected={ field?.value ? field.value : 'manual' }
											options={ [
												{
													label: __(
														'Select specific products',
														'woocommerce-product-options'
													),
													value: 'manual',
												},
												{
													label: __(
														'Select products dynamically',
														'woocommerce-product-options'
													),
													value: 'dynamic',
												},
											] }
											onChange={ ( value ) => field.onChange( value ) }
											default={ 'manual' }
										/>
									) }
								/>
							</OptionFormRow>

							{ ( option.settings === null ||
								option?.settings?.product_selection === null ||
								option?.settings?.product_selection === 'manual' ) && (
								<OptionFormRow
									name={ `options.${ index }.settings.manual_products` }
									label="Products"
									tooltip={ __( 'Select one or more products and/or categories to display.' ) }
								>
									<Controller
										control={ formMethods.control }
										name={ `options.${ index }.settings.manual_products` }
										rules={ { required: true } }
										render={ ( { field } ) => (
											<ProductsRepeater
												optionType={ optionType }
												singleChoice={ singleChoice }
												maxQty={ maxQtyLimit }
												onChange={ ( value ) => field.onChange( value ) }
												value={ field?.value ?? [] }
											/>
										) }
									/>
								</OptionFormRow>
							) }

							{ option?.settings?.product_selection === 'dynamic' && (
								<OptionFormRow
									name={ `options.${ index }.settings.dynamic_products` }
									label="Dynamic products"
									tooltip={ __(
										'Choose which categories to display products from. Only Simple products can be displayed dynamically.',
										'woocommerce-product-options'
									) }
								>
									<Controller
										control={ formMethods.control }
										name={ `options.${ index }.settings.dynamic_products` }
										render={ ( { field } ) => (
											<DynamicProducts
												optionType={ optionType }
												singleChoice={ singleChoice }
												maxQty={ maxQtyLimit }
												onChange={ ( value ) => field.onChange( value ) }
												value={ field?.value ?? [] }
											/>
										) }
									/>
								</OptionFormRow>
							) }

							<OptionFormRow
								name={ `options.${ index }.settings.product_display_style` }
								label={ __( 'Display choices as', 'woocommerce-product-options' ) }
							>
								<Controller
									control={ formMethods.control }
									name={ `options.${ index }.settings.product_display_style` }
									render={ ( { field } ) => (
										<SelectControl
											value={ field?.value ?? 'image_buttons' }
											options={ [
												{
													label: __( 'Image buttons', 'woocommerce-product-options' ),
													value: 'image_buttons',
												},
												{
													label: __( 'Checkboxes', 'woocommerce-product-options' ),
													value: 'checkbox',
												},
												{
													label: __( 'Radio buttons', 'woocommerce-product-options' ),
													value: 'radio',
												},
												{
													label: __( 'Dropdown select', 'woocommerce-product-options' ),
													value: 'dropdown',
												},
												{
													label: __( 'Products', 'woocommerce-product-options' ),
													value: 'product',
												},
											] }
											onChange={ ( value ) => {
												field.onChange( value );
											} }
										/>
									) }
								/>
							</OptionFormRow>
						</>
					) }

					{ ! [ 'html', 'wysiwyg', 'price_formula' ].includes( optionType ) && (
						<>
							<OptionFormRow
								name="description"
								label={ __( 'Description', 'woocommerce-product-options' ) }
								tooltip={ __(
									'Enter an optional description to display underneath the product options.',
									'woocommerce-product-options'
								) }
							>
								<textarea { ...formMethods.register( `options.${ index }.description` ) } />
							</OptionFormRow>
						</>
					) }

					{ ( displayProductsAsImageButtons || [ 'images' ].includes( optionType ) ) && (
						<OptionFormRow
							name={ 'button_width' }
							label={ __( 'Image width', 'woocommerce-product-options' ) }
						>
							<Controller
								control={ formMethods.control }
								name={ `options.${ index }.settings.button_width` }
								render={ () => (
									<>
										<input
											type="number"
											step="1"
											min="1"
											className="regular-input wpo-image-buttons-width-field"
											{ ...formMethods.register( `options.${ index }.settings.button_width` ) }
										/>
										<label
											className="wpo-image-buttons-width-label"
											htmlFor="settings.button_width"
										>
											{ __( 'px', 'woocommerce-product-options' ) }
										</label>
									</>
								) }
							/>
						</OptionFormRow>
					) }

					{ ( displayProductsAsImageButtons || [ 'color_swatches', 'images' ].includes( optionType ) ) && (
						<OptionFormRow
							name={ 'display_label' }
							label={ __( 'Display', 'woocommerce-product-options' ) }
						>
							<Controller
								control={ formMethods.control }
								name={ `options.${ index }.settings.display_label` }
								render={ ( { field } ) => (
									<RadioControl
										selected={ field?.value?.toString() ?? '0' }
										options={ [
											/* translators: %s: Option type label */
											{
												label: sprintf(
													/* translators: %s: Option type label */
													__( 'Display %s only', 'woocommerce-product-options' ),
													optionType === 'product'
														? __( 'images', 'woocommerce-product-options' )
														: optionTypes[
																optionTypes.findIndex(
																	( optionConfig ) => optionConfig.key === optionType
																)
														  ].label.toLowerCase()
												),
												value: '0',
											},
											/* translators: %s: Option type label */
											{
												label: sprintf(
													/* translators: %s: Option type label */
													__( 'Display label and %s', 'woocommerce-product-options' ),
													optionType === 'product'
														? __( 'images', 'woocommerce-product-options' )
														: optionTypes[
																optionTypes.findIndex(
																	( optionConfig ) => optionConfig.key === optionType
																)
														  ].label.toLowerCase()
												),
												value: '1',
											},
										] }
										onChange={ ( value ) => field.onChange( value ) }
									/>
								) }
							/>
						</OptionFormRow>
					) }

					{ ( displayProductsAsImageButtons || [ 'images' ].includes( optionType ) ) &&
						displayLabel === '1' && (
							<OptionFormRow
								name={ 'label_position' }
								label={ __( 'Label position', 'woocommerce-product-options' ) }
							>
								<Controller
									control={ formMethods.control }
									name={ `options.${ index }.settings.label_position` }
									render={ ( { field } ) => (
										<SelectControl
											value={ field?.value ? field.value : 'full' }
											options={ [
												{
													label: __( 'Full overlay', 'woocommerce-product-options' ),
													value: 'full',
												},
												{
													label: __( 'Full overlay on hover', 'woocommerce-product-options' ),
													value: 'full_hover',
												},
												{
													label: __( 'Partial overlay', 'woocommerce-product-options' ),
													value: 'partial',
												},
												{
													label: __( 'Above image', 'woocommerce-product-options' ),
													value: 'above',
												},
												{
													label: __( 'Below image', 'woocommerce-product-options' ),
													value: 'below',
												},
											] }
											onChange={ ( value ) => field.onChange( value ) }
										/>
									) }
								/>
							</OptionFormRow>
						) }

					{ ! [ 'html', 'wysiwyg', 'price_formula' ].includes( optionType ) && productDisplayStyle !== 'product' && (
							<OptionFormRow
								name="required"
								label={ __( 'Required', 'woocommerce-product-options' ) }
								tooltip={ __(
									'Force customers to select an option before they can add the product to the cart.',
									'woocommerce-product-options'
								) }
							>
								<Controller
									control={ formMethods.control }
									name={ `options.${ index }.required` }
									render={ ( { field } ) => (
										<CheckboxControl
											checked={ [ '1', 1, true ].includes( field?.value ) }
											onChange={ ( changeValue ) => field.onChange( changeValue ) }
											isClassicStyle
										/>
									) }
								/>
							</OptionFormRow>
						) }

					<OptionFormRow label={ __( 'Advanced settings', 'woocommerce-product-options' ) }>
						<FormToggle
							checked={ advancedSettings }
							onChange={ () => setAdvancedSettings( ! advancedSettings ) }
						/>
					</OptionFormRow>

					{ advancedSettings && optionType === 'file_upload' && (
						<>
							<OptionFormRow
								name="settings[file_upload_size]"
								label={ __( 'Maximum file size (MB)', 'woocommerce-product-options' ) }
							>
								<input
									type="number"
									min="0"
									className="regular-input"
									{ ...formMethods.register( `options.${ index }.settings.file_upload_size` ) }
								/>
							</OptionFormRow>

							<OptionFormRow
								name="settings[file_upload_items_max]"
								label={ __( 'Maximum number of files', 'woocommerce-product-options' ) }
							>
								<input
									type="number"
									step={ 1 }
									min={ 1 }
									className="regular-input"
									{ ...formMethods.register( `options.${ index }.settings.file_upload_items_max` ) }
								/>
							</OptionFormRow>

							<OptionFormRow
								name="settings[file_upload_allowed_types]"
								label={ __( 'Allowed file types', 'woocommerce-product-options' ) }
							>
								<Controller
									control={ formMethods.control }
									name={ `options.${ index }.settings.file_upload_allowed_types` }
									render={ ( { field } ) => (
										<FileTypeSelect
											value={ field?.value }
											onChange={ ( value ) => field.onChange( value ) }
										/>
									) }
								/>
							</OptionFormRow>
						</>
					) }

					{ advancedSettings && quantityLimited && (
						<OptionFormRow
							name="settings.choice_qty"
							label={ __( 'Choice restrictions', 'woocommerce-product-options' ) }
							tooltip={ __(
								'Set the minimum and maximum number of choices that can be selected.',
								'woocommerce-product-options'
							) }
						>
							<div className="wpo-options-min-max-field">
								<div className="wpo-options-min-field">
									<label htmlFor="settings.choice_qty.min">
										{ __( 'Minimum', 'woocommerce-product-options' ) }
									</label>
									<input
										type="number"
										min="0"
										max={ getChoicesLength() ?? 1 }
										step="1"
										className="regular-input"
										{ ...formMethods.register( `options.${ index }.settings.choice_qty.min` ) }
									/>
								</div>

								<div className="wpo-options-max-field">
									<label htmlFor="settings.choice_qty.max">
										{ __( 'Maximum', 'woocommerce-product-options' ) }
									</label>
									<input
										type="number"
										step="1"
										min={ minQtyLimit }
										max={ getChoicesLength() ?? minQtyLimit }
										className="regular-input"
										{ ...formMethods.register( `options.${ index }.settings.choice_qty.max` ) }
									/>
								</div>
							</div>
						</OptionFormRow>
					) }

					{ advancedSettings && ( displayProductsAsImageButtons || [ 'images' ].includes( optionType ) ) && (
						<OptionFormRow
							name="settings.show_in_product_gallery"
							label={ __( 'Update main image', 'woocommerce-product-options' ) }
							tooltip={ __(
								'Update the main product image when the customer clicks on an image button; and also include choice images in the product gallery.',
								'woocommerce-product-options'
							) }
						>
							<Controller
								control={ formMethods.control }
								name={ `options.${ index }.settings.show_in_product_gallery` }
								render={ ( { field } ) => {
									return (
										<CheckboxControl
											isClassicStyle
											checked={ !! field?.value }
											onChange={ ( value ) => field.onChange( value ) }
										/>
									);
								} }
							/>
						</OptionFormRow>
					) }

					{ advancedSettings && optionType === 'number' && (
						<>
							<OptionFormRow
								name="settings.default_value"
								className={ 'option-default-value-row' }
								label={ __( 'Default value', 'woocommerce-product-options' ) }
							>
								<input
									type="number"
									className="regular-input"
									{ ...formMethods.register( `options.${ index }.settings.default_value` ) }
									step={ numberType === 'whole' ? 1 : 'any' }
								/>
							</OptionFormRow>
							<OptionFormRow
								name="settings.number_type"
								className={ 'option-step-row' }
								label={ __( 'Number type', 'woocommerce-product-options' ) }
							>
								<Controller
									control={ formMethods.control }
									name={ `options.${ index }.settings.number_type` }
									render={ ( { field } ) => (
										<RadioControl
											selected={ field?.value ?? 'whole' }
											options={ [
												{
													label: __( 'Whole number', 'woocommerce-product-options' ),
													value: 'whole',
												},
												{
													label: __( 'Decimal', 'woocommerce-product-options' ),
													value: 'decimal',
												},
											] }
											onChange={ ( value ) => field.onChange( value ) }
										/>
									) }
								/>
							</OptionFormRow>
							<OptionFormRow
								name="settings.number_limits"
								label={ __( 'Number limits', 'woocommerce-product-options' ) }
							>
								<div className="wpo-options-min-max-field">
									<div className="wpo-options-min-field">
										<label htmlFor="settings.number_limits.min">
											{ __( 'Minimum', 'woocommerce-product-options' ) }
										</label>
										<input
											type="number"
											min="0"
											className="regular-input"
											{ ...formMethods.register(
												`options.${ index }.settings.number_limits.min`
											) }
											step={ numberType === 'whole' ? 1 : 'any' }
										/>
									</div>

									<div className="wpo-options-max-field">
										<label htmlFor="settings.number_limits.max">
											{ __( 'Maximum', 'woocommerce-product-options' ) }
										</label>
										<input
											type="number"
											min="0"
											className="regular-input"
											{ ...formMethods.register(
												`options.${ index }.settings.number_limits.max`
											) }
											step={ numberType === 'whole' ? 1 : 'any' }
										/>
									</div>
								</div>
							</OptionFormRow>
						</>
					) }

					{ advancedSettings && [ 'text', 'textarea' ].includes( optionType ) && (
						<OptionFormRow
							name="settings.choice_char"
							label={ __( 'Character limits', 'woocommerce-product-options' ) }
						>
							<div className="wpo-options-min-max-field">
								<div className="wpo-options-min-field">
									<label htmlFor="settings.choice_char.min">
										{ __( 'Minimum', 'woocommerce-product-options' ) }
									</label>
									<input
										type="number"
										min="0"
										step="1"
										className="regular-input"
										{ ...formMethods.register( `options.${ index }.settings.choice_char.min` ) }
									/>
								</div>
								<div className="wpo-options-max-field">
									<label htmlFor="settings.choice_char.max">
										{ __( 'Maximum', 'woocommerce-product-options' ) }
									</label>
									<input
										type="number"
										className="regular-input"
										{ ...formMethods.register( `options.${ index }.settings.choice_char.max` ) }
									/>
								</div>
							</div>
						</OptionFormRow>
					) }

					{ advancedSettings && optionType === 'datepicker' && (
						<>
							<OptionFormRow
								name="settings[datepicker][date_format]"
								label={ __( 'Date format', 'woocommerce-product-options' ) }
							>
								<Controller
									control={ formMethods.control }
									name={ `options.${ index }.settings.datepicker.date_format` }
									render={ ( { field } ) => (
										<DateFormat
											onChange={ ( changeValue ) => field.onChange( changeValue ) }
											value={ field.value }
										/>
									) }
								/>
							</OptionFormRow>
							<OptionFormRow
								name="settings[datepicker][date_limits]"
								label={ __( 'Date limits', 'woocommerce-product-options' ) }
								tooltip={ __(
									'Enter a date in the format YYYY-MM-DD, or enter a dynamic date such as +6d to disable the date which is 6 days from the current date.',
									'woocommerce-product-options'
								) }
							>
								<div className="wpo-options-min-max-field">
									<div className="wpo-options-min-field">
										<label htmlFor="settings.datepicker.min_date">
											{ __( 'Minimum', 'woocommerce-product-options' ) }
										</label>
										<input
											type="text"
											className="regular-input"
											{ ...formMethods.register(
												`options.${ index }.settings.datepicker.min_date`
											) }
										/>
									</div>

									<div className="wpo-options-max-field">
										<label htmlFor="settings.datepicker.max_date">
											{ __( 'Maximum', 'woocommerce-product-options' ) }
										</label>
										<input
											type="text"
											className="regular-input"
											{ ...formMethods.register(
												`options.${ index }.settings.datepicker.max_date`
											) }
										/>
									</div>
								</div>
							</OptionFormRow>
							<OptionFormRow
								name="settings[datepicker][disable_dates]"
								label={ __( 'Disable dates', 'woocommerce-product-options' ) }
								tooltip={ __(
									'Enter a comma separated list of dates in the format YYYY-MM-DD, or enter a dynamic date such as +6d to disable the date which is 6 days from the current date.',
									'woocommerce-product-options'
								) }
							>
								<input
									type="text"
									className="regular-input"
									{ ...formMethods.register(
										`options.${ index }.settings.datepicker.disable_dates`
									) }
								/>
							</OptionFormRow>
							<OptionFormRow
								name="settings[datepicker][time_limits]"
								label={ __( 'Time limits', 'woocommerce-product-options' ) }
								tooltip={ __(
									'Enter a start and end time to restrict the available times.',
									'woocommerce-product-options'
								) }
							>
								<div className="wpo-options-min-max-field">
									<div className="wpo-options-min-field">
										<label htmlFor="settings.datepicker.min_time">
											{ __( 'Minimum', 'woocommerce-product-options' ) }
										</label>
										<input
											type="text"
											className="regular-input"
											{ ...formMethods.register(
												`options.${ index }.settings.datepicker.min_time`
											) }
										/>
									</div>

									<div className="wpo-options-max-field">
										<label htmlFor="settings.datepicker.max_time">
											{ __( 'Maximum', 'woocommerce-product-options' ) }
										</label>
										<input
											type="text"
											className="regular-input"
											{ ...formMethods.register(
												`options.${ index }.settings.datepicker.max_time`
											) }
										/>
									</div>
								</div>
							</OptionFormRow>
							<OptionFormRow
								name="settings[datepicker][time_increment]"
								label={ __( 'Time increment', 'woocommerce-product-options' ) }
								tooltip={ __(
									'Choose the time increments for the time picker.',
									'woocommerce-product-options'
								) }
							>
								<div className="wpo-options-min-max-field">
									<div className="wpo-options-min-field">
										<label htmlFor="settings.datepicker.hour_increment">
											{ __( 'Hours', 'woocommerce-product-options' ) }
										</label>
										<input
											type="number"
											min="1"
											max="24"
											className="regular-input"
											{ ...formMethods.register(
												`options.${ index }.settings.datepicker.hour_increment`
											) }
										/>
									</div>

									<div className="wpo-options-max-field">
										<label htmlFor="settings.datepicker.minute_increment">
											{ __( 'Minutes', 'woocommerce-product-options' ) }
										</label>
										<input
											type="number"
											min="1"
											max="60"
											className="regular-input"
											{ ...formMethods.register(
												`options.${ index }.settings.datepicker.minute_increment`
											) }
										/>
									</div>
								</div>
							</OptionFormRow>
						</>
					) }

					{ advancedSettings && (
						<OptionFormRow
							name="conditional_logic"
							label={ __( 'Conditional logic', 'woocommerce-product-options' ) }
						>
							<Controller
								control={ formMethods.control }
								name={ `options.${ index }.conditional_logic` }
								render={ ( { field } ) => (
									<ConditionalLogicRepeater
										formMethods={ formMethods }
										optionId={ id }
										onChange={ ( value ) => field.onChange( value ) }
										value={ field?.value ?? [] }
									/>
								) }
							/>
						</OptionFormRow>
					) }
				</tbody>
			</table>

			<input type={ 'hidden' } { ...formMethods.register( `options.${ index }.id` ) } />
			<input type={ 'hidden' } { ...formMethods.register( `options.${ index }.group_id` ) } />
			<input type={ 'hidden' } { ...formMethods.register( `options.${ index }.menu_order` ) } />
		</>
	);
};

export default OptionForm;
