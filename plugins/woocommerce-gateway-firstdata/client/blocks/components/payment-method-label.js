import { decodeEntities } from "@wordpress/html-entities";
import { PaymentMethodIcons } from "./payment-method-icons";

/**
 * Creates an element for displaying the gateway title and icons.
 *
 * This would be normally displayed as the title element of the accordion component where the Checkout block lists payment methods.
 */
export const PaymentMethodLabel = (props) => {
	const {
		settings = {},
	} = props;

	const label = decodeEntities(settings.title || '');

	return (
		<div className={`sv-wc-payment-method-label wc-${settings.id}-label`}
		     style={{ display: 'flex', verticalAlign: 'middle', gap: '5px', justifyContent: 'space-between', width: '100%', paddingRight: '16px'}}>
			{label}
			<PaymentMethodIcons settings={settings} />
		</div>
	)
}
