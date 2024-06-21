import conditionalLogic from '../wpo-single-product/conditional-logic';
import priceCalculator from '../wpo-single-product/price-calculator';
import fieldValidation from '../wpo-single-product/field-validation';

import fileUpload from '../wpo-single-product/fields/file-upload';
import imageButtons from '../wpo-single-product/fields/image-buttons';
import dropdown from '../wpo-single-product/fields/dropdown';
import customCheckboxes from '../wpo-single-product/fields/custom-checkboxes';
import datePicker from '../wpo-single-product/fields/date-picker';

import wptMultiCart from '../wpo-single-product/integration/wpt-multi-cart';
import wbvPriceCalculator from '../wpo-single-product/integration/wbv-price-calculator';

( function ( $ ) {
	/**
	 * WooCommerce Product Table
	 */
	$( document ).on( 'draw.wcpt responsiveDisplay.wcpt', '.wc-product-table', ( event, table ) => {
		const tableElement = table.$table.get( 0 );

		if ( table.config.multiAddToCart ) {
			const cartForms = tableElement.querySelectorAll( 'form.cart' );

			cartForms.forEach( ( cartForm ) => {
				wptMultiCart( cartForm ).init();
			} );
		}

		initModules( tableElement );
	} );

	/**
	 * General init helper.
	 *
	 * @param {HTMLElement} tableElement The table element where the cart options must be initialized.
	 * @return {void}
	 */
	function initModules( tableElement ) {
		const cartForms = tableElement ? tableElement.querySelectorAll( 'form.cart' ) : [];

		cartForms.forEach( ( cartForm ) => {
			conditionalLogic( cartForm ).init();
			fieldValidation( cartForm ).init();
			fileUpload( cartForm ).init();
			imageButtons( cartForm, $ ).init();

			// check if this is a WBV cart form
			if ( cartForm.classList.contains( 'wcbvp-cart' ) ) {
				wbvPriceCalculator( cartForm ).init();
			} else {
				priceCalculator( cartForm ).init();
			}
		} );

		dropdown.init();
		customCheckboxes.init();
		datePicker.init();
	}
} )( jQuery );
