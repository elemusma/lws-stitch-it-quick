import { Label } from '@woocommerce/blocks-checkout';

/**
 * Hosted field label.
 *
 * @link https://github.com/woocommerce/woocommerce/blob/trunk/plugins/woocommerce-blocks/packages/components/label/index.tsx
 */
export const HostedFieldLabel = ( props ) => {
	return (
		<Label
			label={ props.label }
			screenReaderLabel={ props.screenReaderLabel || props.label }
			wrapperElement="label"
			wrapperProps={ {
				htmlFor: props.htmlFor,
				className: "required"
			} }
			htmlFor={ props.htmlFor }
		/>
	)
}
