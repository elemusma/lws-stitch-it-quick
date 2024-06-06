import { useEffect } from '@wordpress/element';
import { decodeEntities } from '@wordpress/html-entities';
import { settings } from "./utils";

/**
 * Sets up payment details and POST data to be processed on server-side on checkout submission.
 *
 * @param {Function} onPaymentSetup       Callback for registering observers on the payment setup event
 * @param {Object}   emitResponse         Helpers for observer response objects
 * @param {Clover}   integrationInstance  Clover integration instance
 */
export const usePaymentProcessing = (
	onPaymentSetup,
	emitResponse,
	integrationInstance
) => {

	function handleTokenError(result) {

		const errorResponse = {
			type: emitResponse.responseTypes.ERROR,
			messageContext: emitResponse.noticeContexts.PAYMENTS,
		};

		let errorMessage = '';

		if ( result.errors ) {

			Object.values( result.errors ).forEach( value => {
				errorMessage += (value + "\n" );
			});

		} else {
			errorMessage = JSON.stringify( result );
		}

		return {
			...errorResponse,
			message: decodeEntities(errorMessage),
		};
	}

	useEffect(() => {

		const unsubscribe = onPaymentSetup(async () => {

			try {

				const result = await integrationInstance.createToken();

				if ( result.token === undefined ) {
					return handleTokenError( result );
				}

				const paymentData = {
					[`wc-${settings.id}-js-token`]: result.token,
					[`wc-${settings.id}-account-number`]: `${result.card.first6}${result.card.last4}`,
					[`wc-${settings.id}-exp-month`]: result.card.exp_month,
					[`wc-${settings.id}-exp-year`]: result.card.exp_year,
				};

				// All good to send payment data to server.
				return {
					type: emitResponse.responseTypes.SUCCESS,
					meta: {
						paymentMethodData: paymentData,
					},
				};

			} catch (error) {
				return handleTokenError( error );
			}
		});

		return unsubscribe;
	}, [
		emitResponse.responseTypes.SUCCESS,
		emitResponse.responseTypes.ERROR,
		emitResponse.noticeContexts.PAYMENTS,
		onPaymentSetup,
		integrationInstance,
	]);
};
