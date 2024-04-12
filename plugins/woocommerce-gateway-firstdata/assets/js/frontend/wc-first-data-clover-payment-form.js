/*
 WooCommerce First Data Clover payment form handler
 Version 5.0.0

 Copyright: (c) 2013-2024, Kestrel [hey@kestrelwp.com]
 Licensed under the GNU General Public License v3.0
 http://www.gnu.org/licenses/gpl-3.0.html
*/
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/main/docs/suggestions.md
 */
jQuery(function( $ ) {

	"use strict";

	// Handles the payment forms for Clover Custom Checkout.
	//
	// @since 5.0.0
	window.WC_First_Data_Clover_Payment_Form_Handler = class WC_First_Data_Clover_Payment_Form_Handler extends SV_WC_Payment_Form_Handler_v5_11_12 {


		// Constructs the form handler.
		//
		// @since 5.0.0
		//
		// @param [Object] args =
		//     id:                      [String]  gateway ID
		//     id_dasherized:           [String]  gateway slug (dasherized ID)
		//     plugin_id:               [String]  the plugin ID
		//     type:                    [String]  gateway type, either `credit-card` or `echeck`
		//     type:                    [String]  gateway type, either `credit-card` or `echeck`
		//     csc_required:            [Boolean] if the gateway requires the CSC field to be displayed
		//     csc_required_for_tokens: [Boolean] true if the gateway requires the CSC field to be displayed when paying with a saved payment method
		//     enabled_card_types       [Array]: set of card types enabled for this gateway
		//     styles:                  [Array]   iframe field styles
		//     publicToken:             [String]  public token
		//     locale:                  [String]  locale for elements, e.g. en-US
		constructor( args ) {

			super( args );

			// Clover specific args
			this.debug       = args.debug;
			this.styles      = args.styles;
			this.publicToken = args.publicToken;
			this.locale      = args.locale;

			// TODO: note that we had to copy and paste this code from super() due an order of operations issue:
			//   we need this.publicToken to be set before handle_pay_page()/handle_add_payment_method_page() are called,
			//   but you can't use 'this' before super() is called, and super() meanwhile calls those handle methods.
			//   Basically the parent constructor in the framework is doing too much and/or isn't flexible enough as of 5.10.12 {JS - 2022-11-19}
			if ($( 'form#order_review' ).length) {
				this.handle_pay_page();

			} else if ($( 'form#add_payment_method' ).length) {
				this.handle_add_payment_method_page();
			}
		}


		// Handles the checkout page.
		//
		// @since 5.0.0
		handle_checkout_page() {

			super.handle_checkout_page();

			// unblock the UI when a server-side error occurs
			$( document.body ).on( 'checkout_error', () => {
				this.set_token();
				this.unblock_ui();
			});
		}


		// Handle required actions on the Order > Pay page
		//
		// @since 5.0.0
		handle_pay_page() {

			// see long comment in constructor
			if ( ! this.publicToken ) { return; }

			super.handle_pay_page();
		}


		// Handle required actions on the Add Payment Method page
		//
		// @since 5.0.0
		handle_add_payment_method_page() {

			// see long comment in constructor
			if ( ! this.publicToken ) { return; }

			super.handle_add_payment_method_page();
		}


		// Sets up the hosted iframe elements.
		//
		// @since 5.0.0
		set_payment_fields() {

			const cardNumberWrapper    = `#wc-${this.id_dasherized}-account-number-hosted`;
			const expiryWrapper        = `#wc-${this.id_dasherized}-expiry-hosted`;
			const cvvWrapper           = `#wc-${this.id_dasherized}-csc-hosted`;
			const streetAddressWrapper = `#wc-${this.id_dasherized}-street-address-hosted`;
			const postalCodeWrapper    = `#wc-${this.id_dasherized}-postal-code-hosted`;

			// bail if the custom input is already mounted on the placeholder
			if ($( `${cardNumberWrapper} iframe` ).length !== 0) { return; }

			// bail if the fields don't exist to mount to, e.g. a free order
			if ($(cardNumberWrapper).length === 0) { return; }

			this.integration = new Clover( this.publicToken, { locale: this.locale } );
			this.elements    = this.integration.elements();

			// create
			this.hostedElements = {
				cardNumber: this.elements.create('CARD_NUMBER', this.styles),
				expiry:     this.elements.create('CARD_DATE', this.styles),
				cvv:        this.elements.create('CARD_CVV', this.styles),
				postalCode: this.elements.create('CARD_POSTAL_CODE', this.styles),
			};

			// mount (note that CVV and postal code are required and the clover.createToken() won't return if they're not mounted)
			this.hostedElements.cardNumber.mount( cardNumberWrapper );
			this.hostedElements.expiry.mount( expiryWrapper );
			this.hostedElements.cvv.mount( cvvWrapper );
			this.hostedElements.postalCode.mount( postalCodeWrapper );


			if ($( streetAddressWrapper ).length !== 0) {
				// Optional street address code checkout field is enabled
				this.hostedElements['streetAddress'] = this.elements.create('CARD_STREET_ADDRESS', this.styles);
				this.hostedElements.streetAddress.mount( streetAddressWrapper );
			}

			// handle inline errors
			// note that jQuery.on doesn't seem to work
			Object.values( this.hostedElements ).forEach( element => {
				element.addEventListener( 'change', event => this.on_field_change( event ) );
				element.addEventListener( 'blur',   event => this.on_field_change( event ) );
			} );
		}


		// Handles iframe field errors.
		//
		// @since 5.0.0
		//
		// @param [Object] event error event details
		//   event is passed from the Clover SDK in the format:
		//   event.[CARD_CVV | CARD_DATE | CARD_NUMBER | CARD_STREET_ADDRESS | CARD_POSTAL_CODE].error for message, .touched if user has blurred or changed field
		on_field_change( event ) {

			const mapping = {
				CARD_NUMBER:         `#wc-${this.id_dasherized}-account-number-hosted-error`,
				CARD_DATE:           `#wc-${this.id_dasherized}-expiry-hosted-error`,
				CARD_CVV:            `#wc-${this.id_dasherized}-csc-hosted-error`,
				CARD_STREET_ADDRESS: `#wc-${this.id_dasherized}-street-address-hosted-error`,
				CARD_POSTAL_CODE:    `#wc-${this.id_dasherized}-postal-code-hosted-error`
			}

			for ( const [hostedElement, errorWrapper] of Object.entries(mapping) ) {

				// show field error
				if ( event[ hostedElement ] !== undefined && event[ hostedElement ].error !== undefined ) {
					$( errorWrapper ).text( event[ hostedElement ].error );
				}

				// remove the error message when field is valid and complete
				if ( event[ hostedElement ] !== undefined && event[ hostedElement ].error === undefined && event[ hostedElement ].touched ) {
					$( errorWrapper ).text( '' );
				}
			}
		}


		// Verifies the payment form.
		//
		// This either indicates a valid form if a token has already been generated
		// or a saved method is being used, or creates a new token via Clover SDK.
		//
		// @since 5.0.0
		//
		// @return [Boolean]
		validate_payment_data() {

			// sanity check to ensure we're validating our gateway
			if ( ! this.is_selected() ) { return true; }

			// bail if token is already present
			if ( this.has_token() ) { return true; }

			// bail if using a saved payment method
			if ( this.using_saved_card() ) { return true; }

			this.block_ui();

			// tokenize the form
			this.create_token();

			return false;
		}


		// Override from parent and do nothing
		format_credit_card_inputs() {
			// no-op
		}


		// Creates a new token from the hosted iframe fields.
		//
		// If creating a token is successful, it's set in the hidden field and the form is re-submitted,
		// at which point validate_payment_data() will pass successfully since the token is now set.
		//
		// @since 5.0.0
		create_token() {

			this.integration.createToken().then( result => {

				if ( result.token !== undefined ) {

					this.log( 'token created', result );

					this.set_token( result );

					return this.form.submit();
				} else {

					this.handle_token_error( result );
				}
			}).catch( error => {

				this.handle_token_error( error );
			});
		}

		// Handles errors in the token creation process
		//
		// @since 5.0.0
		handle_token_error( result ) {

			let error_message = '';

			if ( result.errors ) {

				Object.values( result.errors ).forEach( value => {
					error_message += (value + '<br />' );
				});

			} else {

				error_message = JSON.stringify( result );
			}

			this.log( 'token error', result );

			this.unblock_ui();

			this.set_token();

			// hide and remove any previous errors
			$( '.woocommerce-error, .woocommerce-message' ).remove();

			// add errors
			this.form.prepend('<p class="woocommerce-error">' + error_message + '</p>');

			// unblock UI
			this.form.removeClass( 'processing' ).unblock();
			this.form.find( '.input-text, select' ).blur();

			// scroll to top
			return $( 'html, body' ).animate( { scrollTop: this.form.offset().top - 100 }, 1000 );
		}


		// Sets the token data.
		//
		// Fills the JS token, expiry, and sanitized card number inputs from a createToken()
		// response. The result param can be omitted to clear those inputs, which
		// we want to do on checkout error to avoid reusing a previous token.
		//
		// @since 5.0.0
		//
		// @param [Object] result
		set_token( result ) {

			if ( result == null ) { result = { card: {} }; }

			const token    = ( result.token != null )           ? result.token       : '';
			const expMonth = ( result.card.exp_month != null )  ? result.card.exp_month : '';
			const expYear  = ( result.card.exp_year != null )   ? result.card.exp_year  : '';

			const cardNumber = ( result.card.last4 != null && result.card.first6 != null )  ? result.card.first6 + result.card.last4 : '';

			$( `input[name=wc-${this.id_dasherized}-js-token]` ).val( token );
			$( `input[name=wc-${this.id_dasherized}-account-number]` ).val( cardNumber );
			$( `input[name=wc-${this.id_dasherized}-exp-month]` ).val( expMonth );
			$( `input[name=wc-${this.id_dasherized}-exp-year]` ).val( expYear );
		}


		// Determines if the form already has a token set.
		//
		// @since 5.0.0
		//
		// @return [Boolean]
		has_token() { return $( `input[name=wc-${this.id_dasherized}-js-token]` ).val(); }


		// Determines if submitting the form with a saved card selected.
		//
		// @since 5.0.0
		//
		// @return [Boolean]
		using_saved_card() { return this.form.find( `input.js-wc-${this.id_dasherized}-payment-token:checked` ).val(); }


		// Determines if Clover is the selected payment method.
		//
		// @since 5.0.0
		//
		// @return [Boolean]
		is_selected() { return this.get_selected_gateway_id() === this.id; }


		// Gets the selected payment gateway ID.
		//
		// @since 5.0.0
		//
		// @return [String]
		get_selected_gateway_id() { return this.form.find( 'input[name=payment_method]:checked' ).val(); }


		// Logs a message and data.
		//
		// @since 5.0.0
		//
		// @param [String] message error or info message
		// @param [Object] error data to log
		log( message, data ) {

			if ( ! this.debug ) { return; }

			console.log( '[Clover] ' + message );
			console.log( data );
		}
	};


	// dispatch loaded event
	return $( document.body ).trigger( "wc_first_data_clover_payment_form_handler_loaded" );
});
