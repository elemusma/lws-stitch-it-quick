
/**
 * Creates an element for displaying the gateway icon or icons.
 *
 * These may be displayed next to the payment method label.
 */
export const PaymentMethodIcons = (props) => {
	const {
		settings= {},
	} = props;

	const icons = settings.icons || {};
	const cardElements = Object.values( icons ).map( ( icon ) => (
		<img src={icon.src} alt={icon.alt} style={{ maxWidth: '40px' }} key={icon.id} />
	));

	return (
		<div className={`sv-wc-payment-method-icons wc-${settings.id}-icons`}
		     style={{verticalAlign: 'middle', display: 'flex', flexWrap: 'wrap', marginLeft: 'auto', gap: '5px'}}>
			{cardElements}
		</div>
	);
}
