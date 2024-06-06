import { useState, useCallback } from '@wordpress/element';
import { settings } from "./utils";

export const usePaymentForm = () => {

	const [hostedFieldsInstance, setHostedFieldsInstance] = useState(null);
	const [integrationInstance, setIntegrationInstance] = useState(null);

	const setupIntegration = useCallback(async () => {

		const defaultStyles = {
			body: {
				fontFamily: '"Source Sans Pro","HelveticaNeue-Light","Helvetica Neue Light","Helvetica Neue",Helvetica,Arial,"Lucida Grande",sans-serif',
			},
			input: {
				fontSize: '16px', // this makes the placeholders larger than they should be but the input text is the right size
				fontWeight: '400',
			}
		}

		const cardNumberWrapper    = `wc-${settings.id}-number-hosted`;
		const expiryWrapper        = `wc-${settings.id}-expiry-hosted`;
		const cvvWrapper           = `wc-${settings.id}-cvc-hosted`;
		const streetAddressWrapper = `wc-${settings.id}-street-address-hosted`;
		const postalCodeWrapper    = `wc-${settings.id}-postal-code-hosted`;

		// bail if the fields don't exist to mount to, e.g. a free order
		if ( document.getElementById( cardNumberWrapper ).length === 0 ) {
			return;
		}

		// bail if the custom input is already mounted on the placeholder
		if ( document.getElementById( cardNumberWrapper ).getElementsByTagName('iframe').length !== 0 ) {
			return;
		}

		const integration = new Clover( settings.public_token, { locale: settings.locale } );
		const elements = integration.elements();
		const styles = {
				...defaultStyles,
				...settings.hosted_field_styles
			}
		const hostedElements = {
			cardNumber: elements.create('CARD_NUMBER', styles),
			expiry:     elements.create('CARD_DATE', styles),
			cvv:        elements.create('CARD_CVV', styles),
			postalCode: elements.create('CARD_POSTAL_CODE', styles),
		};

		// mount (note that CVV and postal code are required and the clover.createToken() won't return if they're not mounted)
		hostedElements.cardNumber.mount( `#${cardNumberWrapper}` );
		hostedElements.expiry.mount( `#${expiryWrapper}` );
		hostedElements.cvv.mount( `#${cvvWrapper}` );
		hostedElements.postalCode.mount( `#${postalCodeWrapper}` );

		if ( settings.flags?.avs_street_address ) {
			// Optional street address code checkout field is enabled
			hostedElements['streetAddress'] = elements.create('CARD_STREET_ADDRESS', styles);
			hostedElements.streetAddress.mount( `#${streetAddressWrapper}` );
		}

		setHostedFieldsInstance(hostedElements);
		setIntegrationInstance(integration);
	}, []);

	return {
		setupIntegration,
		hostedFieldsInstance,
		integrationInstance
	};
};
