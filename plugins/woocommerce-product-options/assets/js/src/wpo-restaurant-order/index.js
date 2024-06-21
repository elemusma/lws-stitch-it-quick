import conditionalLogic from '../wpo-single-product/conditional-logic';
import priceCalculator from '../wpo-single-product/price-calculator';
import fieldValidation from '../wpo-single-product/field-validation';

import fileUpload from '../wpo-single-product/fields/file-upload';
import imageButtons from '../wpo-single-product/fields/image-buttons';
import dropdown from '../wpo-single-product/fields/dropdown';
import customCheckboxes from '../wpo-single-product/fields/custom-checkboxes';
import datePicker from '../wpo-single-product/fields/date-picker';

( function ( $ ) {
	/**
	 * WooCommerce Restauarant Ordering (Custom init)
	 */
	$( document.body ).on( 'wro:modal:open', () => {
		const cartForms = document.querySelectorAll( 'form.cart' );

		cartForms.forEach( ( cartForm ) => {
			const addtoCartButton = cartForm.querySelector( 'button#add-product' );

			conditionalLogic( cartForm ).init();
			priceCalculator( cartForm, 'wro' ).init();
			fileUpload( cartForm, addtoCartButton ).init();
			fieldValidation( cartForm, addtoCartButton ).init();
			imageButtons( cartForm, $ ).init();
		} );

		dropdown.init();
		customCheckboxes.init();
		datePicker.init();
	} );
} )( jQuery );
