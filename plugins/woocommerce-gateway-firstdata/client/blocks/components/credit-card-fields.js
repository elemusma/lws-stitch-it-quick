import { useState, useEffect } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { settings } from "../utils";
import { HostedField } from "./hosted-field";

export const CreditCardFields = ( props ) => {
	const { components: { LoadingMask }, isLoaded, hostedFieldsInstance } = props;
	const mapping = {
		CARD_NUMBER:         'number',
		CARD_DATE:           'expiry',
		CARD_CVV:            'cvc',
		CARD_STREET_ADDRESS: 'streetAddress',
		CARD_POSTAL_CODE:    'postalCode',
	}

	// field-specific error messages
	const [error, setError] = useState({
		number: '',
		expiry: '',
		cvc: '',
		streetAddress: '',
		postalCode: '',
	});

	// Handles iframe field errors.
	//
	// @param [Object] event error event details
	//   event is passed from the Clover SDK in the format:
	//   event.[CARD_CVV | CARD_DATE | CARD_NUMBER | CARD_STREET_ADDRESS | CARD_POSTAL_CODE].error for message, .touched if user has blurred or changed field
	function onHostedFieldChange( event ) {

		for ( const [hostedElement, fieldName] of Object.entries(mapping) ) {

			// show field error
			if ( event[ hostedElement ] !== undefined && event[ hostedElement ].error !== undefined ) {
				setError((prevState) => ({
					...prevState,
					[fieldName]: event[ hostedElement ].error
				}));
			}

			// remove the error message when field is valid and complete
			if ( event[ hostedElement ] !== undefined && event[ hostedElement ].error === undefined && event[ hostedElement ].touched ) {
				setError((prevState) => ({
					...prevState,
					[fieldName]: ''
				}));
			}
		}
	}

	useEffect(() => {
		if ( hostedFieldsInstance ) {
			Object.values( hostedFieldsInstance ).forEach( (element) => {
				element.addEventListener( 'change', (event) => { onHostedFieldChange( event ) } );
				element.addEventListener( 'blur',  (event) => { onHostedFieldChange( event ) } );
			} );
		}
	}, [hostedFieldsInstance]);

	return (
		<LoadingMask isLoading={!isLoaded} showSpinner={true}>
			<div style={{display: 'grid'}} className={`wc-block-card-elements payment_method_${settings.name}`}>

				<HostedField
					name="number"
					gatewayId={settings.id}
					error={error.number}
					label={__('Card Number', 'woocommerce-gateway-firstdata')}
				/>

				<div
					style={{
						display: 'grid',
						gridTemplateColumns: '1fr 1fr',
						gap: '16px',
					}}
				>
					<HostedField
						name="expiry"
						gatewayId={settings.id}
						error={error.expiry}
						label={__('Expiration', 'woocommerce-gateway-firstdata')}
					/>

					<HostedField
						name="cvc"
						gatewayId={settings.id}
						error={error.cvc}
						label={__('CVV', 'woocommerce-gateway-firstdata')}
					/>
				</div>
				{settings.flags?.avs_street_address && (
					<HostedField
						name="street-address"
						gatewayId={settings.id}
						error={error.streetAddress}
						label={__('Street Address', 'woocommerce-gateway-firstdata')}
					/>
				)}
				<div style={{display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px'}}>
					<HostedField
						name="postal-code"
						gatewayId={settings.id}
						error={error.postalCode}
						label={__('Postal Code', 'woocommerce-gateway-firstdata')}
					/>
				</div>
			</div>
		</LoadingMask>
	)
}
