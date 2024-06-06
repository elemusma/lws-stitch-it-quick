import { ValidationInputError } from '@woocommerce/blocks-checkout';
import { HostedFieldLabel } from "./hosted-field-label";

export const HostedField = (props) => {
	const {
		className = '',
		id,
		gatewayId,
		error,
		label,
		name,
		...rest
	} = props;

	const containerId = id || `wc-${gatewayId}-${name}-hosted`;

	return (
		<div className={[`wc-block-gateway-container wc-card-${name}-element`, className].join(' ')} {...rest}>
			<HostedFieldLabel
				label={label}
				htmlFor={containerId}
			/>
			<div
				id={containerId}
				style={{paddingTop: '14px'}} // This is a temporary fix for the hosted field input not being vertically centered.
				className={[`wc-block-gateway-input empty wc-${gatewayId}-hosted-field-input`].join(' ')}
			/>
			<ValidationInputError errorMessage={error} />
		</div>
	);
}
