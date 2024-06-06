jQuery( function( $ ) {

	'use strict';

	/**
	 * Payment.JS payment form handler.
	 *
	 * @since 4.7.0
	 *
	 * @type {Window.WC_First_Data_Payeezy_Payment_JS_Handler} object
	 */
	window.WC_First_Data_Payeezy_Payment_JS_Form_Handler = class WC_First_Data_Payeezy_Payment_JS_Form_Handler extends SV_WC_Payment_Form_Handler_v5_12_4 {


		/**
		 * Initializes the payment form.
		 *
		 * @since 4.7.0
		 *
		 * @param {object} args array of arguments
		 */
		constructor( args ) {

			super( args );

			// this will store the payment form object from Payment.JS upon initialization of the form
			this.paymentFormInstance = null;

			// store errors from Payment.JS (authorization or validation)
			this.errors = [];

			$( document.body ).on( 'sv_wc_payment_form_valid_payment_data', ( event, data ) =>  {

				// don't do anything if this isn't our form
				if ( data.payment_form.id !== this.id ) {
					return;
				}

				// bail if a saved method is selected
				if ( this.saved_payment_method_selected ) {

					console.log( 'Using an existing payment method...' );
					return;
				}

				// form was resubmitted, processing is complete
				if ( this.getClientToken() ) {

					console.log( 'Client token found: submitting form for processing...' );
					return true;

				} else if ( null !== this.paymentFormInstance ) {

					// block the UI
					this.form.block( { message: null, overlayCSS: { background: '#fff', opacity: 0.6 } } );

					this.paymentFormInstance.onSubmit(

						// on success
						function( clientToken ) {

							// console.log( clientToken );
							console.log( 'Payment.JS success' );

							// save client token to form hidden input
							$( 'input[name="wc-first-data-payeezy-credit-card-client-token"]' ).val( clientToken );

							// this breaks the current callback logic and force submits the form,
							// along with the now populated input above
							data.payment_form.form.submit();
						},

						// on failure
						function( error ) {

							console.log( 'Payment.JS error: ' + error );

							data.payment_form.render_errors( [ window.wc_first_data_payeezy_payment_js_payment_form.i18n.form_validation_error ] );
						}
					);

				} else {

					console.error( 'Payment form instance is missing' );

					data.payment_form.render_errors( [ window.wc_first_data_payeezy_payment_js_payment_form.i18n.general_error ] );

					return;
				}

				// return false by default to keep the form from submitting
				return false;

			} );
		}


		/**
		 * Sets up payment fields.
		 *
		 * In the case of Payment.JS, these fields are set up by the Payment.JS remote script.
		 * What we do here is merely invoke the Payment.JS method to build the form for us.
		 *
		 * @since 4.7.0
		 */
		set_payment_fields() {

			super.set_payment_fields();

			if ( this.form.find( 'input[name=payment_method]:checked' ).val() === this.id ) {
				this.block_ui();
			}


			let hooks  = { preFlowHook: ( callback ) => { this.authorizeSession( callback ) } },
			    config = window.wc_first_data_payeezy_payment_js_payment_form.payment_js_configuration;

			try {

				// bail if the SDK is missing
				if ( null === config || ! window.firstdata ) {
					throw new Error( 'SDK is not loaded.' );
				}

				// initialize Payment.JS pay form
				window.firstdata.createPaymentForm( config, hooks, ( PaymentForm ) => {

					console.log( 'Payment.JS initialized' );

					// store the callback within the handler
					this.paymentFormInstance = PaymentForm;

				} );

			} catch ( error ) {

				console.error( 'Failed initializing Payment.JS. ' + error.message );

				this.render_inline_error( window.wc_first_data_payeezy_payment_js_payment_form.i18n.general_error );

			} finally {

				this.unblock_ui()
			}
		}


		/**
		 * Authorizes the session.
		 *
		 * Submits authorization data to First Data callback to proceed with payment.
		 *
		 * @since 4.7.0
		 *
		 * @param {function} callback function
		 */
		authorizeSession( callback ) {

			console.log( 'Payment.JS session authorization...' );

			let ajaxUrl = window.wc_first_data_payeezy_payment_js_payment_form.ajax_url ? window.wc_first_data_payeezy_payment_js_payment_form.ajax_url : '',
			    nonce   = window.wc_first_data_payeezy_payment_js_payment_form.generate_payment_js_client_token_nonce ? window.wc_first_data_payeezy_payment_js_payment_form.generate_payment_js_client_token_nonce : '',
			    orderID = $( 'form#order_review' ).data( 'order-id' ) > 0 ? $( 'form#order_review' ).data( 'order-id' ) : ( window.wc_first_data_payeezy_payment_js_payment_form.order_id ? window.wc_first_data_payeezy_payment_js_payment_form.order_id : '' );

			// generate a client token via API call in background through AJAX (this will also store the token in the current order)
			$.post( ajaxUrl, {
				action:   'wc_first_data_payeezy_payment_js_generate_client_token',
				security: nonce,
				order:    orderID ? parseInt( orderID, 10 ) : 0,
			} ).done( ( response ) => {

				if ( ! response.success || ! response.data ) {

					console.log( 'Payment.JS session authorization error' );
					console.log( response.data ? response.data : response ); // response.data should have the error message

					this.render_errors( [ window.wc_first_data_payeezy_payment_js_payment_form.i18n.authorization_error ] );

				} else {

					console.log( 'Payment.JS session authorization success' );

					let clientToken     = response.data.clientToken,
					    publicKeyBase64 = response.data.publicKeyBase64;

					// trigger callback provided by Payment.JS
					callback( {
						clientToken:     clientToken,
						publicKeyBase64: publicKeyBase64
					} );
				}

			} );
		}


		/**
		 * Gets the stored client token.
		 *
		 * @since 4.7.0
		 *
		 * @returns {string} client token
		 */
		getClientToken() {

			// this value is set during session authorization
			return $( 'input[name="wc-first-data-payeezy-credit-card-client-token"]' ).val();
		}


		/**
		 * Validates the form.
		 *
		 * Payment.JS handles the validation for us, plus we can't interact with inputs in iframes.
		 * Therefore, we always return true trusting the validation from Payment.JS
		 *
		 * @since 4.7.0
		 *
		 * @returns {boolean} displays errors on failure
		 */
		validate_card_data() {

			return true;
		}


		/**
		 * Renders an error inline in the payment method UI.
		 *
		 * This is useful for failures that cause the form to be unavailable.
		 *
		 * @param {string} message
		 */
		render_inline_error( message ) {

			let $newPaymentMethod = $( 'div.js-wc-' + this.id_dasherized + '-new-payment-method-form' );

			$newPaymentMethod.prepend( '<div class="woocommerce-error">' + message + '</div>' );
			$newPaymentMethod.find( '.form-row' ).hide()
		}


	}

	// dispatch loaded event
	$( document.body ).trigger( "wc_first_data_payeezy_payment_js_form_handler_loaded" );

} );
