/**
 * Retrieves the input type for a field.
 *
 * @param {string} type
 * @return {string} The input type
 */
export const getFieldInputType = ( type ) => {
	switch ( type ) {
		case 'textarea':
			return 'textarea';
		case 'dropdown':
			return 'select';
		default:
			return 'input';
	}
};

export const isFieldType = ( field, types ) => {
	if ( Array.isArray( types ) ) {
		return types.includes( field?.type );
	}
	return field?.type === types;
};

export const isCheckboxLike = ( field ) => {
	return isFieldType( field, [ 'checkbox', 'image_buttons', 'text_labels' ] );
};

export const isRadioLike = ( field ) => {
	return isFieldType( field, [ 'radio', 'color_swatches' ] );
};

export const isTextLike = ( field ) => {
	return isFieldType( field, [ 'text', 'textarea', 'customer_price' ] );
};
